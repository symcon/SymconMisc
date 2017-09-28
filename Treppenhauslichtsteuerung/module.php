<?
    class Treppenhauslichtsteuerung extends IPSModule {
        public function Create()
        {
            //Never delete this line!
            parent::Create();

            $this->RegisterPropertyInteger("InputTriggerID", 0);
            $this->RegisterPropertyInteger("Duration", 1);
            $this->RegisterPropertyInteger("OutputID", 0);
            $this->RegisterTimer("OffTimer", 0, "THL_Stop(\$_IPS['TARGET']);");
            $this->RegisterVariableBoolean("Active", "Treppenhauslichtsteuerung aktiv", "~Switch");
            $this->EnableAction("Active");
        }

        public function ApplyChanges() {
            //Never delete this line!
            parent::ApplyChanges();

            $triggerID = $this->ReadPropertyInteger("InputTriggerID");
            $outputID = $this->ReadPropertyInteger("OutputID");

            $eid = @IPS_GetObjectIDByIdent("HoldEv", $this->InstanceID);
            if ($eid === false){
                $eid = IPS_CreateEvent(0 /* Trigger */);
                IPS_SetParent($eid, $this->InstanceID);
                IPS_SetName($eid, "On");
                IPS_SetIdent($eid, "HoldEv");
                IPS_SetEventActive($eid, true);
                IPS_SetEventTriggerValue($eid, true);
                IPS_SetEventTriggerSubsequentExecution($eid, true);
                IPS_SetEventScript($eid, "THL_Start(\$_IPS['TARGET']);");
            }

            if (($outputID != 0) && ($this->GetProfileAction(IPS_GetVariable($outputID)) < 10000)) {
                echo $this->Translate("The output variable of the Treppenhauslichtsteuerung has no variable action. Please choose a variable with a variable action or add a variable action to the output variable.");
            }

            IPS_SetEventActive($eid, !(($triggerID == 0) || ($outputID == 0)));
            IPS_SetEventTrigger($eid, 4, $triggerID);
        }

        public function RequestAction($Ident, $Value) {

            switch($Ident) {
                case "Active":
                    $this->SetActive($Value);
                    break;
                default:
                    throw new Exception("Invalid ident");
            }

        }
        
        public function SetActive($Value) {
            SetValue($this->GetIDForIdent("Active"), $Value);
        }
        
        public function Start(){
            if (!GetValue($this->GetIDForIdent("Active"))){
                return;
            }
            $duration = $this->ReadPropertyInteger("Duration");

            $this->SwitchVariable(true);
            $this->SetTimerInterval("OffTimer", $duration * 60 * 1000);
        }

        public function Stop(){
            $this->SwitchVariable(false);
            $this->SetTimerInterval("OffTimer", 0);
        }

        private function SwitchVariable(bool $Value){
            $outputID = $this->ReadPropertyInteger("OutputID");

            $object = IPS_GetObject($outputID);
            $variable = IPS_GetVariable($outputID);

            $actionID = $this->GetProfileAction($variable);

            //Quit if actionID is not a valid target
            if($actionID < 10000){
                echo $this->Translate("The output variable of the Treppenhauslichtsteuerung has no variable action. Please choose a variable with a variable action or add a variable action to the output variable.");
                return;
            }

            $profileName = $this->GetProfileName($variable);

            //If we somehow do not have a profile take care that we do not fail immediately
            if($profileName != "") {
                //If we are enabling analog devices we want to switch to the maximum value (e.g. 100%)
                if ($Value) {
                    $actionValue = IPS_GetVariableProfile($profileName)['MaxValue'];
                } else {
                    $actionValue = 0;
                }
                //Reduce to boolean if required
                if($variable['VariableType'] == 0) {
                    $actionValue = ($actionValue > 0);
                }
            } else {
                $actionValue = $Value;
            }

            if(IPS_InstanceExists($actionID)){
                IPS_RequestAction($actionID, $object['ObjectIdent'], $actionValue);
            } else if(IPS_ScriptExists($actionID)) {
                echo IPS_RunScriptWaitEx($actionID, Array("VARIABLE" => $outputID, "VALUE" => $actionValue));
            }
        }

        private function GetProfileName($variable){
            if($variable['VariableCustomProfile'] != ""){
                return $variable['VariableCustomProfile'];
            } else {
                return $variable['VariableProfile'];
            }
        }

        private function GetProfileAction($variable){
            if($variable['VariableCustomAction'] > 0){
                return $variable['VariableCustomAction'];
            } else {
                return $variable['VariableAction'];
            }
        }
    }
?>