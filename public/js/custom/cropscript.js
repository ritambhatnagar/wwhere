function Crop () {
	this.id = "upload-crop-image";
	this.screenHeight = window.innerHeight;
	this.screenWidth = window.innerWidth;
	this.uploadUrl = "upload.php";
	this.x = 0;
	this.y = 0;
	this.h = 0;
	this.w = 0;
}
Crop.prototype.init = function() {
	var that = this;
	var mainArea = document.createElement("div");
	var canvas = document.createElement("canvas");
	var selectArea = document.createElement("span");
	var cropBtn = document.createElement("button");
	var cropBtnText = document.createTextNode("CROP");
	var doneBtn = document.createElement("button");
	var doneBtnText = document.createTextNode("Done");
	mainArea.appendChild(canvas);
	mainArea.appendChild(selectArea);
	cropBtn.appendChild(cropBtnText);
	mainArea.appendChild(cropBtn);
	doneBtn.appendChild(doneBtnText);
	mainArea.appendChild(doneBtn);
	//$(mainArea).insertBefore( ".navbar" );
	document.body.appendChild(mainArea);
	mainArea.id = "mainarea";
	canvas.id = "canvas";
	selectArea.id = "selectarea";
	cropBtn.id = "crop-btn";
	doneBtn.id = "done-btn";
	$('#crop-btn').hide();
	$('#done-btn').hide();
	this.canvas = document.getElementById("canvas");
	this.context = this.canvas.getContext('2d');

	$("#mainarea").css({
		height: that.screenHeight + "px",
		width: that.screenWidth + "px"
	}).hide();

	this.attachEvents();
};

Crop.prototype.attachEvents = function() {
	var that = this;
	$('#' + this.id).bind('change', function () {
		$("#mainarea").show();
        if (this.files && this.files[0]) {
            var reader = new FileReader();
            reader.onload = function (e) {
				var img = new Image();
				img.onload = function(){
					var ratio = this.width / this.height;
					var scalingRatio = this.width / 500;
					that.canvas.width = 500;
					that.canvas.height = 500 / ratio;
					that.context.drawImage( img, 0, 0, 500, that.canvas.height);
						
					$("#crop-btn")
						.css('margin-left', ((that.screenWidth - that.canvas.width) / 2 + (that.canvas.width / 2) - 30) + "px")
						.show()
						.bind('click', function() {
							$(this).hide();
							$('#selectarea').hide();
							that.context.clearRect(0, 0, that.canvas.width, that.canvas.height);
							that.canvas.width = that.w * scalingRatio;
							that.canvas.height = that.h * scalingRatio;
							$('#canvas').css({
								height: that.canvas.height,
								width: that.canvas.width,
								'margin-left': (that.screenWidth - that.canvas.width) / 2 + "px",
								'margin-top': (that.screenHeight - that.canvas.height) / 2 + "px"
							});

							$("#done-btn")
								.css('margin-left', ((that.screenWidth - that.canvas.width) / 2 + (that.canvas.width / 2) - 30) + "px")
								.show()
								.bind('click', function() {
									var dataurl = that.canvas.toDataURL("image/png");
									$(this).hide();
									$.ajax({ 
										type: "POST", 
										url: that.uploadUrl,
										dataType: 'text',
										data: {
										    base64data : dataurl
										},
										success:function(data) {
											$('#mainarea').hide();
											$('#'+that.id).attr('disabled','disabled'); 
											$('#location-logo-upload').attr('src', dataurl);
										}
									});
								});

							that.context.drawImage( img, that.x * scalingRatio, that.y * scalingRatio, that.w * scalingRatio, that.h * scalingRatio, 0, 0, that.canvas.height, that.canvas.width);							
							$('#done-btn').trigger('click');
						});

					$('#canvas').css({
						height: that.canvas.height,
						width: that.canvas.width,
						'margin-left': (that.screenWidth - that.canvas.width) / 2 + "px",
						'margin-top': (that.screenHeight - that.canvas.height) / 2 + "px"
					});

					$('#selectarea').css({
						left: (that.screenWidth - that.canvas.width) / 2 + "px",
						top: (that.screenHeight - that.canvas.height) / 2 + "px",
						height: that.canvas.width - (2*(that.canvas.width / 3)) + "px",
						width: that.canvas.width - (2*(that.canvas.width / 3)) + "px"
					});
						
					that.w = that.canvas.width - (2*(that.canvas.width / 3));
					that.h = that.canvas.width - (2*(that.canvas.width / 3));

					$("#selectarea").resizable({
						minHeight: 50,
						minWidth: 50,
						aspectRatio: 1,
						containment: '#canvas',
						resize: function( event, ui ) {
							that.w = ui.size.width;
							that.h = ui.size.height;
						}
					});

					$("#selectarea").draggable({
						containment: '#canvas',
						drag: function( event, ui ) {
							that.x = Math.abs($('#canvas').offset().left - $('#selectarea').position().left);
							that.y = Math.abs($('#canvas').offset().top - $('#selectarea').position().top);
						}
					});					
				};
				img.src = e.target.result;
            };
            reader.readAsDataURL(this.files[0]);
		}
	});
};