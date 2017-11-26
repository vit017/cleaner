<?php
	
	
	class WideImage_Operation_CopyChannelsPalette
	{
		
		function execute($img, $channels)
		{
			$blank = array('red' => 0, 'green' => 0, 'blue' => 0);
			if (isset($channels['alpha']))
				unset($channels['alpha']);
			$width = $img->getWidth();
			$height = $img->getHeight();
			$copy = WideImage_PaletteImage::create($width, $height);
			
			if ($img->isTransparent())
			{
				$otci = $img->getTransparentColor();
				$TRGB = $img->getColorRGB($otci);
				$tci = $copy->allocateColor($TRGB);
			}
			else
			{
				$otci = null;
				$tci = null;
			}
			
			for ($x = 0; $x < $width; $x++)
				for ($y = 0; $y < $height; $y++)
				{
					$ci = $img->getColorAt($x, $y);
					if ($ci === $otci)
					{
						$copy->setColorAt($x, $y, $tci);
						continue;
					}
					$RGB = $img->getColorRGB($ci);
					$newRGB = $blank;
					foreach ($channels as $channel)
						$newRGB[$channel] = $RGB[$channel];
					
					$color = $copy->getExactColor($newRGB);
					if ($color == -1)
						$color = $copy->allocateColor($newRGB);
					$copy->setColorAt($x, $y, $color);
				}
			
			if ($img->isTransparent())
				$copy->setTransparentColor($tci);
			
			return $copy;
		}
	}
