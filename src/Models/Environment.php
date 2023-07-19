<?php 
namespace App\Models;

use App\Controllers\web\SecretsController;
use DateTime;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Environment extends Eloquent
{    
    protected $table = 'environments';
    protected $fillable = [
        'name',
        'slug',
        'env',
        'content',
        'content_type',
        'updated_at'
    ];

    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $dates = ['created_at', 'updated_at'];

    protected function updatedAt(): Attribute
    {
        $data = new DateTime($this->attributes['updated_at']);
        $formated = $data->format('d/m/Y H:i:s');
        return Attribute::make(
            get: fn () => $formated,
        );
    }

    protected function getLastUpdatedAtAttribute()
    {
        $data = new DateTime($this->attributes['updated_at']);
        $formated = $data->format('Y-m-d H:i:s');
        return $formated;
    }

    protected function content(): Attribute
    {
        return Attribute::make(
            get: fn (string $value) => SecretsController::decrypt($value),
            set: fn (string $value) => SecretsController::encrypt($value),
        );
    }

}