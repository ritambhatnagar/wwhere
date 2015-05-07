$(document).ready(function () {
    $('#frm-login').validate({
        rules: {
            iCountryId1 : {
                required:true,
            },
            vContactNo: {
                required: true
            },
            vPassword: {
                required: true
            }
        },
        messages: {
            iCountryId1 : {
                required : "Please select a country",
            },
            vContactNo: {
                required: 'Please enter registered phone number'
            },
            vPassword: {
                required: 'Please enter password'
            }
        },
        submitHandler: function (form) {
            $.ajax({
                url: rootPath + "user/login_action",
                type: "post",
                data: 'ajax=' + ($('#ajax').val()) + '&iCountryId=' + ($('#iCountryId1').val()) + '&vContactNo=' + ($('#vContactNo').val()) + '&vPassword=' + ($('#Password').val()),
                success: function (data) {
                    if (data == 'locations') {
                        window.parent.location = rootPath + 'locations';
                    } else {
                        $('#log_error').html(data);
                        $('#closeGroup').trigger('click');
                    }
                }
            });
        },
        showErrors: function (map, list)
        {
            this.currentElements.parent().parents('label:first, div:first').find('.has-error').remove();
            this.currentElements.parent().parents('.form-group:first').removeClass('has-error');
            $.each(list, function (index, error)
            {
                var ee = $(error.element);
                var eep = ee.parents('label:first').length ? ee.parents('label:first') : ee.parents('div:first');
                ee.parents('.form-group:first').addClass('has-error');
                eep.parent().find('.has-error').remove();
                eep.parent().append('<span class="has-error help-block">' + error.message + '</span>');
            });
            //refreshScrollers();
        }
    });
});