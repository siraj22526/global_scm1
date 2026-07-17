@extends('layouts.app')

@section('title', 'Admin Dashboard - Global SCM Risk Intel')

@section('content')
<div class="mb-6">
    <h2 class="text-2xl font-bold">Panel Kontrol Administrator</h2>
    <p class="text-muted-foreground">Kelola pengguna, dataset pelabuhan (WPI), leksikon kata sentimen, dan pembobotan skor risiko.</p>
</div>

<div x-data="{ tab: 'users' }">
    <!-- Tab Navigation -->
    <div class="flex flex-wrap gap-2 border-b border-border mb-6" role="tablist">
        <button @click="tab = 'users'" :class="tab === 'users' ? 'bg-primary/10 text-primary border-primary' : 'text-muted-foreground hover:text-foreground border-transparent'" class="px-4 py-2.5 rounded-t-xl text-sm font-medium min-h-11 border-b-2 transition-colors duration-150"><i class="fa-solid fa-users mr-1"></i> Pengguna</button>
        <button @click="tab = 'ports'" :class="tab === 'ports' ? 'bg-primary/10 text-primary border-primary' : 'text-muted-foreground hover:text-foreground border-transparent'" class="px-4 py-2.5 rounded-t-xl text-sm font-medium min-h-11 border-b-2 transition-colors duration-150"><i class="fa-solid fa-anchor mr-1"></i> Pelabuhan (CSV)</button>
        <button @click="tab = 'weights'" :class="tab === 'weights' ? 'bg-primary/10 text-primary border-primary' : 'text-muted-foreground hover:text-foreground border-transparent'" class="px-4 py-2.5 rounded-t-xl text-sm font-medium min-h-11 border-b-2 transition-colors duration-150"><i class="fa-solid fa-scale-unbalanced mr-1"></i> Bobot Risiko</button>
        <button @click="tab = 'lexicon'" :class="tab === 'lexicon' ? 'bg-primary/10 text-primary border-primary' : 'text-muted-foreground hover:text-foreground border-transparent'" class="px-4 py-2.5 rounded-t-xl text-sm font-medium min-h-11 border-b-2 transition-colors duration-150"><i class="fa-solid fa-book-open mr-1"></i> Leksikon Sentimen</button>
    </div>

    <!-- 1. Users Panel -->
    <div x-show="tab === 'users'" x-cloak x-transition.opacity>
        <div class="skeuo-card p-5">
            <h5 class="font-bold mb-4">Daftar Pengguna</h5>
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead>
                        <tr class="text-left text-muted-foreground uppercase text-xs tracking-wide border-b border-border">
                            <th class="py-2.5 pr-3">Nama</th>
                            <th class="py-2.5 pr-3">Email</th>
                            <th class="py-2.5 pr-3">Role</th>
                            <th class="py-2.5 pr-3">Status</th>
                            <th class="py-2.5 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody id="adminUsersBody">
                        <tr><td colspan="5" class="text-center text-muted-foreground py-6">Memuat data pengguna...</td></tr>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- 2. Ports CSV Importer Panel -->
    <div x-show="tab === 'ports'" x-cloak x-transition.opacity>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="skeuo-card p-5">
                <h5 class="font-bold mb-3"><i class="fa-solid fa-file-csv mr-1 text-primary"></i> Impor Dataset World Port Index</h5>
                <p class="text-sm text-muted-foreground mb-4">Unggah berkas CSV untuk memperbarui basis data pelabuhan global secara massal. Ukuran maksimal file adalah 5MB.</p>

                <form id="portsImportForm" class="space-y-4">
                    <div>
                        <label for="csvFile" class="block text-sm font-semibold text-muted-foreground mb-1.5">Pilih Berkas CSV</label>
                        <input type="file" id="csvFile" class="input-skeuo" accept=".csv" required>
                    </div>
                    <button type="submit" class="btn-skeuo w-full">Unggah &amp; Proses Impor</button>
                </form>

                <div id="importLoading" class="hidden text-center py-3 mt-2">
                    <div class="spinner spinner-sm text-primary inline-block mr-2"></div>
                    <span class="text-sm text-muted-foreground">Memproses data... Harap tunggu</span>
                </div>
            </div>
            <div class="skeuo-card p-5">
                <h5 class="font-bold mb-3">Laporan Hasil Impor</h5>
                <div id="importReport" class="text-sm text-muted-foreground max-h-64 overflow-y-auto">
                    <p>Belum ada aktivitas impor di sesi ini.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- 3. Weights Panel -->
    <div x-show="tab === 'weights'" x-cloak x-transition.opacity>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="skeuo-card p-5">
                <h5 class="font-bold mb-3">Sesuaikan Bobot Skor Risiko</h5>
                <p class="text-sm text-muted-foreground mb-4">Total penjumlahan seluruh bobot komponen harus bernilai tepat <strong>1.00</strong> agar kalkulasi skor valid.</p>

                <form id="weightsForm" class="space-y-4">
                    <div>
                        <label class="block text-sm font-semibold text-muted-foreground mb-1.5">Bobot Cuaca (Weather Risk)</label>
                        <input type="number" step="0.01" min="0" max="1" class="input-skeuo" id="wWeather" value="{{ $weights['weather'] ?? 0.30 }}" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-muted-foreground mb-1.5">Bobot Sentimen Berita (Political News Risk)</label>
                        <input type="number" step="0.01" min="0" max="1" class="input-skeuo" id="wNews" value="{{ $weights['news'] ?? 0.40 }}" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-muted-foreground mb-1.5">Bobot Inflasi (Inflation Risk)</label>
                        <input type="number" step="0.01" min="0" max="1" class="input-skeuo" id="wInflation" value="{{ $weights['inflation'] ?? 0.20 }}" required>
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-muted-foreground mb-1.5">Bobot Nilai Kurs (Currency Risk)</label>
                        <input type="number" step="0.01" min="0" max="1" class="input-skeuo" id="wCurrency" value="{{ $weights['currency'] ?? 0.10 }}" required>
                    </div>
                    <button type="submit" class="btn-skeuo w-full">Simpan Bobot Baru</button>
                </form>
            </div>
        </div>
    </div>

    <!-- 4. Lexicon Panel -->
    <div x-show="tab === 'lexicon'" x-cloak x-transition.opacity>
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <!-- Form Tambah Kata -->
            <div class="skeuo-card p-5">
                <h5 class="font-bold mb-3">Tambah Kata Leksikon</h5>
                <form id="lexiconForm" class="space-y-4">
                    <div>
                        <label for="lexType" class="block text-sm font-semibold text-muted-foreground mb-1.5">Jenis Kata</label>
                        <select id="lexType" class="input-skeuo" required>
                            <option value="positive">Positif</option>
                            <option value="negative">Negatif</option>
                        </select>
                    </div>
                    <div>
                        <label for="lexWord" class="block text-sm font-semibold text-muted-foreground mb-1.5">Kata (Bahasa Inggris)</label>
                        <input type="text" id="lexWord" class="input-skeuo" placeholder="Contoh: delay, boom" required>
                    </div>
                    <button type="submit" class="btn-skeuo w-full">Tambah Kata</button>
                </form>
            </div>

            <!-- List Leksikon -->
            <div class="skeuo-card p-5 lg:col-span-2">
                <h5 class="font-bold mb-4">Kamus Kata Saat Ini</h5>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <h6 class="text-success font-bold border-b border-success/30 pb-2 mb-3">Positif</h6>
                        <div id="posLexList" class="flex flex-wrap gap-2 max-h-64 overflow-y-auto"></div>
                    </div>
                    <div>
                        <h6 class="text-danger font-bold border-b border-danger/30 pb-2 mb-3">Negatif</h6>
                        <div id="negLexList" class="flex flex-wrap gap-2 max-h-64 overflow-y-auto"></div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@section('scripts')
<script>
    window.addEventListener('DOMContentLoaded', () => {
        loadUsers();
        loadLexicon();

        document.getElementById('weightsForm').addEventListener('submit', handleWeightsUpdate);
        document.getElementById('lexiconForm').addEventListener('submit', handleLexiconStore);
        document.getElementById('portsImportForm').addEventListener('submit', handlePortsImport);
    });

    // 1. Load Users
    async function loadUsers() {
        const tbody = document.getElementById('adminUsersBody');
        try {
            const res = await apiFetch('/api/admin/users');
            if (res.success) {
                tbody.innerHTML = '';
                res.data.data.forEach(user => {
                    const row = document.createElement('tr');
                    row.className = 'border-t border-border';
                    row.innerHTML = `
                        <td class="py-2.5 pr-3">${user.name}</td>
                        <td class="py-2.5 pr-3 text-sm text-muted-foreground">${user.email}</td>
                        <td class="py-2.5 pr-3">
                            <select onchange="updateUserRole(${user.id}, this.value)" class="input-skeuo !min-h-9 !py-1 text-sm">
                                <option value="user" ${user.role === 'user' ? 'selected' : ''}>User</option>
                                <option value="admin" ${user.role === 'admin' ? 'selected' : ''}>Admin</option>
                            </select>
                        </td>
                        <td class="py-2.5 pr-3">
                            <span class="inline-flex px-2.5 py-1 rounded-full text-xs font-bold ${user.is_active ? 'bg-success/10 text-success' : 'bg-danger/10 text-danger'}">${user.is_active ? 'Aktif' : 'Nonaktif'}</span>
                        </td>
                        <td class="py-2.5 text-right">
                            <button onclick="toggleUserStatus(${user.id}, ${user.is_active})" class="btn-skeuo-outline !min-h-9 !py-1 text-sm ${user.is_active ? 'btn-skeuo-danger' : ''}">
                                ${user.is_active ? 'Nonaktifkan' : 'Aktifkan'}
                            </button>
                        </td>
                    `;
                    tbody.appendChild(row);
                });
            }
        } catch (err) {
            tbody.innerHTML = `<tr><td colspan="5" class="text-center text-danger py-6">${err.message || 'Gagal memuat pengguna.'}</td></tr>`;
        }
    }

    async function updateUserRole(userId, role) {
        try {
            const res = await apiFetch(`/api/admin/users/${userId}`, {
                method: 'PATCH',
                body: { role }
            });
            if (res.success) {
                loadUsers();
            }
        } catch (err) {
            if (err.cancelled) return;
            console.error(err);
            loadUsers();
        }
    }

    async function toggleUserStatus(userId, currentStatus) {
        try {
            const res = await apiFetch(`/api/admin/users/${userId}`, {
                method: 'PATCH',
                body: { is_active: !currentStatus }
            });
            if (res.success) {
                loadUsers();
            }
        } catch (err) {
            if (err.cancelled) return;
            console.error(err);
        }
    }

    // 2. Import Ports CSV
    async function handlePortsImport(e) {
        e.preventDefault();
        const fileInput = document.getElementById('csvFile');
        const file = fileInput.files[0];
        if (!file) return;

        const formData = new FormData();
        formData.append('file', file);

        document.getElementById('importLoading').classList.remove('hidden');
        const report = document.getElementById('importReport');
        report.innerHTML = '<p class="text-info">Sedang memproses file CSV...</p>';

        try {
            const res = await apiFetch('/api/admin/ports/import', {
                method: 'POST',
                body: formData
            });

            document.getElementById('importLoading').classList.add('hidden');
            if (res.success) {
                showToast(res.meta?.message || 'Impor data selesai.');

                let detailsHtml = '';
                if (res.data.details.length > 0) {
                    detailsHtml = '<h6 class="text-warning mt-3 mb-1 font-semibold">Rincian Baris Gagal:</h6><ul class="text-danger pl-4 list-disc">';
                    res.data.details.forEach(d => {
                        detailsHtml += `<li>${d}</li>`;
                    });
                    detailsHtml += '</ul>';
                }

                report.innerHTML = `
                    <div class="bg-success/10 text-success border border-success/25 rounded-xl p-3">
                        <strong>Impor Sukses!</strong><br>
                        - Berhasil diimpor: ${res.data.imported} pelabuhan<br>
                        - Gagal / Dilewati: ${res.data.failed} baris
                    </div>
                    ${detailsHtml}
                `;
                fileInput.value = '';
            }
        } catch (err) {
            document.getElementById('importLoading').classList.add('hidden');
            report.innerHTML = `<div class="bg-danger/10 text-danger border border-danger/25 rounded-xl p-3"><strong>Gagal Impor:</strong><br>${err.message}</div>`;
            showToast(err.message || 'Gagal memproses file.', 'error');
        }
    }

    // 3. Update Risk Weights
    async function handleWeightsUpdate(e) {
        e.preventDefault();
        const wWeather = parseFloat(document.getElementById('wWeather').value);
        const wNews = parseFloat(document.getElementById('wNews').value);
        const wInflation = parseFloat(document.getElementById('wInflation').value);
        const wCurrency = parseFloat(document.getElementById('wCurrency').value);

        const sum = wWeather + wNews + wInflation + wCurrency;
        if (Math.abs(sum - 1.0) > 0.001) {
            showToast(`Total bobot adalah ${sum.toFixed(2)}. Penjumlahan seluruh bobot harus berjumlah 1.00!`, 'error');
            return;
        }

        try {
            const res = await apiFetch('/api/admin/risk-weights', {
                method: 'PUT',
                body: {
                    weather: wWeather,
                    news: wNews,
                    inflation: wInflation,
                    currency: wCurrency
                }
            });
        } catch (err) {
            if (err.cancelled) return;
            console.error(err);
        }
    }

    // 4. Lexicon KAMUS
    async function loadLexicon() {
        const posList = document.getElementById('posLexList');
        const negList = document.getElementById('negLexList');

        try {
            const res = await apiFetch('/api/admin/lexicon');
            if (res.success) {
                posList.innerHTML = '';
                negList.innerHTML = '';

                const pos = res.data.positive;
                const neg = res.data.negative;

                Object.keys(pos).forEach(id => {
                    posList.appendChild(createLexiconBadge(id, pos[id], 'positive'));
                });

                Object.keys(neg).forEach(id => {
                    negList.appendChild(createLexiconBadge(id, neg[id], 'negative'));
                });
            }
        } catch (err) {
            console.error(err);
        }
    }

    function createLexiconBadge(id, word, type) {
        const badge = document.createElement('span');
        badge.className = `inline-flex items-center gap-2 px-2.5 py-1.5 rounded-lg text-sm ${type === 'positive' ? 'bg-success/10 text-success border border-success/30' : 'bg-danger/10 text-danger border border-danger/30'}`;
        badge.innerHTML = `
            ${word}
            <i onclick="deleteLexicon(${id}, '${type}')" class="fa-solid fa-xmark text-xs cursor-pointer opacity-60 hover:opacity-100"></i>
        `;
        return badge;
    }

    async function handleLexiconStore(e) {
        e.preventDefault();
        const type = document.getElementById('lexType').value;
        const wordInput = document.getElementById('lexWord');
        const word = wordInput.value;

        try {
            const res = await apiFetch('/api/admin/lexicon', {
                method: 'POST',
                body: { type, word }
            });
            if (res.success) {
                wordInput.value = '';
                loadLexicon();
            }
        } catch (err) {
            console.error(err);
        }
    }

    async function deleteLexicon(id, type) {
        try {
            const res = await apiFetch(`/api/admin/lexicon/${id}?type=${type}`, {
                method: 'DELETE'
            });
            if (res.success) {
                loadLexicon();
            }
        } catch (err) {
            if (err.cancelled) return;
            console.error(err);
        }
    }
</script>
@endsection
