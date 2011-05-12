<?php
	//If someone is querying for an image directly
	if(!$_GET)
	{
		header("Content-type: text/html");
		?>
		<h1>SDSS Image Query</h1>
		<form name="input" action="SDSS.php" method="get">
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
		header("Content-type: image/jpeg");
		// Submit query to some database and store results in xml format
		$xml = simplexml_load_file("http://casjobs.sdss.org/ImgCutoutDR6/imgcutout.asmx/GetJpeg?ra_=".$_GET['ra']."&dec_=".$_GET['dec']."&scale_=".$_GET['scale']."&width_=".$_GET['width']."&height_=".$_GET['height']."&opt_=&imgtype_=&imgfield_=");
		//echo $xml;
		$im = base64_decode($xml);
		echo $im;
	}
?>
