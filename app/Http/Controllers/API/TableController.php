<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class TableController extends Controller
{
    public function store(Request $request)
    {
        $request->validate(['name' => 'required|string|unique:tables,name']);

        $table = Table::create([
            'name' => $request->name,
            'qr_code' => url('/api/misc/scan/qr/' . Str::uuid()) 
        ]);

        return response()->json([
            'success' => true,
            'data' => $table
        ]);
    }

    // Admin: view all tables
    public function index()
    {
        $tables = Table::all();
        return response()->json(['success' => true, 'data' => $tables]);
    }

    // Admin: edit 
    public function update(Request $request, $id)
    {
        $table = Table::find($id);
        if (!$table) return response()->json(['message' => 'Table not found'], 404);

        $request->validate(['name' => 'required|string|unique:tables,name,' . $id]);
        $table->name = $request->name;
        $table->save();

        return response()->json(['success' => true, 'data' => $table]);
    }

    // Admin: delete
    public function delete($id)
    {
        $table = Table::find($id);
        if (!$table) return response()->json(['message' => 'Table not found'], 404);

        $table->delete();
        return response()->json(['success' => true, 'message' => 'Table deleted']);
    }

    // Scan QR
    public function scanQr($uuid)
    {
        $table = Table::where('qr_code', url('/api/misc/scan/qr/' . $uuid))->first();
        if (!$table) return response()->json(['message' => 'Invalid QR code'], 404);

        return response()->json(['success' => true, 'table' => $table]);
    }
}
