<?php
	
	
class WideImage_vendor_de77_TGA
{
	static function rle_decode($data, $datalen)
	{
		$len = strlen($data);
		$out = '';
		$i = 0;
		$k = 0;
		while ($i<$len)
		{
			self::dec_bits(ord($data[$i]), $type, $value);
			if ($k >= $datalen)
			{
				break;
			}
			$i++;
			if ($type == 0) 			{
				for ($j=0; $j<3*$value; $j++)
				{
					$out .= $data[$j+$i];
					$k++;
				}
				$i += $value*3;
			}
			else 			{
				for ($j=0; $j<$value; $j++)
				{
					$out .= $data[$i] . $data[$i+1] . $data[$i+2];
					$k++;
				}
				$i += 3;
			}
		}
		return $out;
	}
	static function dec_bits($byte, &$type, &$value)
	{
		$type = ($byte & 0x80) >> 7;
		$value = 1 + ($byte & 0x7F);
	}
	static function imagecreatefromstring($bin_data)
	{
		$bin_pos = 0;
		$header = substr($bin_data, $bin_pos, 18);
		$bin_pos += 18;
		$header = unpack(	"cimage_id_len/ccolor_map_type/cimage_type/vcolor_map_origin/vcolor_map_len/" .
							"ccolor_map_entry_size/vx_origin/vy_origin/vwidth/vheight/" .
							"cpixel_size/cdescriptor", $header);
		switch ($header['image_type'])
		{
			case 2:					case 10:							break;
			default:	return false; 		}
		if ($header['pixel_size'] != 24)
		{
			return false;
					}
		$bytes = $header['pixel_size'] / 8;
		if ($header['image_id_len'] > 0)
		{
			$header['image_id'] = substr($bin_data, $bin_pos, $header['image_id_len']);
			$bin_pos += $header['image_id_len'];
		}
		else
		{
			$header['image_id'] = '';
		}
		$im = imagecreatetruecolor($header['width'], $header['height']);
		$size = $header['width'] * $header['height'] * 3;
				$pos = $bin_pos;
		$bin_pos = strlen($bin_data) - 26;
		$newtga = substr($bin_data, $bin_pos, 26);
		if (substr($newtga, 8, 16) != 'TRUEVISION-XFILE')
		{
			$newtga = false;
		}
		$bin_pos = strlen($bin_data);
		$datasize = $bin_pos - $pos;
		if ($newtga)
		{
			$datasize -= 26;
		}
		$bin_pos = $pos;
				$data = substr($bin_data, $bin_pos, $datasize);
		$bin_pos += $datasize;
		if ($header['image_type'] == 10)
		{
			$data = self::rle_decode($data, $size);
		}
		if (self::bit5($header['descriptor']) == 1)
		{
			$reverse = true;
		}
		else
		{
			$reverse = false;
		}
		$pixels = str_split($data, 3);
		$i = 0;
				if ($reverse)
		{
		    for ($y=0; $y<$header['height']; $y++)
		    {
		    	for ($x=0; $x<$header['width']; $x++)
		    	{
		    		imagesetpixel($im, $x, $y, self::dwordize($pixels[$i]));
		    		$i++;
		    	}
		    }
	    }
	    else
	    {
	        for ($y=$header['height']-1; $y>=0; $y--)
		    {
		    	for ($x=0; $x<$header['width']; $x++)
		    	{
		    		imagesetpixel($im, $x, $y, self::dwordize($pixels[$i]));
		    		$i++;
		    	}
		    }
	    }
		return $im;
	}
	static function imagecreatefromtga($filename)
	{
		return self::imagecreatefromstring(file_get_contents($filename));
	}
	static function dwordize($str)
	{
		$a = ord($str[0]);
		$b = ord($str[1]);
		$c = ord($str[2]);
		return $c*256*256 + $b*256 + $a;
	}
	static function bit5($x)
	{
		return ($x & 32) >> 5;
	}
}