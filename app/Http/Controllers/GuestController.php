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
            $query->where('name', 'like', "%$search%")
                  ->orWhere('email', 'like', "%$search%");
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
        return view('cms.index', compact('guests'));
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

        // Rename to stable guest-ID-based filename
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

        // Ensure QR file exists (qr_code UUID never changes)
        if (!$guest->qr_code_path || !Storage::disk('public')->exists($guest->qr_code_path)) {
            $this->generateQrCode($guest);
        }

        return redirect()->route('cms.index')->with('success', 'Cập nhật thành công!');
    }

    public function destroy(Guest $guest)
    {
        if ($guest->media_path) {
            Storage::disk('public')->delete($guest->media_path);
        }
        if ($guest->qr_code_path) {
            Storage::disk('public')->delete($guest->qr_code_path);
        }
        $guest->delete();
        return redirect()->route('cms.index')->with('success', 'Đã xóa khách!');
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

    public function downloadQr(Guest $guest)
    {
        $path = storage_path('app/public/' . $guest->qr_code_path);
        return response()->download($path, 'guest-' . $guest->id . '-' . Str::slug($guest->name) . '-qr.png');
    }

    public function downloadAllQr()
    {
        $guests = Guest::whereNotNull('qr_code_path')->get();
        $zipPath = storage_path('app/public/qrcodes_all.zip');

        $zip = new ZipArchive();
        $zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE);
        foreach ($guests as $guest) {
            $filePath = storage_path('app/public/' . $guest->qr_code_path);
            if (file_exists($filePath)) {
                $zip->addFile($filePath, 'guest-' . $guest->id . '-' . Str::slug($guest->name) . '.png');
            }
        }
        $zip->close();

        return response()->download($zipPath, 'qrcodes.zip')->deleteFileAfterSend();
    }

    private function generateQrCode(Guest $guest)
    {
        // URL uses stable UUID - not guessable, never changes
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
