<?php
	
	
	class WideImage_Operation_ApplyConvolution
	{
		
		function execute($image, $matrix, $div, $offset)
		{
			$new = $image->asTrueColor();
			if (!imageconvolution($new->getHandle(), $matrix, $div, $offset))
				throw new WideImage_GDFunctionResultException("imageconvolution() returned false");
			return $new;
		}
	}
