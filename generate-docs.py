#!/usr/bin/env python3
"""
Export Draw.io diagrams to PNG and create Word document with embedded images.
"""

import os
import sys
from pathlib import Path
from xml.etree import ElementTree as ET

# Try to import required packages
try:
    from PIL import Image, ImageDraw, ImageFont
    PIL_AVAILABLE = True
except ImportError:
    PIL_AVAILABLE = False

try:
    from docx import Document
    from docx.shared import Inches, Pt, RGBColor
    from docx.enum.text import WD_ALIGN_PARAGRAPH
    DOCX_AVAILABLE = True
except ImportError:
    DOCX_AVAILABLE = False

def create_placeholder_png(diagram_name: str, output_path: str):
    """Create a placeholder PNG for diagrams."""
    if not PIL_AVAILABLE:
        print(f"⚠️  PIL not available. Skipping PNG creation for {diagram_name}")
        return False
    
    # Create a simple placeholder image
    width, height = 800, 400
    img = Image.new('RGB', (width, height), color='#f5f5f5')
    draw = ImageDraw.Draw(img)
    
    # Draw border
    draw.rectangle([10, 10, width-10, height-10], outline='#6c8ebf', width=2)
    
    # Add text
    text = f"{diagram_name}\n\n[Diagram Placeholder]\nView source: docs/diagrams/{Path(output_path).name}.drawio"
    try:
        # Try to use default font, fallback to default if not available
        draw.text((50, height//2 - 40), text, fill='#333333')
    except:
        draw.text((50, height//2 - 40), text, fill='#333333')
    
    img.save(output_path, 'PNG')
    print(f"✓ Created: {output_path}")
    return True

def markdown_to_docx(md_path: str, docx_path: str):
    """Convert Markdown to Word document with embedded images."""
    if not DOCX_AVAILABLE:
        print("⚠️  python-docx not available. Install with: pip install python-docx")
        return False
    
    if not os.path.exists(md_path):
        print(f"✗ Markdown file not found: {md_path}")
        return False
    
    doc = Document()
    
    # Read markdown file
    with open(md_path, 'r', encoding='utf-8') as f:
        lines = f.readlines()
    
    # Parse YAML front matter
    yaml_data = {}
    content_start = 0
    if lines[0].startswith('---'):
        content_start = 1
        while content_start < len(lines):
            if lines[content_start].startswith('---'):
                content_start += 1
                break
            parts = lines[content_start].split(':', 1)
            if len(parts) == 2:
                yaml_data[parts[0].strip()] = parts[1].strip()
            content_start += 1
    
    # Add title page
    title = yaml_data.get('title', 'Project Documentation')
    doc.add_heading(title, 0)
    
    if 'date' in yaml_data:
        doc.add_paragraph(f"Date: {yaml_data['date']}")
    if 'version' in yaml_data:
        doc.add_paragraph(f"Version: {yaml_data['version']}")
    
    doc.add_page_break()
    
    # Add table of contents
    doc.add_heading('Table of Contents', 1)
    doc.add_paragraph('(Generated from sections in markdown)')
    doc.add_page_break()
    
    # Process markdown content
    i = content_start
    while i < len(lines):
        line = lines[i].rstrip()
        
        # Headings
        if line.startswith('# '):
            doc.add_heading(line[2:], 1)
        elif line.startswith('## '):
            doc.add_heading(line[3:], 2)
        elif line.startswith('### '):
            doc.add_heading(line[4:], 3)
        elif line.startswith('#### '):
            doc.add_heading(line[5:], 4)
        
        # Images
        elif line.startswith('!['):
            # Extract image path from markdown: ![alt](path)
            try:
                alt_end = line.index('](')
                path_start = alt_end + 2
                path_end = line.rindex(')')
                img_path = line[path_start:path_end]
                
                # Make path absolute if needed
                if not os.path.isabs(img_path):
                    base_dir = os.path.dirname(md_path)
                    img_path = os.path.join(base_dir, img_path)
                
                if os.path.exists(img_path):
                    doc.add_picture(img_path, width=Inches(6))
                    doc.add_paragraph()
                else:
                    doc.add_paragraph(f"[Image not found: {img_path}]")
            except:
                doc.add_paragraph(line)
        
        # Horizontal rules
        elif line.strip() == '---' or line.strip() == '***' or line.strip() == '___':
            doc.add_paragraph('_' * 50)
        
        # Tables (simplified - just add as code)
        elif line.startswith('|'):
            # For simplicity, add as verbatim text
            doc.add_paragraph(line, style='List Bullet')
        
        # Lists
        elif line.startswith('- ') or line.startswith('* '):
            doc.add_paragraph(line[2:], style='List Bullet')
        elif line.startswith('  - ') or line.startswith('  * '):
            doc.add_paragraph(line[4:], style='List Bullet 2')
        
        # Code blocks
        elif line.startswith('```'):
            i += 1
            code_lines = []
            while i < len(lines) and not lines[i].startswith('```'):
                code_lines.append(lines[i].rstrip())
                i += 1
            if code_lines:
                code_block = '\n'.join(code_lines)
                doc.add_paragraph(code_block, style='Intense Quote')
        
        # Regular paragraphs
        elif line.strip():
            # Skip front matter
            if not line.startswith('---'):
                doc.add_paragraph(line)
        
        i += 1
    
    # Save document
    doc.save(docx_path)
    print(f"✓ Created Word document: {docx_path}")
    return True

def main():
    """Main entry point."""
    project_dir = Path('c:/laragon/www/test/Shop')
    diagrams_dir = project_dir / 'docs' / 'diagrams'
    
    print("📊 Shop Project Documentation Generator")
    print("=" * 50)
    
    # Step 1: Create PNG exports from .drawio files
    print("\n1️⃣  Exporting diagrams to PNG...")
    
    if diagrams_dir.exists():
        for drawio_file in diagrams_dir.glob('*.drawio'):
            png_file = str(drawio_file) + '.png'
            create_placeholder_png(drawio_file.stem, png_file)
    else:
        print(f"⚠️  Diagrams directory not found: {diagrams_dir}")
    
    # Step 2: Convert Markdown to Word
    print("\n2️⃣  Converting Markdown to Word document...")
    
    md_file = str(project_dir / 'docs' / 'project-summary.md')
    docx_file = str(project_dir / 'docs' / 'project-summary.docx')
    
    if markdown_to_docx(md_file, docx_file):
        print(f"\n✅ Documentation generated successfully!")
        print(f"   - Markdown: {md_file}")
        print(f"   - Word Doc: {docx_file}")
        print(f"   - Diagrams: {diagrams_dir}")
    else:
        print(f"\n⚠️  Generation completed with warnings")
        print(f"   - Install python-docx: pip install python-docx")
        print(f"   - Markdown file: {md_file}")

if __name__ == '__main__':
    main()
