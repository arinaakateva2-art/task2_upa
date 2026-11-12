<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\File;
use App\Models\Folder;

class FileManagerController extends Controller
{
    public function index(Request $request)
    {
        $userId = auth()->id();
        $folderId = $request->query('folder');
        
        $folders = Folder::where('user_id', $userId)
            ->where('parent_id', $folderId)
            ->get();

        $query = File::where('user_id', $userId)
            ->where('folder_id', $folderId);

        if ($request->has('search')) {
            $query->where('name', 'like', '%' . $request->search . '%');
        }

        $files = $query->get();
        
        $currentFolder = $folderId ? Folder::where('user_id', $userId)->find($folderId) : null;

        return view('files.index', compact('folders', 'files', 'folderId', 'currentFolder'));
    }

    public function upload(Request $request)
    {
        $request->validate([
            'file' => 'required|file|max:5120|mimes:jpg,jpeg,png,pdf',
        ]);

        $uploadedFile = $request->file('file');
        $path = $uploadedFile->store('uploads/user_' . auth()->id(), 'public');

        File::create([
            'user_id' => auth()->id(),
            'folder_id' => $request->folder_id, 
            'name' => $uploadedFile->getClientOriginalName(), 
            'path' => $path,
            'mime_type' => $uploadedFile->getMimeType(),
            'size' => $uploadedFile->getSize(),
            'tags' => $request->tags,
        ]);

        return back()->with('success', 'Файл загружен!');
    }

    public function createFolder(Request $request)
    {
        $request->validate(['name' => 'required|string|max:255']);
        
        Folder::create([
            'user_id' => auth()->id(),
            'parent_id' => $request->parent_id,
            'name' => $request->name,
        ]);

        return back()->with('success', 'Папка создана!');
    }

    public function deleteFolder(Folder $folder)
    {
        abort_if($folder->user_id !== auth()->id(), 403);
        $folder->delete();
        return back()->with('success', 'Папка удалена!');
    }

    public function download(File $file)
    {
        abort_if($file->user_id !== auth()->id(), 403);
        return Storage::disk('public')->download($file->path, $file->name);
    }

    public function destroy(File $file)
    {
        abort_if($file->user_id !== auth()->id(), 403);
        Storage::disk('public')->delete($file->path); 
        $file->delete(); 
        return back()->with('success', 'Файл удален!');
    }

    public function rename(Request $request, File $file)
    {
        abort_if($file->user_id !== auth()->id(), 403);
        $request->validate(['name' => 'required|string|max:255']);
        $file->update(['name' => $request->name]);
        return back()->with('success', 'Файл переименован!');
    }

    public function move(Request $request, File $file)
    {
        abort_if($file->user_id !== auth()->id(), 403);
        $file->update(['folder_id' => $request->new_folder_id]);
        return back()->with('success', 'Файл перемещен!');
    }
}