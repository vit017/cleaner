<?php
	
	
	class WideImage_Operation_CorrectGamma
	{
		
		function execute($image, $input_gamma, $output_gamma)
		{
			$new = $image->copy();
			if (!imagegammacorrect($new->getHandle(), $input_gamma, $output_gamma))
				throw new WideImage_GDFunctionResultException("imagegammacorrect() returned false");
			
			return $new;
		}
	}
