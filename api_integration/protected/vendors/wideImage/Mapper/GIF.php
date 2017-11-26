<?php
	
	
	class WideImage_Mapper_GIF
	{
		function load($uri)
		{
			return @imagecreatefromgif($uri);
		}
		
		function save($handle, $uri = null)
		{
																					if ($uri)
				return imagegif($handle, $uri);
			else
				return imagegif($handle);
		}
	}
