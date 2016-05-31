<?
class SzenenSteuerung extends IPSModule {

	public function Create() {
		//Never delete this line!
		parent::Create();

		//These lines are parsed on Symcon Startup or Instance creation
		//You cannot use variables here. Just static values.
		$this->RegisterPropertyInteger("SceneCount", 3);
		
		if(!IPS_VariableProfileExists("SZS.SceneControl")){
			IPS_CreateVariableProfile("SZS.SceneControl", 1);
			IPS_SetVariableProfileValues("SZS.SceneControl", 1, 2, 0);
			//IPS_SetVariableProfileIcon("SZS.SceneControl", "");
			IPS_SetVariableProfileAssociation("SZS.SceneControl", 1, "Speichern", "", -1);
			IPS_SetVariableProfileAssociation("SZS.SceneControl", 2, "Ausführen", "", -1);
		}

	}

	public function Destroy() {
		//Never delete this line!
		parent::Destroy();
	}

	public function ApplyChanges() {
		//Never delete this line!
		parent::ApplyChanges();
		
		$this->CreateCategoryByIdent($this->InstanceID, "Targets", "Targets");
		
		for($i = 1; $i <= $this->ReadPropertyInteger("SceneCount"); $i++) {
			if(@IPS_GetObjectIDByIdent("Scene".$i, $this->InstanceID) == 0){
				//Scene
				$vid = IPS_CreateVariable(1 /* Scene */);
				IPS_SetParent($vid, $this->InstanceID);
				IPS_SetName($vid, "Scene".$i);
				IPS_SetIdent($vid, "Scene".$i);
				IPS_SetVariableCustomProfile($vid, "SZS.SceneControl");
				$this->EnableAction("Scene".$i);
				SetValue($vid, 1);
				//SceneData
				$vid = IPS_CreateVariable(3 /* SceneData */);
				IPS_SetParent($vid, $this->InstanceID);
				IPS_SetName($vid, "Scene".$i."Data");
				IPS_SetIdent($vid, "Scene".$i."Data");
				IPS_SetHidden($vid, true);
				
			}
		}
		//Delete excessive Scences 
		$ChildrenIDsCount = sizeof(IPS_GetChildrenIDs($this->InstanceID))/2;
		if($ChildrenIDsCount > $this->ReadPropertyInteger("SceneCount")) {
			for($j = $this->ReadPropertyInteger("SceneCount")+1; $j <= $ChildrenIDsCount; $j++) {
				IPS_DeleteVariable(IPS_GetObjectIDByIdent("Scene".$j, $this->InstanceID));
				IPS_DeleteVariable(IPS_GetObjectIDByIdent("Scene".$j."Data", $this->InstanceID));
			}
		}
	}
	
	public function RequestAction($Ident, $Value) {
		
		switch($Value) {
			case "1":
				$this->SaveValues($Ident);
				break;
			case "2":
				$this->LoadValues($Ident);
				break;
			default:
				throw new Exception("Invalid action");
		}
	}
	
	public function SaveValues($Scene) {
		
		$targetIDs = IPS_GetObjectIDByIdent("Targets", $this->InstanceID);
		$data = Array();
		
		//We want to save all Lamp Values
		foreach(IPS_GetChildrenIDs($targetIDs) as $TargetID) {
			//only allow links
			if(IPS_LinkExists($TargetID)) {
				$linkVariableID = IPS_GetLink($TargetID)['TargetID'];
				if(IPS_VariableExists($linkVariableID)) {
					$data[$linkVariableID] = GetValue($linkVariableID);
				}
			}
		}
		SetValue(IPS_GetObjectIDByIdent($Scene."Data", $this->InstanceID), wddx_serialize_value($data));
	}
	
	public function LoadValues($Scene) {
		
		$data = wddx_deserialize(GetValue(IPS_GetObjectIDByIdent($Scene."Data", $this->InstanceID)));
		
		if($data != NULL) {
			foreach($data as $id => $value) {
				if (IPS_VariableExists($id)){
					$o = IPS_GetObject($id);
					$v = IPS_GetVariable($id);

					if($v['VariableCustomAction'] != "")
						$actionID = $v['VariableCustomAction'];
					else
						$actionID = $v['VariableAction'];
					
					if(IPS_InstanceExists($actionID)) {
						IPS_RequestAction($actionID, $o['ObjectIdent'], $value);
					} else if(IPS_ScriptExists($actionID)) {
						echo IPS_RunScriptWaitEx($actionID, Array("VARIABLE" => $id, "VALUE" => $value));
					}
				}
			}
		} else {
			echo "No SceneData for this Scene";
		}
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