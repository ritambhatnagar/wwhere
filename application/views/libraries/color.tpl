<%javascript%>
 <%if isset($id) && $id neq ''%>
 $(document).ready(function(){  
       
                  $('#<%$id%>').colorpicker({
                      <%$params%>
		      <%$array%>	
                  });
        
       });   
 <%/if%>
<%if isset($tag) && $tag neq ''%>
                  $('<%$tag%>').colorpicker({
                       "inline":false,
                      <%$params%>
		      <%$array%>	
                  });
<%/if%>
<%if isset($hidden) && $hidden neq ''%>
                  var dialogHidden = $('#<%$id%>').colorpicker({
                                   <%$param%>
                                });
				$('#<%$btnid%>').click(function(e) {
                                    e.stopPropagation();
                                    dialogHidden.colorpicker('open');
                             });
<%/if%>
<%if isset($dialog) && $dialog neq ''%>
 var span=$('<span/>');
              $(span).css('display','inline-block');
              $(span).addClass('basic');
              $('#<%$divid%>').append(span);
              $('.basic').colorpicker({
                <%$params%>
              });   
              var dialogModal = $('#<%$divid%>').dialog({
                    autoOpen:false,
                    minWidth:700,
                    minHeight:400,
                    modal:true,
                    buttons:{'Close': function() {
                                $(this).dialog('close');
                             }
                    }
            });
            $('#<%$btnid%>').click(function() {
              dialogModal.dialog('open');
            });
<%/if%>
<%/javascript%>
