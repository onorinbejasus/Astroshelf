function removeOverlayVerts(ctx, overlay)
{
	ctx.deleteBuffer(overlay.vertexObject);
	ctx.deleteBuffer(overlay.texCoordObject);
	ctx.deleteBuffer(overlay.gridIndexObject);
	ctx.deleteBuffer(overlay.triangleIndexObject);
}

function makeOverlayVerts(ctx, boundingBox)
{
	var radius = 2.0;
	var v = new Array();
	
	var south = Math.floor(boundingBox.south);
	var north = Math.ceil(boundingBox.north);
	var west = Math.floor(boundingBox.west);
	var east = Math.ceil(boundingBox.east);
	var height = north - south;
	var width = east - west;
	height++;
	width++;
	for(var k=south; k<=north; k++)
	{
		var radAngle = (Math.PI*(k-89))/178.0;
		var currRadius = Math.sin(radAngle) * radius;
		var currZ = Math.cos(radAngle) * radius;
		for(var i=west; i<=east; i++)
		{
			var angle = (2*Math.PI*i)/360.0 + Math.PI/2.0;
			v[(k-south)*width*3 + (i-west)*3] = Math.cos(angle) * currRadius;
			v[(k-south)*width*3 + (i-west)*3+1] = currZ;
			v[(k-south)*width*3 + (i-west)*3+2] = Math.sin(angle) * currRadius;
		}
	}
	var vertices = new WebGLFloatArray(v);
	
	
	var indx = new Array();
	for(var k=0; k<height; k++)
	{
		for(var i=0; i<width-1; i++)
		{
			indx[k*width*2 + i*2] = k*width + i;
			indx[k*width*2 + i*2+1] = k*width + i + 1;
			
		}
		
	}
	var length = indx.length;
	for(var k=0; k<height-1; k++)
	{
		for(var i=0; i<width; i++)
		{
			indx[k*width*2 + i*2 + length] = k*width + i;
			indx[k*width*2 + i*2+1 + length] = (k+1)*width + i;	
		}
		//indx[(k+1)*width*4 - 3] = indx[k*width*4];
	}
	 // index array
	var gridIndices = new WebGLUnsignedShortArray(indx);
	
	
	var triangleIndx = new Array();
	for(var k=0; k<height-1; k++)
	{
		for(var i=0; i<width-1; i++)
		{
			triangleIndx[k*width*6 + i*6] = (k+1)*width + i;
			triangleIndx[k*width*6 + i*6+1] = k*width + i;
			triangleIndx[k*width*6 + i*6+2] = (k+1)*width + i + 1;

			triangleIndx[k*width*6 + i*6+3] = (k+1)*width + i + 1;
			triangleIndx[k*width*6 + i*6+4] = k*width + i;
			triangleIndx[k*width*6 + i*6+5] = k*width + i + 1;

		}
		
	}
	 // index array
	var triangleIndices = new WebGLUnsignedShortArray(triangleIndx);
	
	var uvs = new Array();
	for(var k=0; k<height; k++)
	{
		for(var i=0; i<width; i++)
		{
			uvs[k*width*2 + i*2] = i / (width-1);
			uvs[k*width*2 + i*2+1] = k / (height-1);
		}
	}
	var texCoords = new WebGLFloatArray(uvs);

	var retval = { };

	retval.vertexObject = ctx.createBuffer();
	ctx.bindBuffer(ctx.ARRAY_BUFFER, retval.vertexObject);
	ctx.bufferData(ctx.ARRAY_BUFFER, vertices, ctx.STATIC_DRAW);
	retval.numVerts = vertices.length/3;
	
	retval.texCoordObject = ctx.createBuffer();
    ctx.bindBuffer(ctx.ARRAY_BUFFER, retval.texCoordObject);
    ctx.bufferData(ctx.ARRAY_BUFFER, texCoords, ctx.STATIC_DRAW);
    
	ctx.bindBuffer(ctx.ARRAY_BUFFER, null);
	
	

	retval.gridIndexObject = ctx.createBuffer();
	ctx.bindBuffer(ctx.ELEMENT_ARRAY_BUFFER, retval.gridIndexObject);
	ctx.bufferData(ctx.ELEMENT_ARRAY_BUFFER, gridIndices, ctx.STATIC_DRAW);
	
	retval.triangleIndexObject = ctx.createBuffer();
	ctx.bindBuffer(ctx.ELEMENT_ARRAY_BUFFER, retval.triangleIndexObject);
	ctx.bufferData(ctx.ELEMENT_ARRAY_BUFFER, triangleIndices, ctx.STATIC_DRAW);
	
	ctx.bindBuffer(ctx.ELEMENT_ARRAY_BUFFER, null);
	
	retval.numGridIndices = gridIndices.length;
	retval.numTriangleIndices = triangleIndices.length;

	return retval;
}

function makeSphere(ctx, numSlices, numStacks)
{
	var radius = 2.0;
	var v = new Array();
	for(var k=0; k<=numStacks; k++)
	{
		var radAngle = (Math.PI*k)/numStacks;
		var currRadius = Math.sin(radAngle) * radius;
		var currZ = Math.cos(radAngle) * radius;
		for(var i=0; i<numSlices; i++)
		{
			var angle = (2*Math.PI*i)/numSlices + Math.PI/2.0;
			v[k*numSlices*3 + i*3] = Math.cos(angle) * currRadius;
			v[k*numSlices*3 + i*3+1] = currZ;
			v[k*numSlices*3 + i*3+2] = Math.sin(angle) * currRadius;
		}
	}
	var vertices = new WebGLFloatArray(v);
	
	var indx = new Array();
	for(var k=0; k<=numStacks; k++)
	{
		for(var i=0; i<numSlices; i++)
		{
			indx[k*numSlices*4 + i*4] = k*numSlices + i;
			indx[k*numSlices*4 + i*4+1] = k*numSlices + i + 1;
			if(k < numStacks)
			{
				indx[k*numSlices*4 + i*4+2] = k*numSlices + i;
				indx[k*numSlices*4 + i*4+3] = (k+1)*numSlices + i;
			}
		}
		indx[(k+1)*numSlices*4 - 3] = indx[k*numSlices*4];
	}
	 // index array
	var gridIndices = new WebGLUnsignedShortArray(indx);
	
	
	var triangleIndx = new Array();
	for(var k=0; k<numStacks; k++)
	{
		for(var i=0; i<numSlices; i++)
		{
			triangleIndx[k*numSlices*6 + i*6] = (k+1)*numSlices + i;
			triangleIndx[k*numSlices*6 + i*6+1] = k*numSlices + i;
			triangleIndx[k*numSlices*6 + i*6+2] = (k+1)*numSlices + i + 1;

			triangleIndx[k*numSlices*6 + i*6+3] = (k+1)*numSlices + i + 1;
			triangleIndx[k*numSlices*6 + i*6+4] = k*numSlices + i;
			triangleIndx[k*numSlices*6 + i*6+5] = k*numSlices + i + 1;

		}
		triangleIndx[(k+1)*numSlices*6 - 4] = triangleIndx[k*numSlices*6];
		
		triangleIndx[(k+1)*numSlices*6 - 3] = triangleIndx[k*numSlices*6];
		triangleIndx[(k+1)*numSlices*6 - 1] = triangleIndx[k*numSlices*6 + 1];
	}
	 // index array
	var triangleIndices = new WebGLUnsignedShortArray(triangleIndx);
	
	var uvs = new Array();
	for(var k=0; k<=numStacks; k++)
	{
		var radAngle = (Math.PI*k)/numStacks;
		for(var i=0; i<numSlices; i++)
		{
			var angle = (2*Math.PI*i)/numSlices;
			uvs[k*numSlices*2 + i*2] = angle/(2 * Math.PI);
			uvs[k*numSlices*2 + i*2+1] = radAngle/Math.PI;
		}
	}
	var texCoords = new WebGLFloatArray(uvs);

	var retval = { };

	retval.vertexObject = ctx.createBuffer();
	ctx.bindBuffer(ctx.ARRAY_BUFFER, retval.vertexObject);
	ctx.bufferData(ctx.ARRAY_BUFFER, vertices, ctx.STATIC_DRAW);
	retval.numVerts = vertices.length/3;
	
	retval.texCoordObject = ctx.createBuffer();
    ctx.bindBuffer(ctx.ARRAY_BUFFER, retval.texCoordObject);
    ctx.bufferData(ctx.ARRAY_BUFFER, texCoords, ctx.DYNAMIC_DRAW);
    
	ctx.bindBuffer(ctx.ARRAY_BUFFER, null);
	
	

	retval.gridIndexObject = ctx.createBuffer();
	ctx.bindBuffer(ctx.ELEMENT_ARRAY_BUFFER, retval.gridIndexObject);
	ctx.bufferData(ctx.ELEMENT_ARRAY_BUFFER, gridIndices, ctx.STATIC_DRAW);
	
	retval.triangleIndexObject = ctx.createBuffer();
	ctx.bindBuffer(ctx.ELEMENT_ARRAY_BUFFER, retval.triangleIndexObject);
	ctx.bufferData(ctx.ELEMENT_ARRAY_BUFFER, triangleIndices, ctx.STATIC_DRAW);
	
	ctx.bindBuffer(ctx.ELEMENT_ARRAY_BUFFER, null);
	
	retval.numGridIndices = gridIndices.length;
	retval.numTriangleIndices = triangleIndices.length;
	
	
	retval.numSlices = numSlices;
	retval.numStacks = numStacks;
	return retval;
}

