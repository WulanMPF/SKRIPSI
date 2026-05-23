<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertMasterDependency extends Model
{
    protected $table = 'pert_master_dependency';
    protected $primaryKey = 'id_dependency';
    public $timestamps = false;

    protected $fillable = [
        'id_master',
        'ketergantungan'
    ];

    // relasi ke tabel pert_master_task atribut id_master
    public function task()
    {
        return $this->belongsTo(PertMasterTask::class, 'id_master');
    }

    // relasi ke tabel pert_master_task atribut ketergantungan
    public function dependsOn()
    {
        return $this->belongsTo(PertMasterTask::class, 'ketergantungan');
    }
}
