<?

	class Geofency extends IPSModule {
		
		public function Create() {
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyString("Username", "");
			$this->RegisterPropertyString("Password", "");
            $orientationass = Array(
                Array(0, "N",  "", -1),
                Array(22, "NNO",  "", -1),
                Array(45, "NO",  "", -1),
                Array(67, "ONO",  "", -1),
                Array(90, "O",  "", -1),
                Array(112, "OSO",  "", -1),
                Array(135, "SO",  "", -1),
                Array(157, "SSO",  "", -1),
                Array(180, "S",  "", -1),
                Array(202, "SSW",  "", -1),
                Array(225, "SW",  "", -1),
                Array(247, "WSW",  "", -1),
                Array(270, "W",  "", -1),
                Array(292, "WNW",  "", -1),
                Array(315, "NW",  "", -1),
                Array(337, "NNW",  "", -1)
            );
            $this->RegisterProfile("Geofency.Distance.m", "Distance", "", " m", 0, 0, 0, 2, 2);
            $this->RegisterProfileAssociation("Geofency.Orientation", "WindDirection", "", "", 0, 360, 1, 0, $orientationass, 1);

        }
	
		public function ApplyChanges()
        {
			//Never delete this line!
			parent::ApplyChanges();
            
			$this->RegisterHook("/hook/geofency");

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
			
			if(!isset($_POST['device']) || !isset($_POST['id']) || !isset($_POST['name'])) {
				$this->SendDebug("Geofency", "Malformed data: ".print_r($_POST, true), 0);
				return;
			}
			
			$this->SendDebug("GeoFency", "Array POST: ".print_r($_POST, true), 0);

			$deviceID = $this->CreateInstanceByIdent($this->InstanceID, $this->ReduceGUIDToIdent($_POST['device']), "Device");
			SetValue($this->CreateVariableByIdent($deviceID, "Latitude", "Latitude", 2), floatval($_POST['latitude']));
			SetValue($this->CreateVariableByIdent($deviceID, "Longitude", "Longitude", 2), floatval($_POST['longitude']));
			SetValue($this->CreateVariableByIdent($deviceID, "Timestamp", "Timestamp", 1, "~UnixTimestamp"), intval(strtotime($_POST['date'])));
			SetValue($this->CreateVariableByIdent($deviceID, $this->ReduceGUIDToIdent($_POST['id']), utf8_decode($_POST['name']), 0, "~Presence"), intval($_POST['entry']) > 0);
            if(isset($_POST['currentLatitude']) && isset($_POST['currentLongitude']))
            {
                $this->SendDebug("GeoFency", "Current Latitude: ".print_r($_POST["currentLatitude"], true)." Current Longitude: ".print_r($_POST["currentLongitude"], true), 0);
                SetValue($this->CreateVariableByIdent($deviceID, "CurrentLatitude", "current Latitude", 2), floatval($_POST['currentLatitude']));
                SetValue($this->CreateVariableByIdent($deviceID, "CurrentLongitude", "current Longitude", 2), floatval($_POST['currentLongitude']));
                SetValue($this->CreateVariableByIdent($deviceID, "Direction", "Ein-/Austrittswinkel", 1, "~WindDirection"), $this->GetDirectionToCenter($_POST['latitude'], $_POST['longitude'], $_POST['currentLatitude'], $_POST['currentLongitude']));
                SetValue($this->CreateVariableByIdent($deviceID, "Orientation", "Himmelsrichtung", 1, "Geofency.Orientation"), $this->GetDirectionToCenter($_POST['latitude'], $_POST['longitude'], $_POST['currentLatitude'], $_POST['currentLongitude']));
                SetValue($this->CreateVariableByIdent($deviceID, "Distance", "Distanz", 2, "Geofency.Distance.m"), $this->GetDistanceToCenter($_POST['latitude'], $_POST['longitude'], $_POST['currentLatitude'], $_POST['currentLongitude'], "m"));
            }
            else
            {
                $objidlat = @IPS_GetObjectIDByIdent("currentLatitude", $deviceID);
                if($objidlat)
                {
                    SetValueFloat($objidlat, 0);
                }
                $objidlong = @IPS_GetObjectIDByIdent("currentLongitude", $deviceID);
                if($objidlong)
                {
                    SetValueFloat($objidlong, 0);
                    SetValueInteger(IPS_GetObjectIDByIdent("direction", $deviceID), 0);
                    SetValueInteger(IPS_GetObjectIDByIdent("orientation", $deviceID), 0);
                    SetValueFloat(IPS_GetObjectIDByIdent("distance", $deviceID), 0);
                }
            }
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

        protected function GetDistanceToCenter($center_latitude, $center_longitude, $current_latitude, $current_longitude, $unit)
        {
            $theta = $center_longitude - $current_longitude;
            $distance = sin(deg2rad($center_latitude)) * sin(deg2rad($current_latitude)) +  cos(deg2rad($center_latitude)) * cos(deg2rad($current_latitude)) * cos(deg2rad($theta));
            $distance = acos($distance);
            $distance = rad2deg($distance);
            $miles = $distance * 60 * 1.1515;
            $unit = strtoupper($unit);

            if ($unit == "KM") // Kilometer
            {
                $distance = round(($miles * 1.609344), 2);
            }
            else if ($unit == "NM") // Nautic Mile NM
            {
                $distance = round(($miles * 0.8684), 2);
            }
            else if ($unit == "M") // Meter m
            {
                $distance = round(($miles * 1.609344 * 1000), 2);
            }
            else
            {
                $distance = round($miles, 2);
            }
            return $distance;
        }

        protected function GetDirectionToCenter($center_latitude, $center_longitude, $current_latitude, $current_longitude)
        {
            //difference in longitudinal coordinates
            $dLon = deg2rad($current_longitude) - deg2rad($center_longitude); // Δlon = abs( lonA - lonB )

            //difference in the phi of latitudinal coordinates
            $dPhi = log(tan(deg2rad($current_latitude) / 2 + pi() / 4) / tan(deg2rad($center_latitude) / 2 + pi() / 4)); // Δφ = ln( tan( latB / 2 + π / 4 ) / tan( latA / 2 + π / 4) )

            //we need to recalculate $dLon if it is greater than pi
            if(abs($dLon) > pi()) {
                if($dLon > 0) {
                    $dLon = (2 * pi() - $dLon) * -1;
                }
                else {
                    $dLon = 2 * pi() + $dLon;
                }
            }
            //return the angle, normalized
            $angle = (rad2deg(atan2($dLon, $dPhi)) + 360) % 360; // tragen :  θ = atan2( Δlon ,  Δφ )
            return $angle;
        }

        protected function RegisterProfile($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Vartype)
        {

            if(!IPS_VariableProfileExists($Name))
            {
            	IPS_CreateVariableProfile($Name, $Vartype);
            }
            else
            {
                $profile = IPS_GetVariableProfile($Name);
                if($profile['ProfileType'] != $Vartype)
                    throw new Exception("Variable profile type does not match for profile ".$Name);
            }

            IPS_SetVariableProfileIcon($Name, $Icon);
            IPS_SetVariableProfileText($Name, $Prefix, $Suffix);
            IPS_SetVariableProfileDigits($Name, $Digits); //  Nachkommastellen
            IPS_SetVariableProfileValues($Name, $MinValue, $MaxValue, $StepSize);

        }

        protected function RegisterProfileAssociation($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Associations, $Vartype)
        {
            if ( sizeof($Associations) === 0 )
            {
                $MinValue = 0;
                $MaxValue = 0;
            }

            $this->RegisterProfile($Name, $Icon, $Prefix, $Suffix, $MinValue, $MaxValue, $Stepsize, $Digits, $Vartype);

            foreach($Associations as $Association)
            {
                IPS_SetVariableProfileAssociation($Name, $Association[0], $Association[1], $Association[2], $Association[3]);
            }
        }
		
	}

?>
