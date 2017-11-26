<?php
	
	
	class WideImage_NoFontException extends WideImage_Exception {}
	
	class WideImage_InvalidFontFileException extends WideImage_Exception {}
	
	class WideImage_InvalidCanvasMethodException extends WideImage_Exception {}
	
	class WideImage_Canvas
	{
		protected $handle = 0;
		protected $image = null;
		protected $font = null;
		
		function __construct($img)
		{
			$this->handle = $img->getHandle();
			$this->image = $img;
		}
		
		function setFont($font)
		{
			$this->font = $font;
		}
		
		function useFont($file, $size = 12, $color = 0, $bgcolor = null)
		{
			$p = strrpos($file, '.');
			if ($p === false || $p < strlen($file) - 4)
				$ext = 'ttf';
			else
				$ext = strtolower(substr($file, $p + 1));
			if ($ext == 'ttf' || $ext == 'otf')
				$font = new WideImage_Font_TTF($file, $size, $color);
			elseif ($ext == 'ps')
				$font = new WideImage_Font_PS($file, $size, $color, $bgcolor);
			elseif ($ext == 'gdf')
				$font = new WideImage_Font_GDF($file, $color);
			else
				throw new WideImage_InvalidFontFileException("'$file' appears to be an invalid font file.");
			$this->setFont($font);
			return $font;
		}
		
		function writeText($x, $y, $text, $angle = 0)
		{
			if ($this->font === null)
				throw new WideImage_NoFontException("Can't write text without a font.");
			$angle = - floatval($angle);
			if ($angle < 0)
				$angle = 360 + $angle;
			$angle = $angle % 360;
			$this->font->writeText($this->image, $x, $y, $text, $angle);
		}
		
		function __call($method, $params)
		{
			if (function_exists('image' . $method))
			{
				array_unshift($params, $this->handle);
				call_user_func_array('image' . $method, $params);
			}
			else
				throw new WideImage_InvalidCanvasMethodException("Function doesn't exist: image{$method}.");
		}
	}
