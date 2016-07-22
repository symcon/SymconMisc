<?

	class ZaehlerUeberlauf extends IPSModule
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyInteger("SourceVariable", 0);
			$this->RegisterPropertyInteger("MaximumValue", 999999);
			
			$this->RegisterVariableFloat("Counter", "Counter", "", 1);
		}
	
		public function ApplyChanges()
		{
			
			//Never delete this line!
			parent::ApplyChanges();
			
			//Create our trigger
			if(IPS_VariableExists($this->ReadPropertyInteger("SourceVariable"))) {
				$eid = @IPS_GetObjectIDByIdent("SourceTrigger", $this->InstanceID);
				if($eid === false) {
					$eid = IPS_CreateEvent(0 /* Trigger */);
					IPS_SetParent($eid, $this->InstanceID);
					IPS_SetIdent($eid, "SourceTrigger");
					IPS_SetName($eid, "Trigger for #".$this->ReadPropertyInteger("SourceVariable"));
				}
				IPS_SetEventTrigger($eid, 0, $this->ReadPropertyInteger("SourceVariable"));
				IPS_SetEventScript($eid, "ZUL_Update(\$_IPS['TARGET'], \$_IPS['OLDVALUE'], \$_IPS['VALUE']);");
				IPS_SetEventActive($eid, true);
			}
			
		}
	
		/**
		* This function will be available automatically after the module is imported with the module control.
		* Using the custom prefix this function will be callable from PHP and JSON-RPC through:
		*
		* ZUL_Update($id);
		*
		*/
		public function Update(int $OldValue, int $Value)
		{
			
			if (($Value - $OldValue) < 0) {
				$diff = $this->ReadPropertyInteger("MaximumValue") + 1 - $OldValue + $Value;
			} else {
				$diff = $Value - $OldValue;
			}
			
			//update value
			SetValue($this->GetIDForIdent("Counter"), GetValue($this->GetIDForIdent("Counter")) + $diff);
			
		
		}
	
	}

?>
