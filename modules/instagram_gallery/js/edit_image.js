var counter = 0;
var mouseX = 0;
var mouseY = 0;
var enable_custom_tag = true;

var mouseX_img = 0;
var mouseY_img = 0;

$("#imgtag img").click(function(e) { // make sure the image is click
  var imgtag = $(this).parent(); // get the div to append the tagging list
  //alert(e.pageX);
  //alert( $(imgtag).offset().left);
  mouseX = ( e.pageX - $(imgtag).offset().left ) - 50; // x and y axis
  mouseY = ( e.pageY - $(imgtag).offset().top ) - 50;
 
  $( '#tagit' ).remove( ); // remove any tagit div first
  if( !enable_custom_tag ){
	
	
	
	$( imgtag ).append( '<div id="tagit"><div class="box"></div><div class="name" style="height:160px"><div class="text" style="display:none">Tag</div><input type="text" name="txtname" id="tagname" style="display:none"/><div class="text">ID prodotto</div><input type="text" name="product" id="idproduct" /><div class="text">Colore tag</div><div style="margin-bottom:10px;"><select name="colorpicker" id="colortag" class="color_tagedit"><option value="#ffffff"></option><option value="#b1dfd9"><option value="#333"></option><option value="#b9dcf3"></option><option value="#a6a7d4"></option><option value="#f7d1ad"></option></select></div><button class="btn btn-sm btn-success"  id="btnsave"><i class="fa fa-save"></i></button><button class="btn btn-sm btn-danger" id="btncancel"><i class="fa fa-trash-o"></i></button></div></div>' );
  }else{
	$( imgtag ).append( '<div id="tagit"><div class="box"></div><div class="name"><div class="text">Tag</div><input type="text" name="txtname" id="tagname" /><div class="text">ID prodotto</div><input type="text" name="product" id="idproduct" /><div class="text">Colore tag</div><div style="margin-bottom:10px;"><select name="colorpicker" id="colortag" class="color_tagedit"><option value="#ffffff"></option><option value="#b1dfd9"><option value="#333"></option><option value="#b9dcf3"></option><option value="#a6a7d4"></option><option value="#f7d1ad"></option></select></div><button class="btn btn-sm btn-success"  id="btnsave"><i class="fa fa-save"></i></button><button class="btn btn-sm btn-danger" id="btncancel"><i class="fa fa-trash-o"></i></button></div></div>' );
  }
  $( '#tagit' ).css({ top:mouseY, left:mouseX });
  $('select[name="colorpicker"]').simplecolorpicker({picker: true,theme: 'regularfont'});
  $('#tagname').focus();
});

// Save button click - save tags
$( document ).on( 'click',  '#tagit #btnsave', function(){
	
	
	name = $('#tagname').val();
	var img = $('#imgtag').find( 'img' );
	var id = $( img ).attr( 'id' );
  $.ajax({
	type: "POST", 
	url: "index.php", 
	data: {	
		ctrl: 'Tag',
		ajax:1,
		action: 'tag',
		mod:'instagram_gallery',
		pic_id:id,
		name:name,
		pic_x:mouseX,
		pic_y:mouseY,
		type:'insert',
		id_product:$('#idproduct').val(),
		color:$('#colortag').val(),
	},
	//data: "action=tag&pic_id=" + id + "&name=" + name + "&pic_x=" + mouseX + "&pic_y=" + mouseY + "&type=insert&id_product="+$('#idproduct').val()+"&color="+$('#colortag').val(),
	cache: true, 
	success: function(data){
	  viewtag( id );
	  $('#tagit').fadeOut();
	}
  });
  
});

// Cancel the tag box.
$( document ).on( 'click', '#tagit #btncancel', function() {
  $('#tagit').fadeOut();
});

// mouseover the taglist 
$('#taglist').on( 'mouseover', 'li', function( ) {
  id = $(this).attr("id");
  $('#view_' + id).css({ opacity: 1.0 });
}).on( 'mouseout', 'li', function( ) {
	$('#view_' + id).css({ opacity: 0.0 });
});

// mouseover the tagboxes that is already there but opacity is 0.
$( '#tagbox' ).on( 'mouseover', '.tagview', function( ) {
	var pos = $( this ).position();
	$(this).css({ opacity: 1.0 }); // div appears when opacity is set to 1.
}).on( 'mouseout', '.tagview', function( ) {
	$(this).css({ opacity: 0.0 }); // hide the div by setting opacity to 0.
});

// Remove tags.
$( '#taglist' ).on('click', '.remove', function() {
  id = $(this).parent().attr("id");
  // Remove the tag
  $.ajax({
	type: "POST", 
	url: "index.php", 
	data: "ctrl=Tag&ajax=1&action=tag&mod=instagram_gallery&tag_id=" + id + "&type=remove",
	success: function(data) {
		var img = $('#imgtag').find( 'img' );
		var id = $( img ).attr( 'id' );
		//get tags if present
		viewtag( id );
	}
  });
});

// load the tags for the image when page loads.
var img = $('#imgtag').find( 'img' );
var id = $( img ).attr( 'id' );

viewtag( id ); // view all tags available on page load

function viewtag( pic_id )
{
  // get the tag list with action remove and tag boxes and place it on the image.
  $.post( "index.php" ,  "ctrl=Tag&ajax=1&action=list&mod=instagram_gallery&pic_id=" + pic_id, function( data ) {
	$('#taglist ul').html(data.lists);
	 $('#tagbox').html(data.boxes);
  }, "json");

}


