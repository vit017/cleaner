<?php
	
	
	
	
	class WideImage_InvalidCoordinateException extends WideImage_Exception {}
	
	
	class WideImage_Coordinate
	{
		static protected $coord_align = array("left", "center", "right", "top", "middle", "bottom");
		static protected $coord_numeric = array("[0-9]+", "[0-9]+\.[0-9]+", "[0-9]+%", "[0-9]+\.[0-9]+%");
		
		
		static function parse($c)
		{
			$tokens = array();
			$operators = array('+', '-');
			
			$flush_operand = false;
			$flush_operator = false;
			$current_operand = '';
			$current_operator = '';
			$coordinate = strval($c);
			$expr_len = strlen($coordinate);
			
			for ($i = 0; $i < $expr_len; $i++)
			{
				$char = $coordinate[$i];
				
				if (in_array($char, $operators))
				{
					$flush_operand = true;
					$flush_operator = true;
					$current_operator = $char;
				}
				else
				{
					$current_operand .= $char;
					if ($i == $expr_len - 1)
						$flush_operand = true;
				}
				
				if ($flush_operand)
				{
					if (trim($current_operand) != '')
						$tokens[] = array('type' => 'operand', 'value' => trim($current_operand));
					
					$current_operand = '';
					$flush_operand = false;
				}
				
				if ($flush_operator)
				{
					$tokens[] = array('type' => 'operator', 'value' => $char);
					$flush_operator = false;
				}
			}
			return $tokens;
		}
		
		
		static function evaluate($coord, $dim, $sec_dim = null)
		{
			$comp_regex = implode('|', self::$coord_align) . '|' . implode('|', self::$coord_numeric);
			if (preg_match("/^([+-])?({$comp_regex})$/", $coord, $matches))
			{
				$sign = intval($matches[1] . "1");
				$val = $matches[2];
				if (in_array($val, self::$coord_align))
				{
					if ($sec_dim === null)
					{
						switch ($val)
						{
							case 'left':
							case 'top':
								return 0;
								break;
							case 'center':
							case 'middle':
								return $sign * intval($dim / 2);
								break;
							case 'right':
							case 'bottom':
								return $sign * $dim;
								break;
							default:
								return null;
						}
					}
					else
					{
						switch ($val)
						{
							case 'left':
							case 'top':
								return 0;
								break;
							case 'center':
							case 'middle':
								return $sign * intval($dim / 2 - $sec_dim / 2);
								break;
							case 'right':
							case 'bottom':
								return $sign * ($dim - $sec_dim);
								break;
							default:
								return null;
						}
					}
				}
				elseif (substr($val, -1) === '%')
					return intval(round($sign * $dim * floatval(str_replace('%', '', $val)) / 100));
				else
					return $sign * intval(round($val));
			}
		}
		
		
		static function fix($value, $dim, $sec_dim = null)
		{
			$coord_tokens = self::parse($value);
			
			if (count($coord_tokens) == 0 || $coord_tokens[count($coord_tokens) - 1]['type'] != 'operand')
				throw new WideImage_InvalidCoordinateException("Couldn't parse coordinate '$value' properly.");
			
			$value = 0;
			$operation = 1;
			foreach ($coord_tokens as $token)
			{
				if ($token['type'] == 'operand')
				{
					$operand_value = self::evaluate($token['value'], $dim, $sec_dim);
					if ($operation == 1)
						$value = $value + $operand_value;
					elseif ($operation == -1)
						$value = $value - $operand_value;
					else
						throw new WideImage_InvalidCoordinateException("Invalid coordinate syntax.");
					
					$operation = 0;
				}
				elseif ($token['type'] == 'operator')
				{
					if ($token['value'] == '-')
					{
						if ($operation == 0)
							$operation = -1;
						else
							$operation = $operation * -1;
					}
					elseif ($token['value'] == '+')
					{
						if ($operation == 0)
							$operation = '1';
					}
				}
			}
			return $value;
		}
	}
