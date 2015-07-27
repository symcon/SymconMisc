<?

	class Unwetterzentrale extends IPSModule
	{
		
		private $imagePath;
		
		public function __construct($InstanceID)
		{
			//Never delete this line!
			parent::__construct($InstanceID);
			
			//You can add custom code below.
			$this->imagePath = "media/radar".$InstanceID.".gif";
			
		}
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyString("area", "dsch");
			$this->RegisterPropertyInteger("homeX", 324);
			$this->RegisterPropertyInteger("homeY", 179);
			$this->RegisterPropertyInteger("homeRadius", 10);
			
		}		
	
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			
			$this->RegisterVariableInteger("RainValue", "Regenwert");

			//$this->RegisterMediaImage("RadarImage", "Radarbild", $this->imagePath);
			//$this->RegisterEventCyclic("UpdateTimer", "Automatische aktualisierung", 15);
			
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
			
			//Calculate time
			$minute=floor(date("i") / 15) * 15;
			$dateline=mktime(date("H"), $minute, 0, date("m"), date("d"), date("y"));

			//Download picture
			$opts = array(
			'http'=>array(
				'method'=>"GET",
				'max_redirects'=>1
			)
			);
			$context = stream_context_create($opts);

			$remoteImage = "http://www.wetteronline.de/daten/radar/$area/".gmdate("Y", $dateline)."/".gmdate("m", $dateline)."/".gmdate("d", $dateline)."/".gmdate("Hi", $dateline).".gif";
			$data = @file_get_contents($remoteImage, false, $context);
			if($data === false) {
				//No new picture. Download old one.
				$dateline -= 15*60;
				$remoteImage = "http://www.wetteronline.de/daten/radar/$area/".gmdate("Y", $dateline)."/".gmdate("m", $dateline)."/".gmdate("d", $dateline)."/".gmdate("Hi", $dateline).".gif";
				$data = @file_get_contents($remoteImage, false, $context);
				if($data === false) {
					return;
				}
			}

			if((strpos($http_response_header[0], "200") === false)) {
			return;
			}

			file_put_contents($imagePath, $data);

			//Radarbild auswerten
			$im = ImageCreateFromGIF($imagePath);

			//Stärken 
			$regen[6] = imagecolorresolve($im, 250,2,250);
			$regen[5] = imagecolorresolve($im, 156,50,156);
			$regen[4] = imagecolorresolve($im,  28,126,220);
			$regen[3] = imagecolorresolve($im,  44,170,252);
			$regen[2] = imagecolorresolve($im,  84,210,252);
			$regen[1] = imagecolorresolve($im, 172,254,252);

			//Pixel durchgehen
			$regenmenge = 0;
			for($x=$homeX-$homeRadius; $x<=$homeX+$homeRadius; $x++) {
			for($y=$homeY-$homeRadius; $y<=$homeY+$homeRadius; $y++) {
				$found = array_search(imagecolorat($im, $x, $y), $regen);
				if(!($found === FALSE)) {
					$regenmenge+=$found;
				}
			}
			}

			// Bereich zeichnen
			$schwarz = ImageColorAllocate ($im, 0, 0, 0);
			$rot = ImageColorAllocate ($im, 255, 0, 0);
			imagerectangle($im, $homeX-$homeRadius, $homeY-$homeRadius, $homeX+$homeRadius, $homeY+$homeRadius, $rot);
			imagesetpixel($im, $homeX, $homeY, $rot);
			imagegif($im, $localImage);

			imagedestroy($im);

			SetValue($this->GetIDForIdent("RainValue"), $regenmenge);
			
		}
	
	}

?>
