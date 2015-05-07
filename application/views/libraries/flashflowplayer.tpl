<%$this->js->clean_js()%>
<%$this->js->add_js("flowplayer/flowplayer-3.2.12.min.js" )%>

<%$this->js->js_src()%>

<%section name=row loop=$media_arr%>




<a href="<%$media_arr[row].mainurl%>" style="display:block;width:<%$pWidth%>px;height:<%$pHeight%>px;text-align:center;margin:15px;float:left;border-radius:5px;border:5px solid #999;" id="nsplflash<%$smarty.section.row.index%>">
<!--    <img src="<%*$this->flowplayer->getThumnails($media_arr[row].mainurl)*%>" />-->
</a>
<script type="text/javascript">
    $f("nsplflash<%$smarty.section.row.index%>", "<%$playerPATH%>",
    {
    plugins: {
   
   
        <%if isset($media_arr[row].controls) && $media_arr[row].controls|is_array%>
        controls:  <%json_encode($media_arr[row].controls)%>, 
        <%/if%>
    
             },      
        <%if  $media_arr[row].playlist|@count gt 0%>
        playlist:  <%json_encode($media_arr[row].playlist)%>, 
              
        <%/if%>
        
        <%if isset($media_arr[row].clip) && $media_arr[row].clip|is_array%>
        clip:  <%json_encode($media_arr[row].clip)%>, 
        <%/if%>

    <%if isset($media_arr[row].extraparams)%>
    <%$media_arr[row].extraparams%>
    <%/if%>                  

}); </script>

<%/section%>
