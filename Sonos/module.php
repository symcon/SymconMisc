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
			
			$this->RegisterVariableInteger("Status", "Status", "Status.SONOS");
			$this->EnableAction("Status");
			$this->RegisterVariableInteger("Volume", "Volume", "Volume.SONOS");
			$this->EnableAction("Volume");
			
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
		
			$this->RegisterProfileInteger($Name, $Icon, $Prefix, $Suffix, $Associations[0][0], $Associations[sizeof($Associations)-1][0], 0);
		
			foreach($Associations as $Association) {
				IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
			}
			
		}
		
	
	}

?>
