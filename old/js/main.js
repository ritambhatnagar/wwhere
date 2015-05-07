
function myfunc(){

/*	alert('hellosdf');
	tryajax();
	$(".row").hide();
*/
$('#mailer_form').submit();
}

function tryajax(){
 $.ajax({
        url: "http://yogesh.joeee.com/locator/database_apis/apicall.php",
        type: "POST",
        //crossDomain: true,
        data: '{"0":"a@aa.com", "1":"1"}',
        //dataType: 'JSON',
        success: function(){
            alert("success");
        },
        error:function(){
            alert("failure");
        }
    });
}