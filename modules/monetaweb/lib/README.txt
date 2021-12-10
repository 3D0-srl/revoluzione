****************************************************************************************
** MonetaWeb 2.0 **
****************************************************************************************
Requirements:
- PHP 5
- Curl extension installed ( http://www.php.net/curl )

CONFIGURATION
In the config.php you should customize the settings.
1. the variable:
	$setefiPaymentGatewayDomain: 'https://test.monetaonline.it';
  points to the Setefi TEST system, for your PRODUCTION application you need to change that url with the correct one.

2. the variable:
  $terminalId: '99999999', is a generic test terminal id, you should use your terminal id
    
3. the variable:
  $terminalPassword: '99999999', is a generic test terminal password, you should use your terminal password

4. the variable:
	$merchantDomain: 'merchant_domain'
  should be personalized with your application domain name.
  To try the demo app locally you can set $merchantDomain = 'http://127.0.0.1', note that the notify, result, recovery actions would not be reached by Setefi.

