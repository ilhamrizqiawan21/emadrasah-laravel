import os
from pathlib import Path

OUT_DIR = Path("docs_text")
OUT_DIR.mkdir(exist_ok=True)

PDF_DIRS = [
    Path(r"c:\laragon\www\emadrasah2"),
]

IMAGE_DIRS = [
    Path(r"c:\Users\ilham\Downloads\Petunjuk-Pengisian-Buku-Induk-dokumen-Statis"),
    Path(r"c:\Users\ilham\Downloads\Nomor-Induk-Peserta-didik-dalam-buku-Induk-Siswa"),
    Path(r"c:\Users\ilham\Downloads\BUKU-INDUK-REGISTER---PESERTA-MADRASAH-TSANAWIYAH-MTs-KURIKULUM-MERDEKA"),
    Path(r"c:\Users\ilham\Downloads\LAPORAN-HASIL-CAPAIAN-PEMBELAJARAN"),
    Path(r"c:\Users\ilham\Downloads\PEMERIKSAAN-BUKU-INDUK-REGISTER"),
]


"""Extract text from PDFs and run OCR on images.

Saves outputs to `scripts/extracted_texts/`.

Usage:
  python extract_all_documents.py            # scans default paths
  python extract_all_documents.py --paths "C:\path1;C:\path2"

Notes:
- Requires Tesseract OCR installed and on PATH (or set TESSERACT_CMD env).
- For PDF image OCR fallback, PyMuPDF is used to render pages to images.
"""
import argparse
import io
from pathlib import Path
import sys

try:
    import fitz  # PyMuPDF
except Exception:
    fitz = None

try:
    from PIL import Image
except Exception:
    Image = None

try:
    import pytesseract
except Exception:
    pytesseract = None


def extract_text_from_pdf(pdf_path, out_path):
    if fitz is None:
        print("PyMuPDF (fitz) not installed. Skipping PDF:", pdf_path)
        return
    doc = fitz.open(str(pdf_path))
    texts = []
    for page_num in range(len(doc)):
        page = doc.load_page(page_num)
        text = page.get_text("text")
        if text and text.strip():
            texts.append(f"\n=== Page {page_num+1} (extracted text) ===\n")
            texts.append(text)
        else:
            # fallback: render page and OCR
            if Image is None or pytesseract is None:
                texts.append(f"\n=== Page {page_num+1} (no text and OCR not available) ===\n")
                continue
            pix = page.get_pixmap(dpi=200)
            img_bytes = pix.tobytes("png")
            img = Image.open(io.BytesIO(img_bytes))
            ocr = pytesseract.image_to_string(img, lang='ind+eng')
            texts.append(f"\n=== Page {page_num+1} (OCR) ===\n")
            texts.append(ocr)
    out_path.parent.mkdir(parents=True, exist_ok=True)
    out_path.write_text("".join(texts), encoding="utf-8")
    print(f"Saved extracted text to {out_path}")


def extract_text_from_image(img_path, out_path):
    if Image is None or pytesseract is None:
        print("Pillow or pytesseract not installed. Skipping image:", img_path)
        return
    img = Image.open(img_path)
    text = pytesseract.image_to_string(img, lang='ind+eng')
    out_path.parent.mkdir(parents=True, exist_ok=True)
    out_path.write_text(text, encoding='utf-8')
    print(f"Saved OCR text to {out_path}")


def find_documents(paths):
    exts_pdf = {'.pdf'}
    exts_img = {'.jpg', '.jpeg', '.png', '.tif', '.tiff', '.bmp'}
    for base in paths:
        base_p = Path(base)
        if not base_p.exists():
            continue
        if base_p.is_file():
            ext = base_p.suffix.lower()
            if ext in exts_pdf or ext in exts_img:
                yield base_p
            continue
        for p in base_p.rglob('*'):
            if p.suffix.lower() in exts_pdf or p.suffix.lower() in exts_img:
                yield p


def main():
    parser = argparse.ArgumentParser()
    parser.add_argument('--paths', help='Semicolon-separated list of paths to scan')
    parser.add_argument('--outdir', help='Output directory (relative to script)', default='extracted_texts')
    args = parser.parse_args()

    script_dir = Path(__file__).resolve().parent
    project_root = script_dir.parent

    default_paths = [
        project_root,  # project root (will catch PDFs in project)
        r"C:\Users\ilham\Downloads\Petunjuk-Pengisian-Buku-Induk-dokumen-Statis",
        r"C:\Users\ilham\Downloads\Nomor-Induk-Peserta-didik-dalam-buku-Induk-Siswa",
        r"C:\Users\ilham\Downloads\BUKU-INDUK-REGISTER---PESERTA-MADRASAH-TSANAWIYAH-MTs-KURIKULUM-MERDEKA",
        r"C:\Users\ilham\Downloads\LAPORAN-HASIL-CAPAIAN-PEMBELAJARAN",
        r"C:\Users\ilham\Downloads\PEMERIKSAAN-BUKU-INDUK-REGISTER",
    ]
    if args.paths:
        paths = [p.strip() for p in args.paths.split(';') if p.strip()]
    else:
        paths = default_paths

    out_root = script_dir / args.outdir
    out_root.mkdir(parents=True, exist_ok=True)

    docs = list(find_documents(paths))
    if not docs:
        print("No documents found in the provided paths.")
        return

    for doc in docs:
        rel = doc.name
        out_file = out_root / (rel + '.txt')
        if doc.suffix.lower() == '.pdf':
            extract_text_from_pdf(doc, out_file)
        else:
            extract_text_from_image(doc, out_file)

    print("Done. Extracted texts are in:", out_root)


if __name__ == '__main__':
    main()
