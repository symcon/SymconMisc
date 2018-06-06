<?

	class Umbenenner extends IPSModule
	{
		
		public function Create()
		{
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyInteger("SourceVariable", 0);

			$this->RegisterVariableString("Name", "Name");
			$this->EnableAction("Name");

		}
	
		public function ApplyChanges()
		{
			
			//Never delete this line!
			parent::ApplyChanges();
			
			$this->SetValue("Name", IPS_GetName($this->ReadPropertyInteger("SourceVariable")));
			
		}

        public function RequestAction($Ident, $Value) {

            switch ($Ident) {
                case "Name":
                    IPS_SetName($this->ReadPropertyInteger("SourceVariable"), $Value);
                    $this->SetValue("Name", $Value);
                    break;

                default:
                    throw new Exception("Invalid Ident");
            }
        }
	
	}

?>
