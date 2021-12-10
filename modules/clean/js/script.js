function confirm_clean(el){
	var t = confirm("Sicuro di volere procedere con questa operazione? L'operazione è irreversibile!");
	if( t ){
		el.closest('form').submit();
	}
}