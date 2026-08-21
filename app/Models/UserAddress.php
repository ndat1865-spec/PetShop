<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class UserAddress extends Model
{
    protected $fillable = ['user_id','label','receiver_name','phone','province','district','ward','address_line','is_default'];

    protected $casts = ['is_default' => 'boolean'];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function fullAddress(): string
    {
        return implode(', ', array_filter([$this->address_line, $this->ward, $this->district, $this->province]));
    }
}
