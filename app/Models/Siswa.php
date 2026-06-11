<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Siswa extends Model
{
    use HasFactory;

    protected $table = 'siswa';

    protected $fillable = [
        'no_urut', 'nis', 'nisn', 'nism', 'nik', 'no_kk',
        'nama_lengkap', 'nama_panggilan', 'jenis_kelamin', 'tempat_lahir', 'tanggal_lahir',
        'agama', 'kewarganegaraan', 'anak_ke', 'saudara_kandung', 'saudara_tiri', 'saudara_angkat',
        'status_anak', 'yatim_piatu', 'bahasa_sehari_hari',
        'alamat', 'rt', 'rw', 'desa_kelurahan', 'kecamatan', 'kabupaten_kota', 'provinsi', 'kode_pos',
        'nama_orang_tua',
        'no_telepon', 'hp', 'bertempat_tinggal_pada', 'jarak_ke_madrasah', 'moda_transportasi',
        'golongan_darah', 'penyakit_pernah_diderita', 'kelainan_jasmani', 'tinggi_badan_awal', 'berat_badan_awal',
        'hobi_kesenian', 'hobi_olahraga', 'hobi_organisasi', 'hobi_lain',
        'foto', 'kelas_id', 'tahun_pelajaran_id', 'status'
    ];

    protected $casts = [
        'tanggal_lahir' => 'date',
        'tinggi_badan_awal' => 'decimal:2',
        'berat_badan_awal' => 'decimal:2',
    ];

    public function kelas()
    {
        return $this->belongsTo(Kelas::class);
    }

    public function tahunPelajaran()
    {
        return $this->belongsTo(TahunPelajaran::class);
    }

    public function orangTuaWali()
    {
        return $this->hasOne(OrangTuaWali::class);
    }

    public function perkembangan()
    {
        return $this->hasOne(PerkembanganSiswa::class);
    }

    public function raportNilai()
    {
        return $this->hasMany(RaportNilai::class);
    }

    public function dokumen()
    {
        return $this->hasMany(SiswaDokumen::class);
    }
}
