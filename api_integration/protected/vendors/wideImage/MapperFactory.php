<?php
	
	
	
	class WideImage_UnsupportedFormatException extends WideImage_Exception {}
	
	
	abstract class WideImage_MapperFactory
	{
		static protected $mappers = array();
		static protected $customMappers = array();
		
		static protected $mimeTable = array(
			'image/jpg' => 'JPEG', 
			'image/jpeg' => 'JPEG', 
			'image/pjpeg' => 'JPEG', 
			'image/gif' => 'GIF', 
			'image/png' => 'PNG'
			);
		
		
		static function selectMapper($uri, $format = null)
		{
			$format = self::determineFormat($uri, $format);
			
			if (array_key_exists($format, self::$mappers))
				return self::$mappers[$format];
			
			$mapperClassName = 'WideImage_Mapper_' . $format;
			
			if (!class_exists($mapperClassName, false))
			{
				$mapperFileName = WideImage::path() . 'Mapper/' . $format . '.php';
				if (file_exists($mapperFileName))
					require_once $mapperFileName;
			}
			
			if (class_exists($mapperClassName))
			{
				self::$mappers[$format] = new $mapperClassName();
				return self::$mappers[$format];
			}
			
			throw new WideImage_UnsupportedFormatException("Format '{$format}' is not supported.");
		}
		
		static function registerMapper($mapper_class_name, $mime_type, $extension)
		{
			self::$customMappers[$mime_type] = $mapper_class_name;
			self::$mimeTable[$mime_type] = $extension;
		}
		
		static function getCustomMappers()
		{
			return self::$customMappers;
		}
		
		static function determineFormat($uri, $format = null)
		{
			if ($format == null)
				$format = self::extractExtension($uri);
			
						if (preg_match('~[a-z]*/[a-z-]*~i', $format))
				if (isset(self::$mimeTable[strtolower($format)]))
				{
					return self::$mimeTable[strtolower($format)];
				}
			
						$format = strtoupper(preg_replace('/[^a-z0-9_-]/i', '', $format));
			if ($format == 'JPG')
				$format = 'JPEG';
			
			return $format;
		}
		
		static function mimeType($format)
		{
			return array_search(strtoupper($format), self::$mimeTable);
		}
		
		static function extractExtension($uri)
		{
			$p = strrpos($uri, '.');
			if ($p === false)
				return '';
			else
				return substr($uri, $p + 1);
		}
	}
