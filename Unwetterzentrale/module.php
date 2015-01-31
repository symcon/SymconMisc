<?

	class Unwetterzentrale /*extends IPSModule*/
	{
		private $imagePath = "";
		private $rainValueVariableID = 0;
	
		public function __construct($InstanceID)
		{
			parent::__construct($InstanceID);
			
			$this->imagePath = "media/radar".$InstanceID.".gif";
			
			$this->RegisterProperty("area", "dsch");
			$this->RegisterProperty("homeX", "324");
			$this->RegisterProperty("homeY", "179");
			$this->RegisterProperty("homeRadius", "10");
						
			$this->rainValueVariableID = $this->RegisterVariableInteger("RainValue", "Regenwert");
			
			$this->RegisterMediaImage("RadarImage", "Radarbild", $this->imagePath);
			$this->RegisterEventCyclic("UpdateTimer", "Automatische aktualisierung", 15);
			
		}
	
		public function ApplyChanges()
		{
			//Sobald der Nutzer neue Eigenschaften per Übernehmen speichert, wollen wir direkt neu Laden
			//Eventuelle Fehler werden dann an die Konsole weitergegeben
			$this->RequestInfo();
		}
	
		/**
		 * Diese Funktion ist als Public deklariert und wird somit automatisch beim IP-Symcon Start mit dem entsprechenden
		 * Prefix verfügbar gemacht. In diesem Fall wäre diese Funktion für den Endkunden direkt aufrufbar über:
		 *
		 * UWZ_RequestInfo($id);
		 *
		 */
		public function RequestInfo()
		{
		
			//Zeit berechnen
			$minute=floor(date("i") / 15) * 15;
			$dateline=mktime(date("H"), $minute, 0, date("m"), date("d"), date("y"));

			//Radarbild Downloaden
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
				//Altes Bild laden
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

			file_put_contents(IPS_GetKernelDir().$this->imagePath, $data);

			//Radarbild auswerten
			$im = ImageCreateFromGIF (IPS_GetKernelDir().$this->imagePath);

			//Stärken 
			$regen[6] = imagecolorresolve  ($im, 250,2,250); 
			$regen[5] = imagecolorresolve  ($im, 156,50,156); 
			$regen[4] = imagecolorresolve  ($im,  28,126,220); 
			$regen[3] = imagecolorresolve  ($im,  44,170,252); 
			$regen[2] = imagecolorresolve  ($im,  84,210,252); 
			$regen[1] = imagecolorresolve  ($im, 172,254,252);  

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

			SetValue($this->rainValueVariableID, $regenmenge);			
		}
	
	}

?>
