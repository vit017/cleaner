<?php
	
	
	class WideImage_Mapper_PNG
	{
		function load($uri)
		{
			return @imagecreatefrompng($uri);
		}
		
		function save($handle, $uri = null, $compression = 9, $filters = PNG_ALL_FILTERS)
		{
			return imagepng($handle, $uri, $compression, $filters);
		}
	}
