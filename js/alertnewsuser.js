/** CODE FOR alerts user autocomplete field **/
(function ($, root, undefined) {
  var urlAgenda = window.location.href;
  if(urlAgenda.indexOf('/login')>-1){
    
    var maximumItems = 3;
    var numberOfItems = 0;
    
    $(document).ready(function() {
      
      $('#inputAlertAutocomplete').autocomplete({
        delay: 500,
				minLength: 3,
				source: function(request, response) {
        var data = {
           "action": "searchinallterms",
           "searchtext": request.term
         };
         $.post(ajax_object.ajax_url, data, function(theajaxresponse) {
           response($.parseJSON(theajaxresponse));
         })
         .fail(function() {
           console.log( "error javascript inputAlertAutocomplete" );
         });
				},
				autoFocus: true,
				select: function(event, theselected) {
          
          //update the LI list
          var liIdStr = theselected.item.value;
          var liId = liIdStr.replace(/\ /g, '_');
          liId = liId.replace(/\'/g, '_');
          
          var htmlToAdd = '<li id="li_'+liId+'">';
          htmlToAdd+= theselected.item.label; //theselected.item.value
          htmlToAdd+= '<span class="alertDeleteValue" style="margin-left: 10px;cursor: pointer;" data_id="'+liId+'" data_value="'+theselected.item.value+'">X</span>';
          htmlToAdd+= '</li>';
					$('#listoftermsselected').append(htmlToAdd);
          
          //save the term for the user
          var data = {
              "action": "savealertforuser",
              "savetheterm": theselected.item.value
            };
            $.post(ajax_object.ajax_url, data, function(theajaxresponse) {
              //console.log("saved");
            })
          .fail(function() {
            console.log( "error javascript inputAlertAutocomplete" );
          });          
          
          //add to maximum to limit
          numberOfItems = numberOfItems+1;
          if(maximumItems <= numberOfItems){
            //limit
            //disable the selector
            $('#inputAlertAutocomplete').autocomplete({
              disabled: true
            });
            $('#inputAlertAutocomplete').prop('disabled', true);
            //$("input").prop('disabled', false);
          }//end if numbers max
          
          //empty the field (the return false is with it
          $('#inputAlertAutocomplete').val("");
          return false;
        }
      });
      
      //delete an alert for user
      $('.alertDeleteValue').live( "click", function() {
          //get the value
          var theValue = $(this).attr('data_value');
          var liId = $(this).attr('data_id');
          
          numberOfItems = numberOfItems-1;
          if(numberOfItems < maximumItems){
            //disable the selector
            $('#inputAlertAutocomplete').autocomplete({
              disabled: false
            });
            $('#inputAlertAutocomplete').prop('disabled', false);
          }
          
          //delete the li
          $('#li_'+liId).remove();
          //save the change
          var data = {
              "action": "deletealertforuser",
              "deletetheterm": theValue
            };
            $.post(ajax_object.ajax_url, data, function(theajaxresponse) {
              //console.log("deleted");
            })
          .fail(function() {
            console.log( "error javascript inputAlertAutocomplete" );
          });
      });
      
    });//end document ready
        
  }//end if url
})(jQuery, this);       