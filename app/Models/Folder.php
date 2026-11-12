<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;

class Folder extends Model
{
    protected $fillable = ['user_id', 'parent_id', 'name'];

    public function files()
    {
        return $this->hasMany(File::class);
    }
}