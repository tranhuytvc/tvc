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
    public function index()
    {
        $guests = Guest::withCount('checkins')->latest()->paginate(20);
        return view('cms.index', compact('guests'));
    }

    public function create()
    {
        return view('cms.create');
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'media_type' => 'required|in:image,video',
            'media' => 'required|file|max:102400',
        ]);

        $token = Str::random(16);
        $mediaFile = $request->file('media');
        $ext = $mediaFile->getClientOriginalExtension();
        $mediaPath = $mediaFile->storeAs('media', $token . '.' . $ext, 'public');

        $guest = Guest::create([
            'name' => $request->name,
            'email' => $request->email,
            'media_type' => $request->media_type,
            'media_path' => $mediaPath,
            'qr_token' => $token,
        ]);

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
            'name' => 'required|string|max:255',
            'email' => 'nullable|email|max:255',
            'media_type' => 'required|in:image,video',
            'media' => 'nullable|file|max:102400',
        ]);

        $data = [
            'name' => $request->name,
            'email' => $request->email,
            'media_type' => $request->media_type,
            'is_active' => $request->has('is_active'),
        ];

        if ($request->hasFile('media')) {
            if ($guest->media_path) {
                Storage::disk('public')->delete($guest->media_path);
            }
            $mediaFile = $request->file('media');
            $ext = $mediaFile->getClientOriginalExtension();
            $data['media_path'] = $mediaFile->storeAs('media', $guest->qr_token . '.' . $ext, 'public');
        }

        $guest->update($data);
        $this->generateQrCode($guest);

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

    public function downloadQr(Guest $guest)
    {
        $path = storage_path('app/public/' . $guest->qr_code_path);
        return response()->download($path, Str::slug($guest->name) . '-qr.png');
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
                $zip->addFile($filePath, Str::slug($guest->name) . '-' . $guest->qr_token . '.png');
            }
        }
        $zip->close();

        return response()->download($zipPath, 'qrcodes.zip')->deleteFileAfterSend();
    }

    private function generateQrCode(Guest $guest)
    {
        $url = route('welcome', ['token' => $guest->qr_token]);
        $dir = storage_path('app/public/qrcodes');
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
        $filename = 'qrcodes/' . $guest->qr_token . '.png';
        $filepath = storage_path('app/public/' . $filename);

        QrCode::format('png')
            ->size(400)
            ->errorCorrection('H')
            ->generate($url, $filepath);

        $guest->update(['qr_code_path' => $filename]);
    }
}
