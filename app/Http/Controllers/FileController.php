<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\File;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileController extends Controller
{
    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:102400', // Max size 100MB
        ]);
    
        $uploadedFile = $request->file('file');
        $filePath = $uploadedFile->store('uploads', 'public');
    
        // Create a unique share link
        $shareLink = \Illuminate\Support\Str::random(10);
    
        // Save the file metadata to the database
        $file = File::create([
            'file_name' => $uploadedFile->getClientOriginalName(),
            'file_path' => $filePath,
            'file_size' => $uploadedFile->getSize(),
            'share_link' => $shareLink, // Save the share link here
        ]);
    
        return redirect()->back()->with('success', 'File uploaded successfully!');
    }

    public function index()
    {
        // Retrieve all files from the database
        $files = File::latest()->get();

        return inertia('Files/Index', [
            'files' => $files,
        ]);
    }

    public function download($id)
{
    $file = File::findOrFail($id); // Retrieve the file metadata from the database

    // Ensure the file exists on the storage
    if (Storage::disk('public')->exists($file->file_path)) {
        return Storage::disk('public')->download($file->file_path, $file->file_name);
    }

    return redirect()->back()->with('error', 'File not found.');
}

public function share($link)
{
    $file = File::where('share_link', $link)->firstOrFail();

    if (Storage::disk('public')->exists($file->file_path)) {
        return response()->download(storage_path('app/public/' . $file->file_path), $file->file_name);
    }

    return redirect()->back()->with('error', 'File not found.');
}
}
