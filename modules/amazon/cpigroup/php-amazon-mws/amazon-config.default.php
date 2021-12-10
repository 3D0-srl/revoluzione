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

/* Live Details*/

/*$store['myStore']['merchantId'] = 'A1VZO1IYX3SMHG';//Merchant ID for this store
$store['myStore']['marketplaceId'] = 'APJ6JRA9NG5V4'; //Marketplace ID for this store
$store['myStore']['keyId'] = 'AKIAIE3EOJJQ2PP5374Q'; //Access Key ID
$store['myStore']['secretKey'] = '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'; //Secret Access Key for this store
$store['myStore']['serviceUrl'] = 'https://mws-eu.amazonservices.com'; //optional override for Service URL
$store['myStore']['MWSAuthToken'] = ''; //token needed for web apps and third-party developers
*/

/*
$store['myStore']['merchantId'] = 'AUHPZTSGVUCW1';//Merchant ID for this store
$store['myStore']['marketplaceId'] = 'A21TJRUUN4KGV'; //Marketplace ID for this store
$store['myStore']['keyId'] = 'AKIAJU7SBOMDDDJXFYRA'; //Access Key ID
$store['myStore']['secretKey'] = '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'; //Secret Access Key for this store
$store['myStore']['serviceUrl'] = 'https://mws.amazonservices.in'; //optional override for Service URL
$store['myStore']['MWSAuthToken'] = ''; //token needed for web apps and third-party developers
*/
global $store;
//Service URL Base
//Current setting is United States
$AMAZON_SERVICE_URL = 'https://mws-eu.amazonservices.com';

//$AMAZON_SERVICE_URL = 'https://mws.amazonservices.in';
//Location of log file to use
$logpath = __DIR__.'/log.txt';

//Name of custom log function to use
$logfunction = '';

//Turn off normal logging
$muteLog = true;

?>
