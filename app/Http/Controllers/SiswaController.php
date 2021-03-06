<?php

namespace App\Http\Controllers;


use App\Http\Requests\SiswaRequest;
use Illuminate\Http\Request;
use Storage, Session;
use App\Siswa, App\Telepon;


class SiswaController extends Controller
{

    public function cari(Request $request)
    {
        $kata_kunci = trim($request->input('kata_kunci'));
        if (!empty($kata_kunci)) {
            $jenis_kelamin  = $request->input('jenis_kelamin');
            $id_kelas       = $request->input('id_kelas');
            //Query
            $query      = Siswa::where('nama_siswa', 'LIKE', '%' . $kata_kunci . '%')->orderBY('nama_siswa', 'asc');
            (!empty($jenis_kelamin)) ? $query->JenisKelamin('jenis_kelamin', $jenis_kelamin) : '';
            (!empty($id_kelas)) ? $query->Kelas('id_kelas', $id_kelas) : '';
            $siswa_list = $query->paginate(5);

            //Link Pagination
            $pagination = (!empty($jenis_kelamin)) ? $siswa_list->appends(['jenis_kelamin' => $jenis_kelamin]) : '';
            $pagination = (!empty($id_kelas)) ? $pagination = $siswa_list->appends(['id_kelas' => $id_kelas]) : '';
            $pagination = $siswa_list->appends(['kata_kunci' => $kata_kunci]);

            $jumlah_siswa = $siswa_list->total();
            return view('pages.siswa.index', compact('siswa_list', 'kata_kunci', 'pagination', 'jumlah_siswa', 'id_kelas', 'jenis_kelamin'));
        }

        return redirect('siswa');
    }

    public function index()
    {
        $siswa_list = Siswa::orderBy('nama_siswa', 'asc')->paginate(5);

        $jumlah_siswa = Siswa::count();


        return view('pages.siswa.index', compact('siswa_list', 'jumlah_siswa'));
    }

    public function create()
    {

        return view('pages.siswa.create');
    }

    public function show(Siswa $siswa)
    {
        return view('pages.siswa.show', compact('siswa'));
    }

    public function store(SiswaRequest $request)
    {
        $input = $request->all();

        if ($request->hasFile('foto')) {
            if ($request->hasFile('foto')) {
                $input['foto'] = $this->uploadFoto($request);
            }
        }
        //simpan data siswa
        $siswa = Siswa::create($input);
        //simpan data telepon
        $telepon = new Telepon;
        $telepon->nomor_telepon = $request->input('nomor_telepon');
        $siswa->telepon()->save($telepon);
        //simpan data hobi
        $siswa->hobi()->attach($request->input('hobi_siswa'));
        Session::flash('flsh_massage', 'Data siswa berhasil disimpan.');

        return redirect('siswa');
    }

    public function edit(Siswa $siswa)
    {
        $siswa->nomor_telepon = $siswa->telepon->nomor_telepon;
        return view('pages.siswa.edit', compact('siswa'));
    }

    public function update(Siswa $siswa, SiswaRequest $request)
    {
        $input = $request->all();

        if ($request->hasFile('foto')) {
            $this->hapusFoto($siswa);
            $input['foto'] = $this->uploadFoto($request);
        }
        //update data siswa
        $siswa->update($input);
        //Update data telepon
        $telepon = $siswa->telepon;
        $telepon->nomor_telepon = $request->input('nomor_telepon');
        $siswa->telepon()->save($telepon);
        //Update data hobi
        $siswa->hobi()->sync($request->input('hobi_siswa'));
        Session::flash('flsh_massage', 'Data siswa berhasil diupdate.');

        return redirect('siswa');
    }

    public function uploadFoto(SiswaRequest $request)
    {
        $foto = $request->file('foto');
        $ext = $foto->getClientOriginalExtension();

        if ($request->file('foto')->isValid()) {
            $foto_name = date('YmdHis') . ".$ext";
            $upload_path = 'fotoupload';
            $request->file('foto')->move($upload_path, $foto_name);
            return $foto_name;
        }
        return false;
    }

    public function hapusFoto(Siswa $siswa)
    {
        $exists = Storage::disk('foto')->exists($siswa->foto);

        if (isset($siswa->foto) && $exists) {
            $delete = Storage::disk('foto')->delete($siswa->foto);
            if ($delete) {
                return true;
            }
            return false;
        }
    }

    public function destroy(Siswa $siswa)
    {
        $this->hapusFoto($siswa);
        $siswa->delete();
        Session::flash('flsh_massage', 'Data siswa berhasil disimpan.');
        Session::flash('penting', true);
        return redirect('siswa');
    }

    public function __construct()
    {
        $this->middleware('auth', ['except' => ['index', 'show', 'cari']]);
    }
}
