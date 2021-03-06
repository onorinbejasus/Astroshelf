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
	var dx = tan_fov_div_2 * ((x / width_div_2) - 1.0) * aspect;
	var dy = tan_fov_div_2 * (1.0 - (y / height_div_2));
	
	var p1x = -dx;
	var p1y = -dy;
	var p1z = -1.0;
	
	var currentMVMatrix = new CanvasMatrix4();
	currentMVMatrix.makeIdentity();
      
    currentMVMatrix.rotate(-rotateY, 0,1,0);
    currentMVMatrix.rotate(-rotateX, 1,0,0);
                
	currentMVMatrix.translate(0, 0, 2); 
	currentMVMatrix.scale(currScale, currScale, currScale); 
	currentMVMatrix.translate(0, 0, -2); 
	
	currentMVMatrix.invert();
	
	var dirX = p1x*currentMVMatrix.m11 + p1y*currentMVMatrix.m21 + p1z*currentMVMatrix.m31;
	var dirY = p1x*currentMVMatrix.m12 + p1y*currentMVMatrix.m22 + p1z*currentMVMatrix.m32;
	var dirZ = p1x*currentMVMatrix.m13 + p1y*currentMVMatrix.m23 + p1z*currentMVMatrix.m33;
	
	var origX = currentMVMatrix.m41;
	var origY = currentMVMatrix.m42;
	var origZ = currentMVMatrix.m43;
	
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
	
		retval.RA = Number.NaN;
		retval.Dec = Number.NaN;
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

	
	document.getElementById("Dec").innerHTML = currentRADec.Dec;
	document.getElementById("RA").innerHTML = currentRADec.RA;
	
	//document.getElementById("Dec").innerHTML = rotateX;
	//document.getElementById("RA").innerHTML = rotateY;
	//document.getElementById("xyz").innerHTML = currScale;
}

function updateRADecForPoint(x, y)
{
	currentRADec = getRADecForScreenPoint(x, y);

	
	document.getElementById("Dec").innerHTML = currentRADec.Dec;
	document.getElementById("RA").innerHTML = currentRADec.RA;
}

var mousePressed = false;
var oldMouseX = 0;
var oldMouseY = 0;
var rotateY = 140.0;
var rotateX = -5.3;
function pressMouse(event)
{
	mousePressed = true;
	oldMouseX = event.clientX;
	oldMouseY = event.clientY;
}

function releaseMouse(event)
{	
	mousePressed = false;
	
	var box = getViewBoundingBox();

	for(var i = 0; i<overlay.length; i++)
	{
		loadImageTextureWithBox(gl, "updateOverlay.php?north=" + box.north + "&east=" + box.east + "&south=" + box.south + "&west=" + box.west + "&arcSecondsRadius=10&keyVal=" + document.getElementById(String(i) + "dbFields").options[document.getElementById(String(i) + "dbFields").selectedIndex].value + "&keyMin=" + document.getElementById(String(i) + "keyMin").value + "&keyMax=" + document.getElementById(String(i) + "keyMax").value, box, i);
		document.getElementById("xyz").innerHTML = "updateOverlay.php?north=" + box.north + "&east=" + box.east + "&south=" + box.south + "&west=" + box.west + "&arcSecondsRadius=10&keyVal=" + document.getElementById(String(i) + "dbFields").options[document.getElementById(String(i) + "dbFields").selectedIndex].value + "&keyMin=" + document.getElementById(String(i) + "keyMin").value + "&keyMax=" + document.getElementById(String(i) + "keyMax").value;
		document.getElementById(String(i) + "loader").style.opacity = 1.0;
	}
	
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
    ctx.texImage2D(ctx.TEXTURE_2D, 0, image);
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
			rotateX += newRADec.Dec - oldRADec.Dec;
			rotateY -= newRADec.RA - oldRADec.RA;
		//	newRADec = getRADecForScreenPoint(event.clientX, event.clientY);
		//}
		
		
		if(rotateX > 90)
		{
			rotateX = 90;
		}
		else if(rotateX < -90)
		{
			rotateX = -90;
		}
		
		while(rotateY > 180)
		{
			rotateY -= 360;
		}
		while(rotateY < -180)
		{
			rotateY += 360;
		}
		
		oldMouseX = event.clientX;
		oldMouseY = event.clientY;
	}
}

function keyPress(event)
{
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
	else if(character == "i" || character == "I")
	{
		rotateX -= 1.0;
		updateRADecForPoint(0, 0);
	}
	else if(character == "k" || character == "K")
	{
		rotateX += 1.0;
		updateRADecForPoint(0, 0);
	}
	else if(character == "j" || character == "J")
	{
		rotateY -= 1.0;
		updateRADecForPoint(0, 0);
	}
	else if(character == "l" || character == "L")
	{
		rotateY += 1.0;
		updateRADecForPoint(0, 0);
	}
	else if(character == "r" || character == "R")
	{
		var box = getViewBoundingBox();
		if(showTexture1)
		{
			otherTexture = loadImageTexture(gl, "http://vis.cs.pitt.edu/astroshelf/smartoverlay/updateOverlay.php?north=" + box.north + "&east=" + box.east + "&south=" + box.south + "&west=" + box.west + "&arcSecondsRadius=3&arcSecondResolution=0.005");
		}
		else
		{
			spiritTexture = loadImageTexture(gl, "http://vis.cs.pitt.edu/astroshelf/smartoverlay/updateOverlay.php?north=" + box.north + "&east=" + box.east + "&south=" + box.south + "&west=" + box.west + "&arcSecondsRadius=3&arcSecondResolution=0.005");
		}
	}
	else if(character == "g" || character == "G")
	{
		drawGrid = !drawGrid;
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
	}
	else if(event.button == 2)
	{
		desiredZoomLevel = currScale/1.5;
		zooming = true;
		zoom();
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