<?php
class Outlet extends Tracciato{
	public $fields = array(
		'C01-EAN' => array(
			'name' => 'EAN',
		),
		'C03-COM_REF' => array(
			'name' => 'Riferimento',
			'required' => 1
		),
		'C04-PHYS_REF' => array(
			'name' => 'Riferimento',
			'ignore' => 1
		),
		'C05-GLOBAL_BRAND' => array(
			'name' => 'Brand',
		),
		'C06-COLOR' => array(
			'name' => 'Colore',
			'function_values' => 'getColors'
		),
		'C07-NAME' => array(
			'name' => 'Nome',
		),
		'C08-DESCRIPTION' => array(
			'name' => 'Descrizione',
		),
		'C09-COMPOSITION_MATERIAL' => array(
			'name' => 'Composizione Materiale',
		),
		'C10-PRODUCT_DIMENSIONS' => array(
			'name' => 'Dimensioni',
		),
		'C16-PRODUCT_TEMPLATE' => array(
			'name' => 'Template',
			'fixed' => 1, //stabilisce che questo campo deve assumere un valore fisso
			'function_values' => 'getCategories',
			'required' => 1
		),
		'C17-GENDER' => array(
			'name' => 'Genere',
			'function_values' => 'getGenders'
		),
		'C18-SIZE' => array(
			'name' => 'Taglia',
		),
		'C19-SEC_ATT' => array(
			'name' => 'Tipologia',
			//'ignore' => 1,
			'fixed' => '1',
			'function_values' => 'getSecAtt'
		),
		'C20-WEB_SIZE' => array(
			'name' => 'Taglia',
			'ignore' => 1,
		),
		'C21-RRP_WO_VAT' => array(
			'name' => 'Prezzo senza IVA',
		),
		'C21-RRP_WITH_VAT' => array(
			'name' => 'Prezzo con IVA',
		),
		'C22-IMAGE_1' => array(
			'name' => 'Immagine 1',
		),
		'C23-IMAGE_2' => array(
			'name' => 'Immagine 2',
		),
		'C24-IMAGE_3' => array(
			'name' => 'Immagine 3',
		),
		'C25-IMAGE_4' => array(
			'name' => 'Immagine 4',
		),
		'C26-IMAGE_5' => array(
			'name' => 'Immagine 5',
		),
		'special price' => array(
			'name' => 'Special price',
			
		),
	);


		function getGenders($lang){
			
			$path = '../modules/privalia/values/'.$lang."/genders.txt";
			$dati = array();
			$handle = fopen($path, "r");
			if ($handle) {
				while (($line = fgets($handle)) !== false) {
					$dati[trim($line)] = trim($line);
				}

				fclose($handle);
			} else {
				// error opening the file.
			} 
			return $dati;
		}

		function getSeasons($lang){
			
			$path = '../modules/privalia/values/'.$lang."/seasons.txt";
			$dati = array();
			$handle = fopen($path, "r");
			if ($handle) {
				while (($line = fgets($handle)) !== false) {
					$dati[trim($line)] = trim($line);
				}

				fclose($handle);
			} else {
				// error opening the file.
			} 
			return $dati;
		}

		function getColors($lang){

			
			$path = '../modules/privalia/values/'.$lang."/colors.txt";
			$dati = array();
			$handle = fopen($path, "r");
			if ($handle) {
				while (($line = fgets($handle)) !== false) {
					$dati[trim($line)] = trim($line);
				}

				fclose($handle);
			} else {
				// error opening the file.
			} 
			return $dati;

		}
		
		function getCategories($lang){

			
			$path = '../modules/privalia/values/'.$lang."/categories.txt";
			$dati = array();
			$handle = fopen($path, "r");
			if ($handle) {
				while (($line = fgets($handle)) !== false) {
					$dati[trim($line)] = trim($line);
				}

				fclose($handle);
			} else {
				// error opening the file.
			} 
			
			return $dati;

		}

		function getSecAtt(){
			return array(
				'UNICO' => 'UNICO',
				'TALLA' => 'TALLA',
			);
		}
	
}

?>