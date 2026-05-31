<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PertMitraTask extends Model
{
    use HasFactory;

    protected $table = 'pert_mitra_task';
    protected $primaryKey = 'id_mitra_task';
    public $timestamps = false; // karena pakai created_at manual

    protected $fillable = [
        'id_result',
        'project_id',
        'kode_task',
        'nama_pekerjaan',
        'time_expected',
        'created_at',
    ];

    // Relasi ke PertResult
    public function result()
    {
        return $this->belongsTo(PertResult::class, 'id_result', 'id_result');
    }
}
