#!/usr/bin/env python3
# Script to clean MahasiswaController.php
import re

filepath = r'c:\laragon\www\SPK_PMB\SPK_PMB\app\controllers\MahasiswaController.php'

with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

# 1. Remove golongan logic block:
#   $golongan = $_POST['golongan'] ?? 'Umum';
#   if (isset($_POST['ajukan_beasiswa'])) {
#       $golongan = ($_POST['ajukan_beasiswa'] === 'Beasiswa') ? 'Beasiswa' : 'Umum';
#   }
content = re.sub(
    r"\s*\$golongan = \$_POST\['golongan'\] \?\? 'Umum';\s*"
    r"if \(isset\(\$_POST\['ajukan_beasiswa'\]\)\) \{\s*"
    r"\$golongan = \(\$_POST\['ajukan_beasiswa'\] === 'Beasiswa'\) \? 'Beasiswa' : 'Umum';\s*\}\s*",
    '\n',
    content
)

# 2. Remove 'nim' => trim($_POST['nim'] ?? '') from $mhsData
content = re.sub(
    r"\s*'nim'\s*=>\s*trim\(\$_POST\['nim'\]\s*\?\?\s*''\),\s*",
    '\n',
    content
)

# 3. Remove 'golongan' => $golongan from $mhsData
content = re.sub(
    r"\s*'golongan'\s*=>\s*\$golongan\s*",
    '',
    content
)

# 4. Remove all beasiswa fields from $detailData
fields_to_remove = [
    'penghasilan_orangtua',
    'pekerjaan_ayah',
    'pekerjaan_ibu',
    'penghasilan_ayah',
    'penghasilan_ibu',
    'aset_kendaraan',
    'aset_bangunan',
    'uang_makan_harian',
]
for field in fields_to_remove:
    content = re.sub(
        r"\s*'" + re.escape(field) + r"'\s*=>[^\n,]+,?\s*",
        '\n',
        content
    )

# Write result
with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print("Controller cleaned successfully.")
print("Content preview (lines 60-100):")
lines = content.split('\n')
for i, line in enumerate(lines[59:100], 60):
    print(f"{i}: {line}")

