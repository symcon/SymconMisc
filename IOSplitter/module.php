<?

	class IOSplitter extends IPSModule
	{
		
		public function __construct($InstanceID)
		{
			//Never delete this line!
			parent::__construct($InstanceID);
			
		}
		
		public function ApplyChanges()
		{
			//Never delete this line!
			parent::ApplyChanges();
			
			//Always create our own VirtualIO, when no parent is already available
			$this->RequireParent("{6179ED6A-FC31-413C-BB8E-1204150CF376}");
			
		}
		
		public function ForwardData($JSONString)
		{
			$data = json_decode($JSONString);
			IPS_LogMessage("IOSplitter FRWD", print_r(json_decode($JSONString), true));

			//We would package our payload here before sending it further...

			$this->SendDataToParent(json_encode(Array("DataID" => "{79827379-F36E-4ADA-8A95-5F8D1DC92FA9}", "Buffer" => $data->Buffer)));
		}
		
		public function ReceiveData($JSONString)
		{
			$data = json_decode($JSONString);
			IPS_LogMessage("IOSplitter RECV", print_r($data, true));

			//We would parse our payload here before sending it further...

			//Lets just forward to our children
			$this->SendDataToChildren(json_encode(Array("DataID" => "{66164EB8-3439-4599-B937-A365D7A68567}", "Buffer" => $data->Buffer)));
		}
		
	
	}

?>
