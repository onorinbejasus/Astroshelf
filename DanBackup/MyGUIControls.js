function addOverlayObject()
{	
	
	
	var floaty = document.getElementById('floaty');
	floaty.innerHTML += "<br />\n\
						<img src='loader.gif' id='" + String(overlay.length) +"loader' style='opacity: 0.0'/>\n\
						Key: ";
						
	floaty.innerHTML += "<select id='" + String(overlay.length) + "dbFields' name='" + String(overlay.length) + "dbFields' onchange='updateKeyForOverlay(" + String(overlay.length) + "); releaseMouse(null);'> </select>";					
	var newMenu = document.getElementById(String(overlay.length) + "dbFields");
	var menu = document.getElementById('0dbFields').cloneNode(true);
	var numChildren = menu.children.length;
	for(var i=0; i<numChildren; i++)
	{
		newMenu.appendChild(menu.children[0]);
	}
	floaty.innerHTML += " Color: <input type='textfield' id='" + String(overlay.length) + "hex' value='FF0000' onchange='updateColorForOverlay(" + String(overlay.length) + ");'/> \n\
						 Alpha: <input type='textfield' id='" + String(overlay.length) + "alpha' value='100' onchange='updateAlphaForOverlay(" + String(overlay.length) + ");'/> \n\
						 MinVal: <input type='textfield' id='" + String(overlay.length) + "keyMin' value='0.0' onchange='updateKeyMinForOverlay(" + String(overlay.length) + "); releaseMouse(null);'/> \n\
						 MaxVal: <input type='textfield' id='" + String(overlay.length) + "keyMax' value='1.0' onchange='updateKeyMaxForOverlay(" + String(overlay.length) + "); releaseMouse(null);'/> \n\
						 <input type='checkbox' id='" + String(overlay.length) + "visible' checked='true'/>";
		
	var box = getViewBoundingBox();
	
	var index = overlay.length;
	overlay[index] = { };
	overlay[index].mesh = makeOverlayVerts(gl, box);
	overlay[index].color = new Array(1.0, 1.0, 1.0, 1.0);
	overlay[index].keyMin = 0.0;
	overlay[index].keyMax = 1.0;
	overlay[index].key = "z";
	//overlay[index].texture = null;
	loadImageTextureWithBox(gl, "updateOverlay.php?north=" + box.north + "&east=" + box.east + "&south=" + box.south + "&west=" + box.west + "&arcSecondsRadius=3&keyVal=" + overlay[index].key + "&keyMin=" + overlay[index].keyMin + "&keyMax=" + overlay[index].keyMax, box, index);
	
	
	//Update values stored in HTML elements to reflect the values in their respective overlays
	
	for(var i = 0; i<overlay.length; i++)
	{
		
		document.getElementById(String(i) + "dbFields").selectedIndex = indexForMenuComponent(overlay[i].key);
		document.getElementById(String(i) + "hex").value = colorComponentToHex(overlay[i].color[0]) + colorComponentToHex(overlay[i].color[1]) + colorComponentToHex(overlay[i].color[2]);
		document.getElementById(String(i) + "alpha").value = overlay[i].color[3] * 100;
		document.getElementById(String(i) + "keyMin").value = overlay[i].keyMin;
		document.getElementById(String(i) + "keyMax").value = overlay[i].keyMax;
		
	}
}

function indexForMenuComponent(component)
{
	var options = document.getElementById('0dbFields').options;
	for(var i=0; i<options.length; i++)
	{
		if(component == options[i].value)
		{
			return i;
		}
	}
	return 0;
}

function colorComponentToHex(component)
{
	var value = (component * 255.0).toString(16); 
	if(value.length < 2)
	{
		value = "0" + value;
	}
	
	return value.toUpperCase();
}

function hexToR(hexString)
{
	return parseInt((cutHex(hexString)).substring(0,2), 16) / 255.0;
}

function hexToG(hexString)
{
	return parseInt((cutHex(hexString)).substring(2,4), 16) / 255.0;
}

function hexToB(hexString)
{
	return parseInt((cutHex(hexString)).substring(4,6), 16) / 255.0;
}

function cutHex(hexString)
{
	return (hexString.charAt(0)=="#") ? hexString.substring(1,7):hexString;
}

function updateColorForOverlay(i)
{
	var hexString = document.getElementById(String(i) + "hex").value;
	overlay[i].color[0] = hexToR(hexString);
	overlay[i].color[1] = hexToG(hexString);
	overlay[i].color[2] = hexToB(hexString);
}

function updateAlphaForOverlay(i)
{
	overlay[i].color[3] = parseFloat(document.getElementById(String(i) + "alpha").value)/100.0;
}

function updateKeyMinForOverlay(i)
{
	overlay[i].keyMin = parseFloat(document.getElementById(String(i) + "keyMin").value);
}

function updateKeyMaxForOverlay(i)
{
	overlay[i].keyMax = parseFloat(document.getElementById(String(i) + "keyMax").value);
}

function updateKeyForOverlay(i)
{
	overlay[i].key = document.getElementById(String(i) + "dbFields").options[document.getElementById(String(i) + "dbFields").selectedIndex].value;
}