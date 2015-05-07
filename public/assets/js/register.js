$(document).ready(function ()
{
    $('#iCountryId').change(function () {
        var code = $(this).val();
        var code_data = code.split('|');
        $('#ccode').val(code_data[1]);
    });

    $.validator.addMethod("vPassword", function (value, element) {
        return this.optional(element) || /^(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{6,16}$/i.test(value);
    }, "Passwords are 6-16 characters with uppercase letters, lowercase letters and at least one number.");
    // validate signup form on keyup and submit

    $.validator.setDefaults(
            {
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

                        ee.parents('.form-group:first').addClass('has-error');
                        eep.find('.has-error').remove();
                        eep.append('<p class="has-error help-block">' + error.message + '</p>');
                    });
                    //refreshScrollers();
                }
            });

    $("#validateSignupForm").validate({
        rules: {
            vFirstName: "required",
            vLastName: "required",
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
            vPassword: "required vPassword",
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
            vCity: "required"

        },
        messages: {
            vFirstName: "Please enter your Firstname",
            vLastName: "Please enter your Lastname",
            vContactNo: {
                required: "Please enter a Contactno",
                number: "Please enter a valid number",
                remote: 'ContactNo already exists'
            },
            vPassword2: {
                required: "Please provide a password",
                equalTo: "Please enter the same password as above"
            },
            iCountryId: "Please select country",
            vEmail: {
                required: 'Please enter Email address',
                email: 'Please enter valid email address',
                remote: 'Email address already exists'
            },
            vCity: "Please enter City"
        }
    });
});