$(document).ready(function () {
     jQuery("#vCity").autocomplete({
        source: function (request, response) {
            jQuery.getJSON(
                    "http://gd.geobytes.com/AutoCompleteCity?callback=?&q=" + $('#vCity').val(),
                    function (data) {
                        response(data);
                    }
            );
        },
        minLength: 1,
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
    
    $.validator.addMethod("vPassword", function (value, element) {
        return this.optional(element) || /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z])(?=.*[^a-zA-Z0-9])(?!.*\s).{6,20}$/.test(value);
    }, "Password between 6 and 20 characters; must contain at least one lowercase letter, one uppercase letter, one numeric digit, and one special character, but cannot contain whitespace.");

    $("#user-form").validate({
        // Specify the validation rules
        rules: {
            vFirstName: "required",
            vLastName: "required",
            vEmail: {
                required: true,
                remote: {
                    type: "post",
                    url: rootPath + "user/checkEmail"
                }
            },
            vCompany: "required",
            vOldPassword:{
                required: true,
                remote:{
                    type: "post",
                    url: rootPath + "user/checkPassword"
                }
            },
            vPassword: "required vPassword",
            vPassword2: {
                required: true,
                minlength: 6,
                equalTo: "#vPassword"
            },
            iCountryId: "required",
            vContactNo: {
                required: true,
                minlength: 10,
                number: true
            }
//            vZipcode: "required vZipcode"
        },
        // Specify the validation error messages
        messages: {
            vFirstName: "Please enter First Name",
            vLastName: "Please enter Last Name",
            vEmail: {
                required: "Please enter valid Email",
                remote: "Email already exists"
            },
            vCompany: "Please enter Company",
            vOldPassword:{
                required:"Please enter old password",
                remote:"Password doesnt match"
            },
            vPassword2: {
                required: "Please provide a password",
                minlength: "Your password must be at least 6 characters long",
                equalTo: "Please enter same password to confirm"
            },
            iCountryId: "Please select Country",
            vContactNo: {
                required: "Please enter your phone number",
                minlength: "Please enter atleast 10 digits"
            }
        },
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
    
    $('#oldpassworddiv').hide();
    $('#passworddiv').hide();
    $('#password2div').hide();
    $('#changeid').click(function () {
        $('#oldpassworddiv').show();
        $('#passworddiv').show();
        $('#password2div').show();
        $('#changediv').hide();
        $('#vOldPassword').attr("name", "vOldPassword");
        $('#vPassword').attr("name", "vPassword");
        $('#vPassword2').attr("name", "vPassword2");
    });
    $('#cancelid').click(function () {
        $('#oldpassworddiv').hide();
        $('#passworddiv').hide();
        $('#password2div').hide();
        $('#vOldPassword').removeAttr("name");
        $('#vPassword').removeAttr("name");
        $('#vPassword2').removeAttr("name");
        $('#changediv').show();
    });
});