<?php
	
	
	class WideImage_Operation_InvalidFitMethodException extends WideImage_Exception {}
	
	class WideImage_Operation_InvalidResizeDimensionException extends WideImage_Exception {}
	
	
	class WideImage_Operation_Resize
	{
		
		protected function prepareDimensions($img, $width, $height, $fit)
		{
			if ($width === null && $height === null)
			{
				$width = $img->getWidth();
				$height = $img->getHeight();
			}
			
			if ($width !== null)
				$width = WideImage_Coordinate::fix($width, $img->getWidth());
			
			if ($height !== null)
				$height = WideImage_Coordinate::fix($height, $img->getHeight());
			
			if ($width === null)
				$width = floor($img->getWidth() * $height / $img->getHeight());
			
			if ($height === null)
				$height = floor($img->getHeight() * $width / $img->getWidth());
			
			if ($width === 0 || $height === 0)
				return array('width' => 0, 'height' => 0);
			
			if ($fit == null)
				$fit = 'inside';
			$dim = array();
			if ($fit == 'fill')
			{
				$dim['width'] = $width;
				$dim['height'] = $height;
			}
			elseif ($fit == 'inside' || $fit == 'outside')
			{
				$rx = $img->getWidth() / $width;
				$ry = $img->getHeight() / $height;
				
				if ($fit == 'inside')
					$ratio = ($rx > $ry) ? $rx : $ry;
				else
					$ratio = ($rx < $ry) ? $rx : $ry;
				
				$dim['width'] = round($img->getWidth() / $ratio);
				$dim['height'] = round($img->getHeight() / $ratio);
			}
			else
				throw new WideImage_Operation_InvalidFitMethodException("{$fit} is not a valid resize-fit method.");
			
			return $dim;
		}
		
		
		function execute($img, $width, $height, $fit, $scale)
		{
			$dim = $this->prepareDimensions($img, $width, $height, $fit);
			if (($scale === 'down' && ($dim['width'] >= $img->getWidth() && $dim['height'] >= $img->getHeight())) ||
				($scale === 'up' && ($dim['width'] <= $img->getWidth() && $dim['height'] <= $img->getHeight())))
				$dim = array('width' => $img->getWidth(), 'height' => $img->getHeight());
			if ($dim['width'] <= 0 || $dim['height'] <= 0)
				throw new WideImage_Operation_InvalidResizeDimensionException("Both dimensions must be larger than 0.");
			
			if ($img->isTransparent() || $img instanceof WideImage_PaletteImage)
			{
				$new = WideImage_PaletteImage::create($dim['width'], $dim['height']);
				$new->copyTransparencyFrom($img);
				if (!imagecopyresized(
						$new->getHandle(), 
						$img->getHandle(),
						0, 0, 0, 0,
						$new->getWidth(),
						$new->getHeight(),
						$img->getWidth(),
						$img->getHeight()))
					throw new WideImage_GDFunctionResultException("imagecopyresized() returned false");
			}
			else
			{
				$new = WideImage_TrueColorImage::create($dim['width'], $dim['height']);
				$new->alphaBlending(false);
				$new->saveAlpha(true);
				if (!imagecopyresampled(
						$new->getHandle(),
						$img->getHandle(),
						0, 0, 0, 0,
						$new->getWidth(),
						$new->getHeight(),
						$img->getWidth(),
						$img->getHeight()))
					throw new WideImage_GDFunctionResultException("imagecopyresampled() returned false");
				$new->alphaBlending(true);
			}
			return $new;
		}
	}
