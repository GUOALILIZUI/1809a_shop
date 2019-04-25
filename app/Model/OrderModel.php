<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class OrderModel extends Model
{
    //
    protected $table='p_order';
    public $timestamps=false;

    //生成订单编号
    public static function orderSn(){
        $order='1809'.time().rand(1111,9999).Str::random(16);
        $order_sn=substr($order,5,16);
        return $order_sn;
    }
}
