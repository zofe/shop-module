<?php

namespace App\Modules\Shop\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Zofe\Rapyd\Traits\ShortId;

class License extends Model
{
    use HasUuids, ShortId;
    protected $table = 'licenses';
}
