<?php

namespace App\Http\Controllers;

use App\Models\Kelas;
use App\Models\OrangTuaWali;
use App\Models\PerkembanganSiswa;
use App\Models\Siswa;
use App\Models\SiswaDokumen;
use App\Models\TahunPelajaran;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class BukuIndukController extends Controller
{
    private const SISWA_FIELDS = [
        'no_urut', 'nis', 'nisn', 'nism', 'nik', 'no_kk',
        'nama_lengkap', 'nama_panggilan', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'agama', 'kewarganegaraan', 'anak_ke', 'saudara_kandung', 'saudara_tiri', 'saudara_angkat',
        'status_anak', 'yatim_piatu', 'bahasa_sehari_hari',
        'alamat', 'rt', 'rw', 'desa_kelurahan', 'kecamatan', 'kabupaten_kota', 'provinsi', 'kode_pos',
        'nama_orang_tua',
        'no_telepon', 'hp', 'bertempat_tinggal_pada', 'jarak_ke_madrasah', 'moda_transportasi',
        'golongan_darah', 'penyakit_pernah_diderita', 'kelainan_jasmani', 'tinggi_badan_awal', 'berat_badan_awal',
        'hobi_kesenian', 'hobi_olahraga', 'hobi_organisasi', 'hobi_lain',
        'kelas_id', 'tahun_pelajaran_id', 'status',
    ];

    private const ORTU_FIELDS = [
        'nama_ayah', 'pendidikan_ayah', 'pekerjaan_ayah', 'penghasilan_ayah', 'no_hp_ayah', 'status_ayah',
        'nama_ibu', 'pendidikan_ibu', 'pekerjaan_ibu', 'penghasilan_ibu', 'no_hp_ibu', 'status_ibu',
        'nama_wali', 'hubungan_wali', 'pendidikan_wali', 'pekerjaan_wali', 'no_hp_wali', 'alamat_ortu',
    ];

    private const PERKEMBANGAN_FIELDS = [
        'asal_madrasah', 'nama_madrasah_asal', 'tgl_ijazah_asal', 'no_ijazah_asal', 'jenis_masuk',
        'tgl_diterima', 'dari_tingkat', 'no_surat_pindah', 'jenis_keluar', 'thn_lulus', 'no_ijazah_lulus',
        'melanjutkan_ke', 'pindah_ke_madrasah', 'pindah_tingkat', 'alasan_keluar', 'tgl_keluar',
    ];

    public function index(Request $request): Response
    {
        $sorts = [
            'no_urut' => 'no_urut',
            'nis' => 'nis',
            'nama_lengkap' => 'nama_lengkap',
            'kelas' => 'kelas_id',
            'status' => 'status',
        ];
        $sort = $sorts[$request->input('sort')] ?? 'created_at';
        $direction = $request->input('direction') === 'asc' ? 'asc' : 'desc';

        $siswa = Siswa::with(['kelas', 'tahunPelajaran'])
            ->when($request->search, function ($query, string $search) {
                $query->where(function ($query) use ($search) {
                    $query->where('nama_lengkap', 'like', "%{$search}%")
                        ->orWhere('nis', 'like', "%{$search}%")
                        ->orWhere('nisn', 'like', "%{$search}%")
                        ->orWhere('nik', 'like', "%{$search}%");
                });
            })
            ->when($request->status, fn ($query, string $status) => $query->where('status', $status))
            ->when($request->kelas_id, fn ($query, string $kelasId) => $query->where('kelas_id', $kelasId))
            ->when($request->tahun_pelajaran_id, fn ($query, string $tahunPelajaranId) => $query->where('tahun_pelajaran_id', $tahunPelajaranId))
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString()
            ->through(fn (Siswa $siswa) => [
                'id' => $siswa->id,
                'no_urut' => $siswa->no_urut,
                'nis' => $siswa->nis,
                'nisn' => $siswa->nisn,
                'nik' => $siswa->nik,
                'nama_lengkap' => $siswa->nama_lengkap,
                'status' => $siswa->status,
                'kelas' => $siswa->kelas ? [
                    'id' => $siswa->kelas->id,
                    'nama_kelas' => $siswa->kelas->nama_kelas,
                ] : null,
                'tahun_pelajaran' => $siswa->tahunPelajaran ? [
                    'id' => $siswa->tahunPelajaran->id,
                    'kode' => $siswa->tahunPelajaran->kode,
                ] : null,
            ]);

        return Inertia::render('BukuInduk/Index', [
            'siswa' => $siswa,
            'kelas' => $this->kelasOptions(),
            'tahunPelajaran' => $this->tahunPelajaranOptions(),
            'filters' => [
                'search' => $request->search,
                'status' => $request->status,
                'kelas_id' => $request->kelas_id,
                'tahun_pelajaran_id' => $request->tahun_pelajaran_id,
                'sort' => $request->sort,
                'direction' => $request->direction,
            ],
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('BukuInduk/Form', [
            'kelas' => $this->kelasOptions(),
            'tahunPelajaran' => $this->tahunPelajaranOptions(),
        ]);
    }

    public function store(Request $request)
    {
        $request->validate($this->rules());

        DB::beginTransaction();
        try {
            $siswaData = $request->only(self::SISWA_FIELDS);
            if ($request->hasFile('foto')) {
                $siswaData['foto'] = $this->storeFoto($request->file('foto'), $request->input('nis'));
            }
            $siswa = Siswa::create($siswaData);

            $ortuData = $request->only(self::ORTU_FIELDS);
            $ortuData['siswa_id'] = $siswa->id;
            OrangTuaWali::create($ortuData);

            $perkembanganData = $request->only(self::PERKEMBANGAN_FIELDS);
            $perkembanganData['siswa_id'] = $siswa->id;
            PerkembanganSiswa::create($perkembanganData);

            $this->storeDokumen($request, $siswa);

            DB::commit();
            return redirect()->route('buku-induk.index')->with('success', 'Data Buku Induk Siswa berhasil ditambahkan.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function edit(Siswa $siswa): Response
    {
        $siswa->load(['kelas', 'tahunPelajaran', 'orangTuaWali', 'perkembangan', 'dokumen']);

        return Inertia::render('BukuInduk/Form', [
            'siswa' => $this->detailPayload($siswa),
            'kelas' => $this->kelasOptions(),
            'tahunPelajaran' => $this->tahunPelajaranOptions(),
        ]);
    }

    public function update(Request $request, Siswa $siswa)
    {
        $request->validate($this->rules($siswa));

        DB::beginTransaction();
        try {
            $siswaData = $request->only(self::SISWA_FIELDS);
            if ($request->hasFile('foto')) {
                $siswaData['foto'] = $this->storeFoto($request->file('foto'), $request->input('nis'), $siswa->foto);
            }
            $siswa->update($siswaData);

            $siswa->orangTuaWali()->updateOrCreate(['siswa_id' => $siswa->id], $request->only(self::ORTU_FIELDS));

            $perkembanganData = $request->only(self::PERKEMBANGAN_FIELDS);
            $siswa->perkembangan()->updateOrCreate(['siswa_id' => $siswa->id], $perkembanganData);

            $this->storeDokumen($request, $siswa);

            DB::commit();
            return redirect()->route('buku-induk.index')->with('success', 'Data Buku Induk Siswa berhasil diperbarui.');
        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withInput()->with('error', 'Terjadi kesalahan: ' . $e->getMessage());
        }
    }

    public function show(Siswa $siswa): Response
    {
        $siswa->load(['kelas', 'tahunPelajaran', 'orangTuaWali', 'perkembangan', 'dokumen']);

        return Inertia::render('BukuInduk/Show', [
            'siswa' => $this->detailPayload($siswa),
        ]);
    }

    public function destroy(Siswa $siswa)
    {
        $siswa->delete();
        return redirect()->route('buku-induk.index')->with('success', 'Data siswa berhasil dihapus.');
    }

    public function exportPdf(Siswa $siswa)
    {
        $siswa->load(['kelas', 'tahunPelajaran', 'orangTuaWali', 'perkembangan']);
        $pdf = Pdf::loadView('buku-induk.pdf', compact('siswa'))->setPaper('a4', 'portrait');
        return $pdf->stream('Buku_Induk_'.$siswa->nis.'.pdf');
    }

    private function rules(?Siswa $siswa = null): array
    {
        return [
            'no_urut' => ['nullable', 'integer', 'min:1'],
            'nama_lengkap' => ['required', 'string', 'max:100'],
            'nama_panggilan' => ['nullable', 'string', 'max:50'],
            'nis' => ['required', 'string', 'max:50', Rule::unique('siswa', 'nis')->ignore($siswa?->id)],
            'nisn' => ['nullable', 'string', 'max:10', Rule::unique('siswa', 'nisn')->ignore($siswa?->id)],
            'nism' => ['nullable', 'string', 'max:30'],
            'nik' => ['nullable', 'digits:16', Rule::unique('siswa', 'nik')->ignore($siswa?->id)],
            'no_kk' => ['nullable', 'string', 'max:16'],
            'kelas_id' => ['required', 'exists:kelas,id'],
            'tahun_pelajaran_id' => ['nullable', 'exists:tahun_pelajaran,id'],
            'status' => ['required', 'in:Aktif,Lulus,Pindah,Keluar,Meninggal'],
            'jenis_kelamin' => ['nullable', 'in:L,P'],
            'tempat_lahir' => ['nullable', 'string', 'max:100'],
            'tanggal_lahir' => ['nullable', 'date'],
            'agama' => ['nullable', 'string', 'max:20'],
            'kewarganegaraan' => ['nullable', 'string', 'max:30'],
            'anak_ke' => ['nullable', 'integer', 'min:1'],
            'saudara_kandung' => ['nullable', 'integer', 'min:0'],
            'saudara_tiri' => ['nullable', 'integer', 'min:0'],
            'saudara_angkat' => ['nullable', 'integer', 'min:0'],
            'status_anak' => ['nullable', 'in:Kandung,Tiri,Angkat'],
            'yatim_piatu' => ['nullable', 'in:Tidak,Yatim,Piatu,Yatim Piatu'],
            'bahasa_sehari_hari' => ['nullable', 'string', 'max:50'],
            'alamat' => ['nullable', 'string'],
            'rt' => ['nullable', 'string', 'max:5'],
            'rw' => ['nullable', 'string', 'max:5'],
            'desa_kelurahan' => ['nullable', 'string', 'max:60'],
            'kecamatan' => ['nullable', 'string', 'max:60'],
            'kabupaten_kota' => ['nullable', 'string', 'max:60'],
            'provinsi' => ['nullable', 'string', 'max:50'],
            'kode_pos' => ['nullable', 'string', 'max:10'],
            'nama_orang_tua' => ['nullable', 'string', 'max:255'],
            'no_telepon' => ['nullable', 'string', 'max:20'],
            'hp' => ['nullable', 'string', 'max:20'],
            'bertempat_tinggal_pada' => ['nullable', 'string', 'max:60'],
            'jarak_ke_madrasah' => ['nullable', 'string', 'max:10'],
            'moda_transportasi' => ['nullable', 'string', 'max:50'],
            'golongan_darah' => ['nullable', 'in:A,B,AB,O,Tidak Tahu'],
            'penyakit_pernah_diderita' => ['nullable', 'string'],
            'kelainan_jasmani' => ['nullable', 'string', 'max:100'],
            'tinggi_badan_awal' => ['nullable', 'numeric', 'min:0'],
            'berat_badan_awal' => ['nullable', 'numeric', 'min:0'],
            'hobi_kesenian' => ['nullable', 'string', 'max:100'],
            'hobi_olahraga' => ['nullable', 'string', 'max:100'],
            'hobi_organisasi' => ['nullable', 'string', 'max:100'],
            'hobi_lain' => ['nullable', 'string', 'max:100'],
            'nama_ayah' => ['nullable', 'string', 'max:100'],
            'pendidikan_ayah' => ['nullable', 'string', 'max:30'],
            'pekerjaan_ayah' => ['nullable', 'string', 'max:60'],
            'penghasilan_ayah' => ['nullable', 'string', 'max:30'],
            'no_hp_ayah' => ['nullable', 'string', 'max:20'],
            'status_ayah' => ['nullable', 'in:Hidup,Meninggal,Tidak Diketahui'],
            'nama_ibu' => ['nullable', 'string', 'max:100'],
            'pendidikan_ibu' => ['nullable', 'string', 'max:30'],
            'pekerjaan_ibu' => ['nullable', 'string', 'max:60'],
            'penghasilan_ibu' => ['nullable', 'string', 'max:30'],
            'no_hp_ibu' => ['nullable', 'string', 'max:20'],
            'status_ibu' => ['nullable', 'in:Hidup,Meninggal,Tidak Diketahui'],
            'nama_wali' => ['nullable', 'string', 'max:100'],
            'hubungan_wali' => ['nullable', 'string', 'max:40'],
            'pendidikan_wali' => ['nullable', 'string', 'max:30'],
            'pekerjaan_wali' => ['nullable', 'string', 'max:60'],
            'no_hp_wali' => ['nullable', 'string', 'max:20'],
            'alamat_ortu' => ['nullable', 'string', 'max:200'],
            'asal_madrasah' => ['nullable', 'string', 'max:100'],
            'nama_madrasah_asal' => ['nullable', 'string', 'max:100'],
            'tgl_ijazah_asal' => ['nullable', 'date'],
            'no_ijazah_asal' => ['nullable', 'string', 'max:50'],
            'jenis_masuk' => ['nullable', 'in:Baru,Pindahan'],
            'tgl_diterima' => ['nullable', 'date'],
            'dari_tingkat' => ['nullable', 'string', 'max:20'],
            'no_surat_pindah' => ['nullable', 'string', 'max:50'],
            'jenis_keluar' => ['nullable', 'string', 'max:40'],
            'thn_lulus' => ['nullable', 'string', 'max:9'],
            'no_ijazah_lulus' => ['nullable', 'string', 'max:50'],
            'melanjutkan_ke' => ['nullable', 'string', 'max:100'],
            'pindah_ke_madrasah' => ['nullable', 'string', 'max:100'],
            'pindah_tingkat' => ['nullable', 'string', 'max:20'],
            'alasan_keluar' => ['nullable', 'string'],
            'tgl_keluar' => ['nullable', 'date'],
            'foto' => ['nullable', 'image', 'max:5120'],
            'dokumen_akta' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'dokumen_kk' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'dokumen_ijazah' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
            'dokumen' => ['nullable', 'array'],
            'dokumen.*' => ['nullable', 'file', 'mimes:pdf,jpg,jpeg,png', 'max:5120'],
        ];
    }

    private function kelasOptions()
    {
        return Kelas::orderBy('tingkat')->orderBy('nama_kelas')->get(['id', 'nama_kelas']);
    }

    private function tahunPelajaranOptions()
    {
        return TahunPelajaran::orderBy('kode')->get(['id', 'kode', 'is_aktif']);
    }

    private function detailPayload(Siswa $siswa): array
    {
        return [
            ...$siswa->only([...self::SISWA_FIELDS, 'foto']),
            'id' => $siswa->id,
            'tanggal_lahir' => $siswa->tanggal_lahir?->toDateString(),
            'foto_url' => $siswa->foto ? Storage::url($siswa->foto) : null,
            'kelas' => $siswa->kelas ? [
                'id' => $siswa->kelas->id,
                'nama_kelas' => $siswa->kelas->nama_kelas,
            ] : null,
            'tahun_pelajaran' => $siswa->tahunPelajaran ? [
                'id' => $siswa->tahunPelajaran->id,
                'kode' => $siswa->tahunPelajaran->kode,
            ] : null,
            'orang_tua_wali' => $siswa->orangTuaWali?->only(self::ORTU_FIELDS),
            'perkembangan' => [
                ...($siswa->perkembangan?->only(self::PERKEMBANGAN_FIELDS) ?? []),
                'tgl_ijazah_asal' => $siswa->perkembangan?->tgl_ijazah_asal?->toDateString(),
                'tgl_diterima' => $siswa->perkembangan?->tgl_diterima?->toDateString(),
                'tgl_keluar' => $siswa->perkembangan?->tgl_keluar?->toDateString(),
            ],
            'dokumen' => $siswa->dokumen->map(fn (SiswaDokumen $dokumen) => [
                'id' => $dokumen->id,
                'jenis_dokumen' => $dokumen->jenis_dokumen,
                'nama_file' => $dokumen->nama_file,
                'file_path' => $dokumen->file_path,
                'url' => Storage::url($dokumen->file_path),
            ])->values(),
        ];
    }

    private function storeFoto(UploadedFile $file, string $nis, ?string $oldPath = null): string
    {
        if ($oldPath) {
            Storage::disk('public')->delete($oldPath);
        }

        $filename = time().'_foto_'.Str::slug($nis).'.'.$file->getClientOriginalExtension();

        return $file->storeAs('siswa-foto', $filename, 'public');
    }

    private function storeDokumen(Request $request, Siswa $siswa): void
    {
        $files = [
            'Akta Kelahiran' => $request->file('dokumen_akta'),
            'Kartu Keluarga' => $request->file('dokumen_kk'),
            'Ijazah' => $request->file('dokumen_ijazah'),
        ];

        foreach (($request->file('dokumen') ?? []) as $jenis => $file) {
            $files[$jenis] = $file;
        }

        foreach ($files as $jenis => $file) {
            if (! $file instanceof UploadedFile) {
                continue;
            }

            $old = $siswa->dokumen()->where('jenis_dokumen', $jenis)->first();
            if ($old) {
                Storage::disk('public')->delete($old->file_path);
            }

            $filename = time().'_'.Str::slug((string) $jenis).'_'.Str::slug($siswa->nis).'.'.$file->getClientOriginalExtension();
            $path = $file->storeAs('siswa-dokumen', $filename, 'public');

            $siswa->dokumen()->updateOrCreate(
                ['jenis_dokumen' => $jenis],
                ['file_path' => $path, 'nama_file' => $file->getClientOriginalName()]
            );
        }
    }
}
