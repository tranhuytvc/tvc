<?php

namespace App\Http\Controllers;

use App\Models\Station;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class StationController extends Controller
{
    public function index()
    {
        $stations = Station::withCount('checkins')->latest()->get();
        return view('cms.stations.index', compact('stations'));
    }

    public function create()
    {
        $defaults = Station::defaultSettings();
        return view('cms.stations.form', compact('defaults'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'scan_slug' => 'required|string|unique:stations,scan_slug|regex:/^[a-z0-9\-]+$/',
            'display_slug' => 'required|string|unique:stations,display_slug|regex:/^[a-z0-9\-]+$/',
        ]);

        $settings = $this->extractSettings($request);
        if ($request->hasFile('logo')) {
            $settings['logo_path'] = $request->file('logo')->store('settings/logos', 'public');
        }
        if ($request->hasFile('bg_image')) {
            $settings['bg_image'] = $request->file('bg_image')->store('settings/backgrounds', 'public');
        }
        if ($request->hasFile('bg_video')) {
            $settings['bg_video'] = $request->file('bg_video')->store('settings/backgrounds', 'public');
        }

        Station::create([
            'name' => $request->name,
            'scan_slug' => $request->scan_slug,
            'display_slug' => $request->display_slug,
            'settings' => $settings,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('cms.stations.index')->with('success', 'Tạo station thành công!');
    }

    public function edit(Station $station)
    {
        $defaults = Station::defaultSettings();
        $settings = $station->mergedSettings();
        return view('cms.stations.form', compact('station', 'defaults', 'settings'));
    }

    public function update(Request $request, Station $station)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'scan_slug' => 'required|string|unique:stations,scan_slug,' . $station->id . '|regex:/^[a-z0-9\-]+$/',
            'display_slug' => 'required|string|unique:stations,display_slug,' . $station->id . '|regex:/^[a-z0-9\-]+$/',
        ]);

        $settings = $this->extractSettings($request);

        if ($request->hasFile('logo')) {
            if ($station->getSetting('logo_path')) {
                Storage::disk('public')->delete($station->getSetting('logo_path'));
            }
            $settings['logo_path'] = $request->file('logo')->store('settings/logos', 'public');
        } else {
            $settings['logo_path'] = $station->getSetting('logo_path');
        }

        if ($request->hasFile('bg_image')) {
            if ($station->getSetting('bg_image')) {
                Storage::disk('public')->delete($station->getSetting('bg_image'));
            }
            $settings['bg_image'] = $request->file('bg_image')->store('settings/backgrounds', 'public');
        } else {
            $settings['bg_image'] = $station->getSetting('bg_image');
        }

        if ($request->hasFile('bg_video')) {
            if ($station->getSetting('bg_video')) {
                Storage::disk('public')->delete($station->getSetting('bg_video'));
            }
            $settings['bg_video'] = $request->file('bg_video')->store('settings/backgrounds', 'public');
        } else {
            $settings['bg_video'] = $station->getSetting('bg_video');
        }

        $station->update([
            'name' => $request->name,
            'scan_slug' => $request->scan_slug,
            'display_slug' => $request->display_slug,
            'settings' => $settings,
            'is_active' => $request->has('is_active'),
        ]);

        return redirect()->route('cms.stations.index')->with('success', 'Cập nhật thành công!');
    }

    public function destroy(Station $station)
    {
        if ($station->getSetting('logo_path')) {
            Storage::disk('public')->delete($station->getSetting('logo_path'));
        }
        if ($station->getSetting('bg_image')) {
            Storage::disk('public')->delete($station->getSetting('bg_image'));
        }
        if ($station->getSetting('bg_video')) {
            Storage::disk('public')->delete($station->getSetting('bg_video'));
        }
        $station->delete();
        return redirect()->route('cms.stations.index')->with('success', 'Đã xóa station!');
    }

    private function extractSettings(Request $request): array
    {
        return [
            'event_name' => $request->input('event_name', ''),
            'bg_type' => $request->input('bg_type', 'gradient'),
            'bg_color_from' => $request->input('bg_color_from', '#0f0c29'),
            'bg_color_to' => $request->input('bg_color_to', '#302b63'),
            'accent_color' => $request->input('accent_color', '#667eea'),
            'font_color' => $request->input('font_color', '#ffffff'),
            'welcome_title_checkin' => $request->input('welcome_title_checkin', 'Xin chào,'),
            'welcome_title_checkout' => $request->input('welcome_title_checkout', 'Tạm biệt,'),
            'welcome_msg_checkin' => $request->input('welcome_msg_checkin', 'Chào mừng bạn đã đến!'),
            'welcome_msg_checkout' => $request->input('welcome_msg_checkout', 'Hẹn gặp lại!'),
            'scan_title' => $request->input('scan_title', 'Quét mã QR Check-in'),
            'scan_subtitle' => $request->input('scan_subtitle', 'Đưa mã QR vào khung hình'),
            'display_orientation' => $request->input('display_orientation', 'landscape'),
            'countdown_seconds' => (int) $request->input('countdown_seconds', 10),
            'show_clock' => $request->boolean('show_clock', true),
            'idle_text' => $request->input('idle_text', 'Quét mã QR để check-in'),
        ];
    }
}
