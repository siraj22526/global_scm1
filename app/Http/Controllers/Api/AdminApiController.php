<?php

namespace App\Http\Controllers\Api;

use App\Models\User;
use App\Models\Port;
use App\Models\Country;
use App\Models\Article;
use App\Models\PositiveWord;
use App\Models\NegativeWord;
use App\Models\RiskWeight;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class AdminApiController extends ApiController
{
    /**
     * GET /api/admin/users
     */
    public function users()
    {
        $users = User::paginate(20);
        return $this->sendResponse($users);
    }

    /**
     * PATCH /api/admin/users/{id}
     */
    public function updateUser(Request $request, int $id)
    {
        $user = User::find($id);
        if (!$user) {
            return $this->sendError('USER_NOT_FOUND', 'User tidak ditemukan.', 404);
        }

        if ($user->id === auth()->id()) {
            return $this->sendError('SELF_EDIT_FORBIDDEN', 'Anda tidak dapat mengubah akun Anda sendiri.', 403);
        }

        $request->validate([
            'role' => ['sometimes', 'string', 'in:user,admin'],
            'is_active' => ['sometimes', 'boolean']
        ]);

        if ($request->has('role')) {
            $user->role = $request->input('role');
        }

        if ($request->has('is_active')) {
            $user->is_active = $request->input('is_active');
        }

        $user->save();

        return $this->sendResponse($user, 'User berhasil diperbarui.');
    }

    /**
     * POST /api/admin/ports
     */
    public function storePort(Request $request)
    {
        $data = $request->validate([
            'country_iso' => ['required', 'string', 'max:2'],
            'name' => ['required', 'string', 'max:150'],
            'wpi_code' => ['nullable', 'string', 'max:20'],
            'latitude' => ['required', 'numeric', 'between:-90,90'],
            'longitude' => ['required', 'numeric', 'between:-180,180'],
            'harbor_size' => ['nullable', 'string', 'max:20']
        ]);

        $country = Country::where('iso2', strtoupper($data['country_iso']))->first();
        if (!$country) {
            return $this->sendError('COUNTRY_NOT_FOUND', 'Negara asal pelabuhan tidak ditemukan.', 404);
        }

        $port = Port::create([
            'country_id' => $country->id,
            'name' => $data['name'],
            'wpi_code' => $data['wpi_code'],
            'latitude' => $data['latitude'],
            'longitude' => $data['longitude'],
            'harbor_size' => $data['harbor_size']
        ]);

        return $this->sendResponse($port, 'Pelabuhan berhasil ditambahkan.', 201);
    }

    /**
     * PUT /api/admin/ports/{id}
     */
    public function updatePort(Request $request, int $id)
    {
        $port = Port::find($id);
        if (!$port) {
            return $this->sendError('PORT_NOT_FOUND', 'Pelabuhan tidak ditemukan.', 404);
        }

        $data = $request->validate([
            'country_iso' => ['sometimes', 'string', 'max:2'],
            'name' => ['sometimes', 'string', 'max:150'],
            'wpi_code' => ['nullable', 'string', 'max:20'],
            'latitude' => ['sometimes', 'numeric', 'between:-90,90'],
            'longitude' => ['sometimes', 'numeric', 'between:-180,180'],
            'harbor_size' => ['nullable', 'string', 'max:20']
        ]);

        if (isset($data['country_iso'])) {
            $country = Country::where('iso2', strtoupper($data['country_iso']))->first();
            if (!$country) {
                return $this->sendError('COUNTRY_NOT_FOUND', 'Negara asal tidak ditemukan.', 404);
            }
            $port->country_id = $country->id;
        }

        if (isset($data['name'])) $port->name = $data['name'];
        if (array_key_exists('wpi_code', $data)) $port->wpi_code = $data['wpi_code'];
        if (isset($data['latitude'])) $port->latitude = $data['latitude'];
        if (isset($data['longitude'])) $port->longitude = $data['longitude'];
        if (array_key_exists('harbor_size', $data)) $port->harbor_size = $data['harbor_size'];

        $port->save();

        return $this->sendResponse($port, 'Pelabuhan berhasil diperbarui.');
    }

    /**
     * DELETE /api/admin/ports/{id}
     */
    public function destroyPort(int $id)
    {
        $port = Port::find($id);
        if (!$port) {
            return $this->sendError('PORT_NOT_FOUND', 'Pelabuhan tidak ditemukan.', 404);
        }

        $port->delete();

        return $this->sendResponse(null, 'Pelabuhan berhasil dihapus.');
    }

    /**
     * POST /api/admin/ports/import
     */
    public function importPorts(Request $request)
    {
        $request->validate([
            'file' => ['required', 'file', 'max:5120'], // Max 5MB
        ]);

        $file = $request->file('file');
        
        // Simple CSV parsing
        $path = $file->getRealPath();
        $handle = fopen($path, 'r');
        
        if (!$handle) {
            return $this->sendError('FILE_READ_FAILED', 'Gagal membaca file CSV.', 422);
        }

        // Headers
        $headers = fgetcsv($handle, 1000, ',');
        
        // Match header indices
        // Expected headers: country_code (or iso2), name, wpi_code, latitude, longitude, harbor_size
        $headerIndices = [
            'country_code' => -1,
            'name' => -1,
            'wpi_code' => -1,
            'latitude' => -1,
            'longitude' => -1,
            'harbor_size' => -1,
        ];

        foreach ($headers as $index => $colName) {
            $colName = strtolower(trim($colName));
            if (in_array($colName, ['country_code', 'country', 'iso2', 'iso'])) $headerIndices['country_code'] = $index;
            elseif (in_array($colName, ['name', 'port_name', 'port'])) $headerIndices['name'] = $index;
            elseif ($colName === 'wpi_code' || $colName === 'wpi') $headerIndices['wpi_code'] = $index;
            elseif ($colName === 'latitude' || $colName === 'lat') $headerIndices['latitude'] = $index;
            elseif ($colName === 'longitude' || $colName === 'lng' || $colName === 'lon') $headerIndices['longitude'] = $index;
            elseif ($colName === 'harbor_size' || $colName === 'size') $headerIndices['harbor_size'] = $index;
        }

        // Check if mandatory headers exist
        if ($headerIndices['name'] === -1 || $headerIndices['latitude'] === -1 || $headerIndices['longitude'] === -1 || $headerIndices['country_code'] === -1) {
            fclose($handle);
            return $this->sendError('INVALID_CSV_HEADERS', 'Header CSV harus berisi minimal: name, latitude, longitude, country_code.', 422);
        }

        $imported = 0;
        $failed = 0;
        $failedDetails = [];
        $rowNum = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle, 1000, ',')) !== false) {
                $rowNum++;
                $pName = $row[$headerIndices['name']] ?? '';
                $pLat = $row[$headerIndices['latitude']] ?? '';
                $pLng = $row[$headerIndices['longitude']] ?? '';
                $pCountryCode = $row[$headerIndices['country_code']] ?? '';
                $pWpi = $headerIndices['wpi_code'] !== -1 ? ($row[$headerIndices['wpi_code']] ?? null) : null;
                $pSize = $headerIndices['harbor_size'] !== -1 ? ($row[$headerIndices['harbor_size']] ?? null) : null;

                // Validate coordinates (Baris tanpa koordinat ditolak)
                if (empty($pLat) || empty($pLng) || !is_numeric($pLat) || !is_numeric($pLng)) {
                    $failed++;
                    $failedDetails[] = "Baris {$rowNum}: Koordinat latitude/longitude tidak valid atau kosong.";
                    continue;
                }

                if (empty($pName)) {
                    $failed++;
                    $failedDetails[] = "Baris {$rowNum}: Nama pelabuhan kosong.";
                    continue;
                }

                $country = Country::where('iso2', strtoupper(trim($pCountryCode)))
                    ->orWhere('iso3', strtoupper(trim($pCountryCode)))
                    ->first();

                if (!$country) {
                    $failed++;
                    $failedDetails[] = "Baris {$rowNum}: Negara dengan kode '{$pCountryCode}' tidak ditemukan.";
                    continue;
                }

                Port::updateOrCreate(
                    [
                        'country_id' => $country->id,
                        'name' => trim($pName)
                    ],
                    [
                        'wpi_code' => $pWpi ? trim($pWpi) : null,
                        'latitude' => (float) $pLat,
                        'longitude' => (float) $pLng,
                        'harbor_size' => $pSize ? trim($pSize) : null
                    ]
                );

                $imported++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            fclose($handle);
            return $this->sendError('IMPORT_EXCEPTION', 'Terjadi kesalahan sistem saat mengimpor data: ' . $e->getMessage(), 500);
        }

        fclose($handle);

        return $this->sendResponse([
            'imported' => $imported,
            'failed' => $failed,
            'details' => $failedDetails
        ], "Impor selesai: {$imported} pelabuhan berhasil, {$failed} gagal.");
    }

    /**
     * POST /api/admin/articles
     */
    public function storeArticle(Request $request)
    {
        $data = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'body' => ['required', 'string'],
            'status' => ['required', 'string', 'in:draft,published']
        ]);

        $article = Article::create([
            'user_id' => auth()->id(),
            'title' => $data['title'],
            'slug' => Str::slug($data['title']) . '-' . uniqid(),
            'body' => $data['body'],
            'status' => $data['status']
        ]);

        return $this->sendResponse($article, 'Artikel berhasil dibuat.', 201);
    }

    /**
     * PUT /api/admin/articles/{id}
     */
    public function updateArticle(Request $request, int $id)
    {
        $article = Article::find($id);
        if (!$article) {
            return $this->sendError('ARTICLE_NOT_FOUND', 'Artikel tidak ditemukan.', 404);
        }

        $data = $request->validate([
            'title' => ['sometimes', 'string', 'max:255'],
            'body' => ['sometimes', 'string'],
            'status' => ['sometimes', 'string', 'in:draft,published']
        ]);

        if (isset($data['title'])) {
            $article->title = $data['title'];
            $article->slug = Str::slug($data['title']) . '-' . uniqid();
        }
        if (isset($data['body'])) $article->body = $data['body'];
        if (isset($data['status'])) $article->status = $data['status'];

        $article->save();

        return $this->sendResponse($article, 'Artikel berhasil diperbarui.');
    }

    /**
     * DELETE /api/admin/articles/{id}
     */
    public function destroyArticle(int $id)
    {
        $article = Article::find($id);
        if (!$article) {
            return $this->sendError('ARTICLE_NOT_FOUND', 'Artikel tidak ditemukan.', 404);
        }

        $article->delete();

        return $this->sendResponse(null, 'Artikel berhasil dihapus.');
    }

    /**
     * GET /api/admin/lexicon
     */
    public function lexicon()
    {
        $pos = PositiveWord::all()->pluck('word', 'id');
        $neg = NegativeWord::all()->pluck('word', 'id');

        return $this->sendResponse([
            'positive' => $pos,
            'negative' => $neg
        ]);
    }

    /**
     * POST /api/admin/lexicon
     */
    public function storeLexicon(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', 'string', 'in:positive,negative'],
            'word' => ['required', 'string', 'max:50', 'alpha']
        ]);

        $word = strtolower(trim($data['word']));
        
        if ($data['type'] === 'positive') {
            $wordObj = PositiveWord::firstOrCreate(['word' => $word]);
            Cache::forget('lexicon_positive_words');
        } else {
            $wordObj = NegativeWord::firstOrCreate(['word' => $word]);
            Cache::forget('lexicon_negative_words');
        }

        return $this->sendResponse($wordObj, 'Kata berhasil ditambahkan ke leksikon.', 201);
    }

    /**
     * DELETE /api/admin/lexicon/{id}
     */
    public function destroyLexicon(Request $request, int $id)
    {
        $type = $request->query('type');
        if (!in_array($type, ['positive', 'negative'])) {
            return $this->sendError('INVALID_TYPE', "Parameter 'type' harus bernilai 'positive' atau 'negative'.", 422);
        }

        if ($type === 'positive') {
            $wordObj = PositiveWord::find($id);
            if ($wordObj) {
                $wordObj->delete();
                Cache::forget('lexicon_positive_words');
            }
        } else {
            $wordObj = NegativeWord::find($id);
            if ($wordObj) {
                $wordObj->delete();
                Cache::forget('lexicon_negative_words');
            }
        }

        return $this->sendResponse(null, 'Kata berhasil dihapus dari leksikon.');
    }

    /**
     * PUT /api/admin/risk-weights
     */
    public function updateWeights(Request $request)
    {
        $data = $request->validate([
            'weather' => ['required', 'numeric', 'between:0,1'],
            'news' => ['required', 'numeric', 'between:0,1'],
            'inflation' => ['required', 'numeric', 'between:0,1'],
            'currency' => ['required', 'numeric', 'between:0,1'],
        ]);

        $sum = array_sum($data);
        if (abs($sum - 1.0) > 0.001) {
            return $this->sendError('INVALID_WEIGHTS_SUM', 'Total seluruh bobot harus berjumlah 1.00.', 422);
        }

        foreach ($data as $comp => $weight) {
            RiskWeight::where('component', $comp)->update(['weight' => $weight]);
        }

        return $this->sendResponse(RiskWeight::all(), 'Bobot skor risiko berhasil diperbarui.');
    }
}
