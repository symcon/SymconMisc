<?

//Constants will be defined with IP-Symcon 5.0 and newer
if(!defined('IPS_KERNELMESSAGE')) {
    define('IPS_KERNELMESSAGE', 10100);
}
if(!defined('KR_READY')) {
    define('KR_READY', 10103);
}

class WebHookModule extends IPSModule {

    private $hook = "";

    public function __construct($InstanceID, $hook) {

        parent::__construct($InstanceID);

        $this->hook = $hook;

    }

    public function Create() {

        //Never delete this line!
        parent::Create();

        //We need to call the RegisterHook function on Kernel READY
        $this->RegisterMessage(0, IPS_KERNELMESSAGE);

    }

    public function MessageSink($TimeStamp, $SenderID, $Message, $Data) {

        //Never delete this line!
        parent::MessageSink($TimeStamp, $SenderID, $Message, $Data);

        if($Message == IPS_KERNELMESSAGE && $Data[0] == KR_READY) {
            $this->RegisterHook("/hook/" . $this->hook);
        }

    }

    public function ApplyChanges() {

        //Never delete this line!
        parent::ApplyChanges();

        //Only call this in READY state. On startup the WebHook instance might not be available yet
        if(IPS_GetKernelRunlevel() == KR_READY) {
            $this->RegisterHook("/hook/" . $this->hook);
        }

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

        $this->SendDebug("WebHook", "Array POST: " . print_r($_POST, true), 0);

    }

}

?>
