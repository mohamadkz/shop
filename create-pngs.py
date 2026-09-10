#!/usr/bin/env python3
"""Create placeholder PNG files for diagrams."""
import os

diagrams_dir = r"c:\laragon\www\test\Shop\docs\diagrams"

# Create simple placeholder PNG files (1x1 pixel valid PNGs)
# This is a valid 1x1 white PNG file in base64
png_base64 = "iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8DwHwAFBQIAX8jx0gAAAABJRU5ErkJggg=="

import base64

diagrams = [
    'high-level-architecture',
    'processing-pipeline',
    'component-relationships',
    'data-model'
]

for diagram in diagrams:
    png_path = os.path.join(diagrams_dir, f"{diagram}.drawio.png")
    png_data = base64.b64decode(png_base64)
    
    with open(png_path, 'wb') as f:
        f.write(png_data)
    
    print(f"Created: {png_path}")

print("Done!")
