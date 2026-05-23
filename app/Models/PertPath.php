<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertPath extends Model
{
    protected $table = 'pert_path';
    protected $primaryKey = 'id_path';
    public $timestamps = false;

    protected $fillable = [
        'id_result',
        'id_master',
        'urutan'
    ];

    // relasi ke tabel pert_result atribut id_result
    public function result()
    {
        return $this->belongsTo(PertResult::class, 'id_result');
    }

    // relasi ke tabel pert_master_task atribut id_master
    public function masterTask()
    {
        return $this->belongsTo(PertMasterTask::class, 'id_master');
    }
}
