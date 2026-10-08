Extract documents helper

This folder contains a script to extract text from PDFs and run OCR on images.

Prerequisites (Windows):
- Python 3.8+
- Install Tesseract OCR: https://github.com/tesseract-ocr/tesseract
  - Add the Tesseract installation folder to your PATH, or set environment variable `TESSERACT_CMD`.
- (Optional) For some PDF -> image operations you may need `poppler` for `pdf2image` (not required for PyMuPDF fallback).

Install Python requirements:

```powershell
python -m pip install -r scripts/requirements.txt
```

Run extraction (scans project root and the Downloads folders used by the project):

```powershell
python scripts\extract_all_documents.py
```

Or provide custom paths (semicolon-separated):

```powershell
python scripts\extract_all_documents.py --paths "C:\path\to\pdfs;C:\another\folder"
```

Outputs will be written to `scripts/extracted_texts/` as .txt files.
