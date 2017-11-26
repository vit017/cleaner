<?php
	
	include_once WideImage::path() . '/vendor/de77/TGA2.php';
	
	class WideImage_Mapper_TGA
	{
		function load($uri)
		{
			return WideImage_vendor_de77_TGA::imagecreatefromtga($uri);
		}
		function loadFromString($data)
		{
			return WideImage_vendor_de77_TGA::imagecreatefromstring($data);
		}
		function save($handle, $uri = null)
		{
			throw new WideImage_Exception("Saving to TGA isn't supported.");
		}
	}
