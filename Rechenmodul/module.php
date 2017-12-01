<?
class Rechenmodul extends IPSModule {
    
    public function Create(){
        //Never delete this line!
        parent::Create();
        
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyInteger("Calculation", 0);
        $this->RegisterPropertyString("Variables", "[]");
        
    }

    public function Destroy(){
        //Never delete this line!
        parent::Destroy();
        
    }

    public function ApplyChanges(){
        //Never delete this line!
        parent::ApplyChanges();
        
        switch ($this->ReadPropertyInteger("Calculation")) {
            case 0: // Everything
                $this->RegisterVariableFloat("Sum", $this->Translate("Sum"));
                $this->RegisterVariableFloat("Minimum", $this->Translate("Minimum"));
                $this->RegisterVariableFloat("Maximum", $this->Translate("Maximum"));
                $this->RegisterVariableFloat("Average", $this->Translate("Average"));
                $this->RegisterVariableFloat("Count", $this->Translate("Count"));
                break;
            
            case 1: // Sum
                $this->RegisterVariableFloat("Sum", $this->Translate("Sum"));
                break;
            
            case 2: // Minimum
                $this->RegisterVariableFloat("Minimum", $this->Translate("Minimum"));
                break;
            
            case 3: // Maximum
                $this->RegisterVariableFloat("Maximum", $this->Translate("Maximum"));
                break;
            
            case 4: // Average
                $this->RegisterVariableFloat("Average", $this->Translate("Average"));
                break;
            
            case 5: // Count
                $this->RegisterVariableFloat("Count", $this->Translate("Count"));
                break;
        }
        
        // Delete all events
        $childrenIDs = IPS_GetChildrenIDs($this->InstanceID);
        
        foreach ($childrenIDs as $id) {
            if (IPS_GetObject($id)["ObjectType"] == 4) {
                IPS_DeleteEvent($id);
            }
        }
        
        // Create new elements for all variables
        $variables = json_decode($this->ReadPropertyString("Variables"));
        
        foreach($variables as $variable) {
            $variableID = intval($variable->ID);
            if (IPS_GetObject($variableID)["ObjectType"] == 2) {
                $eventID = IPS_CreateEvent(0);
                IPS_SetParent($eventID, $this->InstanceID);
                IPS_SetEventTrigger($eventID, 1, $variableID);
                IPS_SetEventScript($eventID, "RM_Update(\$_IPS['TARGET']);");
                IPS_SetEventActive($eventID, true);
            }
        }
        
        $this->Update();
    }
    
    public function Update() {
        
        $sum = 0;
        $average = 0;
        $minimum = 2147483647; // = Max int TODO: Should we consider that float variables have a different maximum?
        $maximum = -2147483648; // = Min int
        $count = 0;
        
        $variables = json_decode($this->ReadPropertyString("Variables"));
        
        foreach($variables as $variable) {
            if (IPS_GetObject(intval($variable->ID))["ObjectType"] == 2) {
                $count++;
                $value = GetValue(intval($variable->ID));
                $sum += $value;
                $average += $value / sizeof($variables);
                if ($value < $minimum) {
                    $minimum = $value;
                }
                if ($value > $maximum) {
                    $maximum = $value;
                }
            }
        }
        
        if ((($this->ReadPropertyInteger("Calculation") == 0) || ($this->ReadPropertyInteger("Calculation") == 1)) && (@$this->GetIDForIdent("Sum") != false)) {
            SetValue($this->GetIDForIdent("Sum"), $sum);
        }
        
        if ((($this->ReadPropertyInteger("Calculation") == 0) || ($this->ReadPropertyInteger("Calculation") == 2)) && (@$this->GetIDForIdent("Minimum") != false)) {
            SetValue($this->GetIDForIdent("Minimum"), $minimum);
        }
        
        if ((($this->ReadPropertyInteger("Calculation") == 0) || ($this->ReadPropertyInteger("Calculation") == 3)) && (@$this->GetIDForIdent("Maximum") != false)) {
            SetValue($this->GetIDForIdent("Maximum"), $maximum);
        }
        
        if ((($this->ReadPropertyInteger("Calculation") == 0) || ($this->ReadPropertyInteger("Calculation") == 4)) && (@$this->GetIDForIdent("Average") != false)) {
            SetValue($this->GetIDForIdent("Average"), $average);
        }
        
        if ((($this->ReadPropertyInteger("Calculation") == 0) || ($this->ReadPropertyInteger("Calculation") == 5)) && (@$this->GetIDForIdent("Count") != false)) {
            SetValue($this->GetIDForIdent("Count"), $count);
        }
    }
}

?>