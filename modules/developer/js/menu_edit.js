if( $('#showLabel').prop('checked') == true ){
    $('#fields_label').show();
}else{
    $('#fields_label').hide();
}

$('#showLabel').on('change',function(){
    if( $(this).prop('checked') == true ){
        $('#fields_label').show();
    }else{
        $('#fields_label').hide();
    }
})



if( $('#staticTextLabel').prop('checked') == true ){
    $('#div_labelText').show();
    $('#div_labelFunction').hide();
}else{
    $('#div_labelText').hide();
    $('#div_labelFunction').show();
}

$('#staticTextLabel').on('change',function(){
    if( $(this).prop('checked') == true ){
        $('#div_labelText').show();
        $('#div_labelFunction').hide();
    }else{
        $('#div_labelText').hide();
        $('#div_labelFunction').show();
    }
})