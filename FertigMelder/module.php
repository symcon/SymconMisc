<?
class FertigMelder extends IPSModule {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("SourceID", 0);
		$this->RegisterPropertyInteger("Period", 15);
		$this->RegisterPropertyFloat("BorderValue", 0);
		
		//Timer
		$this->RegisterTimer("CheckIfDoneTimer", 0, 'FM_CheckEvent($_IPS[\'TARGET\'], "Done");');
		
		if(!IPS_VariableProfileExists("FM.Status")) {
			IPS_CreateVariableProfile("FM.Status", 1);
			IPS_SetVariableProfileValues("FM.Status", 0, 2, 1);
			IPS_SetVariableProfileAssociation("FM.Status", 0, "Off", "Sleep", -1);
			IPS_SetVariableProfileAssociation("FM.Status", 1, "Running", "Motion", -1);
			IPS_SetVariableProfileAssociation("FM.Status", 2, "Done", "Ok", -1);
		}
		
		$this->RegisterVariableInteger("Status", "Status", "FM.Status");
		$this->RegisterVariableBoolean("Active", "Active", "~Switch");
		$this->EnableAction("Active");
		
	}

	public function Destroy(){
		//Never delete this line!
		parent::Destroy();
		
	}

	public function ApplyChanges(){
		//Never delete this line!
		parent::ApplyChanges();
		
		$sourceID = $this->ReadPropertyInteger("SourceID");
		
		$eid = @IPS_GetObjectIDByIdent("EventUp", $this->InstanceID);
		if ($eid == 0) {
			$eid = IPS_CreateEvent(0);
			IPS_SetParent($eid, $this->InstanceID);
			IPS_SetIdent($eid, "EventUp");
			IPS_SetName($eid, "EventUp");
			IPS_SetHidden($eid, true);
			IPS_SetEventTriggerSubsequentExecution($eid, false);
			IPS_SetEventScript($eid, 'FM_CheckEvent($_IPS[\'TARGET\'], "Up");');
		}
		if ($sourceID != 0){
			IPS_SetEventTrigger($eid, 2, $sourceID); // Grenzwertunterschreitung
			IPS_SetEventTriggerValue ($eid, $this->ReadPropertyFloat("BorderValue"));
			IPS_SetEventActive($eid, false);
		}
		
		$eid = @IPS_GetObjectIDByIdent("EventDown", $this->InstanceID);
		if ($eid == 0) {
			$eid = IPS_CreateEvent(0);
			IPS_SetParent($eid, $this->InstanceID);
			IPS_SetIdent($eid, "EventDown");
			IPS_SetName($eid, "EventDown");
			IPS_SetHidden($eid, true);
			IPS_SetEventTriggerSubsequentExecution($eid, false);
			IPS_SetEventScript($eid, 'FM_CheckEvent($_IPS[\'TARGET\'], "Down");');
		}
		if ($sourceID != 0){
			IPS_SetEventTrigger($eid, 3, $sourceID); // Grenzwertunterschreitung
			IPS_SetEventTriggerValue ($eid, $this->ReadPropertyFloat("BorderValue"));
			IPS_SetEventActive($eid, false);
		}
		
		$this->SetActive(GetValue($this->GetIDForIdent("Active")));
		
	}

	public function SetActive($Active){
		
		if ($this->ReadPropertyInteger("SourceID") != 0) {
			IPS_SetEventActive(@IPS_GetObjectIDByIdent("EventUp", $this->InstanceID), $Active);
			IPS_SetEventActive(@IPS_GetObjectIDByIdent("EventDown", $this->InstanceID), $Active);
			
			if ($Active) {
				if (GetValue($this->ReadPropertyInteger("SourceID")) >= $this->ReadPropertyFloat("BorderValue")) {
					SetValue($this->GetIDForIdent("Status"), 1);
				} else {
					SetValue($this->GetIDForIdent("Status"), 0);
				}
			} else {
				SetValue($this->GetIDForIdent("Status"), 0);
			}
		} else {
			echo "Quellvariable nicht ausgewählt";
		}
	}

	public function CheckEvent($Eventtype){
		
		switch ($Eventtype){
			case "Up":
				$this->SetTimerInterval("CheckIfDoneTimer", 0);
				SetValue($this->GetIDForIdent("Status"), 1);
				break;
			
			case "Down":
				$this->SetTimerInterval("CheckIfDoneTimer", $this->ReadPropertyInteger("Period") * 1000);
				break;
				
			case "Done":
				$this->SetTimerInterval("CheckIfDoneTimer", 0);
				SetValue($this->GetIDForIdent("Status"), 2);
				break;
		}
		
	}

	public function RequestAction($Ident, $Value) {
		
		switch($Ident) {
			case "Active":
				//Neuen Wert in die Statusvariable schreiben
				$this->SetActive($Value);
				SetValue($this->GetIDForIdent($Ident), $Value);
				break;
			
			default:
				throw new Exception("Invalid Ident");
		}
	}

}
?>