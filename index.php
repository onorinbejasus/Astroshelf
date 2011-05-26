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
 
    attribute vec3 vNormal;
    attribute vec2 vTexCoord;
    attribute vec4 vPosition;
 
    varying float v_Dot;
    varying vec2 v_texCoord;
	varying float texRA;
	varying float texDec;
 
    void main()
    {
        gl_Position = u_modelViewProjMatrix * vPosition;
        v_texCoord = vTexCoord.st;
		texRA = vTexCoord.x;
		texDec = vTexCoord.y;
        vec4 transNormal = u_normalMatrix * vec4(vNormal, 1);
        v_Dot = max(dot(transNormal.xyz, lightDir), 0.0);
    }
</script> 
 
<script id="fshader" type="x-shader/x-fragment"> 
#ifdef GL_ES
    precision highp float;
#endif
        
    uniform sampler2D panView;
	uniform sampler2D SDSS;
	uniform sampler2D FIRST;
	uniform float lats;
	uniform float longs;
	uniform int grid;
	uniform float degPerLat;
	uniform float degPerLong;
	
	// SDSS image information 
	uniform float sdssRA;
	uniform float sdssDec;
	uniform float sdssWidth;
	uniform float sdssHeight;
	
	// FIRST image information
	uniform float firstRA;
	uniform float firstDec;
	uniform float firstWidth;
	uniform float firstHeight;
	uniform float firstBlend;
 
    varying float v_Dot;
    varying vec2 v_texCoord;
	varying float texRA;
	varying float texDec;
 
    void main()
    {
        vec2 texCoord = vec2(texRA, 1.0 - texDec);
        vec4 color = texture2D(panView, texCoord);
        //color += vec4(0.1, 0.1, 0.1, 1);
		
		// Map the SDSS image
		float sdssCoordX = ((texRA*longs*degPerLong) - sdssRA)/((sdssWidth));
		float sdssCoordY = ((texDec*lats*degPerLat) - (sdssDec + ((lats*degPerLat)/2.0)))/((sdssHeight));
		if((sdssCoordX >= 0.0) && (sdssCoordX <= 1.0))
		{
			if((sdssCoordY >= 0.0) && (sdssCoordY <= 1.0))
			{
				/*Joe I know this isn't necessary, but it keeps certain dots from becoming oversaturated
				ie they are gray in SDSS and gray in FIRST, now they become whiter rather than staying
				gray*/
				color = (1.0 - firstBlend)*texture2D(SDSS, vec2(sdssCoordX, 1.0-sdssCoordY));
				//color = color+vec4(0.3, 0.0, 0.0, 0.0);
			}
		}
		
		// Map the FIRST image
		float firstCoordX = sdssCoordX; //((texRA*longs*degPerLong) - firstRA)/((firstWidth));
		float firstCoordY = sdssCoordY; //((texDec*lats*degPerLat) - (firstDec + ((lats*degPerLat)/2.0)))/((firstHeight));
		if((firstCoordX >= 0.0) && (firstCoordX <= 1.0))
		{
			if((firstCoordY >= 0.0) && (firstCoordY <= 1.0))
			{
				color += firstBlend*texture2D(FIRST, vec2(firstCoordX, 1.0-firstCoordY));
				//color = color+vec4(0.3, 0.0, 0.0, 0.0);
			}
		}
		
		if(grid == 1)
		{
			color = vec4(texDec, texRA, 1.0, 1.0);
		}
        gl_FragColor = color; //vec4(color.xyz * v_Dot, color.a);
    }
</script> 
 
<script type="text/javascript">	
	
	var overlay = new Array();
	var gridTexture;
	var rotation = {};
	var camera = {};
	var gl;
	var SDSStexture;
	var FIRSTtexture;
	var drawGrid = true;
//	var first = false;
    function init()
    {
        // Initialize
        gl = initWebGL(
            // The id of the Canvas Element
            "skycanvas",
            // The ids of the vertex and fragment shaders
            "vshader", "fshader", 
            // The vertex attribute names used by the shaders.
            // The order they appear here corresponds to their index
            // used later.
            [ "vNormal", "vTexCoord", "vPosition"],
            // The clear color and depth values
            [ 0, 0, 0, 1 ], 10000);
 
        // Set some uniform variables for the shaders
        gl.uniform3f(gl.getUniformLocation(gl.program, "lightDir"), 0, 0, 1);
        gl.uniform1i(gl.getUniformLocation(gl.program, "panView"), 0);
		gl.uniform1i(gl.getUniformLocation(gl.program, "SDSS"), 1);
		gl.uniform1i(gl.getUniformLocation(gl.program, "FIRST"), 2);
		
        // Enable texturing
        gl.enable(gl.TEXTURE_2D);
 
        // Create a box. On return 'gl' contains a 'box' property with
        // the BufferObjects containing the arrays for vertices,
        // normals, texture coords, and indices.
        gl.box = makeSphere(gl, 2.0, 180, 360);
		gl.uniform1f(gl.getUniformLocation(gl.program, "lats"), 180.0);
		gl.uniform1f(gl.getUniformLocation(gl.program, "longs"), 360.0);
		gl.uniform1f(gl.getUniformLocation(gl.program, "degPerLat"), 1.0);
		gl.uniform1f(gl.getUniformLocation(gl.program, "degPerLong"), 1.0);
		
		gl.uniform1f(gl.getUniformLocation(gl.program, "firstBlend"), .5);

        // Load an image to use. Returns a WebGLTexture object
        spiritTexture = loadImageTexture(gl, "SkyImg.jpg");
		sdssTexture = loadImageTexture(gl, "SDSS.jpeg");
 
        // Create some matrices to use later and save their locations in the shaders
        gl.mvMatrix = new J3DIMatrix4();
        gl.u_normalMatrixLoc = gl.getUniformLocation(gl.program, "u_normalMatrix");
        gl.normalMatrix = new J3DIMatrix4();
        gl.u_modelViewProjMatrixLoc =
                gl.getUniformLocation(gl.program, "u_modelViewProjMatrix");
        gl.mvpMatrix = new J3DIMatrix4();
 
        // Enable all of the vertex attribute arrays.
        gl.enableVertexAttribArray(0);
        gl.enableVertexAttribArray(1);
        gl.enableVertexAttribArray(2);
 
        // Set up all the vertex attributes for vertices, normals and texCoords
 
        // Bind the index array
		
		currentAngle = 0.0;
		incAngle = 0.1;
		
		var rotateY = 180.0+30;
		var rotateX = 180.0;
		
		camera.fov = 30;
        return gl;
    }
 
    var width = -1;
    var height = -1;
 	var width_div_2 = 0;
 	var height_div_2 = 0;
 	var tan_fov_div_2;
	var fov = 40;
 	var aspect = 0;
    function reshape(gl)
    {
        var canvas = document.getElementById('skycanvas');
		
		// Set the viewport and projection matrix for the scene
		gl.viewport(0, 0, width, height);
        gl.perspectiveMatrix = new J3DIMatrix4();
        gl.perspectiveMatrix.perspective(fov, width/height, 1, 10000);
        gl.perspectiveMatrix.lookat(0, 0, 0, 1, 0, 0, 0, 1, 0);
		tan_fov_div_2 = Math.tan(((fov/2.0) * Math.PI)/180.0);
		
        if (canvas.width == width && canvas.height == height)
            return;
 
        width = canvas.width;
        height = canvas.height;
		
		width_div_2 = width/2.0;
 		height_div_2 = height/2.0;
 		aspect = width/height;
		rotation = getRotate();
		//console.log("fov = " + fov)
    }
 
 	var currScale = 7.14;
    function drawPicture(gl)
    {
    	
        // Make sure the canvas is sized correctly.
        reshape(gl);
		 
        // Clear the canvas
        gl.clear(gl.COLOR_BUFFER_BIT | gl.DEPTH_BUFFER_BIT);
 
        // Make a model/view matrix.
        gl.mvMatrix.makeIdentity();
 
		gl.mvMatrix.rotate(rotateY,0,0,1);
		gl.mvMatrix.rotate(rotateX, 0,1,0);
 
        // Construct the normal matrix from the model-view matrix and pass it in
        gl.normalMatrix.load(gl.mvMatrix);
        gl.normalMatrix.invert();
        gl.normalMatrix.transpose();
        gl.normalMatrix.setUniform(gl, gl.u_normalMatrixLoc, false);
 
        // Construct the model-view * projection matrix and pass it in
        gl.mvpMatrix.load(gl.perspectiveMatrix);
        gl.mvpMatrix.multiply(gl.mvMatrix);
        gl.mvpMatrix.setUniform(gl, gl.u_modelViewProjMatrixLoc, false);
 

		//Bind the geometry attributes
		gl.bindBuffer(gl.ARRAY_BUFFER, gl.box.vertexObject);
        gl.vertexAttribPointer(2, 3, gl.FLOAT, false, 0, 0);
 
        gl.bindBuffer(gl.ARRAY_BUFFER, gl.box.normalObject);
        gl.vertexAttribPointer(0, 3, gl.FLOAT, false, 0, 0);
 
        gl.bindBuffer(gl.ARRAY_BUFFER, gl.box.texCoordObject);
        gl.vertexAttribPointer(1, 2, gl.FLOAT, false, 0, 0);
		
        // Bind the texture to use
		gl.activeTexture(gl.TEXTURE0);
        gl.bindTexture(gl.TEXTURE_2D, spiritTexture);
		gl.activeTexture(gl.TEXTURE1);
		gl.bindTexture(gl.TEXTURE_2D, SDSStexture);
	
		gl.activeTexture(gl.TEXTURE2);
		gl.bindTexture(gl.TEXTURE_2D, FIRSTtexture);

		
		gl.uniform1i(gl.getUniformLocation(gl.program, "grid"), 0);
		gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, gl.box.indexObject);
		gl.drawElements(gl.TRIANGLES, gl.box.numIndices, gl.UNSIGNED_SHORT, 0);
		
		gl.clear(gl.DEPTH_BUFFER_BIT);
		
		if(drawGrid)
		{
			gl.uniform1i(gl.getUniformLocation(gl.program, "grid"), 1);
			gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, gl.box.gridIndexObject);
			gl.drawElements(gl.LINES, gl.box.numGridIndices, gl.UNSIGNED_SHORT, 0);
		}
 
        // Finish up.
        gl.flush();
 
        // Show the framerate
        //framerate.snapshot();
 
        currentAngle += incAngle;
        if (currentAngle > 360)
            currentAngle -= 360;
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
<canvas id="skycanvas" onmousedown="pressMouse(event)" onmouseup="releaseMouse(event)" onmouseout="releaseMouse(event)" onmousemove="updateRADec(event); rotateCamera(event)" onmousewheel="wheel(event)">
    If you're seeing this your web browser doesn't support the &lt;canvas>&gt; element. Ouch!
</canvas>
<div id="floaty">
	<img src="loader.gif" id="0loader" style="opacity: 0.0"/>

	SDSS <input name='range' type='range' id='alpha' min='0' max='100' value='0' onchange='updateAlpha()' disabled="disabled" /> FIRST
	<input name='first' id='first' type='checkbox' onchange='updateFirst()' /> 
	
</div>

<div id="overlays"> 
	<img src="loader.gif" id="0loader" style="opacity: 0.0"/>
	Key: <?php getControls(0);?>
	Color: <input type="range" id="0hex" value="FF0000" onchange="updateColorForOverlay(0);"/>
	Alpha: <input type="range" id="0alpha" value="100" onchange="updateAlphaForOverlay(0);"/>
	MinVal: <input type="textfield" id="0keyMin" value="0.0" onchange="updateKeyMinForOverlay(0); releaseMouse(null);"/>
	MaxVal: <input type="textfield" id="0keyMax" value="1.0" onchange="updateKeyMaxForOverlay(0); releaseMouse(null);"/>
	<input type="checkbox" id="0visible" checked='true'/>
	<input type="submit" value="New" onclick="addOverlayObject()"/>
	
</div>

<div id="coordinates">
        RA: <input type="text" id="RA" value="180" onfocus="changingRA = true;" onblur="changingRA = false;">
        Dec: <input type="text" id="Dec" value="30" onfocuse="changingDec = true;" onblur="changingDec = false;">
        Scale: <span id="xyz"> Stuff </span>
        <input type="submit" id="change" value="Change coordinates" onclick="changeRA(); changeDec(); updateView();">
</div>
</body>
 
</html>
