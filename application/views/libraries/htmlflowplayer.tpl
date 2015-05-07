 
<%$this->js->clean_js()%>

<%$this->js->add_js("flowplayer/flowplayerhtml5/flowplayer.min.js" )%> 

<%$this->js->js_src()%>

<%$this->css->add_css("flowplayer/skin/minimalist.css","flowplayer/skin/basic.css")%>
<%$this->css->css_src()%>






<%section name=row loop=$html_arr%>


<div   data-ratio="0.5625" data-engine="html5" style="background:#777 url('<%$this->flowplayer->getThumnails($html_arr[row].mainurl)%>') no-repeat;clear:both;width: 600px;height:336px;text-align:center;margin:15px;border-radius:5px;border:3px solid #999;" class="<%$playerClass%>  no-toggle aside-time play-button">
    <video>
        <source type="<%$this->flowplayer->getFormatType($html_arr[row].mainurl)%>" src="<%$html_arr[row].mainurl%>">
    </video>';
    <%if isset($html_arr[row].playlist) and is_array($html_arr[row].playlist)%> 
    <a class="fp-prev" ></a>
    <a class="fp-next"></a>
    <div class="fp-playlist">
        <%foreach $html_arr[row].playlist as $value%> 
        <a class="thumbnail" href="<%$value%>"></a>
        <%/foreach%>
    </div>
    <%/if%>
</div>   <br/><br/><br/><br/><br/><br/><br/> 
<%/section%>
<script>
                
    $(function () {

                   
    $(".<%$playerClass%>").flowplayer({ swf: "<%$htmlplayerPATH%>" });
                  
});
</script>




