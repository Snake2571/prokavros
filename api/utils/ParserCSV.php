<?

//ini_set("display_errors", "1");
//error_reporting(E_ALL);




class ParserCSV {
    
    /*fileName = 'tarifs.csv';
        $csvData = file_get_contents($fileName);
        $lines = explode(PHP_EOL, $csvData);
        $array = array();
        
        foreach ($lines as $line) {
            $array[] = str_getcsv($line);
        }*/
    
    
    public function rents() {
        $fileName = 'parse/all_rent_6.csv';
        $csvData = file_get_contents($fileName);
        $lines = explode(PHP_EOL, $csvData);
        
        $array = array();
        
        foreach ($lines as $line) {
            $array[] = str_getcsv($line,";");
        }
        
        return $array;
    }
    
    
    public function admins() {
        $fileName = 'parse/admins.csv';
        $csvData = file_get_contents($fileName);
        $lines = explode(PHP_EOL, $csvData);
        
        $array = array();
        
        foreach ($lines as $line) {
            $array[] = str_getcsv($line);
        }
        
        return $array;
        
        
    }
    
    public function tarifs() {
        
        $fileName = 'parse/.csv';
        $csvData = file_get_contents($fileName);
        $lines = explode(PHP_EOL, $csvData);
        $array = array();
        
        foreach ($lines as $line) {
            $array[] = str_getcsv($line);
        }
        
        return $array;
    }
    
    public function clients() {
        
        $fileName = 'parse/bl.csv';
        $csvData = file_get_contents($fileName);
        $lines = explode(PHP_EOL, $csvData);
        $array = array();
        
        foreach ($lines as $line) {
            //$array[] = str_getcsv($line);
            $array[] = str_getcsv($line,";");
        }
        return $array;
    }
    
    public function clientsVip() {
        
        $fileName = 'parse/vip2.csv';
        $csvData = file_get_contents($fileName);
        $lines = explode(PHP_EOL, $csvData);
        $array = array();
        
        foreach ($lines as $line) {
            //$array[] = str_getcsv($line);
            $array[] = str_getcsv($line,";");
        }
        return $array;
    }

    public function inventory() {
        $fileName = 'parse/daughterCSV.csv';
        $csvData = file_get_contents($fileName);
        $lines = explode(PHP_EOL, $csvData);
        $array = array();
        
        $Data = str_getcsv($csvData, "\n"); //parse the rows 
        
        foreach($Data as &$Row) {
            $Row = str_getcsv($Row, ";");
            /*echo "<br><br>";
            echo $Row[0]." ".$Row[1];
            echo "<br><br>";*/
            $array[] = $Row;
        } 
             
        
              
        
        
        //foreach ($lines as $line) {
            //$array[] = str_getcsv($line);
        //}
        return $array;
    }
    
    
    public function inventoryMain() {
        $fileName = 'parse/InventoryMain.csv';
        $csvData = file_get_contents($fileName);
        $lines = explode(PHP_EOL, $csvData);
        $array = array();
        
        foreach ($lines as $line) {
            $array[] = str_getcsv($line);
        }
        return $array;
    }
    
    
    public function inventoryWithNotMark() {
        $fileName = 'parse/InventoryNotMark.csv';
        $csvData = file_get_contents($fileName);
        $lines = explode(PHP_EOL, $csvData);
        $array = array();
        
        foreach ($lines as $line) {
            $array[] = str_getcsv($line);
        }
        return $array;
    }
    
    
    

    
    public function shifts() {
        
        
        
        $fileName = 'parse/all_base_redact_p20.csv';
        
        $csvData = file_get_contents($fileName);
        
        $lines = explode(PHP_EOL, $csvData);
        
        $array = array();
        
        
        //print_r($expression);
        
        foreach ($lines as $line) {
            //echo "1";    
            try {
                //echo $line;                    
                //echo str_getcsv($line);
                $array[] = str_getcsv($line);    
            } catch (\exception $e) {
                echo "Exception";
            }
            
        }
        
        
        
        return $array;
    }
    



}

?>