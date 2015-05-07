$(document).ready(function () {
//    $('.multiselect').multiselect({
//        numberDisplayed: 2,
//        nonSelectedText: 'No Group Selected'
//    });
    $('#loader').hide();
    
    var xhr;

    $('.group_select').click(function () {
        var lid = $(this).data('lid');
        var cid = $(this).data('cid');

        if (xhr && xhr.readystate != 4) {
            xhr.abort();
        }
        xhr = $.ajax({
            type: 'POST',
            url: rootPath + 'location/group_location',
            data: {lid: lid, cid: cid},
            success: function (data) {
                $('#gp_select_'+lid+' .dropdown-menu li').removeClass('active');
                $('#gp_select_'+lid+' [data-cid='+cid+']').parent().addClass('active');
                $('#gp_name'+lid).text('Added to '+$('#gp_select_'+lid).find('[data-cid='+cid+']').text());
//                window.location.reload();
                $('#cancelbtn4all').trigger('click');
                $('#confirmbtn4all').html('Confirm');
                $('#confirmbtn4all').attr('disabled', false);
                $('#removeli'+lid).show();
            }
        });
    });
    
    $('.remove_group').click(function () {
        var lid = $(this).data('lid');
        var cid = $('#gp_select_'+lid+' li.active a').data('cid');
        if (xhr && xhr.readystate != 4) {
            xhr.abort();
        }
        xhr = $.ajax({
            type: 'POST',
            url: rootPath + 'location/group_location',
            data: {lid: lid, cid: cid, mode : 'Remove'},
            success: function (data) {
                $('#gp_select_'+lid+' .dropdown-menu li').removeClass('active');
                $('#gp_name'+lid).text('Add to Group');
                $('#removeli'+lid).hide();
            }
        });
    });
    
    $('.delete_location').click(function () {
        var result = confirm('Are you sure you want to delete this ?');
        if (result) {
            var id = $(this).data('id');
            $.ajax({
                type: "POST",
                data: {'d': id},
                url: rootPath + "location/delete_data",
                success: function () {
                    window.location.reload();
                    $('#uploadlocationimg').show();
                    $('#vLocationImgss').hide();                    
                }
            });
        }
        $('#confirmbtnlink').trigger('click')
    });
});