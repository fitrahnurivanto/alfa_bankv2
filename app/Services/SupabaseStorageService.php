<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SupabaseStorageService
{
    protected $url;
    protected $key;
    protected $bucket;

    public function __construct()
    {
        $this->url = config('services.supabase.url');
        $this->key = config('services.supabase.key');
        $this->bucket = config('services.supabase.bucket', 'management_project');
        
        // Log configuration on initialization (for debugging)
        if (empty($this->url) || empty($this->key)) {
            Log::warning('Supabase configuration incomplete', [
                'url_set' => !empty($this->url),
                'key_set' => !empty($this->key),
                'bucket' => $this->bucket
            ]);
        }
    }

    /**
     * Upload file to Supabase Storage
     * 
     * @param \Illuminate\Http\UploadedFile|string $file UploadedFile object or file path
     * @param string $path Path dalam bucket (e.g., 'avatars/filename.jpg')
     * @return string|false Public URL atau false jika gagal
     */
    public function upload($file, $path)
    {
        try {
            // Validate configuration first
            if (empty($this->url) || empty($this->key)) {
                Log::error('Supabase configuration missing', [
                    'url' => $this->url ? 'SET' : 'MISSING',
                    'key' => $this->key ? 'SET' : 'MISSING',
                    'bucket' => $this->bucket
                ]);
                return false;
            }

            // Handle both UploadedFile object and file path string
            if (is_string($file)) {
                // File path string
                $fileContent = file_get_contents($file);
                $contentType = mime_content_type($file);
                $fileSize = filesize($file);
            } else {
                // UploadedFile object
                $fileContent = file_get_contents($file->getRealPath());
                $contentType = $file->getMimeType();
                $fileSize = $file->getSize();
            }

            $uploadUrl = "{$this->url}/storage/v1/object/{$this->bucket}/{$path}";
            
            // Log upload attempt
            Log::info('Attempting Supabase upload', [
                'path' => $path,
                'size' => $fileSize,
                'type' => $contentType,
                'url' => $uploadUrl,
                'bucket' => $this->bucket
            ]);

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->key,
                'Content-Type' => $contentType,
            ])->withBody($fileContent, $contentType)
            ->post($uploadUrl);

            if ($response->successful()) {
                $publicUrl = $this->getPublicUrl($path);
                Log::info('Supabase upload successful', [
                    'path' => $path,
                    'public_url' => $publicUrl
                ]);
                return $publicUrl;
            }

            // Log detailed error
            Log::error('Supabase upload failed', [
                'status' => $response->status(),
                'path' => $path,
                'bucket' => $this->bucket,
                'response_body' => $response->body(),
                'response_json' => $response->json(),
                'url' => $uploadUrl
            ]);
            return false;

        } catch (\Exception $e) {
            Log::error('Supabase upload exception', [
                'message' => $e->getMessage(),
                'file' => $e->getFile(),
                'line' => $e->getLine(),
                'path' => $path ?? 'N/A',
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Delete file from Supabase Storage
     * 
     * @param string $path Path dalam bucket
     * @return bool
     */
    public function delete($path)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->key,
            ])->delete("{$this->url}/storage/v1/object/{$this->bucket}/{$path}");

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Supabase delete error: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get public URL for a file
     * 
     * @param string $path
     * @return string
     */
    public function getPublicUrl($path)
    {
        return "{$this->url}/storage/v1/object/public/{$this->bucket}/{$path}";
    }

    /**
     * Check if file exists
     * 
     * @param string $path
     * @return bool
     */
    public function exists($path)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->key,
            ])->head("{$this->url}/storage/v1/object/{$this->bucket}/{$path}");

            return $response->successful();

        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Move/rename file
     * 
     * @param string $from
     * @param string $to
     * @return bool
     */
    public function move($from, $to)
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->key,
            ])->post("{$this->url}/storage/v1/object/{$this->bucket}/move", [
                'bucketId' => $this->bucket,
                'sourceKey' => $from,
                'destinationKey' => $to,
            ]);

            return $response->successful();

        } catch (\Exception $e) {
            Log::error('Supabase move error: ' . $e->getMessage());
            return false;
        }
    }
}
