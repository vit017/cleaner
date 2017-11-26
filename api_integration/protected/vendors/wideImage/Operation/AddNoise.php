<?php
	
	
	class WideImage_Operation_AddNoise {
		
		function execute($image, $amount, $type) {
			switch ($type)
			{
				case 'salt&pepper'	:	$fun = 'saltPepperNoise_fun';
										break;
				case 'color'		:	$fun = 'colorNoise_fun';
										break;
				default				:	$fun = 'monoNoise_fun';
										break;
			}
			return self::filter($image->asTrueColor(), $fun, $amount);
		}
		
		function filter($image, $function, $value)
		{
			for ($y = 0; $y < $image->getHeight(); $y++)
			{
		    	for ($x = 0; $x< $image->getWidth(); $x++)
				{
					$rgb = imagecolorat($image->getHandle(), $x, $y);
					$a = ($rgb >> 24) & 0xFF;
					$r = ($rgb >> 16) & 0xFF;
					$g = ($rgb >> 8) & 0xFF;
					$b = $rgb & 0xFF;
					self::$function($r, $g, $b, $value);
					$color = imagecolorallocatealpha($image->getHandle(), $r, $g, $b, $a);
					imagesetpixel($image->getHandle(), $x, $y, $color);
		      	}
		    }
		    return $image;
		}
		
		function colorNoise_fun(&$r, &$g, &$b, $amount)
		{
			$r = self::byte($r + mt_rand(0, $amount) - ($amount >> 1) );
			$g = self::byte($g + mt_rand(0, $amount) - ($amount >> 1) );
			$b = self::byte($b + mt_rand(0, $amount) - ($amount >> 1) );
		}
		
		function monoNoise_fun(&$r, &$g, &$b, $amount)
		{
			$rand = mt_rand(0, $amount) - ($amount >> 1);
			$r = self::byte($r + $rand);
			$g = self::byte($g + $rand);
			$b = self::byte($b + $rand);
		}
		
		function saltPepperNoise_fun(&$r, &$g, &$b, $amount)
		{
			if (mt_rand(0, 255 - $amount) != 0) return;
			$rand = mt_rand(0, 1);
			switch ($rand)
			{
				case 0 :	$r = $g = $b = 0;
							break;
				case 1 :	$r = $g = $b = 255;
							break;
			}
		}
		
		function byte($b)
		{
			if ($b > 255) return 255;
			if ($b < 0) return 0;
			return (int) $b;
		}
	}
