<?php
use Marion\Core\marion;
use Illuminate\Database\Capsule\Manager as DB;

/**
 * helper che controlla se il corso è disponibile o meno
 *
 * @param int $product_id
 * @return boolean
 */
function elearning_check_course($product_id): bool{
    $user = Marion::getUser();
     return DB::table('cart','c')
        ->join('cartRow as r','r.cart','=','c.id')
        ->whereIn('c.status',['confirmed'])
        ->where('product',$product_id)
        ->where('c.user',$user->id)->exists();
}


function elearnig_status_cart(): array{
    $data = Marion::getConfig('elearning_conf');
    if( okArray($data) ){
        $status = unserialize($data['cart_status']);
        return $status;
    }
    return [];
}


?>