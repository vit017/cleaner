<?php
	
	
	class WideImage_Operation_CopyChannelsTrueColor
	{
		
		function execute($img, $channels)
		{
			$blank = array('red' => 0, 'green' => 0, 'blue' => 0, 'alpha' => 0);
			
			$width = $img->getWidth();
			$height = $img->getHeight();
			$copy = WideImage_TrueColorImage::create($width, $height);
			
			if (count($channels) > 0)
				for ($x = 0; $x < $width; $x++)
					for ($y = 0; $y < $height; $y++)
					{
						$RGBA = $img->getRGBAt($x, $y);
						$newRGBA = $blank;
						foreach ($channels as $channel)
							$newRGBA[$channel] = $RGBA[$channel];
						
						$color = $copy->getExactColorAlpha($newRGBA);
						if ($color == -1)
							$color = $copy->allocateColorAlpha($newRGBA);
						
						$copy->setColorAt($x, $y, $color);
					}
			
			return $copy;
		}
	}
