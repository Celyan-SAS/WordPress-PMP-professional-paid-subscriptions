/** CODE FOR alerts user autocomplete field **/
(function ($, root, undefined) {
  var urlAgenda = window.location.href;
  if(urlAgenda.indexOf('/compte-pro')>-1){
        
    $(document).ready(function() {
            
      //delete an alert for user
      $('.buttonkicksubuser').live( "click", function() {
        var iduser = $(this).attr('data_iduser');
        //console.log("the user id"+iduser);
        var data = {
            "action": "deletesubuser",
            "iduser": iduser
          };
          $.post(ajax_object.ajax_url, data, function(theajaxresponse) {
            //console.log("deleted");
            //remove line
            $('#li_'+iduser).remove();
            
            //remove the number
            var numberpeople = $('#totalcountpeople').html();
            var newnumberpeople = parseInt(numberpeople)-1;
            $('#totalcountpeople').html(newnumberpeople);
          })
        .fail(function() {
          console.log( "error javascript buttonkicksubuser" );
        });
      });
      
    });//end document ready
        
  }//end if url
})(jQuery, this);       