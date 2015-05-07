$(document).ready(function()
{
    
    if (typeof Dropzone != 'undefined')
        Dropzone.autoDiscover = false;
    
    if ($.fn.dropzone != 'undefined')
        
        new Dropzone(document.body, {
            url: $('.dropzone').attr('action'),
            previewsContainer: ".dropzone",
            clickable: "#clickable",
            acceptedFiles: '.csv',
            maxFiles: 1,
            resize: 1,
            autoDiscover: true,
            addRemoveLinks: true,
            dictDefaultMessage: "Drop files here to upload",
            success: function (a, c, d) {
                $('#importFromFile').hide();
                $('#final_form').show();
                $('#final_form').html(c);
                if (typeof $.fn.select2 != 'undefined')
                {
                    $(".importselect").select2({
                        placeholder: "None",
                        allowClear: true
                    });
                }
            },
            complete:function(file){
                this.removeFile(file);
            },
            dragenter:function(){
                $('#importFromFile').addClass('dropzone-previews');
            },
            drop:function(){
                $('#importFromFile').removeClass('dropzone-previews');
            },
            dragleave:function(){
                $('#importFromFile').removeClass('dropzone-previews');
            }
        });
});