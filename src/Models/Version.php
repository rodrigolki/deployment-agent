<?php 
namespace App\Models;

use DateTime;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model as Eloquent;

class Version extends Eloquent
{    
    protected $table = 'versions';
    protected $fillable = [
        'refer_id',
        'refer_type',
        'content',
        'version_date'
    ];

    protected $primaryKey = 'id';
    public $timestamps = false;
    protected $dates = ['version_date'];

    protected function versionDate(): Attribute
    {
        $formated = null;
        if (isset($this->attributes['version_date'])) {
            $data = new DateTime($this->attributes['version_date']);
            $formated = $data->format('d/m/Y H:i:s');
        }
        return Attribute::make(
            set: fn ($value) => $value->format('Y-m-d H:i:s'),
            get: fn () => $formated,
        );
    }
}