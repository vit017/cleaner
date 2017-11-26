<?php
	
	
	
	class WideImage_InvalidImageDimensionException extends WideImage_Exception {}
	
	
	class WideImage_UnknownErrorWhileMappingException extends WideImage_Exception {}
	
	
	abstract class WideImage_Image
	{
		
		protected $handle = null;
		
		
		protected $handleReleased = false;
		
		
		protected $canvas = null;
		
		
		protected $sdata = null;
		
		
		function __construct($handle)
		{
			WideImage::assertValidImageHandle($handle);
			$this->handle = $handle;
		}
		
		
		function __destruct()
		{
			$this->destroy();
		}
		
		
		function destroy()
		{
			if ($this->isValid() && !$this->handleReleased)
				imagedestroy($this->handle);
			
			$this->handle = null;
		}
		
		
		function getHandle()
		{
			return $this->handle;
		}
		
		
		function isValid()
		{
			return WideImage::isValidImageHandle($this->handle);
		}
		
		
		function releaseHandle()
		{
			$this->handleReleased = true;
		}
		
		
		function saveToFile($uri)
		{
			$mapper = WideImage_MapperFactory::selectMapper($uri, null);
			$args = func_get_args();
			array_unshift($args, $this->getHandle());
			$res = call_user_func_array(array($mapper, 'save'), $args);
			if (!$res)
				throw new WideImage_UnknownErrorWhileMappingException(get_class($mapper) . " returned an invalid result while saving to $uri");
		}
		
		
		function asString($format)
		{
			ob_start();
			$args = func_get_args();
			$args[0] = null;
			array_unshift($args, $this->getHandle());
			
			$mapper = WideImage_MapperFactory::selectMapper(null, $format);
			$res = call_user_func_array(array($mapper, 'save'), $args);
			if (!$res)
				throw new WideImage_UnknownErrorWhileMappingException(get_class($mapper) . " returned an invalid result while writing the image data");
			
			return ob_get_clean();
		}
		
		
		protected function writeHeader($name, $data)
		{
			header($name . ": " . $data);
		}
		
		
		function output($format)
		{
			$args = func_get_args();
			$data = call_user_func_array(array($this, 'asString'), $args);
			
			$this->writeHeader('Content-length', strlen($data));
			$this->writeHeader('Content-type', WideImage_MapperFactory::mimeType($format));
			echo $data;
		}
		
		
		function getWidth()
		{
			return imagesx($this->handle);
		}
		
		
		function getHeight()
		{
			return imagesy($this->handle);
		}
		
		
		function allocateColor($R, $G = null, $B = null)
		{
			if (is_array($R))
				return imageColorAllocate($this->handle, $R['red'], $R['green'], $R['blue']);
			else
				return imageColorAllocate($this->handle, $R, $G, $B);
		}
		
		
		function isTransparent()
		{
			return $this->getTransparentColor() >= 0;
		}
		
		
		function getTransparentColor()
		{
			return imagecolortransparent($this->handle);
		}
		
		
		function setTransparentColor($color)
		{
			return imagecolortransparent($this->handle, $color);
		}
		
		
		function getTransparentColorRGB()
		{
			$total = imagecolorstotal($this->handle);
			$tc = $this->getTransparentColor();
			
			if ($tc >= $total && $total > 0)
				return null;
			else
				return $this->getColorRGB($tc);
		}
		
		
		function getRGBAt($x, $y)
		{
			return $this->getColorRGB($this->getColorAt($x, $y));
		}
		
		
		function setRGBAt($x, $y, $color)
		{
			$this->setColorAt($x, $y, $this->getExactColor($color));
		}
		
		
		function getColorRGB($colorIndex)
		{
			return imageColorsForIndex($this->handle, $colorIndex);
		}
		
		
		function getColorAt($x, $y)
		{
			return imagecolorat($this->handle, $x, $y);
		}
		
		
		function setColorAt($x, $y, $color)
		{
			return imagesetpixel($this->handle, $x, $y, $color);
		}
		
		
		function getClosestColor($R, $G = null, $B = null)
		{
			if (is_array($R))
				return imagecolorclosest($this->handle, $R['red'], $R['green'], $R['blue']);
			else
				return imagecolorclosest($this->handle, $R, $G, $B);
		}
		
		
		function getExactColor($R, $G = null, $B = null)
		{
			if (is_array($R))
				return imagecolorexact($this->handle, $R['red'], $R['green'], $R['blue']);
			else
				return imagecolorexact($this->handle, $R, $G, $B);
		}
		
		
		function copyTransparencyFrom($sourceImage, $fill = true)
		{
			if ($sourceImage->isTransparent())
			{
				$rgba = $sourceImage->getTransparentColorRGB();
				if ($rgba === null)
					return;
				
				if ($this->isTrueColor())
				{
					$rgba['alpha'] = 127;
					$color = $this->allocateColorAlpha($rgba);
				}
				else
					$color = $this->allocateColor($rgba);
				
				$this->setTransparentColor($color);
				if ($fill)
					$this->fill(0, 0, $color);
			}
		}
		
		
		function fill($x, $y, $color)
		{
			return imagefill($this->handle, $x, $y, $color);
		}
		
		
		protected function getOperation($name)
		{
			return WideImage_OperationFactory::get($name);
		}
		
		
		function getMask()
		{
			return $this->getOperation('GetMask')->execute($this);
		}
		
		
		function resize($width = null, $height = null, $fit = 'inside', $scale = 'any')
		{
			return $this->getOperation('Resize')->execute($this, $width, $height, $fit, $scale);
		}
		
		
		function resizeDown($width = null, $height = null, $fit = 'inside')
		{
			return $this->resize($width, $height, $fit, 'down');
		}
		
		
		function resizeUp($width = null, $height = null, $fit = 'inside')
		{
			return $this->resize($width, $height, $fit, 'up');
		}
		
		
		function rotate($angle, $bgColor = null, $ignoreTransparent = true)
		{
			return $this->getOperation('Rotate')->execute($this, $angle, $bgColor, $ignoreTransparent);
		}
		
		
		function merge($overlay, $left = 0, $top = 0, $pct = 100)
		{
			return $this->getOperation('Merge')->execute($this, $overlay, $left, $top, $pct);
		}
		
		
		function resizeCanvas($width, $height, $pos_x, $pos_y, $bg_color = null, $scale = 'any', $merge = false)
		{
			return $this->getOperation('ResizeCanvas')->execute($this, $width, $height, $pos_x, $pos_y, $bg_color, $scale, $merge);
		}
		
		
		function roundCorners($radius, $color = null, $smoothness = 2, $corners = 255)
		{
			return $this->getOperation('RoundCorners')->execute($this, $radius, $color, $smoothness, $corners);
		}
		
		
		function applyMask($mask, $left = 0, $top = 0)
		{
			return $this->getOperation('ApplyMask')->execute($this, $mask, $left, $top);
		}
		
		
		function applyFilter($filter, $arg1 = null, $arg2 = null, $arg3 = null, $arg4 = null)
		{
			return $this->getOperation('ApplyFilter')->execute($this, $filter, $arg1, $arg2, $arg3, $arg4);
		}
		
		
		function applyConvolution($matrix, $div, $offset)
		{
			return $this->getOperation('ApplyConvolution')->execute($this, $matrix, $div, $offset);
		}
		
		
		function crop($left = 0, $top = 0, $width = '100%', $height = '100%')
		{
			return $this->getOperation('Crop')->execute($this, $left, $top, $width, $height);
		}
		
		
		function autoCrop($margin = 0, $rgb_threshold = 0, $pixel_cutoff = 1, $base_color = null)
		{
			return $this->getOperation('AutoCrop')->execute($this, $margin, $rgb_threshold, $pixel_cutoff, $base_color);
		}
		
		
		function asNegative()
		{
			return $this->getOperation('AsNegative')->execute($this);
		}
		
		
		function asGrayscale()
		{
			return $this->getOperation('AsGrayscale')->execute($this);
		}
		
		
		function mirror()
		{
			return $this->getOperation('Mirror')->execute($this);
		}
		
		
		function unsharp($amount, $radius, $threshold)
		{
			return $this->getOperation('Unsharp')->execute($this, $amount, $radius, $threshold);
		}
		
		
		function flip()
		{
			return $this->getOperation('Flip')->execute($this);
		}
		
		
		function correctGamma($inputGamma, $outputGamma)
		{
			return $this->getOperation('CorrectGamma')->execute($this, $inputGamma, $outputGamma);
		}
		
		
		function addNoise($amount, $type)
		{
			return $this->getOperation('AddNoise')->execute($this, $amount, $type);
		}
		
		
		function __call($name, $args)
		{
			$op = $this->getOperation($name);
			array_unshift($args, $this);
			return call_user_func_array(array($op, 'execute'), $args);
		}
		
		
		function __toString()
		{
			if ($this->isTransparent())
				return $this->asString('gif');
			else
				return $this->asString('png');
		}
		
		
		function copy()
		{
			$dest = $this->doCreate($this->getWidth(), $this->getHeight());
			$dest->copyTransparencyFrom($this, true);
			$this->copyTo($dest, 0, 0);
			return $dest;
		}
		
		
		function copyTo($dest, $left = 0, $top = 0)
		{
			if (!imagecopy($dest->getHandle(), $this->handle, $left, $top, 0, 0, $this->getWidth(), $this->getHeight()))
				throw new WideImage_GDFunctionResultException("imagecopy() returned false");
		}
		
		
		function getCanvas()
		{
			if ($this->canvas == null)
				$this->canvas = new WideImage_Canvas($this);
			return $this->canvas;
		}
		
		
		abstract function isTrueColor();
		
		
		abstract function asTrueColor();
		
		
		abstract function asPalette($nColors = 255, $dither = null, $matchPalette = true);
		
		
		abstract function getChannels();
		
		
		abstract function copyNoAlpha();
		
		
		function __sleep()
		{
			$this->sdata = $this->asString('png');
			return array('sdata', 'handleReleased');
		}
		
		
		function __wakeup()
		{
			$temp_image = WideImage::loadFromString($this->sdata);
			$temp_image->releaseHandle();
			$this->handle = $temp_image->handle;
			$temp_image = null;
			$this->sdata = null;
		}
	}
