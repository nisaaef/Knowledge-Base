<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SettingController extends Controller
{
    public function edit()
    {
        return view('admin.settings.edit', [
            'values' => [
                'site_name' => Setting::read('site_name'),
                'site_description' => Setting::read('site_description'),
                'hero_title' => Setting::read('hero_title'),
                'hero_subtitle' => Setting::read('hero_subtitle'),
                'logo_show_name' => Setting::read('logo_show_name'),
            ],
            'logoUrl' => Setting::logoUrl(),
        ]);
    }

    public function update(Request $request)
    {
        $data = $request->validate([
            'site_name' => ['required', 'string', 'max:60'],
            'site_description' => ['nullable', 'string', 'max:160'],
            'hero_title' => ['nullable', 'string', 'max:100'],
            'hero_subtitle' => ['nullable', 'string', 'max:200'],
            'logo_show_name' => ['nullable', 'in:0,1'],
            'logo' => ['nullable', 'image', 'mimes:png,jpg,jpeg,webp', 'max:1024'],
            'remove_logo' => ['nullable', 'boolean'],
        ], [
            'site_name.required' => 'Nama situs wajib diisi.',
            'logo.image' => 'Logo harus berupa gambar.',
            'logo.mimes' => 'Logo harus berformat PNG, JPG, atau WebP.',
            'logo.max' => 'Ukuran logo maksimal 1 MB.',
        ]);

        Setting::put($data);

        $oldPath = Setting::allValues()['logo_path'] ?? null;

        if ($request->hasFile('logo')) {
            $path = $request->file('logo')->store('branding', 'public');
            Setting::setValue('logo_path', $path);

            if ($oldPath) {
                Storage::disk('public')->delete($oldPath);
            }
        } elseif ($request->boolean('remove_logo') && $oldPath) {
            Setting::setValue('logo_path', null);
            Storage::disk('public')->delete($oldPath);
        }

        return redirect()->route('admin.settings.edit')->with('status', 'Pengaturan disimpan.');
    }
}
