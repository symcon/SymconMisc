<?
class WasserAlarm extends IPSModule {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("CounterID", 0);
		$this->RegisterPropertyInteger("Interval", 15);
		
		//Timer
		$this->RegisterTimer("UpdateCounter", 0, 'WAA_CheckAlert($_IPS[\'TARGET\'], "CountAlertBorder", "CounterValueOld");');
		$this->RegisterTimer("UpdateFlow", 0, 'WAA_CheckAlert($_IPS[\'TARGET\'], "FlowAlertBorder", "FlowValueOld");');
		
		//Variablenprofile
		//AlertLevel
		if(!IPS_VariableProfileExists("WAA.AlertLevel")){
			IPS_CreateVariableProfile("WAA.AlertLevel", 1);
			IPS_SetVariableProfileValues("WAA.AlertLevel", 0, 6, 1);
			IPS_SetVariableProfileAssociation("WAA.AlertLevel", 0, $this->Translate("No activity"), "IPS", 0x80FF80);
			IPS_SetVariableProfileAssociation("WAA.AlertLevel", 1, $this->Translate("Everything fine"), "HollowArrowUp", 0x00FF00);
			IPS_SetVariableProfileAssociation("WAA.AlertLevel", 2, $this->Translate("Normal activity"), "HollowDoubleArrowUp", 0x008000);
			IPS_SetVariableProfileAssociation("WAA.AlertLevel", 3, $this->Translate("High activity"), "Lightning", 0xFFFF00);
			IPS_SetVariableProfileAssociation("WAA.AlertLevel", 4, $this->Translate("Abnormal activity"), "Mail", 0xFF8040);
			IPS_SetVariableProfileAssociation("WAA.AlertLevel", 5, $this->Translate("Pre-Alarm"), "Warning", 0xFF0000);
			IPS_SetVariableProfileAssociation("WAA.AlertLevel", 6, $this->Translate("Alarm triggered"), "Alert", 0x800000);
		}
		
		//BorderValue
		if(!IPS_VariableProfileExists("WAA.BorderValue")){
			IPS_CreateVariableProfile("WAA.BorderValue", 2);
			IPS_SetVariableProfileDigits("WAA.BorderValue", 3);
			IPS_SetVariableProfileValues("WAA.BorderValue", 0, 250, 0.5);
		}
		
		$this->RegisterVariableInteger("CounterAlertStatus", $this->Translate("Alertlevel Counter (every minute)"), "WAA.AlertStates");
		$this->RegisterVariableFloat("CounterValueOld", $this->Translate("Old countervalue"), "");
		$this->RegisterVariableFloat("CountAlertBorder", $this->Translate("Bordlervalue counteralert"), "WAA.BorderValue");
		$this->EnableAction("CountAlertBorder");
		
		$this->RegisterVariableBoolean("FlowAlertStatus", $this->Translate("Alertstatus flow"), "~Alert");
		$this->RegisterVariableFloat("FlowValueOld", $this->Translate("Old flowvalue"), "");
		$this->RegisterVariableFloat("FlowAlertBorder", $this->Translate("Bordervalue flow"), "WAA.BorderValue");
		$this->EnableAction("FlowAlertBorder");
		
	}

	public function Destroy(){
		//Never delete this line!
		parent::Destroy();
		
	}

	public function ApplyChanges(){
		//Never delete this line!
		parent::ApplyChanges();
		
		if ($this->ReadPropertyInteger("CounterID") != 0){
			$CounterValue = GetValue($this->ReadPropertyInteger("CounterID"));
			SetValue($this->GetIDForIdent("CounterValueOld"), $CounterValue);
			SetValue($this->GetIDForIdent("FlowValueOld"), $CounterValue);
		}
		
		$this->SetTimerInterval("UpdateCounter", 60*1000);
		$this->SetTimerInterval("UpdateFlow", $this->ReadPropertyInteger("Interval")*60*1000);
	}

	public function CheckAlert(String $BorderValue, String $OldValue){
		
		$CounterValue = GetValue($this->ReadPropertyInteger("CounterID"));
		$ValueOld = GetValueFloat($this->GetIDForIdent($OldValue));
		
		if (($CounterValue - $ValueOld) > GetValueFloat($this->GetIDForIdent($BorderValue))) {
			if ($BorderValue == "CountAlertBorder") {
				SetValue($this->GetIDForIdent("CounterAlertStatus"), GetValueInteger($this->GetIDForIdent("CounterAlertStatus")) + 1);
				SetValue($this->GetIDForIdent($OldValue), $CounterValue);
			} elseif (GetValueFloat($this->GetIDForIdent($BorderValue)) != 0) {
				//Kontrolle ob es die erste Abfrage nach Systemneustart ist. Wenn ja, den Wert ignorieren.
				if (time() > (IPS_GetKernelStartTime() + ($this->ReadPropertyInteger("Interval") * 60) + 30)) {
					SetValue($this->GetIDForIdent("FlowAlertStatus"), true);
				}
				SetValue($this->GetIDForIdent($OldValue), $CounterValue);
			}
			
		} else {
			if ($BorderValue == "CountAlertBorder") {
				SetValue($this->GetIDForIdent("CounterAlertStatus"), 0);
				SetValue($this->GetIDForIdent($OldValue), $CounterValue);
			} elseif (GetValueFloat($this->GetIDForIdent($BorderValue)) != 0) {
				SetValue($this->GetIDForIdent("FlowAlertStatus"), false);
				SetValue($this->GetIDForIdent($OldValue), $CounterValue);
			}
		}
	}

	public function RequestAction($Ident, $Value) {
		
		switch($Ident) {
			case "CountAlertBorder":
			case "FlowAlertBorder";
				//Neuen Wert in die Statusvariable schreiben
				SetValue($this->GetIDForIdent($Ident), $Value);
				break;
			default:
				throw new Exception($this->Translate("Invalid Ident"));
		}
	}

}
?>