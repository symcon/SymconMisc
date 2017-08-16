<?
if (!defined('IPS_BASE')) {
	define("IPS_BASE", 10000);
}
if (!defined('VM_UPDATE')) {
	define("VM_UPDATE", IPS_BASE + 603);
}

	class Alarmierung extends IPSModule {
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();

			$this->RegisterPropertyString("Sensors", "[]");
			$this->RegisterPropertyString("Targets", "[]");

		}

		public function ApplyChanges() {
			//Never delete this line!
			parent::ApplyChanges();
			$this->RegisterVariableBoolean("Active", $this->Translate("Active"), "~Switch", 0 );
			$this->EnableAction("Active");
			$this->RegisterVariableBoolean("Alert", $this->Translate("Alert"), "~Alert", 0);
			$this->EnableAction("Alert");

			$sensors = json_decode($this->ReadPropertyString("Sensors"));
			foreach ($sensors as $sensor) {
				$this->RegisterMessage($sensor->ID, VM_UPDATE);	
			}
			
			
		}

		public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
			
			$this->SendDebug("MessageSink", "SenderID: ". $SenderID .", Message: ". $Message , 0);
			
			$sensors = json_decode($this->ReadPropertyString("Sensors"));
			foreach ($sensors as $sensor) {
				if($sensor->ID == $SenderID) {
					$this->TriggerAlert($sensor->ID, GetValue($sensor->ID));
					return;
				}
			}
			
		}

		public function TriggerAlert(int $SourceID, int $SourceValue) {
			
			//Only enable alarming if our module is active
			if(!GetValue($this->GetIDForIdent("Active"))) {
				return;
			}
			
			switch($this->GetProfileName(IPS_GetVariable($SourceID))) {
				case "~Window.Hoppe":
					if($SourceValue == 0 || $SourceValue == 2) {
						$this->SetAlert(true);
					}
					break;
				case "~Window.HM":
					if($SourceValue == 1 || $SourceValue == 2) {
						$this->SetAlert(true);
					}
					break;
				case "~Lock.Reversed":
				case "~Battery.Reversed":
				case "~Presence.Reversed":
				case "~Window.Reversed":
					if(!$SourceValue) {
						$this->SetAlert(true);
					}
					break;
				default:
					if($SourceValue) {
						$this->SetAlert(true);
					}
					break;
			}

		}
		
		public function SetAlert(bool $Status) {

			$targets = json_decode($this->ReadPropertyString("Targets"));
			
			//Lets notify all target devices
			foreach($targets as $targetID) {
				//only allow links
				if (IPS_VariableExists($targetID->ID)) {
					$o = IPS_GetObject($targetID->ID);
					$v = IPS_GetVariable($targetID->ID);

					$actionID = $this->GetProfileAction($v);
					$profileName = $this->GetProfileName($v);

					//If we somehow do not have a profile take care that we do not fail immediately
					if ($profileName != "") {
						//If we are enabling analog devices we want to switch to the maximum value (e.g. 100%)
						if ($Status) {
							$actionValue = IPS_GetVariableProfile($profileName)['MaxValue'];
						} else {
							$actionValue = 0;
						}
						//Reduce to boolean if required
						if ($v['VariableType'] == 0) {
							$actionValue = $actionValue > 0;
						}
					} else {
						$actionValue = $Status;
					}

					if (IPS_InstanceExists($actionID)) {
						IPS_RequestAction($actionID, $o['ObjectIdent'], $actionValue);
					} else {
						if (IPS_ScriptExists($actionID)) {
							echo IPS_RunScriptWaitEx($actionID, Array("VARIABLE" => $targetID->ID, "VALUE" => $actionValue));
						}
					}
				}
			}
			
			SetValue($this->GetIDForIdent("Alert"), $Status);
		
		}
		
		public function ConvertToNewVersion(){

			if (@IPS_GetObjectIDByIdent("Sensors", $this->InstanceID) !== false) {
				IPS_SetProperty($this->InstanceID, "Sensors", json_encode($this->convertLinksToTargetIDArray("Sensors")));
			}
			
			if (@IPS_GetObjectIDByIdent("Targets", $this->InstanceID) !== false) {
				IPS_SetProperty($this->InstanceID, "Targets", json_encode($this->convertLinksToTargetIDArray("Targets")));
			}
			
			IPS_ApplyChanges($this->InstanceID);

			echo $this->Translate("Converting successful! Please reopen this configuration page. If everything is correct, all events and categories of this instance can be deleted");
			
		}
		
		private function convertLinksToTargetIDArray($CategoryIdent) {

			$linkArray = array();
			$linkIDs = IPS_GetChildrenIDs(@IPS_GetObjectIDByIdent($CategoryIdent, $this->InstanceID));
			foreach ($linkIDs as $linkID) {
				if (IPS_LinkExists($linkID)){
					$linkArray[] = array("ID" => IPS_GetLink($linkID)['TargetID']);
				}
			}
			
			return $linkArray;
			
		}
		

		public function SetActive(bool $Value) {
			
			SetValue($this->GetIDForIdent("Active"), $Value);
			
			if(!$Value) {
				$this->SetAlert(false);
			}
			
		}

		public function RequestAction($Ident, $Value) {
			
			switch($Ident) {
				case "Active":
					$this->SetActive($Value);
					break;
				case "Alert":
					$this->SetAlert($Value);
					break;
				default:
					throw new Exception("Invalid ident");
			}
		
		}

		private function GetProfileName($Variable) {
			
			if($Variable['VariableCustomProfile'] != "")
				return $Variable['VariableCustomProfile'];
			else
				return $Variable['VariableProfile'];
		}

		private function GetProfileAction($Variable) {
			
			if($Variable['VariableCustomAction'] != "")
				return $Variable['VariableCustomAction'];
			else
				return $Variable['VariableAction'];
		}

		private function GetActionForVariable($Variable) {
			
			$v = IPS_GetVariable($Variable);

			if ($v['VariableCustomAction'] > 0) {
				return $v['VariableCustomAction'];
			} else {
				return $v['VariableAction'];
			}
			
		}
		

		private function CreateVariableByIdent($id, $ident, $name, $type, $profile = "") {
			
			 $vid = @IPS_GetObjectIDByIdent($ident, $id);
			 if($vid === false)
			 {
				 $vid = IPS_CreateVariable($type);
				 IPS_SetParent($vid, $id);
				 IPS_SetName($vid, $name);
				 IPS_SetIdent($vid, $ident);
				 if($profile != "")
					IPS_SetVariableCustomProfile($vid, $profile);
			 }
			 return $vid;
		}

		public function GetConfigurationForm() {
			
			if ($this->ReadPropertyString("Sensors") == "[]" && $this->ReadPropertyString("Targets") == "[]") {
				$sensorsID = @IPS_GetObjectIDByIdent("Sensors", $this->InstanceID);
				$targetsID = @IPS_GetObjectIDByIdent("Targets", $this->InstanceID);
				if ($sensorsID !== false || $targetsID !== false) {
					if (IPS_CategoryExists($sensorsID) || IPS_CategoryExists($targetsID)) {
						if (IPS_HasChildren($sensorsID) || IPS_HasChildren($targetsID)) {
							$formconvert = array();
							$formconvert['elements'][] = array("type" => "Button", "label" => $this->Translate("Convert"), "onClick" => "ARM_ConvertToNewVersion(\$id)");
							return json_encode($formconvert);
						}
					}
				}
			}
			
			
			$formdata = json_decode(file_get_contents(__DIR__ . "/form.json"));

			//Annotate existing elements
			$sensors = json_decode($this->ReadPropertyString("Sensors"));
			foreach($sensors as $sensor) {
				//We only need to add annotations. Remaining data is merged from persistance automatically.
				//Order is determinted by the order of array elements
				if(IPS_ObjectExists($sensor->ID) && $sensor->ID !== 0) {
					$status = "OK";
					$rowColor = "";
					if (!IPS_VariableExists($sensor->ID)) {
						$status = $this->Translate("Not a variable");
						$rowColor = "#FFC0C0";
					}				
						
					$formdata->elements[1]->values[] = Array(
						"Name" => IPS_GetName($sensor->ID),
						"Status" => $status,
					);
					
				} else {
					$formdata->elements[1]->values[] = Array(
						"Name" => $this->Translate("Not found!"),
						"rowColor" => "#FFC0C0",
					);
				}
			}

			//Annotate existing elements
			$targets = json_decode($this->ReadPropertyString("Targets"));
			foreach($targets as $target) {
				//We only need to add annotations. Remaining data is merged from persistance automatically.
				//Order is determinted by the order of array elements
				if(IPS_ObjectExists($target->ID) && $target->ID !== 0) {
					$status = "OK";
					$rowColor = "";
					if (!IPS_VariableExists($target->ID)) {
						$status = $this->Translate("Not a variable");
						$rowColor = "#FFC0C0";
					} else if ($this->GetActionForVariable($target->ID) <= 10000){
						$status = $this->Translate("No action set");
						$rowColor = "#FFC0C0";
					}

					$formdata->elements[3]->values[] = Array(
						"Name" => IPS_GetName($target->ID),
						"Status" => $status,
						"rowColor" => $rowColor,
					);
				} else {
					$formdata->elements[3]->values[] = Array(
						"Name" => $this->Translate("Not found!"),
						"rowColor" => "#FFC0C0",
					);
				}
			}

			return json_encode($formdata);
		}
		
	}
?>
