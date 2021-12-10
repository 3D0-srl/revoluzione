<?php
use TheIconic\Tracking\GoogleAnalytics\Analytics;
require_once(_MARION_MODULE_DIR_.'google_tracking/vendor/autoload.php');
require_once(_MARION_MODULE_DIR_.'google_tracking/ga-ecommerce-tracking.php');
class IndexController extends FrontendController{


    function display(){
        $this->test();
        
    }

    function gen_uuid() {
        return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            // 32 bits for "time_low"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),
    
            // 16 bits for "time_mid"
            mt_rand( 0, 0xffff ),
    
            // 16 bits for "time_hi_and_version",
            // four most significant bits holds version number 4
            mt_rand( 0, 0x0fff ) | 0x4000,
    
            // 16 bits, 8 bits for "clk_seq_hi_res",
            // 8 bits for "clk_seq_low",
            // two most significant bits holds zero and one for variant DCE1.1
            mt_rand( 0, 0x3fff ) | 0x8000,
    
            // 48 bits for "node"
            mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
        );
    }

    function test(){
        $analytics = new Analytics();

        // Build the order data programmatically, including each order product in the payload
        // Take notice, if you want GA reports to tie this event with previous user actions
        // you must get and set the same ClientId from the GA Cookie
        // First, general and required hit data
        $analytics->setProtocolVersion('1')
            ->setTrackingId('UA-163600893-2')
            ->setClientId($this->gen_uuid());
           // ->setUserId('123');
        $analytics->setDocumentPath('/');
        
        // Then, include the transaction data
        $analytics->setTransactionId('7778922')
            ->setAffiliation('THE ICONIC')
            ->setRevenue(250.0)
            ->setTax(25.0)
            ->setShipping(15.0)
            ->setCouponCode('MY_COUPON');
        
        // Include a product
        $productData1 = [
            'sku' => 'AAAA-6666',
            'name' => 'Test Product 2',
            'brand' => 'Test Brand 2',
            'category' => 'Test Category 3/Test Category 4',
            'variant' => 'yellow',
            'price' => 50.00,
            'quantity' => 1,
            'coupon_code' => 'TEST 2',
            'position' => 2
        ];
        
        $analytics->addProduct($productData1);
        
        // You can include as many products as you need this way
        $productData2 = [
            'sku' => 'AAAA-5555',
            'name' => 'Test Product',
            'brand' => 'Test Brand',
            'category' => 'Test Category 1/Test Category 2',
            'variant' => 'blue',
            'price' => 85.00,
            'quantity' => 2,
            'coupon_code' => 'TEST',
            'position' => 4
        ];
        
        $analytics->addProduct($productData2);
        
        // Don't forget to set the product action, in this case to PURCHASE
        $analytics->setProductActionToPurchase();
        
        // Finally, you must send a hit, in this case we send an Event
        $res = $analytics->setEventCategory('Checkout')
            ->setEventAction('Purchase')
             ->sendEvent();
        $response = $analytics->setDebug(true)->sendPageview();
        $debugResponse = $response->getDebugResponse();
        //print_r($debugResponse);
        debugga($debugResponse);exit;

    }
    function getIP(){
        //whether ip is from share internet
        if (!empty($_SERVER['HTTP_CLIENT_IP']))   
        {
        $ip_address = $_SERVER['HTTP_CLIENT_IP'];
        }
        //whether ip is from proxy
        elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR']))  
        {
        $ip_address = $_SERVER['HTTP_X_FORWARDED_FOR'];
        }
        //whether ip is from remote address
        else
        {
        $ip_address = $_SERVER['REMOTE_ADDR'];
        }
        return $ip_address;
    }

    //http://www.google-analytics.com/collect?v=1&tid=UA-XXX-Y&cid=35009a79-1a05-49d7-b876-2b884d0f825b&an=My%20Awesom%20APP&aid=com.daimto.awesom.app&av=1.0.0&aiid=come.daimto.awesom.installer &t=event&ec=list&ea=accounts&userclicked&ev=10

    function event(){
        //$cart = Cart::withId(2);
        $analytics = new Analytics(true);
        $tracking_id = 'UA-163600893-2';
        $client_id = preg_replace("/^.+\.(.+?\..+?)$/", "\\1", @$_COOKIE['_ga']);

        $analytics->setDebug(false);
        $analytics
            ->setProtocolVersion('1')
            ->setTrackingId($tracking_id)
            ->setClientId($client_id);
            //->setTransactionId($cart->id)
            //->setRevenue(10)
            //->setAsyncRequest(true)
            //->setCurrencyCode('EUR');

            $res = $analytics->setEventCategory('Test')
            ->setEventAction('test2')
            ->setEventLabel('ciccio bello')
            ->setEventValue(1)
            ->sendEvent();
            debugga($res);exit;
        //$analytics->setDataL
       
        //debugga($res);
      
    }

    function page(){
        $analytics = new Analytics();
        $tracking_id = 'UA-163600893-2';
        $client_id = preg_replace("/^.+\.(.+?\..+?)$/", "\\1", @$_COOKIE['_ga']);

        debugga($analytics);exit;
        
        $analytics
            ->setProtocolVersion('1')
            ->setTrackingId($tracking_id)
            ->setClientId($client_id)
            ->setDocumentPath('/testciro-nuovo')
            ->setDocumentTitle('Amedo sei bello')
            ->setIpOverride($this->getIP());


        // When you finish bulding the payload send a hit (such as an pageview or event)
        $res = $analytics->sendPageview();
        debugga($res);
        debugga($client_id,'fatto');exit;
    }

    function thanks(){
        $cart = Cart::withId(2);
        $orders = $cart->getOrders();
        $analytics = new Analytics(true);
        $tracking_id = 'UA-163600893-2';
        $client_id = preg_replace("/^.+\.(.+?\..+?)$/", "\\1", @$_COOKIE['_ga']);

        $analytics->setDebug(false);
        $analytics
            ->setProtocolVersion('1')
            ->setTrackingId($tracking_id)
            ->setClientId($client_id);
            //->setTransactionId($cart->id)
            //->setRevenue(10)
            //->setAsyncRequest(true)
            //->setCurrencyCode('EUR');

            $res = $analytics->setEventCategory('Categoria')
            ->setEventAction('Azione')
            ->setEventLabel('Ciro carrello')
            ->setEventValue(1)
            ->sendEvent();
            debugga($res);exit;
            exit;

            foreach ($orders as $k => $order) {
                $product = $order->getProduct();
                $analytics->addProduct([
                    'sku' => $product->sku,
                    'name' => $product->get('name'),
                    'category' => $product->getFullNameSection(),
                    'price' => $order->price,
                    'quantity' => $order->quantity,
                    'position' => $k+1,
                ]);
                
            }
            $res = $analytics->setEventCategory('Checkout')
            ->setEventAction('Purchase')
            ->sendEvent();


            $debugResponse = $res->getDebugResponse();
            debugga($debugResponse);exit;
       /* $analytics
            ->setProtocolVersion('1')
            ->setTrackingId($tracking_id)
            ->setClientId($client_id)
            ->setDocumentPath('/testciro')
            ->setIpOverride($this->getIP());
        */

       /* $res = $analytics->setTransactionId(1667) // transaction id. required
        ->setRevenue(65.00)
        ->setShipping(5.00)
        ->setTax(10.83)
        // make the 'transaction' hit
        ->sendTransaction();
        // When you finish bulding the payload send a hit (such as an pageview or event)
       // $res = $analytics->sendPageview();
        debugga($res);
        debugga($client_id,'fatto');exit;
        
        $analytics->setProtocolVersion('1')
            ->setTrackingId('UA-163600893-2')
            ->setClientId($client_id);
        $analytics->setTransactionId($cart->id) // transaction id. required
            ->setRevenue($cart->getTotal())
            ->setShipping(5.00)
            //->setTax(10.83)
            // make the 'transaction' hit
            ->sendTransaction();
        foreach ($orders as $order) {
            $product = $order->getProduct();
            $response = $analytics->setTransactionId($cart->id) // transaction id. required, same value as above
                ->setItemName($product->get('name')) // required
                ->setItemCode($product->sku) // SKU or id
                ->setItemCategory($product->getFullNameSection()) // item variation: category, size, color etc.
                ->setItemPrice($order->price)
                ->setItemQuantity($order->quantity)
                // make the 'item' hit
                ->sendItem();
            debugga($response);exit;
        }*/
    }
}

?>