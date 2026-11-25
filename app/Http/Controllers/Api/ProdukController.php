<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Produk;
use Illuminate\Http\Request;

class ProdukController extends Controller
{
    public function index()
    {
        $produks = Produk::latest()->get();
        return response()->json([
            'success' => true,
            'message' => 'Data produk berhasil dimuat',
            'data'    => $produks
        ], 200);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'kode_produk' => 'required|string|max:20|unique:produks,kode_produk',
            'nama_produk' => 'required|string|max:100',
            'kategori'    => 'required|string|max:50',
            'stok'        => 'required|integer|min:0',
            'harga'       => 'required|integer|min:100',
        ]);

        $produk = Produk::create($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data produk berhasil ditambahkan!',
            'data'    => $produk
        ], 201);
    }

    public function show($id)
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Data produk tidak ditemukan'
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data'    => $produk
        ], 200);
    }

    public function update(Request $request, $id)
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Data produk tidak ditemukan'
            ], 404);
        }

        $validated = $request->validate([
            'kode_produk' => 'required|string|max:20|unique:produks,kode_produk,' . $id,
            'nama_produk' => 'required|string|max:100',
            'kategori'    => 'required|string|max:50',
            'stok'        => 'required|integer|min:0',
            'harga'       => 'required|integer|min:100',
        ]);

        $produk->update($validated);

        return response()->json([
            'success' => true,
            'message' => 'Data produk berhasil diperbarui!',
            'data'    => $produk
        ], 200);
    }

    public function destroy($id)
    {
        $produk = Produk::find($id);
        if (!$produk) {
            return response()->json([
                'success' => false,
                'message' => 'Data produk tidak ditemukan'
            ], 404);
        }

        $produk->delete();

        return response()->json([
            'success' => true,
            'message' => 'Data produk berhasil dihapus!'
        ], 200);
    }
}