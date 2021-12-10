<?php
class ga_ecommerce_tracking
{
	
	public $debug        = false;
	public $ga_query_str = '';
	public $log          = array();
	public $ga_params    = array();
		
	// set some base values
	public function __construct( $ga_id_PARAM, $page_title_PARAM, $debug_PARAM ) {    
		
		$this->ga_id      = $ga_id_PARAM;
		$this->page_title = $page_title_PARAM;
		$this->debug      = $debug_PARAM;
		$this->ga_url     = $this->debug ? 'https://ssl.google-analytics.com/debug/collect' : 'https://www.google-analytics.com/collect';
		
		$this->ga_params['tid'] = $this->ga_id;
		$this->ga_params['v']   = '1';
		$this->ga_params['t']   = 'event';
		$this->ga_params['aip'] = '1';
		$this->ga_params['ds']  = 'web';
		$this->ga_params['qt']  = 0;
		$this->ga_params['cid'] = $this->generate_cid();
		$this->ga_params['uip'] = $_SERVER['REMOTE_ADDR'];
		$this->ga_params['ua']  = urlencode($_SERVER['HTTP_USER_AGENT']);
		$this->ga_params['dh']  = $_SERVER['HTTP_HOST'];
		$this->ga_params['dp']  = $_SERVER['REQUEST_URI'];
		$this->ga_params['dt']  = urlencode($this->page_title);
		
  } // end __construct()
	
	// returns correctly formatted 'cid' - a required ga paramter
	private function generate_cid(){	
		
		$data = openssl_random_pseudo_bytes(16);		
		assert(strlen($data) == 16);	
		$data[6] = chr(ord($data[6]) & 0x0f | 0x40); //set version to 0100
		$data[8] = chr(ord($data[8]) & 0x3f | 0x80); //set bits 6-7 to 10	
		return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
	
	} // end generate_cid()
	
	// post data to ga
	public function send_hit( $order_arr_PARAM ){		
		
		// order and products
		foreach( $order_arr_PARAM as $key=>$value ){
			$this->ga_params[$key] = $value;
		}
		
		// build url	
		foreach( $this->ga_params as $key => $value ){
			$this->ga_query_str.= '&'.$key.'='.urlencode($value);		
		}
		
		$this->ga_query_str = ltrim($this->ga_query_str, '&');		
			
		$ch = curl_init();
		curl_setopt_array($ch, array(
			CURLOPT_RETURNTRANSFER => 1,
			CURLOPT_URL            => $this->ga_url.'?'.$this->ga_query_str,		
			CURLOPT_POST           => 1			
		));
		
		$response = '';
		if(
			( ($response = curl_exec($ch)) === false ) &&
			( $this->debug )
		){  				
			$this->log['curl_error'] = curl_error($ch);				
		}
		
		if( $this->debug ){
			$this->log['curl_response'] = print_r( $response, true );
			$this->log['ga_params']     = print_r( $this->ga_params, true );
			$this->log['ga_url']        = print_r( $this->ga_url, true );
			$this->log['ga_query_str']  = print_r( $this->ga_query_str, true );
			
			echo '<pre>';
			print_r($this->log);
			echo '</pre>';
		}
			
		curl_close($ch);
				
	} // end send_hit()
	
} // end ga_ecommerce_tracking
?>