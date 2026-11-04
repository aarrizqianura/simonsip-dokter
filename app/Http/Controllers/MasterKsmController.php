<?php

namespace App\Http\Controllers;

use App\Models\Ksm;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class MasterKsmController extends Controller
{
    public function index(): View
    {
        $ksmList = Ksm::withCount('dokters')
            ->with(['dokters.activeSipRecords'])
            ->orderBy('nama_ksm')
            ->get();

        return view('master.ksm', compact('ksmList'));
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'nama_ksm' => ['required', 'string', 'max:150', 'unique:ksm,nama_ksm'],
        ], [
            'nama_ksm.unique' => 'Nama KSM tersebut sudah terdaftar.',
        ]);

        Ksm::create($validated);

        return back()->with('success', 'Master Data KSM "' . $validated['nama_ksm'] . '" berhasil ditambahkan.');
    }

    public function update(Request $request, Ksm $ksm): RedirectResponse
    {
        $validated = $request->validate([
            'nama_ksm' => ['required', 'string', 'max:150', Rule::unique('ksm', 'nama_ksm')->ignore($ksm->id)],
        ]);

        $ksm->update($validated);

        return back()->with('success', 'Master Data KSM berhasil diperbarui.');
    }

    public function destroy(Ksm $ksm): RedirectResponse
    {
        if ($ksm->dokters()->exists()) {
            return back()->with('error', 'KSM tidak dapat dihapus karena masih memiliki data dokter terdaftar.');
        }

        $ksm->delete();

        return back()->with('success', 'Master Data KSM berhasil dihapus.');
    }
}

