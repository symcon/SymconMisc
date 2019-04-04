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

            //Falls der "erste Wert" am Tag gesucht wird, betrachte erstmal nur den gewählten Tag.
            //Falls dort keine Werte vorhanden sind sind nutzen wir die Funktionsweise von "letzter Wert" am Tag, der dann auch den Wert von Vortagen ausgibt.
            if($this->ReadPropertyInteger("ValueType") == 0) {
                $values = AC_GetLoggedValues($acID, $variableID, $date, $date + (24 * 3600) - 1, 0);
                if($values === false) {
                    return;
                }
            }
            
            //Der letzte Wert am Tag fragt alle Werte bis zum Endzeitpunkt ab mit Limit 1.
            //Da AC_GetLoggedValues immer den neusten Wert zuerst ausgibt ist es genau der Wert den wir suchen
            if($this->ReadPropertyInteger("ValueType") == 1 || (isset($values) && sizeof($values) == 0)) {
                $values = AC_GetLoggedValues($acID, $variableID, 0, $date + (24 * 3600) - 1, 1);
                if($values === false) {
                    return;
                }
            }

            if(!isset($values) || sizeof($values) == 0) {
            	echo "Leider wurden für den gewählten Zeitraum keine Werte im Archiv gespeichert!";
            	return;
            }

            //Immer den letzten Wert im Array ausgeben
            SetValue($this->GetIDForIdent("Reading"), $values[sizeof($values)-1]['Value']);

        }
	
	}

?>
