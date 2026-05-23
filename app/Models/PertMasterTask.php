<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertMasterTask extends Model
{
    protected $table = 'pert_master_task';
    protected $primaryKey = 'id_master';
    public $timestamps = false;

    protected $fillable = [
        'nama_pekerjaan',
        'realistis'
    ];

    // relasi dari tabel pert_master_dependency atribut id_master
    public function dependencies()
    {
        return $this->hasMany(PertMasterDependency::class, 'id_master');
    }

    // relasi dari tabel pert_master_dependency atribut ketergantungan
    public function dependents()
    {
        return $this->hasMany(PertMasterDependency::class, 'ketergantungan');
    }

    // relasi dari tabel pert_input atribut id_master
    public function inputs()
    {
        return $this->hasMany(PertInput::class, 'id_master');
    }

    // relasi dari tabel pert_path atribut id_master
    public function paths()
    {
        return $this->hasMany(PertPath::class, 'id_master');
    }
}
