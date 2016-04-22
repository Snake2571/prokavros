<?
    class Breakdown extends ActiveRecord\Model {
        static $table_name = 'breakdown';
        
        public function toArray() {
                
            $discription = mb_convert_encoding($this->description, 'UTF-8', 'WINDOWS-1251');                
            return array('description'=>$discription , 'summ'=>$this->summ, 'code'=>$this->code);
        }
        
        public function createJsonArray() {
            return array('description'=>$this->description, 'summ'=>$this->summ, 'code'=>$this->code);
        }
        
    };
?>