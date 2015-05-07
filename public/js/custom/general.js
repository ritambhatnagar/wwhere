var $timeout = 1000;

jQuery.browser = {};
(function () {
    jQuery.browser.msie = false;
    jQuery.browser.version = 0;
    if (navigator.userAgent.match(/MSIE ([0-9]+)\./)) {
        jQuery.browser.msie = true;
        jQuery.browser.version = RegExp.$1;
    }
})();

$(document).ready(function (e) {

    $('#widgetClose').click(function () {
        $('#spamcheckdiv').html('')
        $('#viewboxiframe').hide()
        $('#viewboxiframe').attr('src', '');
        $('#viewboxiframe').html('');
        $('#viewboxtitle').text('')
    });
//    $('.loaderdiv').fadeOut(1000);
//    setInterval(function () {
//        sessiontimeout();
//    }, (timeout * 60001));
});

function setCookie(cname, cvalue, exdays) {
    var d = new Date();
    d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
    var expires = "expires=" + d.toUTCString();
    document.cookie = cname + "=" + cvalue + "; " + expires;
}

function getCookie(cname) {
    var name = cname + "=";
    var ca = document.cookie.split(';');
    for (var i = 0; i < ca.length; i++) {
        var c = ca[i];
        while (c.charAt(0) == ' ')
            c = c.substring(1);
        if (c.indexOf(name) != -1)
            return c.substring(name.length, c.length);
    }
    return "";
}

function preventcall() {
    window.event.preventDefault();
}
var documentHtml = function (html)
{
    var result = String(html)
            .replace(/<\!DOCTYPE[^>]*>/i, '')
            .replace(/<(html|head|body|title|meta)([\s\>])/gi, '<div class="document-$1"$2')
            .replace(/<\/(html|head|body|title|meta)\>/gi, '</div>');

    return $.trim(result);
}

function sessiontimeout() {
    $.ajax({
        type: 'POST',
        url: 'chksession1',
        success: function (data) {
            if (data == 'timeout') {
                $.ajax({
                    type: 'POST',
                    url: rootPath + 'logout',
                    success: function (data) {
                        window.location.replace(window.location.href);
                    }});
            } else if (data == 'true') {
//                        alert(1)
            } else {
//                window.location.href = 'signout';
                window.location.replace('logout');
//                    alert('timeover')
            }
        }
    });
}


function getUserName() {

    $.ajax({
        type: 'POST',
        url: rootPath + 'content/getProfileName',
        success: function (data) {
            if (data != '') {
                $('.profile_name').text(data)
            }
        }
    });

}

function showformpage(obj, href) {
    if (typeof (href) == 'undefined') {
        href = $(obj).data('href');
    }
    if ($(obj).data('module') == 'product') {
        if ($("#iVendorId").val() == '') {
            $('#ProductsForm').valid();
            return false;
        }
    }
    $.ajax({
        async: true,
        url: href,
        success: function (results) {
            $('#viewformiframe').html(results)
        }
    });
    var namedata = href.split('N=');
    $('#viewformpagetitle').text(namedata[1]);
    $('#viewformlink').trigger('click');
}
