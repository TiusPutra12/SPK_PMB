#!/usr/bin/env python3
filepath = r'c:\laragon\www\SPK_PMB\SPK_PMB\app\controllers\MahasiswaController.php'
with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()
# Fix $mhsData indentation
content = content.replace('\n$mhsData = [\n', '\n            $mhsData = [\n')
with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)
print("Done")
lines = content.split('\n')
for i, line in enumerate(lines[55:68], 56):
    print(f"{i}: {repr(line)}")

