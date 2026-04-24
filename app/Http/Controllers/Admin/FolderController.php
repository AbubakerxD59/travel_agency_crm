<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Folder;
use Illuminate\Http\Request;
use Illuminate\View\View;

class FolderController extends Controller
{
    public function index(Request $request): View
    {
        $folders = Folder::query()
            ->with(['agent', 'company', 'destination'])
            ->latest()
            ->paginate(15)
            ->withQueryString();

        return view('admin.folders.index', [
            'folders' => $folders,
        ]);
    }

    public function show(Folder $folder): View
    {
        $folder->load(['agent', 'company', 'destination', 'itineraries', 'passengers', 'packageCosts', 'hotelDetails']);

        return view('admin.folders.show', [
            'folder' => $folder,
        ]);
    }
}
