<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use App\Models\SnelstartExport;

class ExportController extends Controller
{
    public function index()
    {
        $exports = SnelstartExport::orderBy('export_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);
        
        return view('export.index', compact('exports'));
    }

    public function download(SnelstartExport $export)
    {
        $filePath = 'daily_snelstart_exports/' . $export->filename;
        
        if (Storage::exists($filePath)) {
            return Storage::download($filePath, $export->filename);
        }
        
        return redirect()->route('export.index')->with('error', 'File not found.');
    }
}
