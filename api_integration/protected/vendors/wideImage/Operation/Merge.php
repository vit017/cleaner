<?php
	
	
	
	class WideImage_Operation_Merge
	{
		
		function execute($base, $overlay, $left, $top, $pct)
		{
			$x = WideImage_Coordinate::fix($left, $base->getWidth(), $overlay->getWidth());
			$y = WideImage_Coordinate::fix($top, $base->getHeight(), $overlay->getHeight());
			
			$result = $base->asTrueColor();
			$result->alphaBlending(true);
			$result->saveAlpha(true);
			
			if ($pct <= 0)
				return $result;
			
			if ($pct < 100)
			{
				if (!imagecopymerge(
					$result->getHandle(), 
					$overlay->getHandle(), 
					$x, $y, 0, 0, 
					$overlay->getWidth(), 
					$overlay->getHeight(), 
					$pct))
				throw new WideImage_GDFunctionResultException("imagecopymerge() returned false");
			}
			else
			{
				if (!imagecopy(
					$result->getHandle(), 
					$overlay->getHandle(), 
					$x, $y, 0, 0, 
					$overlay->getWidth(), 
					$overlay->getHeight()))
				throw new WideImage_GDFunctionResultException("imagecopy() returned false");
			}
			
			return $result;
		}
	}
