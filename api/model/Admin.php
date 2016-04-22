<?
    class Admin extends ActiveRecord\Model {
        
        public function toArray() {
            return array( "name"=>$this->name,
                            "email"=>$this->email,
                            "login"=>$this->login,
                            "pass"=>$this->pass );
        }
        
    };
?>