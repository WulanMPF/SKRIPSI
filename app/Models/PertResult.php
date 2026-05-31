<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertResult extends Model
{
    protected $table = 'pert_result';
    protected $primaryKey = 'id_result';
    public $timestamps = false;

    protected $fillable = [
        'project_id',
        'tot_durasi',
        'is_critical'
    ];

    // relasi dari tabel pert_path atribut id_result
    public function paths()
    {
        return $this->hasMany(PertPath::class, 'id_result');
    }

    public function mitraTasks()
    {
        return $this->hasMany(PertMitraTask::class, 'id_result', 'id_result');
    }
}
