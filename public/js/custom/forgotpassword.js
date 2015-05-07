$(document).ready(function () {
    $("#forgot_password").validate({
        rules: {
            'login_contact': {
                required: true,
                number: true,
                remote: {
                    url: rootPath + "user/checkContact",
                    type: "post"
                }
            }
        },
        messages: {
            'login_contact': {
                required: "Please enter your registered phone number",
                number: "Please enter valid number",
                remote: "Contact Number doesn't match"
            }
        },
        submitHandler: function (form) {
            form.submit();
        },
        showErrors: function (map, list)
        {
            this.currentElements.parents('label:first, div:first').find('.has-error').remove();
            this.currentElements.parents('.form-group:first').removeClass('has-error');
            $.each(list, function (index, error)
            {
                var ee = $(error.element);
                var eep = ee.parents('label:first').length ? ee.parents('label:first') : ee.parents('div:first');
//                ee.addClass('has-error');
                eep.parent().find('.has-error').remove();
                eep.parent().append('<span class="has-error help-block">' + error.message + '</span>');
            });
            //refreshScrollers();
        }
    });
});

















