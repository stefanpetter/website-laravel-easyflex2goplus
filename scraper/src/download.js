import { chromium } from "playwright";
import dotenv from "dotenv";
import fs from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";

dotenv.config();

const requiredVars = [
  "LOGIN_URL",
  "USERNAME",
  "PASSWORD"
];

const missing = requiredVars.filter((name) => !process.env[name]);
if (missing.length > 0) {
  console.error(`Missing required env vars: ${missing.join(", ")}`);
  process.exit(1);
}

const formatDate = (date) => {
  const year = date.getFullYear();
  const month = String(date.getMonth() + 1).padStart(2, "0");
  const day = String(date.getDate()).padStart(2, "0");
  return `${year}-${month}-${day}`;
};

const addDays = (date, days) => {
  const next = new Date(date);
  next.setDate(next.getDate() + days);
  return next;
};

const getCurrentWeekRange = () => {
  const now = new Date();
  const mondayBasedDayIndex = (now.getDay() + 6) % 7;
  const monday = addDays(now, -mondayBasedDayIndex);
  const sunday = addDays(monday, 6);
  return {
    start: formatDate(monday),
    end: formatDate(sunday)
  };
};

const buildPlanningUrl = () => {
  const baseUrl = process.env.PLANNING_URL_BASE || "https://uniepool.easyflex2go.nl/planning/assigner";
  const week = getCurrentWeekRange();
  const params = new URLSearchParams({
    f: '{"view_entities":[]}',
    fs: "",
    ps: week.start,
    pe: week.end,
    pcp: "day",
    v: process.env.PLANNING_V || "9",
    p: process.env.PLANNING_P || "1",
    pet: "",
    s: ""
  });
  return `${baseUrl}?${params.toString()}`;
};

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, "..");
const downloadsDir = path.resolve(projectRoot, "downloads");

await fs.mkdir(downloadsDir, { recursive: true });

const browser = await chromium.launch({
  headless: process.env.HEADLESS !== "false",
  args: ["--ignore-certificate-errors"]
});

const context = await browser.newContext({
  acceptDownloads: true,
  ignoreHTTPSErrors: true
});

const page = await context.newPage();

try {
  console.log("Opening login page...");
  await page.goto(process.env.LOGIN_URL, { waitUntil: "domcontentloaded" });

  if (process.env.LOGIN_ROLE) {
    console.log(`Selecting login role: ${process.env.LOGIN_ROLE}`);
    await page
      .getByRole("radio", { name: new RegExp(`^${process.env.LOGIN_ROLE}$`, "i") })
      .check({ force: true });
  }

  console.log("Filling credentials...");
  if (process.env.USERNAME_SELECTOR) {
    await page.fill(process.env.USERNAME_SELECTOR, process.env.USERNAME);
  } else {
    await page.getByRole("textbox").first().fill(process.env.USERNAME);
  }

  if (process.env.PASSWORD_SELECTOR) {
    await page.fill(process.env.PASSWORD_SELECTOR, process.env.PASSWORD);
  } else {
    await page.locator("input[type='password']").first().fill(process.env.PASSWORD);
  }

  console.log("Submitting login form...");
  if (process.env.SUBMIT_SELECTOR) {
    await page.click(process.env.SUBMIT_SELECTOR);
  } else {
    await page.getByRole("button", { name: /inloggen/i }).click();
  }

  const waitAfterLoginMs = Number(process.env.WAIT_AFTER_LOGIN_MS || 0);
  if (waitAfterLoginMs > 0) {
    console.log(`Waiting ${waitAfterLoginMs}ms after login (for redirects/MFA)...`);
    await page.waitForTimeout(waitAfterLoginMs);
  }

  console.log("Waiting for post-login page to be ready...");
  await Promise.race([
    page.waitForSelector("#planningIndex", { timeout: 20000 }),
    page.getByRole("link", { name: /planning/i }).first().waitFor({ timeout: 20000 }),
    page.waitForFunction(() => !window.location.href.includes("/login"), { timeout: 20000 })
  ]);
  console.log("Post-login page ready.");

  if (process.env.WAIT_FOR_MANUAL_CONFIRM === "true") {
    console.log("Manual confirmation mode enabled. Complete MFA manually in the opened browser.");
    console.log("Press Enter in terminal when done.");
    await new Promise((resolve) => {
      process.stdin.resume();
      process.stdin.once("data", resolve);
    });
  }

  let targetDownloadUrl = process.env.DOWNLOAD_PAGE_URL;
  if (process.env.USE_PLANNING_CURRENT_WEEK === "true") {
    targetDownloadUrl = buildPlanningUrl();
  }

  if (!targetDownloadUrl) {
    console.error("Set DOWNLOAD_PAGE_URL or USE_PLANNING_CURRENT_WEEK=true in .env");
    process.exit(1);
  }

  console.log("Clicking Planning in left menu...");
  await page
    .getByRole("link", { name: /^Planning$/i })
    .or(page.getByRole("button", { name: /^Planning$/i }))
    .first()
    .click({ noWaitAfter: true });
  await page.waitForTimeout(700);

  const submenuCandidates = [
    page.getByRole("link", { name: /^Unie-Pool Personeel B\.V\. Planning$/i }).first(),
    page.getByRole("link", { name: /^Unie-Pool Peroneel B\.V\. Planning$/i }).first(),
    page.getByRole("button", { name: /^Unie-Pool Personeel B\.V\. Planning$/i }).first(),
    page.getByRole("button", { name: /^Unie-Pool Peroneel B\.V\. Planning$/i }).first()
  ];

  let planningSubmenuItem = null;
  for (const candidate of submenuCandidates) {
    if ((await candidate.count()) > 0 && (await candidate.isVisible().catch(() => false))) {
      planningSubmenuItem = candidate;
      break;
    }
  }

  if (!planningSubmenuItem) {
    throw new Error("Could not find visible Planning submenu item for Unie-Pool ... Planning.");
  }

  console.log("Clicking Planning submenu item: Unie-Pool Peroneel B.V. Planning...");
  await planningSubmenuItem.click({ noWaitAfter: true });
  console.log("Waiting for planning view to be ready...");
  await Promise.race([
    page.waitForFunction(() => window.location.href.includes("/planning"), { timeout: 30000 }),
    page.getByTestId("export-planning-button").waitFor({ timeout: 30000 }),
    page.getByRole("button", { name: /exporteren/i }).first().waitFor({ timeout: 30000 })
  ]);
  console.log(`Planning view ready: ${page.url()}`);
  await page.waitForTimeout(3000);

  console.log("Opening export modal...");
  const exportOpenCandidates = [
    process.env.DOWNLOAD_BUTTON_SELECTOR ? page.locator(process.env.DOWNLOAD_BUTTON_SELECTOR).first() : null,
    page.getByTestId("export-planning-button").first(),
    page.getByRole("button", { name: /^Exporteren$/i }).first(),
    page.getByRole("link", { name: /^Exporteren$/i }).first()
  ].filter(Boolean);

  let exportTrigger = null;
  for (const candidate of exportOpenCandidates) {
    if ((await candidate.count()) > 0 && (await candidate.isVisible().catch(() => false))) {
      exportTrigger = candidate;
      break;
    }
  }

  if (!exportTrigger) {
    throw new Error("Could not find Exporteren trigger on planning page.");
  }

  await exportTrigger.click();

  const exportDialog = page.getByRole("dialog").last();
  await exportDialog.waitFor({ timeout: 20000 });

  const profileLabel = process.env.EXPORT_PROFILE_LABEL || "test export planning chaffeur";
  const escapedProfileLabel = profileLabel.replace(/[.*+?^${}()|[\]\\]/g, "\\$&");
  const profileRegex = new RegExp(`^${escapedProfileLabel}$`, "i");

  console.log(`Selecting export profile: ${profileLabel}...`);
  let profileSelected = false;

  const selectInDialog = exportDialog.locator("select").first();
  if ((await selectInDialog.count()) > 0) {
    const options = selectInDialog.locator("option");
    const optionsCount = await options.count();
    for (let i = 0; i < optionsCount; i += 1) {
      const optionText = (await options.nth(i).innerText()).trim();
      if (optionText.toLowerCase() === profileLabel.toLowerCase()) {
        await selectInDialog.selectOption({ index: i });
        profileSelected = true;
        break;
      }
    }
  }

  if (!profileSelected) {
    const combo = exportDialog.getByRole("combobox").first();
    if ((await combo.count()) > 0) {
      await combo.click();

      const roleOption = page.getByRole("option", { name: profileRegex }).first();
      if ((await roleOption.count()) > 0 && (await roleOption.isVisible().catch(() => false))) {
        await roleOption.click();
        profileSelected = true;
      }

      if (!profileSelected) {
        const textOption = page.getByText(profileRegex).first();
        if ((await textOption.count()) > 0 && (await textOption.isVisible().catch(() => false))) {
          await textOption.click();
          profileSelected = true;
        }
      }

      if (!profileSelected) {
        await combo.fill(profileLabel).catch(() => {});
        await combo.press("Enter").catch(() => {});
        profileSelected = true;
      }
    }
  }

  if (!profileSelected) {
    throw new Error(`Could not select "${profileLabel}" in geselecteerde kolommen.`);
  }

  console.log("Waiting for download to start...");
  const [download] = await Promise.all([
    page.waitForEvent("download"),
    exportDialog.getByRole("button", { name: /downloaden/i }).first().click()
  ]);

  const suggestedFilename = download.suggestedFilename();
  const filePath = path.resolve(downloadsDir, suggestedFilename);
  await download.saveAs(filePath);

  console.log(`Download completed: ${filePath}`);

  if (process.env.SCREENSHOT_AFTER_DOWNLOAD === "true") {
    const screenshotPath = path.resolve(downloadsDir, "after-download.png");
    await page.screenshot({ path: screenshotPath, fullPage: true });
    console.log(`Saved screenshot: ${screenshotPath}`);
  }
} catch (error) {
  console.error("Automation failed:", error);
  process.exitCode = 1;
} finally {
  await context.close();
  await browser.close();
}
