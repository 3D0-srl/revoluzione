function submit_feed(el,id_store,market,type){

    el.attr('disabled','disabled');
    $.ajax({
        type: "GET",
            url: "../index.php",
            cache: false,
            dataType: "json",
            data: {action:'feed_responses',ctrl:'Cron',id_store:id_store,mod:'amazon',action:type,market:market},
            success: function(data){
                  //$('#message_loader').hide();
                  //document.location.reload();
                  el.removeAttr('disabled');
                  if(data.result == 'ok'){
                        $('.feed-info').show();
                      //document.location.reload();
                  }else{
                  }
            },
           
      });
  
}

function salva_ricarico(el,id_store,market){

      el.attr('disabled','disabled');
      $.ajax({
          type: "POST",
              url: "index.php",
              cache: false,
              dataType: "json",
              data: {action:'ricarico',ctrl:'Action',id_store:id_store,mod:'amazon',market:market,ajax:1,formdata:$('#ricarico_'+market+'_form').serialize()},
              success: function(data){
                    //$('#message_loader').hide();
                    //document.location.reload();
                    el.removeAttr('disabled');
                    if(data.result == 'ok'){
                          //$('.feed-info').show();
                        //document.location.reload();
                    }else{
                    }
              },
             
        });
    
  }


function profile_eidt(id){

     
      $.ajax({
          type: "POST",
              url: "index.php",
              cache: false,
              dataType: "json",
              data: {action:'form_category',ctrl:'Action',id:id,mod:'amazon',ajax:1},
              success: function(data){
                   
                    if(data.result == 'ok'){
                          $('#div_profile_'+data.market).html(data.html);
                        
                    }else{
                    }
              },
             
        });
    
  }

  function save_profile(market){

     var formdata = $('#new_profile_'+market).serialize();
      $.ajax({
          type: "POST",
              url: "index.php",
              cache: false,
              dataType: "json",
              data: {action:'save_profile',ctrl:'Action',mod:'amazon',formdata:formdata,ajax:1},
              success: function(data){
                    //$('#message_loader').hide();
                    //document.location.reload();
                   
                    if(data.result == 'ok'){
                          document.location.href=data.url;
                          //$('#div_profile_'+market).html(data.html);
                          //$('.feed-info').show();
                        //document.location.reload();
                    }else{
                    }
              },
             
        });
    
  }


  function add_profilo(market){
        $('#'+market+'_profile_list').toggle();
        $('#new_profile_'+market).toggle();
  }

  $(document).ready(function(){
      //profile_eidt(5,'Italy','Clothing');
  });


  