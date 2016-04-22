<?
class Rent extends ActiveRecord\Model {
    
    public static function find_num_rows() {
        return self::find('all', array('select' => 'count(*) AS num_rows'));
    }
    
    public function toArray() {
                
            $pointDB = null; //Point::find($this->idpoints);
            $shiftDB = null; //Shift::find($this->idshift);
            $inentoryDB = null;
            $clientDB = null;
            
            if ( !is_null($this->idpoints) ) 
                $pointDB = Point::find($this->idpoints);    
            
            if ( !is_null($this->idshift) ) 
                $shiftDB = Shift::find($this->idshift);

            if ( !is_null($this->idinventory) )
                $inentoryDB = Inventories::find($this->idinventory);
            
            if ( !is_null($this->idclient) ) {
                //echo "<br><br>CLIENT<br><br>";                    
                $clientDB = Client::find($this->idclient);
                //var_dump($clientDB);
            } 
                                  
            
            $date = 0;
            
            $resultArr = array( 'isCompleted'=>$this->completed, 'serverId'=>$this->serverid );
            
            if ( !is_null($pointDB) ) 
                $resultArr['point'] = $pointDB->toArray();                 
            
            if ( !is_null($shiftDB) ) 
                $resultArr['shift'] = $shiftDB->toArray();
            
            if ( !is_null($inentoryDB) ) 
                $resultArr['inventory'] = $inentoryDB->toArray();                                 
            
            if ( !is_null($clientDB) ) 
                $resultArr['client'] = $clientDB->toArray();
                                                    
            return $resultArr;
            
        }

    
    public static function allInArray($parametrs) {
    
            $rentsDB = Rent::all( $parametrs );
            
            $rentsInArr = array();
            foreach ( $rentsDB as $rent) {
                array_push($rentsInArr, $rent->toArray());
            }
            
            return $rentsInArr; 
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
        $attributes['serverId'] = Rent::getGUID();         
        return parent::create($attributes);
    }
    
    
};
?>