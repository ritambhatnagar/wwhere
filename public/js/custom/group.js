$(document).ready(function () {

//    $.validator.addMethod("vGroup", function (value, element) {
//        return this.optional(element) || /^[a-z0-9]+$/.test(value);
//    }, "UrlName between 1 and 20 characters; must contain at least one lowercase letter, one numeric digit, but cannot contain whitespace.");

    $("#validateGroupForm").validate({
        rules: {
            vGroup: {
                required: true,
                alphanumeric: true,
                maxlength: 20,
                remote: {
                    type: "post",
                    url: rootPath + "group/userGroup",
                    data: {
                        d: function () {
                            return $('#iGroupId').val();
                        }
                    }
                }
            }
        },
        messages: {
            vGroup: {
                required: "Group name please",
                alphanumeric: "Group url between 1 and 20 characters; alpanumeric with at least 1 character, but cannot contain whitespace",
                remote: 'Not available'
            }
        },
        submitHandler: function (form) {
            if (typeof ($('#ajax').val()) != 'undefined') {
                $.ajax({
                    url: rootPath + "group/group_add_edit_action",
                    type: "post",
                    data: 'ajax=' + ($('#ajax').val()) + '&vGroup=' + ($('#vGroup').val()) + '&iGroupId=' + ($('#iGroupId').val()),
                    beforeSend: function () {
//                        $('#ajaxload').removeClass('hidden');
                        $('#loader').show();
                        $('#myListDiv').hide()
                    },
                    success: function (data) {
//                        console.log(data);
                        $('#loader').hide();
                        window.location.reload();
                        $('#vGroup').val('');
                        $('#closeGroup').trigger('click');
                        $('#formClose').trigger('click');
                    }
                });
            } else {
                form.submit();
            }
        },
        showErrors: function (map, list)
        {
            this.currentElements.parents('label:first, div:first').find('.has-error').remove();
            this.currentElements.parents('.form-group:first').removeClass('has-error');

            $.each(list, function (index, error)
            {
                var ee = $(error.element);
                var eep = ee.parents('label:first').length ? ee.parents('label:first') : ee.parents('div:first');

                ee.parents('.form-group:first').addClass('has-error');
                eep.find('.has-error').remove();
                eep.append('<span class="has-error help-block">' + error.message + '</span>');                
            });

            //refreshScrollers();
        }
    });

    $(function () {
        var txt = $("#vGroup");
        var func = function () {
            txt.val(txt.val().replace(/\s/g, ''));
        }
        txt.keyup(func).blur(func);
    });

    $('#groupeditsubmit').click(function () {
        if ($("#validateGroupForm").valid()) {
            //$("#validateGroupForm").submit()
        }
    });
    $('#groupaddsubmit').click(function () {
        if ($("#validateGroupForm").valid()) {
            //$("#validateGroupForm").submit()
        }
    });
    $('.delete_group').click(function () {
        var result = confirm('Are you sure you want to delete this ?');
        if (result) {
            var id = $(this).data('id');
            $.ajax({
                type: 'POST',
                url: rootPath + 'group/delete_data',
                data: {d: id},
                success: function (data) {
//                    console.log(data);
                    window.location.reload();
                    $('#cancelbtn4all').trigger('click');
                    $('#confirmbtn4all').html('Confirm');
                    $('#confirmbtn4all').attr('disabled', false);
                }
            });
        }
    });
});

function deleteimg(con, confirm) {
    if (confirm == "confirm") {
        var id = $(con).data('id');
        $.ajax({
            type: 'POST',
            url: rootPath + 'group/delete_data',
            data: {d: id},
            success: function (data) {
//                    console.log(data);
                window.location.reload();
                $('#cancelbtn4all').trigger('click');
                $('#confirmbtn4all').html('Confirm');
                $('#confirmbtn4all').attr('disabled', false);
            }
        });

    } else {
        $('#confirmmsg').text('Are you sure you want to delete this ?')
        $('#confirmbtn4all').click(function () {
            $('#confirmbtn4all').html('<i class="fa fa-ban"></i> Please Wait...');
            $('#confirmbtn4all').attr('disabled', true);
            var confirm = 'confirm';
            deleteimg(con, confirm)
        })
        $('#confirmbtnlink').trigger('click')
    }
}