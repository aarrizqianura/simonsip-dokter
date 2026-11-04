<?php

namespace App\Http\Controllers;

use App\Models\Spesialisasi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterSpesialisasiController extends Controller
{
    public function index(): View
    {
        $spesialisasiList = Spesialisasi::withCount('dokters')
            ->orderBy('nama_spesialisasi')
            ->get();

        return view('master.spesialisasi', compact('spesialisasiList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_spesialisasi' => ['required', 'string', 'max:150', 'unique:spesialisasi,nama_spesialisasi'],
        ], [
            'nama_spesialisasi.unique' => 'Nama Spesialisasi tersebut sudah terdaftar.',
        ]);

        Spesialisasi::create($validated);

        return back()->with('success', 'Master Spesialisasi "' . $validated['nama_spesialisasi'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, Spesialisasi $spesialisasi): RedirectResponse
    {
        $validated = $request->validate([
            'nama_spesialisasi' => ['required', 'string', 'max:150', Rule::unique('spesialisasi', 'nama_spesialisasi')->ignore($spesialisasi->id)],
        ]);

        $spesialisasi->update($validated);

        return back()->with('success', 'Master Spesialisasi berhasil diperbarui.');
    }

    public function destroy(Spesialisasi $spesialisasi): RedirectResponse
    {
        if ($spesialisasi->dokters()->exists()) {
            return back()->with('error', 'Spesialisasi tidak dapat dihapus karena masih memiliki data dokter terdaftar.');
        }

        $spesialisasi->delete();

        return back()->with('success', 'Master Spesialisasi berhasil dihapus.');
    }
}

