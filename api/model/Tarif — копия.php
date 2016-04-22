<?
class Tarif extends ActiveRecord\Model {
    
    public function toArray() {
            return array( 'name'=>$this->name, 
                                                'sumHour'=>$this->sum_per_hour,
                                                'sumDay'=>$this->sum_day,
                                                'sumTsHour'=>$this->sum_ts_hour );
        }
    
    
};
?>