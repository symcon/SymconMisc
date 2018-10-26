<?

	class ZaehlerstandTag extends IPSModule
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyInteger("SourceVariable", 0);
			$this->RegisterPropertyInteger("ValueType", 0);
			
		}

        public function ApplyChanges()
        {

            //Never delete this line!
            parent::ApplyChanges();

            //Create variables
            $this->RegisterVariableInteger("Date", "Datum", "~UnixTimestampDate", 1);
            $this->EnableAction("Date");

            if (GetValue($this->GetIDForIdent("Date")) == 0) {
                SetValue($this->GetIDForIdent("Date"), time());
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
                        $this->RegisterVariableInteger("Reading", "Zählerstand", $sourceProfile, 3);
                        break;
                    case 2: /* Float */
                        $this->RegisterVariableFloat("Reading", "Zählerstand", $sourceProfile, 3);
                        break;
                    default:
                        return;
                }

            }

        }

        public function RequestAction($Ident, $Value) {

            switch($Ident) {
                case "Date":
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
            $date = GetValue($this->GetIDForIdent("Date"));

            $values = AC_GetLoggedValues($acID, $variableID, $date, $date + (24*3600) - 1, 0);

            if($values === false) {
                return;
            }

            if(sizeof($values) == 0) {
            	echo "Leider ist kein Wert für diesen Tag verfügbar";
            	return;
            }
            
            if($this->ReadPropertyInteger("ValueType") == 0) {
                SetValue($this->GetIDForIdent("Reading"), $values[0]['Value']);
            } else {
                SetValue($this->GetIDForIdent("Reading"), $values[sizeof($values)-1]['Value']);
            }

        }
	
	}

?>
