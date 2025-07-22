<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Notifications\Notifiable;

class Organization extends Model
{
    use HasFactory;
    use SoftDeletes;
    use Notifiable;
    use HasUuids;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */

    protected $fillable = [
        'name',
        'vat_number',
        'chamber_of_commerce',
        'street',
        'street_number',
        'city',
        'postal_code',
        'country',
        'billing_email',
        'billing_details',
        'metadata',
        'owner_id',
    ];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<string>
     */
    protected $hidden = [
        'owner_id',
        'parent_id',
        'created_at',
        'updated_at',
        'deleted_at',
    ];


}
