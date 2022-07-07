<?php 
namespace App\Models;

use Illuminate\Database\Eloquent\Model as Eloquent;

class Document extends Eloquent
{    
    protected $table = 'documents';
    protected $fillable = [
        'document_id',
        'sign_status',
        'url', 
        'url_signed',
        'short_link', 
        'usuclin', 
        'metadata'
    ];
    protected $primaryKey = 'id';
    // protected $dates = ['created_at', 'updated_at'];

}