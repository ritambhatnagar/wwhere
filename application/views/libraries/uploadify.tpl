<%javascript%>    
$("#<%$input_id%>").uploadify({
                                    	 'uploader': "<%$base%>content/uploads/does_upload",
                                                      'cancelImg': "<%$css_url%>uploadify/cancel.png",
                                                      'swf': "<%$css_url%>uploadify/uploadify.swf",                                  
                                                      'displayData': 'speed',
                                                      'fileDesc': 'Image Files',
                                                      <%$ext_string%>
                                                      
                                                      'onUploadSuccess' : function(file, data, response) { 
                                                             console.log(data);
                                                             var obj =  $.parseJSON(data);
                                                             if($.type(obj) != 'string')
                                                                {
                                                                   
                                                                    var url  = obj.file_path;
                                                                    var part ="<%$base%>";
                                                                    var path = part+url.split('/').slice(4).join('/')+obj.file_name;
                                                                    var html = '';
                                                                    
                                                                    html+='<li  id=\"'+obj.raw_name+'_1\"><a href='+path+' title="" class="thickbox"><img src='+path+' height="<%$height%>" width="<%$width%>"/></a><input type=\"hidden\" name="<%$input_id%>[]" value=\"'+obj.file_name+'\"><a id=\"'+obj.raw_name+'_a\" onclick=\"deleteImg( \'#'+obj.raw_name+'_1\');\">Delete</a></li>';
                                                                   
                                                                    $('#img').append(html);
                                                                    $('#'+obj.raw_name+'_1').addClass('related');
                                                                    $('#'+obj.raw_name+'_a').addClass('link');
                                                                    tb_init('a.thickbox, area.thickbox, input.thickbox');
                                                                }
                                                                 else
                                                                 {
                                                                        $('#err').remove();
                                                                        var html = '';
                                                                        html += '<div id=\'err\'>error in type of uploaded file</div>';
                                                                        $('#img').append(html);
                                                                        $('#err').addClass('selected');
                                                                        setTimeout(function() {
                                                                                    $('#err').fadeOut().empty();
                                                                         }, 4000);
                                                                  }
                                                      }
                               });      
                               
                               function deleteImg(id){
                                        var r=confirm("Do you want to delete!")
                                        if (r==true)
                                          {
                                                $(id).remove();
                                          }
                               }
                              
<%/javascript%>                                   