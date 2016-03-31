<?

	class Alarmierung extends IPSModule
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
		}
	
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			
			$this->CreateCategoryByIdent($this->InstanceID, "Sensors", "Sensors");
			$this->CreateCategoryByIdent($this->InstanceID, "Targets", "Alert Target");
			
			$this->CreateVariableByIdent($this->InstanceID, "Active", "Active", 0, "~Switch");
			$this->EnableAction("Active");
			$this->CreateVariableByIdent($this->InstanceID, "Alert", "Alert", 0, "~Alert");
			$this->EnableAction("Alert");
			
		}
	
		/**
		* This function will be available automatically after the module is imported with the module control.
		* Using the custom prefix this function will be callable from PHP and JSON-RPC through:
		*
		* ARM_UpdateEvents($id);
		*
		*/
		public function UpdateEvents()
		{

			$sensorsID = $this->CreateCategoryByIdent($this->InstanceID, "Sensors", "Sensors");
			
			//We want to listen for all changes on all sensorsID
			foreach(IPS_GetChildrenIDs($sensorsID) as $sensorID) {
				//only allow links
				if(IPS_LinkExists($sensorID)) {
					if(@IPS_GetObjectIDByIdent("Sensor".$sensorID, $this->InstanceID) == 0) {
						$linkVariableID = IPS_GetLink($sensorID)['TargetID'];
						if(IPS_VariableExists($linkVariableID)) {
							$eid = IPS_CreateEvent(0 /* Trigger */);
							IPS_SetParent($eid, $this->InstanceID);
							IPS_SetName($eid, "Trigger for #".$linkVariableID);
							IPS_SetEventTrigger($eid, 0, $linkVariableID);
							IPS_SetEventScript($eid, "ARM_TriggerAlert(\$_IPS['TARGET'], \$_IPS['VARIABLE'], \$_IPS['VALUE']);");
							IPS_SetEventActive($eid, true);
						}
					}
				}
			}

		}
		
		public function TriggerAlert($sourceID, $sourceValue) {
			
			//Only enable alarming if our module is active
			if(!GetValue($this->GetIDForIdent("Active"))) {
				return;
			}
			
			switch($this->GetProfileName(IPS_GetVariable($sourceID))) {
				case "~Window.Hoppe":
					if($sourceValue == 0 || $sourceValue == 2) {
						$this->SetAlert(true);
					}
					break;
				case "~Window.HM":
					if($sourceValue == 1 || $sourceValue == 2) {
						$this->SetAlert(true);
					}
					break;
				case "~Lock.Reversed":
				case "~Battery.Reversed":
				case "~Presence.Reversed":
				case "~Window.Reversed":
					if(!$sourceValue) {
						$this->SetAlert(true);
					}
					break;
				default:
					if($sourceValue) {
						$this->SetAlert(true);
					}
					break;
			}

		}
		
		public function SetAlert($status)
		{
		
			$targetsID = $this->CreateCategoryByIdent($this->InstanceID, "Targets", "Alert Target");
			
			//Lets notify all target devices
			foreach(IPS_GetChildrenIDs($targetsID) as $targetID) {
				//only allow links
				if (IPS_LinkExists($targetID)) {
					$linkVariableID = IPS_GetLink($targetID)['TargetID'];
					if (IPS_VariableExists($linkVariableID)) {
						$o = IPS_GetObject($linkVariableID);
						$v = IPS_GetVariable($linkVariableID);

						$actionID = $this->GetProfileAction($v);
						$profileName = $this->GetProfileName($v);

						//If we somehow do not have a profile take care that we do not fail immediately
						if($profileName != "") {
							//If we are enabling analog devices we want to switch to the maximum value (e.g. 100%)
							if ($status) {
								$actionValue = IPS_GetVariableProfile($profileName)['MaxValue'];
							} else {
								$actionValue = 0;
							}
							//Reduce to boolean if required
							if($v['VariableType'] == 0) {
								$actionValue = $actionValue > 0;
							}
						} else {
							$actionValue = $status;
						}

						if(IPS_InstanceExists($actionID)) {
							IPS_RequestAction($actionID, $o['ObjectIdent'], $actionValue);
						} else if(IPS_ScriptExists($actionID)) {
							echo IPS_RunScriptWaitEx($actionID, Array("VARIABLE" => $linkVariableID, "VALUE" => $actionValue));
						}
					}
				}
			}
			
			SetValue($this->GetIDForIdent("Alert"), $status);
		
		}
		
		public function RequestAction($Ident, $Value)
		{
			
			switch($Ident) {
				case "Active":
					SetValue($this->GetIDForIdent($Ident), $Value);
					break;
				case "Alert":
					$this->SetAlert($Value);
					break;
				default:
					throw new Exception("Invalid ident");
			}
		
		}

		private function GetProfileName($variable) {
			if($variable['VariableCustomProfile'] != "")
				return $variable['VariableCustomProfile'];
			else
				return $variable['VariableProfile'];
		}

		private function GetProfileAction($variable) {
			if($variable['VariableCustomAction'] != "")
				return $variable['VariableCustomAction'];
			else
				return $variable['VariableAction'];
		}
		
		private function CreateCategoryByIdent($id, $ident, $name)
		 {
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
		
		private function CreateVariableByIdent($id, $ident, $name, $type, $profile = "")
		 {
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
	
	}

?>
