<?php
	
	
	class WideImage_Operation_Unsharp {
		
		function execute($image, $amount, $radius, $threshold) {
						if ($amount > 500)    $amount = 500;
			$amount = $amount * 0.016;
			if ($radius > 50)    $radius = 50;
			$radius = $radius * 2;
			if ($threshold > 255)    $threshold = 255;
			$radius = abs(round($radius));     			if ($radius == 0) {
				return $image;
			}
			
			$matrix = array(
				array(1, 2, 1),
				array(2, 4, 2),
				array(1, 2, 1)
			);
			$blurred = $image->applyConvolution($matrix, 16, 0);
			if($threshold > 0) {
												for ($x = 0; $x < $image->getWidth(); $x++) {
					for ($y = 0; $y < $image->getHeight(); $y++) {
						$rgbOrig = $image->getRGBAt($x, $y);
						$rOrig = $rgbOrig["red"];
						$gOrig = $rgbOrig["green"];
						$bOrig = $rgbOrig["blue"];
						$rgbBlur = $blurred->getRGBAt($x, $y);
						$rBlur = $rgbBlur["red"];
						$gBlur = $rgbBlur["green"];
						$bBlur = $rgbBlur["blue"];
																		$rNew = (abs($rOrig - $rBlur) >= $threshold)
							? max(0, min(255, ($amount * ($rOrig - $rBlur)) + $rOrig))
							: $rOrig;
						$gNew = (abs($gOrig - $gBlur) >= $threshold)
							? max(0, min(255, ($amount * ($gOrig - $gBlur)) + $gOrig))
							: $gOrig;
						$bNew = (abs($bOrig - $bBlur) >= $threshold)
							? max(0, min(255, ($amount * ($bOrig - $bBlur)) + $bOrig))
							: $bOrig;
						$rgbNew = array("red" => $rNew, "green" => $gNew, "blue" => $bNew, "alpha" => 0);
						if (($rOrig != $rNew) || ($gOrig != $gNew) || ($bOrig != $bNew)) {
							$image->setRGBAt($x, $y, $rgbNew);
						}
					}
				}
			}
			else {
				$w = $image->getWidth();
				$h = $image->getHeight();
				for ($x = 0; $x < $w; $x++) {
					for ($y = 0; $y < $h; $y++) {
						$rgbOrig = $image->getRGBAt($x, $y);
						$rOrig = $rgbOrig["red"];
						$gOrig = $rgbOrig["green"];
						$bOrig = $rgbOrig["blue"];
						$rgbBlur = $blurred->getRGBAt($x, $y);
						$rBlur = $rgbBlur["red"];
						$gBlur = $rgbBlur["green"];
						$bBlur = $rgbBlur["blue"];
						$rNew = ($amount * ($rOrig - $rBlur)) + $rOrig;
							if($rNew>255){$rNew=255;}
							elseif($rNew<0){$rNew=0;}
						$gNew = ($amount * ($gOrig - $gBlur)) + $gOrig;
							if($gNew>255){$gNew=255;}
							elseif($gNew<0){$gNew=0;}
						$bNew = ($amount * ($bOrig - $bBlur)) + $bOrig;
							if($bNew>255){$bNew=255;}
							elseif($bNew<0){$bNew=0;}
						$rgbNew = array("red" => $rNew, "green" => $gNew, "blue" => $bNew, "alpha" => 0);
						$image->setRGBAt($x, $y, $rgbNew);
					}
				}
			}
			return $image;
		}
	}
