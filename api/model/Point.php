<?
    class Point extends ActiveRecord\Model {
        
        public function toArray() {
            return array( "title"=>$this->title,
                            "address"=>$this->address );
        }
        
        
    };
?>