function getViewBoundingBox()
{
	var topLeft = getRADecForScreenPoint(0, 0);
	var topMiddle = getRADecForScreenPoint(width_div_2, 0);
	var topRight = getRADecForScreenPoint(width, 0);
	var bottomLeft = getRADecForScreenPoint(0, height);
	var bottomMiddle = getRADecForScreenPoint(width_div_2, height);
	var bottomRight = getRADecForScreenPoint(width, height);
	
	var retval = { };
	retval.north = Math.ceil(Math.max(Math.max(topLeft.Dec, topRight.Dec), topMiddle.Dec));
	retval.east = Math.ceil(Math.max(bottomRight.RA, topRight.RA));
	retval.south = Math.floor(Math.min(Math.min(bottomLeft.Dec, bottomRight.Dec), bottomMiddle.Dec));
	retval.west = Math.floor(Math.min(topLeft.RA, bottomLeft.RA));
	
	return retval;
}

function getRADecForScreenPoint(x, y)
{
	//console.log("Width: " + x + " Y: " + y);
	var dx = tan_fov_div_2 * ((x / width_div_2) - 1.0) * aspect;
	var dy = tan_fov_div_2 * (1.0 - (y / height_div_2));
	
	var proj = new J3DIVector3(x/width, y/height, 1.0);

	//var pMatrix = new J3DIMatrix4();
	//pMatrix.perspective(fov, width/height, 1, 10000);
	//pMatrix.invert();
	
	//proj.multVecMatrix(pMatrix);
	
	//console.log("proj = " + proj);
	
	
	var p1x = -dx;
	var p1y = -dy;
	var p1z = -1.0;
	
	var currentMVMatrix = new J3DIMatrix4();
	
	currentMVMatrix.makeIdentity();    
    
	currentMVMatrix.rotate(rotateY, 0,0,1);
    currentMVMatrix.rotate(rotateX, 0,1,0);          
	//currentMVMatrix.translate(0, 0, 2); 
	//currentMVMatrix.scale(currScale, currScale, currScale); 
	//currentMVMatrix.translate(0, 0, -2); 
	
	//currentMVMatrix.invert();
	
	var dirX = p1x*currentMVMatrix.get(1,1) + p1y*currentMVMatrix.get(2,1) + p1z*currentMVMatrix.get(3,1);
	var dirY = p1x*currentMVMatrix.get(1,2) + p1y*currentMVMatrix.get(2,2) + p1z*currentMVMatrix.get(3,2);
	var dirZ = p1x*currentMVMatrix.get(1,3) + p1y*currentMVMatrix.get(2,3) + p1z*currentMVMatrix.get(3,3);
	//console.log(dirX);
	var origX = currentMVMatrix.get(4,1);
	var origY = currentMVMatrix.get(4,2);
	var origZ = currentMVMatrix.get(4,3);
	
	/*
	p2x = p2x*currentMVMatrix.m11 + p2y*currentMVMatrix.m21 + p2z*currentMVMatrix.m31;
	p2y = p2x*currentMVMatrix.m12 + p2y*currentMVMatrix.m22 + p2z*currentMVMatrix.m32;
	p2z = p2x*currentMVMatrix.m13 + p2y*currentMVMatrix.m23 + p2z*currentMVMatrix.m33;
	*/
	
	var A = dirX*dirX + dirY*dirY + dirZ*dirZ;
	var B = 2 * (dirX*origX + dirY*origY + dirZ*origZ);
	var C = (origX*origX + origY*origY + origZ*origZ) - 4;
	
	if(B*B - 4*A*C < 0)	//No intersection!
	{
		var retval = { };
	
		retval.RA = Number.NaN;
		retval.Dec = Number.NaN;
		return retval;
	}
	
	var t0 = (-B - Math.sqrt(B*B - 4*A*C))/(2*A);
	var t1 = (-B + Math.sqrt(B*B - 4*A*C))/(2*A);
	//console
	var t = 0;
	if(t0 > 0 && t1 > 0)
	{
		t = Math.max(t0, t1);
	}
	else if(t0 > 0)
	{
		t = t0;
	}
	else if(t1 > 0)
	{
		t = t1;
	}
	else	//No intersection in front of click!
	{
		var retval = { };
	
		retval.RA = 'BOB';
		retval.Dec = 'LARRY';
		return retval;
	}
	
	var x = origX + t*dirX;
	var y = origY + t*dirY;
	var z = origZ + t*dirZ;
	
	var length = Math.sqrt(x*x + y*y + z*z);
	x /= length;
	y /= length;
	z /= length;
	
	
	
	var retval = { };
	
	retval.RA = (Math.acos((x*0 + 0*0 + z*-1)/Math.sqrt(x*x + z*z)) * 180.0) / Math.PI;
	if(x > 0)
		retval.RA *= -1;
		
	retval.Dec = (Math.acos((x*x + y*0 + z*z)/Math.sqrt(x*x + z*z)) * 180.0) / Math.PI;
	if(y > 0)
		retval.Dec *= -1;
		
	if(retval.Dec > 90)
	{
		retval.Dec = 90 - (retval.Dec - 90);
	}
	else if(retval.Dec < -90)
	{
		retval.Dec = -90 - (retval.Dec + 90);
	}
	
	//document.getElementById("xyz").innerHTML = " X:" + raVecX + " Y:" + raVecY + " Z:" + raVecZ;
	//document.getElementById("xyz").innerHTML = " X:" + x + " Y:" + y + " Z:" + z;
	return retval;
}


var currentRADec = { };
var oldClientX = 0;
var oldClientY = 0;
function updateRADec(event)
{
	if(!event.clientX)
	{
		event.clientX = oldClientX;
	}
	else
	{
		oldClientX = event.clientX;
	}
	
	if(!event.clientY)
	{
		event.clientY = oldClientY;
	}
	else
	{
		oldClientY = event.clientY;
	}
	
	currentRADec = getRADecForScreenPoint(event.clientX, event.clientY);

	
	document.getElementById("Dec").innerHTML = rotateY-180; //currentRADec.Dec;
	document.getElementById("RA").innerHTML = (360-rotateX); //currentRADec.RA;
	
	//document.getElementById("Dec").innerHTML = rotateX;
	//document.getElementById("RA").innerHTML = rotateY;
	//document.getElementById("xyz").innerHTML = currScale;
}

function updateRADecForPoint(x, y)
{
	currentRADec = getRADecForScreenPoint(x, y);

	
	document.getElementById("Dec").innerHTML = rotateY-180; //currentRADec.Dec;
	document.getElementById("RA").innerHTML = (360-rotateX); //currentRADec.RA;
}

/* mouse Wheel */
function handle(delta) {
	
	if (delta < 0){
		
		console.log("zoom out");
		
		if(fov >= 0)
		{
			fov = fov - 1;
			updateView();
		}
	}
	
	else{
		 console.log("zoom in");
		
		if(fov > 0)
		{
			fov = fov + 1;
			updateView();
		}
	}
}

function wheel(event){
	var delta = 0;
	if (!event) event = window.event;
	if (event.wheelDelta) {
		delta = event.wheelDelta/120; 
		if (window.opera) delta = -delta;
	} else if (event.detail) {
		delta = -event.detail/3;
	}
	if (delta)
		handle(delta);
        if (event.preventDefault)
                event.preventDefault();
        event.returnValue = false;
}

var mousePressed = false;
var oldMouseX = 0;
var oldMouseY = 0;
var rotateY = 180.0+30;
var rotateX = 180.0;
function pressMouse(event)
{
	if (event.which == 3)
	{
		//fov = fov + 5;
		//alert("Right ");
	}
	if (event.which == 1)
	{
		//fov = fov - 5;
		//alert("Right mouse click");
	}
	mousePressed = true;
	oldMouseX = event.clientX;
	oldMouseY = event.clientY;
}
var oldNorth;
var oldSouth;
var oldEast;
var oldWest;
function releaseMouse(event)
{	
	mousePressed = false;
	
	var box = getViewBoundingBox();
	
	
	
	// --- Query for images from SDSS and FIRST ----
	if((((360 - rotateX)+fov) > oldEast) || (((360 - rotateX)-fov) < oldWest) || (((rotateY-180)+fov) > oldNorth) || (((rotateY-180)-fov) < oldSouth))
	{
		if(fov < 20) updateView();
	}
	// ---------------------------------------------
	
	//alert("Right mouse click");
	/*for(var i = 0; i<overlay.length; i++)
	{
		loadImageTextureWithBox(gl, "updateOverlay.php?north=" + box.north + "&east=" + box.east + "&south=" + box.south + "&west=" + box.west + "&arcSecondsRadius=10&keyVal=" + document.getElementById(String(i) + "dbFields").options[document.getElementById(String(i) + "dbFields").selectedIndex].value + "&keyMin=" + document.getElementById(String(i) + "keyMin").value + "&keyMax=" + document.getElementById(String(i) + "keyMax").value, box, i);
		document.getElementById("xyz").innerHTML = "updateOverlay.php?north=" + box.north + "&east=" + box.east + "&south=" + box.south + "&west=" + box.west + "&arcSecondsRadius=10&keyVal=" + document.getElementById(String(i) + "dbFields").options[document.getElementById(String(i) + "dbFields").selectedIndex].value + "&keyMin=" + document.getElementById(String(i) + "keyMin").value + "&keyMax=" + document.getElementById(String(i) + "keyMax").value;
		document.getElementById(String(i) + "loader").style.opacity = 1.0;
	}*/
	
}

function updateView()
{
		
	gl.uniform1f(gl.getUniformLocation(gl.program, "sdssRA"), (360 - rotateX) - (fov));
	gl.uniform1f(gl.getUniformLocation(gl.program, "sdssDec"), (rotateY-180) - (fov));
	gl.uniform1f(gl.getUniformLocation(gl.program, "sdssWidth"), 2.0*(fov));
	gl.uniform1f(gl.getUniformLocation(gl.program, "sdssHeight"),2.0*(fov));
	
	gl.uniform1f(gl.getUniformLocation(gl.program, "firstRA"), (360 - rotateX) - (fov));
	gl.uniform1f(gl.getUniformLocation(gl.program, "firstDec"), (rotateY-180) - (fov));
	gl.uniform1f(gl.getUniformLocation(gl.program, "firstWidth"), 2.0*(fov));
	gl.uniform1f(gl.getUniformLocation(gl.program, "firstHeight"),2.0*(fov));
	/*if(((rotateY-180) - 3.0*fov) < -10.0)
	{	
		gl.uniform1f(gl.getUniformLocation(gl.program, "sdssDec"), -10);
		gl.uniform1f(gl.getUniformLocation(gl.program, "sdssHeight"), ((rotateY-180) - 3.0*fov) + 10.0);
	}*//*
	if((((360 - rotateX) - 3.0*fov) < 310) && ((360 - rotateX) > 60))
	{	
		//gl.uniform1f(gl.getUniformLocation(gl.program, "sdssDec"), (rotateY-180) - 3.0*fov);
		gl.uniform1f(gl.getUniformLocation(gl.program, "sdssWidth"), ((360 - rotateX) + 3.0*fov) - 310);
	}*//*
	if((((360 - rotateX) < 310) && (((360 - rotateX) + 3.0*fov) > 60))
	{
		gl.uniform1f(gl.getUniformLocation(gl.program, "sdssWidth"), 60.0 - ((360 - rotateX) - 3.0*fov));
	}*/
	
	
	var scale = (fov*3600.0)/width;
	//var url = "SDSS.php?ra=" + ((360 - rotateX)) + "&dec=" + ((rotateY-180)) + "&scale=" + scale + "&width=" + Math.floor(((6.0*fov)*scale*15.0)) + "&height=" + Math.floor(((4.0*fov)*scale*15.0));
	var url = "SDSS.php?ra=" + ((360 - rotateX)) + "&dec=" + ((rotateY-180)) + "&scale=" + scale + "&width=" + width + "&height=" + width;
	var urlB = "FIRST.php?ra=" + ((360 - rotateX)) + "&dec=" + ((rotateY-180)) + "&scale=" + scale + "&width=" + width + "&height=" + width;
	SDSStexture = loadImageTexture(gl, url);
	FIRSTtexture = loadImageTexture(gl, urlB);
	console.log("width: " + width);
	document.getElementById('xyz').innerHTML = scale;
	oldWest = (360 - rotateX) - fov;
	oldEast = (360 - rotateX) + fov;
	oldNorth = (rotateY-180) + fov;
	oldSouth = (rotateY-180) - fov;
}

function loadImageTextureWithBox(ctx, url, box, qNumber)
{
    var texture = ctx.createTexture();
    texture.image = new Image();
    texture.image.onload = function() { doLoadImageTextureWithBox(ctx, texture.image, texture, box, qNumber) }
    texture.image.src = url;
    return texture;
}

function doLoadImageTextureWithBox(ctx, image, texture, box, qNumber)
{
    ctx.bindTexture(ctx.TEXTURE_2D, texture);
    ctx.texImage2D( gl.TEXTURE_2D, 0, gl.RGBA, gl.RGBA, gl.UNSIGNED_BYTE, image);
    ctx.texParameteri(ctx.TEXTURE_2D, ctx.TEXTURE_MAG_FILTER, ctx.LINEAR);
    ctx.texParameteri(ctx.TEXTURE_2D, ctx.TEXTURE_MIN_FILTER, ctx.LINEAR);
    ctx.texParameteri(ctx.TEXTURE_2D, ctx.TEXTURE_WRAP_S, ctx.CLAMP_TO_EDGE);
    ctx.texParameteri(ctx.TEXTURE_2D, ctx.TEXTURE_WRAP_T, ctx.CLAMP_TO_EDGE);
    //ctx.generateMipmap(ctx.TEXTURE_2D)
    ctx.bindTexture(ctx.TEXTURE_2D, null);
    
    //updateTexCoords(gl, gl.sphere, box);
    if(overlay[qNumber].texture != null)
    {
    	gl.deleteTexture(overlay[qNumber].texture);
    }
    overlay[qNumber].texture = texture;
    
    if(overlay[qNumber].mesh != null)
    {
    	removeOverlayVerts(gl, overlay[qNumber].mesh);
    }
    overlay[qNumber].mesh = makeOverlayVerts(gl, box);
    document.getElementById(String(qNumber) + "loader").style.opacity = 0.0;
}

function rotateCamera(event)
{
	if(mousePressed)
	{
		zooming = false;	//Cancels current zoom events
		
		
		var oldRADec = getRADecForScreenPoint(oldMouseX, oldMouseY);
		var newRADec = getRADecForScreenPoint(event.clientX, event.clientY);
		//while(Math.abs(newRADec.RA - oldRADec.RA) > 0.00001 || Math.abs(newRADec.Dec - oldRADec.Dec) > 0.00001)
		//{
			rotateY += newRADec.Dec - oldRADec.Dec;
			rotateX -= newRADec.RA - oldRADec.RA;
			//rotateX += 0.04*(event.clientX - oldMouseX);
			//rotateY -= 0.04*(event.clientY - oldMouseY);
		//	newRADec = getRADecForScreenPoint(event.clientX, event.clientY);
		//}
		
		
		if(rotateX > 360)
		{
			rotateX -= 360;
		}
		else if(rotateX < 0)
		{
			rotateX += 360;
		}
		
		if(rotateY > 360)
		{
			rotateY -= 360;
		}
		else if(rotateY < 0)
		{
			rotateY += 360;
		}
		
		oldMouseX = event.clientX;
		oldMouseY = event.clientY;
	}
}

function getRotate()
{
	retval = {};
	retval.x = rotateX;
	retval.y = rotateY;
	return retval;
}

function keyPress(event)
{
	console.log("KeyPress event");
	var character = String.fromCharCode(event.which);
	if(character == "a" || character == "A")
	{
		desiredZoomLevel = currScale/1.5;
		zooming = true;
		zoom();
	}
	else if(character == "q" || character == "Q")
	{
		desiredZoomLevel = currScale*1.5;
		zooming = true;
		zoom();
	}
	else if(event.which == 39) // left arrow
	{
		rotateX -= 1.0;
		updateRADecForPoint(0, 0);
	}
	else if(event.which == 37) // right arrow
	{
		rotateX += 1.0;
		updateRADecForPoint(0, 0);
	}
	else if(event.which == 40) //  down arrow
	{
		rotateY -= 1.0;
		updateRADecForPoint(0, 0);
	}
	else if(event.which == 38) // up arrow
	{
		rotateY += 1.0;
		updateRADecForPoint(0, 0);
	}
	else if(character == "r" || character == "R")
	{
		/*var box = getViewBoundingBox();
		if(showTexture1)
		{
			otherTexture = loadImageTexture(gl, "http://vis.cs.pitt.edu/astroshelf/smartoverlay/updateOverlay.php?north=" + box.north + "&east=" + box.east + "&south=" + box.south + "&west=" + box.west + "&arcSecondsRadius=3&arcSecondResolution=0.005");
		}
		else
		{
			spiritTexture = loadImageTexture(gl, "http://vis.cs.pitt.edu/astroshelf/smartoverlay/updateOverlay.php?north=" + box.north + "&east=" + box.east + "&south=" + box.south + "&west=" + box.west + "&arcSecondsRadius=3&arcSecondResolution=0.005");
		}*/
		updateView();
	}
	else if(character == "g" || character == "G")
	{
		drawGrid = !drawGrid;
	}
	else if(character == "Z" || character == "z")
	{
		if(fov < 100)
		{
			fov = fov + 1;
			//if((fov < 15) && (fov > 5)) updateView();
			updateView();
			//console.log("Zooming out: fov = " + fov);
		}
	}
	else if(character == "X" || character == "x")
	{
		if(fov > 0)
		{
			fov = fov - 1;
			updateView();
			//if((fov < 15) && (fov > 5)) updateView();
			//console.log("Zooming out: fov = " + fov);
		}
	}
}

var desiredZoomLevel = 0;
var zooming = false;
function beginZoom(event)
{
	if(event.button == 0)
	{
		desiredZoomLevel = currScale*1.5;
		zooming = true;
		zoom();
		//fov = fov-1;
	}
	else if(event.button == 2)
	{
		desiredZoomLevel = currScale/1.5;
		zooming = true;
		zoom();
		//fov = fov+1;
	}
}

function zoom()
{
	if(zooming)
	{
		currScale -= (currScale - desiredZoomLevel)/8.0;		
		if(Math.abs(currScale - desiredZoomLevel) < 0.02)
		{
			currScale = desiredZoomLevel;
			zooming = false;
			releaseMouse(null);
		}
		else
		{
			setTimeout("zoom()", 5);
		}
	}
}
