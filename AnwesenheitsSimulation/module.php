<?
class AnwesenheitsSimulation extends IPSModule
{

	public function Create() {
		//Never delete this line!
		parent::Create();

		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("RequiredSwitchCount", 4);
		$this->RegisterPropertyInteger("ArchiveControlID", IPS_GetInstanceListByModuleID("{43192F0B-135B-4CE7-A0A7-1475603F3060}")[0]);
		$this->RegisterPropertyInteger("Interval", 600);
		
		//Timer
		$this->RegisterTimer("UpdateDataTimer", 0, 'AS_UpdateData($_IPS[\'TARGET\']);');
		$this->RegisterTimer("UpdateTargetsTimer", 0, 'AS_UpdateTargets($_IPS[\'TARGET\']);');
		
		//Variables
		$this->RegisterVariableString("SimulationData", "SimulationData", "");
		IPS_SetHidden(IPS_GetObjectIDByIdent("SimulationData", $this->InstanceID), true);
		$this->RegisterVariableString("SimulationDay", "Simulationsquelle (Tag)", "");
		$this->RegisterVariableBoolean("Active", "Simulation aktiv", "~Switch");
		$this->EnableAction("Active");
		
		$this->CreateCategoryByIdent($this->InstanceID, "Targets", "Targets (Simulation)");

	}

	public function Destroy() {
		//Never delete this line!
		parent::Destroy();

	}

	public function ApplyChanges() {
		//Never delete this line!
		parent::ApplyChanges();
		
	}

	public function RequestAction($Ident, $Value) {
			switch($Ident) {
				case "Active":
					SetValue($this->GetIDForIdent($Ident), $Value);
					if ($Value){
						//When activating the simulation, fetch actual data for a day and activate timer for updating targets
						if($this->UpdateData()) {
							$this->SetTimerInterval("UpdateTargetsTimer", $this->ReadPropertyInteger("Interval")*1000);
						}
						//Set timer for fetching data after midnight at 00:00:01
						$this->SetTimerInterval("UpdateDataTimer", (strtotime("tomorrow") - time() + 1)*1000);
					} else {
						//When deactivating the simulation, kill data for simulation and deactivate timer for updating targets
						SetValue(IPS_GetObjectIDByIdent("SimulationDay", $this->InstanceID), "Simulation deaktiviert");
						SetValue(IPS_GetObjectIDByIdent("SimulationData", $this->InstanceID), "");
						$this->SetTimerInterval("UpdateTargetsTimer", 0);
						$this->SetTimerInterval("UpdateDataTimer", 0);
					}
					break;
				default:
					throw new Exception("Invalid ident");
			}
		
	}

	//Returns all "real" variableID's as array, which are linked in the "Targets" category
	public function GetTargets() {
		
		$targetIDs = IPS_GetChildrenIDs(IPS_GetObjectIDByIdent("Targets", $this->InstanceID));
		
		$result = array();
		foreach($targetIDs as $targetID) {
			//Only allow links
			if (IPS_LinkExists($targetID)) {
				if (IPS_VariableExists(IPS_GetLink($targetID)['TargetID']))
				$result[] = IPS_GetLink($targetID)['TargetID'];
			}
		}
		return $result;
	}

	// 
	public function GetDayData($day, $targetIDs) {
		$dayStart = mktime(0, 0, 0, date("m"), date("d"), date("Y"));
		$dayDiff = $day * 24 * 3600;
		$dayData = array();
		$values = array();
		
		//Going through all linked variables
		foreach($targetIDs as $targetID) {
			if (AC_GetLoggingStatus($this->ReadPropertyInteger("ArchiveControlID"), $targetID)) {
				//Fetch Data for all variables but only one day
				$values = AC_GetLoggedValues($this->ReadPropertyInteger("ArchiveControlID"), $targetID, $dayStart - $dayDiff, $dayStart + (24 * 3600) - $dayDiff - 1, 0);
				if (sizeof($values) > 0){
					
					//Transform UnixTimeStamp into human readable value
					foreach($values as $key => $value){
						$values[$key]['TimeStamp'] = date("H:i:s", $value['TimeStamp']);
					}
					
					$dayData[$targetID] = $values;
				}
			}
		}
		
		// return all values for linked variables for one day in a array
		if (sizeof($values) > 0){
			return array("Date" => date("d.m.Y", $dayStart - $dayDiff), "Data" => $dayData);
		}
		
	}

	//returns a array of all linked variables for 1 day and checks if this meets the needed switchcount
	public function GetDataArray($days, $targetIDs) {
		
		//Get the dayData for all variables
		foreach ($days as $day) {
			$data = $this->GetDayData($day, $targetIDs);
			if (sizeof($data['Data']) > 0) {
				
				//Sum up the switchCount
				$switchCounts = 0;
				foreach ($data['Data'] as $value){
					$switchCounts += sizeof($value);
				}
				
				//Check if the needed switchCount requierement is meet
				if ($switchCounts >= ($this->ReadPropertyInteger("RequiredSwitchCount") * sizeof($targetIDs))){
					return $data;
				}
				
			}
		}
		
		return array();

	}

	//Fetches the needed SimulationData for a whole day
	public function UpdateData() {
		$targetIDs = $this->GetTargets();

		//Tries to fetch data for a random but same weekday for the last 4 weeks
		$weekDays = array(7, 14, 21, 28);
		shuffle($weekDays);

		//If no same weekday possible -> fetch 1 out of the last 30 days (but not the last 4 weeks)
		$singleDays = array_diff(range(1, 30), $weekDays);
		shuffle($singleDays);
		
		$simulationData = $this->GetDataArray(array_merge($weekDays, $singleDays), $targetIDs);
		if(sizeof($simulationData) == 0) {
			SetValue(IPS_GetObjectIDByIdent("SimulationDay", $this->InstanceID), "Zu wenig Daten!");
			return false;
		} else {
			SetValue(IPS_GetObjectIDByIdent("SimulationDay", $this->InstanceID), $simulationData['Date']);
			SetValue(IPS_GetObjectIDByIdent("SimulationData", $this->InstanceID), wddx_serialize_value($simulationData['Data']));
			return true;
		}

	}

	public function UpdateTargets() {
	
		//If simulation is activated
		if (GetValueBoolean(IPS_GetObjectIDByIdent("Active", $this->InstanceID))){
			$simulationData = wddx_deserialize(GetValueString(IPS_GetObjectIDByIdent("SimulationData", $this->InstanceID)));
			
			//Being sure there is simulationData
			if($simulationData != NULL && $simulationData != "") {
				//Going through all variableID's of the simulationData
				foreach($simulationData as $id => $value) {
					if (IPS_VariableExists($id)) {
						$varValue = null;

						//Getting the value to set
						foreach ($value as $key) {
							if (date("H:i:s") > $key["TimeStamp"]) {
								$varValue = $key["Value"];
								break;
							}
						}
						
						$v = IPS_GetVariable($id);
						//Set variableValue, if there is a varValue and its not the same as already set
						if ($varValue != null && $varValue != $v["VariableValue"]) {
							$o = IPS_GetObject($id);
							if($v['VariableCustomAction'] != "") {
								$actionID = $v['VariableCustomAction'];
							} else {
								$actionID = $v['VariableAction'];
							}
							
							if(IPS_InstanceExists($actionID)) {
								IPS_RequestAction($actionID, $o['ObjectIdent'], $varValue);
							} else if(IPS_ScriptExists($actionID)) {
								echo IPS_RunScriptWaitEx($actionID, Array("VARIABLE" => $id, "VALUE" => $varValue));
							}
						}
					}
				}
			} else {
				echo "No valid SimulationData";
			}
		}
	
	}

	private function CreateCategoryByIdent($id, $ident, $name) {
		 $cid = @IPS_GetObjectIDByIdent($ident, $id);
		 if($cid === false)
		 {
			 $cid = IPS_CreateCategory();
			 IPS_SetParent($cid, $id);
			 IPS_SetName($cid, $name);
			 IPS_SetIdent($cid, $ident);
		 }
		 return $cid;
	}

}
?>