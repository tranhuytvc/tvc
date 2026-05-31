<?php

namespace App\Http\Controllers;

use App\Models\Guest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use SimpleSoftwareIO\QrCode\Facades\QrCode;
use ZipArchive;

class GuestController extends Controller
{
    public function index(Request $request)
    {
        $query = Guest::withCount('checkins');

        if ($search = $request->get('search')) {
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
            });
        }

        if ($status = $request->get('status')) {
            match ($status) {
                'locked'    => $query->where('is_locked', true),
                'active'    => $query->where('is_locked', false)->where('is_active', true),
                'inactive'  => $query->where('is_active', false),
                default     => null,
            };
        }

        $guests = $query->latest()->paginate(20)->withQueryString();
        $totalCount = Guest::count();
        return view('cms.index', compact('guests', 'totalCount'));
    }

    public function create()
    {
        return view('cms.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'media_type'     => 'required|in:image,video',
            'media'          => 'required|file|max:102400',
            'scan_mode'      => 'required|in:unlimited,one_time,checkin_checkout,max_scans',
            'max_scan_count' => 'required_if:scan_mode,max_scans|integer|min:1|max:999',
        ]);

        $mediaFile = $request->file('media');
        $ext = $mediaFile->getClientOriginalExtension();
        $tmpId = 'tmp_' . Str::random(8);
        $mediaPath = $mediaFile->storeAs('media', $tmpId . '.' . $ext, 'public');

        $guest = Guest::create([
            'name'           => $request->name,
            'email'          => $request->email,
            'media_type'     => $request->media_type,
            'media_path'     => $mediaPath,
            'scan_mode'      => $request->scan_mode,
            'max_scan_count' => $request->input('max_scan_count', 2),
        ]);

        $newMediaPath = 'media/guest_' . $guest->id . '.' . $ext;
        Storage::disk('public')->move($mediaPath, $newMediaPath);
        $guest->update(['media_path' => $newMediaPath]);

        $this->generateQrCode($guest);

        return redirect()->route('cms.index')->with('success', 'Thêm khách thành công!');
    }

    public function edit(Guest $guest)
    {
        return view('cms.edit', compact('guest'));
    }

    public function update(Request $request, Guest $guest)
    {
        $request->validate([
            'name'           => 'required|string|max:255',
            'email'          => 'nullable|email|max:255',
            'media_type'     => 'required|in:image,video',
            'media'          => 'nullable|file|max:102400',
            'scan_mode'      => 'required|in:unlimited,one_time,checkin_checkout,max_scans',
            'max_scan_count' => 'required_if:scan_mode,max_scans|integer|min:1|max:999',
        ]);

        $data = [
            'name'           => $request->name,
            'email'          => $request->email,
            'media_type'     => $request->media_type,
            'is_active'      => $request->has('is_active'),
            'scan_mode'      => $request->scan_mode,
            'max_scan_count' => $request->input('max_scan_count', 2),
        ];

        if ($request->hasFile('media')) {
            if ($guest->media_path) {
                Storage::disk('public')->delete($guest->media_path);
            }
            $mediaFile = $request->file('media');
            $ext = $mediaFile->getClientOriginalExtension();
            $data['media_path'] = $mediaFile->storeAs('media', 'guest_' . $guest->id . '.' . $ext, 'public');
        }

        $guest->update($data);

        if (!$guest->qr_code_path || !Storage::disk('public')->exists($guest->qr_code_path)) {
            $this->generateQrCode($guest);
        }

        return redirect()->route('cms.index')->with('success', 'Cập nhật thành công!');
    }

    // Xóa 1 khách + toàn bộ file liên quan
    public function destroy(Guest $guest)
    {
        $this->deleteGuestFiles($guest);
        $guest->delete();
        return redirect()->route('cms.index')->with('success', 'Đã xóa khách "' . $guest->name . '"!');
    }

    // Xóa nhiều khách được chọn
    public function destroySelected(Request $request)
    {
        $ids = $request->input('ids', []);
        if (empty($ids)) {
            return back()->with('error', 'Chưa chọn khách nào!');
        }

        $guests = Guest::whereIn('id', $ids)->get();
        $count = $guests->count();
        foreach ($guests as $guest) {
            $this->deleteGuestFiles($guest);
            $guest->delete();
        }

        return redirect()->route('cms.index')->with('success', "Đã xóa $count khách!");
    }

    // Xóa TẤT CẢ khách + toàn bộ file
    public function destroyAll()
    {
        $guests = Guest::all();
        foreach ($guests as $guest) {
            $this->deleteGuestFiles($guest);
        }
        Guest::truncate();

        // Clean up any orphan files in qrcodes/ and media/ directories
        Storage::disk('public')->deleteDirectory('qrcodes');
        Storage::disk('public')->deleteDirectory('media');

        return redirect()->route('cms.index')->with('success', 'Đã xóa tất cả dữ liệu khách mời!');
    }

    public function toggleLock(Guest $guest)
    {
        $guest->update(['is_locked' => !$guest->is_locked]);
        $msg = $guest->is_locked ? 'Đã khóa QR code!' : 'Đã mở khóa QR code!';
        return back()->with('success', $msg);
    }

    public function resetScans(Guest $guest)
    {
        $guest->update(['scan_count' => 0, 'is_locked' => false]);
        return back()->with('success', 'Đã đặt lại bộ đếm quét!');
    }

    // Tải QR 1 khách - tên file = tên khách
    public function downloadQr(Guest $guest)
    {
        $path = storage_path('app/public/' . $guest->qr_code_path);
        if (!file_exists($path)) {
            return back()->with('error', 'File QR chưa được tạo!');
        }
        $filename = $guest->name . '.png';
        return response()->download($path, $filename);
    }

    // Tải tất cả QR dạng ZIP - mỗi file tên = tên khách
    public function downloadAllQr()
    {
        $guests = Guest::whereNotNull('qr_code_path')->get();
        if ($guests->isEmpty()) {
            return back()->with('error', 'Chưa có QR code nào!');
        }

        $zipPath = storage_path('app/public/qrcodes_all.zip');
        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);

        $usedNames = []; // Handle duplicate names
        foreach ($guests as $guest) {
            $filePath = storage_path('app/public/' . $guest->qr_code_path);
            if (!file_exists($filePath)) continue;

            $baseName = $guest->name;
            $zipName = $baseName . '.png';

            // Deduplicate if same name
            if (isset($usedNames[$baseName])) {
                $usedNames[$baseName]++;
                $zipName = $baseName . ' (' . $usedNames[$baseName] . ').png';
            } else {
                $usedNames[$baseName] = 1;
            }

            $zip->addFile($filePath, $zipName);
        }
        $zip->close();

        return response()->download($zipPath, 'qr-codes.zip')->deleteFileAfterSend();
    }

    // Helper: xóa file media + QR của 1 khách
    private function deleteGuestFiles(Guest $guest): void
    {
        if ($guest->media_path) {
            Storage::disk('public')->delete($guest->media_path);
        }
        if ($guest->qr_code_path) {
            Storage::disk('public')->delete($guest->qr_code_path);
        }
    }

    private function generateQrCode(Guest $guest)
    {
        $url = route('welcome', ['qrCode' => $guest->qr_code]);
        $dir = storage_path('app/public/qrcodes');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = 'qrcodes/guest_' . $guest->id . '.png';
        $filepath = storage_path('app/public/' . $filename);

        QrCode::format('png')
            ->size(400)
            ->errorCorrection('H')
            ->generate($url, $filepath);

        $guest->update(['qr_code_path' => $filename]);
    }
}
