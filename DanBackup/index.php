<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8">
<title>Spinning WebGL Box</title>
 
<script src="MyGUIControls.js" type="text/javascript"> </script>
<script src="J3DIMath.js" type="text/javascript"> </script>
<script src="J3DI.js" type="text/javascript"> </script>
<script src="utils3d.js" type="text/javascript"> </script>
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
		gl_Position = u_modelViewProjMatrix * vPosition;
		v_texCoord = vTexCoord.st;
		vec4 transNormal = u_normalMatrix * vec4(vNormal, 1);
	}
</script>

<script id="fshader" type="x-shader/x-fragment">
	uniform sampler2D sampler2d;
	uniform vec4 u_color;
	
	varying vec2 v_texCoord;

	void main()
	{
		
		vec2 texCoord = vec2(v_texCoord.s, 1.0 - v_texCoord.t);
		vec4 color = texture2D(sampler2d, texCoord);
		
		if(v_texCoord.s < 0.0 || v_texCoord.t < 0.0)
		{
			color = vec4(0.0, 0.0, 0.0, 0.0);
		}
		color.rgb = u_color.rgb;
		color.a *= u_color.a;
		
		
		gl_FragColor = color;
	}
	
	
	/*float blursize = 1.0/1024.0;
	void main()
	{
		vec2 texCoord = vec2(v_texCoord.s, 1.0 - v_texCoord.t);
				
		vec4 sum = vec4(0.0);
		sum += texture2D(sampler2d, vec2(texCoord.x + 10.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x + 9.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x + 8.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x + 7.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x + 6.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x + 5.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x + 4.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x + 3.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x + 2.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x + blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x - blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x - 2.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x - 3.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x - 4.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x - 5.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x - 6.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x - 7.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x - 8.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x - 9.0*blursize, texCoord.y));
		sum += texture2D(sampler2d, vec2(texCoord.x - 10.0*blursize, texCoord.y));
		
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
 
        // Enable texturing
        gl.enable(gl.TEXTURE_2D);
 
        // Create a box. On return 'gl' contains a 'box' property with
        // the BufferObjects containing the arrays for vertices,
        // normals, texture coords, and indices.
        gl.sphere = makeSphere(gl, 360, 178);
 
        
        
 
        // Create some matrices to use later and save their locations in the shaders
        gl.mvMatrix = new J3DIMatrix4();
        gl.u_normalMatrixLoc = gl.getUniformLocation(gl.program, "u_normalMatrix");
        gl.normalMatrix = new J3DIMatrix4();
        gl.u_modelViewProjMatrixLoc = gl.getUniformLocation(gl.program, "u_modelViewProjMatrix");
        gl.mvpMatrix = new J3DIMatrix4();
 
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

		for(var i=0; i<1; i++)
		{
			overlay[i] = { };
			overlay[i].texture = loadImageTextureWithBox(gl, "updateOverlay.php?north=" + box.north + "&east=" + box.east + "&south=" + box.south + "&west=" + box.west + "&arcSecondsRadius=3&hex=" + document.getElementById(String(i) + "hex").value + "&keyVal=" + document.getElementById(String(i) + "dbFields").options[document.getElementById(String(i) + "dbFields").selectedIndex].value + "&keyMin=" + document.getElementById(String(i) + "keyMin").value + "&keyMax=" + document.getElementById(String(i) + "keyMax").value, box, i);
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
        gl.perspectiveMatrix = new J3DIMatrix4();
        gl.perspectiveMatrix.perspective(30, width/height, 1, 3);
    }
 
 	
 	var currScale = 7.14;
    function drawPicture(gl)
    {
    	
        // Make sure the canvas is sized correctly.
        reshape(gl);
 
        // Clear the canvas
        gl.clear(gl.COLOR_BUFFER_BIT);
 
        // Make a model/view matrix.
        gl.mvMatrix.makeIdentity();
      
       	gl.mvMatrix.rotate(rotateY, 0,1,0);
       	gl.mvMatrix.rotate(rotateX, 1,0,0);
                
		gl.mvMatrix.translate(0, 0, 2); 
		gl.mvMatrix.scale(currScale, currScale, currScale); 
		gl.mvMatrix.translate(0, 0, -2); 
              
 		
 		
        // Construct the normal matrix from the model-view matrix and pass it in
        gl.normalMatrix.load(gl.mvMatrix);
        gl.normalMatrix.invert();
        gl.normalMatrix.transpose();
        gl.uniformMatrix4fv(gl.u_normalMatrixLoc, false,
                            gl.normalMatrix.getAsWebGLFloatArray());
 
        // Construct the model-view * projection matrix and pass it in
        gl.mvpMatrix.load(gl.mvMatrix);
        gl.mvpMatrix.multiply(gl.perspectiveMatrix);
        gl.uniformMatrix4fv(gl.u_modelViewProjMatrixLoc, false,
                            gl.mvpMatrix.getAsWebGLFloatArray());
		
 		// Bind the index array
 		
 		if(drawGrid)
 		{
 			gl.uniform4f(gl.getUniformLocation(gl.program, "u_color"), 0.0, 0.5, 1.0, 0.4);
			gl.bindBuffer(gl.ARRAY_BUFFER, gl.sphere.texCoordObject);
			gl.vertexAttribPointer(1, 2, gl.FLOAT, false, 0, 0);
			
			gl.bindBuffer(gl.ARRAY_BUFFER, gl.sphere.vertexObject);
			gl.vertexAttribPointer(2, 3, gl.FLOAT, false, 0, 0);
        
			gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, gl.sphere.gridIndexObject);
			//gl.disable(gl.TEXTURE_2D);
			gl.bindTexture(gl.TEXTURE_2D, gridTexture);
			gl.drawElements(gl.LINES, gl.sphere.numGridIndices, gl.UNSIGNED_SHORT, 0);
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
				// Bind the index array
				gl.bindBuffer(gl.ELEMENT_ARRAY_BUFFER, overlay[i].mesh.triangleIndexObject);
				
				// Draw the skysphere        
				gl.drawElements(gl.TRIANGLES, overlay[i].mesh.numTriangleIndices, gl.UNSIGNED_SHORT, 0);
				//gl.drawArrays(gl.LINE_LOOP, 0, gl.sphere.numVerts);
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