<?php
class AmazonTabAdmin extends ProductTabAdminController{
	


	public function getTitle(): string{
		return 'Amazon';
	}

	public function getTag():string{
		
		return 'amazon';
	}


	function setMedia()
    {
        $this->registerJS('../modules/amazon/js/product.js');
        $this->registerCSS('../modules/amazon/css/product.css');
    }

	function getContent(){
        $id = $this->getID();
        $product = Product::withId($id);
       
        if( is_object($product) ){

            require_once(_MARION_MODULE_DIR_.'amazon/classes/AmazonProduct.class.php');
            require_once(_MARION_MODULE_DIR_.'amazon/classes/AmazonTool.class.php');
            $list = AmazonProduct::prepareQuery()->where('id_product',$id)->get();
            $old = array();
            foreach($list as $v){
                $old[$v->id_account][$v->marketplace] = $v;
            }
            
            
            
    
            $tabs = array();
            $database = _obj('Database');
            $stores = $database->select('*','amazon_store',"1=1");
            if( okArray($stores) ){

                 
                foreach($stores as $k => $v){
                        $tabs[$k]['id'] = $v['id'];
                        $tabs[$k]['name'] = $v['name'];
                        $markets = unserialize($v['marketplace']);
                        //debugga($markets);exit;
                        foreach($markets as $k2 => $m){
                            $tabs[$k]['markets'][$k2]['img'] = AmazonTool::getMarketplaceImage($m);
                            
                            if( $old[$v['id']][$m] ){
                                $tabs[$k]['markets'][$k2]['price'] = $old[$v['id']][$m]->price;
                                $tabs[$k]['markets'][$k2]['bullet_1'] = $old[$v['id']][$m]->bullet_1;
                                $tabs[$k]['markets'][$k2]['bullet_2'] = $old[$v['id']][$m]->bullet_2;
                                $tabs[$k]['markets'][$k2]['bullet_3'] = $old[$v['id']][$m]->bullet_3;
                                $tabs[$k]['markets'][$k2]['disable_sync'] = $old[$v['id']][$m]->disable_sync;
                                $tabs[$k]['markets'][$k2]['parent_description'] = $old[$v['id']][$m]->parent_description;
                                $tabs[$k]['markets'][$k2]['new_product'] = $old[$v['id']][$m]->new_product;
                            }else{
                                $tabs[$k]['markets'][$k2]['disable_sync'] = 0;
                                $tabs[$k]['markets'][$k2]['parent_description'] = 0;
                            }
                            $tabs[$k]['markets'][$k2]['name'] = $m;
                            
                        }
                        
                    }
                    


            
                    
                    }
            
                    if( count($tabs) == 1 ){
                        $this->setVar('one_account', 1);
                    }
                    if( $product->type == 2 && $product->parent == 0){
                        $this->setVar('hasChildren', 1);
                    }
                    if( $product->parent ){
                        $this->setVar('is_children', 1);
                    }

                    
                    $this->setVar('tabs', $tabs);
            
                }
        
        

        
        
        $this->output('tab_product.htm');
	

       
		
    }

    function checkData(){
        $formdata = $this->getFormdata();
        

        if( !okArray($formdata)) return false;

        $error =1;
        $id = $formdata['id'];

        $product = Product::withId($id);
        if( $formdata['parent'] == 0 && $product->parent ) return 1;
        
        foreach($formdata['modules']['amazon'] as $id_account => $data){
		
			foreach($data as $market => $values){
				$array = $this->checkDataForm('amazon_product',$values);
				
				if($array[0] == 'nak'){
                   
                    $error = $array[1];
                }else{
                    $this->checked_data[$id_account][$market] = $array;
                }
			}
        }
        
        return $error;
    }

    function process($product = null)
    {
        require_once(_MARION_MODULE_DIR_.'amazon/classes/AmazonProduct.class.php');
        $list = AmazonProduct::prepareQuery()->where('id_product',$product->id)->get();
        foreach($list as $v){
            $old[$v->id_account][$v->marketplace] = $v;
        }
       $checked_data = $this->checked_data;
       foreach($checked_data as $id_account => $data){
            foreach($data as $market => $array){
                
                $obj = $old[$id_account][$market];
                
                if( !$obj ){
                    $obj = AmazonProduct::create();
                    
                }
                $obj->set($array);
                $obj->id_account = $id_account;
                $obj->marketplace = $market;
                $obj->id_product = $product->id;
                
                $obj->save();

                //debugga($obj);exit;
                
                
            }
        }
        
        
    }

    
}
Product::registerAdminTab('AmazonTabAdmin');
?>