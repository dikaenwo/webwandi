<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class MenuController extends Controller
{
    public function index()
    {
        $menus = Menu::with('category')->orderBy('sort_order')->get();
        // Return JSON for the alpine js front-end
        return response()->json($menus);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'category_id'  => 'required|exists:categories,id',
            'price'        => 'nullable|integer|min:0',
            'has_hot'      => 'nullable|boolean',
            'price_hot'    => 'nullable|integer|min:0',
            'desc_hot'     => 'nullable|string',
            'has_ice'      => 'nullable|boolean',
            'price_ice'    => 'nullable|integer|min:0',
            'desc_ice'     => 'nullable|string',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'tag'          => 'nullable|string|max:50',
            'is_available' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            $validated['image'] = $request->file('image')->store('menus', 'public');
        }

        $validated['is_available'] = $request->has('is_available') ? $request->boolean('is_available') : true;
        $validated['has_hot'] = $request->boolean('has_hot');
        $validated['has_ice'] = $request->boolean('has_ice');

        $menu = Menu::create($validated);

        return response()->json([
            'message' => 'Menu berhasil ditambahkan',
            'menu'    => $menu->load('category')
        ]);
    }

    public function update(Request $request, Menu $menu)
    {
        $validated = $request->validate([
            'name'         => 'required|string|max:255',
            'description'  => 'nullable|string',
            'category_id'  => 'required|exists:categories,id',
            'price'        => 'nullable|integer|min:0',
            'has_hot'      => 'nullable|boolean',
            'price_hot'    => 'nullable|integer|min:0',
            'desc_hot'     => 'nullable|string',
            'has_ice'      => 'nullable|boolean',
            'price_ice'    => 'nullable|integer|min:0',
            'desc_ice'     => 'nullable|string',
            'image'        => 'nullable|image|mimes:jpeg,png,jpg,webp|max:2048',
            'tag'          => 'nullable|string|max:50',
            'is_available' => 'nullable|boolean',
        ]);

        if ($request->hasFile('image')) {
            // Delete old image
            if ($menu->image && Storage::disk('public')->exists($menu->image)) {
                Storage::disk('public')->delete($menu->image);
            }
            $validated['image'] = $request->file('image')->store('menus', 'public');
        }

        if ($request->has('is_available')) {
            $validated['is_available'] = $request->boolean('is_available');
        }
        $validated['has_hot'] = $request->boolean('has_hot');
        $validated['has_ice'] = $request->boolean('has_ice');

        $menu->update($validated);

        return response()->json([
            'message' => 'Menu berhasil diperbarui',
            'menu'    => $menu->fresh('category')
        ]);
    }

    public function destroy(Menu $menu)
    {
        if ($menu->image && Storage::disk('public')->exists($menu->image)) {
            Storage::disk('public')->delete($menu->image);
        }
        
        $menu->delete();

        return response()->json([
            'message' => 'Menu berhasil dihapus'
        ]);
    }
}
