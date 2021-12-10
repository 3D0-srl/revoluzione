function submitform() {

 if( $('#allow_delete_account').prop('checked') == true ){
	$('#form_delete_account').submit();
 }else{
	$('.allow_delete_account').addClass('error');
	$('#dismiss_modal').click();
	MarionAlert('Attenzione!',"Dvi dichiarare il consenso alla rimozione dei dati");
 }	
}