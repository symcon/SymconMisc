<?

include __DIR__ . "/../libs/WebHookModule.php";

	class EgiGeoZone extends WebHookModule {

		public function __construct($InstanceID) {

        	parent::__construct($InstanceID, "egigeozone");

        }

        public function Create() {

			//Never delete this line!
			parent::Create();
			
			//Properties
			$this->RegisterPropertyString("Username", "");
			$this->RegisterPropertyString("Password", "");

		}

		public function ApplyChanges() {

			//Never delete this line!
			parent::ApplyChanges();

            //Cleanup old hook script
            $id = @IPS_GetObjectIDByIdent("Hook", $this->InstanceID);
            if($id > 0) {
                IPS_DeleteScript($id, true);
            }

		}

		/**
		* This function will be called by the hook control. Visibility should be protected!
		*/
		protected function ProcessHookData() {

            //Never delete this line!
            parent::ProcessHookData();
			
			if((IPS_GetProperty($this->InstanceID, "Username") != "") || (IPS_GetProperty($this->InstanceID, "Password") != "")) {
				if(!isset($_SERVER['PHP_AUTH_USER']))
					$_SERVER['PHP_AUTH_USER'] = "";
				if(!isset($_SERVER['PHP_AUTH_PW']))
					$_SERVER['PHP_AUTH_PW'] = "";
					
				if(($_SERVER['PHP_AUTH_USER'] != IPS_GetProperty($this->InstanceID, "Username")) || ($_SERVER['PHP_AUTH_PW'] != IPS_GetProperty($this->InstanceID, "Password"))) {
					header('WWW-Authenticate: Basic Realm="Geofency WebHook"');
					header('HTTP/1.0 401 Unauthorized');
					echo "Authorization required";
					return;
				}
			}
			
			if(!isset($_GET['device']) || !isset($_GET['id']) || !isset($_GET['name'])) {
				$this->SendDebug("EgiGeoZone", "Malformed data: ".print_r($_GET, true), 0);
				return;
			}
			
			//Adding a missing '+' in front of the timezone to get a strtotime() convertable string
			//Example:
			//"2016-08-12T17:40:13 0000" => "2016-08-12T17:40:13 +0000"
			if(strlen($_GET['date']) == 24){
				$_GET['date'] = implode("+", str_split($_GET['date'], 20));
			}

			$deviceID = $this->CreateInstanceByIdent($this->InstanceID, $this->ReduceGUIDToIdent($_GET['device']), "Device");
			SetValue($this->CreateVariableByIdent($deviceID, "Latitude", "Latitude", 2), floatval($_GET['latitude']));
			SetValue($this->CreateVariableByIdent($deviceID, "Longitude", "Longitude", 2), floatval($_GET['longitude']));
			SetValue($this->CreateVariableByIdent($deviceID, "Timestamp", "Timestamp", 1, "~UnixTimestamp"), intval(strtotime($_GET['date'])));
			SetValue($this->CreateVariableByIdent($deviceID, $this->ReduceToAllowedIdent($_GET['name']), utf8_decode($_GET['name']), 0, "~Presence"), intval($_GET['entry']) > 0);
			
		}
		
		private function ReduceGUIDToIdent($guid) {
			return str_replace(Array("{", "-", "}"), "", $guid);
		}
		
		private function CreateVariableByIdent($id, $ident, $name, $type, $profile = "") {
			 $vid = @IPS_GetObjectIDByIdent($ident, $id);
			 if($vid === false) {
				 $vid = IPS_CreateVariable($type);
				 IPS_SetParent($vid, $id);
				 IPS_SetName($vid, $name);
				 IPS_SetIdent($vid, $ident);
				 if($profile != "")
					IPS_SetVariableCustomProfile($vid, $profile);
			 }
			 return $vid;
		}
		
		private function CreateInstanceByIdent($id, $ident, $name, $moduleid = "{485D0419-BE97-4548-AA9C-C083EB82E61E}") {
			 $iid = @IPS_GetObjectIDByIdent($ident, $id);
			 if($iid === false) {
				 $iid = IPS_CreateInstance($moduleid);
				 IPS_SetParent($iid, $id);
				 IPS_SetName($iid, $name);
				 IPS_SetIdent($iid, $ident);
			 }
			 return $iid;
		}
		
		//Replaces all unallowed Chars of a String with "_"
		//Allowed Chars: "a..z", "A..Z", "_", "0..9"
		private function ReduceToAllowedIdent($String) {

			for($i = 0; $i < strlen($String) ; $i++) {
				$val = ord($String[$i]);
				//    Between (1..9)                Between (A..Z)                Underscore (_)  Between (a..z)
				if (!(($val >= 48 && $val <= 57) || ($val >= 65 && $val <= 90) || ($val == 95) || ($val >= 97 && $val <= 122))) {
					$String[$i] = "_";
				}
			}
			return $String;
		}
	
	}

?>
