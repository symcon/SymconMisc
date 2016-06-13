<?
// Klassendefinition
class WundergroundWeather extends IPSModule {

	public function Create() {
		// Diese Zeile nicht löschen.
		parent::Create();

		$this->RegisterPropertyString("Location", "Lübeck");
		$this->RegisterPropertyString("Country", "Germany");
		$this->RegisterPropertyString("APIKey", "");
		$this->RegisterPropertyBoolean("FetchNow", true);
		$this->RegisterPropertyBoolean("FetchHourly", true);
		$this->RegisterPropertyInteger("FetchHourlyHoursCount", 3);
		$this->RegisterPropertyBoolean("FetchHalfDaily", true);
		$this->RegisterPropertyInteger("FetchHalfDailyHalfDaysCount", 4);
		$this->RegisterPropertyBoolean("FetchStormWarning", true);
		$this->RegisterPropertyInteger("FetchStormWarningStormWarningCount", 3);
		$this->RegisterPropertyInteger("UpdateWeatherInterval", 10);
		$this->RegisterPropertyInteger("UpdateWarningInterval", 60);

		//Variablenprofil anlegen ($name, $ProfileType, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Icon)
		$this->CreateVarProfile("WGW.Rainfall", 2, " Liter/m²" ,0 , 10, 0 , 2, "Rainfall");
		$this->CreateVarProfile("WGW.Sunray", 2, " W/m²", 0, 2000, 0, 2, "Sun");
		$this->CreateVarProfile("WGW.Visibility", 2, " km", 0, 0, 0, 2, "");
		$this->CreateVarProfileWGWWindSpeedkmh();
		$this->CreateVarProfileWGWUVIndex();

		//Timer erstellen
		$this->RegisterTimer("UpdateWeather", $this->ReadPropertyInteger("UpdateWeatherInterval"), 'WGW_UpdateWeatherData($_IPS[\'TARGET\']);');
		$this->RegisterTimer("UpdateStormWarning", $this->ReadPropertyInteger("UpdateWarningInterval"), 'WGW_UpdateStormWarningData($_IPS[\'TARGET\']);');

	}

	// Überschreibt die intere IPS_ApplyChanges($id) Funktion
	public function ApplyChanges() {
		// Diese Zeile nicht löschen
		parent::ApplyChanges();

		if (($this->ReadPropertyString("APIKey") != "") && ($this->ReadPropertyString("Location") != "")){

			//Timerzeit setzen in Minuten
			if ($this->ReadPropertyBoolean("FetchNow") || $this->ReadPropertyBoolean("FetchHourly") || $this->ReadPropertyBoolean("FetchHalfDaily")) {
				$this->SetTimerInterval("UpdateWeather", $this->ReadPropertyInteger("UpdateWeatherInterval")*1000*60);
			} else {
				$this->SetTimerInterval("UpdateWeather", 0);
			}

			if ($this->ReadPropertyBoolean("FetchStormWarning")) {
				$this->SetTimerInterval("UpdateStormWarning", $this->ReadPropertyInteger("UpdateWarningInterval")*1000*60);
			} else {
				$this->SetTimerInterval("UpdateStormWarning", 0);
			}

			//Jetzt Variablen erstellen/löschen
			$keep = $this->ReadPropertyBoolean("FetchNow");
			$this->MaintainVariable("NowPressure", "Luftdruck (aktuell)", 2, "AirPressure.F", 10, $keep);
			$this->MaintainVariable("NowHumidity", "Luftfeuchtigkeit (aktuell)", 2, "Humidity.F", 20, $keep);
			$this->MaintainVariable("NowRain", "Niederschlag/h (aktuell)", 2, "WGW.Rainfall", 30, $keep);
			$this->MaintainVariable("NowRainToday", "Niederschlag Tag (aktuell)", 2, "WGW.Rainfall", 40, $keep);
			$this->MaintainVariable("NowVisibility", "Sichtweite (aktuell)", 2, "WGW.Visibility", 50, $keep);
			$this->MaintainVariable("NowSolar", "Sonnenstrahlung (aktuell)", 2, "WGW.Sunray", 60, $keep);
			$this->MaintainVariable("NowTemp", "Temperatur (aktuell)", 2, "Temperature", 70, $keep);
			$this->MaintainVariable("NowTempFeel", "Temperatur gefühlt (aktuell)", 2, "Temperature", 80, $keep);
			$this->MaintainVariable("NowTempDewpoint", "Temperatur Taupunkt (aktuell)", 2, "Temperature", 90, $keep);
			$this->MaintainVariable("NowUV", "UV Strahlung (aktuell)", 1, "WGW.UVIndex", 100, $keep);
			$this->MaintainVariable("NowWindgust", "Windböe (aktuell)", 2, "WGW.WindSpeedkmh", 110, $keep);
			$this->MaintainVariable("NowWindspeed", "Windgeschwindigkeit (aktuell)", 2, "WGW.WindSpeedkmh", 120, $keep);
			$this->MaintainVariable("NowWindDeg", "Windrichtung (aktuell)", 2, "WindDirection.Text", 130, $keep);

			//Stündliche Variablen erstellen/löschen
			if ($this->ReadPropertyBoolean("FetchHourly")) {
				$keep = $this->ReadPropertyInteger("FetchHourlyHoursCount");
			} else {
				$keep = 0;
			}
			for ($i = 1; $i <= 24; $i++) {
				$this->MaintainVariable("HourlyCondition".$i."h", "Gegebenheit Vorhersage (".$i."h)", 3, "", 1000+$i, $i <= $keep);
				$this->MaintainVariable("HourlyHumidity".$i."h", "Luftfeuchte Vorhersage (".$i."h)", 2, "Humidity.F", 1050+$i, $i <= $keep);
				$this->MaintainVariable("HourlyPressure".$i."h", "Luftdruck Vorhersage (".$i."h)", 2, "AirPressure.F", 1100+$i, $i <= $keep);
				$this->MaintainVariable("HourlyRain".$i."h", "Regenmenge Vorhersage (".$i."h)", 2, "WGW.Rainfall", 1150+$i, $i <= $keep);
				$this->MaintainVariable("HourlyTemp".$i."h", "Temperatur Vorhersage (".$i."h)", 2, "~Temperature", 1200+$i, $i <= $keep);
				$this->MaintainVariable("HourlySky".$i."h", "Wolkendecke Vorhersage (".$i."h)", 1, "~Intensity.100", 1250+$i, $i <= $keep);
				$this->MaintainVariable("HourlyWindspeed".$i."h", "Windgeschwindigkeit Vorhersage (".$i."h)", 2, "WGW.WindSpeedkmh", 1300+$i, $i <= $keep);
			}

			//12stündliche Variablen erstellen/löschen
			if ($this->ReadPropertyBoolean("FetchHalfDaily")) {
				$keep = $this->ReadPropertyInteger("FetchHalfDailyHalfDaysCount");;
			} else {
				$keep = 0;
			}
			for ($i = 1; $i <= 8; $i++) {
				$this->MaintainVariable("HalfDailyHighTemp".(12*$i)."h", "Höchsttemperatur 12Std-Vorhersage (".(12*$i)."h)", 2, "~Temperature", 2000+$i, $i <= $keep);
				$this->MaintainVariable("HalfDailyLowTemp".(12*$i)."h", "Tiefsttemperatur 12Std-Vorhersage (". (12*$i)."h)", 2, "~Temperature", 2050+$i, $i <= $keep);
			}

			//Unwetterwarnungen Variablen erstellen/löschen
			if ($this->ReadPropertyBoolean("FetchStormWarning")) {
				$keep = $this->ReadPropertyInteger("FetchStormWarningStormWarningCount");;
			} else {
				$keep = 0;
			}
			for ($i = 1; $i <= 6; $i++) {
				$this->MaintainVariable("StormWarning".$i."Text", "Unwetter ".$i." Beschreibung", 3, "~TextBox", 3000+$i, $i <= $keep);
				$this->MaintainVariable("StormWarning".$i."Date", "Unwetter ".$i." Datum", 1, "~UnixTimestamp", 3050+$i, $i <= $keep);
				$this->MaintainVariable("StormWarning".$i."Name", "Unwetter ".$i." Name", 3, "~String", 3100+$i, $i <= $keep);
				$this->MaintainVariable("StormWarning".$i."Type", "Unwetter ".$i." Typ", 3, "~String", 3150+$i, $i <= $keep);
			}

			//Instanz ist aktiv
			$this->SetStatus(102);

		} else {
			//Instanz ist inaktiv
			$this->SetStatus(104);
		}

	}

	public function UpdateWeatherData() {

		if ($this->ReadPropertyBoolean("FetchNow")) {
			//Wetterdaten vom aktuellen Wetter
			$WeatherNow = $this->RequestAPI("/conditions/lang:DL/q/");

			$this->SendDebug("WGW Now", print_r($WeatherNow, true), 0);

			//Wetterdaten in Variable speichern
			SetValue($this->GetIDForIdent("NowTemp"), $WeatherNow->current_observation->temp_c);
			SetValue($this->GetIDForIdent("NowTempFeel"), $WeatherNow->current_observation->feelslike_c);
			SetValue($this->GetIDForIdent("NowTempDewpoint"), $WeatherNow->current_observation->dewpoint_c);
			SetValue($this->GetIDForIdent("NowHumidity"), substr($WeatherNow->current_observation->relative_humidity, 0, -1));
			SetValue($this->GetIDForIdent("NowPressure"), $WeatherNow->current_observation->pressure_mb);
			SetValue($this->GetIDForIdent("NowWindDeg"), $WeatherNow->current_observation->wind_degrees);
			SetValue($this->GetIDForIdent("NowWindspeed"), $WeatherNow->current_observation->wind_kph);
			SetValue($this->GetIDForIdent("NowWindgust"), $WeatherNow->current_observation->wind_gust_kph);
			SetValue($this->GetIDForIdent("NowRain"), $WeatherNow->current_observation->precip_1hr_metric);
			SetValue($this->GetIDForIdent("NowRainToday"), $WeatherNow->current_observation->precip_today_metric);
			if ($WeatherNow->current_observation->solarradiation === "--") {
				SetValue($this->GetIDForIdent("NowSolar"), 0);
			} else {
				SetValue($this->GetIDForIdent("NowSolar"), $WeatherNow->current_observation->solarradiation);
			}
			SetValue($this->GetIDForIdent("NowVisibility"), $WeatherNow->current_observation->visibility_km);
			SetValue($this->GetIDForIdent("NowUV"), $WeatherNow->current_observation->UV);
		}

		//Stündliche Vorhersagen
		if ($this->ReadPropertyBoolean("FetchHourly")) {
			$WeatherNextHours = $this->RequestAPI("/hourly/lang:DL/q/");

			$this->SendDebug("WGW Hourly", print_r($WeatherNextHours, true), 0);

			for ($i=1; $i <= $this->ReadPropertyInteger("FetchHourlyHoursCount"); $i++) {
				SetValue($this->GetIDForIdent("HourlyTemp".$i."h"), $WeatherNextHours->hourly_forecast[$i-1]->temp->metric);
				SetValue($this->GetIDForIdent("HourlySky".$i."h"), $WeatherNextHours->hourly_forecast[$i-1]->sky);
				SetValue($this->GetIDForIdent("HourlyCondition".$i."h"), $WeatherNextHours->hourly_forecast[$i-1]->condition);
				SetValue($this->GetIDForIdent("HourlyHumidity".$i."h"), $WeatherNextHours->hourly_forecast[$i-1]->humidity);
				SetValue($this->GetIDForIdent("HourlyWindspeed".$i."h"), $WeatherNextHours->hourly_forecast[$i-1]->wspd->metric);
				SetValue($this->GetIDForIdent("HourlyPressure".$i."h"), $WeatherNextHours->hourly_forecast[$i-1]->mslp->metric);
				SetValue($this->GetIDForIdent("HourlyRain".$i."h"), $WeatherNextHours->hourly_forecast[$i-1]->qpf->metric);
			}
		}

		//12 stündliche Vorhersagen
		if ($this->ReadPropertyBoolean("FetchHalfDaily")) {
			$WeatherNextHalfDays = $this->RequestAPI("/forecast/lang:DL/q/");

			$this->SendDebug("WGW HalfDays", print_r($WeatherNextHalfDays, true), 0);

			for ($i=1; $i <= $this->ReadPropertyInteger("FetchHalfDailyHalfDaysCount") ; $i++) {
				SetValue($this->GetIDForIdent("HalfDailyHighTemp".(12*$i)."h"), $WeatherNextHalfDays->forecast->simpleforecast->forecastday[$i-1]->high->celsius);
				SetValue($this->GetIDForIdent("HalfDailyLowTemp".(12*$i)."h"), $WeatherNextHalfDays->forecast->simpleforecast->forecastday[$i-1]->low->celsius);
			}
		}

}

	public function UpdateStormWarningData() {

		//Abfrage von Unwetterwarnungen
		if ($this->ReadPropertyBoolean("FetchStormWarning")) {
			$warnings = $this->RequestAPI("/alerts/lang:DL/q/");

			$alerts = array_slice($warnings->alerts, 0, 3);

			$this->SendDebug("WGW Alerts", print_r($alerts, true), 0);

			//Unwetterdaten setzen
			for ($i = 1; $i <= $this->ReadPropertyInteger("FetchStormWarningStormWarningCount"); $i++) {
				if(isset($alerts[$i-1]) && ($alerts[$i-1]->date !== "")) {
					SetValue($this->GetIDForIdent("StormWarning".$i."Date"), strtotime($alerts[$i-1]->date));
				} else {
					SetValue($this->GetIDForIdent("StormWarning".$i."Date"), 0);
				}

				if(isset($alerts[$i-1]) && ($alerts[$i-1]->type !== "")) {
					SetValue($this->GetIDForIdent("StormWarning".$i."Type"), $alerts[$i-1]->type);
				} else {
					SetValue($this->GetIDForIdent("StormWarning".$i."Type"), "");
				}

				if(isset($alerts[$i-1]) && ($alerts[$i-1]->wtype_meteoalarm_name !== "")) {
					SetValue($this->GetIDForIdent("StormWarning".$i."Name"), $alerts[$i-1]->wtype_meteoalarm_name);
				} else {
					SetValue($this->GetIDForIdent("StormWarning".$i."Name"), "");
				}

				if(isset($alerts[$i-1]) && ($alerts[$i-1]->description !== "")) {
					SetValue($this->GetIDForIdent("StormWarning".$i."Text"), str_replace("deutsch:", "", $alerts[$i-1]->description));
				} else {
					SetValue($this->GetIDForIdent("StormWarning".$i."Text"), "");
				}
			}
		}
	}

	private function WithoutSpecialChars($String){

		return str_replace(array("ä", "ö", "ü", "Ä", "Ö", "Ü", "ß"), array("a", "o", "u", "A", "O", "U", "ss"), $String);

	}

	//JSON String abfragen und als decodiertes Array zurückgeben
	private function RequestAPI($URLString) {

		$location = $this->WithoutSpecialChars($this->ReadPropertyString("Location"));  // Location
		$country = $this->WithoutSpecialChars($this->ReadPropertyString("Country"));  // Country
		$APIkey = $this->ReadPropertyString("APIKey");  // API Key Wunderground

		$this->SendDebug("WGW Requested URL", "http://api.wunderground.com/api/".$APIkey.$URLString.$country."/".$location.".json", 0);
		$content = file_get_contents("http://api.wunderground.com/api/".$APIkey.$URLString.$country."/".$location.".json");  //Json Daten öffnen

		if ($content === false) {
			throw new Exception("Die Wunderground-API konnte nicht abgefragt werden!");
		}
		
		$content = json_decode($content);
		
		if (isset($content->response->error)) {
			throw new Exception("Die Anfrage bei Wunderground beinhaltet Fehler: ".$content->response->error->description);
		}

		return $content;
	}

	// Variablenprofile erstellen
	private function CreateVarProfile($name, $ProfileType, $Suffix, $MinValue, $MaxValue, $StepSize, $Digits, $Icon) {
		if (!IPS_VariableProfileExists($name)) {
			IPS_CreateVariableProfile($name, $ProfileType);
			IPS_SetVariableProfileText($name, "", $Suffix);
			IPS_SetVariableProfileValues($name, $MinValue, $MaxValue, $StepSize);
			IPS_SetVariableProfileDigits($name, $Digits);
			IPS_SetVariableProfileIcon($name, $Icon);
		 }
	}

	//Variablenprofil für die Windgeschwindigkeit erstellen
	private function CreateVarProfileWGWWindSpeedKmh() {
		if (!IPS_VariableProfileExists("WGW.WindSpeedkmh")) {
			IPS_CreateVariableProfile("WGW.WindSpeedkmh", 2);
			IPS_SetVariableProfileText("WGW.WindSpeedkmh", "", " km/h");
			IPS_SetVariableProfileValues("WGW.WindSpeedkmh", 0, 200, 0);
			IPS_SetVariableProfileDigits("WGW.WindSpeedkmh", 1);
			IPS_SetVariableProfileIcon("WGW.WindSpeedkmh", "WindSpeed");
			IPS_SetVariableProfileAssociation("WGW.WindSpeedkmh", 0, "%.1f", "", 0xFFFF00);
			IPS_SetVariableProfileAssociation("WGW.WindSpeedkmh", 2, "%.1f", "", 0x66CC33);
			IPS_SetVariableProfileAssociation("WGW.WindSpeedkmh", 4, "%.1f", "", 0xFF6666);
			IPS_SetVariableProfileAssociation("WGW.WindSpeedkmh", 6, "%.1f", "", 0x33A488);
			IPS_SetVariableProfileAssociation("WGW.WindSpeedkmh", 10, "%.1f", "", 0x00CCCC);
			IPS_SetVariableProfileAssociation("WGW.WindSpeedkmh", 20, "%.1f", "", 0xFF33CC);
			IPS_SetVariableProfileAssociation("WGW.WindSpeedkmh", 36, "%.1f", "", 0XFFCCFF);
		 }
	}

	//Variablenprofil für den UVIndex erstellen
	private function CreateVarProfileWGWUVIndex() {
		if (!IPS_VariableProfileExists("WGW.UVIndex")) {
			IPS_CreateVariableProfile("WGW.UVIndex", 1);
			IPS_SetVariableProfileValues("WGW.UVIndex", 0, 12, 0);
			IPS_SetVariableProfileAssociation("WGW.UVIndex", 0, "%.1f", "" , 0xC0FFA0);
			IPS_SetVariableProfileAssociation("WGW.UVIndex", 3, "%.1f", "" , 0xF8F040);
			IPS_SetVariableProfileAssociation("WGW.UVIndex", 6, "%.1f", "" , 0xF87820);
			IPS_SetVariableProfileAssociation("WGW.UVIndex", 8, "%.1f", "" , 0xD80020);
			IPS_SetVariableProfileAssociation("WGW.UVIndex", 11, "%.1f", "" , 0xA80080);
		 }
	}

 }
?>
