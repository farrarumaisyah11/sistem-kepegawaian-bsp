<?php

namespace App\Http\Controllers;

use App\Models\Pegawai;
use App\Models\Jabatan;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use App\Imports\PegawaiImport;

class AdminController extends Controller
{
    public function index()
    {
        return app(DashboardController::class)->index();
    }

    public function stats()
    {
        return app(DashboardController::class)->stats();
    }

    public function show($id)
    {
        $pegawai = Pegawai::findOrFail($id);

        return view('admin.pegawai.show', compact('pegawai'));
    }

    public function edit($id)
    {
        $pegawai = Pegawai::findOrFail($id);

        return view('admin.pegawai.edit', compact('pegawai'));
    }

    public function update(Request $request, $id)
    {
        $pegawai = Pegawai::findOrFail($id);
        $pegawai->update($request->all());

        return redirect()->route('admin.pegawai.index');
    }

    public function destroy($id)
    {
        Pegawai::findOrFail($id)->delete();

        return redirect()->route('admin.pegawai.index');
    }

    public function import(Request $request)
    {
        Excel::import(new PegawaiImport, $request->file('file'));

        return back()->with('success', 'Data Pegawai berhasil diimpor.');
    }

    public function jabatanIndex()
    {
        $jabatan = Jabatan::all();

        return view('admin.jabatan.index', compact('jabatan'));
    }

    public function jabatanShow($id)
    {
        $jabatan = Jabatan::findOrFail($id);

        return view('admin.jabatan.show', compact('jabatan'));
    }

    public function jabatanCreate()
    {
        return view('admin.jabatan.create');
    }

    public function jabatanStore(Request $request)
    {
        Jabatan::create($request->all());

        return redirect()->route('admin.jabatan.index');
    }

    public function jabatanEdit($id)
    {
        $jabatan = Jabatan::findOrFail($id);

        return view('admin.jabatan.edit', compact('jabatan'));
    }

    public function jabatanUpdate(Request $request, $id)
    {
        $jabatan = Jabatan::findOrFail($id);
        $jabatan->update($request->all());

        return redirect()->route('admin.jabatan.index');
    }

    public function jabatanDestroy($id)
    {
        Jabatan::findOrFail($id)->delete();

        return redirect()->route('admin.jabatan.index');
    }
}