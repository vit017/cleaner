<?php
	
	
	class WideImage_Mapper_GD
	{
		function load($uri)
		{
			return @imagecreatefromgd($uri);
		}
		
		function save($handle, $uri = null)
		{
			if ($uri == null)
				return imagegd($handle);
			else
				return imagegd($handle, $uri);
		}
	}
