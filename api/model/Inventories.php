<?
    class Inventories extends ActiveRecord\Model {
            
        public function toArray() {

            $pointDB = Point::find(array('id'=>$this->idpoint));
            $tarifDB = Tarif::find(array('id'=>$this->idtarif));
            
            //echo "<br><br><br>".$this->idtarif."<br><br><br>";
            
            //echo "<br><br><br>";
            //var_dump($tarifDB);
            //echo "<br><br><br>";               
            
            $inventoryModel = mb_convert_encoding($this->model, 'UTF-8', 'WINDOWS-1251');                
            
            $result = array( 'serverId'=>$this->id, 
                                                //'point'=>$jsonPointItemArr,
                                                //'tarif'=>$jsonTarifItemArr,
                                                'idParent'=>$this->idparent,
                                                'points'=>$this->getPointArr(), 
                                                'model'=>$inventoryModel,
                                                //'countRents'=>count($rents),
                                                'count_rent'=>$this->count_rent,
                                                'idGroup'=>$this->idgroup,
                                                'number'=>$this->number,
                                                'numberFrame'=>$this->numberframe,
                                                'state'=>$this->state );
            
            if (!is_null($pointDB)) {
                $result['point'] = $pointDB->toArray();
            }
            
            if (!is_null($tarifDB)) {
                
                //echo "<br><br><br>";
                //var_dump($tarifDB);
                //echo "<br><br><br>";                    
                    
                $result['tarif'] = $tarifDB->toArray();
            }
            
            
            return $result;
        }            
        
        public function getPointArr() {
                
            $point = Point::find(array('id'=>$this->idpoint));
            
            if (!is_null($point)) {
                //var_dump($point);    
                $title = mb_convert_encoding($point->title,  'UTF-8','WINDOWS-1251');                
                $address = mb_convert_encoding($point->address,  'UTF-8','WINDOWS-1251');                  
                return array('title'=>$title, 'address'=>$address);
            }            

            return null;                        
        }        
        
        public static function updateViaJson($json) {
                            
            $inventoryDecode = json_decode($json);

            $inventory = Inventories::find($inventoryDecode->serverId);
            //$inventory->avatar = $inventoryDecode->avatar;
            $inventory->state = $inventoryDecode->state;  
            $inventory->save();
        }    
    };
?>