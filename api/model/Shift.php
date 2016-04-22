<?
    class Shift extends ActiveRecord\Model {
        
        public function toArray() {
                
            $adminDB = Admin::find($this->idadmin);
            $pointDB = Point::find($this->idpoint);

            $date = 0;
            
            /*if (!is_null($this->shiftdate)) {
                $date = $this->shiftdate->format('Y-m-d');
            } else if (!is_null($this->shiftDate)) {
                $date = $this->shiftDate->format('Y-m-d');
            }*/                
                
            $jsonArr = array(
                //'admin'=>$adminDB->toArray(),
                'shiftData'=>$date,
                'serverId'=>$this->id );                             
            
            if (!is_null($pointDB) && !is_null($this->idpoint)) {
                $jsonArr['point'] = $pointDB->toArray();
            }
            
            return $jsonArr;
            
        }
        
        public static function findOrCreateByDate($strDate) {
            $shift = null;                           
           $time = strtotime($strDate);
           $year = date("Y", $time);
           
           if ($year != 1970) {
             $date = date("Y-m-d", $time);             
             $shift = Shift::find(array('shiftDate'=>$date));  
            
            if ( is_null($shift) ) {
                $shift = Shift::create(array('shiftDate'=>$date));                                        
            }
           
           }
           
           return $shift;
        }
        
        public static function allInArray($parametrs) {
            $shiftsDB = Shift::all( $parametrs );
            
            $shiftsInArr = array();
            foreach ( $shiftsDB as $shiftDB ) {
                array_push($shiftsInArr, $shiftDB->toArray());
            }
            
            return $shiftsInArr; 
        }
        
        public static function allInJson($parametrs) {
            return json_encode(Shift::allInArray($parametrs));
        }
        
    };
?>