<?
    ini_set("display_errors", "1");
    error_reporting(E_ALL);
    require_once 'php-activerecord/ActiveRecord.php';
    require_once 'model/tables/RentTable.php';

    class ManagerDB {

        function __construct() {

            $this->exhibitSettings();

            ActiveRecord\Config::initialize(function($cfg){
                $cfg->set_model_directory('model');
                $cfg->set_connections(array('development' => 'mysql://prokatvros_debug:debug2015@localhost/prokatvros_debug'));
            });
        }

        private function exhibitSettings() {
            ini_set("display_errors", "1");
            error_reporting(E_ALL);
        }

        public function checkAuthData($login, $pass) {
            $admin = Admin::find( array('login'=>$login, 'pass'=>$pass) );
            return !is_null($admin);
        }

        public function authAdmin($login, $pass, $regId) {
            $admin = Admin::find( array('login'=>$login, 'pass'=>$pass) );

            if (is_null($admin)) {
                echo json_encode( array('Error'=>'Invalid login or password') );
                return;
            }
            $pointcontrArr  = Pointcontract::all( array('idAdmin'=>$admin->id) );
            $optionsPoints  = array();
            $resultPointArr = array();
            if (count($pointcontrArr) != 0) {
                // echo json_encode(array('Error'=>'Administrator not attached not to one point'));
                // return;
                foreach ($pointcontrArr as $point) {
                    array_push($optionsPoints, $point->idpoint);
                }
                foreach ($pointsArr as $point) {
                    array_push( $resultPointArr, array('id'=>$point->id, 'title'=>$point->title, 'address'=>$point->address) );
                }
                $pointsArr = Point::find($optionsPoints);
            }
            $resultArr = array( 'serverId'=>$admin->id,
                                'name'=>$admin->name,
                                'email'=>$admin->email );
            $resultArr['points'] = $resultPointArr;
            echo json_encode($resultArr);
        }

        public function reqUpdateShift($jsonShift) {
            $shiftDB = Shift::find($jsonShift->serverId);
            $shiftDB->state = $jsonShift->state;
            $shiftDB->save();
            echo json_encode( array('message'=>'success', 'error_code'=>'0') );
        }

        public function reqAcceptShift($idPoint, $idAdmin) {
            $accept_date = date("d-m-Y H:m:s", time());

            $shift = Shift::create(array('idAdmin'=>$idAdmin, 'idPoint'=>$idPoint, 'accept_date'=>$accept_date));

            if ( !is_null($shift) )
                echo json_encode( array( 'accept_date'=>$accept_date ) );
            else
                echo json_encode( array( 'error'=>'Unable to make a record' ) );
        }

        public function reqGetClients($phone) {
            $phone = str_replace(' ', '', $phone);

            $clientArr = Client::find('all', array('conditions' => "phone LIKE '%$phone%'"));//find( array('phone'=>$phone) );

            if ( !is_null($clientArr) ) {

                $resultArr = array();

                foreach ($clientArr as $client) {

                   $clientName = mb_convert_encoding($client->name, 'UTF-8', 'WINDOWS-1251');

                   $rentArr = Rent::all( array('idClient'=>$client->id) );

                    $resultClientArr = array('id'=>$client->id, 'name'=>$clientName, 'phone'=>$client->phone, 'sex'=>$client->sex);
                    $resultRentArr = array();

                    foreach ( $rentArr as $rent ) {
                        $completed = $this->toBooleanType( $rent->completed );
                        array_push( $resultRentArr, array('id'=>$rent->id, 'completed'=>$completed) );
                    }

                    $resultClientArr['rents'] = $resultRentArr;
                   array_push($resultArr, $resultClientArr);

                }

                $jsonString = json_encode(array('client'=>$resultArr),JSON_UNESCAPED_UNICODE);

                echo mb_convert_encoding($jsonString, 'WINDOWS-1251', 'UTF-8');
                //echo json_encode(array('client'=>$resultArr));
            } else {
                echo json_encode(array('error'=>'Not found client by number'));
            }
        }

        public function reqGetInventory($idPoint) {

            $inventoriesArr = null;

            if ( is_null($idPoint) ) {
                //$inventoriesArr = Inventories::all();
                $inventoriesArr = Inventories::find('all', array('order' => 'idParent desc'));
            } else {
                $inventoriesArr = Inventories::all( array('idPoint'=>$idPoint) );
            }

            $resultArr = array();

            foreach ($inventoriesArr as $inventory) {

                $rent = Rent::last( array('idInventory'=>$inventory->id) );
                $rentsResultArr = array();

                $comleted = null;
                $rentId = -1;

                if ( !is_null($rent) && !is_null($rent->completed) ) {
                    $comleted = $this->toBooleanType($rent->completed);
                    $rentId = $rent->id;
                }

                //$inventory->model = mb_convert_encoding($inventory->model, 'UTF-8', 'WINDOWS-1251');

                //array_push( $rentsResultArr,  $inventory->toArray() );
                //array_push($resultArr, array('id'=>$inventory->id, 'model'=>$inventory->model, 'number'=>$inventory->number, 'rents'=>$rentsResultArr) );

                $arr = $inventory->toArray();
                array_push($resultArr, $arr );


                //print_r($arr['tarif']);
            }

            $jsonString = json_encode(array('results'=>$resultArr));
            echo $jsonString;
        }

        public function reqGetShifts($idAdmin) {

            echo Shift::allInJson( array('idAdmin'=>$idAdmin, 'state'=>1) );
        }

        public function reqAddRentItem($idPoint, $jsonRent) {
            //$jsonRent = '{"client":{"name":"vasa","phone":"9999","surname":"po","serverId":1},"token":"8645960203193091442920487271","inventory":{"model":"Лонгборд","number":"500","serverId":"1","countRents":0},"isCompleted":0,"endTime":1443006912516,"serverId":0}';
            $rent = json_decode($jsonRent, TRUE);
            //print_r($rent);
            //echo $jsonRent;

            if( !is_null( $rent )
                        && !is_null( $rent['client'] )
                        // && !is_null( $rent['administrator'] )
                        && !is_null( $rent['inventory'] ) ) {

                    $client = Client::createOrUpdate( $rent['client']['phone'],
                                                        $rent['client']['name'] );

                    $inventoryDB = Inventories::find( array('number'=>$rent['inventory']['number']) );
                    $inventoryDB->state = $rent['inventory']['state'];
                    $inventoryDB->save();

                    $paramsNewRent = array(
                                   'end'=>$rent['endTime'],
                                   'token'=>$rent['token'],
                                   'completed'=>$rent['isCompleted'],
                                   'idClient'=>$client->id,
                                   'idInventory'=>$rent['inventory']['serverId'] );


                    if ( isset($rent['inventoryAddition']) )
                        $paramsNewRent['inventoryAddition'] = $rent['inventoryAddition']['serverId'];

                    $rent = Rent::create($paramsNewRent);
                    echo json_encode($rent->toArray());
                    //var_dump($rent);

               } else {
                    //echo "Error: Invalid json structure";
                }
        }

        public function reqUpdateRentItem($idPoint, $jsonRent) {

            $error_code = 0;
            $message = "Update data was successfull";
            $rent = json_decode($jsonRent, TRUE);

            //echo "<br><br>";
            //print_r($rent);
            //echo $rent['serverId'];
            //echo "<br><br>";

            $rentDB = Rent::find(array('serverId'=>$rent['serverId']));

            if ( !is_null($rentDB) ) {
                $rentDB->completed = $rent['isCompleted'];

                if (isset($rent['breakdown'])) {
                    $breakdown = Breakdown::find( array('code'=>$rent['breakdown']['code']) );
                    if ( !is_null($breakdown) ) {
                        $rentDB->idbreakdown = $breakdown->id;
                    }
                }


                //echo "<br><br>";
                //echo $rent['client']['summ'];
                //echo "<br><br>";

                if ( isset($rent['client']) ) {
                    $clientDB = Client::find(array('serverId'=>$rent['client']['serverId']));
                    $clientDB->summ = $rent['client']['summ'];
                    $clientDB->save();
                }

                $inventoryDB = Inventories::find(array('number'=>$rent['inventory']['number']));
                $inventoryDB->count_rent = $rent['inventory']['count_rent'];
                $inventoryDB->state = $rent['inventory']['state'];
                $inventoryDB->save();

                $rentDB->save();
            } else {
                $error_code = 1;
                $message = "Not found rent for update";
            }

            echo json_encode( array( 'message'=>$message, 'error_code'=>$error_code ) );
        }

        public function reqAddClientItem($jsonClient) {

            $message = "Add was successfull";

            $error_copy_user = 1;
            $error_no_error = 0;

            $error_code = $error_no_error;

            $client = json_decode($jsonClient, TRUE);

            //if ( !is_null($client["countRents"]) )
                //unset($client["countRents"]);

            if ( !is_null($client["summ"]) )
                unset($client["summ"]);

            $clientDB = Client::find(array('phone'=>$client["phone"]));

            if ($clientDB != null) {
                $error_code = $error_copy_user;
                $message = "Already have user with this phone";
            } else {
                Client::create($client);
            }


            echo json_encode(array('message'=>$message, 'error_code'=>$error_code));
        }

        public function reqRedactClientItem($id, $jsonClient) {



            $message = "Add was successfull";

            $error_not_found_user = 1;
            $error_can_not_put_data = 2;
            $error_no_error = 0;

            $error_code = $error_no_error;

            //$client = json_decode($jsonClient, TRUE);

            $clientDB = Client::find( array('serverId'=>$id) );

            if ($clientDB != null) {

                $clientDB->initViaJson($jsonClient);
                try {
                    $clientDB->save();
                } catch(Exception $e) {
                    $message = $e->getMessage();
                    $error_code = $error_can_not_put_data;
                }

            } else {
                $error_code = $error_not_found_user;
                $message = "Not found user";
            }


            echo json_encode(array('message'=>$message, 'error_code'=>$error_code));
        }

        public function reqAddRents($idPoint, $jsonListRents) {
            $rentsArr = json_decode($jsonListRents, TRUE);

            $jsonLastError = json_last_error();

            if ($jsonLastError != JSON_ERROR_NONE) {
                showJsonError($jsonLastError);
                echo "<br><br>".$rentsJsonString."<br><br>";
                return;
            }

             foreach ($rentsArr['rents'] as $rent) {

                  if( !is_null( $rent )
                        && !is_null( $rent['client'] )
                        && !is_null( $rent['administrator'] )
                        && !is_null( $rent['inventory'] ) ) {

                    $client = Client::create( array( 'guid'=>$rent['client']['id'],
                                        'name'=>$rent['client']['name'],
                                        'phone'=>$rent['client']['phone'],
                                        'sex'=>$rent['client']['sex'] ) );

                    $rent = Rent::create( array( 'id'=>$rent['id'],
                                   'start'=>$rent['start'],
                                   'end'=>$rent['end'],
                                   'note'=>$rent['note'],
                                   'idAdmin'=>$rent['administrator']['id'],
                                   'idClient'=>$rent['client']['id'],
                                   'idInventory'=>$rent['inventory']['id'] ) );

                    var_dump($rent);

               } else {
                    echo "Error: Invalid json structure";
                }
            }
        }

        public function reqGetAllRentsArr() {

            $rentsDBArr = Rent::all(array('limit' => 10000));
            //$rentsArr2 = Rent::all(array('completed'=>0));
            //echo count($rentsArr2);

            $rents = $this->buildRents($rentsDBArr);

            return $rents;
        }

        public function reqGetAllRents() {

            $rentsArr = Rent::all(array('limit' => 50));
            $rentsArr2 = Rent::all(array('completed'=>0));
            //echo count($rentsArr2);
            $resultArr = array();

            foreach ($rentsArr as $rent) {
                array_push($resultArr, $rent->toArray());
            }

            /*foreach ($rentsArr2 as $rent) {
                array_push($resultArr, $rent->toArray());
            }*/

            echo json_encode(array('results'=>$resultArr, 'message'=>'success', 'error_code'=>0));




            /*foreach($rentsArr as $rent) {

                $admin = Admin::find( array('id'=>$rent->idadmin) );
                $client = Client::find( array('id'=>$rent->idclient) );
                $inventory = Inventories::find( array('idRents'=>$rent->id) );

                $tarif = Tarif::find( array('id'=>$inventory->idtarif) );

                $resultTarifArr = array('id'=>$tarif->id, 'sum_per_hour'=>$tarif->sum_per_hour);

                $resultAdminArr = array('id'=>$admin->id, 'name'=>$admin->name, 'email'=>$admin->email);
                $resultClientArr = array('id'=>$client->id, 'name'=>$client->name, "phone"=>$client->phone, "sex"=>$client->sex);
                $resultInventoryArr = array('id'=>$inventory->id, 'model'=>$inventory->model, 'number'=>$inventory->number, 'tarif'=>$resultTarifArr);

                $startDate = $rent->start;//date_format($rent->start, 'Y-m-d H:i:s');
                $endDate =  $rent->end; //date_format($rent->end, 'Y-m-d H:i:s');

                if(!is_null($rent->start)) {
                    $startDate = date_format($rent->start, 'Y-m-d H:i:s');
                }

                if (!is_null($rent->end)) {
                    $endDate = date_format($rent->end, 'Y-m-d H:i:s');
                }

                array_push( $resultArr, array( 'id'=>$rent->guid, 'start'=>$startDate, 'end'=>$endDate, 'note'=>$rent->note, 'administrator'=>$resultAdminArr, 'client'=>$resultClientArr, 'inventory'=>$resultInventoryArr ) );
            }

            echo json_encode(array("modified"=>"2015-11-23 22:23:00", "rents"=>$resultArr)); */
        }

        public function reqGetRents($idPoins, $date) {

            $rentsArr = Rent::all( array('idPoints'=>$idPoins) );
            $resultArr = array();

            foreach($rentsArr as $rent) {

                $admin = Admin::find( array('id'=>$rent->idadmin) );
                $client = Client::find( array('id'=>$rent->idclient) );
                $inventory = Inventories::find( array('idRents'=>$rent->id) );

                $tarif = Tarif::find( array('id'=>$inventory->idtarif) );

                $resultTarifArr = array('id'=>$tarif->id, 'sum_per_hour'=>$tarif->sum_per_hour);

                $resultAdminArr = array('id'=>$admin->id, 'name'=>$admin->name, 'email'=>$admin->email);
                $resultClientArr = array('id'=>$client->id, 'name'=>$client->name, "phone"=>$client->phone, "sex"=>$client->sex);
                $resultInventoryArr = array('id'=>$inventory->id, 'model'=>$inventory->model, 'number'=>$inventory->number, 'tarif'=>$resultTarifArr);

                $startDate = $rent->start;//date_format($rent->start, 'Y-m-d H:i:s');
                $endDate =  $rent->end; //date_format($rent->end, 'Y-m-d H:i:s');

                if(!is_null($rent->start)) {
                    $startDate = date_format($rent->start, 'Y-m-d H:i:s');
                }

                if (!is_null($rent->end)) {
                    $endDate = date_format($rent->end, 'Y-m-d H:i:s');
                }

                array_push( $resultArr, array( 'id'=>$rent->guid, 'start'=>$startDate, 'end'=>$endDate, 'note'=>$rent->note, 'administrator'=>$resultAdminArr, 'client'=>$resultClientArr, 'inventory'=>$resultInventoryArr ) );
            }

            echo json_encode(array("modified"=>"2015-11-23 22:23:00", "rents"=>$resultArr));
        }

        public function reqGetRentsByInventory( $number ) {

            $inventory = Inventories::find( array('number'=>$number) );

            $rents = Rent::all( array('idInventory'=>$inventory->id) );

            $rents = $this->buildRents($rents);

            return $rents;
        }

        public function reqGetRentsByClient( $phone ) {

            $client = Client::find( array('phone'=>$phone) );

            $rents = Rent::all( array('idClient'=>$client->id) );


            $rents = $this->buildRents($rents);

            return $rents;
        }

        public function reqGetRentsByDate( $date ) {

            $date = new ActiveRecord\DateTime($date);

            $shift = Shift::find( array('shiftDate'=>$date) );

            $rents = Rent::all( array('idShift'=>$shift->id) );


            $rents = $this->buildRents($rents);

            return $rents;
        }

        public function reqCountRents() {
            $numRows = Rent::find_num_rows();
            return $numRows[0]->num_rows;
        }

        /*public function reqGetAllRents() {

            $rents = Rent::find('all', array('limit' => 15000));

            $rents = $this->buildRents($rents);

            return $rents;
        } */

        private function buildRents($rents) {
            //var_dump($rents[0]);

            $tableArr = array();


            foreach($rents as $rent) {
                $rentTable = new RentTable();
                //$tableRowArr = array();
               //echo "idClient: ".$rent->idclient."<br>";
                $client = $this->reqGetClientById($rent->idclient);
                $inventories = $this->reqGetInventoriesById($rent->idinventory);
                $shift = $this->reqGetShiftById($rent->idshift);


                if ($client != null) {
                    //echo "name: ".$client->name."<br>";
                    //echo "phone: ".$client->phone."<br>";

                    $rentTable->clientName = $client->name;
                    $rentTable->clientPhone = $client->phone;
                }

                if ($inventories != null) {
                    //echo "number: ".$inventories->number."<br>";
                    //echo "model: ".$inventories->model."<br>";

                    $rentTable->inventoryNumber = $inventories->number;
                    $rentTable->inventoryModel = $inventories->model;

                }

                if ($shift != null)
                    //echo "shift: ".$shift->shiftdate."<br>";
                    $rentTable->shiftDate = $shift->shiftdate;
                else
                    //echo "no date";

                //var_dump($rent);
                //echo "expense: ".$rent->expense."<br>";
                $rentTable->expense = $rent->expense;

                array_push( $tableArr, $rentTable );

            }

            return $tableArr;
        }

        private function reqGetClientById($id) {

            try {

                if($id == -1)
                    return null;
                else
                    return Client::find($id);

            } catch(\exception $e) {
                    //echo $e->getMessage()."<br>";
                    return null;
              }
        }

        private function reqGetInventoriesById($id) {

            try {

                if($id == -1)
                    return null;
                else
                    return Inventories::find($id);

            } catch(\exception $e) {
                    //echo $e->getMessage()."<br>";
                    return null;
             }
        }

        private function reqGetShiftById($id) {

            try {

                if($id == -1)
                    return null;
                else
                    return Shift::find($id);

            } catch(\exception $e) {
                    //echo $e->getMessage()."<br>";
                    return null;
             }
        }

        public function reqLogout($idShift) {

            $shift = Shift::find( array('id'=>$idShift) );

            if ( !is_null($shift) ) {
                $shift->end_date = time();
                $shift->save();
                echo json_encode( array('result'=>'Success') );
            } else {
                echo json_encode( array('result'=>'Error, can not found shift') );
            }
        }

        private function toBooleanType($integerValue) {

            return $integerValue != 0;
        }

        function showJsonError($json_last_error) {

            switch ($json_last_error) {
                case JSON_ERROR_NONE:
                    //echo ' - Ошибок нет!!!!!!!!!!';
                    //$isValid = TRUE;
                break;
                case JSON_ERROR_DEPTH:
                    echo ' - Достигнута максимальная глубина стека';
                break;
                case JSON_ERROR_STATE_MISMATCH:
                    echo ' - Некорректные разряды или не совпадение режимов';
                break;
                case JSON_ERROR_CTRL_CHAR:
                    echo ' - Некорректный управляющий символ';
                break;
                case JSON_ERROR_SYNTAX:
                    echo ' - Синтаксическая ошибка, не корректный JSON';
                break;
                case JSON_ERROR_UTF8:
                    echo ' - Некорректные символы UTF-8, возможно неверная кодировка';
                break;
                default:
                    echo ' - Неизвестная ошибка';
                break;
            }

            //return $isValid;
        }

        public function parseTarifs($inventory) {

            Tarif::create(array('name'=>$inventory[0],'sum_per_hour'=>$inventory[1], 'sum_day'=>$inventory[2], 'sum_ts_hour'=>$inventory[3] ));
        }

        public function parseClients($client) {

            $clientName = mb_convert_encoding($client[0], 'WINDOWS-1251', 'UTF-8');

            $phone = $client[1];
            $phone = substr($phone, -10 );

            try {

                $clientDB = Client::find(array('phone'=>$phone));

                if (!is_null($clientDB)) {
                    $clientDB->blacklist = 1;
                } else {
                    echo "NOT FOUND <br>";
            }


            } catch (\exception $e) {
                if (empty($client[0]))
                    $client[0] = 'EMPTY';
            }
        }

        public function parseClientsVip($client) {

            $clientName = mb_convert_encoding($client[0], 'WINDOWS-1251', 'UTF-8');

            $phone = $client[1];



            if ( ! empty($phone) ) {
                $phone = substr($phone, -10 );

                try {

                    $clientDB = Client::find(array('phone'=>$phone));

                    if (!is_null($clientDB)) {
                        $clientDB->vipnumber = $client[0];
                        //$clientDB->save();
                        //echo "Was save <br>";
                        //echo "Number: ".$client[0]."<br>";
                    } else {
                        echo "NOT FOUND <br>";
                    }


                } catch (\exception $e) {
                    if (empty($client[0]))
                        $client[0] = 'EMPTY';
                }

            }
        }

        public function searchDate($string) {
            $date = preg_replace("/[^0-9,.]/", "", $string );

                    if (strpos($date,'2014') !== false || strpos($date,'2015') !== false) {

                    } else {
                        $date = $date."2015";
                    }
            try {
                return new ActiveRecord\DateTime($date);
            } catch(\exception $e) {
                return null;
            }
        }

        /*public function parseRents( $rents ) {

           try {
                $client = Client::find(array('phone'=>$rents[7]));
                $inventory = Inventories::find( array('number'=>$rents[3]) );
                $shift = null;

                $date = $this->searchDate( $rents[0] );

                if(!is_null($date) )
                    $shift = Shift::find(array('shiftDate'=>$date));


                $idInventory = -1;
                $idClient = -1;
                $idShift = -1;

                if ( !is_null($inventory) )
                     $idInventory = $inventory->id;

                if ( !is_null($client) )
                    $idClient = $client->id;

                if ( !is_null($shift) ) {
                    $idShift = $shift->id;
                    //echo $idShift."<br>";
                }


                 Rent::create( array( 'start'=>$rents[1],
                                        'end'=>$rents[2],
                                        'idInventory'=>$idInventory,
                                        'idClient'=>$idClient,
                                        'expense'=>$rents[22],
                                        'idShift'=>$idShift ) );

            } catch (\exception $e) {
                //echo "Exception in parseRents <br>";
                echo $e->getMessage()."<br>";
            }
        } */

        public function parseRents( $rents ) {

           $phone = null;
           $name = null;
           $number = null;
           $strDate = null;
           $summ = null;

           if (isset($rents[10])) {
                $phone = $rents[10];
                $phone = substr($phone, -10 );
           }

            if ( isset($rents[11]) )
                $name = $rents[11];

           if ( isset($rents[5]) )
                $number = trim($rents[5], ',');

           if (isset($rents[2]))
                $strDate = $rents[2];

           if ( isset($rents[8]) )
                $summ = $rents[8];

            /*echo "Phone: ".$phone."<br>";
            echo "Name: ".$name."<br>";
            echo "Number: ".$number."<br>";
            echo "Date: ".$strDate."<br>";
            echo "Summ: ".$summ."<br>";
            echo "<br><br>"; */

            $client = null;

            if (!is_null($phone)) {
                $client = Client::find(array('phone'=>$phone));
                if ( is_null($client) ) {
                    $params = array('phone'=>$phone);

                    if (!is_null($name)) {
                        $params['name'] = $name;
                    }

                   $client = Client::create($params);
                }

            } else if (!is_null($name)) {

                $client = Client::find(array('name'=>$name));
                if ( is_null($client) )
                   $client = Client::create(array('name'=>$name));

            }



           $inventory = Inventories::find( array('number'=>$number) );
           $shift = Shift::findOrCreateByDate($strDate);



           $rentParam = array('idPoints'=>1, 'completed'=>1);

           if ( !is_null($client) ) {
               $rentParam['idClient'] = $client->id;
           }

           if ( !is_null($inventory) ) {
               $rentParam['idInventory'] = $inventory->id;
           }

           if ( !is_null($shift) ) {
               $rentParam['idShift'] = $shift->id;
           }

           $rent = Rent::create($rentParam);

            if (!is_null($rent ))
                echo "success <br>";
            else
                echo "FAIL !!!!!";






            /*try {















                $shift = null;

                $date = $this->searchDate( $rents[0] );

                if(!is_null($date) )
                    $shift = Shift::find(array('shiftDate'=>$date));


                $idInventory = -1;
                $idClient = -1;
                $idShift = -1;

                if ( !is_null($inventory) )
                     $idInventory = $inventory->id;

                if ( !is_null($client) )
                    $idClient = $client->id;

                if ( !is_null($shift) ) {
                    $idShift = $shift->id;
                    //echo $idShift."<br>";
                }


                 Rent::create( array( 'start'=>$rents[1],
                                        'end'=>$rents[2],
                                        'idInventory'=>$idInventory,
                                        'idClient'=>$idClient,
                                        'expense'=>$rents[22],
                                        'idShift'=>$idShift ) );

            } catch (\exception $e) {
                //echo "Exception in parseRents <br>";
                echo $e->getMessage()."<br>";
            } */
        }

        public function parseInventory($inventories) {


            /*echo "<br><br>";
            print_r($inventories[4]);
            echo "<br><br>";

            $idTarif = rand(1, 25);
            $point = Point::find(array('title'=>$inventories[3]));

            try {
               Inventories::create(array('idPoint'=>$point->id, 'number'=>$inventories[1], 'model'=>$inventories[0], 'numberFrame'=>$inventories[2], 'idTarif'=>$idTarif, 'idParent'=>$inventories[4] ));
            } catch (\exception $e) {
                echo "Exception";
            } */
        }

        public function parseInventoryMain($inventory) {

            /*try {
                Inventories::create(array('model'=>$inventory[0], 'idParent'=>0));

            } catch (\exception $e) {
                echo "Exception";
            } */
        }

        public function parseAdmins($admins) {

            //echo "Was commented";
            //echo $admins[0]."<br>";
            foreach ($admins as $admin) {
                //echo $admin."<br>";
                try {
                    Admin::create(array('name'=>$admin));
                } catch (\exception $e) {
                    echo "Exception";
                }

            }
            //print_r($admins);
        }

        public function parseShift($acceptDate) {

            echo $acceptDate."<br>";



            try {
                Shift::create(array('shiftDate'=>$acceptDate));

            } catch (\exception $e) {
                    echo "Exception of create order in Shifts <br>";
            }

            /*foreach ($shifts as $shift) {
                try {
                    //Admin::create(array('name'=>$admin));

                } catch (\exception $e) {
                    echo "Exception";
                }

            }*/
        }

        public function reqPostMessage($jsonData) {

            $error_code = 0;

            $messageArr = json_decode($jsonData);
            $idAdmin = $messageArr->admin->serverId;


            $message = Message::create( array( 'idAdmin'=>$idAdmin, 'message'=>$messageArr->message ) );

            if (is_null($message))
                $error_code = 1;

            echo json_encode(array('error_code'=>$error_code));
        }

        public function reqGetMessage() {

            $messagesArr = Message::all();
            $jsonMessagesArr = array();

            foreach ( $messagesArr as $message ) {
                $admin = Admin::find( array('id'=>$message->idadmin) );

                $jsonAdmin = null;
                if ( !is_null($admin) ) {
                    $name = mb_convert_encoding($admin->name,  'UTF-8','WINDOWS-1251');
                    $jsonAdmin = array('serverid'=>$admin->id, 'name'=>$name);

                }

                array_push($jsonMessagesArr, array('message'=>$message->message, 'admin'=>$jsonAdmin) );
            }

            echo json_encode($jsonMessagesArr);
        }

        public function reqDataLoad($idAdmin, $jsonData) {

            $dataArr = json_decode($jsonData);

            foreach($dataArr->data->rents as $rent) {

                $client = $rent->client;

                $idClient = $client->serverId;
                $idInventory = $rent->inventory->serverId;

                if( $idClient == -1 ) {
                   $idClient = Client::create(array('surname'=>$client->surname, 'name'=>$client->name, 'phone'=>$client->phone))->id;
                   $rent->client->serverId = $idClient;
                }

                $newRentId = Rent::create(array('token'=>$rent->token, 'end'=>$rent->endTime,'idAdmin'=>$idAdmin, 'idClient'=>$idClient, 'idInventory'=>$idInventory, 'completed'=>$rent->completed))->id;

                $rent->serverId = $newRentId;
            }

            echo json_encode($dataArr);
        }

        public function reqBreakdownsInRents() {
            $rentsArr = Rent::all();

            $jsonBreakdownsRentArr = array();

            foreach($rentsArr as $rent) {
                if ($rent->idbreakdown > 0) {
                    $client = Client::find(array('id'=>$rent->idclient));
                    $inventory = Inventories::find(array('id'=>$rent->idinventory));

                    array_push( $jsonBreakdownsRentArr, array( 'name'=>$client->name,
                                                                'phone'=>$client->phone,
                                                                'model'=>$inventory->model,
                                                                'number'=>$inventory->number ) );
                }
            }

            echo json_encode($jsonBreakdownsRentArr);
        }

        public function reqRentUpdate($jsonRent) {

            $jsonRent = '{"data":{"rents":[{"serverId":0,"client":{"surname":"vert","phone":"9559","serverId":4,"countRents":3,"blackList":0,"summ":-2,"name":"vasa"},"endTime":1443047505915,"token":"8645960203193091442961055142","inventory":{"countRents":2,"model":"Flash aluminium 150","tarif":{"sumPerHour":0,"sumDay":-1,"sumTsDay":0},"serverId":"92"},"isCompleted":0},{"breakdown":{"cost":0,"description":"Прокол колеса","code":"1"},"serverId":0,"endTime":1443074647029,"client":{"surname":"vert","phone":"9559","serverId":4,"blackList":0,"countRents":5,"summ":-4,"name":"vasa"},"token":"8645960203193091442988483651","isCompleted":1,"inventory":{"countRents":-1,"model":"Flash aluminium 150"}},{"breakdown":{"cost":0,"description":"Прокол колеса","code":"1"},"serverId":0,"client":{"surname":"vert","phone":"9559","serverId":4,"countRents":5,"blackList":0,"summ":-4,"name":"vasa"},"endTime":1443074647029,"token":"8645960203193091442988483651","inventory":{"countRents":-1,"model":"Flash aluminium 150"},"isCompleted":1},{"breakdown":{"cost":0,"description":"Прокол колеса","code":"1"},"serverId":0,"endTime":1443161441852,"client":{"surname":"vert","phone":"9559","serverId":4,"blackList":0,"countRents":6,"summ":296,"name":"vasa"},"token":"8645960203193091442988709556","isCompleted":1,"inventory":{"countRents":-1,"number":"613","model":"ролики active fit. 3"}}]}}';
        }

        public function parseInventoryNotMark($inventories) {

            $a = "You are cat";

            if (strpos($a,'are') !== false) {
                echo 'true';
            }
        }

        public function createDebugOrder() {

            echo "nothing";
        }

        public function reqClients() {
            //$clientsArr = Client::all(array('limit'=>10));

            $clientsArr = Client::all();

            echo "{results:[";
            $i = 0;
            $count = count($clientsArr);

            foreach ($clientsArr as $client) {

                echo $client->to_json();

                $i++;
                if ($i < $count)
                     echo ",";
            }

            echo "]}";
        }

        public function clearSqlite () {
            $db      = new SQLite3('files/backupname2.db');
            $results = $db->query('DELETE FROM Client');
            $results = $db->query('DELETE FROM Admin');
            $results = $db->query('DELETE FROM Rental');
            $results = $db->query('DELETE FROM Point');
            $results = $db->query('DELETE FROM Inventory');
            $results = $db->query('DELETE FROM Tarif');
            $results = $db->query('DELETE FROM PlanExchange');
            $db->close();
        }

        public function collectData() {
          $this->clearSqlite(); //Очищаем отправляемую базу данных

          $error_code = 0;
          $message    = 'successful';

          $dbh = new PDO('sqlite:files/backupname2.db');//создаем PHP DATA OBJECT
          $i=0;

          try {
            $dbh->beginTransaction();

            //Делаем выборку всех записей клиентов, пунктов, инвентаря, тарифов и историю рент
            $clientsDBArr = Client::all();
            $adminsDBArr = Admin::all();
            $pointDBArr = Point::all();
            $inventoryDBArr = Inventories::all();
            $tarifDBArr = Tarif::all();
            $rentsDBArr = Rent::all(array('completed'=>0));

            //Инсертим админов для оффлайн авторизации
            for ( $p=0; $p<count($adminsDBArr); $p++ ) {
              $t = json_decode($adminsDBArr[$p]->to_json());
              try {
                $name = mb_convert_encoding($t->name, 'UTF-8', 'WINDOWS-1251');
                $queryStr = "INSERT INTO Admin (Name, ServerId, pass, login) VALUES ('$name', '$t->id', '$t->pass', '$t->login')";
                $dbh->query($queryStr);
              }
              catch (\Exception $e) {
                echo $e->getMessage();
              }
              $i++;
            }

            //Инсертим тарифы
            for ( $p=0; $p<count($tarifDBArr); $p++ ) {
              $t = json_decode($tarifDBArr[$p]->to_json());
              try {
                $name = mb_convert_encoding($t->name, 'UTF-8', 'WINDOWS-1251');
                $queryStr = "INSERT INTO Tarif (Id, Name, SumDay, SumHour, SumTsHour) VALUES ('$t->id', '$name', '$t->sum_day', '$t->sum_per_hour', '$t->sum_ts_hour')";
                $dbh->query($queryStr);
              }
              catch (\Exception $e) {
                echo $e->getMessage();
              }
              $i++;
            }

            //Инсертим инвентарь
            for ( $p=0; $p<count($inventoryDBArr); $p++ ) {
              //$inventoryDBArr[$p]->model = mb_convert_encoding($inventoryDBArr[$p]->model, 'UTF-8', 'ASCII');
              $pn = json_decode($inventoryDBArr[$p]->to_json());
              //echo $pn->model."<br>";
              try {
                  $queryStr = "INSERT INTO Inventory (CountRent, IdGroup, IdParent, Model, Number, NumberFrame, ServerId, State, Tarif) VALUES ('$pn->count_rent', '$pn->idgroup', '$pn->idparent', '$pn->model', '$pn->number', '$pn->numberframe', '$pn->id', '$pn->state', '$pn->idtarif')";
                  $dbh->query($queryStr);
              }
              catch (\Exception $e) {
                  echo $e->getMessage();
              }
              $i++;
            }

            //Инсертим пункты проката
            for ( $p=0; $p<count($pointDBArr ); $p++ ) {
              $title = mb_convert_encoding($pointDBArr[$p]->title, 'UTF-8', 'WINDOWS-1251');
              //$title = $pointDBArr[$p]->title;
              $pn = json_decode($pointDBArr[$p]->to_json());
              $queryStr = "INSERT INTO Point (ServerId, Title) VALUES ('$pn->id', '$title')";
              $dbh->query($queryStr);
              $i++;
            }

            //Инсертим клиентов
            for ( $p=0; $p<count($clientsDBArr); $p++ ) {
              $c = json_decode($clientsDBArr[$p]->to_json());
              $queryStr = "INSERT INTO Client (Avatar, BlackList, Name, Phone,  ServerId, Summ, VipNumber, CountRents)
                           VALUES ('$c->avatar', '$c->blacklist', '$c->name', '$c->phone', '$c->serverid', '$c->summ', '$c->vipnumber', '$c->count_rents')";
              $dbh->query($queryStr);
              $i++;
            }

            //Инсертим ренты
            for ( $p=0; $p<count($rentsDBArr); $p++ ) {
                $r = json_decode($rentsDBArr[$p]->to_json());

                $invDB = Inventories::find(array('id'=>$r->idinventory));
                $clntDB = Client::find(array('id'=>$r->idclient));

                $queryInv = "INSERT INTO Inventory (CountRent, IdGroup, IdParent, Model, Number, NumberFrame, ServerId, State, Tarif)
                             VALUES ('$invDB->count_rent', '$invDB->idgroup', '$invDB->idparent', '$invDB->model', '$invDB->number', '$invDB->numberframe', '$invDB->id', '$invDB->state', '$invDB->idtarif')";
                $dbh->query($queryInv);
                $idInv = $dbh->lastInsertId();

                $queryClnt = "INSERT INTO Client (Avatar, BlackList, Name, Phone,  ServerId, Summ, VipNumber)
                              VALUES ('$clntDB->avatar', '$clntDB->blacklist', '$clntDB->name', '$clntDB->phone', '$clntDB->serverid', '$clntDB->summ', '$clntDB->vipnumber')";
                $dbh->query($queryClnt);
                $idClnt = $dbh->lastInsertId();

                $queryRnt = "INSERT INTO Rental (Client, Inventory, IsCompleted, ServerId, EndTime)
                             VALUES ( '$idClnt', '$idInv', '$r->completed', '$r->serverid', '$r->end')";

                $dbh->query($queryRnt);
                $i++;
            }

            $dbh->commit();
          }
          catch (\Exception $e) {
            $dbh->commit();
            $error_code = 1;
            $message = $e->getMessage();
          }
          $path = '/files/backupname2.db';
          echo json_encode(array('data'=>array('url'=>$path), 'message'=>$message, 'error_encode'=>$error_code));
        }

        public function recodingInventory() {

            echo "Recode was OFFLINE";

            //$inventoryDBArr = Client::all();
            $inventoryDBArr = Inventories::all();

            foreach ($inventoryDBArr as $inventoryDB) {

               $inventoryModel = mb_convert_encoding($inventoryDB->model, 'UTF-8', 'WINDOWS-1251');
               $inventoryModel = json_decode(json_encode($inventoryModel));
               $inventoryDB->model = $inventoryModel;

            }
        }

        public function testParse() {


            $clientsArr = Client::all(/*array('limit'=>10)*/);
            $pointArr = Point::all();
            $inventoryArr = Inventories::all();
            $tarifArr = Tarif::all();
            $breakDownArr = Breakdown::all();
            $rentsArr = Rent::all( array('limit'=>10) );

            $jsonClientArr = array();

            foreach ($clientsArr as $client) {
                array_push($jsonClientArr,  $client->toArray() );
            }

            $jsonPointArr = array();

            foreach ($pointArr as $point) {

                $title = mb_convert_encoding($point->title,  'UTF-8','WINDOWS-1251');
                $address = mb_convert_encoding($point->address,  'UTF-8','WINDOWS-1251');

                array_push($jsonPointArr , array( 'serverId'=>$point->id,
                                                    'title'=>$title,
                                                     'address'=>$address ) );
            }


            $jsonTarifArr = array();

            foreach ($tarifArr as $tarif) {
                array_push($jsonTarifArr, array( 'name'=>$tarif->name,
                                                    'sumHour'=>$tarif->sum_per_hour,
                                                    'sumDay'=>$tarif->sum_day,
                                                    'sumTsHour'=>$tarif->sum_ts_hour ) );
            }


            $jsonBreakDownArr = array();

            foreach ($breakDownArr as $breakDown ) {
                $description = mb_convert_encoding($breakDown->description,  'UTF-8','WINDOWS-1251');
                array_push($jsonBreakDownArr, array( 'description'=>$description,
                                                    'code'=>$breakDown->code,
                                                    'summ'=>$breakDown->summ ) );
            }

            $jsonInventoryArr = array();

            foreach ($inventoryArr as $inventory) {

                $tarif = Tarif::find(array("id"=>$inventory->idtarif));
                $point = Point::find(array("id"=>$inventory->idpoint));
                $rents = Rent::find(array("idInventory"=>$inventory->id));

                $jsonPointItemArr = null;
                $jsonTarifItemArr = null;

                if ( !is_null($tarif) ) {
                    $jsonTarifItemArr = array( 'name'=>$tarif->name,
                                                    'sumHour'=>$tarif->sum_per_hour,
                                                    'sumDay'=>$tarif->sum_day,
                                                    'sumTsHour'=>$tarif->sum_ts_hour );
                }

                if( !is_null($point) ) {

                    $title = mb_convert_encoding($point->title,  'UTF-8','WINDOWS-1251');
                    $address = mb_convert_encoding($point->address,  'UTF-8','WINDOWS-1251');

                    $jsonPointItemArr = array('title'=>$title, 'name'=>$address);
                    //$jsonPointItemArr = array('title'=>'s', 'name'=>'s');
                }



                array_push( $jsonInventoryArr, $inventory->toArray() );
            }


            $jsonRentsArr = array();

             foreach($rentsArr as $rent) {


                $client = Client::find(array('id'=>$rent->idclient));
                $inventory = Inventories::find(array('id'=>$rent->idinventory));
                $inventoryAddition = Inventories::find( array('id'=>$rent->idinventoryaddition) );
                $breakdown = Breakdown::find( array('id'=>$rent->idbreakdown) );

                //var_dump($breakdown);

                if ( !is_null($client) && !is_null($inventory)  ) {

                    $jsonClientItemArr =  $client->toArray();

                    $inventoryModel = mb_convert_encoding($inventory->model, 'UTF-8', 'WINDOWS-1251');
                    $jsonInventoryItemArr = array( 'model'=>$inventoryModel,
                                                    'number'=>$inventory->number );

                    $jsonInventoryAdditionItemArr = null;

                    if ( !is_null($inventoryAddition) ) {
                        $inventoryModel = mb_convert_encoding($inventoryAddition->model, 'UTF-8', 'WINDOWS-1251');

                        $jsonInventoryAdditionItemArr = array('model'=>$inventoryModel,
                                                        'number'=>$inventoryAddition->number);
                    }

                    $jsonRentItemArr = array( 'endTime'=>$rent->end,
                                            'isCompleted'=>$rent->completed,
                                            'client'=>$jsonClientItemArr,
                                            'inventory'=>$jsonInventoryItemArr,
                                            'inventoryAddition'=>$jsonInventoryAdditionItemArr,
                                            'token'=>$rent->token );

                    if (!is_null($breakdown)) {
                        $jsonRentItemArr['breakdown'] = $breakdown->createJsonArray();
                    }

                    array_push($jsonRentsArr, $jsonRentItemArr);
                }
             }



             $fp = fopen('files/results.json', 'w');
             fwrite($fp, json_encode( array('data'=>array('clients'=>$jsonClientArr,
                                            'points'=>$jsonPointArr,
                                            'inventories'=>$jsonInventoryArr,
                                            'tarifs'=>$jsonTarifArr,
                                            'breakdown'=>$jsonBreakDownArr,
                                            'rents'=>$jsonRentsArr ) ) ) );

             fclose($fp);

             $error_code = 1;
             $path = "NUN";



            if ( sizeof($jsonClientArr) !=0 || sizeof($jsonPointArr) !=0 || sizeof($jsonInventoryArr) !=0 || sizeof($jsonTarifArr)!=0 ) {
                $error_code = 0;
                $path = '/files/results.json';
            }

            echo json_encode(array('data'=>array('url'=>$path), 'error_encode'=>$error_code));
        }

        public function reqGetAllPoints() {
            $pointsArr = Point::all();
            $jsonPointsArray = array();
            $error_code = 0;

            if ( !is_null($pointsArr) ) {
                foreach ($pointsArr as $point) {
                    $title = mb_convert_encoding($point->title, 'UTF-8', 'WINDOWS-1251');
                    array_push($jsonPointsArray, array('serverId'=>$point->id, 'title'=>$title, 'address'=>$point->address));
                }
            } else {
                $error_code = 1;
            }


            echo json_encode(array('results'=>$jsonPointsArray,'error_code'=>$error_code ) );
        }

        public function reqGetAllBreakdown() {
            $breakdownArrDB = Breakdown::all();
            $jsonBreakdownArr = array();
            $error_code = 0;

            if ( !is_null($breakdownArrDB) ){
                foreach ( $breakdownArrDB as $b ) {

                    array_push($jsonBreakdownArr, $b->toArray());
                }
            } else {
                $error_code = 1;
            }

            echo json_encode(array('results'=>$jsonBreakdownArr, 'error_code'=>$error_code ) );
        }

        public function reqAddImage($base64) {

            $errorCode = 0;

            //$imgInBase64 = "/9j/4AAQSkZJRgABAQEAYABgAAD/2wBDAAUDBAQEAwUEBAQFBQUGBwwIBwcHBw8LCwkMEQ8SEhEPERETFhwXExQaFRERGCEYGh0dHx8fExciJCIeJBweHx7/2wBDAQUFBQcGBw4ICA4eFBEUHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh4eHh7/wAARCAGQAREDASIAAhEBAxEB/8QAHwAAAQUBAQEBAQEAAAAAAAAAAAECAwQFBgcICQoL/8QAtRAAAgEDAwIEAwUFBAQAAAF9AQIDAAQRBRIhMUEGE1FhByJxFDKBkaEII0KxwRVS0fAkM2JyggkKFhcYGRolJicoKSo0NTY3ODk6Q0RFRkdISUpTVFVWV1hZWmNkZWZnaGlqc3R1dnd4eXqDhIWGh4iJipKTlJWWl5iZmqKjpKWmp6ipqrKztLW2t7i5usLDxMXGx8jJytLT1NXW19jZ2uHi4+Tl5ufo6erx8vP09fb3+Pn6/8QAHwEAAwEBAQEBAQEBAQAAAAAAAAECAwQFBgcICQoL/8QAtREAAgECBAQDBAcFBAQAAQJ3AAECAxEEBSExBhJBUQdhcRMiMoEIFEKRobHBCSMzUvAVYnLRChYkNOEl8RcYGRomJygpKjU2Nzg5OkNERUZHSElKU1RVVldYWVpjZGVmZ2hpanN0dXZ3eHl6goOEhYaHiImKkpOUlZaXmJmaoqOkpaanqKmqsrO0tba3uLm6wsPExcbHyMnK0tPU1dbX2Nna4uPk5ebn6Onq8vP09fb3+Pn6/9oADAMBAAIRAxEAPwDyjUr68GoXAW7nA8xh/rD61XS9vO13PgeshpdS41C4HfzW/mag5HIB9xQBP9su8Z+1T9eP3h4qN728Az9rm98yHrTVJbg9egpCuaAF+3XhP/H3NnP/AD0NL9uvScG7nGOf9YahZRxj9aQ8N6DtQBY+23na7uB3++act9eDj7VPxz/rDVUZJ9qeOOp6UAWft15/z9TH28w08Xt4BzdTZ/3zVNcemc9qlT8c+nrQBaW+vMcXM3/fZqRb69I5uZ/+/hqmMnsOKkBPoPagCyL28B/4+p/++zSm+vB/y9Tev3zUGefekHtigCwb67/5+ZsH/bNH228xn7TN/wB9moAQeOaM8460ATi9ux/y9T9P75pTe3eP+PqbA/2zVce9KOtAExvbzP8Ax9TZ7fOaa99df8/U/X++aiIzj9eajOPp7UASte3mc/ap8/75qM315jP2mb/vs1E3POD0pjYP1oAnN9dj/l6n56/OaBfXmOLqcdvvmqpBPAbFIe3ODQBaN/eEH/S5uP8Apoab9uvT0u5zz/z0NQHknrz700juAKALK3t3jm7nz/10NH2296m5m9MeYeKqFiFz1NKDx9eDQBOb68PP2uc/8DNKby9zn7XL/wB/DUAHYdc9jQSAOefpQBOLy+GQLuYf8DNC3d7n/j7nPbJkNQnbg9h6UmCACMUAWPt16CSLqb/vs0ov74nAupf+/hquCSaXGe/Q9qALn229/wCfu4/77NFQbG96KAJdS41C5HX963b3qvjnt6VZ1ED+0rnB3fvGx+dV8jH/ANagBjIc5Vc0qBmXjr70/HP8qMYIPT1xQA1l4GR/9amEHd/hU5XJGD79KTHPrigCIZxx/Kjn0qbI446nAqhcahbQttDhsY3AHkZ6UAWxwBkY70o6jntWMNbDlvLiGY+XDHHy+v8Anv8AWpo9Ql2FmjXKjPynOfYH1/z3oA2FGQOgp4P4mqkF5E678hQRkN2Iq2mGAKsCpAIoAcAPTmlyc8dDQCMAZ/GgEdMZoABml6Un4mg+3SgAGfoKCD2NJnbjhjk4+lGR3FACHHY1GfXJp7ccVG2KAGtx0/8A10x854+tPIxj1qJs55H60AN3cnigt7mqGpanHbFkQByq7mwenOKwTq97LIWDBcEjkZx70AdX3wTj3oyQRxknnrxXKR6peBNsj5YE7sHk8/5FXLbXVwizjLHqVPT60Ab4OMHrzShxnnoDjpxUFrcJcrvik3AcVKecfNQAucjn8qVPmYkHHFIO4PHuRSg98AZ70AOOM9OtByOT+NAwOc/nQvIOV+gFACLtLZAOCelSBuMcDHamcg4/pS4Hc9+3egB+W/vUU35v+fd/++x/jRQBZ1Igajck/wDPVv51CF7dTUupHN/c9z5rfzqAHA5PP0oAeMA5x0pQQc8fgaTcOQODTkGCTxQAL1wRkdqcV9KaCCSDwPbtTJ5lij3uRgdfT6/rQBka5qCpGqx8q3zMQOQP6fX61zd3dsJTIDh2UgtjryevvxS6hc7brzSG8xX2OfofvY7e9Z7FgJPl/dk5C+o9Pp/hQBHJcSt5jJJtLDDr6jPepVvJ8M6AoqEEBDjBzx/n6VV3JHjaW6c5/TNMif5hksrdCM44/wA4oA2otSuDDgtu8vLADqR35+hP4Guo8N36MnkmTehAKH07VwlvOFkUAHIBB44P+ckVp6fKqy7434wNw6DDcH6YOPzoA9G6cYo7Yz+dVNJuHurPzWyW3kH2xirfToP/AK1ABnBoJ9OKXPU44owQBkEbvu+/agBKOp4FKF+XfxjJUfUYz/MU3rjnj3oAaT7/AFprg9PT0p7ZA6Co2G7GeoPrQBGRznBB75NZGv6iLYpDGxEmCSF6njgVr3TBIHc9FFec6ldSXeo3EyOeWKgkkfLjr7dKAHTXJMZdsbmwMDrx/wDXNVlkOSd+1M55781GzbpCFUgDgLn8qid2BznIBBx3NAFxnLFpBgnr9T9aerIZmDN8oxyOMnv/AJ9KpEjcME5HGaUSbQSck5xk9KAN2zmaKdbiJjkYCov93+gro7O4E8Sy7l3Hrj19q4y1lQhB97OAQTjPoK2dDnEd4AxyuMA+gNAHRtjoTwfyNKCSSRyP507aDxg565pwXdjjn2oAau7POOKcuQQT+WegoCNntjt605V5wcmgBDyeTn3zQuM5PBp68EY5p3ynnjk0AR+UfQfnRUvlj0X/AL6ooAfqH/IQuf8Arq2fzqBCB7VZ1IA6jcEDH71v51X25A7mgBRjHrmnKM5FGMHngUvG/ZkEkdKAFIORzjnuOtZviRgtiM4znI9jjH49efatPGemf8KxPFAKR7nUOjdcnHA/l1/X8gDj9QY+fg43AfMQeox39/X6ZqrdSMFwRkHoep/z0/KrM0h83gEp91ifvKR/I8dapTsxUxIoIzkEfxd+nagAdMK6HAJAKkcg5pnyqd2Mcjk+tEJyoUgfNkA+how0jYA7ZLY+7QAIxLfuwBnsa1dGjia8HmqxgYMGCn23D9RVO0s5WJRIjuJ+Ubcn3I744/nXdeHvCN1dvDMpaJ15EUq/MSpBBHY/LkH6H0pNgW/DWn6gNal0pBk4aQlzjcQBwPU8jp1rutG8Garf2kd4beVYS8W7auSEdGdeP7xC8fhXp/w60LR9H1W31m+0+3u2S3AVoRuIcKNyrk9eNxx6SA5r1PQLy1srFbSLR7eHy3Chok4ki3E455G3HHPf8KLgeP6D8J4rfUn+2RyX9qcPAxAj2tjcok5+63A+oYdOvc2fww0y6soVuNNt7QwOVjZTuZcbsgc9N5GCeRk16LLLI1kWS3iCsBIxAxuPyk/oeP8A61TCZppo5nVgBIH+bJyBnBP5CkBwifCfQmntftVpHNDapvFvleWypyxH3s/ODz1xUd38H/DV35qtZysZgN/kOuceg/u87j+Jr0SGPMyyCZUBRZBlclwTnge1PO6CRGjDO77QW7Ywckj8QfyoA8C1r4G2Yslks9Sma4DhSI4QoYFj8+M4A2ntx8o5PNed6l8MfEdjPKipDcIjHayNy65OCB7jnBORkV9aTTSpES0caqqA/J/D8pGD9P6VnarBZyws89tlW3SOS3XGP06Z74pgfCXi1ri0028iaF0nijZsY4G3Gfrjd+hrz+BJY4WARtmQGyOQeTj/AD6V9u/FD4Z2WraBcnTIALqWB4bWMoBAZB84Zv7oz1GcAZ9K+c7v4T69aWcszi3ZbZf9LflfJ3BSMnHH3gPX2NFwPKigBbzOmeRkZz6VHKGBUODubAH0rrPEGiSabdtbPFlIY1eQnouQPlJHUk+nrWHLYMA5b5Sv3lc/dHX8qLgZrRkoCeSOTj+dRuMKAWJJPDentViRcAkseSAT7f55qOUHeEUDjrmmA61L+YdzZOBzWvpbMQQGGf7x6D1JP9OtY8YKg4+8CMYHWtDSSm4qAAOpxxj15oA72yUG0iCtnCgZqfGD071W0MiSwVlXaMDnHH4e1XgDz0IxQBHjtjPagLgg5IAqTbwRjAxxTsHAGenWgBhGOR0zmk545GDUuDjrShT09OlAEXlL/t/99UVPt/26KAF1Mf8AEwuG/wCmrfzqsBmrOprnULhfWRv51XUDHByaADB7d6co444zSgfL06npS4I5FAAozgVjeKYi1pkrmPuwGcfX2raXjp60yZBJC8bKGVlIIPegDzOeNhl1cE9CvQMv+f8A69UbmMgbkyuOvqD6f4Gui1WwaJ5Ni7wSSG549j/j+dYzqHXaWI7c0AU1HmB8tgH72R3q5YQzM4Ee5mK4f5v4ce3Pekig84JGkW6QnAAHB7c/416P4O8O5T53sZH2bjK0oK7DhQy4OflY43DkEYOOAQBfA/go6jcwXO2SWOSM7J43GyLAJIPo3HT3yK9Y8GeGraxliivmFuIUyly3zfvgGPQc7fnYZ/iDHGSOavh/TrHTJnZXS2uoiA8sIBOcLguRwRkfe2g9vet6BYXiWwUSBLWRlQRgJJC2QwQjrt3HP0LYyC1IDsPCN2/26KziG9pZGZvK27Hl7g475UkN3UjPt6VaWKi5inEmwRI+2KMDA3D5gSec8Djscetcd4KjttNvJJxdgyThFa5MIAjJbcCgHGWyO2DyCAa7a2uYN9yoLRqkrs6j+E9QM/3SOlJjsXrVGlhD7g5KBWBY8gc4z7YOPrT45EC+WXYkOm7PtnOPbB/nUMEseBkKF2fNk/ePPp+VOWSyRFCyfaSqsSP7v+z/AFpAS4keEloMMwBZk42ELx269RipCtw6AuxRUkJztHzAY/Tjn2p0N9KIImjtQPlyc59hz68EflTrm/micSeQW8sYUMOFJOCcfiKYIqSsyBw0MkbR427Dznjk+vU/lVDUIVmZiEILgKhV+B36dOmQa0swCAvKvzGQMZFOSGPXn0wKgUW8KD5k2HlVByVOOhPX/wCvSYzLv47i0gUWSec0MSlUYjDAdBuPb17msO8uYvsptL7TvtgwDM8iBED4xluuThuAMnnnoTW/PISu1plMMnIK85HOcH0wSOmAcfWsq9Er3AJkWFNjEBVG1ec7mPbkf+O98cAHlfj7wBouqQy3OlW0jhUcfPzIDghGC46ZJYnrnHoK8B8XeDZNGMvmxM0E4ws6RllJHXBxkn24z9Oa+qNU0tpbR5rC+e3iOfOufMyg9eWA+QgDlc+2M5rnPF2jx6vpRsrG0gB+UNKwYMRwcjJwn3R9ATk4zTQj46vLaWKZg6HeG24K8EnHX8/0qm0RhlMcuQ6nBOOvvXrvj3whMl27WsE0uTIz7UCfvt3IwT93GSPTJ54NeZaxbSGfOVlfgF4/unjPA+n5/jVCM0kghgNing8dvrWjpMTeYQFDgEZH1H61SZXQkSIQ5GceuOlbfh20e4cAMA+fmxxxxnJ7f59aAO201AlqgAIyM4J/zirGP16UIoVdoGFHA9qcT2xQA0DA9PSlIPU/pQSKQE59fpQAnH+GKd9SMU0HJ560ueeOuaAHfL/s/nRR83t+dFAD9TydQuD1/eN396gxgc85/Kp9RGb+c4H+tb+dQAdhQAoz09Kd3xjp70hHOaUYBx60AHp/nNBx75ox60v40AU73T0nJZW2MR1UD/DBH1rCvNCnXLBkYN94gYyPf3/GuqHTnpXrXwG8Lpqkj3t7okVzbShkS4l3MBtYdOoUg+x46jBoA8u8LfDzU4tMi16702W0tWl8uAHIZuu84OSoIPU5BwRg9vTfD3h+C60+MyaWsFtHbuHmWfBRiMM/QY4ySvQqOckAj0Xxdp5vjb6bI0kFtDutxsbPlAk7VkAz6ghgfz4xBF4dluVE7sIvMj8tRCWbZxxt24yDwcjDKVB6ZAm47HH6TpSrau0MQa48r/SVEasrSHpgY74XBOOo9K6Pwv4bijvH1O7Terwj92+Vdy3T5+pHAAycrgY71u2PhqztLh3KqVcNEUYKuemQw+6e2R074B+atuO2tYbY2duswYfK4YFtykjK4P8ADznGSR2PHIFitZJbNY7EhMkUuYZncAupYHnnvnBz2Oc1pM/7qNZPnIwHkb+L6EdeefzqCKOZxGQFXauxouGZeuMt0Zsd+4Hr0ltiizlSxVVx5YI9Ox/LI/XpSGWRM2DtRimNjfLjZx29K2LBIItss7sFdMrwBz1z06dc4H86xUZisuAylwAzE/Mc8H+VW7USSuBG0ZOQMs2Cp4wCPr/nigDeTVI1cJtLHJCuAASuM4Hp9DipJrpLizc7dhdCAcjOT7E+o/SsuK1hjjxcOrdgo5zxgfUirT29j9naMfK2d5fH3eRkc89TRcRFcmEFpYizBcFsHjk8/KTxgA/lWPdFpneSJtkZCiNGUgsXGck557ZHbNXb0ygAwLvjkT7zHG0ZOSM+nH4nvWM4aOCCNHWY4wkhJPf7x9t2eB2IxQMS2mWJ4FkkiwAUbyIdm1VOTgdAoJGe5yOgzTZnjmDwzRqYgNrN5QZmOM8jvz17YHfNU9WnZ7tYbViZXPzOBnacckD15PsOfTmdlRNMDM21VHlt0zJj+EY4zjJzjj6mgDPu7K4u1SOaSURsxlVjIS0QJYEqeegzx6+gHNK4tpbFlLzQoquTawDDjOflL54IzySc8gY6V0O2O5jEbtKkTZHKlCqg4wfbOc/j61BLaw3YZWw7AceidQMHnA64H15NAHnEumnU76+JAmieHy7i4QqDJIOnLdgcFsYGAAMDNeFeO/BGpaewjS2hS4ujlQnOxTksV69tuScDqOowPpnUNF2NJdmCdn5DmQspbJzjGQMk9/1NZU2mQ3Ui2x0rzLdlA3Ttuj3KTucE4wFB4PHU+vDTEfIk3hm9tmhEsDN5pAUAHlu2BjLjjqOMdK6PQ9MWyRWkO9yM8jGD+FfS3xG+H2i2fh651jSLU+UlntuI05ZSOr9sA9QgwPzr59OQSCrKQcYYYI+opiEPPakPeh8MpGSCR2pBnaAeTTAGOAKbnHA/Wg8Y44pCT0PFACn9M0A4PJ4/Wm5xz05ppJLdcUAS4Pofzopu8+pooAm1Ak38/HHmt9etRDg8VLqZH9oXHIGJW7e9Q9/qeKAH59DS9BwRUa4ZQQCPY1IOO2CRQAoz0PNAyPejvSY75OPegDQ8O6ZJrOu2Wlxb91xKEJXkqvc4HJwMnjn0r7B0a00rR/Da6ZZ21ssUkaxTKnIyBjA7jqfcZGMV4d+zx4aa6upNcmtEljVykbMcMmOCyjPzDJwehBH5+3+IfIgVwsL7wmG8wb+e555I598enUVLY0Z80RuLnyomLhRhi+WO0HgHP3v8806WS3tjtBKg/MYt2BnPOM46kgnpz271UtNTggtpZGbzHkA+6egHpjv7Vhanq4mkPmx3BU4ICE856Zxxz6jnnpUjNaO6juHupwzLEvyuSuMf7Q9Pwzx6EUr+IbGGLEkhkgPyPxhsn+6O574HbBGea55LiSWcQSS3QkmDRRSEhwnplR16ckY5weD11IvC9v8AYBI5uHlJUMrOTj0YNySMAcEnpximBoQ6vpbBZh5rlV/eGNgwJGcP79O3Ix0Bq/HfRGSOQO7mZVZ/N6YDccYwO2SOOc9M4xW0GGS1E8ZZZDJ87RnYgPr9Rwcc/nVNru305o5jfu7RkpEHTB2NwCVbuB1wSD+FAHYefptxHLGtwkgkUkorAkckHJ9untVlJhHvjV2+2Qusb8cfd6E/73P19q56O1t2wbSGEsHJJRiFVnUHj0U5U47VfaSFrO4ilVvtUUirck8upzwVB67eG+n1oA6m2jclDcY2ltp39j2BPb6066tt8jMrs2HzuB+XnJyfzP5VmtdRW0rL5yFN+4hgMHOM4GegPp71PdYtIyn2lVIXLP27AqF+oBH1psCveSC0jdjcFHZgu0NjI5GAfrj/ACaxRqlqLx1uXztDSHaTkAKGP5ZP5Cm6uyXDwWqBS1yd6mPqpTbjGeo+8c159qfiTS7PVrmG4uPNhkVY8qSxTauWBz6nAJHHzc9OUB21vOl1breW8bh2Ekg2nCRg88no2R/F04BA24NXoklurKKWZUXADrlioJ2jBzxg9APYE+1ch4X8Q2moWS+fMsu+UG5+Urs3bQMDuBk/XAJ613fmW6x+ZhvJVQTuOASDncT+QxjtQBnylo4ooORHjBSNdgyOQen5DvjNVo7ye28t5keXd1ZFAz7Y9vfn9BW1sV4izFgXfYiFuGb1BHfrwefwFUmjwjIseZCq7iG5IPt6e3SkBSS3e6Y+ZO8EQBaRjIG3c8DgEAZ//VUL6c0E5WaUiJ8FWXIYkdCB1BBI5P1qO6Z7JRsRscbQDwCDnpx+f9OK1Wu0utNhuImVXT5wRJkgHgg8DmgDU8LpZLpUmlT2ga1+bc5kB80tkFi2cnOe/NfLPxV8L3XhjxbdwyCPyJpCYCrE5UdMg89Mc19S6C0BZC8TNMeoTgk84J9APXivG/2m9FkM9nrcWnBI0Xyp7nzD1ycKVOcZOMc89e9UmJniIzjim5+bGaXPIpGIqhDGxzg8U3PBx+NOc5GAaiYgk85NACse2KTgjjBpAeen0pCefSgB2B/dH50Uf560UAWdQx/aNwe/mt/OogRnnIqW/b/T7jP/AD1br9TUCHjpg0AO75B6e1PXJpnOeeKcnHSgB/XHAoJwCTjAGePSk69jWh4dtVvtesLQx+YstwisvqMgnr147UAfUfwc0O20b4dadHC/2mSZDcSyQksrFzuzjAyB7gEYrT8SzBiUjyRtIy3T8Ox9a3dMEFpY24jgZYYolaNQgXGPQAYwfp0rh/FupzJPIsVj554LiCQbh7YOCPY81DGjO1edBBFGbYzJGoyysBwD1JPTBJwc5B7iqg01fMMsF1PE8jkh8Bd5J5JUZBbjB9/XrVC61d44Wjhto1Z+TvyqqQOjY6N6Hp1yRXG/FC51DQ/Aeo67pd8lpOVhjZogQQGlRWf5T1wSMgE9eelIZ6fZRGzjF5d3ECCM7hJIBGDgAckkAY9RjI4PNV9Q8YaNa2xmt9Rj1O4UHMFipnJPfmMHJ57c8Dr3+adU1XxJcPCmmapbSTXkyQQTR2vl+aWOS58xTIFXuSfQ969r+HPgrwrdzsniq78U+LJBOygtqzwwNzjcIEKgA8nBYk8ccHFJCOU8Z+LvHWqu0mn+HddULuUCSF1ZlJ43AhSxHJBPI9q8/muPiHfQCOfR9Qn2sf3TKWkznBycgkYzwM/4fbugfCP4aW1mJW8AaAjyDcwkgEzAdgWbJJ9TWBr/AIb+GMc32SP4X+H7s5xtWzQM30wvH4kU7CPnT4aeOPE3hrU4l1/S9VhsZl8qRp7aX5fQliPug5GfQ+1fSfgzUrDWrK1u4po7y4fhzDKH2r/CCeew7+mK4zUvCnw2MRSy8H63ospXDtoOuSw7cdsBwpx7jHXpXl/xE0Ox0GA6x4b8Y31zIrH9z4l05XDn/YvIcN+vPrSsB9OR2qQWOHKsVkjIOB0wFz3wT39hUt0qCzaNAk3mxFweoPILHnpXyZ4S+MHiGe2tLezfVdOuvN2zW0l59stpF8tpEeMTIzrnyyoG48kcjmrPhr4++J/Emrw6Tpone8uPuKbOBApBJyD82eMn7o96LDOy+Ovii70u7lh05pJJTCbbfG4xGpXcy5HTCjGfc189N44t4b9Iom+0+XEYt+0nGevTrx9PxrpNP1Lxt8R/HOoaHokOnSLZSyNc6nfpvit4VcjzZN37tc8AKqZboM17Do/wptrOCBdU8X+LL13jUGGw8rTY33cjCIpYKT0yQfbtTSC54RoXijxJCIprLTbmNI2yNtvJ86jlucc56evYV7t8OfiZqfyR69pepxQEr8w06djwc4GAfbp19sV2Phr4JaXqJR7rTrtbMHIjv9Uurl/975nCjI9vyrv/APhSfwshtQ0/hi3jKj5pVvJ4+fXPmcUWFc5FfG3hZN2NVgtnyWZLpWiIbOBneBjHJ/HFben6vp2ox/6FdW9wWxuMUok56Z+Ukc+/FY/in4T/AA4l2x6b4+8ReG7ichYhZ+JJZEY9gI5GYEflXzz8RdI8Z+CL2+1HTfG2meNdFtZQt5K9tGL23U8ZYMu5gMHJRmAxzilyjufSms2huo/JYGUqcJzxkd9p/GqGmXIgke0Mo2uCfmbJB9Tjoa8A8S+NfEOhaJcDT9bV7m3iWWSQLJGnzIGUxqXKyJggbtoB7DvXsukL/qZnMcZmgjlCB93zMgJKjsOe/J71LQzttBlVH+ckKo4K8g59eeOmc1ifGXTbG/8AAN79t85SozAzOVUNnO8Dp/D1xk9B1q9pUgWRPlTfnOG+n61u+IoLXU9Cu4ri1aSGeB/NQrnjbg5GPujnt0FNCZ8S54znrzTDWp4ms1sNevLONGWNJD5YZNuV7HHYeg9MdayyeasQjEY/ComPXAGae5OT/OmccqOvrQA0nmm5Lcc07nPQ1GevAoAdlv7tFLkep/KigC3qY/4mNxz/AMtW/nUGSTwf1qbUz/xMbgdP3rfzqBelAEoPHrzQRnnmkU/LggmnLweufagBydeK1fCqB/EumoRHta5QN5gyoGeSf8+lZRrb8EC3k8W6bHcpK8TzAMsUxiY8HGGAJH4ChgfXMVyH0W2fzY5WEfDByrDHXk4z0/SvIPF+tXVrdSSTRKZQ5EQyRuOOc7Tg54+8cdK9V1IwNZ27ySiMxoNjtLvw5AXrjGeOSevPTpXmOr3dp9qmVpYpYto8z9ykiADjDHGQOmMnvxioKMzRruXUJ/MltZ5X2ZKgAFsY+bB6HvnocdK7LTtCsNQ0q8tdY0g6jp99GYfImjJ44565zkfUY49KzLC2jSEXiwRW8TvnMahkB4OCQTtwc8Ed+lei6KkrxLcKFQeUcqqbeMdOuD7dO/SgD4i8bQx+FPFl54fspJFi0m7xDcE5cxSTQyIxPdgvGfavpSDXdL0vToTFGI4kbbb5ba7kcnnt6lj0B9TXhn7Rlhv+I+vSiHH2jR45kLDktC6bjx/sjPX1pPAuuT+JtEhvdRdCmmQrbOrNtUbR1OPuqRyccnoOcU7ks+nPAvjy5ulaNbnyrUOS8uz5pRwBjOSFPIUdTzgE5I0Nd+Inh9510uxtEurmR9pCyYUN6MwyWPsM+lfLl74z1PU1/s3RpGgtWYq0sahDLxghQPugjAPsMDgVR1TxdD4Ss3trOSKTUGQpIQeEz1Vm7/7q/wDAifuk1Gz234l/FXwt4W0ua3tydS1R1zKox5EJHQHGQx7AkkDH4V8q+N/GmpeJtQ82/nfymbhN2QpJ7j+nFY2o6nPqV5NNcyyzNvLkuv3ie+39PYfSmeHtPm1bWoNLhVT9qkCGRm+VV5LMT6BQTn2NNCOx8IrrN94isdC0uz8y9vtPtXj+UN5cqSebHJtPXg4x3D132p/DfxH8GbmfxjbfZdTuRbTRy28sRURl15eMg9VB3DjBAIrsP2NNFt9X8eaz4/uLU/ZUb7NpwK52xoAox9FCj6g19EfFDw3D4j0aazkj3sv+r3fLglOmffOD25pAfKv7KPiPQPB/h7XJ/EULTLqpju4nZ8I7W0pBDdzzIrY6kgV9J6J41t9fVdYsLODTNKCh5dQvSEJX2z1PsOB03ZyK+KXgtfDU1xp+oWv26PwtrXntasdpuLOcKpPPXDJEcHj5+a0ZvEXiTxlfwXmsambXT0YtHBz5MIHAVEH3j0+Yj2GOlMD7Tu/jH4ZW6j07RpG1GYkKXiGF/AdWPsB9SKTXtf07UbV5tXvEt4IiG2jBKezFvlU9sYz9a+S4fGth4TM8WnRI1yy7XMjnzJP+ujr91f8ApmmPcnkVxXiv4kaxrDkteMwXIRUXZGg9FUfKv4D8SaWoH0b4/wDif4V0eIxaVbRBjghuhII7/wAWO+BtFeA+M/ihc6tIYLG2to5JG8mOQKNoDblYY5BBDkHHGDjnrXmt5dy3Mpa9kaQMchdxH4/596seG7eXUvEel2uNsL3iABeijeCx/IfpVAehfDTT7jW107wsb+5m8y7ZWgkleSOGNOCFQ5A3E9hkAH1r6e1gNpptLd/le32pIB8nygYAzgnHsOeO1eY/sXaE15ea54uljJiNw6wMVBG3JJxnoSW/8dPpXp3xOR2nXYty4SUA7CCAT1O0jG7rx6Vmxo6PSWjljiKOZFYDyz2/XHNa+tTLB4Zvbgy/MI+duZGbkbQMZGM8Yx3rm/ChLaVEsblpIYyGYccjqOf5da3tRI/4Ru8aSRVXymYnJXJXt2Hfgn2z6UIGfJniqKWDxFfR3DBpvN3OQ5bJIz1JJ796yDjdwADV3WNg1W62GRh5zfNJKJXPqWYcMc55FUWOD3zWghpAA7UzI65PPelbJI9KTII55oARuV9utRkY9M+gNPJHQZ6etNYL+HtQAYPoPzoox/sj86KALep5+33AHP71sZ+tQqPm7mp9Sz/aM4GcCVv51XyevOKAJF9e/bFPx0JGaiDAHJPHb3qUHI45oAeoAGAP1rd8Bzi38V2Mm8o2/EbCESENg4wMjB6jI/KsEdOOxqa2leG4jmQKXjcMA3Q4PQ+1DA+vmuXi8OurJIMxlUbKoR6fNkduoOR9a8c1TSNS1G8jSGCW43HeskJYog74BIHPTPP616f4Mu11XwZDfLbrEHUqqxHJCg8qTgc9+B0HesTXdLsvLP7reJeNrSbUwe54BIHX+orMoo+EJYbPTns58yXij52gc4THYsuD9Qcj2rsbXxBYW+h3EDLIsgjIQop+Y9fQlu3TpXmkj2dpIz27XM/lkmJeAVHqf7ox/e9R0612ejWtwbCe9tozJdqECDBYYbPqeTjjJHGOvegDwL4xXMcOpWGuXMKPb2lwY7uJQSz20ylJflJOMAjHuc148VuvDviCbRWuwbO4ZCZFJ8q4jPMUvupVs/iR617X8UdOWa8uoZpFnuHjc4VuFAxktjP0J45NeKNqUtvYpo+o2MWpWNuT9lLOY57dWJLKkgz8pJztYEZyQAc1SJZ0uveI7Dw/DJYaWVuL4qBJIcbEbHPI6nGeB8q+rHpwFzLPOTcTsXdiQGxwtbKQ+Fpl2yp4ht5NvyhIoZx+eV/lVmzsPD0QdfI8T3S8bk8mCEH05Jb+VMDm7crgxo2H4+YZOfYDqSa7fw/4fvkuk8Nwhh4g1UeVdIo5060bBYN6SOMZX+FeDyxA0/DsOoJKln4T8L2ej3dwhCX0zteXwHGdjNhIj/tKoI9RXsPwl8Ax6HqtkbVxJJODNfXEuWdhlgdz+pOcewJ+o2M9y+EegWXhfwpZ6Pb2yW8cMKE4JBIHGfqe57kk967XUYwqGN8OoypyvUnHJPvmsfSUNo6ROMsyruAOVUDgD647ds+1azrttiHjeLBDBn/hVjxkev8ASpA+OP2nNCTw/wCNE8U21m13DJH9m1SHOEuIWX5l4+6Rjgjp8p7V4h4jtr7TRC9rdzz6ReAGwvB0kjHRDjpImcFOoPtgn7e+L3h2DW7ELL5vmCQq+1wrPzgqfzOMd+3NfMPiHwTrnhx7t9AWO6sZWzd6bdReZby4/iCn+YwV7ECmmB5E80kwKPKTznb6/WmlgkfD4y2MZ5NdJqOnaQXlF5pup6FMvURL9rt8npgkh1B+rVnpoFlPG0lt4n0XI6LPJJC5/Bkx+tUIynMbkRrhMD14rp/BFu8Vnqmul5M21uba0A/5aXk4Mcar2yEMj+233qrb6PolucX3ieKUcA2+l2zzyNnsGYKg/M/Su58D2Nxf31hJHp01jplhNu0+2Vt7CVsFp5GIw8hCgZHCgDgBcFAfTXwB0FfBXw9+yyt/pC/uwUIIdzyxHtkkZFV/FjwXWpxwtICwO4pv3Aj+7t4z9Qc1tQX0VnoVsrKIxBEB5YAXBPJ4Bx+XBxWXCYr4R3KBShO5Czt19CD+dZlG1oly0cSRfIrFOhyMf5960fGl5eReB7k2keZhEUWQxbwp7Fieg7cYH6VRsEJCZ8tj67eazfjRfyWnw3vbdpRG1xC0f79CRsJAKAD+Ig8EgjBJ4xTW4HzNKzOxkfG48sPc9aic4HI/KnuORwQOn0pj4xn8K0JG8deOe1Nz8xoHX17ilIBPNAERJ6f1pcDdyOh6inAAtwOnvSgDOeqmgCTYPWij/gVFAD9R51GfqP3jfzNV1yen5VZ1In7fcf8AXVsfnVfGWyR09qAHLzT1GMcd6amQxPoalXGetADlGBnt7U7J9qaueM5peP8A69AHunwK1K4fQPspklhtYiVwkeQxyScn1z2rqNami3tAkJjkRnDI6fdOck5545HP4DoceL/BnxLZ6V4hlsr1sG6URwv1w3XHT0ya9PvHxq8sksnnpE2TKDhV3YxjrkDGS3Ocgdqh7jRW8UzTQKPsW1ndfmYIWYAenOASSPmOcV2Hg3Ukt7dTdEIS2xlZs/McDDHJJ7A8n+VYWqwxXNoxEyqsa+aw34IAxwFB5JOBzwO/PA5m010290PMDRo8Bmj4IZEJ44wCCcnGe3u3CGbPxa0/TrlrkWghijkVfOEeQ/lj0x91Sfpn3OBXzF4m8MT213c7YI2WMHCq4LMM4Hyg5GOhH/66+j444rq5Vry48uFzuigMmJJB/E5I6ZH5DuOhZ4p0Gzv9Lk062tmRHl3JLEgGFwc5OQfu5+UEYGCxyQAwPl3RfDrS3ISXz44yRkDBZicfKo6n+te6fD3wLpt4IfOiaYSk/OxCKrbfUdFUYJHJ49wKxP7OvYbuCCKaKa5jucGD7PyvqDnleMdQemTivbPBlrp/h+zjtZwY9hMwBcDYCoyRnoAB1Bb2zmmxF7Q/B1pYWyJBAkcsucyJ8zDoduT274J6kZ6GtZBpWiiOKG2ZCYgpf7yphurcc5bv7fQVznxc8Wnwta2ZPmo0p3YjXLsepU/h2HXmvKU+MelyaxILW6vZrEkCR9heRpMnpngKM4yOu0VIz6Z0nUNjRs80MZ3DLOeuVOOenIH4ZFT3eob55f8ASfKRzwcZRCBhR/u5BP4mvmp/i1L9kQtbyJGHZoS4OVwBg5+nBHbr71Bpf7QFhc3Eeny2d7/rgZHXZkLg56gA+uOOvFMR9B6oiTQ3FvLGhRijHzPusDnAz16gnPWuWfQ9NuCsd3AmxssHbqnQnockZx+hrkJPibBJbTOq3cEMeQCYyzMuTyME4HfB7g1qad488O63oNy0F3HJcQyoRGoO5Q52gleMDOBn1BpDG6z4F0O6uQg0mLc7Z81JyhOf4eeCrDqMgj3rgfid8AYIh/aOgs8YcgtCSHHHVsnnA4zwcV7u09tJ4egkLK0xjX5weeOPx+nXirun6ss+lSwTEsU+UAqBz7Y//VQmI+JLXwheaReqbq3V0SVRJty4wc4zjr9D6V7d4OvxbaSn9nb4nABCLCuyQAYI4HXHHIzj2rX8ZeHIZ5nmtk2OZNxiWMMAvGSoxgj2xxgmub1SRdJEVwkEMUsQ/eQudhJ3cHcBj19j1xkU73GaU+rxXQig2L+7ICAZBQHkMMEFcdCOSOetbWhmRkW2m090d2DFgxcMpHDAjp68gd+nSuFtdUlk1Z0VmSKU8ExAFGOMZz0GT16DIPI5r0zw4qW9rFBNuFxIDsVUx6bsDqBnnHOMnnHFJgdd4csVAknmZQkSAfNnnkdzyPr715J+0brkz3ltogObcgS88gMpOCOchhkg9eGFepXt82l+HnimlUqqtI8gbJZQM4HfPsRzn1r5i8aa3NrXii5mMwkgBIhVc7VGT/QgfgKcRGXIMjkc1ESARxxj1p0jduvHamZwelWIUqcdelNBzx39cdsUFsEH0/WlHr0oARQOAeB1z60N0PQYHFKQBnrk96TA9OKADI9V/M0UbE9B+dFAE+olV1G5Bz/rW/nURA3BvTpUuo5OoXOevmt0+tRrzjB7UAOXIPOCDT16YIx2FM5Ixxjt7VIp7Z9qAHDrjI6UEde1KABgc8U1x8h7UAZ1y8kciSW4AaNgy8dTn9ef8K9j+E3iey1iwk0vUr0R67zsJH385GV7Zxj9D2rx+8Pl2+VGM/Lu/wA/5/KufjvbjTLuO8tnZJomBRgSCP8AOKTQH1pfeVDZG2kI81gI5GXaAI0OAo9CxyB9cn34jxOom1sXUbykK+ZEjGM9iit16HHtlz6k53gn4mab4h0mTS9WnZdSjwYjLgLOSfmUMMbT6D3/AC29eDQTWtmXAtkcLlH2uW2EEsvVgq8AZ6DPc4koo6VPFIYFm3C4Z2ySAmADvbA5IUAbB2JYkfcGPTvD91FqdlPp5dPPFuWhWMhY1fJIBz0Xg988ZznmvM77SprdkklfzvMcqYQxAKYBCk9vuIDjgAEDknbu+EdWi0u9haFC6SJLIXkbaGYgb/yHboCw9OADktYV7fxzNGzvJBHcCR5Y1wgdVDbRkjbtB56gZGSCefW59Q0+S2ivbMwtLbr/AKVIMSneCSxPc98Y4GRzXlnxL8OXCq+qWN5PcQPNujQFTvdiWCLxkjPPqPzNcZ4Y8bz2GoxWurSShI98Yk3BVXPDDAHQ4xnJ9enUA+h/iPp8PizwzBfw2jSTQOssW4BnV8ZBbHHHAP149/jjxroV9pl9Kl1vs4FuJDb3AUlAWYsUlI5DAnqe1fXHgvxBLZadDJDG7abMGMk2d0cIAbcoHAPf3JHtVfxdoPh3UIW1NLKVC7K0yom9ck52nPBznPPpz1ouI+U9L8cpd6NJpXiO2W7eIEwTg4LHphj+XPsPesbSdaXS9Y+3rGrsD0YYBOMZxj6/nXf/ABV0Pw9df6Xogt7WZGC+QkAUED77ZHB5JOD2AI9K840/SZrq5kjmnSNQ6ltufMfgHC8Yziq0BluG717xJqCn+1Z4FztWOHzHZRngKiAljXtnwu8G3VremyEbwTXMsP2k3Qw4jjyyo6jJUuWDFe2B3rb+EF9fpof2HToPs1nGAqqrbTLkAA7gAc4B59a9R0OGGxlS8dhFKwRAmwEgYPJPcnnB56Z5GalsDX14R6XpUNuHjjUHgu2Qg7djnqKzdPvICk4WUAxsUKjkjjIbvxjpjj+VeffFrxx5muw6RDISI2DuEcFXfrg4PPy5xjjOeRiqw8YW62G53kaFQDHA/wAjMAvz/MPUZyDz0xnApWGd/e6hAs7y3MiJFGuBl8NGTwWyDyMnHbH0wa8j8W3kNzqB8q8C3XnPG6Mo5wCvOTxkYXvwAeQaXVPFSanat/ZrT+W5bADfeyMHI/hwWHI4J5PDGsCXT7u61PYR9reNdxMX7sFQDgg9jgdAMAAj+GgDrvBmgQvIL2aSITEkeUvBJIAzg9Tw3A7E8Hv6x4fhW309HluBK8f3RgFWz0+nbBH/ANauU0WBLHR7e2l2IQh2wO5KgDluTnBAOev5VzfxQ+Jln4a0R7fTrovfSJtjUdUA4L565OCB7cnJosBB8ZviCTI2naYyLLHMGeXg59MDqB7/AKZXNeUQXDySGRhu3ktnHXJ/xrkY9SmvLtry4Zi8jlnJOep/xOa6TTXMgAI2sM47896taEmk/J2g0zByOc04nIJwOe9NLetMAUc5HBx1NAyTnB96cTzgU1vTtQAe+ecd6UHntnHHNNDZXP8AWlBycn6cdqADD+q/nRT8e38qKAJNRP8AxMLnt+9bn8aij9/pT9RGdRuOODK2PzpgJA2jIHWgBw65JBPSpEPOT69qjXquCPTp0qReBQBJn2oPFAzjp+tHPQc+lAFK9ZWjliY4ATP19v0rlNZZYgTKw3HGRxWtqFwSXSHpH/rJXOI1Oe57t7CueeZnuS0XmGTJ/fOBvJ/2R/APfr70AZt7JNC4JRoz12jhx9f7vr612Phf4h6jp8EMWpWy6gtuvlwuWKyRqSc8/wAWcgE9cDFcw0SFGxnfnJOSR1qvcgpHzjIycZzmgD6E8HeI7PxNaFLcrOYm+YcKy7UbnHZc8D6+oFVNRkvYr6YlifsmEW45wWBGVAHTpyfbbjivBLJbywdZrW4lt5flO+NyDnr+XPeuj0zxpqUCxRag8kiRFtpAyAT7Hvycn3qWh3PU7vU7++uU8+W6m8olnPHl7gOgVeAACAT1w1ebeMdInguSsmz96CVWMbQCD1IHbr9evSuqt/FtjeW6NaxhHJJjUPuZWJBHA785J7c+1GowtqGmGV5kZBIVMchGcqMkn+XXHPvSAx/BfxT13wvGLJ44bq03kSpNls9QT168npXdad8fbONkt5tOUadLITJECd6grjknOSMnBxwT/sivFNbtWSSSQLwzAEYAHXp9ev50628G6/faDqGuWOntNY6XGkmoOrAmANIUDFepGRyR0GCaYJHd+OfiBoWpRNFa6LD5GWMWWLbCzBiu48lcDHbrXEeFNY0sa6z6zuFtM+WYKSsfbdgc8Csi0sJrllRP3hI528lPr6cfzrUbwteRRyynEhiIyoUkkYzn04pOSWhoqU5bI920HxV4J0O0RbO6E2MSLIzFsMM7cY4x+WPxNZniT4tLJBHY6RI7M0QRBkgL/eGOmfzHfjPPiOlxzPqdrCbFZI7iXapEDneM4O3Zgtj26Vu+H20q5vXNtpFzFcfwBLrfET0H3huXrn7x6c0GTbR6D4B0sa9fyXeqTPFGrM7vL8mBng4/IH04wMVc1HwtdXmpT30arJDbIEJ3YkwGA3D25A6Hgj0zUthpn2LTxFPbX8UZxmUIdgcep5Cn/gXPQ1bt2021mgnfUJ1tI0dftSLlkJxj5CRkhscEEEEjmi4uZPQb4W8OGeB/KimEchyT5edwPTcoznuCD0HJ54resBp/ha1MuszwtLEqtbASANDxg7ieOCTgMSCGPqRXHeMvi3JaefZWNhZwkMRmNNjLIpyHU/eweOD0xxxxXlPibxZrfiCV5L6/mlDN0wFP6dKLFHo3jH4lqs08Wl3TSBirRqH4iAwOuMHjqDz9cV5lfXE2r3klzqEkkrkBVctllQcD64GP8msUriQPg7M9zWtp6xhiM8se559c1SVhD/s01nt3qrxSH5ZU5V/8PpXQ6DOq5TdlW9ecEdKrac5ijKNGs8MifvI3+6f8D7057KSzlN1Yo8tuAC8MgJaMf1HXnrTA6MDK+9IcevFR2MiSwCRH3KwyOelSMeRgUAIB0+Y4+tGPmGe1Gfm5HH86Ugc+/egBPy9OtLnBxnikA7EdqNw3ZyfYUAP2+5/OikyPQ0UAS6m3/ExuOgPmNjj3qBWGfQ1LqhAv58jgyt/Oq+QGGT3xQBMpIOQAMn86lXPBP41EMZ3Ec4qRcfeJ49qAJPf86HXKkZPTp0oGMU7OR2xQBzN+/mybEhQIgwMYAXPesy4tgpOPnBx0X/P610uoRIr9wB8wQfxHP8qx9SJVFC8B2HPpk+lAGYwVQ5ZwQMcn37j8KpXKCa4W3zhnZeR2Hc1NIzlMMQC3XkY9j+lS6ZBhZb1wSsYESHPU9Wx9OKAIdTA3FVGBjnA9Ky51LscHjp+NaV4xKSNtwwwOvQ/Ws/IBUEHr09AO9ADbOW5069juLVykqlvunrXpvhjW7a/sYICzb/NCTGU/cGc5z6dsnnPpmvPbGAXGoQjOQUJHPAO4DJroLnT7i3u3+xFnVCu4Afe6nBA59evbpSY0dJ4y0a5m0Nr+NDcIVy7qpCK+OMd+uD7kn6Vd8BeJb/RNck8QeHys0E67bywdTIjiVFWeCZFBIViCVbGOe3NVtO12W78NXGnCPzJGUKYwgBUb1OOOT0H0xgdad4a8Iv4guZr3zryxlgV5RIhEcisOMkjBGcjj1zSGnZl3wtb+Cg148FvrFhJK5kaGW1DxQZuSPLDHG4Lb8g92yO9a+vp4Wk0G9j0+/upLpraX7Mr2yIrzedtQHg7QYssTn72BkH5atWnhnxjpUCalp3iPX3w/ywi4dmMIYAd+cDr9M+ta123xIuVOjWviPWZnKrGbx5DH5ZcjY+RycqWGM9hUOKbubwxE4x5U395iRSLYeHND8U6jol5pejeFbC5t9KEkDRveXtyzbMZGdiE7mkbgngda4X4eaVYNeWo3yOJGELuR80YPG5e2QTnB9e/UdxrfgjxbrOobr3xBeagiyKgN27M8bYxtwxxgELnrnBJ9a63wb4Ah0SOH+0jGJNy+dGHBVV3cKT0wN2MjHUe9UYbmj4nnk8MeHC8U6lYmEcxSXD7Dj5sDuMZwRgqSDwAa8G8Wa9qGr6w00F0giAJO1BtnwQCX4+cdME11fxo8Wx3uoHRdMuJbgqzK8+d/lYblB34YH8MY4JFY8XhPUn8EJ4lEbC3R4oQmznYw4Zj2+bHX1FJ3RjVbS0PP/EtittqpUQpGzwLIQmcZOOmTkDjpnj6VkBdijuSevpXZfEyS0Hi7UYrNneCApbxsx5+Rfmz9SxrkmGWUdiN27PHtVo0Q6O3MvykZDDCnqM9v1AqzYP8AOCVVTnoTUVuCvTLYcBex9qsSRtBdpMMrHMolU9ge4/OmBsWETMdqKzBjnnv249eDWxDcYQMH+cfKAO5HT86xrB2jCtHIqksAMitKzLfbJCrBgBgEN6/5P6UAaEaIjmSEKrnBkQcAn6evvU5wQOoqvArCcnkrtBB9ascEg4xQA3GB0oOADSsOOOcUhB64GfSgA47fzowDg9OO5pgOcjn1pSfTg+hoAdgepopvPp/49RQBJqpxqNxnj9634c8VEDjr+tSal/yE7k5z+9bB9OahQHBHAA/OgCdcEH+p71IpxnHfFQx/T65FTJ2zj8RQBIOT/jT+p6U1R39adxg+1AFC8i+YyYLOzDAzwOwrB1coyn5mIUYyOpH+FdJqIVrdlJIyM8HFcteyNLLuIAHYHoAaAMqfc1ysMRVmZtqrnv0H+Nat4kVusdrCBsiQrnPVz1P1JFZN7G0NwkqPh42yo6kEHg/y/OtIXUV7b7kZY5cgSx5ztbsc9we1AGZcj52QsVK8/wCfeqEmdxycAcE9cVpSoAWJ3Fs4LZ5NZ8oKljncFPJHFAGv4VtpJ9RtjCpaTyJGCgYDFSvB9etet+HNOt7vXUi1CJoocmMyAbWV8Ebeo9evvmvOPhrLCninRRcbFhe5a2Zm/wBtMhs9sEDFfQNlpKCdgx8/MzSCVk2Y3ANt+oBGD9aiQI5HWfhDraxzX2kIIJ93mmMhtzLySpPAGe+P5iqfhDxQvhq9aHVYGglZ1MpmQ7ZCCDnJ4bcy9OoA+uPoLw5eLFpKW88McpWJT5igh1J7jt6cHPTtTfEnhHQNXtJ7e7tra7t5t25lG1skhiSp5ByByPfmlcoz/D3xG0K4tYnezcI8gG8qpMasqsCcYGMnB9yB3qbxD4/0S2MMdrHawrcKpeUqMDgcMwyCByOOmB2Ir5t+Nel+HPBWrnTNH1i6u9RScTGzVspZxE7irsTwxIXCjsATziuNu7wXMNol94qt4LWWPzFiRTM0QOAQVTO1+vBxkDJ7CnYVz2TxL4nhvdUa8ubmAGNGllEEm54yCeAOr46epDYrO1f4l6nch9J0S3F/fsq28MkYBQKq4zIT97gAn3HbnJ8H/hp4X8aWp1K38UrrFwgAurXy2R43OOXDEHBC8N0JA9xXsOlfDzQdDb7PsVBGx+W3VQcHOBu5GM8jrjnHBoA8e+Gnwb1LVL03OqTyIjsGmkkwSS/J5OBzx1PFehfHDxBoXh/4et4athFCkmzLx8gtG25SP9kYAHrlj6U74jfEXSvDuiroumXG48Kbe2fIUDqWwfvAc8+gx1r5o8SavqXivXle7kZrZ5MyAHIVerfoDQtQZk6y0srJLK2+e8LXErHk5c5x+WKoLywJA65Oe/px/nrVvVpTdajLKehY4H90cAAew/pUEIAcEgtye+fpViJoCMBj0ByDn36+1aRg+12SxRLvnR8x4H8QzkfjWdbxZUAuQAfm9B/nrV+e8/s6xMdv8tzcrhcD5okI5P1PQfnQBNphLW+Q+1ScP/skkAfqP0FatpDteTad27jzG6dO31FYemnZE6lQAo5Y+w6ccV0lgfMj+fbnGQCPXnjNAFrByFBJC4xx1qVMhBubNJGFaIAZxgjinjG3AOfrQAhX34NIfvdAc0uSR0ApOB1z/WgBhwDxyKTJz2xmn45Iz9KTI3evfFAC5X0/Wik8z/ZFFACakw/tO47HzW/nUacEDIyf1p+p7RqNwcZxK3T60xOeeAM8UASLnGN31qfO0Ebc1EvbkHPepF6Y7ZoAlXk4pxBI5A9qQKPyFOAGTQBXvMGBi3CgZNcnO2GZ5Mh8cBjgZ9P611eoqTbsQQNvX6d65iGG2upLh7551KldqIwG4cjJP4UAZVzKHcyMRgtwRwSf8mo7WK7W8jnhBiTJVywwGUjkAHk9PwNbm23ii22dtHEdx2t1diD6nnAqveLuXd5jPJnLZHTocD/PrQBnX78DAwzY6dqrTjETjAyT1xwPSrk48wAAn5CABjHHJGP0qBl6jIPIHJ/z6CgB+kymJ/PWTH2Z47gdOdjDP14zX2h4ft7fU/D6/vGkR1UqzfQEfoR+dfFmnMqXO6QZUjy5AP7p619VfALVWuvBlvaurNcWrG2lLsP+WeQPzGDxUyGjtrW0uYIgm0grgHjOQMkY/EgfhXl3x8+Mi+GHn8MeFJY5de2lbq7HzLYey9jL+i+56dD8dviGfBHhpLXT5U/4SDUlZbIdfs8Y4acj26L6tz/Ca+RJtPvdsd7crIVvCxEzkks2ckknqTyefekkDdkOsYpb0vLM0k8kzl5JHJZmc8liTyT3zU76XI0uyNHL7enr/h9au6CnkwqRHukOQFGeRjp+v6V674V8KabrXhO41e5eO1ktSFkjddzDPAPPOD65HcVlKo09Dz6lZxloeYeAr/XPB/iS28TaRG0r2/8Ar7c5C3EROGRgPXt6EA9q+kfFnimPXfAUeteH5ibK/h3JkkPHzsdXx3Q4BHuD9fMo7nSbDSpfskRaZVBcnj5RjKqv/sxOe2Bk034H+JrUeLL/AMIqxjttY/0iw3Y2pdqMlPT94g2nsSFop1HN2LwuJdRtNGZp/hW41LUwt39rldTslwhLjgYY/wDAcnisrxxpeneH9SubSwkaUW8SxGRlCkyN8zDA9BsX6k173q9vPpFhuUW0MMSMXMeVIAGTz34HP0FfM/jG/ku74mRzvdjLKTySzEt+mcfhWyO1nOhmZtzMCx49MnrU3cEHkEDINCxZJI3EZ2gAZzz2pYnw3BG7fkEDqPSqESt5gtsIFznIDcZJ6f570y2sLh7gvLMgctkszFiTjrT0ZcgEY5z1yPb61JDgsCpyO5x7/wD1zQBoWtjGg2NdsxIG1I4eCPXJPH5VZim8rVmtEcMylcs7c5IyR6cZxSQeTEXlL7IolLOwPbv/AIVW8PwteXstzMCJJGLn2z2/n+VAHYBQCxGcGlAA9KEyQRnAAxjFAADdTn0oAQgDBzzTSR14Jp/XsetGOQB+tAEWOcnk0xjjkkVLIcNkgYNMKhjyMgHIzQBHhvSipto9qKAI9SA/tC5AH/LVu3vUUYYEA1JqDf8AEyuM5H71senWo1xvzwCaAJo+Rg8YqZevXIqFT+HFTIRjNAEqAcnFOJyAKYpHTB/Ond85oASQboyvY1yeoIlpfJO2VRm2uMZwrf4f4113OM54rG160E0B25Lck+nAoAybnzoXwFwwx0HT2/T+dRbTh8ncfvAnpRaSmWwWZsNLC3lOB1P91se44/A0MobJIOS36HrQBUlUgPheT+mD0qjyg5xgn5eef85rSvQqy4B77sD3/wD1VSUAqA/fv09f/rUARowGMAAEgkZ7+le2fs/a1bWN9qMN/cmGzMSXpOD8pRSsh9BwoPPdhXh4yN2Rj5sgEetaf2m4XSLhLaVgT8kmG274nI3KfYlVpNAdnpVlqHxg+J17qsiyLC0oKxE48mBeI0+m3k47k+9e/at8MfD974Gu/DawRrNLGHjuyMFJ0H7tgOgUfdI7qTmnfBrwh/winhaFZ40W8njVpcZyvHf3PU/gPWtbxTrZtbaSO2YebuCA43AMeOnfH+FQO1z5U8GLbRMy6grR+SGR1QfMHU4Kn6YNeuaYIrfwNqt5FaalH57xxCR4SYpBhmYAYGCODuOeo9a851e11C11S/1fRmiSG+vZTFJIm/G1trup6YLhxnHaqfiDxd4n8P3cEMGtTXUxjBuIZcMigjgEDHYnj3rmlHmloePUpc1VpMxNb1SeS6kgid8SHBGecc9awLOK9l1IXml+eJLMCeOaLIKlSPmHfg4x9Kk1e8aa9FqLYwHzdtxtPByeg9BX038K/h7p+n2Y1yOQm3ubVdkTAYHc/h049e/Fbwjyo9ChSUFc5nxt49s/FXw+0+9RY4768wmoQAYaKRBukx/sthSPZ/rXiF8RcXMkrg4LHC457mvQfi/Jp8HiXUTY2UMDWwW3lZBgSPjcxH5p+teZh38sgtyeQPatUdBN5YjiCkduMcU5ok2DPGe/OTTrRUcgDIxkZ9On6Uk6sHEYAG08gimIR0Ux9cZOck9M1YgVhIChIPHGMgDpmooy4kwq7sdSRge2auRjyrd55XKCOMFuBye386AK2qTgKllFjDENL9AeB+YzXT+H7VIIAdmJHAOCPyrmdDtWu79ZXyxdtxPp/niu4gXG5VQ4Xj68UAS4IQZHOOcUYGfbrT+/y0YwMYoAaBn6+tNcVJkEjJ6e9NI7nigCIqAMnv0pp74GRUjDscc9eKjfHocUAG5v7tFNwvt+dFADNTwb+5x/z0bj8TUUeO4OPrUuoA/2lcdf9a3f3qHGMYoAmQgHp26VMpUnHSoEODg9CfpUiNz16e/SgCwpGP607rzmoomXninrwOmcj1oAeMg+vvTJ4wycgZ5704Y+h70Eg5XOaAONvAdP1RnfBt5U2TBe6k8n6g4NOlSSLdA2AytncOdw9B+Fa/iGxWe33Bed3A96yLOaWS1+zTn97afcB7xZ9f8AZ/lQBBdq5tyWIJCnbnk5H/1v5VReNstkcM24Y9c/5/KtaRVZSgAALcbRnnt/P9ao3EZRPLcDduGMfkP5UAZ83yyHKjBOD/TNX7GITMYHO1J42Q8+vT9aqRj5trrz3UehqxBJsGcE7TkewoA+sfhv4ol8ReA9Kuxu+0i0EVzkEYdBsb68r+tYXxG1iTRfDt9qxCQtEhWBOcmV2wu38cH8Kw/2bLpbnUdU0kgFNiX8Zz/C2FkGO+GCn6tWX+0h4hjuvFEHhWyZTBpUYnvOPv3DjhSf9hD09XPpWctCZy5Y3M2yli0jwOt9Nbm7ayso1hRpOA75wT3xnccDjJ59/PptAvbw2l1dOzajqV8qIpYfMCOT7dR7V6RpTrLplqZolkRokYxn7pIHy5HcA84P071AbSKzvzqgLNcQx7YcgbYhjsOnQ1wxqcrZ4VPEcjfcwF0/StS+JGv6OsZKSzObEgYUtEDk8E89T1x1r6Jk1L/hH/h1cfakJWytGTHRjtB3KfU/4/SvkbUnuLWdb+3uGjnhmLwyJnKMOQfzxXtfjr4kWfiX4WaN9hSMXuok/bYzgmFotvmZ/wB5tuD3DGu2Luke1Qd4I8c8TXL+QtvIVknmcyzkHA3sct+THH0ArEtN0rEMejcEdcelW9Qug07gMNxbAz1zyM/jnNVrTl2w0ffJPbitTUvxfdwjY3NkMP4RTCckAqx5HAPT2pdyqGUrllYH5e3t+ZqyuHCl9yj68DPc0ANhTfIBzjOAccACjV3R51sIgQpYO+D1PYH6Cn3LJZ2LS/eboqg4BNVNMDO7ySsSzd+vXqf6UAdF4djWCN3K9B1JzjtxW7bcRjI5OM571kaeQjNGnrnHX0rXhJKsxyOeAaAJM8njigDvk00Zx056HvThyff60AGc8YoxlaQ55HFI2SMYU0ABHHY+tQuc/WpSD15zUTfe4x9KAG8ehopfwooAj1AFdSuSP+erfzqIngZP1qXUxjULj3kb+dQKMAYOTQBJ3BpyjJwRxmmKCcdF7GpcAADHfNADiSM9Tj1p4Y46dqixnjHfpmnYyOn4UASZO4Z7jkilJ5JGKjBweOM96XJz79zQArqJE2sTg/pXN36TWd2LtB80bFfm6Nx0/EZrpG5yBwRVDU4PNjzgE4IAoAySsQMbwkvHKu+PJ7d1PuKhmIkLDdu4Jzn6YpYAI5DYSAbJW3QtnlWx0+h/z1pjD5cEBc9z/n1zQBQmC4BPDduORUcLYLKwKgrjHfv/APrq1IhaNUzxnhuuRioTnd8yck88d6AOz+FXin/hDvE8Otum+CGCWKePOAyMvH4Bwp/Cuas9QutV1G81O/fzby9dp52b+N2JJPsM/lxUdmFw0chBjcFWGOxH/wBc1R0FmhuZIBGrGLKklsHIzz/n2rOotDGurwPXNLYR6XbKT0Tb+VSXcAuYGjDEFhwc9P8APesZ79I7dIg4ACBgQOACOPyyfyqbR7l2hQgklSVYE9hxn8sV5zi1qfPyg/iOM8R2MkRfzQApQnb684/kKzNFhS00Ka9K4kmkOz6LnA9uc12Xj0KYI5Ezl0bG1c5bgAfrXK6sPIWO1jOUijVMjGM9T+td1B8yuz2sFJyhcxAgG3cAW9B396sQo3lNkrnODznB600x7WVRyVyTnqB3q7bhYYFC87icn2z/AImtzsAKgTc3qSR69MDNS26kjaUJG4FsjqB1/WhnDFUQEJ0X/P6UzUrrybY20ZKzycEg8otAFO8cXd6ACPIjY7cHhj3NadggUqxC4J5HPSqGnRAxYK/j7Vq2h8+cHnAGMEd6ANSANGo8lwCcdufStaDLsZAAFYVnW23bjC5z8xJ59v8A6/0rRsGDQL1K84PTHNAE+CDzSnB5/pRnn170p5xzigBpB64pG4IXBOf0p3PpQcD8aAIziom5A4GanYZHOKiPX2HegCLzB/zyk/75oqbPuf8AvqigCDUVzqVycAHzGyfxNRcA7cc+v1qzqK51G4zj/WN/OoGyQM8fzoAMAAkDBz2p6nGcrzTOpAHUdaep/DNABgEZAOf6U7IUAdzzSKMnsKXj0/GgACn8elHGc0c5+7n3pCcHqMentQArfn/jSKMc4z9aTOOSAaXJJzx9KAMHXLNM53bckHPcGq7fvrcT5AlyVnGcDdjr9CMH863b63WWM5wB3P8An61gOJdPuS6xs8f3ZFP8S/8A1uo/+vQBG0exiuPcHPf0qtIgX5lLYPX2rRkAV/mKuHwUZeh4yDVT50ZkJALcDjnvQAyAtu3Lwcc/0qC+Y2uteeGKrOoOcjrjB/pU4k2ckbQCRkdAKg1RftOnyMvMkPzLj070mroUldWNNr+Qqu84yOvXbye31qXT9Qkt7t2GCGyBjJ+bHH6/zrDt7ktbuspySufQ1JG7C4yCxUt0PXOa5+U4JU1sdLLPcXU8Ubn5bUNLgnjJPH64/KuflUyTHaN/J2DHPetlWK2LOH5uHATjqiZH6kH86xgm1iYwoOcdenFbQjyxsddGHJBIiEe2MygEY42j0yCf0qRBtQ7928jOMYGP8inRgsI0GQAOT6jvUqI82VQ4Utlmz0AyRVmoM8cUIlxgKMZPJ5H+P86x3Mkt0Z5OXY8DsParc8v2pwVDGBBw3Xeemf6Cq15D5cGN3PIAA4/z/hQBZtpAQY/N5B5OcZ9fxrRt7wRxkglOoT5c/wCcetULxSJJLdYoIwHIVfJXgD3xnrVZZZVKqPLVhkAiMdfyoA6y1meYh1VjI4wOwIHGT+prXsgYBsbo/I9ie1cpp1rdmIFpX44ITgYx39K6KzsWFuUe7l+YbcBiBnPPXmgDV9OR+dIevWmxqY1AMkrnHV2yTTz7nt2oAOuc85pu3CgDGBS4HXpRwepoAYcZ7Z9aY2OAcfWpTjHGaic7jgD6UALgf3aKZt9xRQA7UwBqNxwP9Y3f3qAAHqRirGpc6jcZ/wCejfhzUAOemM/rQAdOAM+9HYHP1zSnkZ6A9hRx6gnFAAOmT9cCjPIJHakUnPI5I9KMkHjpjHTpQA4gA84I71E4BB54py9eTn39aVlH0HtQA3Gceg9aVSenFNYHqCc0zOckHmgB+Tx6Vm6varLl8c+orQHt60Ou4Yz06+ooA5q2Agb7NIPlY5ib0f0+jdvQ/Wo74s/LAg9CT144FXdVtUI8s8KTxj+X51SWd5UYTEGVfvsONw/vUAQhMhhjnGR9adEIuF/hb5WHqOhqRPubnI460jJnIBBBPJ/lQBgvvt55ICcbWK59R6/kRVuGZpdscZ+eVti47k8Cm6+GZ47oJg48uT39P6irHgqLdey3r8pbJlQe7twB/OptqZuF2bOp4jeO3QqscKbEyewxz+mapsoKsy8HHJPTJ5/P/CpbsGaQEkkY5B/U0lmoyC+7Ypye+ecVRoRrEdyqnyjaBjPQY/yKgvpjOy2EDHDfLKevy+lT6lO8MJRTvmlJ69fUk89Ks+GdNRYnuZkJZT1I6jnP+fegAjs0gi8pfmcnB446dPwqlqkIWB9qnA+Y8dMfyrqxbYRGKryc8e/b+X5VWn09WExDE7lO4deaAMW7hMs8u1CE8wjdjk89Oe3NXtN0kQjz50aR2OVGB/Ws1dLlnZHclWIHDkn6mtix0OLbumDEA42h2GT+dAGtaopkUiPywp4U8ZP/AOqrqjaMD1/M1VgsIYeUMv0MrHH5mrYHrQAYx+NGPY0e5JoyDQAnbHNBA+ntS/SkJHXkUANYkGo2bABPbsKlOT9KicfN3x+tAC7R70Uz5f7pooAk1E7dQuQf+erAn8agyOP8Kn1Ikahc5/56t/OoBnIPNABn26enal4zxj8aAOMgjBNCnHXFACHIzxScnnqaceeByfekGQMnFACcA+x596UEMOmCP1oA78+lNYY5AJx6UADKDwwFNKAZ5zT/AJiOn9KVwSuTQBEBz6DFDDcOuPQjsaVhyetHPPpmgCnfRFoyzdQPlH9awLiN4gJ1Hzpxt7MD1H+FdVIiupBA571iX8KgS5z833eP1FAFFNs0RdCdhHTvjpz7g0MpBKkYzkdcVXV3sb7ExxA5+fjke9WrkmMZJ+UgFT147f5+tAFa7RZY3hwACMEn17Mfyq7okC2OhIh2+bMGnkIxwTwoz9P51QkQyN5ScGV9pOeQO5/AZrWu8MCg4AwT6EAcD8KAK8GWQHG5mAJJPQZ/+v8A5xT5p4rK1DsVCrjgfNvPoD+QpybII3uZCUVFJLYyOKzYA+p3qSBQIUH7oN2z1Y/X+VAFvSbCW4vftF0rPLIcjHRRjgfn3+tdVbw7CEGCoXHHTvzTNPtxHE0agqFHUnJJ9auxoVQDr6k9aABEVsZ5IHFOUDdkdemaEHHWlAGeBQBEY8soACgHJI/SnqoB3E5J4Ge1L0yOMZpevRqAA0hJBxjOCBS56fzooAXr3pB1OPzpMjHFJ0AGSfc0ALnA9u1L9KaSScHBpC2MAYoAVuSOvvntTGbnAIGO1BP5/nTMg5zQA/H+1RTdw9R+dFADtSz/AGhcHH/LRv5moeq9ce9Tagc6hc5H/LVv51Bn1H5UAIcjoc0Dpk9T1pSwzyR0oyAOn096ABee2MfrTlX5s5z703lh1xilO3twKADIySeD/KlPbB60c5NA5bk89KAAgghs455zSsORj8cUo2npz7UvQ7eaAGlQSAR+VBCngAY/nT9o98mjaN3JoAjC9Oar3dqsjZIyT/KrgGOaXao5IJNAHL3tkDEQw3OD83HArKimNtObWbOCCIww+77fT/GutvLcbXIGFxyR3z1/ma5HxBDvQsBhuDkdRigC5pO2a9kmJAWFdqA/xMf/AKw/Wrcq/upZHb5QvUnp/j3NV9NUx2MImA8wx+bKMBc545/4CBVG9nbULjyINy2/BHXk+v0oAbPM2pTx2sYkS0ThV/ve59vQV1Gj2n2WEbgAxPQ89Kp6PYiCHftBITuOef6mt9YCCAScFumc/jQA+FB985/OpmyGIoUAHIo7cfWgBeMZwPWm5GD+tISO4xRuyeKADJxwfwoBGfem5wTnrSdOTye9AD9wPt+NAOevNNzSZyPXmgBzN7Z5pA1MJ7imk8UASbhu+b6CkJPNR9uPm4ppc/dxj8KAJCenH40xgT0bGKQNzx2o3AY/rQAuxPf86KTf7j8qKAJtTGdSuRyP3jc/jULADA4FT6lxqFzx/wAtWx781B2BPAoAPwz9KQADA9qUHqew7ZpB0J2n8KAFGOucH2pW6HI+hFNByeDj60q+ooAVcj/65py/XFIvqP15pxCg9D0oAVB8xOaQYzjpmlUYOOT9aUgkgjjnvQA7J4zjijDdvypOfTmhScdPxoAcAc459aUD8D70bvfPvRvBGc//AFqAIrqMSREbsAcnjNcpfRK9xFbt0dxv/wB3GT+ma7BgGQpzhhg4PNctrX+j3jMEUusR8vPYnAB/AZNAFDWrl7q4extuEDfvWGOW6hfoP5/Sr+i6X5aGWZD8o+6f4jnvVbRbFpGB+UBegAOWPqfp611tnF5cIBGD1oAdGgVQNoBHXIqRRtBJ5NKD2xQeKADB6AnH0pOSRml5xQcjtQAh9D261GTjk9KecY6//XqIg9CaAF3dhnH1ppJwOO9BI/8Ar0wtlcnigBwOecj/AApcnjkc/rUeRuyMUpJHHWgBSeefypu4Gmscde9IOuDjNAChj15yOgoJwpOKQ8Ej05IxSOv0/OgBN24EAcZoBb070KeMcrSZIxxnNAEn4iim8/3ZPzooAuaoG/tG46YMjHA69arFSeOuPWrupqft9wOf9Yx4+tVwmAD1yaAIiBngU7aDxilIIOBzn0pwTOMjFAEYX8qdjHSn7MduAe9LjJ4BFADSucZFOxzQRjGMZ7UDJ55zQAY7MccdM80uOOvFKqdGwM9j3pwUlfrQA0D5ccHFLxjFOK9sYNIAT81AAOfx70jKMcH8adgjoKXB5xQA0ce9ULnTIrq4aWYg5AC/7OM5P61olc9jxSbOlAEcVvFCMIqgZ7Cpc8dKQAjHT86djHB578UAIcZ5NB9j+FLgelIRnmgBKUjPf64o2nnjikOQD1oAafqfrTH6AYPvSk56U05689aAGE8cn64ppzye9ObOOOaj7YJxmgBPmx0wetB6A9KXtjtTcnpg4PFABg9S2KQE5PHtnNALcg8H+dHccHHQ8UAAYdccZxRj588nijjjjGaU4yNuckcE9KAAZK4JOOopRknG4GmtlhgkfSnYKnoBQA7Y394fnRRuHp/49RQB/9k=";

            $data = base64_decode($base64);
            $im = imagecreatefromstring($data);

            $date = new DateTime();

            $path = null;

            if ($im != FALSE) {

                $path = "files/images/".$date->getTimestamp().".jpg";
                imagejpeg($im, $path);
                //imagejpeg($im, "../".$path);
                //$jsonDataArr = array('avatar'=>$path, 'email'=>$cms_users->email);

            } else {
                $errorCode = 1;
            }

            $jsonResponse = array( 'error_code'=>$errorCode );

            if ( !is_null($path) ) {
                $jsonResponse['url'] = $path;
            }

            echo json_encode( $jsonResponse );
        }

        public function reqPutInventory($jsonInventory) {

            Inventories::updateViaJson($jsonInventory);
        }

        public function setGroup() {
            $inventories = Inventories::all();

            foreach($inventories as $inventory) {
                $inventory->idgroup = 1;
                $inventory->save();
                //var_dump($inventories);
            }
        }

        public function commitInventories($idAdmin, $idPoint, $json) {

            $date = date('Y-m-d');
            $shiftDB = Shift::create( array('idAdmin'=>$idAdmin, 'idPoint'=>$idPoint, 'shiftDate'=>$date) );

            $inventoriesArr = json_decode($json, TRUE);

            $resultArr = array();

            if ( !is_null($shiftDB) ) {
                $shiftArr = $shiftDB->toArray();

                array_push($shiftArr, $inventoriesArr);
                array_push($resultArr, $shiftArr);

            }

            $fp = fopen('files/commits/shift/shift_'.$shiftDB->id.'_'.$date.'.json', 'w');
             fwrite( $fp, json_encode( $resultArr ) );

             fclose($fp);

            echo json_encode($shiftDB->toArray());
        }

        public function utilSetCountRents() {
            //$clientArr = Client::all(array('conditions'=>'count_rents is null'));
            $clientArr = Client::all(array('count_rents'=>-1));

            foreach( $clientArr as $client ) {

                $rentsArrDB = Rent::all( array('idClient'=>$client->id) );

                $count = count($rentsArrDB);
                echo $count;

                $client->count_rents = $count;
                $client->save();

            }
        }

        public function utilRecountInventory() {

            $inventoryDBArr = Inventories::all();
            echo "<br><br> Inventory count: ".count($inventoryDBArr)."<br><br>";

            $i = 0;

            foreach ($inventoryDBArr as $inventoryDB) {
                $rentDBArr = Rent::all(array('idInventory'=>$inventoryDB->id));

                $count = count($rentDBArr);

                echo $inventoryDB->model.": ".$count."<br>";
                $inventoryDB->count_rent = $count;
                //$inventoryDB->save();
                $i++;
            }

            echo "<br><br>Make iterations: ".$i;
        }

        public function dbgCreateClient() {

            $clientArrDB = Client::all(array('serverId'=>'N/A'));

            print_r($clientArrDB);

            foreach($clientArrDB as $c) {
                $c->serverid =Client::getGUID();
                $c->save();
            }
        }
};
?>
