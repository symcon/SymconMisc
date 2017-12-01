<?
if (!defined('IPS_BASE')) {
	define("IPS_BASE", 10000);
}
if (!defined('IPS_KERNELSTARTED')) {
	define("IPS_KERNELSTARTED", IPS_BASE + 1);
}

class USBMapper extends IPSModule {
	
	public function Create() {
		//Never delete this line!
		parent::Create();
		
		$this->RegisterPropertyString("Devices", "[]");
		$this->RegisterPropertyBoolean("AutoActive", true);
		
		$this->RegisterTimer("CheckConnections", 60 * 1000, 'USBM_FixPorts($_IPS[\'TARGET\']);');
		
		$this->RegisterMessage(0, IPS_KERNELSTARTED);
		
	}
	
	public function Destroy() {
		//Never delete this line!
		parent::Destroy();
		
	}
	
	public function ApplyChanges() {
		//Never delete this line!
		parent::ApplyChanges();
		
	}
	
	public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {
		
		IPS_LogMessage("USB Mapper", "Checking ports...");
		$this->FixPorts();
		
	}
	
	public function FixPorts() {
		
		$arrString = $this->ReadPropertyString("Devices");
		$deviceList = json_decode($arrString, true);
		
		$usbDevices = $this->GetUSBDevices();
		
		foreach ($deviceList as $device) {
			foreach ($usbDevices as $usbDevice){
				
				if ($device['PortID'] == $usbDevice['id']) {
					// Check if its a serial port
					if (IPS_GetInstance($device['ID'])['ModuleInfo']['ModuleID'] == "{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}") {
						$devicePortArray = explode("/", IPS_GetProperty($device['ID'], "Port"));
						$devicePort = array_pop($devicePortArray);
						if ($devicePort != $usbDevice['device']) {
							IPS_SetProperty($device['ID'], "Port", "/dev/". $usbDevice['device'] ."");
							IPS_ApplyChanges($device['ID']);
						}
					}
				}
			}
		}
		
	}
	
	private function GetUSBDevices() {
		
		if (!stristr(PHP_OS, "linux")) {
			return Array();
		}
		
		$serial_devs = Array();
		$devs = scandir("/sys/class/tty/"); 
		foreach($devs as $dev) {
			if(file_exists("/sys/class/tty/".$dev."/device/driver")) {
				$serial_devs[] = $dev;
			}
		}

		$result = Array();
		foreach($serial_devs as $serial_devs) {
			$path = realpath("/sys/class/tty/".$serial_devs);
			if(strpos($path, "usb") !== false) {
				$path = dirname($path)."/../../../";

				if(file_exists($path."manufacturer")) {
					$manufacturer = trim(file_get_contents($path."manufacturer"));
				} else {
					$manufacturer = "Unknown";
				}
				if(file_exists($path."product")) {
					$product = trim(file_get_contents($path."product"));
				} else {
					$product = "Unknown";
				}
				if(file_exists($path."idVendor")) {
					$idVendor = trim(file_get_contents($path."idVendor"));
				} else {
					$idVendor = "xxxx";
				}
				if(file_exists($path."idProduct")) {
					$idProduct = trim(file_get_contents($path."idProduct"));
				} else {
					$idProduct = "xxxx";
				}
				if(file_exists($path."serial")) {
					$serial = trim(file_get_contents($path."serial"));
				} else {
					$serial = "xxxx";
				}

				$result[] = Array(
					"device" => $serial_devs,
					"manufacturer" => $manufacturer,
					"product" => $product,
					"id" => $idVendor.":".$idProduct.":".$serial, 
				);
			}
		}
		return $result;
	}
	
	public function GetConfigurationForm() {
		
		$formdata = json_decode(file_get_contents(__DIR__ . "/form.json"));
		
		$usbDevices = $this->GetUSBDevices();
		
		$selectUSB = array();
		foreach ($usbDevices as $usbDevice) {
			$selectUSB[] = array("label" => "". substr($usbDevice['product'], 0, 16) . "(now: ". $usbDevice['device'] .")", "value" => $usbDevice['id']);
		}
		
		$formdata->elements[1]->columns[2]->edit->options = $selectUSB;
		
		if($this->ReadPropertyString("Devices") != "") {
			//Annotate existing elements
			$devices = json_decode($this->ReadPropertyString("Devices"));
			foreach($devices as $device) {
				//We only need to add annotations. Remaining data is merged from persistance automatically.
				//Order is determinted by the order of array elements
				if(IPS_ObjectExists($device->ID) && $device->ID !== 0) {
					
					// Check if the selected device is a serial port
					$rowColor = "";
					if (!IPS_GetInstance($device->ID)['ModuleInfo']['ModuleID'] == "{6DC3D946-0D31-450F-A8C6-C42DB8D7D4F1}") {
						$rowColor = "#FFFF00";
					}
					
					$formdata->elements[1]->values[] = Array(
						"Name" => IPS_GetName($device->ID),
						"rowColor" => $rowColor,
						
					);
				} else {
					$formdata->elements[1]->values[] = Array(
						"Name" => "Not found!",
						"rowColor" => "#FF0000",
					);
				}
			}
		}
		
		return json_encode($formdata);
	}
	
}
?>