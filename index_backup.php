<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Astroshelf</title>
 
<script src="MyGUIControls.js" type="text/javascript"> </script>
<script src="J3DI.js" type="text/javascript"> </script>
<script src="J3DIMath.js" type="text/javascript"> </script>
<script src="MyObjects.js" type="text/javascript"> </script>
<script src="MySkyControls.js" type="text/javascript"> </script>

<script id="vshader" type="x-shader/x-vertex">
	uniform mat4 u_modelViewProjMatrix;
	uniform mat4 u_normalMatrix;
	uniform vec3 lightDir;
	uniform vec4 u_color;
	
	attribute vec3 vNormal;
	attribute vec4 vTexCoord;
	attribute vec4 vPosition;

	varying vec2 v_texCoord;
	
	void main()
	{
		v_texCoord = vTexCoord.st;
		vec4 transNormal = u_normalMatrix * vec4(vNormal, 1);
		gl_Position = u_modelViewProjMatrix * vPosition;
		gl_Position = vPosition;
	}
</script>

<script id="fshader" type="x-shader/x-fragment">
	precision highp float;

	uniform sampler2D sampler2d;
	uniform vec4 u_color;
	varying vec2 v_texCoord; 

	void main()
	{
		/*
		vec2 texCoord = vec2(v_texCoord.s, 1.0 - v_texCoord.t);
		gl_FragColor = texture2D(sampler2d, texCoord);
		*/
		float blursize = float((1.0)/1024.0);
		vec2 texCoord = vec2(v_texCoord.s, 1.0 - v_texCoord.t);
		//Get the color from the texture (ie the database query info)
		vec4 color = texture2D(sampler2d, texCoord);
		color += texture2D(sampler2d, vec2(texCoord.x + blursize, texCoord.y));
		color += texture2D(sampler2d, vec2(texCoord.x, texCoord.y + blursize));
		color += texture2D(sampler2d, vec2(texCoord.x - blursize, texCoord.y));
		color += texture2D(sampler2d, vec2(texCoord.x, texCoord.y - blursize));
		color = clamp(color, 0.0, 1.0);

		color *= vec4(3, 0.3, 0.03, 1);
		//colorPercent will be between 0 and 3
		float colorPercent = float(color.r + color.g + color.b);
		//if the coordinates are out of range, then default the color to black
		if((v_texCoord.x < 0.0) || (v_texCoord.y < 0.0))
		{
			color = vec4(0.0, 0.0, 0.0, 0.0);
		}
		//A hack to display the grid that will be changed later
		//if(onGrid == 1)
		if(u_color == vec4(0.0, 0.5, 1.0, 0.4))
		{
			color.rgba = u_color.rgba;
			//onGrid = 0;
		}
		else
		{
			//If the texture coordinate is black, then there is no info, so display black
			if(colorPercent == (0.0))
			{
				color = vec4(0.0, 0.0, 0.0, 0.0);
			}
			//If the texture coordinate is less than 1/3 of the max, display in the blue spectrum
			else if(colorPercent < (1.0))
			{
				color = vec4(0.0, 0.0, colorPercent, 1.0);
			}
			//If the texture coordiante is between 1/3 and 2/3 of the max, display in the green spectrum
			else if(colorPercent < (2.0))
			{
				color = vec4(0.0, (colorPercent - 1.0), (2.0 - colorPercent), 1.0);
			}
			//If the texture coordinate is greater than 2/3 of the max, dispaly in the red spectrum
			else
			{
				color = vec4((colorPercent - 2.0), (3.0 - colorPercent), 0.0, 1.0);
			}
		}
		//color = texture2D(sampler2d, texCoord);
		//color.a *= u_color.a;
		
		//Set the final color for this pixel
		gl_FragColor = color;
		//gl_FragColor = vec4(1.0, 0.0, 0.0, 1.0);
	}
	
	/*
	void main()
	{
		vec2 texCoord = vec2(v_texCoord.s, 1.0 - v_texCoord.t);				
		vec4 sum = texture2D(sampler2d, texCoord);
		for(int i = 2; i < 11; i++)
		{
			sum += texture2D(sampler2d, vec2(texCoord.x + i*blursize, texCoord.y));
			sum += texture2D(sampler2d, vec2(texCoord.x - i*blursize, texCoord.y));
		}
		//sum /= vec4(21.0);
		sum.rgb = u_color.rgb;
		sum.a = clamp(sum.a, 0.0, 1.0);
		sum.a *= u_color.a;
		gl_FragColor = sum;
		
	}*/
</script>
 
<script type="text/javascript">	
	
	var overlay = new Array();
	var gridTexture;
    function init()
    {
        // Initialize
        gl = initWebGL("skycanvas", "vshader", "fshader", ["vColor", "vTexCoord", "vPosition"], [ 0, 0, 0, 1 ], 3);
 		
 		
        // Set some uniform variables for the shaders
        gl.uniform4f(gl.getUniformLocation(gl.program, "u_color"), 0, 1, 0, 1);
        gl.uniform3f(gl.getUniformLocation(gl.program, "lightDir"), 0, 0, 1);
        gl.uniform1i(gl.getUniformLocation(gl.program, "sampler2d"), 0);
        gl.uniform1i(gl.getUniformLocation(gl.program, "on_grid"), 0);
 
        // Enable texturing
        gl.enable(gl.TEXTURE_2D);
 
        // Create a box. On return 'gl' contains a 'box' property with
        // the BufferObjects containing the arrays for vertices,
        // normals, texture coords, and indices.
        gl.sphere = makeSphere(gl, 360, 178);
 
        // GLJoe test variables
		//squareVerts = [1.0, 0.0, 
        
 
        // Create some matrices to use later and save their locations in the shaders
        gl.mvMatrix = new J3DIMatrix4();
		mvMatrix = new J3DIMatrix4();
        gl.u_normalMatrixLoc = gl.getUniformLocation(gl.program, "u_normalMatrix");
		u_normalMatrixLoc = gl.getUniformLocation(gl.program, "u_normalMatrix");
        gl.normalMatrix = new J3DIMatrix4();
		normalMatrix = new J3DIMatrix4();
        gl.u_modelViewProjMatrixLoc = gl.getUniformLocation(gl.program, "u_modelViewProjMatrix");
		u_modelViewProjMatrixLoc = gl.getUniformLocation(gl.program, "u_modelViewProjMatrix");
        gl.mvpMatrix = new J3DIMatrix4();
		mvpMatrix = new J3DIMatrix4();
 
        // Enable all of the vertex attribute arrays.
        //gl.enableVertexAttribArray(0);
        gl.enableVertexAttribArray(1);
        gl.enableVertexAttribArray(2);
        
        // Set up all the vertex attributes for vertices, normals and texCoords
        gl.bindBuffer(gl.ARRAY_BUFFER, gl.sphere.texCoordObject);
		gl.vertexAttribPointer(1, 2, gl.FLOAT, false, 0, 0);
		
        gl.bindBuffer(gl.ARRAY_BUFFER, gl.sphere.vertexObject);
        gl.vertexAttribPointer(2, 3, gl.FLOAT, false, 0, 0);
        
        
 
 		// Bind the index array
        gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, gl.sphere.triangleIndexObject);
        
        drawGrid = true;
        
        gl.enable(gl.BLEND);
        gl.blendFunc(gl.SRC_ALPHA, gl.ONE_MINUS_SRC_ALPHA);
       // gl.disable(gl.DEPTH_TEST);
        
        // Load an image to use. Returns a WebGLTexture object
        var box = getViewBoundingBox();
        
        gridTexture = loadImageTexture(gl, "gridColor.png");

		//For each database key in the overlay, query updateOverlay.php for the new texture, etc...
		for(var i=0; i<1; i++)
		{
			overlay[i] = { };
			overlay[i].texture = loadImageTextureWithBox(gl, "updateOverlay.php?north=" + box.north + "&east=" + box.east + "&south=" + box.south + "&west=" + box.west + "&arcSecondsRadius=3&hex=" + document.getElementById(String(i) + "hex").value + "&keyVal=" + document.getElementById(String(i) + "dbFields").options[document.getElementById(String(i) + "dbFields").selectedIndex].value + "&keyMin=" + document.getElementById(String(i) + "keyMin").value + "&keyMax=" + document.getElementById(String(i) + "keyMax").value, box, i);
			//overlay[i].texture = loadImageTextureWithBox(gl, "http://www.cs.pitt.edu/~afc13/Astroshelf/SDSS.jpeg", box, i);
			overlay[i].mesh = makeOverlayVerts(gl, box);
			overlay[i].color = new Array(1.0, 0.0, 0.0, 1.0);
			overlay[i].keyMin = 0.0;
			overlay[i].keyMax = 1.0;
			overlay[i].key = "z";
		}	
        
        return gl;
    }
 
    var width = -1;
    var height = -1;
 	var width_div_2 = 0;
 	var height_div_2 = 0;
 	var tan_fov_div_2;
 	var aspect = 0;
    function reshape(gl)
    {
        var canvas = document.getElementById('skycanvas');
        if (canvas.clientWidth == width && canvas.clientHeight == height)
            return;
 
        width = canvas.clientWidth;
        height = canvas.clientHeight;
 		
 		width_div_2 = width/2.0;
 		height_div_2 = height/2.0;
 		aspect = width/height;
 		tan_fov_div_2 = Math.tan((15 * Math.PI)/180.0);
 		
        // Set the viewport and projection matrix for the scene
        gl.viewport(0, 0, width, height);
        perspectiveMatrix = new J3DIMatrix4();
        perspectiveMatrix.perspective(30, width/height, 1, 3);
    }
 
 	
 	var currScale = 7.14;
    function drawPicture(gl)
    {
    	
        // Make sure the canvas is sized correctly.
        reshape(gl);
 
        // Clear the canvas
        gl.clear(gl.COLOR_BUFFER_BIT);
		gl.clearColor(0.0, 0.0, 1.0, 1.0);
        // Make a model/view matrix.
        mvMatrix.makeIdentity();
      
       	mvMatrix.rotate(rotateY, 0,1,0);
       	mvMatrix.rotate(rotateX, 1,0,0);
                
		mvMatrix.translate(0, 0, 2); 
		mvMatrix.scale(currScale, currScale, currScale); 
		mvMatrix.translate(0, 0, -2); 
              
 		
 		
        // Construct the normal matrix from the model-view matrix and pass it in
        normalMatrix.load(mvMatrix);
		//console.log('' + gl.normalMatrix.getAsArray());
        normalMatrix.invert();
        normalMatrix.transpose();
		
		//var normFloat32Array = new Float32Array(gl.normalMatrix.getAsArray());
		gl.uniformMatrix4fv(u_normalMatrixLoc, false,
                            normalMatrix.getAsFloat32Array());
 
        // Construct the model-view * projection matrix and pass it in
        mvpMatrix.load(mvMatrix);
        mvpMatrix.multiply(perspectiveMatrix);
		//var mvpFloat32Array = new Float32Array(gl.mvpMatrix.getAsArray());
        gl.uniformMatrix4fv(u_modelViewProjMatrixLoc, false,
                            mvpMatrix.getAsFloat32Array());
 
 
		
 		// Bind the index array
 		if(drawGrid)
 		{
			//console.log('In drawGrid');
 			gl.uniform4f(gl.getUniformLocation(gl.program, "u_color"), 0.0, 0.5, 1.0, 0.4);
			gl.uniform1i(gl.getUniformLocation(gl.program, "onGrid"), 1);
			gl.bindBuffer(gl.ARRAY_BUFFER, gl.sphere.texCoordObject);
			gl.vertexAttribPointer(1, 2, gl.FLOAT, false, 0, 0);
			
			gl.bindBuffer(gl.ARRAY_BUFFER, gl.sphere.vertexObject);
			gl.vertexAttribPointer(2, 3, gl.FLOAT, false, 0, 0);
        
			gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, gl.sphere.gridIndexObject);
			//gl.disable(gl.TEXTURE_2D);
			gl.bindTexture(gl.TEXTURE_2D, gridTexture);
			gl.drawElements(gl.LINES, gl.sphere.numGridIndices, gl.UNSIGNED_SHORT, 10);
			//gl.enable(gl.TEXTURE_2D);
			
		}			
		
		for(var i = 0; i<overlay.length; i++)
		{
			if(document.getElementById(String(i) + "visible").checked && overlay[i].texture)
			{
				gl.uniform4fv(gl.getUniformLocation(gl.program, "u_color"), overlay[i].color);
				
				gl.bindBuffer(gl.ARRAY_BUFFER, overlay[i].mesh.texCoordObject);
				gl.vertexAttribPointer(1, 2, gl.FLOAT, false, 0, 0);
				
				gl.bindBuffer(gl.ARRAY_BUFFER, overlay[i].mesh.vertexObject);
				gl.vertexAttribPointer(2, 3, gl.FLOAT, false, 0, 0);
			
				gl.bindTexture(gl.TEXTURE_2D, overlay[i].texture);
				
				//Comment out the next two lines to "Enable" texture blurring
				gl.texParameteri( gl.TEXTURE_2D, gl.TEXTURE_MAG_FILTER, gl.NEAREST);
				gl.texParameteri( gl.TEXTURE_2D, gl.TEXTURE_MIN_FILTER, gl.NEAREST);
				// Bind the index array
				gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, overlay[i].mesh.triangleIndexObject);
				
				// Draw the skysphere     
				//console.log(
				gl.drawElements(gl.TRIANGLES, overlay[i].mesh.numTriangleIndices, gl.UNSIGNED_SHORT, 0)
				//);
				//gl.drawArrays(gl.TRIANGLES, 0, gl.sphere.numVerts);
			}
		}
	
 		
        // Finish up.
        gl.flush();
        
        //framerate.snapshot();
    }
 
    function start()
    {
        var c = document.getElementById("skycanvas");
 
        c.width = window.innerWidth;
        c.height = window.innerHeight;
 
 		//framerate = new Framerate("xyz");
 		
        var gl = init();
        setInterval(function() { drawPicture(gl) }, 15);
    }	
</script>



<link href="estilo.css" rel="stylesheet" type="text/css" />

<?php
	function getControls($qNumber)
	{
		$xml = simplexml_load_file("http://cas.sdss.org/dr7/en/tools/search/x_sql.asp?format=xml&cmd=" . urlencode("SELECT TOP 1 * FROM SpecObj"));
	
		foreach($xml->Answer->Row as $row)
		{
			echo "<select id='" . $qNumber . "dbFields' name='" . $qNumber . "dbFields' onchange='updateKeyForOverlay(" . $qNumber . "); releaseMouse(null);'>";
			foreach($row->attributes() as $field => $value)
			{
				if(floatval($value))
				{
					if(strcmp($field, "z") == 0)
					{
						echo "<option value=\"" . $field . "\" selected='selected'>" . $field . "</option>";
					}
					else
					{
						echo "<option value=\"" . $field . "\">" . $field . "</option>";
					}
				}
			}
			echo "</select>";
		}
	
	}
?>
</head>
 
<body onload="start()" onkeydown="keyPress(event)">
<canvas id="skycanvas" onmousedown="pressMouse(event)" onmouseup="releaseMouse(event)" onmouseout="releaseMouse(event)" onmousemove="updateRADec(event); rotateCamera(event)">
    If you're seeing this your web browser doesn't support the &lt;canvas>&gt; element. Ouch!
</canvas>
<div id="floaty">
	<img src="loader.gif" id="0loader" style="opacity: 0.0"/>
	Key: <?php getControls(0);?>
	Color: <input type="textfield" id="0hex" value="FF0000" onchange="updateColorForOverlay(0);"/>
	Alpha: <input type="textfield" id="0alpha" value="100" onchange="updateAlphaForOverlay(0);"/>
	MinVal: <input type="textfield" id="0keyMin" value="0.0" onchange="updateKeyMinForOverlay(0); releaseMouse(null);"/>
	MaxVal: <input type="textfield" id="0keyMax" value="1.0" onchange="updateKeyMaxForOverlay(0); releaseMouse(null);"/>
	<input type="checkbox" id="0visible" checked='true'/>
	<input type="submit" value="New" onclick="addOverlayObject()"/>
	
</div>
<div id="coordinates">
	RA: <span id="RA">Awesome!</span> Dec: <span id="Dec">Radical!</span> <span id="xyz"></span>
</div>
</body>
 
</html>