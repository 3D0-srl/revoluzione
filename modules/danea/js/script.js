	

	var index_payment = 0;
	var index_attribute = 0;
	var index_tax = 0;
	var index_price = 0;
	var index_attribute_set = 0;
	var payments = [];
	var attributes = [];
	var taxes = [];
	var attribute_sets = [];
	var attributes_danea = [];
	attributes_danea['Size'] = 'Taglia';
	attributes_danea['Color'] = 'Colore';

	var prices_danea = [];
	prices_danea['1'] = 'Listino 1';
	prices_danea['2'] = 'Listino 2';
	prices_danea['3'] = 'Listino 3';
	prices_danea['4'] = 'Listino 4';
	prices_danea['5'] = 'Listino 5';
	prices_danea['6'] = 'Listino 6';
	prices_danea['7'] = 'Listino 7';
	prices_danea['8'] = 'Listino 8';
	prices_danea['9'] = 'Listino 9';

	function add_payment(marion=null,danea=''){
		
		$('#box_payments').append(
			'<tr>'+
			'<td>'+createSelectPayment(index_payment,marion)+'</td>'+
			'<td><input type="text" class="form-control" name="formdata[mapping_payments]['+index_payment+'][danea]" value="'+danea+'"></td>'+
			'<td class="pull-right"><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest(\'tr\').remove(); return false;"><i class="fa fa-trash-o"></i> Elimina</button></td>'+
			'</tr>'
		);
		$('.select_payment').selectpicker();
		index_payment = index_payment+1;

	}

	function add_tax(marion=null,danea=''){
		
		$('#box_taxes').append(
			'<tr>'+
			'<td>'+createSelectTax(index_tax,marion)+'</td>'+
			'<td><input type="text" class="form-control" name="formdata[mapping_taxes]['+index_tax+'][danea]" value="'+danea+'"></td>'+
			'<td class="pull-right"><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest(\'tr\').remove(); return false;"><i class="fa fa-trash-o"></i> Elimina</button></td>'+
			'</tr>'
		);
		$('.select_tax').selectpicker();
		index_tax = index_tax+1;

	}

	function add_attribute(marion=null,danea=''){
		$('#box_attributes').append(
			'<tr>'+
			'<td>'+createSelectAttribute(index_attribute,marion)+'</td>'+
			'<td>'+createSelectAttributeDanea(index_attribute,danea)+'</td>'+
			'<td class="pull-right"><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest(\'tr\').remove(); return false;"><i class="fa fa-trash-o"></i> Elimina</button></td>'+
			'</tr>'
		);
		index_attribute = index_attribute+1;

	}

	function add_price(marion=null,danea=''){
		$('#box_prices').append(
			'<tr>'+
			'<td>'+createSelectPriceDanea(index_price,danea)+'</td>'+
			'<td>'+createSelectPrice(index_price,marion)+'</td>'+
			'<td class="pull-right"><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest(\'tr\').remove(); return false;"><i class="fa fa-trash-o"></i> Elimina</button></td>'+
			'</tr>'
		);
		$('.select_price').selectpicker();
		$('.select_price_danea').selectpicker();
		index_price = index_price+1;

	}

	function add_attribute_set(marion=null,mapping=''){
		console.log(mapping);
		$('#box_attribute_sets').append(
			'<tr>'+
			createSelectAttributeSet(index_attribute_set,marion,mapping)+
			//'<td>'+createSelectAttributeDanea(index_attribute,danea)+'</td>'+
			'<td class="pull-right"><button type="button" class="btn btn-danger btn-sm" onclick="$(this).closest(\'tr\').remove(); return false;"><i class="fa fa-trash-o"></i> Elimina</button></td>'+
			'</tr>'
		);
		
		index_attribute_set = index_attribute_set+1;

	}

	function get_composition(set,index,mapping=null){
		
		$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "get_attributes",ctrl:'Conf',mod:'danea',ajax:1,set:set},
		  dataType: "json",
		  success: function(data){
			 
			  
			  var html = '<table class="table">'; 
			  for( var k in data ){
				   var options_select = "<option value='size' selected>taglia</option><option value='color'>colore</option>";
				   if( mapping != null && mapping[k] && mapping[k] == 'color'){
						
						 options_select = "<option value='size'>taglia</option><option value='color' selected>colore</option>";
				   }
				   var class_select = "mapping_attribute_sets_select";
				   html = html+ "<tr><td>"+data[k]+"</td><td><select class='"+class_select+"' name='formdata[mapping_attribute_sets]["+index+"]["+k+"]'>"+options_select+"</select></td></tr>";
				  
			   }
			   html = html+ "</table>";
			  
			   $('#attributes_list_'+index).html(
					html	
			   );
			    $('.mapping_attribute_sets_select').selectpicker();
		  },
		 
		});
	}
	
	function initDanea(){

		payments = jQuery.parseJSON(payments_list);
		attributes = jQuery.parseJSON(attributes_list);
		taxes = jQuery.parseJSON(taxes_list);
		prices = jQuery.parseJSON(prices_list);
		attribute_sets = jQuery.parseJSON(attribute_sets_list);

		//console.log(data_payments);
		data_payments = jQuery.parseJSON(data_payments);
		for( var k in data_payments ){
			add_payment(k,data_payments[k]);
		}
		data_attributes = jQuery.parseJSON(data_attributes);
		for( var k in data_attributes ){
			add_attribute(k,data_attributes[k]);
		}

		data_taxes = jQuery.parseJSON(data_taxes);
		for( var k in data_taxes ){
			add_tax(k,data_taxes[k]);
		}

		data_prices = jQuery.parseJSON(data_prices);
		for( var k in data_prices ){
			add_price(k,data_prices[k]);
		}

		data_attribute_sets = jQuery.parseJSON(data_attribute_sets);
		for( var k in data_attribute_sets ){
			
			add_attribute_set(k,data_attribute_sets[k]);
		}
		
		$('#enable_import').on('change',function(){
			if( $(this).prop('checked') == true ){
				$('.import_item').show();
			}else{
				$('.import_item').hide();
			}
		});

		$('#enable_export').on('change',function(){
			if( $(this).prop('checked') == true ){
				$('.export_item').show();
			}else{
				$('.export_item').hide();
			}
		});
		
		$('#use_import_setting').on('change',function(){
			if( $(this).prop('checked') == true ){
				$('#table_mapping_variations_export').hide();
			}else{
				$('#table_mapping_variations_export').show();
			}
		});
		
		$('#manage_variations').on('change',function(){
			if( $(this).prop('checked') == true ){
				$('#table_mapping_variations').show();
			}else{
				$('#table_mapping_variations').hide();
			}
		});
		$('#enable_credentials').on('change',function(){
		
			if( $(this).prop('checked') == true ){
				$('#div_username').show();
				$('#div_password').show();
			}else{
				$('#div_username').hide();
				$('#div_password').hide();
			}
		});
		

		$('#manage_variations_import').on('change',function(){
			if( $(this).prop('checked') == true ){
				$('.manage_variations_import').show();
			}else{
				$('.manage_variations_import').hide();
			}
		});
		$('#manage_variations_import_advanced').on('change',function(){
			if( $(this).prop('checked') == true ){
				$('#advanced_variations').show();
				$('#standard_variations').hide();
			}else{
				$('#advanced_variations').hide();
				$('#standard_variations').show();
			}
		});

		$('#sku_child_dinamic').on('change',function(){
			if( $(this).prop('checked') == true ){
				$('#div_sku_child').hide();
			}else{
				$('#div_sku_child').show();
				
			}
		});
		


		$('#manage_discount_like_product').on('change',function(){
			if( $(this).prop('checked') == true ){
				$('#div_discount_code').show();
				$('#div_discount_vat').show();
			}else{
				$('#div_discount_code').hide();
				$('#div_discount_vat').hide();
			}
		});
		$('#manage_shipping_like_product').on('change',function(){
			if( $(this).prop('checked') == true ){
				$('#div_shipping_code').show();
				$('#div_shipping_vat').show();
			}else{
				$('#div_shipping_code').hide();
				$('#div_shipping_vat').hide();
			}
		});
		$('#manage_payment_like_product').on('change',function(){
			if( $(this).prop('checked') == true ){
				$('#div_payment_code').show();
				$('#div_payment_vat').show();
			}else{
				$('#div_payment_code').hide();
				$('#div_payment_vat').hide();
			}
		});

		$('#log').on('change',function(){
			getLogs($(this).val());
		});
		$('#log').trigger('change');
		$('#sku_child_dinamic').trigger('change');
		$('#enable_import').trigger('change');
		$('#enable_export').trigger('change');
		$('#use_import_setting').trigger('change');
		$('#enable_credentials').trigger('change');
		$('#manage_variations_import').trigger('change');
		$('#manage_variations_import_advanced').trigger('change');
		$('#manage_variations').trigger('change');
		$('#manage_discount_like_product').trigger('change');
		$('#manage_shipping_like_product').trigger('change');
		$('#manage_payment_like_product').trigger('change');
		/*alert('qua');
		$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "parameters",ctrl:'Conf',mod:'danea',ajax:1},
		  dataType: "json",
		  success: function(data){
				payments = data.payments;
				attributes = data.attributes;
				taxes = data.taxes;

				//console.log(data_payments);
				data_payments = jQuery.parseJSON(data_payments);
				for( var k in data_payments ){
					add_payment(k,data_payments[k]);
				}
				data_attributes = jQuery.parseJSON(data_attributes);
				for( var k in data_attributes ){
					add_attribute(k,data_attributes[k]);
				}
		  },
		 
		});*/
	}

	function createSelectTax(index,selected=null){
		var select = "<select class='select_tax' name='formdata[mapping_taxes]["+index+"][marion]'>";
		for( var k in taxes ){
			if( k > 0 ){
				if( selected != null && k == selected ){
					select = select + "<option selected value="+k+">"+taxes[k]+"</option>";
				}else{
					select = select + "<option  value="+k+">"+taxes[k]+"</option>";
				}
			}
		}
		select = select + "</select>";
		return select;
	}

	function createSelectPayment(index,selected=null){
		var select = "<select class='select_payment' name='formdata[mapping_payments]["+index+"][marion]'>";
		for( var k in payments ){
			if( selected != null && k == selected ){
				select = select + "<option selected value="+k+">"+payments[k]+"</option>";
			}else{
				select = select + "<option  value="+k+">"+payments[k]+"</option>";
			}
		}
		select = select + "</select>";
		return select;
	}

	function createSelectAttributeDanea(index,selected=null){
		var select = "<select name='formdata[mapping_attributes]["+index+"][danea]'>";
		for( var k in attributes_danea ){
			if( selected != null && k == selected ){
				select = select + "<option selected value="+k+">"+attributes_danea[k]+"</option>";
			}else{
				select = select + "<option  value="+k+">"+attributes_danea[k]+"</option>";
			}
		}
		select = select + "</select>";
		return select;

	}

	

	function createSelectAttribute(index,selected=null){
		var select = "<select name='formdata[mapping_attributes]["+index+"][marion]'>";
		for( var k in attributes ){
			if( selected != null && k == selected ){
				select = select + "<option selected value="+k+">"+attributes[k]+"</option>";
			}else{
				select = select + "<option  value="+k+">"+attributes[k]+"</option>";
			}
		}
		select = select + "</select>";
		return select;
	}
	
	function createSelectPriceDanea(index,selected=null){
		var select = "<select class='select_price_danea' name='formdata[mapping_prices]["+index+"][danea]'>";
		for( var k in prices_danea ){
			if( selected != null && k == selected ){
				select = select + "<option selected value="+k+">"+prices_danea[k]+"</option>";
			}else{
				select = select + "<option  value="+k+">"+prices_danea[k]+"</option>";
			}
		}
		select = select + "</select>";
		return select;

	}

	function createSelectPrice(index,selected=null){
		var select = "<select class='select_price' name='formdata[mapping_prices]["+index+"][marion]'>";
		for( var k in prices ){
			if( selected != null && k == selected ){
				select = select + "<option selected value="+k+">"+prices[k]+"</option>";
			}else{
				select = select + "<option  value="+k+">"+prices[k]+"</option>";
			}
		}
		select = select + "</select>";
		return select;

	}

	function createSelectAttributeSet(index,selected=null,mapping){
		var select = "<select class='mapping_attribute_sets_select' name='formdata[mapping_attribute_sets]["+index+"][id]' onchange='get_composition($(this).val(),"+index+"); return false;'>";
		for( var k in attribute_sets ){
			if( !selected ) selected = k;
			if( selected != null && k == selected ){
				select = select + "<option selected value="+k+">"+attribute_sets[k]+"</option>";
			}else{
				select = select + "<option  value="+k+">"+attribute_sets[k]+"</option>";
			}
		}
		select = select + "</select>";

		var html = "<td>"+select+"</td><td id='attributes_list_"+index+"'></td>";
		get_composition(selected,index,mapping);
		return html;
	}



	function getLogs(file){
		$.ajax({
		  type: "GET",
		  url: "index.php",
		  data: { action: "logs",ctrl:'Conf',mod:'danea',ajax:1,file:file},
		  dataType: "json",
		  success: function(data){

				$('#logs_area').val(data.data);
				console.log(data.data);
		  },
		 
		})
	}

	initDanea();
	

	$(document).ready(function(){

		$("div.bhoechie-tab-menu>div.list-group>a").click(function(e) {
			e.preventDefault();
			$(this).siblings('a.active').removeClass("active");
			$(this).addClass("active");
			var index = $(this).index();
			$('#current_tab').val(index);
			$("div.bhoechie-tab>div.bhoechie-tab-content").removeClass("active");
			$("div.bhoechie-tab>div.bhoechie-tab-content").eq(index).addClass("active");
			
		});

		var tab = parseInt($('#current_tab').val());
		$("div.bhoechie-tab-menu>div.list-group>a").each(function(index){
			if( index == tab ){
				$(this).click();
			}
		});

		
	});


