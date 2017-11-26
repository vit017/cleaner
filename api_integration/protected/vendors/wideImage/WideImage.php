<?php
	
	require_once WideImage::path() . 'Exception.php';
	require_once WideImage::path() . 'Image.php';
	require_once WideImage::path() . 'TrueColorImage.php';
	require_once WideImage::path() . 'PaletteImage.php';
	require_once WideImage::path() . 'Coordinate.php';
	require_once WideImage::path() . 'Canvas.php';
	require_once WideImage::path() . 'MapperFactory.php';
	require_once WideImage::path() . 'OperationFactory.php';
	require_once WideImage::path() . 'Font/TTF.php';
	require_once WideImage::path() . 'Font/GDF.php';
	require_once WideImage::path() . 'Font/PS.php';
	
	class WideImage_InvalidImageHandleException extends WideImage_Exception {}
	
	class WideImage_InvalidImageSourceException extends WideImage_Exception {}
	
	class WideImage_GDFunctionResultException extends WideImage_Exception {}
	
	class WideImage
	{
		const SIDE_TOP_LEFT = 1;
		const SIDE_TOP = 2;
		const SIDE_TOP_RIGHT = 4;
		const SIDE_RIGHT = 8;
		const SIDE_BOTTOM_RIGHT = 16;
		const SIDE_BOTTOM = 32;
		const SIDE_BOTTOM_LEFT = 64;
		const SIDE_LEFT = 128;
		const SIDE_ALL = 255;
		
		protected static $path = null;
		
		static function version()
		{
			return '11.02.19';
		}
		
		static function path()
		{
			if (self::$path === null)
				self::$path = dirname(__FILE__) . DIRECTORY_SEPARATOR;
			return self::$path;
		}
		
		static function checkGD()
		{
			if (!extension_loaded('gd'))
				throw new WideImage_Exception("WideImage requires the GD extension, but it's apparently not loaded.");
		}
		
		static function registerCustomMapper($mapper_class_name, $mime_type, $extension)
		{
			WideImage_MapperFactory::registerMapper($mapper_class_name, $mime_type, strtoupper($extension));
		}
		
		static function load($source)
		{
			$predictedSourceType = '';
			if ($source == '')
				$predictedSourceType = 'String';
						if (!$predictedSourceType && self::isValidImageHandle($source))
				$predictedSourceType = 'Handle';
						if (!$predictedSourceType)
			{
								$binLength = 64;
				$sourceLength = strlen($source);
				$maxlen = ($sourceLength > $binLength) ? $binLength : $sourceLength;
				for ($i = 0; $i < $maxlen; $i++)
					if (ord($source[$i]) < 32)
					{
						$predictedSourceType = 'String';
						break;
					}
			}
						if (isset($_FILES[$source]) && isset($_FILES[$source]['tmp_name']))
				$predictedSourceType = 'Upload';
						if (!$predictedSourceType)
				$predictedSourceType = 'File';
			return call_user_func(array('WideImage', 'loadFrom' . $predictedSourceType), $source);
		}
		
		static function loadFromFile($uri)
		{
			$data = file_get_contents($uri);
			$handle = @imagecreatefromstring($data);
			if (!self::isValidImageHandle($handle))
			{
				try
				{
										$mapper = WideImage_MapperFactory::selectMapper($uri);
					if ($mapper)
						$handle = $mapper->load($uri);
				}
				catch (WideImage_UnsupportedFormatException $e)
				{
									}
								if (!self::isValidImageHandle($handle))
				{
					$custom_mappers = WideImage_MapperFactory::getCustomMappers();
					foreach ($custom_mappers as $mime_type => $mapper_class)
					{
						$mapper = WideImage_MapperFactory::selectMapper(null, $mime_type);
						$handle = $mapper->loadFromString($data);
						if (self::isValidImageHandle($handle))
							break;
					}
				}
			}
			if (!self::isValidImageHandle($handle))
				throw new WideImage_InvalidImageSourceException("File '{$uri}' appears to be an invalid image source.");
			return self::loadFromHandle($handle);
		}
		
		static function loadFromString($string)
		{
			if (strlen($string) < 128)
				throw new WideImage_InvalidImageSourceException("String doesn't contain image data.");
			$handle = @imagecreatefromstring($string);
			if (!self::isValidImageHandle($handle))
			{
				$custom_mappers = WideImage_MapperFactory::getCustomMappers();
				foreach ($custom_mappers as $mime_type => $mapper_class)
				{
					$mapper = WideImage_MapperFactory::selectMapper(null, $mime_type);
					$handle = $mapper->loadFromString($string);
					if (self::isValidImageHandle($handle))
						break;
				}
			}
			if (!self::isValidImageHandle($handle))
				throw new WideImage_InvalidImageSourceException("String doesn't contain valid image data.");
			return self::loadFromHandle($handle);
		}
		
		static function loadFromHandle($handle)
		{
			if (!self::isValidImageHandle($handle))
				throw new WideImage_InvalidImageSourceException("Handle is not a valid GD image resource.");
			if (imageistruecolor($handle))
				return new WideImage_TrueColorImage($handle);
			else
				return new WideImage_PaletteImage($handle);
		}
		
		static function loadFromUpload($field_name, $index = null)
		{
			if (!array_key_exists($field_name, $_FILES))
				throw new WideImage_InvalidImageSourceException("Upload field '{$field_name}' doesn't exist.");
			if (is_array($_FILES[$field_name]['tmp_name']))
			{
				if (isset($_FILES[$field_name]['tmp_name'][$index]))
					$filename = $_FILES[$field_name]['tmp_name'][$index];
				else
				{
					$result = array();
					foreach ($_FILES[$field_name]['tmp_name'] as $idx => $tmp_name)
						$result[$idx] = self::loadFromFile($tmp_name);
					return $result;
				}
			}
			else
				$filename = $_FILES[$field_name]['tmp_name'];
			if (!file_exists($filename))
				throw new WideImage_InvalidImageSourceException("Uploaded file doesn't exist.");
			return self::loadFromFile($filename);
		}
		
		static function createPaletteImage($width, $height)
		{
			return WideImage_PaletteImage::create($width, $height);
		}
		
		static function createTrueColorImage($width, $height)
		{
			return WideImage_TrueColorImage::create($width, $height);
		}
		
		static function isValidImageHandle($handle)
		{
			return (is_resource($handle) && get_resource_type($handle) == 'gd');
		}
		
		static function assertValidImageHandle($handle)
		{
			if (!self::isValidImageHandle($handle))
				throw new WideImage_InvalidImageHandleException("{$handle} is not a valid image handle.");
		}
	}
	WideImage::checkGD();
	WideImage::registerCustomMapper('WideImage_Mapper_BMP', 'image/bmp', 'bmp');
	WideImage::registerCustomMapper('WideImage_Mapper_TGA', 'image/tga', 'tga');
	