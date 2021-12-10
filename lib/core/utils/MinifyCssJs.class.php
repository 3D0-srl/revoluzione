<?php

abstract class MinificationSequenceFinder
{
	public $start_idx;
	public $end_idx;
	public $type;
	
	abstract protected function findFirstValue($string);
	
	public function isValid(){
		return $this->start_idx !== false;
	}
}

class StringSequenceFinder extends MinificationSequenceFinder
{
	protected $start_delimiter;
	protected $end_delimiter;
	function __construct($start_delimiter, $end_delimiter){
		$this->type = $start_delimiter;
		$this->start_delimiter = $start_delimiter;
		$this->end_delimiter = $end_delimiter;
	}
	public function findFirstValue($string){
		$this->start_idx = strpos($string, $this->start_delimiter);
		if ($this->isValid()){
			$this->end_idx = strpos($string, $this->end_delimiter, $this->start_idx+1);
			// sanity check for non well formed lines
			$this->end_idx = ($this->end_idx === false ? strlen($string) : $this->end_idx + strlen($this->end_delimiter));
		}
	}
}

class QuoteSequenceFinder extends MinificationSequenceFinder
{
	function __construct($type){
		$this->type = $type;
		//debugga($this);exit;
	}
	public function findFirstValue($string){
		$this->start_idx = strpos($string, $this->type);
		if ($this->isValid()){
			// look for first non escaped endquote
			$this->end_idx = $this->start_idx+1;
			while ($this->end_idx < strlen($string)){
				// find number of escapes before endquote
				if (preg_match('/(\\\\*)(' . preg_quote($this->type) . ')/', $string, $match, PREG_OFFSET_CAPTURE, $this->end_idx)){
					$this->end_idx = $match[2][1] + 1;
					// if odd number of escapes before endquote, endquote is escaped. Keep going
					if (!isset($match[1][0]) || strlen($match[1][0]) % 2 == 0){
						return;
					}
				}else{// no match, not well formed
					$this->end_idx = strlen($string);
					return;
				}
			}
		}
	}
}




class MinifyCss{
	public $minificationStore;
	public $singleQuoteSequenceFinder;
	public $doubleQuoteSequenceFinder;
	public $blockCommentFinder;
	
	
	function __construct(){
		$this->minificationStore = array();
		$this->singleQuoteSequenceFinder = new QuoteSequenceFinder('\'');
		$this->doubleQuoteSequenceFinder = new QuoteSequenceFinder('"');
		$this->blockCommentFinder = new StringSequenceFinder('/*', '*/');
	}
	
	function getNextMinificationPlaceholder(){
		return '<-!!-' . sizeof($this->minificationStore) . '-!!->';
	}
	
	function getNextSpecialSequence($string, $sequences){
		// $special_idx is an array of the nearest index for all special characters
		$special_idx = array();
		foreach ($sequences as $finder){
			$finder->findFirstValue($string);
			if ($finder->isValid()){
				$special_idx[$finder->start_idx] = $finder;
			}
		}
		// if none found, return
		if (count($special_idx) == 0){return false;}
		// get first occuring item
		asort($special_idx);
		return $special_idx[min(array_keys($special_idx))];
	}

	function minifyCSS($css){
		//global $minificationStore, $singleQuoteSequenceFinder, $doubleQuoteSequenceFinder, $blockCommentFinder;
		$css_special_chars = array($this->blockCommentFinder, // CSS Comment
		$this->singleQuoteSequenceFinder, // single quote escape, e.g. :before{ content: '-';}
		$this->doubleQuoteSequenceFinder); // double quote
		// pull out everything that needs to be pulled out and saved
		while ($sequence = $this->getNextSpecialSequence($css, $css_special_chars)){
			switch ($sequence->type){
				case '/*': // remove comments
					$css = substr($css, 0, $sequence->start_idx) . substr($css, $sequence->end_idx);
				break;
				default: // strings that need to be preservered
					$placeholder = $this->getNextMinificationPlaceholder();
					$this->minificationStore[$placeholder] = substr($css, $sequence->start_idx, $sequence->end_idx - $sequence->start_idx);
					$css = substr($css, 0, $sequence->start_idx) . $placeholder . substr($css, $sequence->end_idx);
				}
		}
		// minimize the string
		$css = preg_replace('/\s{2,}/s', ' ', $css);
		$css = preg_replace('/\s*([:;{}])\s*/', '$1', $css);
		$css = preg_replace('/;}/', '}', $css);
		// put back the preserved strings
		foreach($this->minificationStore as $placeholder => $original){
			$css = str_replace($placeholder, $original, $css);
		}
		return trim($css);
	}


}


class RegexSequenceFinder extends MinificationSequenceFinder
{
	protected $regex;
	public $full_match;
	public $sub_match;
	public $sub_start_idx;
	function __construct($type, $regex){
		$this->type = $type;
		$this->regex = $regex;
	}
	public function findFirstValue($string){
		$this->start_idx = false; // reset
		preg_match($this->regex, $string, $matches, PREG_OFFSET_CAPTURE);
		if (count($matches) > 0){
		// full match
			$this->full_match = $matches[0][0];
			$this->start_idx = $matches[0][1];
			if (count($matches) > 1){
				// substart
				$this->sub_match = $matches[1][0];
				$this->sub_start_idx = $matches[1][1];
			}
			$this->end_idx = $this->start_idx + strlen($this->full_match);
		}
	}
}


class MinifyJs{
	
	public $minificationStore;
	public $singleQuoteSequenceFinder;
	public $doubleQuoteSequenceFinder;
	public $blockCommentFinder;
	public $lineCommentFinder;
	

	function __construct(){
		$this->minificationStore = array();
		$this->lineCommentFinder = new StringSequenceFinder('//', "\n");
		$this->singleQuoteSequenceFinder = new QuoteSequenceFinder('\'');
		$this->doubleQuoteSequenceFinder = new QuoteSequenceFinder('"');
		$this->blockCommentFinder = new StringSequenceFinder('/*', '*/');
	}

	function getNextMinificationPlaceholder(){
		return '<-!!-' . sizeof($this->minificationStore) . '-!!->';
	}
	

	function getNextSpecialSequence($string, $sequences){
		// $special_idx is an array of the nearest index for all special characters
		$special_idx = array();
		
		foreach ($sequences as $finder){
			$finder->findFirstValue($string);
			if ($finder->isValid()){
				$special_idx[$finder->start_idx] = $finder;
			}
		}
		// if none found, return
		if (count($special_idx) == 0){return false;}
		// get first occuring item
		asort($special_idx);
		return $special_idx[min(array_keys($special_idx))];
	}

	function minimizeJavascriptSimple($javascript){
		return preg_replace(array("/\s+\n/", "/\n\s+/", "/ +/"), array("\n", "\n ", " "), $javascript);
	}
	
	function minifyJavascript($javascript){
		$java_special_chars = 
			array(
				$this->blockCommentFinder, // JavaScript Block Comment
				$this->lineCommentFinder, // JavaScript Line Comment
				$this->singleQuoteSequenceFinder, // single quote escape, e.g. :before{ content: '-';}
				$this->doubleQuoteSequenceFinder, // double quote
				new RegexSequenceFinder( 'regex', "/\(\h*(\/[\k\S]+\/)/") // JavaScript regex expression
			);
		//debugga($java_special_chars);exit;
		// pull out everything that needs to be pulled out and saved
		while ($sequence = $this->getNextSpecialSequence($javascript, $java_special_chars)){
			switch ($sequence->type){
				case '/*':
				case '//': // remove comments
					$javascript = substr($javascript, 0, $sequence->start_idx) . substr($javascript, $sequence->end_idx);
					break;
				default: // quoted strings or regex that need to be preservered
					$start_idx = ($sequence->type == 'regex' ? $sequence->sub_start_idx: $sequence->start_idx);
					$end_idx = ($sequence->type == 'regex' ? $sequence->sub_start_idx + strlen($sequence->sub_match): $sequence->end_idx);
					$placeholder = $this->getNextMinificationPlaceholder();
					$this->minificationStore[$placeholder] = substr($javascript, $start_idx, $end_idx - $start_idx);
					$javascript = substr($javascript, 0, $start_idx) . $placeholder . substr($javascript, $end_idx);
					break;
			}
		}
		// special case where the + indicates treating variable as numeric, e.g. a = b + +c
		$javascript = preg_replace('/([-\+])\s+\+([^\s;]*)/', '$1 (+$2)', $javascript);
		// condense spaces
		$javascript = preg_replace("/\s*\n\s*/", "\n", $javascript); // spaces around newlines
		$javascript = preg_replace("/\h+/", " ", $javascript); // \h+ horizontal white space
		// remove unnecessary horizontal spaces around non variables (alphanumerics, underscore, dollar sign)
		$javascript = preg_replace("/\h([^A-Za-z0-9\_\$])/", '$1', $javascript);
		$javascript = preg_replace("/([^A-Za-z0-9\_\$])\h/", '$1', $javascript);
		// remove unnecessary spaces around brackets and parentheses
		$javascript = preg_replace("/\s?([\(\[{])\s?/", '$1', $javascript);
		$javascript = preg_replace("/\s([\)\]}])/", '$1', $javascript);
		// remove unnecessary spaces around operators that don't need any spaces (specifically newlines)
		$javascript = preg_replace("/\s?([\.=:\-+,])\s?/", '$1', $javascript);
		// unnecessary characters
		$javascript = preg_replace("/;\n/", ";", $javascript); // semicolon before newline
		$javascript = preg_replace('/;}/', '}', $javascript); // semicolon before end bracket
		// put back the preserved strings
		foreach($this->minificationStore as $placeholder => $original){
			$javascript = str_replace($placeholder, $original, $javascript);
		}
		return trim($javascript);
	}


}



?>