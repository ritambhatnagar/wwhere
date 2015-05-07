var image_upload = UploadedImage = 0;
$(document).ready(function () {
/*$('body').bind('keydown', '.input.select2-input',function(e){
    if(e.keyCode == '32') {
        e.preventDefault();
        return;
    }
});
*/
$('#vUrlName').bind('keydown', function(e){
    if(e.keyCode == '32') {
        e.preventDefault();
        return;
    }
});


    $('#oldimg').hide();

    $('#dtStartDate').bdatepicker({
        format: "dd MM yyyy",
        startDate: "+0d"
    });
    $('#dtFinishDate').bdatepicker({
        format: "dd MM yyyy",
        startDate: "+0d"
    });
    $('#dtStartDate').bdatepicker().on('changeDate', function (e) {
        $('#dtFinishDate').prop('disabled', false);
        $('#dtFinishDate').bdatepicker('setStartDate', $(this).val());
    });
    jQuery("#vCity").autocomplete({
        source: function (request, response) {
            jQuery.getJSON(
                    "http://gd.geobytes.com/AutoCompleteCity?callback=?&q=" + $('#vCity').val(),
                    function (data) {
                        console.log(data);
                        if(data == "") return false;
                        else response(data);
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

    $('.delete_image').click(function () {
        var result = confirm('Are you sure you want to delete this ?');
        if (result) {
            var id = $(this).data('id');
            var im = $(this).data('im');
            $.ajax({
                type: "POST",
                data: {'iImageId': id},
                url: rootPath + "location/image_delete",
                success: function () {
                    $('#image' + im).show();
                    $('#vProfileImg' + im).hide();
                    $('#cancelbtn4all').trigger('click');
                    $('#confirmbtn4all').html('Confirm');
                    $('#confirmbtn4all').attr('disabled', false);
                }
            });
        }
    });

    $('.delete_location').click(function () {
        var result = confirm('Are you sure you want to delete this ?');
        if (result) {
            var id = $(this).data('id');
            $.ajax({
                type: "POST",
                data: {'iLocationId': id},
                url: rootPath + "location/l_deleteimg",
                success: function () {
                    $('#uploadlocationimg').show();
                    $('#vLocationImgss').hide();
                }
            });
        }
    });

    var tagsValue = $('#vTags').val();
    $("#vTags").select2({tags: [tagsValue], maximumSelectionSize: 10});

    var eType = '';
    $('input:radio[name="eType"]').each(function () {
        eType = $('input:radio[name="eType"]:checked').val();
    })

    $('input:radio[name="eType"]').change(function () {
        var selected_Type = $(this).val();
        $.ajax({
            type: 'POST',
            url: rootPath + 'location/getCategory',
            data: {eType: selected_Type},
            success: function (data) {
                $('.iCategoryId').html(data);
            }
        });
    });

    if (eType == '') {
        $('#rootwizard').hide();
        $('.headertitle').html('CREATE LOCATION');
    } else {
        $('#rootwizard').show();
        if (eType == 'Private') {
            $('#type_value').val('Private');
            $('#iCategoryId').hide();
            $('.iCategoryId').hide();
            $('#vBookingUrl').hide();
            $('.tab5').hide();
            $('#tab5').hide();
            if ($('#mode').val() == 'edit') {
                $('.headertitle').html('EDIT PERSONAL LOCATION');
            } else {
                $('.headertitle').html('CREATE PERSONAL LOCATION');
            }
        } else {

            $('#iCategoryId').show();
            $('.iCategoryId').show();
            $('#vBookingUrl').show();
/*            $('.tab5').show();
            $('#tab5').show();
*/            if (eType == 'Public') {
                $('#type_value').val('Public');
                $('#eventdiv').hide();
                $('#publicdiv').show();
                if ($('#mode').val() == 'edit') {
                    $('.headertitle').html('EDIT PUBLIC LOCATION');
                } else {
                    $('.headertitle').html('CREATE PUBLIC LOCATION');
                }
            } else {
                $('#type_value').val('Event');
                $('#publicdiv').hide();
                $('#eventdiv').show();
                if ($('#mode').val() == 'edit') {
                    $('.headertitle').html('EDIT EVENT LOCATION');
                } else {
                    $('.headertitle').html('CREATE EVENT LOCATION');
                }
            }
        }
    }

    //image1    
    $('#change1').click(function () {
        $('#image1').show();
        $('#vProfileImg1').hide();
        $('#cancel1').show();
        $('#change1').hide();
    })
    $('#cancel1').click(function () {
        $('#image1').hide();
        $('#vProfileImg1').show();
        $('#cancel1').hide();
        $('#change1').show();
    })

    //image2
    $('#change2').click(function () {
        $('#image2').show();
        $('#vProfileImg2').hide();
        $('#cancel2').show();
        $('#change2').hide();
    })
    $('#cancel2').click(function () {
        $('#image2').hide();
        $('#vProfileImg2').show();
        $('#cancel2').hide();
        $('#change2').show();
    })

    //image3
    $('#change3').click(function () {
        $('#image3').show();
        $('#vProfileImg3').hide();
        $('#cancel3').show();
        $('#change3').hide();
    })
    $('#cancel3').click(function () {
        $('#image3').hide();
        $('#vProfileImg3').show();
        $('#cancel3').hide();
        $('#change3').show();
    })

    //image4
    $('#change4').click(function () {
        $('#image4').show();
        $('#vProfileImg4').hide();
        $('#cancel4').show();
        $('#change4').hide();
    })
    $('#cancel4').click(function () {
        $('#image4').hide();
        $('#vProfileImg4').show();
        $('#cancel4').hide();
        $('#change4').show();
    })

    //image5
    $('#change5').click(function () {
        $('#image5').show();
        $('#vProfileImg5').hide();
        $('#cancel5').show();
        $('#change5').hide();
    })
    $('#cancel5').click(function () {
        $('#image5').hide();
        $('#vProfileImg5').show();
        $('#cancel5').hide();
        $('#change5').show();
    })
    //image

//    if ($('#mode').val() == 'edit') {
//        $('#vUrlName').attr('readonly', true);
//    }
//    $('#mode').change(function(){
//        if ($('#mode').val() == 'edit') {
//            $('#vUrlName').attr('readonly', true);
//        }
//    });
    $(".selectpickerdays").change(function () {
        var sel_class_arr = $(this).attr('class').split('_');
        var sel_class = sel_class_arr[1];
        if (($(this).val() == 'close' || $(this).val() == 'open_24')) {
            $('.day_' + sel_class).hide();
            $('.to').hide();
            $('.empty_' + sel_class).show();
            if ($(this).val() == 'open_24') {
                $('.day_' + sel_class).find("input[name*='opentime[" + sel_class + "]']").removeAttr('value').attr('value', '12:00 AM');
                $('.day_' + sel_class).find("input[name*='closetime[" + sel_class + "]']").removeAttr('value').attr('value', '11:59 PM');
            }
        } else {
            $('.to').show();
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
        $(this).bootstrapWizard({
            onNext: function (tab, navigation, index)
            {
                if (index == 1)
                {
                    $('#resize').trigger('click');
                }
                if (index == 2) {
                    if (!wiz.find('input:radio[name="eType"]').is(':checked')) {
                        wiz.find('#eTypeErr').html("Please select a location type that best describes your need");
                        wiz.find('#eTypeErr').css("color", "#a94442");
                        wiz.find('.eType').css("border", "1px solid #a94442;");
                        wiz.find('#eType').focus();
                        return false;
                    }
                }

                if (index == 3)
                {
                    if (!wiz.find('#vUrlName').val()) {
                        wiz.find('#vUrlNameErr').html("Please enter a URL");
                        wiz.find('#vUrlNameErr').css("color", "#a94442");
                        wiz.find('.vName').css("border", "1px solid #a94442");
                        wiz.find('#vUrlName').focus();
                        return false;
                    } else {
                        if (!checkUrl(wiz.find('#vUrlName').val())) {
                            return false;
                        }
                    }
                    // Make sure we entered the title
                    if (!wiz.find('#vName').val()) {
                        wiz.find('#vNameErr').html("Please enter the location name");
                        wiz.find('#vNameErr').css("color", "#a94442");
                        wiz.find('.vName').css("border", "1px solid #a94442");
                        wiz.find('#vName').focus();
                        return false;
                    }
                    if (!wiz.find('#iCategoryId').val()) {
                        wiz.find('#iCategoryIdErr').html("Please select Category");
                        wiz.find('#iCategoryIdErr').css("color", "#a94442");
                        wiz.find('.iCategoryId').css("border", "1px solid #a94442");
                        wiz.find('#iCategoryId').focus();
                        return false;
                    }
                    if (!wiz.find('#vAddress').val()) {
                        wiz.find('#vAddressErr').html("Please enter address");
                        wiz.find('#vAddressErr').css("color", "#a94442");
                        wiz.find('.vAddress').css("border", "1px solid #a94442");
                        wiz.find('#vAddress').focus();
                        return false;
                    }
                    if (!wiz.find('#vCity').val()) {
                        wiz.find('#vCityErr').html("Please enter city");
                        wiz.find('#vCityErr').css("color", "#a94442");
                        wiz.find('.vCity').css("border", "1px solid #a94442");
                        wiz.find('#vCity').focus();
                        return false;
                    }

                }

//                        if (wiz.find('#vUrlName').val() != '' && wiz.find('input:radio[name="eType"]').is(':checked') && wiz.find('#vName').val() != '') {
//                            newLocationInsert();
//                        }

            },
            onLast: function (tab, navigation, index)
            {
                if (!wiz.find('#vUrlName').val()) {
                    wiz.find('#vUrlNameErr').html("Please enter a URL");
                    wiz.find('#vUrlNameErr').css("color", "#a94442");
                    wiz.find('.vName').css("border", "1px solid #a94442");
                    wiz.find('#vUrlName').focus();
                    return false;
                } else {
                    if (!checkUrl(wiz.find('#vUrlName').val())) {
                        return false;
                    }
                }
                // Make sure we entered the title
                if (!wiz.find('#vName').val()) {
                    wiz.find('#vNameErr').html("Please enter the location name");
                    wiz.find('#vNameErr').css("color", "#a94442");
                    wiz.find('.vName').css("border", "1px solid #a94442");
                    wiz.find('#vName').focus();
                    return false;
                }

                if (!wiz.find('#iCategoryId').val()) {
                    wiz.find('#iCategoryIdErr').html("Please select Category");
                    wiz.find('#iCategoryIdErr').css("color", "#a94442");
                    wiz.find('.iCategoryId').css("border", "1px solid #a94442");
                    wiz.find('#iCategoryId').focus();
                    return false;
                }
                if (!wiz.find('#vAddress').val()) {
                    wiz.find('#vAddressErr').html("Please enter address");
                    wiz.find('#vAddressErr').css("color", "#a94442");
                    wiz.find('.vAddress').css("border", "1px solid #a94442");
                    wiz.find('#vAddress').focus();
                    return false;
                }
                if (!wiz.find('#vCity').val()) {
                    wiz.find('#vCityErr').html("Please enter city");
                    wiz.find('#vCityErr').css("color", "#a94442");
                    wiz.find('.vCity').css("border", "1px solid #a94442");
                    wiz.find('#vCity').focus();
                    return false;
                }
                //newLocationInsert();

            },
            onTabClick: function (tab, navigation, index)
            {
                if (wiz.find('li.active a').attr('href') == '#tab1') {
                    $('#resize').trigger('click');
                }
                else if (wiz.find('li.active a').attr('href') == '#tab2')
                {
                    if (!wiz.find('input:radio[name="eType"]').is(':checked')) {
                        wiz.find('#eTypeErr').html("Please select a location type that best describes your need");
                        wiz.find('#eTypeErr').css("color", "#a94442");
                        wiz.find('.eType').css("border", "1px solid #a94442;");
                        wiz.find('#eType').focus();
                        return false;
                    }
                    if (wiz.find('#vUrlName').val() == '' && wiz.find('#vName').val() == '') {
                        wiz.find('li:eq(1) a').tab('show');
                        return false;
                    }
                } else if (wiz.find('li.active a').attr('href') == '#tab3') {
                    // Make sure we entered the url
                    if (!wiz.find('#vUrlName').val()) {
                        wiz.find('#vUrlNameErr').html("Please enter a URL");
                        wiz.find('#vUrlNameErr').css("color", "#a94442");
                        wiz.find('.vUrlName').css("border", "1px solid #a94442");
                        wiz.find('#vUrlName').focus();
                        return false;
                    } else {
                        if (!checkUrl(wiz.find('#vUrlName').val())) {
                            return false;
                        }
                    }
                    // Make sure we entered the title
                    if (!wiz.find('#vName').val()) {
                        wiz.find('#vNameErr').html("Please enter the location name");
                        wiz.find('#vNameErr').css("color", "#a94442");
                        wiz.find('.vName').css("border", "1px solid #a94442");
                        wiz.find('#vName').focus();
                        return false;
                    }
                    if (!wiz.find('#iCategoryId').val()) {
                        wiz.find('#iCategoryIdErr').html("Please select Category");
                        wiz.find('#iCategoryIdErr').css("color", "#a94442");
                        wiz.find('.iCategoryId').css("border", "1px solid #a94442");
                        wiz.find('#iCategoryId').focus();
                        return false;
                    }
                    if (!wiz.find('#vAddress').val()) {
                        wiz.find('#vAddressErr').html("Please enter address");
                        wiz.find('#vAddressErr').css("color", "#a94442");
                        wiz.find('.vAddress').css("border", "1px solid #a94442");
                        wiz.find('#vAddress').focus();
                        return false;
                    }
                    if (!wiz.find('#vCity').val()) {
                        wiz.find('#vCityErr').html("Please enter city");
                        wiz.find('#vCityErr').css("color", "#a94442");
                        wiz.find('.vCity').css("border", "1px solid #a94442");
                        wiz.find('#vCity').focus();
                        return false;
                    }
                    // newLocationInsert();
                } else if (wiz.find('li.active a').attr('href') == '#tab4') {
                    if (wiz.find('#vUrlName').val() == '' && wiz.find('#vName').val() == '') {
                        wiz.find('li a[href=#tab3]').trigger('click');
                    }
                } else if (wiz.find('li.active a').attr('href') == '#tab5') {
                    if (wiz.find('#vUrlName').val() == '' && wiz.find('#vName').val() == '') {
                        wiz.find('li a[href=#tab3]').trigger('click');
                    }
                }
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
                    //newLocationInsert();
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
                if ($('input:radio[name="eType"]:checked').val() == 'Private') {
                    $total = 4;
                }
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
            //firstSelector: '.first',
            //lastSelector: '.last'
        });
        wiz.find('.finish').click(function ()
        {
            if (wiz.find('li.active a').prop('href') == '#tab3')
            {
                // Make sure we entered the url
                if (!wiz.find('#vUrlName').val()) {
                    wiz.find('#vUrlNameErr').html("Please enter a URL");
                    wiz.find('#vUrlNameErr').css("color", "#a94442");
                    wiz.find('.vUrlName').css("border", "1px solid #a94442");
                    wiz.find('#vUrlName').focus();
                    return false;
                } else {
                    if (!checkUrl(wiz.find('#vUrlName').val())) {
                        return false;
                    }
                }
                // Make sure we entered the title
                if (!wiz.find('#vName').val()) {
                    wiz.find('#vNameErr').html("Please enter the location name");
                    wiz.find('#vNameErr').css("color", "#a94442");
                    wiz.find('.vName').css("border", "1px solid #a94442");
                    wiz.find('#vName').focus();
                    return false;
                }
                if (!wiz.find('#iCategoryId').val()) {
                    wiz.find('#iCategoryIdErr').html("Please select Category");
                    wiz.find('#iCategoryIdErr').css("color", "#a94442");
                    wiz.find('.iCategoryId').css("border", "1px solid #a94442");
                    wiz.find('#iCategoryId').focus();
                    return false;
                }
                if (!wiz.find('#vAddress').val()) {
                    wiz.find('#vAddressErr').html("Please enter address");
                    wiz.find('#vAddressErr').css("color", "#a94442");
                    wiz.find('.vAddress').css("border", "1px solid #a94442");
                    wiz.find('#vAddress').focus();
                    return false;
                }
                if (!wiz.find('#vCity').val()) {
                    wiz.find('#vCityErr').html("Please enter city");
                    wiz.find('#vCityErr').css("color", "#a94442");
                    wiz.find('.vCity').css("border", "1px solid #a94442");
                    wiz.find('#vCity').focus();
                    return false;
                }

            }
            if (wiz.find('#vUrlName').val() != '' && wiz.find('input:radio[name="eType"]').is(':checked') && wiz.find('#vName').val() != '') {
                $('.finish').prop("disabled", true);
                $('.finish').children('a').prop("disabled", true).text('Please wait....');
                newLocationInsert();
            } else {
                return false;
            }
            //$('#submitimage').trigger('click');            

        });
    });

    $(".selectpicker").trigger('change');
    $('.timepicker').timepicker({
        minuteStep: 15,
        showSeconds: false,
        showMeridian: true,
        modalBackdrop: true
    });
    //===============upload file

    var uploadObj = $("#uploadlocationimg").uploadFile({url: rootPath + "public/uploadfiles/upload.php",
        multiple: true, //defino que no se puedan arrastrar y soltar mas de 1 archivo
        allowedTypes: "png,jpg,jpeg", // extensiones permitidas
        fileName: "myfile", //nombre del archivo a enviar por $_Files
        showDelete: true, //mostrar botón eliminar
        showDone: false, //ocultar botón de Hecho
        showProgress: false, //mostrar barra de progreso
        showPreview: true, //mostrar previsualización de las imagenes a cargar
        autoSubmit: false, //deshabilitar el envio del archivo automaticamente, para poder ser enviado se utiliza la función startUpload()
        showDownload: false,
        returnType: "json",
        showPreivew: true,
        maxFileCount: 1, //número máximo de archivos a subir
        maxFileSize: 3145728, //tamaño máximo permitido de los archivos en bytes, en MB: 3MB
        maxFileCountErrorStr: "Maximum file limit", //string que aparece al momento de tener un error del número máximo de archivos
        dragDropStr: "",
        sizeErrorStr: "Too big size of file not accepted", //string que aparece cuando los archivos superan el tamaño máximo permitido
        extErrorStr: "External error occured", //string que aparece cuando existe un error en las extensiones de los archivos a cargar
        cancelStr: "Cancel", //string del botón cancelar
        uploadButtonClass: "btn btn-info", //clase del botón de carga, se definió una clase de bootstrap
        dragdropWidth: "100%", //defino el ancho del area donde se arrastra y sueltan los archivos
        statusBarWidth: "100%", //defino el acho de la barra de estado.
        previewWidth: "20%",
        dynamicFormData: function ()
        {
//            var id = $("#idImagen").val(); //capturo el id de la imagen cargado en el input oculto
//            var titulo = $("#tituloImagen").val(); //capturo el titulo cargado en el input.
            // los datos que se van a enviar
            //alert($('#vUrlName').val());
            var data = {
                userid: $('#vUrlName').val(),
                table: 'location',
                site_path: site_path,
                mainfolder: 'locationimg'

            };
            //alert(data['idImagen']);
            //alert(data['tituloImagen']);
            return data; //debo retornar data para poder que se envien junto con las imagenes.
        },
        deleteCallback: function (data, pd) {
            for (var i = 0; i < data.length; i++) {
                $.post(rootPath + "public/uploadfiles/delete.php", {op: "delete", name: data[i], userid: ''},
                function (resp, textStatus, jqXHR) {
                    //Show Message	
                    //alert("File Deleted");
                });
            }
            pd.statusbar.hide(); //You choice.

        },
        downloadCallback: function (files, pd)
        {
            location.href = rootPath + "public/uploadfiles/download.php?filename=" + files[0];
        },
        onSuccess: function (files, data, xhr, pd) //función que se llama despues de haber subido los archivos.
        {
            // alert(data);
            $("#Message").html(data); // Mostrar la respuestas del script PHP.
        },
        onSubmit: function ()
        {
            //alert("File uploaded");
        },
    });

    //////
    var deleteuploadObj = $("#locimage").uploadFile({url: rootPath + "public/uploadfiles/upload.php",
        multiple: true, //defino que no se puedan arrastrar y soltar mas de 1 archivo
        allowedTypes: "png,jpg,jpeg", // extensiones permitidas
        fileName: "myfile", //nombre del archivo a enviar por $_Files
        showDelete: true, //mostrar botón eliminar
        showDone: false, //ocultar botón de Hecho
        showProgress: false, //mostrar barra de progreso
        showPreview: true, //mostrar previsualización de las imagenes a cargar
        autoSubmit: false, //deshabilitar el envio del archivo automaticamente, para poder ser enviado se utiliza la función startUpload()
        showDownload: false,
        returnType: "json",
        showPreivew: true,
        maxFileCount: 5, //número máximo de archivos a subir
        maxFileSize: 3145728, //tamaño máximo permitido de los archivos en bytes, en MB: 3MB
        maxFileCountErrorStr: "Maximum file limit", //string que aparece al momento de tener un error del número máximo de archivos
        dragDropStr: "",
        sizeErrorStr: "Too big size of file not accepted", //string que aparece cuando los archivos superan el tamaño máximo permitido
        extErrorStr: "External error occured", //string que aparece cuando existe un error en las extensiones de los archivos a cargar
        cancelStr: "Cancel", //string del botón cancelar
        uploadButtonClass: "btn btn-info", //clase del botón de carga, se definió una clase de bootstrap
        dragdropWidth: "100%", //defino el ancho del area donde se arrastra y sueltan los archivos
        statusBarWidth: "100%", //defino el acho de la barra de estado.
        previewWidth: "20%",
        dynamicFormData: function ()
        {
//            var id = $("#idImagen").val(); //capturo el id de la imagen cargado en el input oculto
//            var titulo = $("#tituloImagen").val(); //capturo el titulo cargado en el input.
            // los datos que se van a enviar
            //alert($('#vUrlName').val());
            var data = {
                userid: $('#vUrlName').val(),
                table: 'locationmulti',
                site_path: site_path,
                mainfolder: 'location'

            };
            //alert(data['idImagen']);
            //alert(data['tituloImagen']);
            return data; //debo retornar data para poder que se envien junto con las imagenes.
        },
        deleteCallback: function (data, pd) {
            for (var i = 0; i < data.length; i++) {
                $.post(rootPath + "public/uploadfiles/delete.php", {op: "delete", name: data[i], userid: ''},
                function (resp, textStatus, jqXHR) {
                    //Show Message	
                    //alert("File Deleted");
                });
            }
            pd.statusbar.hide(); //You choice.

        },
        downloadCallback: function (files, pd)
        {
            location.href = rootPath + "public/uploadfiles/download.php?filename=" + files[0];
        },
        onSuccess: function (files, data, xhr, pd) //función que se llama despues de haber subido los archivos.
        {
//            alert(data);
            $("#Message").html(data); // Mostrar la respuestas del script PHP.
        },
        onSubmit: function ()
        {

            //alert("File uploaded");
        },
    });

    $('#hiddentrigger').click(function () {
        var idid = $(this).val();
        uploadObj.update({
            onSubmit: function (data1)
            {
                $.ajax({
                    url: rootPath + 'location/updateImageName',
                    type: 'POST',
                    data: {
                        'iLocationId': idid,
                        'vLocationImage': data1
                    },
                    success: function (data) {
                        return true;
                    }
                });
            },
            dynamicFormData: function ()
            {
                var data = {
                    userid: idid,
                    table: 'location',
                    site_path: site_path,
                    mainfolder: 'locationimg'

                };
                return data;
            }
        });
        uploadObj.startUpload();
    });

    $('#hiddentriggerMulti').click(function () {
        // alert($(this).data('locid'))
        var idid2 = $(this).val();
        //var imagerdio = $('#imageredio').val();
        deleteuploadObj.update({
            onSubmit: function (data2)
            {
                $.ajax({
                    url: rootPath + 'location/insertMultiImageName',
                    type: 'POST',
                    data: {
                        'iLocationId': idid2,
                        'vLocationImage': data2,
                        //'imageredio': imagerdio,
                        'vUrlName': $('#vUrlName').val()
                    },
                    success: function (dataa) {
                        return true;
                    }
                });
            },
            dynamicFormData: function ()
            {
                var data = {
                    userid: idid2,
                    table: 'locationmulti',
                    site_path: site_path,
                    mainfolder: 'location'

                };
                return data;
            },
            onSuccess: function (files, data, xhr, pd)
            {
                image_upload++;
                if (image_upload == deleteuploadObj.getFileCount()) {
                    window.location.replace(rootPath + $('#vUrlName').val());
                }
            }
        });
        deleteuploadObj.startUpload();
        if (image_upload == deleteuploadObj.getFileCount()) {
            window.location.replace(rootPath + $('#vUrlName').val().toLowerCase());
        }
    });

    $(".selectpickerdays").trigger('change');
});

/*function vLogoUpLoading () {
    var formData = new FormData($('#vlogo-input')[0]);
    $.ajax({
        url: rootPath + 'location/uploadLogo',
        type: 'POST',
        data: formData,
        async: false,
        success: function (data) {
            var srcc = data.split('--')[0];
            $('#cropphoto').modal('show');
            $('#cropbox').removeAttr('src');
            $('#cropbox').attr('src', srcc);

            $('#cropbox').Jcrop({
              aspectRatio: 1,
              onSelect: updateCoords
            });

            $('#cropbox').css('max-width', '100%');
            $('#cropbox').css('width', '99%');

            $('#jcrop-holder').css('margin', '0px auto');
            setTimeout(function () {
                $('.jcrop-holder > img').css('max-width','100%');
            }, 100);


            $('#cropbtn').on('click', function(event) {
                event.preventDefault();
                $.ajax({
                    url: rootPath + 'location/cropUpload',
                    type: 'POST',
                    data: {
                        'x': $('#x').val(),
                        'y': $('#y').val(),
                        'w': $('#w').val(),
                        'h': $('#h').val(),
                    },
                    success: function (data) {
                        $('#cropphoto').modal('hide');
                    }
                });


            });

        },
        cache: false,
        contentType: false,
        processData: false
    });

    return false;
}*/


/*function updateCoords(c)
{
    console.log(c);
$('#x').val(c.x);
$('#y').val(c.y);
$('#w').val(c.w);
$('#h').val(c.h);
};

function checkCoords()
{
if (parseInt($('#w').val())) return true;
alert('Please select a crop region then press submit.');
return false;
};*/



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
            'eType': $('[name=eType]:checked').val(),
            'vLatitude': $('#vLatitude').val(),
            'vLongitude': $('#vLongitude').val(),
            'vContactNo': $('#vContactNoC').val(),
            'vWebsite': $('#vWebsite').val(),
            'vBookingUrl': $('#vBookingUrl').val(),
            'vFBPage': $('#vFBPage').val(),
            'vGplusPage': $('#vGplusPage').val(),
            'vTweetPage': $('#vTweetPage').val(),
            'vInstPage': $('#vInstPage').val(),
            'vTags': $('#vTags').val(),
            'opentime': open,
            'closetime': close,
            'statusArray': status,
            'dtStartDate': $('#dtStartDate').val(),
            'dtFinishDate': $('#dtFinishDate').val(),
            'vStartTime': $('#vStartTime').val(),
            'vFinishTime': $('#vFinishTime').val(),
            'imageredio': $('[name=imageredio]:checked').val(),
            'mode': $('#mode').val()
        },
        beforeSend: function (xhr) {
            $('.finish').prop("disabled", true);
        },
        success: function (data) {
            if (data) {
                var data1 = JSON.parse(data);
                $('#iLocationId').val(data1['locationid']);
                $('#hiddentriggerMulti').data('locid', data1['locationid']);
                $('#locaid').val(data1['locationid']);
                $('#mode').val(data1['mode']);
                $('#hiddentrigger').val(data1['locationid']);
                //$('#hiddentrigger').trigger('click');
                $('#hiddentriggerMulti').val(data1['locationid']);
                //$('#hiddentriggerMulti').trigger('click');
//                if(data1['mode'] == 'edit'){
//                    $('#vUrlName').attr('readonly',true);
//                }

                var urll = data.split(',')[2].split(':')[1].split('"')[1];
                var html = '<div class="modal fade" id="my-location-url"><div class="modal-dialog"><div class="modal-content" style="padding: 50px;font-size: 20px;"><div class="modal-body">Your location <b>http://wwhere.is/'+urll+'</b> has been created.</div></div></div></div>';
                $('body').append(html);
                $('#my-location-url').modal('show');
                setTimeout(function(){
                    $('#hiddentrigger').trigger('click');
                    $('#hiddentriggerMulti').trigger('click');
                },3000);
            }
        }
    });
}


function urlExist() {
    //if ($('#mode').val() == 'add') {
    var checkurl = checkUrl($('#vUrlName').val());

    if($('#vUrlName').val())

    if (!checkurl) {
        $('#vUrlNameErr').addClass('text-primary');
        $('#vUrlNameErr').text('Url between 1 and 25 characters; alpanumeric with at least 1 character, but cannot contain whitespace');
        return false;
    } else {
        $('#vUrlNameErr').text('');
    }
    $.ajax({
        url: rootPath + 'location/urlName',
        type: 'POST',
        data: {
            'vUrlName': $('#vUrlName').val(),
            'iLocationId': $('#iLocationId').val(),
            'mode': $('#mode').val()
        },
        success: function (data) {
            if(data == 'empty') {
                $('#urlcheck').text('cannot be empty');
                $('#urlcheck').addClass('error');
                return false;                
            }
            if (data == 'false') {
                $('#urlcheck').text('unavailable');
                $('#urlcheck').addClass('error');
                return false;
            } else {
                $('#urlcheck').addClass('error');
                $('#urlcheck').text('available');
            }
        }
    });
    //}
}


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
            $('.vBookingUrl').hide();
            $('.tab5').hide();
            $('#tab5').hide();
            $('.headertitle').html('CREATE PERSONAL LOCATION');
        } else {
            $('#iCategoryId').show();
            $('.iCategoryId').show();
            $('.vBookingUrl').show();
/*            $('.tab5').show();
            $('#tab5').show();*/
            if ($('#eType').val() == 'Public') {
                $('#eventdiv').hide();
                $('#publicdiv').show();
                $('.headertitle').html('CREATE PUBLIC LOCATION');
            } else {
                $('#publicdiv').hide();
                $('#eventdiv').show();
                $('.headertitle').html('CREATE EVENT LOCATION');
            }
        }
    }
    return true;
}
function deleteimg(obj, im, con) {

    if (con == "confirm") {
        $.ajax({
            type: "POST",
            data: {'iImageId': obj},
            url: rootPath + "location/image_delete",
            success: function () {
                $('#image' + im).show();
                $('#vProfileImg' + im).hide();
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
            var con = 'confirm';
            deleteimg(obj, im, con)
        })
        $('#confirmbtnlink').trigger('click')
    }
}

function l_deleteimg(obj, con) {

    if (con == "confirm") {
        $.ajax({
            type: "POST",
            data: {'iLocationId': obj},
            url: rootPath + "location/l_deleteimg",
            success: function () {
                $('#uploadlocationimg').show();
                $('#vLocationImgss').hide();
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
            var con = 'confirm';
            l_deleteimg(obj, con)
        })
        $('#confirmbtnlink').trigger('click')
    }
}

function checkUrl(data) {
    if ((data.length > 25)) {
        return false;
    } else {
//        return /^(?=.*[a-zA-Z])([a-zA-Z0-9_-]+)$/.test(data);
        return /^[a-zA-Z0-9\s]+$/.test(data);
    }
}

$(document).ready(function () {
$("#vContactNoC").keydown(function (e) {
        // Allow: backspace, delete, tab, escape, enter and .
        if ($.inArray(e.keyCode, [46, 8, 9, 27, 13, 110, 190]) !== -1 ||
             // Allow: Ctrl+A, Command+A
            (e.keyCode == 65 && ( e.ctrlKey === true || e.metaKey === true ) ) || 
             // Allow: home, end, left, right, down, up
            (e.keyCode >= 35 && e.keyCode <= 40)) {
                 // let it happen, don't do anything
                 return;
        }
        // Ensure that it is a number and stop the keypress
        if ((e.shiftKey || (e.keyCode < 48 || e.keyCode > 57)) && (e.keyCode < 96 || e.keyCode > 105)) {
            e.preventDefault();
        }
    });

    $('#vContactNoC').on('keyup', function (e) {
        var checkPhone = checkphone($(this).val());
        if (!checkPhone) {
            $('#vContactNoErr').addClass('text-danger');
            $('#vContactNoErr').text('Only enter numeric value, Can enter comma seperated value, Can enter only 32 digits');
            return false;
        } else {
            $('#vContactNoErr').text('');
        }
    });
    $('#vWebsite').on('keyup', function (e) {
        var checkUrl = checkWebUrl($(this).val());
        console.log(checkUrl);
        if (!checkUrl) {
            $('#vWebsiteErr').addClass('text-danger');
            $('#vWebsiteErr').text('Enter valid Url');
            return false;
        } else {
            $('#vWebsiteErr').text('');
        }
    });
    $('#vAddress').on('keyup', function (e) {
        var checkAddr = checkAddress($(this).val());
        if (!checkAddr) {
            $('#vAddressErr').addClass('text-danger');
            $('#vAddressErr').text('Your address must be upto 80 characters');
            return false;
        } else {
            $('#vAddressErr').text('');
        }
    });
    $('#vDescription').on('keydown', function (e) {
        var checkDes = checkDescription($(this).val());
        if (!checkDes) {
            $('#vDescriptionErr').addClass('text-danger');
            $('#vDescriptionErr').text('Your description must be upto 80 characters');
            return false;
        } else {
            $('#vDescriptionErr').text('');
        }
    });
    $('#vTags').on('keyup', function (e) {
        var checkTags = checkTag($(this).val());
        console.log(checkTags);
        if (!checkTags) {
            $('#vTagsErr').addClass('text-danger');
            $('#vTagsErr').text('Your tags must be upto 10 tags');
            return false;
        } else {
            $('#vTagsErr').text('');
        }
    });
//    var viewportWidth = $(window).width();
//        var viewportHeight = $(window).height();
//        alert(viewportWidth);
//    
//    $(window).resize(function() {
//        
//        if(viewportWidth == 360 && viewportHeight == 640){
//            
//        }
//    });
});

function checkWebUrl(url)
{
    //regular expression for URL
    //console.log(learnRegExp('http://www.google-com.123.com')); // true
//    console.log(learnRegExp('http://www.google-com.123')); // false
//    console.log(learnRegExp('https://www.google-com.com')); // true
//    console.log(learnRegExp('http://google-com.com')); // true
//    console.log(learnRegExp('http://google.com')); //true
//    console.log(learnRegExp('google.com')); //false
//    return /^(https?|ftp):\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?(((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)+(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?)(:\d*)?)(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)?(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i.test(checkWebUrl.arguments[0]);
    var urlregex = new RegExp(
            "^(http:\/\/www.|https:\/\/www.|ftp:\/\/www.|www.){1}([0-9A-Za-z]+\.)");
    return urlregex.test(checkWebUrl.arguments[0]);
}

function checkphone(data) {
    if (data.length > 32) {
        return false;
    } else {
        return /^[0-9\+)(\[\]{},-]+$/.test(data);
    }
}

function checkAddress(data) {
    if (data.length > 80) {
        return false;
    } else {
        return true;
    }
}
function checkDescription(data) {
    if (data.length > 80) {
        return false;
    } else {
        return true;
    }
}

function checkTag() {
    var data;
    data = $('#vTags').val();

    if (/^([^,]*,){0,10}[^,]*$/.test(data)) {
        return true;
    } else {
        return false;
    }
}

$(document).ready(function(){
    $('.delete_img').click(function(e){
        var id = $(this).data('id');
        var source = $(this).data('url');
        $.ajax({
            type : 'POST',
            url : rootPath + 'location/delete_img',
            async : false,
            data : {id : id,source : source},
            success: function (res) {
                if(res > 0){
                    $(e.target).closest('li').remove();
                }
                return false;
            }
        })
        return false;
    })
});