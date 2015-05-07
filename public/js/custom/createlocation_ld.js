$(document).ready(function () {
    if ($('#eType').val() == '') {
        $('#rootwizard').hide();
        $('.headertitle').html('CREATE LOCATION');
    } else {
        $('#rootwizard').show();
        if ($('#eType').val() == 'Private') {
            $('#iCategoryId').hide();
            $('.iCategoryId').hide();
            $('.tab4').hide();
            $('.headertitle').html('CREATE PERSONAL LOCATION');
        } else {
            $('#iCategoryId').show();
            $('.iCategoryId').show();
            $('.tab4').show();
            if ($('#eType').val() == 'Public') {
                $('.headertitle').html('CREATE PUBLIC LOCATION');
            } else {
                $('.headertitle').html('CREATE EVENT LOCATION');
            }
        }
    }
//    if ($('#mode').val() == 'edit') {
//        $('#vUrlName').attr('readonly', true);
//    }
//    $('#mode').change(function(){
//        if ($('#mode').val() == 'edit') {
//            $('#vUrlName').attr('readonly', true);
//        }
//    });
    $(".selectpicker").change(function () {
        var sel_class_arr = $(this).attr('class').split('_')
        var sel_class = sel_class_arr[1];
        if ($(this).val() === 'close') {
            $('.day_' + sel_class).hide();
            $('.empty_' + sel_class).show();
        } else {
            $('.day_' + sel_class).show();
            $('.empty_' + sel_class).hide();
        }
    });

    var bWizardTabClass = '';
    $('.wizard').each(function ()
    {
        if ($(this).is('#rootwizard'))
            bWizardTabClass = 'bwizard-steps';
        else
            bWizardTabClass = '';
        var wiz = $(this);
        $(this).bootstrapWizard(
                {
                    onNext: function (tab, navigation, index)
                    {
                        if (!wiz.find('#vName').val()) {
                            wiz.find('#vNameErr').html("Please enter Title");
                            wiz.find('#vNameErr').css("color", "#a94442");
                            wiz.find('.vName').css("border", "1px solid #a94442");
                            wiz.find('#vName').focus();
                            return false;
                        }
                        newLocationInsert();

                    },
                    onLast: function (tab, navigation, index)
                    {
                        // Make sure we entered the title
                        if (!wiz.find('#vName').val()) {
                            wiz.find('#vNameErr').html("Please enter Title");
                            wiz.find('#vNameErr').css("color", "#a94442");
                            wiz.find('.vName').css("border", "1px solid #a94442");
                            wiz.find('#vName').focus();
                            return false;
                        }
                        newLocationInsert();

                    },
                    onTabClick: function (tab, navigation, index)
                    {
                        if (index == 0)
                        {
                            // Make sure we entered the title
                            if (!wiz.find('#vName').val()) {
                                wiz.find('#vNameErr').html("Please enter Title");
                                wiz.find('#vNameErr').css("color", "#a94442");
                                wiz.find('.vName').css("border", "1px solid #a94442");
                                wiz.find('#vName').focus();
                                return false;
                            }

                        }
                        // Make sure we entered the url
                        if (!wiz.find('#vUrlName').val()) {
                            wiz.find('#vUrlNameErr').html("Please enter URL");
                            wiz.find('#vUrlNameErr').css("color", "#a94442");
                            wiz.find('.vUrlName').css("border", "1px solid #a94442");
                            wiz.find('#vUrlName').focus();
                            return false;
                        }
                        // Make sure we entered the title
                        if (!wiz.find('#vName').val()) {
                            wiz.find('#vNameErr').html("Please enter Title");
                            wiz.find('#vNameErr').css("color", "#a94442");
                            wiz.find('.vName').css("border", "1px solid #a94442");
                            wiz.find('#vName').focus();
                            return false;
                        }
                        newLocationInsert();


                    },
                    onTabShow: function (tab, navigation, index)
                    {
                        var $total = navigation.find('li:not(.status)').length;
                        var $current = index + 1;
                        var $percent = ($current / $total) * 100;
                        if (wiz.find('.progress-bar').length)
                        {
                            wiz.find('.progress-bar').css({width: $percent + '%'});
                            wiz.find('.progress-bar')
                                    .find('.step-current').html($current)
                                    .parent().find('.steps-total').html($total)
                                    .parent().find('.steps-percent').html(Math.round($percent) + "%");
                        }
                        if (index == 2) {
                            newLocationInsert();
                        }
                        // update status
                        if (wiz.find('.step-current').length)
                            wiz.find('.step-current').html($current);
                        if (wiz.find('.steps-total').length)
                            wiz.find('.steps-total').html($total);
                        if (wiz.find('.steps-complete').length)
                            wiz.find('.steps-complete').html(($current - 1));
                        // mark all previous tabs as complete
                        navigation.find('li:not(.status)').removeClass('primary');
                        navigation.find('li:not(.status):lt(' + ($current - 1) + ')').addClass('primary');
                        // If it's the last tab then hide the last button and show the finish instead
                        if ($current >= $total) {
                            wiz.find('.pagination .next').hide();
                            wiz.find('.pagination .finish').show();
                            wiz.find('.pagination .finish').removeClass('disabled');
                        } else {
                            wiz.find('.pagination .next').show();
                            wiz.find('.pagination .finish').hide();
                        }
                    },
                    tabClass: bWizardTabClass,
                    nextSelector: '.next',
                    previousSelector: '.previous',
                    firstSelector: '.first',
                    lastSelector: '.last'
                });
        wiz.find('.finish').click(function ()
        {
            //if ($('.finish').data('go') == 'yes') {
            newLocationInsert();
            window.location.replace(rootPath + 'location');
            //} else {
            //    return false;
            // }
            //alert('Finished!, Starting over!');
//            window.location.replace(rootPath + 'landing_page');
            //wiz.find("a[data-toggle*='tab']:first").trigger('click');
        });
    });

    $("#image-form").validate({
        rules: {
            
        },
        messages: {
            
        },
        submitHandler: function () {
            
            $.ajax({
                url: rootPath + "landing_page/insertUserTemplateFields",
                type: "post",
                data: 'ajax=true&vFieldType=' + ($('#vFieldType').val()) + '&vField=' + ($('#vField').val()) + '&iOrder=' + ($('#iOrder').val()) + '&iLeadFormId=' + ($('#iLeadFormId').val()) + '&fieldmode=' + ($('#fieldmode').val()) + '&iFieldId=' + ($('#iFieldId').val()) + '&vMandatory=' + vMandatory,
                success: function (data) {
                    var response = jQuery.parseJSON(data);
                    
                }
            });
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

    $(".selectpicker").trigger('change');
    $('.timepicker').timepicker({
        minuteStep: 15,
        showSeconds: false,
        showMeridian: true,
        modalBackdrop: true
    });
});

function newLocationInsert() {

    var open = new Array();
    $('[name^=opentime]').each(function () {
        open.push($(this).val());
    });
    var close = new Array();
    $('[name^=closetime]').each(function () {
        close.push($(this).val());
    });
    var status = new Array();
    $('[name^=status]').each(function () {
        status.push($(this).val());
    });

    $.ajax({
        url: rootPath + 'location/newLocationInsert',
        type: 'POST',
        data: {
            'iLocationId': $('#iLocationId').val(),
            'vUrlName': $('#vUrlName').val(),
            'vName': $('#vName').val(),
            'vDescription': $('#vDescription').val(),
            'vAddress': $('#vAddress').val(),
            'vCity': $('#vCity').val(),
            'iCategoryId': $('#iCategoryId').val(),
            'eType': $('#eType').val(),
            'vLatitude': $('#vLatitude').val(),
            'vLongitude': $('#vLongitude').val(),
            'vContactNo': $('#vContactNo').val(),
            'vWebsite': $('#vWebsite').val(),
            'vFBPage': $('#vFBPage').val(),
            'vGplusPage': $('#vGplusPage').val(),
            'vTweetPage': $('#vTweetPage').val(),
            'vInstPage': $('#vInstPage').val(),
            'vTags': $('#vTags').val(),
            'opentime': open,
            'closetime': close,
            'statusArray': status,
            'mode': $('#mode').val()
        },
        success: function (data) {
            if (data) {
                var data1 = JSON.parse(data);
                $('#iLocationId').val(data1['locationid']);
                $('#mode').val(data1['mode']);
//                if(data1['mode'] == 'edit'){
//                    $('#vUrlName').attr('readonly',true);
//                }
            }
        }
    });
}
function urlExist() {
    //if ($('#mode').val() == 'add') {
    $.ajax({
        url: rootPath + 'location/urlName',
        type: 'POST',
        data: {
            'vUrlName': $('#vUrlName').val(),
            'iLocationId': $('#iLocationId').val(),
            'mode': $('#mode').val()
        },
        success: function (data) {
            if (data == 'false') {
                $('#vUrlNameErr').addClass('text-primary');
                $('#vUrlNameErr').text('not available');
                $('#vUrlNameErr').addClass('text-danger');
                return false;
            } else {
                $('#vUrlNameErr').addClass('text-primary');
                $('#vUrlNameErr').text('available');
            }
        }
    });
    //}
}
//function updateStepThree() {

//    $.ajax({
//        url: rootPath + 'location/updateStepThree',
//        type: 'POST',
//        data: {
//            'iLocationId': $('#iLocationId').val(),
//            'vLatitude': $('#vLatitude').val(),
//            'vLongitude': $('#vLongitude').val(),
//            'vContactNo': $('#vContactNo').val(),
//            'vWebsite': $('#vWebsite').val(),
//            'vFBPage': $('#vFBPage').val(),
//            'vGplusPage': $('#vGplusPage').val(),
//            'vTweetPage': $('#vTweetPage').val(),
//            'vInstPage': $('#vInstPage').val(),
//            'vTags': $('#vTags').val(),
//            'mode': $('#mode').val()
//        },
//        success: function (data) {
//            if (data) {
//                var data1 = JSON.parse(data);
//                $('#iLocationId').val(data1['locationid']);
//                $('#mode').val(data1['mode']);
//            }
//        }
//    });
//}

function changeEType(vall) {
    $('#eType').val(vall);
    if ($('#eType').val() == '') {
        $('#rootwizard').hide();
        $('.headertitle').html('CREATE LOCATION');
    } else {
        $('#rootwizard').show();
        if ($('#eType').val() == 'Private') {
            $('#iCategoryId').hide();
            $('.iCategoryId').hide();
            $('.tab4').hide();
            $('.headertitle').html('CREATE PERSONAL LOCATION');
        } else {
            $('#iCategoryId').show();
            $('.iCategoryId').show();
            $('.tab4').show();
            if ($('#eType').val() == 'Public') {
                $('.headertitle').html('CREATE PUBLIC LOCATION');
            } else {
                $('.headertitle').html('CREATE EVENT LOCATION');
            }
        }
    }
    return true;
}

//function timingAction() {
//    var open = new Array();
//    $('[name^=opentime]').each(function () {
//        open.push($(this).val());
//    });
//    var close = new Array();
//    $('[name^=closetime]').each(function () {
//        close.push($(this).val());
//    });
//
//    $.ajax({
//        url: rootPath + 'location/timing_action',
//        type: 'POST',
//        data: {
//            'iLocationId': $('#iLocationId').val(),
//            'opentime': open,
//            'closetime': close,
//            'mode': $('#mode').val()
//        },
//        success: function (data) {
//            if (data) {
//                var data1 = JSON.parse(data);
//                $('#iLocationId').val(data1['locationid']);
//                $('#mode').val(data1['mode']);
//            }
//        }
//    });
//}