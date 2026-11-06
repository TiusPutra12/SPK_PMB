#!/usr/bin/env python3
# Fix the controller file with proper formatting

filepath = r'c:\laragon\www\SPK_PMB\SPK_PMB\app\controllers\MahasiswaController.php'

with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# Fix the $mhsData block that got mangled
old = """$mhsData = [
'nama'     => $_POST['nama'] ?? '',];"""

new = """$mhsData = [
                'nama'     => $_POST['nama'] ?? '',
            ];"""

content = content.replace(old, new)

# Fix $detailData closing bracket indentation
old2 = """'alamat'               => $_POST['alamat'] ?? '',
];"""
new2 = """'alamat'               => $_POST['alamat'] ?? '',
            ];"""
content = content.replace(old2, new2)

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Formatting fixed.")
lines = content.split('\n')
for i, line in enumerate(lines[56:76], 57):
    print(f"{i}: {line}")

