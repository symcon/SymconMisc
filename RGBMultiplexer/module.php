<?
	if (!defined('IPS_BASE')) {
		define("IPS_BASE", 10000);
	}
	if (!defined('VM_UPDATE')) {
		define("VM_UPDATE", IPS_BASE + 603);
	}

	class RGBMultiplexer extends IPSModule
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyInteger("SourceVariableR", 0);
            $this->RegisterPropertyInteger("SourceVariableG", 0);
            $this->RegisterPropertyInteger("SourceVariableB", 0);

			$this->RegisterVariableInteger("Color", "Color", "HexColor", 0);
            $this->EnableAction("Color");

            $this->RegisterTimer("Update", 0, "RGBM_RequestStatus(\$_IPS['TARGET']);");

        }
	
		public function ApplyChanges()
		{
			
			//Never delete this line!
			parent::ApplyChanges();

			if($this->ReadPropertyInteger("SourceVariableR") > 0) {
                $this->RegisterMessage($this->ReadPropertyInteger("SourceVariableR"), VM_UPDATE);
			}
            if($this->ReadPropertyInteger("SourceVariableG") > 0) {
                $this->RegisterMessage($this->ReadPropertyInteger("SourceVariableG"), VM_UPDATE);
            }
            if($this->ReadPropertyInteger("SourceVariableB") > 0) {
                $this->RegisterMessage($this->ReadPropertyInteger("SourceVariableB"), VM_UPDATE);
            }

		}

        public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {

			//Kick off a timer. This will prevent multiple calls to SetValue and a visual "color" jumping
            $this->SetTimerInterval("Update", 250);

        }

        public function RequestAction($Ident, $Value) {

            switch($Ident) {
                case "Color":
                    $this->SetRGB(($Value >> 16) & 0xFF, ($Value >> 8) & 0xFF, $Value & 0xFF);
                    break;
                default:
                    throw new Exception("Invalid Ident");
            }

        }

        public function RequestStatus() {

			$R = $this->getDimValue($this->ReadPropertyInteger("SourceVariableR")) / 100 * 255;
            $G = $this->getDimValue($this->ReadPropertyInteger("SourceVariableG")) / 100 * 255;
            $B = $this->getDimValue($this->ReadPropertyInteger("SourceVariableB")) / 100 * 255;

            SetValue($this->GetIDForIdent("Color"), ($R << 16) + ($G << 8) + $B);

            $this->SetTimerInterval("Update", 0);
		}

		public function SetRGB(int $R, int $G, int $B) {

			$this->dimDevice($this->ReadPropertyInteger("SourceVariableR"), $R / 255 * 100);
            $this->dimDevice($this->ReadPropertyInteger("SourceVariableG"), $G / 255 * 100);
            $this->dimDevice($this->ReadPropertyInteger("SourceVariableB"), $B / 255 * 100);

            SetValue($this->GetIDForIdent("Color"), ($R << 16) + ($G << 8) + $B);

		}

        //Remove this in the future and reference our submodule
        private static function getDimValue($variableID)
        {
            $targetVariable = IPS_GetVariable($variableID);

            if ($targetVariable['VariableCustomProfile'] != '') {
                $profileName = $targetVariable['VariableCustomProfile'];
            } else {
                $profileName = $targetVariable['VariableProfile'];
            }

            $profile = IPS_GetVariableProfile($profileName);

            if (($profile['MaxValue'] - $profile['MinValue']) <= 0) {
                return 0;
            }

            $valueToPercent = function ($value) use ($profile) {
                return (($value - $profile['MinValue']) / ($profile['MaxValue'] - $profile['MinValue'])) * 100;
            };

            $value = $valueToPercent(GetValue($variableID));

            // Revert value for reversed profile
            if (preg_match('/\.Reversed$/', $profileName)) {
                $value = 100 - $value;
            }

            return $value;
        }

		//Remove this in the future and reference our submodule
        private static function dimDevice($variableID, $value)
        {
            if (!IPS_VariableExists($variableID)) {
                return false;
            }

            $targetVariable = IPS_GetVariable($variableID);

            if ($targetVariable['VariableCustomProfile'] != '') {
                $profileName = $targetVariable['VariableCustomProfile'];
            } else {
                $profileName = $targetVariable['VariableProfile'];
            }

            if (!IPS_VariableProfileExists($profileName)) {
                return false;
            }

            // Revert value for reversed profile
            if (preg_match('/\.Reversed$/', $profileName)) {
                $value = 100 - $value;
            }

            $profile = IPS_GetVariableProfile($profileName);

            if (($profile['MaxValue'] - $profile['MinValue']) <= 0) {
                return false;
            }

            if ($targetVariable['VariableCustomAction'] != 0) {
                $profileAction = $targetVariable['VariableCustomAction'];
            } else {
                $profileAction = $targetVariable['VariableAction'];
            }

            if ($profileAction < 10000) {
                return false;
            }

            $percentToValue = function ($value) use ($profile) {
                return (max(0, min($value, 100)) / 100) * ($profile['MaxValue'] - $profile['MinValue']) + $profile['MinValue'];
            };

            if ($targetVariable['VariableType'] == 1 /* Integer */) {
                $value = intval($percentToValue($value));
            } elseif ($targetVariable['VariableType'] == 2 /* Float */) {
                $value = floatval($percentToValue($value));
            } else {
                return false;
            }

            if (IPS_InstanceExists($profileAction)) {
                IPS_RunScriptText('IPS_RequestAction(' . var_export($profileAction, true) . ', ' . var_export(IPS_GetObject($variableID)['ObjectIdent'], true) . ', ' . var_export($value, true) . ');');
            } elseif (IPS_ScriptExists($profileAction)) {
                IPS_RunScriptEx($profileAction, ['VARIABLE' => $variableID, 'VALUE' => $value, 'SENDER' => 'RGBMultiplexer']);
            } else {
                return false;
            }

            return true;
        }

	}

?>
