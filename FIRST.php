<?php
	//If someone is querying for an image directly
	if(!$_GET)
	{
		header("Content-type: text/html");
		?>
		<h1>FIRST Image Query</h1>
		<form name="input" action="FIRST.php" method="get">
		RA: <input type="text" name="ra" /><br />
		DEC: <input type="text" name="dec" /><br />
		Scale: <input type="text" name="scale" /><br />
		Width (in Pixels): <input type="text" name="width" /><br />
		Height (in Pixels): <input type="text" name="height" /><br />
		<input type="submit" value="Submit" />
		</form>
		<?php
	}
	else
	{
		//**Note: This code will attempt to scale an image, but due to inconsistencies
		//in the images the FIRST query returns, it is best to search at a 1.8 scale.
		header("Content-type: image/jpeg");

		//Get the requested dimensions and set to a square
		$dim = min($_GET['width'], $_GET['height']);
		$xoffset;
		$yoffset;
		//The FIRST query is scaled up to 256x256 if the query is smaller, so just use as a minimum
		if($dim <= 256)
		{
			$dim = 256;
			$xoffset = 48;
			$yoffset = 15;
		}
		else if($dim <= 666)
		{
			$xoffset = 48;
			$yoffset = 15;
		}
		else if($dim <= 1024)
		{
			$xoffset = 53;
			$yoffset = 15;
		}
		else
		{
			$dim = 1024;
			$xoffset = 53;
			$yoffset = 15;
		}
		//Since FIRST does not scale images, we will *try*
		$scale = $_GET['scale']/1.8;
		if($scale < 1)
		{
			$scale = 1;
		}
		//Since FIRST has a max resolution of 1024x1024
		$dim2 = $dim*$scale;
		if($dim2 >=1024)
		{
			$dim2 = 1024;
		}
		//Determine the size for the query to FIRST.  Basically converting pixels to arcminutes.
		$size = $dim2*3 / 100;
		//Get the FIRST image
		$im = imagecreatefromgif("http://third.ucllnl.org/cgi-bin/firstimage?RA=".($_GET['ra']/15)."&Dec=".$_GET['dec']."&Equinox=J2000&ImageSize=".$size."&MaxInt=10");
		//Create a blank image to store the crop
		$im2 = imagecreatetruecolor($dim, $dim);
		//Crop extra data and resize in one step
		imagecopyresampled($im2, $im, 0, 0, $xoffset, $yoffset, $dim, $dim, $dim2, $dim2);
		//Uncomment this line to see the image with data
		//imagejpeg($im);
		imagedestroy($im);
		//Show the image
		imagejpeg($im2);
		imagedestroy($im2);
	}
?>
