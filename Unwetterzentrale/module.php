<?

	class Unwetterzentrale extends IPSModule
	{
		
		private $imagePath;
		
		public function __construct($InstanceID)
		{
			//Never delete this line!
			parent::__construct($InstanceID);
			
			//You can add custom code below.
			$this->imagePath = "media/radar".$InstanceID.".png";
			
		}
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyString("area", "SHS");
			$this->RegisterPropertyInteger("homeX", 420);
			$this->RegisterPropertyInteger("homeY", 352);
			$this->RegisterPropertyInteger("homeRadius", 10);
			$this->RegisterPropertyInteger("Interval", 900);
			
			$this->RegisterTimer("UpdateTimer", 900 * 1000, 'UWZ_RequestInfo($_IPS[\'TARGET\']);');
		}
	
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			
			$this->RegisterVariableInteger("RainValue", "Regenwert");

		}

		private function ConvertArea($area) {

			switch($area) {
				case "DL":
					return "brd";
				case "BWB":
					return "baw";
                case "BAY":
                    return "bay";
                case "BRA":
                    return "bbb";
                case "HES":
                    return "hes";
                case "MVP":
                    return "mvp";
                case "NIE":
                    return "nib";
                case "NRW":
                    return "nrw";
                case "RHP":
                    return "rps";
                case "SAC":
                    return "sac";
                case "SAH":
                    return "saa";
                case "SHS":
                    return "shh";
                case "THU":
                    return "thu";
				default:
					throw new Exception("Unknown area");
			}

		}

		/**
		* This function will be available automatically after the module is imported with the module control.
		* Using the custom prefix this function will be callable from PHP and JSON-RPC through:
		*
		* UWZ_RequestInfo($id);
		*
		*/
		public function RequestInfo()
		{
		
			$imagePath = IPS_GetKernelDir() . $this->imagePath;
			$area = $this->ReadPropertyString("area");
			$homeX = $this->ReadPropertyInteger("homeX");
			$homeY = $this->ReadPropertyInteger("homeY");
			$homeRadius = $this->ReadPropertyInteger("homeRadius");
			
			//Download picture
			$opts = array(
			'http'=>array(
				'method'=>"GET",
				'max_redirects'=>1,
				'header'=>"User-Agent: "."Mozilla/5.0 (Windows NT 10.0; WOW64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/50.0.2661.102 Safari/537.36"
			)
			);
			$context = stream_context_create($opts);

			$remoteImage = "https://www.dwd.de/DWD/wetter/radar/rad_" . $this->ConvertArea($area) ."_akt.jpg";
			$data = file_get_contents($remoteImage, false, $context);

			if($data === false) {
				return;
			}
			
			$this->SendDebug($http_response_header[0], $remoteImage, 0);
			
			if((strpos($http_response_header[0], "200") === false)) {
				echo $http_response_header[0]." ".$data;
				return;
			}

			file_put_contents($imagePath, $data);

			$mid = $this->RegisterMediaImage("RadarImage", "Radarbild", $this->imagePath);
			
			//Bild aktualisiern lassen in IP-Symcon
			IPS_SendMediaEvent($mid);
			
			//Radarbild auswerten
			$im = ImageCreateFromJPEG($imagePath);

			//Stärken
			$rainColors[6] = array(
				"r" => 255,
				"g" => 0,
				"b" => 0
			);
			$allowedDifference[6] = 20;

			$rainColors[5] = array(
				"r" => 255,
				"g" => 0,
				"b" => 221
			);
            $allowedDifference[5] = 20;

			$rainColors[4] = array(
				"r" => 0,
				"g" => 0,
				"b" => 255
			);
            $allowedDifference[4] = 30;

			$rainColors[3] = array(
				"r" => 25,
				"g" => 229,
				"b" => 255
			);
            $allowedDifference[3] = 40;

			$rainColors[2] = array(
				"r" => 0,
				"g" => 127,
				"b" => 0
			);
            $allowedDifference[2] = 15;

			$rainColors[1] = array(
				"r" => 237,
				"g" => 255,
				"b" => 94
			);
            $allowedDifference[1] = 30;

			$all = 0;
			$matched = 0;

			//Pixel durchgehen
			$rainValue = 0;
			for($x=$homeX-$homeRadius; $x<=$homeX+$homeRadius; $x++) {
				for($y=$homeY-$homeRadius; $y<=$homeY+$homeRadius; $y++) {
					$rgb = imagecolorat($im, $x, $y);
					$pixelColor = array(
                        "r" => ($rgb >> 16) & 0xFF,
                    	"g" => ($rgb >> 8) & 0xFF,
                    	"b" => $rgb & 0xFF
					);

					$colorMatches = [];
					foreach($rainColors as $index => $rainColor) {
                        $colorMatches[$index] = (new color_difference())->deltaECIE2000(array_values($pixelColor), array_values($rainColor));
					}

                    asort($colorMatches);

					foreach($colorMatches as $index => $rainColor) {
                        if($colorMatches[$index] < $allowedDifference[$index]) {
                            //$this->SendDebug("Rain", print_r($colorMatches, true), 0);
                            $rainValue+=$index;
                        }
                    	break; //we only want the first
                    }
				}
			}

            $this->SendDebug("Stats", $matched . " / " . $all, 0);

			// Bereich zeichnen
			$rot = ImageColorAllocate ($im, 255, 0, 0);
			imagerectangle($im, $homeX-$homeRadius, $homeY-$homeRadius, $homeX+$homeRadius, $homeY+$homeRadius, $rot);
			imagesetpixel($im, $homeX, $homeY, $rot);
			imagepng($im, $imagePath);

			imagedestroy($im);

			SetValue($this->GetIDForIdent("RainValue"), $rainValue);
			
		}
		
		private function RegisterMediaImage($Ident, $Name, $Path) {
		
			//search for already available media with proper ident
			$mid = @IPS_GetObjectIDByIdent($Ident, $this->InstanceID);
		
			//properly update mediaID
			if($mid === false)
				$mid = 0;
				
			//we need to create one
			if($mid == 0)
			{
				$mid = IPS_CreateMedia(1);
				
				//configure it
				IPS_SetParent($mid, $this->InstanceID);
				IPS_SetIdent($mid, $Ident);
				IPS_SetName($mid, $Name);
				//IPS_SetReadOnly($mid, true);
			}

			//update path if needed
			if(IPS_GetMedia($mid)['MediaFile'] != $Path) {
                IPS_SetMediaFile($mid, $Path, false);
			}

            return $mid;
			
		}
	
	}

	//Copyright: https://github.com/renasboy/php-color-difference
	class color_difference
	{
		public function deltaECIE2000($rgb1, $rgb2)
		{
			list($l1, $a1, $b1) = $this->_rgb2lab($rgb1);
			list($l2, $a2, $b2) = $this->_rgb2lab($rgb2);

			$avg_lp = ($l1 + $l2) / 2;
			$c1 = sqrt(pow($a1, 2) + pow($b1, 2));
			$c2 = sqrt(pow($a2, 2) + pow($b2, 2));
			$avg_c = ($c1 + $c2) / 2;
			$g = (1 - sqrt(pow($avg_c, 7) / (pow($avg_c, 7) + pow(25, 7)))) / 2;
			$a1p = $a1 * (1 + $g);
			$a2p = $a2 * (1 + $g);
			$c1p = sqrt(pow($a1p, 2) + pow($b1, 2));
			$c2p = sqrt(pow($a2p, 2) + pow($b2, 2));
			$avg_cp = ($c1p + $c2p) / 2;
			$h1p = rad2deg(atan2($b1, $a1p));
			if ($h1p < 0) {
				$h1p += 360;
			}
			$h2p = rad2deg(atan2($b2, $a2p));
			if ($h2p < 0) {
				$h2p += 360;
			}
			$avg_hp = abs($h1p - $h2p) > 180 ? ($h1p + $h2p + 360) / 2 : ($h1p + $h2p) / 2;
			$t = 1 - 0.17 * cos(deg2rad($avg_hp - 30)) + 0.24 * cos(deg2rad(2 * $avg_hp)) + 0.32 * cos(deg2rad(3 * $avg_hp + 6)) - 0.2 * cos(deg2rad(4 * $avg_hp - 63));
			$delta_hp = $h2p - $h1p;
			if (abs($delta_hp) > 180) {
				if ($h2p <= $h1p) {
					$delta_hp += 360;
				} else {
					$delta_hp -= 360;
				}
			}
			$delta_lp = $l2 - $l1;
			$delta_cp = $c2p - $c1p;
			$delta_hp = 2 * sqrt($c1p * $c2p) * sin(deg2rad($delta_hp) / 2);
			$s_l = 1 + ((0.015 * pow($avg_lp - 50, 2)) / sqrt(20 + pow($avg_lp - 50, 2)));
			$s_c = 1 + 0.045 * $avg_cp;
			$s_h = 1 + 0.015 * $avg_cp * $t;
			$delta_ro = 30 * exp(-(pow(($avg_hp - 275) / 25, 2)));
			$r_c = 2 * sqrt(pow($avg_cp, 7) / (pow($avg_cp, 7) + pow(25, 7)));
			$r_t = -$r_c * sin(2 * deg2rad($delta_ro));
			$kl = $kc = $kh = 1;
			$delta_e = sqrt(pow($delta_lp / ($s_l * $kl), 2) + pow($delta_cp / ($s_c * $kc), 2) + pow($delta_hp / ($s_h * $kh), 2) + $r_t * ($delta_cp / ($s_c * $kc)) * ($delta_hp / ($s_h * $kh)));
			return $delta_e;
		}

		private function _rgb2lab($rgb)
		{
			return $this->_xyz2lab($this->_rgb2xyz($rgb));
		}

		private function _rgb2xyz($rgb)
		{
			list($r, $g, $b) = $rgb;
			$r = $r <= 0.04045 ? $r / 12.92 : pow(($r + 0.055) / 1.055, 2.4);
			$g = $g <= 0.04045 ? $g / 12.92 : pow(($g + 0.055) / 1.055, 2.4);
			$b = $b <= 0.04045 ? $b / 12.92 : pow(($b + 0.055) / 1.055, 2.4);
			$r *= 100;
			$g *= 100;
			$b *= 100;
			$x = $r * 0.412453 + $g * 0.357580 + $b * 0.180423;
			$y = $r * 0.212671 + $g * 0.715160 + $b * 0.072169;
			$z = $r * 0.019334 + $g * 0.119193 + $b * 0.950227;
			return [$x, $y, $z];
		}

		private function _xyz2lab($xyz)
		{
			list ($x, $y, $z) = $xyz;
			$x /= 95.047;
			$y /= 100;
			$z /= 108.883;
			$x = $x > 0.008856 ? pow($x, 1 / 3) : $x * 7.787 + 16 / 116;
			$y = $y > 0.008856 ? pow($y, 1 / 3) : $y * 7.787 + 16 / 116;
			$z = $z > 0.008856 ? pow($z, 1 / 3) : $z * 7.787 + 16 / 116;
			$l = $y * 116 - 16;
			$a = ($x - $y) * 500;
			$b = ($y - $z) * 200;
			return [$l, $a, $b];
		}
	}

?>
