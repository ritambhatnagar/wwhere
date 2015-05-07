<%$this->js->clean_js()%>
<%$this->js->add_js("flowplayer/flowplayer-3.2.12.min.js" )%>

<%$this->js->js_src()%>

<%section name=row loop=$audio_arr%>




<a href="<%$audio_arr[row].mainurl%>" style="display:block;width:648px;height:30px;" id="nsplaudio<%$smarty.section.row.index%>"></a>
<script type="text/javascript">
    $f("nsplaudio<%$smarty.section.row.index%>", "<%$playerPATH%>",{
    plugins: {
                     
        <%if isset($audio_arr[row].controls) && $audio_arr[row].controls|is_array%>
        controls:  <%json_encode($audio_arr[row].controls)%>, 
        <%/if%>
        
         audio: {
            url: '<%$audioplayerPATH%>'
             
        }
    },      
    <%if  $audio_arr[row].playlist|@count gt 0%>
    playlist:  <%json_encode($audio_arr[row].playlist)%>, 
              
    <%/if%>
     
     
       <%if isset($audio_arr[row].clip) && $audio_arr[row].clip|is_array%>
        clip:  <%json_encode($audio_arr[row].clip)%>, 
        <%/if%>
     
     
        
        
         
    <%if isset($audio_arr[row].extraparams)%>
    <%$audio_arr[row].extraparams%>
    <%/if%>                       

}); </script><br>

<%/section%>
