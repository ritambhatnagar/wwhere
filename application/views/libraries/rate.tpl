<%$this->css->add_css("rating/jquery.rating.css")%>
<%$this->css->css_src()%>
<%if $readonly eq 'read'%>
   <%for $i=1 to ($number)%>
      <input name="<%$class_radios%>" type="radio" value="<%$i/$split%>" disabled="disabled" <%if $i lte $select%> checked='checked' readonly='readonly'<%/if%>class="star {split:<%$split%>} "/>
   <%/for%>
<%/if%>
<%if $readonly eq 'edit'%>
   <%if $select gte 1%>
   <%for $i=1 to ($number)%>
      <input name="<%$class_radios%>" id="<%$class_radios%>" type="radio" value="<%$i/$split%>" <%if $i lte $select%> checked='checked' readonly='true'<%/if%>class="star {split:<%$split%>} "/>
   <%/for%>
   <%else%>
   <%for $i=1 to $number%>
      <input name="<%$class_radios%>" id="<%$class_radios%>" type="radio" value="<%$i/$split%>" class="star {split:<%$split%>}"/>
   <%/for%>
   <%/if%>
<%/if%>

<%javascript%>
   <%if $readonly eq 'edit'%>
   var param={name:"<%$class_radios%>"};
   $("input[name = '<%$class_radios%>']").rating({
   
   <%if $ajax eq 'true'%>
      callback: function(value){
         $.ajax({
	    type: 'POST',
	    url: "ajax/getValue/"+value,
	    data:"name="+"<%$class_radios%>"+"&value="+value+"&id="+"<%$class_radios%>"+"&user="+"<%$user%>"+"&rating="+"<%$rating_for%>",
	    //data:$('input.star').serialize(),
	    success: function(data){
                
                 $("input[name = '<%$class_radios%>']").rating('disable', true);
            }
         })
      }
      <%/if%>
   })
   <%/if%>
<%/javascript%>




