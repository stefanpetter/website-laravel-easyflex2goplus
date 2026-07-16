import fs from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, "..");
const downloadsDir = path.resolve(projectRoot, "downloads");

const DEFAULT_UPLOAD_URL = "https://ef2goplus.uniepool.com/api/csv/upload?token=8GTTefsDTHvSdNtmqjy0DavVky8mZeUX";
const DEFAULT_SUCCESS_STATUS = 201;
const RETRY_ATTEMPTS = 5;
const RETRY_DELAY_MS = 5000;

const sleep = (ms) => new Promise((resolve) => setTimeout(resolve, ms));

const isTransientError = (error) =>
  error instanceof TypeError && /EAI_AGAIN|ENOTFOUND|ECONNREFUSED|ECONNRESET|ETIMEDOUT/i.test(error.message + (error.cause?.message ?? ""));

const withRetry = async (label, fn) => {
  for (let attempt = 1; attempt <= RETRY_ATTEMPTS; attempt++) {
    try {
      return await fn();
    } catch (error) {
      if (attempt < RETRY_ATTEMPTS && isTransientError(error)) {
        const delay = RETRY_DELAY_MS * attempt;
        console.warn(`${label}: transient error on attempt ${attempt}/${RETRY_ATTEMPTS}, retrying in ${delay / 1000}s... (${error.cause?.message ?? error.message})`);
        await sleep(delay);
      } else {
        throw error;
      }
    }
  }
};

const normalizeMode = (mode) => {
  const normalized = (mode || "multipart").trim().toLowerCase();
  if (["multipart", "raw", "auto"].includes(normalized)) {
    return normalized;
  }
  throw new Error(`Invalid CSV_UPLOAD_MODE: ${mode}. Use one of: multipart, raw, auto`);
};

const buildHeaders = ({ bearerToken, extraHeaders = {} }) => {
  const headers = {
    Accept: "application/json",
    "X-Requested-With": "XMLHttpRequest",
    ...extraHeaders
  };
  if (bearerToken) {
    headers.Authorization = `Bearer ${bearerToken}`;
  }
  return headers;
};

const uploadMultipart = async ({ uploadUrl, fileFieldName, filename, csvBuffer, bearerToken }) => {
  const form = new FormData();
  form.append(fileFieldName, new Blob([csvBuffer], { type: "text/csv" }), filename);

  return fetch(uploadUrl, {
    method: "POST",
    headers: buildHeaders({ bearerToken }),
    redirect: "manual",
    body: form
  });
};

const uploadRaw = async ({ uploadUrl, filename, csvBuffer, bearerToken }) => {
  return fetch(uploadUrl, {
    method: "POST",
    headers: buildHeaders({
      bearerToken,
      extraHeaders: {
        "Content-Type": "text/csv",
        "X-Filename": filename
      }
    }),
    redirect: "manual",
    body: csvBuffer
  });
};

const describeError = async (response) => {
  const errorBody = await response.text();
  const location = response.headers.get("location");
  const locationInfo = location ? ` Location: ${location}.` : "";
  return `HTTP ${response.status} ${response.statusText}.${locationInfo} Body: ${errorBody}`;
};

const isExpectedSuccess = (response, expectedStatus) => response.status === expectedStatus;

const countCsvDataRows = (csvBuffer) => {
  const lines = csvBuffer
    .toString("utf8")
    .split(/\r?\n/)
    .map((line) => line.trim())
    .filter(Boolean);

  if (lines.length <= 1) {
    return 0;
  }

  return lines.length - 1;
};

const uploadCsvFile = async ({ csvPath, uploadUrl, fileFieldName, bearerToken, uploadMode, expectedStatus }) => {
  const filename = path.basename(csvPath);
  const csvBuffer = await fs.readFile(csvPath);
  const dataRowCount = countCsvDataRows(csvBuffer);

  if (dataRowCount === 0) {
    console.log(`Skipping ${filename}: CSV has no data rows.`);
    return;
  }

  if (uploadMode === "multipart") {
    const response = await uploadMultipart({ uploadUrl, fileFieldName, filename, csvBuffer, bearerToken });
    if (!isExpectedSuccess(response, expectedStatus)) {
      throw new Error(`Upload failed for ${filename} using multipart: ${await describeError(response)}`);
    }
    console.log(`Uploaded ${filename} using multipart: HTTP ${response.status}`);
    await fs.unlink(csvPath);
    console.log(`Deleted ${filename}`);
    return;
  }

  if (uploadMode === "raw") {
    const response = await uploadRaw({ uploadUrl, filename, csvBuffer, bearerToken });
    if (!isExpectedSuccess(response, expectedStatus)) {
      throw new Error(`Upload failed for ${filename} using raw: ${await describeError(response)}`);
    }
    console.log(`Uploaded ${filename} using raw: HTTP ${response.status}`);
    await fs.unlink(csvPath);
    console.log(`Deleted ${filename}`);
    return;
  }

  const multipartResponse = await uploadMultipart({ uploadUrl, fileFieldName, filename, csvBuffer, bearerToken });
  if (isExpectedSuccess(multipartResponse, expectedStatus)) {
    console.log(`Uploaded ${filename} using multipart: HTTP ${multipartResponse.status}`);
    await fs.unlink(csvPath);
    console.log(`Deleted ${filename}`);
    return;
  }

  const multipartError = await describeError(multipartResponse);
  console.warn(`Multipart upload failed for ${filename}. Retrying as raw text/csv. Reason: ${multipartError}`);

  const rawResponse = await uploadRaw({ uploadUrl, filename, csvBuffer, bearerToken });
  if (!isExpectedSuccess(rawResponse, expectedStatus)) {
    const rawError = await describeError(rawResponse);
    throw new Error(
      `Upload failed for ${filename} in auto mode. Multipart error: ${multipartError}. Raw error: ${rawError}`
    );
  }

  console.log(`Uploaded ${filename} using raw fallback: HTTP ${rawResponse.status}`);
  await fs.unlink(csvPath);
  console.log(`Deleted ${filename}`);
};

const main = async () => {
  const uploadUrl = process.env.CSV_UPLOAD_URL || DEFAULT_UPLOAD_URL;
  const fileFieldName = process.env.CSV_UPLOAD_FILE_FIELD || "file";
  const bearerToken = process.env.CSV_UPLOAD_BEARER_TOKEN || "";
  const uploadMode = normalizeMode(process.env.CSV_UPLOAD_MODE || "auto");
  const expectedStatus = Number(process.env.CSV_UPLOAD_SUCCESS_STATUS || DEFAULT_SUCCESS_STATUS);

  if (!Number.isInteger(expectedStatus) || expectedStatus < 100 || expectedStatus > 599) {
    throw new Error(`Invalid CSV_UPLOAD_SUCCESS_STATUS: ${process.env.CSV_UPLOAD_SUCCESS_STATUS}`);
  }

  const entries = await fs.readdir(downloadsDir, { withFileTypes: true });
  const csvFiles = entries
    .filter((entry) => entry.isFile() && /\.csv$/i.test(entry.name))
    .map((entry) => path.resolve(downloadsDir, entry.name));

  if (csvFiles.length === 0) {
    console.log("No .csv files found in downloads/. Skipping upload.");
    return;
  }

  for (const csvPath of csvFiles) {
    await withRetry(path.basename(csvPath), () =>
      uploadCsvFile({ csvPath, uploadUrl, fileFieldName, bearerToken, uploadMode, expectedStatus })
    );
  }
};

main().catch((error) => {
  console.error("CSV upload failed:", error);
  process.exit(1);
});