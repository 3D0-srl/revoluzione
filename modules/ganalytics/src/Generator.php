<?php
namespace Ganalytics;

class Generator {
    private static $code = 'codice';

   public static function scriptBase(
            $pageType=null,
            $pageTarget=null
        ){
        $code = self::$code;
        return "<!-- Google Tag Manager -->
<script>
dataLayer = window.dataLayer ||  [];

dataLayer.push({
        'pageType': '{$pageType}',
        'pageTarget': '{$pageTarget}'
});

(function(w,d,s,l,i){w[l]=w[l]||[];w[l].push({'gtm.start':
new Date().getTime(),event:'gtm.js'});var f=d.getElementsByTagName(s)[0],
j=d.createElement(s),dl=l!='dataLayer'?'&l='+l:'';j.async=true;j.src=
'https://www.googletagmanager.com/gtm.js?id='+i+dl;f.parentNode.insertBefore(j,f);
})(window,document,'script','dataLayer','{$code}');</script>
<!-- End Google Tag Manager -->";
    }
	

    public static function scriptViewProduct($prodotto){
        $NomeProdotto = $prodotto->getName();
        $SKUProdotto = $prodotto->sku;
        $PrezzoProdotto = $prodotto->getPriceValue();
        $CategoriaProdotto = $prodotto->getNameSection();
        return "<script>
dataLayer.push({
'event':'productDetail',
'ecommerce': {
    'detail': {
    'products': [{
    'name': '{$NomeProdotto}',
    'id': '{$SKUProdotto}',
    'price': '{$PrezzoProdotto}',
    'brand': '',
    'variant':'',
    'category': '{$CategoriaProdotto}'
    }]     }}
});
</script>";
    }

    public static function scriptCartThanks(\Shop\Cart $cart){
        $PrezzoTransazione = $cart->total_without_tax
                                +$cart->shippingPriceWithoutTax
                                +$cart->paymentPriceWithoutTax;
        $TasseTransazione = $cart->total_tax
                                +$cart->shippingPriceTax
                                +$cart->paymentPriceTax;

        $MetodoDiPagamento = $cart->getNamePaymentMethod();
        $IDtransazione = $cart->number;
        $NomeCoupon = '';
        if( $cart->discount ){
            $database = \Marion\Core\Marion::getDB();

            $name = $database->select('coupon_name','coupon_cart as co join cart as c on c.id=co.carrello',"carrello='{$cart->id}' AND c.status <> 'active'");
			if( okArray($name)){
                $NomeCoupon = $name[0]['coupon_name'];
            }
            
        }
        
        $script = "<script>
dataLayer.push({
'event':'purchase',
    'ecommerce': {
    'purchase': {
        'actionField': {
        'id': '{$IDtransazione}',                      
        'affiliation': '{$MetodoDiPagamento}', // se Affiliation non è usato, inserire qui il metodo di pagamento
        'revenue': '{$PrezzoTransazione}',                  
        'tax': '{$TasseTransazione}',
        'shipping': '',
        'coupon': '{$NomeCoupon}' 
        },
        'products': [".self::buildProducts($cart->getOrders())."]
    } }
  });
</script>";
        return $script;
    }



private static function buildProducts(array $orders){

    $script = '[';
    foreach($orders as $ord){
        $prodotto = $ord->getProduct();
        $NomeProdotto = $prodotto->getName();
        $SKUProdotto = $prodotto->sku;
        $PrezzoProdotto = $ord->price+$ord->supplement-$ord->discount;
        $CategoriaProdotto = $prodotto->getNameSection();
        $QuantitaProdotto = $ord->quantity;
        
        $script .="{                      
    'name': '{$NomeProdotto}',
    'id': '{$SKUProdotto}',
    'price': '{$PrezzoProdotto}',
    'brand': '',
    'variant':'',
    'category': '{$CategoriaProdotto}',
    'quantity': {$QuantitaProdotto}                       
} ";
    }
    $script.="]";
    return $script;

    }


    public static function scriptCheckout($orders,int $NumeroStep){
        
        $script = "<script>
        dataLayer.push({
        'event':'checkout',
            'ecommerce': {
            'checkout': {
                'actionField': {
                    'step': {$NumeroStep},                      
                    'products': ".self::buildProducts($orders)."
                } 
            }
        })
        </script>";
        return $script;
    
    }


    public static function getDataProduct($id_order){
		if( authUser()){
			$order = \Shop\Order::withId($id_order);
			$prodotto = $order->getProduct();
			$data['name'] = $prodotto->getName(null,false);
			$data['sku'] = $prodotto->sku;
			$data['price'] = $order->price;
			$data['qnt'] = $order->quantity;
			$data['category'] = $prodotto->getNameSection();

			return $data;
		}else{
			$cart = \Shop\Cart::getCurrent();
			$order = $cart['orders'][$id_order];
			if( okArray($order) ){
				$prodotto = \Catalogo\Product::withId($order['product']);
				if( is_object($prodotto) ){
					$data['name'] = $prodotto->getName(null,false);
					$data['sku'] = $prodotto->sku;
					$data['price'] = $order['price'];
					$data['qnt'] = $order['quantity'];
					$data['category'] = $prodotto->getNameSection();
				}
				return $data;
			}
			
		}
	}
    



}
?>
