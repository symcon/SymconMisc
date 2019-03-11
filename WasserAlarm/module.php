<?
class WasserAlarm extends IPSModule {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("MeterID", 0);
		$this->RegisterPropertyInteger("LeakInterval", 1);
		$this->RegisterPropertyInteger("PipeBurstInterval", 5);
		
		//Timer
		$this->RegisterTimer("UpdateLeak", 0, 'WAA_CheckAlert($_IPS[\'TARGET\'], "LeakThreshold", "LeakBuffer");');
		$this->RegisterTimer("UpdatePipeBurst", 0, 'WAA_CheckAlert($_IPS[\'TARGET\'], "PipeBurstThreshold", "PipeBurstBuffer");');
		
		//Variablenprofile
		//AlertLevel
		if(!IPS_VariableProfileExists("WAA.LeakLevel")) {
			IPS_CreateVariableProfile("WAA.LeakLevel", 1);
			IPS_SetVariableProfileValues("WAA.LeakLevel", 0, 6, 1);
			IPS_SetVariableProfileAssociation("WAA.LeakLevel", 0, $this->Translate("No activity"), "IPS", 0x80FF80);
			IPS_SetVariableProfileAssociation("WAA.LeakLevel", 1, $this->Translate("Everything fine"), "HollowArrowUp", 0x00FF00);
			IPS_SetVariableProfileAssociation("WAA.LeakLevel", 2, $this->Translate("Normal activity"), "HollowDoubleArrowUp", 0x008000);
			IPS_SetVariableProfileAssociation("WAA.LeakLevel", 3, $this->Translate("High activity"), "Lightning", 0xFFFF00);
			IPS_SetVariableProfileAssociation("WAA.LeakLevel", 4, $this->Translate("Abnormal activity"), "Mail", 0xFF8040);
			IPS_SetVariableProfileAssociation("WAA.LeakLevel", 5, $this->Translate("Pre-Alarm"), "Warning", 0xFF0000);
			IPS_SetVariableProfileAssociation("WAA.LeakLevel", 6, $this->Translate("Alarm triggered"), "Alert", 0x800000);
		}
		
		//BorderValue
		if(!IPS_VariableProfileExists("WAA.ThresholdValue")) {
			IPS_CreateVariableProfile("WAA.ThresholdValue", 2);
			IPS_SetVariableProfileIcon("WAA.ThresholdValue",  "Distance");
			IPS_SetVariableProfileDigits("WAA.ThresholdValue", 1);
			IPS_SetVariableProfileValues("WAA.ThresholdValue", 0, 250, 0.5);
		}
		
		$this->RegisterVariableInteger("Leak", $this->Translate("Leak"), "WAA.LeakLevel");
		$leakThresholdVariableID = $this->RegisterVariableFloat("LeakThreshold", $this->Translate("Leak threshold"), "WAA.ThresholdValue");
		$this->EnableAction("LeakThreshold");
		
		//Define some default value
		if(GetValue($leakThresholdVariableID) == 0) {
			SetValue($leakThresholdVariableID, 150);
		}
		
		$this->RegisterVariableBoolean("PipeBurst", $this->Translate("Pipe burst"), "~Alert");
		$this->RegisterVariableFloat("PipeBurstThreshold", $this->Translate("Pipe burst threshold"), "WAA.ThresholdValue");
		$this->EnableAction("PipeBurstThreshold");
		
	}

	public function Destroy(){
		//Never delete this line!
		parent::Destroy();
		
	}

	public function ApplyChanges(){
		//Never delete this line!
		parent::ApplyChanges();
		
		if ($this->ReadPropertyInteger("MeterID") != 0) {
			$MeterValue = GetValue($this->ReadPropertyInteger("MeterID"));
			$this->SetBuffer("LeakBuffer", json_encode($MeterValue));
			$this->SetBuffer("PipeBurstBuffer", json_encode($MeterValue));
			$this->SetTimerInterval("UpdateLeak", $this->ReadPropertyInteger("LeakInterval") * 60 * 1000);
			$this->SetTimerInterval("UpdatePipeBurst", $this->ReadPropertyInteger("PipeBurstInterval") * 60 * 1000);
		}
		
	}

	public function CheckAlert(String $ThresholdName, String $BufferName) {
		
		$MeterValue = GetValue($this->ReadPropertyInteger("MeterID"));
		$ValueOld = json_decode($this->GetBuffer($BufferName));
		
		// if Threshold is exceeded -> Set Alert
		if (($MeterValue - $ValueOld) > GetValueFloat($this->GetIDForIdent($ThresholdName))) {
			if ($ThresholdName == "LeakThreshold") {
				SetValue($this->GetIDForIdent("Leak"), GetValueInteger($this->GetIDForIdent("Leak")) + 1);
				$this->SetBuffer($BufferName, json_encode($MeterValue));
			}
			elseif (GetValueFloat($this->GetIDForIdent($ThresholdName)) != 0) {
				SetValue($this->GetIDForIdent("PipeBurst"), true);
				$this->SetBuffer($BufferName, json_encode($MeterValue));
			}
			
		} 
		// if Threshold is not exceeded -> reset Alert
		else {
			if ($ThresholdName == "LeakThreshold") {
				SetValue($this->GetIDForIdent("Leak"), 0);
				$this->SetBuffer($BufferName, json_encode($MeterValue));
			}
			elseif (GetValueFloat($this->GetIDForIdent($ThresholdName)) != 0) {
				SetValue($this->GetIDForIdent("PipeBurst"), false);
				$this->SetBuffer($BufferName, json_encode($MeterValue));
			}
		}
	}

	public function RequestAction($Ident, $Value) {
		
		switch($Ident) {
			case "LeakThreshold":
			case "PipeBurstThreshold";
				//Neuen Wert in die Statusvariable schreiben
				SetValue($this->GetIDForIdent($Ident), $Value);
				break;
				
			default:
				throw new Exception($this->Translate("Invalid Ident"));
		}
	}

}
?>