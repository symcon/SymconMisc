<?

	class Sonos extends IPSModule
	{

		public function __construct($InstanceID)
		{
			//Never delete this line!
			parent::__construct($InstanceID);
			
			//These lines are parsed on Symcon Startup or Instance creation
			//You cannot use variables here. Just static values.
			$this->RegisterPropertyString("IPAddress", "");
                        
			$this->RegisterPropertyString("FavoriteStation",  "");
			$this->RegisterPropertyString("WebFrontStations", "<alle>");

		}

		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			
			$this->RegisterProfileIntegerEx("Status.SONOS", "Information", "", "", Array(
				Array(0, "Prev", "", -1),
				Array(1, "Play", "", -1),
				Array(2, "Pause", "", -1),
				Array(3, "Next", "", -1)
			));
			$this->RegisterProfileInteger("Volume.SONOS", "Intensity", "", " %", 0, 100, 1);
                        
                        //Build Associations according to user settings
                        include(__DIR__ . "/radio_stations.php");
                        $Associations          = Array();
                        $AvailableStations     = get_available_stations();
                        $WebFrontStations      = $this->ReadPropertyString("WebFrontStations");
                        $WebFrontStationsArray = explode(",", $WebFrontStations);
                        $FavoriteStation       = $this->ReadPropertyString("FavoriteStation");
                        $Value                 = 0;

                        foreach ( $AvailableStations as $key => $val ) {
                          if (in_array( $val['name'], $WebFrontStationsArray) || $WebFrontStations === "<alle>" ) {
                            if  ( $val['name'] === $FavoriteStation ){
                              $Color = 0xFCEC00;
                            } else {
                              $Color = -1;
                            }
                            $Associations[] = Array($Value++, $val['name'], "", $Color);
                          }
                        }

                        if(IPS_VariableProfileExists("Radio.SONOS")) {
                           IPS_DeleteVariableProfile("Radio.SONOS");
                        }                        
                        $this->RegisterProfileIntegerEx("Radio.SONOS", "Speaker", "", "", $Associations);
			
			$this->RegisterVariableInteger("Status", "Status", "Status.SONOS");
			$this->EnableAction("Status");
			$this->RegisterVariableInteger("Volume", "Volume", "Volume.SONOS");
			$this->EnableAction("Volume");
			$this->RegisterVariableInteger("Radio", "Radio", "Radio.SONOS");
			$this->EnableAction("Radio");
			
		}

		/**
		* This function will be available automatically after the module is imported with the module control.
		* Using the custom prefix this function will be callable from PHP and JSON-RPC through:
		*
		* SNS_Play($id);
		*
		*/
		public function Play()
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->Play();
			
		}
		
		public function Pause()
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->Pause();
			
		}
		
		public function Previous()
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->Previous();
			
		}
		
		public function Next()
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->Next();
			
		}
		
		public function SetVolume($volume)
		{
		
			include(__DIR__ . "/sonos.php");
			(new PHPSonos($this->ReadPropertyString("IPAddress")))->SetVolume($volume);
			
		}

                public function SetRadio($radio)
                {

                       include(__DIR__ . "/sonos.php");
                       include(__DIR__ . "/radio_stations.php");
                       (new PHPSonos($this->ReadPropertyString("IPAddress")))->SetRadio( get_station_url($radio));
                       (new PHPSonos($this->ReadPropertyString("IPAddress")))->Play();

                }
             
                public function SetRadioFavorite()
                {
 
                      $this->SetRadio($this->ReadPropertyString("FavoriteStation"));

                }
		
		public function RequestAction($Ident, $Value)
		{
			
			switch($Ident) {
				case "Status":
					switch($Value) {
						case 0: //Prev
							$this->Previous();
							break;
						case 1: //Play
							$this->Play();
							SetValue($this->GetIDForIdent($Ident), $Value);
							break;
						case 2: //Pause
							$this->Pause();
							SetValue($this->GetIDForIdent($Ident), $Value);
							break;
						case 3: //Next
							$this->Next();
							break;
					}
					break;
				case "Volume":
					$this->SetVolume($Value);
					SetValue($this->GetIDForIdent($Ident), $Value);
					break;
                                case "Radio":
                                        $this->SetRadio(IPS_GetVariableProfile("Radio.SONOS")['Associations'][$Value]['Name']);
                                        SetValue($this->GetIDForIdent($Ident), $Value);
                                        break;
				default:
					throw new Exception("Invalid ident");
			}
		
		}
		
		//Remove on next Symcon update
		protected function RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize) {
		
			if(!IPS_VariableProfileExists($Name)) {
				IPS_CreateVariableProfile($Name, 1);
			} else {
				$profile = IPS_GetVariableProfile($Name);
				if($profile['ProfileType'] != 1)
					throw new Exception("Variable profile type does not match for profile ".$Name);
			}
			
			IPS_SetVariableProfileIcon($Name, $Icon);
			IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
			IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);
			
		}		

		protected function RegisterProfileIntegerEx($Name, $Icon, $Prefix, $Suffix, $Associations) {
                        if ( sizeof($Associations) === 0 ){
                          $MinValue = 0;
                          $MaxValue = 0;
                        } else {
                          $MinValue = $Associations[0][0];
                          $MaxValue = $Associations[sizeof($Associations)-1][0];
                        }
		
			$this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, 0);
		
			foreach($Associations as $Association) {
				IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
			}
			
		}
		
	
	}

?>
