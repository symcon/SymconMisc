<?

	class EgiGeoZone extends IPSModule {
		
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
			
			$this->RegisterHook("/hook/egigeozone");
		}
		
		private function RegisterHook($WebHook) {
			$ids = IPS_GetInstanceListByModuleID("{015A6EB8-D6E5-4B93-B496-0D3F77AE9FE1}");
			if(sizeof($ids) > 0) {
				$hooks = json_decode(IPS_GetProperty($ids[0], "Hooks"), true);
				$found = false;
				foreach($hooks as $index => $hook) {
					if($hook['Hook'] == $WebHook) {
						if($hook['TargetID'] == $this->InstanceID)
							return;
						$hooks[$index]['TargetID'] = $this->InstanceID;
						$found = true;
					}
				}
				if(!$found) {
					$hooks[] = Array("Hook" => $WebHook, "TargetID" => $this->InstanceID);
				}
				IPS_SetProperty($ids[0], "Hooks", json_encode($hooks));
				IPS_ApplyChanges($ids[0]);
			}
		}
	
		/**
		* This function will be called by the hook control. Visibility should be protected!
		*/
		protected function ProcessHookData() {
			if($_IPS['SENDER'] == "Execute") {
				echo "This script cannot be used this way.";
				return;
			}
			
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
			
			$this->SendDebug("EgiGeoZone", "Array GET: ".print_r($_GET, true), 0);
			
			$deviceID = $this->CreateInstanceByIdent($this->InstanceID, $this->ReduceGUIDToIdent($_GET['device']), "Device");
			SetValue($this->CreateVariableByIdent($deviceID, "Latitude", "Latitude", 2), $this->ParseFloat($_GET['latitude']));
			SetValue($this->CreateVariableByIdent($deviceID, "Longitude", "Longitude", 2), $this->ParseFloat($_GET['longitude']));
			SetValue($this->CreateVariableByIdent($deviceID, "Timestamp", "Timestamp", 1, "~UnixTimestamp"), intval(strtotime($_GET['date'])));
			SetValue($this->CreateVariableByIdent($deviceID, $this->ReduceToAllowedIdent($_GET['name']), utf8_decode($_GET['name']), 0, "~Presence"), intval($_GET['entry']) > 0);
			
		}
		
		private function ReduceGUIDToIdent($guid) {
			return str_replace(Array("{", "-", "}"), "", $guid);
		}
		
		private function CreateCategoryByIdent($id, $ident, $name) {
			 $cid = @IPS_GetObjectIDByIdent($ident, $id);
			 if($cid === false) {
				 $cid = IPS_CreateCategory();
				 IPS_SetParent($cid, $id);
				 IPS_SetName($cid, $name);
				 IPS_SetIdent($cid, $ident);
			 }
			 return $cid;
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

        private function ParseFloat($floatString) {
            $LocaleInfo = localeconv();
            $floatString = str_replace(".", $LocaleInfo["decimal_point"], $floatString);
            $floatString = str_replace(",", $LocaleInfo["decimal_point"], $floatString);
            return floatval($floatString);
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
