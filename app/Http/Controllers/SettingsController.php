<?php

namespace App\Http\Controllers;

use App\Models\Setting;
// use App\Models\EmailTemplate; // DISABLED - Email not configured
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SupabaseStorageService;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class SettingsController extends Controller
{
    /**
     * Show settings page
     */
    public function index()
    {
        $user = $this->currentUser();

        // Marketing hanya boleh kelola Nama Pelatihan, bukan company info
        if ($user->role === 'marketing') {
            return redirect()->route('admin.trainings.index');
        }

        if (!$user->isAdmin()) {
            abort(403);
        }

        $settings = [
            'company_logo' => Setting::get('company_logo'),
        ];

        // Email templates disabled - email not configured
        $emailTemplates = collect([]);

        return view('admin.settings.index', compact('settings', 'emailTemplates'));
    }

    /**
     * Update settings
     */
    public function update(Request $request)
    {
        $user = $this->currentUser();

        if (!$user->isAdmin()) {
            abort(403);
        }

        Log::info('Settings update request received', [
            'user' => $user->name,
            'has_file' => $request->hasFile('company_logo'),
            'all_input' => $request->except(['_token', '_method'])
        ]);

        $validated = $request->validate([
            'company_logo' => 'nullable|image|mimes:png,jpg,jpeg|max:2048',
        ]);

        // No text settings to update (removed company info)

        // Handle company logo upload
        if ($request->hasFile('company_logo')) {
            // Check Supabase configuration first
            if (empty(config('services.supabase.url')) || empty(config('services.supabase.key'))) {
                Log::error('Supabase not configured', [
                    'user' => $user->name,
                    'url_set' => !empty(config('services.supabase.url')),
                    'key_set' => !empty(config('services.supabase.key')),
                    'bucket' => config('services.supabase.bucket')
                ]);
                
                return back()->with('error', 'Konfigurasi Supabase belum lengkap. Silakan set SUPABASE_URL, SUPABASE_KEY, dan SUPABASE_BUCKET di file .env');
            }
            
            $supabase = app(SupabaseStorageService::class);
            
            Log::info('Company logo upload attempt', [
                'user' => $user->name,
                'file_name' => $request->file('company_logo')->getClientOriginalName(),
                'file_size' => $request->file('company_logo')->getSize()
            ]);
            
            // Process image before upload (resize & optimize)
            $file = $request->file('company_logo');
            
            // Create ImageManager with GD driver
            $manager = new ImageManager(new Driver());
            $image = $manager->read($file);
            
            // Resize logo to max width 400px and max height 120px (maintain aspect ratio)
            $image->scale(width: 400, height: 120);
            
            // Encode to PNG with optimization
            $encodedImage = $image->toPng();
            
            // Create temporary file
            $tempPath = sys_get_temp_dir() . '/' . time() . '_company_logo.png';
            file_put_contents($tempPath, $encodedImage);
            
            // Delete old logo if exists
            $oldLogo = Setting::get('company_logo');
            if ($oldLogo && strpos($oldLogo, 'supabase.co') !== false) {
                $path = parse_url($oldLogo, PHP_URL_PATH);
                $path = str_replace('/storage/v1/object/public/' . config('services.supabase.bucket') . '/', '', $path);
                $supabase->delete($path);
            }
            
            // Upload processed image to Supabase
            $filename = time() . '_company_logo.png';
            $logoUrl = $supabase->upload(new \Illuminate\Http\UploadedFile($tempPath, $filename, 'image/png', null, true), 'divisions/' . $filename);
            
            // Delete temporary file
            @unlink($tempPath);
            
            if ($logoUrl) {
                Setting::set('company_logo', $logoUrl);
                Log::info('Company logo uploaded successfully', ['url' => $logoUrl]);
            } else {
                Log::error('Company logo upload failed', [
                    'user' => $user->name,
                    'filename' => $filename,
                    'supabase_url' => config('services.supabase.url'),
                    'supabase_bucket' => config('services.supabase.bucket')
                ]);
                return back()->with('error', 'Gagal upload logo. Cek storage/logs/laravel.log untuk detail error. Pastikan SUPABASE_URL, SUPABASE_KEY, dan SUPABASE_BUCKET sudah benar di .env');
            }
        }

        return back()->with('success', 'Pengaturan berhasil diperbarui!');
    }

    private function currentUser(): \App\Models\User
    {
        $user = \Illuminate\Support\Facades\Auth::user();
        if (!$user instanceof \App\Models\User) {
            abort(403);
        }

        return $user;
    }
    
    /* EMAIL TEMPLATE METHODS DISABLED - Email not configured
    public function updateEmailTemplate(Request $request, EmailTemplate $emailTemplate)
    {
        if (!\Illuminate\Support\Facades\Auth::user()->isAdmin()) {
            abort(403);
        }

        $validated = $request->validate([
            'subject' => 'required|string|max:255',
            'message' => 'required|string',
            'show_login_info' => 'nullable|boolean',
            'is_active' => 'nullable|boolean',
        ]);

        $emailTemplate->update([
            'subject' => $validated['subject'],
            'message' => $validated['message'],
            'show_login_info' => $request->has('show_login_info'),
            'is_active' => $request->has('is_active'),
        ]);

        \Illuminate\Support\Facades\Log::info('Email template updated', [
            'template' => $emailTemplate->name,
            'by' => \Illuminate\Support\Facades\Auth::user()->name,
        ]);

        return redirect()->route('admin.settings.index')
            ->with('success', 'Template email "' . $emailTemplate->label . '" berhasil diupdate!');
    }
    */
}