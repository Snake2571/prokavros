<?

class Client extends ActiveRecord\Model {


    public function toArray() {
        
        
        //return json_decode($this->to_json(), TRUE);
          //$this->name = "Name_h";
         return array( 'serverId'=>$this->id,
                                                'surname'=>$this->surname, 
                                                'name'=>$this->name,
                                                'countRents'=>$this->getCountRents(), 
                                                'summ'=>$this->getSummOfRents(),
                                                'phone'=>$this->phone,
                                                'blackList'=>$this->blacklist,
                                                'sex'=>$this->sex,
                                                'avatar'=>$this->avatar,
                                                'summ'=>$this->summ,
                                                'vipNumber'=>$this->vipnumber );
        
    }
    
    public function toJson() {
        
        
        //return json_decode($this->to_json(), TRUE);

        //print_r($this->toArray());          
        return json_encode($this->toArray());
        
    }
    
        
    public static function createOrUpdate($phone, $name) {
            
        $client = Client::find(array('phone'=>$phone));
        
        if ( is_null($client) ) {
            $client = Client(array('phone'=>$phone, 'name'=>$name));    
        }
        
        return $client;
    }
    
    public function getCountRents() {
            
        $rentsArr = Rent::all(array('idClient'=>$this->id));
        
        return count($rentsArr);
    }
    
    public function getSummOfRents() {
        /*$rentsArr = Rent::all(array('idClient'=>$this->id));
        $summ = 0;
        
        foreach ($rentsArr as $rent) {
            $inventory = Inventories::find(array('id'=>$rent->idinventory));
            $tarif = Tarif::find(array('id'=>$inventory->idtarif));
            //$summ = $summ + $tarif->sum_day; 
        } */
        $summ = 999;                
        return $summ;
    }
    
    
    public function initViaJson($json) {
        $client = json_decode($json);
        
        
        
        $this->name = $client->name;
        $this->surname = $client->surname;
        $this->phone = $client->phone; 
        $this->avatar = $client->avatar;
        //$this->save();
    }
    
    public static function getGUID(){
        if (function_exists('com_create_guid')){
            return ltrim(com_create_guid(),'{}');
        }else{
            mt_srand((double)microtime()*10000);//optional for php 4.2.0 and up.
            $charid = strtoupper(md5(uniqid(rand(), true)));
            $hyphen = chr(45);// "-"
            $uuid = chr(123)// "{"
                .substr($charid, 0, 8).$hyphen
                .substr($charid, 8, 4).$hyphen
                .substr($charid,12, 4).$hyphen
                .substr($charid,16, 4).$hyphen
                .substr($charid,20,12)
                .chr(125);// "}"
            return ltrim($uuid,'{}');
        }
    }
    
    public static function create($attributes, $validate=true) {
        $attributes['serverId'] = Client::getGUID();         
        return parent::create($attributes);
    }
};

?>