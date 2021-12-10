<?php
   
    //$json = new HTML_AJAX_JSON;
    class MailUpException extends Exception {
        
        var $statusCode;
        
        function __construct($inStatusCode, $inMessage) {
            parent::__construct($inMessage);
            $this->statusCode = $inStatusCode;
        }
        
        function getStatusCode() {
            return $this->statusCode;
        }
        
        function setStatusCode($inStatusCode) {
            $this->statusCode = $inStatusCode;
        }
    }

    class MailUpClient {
        
        var $logonEndpoint;
        var $authorizationEndpoint;
        var $tokenEndpoint;
        var $consoleEndpoint;
        var $mailstatisticsEndpoint;
        
        var $clientId;
        var $clientSecret;
        var $callbackUri;
        var $accessToken;
        var $refreshToken;
        
        function getLogonEndpoint() {
            return $this->logonEndpoint;
        }
        
        function setLogonEndpoint($inLogonEndpoint) {
            $this->logonEndpoint = $inLogonEndpoint;
        }
        
        function getAuthorizationEndpoint() {
            return $this->authorizationEndpoint;
        }
        
        function setAuthorizationEndpoint($inAuthorizationEndpoint) {
            $this->authorizationEndpoint = $inAuthorizationEndpoint;
        }
        
        function getTokenEndpoint() {
            return $this->tokenEndpoint;
        }
        
        function setTokenEndpoint($inTokenEndpoint) {
            $this->tokenEndpoint = $inTokenEndpoint;
        }
        
        function getConsoleEndpoint() {
            return $this->consoleEndpoint;
        }
        
        function setConsoleEndpoint($inConsoleEndpoint) {
            $this->consoleEndpoint = $inConsoleEndpoint;
        }
        
        function getMailstatisticsEndpoint() {
            return $this->mailstatisticsEndpoint;
        }
        
        function setMailstatisticsEndpoint($inMailstatisticsEndpoint) {
            $this->mailstatisticsEndpoint = $inMailstatisticsEndpoint;
        }
        
        function getClientId() {
            return $this->clientId;
        }
        
        function setClientId($inClientId) {
            $this->clientId = $inClientId;
        }
        
        function getClientSecret() {
            return $this->clientSecret;
        }
        
        function setClientSecret($inClientSecret) {
            $this->clientSecret = $inClientSecret;
        }
        
        function getCallbackUri() {
            return $this->callbackUri;
        }
        
        function setCallbackUri($inCallbackUri) {
            $this->callbackUri = $inCallbackUri;
        }
        
        function getAccessToken() {
            return $this->accessToken;
        }
        
        function setAccessToken($inAccessToken) {
            $this->accessToken = $inAccessToken;
        }
        
        function getRefreshToken() {
            return $this->refreshToken;
        }
        
        function setRefreshToken($inRefreshToken) {
            $this->refreshToken = $inRefreshToken;
        }
        
        function MailUpClient($inClientId, $inClientSecret, $inCallbackUri) {
            $this->logonEndpoint = "https://services.mailup.com/Authorization/OAuth/LogOn";
            $this->authorizationEndpoint = "https://services.mailup.com/Authorization/OAuth/Authorization";
            $this->tokenEndpoint = "https://services.mailup.com/Authorization/OAuth/Token";
            $this->consoleEndpoint = "https://services.mailup.com/API/v1.1/Rest/ConsoleService.svc";
            $this->mailstatisticsEndpoint = "https://services.mailup.com/API/v1.1/Rest/MailStatisticsService.svc";
            
            $this->clientId = $inClientId;
            $this->clientSecret = $inClientSecret;
            $this->callbackUri = $inCallbackUri;
            $this->loadToken();
        }
        
        function getLogOnUri() {
            $url = $this->getLogonEndpoint() . "?client_id=" . $this->getClientId() . "&client_secret=" . $this->getClientSecret() . "&response_type=code&redirect_uri=" . $this->getCallbackUri();
            return $url;
        }
        
        function logOn() {
            $url = $this->getLogOnUri();
            header("Location: " . $url);
        }
        function logOnWithPassword($username, $password) {
        	return $this->retreiveAccessToken($username, $password);
		}
        function retreiveAccessTokenWithCode($code) {
            $url = $this->getTokenEndpoint() . "?code=" . $code . "&grant_type=authorization_code";
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            $result = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            if ($code != 200 && $code != 302) throw new MailUpException($code, "Authorization error");
            
            $result = json_decode($result);
            
            $this->accessToken = $result->access_token;
            $this->refreshToken = $result->refresh_token;
            
            $this->saveToken();
            
            return $this->accessToken;
        }
        
        function retreiveAccessToken($login, $password) {
            $url = $this->getTokenEndpoint();
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_POST, 1);

			$body = "grant_type=password&username=".$login."&password=".$password."&client_id=".$this->clientId."&client_secret=".$this->clientSecret;
		
			$headers = array();
			$headers["Content-length"] = strlen($body);
			$headers["Accept"] = "application/json";
			$headers["Authorization"] = "Basic ".base64_encode($this->clientId.":".$this->clientSecret);
			
			curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
			
			$result = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            if ($code != 200 && $code != 302) throw new MailUpException($code, "Authorization error");
            
            $result = json_decode($result);
            
            $this->accessToken = $result->access_token;
            $this->refreshToken = $result->refresh_token;
            
            $this->saveToken();
            
            return $this->accessToken;
        }
        
        function refreshAccessToken() {
            $url = $this->getTokenEndpoint();
            $body = "client_id=" . $this->clientId . "&client_secret=" . $this->clientSecret . "&refresh_token=" . $this->refreshToken . "&grant_type=refresh_token";
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            curl_setopt($curl, CURLOPT_POST, true);
            curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: application/x-www-form-urlencoded", "Content-length: " . strlen($body)));
            $result = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            curl_close($curl);
            
            if ($code != 200 && $code != 302) throw new MailUpException($code, "Authorization error");
            
            $result = json_decode($result);
            
            $this->accessToken = $result->access_token;
            $this->refreshToken = $result->refresh_token;
            
            $this->saveToken();
            
            return $this->accessToken;
        }
        
        function callMethod($url, $verb, $body = "", $contentType = "JSON", $refresh = true) {
            $temp = null;
            $cType = ($contentType == "XML" ? "application/xml" : "application/json");
            
            $curl = curl_init();
            curl_setopt($curl, CURLOPT_URL, $url);
            curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
            if ($verb == "POST") {
                curl_setopt($curl, CURLOPT_POST, 1);
                curl_setopt($curl, CURLOPT_POSTFIELDS, $body);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: " . $cType, "Content-length: " . strlen($body), "Accept: " . $cType, "Authorization: Bearer " . $this->accessToken));
            } else if ($verb == "PUT") {
                curl_setopt($curl, CURLOPT_PUT, 1);
                $temp = tmpfile();
                fwrite($temp, $body);
                fseek($temp, 0);
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: " . $cType, "Content-length: " . strlen($body), "Accept: " . $cType, "Authorization: Bearer " . $this->accessToken));
                curl_setopt($curl, CURLOPT_INFILE, $temp);
                curl_setopt($curl, CURLOPT_INFILESIZE, strlen($body));
            } else if ($verb == "DELETE") {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "DELETE");
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: " . $cType, "Content-length: 0", "Accept: " . $cType, "Authorization: Bearer " . $this->accessToken));
            } else {
                curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-type: " . $cType, "Content-length: 0", "Accept: " . $cType, "Authorization: Bearer " . $this->accessToken));
            }
            
            $result = curl_exec($curl);
            $code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            
            if ($temp != null) fclose($temp);
            curl_close($curl);
            
            if ($code == 401 && $refresh == true) {
                $this->refreshAccessToken();
                return $this->callMethod($url, $verb, $body, $contentType, false);
            } else if ($code == 401 && $refresh == false) throw new MailUpException($code, "Authorization error");
             else if ($code != 200 && $code != 302) throw new MailUpException($code, "Unknown error");
            
            return $result;
        }
        
        function loadToken() {
            if (isset($_COOKIE["access_token"])) $this->accessToken = $_COOKIE["access_token"];
            if (isset($_COOKIE["refresh_token"])) $this->refreshToken = $_COOKIE["refresh_token"];
        }
        
        function saveToken() {
            setcookie("access_token", $this->accessToken, time()+60*60*24*30);
            setcookie("refresh_token", $this->refreshToken, time()+60*60*24*30);
        }
        /**	
	        Funzione che importa i dati su una lista/gruppo di MailUp:
	        INPUT:
	                id: identificativo della lista o del gruppo su cui esportare i dati
	                tipo: specifica se e' una lista oppure un gruppo
	                dati: dati da esportare
	        OUTPUT:
	                result: stato dell'importazione su MailUp
        **/
        function addRecipients($id,$tipo,$dati){
                
            //DETERMINAZIONE DELL'URL A CUI INVIARE LA RICHIESTA
            if($tipo == 'gruppo'){
                    $url = "https://services.mailup.com/API/v1.1/Rest/ConsoleService.svc/Console/Group/{$id}/Recipients";
            }elseif($tipo == 'lista'){
                    $url = "https://services.mailup.com/API/v1.1/Rest/ConsoleService.svc/Console/List/{$id}/Recipients";
            }else{
                    return false;
            }
            
            //ELABORAZIONE DEI DATI NEL FORMATO RICHIESTO
            $to_mailup = array();
            foreach($dati as $k => $v){
            	if($v['email']){
                    $to_mailup[$k]['Email']= $v['email'];
                    if($v['nome']){
	                    $to_mailup[$k]['Fields'][] = array(
                            'Description' => 'FirstName',
                            'Id' => 1,
                            'Value' => $v['nome'],
                            );
                    }
                    if($v['cognome']){
	                    $to_mailup[$k]['Fields'][] = array(
                            'Description' => 'FirstName',
                            'Id' => 2,
                            'Value' => $v['cognome'],
                            );
                    }
                  	$to_mailup[$k]['Name']="";
            	}
            	
            }
            
            foreach($to_mailup as $k=>$val){
	            $to_mailup[$k]=(object)$val;
            }
            $to_mailup = array_values( (array)$to_mailup );
            //debugga($to_mailup);exit;
            
            $to_mailup = json_encode($to_mailup);
            //ELABORAZIONE RICHIESTA
            
            $result = $this->callMethod($url, "POST", $to_mailup, "JSON");
           
            $importId = $result; //identificativo dell'importazione sui server di mailUp
            
            //CONTROLLO ESITO DELL'IMPORTAZIONE
            
            $url_check = "https://services.mailup.com/API/v1.1/Rest/ConsoleService.svc/Console/Import/{$importId}";
            $result = $this->callMethod($url_check, "GET", null, null);
            $result = json_decode($result);
            //debugga($result);exit;
            return $result;
                
        }
		/** CREA_GRUPPO
			funzione che crea un gruppo in una lista esistente
			INPUT:
				id_list: identificativo della lista
				nome_gruppo: nome del gruppo
				descrizione_gruppo; note sul gruppo
			OUTPUT: 
				result: identificativo del gruppo
		**/
        function createGroup($id_list,$nome_gruppo,$descrizione_gruppo){
                
                //DETERMINAZIONE DELL'URL A CUI INVIARE LA RICHIESTA
                $url = "https://services.mailup.com/API/v1.1/Rest/ConsoleService.svc/Console/List/{$id_list}/Group";
                //ELABORAZIONE DEI DATI NEL FORMATO RICHIESTO
                $to_mailup = array( 'Name' => $nome_gruppo, 'Notes' => $descrizione_gruppo);
            
                // prendo l'oggetto JSON
                
                $to_mailup = json_encode($to_mailup);
                
                //ELABORAZIONE RICHIESTA 
                $result = $this->callMethod($url,"POST",$to_mailup,"JSON");
                $result = json_decode($result);
                
                return $result->idGroup;
                
        }
        /** CANCELLA_GRUPPO
			funzione che cancella un gruppo da una lista esistente
			INPUT:
				id_list: identificativo della lista
				id_group: identificativo del gruppo
		**/
        function removeGroupFromList($id_list,$id_group){
            $url = "https://services.mailup.com/API/v1.1/Rest/ConsoleService.svc/Console/List/{$id_list}/Group/{$id_group}";
            $this->callMethod($url,"DELETE",null,null);
        }
        
        /** PRENDI_LISTE
			funzione che prende tutte le liste con le relative informazioni
			OUTPUT:
				array contentente le liste
		**/
        function getLists(){
	        $url = "https://services.mailup.com/API/v1.1/Rest/ConsoleService.svc/Console/User/Lists";
	        $result = $this->callMethod($url,"GET",null,null);
            
	        $result = json_decode($result);
	        $toreturn = array();
	        foreach($result->Items as $k=>$val){
		        $toreturn[$k]['nome']= $val->Name;
		        $toreturn[$k]['descrizione']= $val->Description;
		        $toreturn[$k]['id']= $val->idList;
	        }
	        return $toreturn;
        }
        /** PRENDI_LISTE
			funzione che prende tutti i gruppi di una lista con le relative informazioni
			INPUT:
				id_list: identificativo della lista
			OUTPUT:
				array dei gruppi
		**/
        function getAllGroupsFromList($id_list){
	        $url ="https://services.mailup.com/API/v1.1/Rest/ConsoleService.svc/Console/List/{$id_list}/Groups";
	        $result = $this->callMethod($url,"GET",null,null);
	        
	        $result = json_decode($result);
	        foreach($result->Items as $k => $val){
		        $toreturn[$k]['nome']=$val->Name;
		        $toreturn[$k]['note']=$val->Notes;
		        $toreturn[$k]['id']=$val->idGroup;
		        $toreturn[$k]['idLista']=$val->idList;
		        }
	        return $toreturn;
        }
        
        
		/*funzione prende gli utenti iscritti  da ungruppo di MailUp
		INPUT: 
			id: identificativo del gruppo
		OUTPUT:
			result: elementi del gruppo
		*/
		function getRecipientsFromGroup($id,$page=0){
			//DETERMINAZIONE DELL'URL A CUI INVIARE LA RICHIESTA
			$url = "https://services.mailup.com/API/v1.1/Rest/ConsoleService.svc/Console/Group/{$id}/Recipients?PageNumber={$page}";
			
			//ELABORAZIONE RICHIESTA 
			$result = $this->callMethod($url, "GET", null, "JSON");
			
		    $result = json_decode($result);
		    
		    $toreturn = array();
		    foreach($result->Items as $k => $val){
			    //debugga($val);exit;
			    $toreturn[$k]['nome']=$val->Fields[0]->Value;
			    $toreturn[$k]['cognome']=$val->Fields[1]->Value;
			    $toreturn[$k]['email']=$val->Email;
			    $toreturn[$k]['id']=$val->idRecipient;

		    }
		    //debugga(count($toreturn));exit;
		    return array($toreturn,$result->TotalElementsCount);
		}
		
		/**funzione che rimuove un utente da un gruppo
		INPUT: 
			id_group: identificativo del gruppo
			id_recipient :: identificativo dell'utente
		
		**/
		function removeRecipientFromGroup($id_group, $id_recipient){
			$url = "https://services.mailup.com/API/v1.1/Rest/ConsoleService.svc/Console/Group/{$id_group}/Unsubscribe/{$id_recipient}";
			$result = $this->callMethod($url, "DELETE", null, null);
		}
		
		function getRecipentsFromList($id_lista,$page=0){
			$url = "https://services.mailup.com/API/v1.1/Rest/ConsoleService.svc/Console/List/{$id_lista}/Recipients/Subscribed?PageNumber={$page}";
			$result = $this->callMethod($url, "GET", null, null);
			
		    $result = json_decode($result);
		    
		   
		    $toreturn = array();
		    foreach($result->Items as $k => $val){
			    //debugga($val);exit;
			    $toreturn[$k]['nome']=$val->Fields[0]->Value;
			    $toreturn[$k]['cognome']=$val->Fields[1]->Value;
			    $toreturn[$k]['email']=$val->Email;
			    $toreturn[$k]['id']=$val->idRecipient;

		    }
		    //debugga($result->TotalElementsCount);exit;
		    return array($toreturn,$result->TotalElementsCount);
		}
		
		/**funzione che rimuove un utente da una lista
		INPUT: 
			id_list: identificativo della lista
			id_recipient :: identificativo dell'utente
		
		**/
		function removeRecipientFromList($id_list,$id_recipient){
			$url ="https://services.mailup.com/API/v1.1/Rest/ConsoleService.svc/Console/List/{$id_list}/Unsubscribe/{$id_recipient}";
			$this->callMethod($url, "DELETE", null, null);
		}
		
		
		function deleteRecipientsFormList($id_lista,$page=0){
			
			$utenti = $this->getRecipentsFromList($id_lista,$page);
			foreach($utenti[0] as $v){
				
				$this->removeRecipientFromList($id_lista,$v['id']);
				sleep(1);
				
			}
			
		}
		
		function addRecipientToGroup($id_group,$id_recipient){
			$url = "https://services.mailup.com/API/v1.1/Rest/ConsoleService.svc/Console/Group/{$id_group}/Subscribe/{$id_recipient}";
			$this->callMethod($url, "POST", null, null);
		}
		
    }
    
?>