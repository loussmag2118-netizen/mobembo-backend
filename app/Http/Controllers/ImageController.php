<?php

namespace App\Http\Controllers;

use Illuminate\Filesystem\FilesystemAdapter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'image' => 'required|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $path = $request->file('image')->store('images', 'gcs');

        /** @var FilesystemAdapter $disk */
        $disk = Storage::disk('gcs');

        return response()->json([
            'path' => $path,
            'url'  => $disk->url($path),
        ]);
    }
}