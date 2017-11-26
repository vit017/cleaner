<?php
	
	
	class WideImage_Mapper_GD2
	{
		function load($uri)
		{
			return @imagecreatefromgd2($uri);
		}
		
		function save($handle, $uri = null, $chunk_size = null, $type = null)
		{
			return imagegd2($handle, $uri, $chunk_size, $type);
		}
	}
