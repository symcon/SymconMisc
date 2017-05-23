<?
class BildArchiv extends IPSModule {
	
	public function Create(){
		//Never delete this line!
		parent::Create();
		
		//Properties
		$this->RegisterPropertyInteger("ImageID", 0);
		$this->RegisterPropertyInteger("TriggerVariableID", 0);
		$this->RegisterPropertyInteger("MaxQuantity", 10);
		
	}

	public function Destroy(){
		//Never delete this line!
		parent::Destroy();
		
	}

	public function ApplyChanges(){
		//Never delete this line!
		parent::ApplyChanges();
		
		$this->CreateCategoryByIdent($this->InstanceID, "Images", "Bilderarchiv");
		
		$eID = @IPS_GetObjectIDByIdent("AddImage", $this->InstanceID);
		if ($eID == 0) {
			$eID = IPS_CreateEvent(0);
			IPS_SetParent($eID, $this->InstanceID);
			IPS_SetIdent($eID, "AddImage");
			IPS_SetName($eID, "AddImage");
			IPS_SetHidden($eID, true);
			IPS_SetEventTriggerSubsequentExecution($eID, false);
			IPS_SetEventScript($eID, 'BA_AddImage($_IPS[\'TARGET\']);');
		}
		
		$triggerID = $this->ReadPropertyInteger("TriggerVariableID");
		if ($triggerID != 0){
			IPS_SetEventTrigger($eID, IPS_GetEvent($eID)["TriggerType"], $triggerID);
			IPS_SetEventActive($eID, true);
		} else {
			IPS_SetEventActive($eID, false);
		}
		
	}

	public function AddImage() {
		
		$cID = IPS_GetObjectIDByIdent("Images", $this->InstanceID);
		
		//Erst kontrollieren ob das Maximum an Bildern erreicht ist, wenn ja überschüssige Bilder löschen
		$this->CheckForDeletePicture($cID);
		
		//Neues Bild erstellen und Inhalt kopieren
		$this->CopyNewPicture($cID);
		
	}

	private function CheckForDeletePicture($CID) {
		
		$childIDs = IPS_GetChildrenIDs($CID);
		
		//Sortieren der childIDs nach Position (älteste Bilder nach vorne)
		usort($childIDs, function($a, $b) {
			$a = IPS_GetObject($a);
			$b = IPS_GetObject($b);
			return ($a['ObjectPosition'] < $b['ObjectPosition']) ? -1 : (($a['ObjectPosition'] == $b['ObjectPosition']) ? 0 : 1); 
		});
		
		if (sizeof($childIDs) >= $this->ReadPropertyInteger("MaxQuantity")) {
			//Anzahl Bilder welche gelöscht werden müssen
			$delCount =  sizeof($childIDs) - $this->ReadPropertyInteger("MaxQuantity");
			for ($i = 0; $i <= $delCount; $i++){
				IPS_DeleteMedia($childIDs[$i], true);
			}
		}
		
	}
	
	private function CopyNewPicture($CID) {
		
		$pID = $this->ReadPropertyInteger("ImageID");
		
		$mID = IPS_CreateMedia(1);
		IPS_SetName($mID, date("H:i:s d.m.Y"));
		IPS_SetParent($mID, $CID);
		IPS_SetMediaCached($mID, true);
		IPS_SetMediaFile($mID, "media/".$mID.".".pathinfo(IPS_GetMedia($pID)['MediaFile'])['extension'], False);
		IPS_SetMediaContent($mID, IPS_GetMediaContent($pID));
		IPS_SetPosition($mID, time());
		
	}

	private function CreateCategoryByIdent($ID, $Ident, $Name) {
		 $cID = @IPS_GetObjectIDByIdent($Ident, $ID);
		 if($cID === false) {
			 $cID = IPS_CreateCategory();
			 IPS_SetParent($cID, $ID);
			 IPS_SetName($cID, $Name);
			 IPS_SetIdent($cID, $Ident);
		 }
		 return $cID;
	}

}
?>