import fs from "node:fs/promises";
import path from "node:path";
import { fileURLToPath } from "node:url";
import XLSX from "xlsx";

const __filename = fileURLToPath(import.meta.url);
const __dirname = path.dirname(__filename);
const projectRoot = path.resolve(__dirname, "..");
const downloadsDir = path.resolve(projectRoot, "downloads");

const convertWorkbookToCsv = async (xlsxPath) => {
  const workbook = XLSX.readFile(xlsxPath, { cellDates: true });

  if (!workbook.SheetNames.length) {
    throw new Error(`No sheets found in workbook: ${xlsxPath}`);
  }

  const firstSheetName = workbook.SheetNames[0];
  const firstSheet = workbook.Sheets[firstSheetName];
  const csv = XLSX.utils.sheet_to_csv(firstSheet);

  const csvPath = xlsxPath.replace(/\.xlsx$/i, ".csv");
  await fs.writeFile(csvPath, csv, "utf8");

  return { csvPath, firstSheetName };
};

const main = async () => {
  const entries = await fs.readdir(downloadsDir, { withFileTypes: true });
  const xlsxFiles = entries
    .filter((entry) => entry.isFile() && /\.xlsx$/i.test(entry.name))
    .map((entry) => path.resolve(downloadsDir, entry.name));

  if (xlsxFiles.length === 0) {
    console.log("No .xlsx files found in downloads/. Skipping conversion.");
    return;
  }

  for (const xlsxPath of xlsxFiles) {
    const { csvPath, firstSheetName } = await convertWorkbookToCsv(xlsxPath);
    console.log(`Converted ${path.basename(xlsxPath)} (sheet: ${firstSheetName}) -> ${path.basename(csvPath)}`);
    await fs.unlink(xlsxPath);
    console.log(`Deleted ${path.basename(xlsxPath)}`);
  }
};

main().catch((error) => {
  console.error("XLSX to CSV conversion failed:", error);
  process.exit(1);
});