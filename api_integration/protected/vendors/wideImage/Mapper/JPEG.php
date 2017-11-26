<?php
	
	
	class WideImage_Mapper_JPEG
	{
		function load($uri)
		{
			return @imagecreatefromjpeg($uri);
		}
		
		function save($handle, $uri = null, $quality = 100)
		{
			return imagejpeg($handle, $uri, $quality);
		}
	}
