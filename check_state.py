#!/usr/bin/env python3
# EMERGENCY RESTORE: Re-read original and apply all changes carefully

filepath = r'c:\laragon\www\SPK_PMB\SPK_PMB\app\pages\mahasiswa.php'

# First, check what we have left
with open(filepath, 'r', encoding='utf-8') as f:
    current = f.read()
print(f"Current length: {len(current)}")
print("First 200 chars of current content:")
print(repr(current[:200]))
print("\nLast 200 chars of current content:")
print(repr(current[-200:]))

