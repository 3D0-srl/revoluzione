function refresh_feeds(id_store){


    $.ajax({
        type: "GET",
            url: "../index.php",
            cache: false,
            dataType: "json",
            data: {action:'feed_responses',ctrl:'Cron',id_store:id_store,mod:'amazon'},
            success: function(data){
                  //$('#message_loader').hide();
                  document.location.reload();
                  if(data.result == 'ok'){
                      document.location.reload();
                  }else{
                  }
            },
           
      });
  
}