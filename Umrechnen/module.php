<?

	class Umrechnen extends IPSModule
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyInteger("SourceVariable", 0);
			$this->RegisterPropertyString("Formula", "\$Value/10*sin(30)*pi()");
			
			$this->RegisterVariableFloat("Value", "Value", "", 0);
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
				IPS_SetEventScript($eid, "SetValue(IPS_GetObjectIDByIdent(\"Value\", \$_IPS['TARGET']), UMR_Calculate(\$_IPS['TARGET'], \$_IPS['VALUE']));");
				IPS_SetEventActive($eid, true);
			}
			
		}
	
		/**
		* This function will be available automatically after the module is imported with the module control.
		* Using the custom prefix this function will be callable from PHP and JSON-RPC through:
		*
		* UMR_Calculate($id);
		*
		*/
		public function Calculate(float $Value)
		{
			
			eval("\$Value = " . $this->ReadPropertyString("Formula") . ";");
			
			return $Value;
		
		}
	
	}

?>
