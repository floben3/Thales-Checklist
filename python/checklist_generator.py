import argparse
import mysql.connector
import csv
from reportlab.lib.pagesizes import landscape, A4
from reportlab.lib import colors
from reportlab.lib.styles import getSampleStyleSheet, ParagraphStyle
from reportlab.platypus import SimpleDocTemplate, Table, TableStyle, Paragraph
from reportlab.pdfgen import canvas
from reportlab.lib.units import inch
import html
import json
import datetime
import os
import sys
from db_connection import get_db_connection

def sanitize(input_string):
    """Sanitize input string by escaping HTML characters."""
    return html.escape(input_string.strip(), quote=True)

def erase_program_names(program_names, erased_program_names=None, profile=''):
    """Erase specified program names from a comma-separated list."""
    if erased_program_names is not None:
        if profile not in ['admin', 'superadmin']:
            return sanitize(', '.join(set(program_names.split(', ')) - set(erased_program_names)))
        else:
            program_array = {program_name: program_name[:-2] for program_name in program_names.split(', ')}
            return sanitize(', '.join(set(program_array.keys()) - set(erased_program_names)))
    else:
        return sanitize(program_names)

def good_practices_select(where_is=None, order_by=None, erased_goodpractices=None, erased_programs=None, profile=''):
    """Select good practices from the database based on criteria."""
    conn = get_db_connection()
    cursor = conn.cursor(dictionary=True)
    
    if profile not in ['admin', 'superadmin']:
        sql = """
            SELECT
                GOODPRACTICE.goodpractice_id,
                GROUP_CONCAT(DISTINCT PROGRAM.program_name ORDER BY PROGRAM.program_name SEPARATOR ', ') AS program_names,
                PHASE.phase_name,
                GOODPRACTICE.item,
                GROUP_CONCAT(DISTINCT KEYWORD.onekeyword ORDER BY KEYWORD.onekeyword SEPARATOR ', ') AS keywords
            FROM GOODPRACTICE
            INNER JOIN PHASE ON GOODPRACTICE.phase_id = PHASE.phase_id
            INNER JOIN GOODPRACTICE_PROGRAM ON GOODPRACTICE.goodpractice_id = GOODPRACTICE_PROGRAM.goodpractice_id
            INNER JOIN PROGRAM ON GOODPRACTICE_PROGRAM.program_id = PROGRAM.program_id
            INNER JOIN GOODPRACTICE_KEYWORD ON GOODPRACTICE.goodpractice_id = GOODPRACTICE_KEYWORD.goodpractice_id
            INNER JOIN KEYWORD ON GOODPRACTICE_KEYWORD.keyword_id = KEYWORD.keyword_id
            WHERE GOODPRACTICE.is_hidden = FALSE AND GOODPRACTICE_PROGRAM.is_hidden = FALSE
        """
    else:
        sql = """
            SELECT
                GOODPRACTICE.goodpractice_id,
                GROUP_CONCAT(DISTINCT PROGRAM.program_name ORDER BY PROGRAM.program_name SEPARATOR ', ') AS program_names,
                PHASE.phase_name,
                GOODPRACTICE.item,
                GROUP_CONCAT(DISTINCT KEYWORD.onekeyword ORDER BY KEYWORD.onekeyword SEPARATOR ', ') AS keywords
            FROM GOODPRACTICE
            INNER JOIN PHASE ON GOODPRACTICE.phase_id = PHASE.phase_id
            INNER JOIN GOODPRACTICE_PROGRAM ON GOODPRACTICE.goodpractice_id = GOODPRACTICE_PROGRAM.goodpractice_id
            INNER JOIN PROGRAM ON GOODPRACTICE_PROGRAM.program_id = PROGRAM.program_id
            INNER JOIN GOODPRACTICE_KEYWORD ON GOODPRACTICE.goodpractice_id = GOODPRACTICE_KEYWORD.goodpractice_id
            INNER JOIN KEYWORD ON GOODPRACTICE_KEYWORD.keyword_id = KEYWORD.keyword_id
        """
    
    params = []

    if where_is:
        where_clause_start = " AND (" if profile not in ['admin', 'superadmin'] else " WHERE ("
        where_clause = ''
        for column, filters in where_is.items():
            for i, value in enumerate(filters):
                if value:
                    where_clause += f"{column} = %s OR "
                    params.append(value)
            where_clause = where_clause.rstrip("OR ") + ") AND ("
        where_clause = where_clause.rstrip("AND (")
        sql += where_clause_start + where_clause
    
    if erased_goodpractices:
        erased_ids = ', '.join('%s' for _ in erased_goodpractices)
        sql += f" AND GOODPRACTICE.goodpractice_id NOT IN ({erased_ids})"
        params.extend(erased_goodpractices)

    sql += ' GROUP BY GOODPRACTICE.item'

    if order_by:
        column = order_by[0]
        ascending = order_by[1]
        direction = 'ASC' if ascending else 'DESC'
        sql += f" ORDER BY {column} {direction}"
    
    cursor.execute(sql, params)
    good_practices = cursor.fetchall()

    conn.close()
    
    if erased_programs:
        for good_practice in good_practices:
            gp_id = good_practice['goodpractice_id']
            if f'id{gp_id}' in erased_programs:
                good_practice['program_names'] = erase_program_names(good_practice['program_names'], erased_programs[f'id{gp_id}'], profile)
                if not good_practice['program_names']:
                    good_practices.remove(good_practice)

    transformed_good_practices = []
    for good_practice in good_practices:
        transformed_good_practices.append({
            'Programmes': good_practice['program_names'],
            'Phase': good_practice['phase_name'],
            'Item': good_practice['item'],
            'Mots-clés': good_practice['keywords'],
            'Appliquée': ''
        })
    
    return transformed_good_practices

def export_to_csv(data, filename):
    """Export data to CSV file."""
    try:
        header = ['Programmes', 'Phase', 'Item', 'Mots-clés', 'Appliquée']
        with open(filename, mode='w', newline='', encoding='utf-8') as file:
            writer = csv.writer(file)
            writer.writerow(header)
            for row in data:
                writer.writerow([row['Programmes'], row['Phase'], row['Item'], row['Mots-clés'], ''])
        return 0
    except:
        return 1

def get_unique_filename(base_filename):
    """Generate unique filename by appending (n) suffix if file exists."""
    if os.path.exists(base_filename):
        count = 1
        filename, extension = os.path.splitext(base_filename)
        while os.path.exists(f"{filename}({count}){extension}"):
            count += 1
        return f"{filename}({count}){extension}"
    else:
        return base_filename

def header_footer(canvas, doc, username):
    """Header and Footer setup for PDF."""
    canvas.saveState()

    # Header
    canvas.setFont('Helvetica', 12)
    canvas.drawString(inch, 8*inch, f"Identifiant : {username}")
    canvas.drawRightString(10.5*inch, 8*inch, f"Date : {datetime.date.today().strftime('%d/%m/%Y')}")

    # Title
    canvas.setFont('Helvetica-Bold', 16)
    canvas.drawCentredString(5.75*inch, 7.5*inch, "Checklist")

    # Footer
    canvas.setFont('Helvetica', 10)
    canvas.drawCentredString(5.75*inch, 0.75*inch, f"Page {canvas.getPageNumber()}")

    canvas.restoreState()

def export_to_pdf(data, filename, username):
    """Export data to PDF file."""
    try:
        doc = SimpleDocTemplate(filename, pagesize=landscape(A4))
        styles = getSampleStyleSheet()
        body_style = styles['BodyText']
        header_style = ParagraphStyle('header_style', parent=styles['BodyText'], fontName='Helvetica-Bold')

        table_data = []
        header_row = [
            Paragraph('Programmes', header_style),
            Paragraph('Phase', header_style),
            Paragraph('Item', header_style),
            Paragraph('Mots-clés', header_style),
            Paragraph('Appliquée', header_style)
        ]
        table_data.append(header_row)
        
        for row in data:
            table_data.append([
                Paragraph(row['Programmes'], body_style),
                Paragraph(row['Phase'], body_style),
                Paragraph(sanitize(row['Item']), body_style),
                Paragraph(row['Mots-clés'], body_style),
                Paragraph('', body_style)
            ])

        table = Table(table_data, colWidths=[120, 80, 350, 120, 120], repeatRows=1)
        table.setStyle(TableStyle([
            ('INNERGRID', (0, 0), (-1, -1), 0.25, colors.black),
            ('BOX', (0, 0), (-1, -1), 0.25, colors.black),
            ('TEXTCOLOR', (0, 0), (-1, 0), colors.black),
            ('ALIGN', (0, 0), (-1, -1), 'LEFT'),
            ('VALIGN', (0, 0), (-1, -1), 'MIDDLE'),
            ('BACKGROUND', (0, 0), (-1, 0), colors.lightgrey)
        ]))

        elements = [table]
        doc.build(elements, onFirstPage=lambda canvas, doc: header_footer(canvas, doc, username), onLaterPages=lambda canvas, doc: header_footer(canvas, doc, username))
        return 0
    except:
        return 2

def main():
    """Main function for handling command-line arguments and execution."""
    parser = argparse.ArgumentParser(description='Export good practices into CSV or PDF checklist.')
    parser.add_argument('--where', type=str, help='JSON string for WHERE clause filters.')
    parser.add_argument('--order', type=str, help='JSON string for ORDER BY clause.')
    parser.add_argument('--erased_goodpractices', type=str, help='Comma-separated list of goodpractice IDs to erase.')
    parser.add_argument('--erased_programs', type=str, help='JSON string for programs to erase.')    
    parser.add_argument('--username', type=str, help='Username for header.')
    parser.add_argument('--profile', type=str, choices=['operator', 'admin', 'superadmin'], required=True, help='Profile type.')
    parser.add_argument('--output_format', type=str, choices=['csv', 'pdf'], required=True, help='Output format (csv or pdf).')
    parser.add_argument('--output_file', type=str, required=True, help='Output file name.')

    args = parser.parse_args()

    where_is = json.loads(args.where) if args.where else None
    order_by = json.loads(args.order) if args.order else None
    erased_goodpractices = args.erased_goodpractices.split(',') if args.erased_goodpractices else None
    erased_programs = json.loads(args.erased_programs) if args.erased_programs else None
    profile = args.profile
    output_format = args.output_format
    output_file = args.output_file
    username = args.username

    data = good_practices_select(
        where_is=where_is,
        order_by=order_by,
        erased_goodpractices=erased_goodpractices,
        erased_programs=erased_programs,
        profile=profile
    )

    filename = get_unique_filename(output_file)
    if output_format == 'csv':
        exit_code = export_to_csv(data, filename)
    elif output_format == 'pdf':
        exit_code = export_to_pdf(data, filename, username)

    print(filename)
    sys.exit(exit_code)

if __name__ == "__main__":
    main()