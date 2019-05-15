<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class QrCodeModel extends Model
{
    protected $table = 'wx_qrcode';
    public $timestamps=false;
}
