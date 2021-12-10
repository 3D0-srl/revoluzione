<?php
/**
 * Copyright 2013 CPI Group, LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 *
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/**
 * Fetches a list of products from Amazon using a search query.
 * 
 * This Amazon Products Core object retrieves a list of products from Amazon
 * that match the given search query. In order to search, a query is required.
 * The search context (ex: Kitchen, MP3 Downloads) can be specified as an
 * optional parameter.
 */
class AmazonProductGetMatchingProductForId extends AmazonProductsCore{
    
    
    /**
     * AmazonProductList fetches a list of products from Amazon that match a search query.
     * 
     * The parameters are passed to the parent constructor, which are
     * in turn passed to the AmazonCore constructor. See it for more information
     * on these parameters and common methods.
     * Please note that an extra parameter comes before the usual Mock Mode parameters,
     * so be careful when setting up the object.
     * @param string $s [optional] <p>Name for the store you want to use.
     * This parameter is optional if only one store is defined in the config file.</p>
     * @param string $q [optional] <p>The query string to set for the object.</p>
     * @param boolean $mock [optional] <p>This is a flag for enabling Mock Mode.
     * This defaults to <b>FALSE</b>.</p>
     * @param array|string $m [optional] <p>The files (or file) to use in Mock Mode.</p>
     * @param string $config [optional] <p>An alternate config file to set. Used for testing.</p>
     */
    public function __construct($s = null, $q = null, $mock = false, $m = null, $config = null){
        parent::__construct($s, $mock, $m, $config);
        include($this->env);
        
        if($q){
            $this->setQuery($q);
        }
        
        $this->options['Action'] = 'GetMatchingProductForId';
        
        if(isset($THROTTLE_TIME_PRODUCTMATCH)) {
            $this->throttleTime = $THROTTLE_TIME_PRODUCTMATCH;
        }
        $this->throttleGroup = 'GetMatchingProductForId';
    }
    


	public function setIdType($t){
		 if (is_string($t)){
            $this->options['IdType'] = $t;
        } else {
            return false;
        }
	}
	

	function resetIdList(){
		
	}

	protected function parseXML($xml){
        if (!$xml){
            return false;
        }
        $this->AsinCount = 0;
		$this->AsinList = array();
        foreach($xml->children() as $x){
            if($x->getName() == 'ResponseMetadata'){
                continue;
            }
			foreach($xml->GetMatchingProductForIdResult as $v){
				$temp = (array)$v->attributes();
				
				$data = array();
				$data['Id'] = $temp['@attributes']['Id'];
				$data['status'] = $temp['@attributes']['status'];
				
				
				if (isset($v->Products)){
					foreach($v->Products->children() as $z){
						$prod = new AmazonProduct($this->storeName, $z, $this->mockMode, $this->mockFiles,$this->config);
						$data_prod = $prod->getData();
						$asin = $data_prod['Identifiers']['MarketplaceASIN']['ASIN'];
						$asin_parent = $data_prod['Relationships']['VariationParent'][0]['Identifiers']['MarketplaceASIN']['ASIN'];
						$type = $data_prod['AttributeSets'][0]['ProductTypeName'];
						$data['asin'] = $asin;
						$data['type'] = $type;
						if( $asin_parent ){
							$data['asin_parent'] = $asin_parent;
						}
					}
				}
				
				
				//$result[$temp['Id']] = 
				if( $v->Error ){
					
					$data['error'] = (string)$v->Error->Message;

					$this->errors[$data['Id']] = $data;
				}else{
					$this->AsinList[$data['Id']] = $data;
				}

				
				
			}
			
			
        }
		
    }


	public function setIdList($s){
        if (is_string($s)){
            $this->resetIdList();
            $this->options['IdList.Id.1'] = $s;
        } else if (is_array($s)){
           $this->resetIdList();
            $i = 1;
            foreach ($s as $x){
                $this->options['IdList.Id.'.$i] = $x;
                $i++;
            }
        } else {
            return false;
        }
    }
   
    public function getProductList(){
		
        
        
        $url = $this->urlbase."/".$this->urlbranch;
			
        $query = $this->genQuery();
		
        if ($this->mockMode){
           $xml = $this->fetchMockFile();

        } else {
            $response = $this->sendRequest($url, array('Post'=>$query));
			
            if (!$this->checkResponse($response)){
                return false;
            }
            
            $xml = simplexml_load_string($response['body']);
			
			
        }
		
        $this->parseXML($xml);
		
		

		return true;
    }



	
    
}
?>