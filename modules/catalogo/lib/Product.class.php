<?php
use Shop\{Price,tax,Eshop};
use Marion\Core\BaseWithImages;
use Marion\Core\Marion;
class Product extends BaseWithImages{
	use AttachmentTrait;
	
	
	// COSTANTI DI BASE
	const TABLE = 'product'; // nome della tabella a cui si riferisce la classe
	const TABLE_PRIMARY_KEY = 'id'; //chiave primaria della tabella a cui si riferisce la classe
	const TABLE_LOCALE_DATA = 'productLocale'; // nome della tabella del database che contiene i dati locali
	const TABLE_EXTERNAL_KEY = 'product';// / nome della chiave esterna alla tabella del database
	const PARENT_FIELD_TABLE = 'parent'; //nome del campo padre
	const LOCALE_FIELD_TABLE = 'locale'; // nome del campo locale nella tabella contenente i dati locali
	const LOCALE_DEFAULT = 'it'; //il locale di dafault
	const LOG_ENABLED = true; //abilita i log
	const PATH_LOG = ''; // file  in cui verranno memorizzati i log
	const NOTIFY_ENABLED = false; // notifica all'amministratore
	const NOTIFY_ADMIN_EMAIL = 'ciro.napolitano87@gmail.com'; // email a cui inviare la notifica
	const IMAGES_FIELD_TABLE = 'images';
	

	public static $_registred_classes = array();

	//ERRORI
	const ERROR_SKU_EMPTY = "sku_empty";
	const ERROR_SKU_DUPLICATE = "sku_duplicate";
	const ERROR_ATTRIBUTES_DUPLICATE = "attributes_duplicate";
	
	//atttributi che i figli ederitano dal padre di default
	protected static $_parent_fields = array('sku','section','manufacturer','offer','home','ean','virtual_product','centralized_stock');


	//TIPI DI URL
	public static $_typeUrl_multilocale = array(
			0 => "index.php?ctrl=Catalogo&mod=catalogo&action=product&product=%s&lang=%s",
			1 => "%s/catalog/product/%s/%s.htm",
			2 => "%s-catalog-product-%s-%s.htm",
			3 => "%s/cat/prod/%s/%s.htm",
			4 => "%s-cat-prod-%s-%s.htm"
		);

	public static $_typeUrl = array(
			0 => "index.php?ctrl=Catalogo&mod=catalogo&action=product&product=%s",
			1 => "catalog/product/%s/%s.htm",
			2 => "catalog-product-%s-%s.htm",
			3 => "cat/prod/%s/%s.htm",
			4 => "cat-prod-%s-%s.htm"
		);

	
	
	private $_attributes = array();

	//restituisce il nome della sezione
	function getNameSection($locale=NULL){
		if( $this->section ){
			$section = Section::withId($this->section);
			if(is_object($section)){
				if( !$locale ) $locale = $GLOBALS['activelocale'];
				return $section->get('name',$locale);
			}
		}
		return false;

	}

	//restituisce il nome completo della sezione del prodotto
	function getFullNameSection($locale = NULL){
		if( $this->section ){
			$section = Section::withId($this->section);
			if(is_object($section)){
				if( !$locale ) $locale = $GLOBALS['activelocale'];
				return $section->getFullName($locale);
			}
		}
		return false;

	}
	
	//metodo che stabilisce se un prodotto è virtuale. Un prodotto è virtuale se non è un prodotto fisico e quindi non necessita della spedizione.
	function isVirtual(){
		return $this->virtual_product;
	}

	function getInventory($id_inventory=NULL){
		if( !$id_inventory ) $id_inventory = 1;
		if( $this->id ){
			$database = Marion::getDB();
			$data = $database->select('*','product_inventory',"id_product={$this->id} AND id_inventory={$id_inventory}");
			if( okArray($data) ){
				return $data[0]['quantity'];
			}
		}
		return 0;
	}

	function createInventory($id_inventory=NULL){
		if( !$id_inventory ) $id_inventory = 1;
		$toinsert = array(
			'id_inventory' => $id_inventory,
			'id_product' =>	$this->id,
			'quantity' => 0
		);
		$database = Marion::getDB();
		$database->insert('product_inventory',$toinsert);
	}

	//metodo che aggiorna la giacenzac
	function updateStock($qty=0){
		$qty = (int)$qty;
		if( $this->id ){
			$database = Marion::getDB();
			$database->update('product',"id={$this->id}",array('stock' => $qty));
			Marion::do_action('product_update_stock',array($this->id,$qty));
		}
	}

	//metodo che aggiorna la giacenzac
	function updateInventory($qty=0,$id_inventory=NULL){
		$qty = (int)$qty;
		if( !$id_inventory ) $id_inventory = 1;
		if( $this->id ){
			$database = Marion::getDB();
			$database->update('product_inventory',"id_product={$this->id} AND id_inventory={$id_inventory}",array('quantity' => $qty));
			Marion::do_action('product_update_stock',array($this->id,$qty));
		}
	}
	
	//metodo che restituisce la quantità totale dell'articolo
	function getTotalStock(){
		if( $this->isConfigurable() && !$this->centralized_stock){
			$children = $this->getChildren();
			$tot = 0;
			if( okArray($children) ){
				foreach($children as $v){
					$tot += $v->stock;
				}
			}
		}else{
			$tot = $this->stock;
		}
		return $tot;
	}


	//metodo che restituisce true quando il prodotto è disponibile
	function isAvailable(){
		$tot = $this->getTotalStock();
		if( $tot ){
			return true;
		}else{
			return false;
		}
	}

	//restituisce il nome del produttore
	public function getManufacturerName($locale = NULL){
		if( !$locale ){
			$locale = $GLOBALS['activelocale'];
		}
		
		if( $this->manufacturer ){
			$manufacturer = Manufacturer::withId($this->manufacturer);
			if( is_object($manufacturer) ){
				return $manufacturer->get('name',$locale);
			}
		}
		return '';
	}
	
	//prende i valori degli attributi se l'oggetto possiede attributi
	public function getAttributesInit(){
		
		if( $this->hasAttributes() ){
			$attributeSet = AttributeSet::withId($this->attributeSet);
			if( is_object($attributeSet) ){
				
				$attributes = $attributeSet->getComposition();
				
				foreach($attributes as $v){
					$attribute = Attribute::withId($v['attribute']);
					if($attribute){
						$this->_attributes[$attribute->getLabel()] = isset($this->_attributes[$attribute->getLabel()])?$this->_attributes[$attribute->getLabel()]:'';
					}

				}
			}
			if($this->hasId()){
				$database = Marion::getDB();
				$attributes = $database->select('*','productAttribute',"product=".$this->getId());
				if( okArray($attributes) ){
					foreach($attributes as $v){
						$this->_attributes[$v['attribute']] = $v['value'];
					}
					
				}
			}
			
		}
	}

	//prende i tag relativi al prodotto
	public function getTags(){
		if( isset($this->id) && $this->id){
			
			$database = Marion::getDB();
			$tags = $database->select('*','productTagComposition',"id_product={$this->id}");
			
			if(okArray($tags)){
				foreach($tags as $v){
					$this->tags[] = $v['id_tag'];
				}
			}
		}
	}



	public function saveTags($array=array()){
		if( isset($this->id) && $this->id){
			$database = Marion::getDB();
			$database->delete('productTagComposition',"id_product={$this->id}");
			if( okArray($array) ){
				foreach( $array as $v ){
					$toinsert = array(
						'id_tag' => $v,
						'id_product' => $this->id
					);
					$database->insert('productTagComposition',$toinsert);
				}
			}
		}
	}

	
	//prende le sezioni secondarie del prodotto dal database
	public function getOtherSections(){
		if( isset($this->id) && $this->id){
			
			$database = Marion::getDB();
			$otherSections = $database->select('*','otherSectionsProduct',"product={$this->id}");
			
			if(okArray($otherSections)){
				foreach($otherSections as $v){
					$this->otherSections[] = $v['section'];
				}
			}
		}
	}

	//setta le sezioni secondarie del prodotto
	public function setOtherSections($array=array()){
		$this->otherSections = $array;
	}

	//setta le sezioni secondarie del prodotto
	public function saveOtherSections($array=array()){
		if( $this->id ){
			$database = Marion::getDB();
			$database->delete('otherSectionsProduct',"product={$this->id}");
			if( $this->hasChildren()){
				$id_children = $database->select('id','product',"parent={$this->id}");
				if( okArray($id_children) ){
					foreach($id_children as $child){
						$child_ids[] = $child['id'];
						$database->delete('otherSectionsProduct',"product={$child['id']}");
					}
				}
			}
			if(okArray($this->otherSections)){
				foreach($this->otherSections as $v){
					$database->insert('otherSectionsProduct',array('product'=> $this->id,'section'=>$v));
					if( okArray($child_ids) ){
						foreach($child_ids as $id){
							$database->insert('otherSectionsProduct',array('product'=> $id,'section'=>$v));
						}
					}
				}
			}
		}
	}

	//prende l'insieme attributi del prodotto
	public function getAttributeSet(){
		if( $this->attributeSet ){
			$attributeSet = AttributeSet::withId($this->attributeSet);
			if( is_object($attributeSet) ){
				return $attributeSet;
			}

		}
		return false;
	}
	
	//restituisce il nome del prodotto comprensivo di variazioni se il prodotto è configurabile
	function getName($locale=NULL,$html=true,$separator="</br>"){
		if( !$locale ) $locale = $GLOBALS['activelocale'];
		$name = $this->get('name');
		
		if( $this->type == 1 && okArray($this->_attributes) ){
			
			foreach($this->_attributes as $k => $v){
				$attribute = Attribute::withLabel($k);
				if( is_object($attribute) ){
					$attributeValue = AttributeValue::withId($v);
					if( is_object($attributeValue) ){
						if( $html ){
							$name .= $separator.$attribute->get('name',$locale).": <b>".$attributeValue->get('value',$locale)."</b>";
						}else{
							$name .= " ".$attribute->get('name',$locale).": ".$attributeValue->get('value',$locale);
						}
					}
				}
			}
		}
		if( $html ){
			//$name = preg_replace("/".$separator."$/",'',$name);
		}

		return $name;
	}

	function getSKU(){
		$sku = $this->sku;
		if( $this->type == 1 && okArray($this->_attributes) ){
			foreach($this->_attributes as $k => $v){
				$sku .= "_{$v}"; 
			}
		}
		return $sku;
	}


	//restiuisce l'url del prodotto 
	function getUrl($locale=NULL){
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		if( $this->hasParent()){
			$id = $this->getParentId();
		}else{
			$id = $this->getId();
		}
		
		$prettyUrl = $this->get('prettyUrl',$locale);
		if($prettyUrl){
			$name = $prettyUrl; 	
		}else{
			$name = $this->get('name',$locale);
		}
		$name = Marion::slugify($name);
		if( !isMultilocale() ){
			$typeUrls = self::$_typeUrl;
			if($this->urlType){
				$url = sprintf($typeUrls[$this->urlType],$id,$name);
			}else{
				$url = sprintf($typeUrls[0],$id,$name);
			}
		}else{
			$typeUrls = self::$_typeUrl_multilocale;
			if($this->urlType){
				$url = sprintf($typeUrls[$this->urlType],$locale,$id,$name);
			}else{
				$url = sprintf($typeUrls[0],$id,$locale,$name);
			}
		}
		
		$url = _MARION_BASE_URL_.$url;
		return $url;
	}

	//restituisce i vari tipi di url del prodotto
	public static function getTypeUrl(){
		if( isMultilocale() ){
			return self::$_typeUrl_multilocale;
		}else{
			return self::$_typeUrl;
		}
		
	}
	


	

	
	//verifica se un prodotto è configurabile
	function isConfigurable(){
		if($this->type == 2){
			return true;
		}else{
			return false;
		}
	}

	//verifica se un prodotto ha attributi
	function hasAttributes(){
		if(isset($this->attributeSet) && $this->attributeSet ){
			return true;
		}else{
			return false;
		}
	}



	//verifica se il codice articolo è stato inseirto ed se è un duplicato
	public function checkSKU(){
		
		if(!$this->sku){
			return STATIC::ERROR_SKU_EMPTY;
			
		}else{
			$database = Marion::getDB();
			
			if( !$this->hasParent()){
				if($this->hasId()){
					$check = $database->select('*',STATIC::TABLE,"sku='{$this->sku}' AND (parent = 0 OR parent is NULL) AND id <> {$this->getId()} and deleted=0");
				}else{
					$check = $database->select('*',STATIC::TABLE,"sku='{$this->sku}' AND (parent = 0 OR parent is NULL) and deleted=0");
				}
				if(okArray($check)){
					return STATIC::ERROR_SKU_DUPLICATE;
				}
			}
			
			
		}
		return true;
	}

	
	//setta gli attributi di un prodotto se li possiede
	public function setAttributes($attributes){
		
		if( $this->hasAttributes() ){
			
			if(okArray($attributes)){
				foreach($attributes as $k => $v){
					if(array_key_exists($k,$this->_attributes) ){
						$this->_attributes[$k] = $v;
					}
				}
			}
		}
		
		return $this;
	}

	//salva gli attributi di un prodotto se presenti
	public function saveAttributes(){

		if( $this->parent && $this->hasAttributes() ){
			
			if(okArray($this->_attributes)){
				$database = Marion::getDB();
				if( is_object($this->_oldObject) ){
					$old_attributes = $this->_oldObject->getAttributes();
					$intersect = array_intersect(array_values($old_attributes),array_values($this->_attributes));
					if( count($intersect) != count($this->_attributes) ){
						$id_product = $this->getId();
						$database->delete('productAttribute',"product={$id_product}");
						foreach($this->_attributes as $k => $v){
							$toinsert = array();
							$toinsert['product'] = $id_product;
							$toinsert['attribute'] = $k;
							$toinsert['value'] = $v;
							$database->insert('productAttribute',$toinsert);

						}
					}
				}else{
					foreach($this->_attributes as $k => $v){
						$toinsert = array();
						$toinsert['product'] = $this->getId();
						$toinsert['attribute'] = $k;
						$toinsert['value'] = $v;
						$database->insert('productAttribute',$toinsert);

					}
				}				
			}
		}
		
		return $this;
	}

	function getUrlImageLabelPrice($type='original'){
		if( isset($this->price_unit) && is_object($this->price_unit) ){
			if( isset($this->price_unit->image) && $this->price_unit->image ){
				$type = parent::getTypeImageUrl($type);
				return _MARION_BASE_URL_."img/".$this->price_unit->image."/".$type."-nw/labelprie.png";
			}
		}

		return false;

	}

	function getDiscountPercentage(){
		if( is_object($this->price_unit) ){
			
			$sconto = $this->price_unit->defaultValue-$this->price_unit->value;
			$perc = (int)($sconto/$this->price_unit->defaultValue*100);
			if( $perc > 0 ){
				return $perc;
			}
		}

		return false;

	}

	//stabilisce se il prodotto ha un prezzo di listino che non è quello standard
	function hasSpecialPrice(){
		if( isset($this->price_unit) && is_object($this->price_unit) ){
			if( isset($this->price_unit->listPriceName) && $this->price_unit->listPriceName && $this->price_unit->listPriceName != 'standard' && $this->price_unit->listPriceName != 'default' ){
				return true; 
			}
		}
		return false;
	}


	function getDefaultPriceValue($currency = null){
		$value = $this->price_unit->defaultValue;
		if( $this->taxCode ){
			$tax = Tax::withId($this->taxCode);
			
			if( is_object($tax) ){
				
				$value = Eshop::addVatToPrice($value,$tax->percentage);
				
			}
			
		}else{
			//se il prezzo non è IVA inclusa allora la aggiungo
			if( !$this->includedVAT ){
				$value = Eshop::addVatToPrice($value);
			}
		}
		$value = Eshop::priceValue($value,$currency);	
		return $value;
	}

	function getDefaultPriceValueFormatted(){
		return ESHOP::formatMoney($this->getDefaultPriceValue());
	}
	

	/*
		function: getPrice()
		Descrizione: Restituisce l'oggetto prezzo per la specificata quantita e gruppo di acquisto

		INPUT::
			$qnt :: quantità di prodotti
			$group :: gruppo di acquisto

	*/

	

	
	function getDatatShop($id_product,$id_shop=1){
		$database = Marion::getDB();
		$sel = $database->select('*','product_shop_values',"id_product={$id_product} AND id_shop={$id_shop}");
		if( okArray($sel) ){
			return $sel[0];
		}
		return false;
	}
	
	public function getPrice($qnt=1,$group=NULL){
		
		if( $qnt == 1 & $group == NULL && isset($this->price_unit) && is_object($this->price_unit)){
			return $this->price_unit;
		}
		if( !$group ){
			if( authUser() ){
				$current_user = Marion::getUser();
				$group = $current_user->category;
			}else{
				$group = 1;
			}
		}
		
		

		if($this->hasId()){
			if( $this->hasParent() && $this->parentPrice ){ 
				$parent = $this->getParent();
				if(is_object($parent)){
					return $parent->getPrice($qnt,$group);
				}else{
					return false;
				}

			}
			
			
			$database = Marion::getDB();
			$listini = $database->select('p.id,p.dateStart,p.dateEnd,lo.name as listPriceName,l.image','(price as p join priceList as l on p.label=l.label) join priceListLocale as lo on l.id=lo.priceList',"product={$this->id} AND p.label <> 'default' AND p.label <> 'barred' and quantity <= {$qnt} AND (userCategory = {$group} OR userCategory = 0) and lo.locale='{$GLOBALS['activelocale']}' and l.active=1 order by p.quantity DESC,userCategory DESC,l.priority DESC,p.quantity DESC");
			
			
			//prendo il prezzo di default qualora non fosse stato trovato alcun prezzo di listino
			$price_default = Price::prepareQuery()
					->where('product',$this->getId())
					->where('label','default')
					->getOne();

			if( okArray($listini) ){
				$now = date('Y-m-d');
				
				foreach($listini as $k => $v){
					if( $v['dateStart'] ){
						
						if( strtotime( $v['dateStart'] ) > strtotime($now) ){
						
							unset($listini[$k]);
							continue;
						}
					}
					if( $v['dateEnd'] ){
						if( strtotime( $v['dateEnd'] ) < strtotime($now) ){
							unset($listini[$k]);
							continue;
						}
					}
				}
				
				if( okArray($listini) ){
					$listino = array_values($listini)[0];
					
					$price = Price::withId($listino['id']);
					
					
					if( is_object($price) ){
						$price->listPriceName = $listino['listPriceName'];
						$price->defaultValue = $price_default->value;
						if( $listino['image']){
							$price->image = $listino['image'];
						}
						if( $price->type == 'price' ){
							return $price;
						}else{
							$price_default = Price::prepareQuery()
								->where('product',$this->getId())
								->where('label','default')
								->getOne();
							$price->percentage = $price->value;
							$price->value = $price_default->value - $price_default->value*$price->value/100;
							return $price;
						}
					}
				}
			}
			
			
			
			
			if(	is_object($price_default)	){
				return $price_default;
			}
				
		}

		return false;

	}

	
	/*
		function: getPriceValue()
		Descrizione: Restituisce il valore del prezzo IVA inclusa

	*/
	function getPriceValue($qnt=1,$group=NULL,$currency=NULL){
		
		//prendo l'oggetto prezzo
		$price = $this->getPrice($qnt,$group);
		
		if(is_object($price)){
			$value = $price->getValue();
			
			if( $this->taxCode ){
				$tax = Tax::withId($this->taxCode);
				
				if( is_object($tax) ){
					
					$value = Eshop::addVatToPrice($value,$tax->percentage);
					
				}
				
			}else{
				//se il prezzo non è IVA inclusa allora la aggiungo
				if( !$this->includedVAT ){
					$value = Eshop::addVatToPrice($value);
				}
			}
			
			return Eshop::priceValue($value,$currency);
		}	
		
		return false;
	}
	
	/*
		function: getPriceValue()
		Descrizione: Restituisce il valore del prezzo formattato IVA inclusa

	*/
	function getPriceFormatted($qnt=1,$group=NULL){
		$price = $this->getPriceValue($qnt,$group);
		return ESHOP::formatMoney($price);
	}


		
	/*
		function: getPriceValueWithoutVAT()
		Descrizione: Restituisce il valore del prezzo IVA esclusa

	*/
	function getPriceValueWithoutVAT($qnt=1,$group=1){
		$priceVAT = 0;
		$price = $this->getPriceValue($qnt,$group);
		if( $this->taxCode ){
			$tax = Tax::withId($this->taxCode);
			if( is_object($tax) ){
				$priceVAT = Eshop::removeVatFromPrice($price,$tax->percentage);			
			}	
		}
		return $price - $priceVAT;		
	}
	
	/*
		function: getPriceFormattedWithoutVAT()
		Descrizione: Restituisce il valore del prezzo formattato IVA esclusa

	*/
	function getPriceFormattedWithoutVAT($qnt=1,$group=1){
		$price = $this->getPriceValueWithoutVAT($qnt,$group);
		return Eshop::formatMoney($price);
	}

	/*
		function: getPriceList()
		Descrizione: Restituisce il valore del prezzo di listino IVA inclusa

	*/
	function getPriceList(){
		$price_list = Price::prepareQuery()
					->where('product',$this->getId())
					->where('label','barred')
					->getOne();
			
		if(	is_object($price_list)	){
			$value = $price_list->getValue();
			if( !$this->includedVAT ){
				$value = Eshop::addVatToPrice($value);
			}
			return Eshop::priceValue($value);
		}
		return false;
	}

	/*
		function: getPriceListFormatted()
		Descrizione: Restituisce il valore del prezzo di listino formattato IVA inclusa

	*/
	function getPriceListFormatted(){
		return Eshop::formatMoney($this->getPriceList());
	}



	/*
		function: getWeigth()
		Descrizione: Restituisce il peso del prodotto

	*/
	function getWeigth(){
		if( $this->freeShipping ){
			return 0;
		}
		return $this->weight;
	}

	//prende gli attributi del prodotto
	function getAttributes(){
		return $this->_attributes;
	}

	//restituisce gli attributi sotto forma di select a partire dal locale specificato
	public function getSelectAttributes($locale=NULL){
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		if( $this->isConfigurable() ){
			if( $this->hasAttributes() ){
				$attributeSet = AttributeSet::withId($this->attributeSet);
			
				//prendo i valori possibili per gli attibuti
				if(is_object($attributeSet)){
					$select = $attributeSet->getAttributeWithValues($locale);
				}
				if( $this->hasChildren() ){
					$database = Marion::getDB();

					$figli = $database->select('id',"product","parent={$this->id}");
					
					if( okArray($figli) ){
						
						foreach($figli as $v){
							$values = $database->select('*','productAttribute',"product = {$v['id']}");
							
							foreach($values as $key => $value){
								$options[$value['attribute']][] = $value['value'];
							}
						}
						
						foreach($options as $k => $v){
							$options[$k] = array_unique($v);
						}
						foreach($select as $attr => $values){
							foreach($values as $k => $v){
								if( !in_array($k,$options[$attr]) && $k != 0){
									unset($select[$attr][$k]);
								}
							}

						}
						return $select;
					}
				}
					
			}
		}
		return false;

	}


		//restituisce gli attributi sotto forma di select a partire dal locale specificato
	public function getAttributesView($locale=NULL){
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		if( $this->isConfigurable() ){
			if($this->hasAttributes()){
				$attributeSet = AttributeSet::withId($this->attributeSet);
				
				
				$attributes_composition = $attributeSet->getAttributes();
				if( okArray($attributes_composition) ){
					foreach($attributes_composition as $k => $v){
						$attribute_object = Attribute::withId($v['attribute']);
						if( is_object($attribute_object) ){
							$name_attributes[$attribute_object->label] = $attribute_object->get('name',$locale);
						}
					}
				}
					
				
				
				//prendo i valori possibili per gli attibuti
				if(is_object($attributeSet)){
					$select = $attributeSet->getAttributeWithValuesAndImages($locale);
					if( okArray($name_attributes) ){
						if( okArray($select) ){
							foreach($select as $k => $v){
								$select[$k]['name'] = $name_attributes[$k];
							}
						}
					}
					
				}
				
				if( $this->hasChildren() ){
					$database = Marion::getDB();

					$figli = $database->select('id',"product","parent={$this->id} AND (deleted IS NULL OR deleted = 0)");

					if( okArray($figli) ){
						
						foreach($figli as $v){
							$values = $database->select('*','productAttribute',"product = {$v['id']}");
										
							foreach($values as $key => $value){
								$options[$value['attribute']][] = $value['value'];
							}
						}
						
						foreach($options as $k => $v){
							$options[$k] = array_unique($v);
						}
											
						foreach($select as $attr => $values){
							foreach($values['values'] as $k => $v){
								//debugga($k);
								if( !in_array($k,$options[$attr]) && $k != 0){
									unset($select[$attr]['values'][$k]);

								}
							}
						
						}
										
						return $select;
					}
				}
					
			}
		}
		return false;

	}

	/***************************************************** OVERRIDE METODI DELLA CLASSE Base**************************************************************/
	
	
	
	public static function checkTable(){
		$database = Marion::getDB();
		$db = $GLOBALS['setting']['default']['DATABASE']['options']['nome'];
		
		$table = STATIC::TABLE;
		$check = $database->select('*','information_schema.tables',"table_schema = '{$db}' AND table_name = '{$table}'");
		
		if(!okArray($check)){
			self::writeLog("Tabella {$table} non presente nel database {$db}");
			exit;
		}
		
		$table = STATIC::TABLE_LOCALE_DATA;
		$check = $database->select('*','information_schema.tables',"table_schema = '{$db}' AND table_name = '{$table}'");
		
		if(!okArray($check)){
			self::writeLog("Tabella {$table} non presente nel database {$db}");
			exit;
		}

		$table = 'productAttribute';
		$check = $database->select('*','information_schema.tables',"table_schema = '{$db}' AND table_name = '{$table}'");
		
		if(!okArray($check)){
			self::writeLog("Tabella {$table} non presente nel database {$db}");
			exit;
		}
	
		
	}


	//metodo che prende i figli di un oggetto
	public function getChildren($where=NULL){
		$field_id = STATIC::TABLE_PRIMARY_KEY;
		if( !$where ) $where = "1=1 AND (deleted IS NULL OR deleted = 0)";
		if($this->$field_id){
			$database = Marion::getDB();
			$data = $database->select('*',STATIC::TABLE,STATIC::PARENT_FIELD_TABLE."={$this->$field_id} AND {$where}");
			
			$toreturn = array();
			if(okArray($data)){
				foreach($data as $v){
					$toreturn[] = self::withData($v);
				}
				return $toreturn;
			}else{
				return false;
			}
			
		}else{
			return false;
		}
	}


	

		
	public function init()
	{	
		parent::init();
		$this->getAttributesInit();
		//prendo le sezioni correlate
		$this->getRelatedSections();
		$this->getOtherSections();
		$this->getTags();
		
		
	}


	public static function getParentFields(){
		$parent_fields = self::$_parent_fields;
		if( !Marion::getConfig('catalog','use_parent_sku') ){
			if(($key = array_search('sku', $parent_fields)) !== false) {
				unset($parent_fields[$key]);
			}
		}
		if( !Marion::getConfig('catalog','use_parent_ean') ){
			if(($key = array_search('ean', $parent_fields)) !== false) {
				unset($parent_fields[$key]);
			}
		}
		return $parent_fields;
	}
	

	

	public function updateTaxChildren(){
		if( !$this->parent ){
			$database = Marion::getDB();
			$database->update('product',"parent={$this->id} AND parentPrice=1",array('taxCode'=>$this->taxCode));
		}
	}

	public function afterSave(){
		
		parent::afterSave();
		
		//aggiorno i prezzi del prodotto
		if( !$this->parent ){
			Catalog::loadPrices($this->id);
		}
		//$this->saveLocaleData();
		$this->saveAttributes();
		$this->saveOtherSections();
		
		
		if($this->hasChildren()){
			//aggiorno la tassa dei figli che ereditano i prezzi dal padre
			$this->updateTaxChildren();
			$children = $this->getChildren();
			$parent_fields = self::getParentFields();
			foreach($children as $v){
				if( okArray($parent_fields) ){
					foreach($parent_fields as $key){
						$v->$key = $this->$key;
					}
				}
				$v->save();
			}
			
		}

		if( $this->_type_action == 'INSERT'){
			$this->createInventory();
		}

		$database = Marion::getDB();
		if( $this->parent ){
			$database->insert('product_search_changed',array('id_product' => $this->parent));
		}else{
			$database->insert('product_search_changed',array('id_product' => $this->id));
		}


		Marion::do_action('product_after_save',array(&$this));


		return $this;
	}

	function afterLoad(){
		
		parent::afterLoad();
		$this->price_unit = $this->getPrice();


		Marion::do_action('product_after_load',array(&$this));
		

		
	}
	

	public function beforeSave(){
		parent::beforeSave();
		if( $this->parent ){
			$parent_fileds = self::getParentFields();
			$parent = $this->getParent();
			if( is_object($parent) ){
				if( okArray($parent_fileds) ){
					foreach($parent_fileds as $key){
						$this->$key = $parent->$key;
					}
				}
			}
		}
		
	}
	
	public function checkSave(){
		
		$check = $this->checkDuplicateAttributes();
		if( $check != 1) return $check;
		return $this->checkSku();
	}

	
	//controlla se per un prodotto configurabile almeno 2 figli hanno gli stessi attributi
	function checkDuplicateAttributes(){
		
		if( $this->isConfigurable() && !$this->attributeSet){
			return true;
		}
	

		if($this->hasAttributes()){
			if($this->hasParent()){
				$query = Product::prepareQuery()->where('parent',$this->getParentId())->whereExpression("(deleted is NULL OR deleted = 0)");
				if( $this->hasId() ){
					$query->where('id',$this->getId(),'<>');
				}

				$children = $query->getCollection();
				//debugga($query->lastquery);exit;
				$duplicates = $children->findAll(function($child){
				  $cont = count(array_intersect($child->getAttributes(), $this->getAttributes()));
				 
				  if($cont == count( $this->getAttributes() ) ){
						return true;
				  }else{
						return false;
				  }
				});
				
				if($duplicates->count() > 0) return STATIC::ERROR_ATTRIBUTES_DUPLICATE;

			}

		}
		return true;

	}

	public function set($data)
	{

		parent::set($data);
		if( !$this->hasId() ){
			$this->getAttributesInit();
		}
		return $this;
	}


	public function delete(){
		if($this->id){
			$database = Marion::getDB();
			//controllo se prodotto è presente negli ordini. Nel caso sia presente lo metto in stato deleted altrimenti lo elimino
			if( $this->hasChildren() ){
				$children = $this->getChildren();
				if( okArray($children) ){
					foreach($children as $v){
						$check = Order::prepareQuery()->where('product',$v->id)->getOne();
						if( is_object($check) ){
							break;
						}
					}
				}

			}else{
				$check = Order::prepareQuery()->where('product',$this->id)->getOne();
			}
			
			//azione da effettuare quando un prodotto viene eliminato
			Marion::do_action('action_delete_product',array($this));

			if( is_object($check) ){
				$this->set(array('deleted'=>1))->save();
				if( $this->hasChildren() ){
					$children = $this->getChildren();
					foreach($children as $v){
						$v->set(array('deleted'=>1))->save();
					}

				}

			}else{
				parent::deleteChildren();
				$database->delete('productAttribute',"product={$this->id}");
				$database->delete('price',"product={$this->id}");
				$database->delete('priceValueDaily',"id_product={$this->id}");
				$database->delete('product_shop_values',"id_product={$this->id}");
				$database->delete('product_search',"id_product={$this->id}");
				$database->delete('product_search_changed',"id_product={$this->id}");
				parent::delete();
			}
			
			$database->delete('wishlist',"product={$this->id}");
			
		}

	}


	//se il prodotto è configurabile ed ha figli restituisce un array in cui le chiavi sono gli ID dei figli e i valori sono
	//gli attributi e la quantita' in magazzino

	public function getStockChildren($locale=NULL){
		
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		
		if($this->hasChildren() && $this->isConfigurable()){
			
			$attributeSet = AttributeSet::withId($this->attributeSet);
			
			if( is_object($attributeSet) ){
				$children = $this->getSortedChildrenByAttributeSet();
				
				if(okArray($children)){
					
					foreach($children as $v){
						
						$attributeChild = $v->getAttributes();
						foreach($attributeChild as $k => $value){
							$attributeValue = AttributeValue::withId($value);
							$attribute = Attribute::withLabel($k);
						
							if($attributeValue && $attribute){
								$stock[$v->getId()]['sku'] = $v->sku;
								$stock[$v->getId()]['ean'] = $v->ean;
								$stock[$v->getId()]['upc'] = $v->upc;
								$stock[$v->getId()]['weight'] = $v->weight;
								$stock[$v->getId()]['image'] = $v->images[0];
								$stock[$v->getId()]['minOrder'] = $v->minOrder;
								$stock[$v->getId()]['maxOrder'] = $v->maxOrder;
								$stock[$v->getId()]['id'] = $v->getId();
								$stock[$v->getId()]['visibility'] = $v->visibility;
								$stock[$v->getId()]['attributes'][$attribute->get('name',$locale)]=$attributeValue->get('value',$locale);
								$stock[$v->getId()]['stock']=$v->get('stock');
								
							}
						}
					}
					return $stock;

				}
			}else{
				$children = $this->getChildren();
				if(okArray($children)){
					foreach($children as $v){
						$stock[$v->getId()]['weight'] = $v->weight;
						$stock[$v->getId()]['image'] = $v->images[0];
						$stock[$v->getId()]['minOrder'] = $v->minOrder;
						$stock[$v->getId()]['maxOrder'] = $v->maxOrder;
						$stock[$v->getId()]['id'] = $v->getId();
						$stock[$v->getId()]['name'] = $v->get('name');
						$stock[$v->getId()]['stock']=$v->get('stock');
						$stock[$v->getId()]['visibility'] = $v->visibility;
					}
					return $stock;
				}
			}


		}
		return false;

	}

	public function getInventoryChildren($locale=NULL){
		
		if(!$locale){ 
			$locale = $GLOBALS['activelocale'];
			if(!$locale){ 
				$locale = STATIC::LOCALE_DEFAULT;	
			}
		}
		
		if($this->hasChildren() && $this->isConfigurable()){
			
			$attributeSet = AttributeSet::withId($this->attributeSet);
			
			if( is_object($attributeSet) ){
				$children = $this->getSortedChildrenByAttributeSet();
				
				if(okArray($children)){
					
					foreach($children as $v){
						$qnt = $v->getInventory();
						$attributeChild = $v->getAttributes();
						foreach($attributeChild as $k => $value){
							$attributeValue = AttributeValue::withId($value);
							$attribute = Attribute::withLabel($k);
						
							if($attributeValue && $attribute){
								$stock[$v->getId()]['sku'] = $v->sku;
								$stock[$v->getId()]['ean'] = $v->ean;
								$stock[$v->getId()]['upc'] = $v->upc;
								$stock[$v->getId()]['weight'] = $v->weight;
								$stock[$v->getId()]['image'] = $v->images[0];
								$stock[$v->getId()]['minOrder'] = $v->minOrder;
								$stock[$v->getId()]['maxOrder'] = $v->maxOrder;
								$stock[$v->getId()]['id'] = $v->getId();
								$stock[$v->getId()]['visibility'] = $v->visibility;
								$stock[$v->getId()]['attributes'][$attribute->get('name',$locale)]=$attributeValue->get('value',$locale);
								$stock[$v->getId()]['stock']=$qnt;
								
							}
						}
					}
					return $stock;

				}
			}else{
				$children = $this->getChildren();
				if(okArray($children)){
					foreach($children as $v){
						$qnt = $v->getInventory();
						$stock[$v->getId()]['weight'] = $v->weight;
						$stock[$v->getId()]['image'] = $v->images[0];
						$stock[$v->getId()]['minOrder'] = $v->minOrder;
						$stock[$v->getId()]['maxOrder'] = $v->maxOrder;
						$stock[$v->getId()]['id'] = $v->getId();
						$stock[$v->getId()]['name'] = $v->get('name');
						$stock[$v->getId()]['stock']=$qnt;
						$stock[$v->getId()]['visibility'] = $v->visibility;
					}
					return $stock;
				}
			}


		}
		return false;

	}


	//ordina i figli a partire dall'ordine dell'insieme di attributi
	function getSortedChildrenByAttributeSet(){
			if($this->hasChildren() && $this->isConfigurable()){
				$children = $this->getChildren();
					if(okArray($children)){
						
						//prendo l'insieme di attributi fissato per questo prodotto
						$attributeSet =  AttributeSet::withId($this->attributeSet);
						
						//prendo gli attributi con i valori
						$attributes = $attributeSet->getAttributes();
						if(okArray($attributes)){
							foreach($attributes as $v){
								$attribute = Attribute::withId($v['attribute']);
								if($attribute){
									$values = $attribute->getValues();

									if(okArray($values)){
										foreach($values as $v){
											$order[$attribute->getLabel()][$v->getId()] = $v->get('orderView');
										}
									}
								}
							}
						}
						foreach($children as $v){
							$children_tmp1[$v->getId()] = $v;
							$attributes = $v->getAttributes();
							if(okArray($attributes)){
								foreach($attributes as $k => $attr){
									$children_tmp2[$v->getId()][$k] = $order[$k][$attr];
								}
								$children_tmp2[$v->getId()]['_id'] = $v->getId();
							}

						}
						array_multisort($children_tmp2, SORT_ASC);
						
						foreach($children_tmp2 as $v){
							$toreturn[] = $children_tmp1[$v['_id']];
						}
						return $toreturn;

					}
			}
			return false;

	}

	//prende il figlio di un prodotto configurabile a partire dai suoi attributi passati sottoforma di key => value
	function getChildWithAttributes($data=array()){
			$children = $this->getChildrendWithAttributes($data);
			if(okArray($children)){
				return $children[0];
			}
			return false;

	}

	//prende i figli  di un prodotto configurabile a partire dagli attributi passati sottoforma di key => value
	function getChildrendWithAttributes($data=array()){
			if($this->isConfigurable() && $this->hasChildren()){
				$database = Marion::getDB();
				$whereCondiction ='';
				$attributes = $this->getAttributes();
				foreach($data as $k => $v){
					if( array_key_exists($k,$attributes)){
						$whereCondiction .= "id in (select product from productAttribute where attribute='{$k}' AND value={$v}) AND ";
					}
				}
				$whereCondiction = preg_replace('/AND $/','',$whereCondiction);
				
				$query = Product::prepareQuery()
						->where('parent',$this->id)
						->where('deleted',0)
						->whereExpression($whereCondiction);
				$product = $query->get();
			
				return $product;
				
			}
			return false;

	}
	

	/************************* GESTIONE PRODOTTI CORRELATI *****************************************/
	

	//imposta le sezioni correlate
	public function setRelatedSections($array){
		
		$this->relatedSections = $array;
	}

	//salva le sezioni correlati
	public function saveRelatedSections(){
		
		if( $this->hasId() ){
			$database = Marion::getDB();
			$database->delete('productRelatedSection',"product={$this->id}");
			$database->delete('productRelated',"product={$this->id}");
			
			if( okArray($this->relatedSections) ){
				foreach($this->relatedSections as $k => $v){
					if( okArray($v['products']) ){
						$products = $v['products'];
						unset($v['products']);
					}
					
					$v['product'] = $this->id;
					$res = $database->insert('productRelatedSection',$v);
					
					if( !$res ){
						unset($this->relatedSections[$k]);
					}else{
						
						if( okArray($products) ){
							foreach($products as $v1){
								$toinsert = array(
									'product' => $this->id,
									'related' => $v1,
									'section' => $v['section']
								);
								
								$res2 = $database->insert('productRelated',$toinsert);
								
							}
						}
					}	




					

				}
				
				
				//debugga($database->lastquery);exit;
			}
			
		}
		//exit;
	}

	//prende le sezioni correlate dal database
	public function getRelatedSections(){
		if( $this->hasId() ){
			$database = Marion::getDB();
			$sections = $database->select('*','productRelatedSection',"product={$this->id}");
			
			if( okArray($sections) ){
				foreach($sections as $v){
					if( $v['type'] == 'specific' ){
						$products = $database->select('related','productRelated',"product={$this->id} and section={$v['section']}");
						if( okArray($products) ){
							foreach($products as $v1){
								$v['products'][] = $v1['related'];
							}
						}
						//$v['products'] = unserialize($v['products']);
					}
					$this->relatedSections[] = $v;
				}
			}
		}
	}

	public function hasRelatedProducts(){
		if( $this->hasId() ){
			return okArray( $this->relatedSections);
		}

		return false;

	}


	public function getRelatedProducts(){
		if( $this->hasId() ){
			$database = Marion::getDB();
			$sections = $this->relatedSections;
			$products = array();
			if( okArray($sections) ){
				$ids = array();
				foreach($sections as $v){
					if( $v['type'] == 'specific' ){
						$_products = $database->select('related','productRelated',"product={$this->id} and section={$v['section']}"); 
						if( okArray($_products) ){
							$ids_add = array();
							foreach($_products as $v1){
								$ids_add[] = $v1['related'];
							}
							if( okArray($ids) ){
								$ids = array_merge($ids,$ids_add);
							}else{
								$ids = $ids_add;
							}
						}
					}else{
						if( !$v['num_products'] ) $v['num_products'] = 1;
						$query = self::prepareQuery()->where('visibility',1)->where('parent',0)->where('section',$v['section'])->where('deleted',0)->orderBy('rand()')->limit($v['num_products']);
						$products_random = $query->get();
						//debugga($query->lastquery);exit;
						if( okArray($products_random) ){
							if( okArray($products) ){
								$products = array_merge($products,$products_random);
							}else{
								$products = $products_random;
							}
						}
					}
				}
				if( okArray($ids) ){
					$where = '(id in (';
					foreach($ids as $id){
						$where .= "{$id}, ";
					}
					$where = preg_replace('/\, $/','))',$where);
					$products_from_id = self::prepareQuery()->where('visibility',1)->whereExpression($where)->get();
					if( okArray($products_from_id) ){
						if( okArray($products) ){
							$products = array_merge($products,$products_from_id);
						}else{
							$products = $products_from_id;
						}
					}
				}
			}
			return $products;
		}
	}

	/************************* FINE GESTIONE PRODOTTI CORRELATI *****************************************/

/***************************************************** OVERRIDE METODI DELLA CLASSE BaseWithImages**************************************************************/
 
 //restituisce l'immagine all'indice specificato del formato specificato
	function getUrlImage($index=0,$type='original',$watermark=true,$name_image=NULL){
		//if( !$name_image ) $name_image = $this->get('name');
		if( $this->hasImages()){
			$url = parent::getUrlImage($index,$type,$watermark,$name_image);
			
			return $url;
		}else{
			
			$parent = $this->getParent();
			
			if(is_object($parent) && $parent->hasImages() ){
				$url = $parent->getUrlImage($index,$type,$watermark,$name_image);
				return $url;
			}
		}
		return false;
		
	}





	//metodo che restituisce il percorso di un prodotto
	function breadCrumbs($template_html=NULL){
		
		
		$options_default = array(
			"before_html" => "<span>",
			"after_html" => "</span>",
			"divider_html" => " > ",

		);

		/*foreach($options as $k => $v){
			if( $options_default[$k] ){
				$options_default[$k] = $v;
			}
		}*/
		if( $this->section ){
			
			$section = Section::withId($this->section);
			if (is_object($section)) {
				$list[] = array(
						'name' => $section->get('name'),
						'id' => $section->id,
						'parent' => $section->parent,
						'url' => $section->getUrl()
					);
				while( $section->parent ){
					$section = $section->getParent();
					$list[] = array(
						'name' => $section->get('name'),
						'id' => $section->id,
						'parent' => $section->parent,
						'url' => $section->getUrl()
					);

				}
				
				krsort($list);
				$list = array_values($list);
				$list[0]['first'] = 1;
				$list[count($list)-1]['last'] = 1;
				if( !$template_html ){
				$breadCrumbs = '';
				foreach($list as $v){
						$breadCrumbs .= $options_default['before_html'].$v['name'].$options_default['after_html'].$options_default['divider_html'];
					}
					$divider_html = $options_default['divider_html'];
					
					$breadCrumbs = preg_replace("/{$divider_html}$/",'',$breadCrumbs);
				}else{
					
					$template = _obj('Template');
					$template->breadCrumbs_list = $list; 
					
					ob_start();
					$template->output($template_html);
					$breadCrumbs = ob_get_contents();
					ob_end_clean();
					
				}
				
				return $breadCrumbs;
			} else {
				return false;
			}
		}
		return false;
	}


	/************************* WISHLIST ****************************************/
	function addToWishlist(){
		if( authUser() && $this->id){
			if( !$this->isInWhishlist() ){
				$user = Marion::getUser();
				
				$database = Marion::getDB();
				$toinsert = array(
					'product' => $this->id,
					'user' => $user->id
				);
				$database->insert('wishlist',$toinsert);
				return true;
			}
		}
		return false;
	}

	function removeFromWishlist(){
		if( authUser() && $this->id){
			
			if( $this->isInWhishlist() ){
				$user = Marion::getUser();
				$database = Marion::getDB();
				
				$database->delete('wishlist',"product={$this->id} AND user ={$user->id}");
				return true;
			}
		}
		return false;
	}

	function isInWhishlist(){
		if( authUser() && $this->id){
			$user = Marion::getUser();
			$database = Marion::getDB();
			$check = $database->select('*',"wishlist","product={$this->id} AND user ={$user->id}");
			return okArray($check);
		}
		return false;
	}

	/************************* WISHLIST ****************************************/


	public static function registerAdminTab($string=''){
		self::$_registred_classes[] = $string;
	}
}









?>