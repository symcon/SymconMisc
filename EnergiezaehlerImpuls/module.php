<?

	class EnergiezaehlerImpuls extends IPSModule
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyInteger("SourceVariable", 0);
			$this->RegisterPropertyInteger("Interval", 300);
			$this->RegisterPropertyInteger("Impulses", 1000);
			
			$this->RegisterTimer("UpdateTimer", 0, 'EZI_Update($_IPS[\'TARGET\']);');
			
			$this->RegisterVariableFloat("Current", "Current", "Watt.3680", 0);
			$this->RegisterVariableFloat("Counter", "Counter", "Electricity", 1);
			$this->RegisterVariableFloat("LastSourceValue", "Last Value (Temporary)", "", 2);
		}
	
		public function ApplyChanges()
		{
			
			//Never delete this line!
			parent::ApplyChanges();
			
			//Reset source value
			if(IPS_VariableExists($this->ReadPropertyInteger("SourceVariable"))) {
				SetValue($this->GetIDForIdent("LastSourceValue"), GetValue($this->ReadPropertyInteger("SourceVariable")));
			}
			
			$this->SetTimerInterval("UpdateTimer", $this->ReadPropertyInteger("Interval")*1000);
			
			//Always hide this variable
			IPS_SetHidden($this->GetIDForIdent("LastSourceValue"), true);
			
		}
	
		/**
		* This function will be available automatically after the module is imported with the module control.
		* Using the custom prefix this function will be callable from PHP and JSON-RPC through:
		*
		* EZI_Update($id);
		*
		*/
		public function Update()
		{
			if(IPS_VariableExists($this->ReadPropertyInteger("SourceVariable"))) {
				
				$value = GetValue($this->ReadPropertyInteger("SourceVariable"));
				
				//we only count positive deltas
				$diff = max(0, $value - GetValue($this->GetIDForIdent("LastSourceValue")));
				
				//add to our counter
				SetValue($this->GetIDForIdent("Counter"), GetValue($this->GetIDForIdent("Counter"))+($diff/$this->ReadPropertyInteger("Impulses")));
				
				//calculate consumption
				if($diff == 0) {
					SetValue($this->GetIDForIdent("Current"), 0);
				} else {
					SetValue($this->GetIDForIdent("Current"), ($diff/$this->ReadPropertyInteger("Interval"))*3600/($this->ReadPropertyInteger("Impulses")/1000));
				}
				
				//update last source value
				SetValue($this->GetIDForIdent("LastSourceValue"), $value);
			}
		
		}
	
	}

?>
