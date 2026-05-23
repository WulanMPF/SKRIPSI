<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PertInput extends Model
{
    protected $table = 'pert_input';
    protected $primaryKey = 'id_input';
    public $timestamps = false;

    protected $fillable = [
        'id_master',
        'project_id',
        'optimis',
        'pesimis',
        'time_expected'
    ];

    // relasi ke tabel pert_master_task atribut id_master
    public function masterTask()
    {
        return $this->belongsTo(PertMasterTask::class, 'id_master');
    }
}
