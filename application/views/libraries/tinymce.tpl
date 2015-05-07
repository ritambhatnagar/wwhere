        
<%$this->js->clean_js()%>
<%$this->js->add_js("libraries/tiny_mce/jquery.tinymce.js" )%>
<%$this->js->add_js("libraries/tiny_mce/tiny_mce_src.js" )%>
<%$this->js->js_src()%>

<script type="text/javascript">   
    tinymce.init(<%$tinyjson%>);  
</script> 


