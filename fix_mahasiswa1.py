#!/usr/bin/env python3
# Script to clean mahasiswa.php

filepath = r'c:\laragon\www\SPK_PMB\SPK_PMB\app\pages\mahasiswa.php'

with open(filepath, 'r', encoding='utf-8') as f:
    content = f.read()

original_len = len(content)
print(f"Original length: {original_len} chars")

# ============================================================
# 1. Remove PHP variables at top (lines 66-75) - golongan_val and beasiswa vars
# ============================================================
old_php_vars = """            $golongan_val = $data['golongan'] ?? 'Beasiswa';
            $editNilai = $nilai_raw_mhs[$id] ?? [];
            $penghasilan_val = $data['penghasilan_orangtua'] ?? '';
            $pekerjaan_ayah_val = $data['pekerjaan_ayah'] ?? '';
            $pekerjaan_ibu_val = $data['pekerjaan_ibu'] ?? '';
            $penghasilan_ayah_val = $data['penghasilan_ayah'] ?? '';
            $penghasilan_ibu_val = $data['penghasilan_ibu'] ?? '';
            $aset_kendaraan_val = $data['aset_kendaraan'] ?? '';
            $aset_bangunan_val = $data['aset_bangunan'] ?? '';
            $uang_makan_val = $data['uang_makan_harian'] ?? '';"""

new_php_vars = """            $editNilai = $nilai_raw_mhs[$id] ?? [];"""

content = content.replace(old_php_vars, new_php_vars)

# ============================================================
# 2. Remove hidden inputs: nim and golonganInput (lines 145-146)
# ============================================================
old_hidden = """                    <input type="hidden" name="nim" value="<?= htmlspecialchars($data['nim'] ?? '') ?>">
                    <input type="hidden" id="golonganInput" name="golongan" value="<?= htmlspecialchars($golongan_val) ?>">"""

new_hidden = ""

content = content.replace(old_hidden, new_hidden)

# ============================================================
# 3. Remove Step 3 tracker node and line (lines 128-132)
# ============================================================
old_tracker3 = """                    <div id="tracker-line-3" class="flex-1 h-1 mx-2 sm:mx-4 bg-gray-200 rounded relative overflow-hidden"><div id="step-line-2" class="h-full bg-purple-600 w-0 transition-all duration-500"></div></div>
                    <div id="tracker-node-3" class="flex flex-col items-center relative z-10 cursor-pointer" onclick="validateAndGoStep(3)">
                        <div id="step-node-3" class="w-12 h-12 rounded-full border-4 border-gray-300 bg-white text-gray-500 font-bold text-base flex items-center justify-center shadow-sm transition-all duration-300">3</div>
                        <span id="step-label-3" class="text-xs font-semibold text-gray-400 mt-2 text-center transition-colors">Pengajuan Beasiswa</span>
                    </div>"""

new_tracker3 = ""

content = content.replace(old_tracker3, new_tracker3)

# ============================================================
# 4. Remove the HASIL GABUNGAN section (lines 307-362)
# ============================================================
old_hasil_gabungan = """
                            <!-- HASIL GABUNGAN SEMUA SKOR NILAI PENILAIAN -->
                            <div class="p-6 bg-gradient-to-r from-purple-50/70 via-white to-purple-50/70 border-2 border-purple-300 rounded-2xl shadow-sm mt-6">
                                <div class="flex items-center justify-between pb-3.5 mb-4 border-b border-purple-200 flex-wrap gap-2">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl bg-purple-600 text-white flex items-center justify-center shadow-md">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-3m-3 3h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                        </div>
                                        <div>
                                            <h4 class="font-bold text-gray-900 text-sm tracking-wider uppercase">Hasil Gabungan Skor Nilai Penilaian</h4>
                                            <p class="text-xs text-gray-500">Hasil gabungan dan kalkulasi otomatis dari seluruh kriteria penilaian mahasiswa.</p>
                                        </div>
                                    </div>
                                    <span id="badge_status_gabungan" class="text-xs font-semibold px-3 py-1 rounded-full bg-gray-100 text-gray-600 border border-gray-200">
                                        Menunggu Input Nilai
                                    </span>
                                </div>

                                <!-- Field Menampilkan Hasil Gabungan Semua Skor Nilai -->
                                <div class="mb-5">
                                    <label class="filter-label text-xs font-bold text-purple-900 mb-1.5 flex items-center justify-between">
                                        <span>Hasil Gabungan Skor Nilai (Otomatis)</span>
                                        <span class="text-[11px] text-purple-600 font-semibold" id="text_terisi_counter">0/<?= count($kriteria_db) ?> Kriteria Terisi</span>
                                    </label>
                                    <div class="relative">
                                        <input type="text" id="field_hasil_gabungan" name="field_hasil_gabungan" readonly 
                                               class="filter-input w-full bg-white font-mono font-bold text-base sm:text-lg text-purple-900 border-2 border-purple-300 shadow-sm px-4 py-3 rounded-xl focus:outline-none" 
                                               placeholder="Hasil gabungan skor nilai akan otomatis dihitung setelah nilai diinput...">
                                    </div>
                                </div>

                                <!-- Rincian Akumulasi & Statistik -->
                                <div id="breakdown_gabungan_container" class="space-y-4 pt-3 border-t border-purple-100 hidden">
                                    <div class="grid grid-cols-1 sm:grid-cols-3 gap-3">
                                        <div class="p-3.5 bg-white rounded-xl border border-purple-100 shadow-sm text-center">
                                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-1">Total Akumulasi Skor</span>
                                            <span id="stat_total_skor" class="text-xl font-black text-purple-800 font-mono">-</span>
                                        </div>
                                        <div class="p-3.5 bg-white rounded-xl border border-purple-100 shadow-sm text-center">
                                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-1">Nilai Akhir Terbobot (SPK)</span>
                                            <span id="stat_skor_terbobot" class="text-xl font-black text-emerald-600 font-mono">-</span>
                                        </div>
                                        <div class="p-3.5 bg-white rounded-xl border border-purple-100 shadow-sm text-center">
                                            <span class="text-[11px] font-semibold text-gray-400 uppercase tracking-wider block mb-1">Rata-rata Skor Kriteria</span>
                                            <span id="stat_rata_skor" class="text-xl font-black text-blue-600 font-mono">-</span>
                                        </div>
                                    </div>

                                    <div>
                                        <span class="text-xs font-bold text-gray-700 uppercase tracking-wider block mb-2">Rincian Skor Tiap Kriteria:</span>
                                        <div id="pills_breakdown_skor" class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5">
                                            <!-- Diisi secara otomatis oleh JavaScript -->
                                        </div>
                                    </div>
                                </div>
                            </div>"""

content = content.replace(old_hasil_gabungan, "")

# ============================================================
# 5. Remove button "Lanjut ke Proses 3" and replace "Simpan Jalur Reguler" with "Simpan Data"
#    and remove onclick="setGolonganUmumDirect()"
# ============================================================
old_buttons_step2 = """                        <div class="flex justify-between items-center pt-2 gap-3 flex-wrap">
                            <button type="button" onclick="goToStep(1)" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition-colors">Kembali ke Data Diri</button>
                            <div class="flex items-center gap-3">
                                <button type="submit" name="<?= $isEdit ? 'simpan_edit' : 'simpan_tambah' ?>" onclick="setGolonganUmumDirect()" class="px-6 py-3 bg-white border-2 border-purple-600 text-purple-700 font-bold rounded-lg hover:bg-purple-50 transition-all flex items-center gap-2 shadow-sm cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Simpan (Jalur Reguler / Tanpa Beasiswa)</span>
                                </button>
                                <button type="button" id="btnLanjutKe3" onclick="validateAndGoStep(3)" class="px-7 py-3 bg-gradient-to-r from-amber-500 to-amber-600 text-white font-bold rounded-lg shadow-md hover:opacity-90 transition-all flex items-center gap-2 cursor-pointer">
                                    <span>Lanjut ke Proses 3: Pengajuan Beasiswa</span>
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                                </button>
                            </div>
                        </div>"""

new_buttons_step2 = """                        <div class="flex justify-between items-center pt-2 gap-3 flex-wrap">
                            <button type="button" onclick="goToStep(1)" class="px-6 py-3 bg-gray-200 text-gray-700 font-bold rounded-lg hover:bg-gray-300 transition-colors">Kembali ke Data Diri</button>
                            <div class="flex items-center gap-3">
                                <button type="submit" name="<?= $isEdit ? 'simpan_edit' : 'simpan_tambah' ?>" class="px-6 py-3 bg-gradient-to-r from-purple-600 to-purple-500 text-white font-bold rounded-lg shadow-md hover:opacity-90 transition-all flex items-center gap-2 cursor-pointer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    <span>Simpan Data</span>
                                </button>
                            </div>
                        </div>"""

content = content.replace(old_buttons_step2, new_buttons_step2)

# ============================================================
# 6. Remove entire section-step-3 div block (lines 379-576)
# ============================================================
old_step3_section = """
                    <div id="section-step-3" class="space-y-6 hidden">
                        <div class="p-6 border border-amber-300 rounded-xl bg-amber-50/20">
                            <div class="flex items-center justify-between pb-4 border-b border-amber-200 mb-5">
                                <h3 class="text-sm font-bold uppercase tracking-wider text-amber-900 flex items-center gap-2">
                                    <span class="w-6 h-6 rounded-full bg-amber-500 text-white text-xs font-bold flex items-center justify-center">3</span> 
                                    PROSES 3: PENGAJUAN BEASISWA & DATA SOSIAL EKONOMI
                                </h3>
                                <span id="badgeStatusBeasiswa" class="text-xs font-semibold <?= $golongan_val === "Umum" ? "bg-gray-100 text-gray-600 border-gray-200" : "bg-amber-100 text-amber-800 border-amber-200" ?> px-3 py-1 rounded-full border"><?= $golongan_val === "Umum" ? "Jalur Reguler / Tanpa Beasiswa (Opsional)" : "Kriteria Penilaian Ekonomi" ?></span>
                            </div>"""

# Find the end of section-step-3
# Look for the closing div after section-step-3
import re

# Use regex to find and remove the entire section-step-3 block
pattern = r'\n\s*<div id="section-step-3"[^>]*>.*?</div>\s*\n\s*</form>'
match = re.search(pattern, content, re.DOTALL)
if match:
    # Keep </form> but remove the section
    content = content[:match.start()] + '\n                </form>'
    print("section-step-3 removed via regex")
else:
    print("WARNING: section-step-3 not found by regex, trying manual approach")
    # Try a simpler approach - find the section manually
    start_marker = '\n                    <div id="section-step-3"'
    end_marker = '\n                </form>'
    start_idx = content.find(start_marker)
    end_idx = content.find(end_marker)
    if start_idx != -1 and end_idx != -1:
        content = content[:start_idx] + end_marker
        print("section-step-3 removed via manual approach")
    else:
        print(f"Manual approach: start_idx={start_idx}, end_idx={end_idx}")

with open(filepath, 'w', encoding='utf-8') as f:
    f.write(content)

print(f"\nNew length: {len(content)} chars (removed {original_len - len(content)} chars)")
print("mahasiswa.php step 1 done (hidden inputs, tracker, step3 section, result section)")

