
$(function ()
{
    /* Select2 - Advanced Select Controls */
    if (typeof $.fn.select2 != 'undefined')
    {

        // Placeholders
        $("#vContactGroupId").select2({
            placeholder: "Select a Group"
        });

        $("#vLeadStatus").select2({
            placeholder: "Select a Lead Status",
            allowClear: true
        });
        $("#vContactSource").select2({
            placeholder: "Select a Contact Source",
            allowClear: true
        });
        $("#eSubscribe").select2({
            placeholder: "Select a State",
            allowClear: true
        });
        $('#vContactType').select2({
            placeholder: "Contact Type",
            allowClear: true
        });
        $('#addfield').select2({
            placeholder: "Add+",
            allowClear: true
        });
    }

});
$(document).ready(function () {
    $('#addfield').change(function () {
        var dd = '';
        var myString = $(this).val();
        var myArray = myString.split('|');
        var fvalue = myArray[0];
        myArray = myArray[1].split(',');
        // display the result in myDiv
        for (var i = 0; i < myArray.length; i++) {
            if (i == 0) {
                dd = "<select class='form-control' name='contenttype[" + fvalue + "][contact_type][]' data-style='btn-default'>";

            }
            dd += "<option value='" + myArray[i] + "'>" + myArray[i] + "</option>";

            if (i == myArray.length - 1) {
                dd += "</select>";
            }
        }

        $('#labelappend').append('<div class="form-group"><label class="control-label col-md-4">' + fvalue + '</label><div class="col-md-3">' + dd + '</div><div class="col-md-4" id="divappend"><input class="form-control" type="' + fvalue.toLowerCase() + '" name="contenttype[' + fvalue + '][field_value][]" placeholder="Enter ' + fvalue + '"><i class="fa fa-trash-o" id="remove"></i></div></div>');

        $('form').on('click', '#remove', function () {
            $('#labelappend').remove();
        });
    });

//    <select id="'+$(this).val()+'" style="width: 100%;">
//        <option value=""></option>
//    </select>
});