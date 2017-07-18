<?
class EnergieAmpel extends IPSModule {
    
    public function Create(){
        //Never delete this line!
        parent::Create();
        
        //These lines are parsed on Symcon Startup or Instance creation
        //You cannot use variables here. Just static values.
        $this->RegisterPropertyBoolean("PreviousYear", false);
        $this->RegisterPropertyInteger("ExpectedConsumption", 3500);
        $this->RegisterPropertyInteger("Startmonth", 1);
        $this->RegisterPropertyInteger("ConsumptionVariableID", 0);
        $this->RegisterPropertyFloat("PriceConsume", 28.81);
        $this->RegisterPropertyString("ConsumptionPerMonth", "[{\"consumption\":8},{\"consumption\":8},{\"consumption\":8},{\"consumption\":8},{\"consumption\":8},{\"consumption\":8},{\"consumption\":8},{\"consumption\":8},{\"consumption\":9},{\"consumption\":9},{\"consumption\":9},{\"consumption\":9}]");
        $this->RegisterPropertyInteger("ProductionVariableID", 0);
        $this->RegisterPropertyFloat("PriceProduce", 14.76);
        
        $this->RegisterPropertyInteger("ArchiveControlID", IPS_GetInstanceListByModuleID("{43192F0B-135B-4CE7-A0A7-1475603F3060}")[0]);
        
        if (!IPS_VariableProfileExists("Euro.EA")) {
            IPS_CreateVariableProfile("Euro.EA", 2);
            IPS_SetVariableProfileText("Euro.EA", "", " €");
            IPS_SetVariableProfileValues("Euro.EA", 0, 0, 0);
            IPS_SetVariableProfileDigits("Euro.EA", 2);
            IPS_SetVariableProfileIcon("Euro.EA", "");
        }
        
        if (!IPS_VariableProfileExists("Tendency.EA")) {
            IPS_CreateVariableProfile("Tendency.EA", 1);
            IPS_SetVariableProfileText("Tendency.EA", "", " %");
            IPS_SetVariableProfileValues("Tendency.EA", 0, 0, 0);
            IPS_SetVariableProfileIcon("Tendency.EA", "");
            IPS_SetVariableProfileAssociation("Tendency.EA", 0, "%d", "", 0x00FF00);
            IPS_SetVariableProfileAssociation("Tendency.EA", 101, "%d", "", 0xFFFF00);
            IPS_SetVariableProfileAssociation("Tendency.EA", 120, "%d", "", 0xFF0000);
        }
        
        $this->RegisterVariableInteger("WeekTendency", $this->Translate("Week: Tendency"), "Tendency.EA", 10);
        $this->RegisterVariableFloat("WeekProduction", $this->Translate("Week: Production"), "~Electricity", 20);
        $this->RegisterVariableFloat("WeekProductionPrice", $this->Translate("Week: Production (Price)"), "Euro.EA", 30);
        $this->RegisterVariableFloat("WeekConsumption", $this->Translate("Week: Consumption"), "~Electricity", 40);
        $this->RegisterVariableFloat("WeekConsumptionPrice", $this->Translate("Week: Consumption (Price)"), "Euro.EA", 50);
        $this->RegisterVariableFloat("WeekTotal", $this->Translate("Week: Total"), "Euro.EA", 60);
        
        $this->RegisterVariableInteger("MonthTendency", $this->Translate("Month: Tendency"), "Tendency.EA", 70);
        $this->RegisterVariableFloat("MonthProduction", $this->Translate("Month: Production"), "~Electricity", 80);
        $this->RegisterVariableFloat("MonthProductionPrice", $this->Translate("Month: Production (Price)"), "Euro.EA", 90);
        $this->RegisterVariableFloat("MonthConsumption", $this->Translate("Month: Consumption"), "~Electricity", 100);
        $this->RegisterVariableFloat("MonthConsumptionPrice", $this->Translate("Month: Consumption (Price)"), "Euro.EA", 110);
        $this->RegisterVariableFloat("MonthTotal", $this->Translate("Month: Total"), "Euro.EA", 120);
        
        $this->RegisterVariableInteger("YearTendency", $this->Translate("Year: Tendency"), "Tendency.EA", 130);
        $this->RegisterVariableFloat("YearProduction", $this->Translate("Year: Production"), "~Electricity", 140);
        $this->RegisterVariableFloat("YearProductionPrice", $this->Translate("Year: Production (Price)"), "Euro.EA", 150);
        $this->RegisterVariableFloat("YearConsumption", $this->Translate("Year: Consumption"), "~Electricity", 160);
        $this->RegisterVariableFloat("YearConsumptionPrice", $this->Translate("Year: Consumption (Price)"), "Euro.EA", 170);
        $this->RegisterVariableFloat("YearTotal", $this->Translate("Year: Total"), "Euro.EA", 180);
        
        //Update at next full hour
        $this->RegisterTimer("UpdateTimer", 0, "EA_UpdateAll(\$_IPS['TARGET']);"); 
        
    }

    public function Destroy(){
        //Never delete this line!
        parent::Destroy();
        
    }

    public function ApplyChanges(){
        //Never delete this line!
        parent::ApplyChanges();
                    
        $this->SetStatus($this->ComputeState());
        
        $this->UpdateTimer();
    }
    
    public function UpdateAll() {
        if ($this->ComputeState() != 102){
            echo "Energy Lights are in an illegal state and cannot update. Open Energy Lights for more information.";
        } else {        
            $this->Update(0);
            $this->Update(1);
            $this->Update(2);
        }
        
        $this->UpdateTimer();
    }
    
    public function UpdateYear() {
        $this->Update(0);
    }
    
    public function UpdateMonth() {
        $this->Update(1);
    }
    
    public function UpdateWeek() {
        $this->Update(2);
    }
    
    private function Update($scope) { //scope: 0->year, 1->month, 2->week
        if (($scope != 0) && ($scope != 1) && ($scope != 2)){
            throw new Exception("Invalid scope in Update");
        }
        $prefix = ($scope == 0) ? "Year" : (($scope == 1) ? "Month" : "Week");
        
        $tendency = $this->GetTendency($scope);
        if ($tendency != -1){
            SetValue($this->GetIDForIdent($prefix . "Tendency"), $tendency);
        }
        SetValue($this->GetIDForIdent($prefix . "Production"), $this->GetAggregated($this->ReadPropertyInteger("ProductionVariableID"), $scope));
        SetValue($this->GetIDForIdent($prefix . "ProductionPrice"), GetValue($this->GetIDForIdent($prefix . "Production")) * $this->ReadPropertyFloat("PriceProduce") * 0.01);
        SetValue($this->GetIDForIdent($prefix . "Consumption"), $this->GetAggregated($this->ReadPropertyInteger("ConsumptionVariableID"), $scope));
        SetValue($this->GetIDForIdent($prefix . "ConsumptionPrice"), GetValue($this->GetIDForIdent($prefix . "Consumption")) * $this->ReadPropertyFloat("PriceConsume") * 0.01);
        SetValue($this->GetIDForIdent($prefix . "Total"), GetValue($this->GetIDForIdent($prefix . "ProductionPrice")) - GetValue($this->GetIDForIdent($prefix . "ConsumptionPrice")));
    }
    
    private function GetTendency($scope) { //scope: 0->year, 1->month, 2->week
        
        $noData = true;
        
        $startMonth = intval(date("n", $this->GetStartDate()));
        $currentMonth = intval(date("n", time()));
        $previousMonth = ((10 + $currentMonth) % 12) + 1;
        $maxOffset = $previousMonth - $startMonth;
        if ($maxOffset < 0){
            $maxOffset = $maxOffset + 12;
        }
        
        $this->SendDebug("startMonth = " . $startMonth, "", 0);
        $this->SendDebug("currentMonth = " . $currentMonth, "", 0);
        $this->SendDebug("previousMonth = " . $previousMonth, "", 0);
        $this->SendDebug("maxOffset = " . $maxOffset, "", 0);
        
        $expectedConsumption = $this->ReadPropertyInteger("ExpectedConsumption");
        if ($this->ReadPropertyBoolean("PreviousYear")){
            $expectedConsumption = 0;
            if ($this->ReadPropertyInteger("ConsumptionVariableID") != 0){
                $startDatePrevious = mktime(0, 0, 0, $this->ReadPropertyInteger("Startmonth"), 1, (intval(date("n", time())) >= $this->ReadPropertyInteger("Startmonth")) ? intval(date("Y", time())) - 1 : intval(date("Y", time())) - 2);
                $values = AC_GetAggregatedValues($this->ReadPropertyInteger("ArchiveControlID"), $this->ReadPropertyInteger("ConsumptionVariableID"), 3, $startDatePrevious, $this->GetStartDate(), 0);
                foreach ($values as $value){
                    $expectedConsumption += $value["Avg"];
                }
            }
        }
        
        $this->SendDebug("expectedConsumption = " . $expectedConsumption, "", 0);
        $totalPlanned = 0.0;
        if ($scope == 0){
            //Previous months
            for ($offset = 0; $offset <= $maxOffset; $offset++){
                $month = ($startMonth + $offset - 1) % 12 + 1; //startMonth + offset
                $consumptionMonth = ($expectedConsumption * json_decode($this->ReadPropertyString("ConsumptionPerMonth"))[$month-1]->consumption * 0.01);
        
                $this->SendDebug("month = " . $month, "", 0);
                $this->SendDebug("consumptionMonth = " . $consumptionMonth, "", 0);
        
                $totalPlanned += $consumptionMonth;
                $noData = false;
            }
        }
        if (($scope == 0) || ($scope == 1)){
            //Current month
            $secondsCurrentMonthTotal = 60*60*24*intval(date("t", time()));  //We do not consider daylight saving time here
            $secondsCurrentMonthUntilNow = (intval(date("j", time())) - 1) * 60*60*24 + intval(date("G", time())) * 60 * 60 + intval(date("i",time())) * 60 + intval(date("s", time()));
            $totalPlanned += (json_decode($this->ReadPropertyString("ConsumptionPerMonth"))[$currentMonth-1]->consumption * $expectedConsumption * 0.01) * 
                        $secondsCurrentMonthUntilNow / $secondsCurrentMonthTotal;
            if ($secondsCurrentMonthUntilNow > 0){
                $noData = false;
            }
        }
        if ($scope == 2){
            //Split current week into current and previous month
            $daysThisMonth = ((intval(date("w", time())) + 6) % 7) + 1;  //initially all days this week, will be adjusted in coming if
            $daysPreviousMonth = 0;
            if ($daysThisMonth > intval(date("d", time()))){
                $daysPreviousMonth = $daysThisMonth - intval(date("d", time()));
                $daysThisMonth -= $daysPreviousMonth;
                $noData = false;
            }
            //Previous month 
            //No need to worry about days of month only considering current year as days of month are only affected by year in February (and then the current March is in the same year)
            $totalPlanned += (($expectedConsumption * $daysPreviousMonth * json_decode($this->ReadPropertyString("ConsumptionPerMonth"))[$previousMonth-1]->consumption * 0.01) / intval(date("t", mktime(0,0,0, $previousMonth, 1, intval(date("Y", time())))))); 
            //Current month
            $secondsCurrentWeekThisMonth = ($daysThisMonth - 1) * (60 * 60 * 24) + intval(date("G", time())) * 60 * 60 + intval(date("i",time())) * 60 + intval(date("s", time()));
            $totalPlanned += (($expectedConsumption * $secondsCurrentWeekThisMonth * json_decode($this->ReadPropertyString("ConsumptionPerMonth"))[$currentMonth-1]->consumption * 0.01) / (intval(date("t", mktime(0,0,0, $currentMonth, 1, intval(date("Y", time()))))) * 60 * 60 * 24)); 
            if ($secondsCurrentWeekThisMonth > 0){
                $noData = false;
            }
        }
        $totalActual = $this->GetAggregated($this->ReadPropertyInteger("ConsumptionVariableID"), $scope);
        
        $this->SendDebug("scope = " . $scope, "", 0);
        $this->SendDebug("totalActual = " . $totalActual, "", 0);
        $this->SendDebug("totalPlanned = " . $totalPlanned, "", 0);
        
        if ($noData){
            return -1;
        }
        
        if ($totalPlanned == 0) {
            if ($totalActual == 0){
                return 100;   //Goal is met, you wanted to spend no energy and spent none
            } else {
                return 99999; //You wanted to spend no energy, but you spent some :(
            }
        }        
        
        return 100 * $totalActual/$totalPlanned;
    }
    
    private function GetAggregated($variable, $scope) { //scope: 0->year, 1->month, 2->week
        if (!IPS_VariableExists($variable)){
            return 0;
        }
        if ($scope == 0){
            $total = 0.0;
            $values = AC_GetAggregatedValues($this->ReadPropertyInteger("ArchiveControlID") , $variable, 3, $this->GetStartDate(), time(), 0);
            foreach($values as $value) {
                $total += $value["Avg"];
            }
            return $total;
        } else if ($scope == 1){
            $firstOfMonth = mktime(0, 0, 0, intval(date("n", time())), 1, intval(date("Y", time())));
            $values = AC_GetAggregatedValues($this->ReadPropertyInteger("ArchiveControlID") , $variable, 3, $firstOfMonth, time(), 0);
            if (count($values) > 0){
                return $values[0]["Avg"];
            } else {
                return 0;
            }
        } else if ($scope == 2){
            $weekday = ((intval(date("w", time())) + 6) % 7); // $weekday with 0 = monday, ..., 6 = sunday
            $yearBeginningOfWeek = intval(date("Y", time())); // Initialized with current year, but updated later on
            $monthBeginningOfWeek = intval(date("n", time())); // Initialized with current month, but updated later on
            $dayBeginningOfWeek = intval(date("j", time())) - $weekday;
            if ($dayBeginningOfWeek < 1){ // Beginning of week is in previous month
                if (date("n", time()) == "1"){ //Current month is January
                    $yearBeginningOfWeek = $yearBeginningOfWeek - 1;
                }
                $monthBeginningOfWeek = ($monthBeginningOfWeek + 10) % 12 + 1;
                $dayBeforeSevenDays += intval(date("t", mktime(0, 0, 0, $monthBeginningOfWeek, 1, $yearBeginningOfWeek)));
            }
            $values = AC_GetAggregatedValues($this->ReadPropertyInteger("ArchiveControlID") , $variable, 2, mktime(0, 0, 0, $monthBeginningOfWeek, $dayBeginningOfWeek, $yearBeginningOfWeek), time(), 0);
            if (count($values) > 0){
                return $values[0]["Avg"];
            } else {
                return 0;
            }
        } else {
            throw new Exception("GetAggregated used with illegal scope");
        }
        
    }
    
    private function ComputeState(){
        $state = 102;
        
        if (($this->ReadPropertyBoolean("PreviousYear")) && ($this->ReadPropertyInteger("ConsumptionVariableID") == 0)){
            $state = 201;
        }
        
        $totalvalue = 0;
        foreach (json_decode($this->ReadPropertyString("ConsumptionPerMonth")) as $monthEntry) {
            if ($monthEntry->consumption < 0) {
                $state = 204;
            }
            $totalvalue += $monthEntry->consumption;
        }
                
        if ($totalvalue != 100 && ($state < 202)) {
            $state = ($totalvalue < 100) ? 202 : 203;
        }
        
        return $state;
    }
    
    private function UpdateTimer(){
        $this->SetTimerInterval("UpdateTimer", 60 * 60 * 1000 - intval(date("i",time())) * 60 * 1000 - intval(date("s", time())) * 1000);
    }
    
    private function GetStartDate(){
        $startYear = intval(date("Y", time()));
        if (intval(date("n", time())) < $this->ReadPropertyInteger("Startmonth")) {
            $startYear = intval(date("Y", time())) - 1;
        }
        return mktime(0, 0, 0, $this->ReadPropertyInteger("Startmonth"), 1, $startYear);
    }
}

?>