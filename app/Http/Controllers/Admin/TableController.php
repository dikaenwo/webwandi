<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Table;
use Illuminate\Http\Request;

class TableController extends Controller
{
    /**
     * GET /admin/api/tables
     * Return all tables ordered by number
     */
    public function index()
    {
        $tables = Table::orderBy('number')->get();
        return response()->json($tables);
    }

    /**
     * POST /admin/api/tables
     * Create a new table
     */
    public function store(Request $request)
    {
        $request->validate([
            'number'   => 'required|integer|min:1|unique:tables,number',
            'name'     => 'nullable|string|max:100',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $table = Table::create([
            'number'   => $request->number,
            'name'     => $request->name ?: 'Meja ' . $request->number,
            'capacity' => $request->capacity ?? 4,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Meja berhasil ditambahkan.',
            'table'   => $table,
        ], 201);
    }

    /**
     * PUT /admin/api/tables/{table}
     * Update an existing table
     */
    public function update(Request $request, Table $table)
    {
        $request->validate([
            'number'   => 'required|integer|min:1|unique:tables,number,' . $table->id,
            'name'     => 'nullable|string|max:100',
            'capacity' => 'nullable|integer|min:1',
        ]);

        $table->update([
            'number'   => $request->number,
            'name'     => $request->name ?: 'Meja ' . $request->number,
            'capacity' => $request->capacity ?? $table->capacity,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Meja berhasil diperbarui.',
            'table'   => $table->fresh(),
        ]);
    }

    /**
     * DELETE /admin/api/tables/{table}
     * Delete a table
     */
    public function destroy(Table $table)
    {
        $table->delete();

        return response()->json([
            'success' => true,
            'message' => 'Meja berhasil dihapus.',
        ]);
    }
}
