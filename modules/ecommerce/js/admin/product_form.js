var cont_price_list = 0;

function add_price_list(){
	cont_price_list++;
	var t = $('#modello_pricelist').clone().appendTo('#cont_box_pricelist').attr('id','pricelist_'+cont_price_list).show();
	$('#select_listino_prezzo').clone().appendTo("#pricelist_"+cont_price_list+" #cont_listino").attr('name',"formdata[pricelist]["+cont_price_list+"][label]").attr('id','pricelist_list_'+cont_price_list);
	$("#pricelist_list_"+cont_price_list).selectpicker("refresh");
	$('#select_gruppo_utente').clone().appendTo("#pricelist_"+cont_price_list+" #cont_categoria_utente").attr('name',"formdata[pricelist]["+cont_price_list+"][userCategory]").attr('id','pricelist_usercategory_'+cont_price_list);
	$('#pricelist_usercategory_'+cont_price_list).selectpicker("refresh");

	t.find('#modello_quantita').attr('name',"formdata[pricelist]["+cont_price_list+"][quantity]").inputmask("integer",
																									{allowPlus: true,
																									allowMinus: false,
																									rightAlign:false}
																								);

	
	t.find('#price_list_percentage').attr('name',"formdata[pricelist]["+cont_price_list+"][type]");
	t.find('#price_list_price').attr('name',"formdata[pricelist]["+cont_price_list+"][type]");
	

	t.find('#modello_avanzate').attr('name',"formdata[pricelist]["+cont_price_list+"][advanced]").attr('onchange',"advanced_pricelist($(this),"+cont_price_list+"); return false;");
	t.find('#modello_datainizio').attr('name',"formdata[pricelist]["+cont_price_list+"][dateStart]").attr('id','pricelist_dateStart_'+cont_price_list).pickadate(
			        	{
			        	monthsFull: ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'],
						monthsShort: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
						weekdaysFull: ['Domenica', 'Lunedi', 'Martedi', 'Mercoledi', 'Giovedi', 'Venerdi', 'Sabato'],
						weekdaysShort: ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'],
						showMonthsShort: '',
						showWeekdaysFull: '',
			        	format: 'd/mm/yyyy',
			        	
			        	today: 'Oggi',
						clear: 'Annulla',
						close: 'Chiudi',
			    	});
	t.find('#modello_datafine').attr('name',"formdata[pricelist]["+cont_price_list+"][dateEnd]").attr('id','pricelist_dateEnd_'+cont_price_list).pickadate(
			        	{
			        	monthsFull: ['Gennaio', 'Febbraio', 'Marzo', 'Aprile', 'Maggio', 'Giugno', 'Luglio', 'Agosto', 'Settembre', 'Ottobre', 'Novembre', 'Dicembre'],
						monthsShort: ['Gen', 'Feb', 'Mar', 'Apr', 'Mag', 'Giu', 'Lug', 'Ago', 'Set', 'Ott', 'Nov', 'Dic'],
						weekdaysFull: ['Domenica', 'Lunedi', 'Martedi', 'Mercoledi', 'Giovedi', 'Venerdi', 'Sabato'],
						weekdaysShort: ['Dom', 'Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab'],
						showMonthsShort: '',
						showWeekdaysFull: '',
			        	format: 'd/mm/yyyy',
			        	
			        	today: 'Oggi',
						clear: 'Annulla',
						close: 'Chiudi',
			    	});
	t.find('#modello_prezzo').attr('name',"formdata[pricelist]["+cont_price_list+"][value]").inputmask("numeric",
		{allowPlus: true,
		allowMinus: false,
		rightAlign:false}
	);

	t.find('#del_list').attr('onclick',"del_pricelist("+cont_price_list+");return false;");
}

function del_pricelist(codice){
	var t = confirm("Sicuro di voler eliminare questo prezzo?");
	if( t ){
		$('#pricelist_'+codice).remove();	
	}
}

function advanced_pricelist(el,id){
	
	if( el.prop('checked') == true ){
		el.closest('.row_pricelist').find('#advanced_option_pricelist').show();
	}else{
		$("#pricelist_usercategory_"+id).val(0).selectpicker("refresh");
		$("#pricelist_dateStart_"+id).val('');
		$("#pricelist_dateEnd_"+id).val('');
		el.closest('.row_pricelist').find('#advanced_option_pricelist').hide();
	}
}


$(document).ready(function(){
	if( typeof js_manager_pricelist != 'undefined' && js_manager_pricelist != null && js_manager_pricelist == 1){
		$('#box_pricelist').show();
	}
	$('#manager_pricelist').on('change',function(){
		
		if( $(this).prop('checked') == true){
			$('#box_pricelist').show();
		}else{
			$('#box_pricelist').hide();
		}
	});

	if( typeof js_cont_price_list != 'undefined' && js_cont_price_list != null ){
		cont_price_list = js_cont_price_list;
		//console.log(cont_price_stock);
	}

	if( $('#parent_price').prop('checked') == true){
		$('#parentPrice_no').hide();
	}

	if( $('#parent_price').length > 0 ){
		
		$('#parent_price').on('change',function(){
			
			if( $(this).prop('checked') == true ){
				$('#parentPrice_no').hide();
			}else{
				$('#parentPrice_no').show();
			}
		});

		
		
	}
});
