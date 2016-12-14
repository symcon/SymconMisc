<?

	class Locative extends IPSModule {
		
		//This one needs to be available on our OAuth client backend.
		//Please contact us to register for an identifier: https://www.symcon.de/kontakt/#OAuth
		private $oauthIdentifer = "locative";
		
		public function Create() {
			//Never delete this line!
			parent::Create();
			
			$this->RegisterPropertyString("Token", "");

		}
	
		public function ApplyChanges() {
			//Never delete this line!
			parent::ApplyChanges();
			
			$this->RegisterOAuth($this->oauthIdentifer);
		}
		
		private function RegisterOAuth($WebOAuth) {
			$ids = IPS_GetInstanceListByModuleID("{F99BF07D-CECA-438B-A497-E4B55F139D37}");
			if(sizeof($ids) > 0) {
				$clientIDs = json_decode(IPS_GetProperty($ids[0], "ClientIDs"), true);
				$found = false;
				foreach($clientIDs as $index => $clientID) {
					if($clientID['ClientID'] == $WebOAuth) {
						if($clientID['TargetID'] == $this->InstanceID)
							return;
						$clientIDs[$index]['TargetID'] = $this->InstanceID;
						$found = true;
					}
				}
				if(!$found) {
					$clientIDs[] = Array("ClientID" => $WebOAuth, "TargetID" => $this->InstanceID);
				}
				IPS_SetProperty($ids[0], "ClientIDs", json_encode($clientIDs));
				IPS_ApplyChanges($ids[0]);
			}
		}
	
		/**
		* This function will be called by the register button on the property page!
		*/
		public function Register() {
			
			//Return everything which will open the browser
			return "https://oauth.ipmagic.de/authorize/".$this->oauthIdentifer."?username=".urlencode(IPS_GetLicensee());
			
		}
		
		private function FetchBearerToken($code) {
			
			//Exchange our Authentication Code for a permanent Baerer Token
			$options = array(
				'http' => array(
					'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
					'method'  => "POST",
					'content' => http_build_query(Array("code" => $code))
				)
			);
			$context = stream_context_create($options);
			$result = file_get_contents("https://oauth.ipmagic.de/access_token/".$this->oauthIdentifer, false, $context);
			$data = json_decode($result);
			
			if(!isset($data->token_type) || $data->token_type != "Bearer") {
				die("Bearer Token expected");
			}
			
			return $data->access_token;

		}
		
		/**
		* This function will be called by the OAuth control. Visibility should be protected!
		*/
		protected function ProcessOAuthData() {

			if(!isset($_GET['code'])) {
				die("Authorization Code expected");
			}
			
			$token = $this->FetchBearerToken($_GET['code']);
			
			IPS_SetProperty($this->InstanceID, "Token", $token);
			IPS_ApplyChanges($this->InstanceID);

		}
		
		private function FetchData($url) {
			
			if($this->ReadPropertyString("Token") == "") {
				die("No token found. Please register for a token first.");
			}
			
			$opts = array(
			  'http'=>array(
				'method'=>"GET",
				'header'=>"Authorization: Bearer " . $this->ReadPropertyString("Token") . "\r\n"
			  )
			);
			$context = stream_context_create($opts);
			
			return file_get_contents($url, false, $context);
			
		}
		
		public function RequestUserInformation() {
			
			return $this->FetchData("https://my.locative.io/api/v1/user");
			
		}

		public function RequestFencelogs() {
			
			return $this->FetchData("https://my.locative.io/api/v1/fencelogs");
			
		}

		public function RequestGeofences() {
			
			return $this->FetchData("https://my.locative.io/api/v1/geofences");
			
		}

	}

?>
