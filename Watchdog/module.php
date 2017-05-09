<?
class Watchdog extends IPSModule
{

	public function Create() {
		//Never delete this line!
		parent::Create();
		
		//Properties
		$this->RegisterPropertyInteger("TimeBase", 0);
		$this->RegisterPropertyInteger("TimeValue", 60);
		$this->RegisterPropertyInteger("CheckTargetsInterval", 60);
		
		//Timer
		$this->RegisterTimer("CheckTargetsTimer", 0, 'WD_CheckTargets($_IPS[\'TARGET\']);');
		
		//Variables
		$this->RegisterVariableInteger("LastCheck", "Letzte Überprüfung", "~UnixTimestamp");
		$this->RegisterVariableString("AlertView", "Aktive Alarme", "~HTMLBox");
		$this->RegisterVariableBoolean("Alert", "Alarm", "~Alert");
		$this->RegisterVariableBoolean("Active", "Watchdog aktiv", "~Switch");
		$this->EnableAction("Active");
		
		$this->CreateCategoryByIdent($this->InstanceID, "Targets", "Targets (Watchdog)");
		
	}

	public function Destroy() {
		//Never delete this line!
		parent::Destroy();
		
	}

	public function ApplyChanges() {
		//Never delete this line!
		parent::ApplyChanges();
		
		if (GetValue($this->GetIDForIdent("Active"))) {
			$this->SetTimerInterval("CheckTargetsTimer", $this->ReadPropertyInteger("CheckTargetsInterval") * 1000);
		}
	}

	public function RequestAction($Ident, $Value) {
		
		switch($Ident) {
			case "Active":
				$this->SetActive($Value);
				break;
			default:
				throw new Exception("Invalid ident");
		}
		
	}

	public function SetActive(bool $SwitchOn){
		
		if ($SwitchOn){
			//When activating the simulation, fetch actual data for a day and activate timer for updating targets
			$this->CheckTargets();
			$this->SetTimerInterval("CheckTargetsTimer", $this->ReadPropertyInteger("CheckTargetsInterval") * 1000);
		} else {
			//When deactivating the simulation, kill data for simulation and deactivate timer for updating targets
			$this->SetTimerInterval("CheckTargetsTimer", 0);
			SetValue($this->GetIDForIdent("AlertView"), "Watchdog deaktiviert");
		}
		
		SetValue($this->GetIDForIdent("Active"), $SwitchOn);
		
	}

	public function CheckTargets() {
		
		$alertTargets = $this->GetAlertTargets();
		SetValue($this->GetIDForIdent("Alert"), sizeof($alertTargets) > 0);
		
		SetValue($this->GetIDForIdent("LastCheck"), time());
		
		$this->UpdateView($alertTargets);
		
	}

	public function GetAlertTargets() {
		
		$targetIDs = $this->GetTargets();
		
		$watchTime = $this->GetWatchTime();
		$watchTimeBorder = time() - $watchTime;
		
		$alertTargets = array();
		
		foreach ($targetIDs as $targetID){
			
			//resolve link to linked targetID
			$linkedTargetID = IPS_GetLink($targetID)['TargetID'];
			
			$v = IPS_GetVariable($linkedTargetID);
			
			if($v['VariableUpdated'] < $watchTimeBorder){
				$alertTargets[] = array('LinkID' => $targetID, 'VariableID' => $linkedTargetID, 'LastUpdate' => $v['VariableUpdated']);
			}
		}
		return $alertTargets;
		
	}

	//Returns all "real" variableID's as array, which are linked in the "Targets" category
	private function GetTargets() {
		
		$targetIDs = IPS_GetChildrenIDs(IPS_GetObjectIDByIdent("Targets", $this->InstanceID));
		
		$result = array();
		foreach($targetIDs as $targetID) {
			//Only allow links
			if (IPS_LinkExists($targetID)) {
				if (IPS_VariableExists(IPS_GetLink($targetID)['TargetID'])) {
					$result[] = $targetID;
				}
			}
		}
		return $result;
	}

	private function GetWatchTime() {
		
		$timeBase = $this->ReadPropertyInteger("TimeBase");
		$timeValue = $this->ReadPropertyInteger("TimeValue");
		
		switch($timeBase) {
			case 0:
				return $timeValue;
				
			case 1:
				return $timeValue * 60;
				
			case 2:
				return $timeValue * 3600;
				
			case 3:
				return $timeValue * 86400;
		}
		
	}

	private function UpdateView($AlertTargets) {
		
		$html = "<table style='width: 100%; border-collapse: collapse;'>";
		$html .= "<tr>";
		$html .= "<td style='padding: 5px; font-weight: bold;'>Aktor</td>";
		$html .= "<td style='padding: 5px; font-weight: bold;'>Letzte Aktualisierung</td>";
		$html .= "<td style='padding: 5px; font-weight: bold;'>Überfällig seit</td>";
		$html .= "</tr>";
		
		foreach ($AlertTargets as $alertTarget) {
			
			$name = IPS_GetName($alertTarget['LinkID']);
			if(IPS_GetName($alertTarget['VariableID']) == $name) {
				$name = IPS_GetName(IPS_GetParent($alertTarget['VariableID']))."\\".IPS_GetName($alertTarget['VariableID']);
			}
			
			$timediff = time() - $alertTarget['LastUpdate'];
			$timestring = sprintf("%02d:%02d:%02d", (int)($timediff / 3600) , (int)($timediff / 60) % 60, ($timediff) % 60);
			
			$html .= "<tr style='border-top: 1px solid rgba(255,255,255,0.10);'>";
			$html .= "<td style='padding: 5px;'>".$name."</td>";
			$html .= "<td style='padding: 5px;'>".date("d.m.Y H:i:s", $alertTarget['LastUpdate'])."</td>";
			$html .= "<td style='padding: 5px;'>".$timestring." Stunden</td>";
			$html .= "</tr>";
		}
		$html .= "</table>";
		
		SetValue($this->GetIDForIdent("AlertView"), $html);
		
	}

	private function CreateCategoryByIdent($id, $ident, $name) {
		$cid = @IPS_GetObjectIDByIdent($ident, $id);
		if($cid === false) {
			$cid = IPS_CreateCategory();
			IPS_SetParent($cid, $id);
			IPS_SetName($cid, $name);
			IPS_SetIdent($cid, $ident);
		}
		return $cid;
	}

}
?>
