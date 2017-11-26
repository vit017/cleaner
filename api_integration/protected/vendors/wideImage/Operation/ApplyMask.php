<?php
	
	
	
	class WideImage_Operation_ApplyMask
	{
		
		function execute($image, $mask, $left = 0, $top = 0)
		{
			$left = WideImage_Coordinate::fix($left, $image->getWidth(), $mask->getWidth());
			$top = WideImage_Coordinate::fix($top, $image->getHeight(), $mask->getHeight());
			
			$width = $image->getWidth();
			$mask_width = $mask->getWidth();
			
			$height = $image->getHeight();
			$mask_height = $mask->getHeight();
			
			$result = $image->asTrueColor();
			
			$result->alphaBlending(false);
			$result->saveAlpha(true);
			
			$srcTransparentColor = $result->getTransparentColor();
			if ($srcTransparentColor >= 0)
			{
																								$destTransparentColor = $srcTransparentColor;
			}
			else
			{
				$destTransparentColor = $result->allocateColorAlpha(255, 255, 255, 127);
			}
			
			for ($x = 0; $x < $width; $x++)
				for ($y = 0; $y < $height; $y++)
				{
					$mx = $x - $left;
					$my = $y - $top;
					if ($mx >= 0 && $mx < $mask_width && $my >= 0 && $my < $mask_height)
					{
						$srcColor = $image->getColorAt($x, $y);
						if ($srcColor == $srcTransparentColor)
							$destColor = $destTransparentColor;
						else
						{
							$maskRGB = $mask->getRGBAt($mx, $my);
							if ($maskRGB['red'] == 0)
								$destColor = $destTransparentColor;
							elseif ($srcColor >= 0)
							{
								$imageRGB = $image->getRGBAt($x, $y);
								$level = ($maskRGB['red'] / 255) * (1 - $imageRGB['alpha'] / 127);
								$imageRGB['alpha'] = 127 - round($level * 127);
								if ($imageRGB['alpha'] == 127)
									$destColor = $destTransparentColor;
								else
									$destColor = $result->allocateColorAlpha($imageRGB);
							}
							else
								$destColor = $destTransparentColor;
						}
						$result->setColorAt($x, $y, $destColor);
					}
				}
			return $result;
		}
	}
