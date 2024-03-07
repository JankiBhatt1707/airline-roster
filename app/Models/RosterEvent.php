<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class RosterEvent extends Model
{
    use HasFactory;

    protected $fillable = [
        'roster_id',
        'flight_number',
        'type',
        'start_location',
        'end_location',
        'date',
        'check_in_time',
        'check_out_time',
    ];
}
