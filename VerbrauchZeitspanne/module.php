<?

	class VerbrauchZeitspanne extends IPSModule
	{
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyInteger("SourceVariable", 0);

		}
	
		public function ApplyChanges()
		{
			
			//Never delete this line!
			parent::ApplyChanges();

			//Create variables
			$this->RegisterVariableInteger("StartDate", "Start-Datum", "~UnixTimestampDate", 1);
			$this->EnableAction("StartDate");

            if (GetValue($this->GetIDForIdent("StartDate")) == 0) {
                SetValue($this->GetIDForIdent("StartDate"), time());
            }

			$this->RegisterVariableInteger("EndDate", "End-Datum", "~UnixTimestampDate", 2);
			$this->EnableAction("EndDate");

            if (GetValue($this->GetIDForIdent("EndDate")) == 0) {
                SetValue($this->GetIDForIdent("EndDate"), time());
            }
            
            $sourceVariable = $this->ReadPropertyInteger("SourceVariable");
            if($sourceVariable > 0 && IPS_VariableExists($sourceVariable)) {

                $v = IPS_GetVariable($sourceVariable);

                $sourceProfile = "";
                if (IPS_VariableExists($sourceVariable)) {
                    $sourceProfile = $v['VariableCustomProfile'];
                    if ($sourceProfile == "") {
                        $sourceProfile = $v['VariableProfile'];
                    }
                }

                switch ($v['VariableType']) {
                    case 1: /* Integer */
                        $this->RegisterVariableInteger("Usage", "Verbrauch", $sourceProfile, 3);
                        break;
                    case 2: /* Float */
                        $this->RegisterVariableFloat("Usage", "Verbrauch", $sourceProfile, 3);
                        break;
                    default:
                        return;
                }

            }

		}

        public function RequestAction($Ident, $Value) {

            switch($Ident) {
                case "StartDate":
                case "EndDate":
                    //Neuen Wert in die Statusvariable schreiben
                    SetValue($this->GetIDForIdent($Ident), $Value);

                    //Berechnen
					$this->Calculate();
                    break;
                default:
                    throw new Exception("Invalid Ident");
            }

        }

		/**
		* This function will be available automatically after the module is imported with the module control.
		* Using the custom prefix this function will be callable from PHP and JSON-RPC through:
		*
		* VIZ_Calculate($id);
		*
		*/
		public function Calculate()
		{

			$acID = IPS_GetInstanceListByModuleID("{43192F0B-135B-4CE7-A0A7-1475603F3060}")[0];
			$variableID = $this->ReadPropertyInteger("SourceVariable");
			$startDate = GetValue($this->GetIDForIdent("StartDate"));
            $endDate = GetValue($this->GetIDForIdent("EndDate"));

			$sum = 0;
			$values = AC_GetAggregatedValues($acID, $variableID, 1 /* Day */, $startDate, $endDate + (24*3600) - 1, 0);

			if($values === false) {
				return;
			}

			foreach($values as $value) {
				$sum += $value['Avg'];
			}

			SetValue($this->GetIDForIdent("Usage"), $sum);

		}
	
	}

?>
