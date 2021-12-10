$(document).ready(function(){

	if( typeof js_cont_pesi != 'undefined' && js_cont_pesi != null ){
		cont_pesi = js_cont_pesi;
	}

	if( typeof js_num_config_shipping != 'undefined' && js_num_config_shipping != null ){
		cont_tariffe = js_num_config_shipping;
	}

	if( typeof js_freeShipping != 'undefined' && js_freeShipping != null && js_freeShipping == 1){
		$('#div_countries_free_shipping').show();
	}else{
		$('#div_countries_free_shipping').hide();
	}

	$('#freeShipping').on('switchChange.bootstrapSwitch',function(){
		
		if( $(this).prop('checked') == true){
			$('#div_countries_free_shipping').show();
		}else{
			$('#div_countries_free_shipping').hide();
		}
	})
	
	
});

function add_peso(){
	$('#nessun_peso').hide();
	cont_pesi++;
	var t = $('#modello_peso').clone().appendTo('#cont_pesi').attr('id','peso_'+cont_pesi).show();
	t.find('#peso').attr('name',"formdata[pesi]["+cont_pesi+"][weight]");
	t.find('#elimina').attr('onclick',"del_peso("+cont_pesi+");return false;");
	
	
}

function del_peso(codice){
	$('#peso_'+codice).remove();	
}





var cont_pesi = 0;
var cont_tariffe = 0;

function add_valori_attributo(){
	cont_valori_attributo++;
	var t = $('#modello_valore').clone().appendTo('#cont_valori').attr('id','valore_'+cont_valori_attributo).show();
	$('#valore_'+cont_valori_attributo+" input").each(function(i,value){
		if($(this).attr('id') != 'ordine' && $(this).attr('id') != 'image' && $(this).attr('id') != 'img_file'){
			$(this).attr('name',"formdata[valori]["+cont_valori_attributo+"][value_"+$(this).attr('id')+"]");
		}
	});
	
	t.find('#image').attr('id',"image_"+cont_valori_attributo).attr('name',"formdata[valori]["+cont_valori_attributo+"][img]");
	t.find('#wrapper-upload').attr('id',"wrapper-upload_"+cont_valori_attributo);
	t.find('#img_file').attr('id',"img_"+cont_valori_attributo).cironapo({
					id_field_img:"image_"+cont_valori_attributo,
					id_wrapper: "wrapper-upload_"+cont_valori_attributo, 
					box_small: true
				});
	t.find('#ordine').attr('name',"formdata[valori]["+cont_valori_attributo+"][orderView]");
	t.find('#elimina').attr('onclick',"del_valore_attributo("+cont_valori_attributo+");return false;");
	
}

function del_valore_attributo(codice){
	$('#valore_'+codice).remove();	
}



function add_tariffa(){
	
	cont_tariffe++;
	var t = $('#modello_tariffa').clone().appendTo('#cont_tariffe').attr('id','tariffa_'+cont_tariffe).show();
	$('#tariffa_'+cont_tariffe+" .weightTmp").each(function(index,value){
		$(this).attr('name','formdata[valori]['+cont_tariffe+'][price]['+$(this).attr('weight')+']').addClass('weight');
	});
	$('#nazione_select').clone().appendTo("#tariffa_"+cont_tariffe+" #nazione").attr('name',"formdata[valori]["+cont_tariffe+"][country]").attr('id','nazione_'+cont_tariffe).addClass('country');
	$('#nazione_'+cont_tariffe).selectpicker("refresh");
	//t.find('#nazione').closest('div').hide();
	t.find('#elimina').attr('onclick',"del_tariffa("+cont_tariffe+");return false;");
	
}

function del_tariffa(codice){
	$('#tariffa_'+codice).remove();	
}


function salva_tariffe(){
	$('input[type=text]').each(function(){
		$(this).removeClass('errorInput');
	});
	//controllo valori inseirti
	
	var check = true;
	
	$('.weight').each(function(index,value){
		var val = $(this).val();
		
		if( val && !$.isNumeric(val) && check){
			$(this).addClass('errorInput');
			MarionAlert('Attenzione',"Inserire un valore numerico per il campo indicato.");
			check = false;
		}else if( !$.isNumeric(val) && check ){
			$(this).addClass('errorInput');
			MarionAlert('Attenzione',"Inserire un valore per il campo indicato.");
			check = false;
		}else{
			if( val < 0 && val != -1 && check){
				$(this).addClass('errorInput');
				MarionAlert('Attenzione',"Valore non consentito.<br> Sono ammessi solo valori non negativi o <b>-1</b> per escludere la gestione di un peso");
				check = false;
			}
		}
		
	});
	var nazioni = [];
	if( check ){
		//controllo univocità nazioni
		$('select.country').each(function(index,value){
			console.log(value);
			var val = $(this).val();
			if( val == 0 && check){
				MarionAlert('Attenzione',"Per qualche riga non è stata specificata l'area");
				check = false;
			}
			nazioni.push(val);

		});
	}
	
	if( check ){
		var unique=nazioni.filter(function(itm,i,nazioni){
			return i==nazioni.indexOf(itm);
		});
		if( unique.length != nazioni.length){
			MarionAlert('Attenzione',"Area presente più volte");
			check = false;
		}
	}
	if(check) $('#post').submit();
	return false;
}

function change_visibility(id){
	$.ajax({
	  type: "GET",
		  url: "index.php",
		  data: { ctrl: "ShippingMethodAdmin",action:'change_visibility','id':id,'ajax':1,mod:'ecommerce'},
		  dataType: "json",
		  success: function(data){
				if(data.result == 'ok'){
					
							
					var el = $('#status_'+id);
					if( data.status ){
						el.removeClass('label-danger').addClass('label-success').html('ONLINE');
					}else{
						el.removeClass('label-success').addClass('label-danger').html('OFFLINE');
					}
			
				}else{
					
				}
		  },
	 
	});
}

