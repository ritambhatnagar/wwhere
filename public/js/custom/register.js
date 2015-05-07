$(document).ready(function ()
{
    jQuery("#vCity").autocomplete({
        source: function (request, response) {
            jQuery.getJSON(
                    "http://gd.geobytes.com/AutoCompleteCity?callback=?&q=" + $('#vCity').val(),
                    function (data) {
                        response(data);
                    }
            );
        },
        minLength: 3,
        select: function (event, ui) {
            var selectedObj = ui.item;
            jQuery("#vCity").val(selectedObj.value);
            return false;
        },
        open: function () {
            jQuery(this).removeClass("ui-corner-all").addClass("ui-corner-top");
        },
        close: function () {
            jQuery(this).removeClass("ui-corner-top").addClass("ui-corner-all");
        },
        appendTo: $('#autocomplete_list')
    });
    jQuery("#vCity").autocomplete("option", "delay", 100);
    
    $('#iCountryId').change(function () {
        var code = $(this).val();
        var code_data = code.split('|');
        $('#ccode').val("+"+code_data[1]);
    });

//    $.validator.addMethod("vPassword", function (value, element) {
//        return this.optional(element) || /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9])(?!.*\s).{6,20}$/.test(value);
//    }, "Password between 6 and 20 characters; must contain at least one lowercase letter, one uppercase letter, one numeric digit, and one special character, but cannot contain whitespace.");

    $.validator.setDefaults(
            {
                submitHandler: function (form) {
                    form.submit();
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

    $("#validateSignupForm").validate({
        rules: {
            vFirstName: "required",
//            vLastName: "required",
            vContactNo: {
                required: true,
                number: true,
                remote: {
                    url: rootPath + "user/userEmail",
                    type: "post",
                    data: {
                        c: function () {
                            return $('#ccode').val();
                        }
                    }
                }
            },
            vPassword: {
                required : true,
                minlength : 6,
                maxlength : 20
            },
            vPassword2: {
                required: true,
                equalTo: "#vPassword"
            },
            iCountryId: "required",
            vEmail: {
                required: true,
                email: true,
                remote: {
                    url: rootPath + "user/userEmail",
                    type: "post"
                }
            },
            ccode: {
                required: true
            },
            vCity: "required"

        },
        messages: {
            vFirstName: "Please enter your name",
            vLastName: "Please enter your Lastname",
            vContactNo: {
                required: "Please enter Contactno",
                number: "Please enter valid number",
                remote: 'ContactNo already exists'
            },
            vPassword : {
                required : "Please enter password",
                minlength : "Minimum length must be upto 6 characters",
                maxlength : "Maximum length must be upto 20 characters"
            },
            vPassword2: {
                required: "Please confirm password",
                equalTo: "Please enter the same password as above"
            },
            iCountryId: "Please select a country",
            vEmail: {
                required: 'Please enter Email address',
                email: 'Please enter valid email address',
                remote: 'Email address already exists'
            },
            ccode: "Enter valid number",
            vCity: "Please enter a city"
        }
    });
});