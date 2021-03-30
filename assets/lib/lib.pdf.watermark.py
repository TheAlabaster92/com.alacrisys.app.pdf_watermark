# PDF Watermarker v 0.05 - Th92
## Requires os, sys, io, PyPDF2, reportlab
import os, sys, argparse, io, decimal
from PyPDF2                       import PdfFileReader, PdfFileWriter
from reportlab.pdfgen             import canvas
from reportlab.pdfbase.pdfmetrics import stringWidth


## Define Paths
watermark_text      = 'Copia per TEST'
input_file_path     = './dummy.pdf'
watermark_file_path = './dummy.watermark.png'
merged_file_path    = "./dummy.merged.pdf"

# Handles command line input
parser = argparse.ArgumentParser(
    description = 'This script adds the specified text to a PDF file as a watermark'
)
parser.add_argument('-i', '--input-file', metavar = 'input_file', required = True, help = 'The input PDF file')
parser.add_argument('-o', '--output-file', metavar = 'output_file', required = True, help = 'The output PDF file')
parser.add_argument('-t', '--text', metavar = 'text', required = True, help = 'The text to use as watermark')

args = parser.parse_args()

input_file_path     = args.input_file
merged_file_path    = args.output_file
watermark_text      = args.text

## Get PDF width-height
pdf_height  = 0
pdf_width   = 0

with io.open(input_file_path, mode = 'rb') as org_file:
    org_pdf     = PdfFileReader(org_file)
    box         = org_pdf.getPage(0).mediaBox
    pdf_height  = box.getHeight()
    pdf_width   = box.getWidth()


## 1. Create a canvas for the watermark text
c = canvas.Canvas('watermark.pdf')

c.setPageSize((pdf_width, pdf_height))
c.setFillColorRGB(1, 0.70, 0.70, 0.5)
c.setFont('Helvetica-Bold', 36)
textWidth = stringWidth(watermark_text, 'Helvetica-Bold', 36)
c.drawString(float(pdf_width) - float(textWidth) - float(15), float(pdf_height) - float(40), watermark_text)

### Save the canvas to file
c.save()


## 2. Add Watermark to PDF
### Open the watermark file
with io.open('./watermark.pdf', mode = 'rb') as watermark_file:
    watermark = PdfFileReader(watermark_file)

    ### Open the output file
    with io.open(merged_file_path, mode = 'wb') as merged_file:
        ### Start the PDF writer buffer
        output = PdfFileWriter()

        ### Open the input file
        with io.open(input_file_path, mode = 'rb') as input_file:
            input_pdf = PdfFileReader(input_file)
            page_count = input_pdf.getNumPages()

            ### Add watermark to every page
            for page_number in range(page_count):
                input_page = input_pdf.getPage(page_number)
                input_page.mergePage(watermark.getPage(0))
                output.addPage(input_page)

            ### Open the file stream to the output file and save the result
            with io.open(merged_file_path, mode = 'wb') as merged_file:
                #tmp_out = io.BytesIO()
                #output.write(tmp_out)
                #print(tmp_out.getvalue())
                output.write(merged_file)


## 3. Clean files
os.remove('./watermark.pdf')