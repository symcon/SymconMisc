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

			$content = file_get_contents("http://www.wetteronline.de/regenradar", false, $context);
			if(preg_match("/wmapsextract\/vermarktung\/global2maps\/(.*?)&/", $content, $url) != 1) {
				echo "URL matching failed";
				return;
			}
			
			//replace DL to our area
			$url = $url[1];
			$url = str_replace("_DL", "_".$area, $url);
			$url = str_replace("/DL/", "/".$area."/", $url);
			
			$remoteImage = "http://www.wetteronline.de/?ireq=true&pid=p_radar_map&src=wmapsextract/vermarktung/global2maps/".$url;
			$data = @file_get_contents($remoteImage, false, $context);

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
			$im = ImageCreateFromPNG($imagePath);

			//Stärken 
			$regen[6] = imagecolorresolve($im, 252,65,255);
			$regen[5] = imagecolorresolve($im, 153,51,153);
			$regen[4] = imagecolorresolve($im,  28,126,217);
			$regen[3] = imagecolorresolve($im,  42,170,255);
			$regen[2] = imagecolorresolve($im,  83,210,255);
			$regen[1] = imagecolorresolve($im, 170,255,255);

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
			imagepng($im, $imagePath);

			imagedestroy($im);

			SetValue($this->GetIDForIdent("RainValue"), $regenmenge);
			
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
				
				IPS_SetMediaFile($mid, $Path, false);
			}
			
			return $mid;
			
		}
	
	}

?>
