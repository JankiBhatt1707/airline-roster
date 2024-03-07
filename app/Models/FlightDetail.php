<?php namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FlightDetail extends Model
{
    use HasFactory;

    protected $fillable = [
        'flight_number',
        'scheduled_time_departure',
        'scheduled_time_arrival'
    ];
}
