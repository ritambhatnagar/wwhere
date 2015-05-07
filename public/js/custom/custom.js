/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */


$(document).ready(function(){
    $('.login_dropdown').click(function(){
        if( $(".login_dropdown_div").is(":visible") ){
            $('.login_dropdown_div').slideUp();
        }else{
            $('.login_dropdown_div').slideDown();
        }
    });
});