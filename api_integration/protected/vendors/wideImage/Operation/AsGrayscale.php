<?php
	
	
	class WideImage_Operation_AsGrayscale
	{
		
		function execute($image)
		{
			$new = $image->asTrueColor();
			if (!imagefilter($new->getHandle(), IMG_FILTER_GRAYSCALE))
				throw new WideImage_GDFunctionResultException("imagefilter() returned false");
			if (!$image->isTrueColor())
				$new = $new->asPalette();
			return $new;
		}
	}
