<?php
	
	
	class WideImage_TrueColorImage extends WideImage_Image
	{
		
		function __construct($handle)
		{
			parent::__construct($handle);
			$this->alphaBlending(false);
			$this->saveAlpha(true);
		}
		
		static function create($width, $height)
		{
			if ($width * $height <= 0 || $width < 0)
				throw new WideImage_InvalidImageDimensionException("Can't create an image with dimensions [$width, $height].");
			return new WideImage_TrueColorImage(imagecreatetruecolor($width, $height));
		}
		function doCreate($width, $height)
		{
			return self::create($width, $height);
		}
		function isTrueColor()
		{
			return true;
		}
		
		function alphaBlending($mode)
		{
			return imagealphablending($this->handle, $mode);
		}
		
		function saveAlpha($on)
		{
			return imagesavealpha($this->handle, $on);
		}
		
		function allocateColorAlpha($R, $G = null, $B = null, $A = null)
		{
			if (is_array($R))
				return imageColorAllocateAlpha($this->handle, $R['red'], $R['green'], $R['blue'], $R['alpha']);
			else
				return imageColorAllocateAlpha($this->handle, $R, $G, $B, $A);
		}
		
		function asPalette($nColors = 255, $dither = null, $matchPalette = true)
		{
			$nColors = intval($nColors);
			if ($nColors < 1)
				$nColors = 1;
			elseif ($nColors > 255)
				$nColors = 255;
			if ($dither === null)
				$dither = $this->isTransparent();
			$temp = $this->copy();
			imagetruecolortopalette($temp->handle, $dither, $nColors);
			if ($matchPalette == true && function_exists('imagecolormatch'))
				imagecolormatch($this->handle, $temp->handle);
									
			$temp->releaseHandle();
			return new WideImage_PaletteImage($temp->handle);
		}
		
		function getClosestColorAlpha($R, $G = null, $B = null, $A = null)
		{
			if (is_array($R))
				return imagecolorclosestalpha($this->handle, $R['red'], $R['green'], $R['blue'], $R['alpha']);
			else
				return imagecolorclosestalpha($this->handle, $R, $G, $B, $A);
		}
		
		function getExactColorAlpha($R, $G = null, $B = null, $A = null)
		{
			if (is_array($R))
				return imagecolorexactalpha($this->handle, $R['red'], $R['green'], $R['blue'], $R['alpha']);
			else
				return imagecolorexactalpha($this->handle, $R, $G, $B, $A);
		}
		
		function getChannels()
		{
			$args = func_get_args();
			if (count($args) == 1 && is_array($args[0]))
				$args = $args[0];
			return WideImage_OperationFactory::get('CopyChannelsTrueColor')->execute($this, $args);
		}
		
		function copyNoAlpha()
		{
			$prev = $this->saveAlpha(false);
			$result = WideImage_Image::loadFromString($this->asString('png'));
			$this->saveAlpha($prev);
						return $result;
		}
		
		function asTrueColor()
		{
			return $this->copy();
		}
	}
