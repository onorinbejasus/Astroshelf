<?php


	// Set header to HTML if you want to be able to print out stats and debug info
	//header("Content-type: text/html");
	
	// Set header to png if you just want the returned image to show up
	header("Content-type: image/png");

	// Timer for stats
	$time_start = microtime(true);



	// Submit query to some database and store results in xml format
	$xml;
	if($_GET['west'] < -180.0)
	{
		$newWest = $_GET['west'] + 360.0;
		
		//Loads results from the SDSS database
		$xml = simplexml_load_file("http://cas.sdss.org/dr7/en/tools/search/x_sql.asp?format=xml&cmd=" . urlencode("select ra, dec, " . $_GET['keyVal'] . " from SpecObj where dec BETWEEN " . $_GET['south'] . " and " . $_GET['north'] . "AND (ra BETWEEN -180.0 and " . $_GET['east'] . " OR ra BETWEEN " . $newWest . " and 180.0)"));
	}
	else if($_GET['east'] > 180.0)
	{
		$newEast = $_GET['east'] - 360.0;
		
		//Loads results from the SDSS database
		$xml = simplexml_load_file("http://cas.sdss.org/dr7/en/tools/search/x_sql.asp?format=xml&cmd=" . urlencode("select ra, dec, " . $_GET['keyVal'] . " from SpecObj where dec BETWEEN " . $_GET['south'] . " and " . $_GET['north'] . "AND (ra BETWEEN -180.0 and " . $newEast . " OR ra BETWEEN " . $_GET['west'] . " and 180.0)"));
	}
	else
	{
		//Loads results from the SDSS database
		$xml = simplexml_load_file("http://cas.sdss.org/dr7/en/tools/search/x_sql.asp?format=xml&cmd=" . urlencode("select ra, dec, " . $_GET['keyVal'] . " from SpecObj where dec BETWEEN " . $_GET['south'] . " and " . $_GET['north'] . "AND ra BETWEEN " . $_GET['west'] . " and " . $_GET['east']));
		
		//Uncomment this next line to load results from the local astro database (This needs to be implemented for the special cases above)
		//$xml = simplexml_load_file("http://vis.cs.pitt.edu/webtest/astro_cs1699/QueryScripts/Query.php?raLow=" . $_GET['west'] . "&raHigh=" . $_GET['east'] . "&decLow=" . $_GET['south'] . "&decHigh=" . $_GET['north'] . "&var=" . $_GET['keyVal']);
	}


	$totalLat = $_GET['north'] - $_GET['south'];	// Used to position each object relative to the bounds of the image
	$totalLon = $_GET['east'] - $_GET['west'];
	
	$range = $_GET['keyMax'] - $_GET['keyMin'];		// Needed to determine the opacity of the object marker in the image

	$width = 1200; 	// Total width in pixels of the output image
	$height = ($totalLat/$totalLon) * $width;	// Total height in pixels of the output image

	//echo("Width: " . $width . " Height: " . $height . "<br />");	//Andrew: This line was causing the image/png output to be corrupted (since the header specifies that the browser should expect just an image, not extra text)
	

	$im     = imagecreatetruecolor($width, $height);
	imagealphablending($im, true);
	imagesavealpha($im, true);
	imagefill($im, 0, 0, imagecolorallocatealpha($im, 0, 0, 0, 127));

	
	$howmany = 0;	// Used for stats
	foreach($xml->Answer->Row as $row)
	{
		// Determine the x,y coordinates of the pixel to be drawn in the range of [0.0, 1.0]
		$pixLat = ((float)$row['dec'] - (float)$_GET['south']) / $totalLat;
		$pixLon;
		$howmany++;
		if((float)$_GET['west'] < -180.0)
		{
			if((float)$row['ra'] >(float) $_GET['east'])
			{
				$pixLon = (((float)$row['ra'] - 360.0) - (float)$_GET['west']) / $totalLon;
			}
			else
			{
				$pixLon = ((float)$row['ra'] - (float)$_GET['west']) / $totalLon;
			}
		}
		else if((float)$_GET['east'] > 180.0)
		{
			if((float)$row['ra'] < (float)$_GET['west'])
			{
				$pixLon = (((float)$row['ra'] + 360.0) - (float)$_GET['west']) / $totalLon;
			}
			else
			{
				$pixLon = ((float)$row['ra'] - (float)$_GET['west']) / $totalLon;
			}
		}
		else
		{
			$pixLon = ((float)$row['ra'] - (float)$_GET['west']) / $totalLon;
		}

		// Determine the color with which to draw based on the keyValue
		//Dan used out of range values and set them to max or min accordingly,
		//I have choosen to ignore values that are out of range since I think
		//it would be more beneficial to a researcher
		if((float)$row[$_GET['keyVal']] < (float)$_GET['keyMin'])
		{
			$color = imagecolorallocatealpha($im, 0, 0, 0, 127);
		}
		else if((float)$row[$_GET['keyVal']] > (float)$_GET['keyMax'])
		{
			$color = imagecolorallocatealpha($im, 0, 0, 0, 0);
		}
		else
		{
			//Encode the information into a 32-bit unsigned integer

			/*
			//Uncomment these lines for 31 bit precision
			$colorRGBA = 2147483647 * ((float)$row[$_GET['keyVal']] - (float)$_GET['keyMin'])/(float)$range;
			//Use bit shifting to encode the information into the Red, Green, Blue, and Alpha channels
			$colorR = $colorRGBA >> 23;
			$colorG = ($colorRGBA << 8) >> 23;
			$colorB = ($colorRGBA << 16) >> 23;
			$colorA = ($colorRGBA << 24) >> 23;
			*/

			//Encode into alpha channel with 7 bits of precision
			$colorR = 0;
			$colorG = 0;
			$colorB = 0;
			$colorA = 127 * ((float)$row[$_GET['keyVal']] - (float)$_GET['keyMin'])/(float)$range;


			//Put the finite information into the picture to be returned to the client
			$color = imagecolorallocatealpha($im, $colorR, $colorG, $colorB, $colorA);
		}
		// Draw a single pixel to the image
		imagesetpixel($im, $pixLon * $width, $height - ($pixLat * $height), $color);
		imagecolordeallocate($im, $color);
	}
	/*
	//Uncomment this to do a test in the top left corner
	$color = imagecolorallocatealpha($im, 255, 255, 255, 126);
	imagesetpixel($im, 0, 0, $color);
	imagecolordeallocate($im, $color);
	*/
	//Send highly compressed PNG image to the client
	imagepng($im, NULL, 9, PNG_ALL_FILTERS);

	// Clean up
	imagedestroy($im);
	
	$time_end = microtime(true);
	$time = $time_end - $time_start;

	// Print out stats
	// echo ("<br />created image in " . $time . " seconds with " . $howmany . " points.");	

?>
