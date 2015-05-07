// WRITEN BY KARDOS RUDOLF @ PSD-DESIGNER.NET

// FOR CONTENT
$(document).ready( function() {
	$("#CP").hide();
	$("#CS").hide();
	$("#CA").hide();
	$("#CC").hide();
});

// NAVIGATION / MENU SHOW HIDE
$(document).ready( function() {
	$("#button_nav_a").click(function() {
		$("#menu").transition({ y: '330px' });
		$("#basket_content").fadeOut(300);
			$("#button_nav_a").hide();
			$("#button_nav_b").show();
	});
});

$(document).ready( function() {
	$("#button_nav_b").click(function() {
		$("#menu").transition({ y: '0px' });
			$("#basket_content").fadeOut(300);
			$("#button_nav_a").show();
			$("#button_nav_b").hide();
	});
});


/* NAVIGATION LOAD */
$(document).ready(function(){
	$("#HB").click( function() {
		$("#menu").transition({ y: '0px' });
			$("#button_nav_a").show();
			$("#button_nav_b").hide();
			
			$("#basket_content").fadeOut(300);
			$("#cart_a").show();
			$("#cart_b").hide();
		$("#CH").fadeIn(400);
		$("#CP").fadeOut(100).delay(300);
		$("#CS").fadeOut(100).delay(300);
		$("#CA").fadeOut(100).delay(300);
		$("#CC").fadeOut(100).delay(300);
	});
	
	$("#PB").click( function() {
		$("#menu").transition({ y: '0px' });
			$("#button_nav_a").show();
			$("#button_nav_b").hide();
			
			$("#basket_content").fadeOut(300);
			$("#cart_a").show();
			$("#cart_b").hide();
		$("#CH").fadeOut(100).delay(300);
		$("#CP").fadeIn(400);
		$("#CS").fadeOut(100).delay(300);
		$("#CA").fadeOut(100).delay(300);
		$("#CC").fadeOut(100).delay(300);
		$("#MainContent").fadeIn(450);
	});
	
	$("#SB").click( function() {
		$("#menu").transition({ y: '0px' });
			$("#button_nav_a").show();
			$("#button_nav_b").hide();
			
			$("#basket_content").fadeOut(300);
			$("#cart_a").show();
			$("#cart_b").hide();
		$("#CH").fadeOut(100).delay(300);
		$("#CP").fadeOut(100).delay(300);
		$("#CS").fadeIn(400);
		$("#CA").fadeOut(100).delay(300);
		$("#CC").fadeOut(100).delay(300);
	});
	
	$("#AB").click( function() {
		$("#menu").transition({ y: '0px' });
			$("#button_nav_a").show();
			$("#button_nav_b").hide();
			
			$("#basket_content").fadeOut(300);
			$("#cart_a").show();
			$("#cart_b").hide();
		$("#CH").fadeOut(100).delay(300);
		$("#CP").fadeOut(100).delay(300);
		$("#CS").fadeOut(100).delay(300);
		$("#CA").fadeIn(400);
		$("#CC").fadeOut(100).delay(300);
	});
	
	$("#CB").click( function() {
		$("#menu").transition({ y: '0px' });
			$("#button_nav_a").show();
			$("#button_nav_b").hide();
			
			$("#basket_content").fadeOut(300);
			$("#cart_a").show();
			$("#cart_b").hide();
		$("#CH").fadeOut(100).delay(300);
		$("#CP").fadeOut(100).delay(300);
		$("#CS").fadeOut(100).delay(300);
		$("#CA").fadeOut(100).delay(300);
		$("#CC").fadeIn(400);
	});
	
	/* BOTTOM CONTACT EMAIL BUTTON LOAD THE CONTACT MENU PAGE */
	$(".MB").click( function() {
		$("#menu").transition({ y: '0px' });
			$("#button_nav_a").show();
			$("#button_nav_b").hide();
			
			$("#basket_content").fadeOut(300);
			$("#cart_a").show();
			$("#cart_b").hide();
		$("#CH").fadeOut(100).delay(450);
		$("#CP").fadeOut(100).delay(450);
		$("#CS").fadeOut(100).delay(450);
		$("#CA").fadeOut(100).delay(450);
		$("#CC").fadeIn(400);
	});
	
});

// SOCIAL NETWORK SHOW HIDE
$(document).ready( function() {
	$("#social").toggle(function() {
		$("#social_icons").slideDown( 200 ) }, function(){
			$("#social_icons").slideUp( 200 ); 
	});
});

// CART / SHOW HIDE
$(document).ready( function() {
	$("#cart_a").click(function() {
		$("#basket_content").fadeIn(300);
		$("#menu").transition({ y: '0px' });
			$("#cart_a").hide();
			$("#cart_b").show();
	});
});

$(document).ready( function() {
	$("#cart_b").click(function() {
		$("#basket_content").fadeOut(300);
			$("#cart_a").show();
			$("#cart_b").hide();
	});
});

/* INFORMATION BUBBLES HIDE */
$(document).ready( function() {
	$(".whide").click( function() {
		$(".warning").fadeOut(500);
	});
	
	$(".nhide").click( function() {
		$(".notice").fadeOut(500);
	});
	
	$(".nnhide").click( function() {
		$(".note").fadeOut(500);
	});
});

