<?
ini_set("display_errors", "1");
error_reporting(E_ALL);

require 'Slim/Slim.php';
require 'utils/ManagerDB.php';
require 'utils/ParserCSV.php';


\Slim\Slim::registerAutoloader();

$app = new \Slim\Slim();

$app->get('/',
    function () {
        echo "Connect to api server";
    }
);

$app->post('/auth',
    function () use($app) {
      
      $paramLogin = $app->request->post('login'); 
      $paramPassword = $app->request->post('password');
      $paramRegId = $app->request->post('regId');
      
      
        if (is_null($paramLogin) || is_null($paramPassword)) {
            echo json_encode( array( 'error'=>"Not found variables, you must send parameter with following name: 'login', 'password'" ) );                
        } else {
            $managerDB = new ManagerDB();
            $managerDB->authAdmin($paramLogin, $paramPassword, $paramRegId);
            
        }
      
    }
);

$app->post('/accept_shift',
    function () use($app) {
      
      $paramPoint = $app->request->post('point'); 
      $paramUserId = $app->request->post('user_id');
      
      if (is_null($paramPoint) || is_null($paramUserId)) {
            echo json_encode( array( 'error'=>"Not found variables, you must send parameter with following name: 'point', 'user_id'" ) );
      } else {
        $managerDB = new ManagerDB();
        $managerDB->reqAcceptShift($paramPoint, $paramUserId);    
      }
    }
);

$app->post('/logout',
    function () use($app) {
      $paramIdShift = $app->request->post('shift_id'); 
      
      if ( is_null($paramIdShift) ) {
            echo json_encode( array( 'error'=>"Not found variables, you must send parameter with following name: 'shift_id'" ) );
      } else {
            $managerDB = new ManagerDB();
            $managerDB->reqLogout( $paramIdShift );          
      }
    }
);

$app->get('/rents/:pointId/:date',
    function ($pontId, $date) {
      
      $managerDB = new ManagerDB();
      $managerDB->reqGetRents( $pontId, $date );
      
    }
);

$app->get('/rents',
    function () {
      
      $managerDB = new ManagerDB();
      $managerDB->reqGetAllRents();//reqGetRents( $pontId, $date );
      
    }
);

$app->post('/add_rents',
    function () use($app) {
      
      $paramId = $app->request->post('id'); 
      $rentsJsonString = $app->request->post('rents'); 
      
      if ( is_null( $paramId ) || is_null( $rentsJsonString ) ) {
            echo json_encode( array( 'error'=>"Not found variables, you must send parameter with following name: 'id', 'rents'" ) );
      } else {
            $managerDB = new ManagerDB();
            $managerDB->reqAddRents($paramId, $rentsJsonString);          
      }
                
    }
);

$app->post('/add_rent', 
    function () use($app) {
      
      $idAdmin = $app->request->post('id_admin');
      $json = $app->request->post('json_rent'); 
      
      // $idAdmin = 8;
      // $json = '{"client":{"avatar":"files/images/1443864226.jpg","surname":"Потапович","phone":"8885544321","name":"Валентин","countRents":0,"summ":999,"blackList":-1},"endTime":1443951679936,"inventory":{"tarif":{"sumDay":-1,"sumPerHour":0,"sumTsDay":0},"idGroup":1,"idParent":1,"model":"Лонгборд viol","number":"d5","points":{"address":"addr1","title":"Title1"},"serverId":"137","countRents":0},"isCompleted":0,"token":"8645960203193091443865259447"}';
      
      $managerDB = new ManagerDB();
      $managerDB->reqAddRentItem($idAdmin, $json);    
      
      setLogToFile($json);      
                
    }
);

$app->get('/dbg/add_rent', 
    function () use($app) {
      
      $idAdmin = $app->request->post('id_admin');
      //$json = $app->request->post('json_rent'); 
      $json = '{"client":{"avatar":"N/A","vipNumber":"N/A","name":"N/A","phone":"9381279785","serverId":"5BDEE409-D029-3A98-EE1E-6CA145","summ":0,"blackList":0},"endTime":1450289384674,"inventory":{"tarif":{"name":"","sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":20,"model":"stels navigator 500","number":"43","numberFrame":"xl 11 0032956","serverId":"91","state":1,"count_rent":426},"isCompleted":0,"token":"8693460121370371450285560719","paidFine":0,"startTime":1450285784674,"surcharge":50}';      
      // $idAdmin = 8;
      // $json = '{"client":{"avatar":"files/images/1443864226.jpg","surname":"Потапович","phone":"8885544321","name":"Валентин","countRents":0,"summ":999,"blackList":-1},"endTime":1443951679936,"inventory":{"tarif":{"sumDay":-1,"sumPerHour":0,"sumTsDay":0},"idGroup":1,"idParent":1,"model":"Лонгборд viol","number":"d5","points":{"address":"addr1","title":"Title1"},"serverId":"137","countRents":0},"isCompleted":0,"token":"8645960203193091443865259447"}';
      
      $managerDB = new ManagerDB();
      $managerDB->reqAddRentItem($idAdmin, $json);    
      
      setLogToFile($json);      
                
    }
);

$app->post('/add_image', 
    function () use($app) {
      
      $base64 = $app->request->post('base_64'); 
      
      $managerDB = new ManagerDB();
      $managerDB->reqAddImage( $base64 );          
                
    }
);

$app->put('/rent', 
    function () use($app) {
          
      $idAdmin = $app->request->put('id_admin');
      $json = $app->request->put('json_rent'); 

      //$idAdmin = '1';
      //$json = '{"client":{"surname":"гусев ","phone":"9034067728","name":"дмитрий","countRents":9,"summ":999,"blackList":-1},"endTime":1445169035596,"inventory":{"tarif":{"sumDay":200,"sumPerHour":0,"sumTsDay":0},"idGroup":1,"idParent":138,"model":"Велосипед blue","number":"d9","points":{"address":"addr1","title":"Title1"},"serverId":"141","countRents":1},"isCompleted":1,"token":"8693460121370371445082602404","paidFine":0,"startTime":0,"surcharge":0}';

      setLogToFile("PUT RENT ".$json);
      
      $managerDB = new ManagerDB();
      $managerDB->reqUpdateRentItem($idAdmin, $json);          

    }
);

$app->get('/dbg/put/rent', 
    function ()  {
          
      $idAdmin = 1;//$app->request->put('id_admin');
      //$json = '{"client":{"vipNumber":"N/A","name":"","phone":"9889464785","serverId":2,"blackList":0},"endTime":1448186119439,"inventory":{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":1,"model":"Giant Single Simple","number":"12","numberFrame":"c12l3766","serverId":"130","state":1,"count_rent":1},"isCompleted":1,"token":"8645960203193091448182485878","paidFine":0,"startTime":1448182519439,"surcharge":22}'; //$app->request->put('json_rent'); 
      $json = '{"client":{"avatar":"N/A","vipNumber":"N/A","name":"N/A","phone":"9889464785","serverId":"56F379A3-D18D-87CB-25BD-38B485","summ":3224,"blackList":0},"endTime":1450306224168,"inventory":{"tarif":{"name":"","sumDay":150,"sumHour":50,"sumTsHour":350},"idGroup":1,"idParent":20,"model":"stels navigator 850","number":"40","numberFrame":"xl12s086867","serverId":"102","state":4,"count_rent":210},"isCompleted":1,"token":"8693460121370371450299980222","serverId":"17E7376C-07A5-E5BE-83E3-532F64C0EBCF}","paidFine":0,"startTime":1450302564168,"surcharge":50}';

            //$idAdmin = '1';
      //$json = '{"client":{"surname":"гусев ","phone":"9034067728","name":"дмитрий","countRents":9,"summ":999,"blackList":-1},"endTime":1445169035596,"inventory":{"tarif":{"sumDay":200,"sumPerHour":0,"sumTsDay":0},"idGroup":1,"idParent":138,"model":"Велосипед blue","number":"d9","points":{"address":"addr1","title":"Title1"},"serverId":"141","countRents":1},"isCompleted":1,"token":"8693460121370371445082602404","paidFine":0,"startTime":0,"surcharge":0}';

      //setLogToFile("PUT RENT ".$json);
      
      $managerDB = new ManagerDB();
      $managerDB->reqUpdateRentItem($idAdmin, $json);          

    }
);

//$app->get(
$app->post('/add_client', 
    function () use($app) {
      
      $json = $app->request->post('json_client'); 
      
      //$json = '{"surname":"Dhs","phone":"987456","name":"Shs","countRents":-1,"summ":-1,"blackList":-1}';
      //$json = '{"avatar":"files/images/1443691032.jpg","surname":"F","phone":"5","name":"S","countRents":-1,"summ":-1,"blackList":-1}';
      
      $managerDB = new ManagerDB();
      $managerDB->reqAddClientItem($json);
    
    }
);

$app->get('/dbg/add_client', 
    function () use($app) {
      
      //$json = $app->request->post('json_client'); 
      
      $json = '{"vipNumber":"N/A","surname":"tst","name":"Test 2","phone":"9995554488","summ":0,"blackList":0}';
      
      
      $managerDB = new ManagerDB();
      $managerDB->reqAddClientItem($json);
    
    }
);

$app->put('/client/:id', 
    function ($id) use($app) {
      
      $json = $app->request->put('json_client'); 
      $managerDB = new ManagerDB();
      $managerDB->reqRedactClientItem($id, $json);
    
    }
);

$app->get('/admin/shifts/:idAdmin', 
    function ($idAdmin) {
            
      $managerDB = new ManagerDB();
      $managerDB->reqGetShifts($idAdmin);;
    
    }
);

$app->put('/shift/:id', 
    function ($id) use($app) {
      
      $json = $app->request->put('json_shift'); 
      $managerDB = new ManagerDB();
      $managerDB->reqUpdateShift( json_decode($json) );
    
    }
);

$app->get('/shift/:id', 
    function ($id) {
      
      $json = '{"serverId":47,"state":1}'; //$app->request->put('json_shift'); 
      $managerDB = new ManagerDB();
      $managerDB->reqUpdateShift( json_decode($json) );
    
    }
);

$app->post('/inventory/commit/:idPoint', 
    function ($idPoint) use($app) {
            
      $json = $app->request->post('json_inventory_arr');
      $idAdmin = $app->request->post('admin_id');
      
      setLogToFile("Admin ID: "+$idAdmin+" JSON: "+$json);
      //setLogToFile(" JSON: "+$json);
      
      $managerDB = new ManagerDB();
      $managerDB->commitInventories($idAdmin, $idPoint, $json);
    
    }
); 

$app->get('/dbg/inventory/commit',  
    function () {
            
      //$json = $app->request->post('json_inventory_arr');
      $idAdmin = 338;       
      $json = '[{"idGroup":1,"idParent":0,"model":"Велосипеды","serverId":"1","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"Скейтборды","serverId":"2","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"КОНЬКИ РОЛИКОВЫЕ (Р)","serverId":"3","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"КЛЮЧИ","serverId":"4","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"Шестигранники","serverId":"5","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"СНОУБОРДЫ (С)","serverId":"6","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"БОТЫ Сноуборд. (БС)","serverId":"7","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"Крепления сноуборд ","serverId":"8","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"ЛЫЖИ БЕГОВЫЕ (ЛБ)","serverId":"9","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"БОТЫ Лыжные (БЛБ)","serverId":"10","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"Палки Лыжные (ПЛБ)","serverId":"11","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"экстрим видео камеры","serverId":"12","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"спальный мешок(сп)","serverId":"13","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"палатка туристическа","serverId":"14","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"Шлемы","serverId":"15","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"Детские кресла","serverId":"16","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"ЗАМКИ","serverId":"17","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"ФОНАРИ","serverId":"18","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"Прочее оборудование ","serverId":"19","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"Отсутствующие велоси","serverId":"20","state":4,"count_rent":0},{"idGroup":1,"idParent":0,"model":"отсутствующие палатк","serverId":"21","state":4,"count_rent":0},{"tarif":{"name":"Gopro 3+","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":0,"model":"7","serverId":"30","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":0,"model":"9","serverId":"31","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":0,"model":"10","serverId":"32","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":0,"model":"10","serverId":"33","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":0,"model":"12","serverId":"34","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":0,"model":"14","serverId":"35","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":0,"model":"17","serverId":"36","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":0,"model":"18","serverId":"37","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":0,"model":"20","serverId":"38","state":4,"count_rent":0},{"tarif":{"sumDay":700,"sumHour":250,"sumTsHour":1000},"idGroup":1,"idParent":0,"model":"2","number":"","numberFrame":"","serverId":"66","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":1,"model":"Stels Navigator 290","number":"179","numberFrame":"S14A031664","serverId":"256","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Тrek детский прицеп","number":"180","numberFrame":"WTU073CS0145J","serverId":"257","state":2,"count_rent":0},{"tarif":{"sumDay":500,"sumHour":100,"sumTsHour":700},"idGroup":1,"idParent":1,"model":"Желтый велик детский","number":"182","serverId":"258","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":1,"model":"Stels Navigator 570","number":"183","numberFrame":"S6B006007","serverId":"259","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Союз","number":"184","numberFrame":"S710096593","serverId":"260","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Cinelli-185","number":"185","numberFrame":"6MCL532364","serverId":"261","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":1,"model":"Kross level A2","number":"186","numberFrame":"LY2A011914 (EN14766)","serverId":"262","state":4,"count_rent":0},{"tarif":{"sumDay":150,"sumHour":50,"sumTsHour":350},"idGroup":1,"idParent":1,"model":"Stels Navigator 930","number":"187","numberFrame":"S07C116274","serverId":"263","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Стриж синий детский","number":"189","numberFrame":"WL059363","serverId":"264","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"polygon blizzard","number":"190","numberFrame":"81027777","serverId":"265","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Schwinn Corvette ","number":"1","numberFrame":"snmng11m87416","serverId":"119","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Schwinn Corvette ","number":"2","numberFrame":"snmng11m87447","serverId":"120","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Schwinn Cruiser One","number":"4","numberFrame":"ibdfsd12k5552","serverId":"123","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Schwinn Cruiser One ","number":"5","numberFrame":"ibdfsd12k5957","serverId":"124","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":1,"model":"Schwinn Cruiser One ","number":"6","numberFrame":"ibdfsd12k3416","serverId":"126","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":1,"model":"Giant Single Simple","number":"10","numberFrame":"c12l3778","serverId":"128","state":4,"count_rent":0},{"tarif":{"sumDay":800,"sumHour":300,"sumTsHour":1200},"idGroup":1,"idParent":1,"model":"Giant Single Simple","number":"11","numberFrame":"c22d0898","serverId":"129","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":1,"model":"Giant Single Simple","number":"12","numberFrame":"c12l3766","serverId":"130","state":1,"count_rent":5},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":1,"model":"Sibvelz","number":"13","numberFrame":"tj0920251","serverId":"131","state":4,"count_rent":0},{"tarif":{"sumDay":800,"sumHour":300,"sumTsHour":1200},"idGroup":1,"idParent":1,"model":"Sibvelz","number":"14","numberFrame":"tj0920208","serverId":"132","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Sibvelz","number":"15","numberFrame":"tj0920341","serverId":"133","state":4,"count_rent":0},{"tarif":{"sumDay":500,"sumHour":100,"sumTsHour":700},"idGroup":1,"idParent":1,"model":"Sibvelz","number":"16","numberFrame":"tj0920066","serverId":"134","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Sibvelz","number":"17","numberFrame":"pl1105035398","serverId":"135","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Sibvelz","number":"18","numberFrame":"tj0920247","serverId":"136","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":1,"model":"Sibvelz","number":"19","numberFrame":"pl1105035297","serverId":"137","state":4,"count_rent":0},{"tarif":{"sumDay":500,"sumHour":100,"sumTsHour":700},"idGroup":1,"idParent":1,"model":"Sibvelz","number":"21","numberFrame":"pl1105035477","serverId":"138","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Sibvelz","number":"22","numberFrame":"pl1105035582","serverId":"139","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Sibvelz Woman","number":"25","numberFrame":"tj0909409","serverId":"140","state":4,"count_rent":0},{"tarif":{"sumDay":500,"sumHour":100,"sumTsHour":700},"idGroup":1,"idParent":1,"model":"Sibvelz Woman","number":"24","numberFrame":"tj0909364","serverId":"141","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Sibvelz Woman","number":"26","numberFrame":"tj0908365","serverId":"142","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Sibvelz Woman","number":"23","numberFrame":"tj0907891","serverId":"143","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":1,"model":"Trek Drift Surf","number":"27","numberFrame":"007c0085d","serverId":"144","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"KHS Alite 150","number":"31","numberFrame":"v12d03514","serverId":"146","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":1,"model":"Bergamont vitox 5.3","number":"35","numberFrame":"aa21024416","serverId":"150","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":1,"model":"stels pilot 720","number":"42","numberFrame":"xd71216885","serverId":"155","state":4,"count_rent":0},{"tarif":{"name":"Gopro 3+","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stels navigator 500","number":"45","numberFrame":"xl 11603224","serverId":"156","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stels navigator 500","number":"46","numberFrame":"xl 10052844","serverId":"157","state":4,"count_rent":0},{"tarif":{"sumDay":500,"sumHour":100,"sumTsHour":700},"idGroup":1,"idParent":1,"model":"stels navigator 500","number":"49","numberFrame":"xl 1o035312","serverId":"159","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"stels navigator 500","number":"50","numberFrame":"WOLP53865","serverId":"160","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stels navigator 440","number":"52","numberFrame":"xl 12j017100","serverId":"161","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":1,"model":"stels navigator 310","number":"53","numberFrame":"w120711015","serverId":"162","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stels navigator 310","number":"54","numberFrame":"kl 11l006439","serverId":"163","state":4,"count_rent":0},{"tarif":{"sumDay":800,"sumHour":300,"sumTsHour":1200},"idGroup":1,"idParent":1,"model":"giant boulder 3","number":"56","numberFrame":"c11j0107","serverId":"165","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"giant snap","number":"57","numberFrame":"c12a6763","serverId":"166","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":1,"model":"giant snap","number":"58","numberFrame":"c32l1110","serverId":"167","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"nordway cascade","number":"59","numberFrame":"8841206720","serverId":"168","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"nordway gt70","number":"60","numberFrame":"yhy2021793","serverId":"169","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"nordway active","number":"61","numberFrame":"at81122332","serverId":"170","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"nordway trail","number":"63","numberFrame":"k70830112","serverId":"171","state":4,"count_rent":0},{"tarif":{"sumDay":150,"sumHour":50,"sumTsHour":350},"idGroup":1,"idParent":1,"model":"stern dynamik 1.0","number":"64","numberFrame":"v897253","serverId":"172","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stern dynamik 2.0","number":"65","numberFrame":"v391580","serverId":"173","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"kross trans siberian","number":"68","numberFrame":"ly2c031154","serverId":"175","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stark","number":"76","numberFrame":"saipk01662","serverId":"180","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":1,"model":"nordway cascade","number":"79","numberFrame":"SF31263800","serverId":"181","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Skystyle Woman","number":"81","numberFrame":"u5048030","serverId":"182","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Murray","number":"82","numberFrame":"1102109","serverId":"183","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Scope","number":"83","numberFrame":"yla13130a","serverId":"184","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Haro BMX","number":"84","numberFrame":" POW8J0886","serverId":"185","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Trek 3700","number":"87","numberFrame":"TBI-0406 CB3D0395","serverId":"186","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Stern Motion 3.0","number":"89","numberFrame":"AT111107415","serverId":"187","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"TopGear Sigma 225","number":"90","numberFrame":"U5048030","serverId":"188","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":120,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"stels navigator 500","number":"91","numberFrame":"S08A027084","serverId":"189","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stels navigator 610","number":"92","numberFrame":"XL13L058083","serverId":"190","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stels navigator 610","number":"93","numberFrame":"xL13L050260","serverId":"191","state":4,"count_rent":0},{"tarif":{"sumDay":800,"sumHour":300,"sumTsHour":1200},"idGroup":1,"idParent":1,"model":"stels navigator 610","number":"96","numberFrame":"XL13L067047","serverId":"192","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":1,"model":"stels navigator 610","number":"97","numberFrame":"XL13L056515","serverId":"193","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":1,"model":"stels navigator 610","number":"98","numberFrame":"XL13L051604","serverId":"194","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stels navigator 610","number":"99","numberFrame":"XL13L051928","serverId":"195","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"nordway uptown red","number":"103","numberFrame":"K60738237","serverId":"196","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Электро велосипед","number":"105","serverId":"197","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Schwinn Roadster tri","number":"108","numberFrame":"JNJ13H22541","serverId":"198","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Schwinn Voyageur 2","number":"110","numberFrame":"CM13 G560 761","serverId":"199","state":4,"count_rent":0},{"tarif":{"sumDay":500,"sumHour":100,"sumTsHour":700},"idGroup":1,"idParent":1,"model":"Schwinn Voyageur 2","number":"111","numberFrame":"CM13 M547 265","serverId":"200","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Schwinn Voyageur 2","number":"112","numberFrame":"CM13 M547 266","serverId":"201","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Schwinn Voyageur 2","number":"113","numberFrame":"CM13 M547 250.","serverId":"202","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Schwinn Voyageur 2","number":"114","numberFrame":"CM13 M547 255","serverId":"203","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":1,"model":"Schwinn Voyageur 2","number":"115","numberFrame":"CM13 G532 844","serverId":"204","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Schwinn Voyageur 2","number":"116","numberFrame":"CM13 G532 788","serverId":"205","state":4,"count_rent":0},{"tarif":{"name":"Gopro 3+","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Schwinn Voyageur 2","number":"117","numberFrame":"CM13 M547 257","serverId":"206","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Schwinn Voyageur 2","number":"118","numberFrame":"CM13 G532 815","serverId":"207","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Schwinn Voyageur 2","number":"119","numberFrame":"CM13 M547 261","serverId":"208","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Schwinn Tango Tandem","number":"120","numberFrame":"CM13L547412","serverId":"209","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Schwinn Tango Tandem","number":"121","numberFrame":"CM13L547391","serverId":"210","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":1,"model":"Stels  NАVIGATOR ","number":"122","numberFrame":"Z014763813","serverId":"211","state":4,"count_rent":0},{"tarif":{"sumDay":500,"sumHour":100,"sumTsHour":700},"idGroup":1,"idParent":1,"model":"Stels  NАVIGATOR ","number":"123","numberFrame":"ZO12724413","serverId":"212","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":1,"model":"Stels  NАVIGATOR ","number":"124","numberFrame":"XL13JO38142","serverId":"213","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Stels  NАVIGATOR ","number":"125","numberFrame":"XL13JO38000","serverId":"214","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":1,"model":"Stels  NАVIGATOR ","number":"126","numberFrame":"Z014649613","serverId":"215","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":1,"model":"Stels  NАVIGATOR ","number":"127","numberFrame":"z0263723","serverId":"216","state":4,"count_rent":0},{"tarif":{"name":"Gopro 3+","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"рикша","number":"129","numberFrame":"w120739067","serverId":"217","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Pigeon special ","number":"130","numberFrame":"FC100327","serverId":"218","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":1,"model":"Рикша","number":"131","numberFrame":"","serverId":"219","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"trek alpaha","number":"132","numberFrame":"336C0129D/TDI-2193","serverId":"220","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Trek alpha 3900","number":"134","numberFrame":"WTU 022C0015A","serverId":"221","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":1,"model":"stels navigator 610","number":"135","numberFrame":"XL13J066336","serverId":"222","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stels navigator 610","number":"136","numberFrame":"XL13KO36654","serverId":"223","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stels navigator 610","number":"137","numberFrame":"XL13ko36945","serverId":"224","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stels navigator 610","number":"138","numberFrame":"XL13KO36945","serverId":"225","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stels miss 6000","number":"145","numberFrame":"XL13KO8788","serverId":"226","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stels miss 6000","number":"146","numberFrame":"XL13KO8691","serverId":"227","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"stels miss 6000","number":"147","numberFrame":"XL13KO8823","serverId":"228","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stels miss 6000","number":"148","numberFrame":"XL13KO8847","serverId":"229","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":1,"model":"Stern 1. 0","number":"149","numberFrame":"VM613210","serverId":"230","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Trek Mystic голубой ","number":"150","numberFrame":"WTU284P0386C","serverId":"231","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Самокат Кусты","number":"151","serverId":"232","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Детский прицеп для в","number":"152","serverId":"233","state":4,"count_rent":0},{"tarif":{"sumDay":150,"sumHour":50,"sumTsHour":350},"idGroup":1,"idParent":1,"model":"Look","number":"153","numberFrame":"55k113504","serverId":"234","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"stern dynamic ","number":"154","numberFrame":"v412632","serverId":"235","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":" bulls wildcross aa3","number":"157","numberFrame":"OO160601","serverId":"236","state":4,"count_rent":0},{"tarif":{"sumDay":150,"sumHour":50,"sumTsHour":350},"idGroup":1,"idParent":1,"model":"del sol ","number":"160","numberFrame":"XDS1420798","serverId":"237","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"del sol ","number":"161","numberFrame":"XDS1420813","serverId":"238","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"del sol ","number":"159","numberFrame":"XDS1420841","serverId":"239","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Electra bicyclecd","number":"162","numberFrame":"EAC 2400500","serverId":"240","state":4,"count_rent":0},{"tarif":{"sumDay":700,"sumHour":250,"sumTsHour":1000},"idGroup":1,"idParent":1,"model":"Electra","number":"163","numberFrame":"LW2D06913","serverId":"241","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":1,"model":"Stels Navigator 410","number":"165","numberFrame":"xd6100975","serverId":"242","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Stels Navigator 340","number":"166","numberFrame":"XL13J039040","serverId":"243","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":1,"model":"Stels Navigator 340","number":"167","numberFrame":"XL13J039250","serverId":"244","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Stels Navigator 340","number":"168","numberFrame":"XL13I029773","serverId":"245","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Stels Navigator 340","number":"169","numberFrame":"XL13J038870","serverId":"246","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Stels Navigator 340","number":"170","numberFrame":"XL13J038458","serverId":"247","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":1,"model":"Stels Navigator 340","number":"171","numberFrame":"XL13J040064","serverId":"248","state":4,"count_rent":0},{"tarif":{"name":"Gopro 3+","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Stels Navigator 340","number":"172","numberFrame":"XL13J038827","serverId":"249","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":1,"model":"Stels Navigator 340","number":"173","numberFrame":"XL13J038930","serverId":"250","state":4,"count_rent":0},{"tarif":{"sumDay":150,"sumHour":50,"sumTsHour":350},"idGroup":1,"idParent":1,"model":"Stels Navigator 340","number":"174","numberFrame":"XL13J038030","serverId":"251","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Stels Navigator 340","number":"175","numberFrame":"XL13J039218","serverId":"252","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Stels Navigator 130","number":"176","numberFrame":"S13G000452","serverId":"253","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":1,"model":"Stels Navigator 130","number":"177","numberFrame":"S13G000470","serverId":"254","state":4,"count_rent":0},{"tarif":{"sumDay":500,"sumHour":100,"sumTsHour":700},"idGroup":1,"idParent":1,"model":"Stels Navigator 290","number":"178","numberFrame":"S14A031753","serverId":"255","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"element active","number":"67","numberFrame":"v688021","serverId":"174","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"Schwinn Tango Tandem","number":"9","numberFrame":"cm13a503287","serverId":"121","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"specialized","number":"71","numberFrame":"p8jhb0053","serverId":"176","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"Schwinn Cruiser One ","number":"7","numberFrame":"ibdfsd12k3406","serverId":"127","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"Bergamont vitox 5.3","number":"38","numberFrame":"aa21024350","serverId":"153","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"KHS Alite 150","number":"30","numberFrame":"v12d05295","serverId":"145","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"haro flightline","number":"73","numberFrame":"k9kb002484","serverId":"178","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"giant boulder 3","number":"55","numberFrame":"c11O2193","serverId":"164","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"stels navigator 500","number":"47","numberFrame":"xl11c032877","serverId":"158","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"Bergamont vitox 5.3","number":"37","numberFrame":"aa26024887","serverId":"152","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"Schwinn Cruiser One ","number":"8","numberFrame":"ibdfsd12k5824","serverId":"125","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"Bergamont vitox 5.3","number":"34","numberFrame":"aa21024362","serverId":"149","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"Bergamont vitox 5.3","number":"36","numberFrame":"aa21024403","serverId":"151","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"Bergamont vitox 5.3","number":"39","numberFrame":"aa21024324","serverId":"154","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"gt agressor","number":"75","numberFrame":"mngs007100513","serverId":"179","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"rockrider 5.2","number":"72","numberFrame":"bi-1623","serverId":"177","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"Bergamont vitox 6.3","number":"32","numberFrame":"bgm120919856","serverId":"147","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"Bergamont vitox 6.3","number":"33","numberFrame":"bgm120919859","serverId":"148","state":4,"count_rent":0},{"idGroup":1,"idParent":1,"model":"Schwinn Cruiser One","number":"3","numberFrame":"ibdfsd12k5520","serverId":"122","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":2,"model":"Лонгборд","number":"500","numberFrame":"","serverId":"266","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":2,"model":"Скейтборд ROGERS","number":"501","numberFrame":"","serverId":"267","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":2,"model":"Скейтборд KROWN","number":"502","numberFrame":"","serverId":"268","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":2,"model":"Самокат взрослый чер","number":"550","numberFrame":"","serverId":"269","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":2,"model":"Самокат детский оран","number":"551","numberFrame":"","serverId":"270","state":4,"count_rent":0},{"tarif":{"sumDay":150,"sumHour":50,"sumTsHour":350},"idGroup":1,"idParent":2,"model":"Спмокат детский золо","number":"552","numberFrame":"","serverId":"271","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":2,"model":"самокат детский зеле","number":"553","numberFrame":"T9101R0343","serverId":"272","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":3,"model":"(кр)magnum m1","number":"600","numberFrame":"10 us (43)","serverId":"273","state":4,"count_rent":0},{"tarif":{"sumDay":500,"sumHour":100,"sumTsHour":700},"idGroup":1,"idParent":3,"model":"amigo sport ","number":"601","numberFrame":"35-38","serverId":"274","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":3,"model":"(кр)african leopard","number":"602","numberFrame":"28-31","serverId":"275","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":3,"model":"(кр)maxcity spark","number":"603","numberFrame":"40-43","serverId":"276","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":3,"model":"(кр)maxAmigo","number":"604","numberFrame":"38-41","serverId":"277","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":3,"model":"(кр)fiestaб","numberFrame":"40-43","serverId":"22","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":3,"model":"(кр)красные детские","number":"605","serverId":"278","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":3,"model":"(кр)кислотные железн","number":"606","serverId":"279","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":3,"model":"(кр)фиолетовые желез","number":"607","serverId":"280","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":3,"model":"(кр)in like skates","number":"0","numberFrame":"33-36","serverId":"25","state":4,"count_rent":0},{"tarif":{"sumDay":700,"sumHour":250,"sumTsHour":1000},"idGroup":1,"idParent":3,"model":"maxcity розовые ","number":"611","numberFrame":"38-41","serverId":"281","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":3,"model":"maxcity синие","number":"612","numberFrame":"35-38","serverId":"282","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":3,"model":"active fit. 3","number":"613","numberFrame":"37","serverId":"283","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":3,"model":"(кр)maxcity","number":"614","numberFrame":"46","serverId":"284","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":3,"model":"maxAmigo (черно-сини","number":"619","numberFrame":"38-41","serverId":"285","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":3,"model":"oxelo черно-белые ра","number":"621","numberFrame":"42","serverId":"286","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":3,"model":"Re:action серые разм","number":"622","numberFrame":"39","serverId":"287","state":4,"count_rent":0},{"tarif":{"sumDay":150,"sumHour":50,"sumTsHour":350},"idGroup":1,"idParent":3,"model":"(кр)красно-белые дет","number":"620","serverId":"288","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":3,"model":"oxelo фиолетовые","number":"608","numberFrame":"35-38","serverId":"289","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":3,"model":"oxelo черно-белые","number":"609","numberFrame":"38","serverId":"290","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":3,"model":"oxelo черно-розовые","number":"610","numberFrame":"41","serverId":"291","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":3,"model":"sport collection син","number":"615","numberFrame":"44","serverId":"292","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":3,"model":"max city 40-43р желт","number":"616","numberFrame":"43","serverId":"293","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":3,"model":"max city желто-белые","number":"617","numberFrame":"35-38","serverId":"294","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":3,"model":"38-41сине-белые max ","number":"618","numberFrame":"38-41","serverId":"295","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":4,"model":"13 мм","numberFrame":"","serverId":"296","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":4,"model":"14 мм ","numberFrame":"","serverId":"297","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":4,"model":"15 мм","numberFrame":"","serverId":"298","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":4,"model":"15 мм монтажка ","numberFrame":"","serverId":"299","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":4,"model":"17 мм","numberFrame":"","serverId":"300","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":4,"model":"19 мм","numberFrame":"","serverId":"301","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":4,"model":"разводной 250мм","numberFrame":"","serverId":"302","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":5,"model":"8мм длинный","numberFrame":"","serverId":"303","state":4,"count_rent":0},{"tarif":{"sumDay":700,"sumHour":250,"sumTsHour":1000},"idGroup":1,"idParent":5,"model":"10мм длинный","numberFrame":"","serverId":"304","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":5,"model":"тройной parktool 4, ","numberFrame":"","serverId":"305","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":5,"model":"кареточный ключ","numberFrame":"","serverId":"306","state":4,"count_rent":0},{"tarif":{"sumDay":800,"sumHour":300,"sumTsHour":1200},"idGroup":1,"idParent":5,"model":"BikeHand 30-32-36-40","numberFrame":"","serverId":"307","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":5,"model":"зубило ","numberFrame":"","serverId":"308","state":4,"count_rent":0},{"tarif":{"name":"Gopro 3+","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":5,"model":"монтажки ","numberFrame":"","serverId":"309","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":5,"model":"ножницы","numberFrame":"","serverId":"310","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":5,"model":"штангенциркуль","numberFrame":"","serverId":"311","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":5,"model":"Набор бит 7 шт","numberFrame":"","serverId":"312","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":5,"model":"Канцелярский нож","numberFrame":"","serverId":"313","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":5,"model":"Струбцина ","numberFrame":"","serverId":"314","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":5,"model":"Переходник вело-авто","numberFrame":"","serverId":"315","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":6,"model":"blackfire (с-bf)","numberFrame":"157","serverId":"316","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":6,"model":"Strike Bone(c-sb)","numberFrame":"160","serverId":"317","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":6,"model":"RoyalFlush (c-rf)","numberFrame":"161","serverId":"318","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":6,"model":"SC Oop (c-so)","numberFrame":"160","serverId":"319","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":6,"model":"Black Fire (c- bf)","numberFrame":"159","serverId":"320","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":6,"model":"SpecialLady  (c - sl","numberFrame":"152","serverId":"321","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":6,"model":"BlackFire Scoup 160 ","numberFrame":"160","serverId":"322","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":6,"model":"Bone targa 151","number":"c8","numberFrame":"151","serverId":"323","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":120,"sumTsHour":600},"idGroup":1,"idParent":7,"model":"BlackFire","numberFrame":"42","serverId":"324","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":7,"model":"BlackFire","numberFrame":"41","serverId":"325","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":7,"model":"BlackFire","numberFrame":"44","serverId":"326","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":7,"model":"BlackFire","numberFrame":"40","serverId":"327","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":7,"model":"BlackFire","numberFrame":"39","serverId":"328","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":7,"model":"BlackFire","numberFrame":"43","serverId":"329","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":7,"model":"Bone","numberFrame":"42,5","serverId":"330","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":7,"model":"wed\u0027ze boogey, 37 ","numberFrame":"37","serverId":"331","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":8,"model":"Bone","number":"1110","numberFrame":"33-37","serverId":"332","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":9,"model":"Rossignol  Xtour int","numberFrame":"195","serverId":"333","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":9,"model":"Rossignol  Xtour ven","numberFrame":"170","serverId":"334","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":9,"model":"Rossignol  Xtour ven","numberFrame":"170","serverId":"335","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":10,"model":"Rossignol  Xtour чер","numberFrame":"44","serverId":"336","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":10,"model":"Rossignol  Xtour све","numberFrame":"38","serverId":"337","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":10,"model":"Rossignol  Xtour све","numberFrame":"38","serverId":"338","state":4,"count_rent":0},{"tarif":{"name":"Gopro 3+","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":11,"model":"Leki vasa 145","numberFrame":"145","serverId":"339","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":11,"model":"One way diamant 155","numberFrame":"155","serverId":"340","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":11,"model":"Flash aluminium 150","numberFrame":"150","serverId":"341","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":12,"model":"go pro 3+","numberFrame":"","serverId":"39","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":12,"model":"go pro 2","serverId":"342","state":4,"count_rent":0},{"tarif":{"sumDay":150,"sumHour":50,"sumTsHour":350},"idGroup":1,"idParent":12,"model":"go pro 3","serverId":"343","state":4,"count_rent":0},{"tarif":{"sumDay":500,"sumHour":100,"sumTsHour":700},"idGroup":1,"idParent":13,"model":"спальный мешок","serverId":"345","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":13,"model":"спальный мешок ","serverId":"346","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":13,"model":"спальный мешок","serverId":"347","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":13,"model":"спальный мешок","serverId":"348","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":13,"model":" спальный мешок","serverId":"349","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":13,"model":"спальный мешок","serverId":"350","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":13,"model":"спальный мешок","serverId":"351","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":14,"model":"палатка 2-х местная ","serverId":"40","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":14,"model":"палатка 2-х местная ","serverId":"41","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":14,"model":"палатка 2-х местная ","serverId":"42","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":14,"model":"палатка 2-х местная ","serverId":"43","state":4,"count_rent":0},{"tarif":{"sumDay":800,"sumHour":300,"sumTsHour":1200},"idGroup":1,"idParent":14,"model":"палатка 4-х местная ","serverId":"44","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":14,"model":"палатка 2-х местная ","serverId":"45","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":15,"model":"черный взрослый oxel","serverId":"46","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":15,"model":"черный взрослый CAN ","number":"801","numberFrame":"","serverId":"47","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":15,"model":"черный взрослый oxel","number":"802","numberFrame":"","serverId":"48","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":15,"model":"черный взрослый oxel","number":"803","numberFrame":"","serverId":"49","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":15,"model":"черный взрослый oxel","number":"804","numberFrame":"","serverId":"50","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":15,"model":"черный взрослый clas","number":"805","numberFrame":"","serverId":"51","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":15,"model":"белый взрослый oxell","number":"807","numberFrame":"","serverId":"52","state":4,"count_rent":0},{"tarif":{"sumDay":800,"sumHour":300,"sumTsHour":1200},"idGroup":1,"idParent":15,"model":"белый взрослый oxell","number":"808","numberFrame":"","serverId":"53","state":4,"count_rent":0},{"tarif":{"sumDay":700,"sumHour":250,"sumTsHour":1000},"idGroup":1,"idParent":15,"model":"синий летний пенопла","number":"809","numberFrame":"","serverId":"54","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":15,"model":"белый взрослый oxell","number":"806","serverId":"352","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":15,"model":"голубой детский","serverId":"353","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":15,"model":"красный с голубым де","serverId":"354","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":16,"model":"Кресло детское","number":"701","serverId":"56","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":16,"model":"Кресло детское","number":"703","serverId":"59","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":16,"model":"Кресло детское","number":"705","serverId":"62","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":16,"model":"Кресло детское","number":"700","serverId":"355","state":4,"count_rent":0},{"tarif":{"sumDay":500,"sumHour":100,"sumTsHour":700},"idGroup":1,"idParent":16,"model":"Кресло детское","number":"702","serverId":"356","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":16,"model":"Кресло детское","number":"704","serverId":"357","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":17,"model":"замок с цепью рецайк","number":"900","serverId":"358","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":17,"model":"замок с цепью рецайк","number":"901","serverId":"359","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":17,"model":"замок с цепью рецайк","number":"902","serverId":"360","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":17,"model":"замок с цепью рецайк","number":"903","serverId":"361","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":17,"model":"замок с цепью рецайк","number":"904","serverId":"362","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":17,"model":"замок  бриг","number":"905","serverId":"363","state":4,"count_rent":0},{"tarif":{"name":"Gopro 3+","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":17,"model":"замок трос","number":"906","serverId":"364","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":17,"model":"замок маленький","number":"907","serverId":"365","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":17,"model":"замок тросик тонкий","number":"908","serverId":"366","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1601","serverId":"367","state":4,"count_rent":0},{"tarif":{"sumDay":1000,"sumHour":300,"sumTsHour":1500},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1602","serverId":"368","state":4,"count_rent":0},{"tarif":{"sumDay":700,"sumHour":250,"sumTsHour":1000},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1603","serverId":"369","state":4,"count_rent":0},{"tarif":{"sumDay":500,"sumHour":100,"sumTsHour":700},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1604","serverId":"370","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1605","serverId":"371","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1606","serverId":"372","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1607","serverId":"373","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1608","serverId":"374","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1609","serverId":"375","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1610","serverId":"376","state":4,"count_rent":0},{"tarif":{"sumDay":700,"sumHour":250,"sumTsHour":1000},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1611","serverId":"377","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1612","serverId":"378","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1613","serverId":"379","state":4,"count_rent":0},{"tarif":{"sumDay":800,"sumHour":300,"sumTsHour":1200},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1614","serverId":"380","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1615","serverId":"381","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1616","serverId":"382","state":4,"count_rent":0},{"tarif":{"sumDay":700,"sumHour":250,"sumTsHour":1000},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1617","serverId":"383","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1618","serverId":"384","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1619","serverId":"385","state":4,"count_rent":0},{"tarif":{"sumDay":500,"sumHour":100,"sumTsHour":700},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1620","serverId":"386","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":18,"model":"фонарик BTWIN","number":"1621","serverId":"387","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":19,"model":"Пылесос керхер WD 3.","number":"5000","numberFrame":"","serverId":"388","state":4,"count_rent":0},{"tarif":{"name":"Gopro 3+","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":19,"model":"Трубка от пылесоса К","number":"5001","numberFrame":"","serverId":"389","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":19,"model":"Трубка от пылесоса К","number":"5002","numberFrame":"","serverId":"390","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":19,"model":"Шланг от пылесоса Ке","number":"5003","numberFrame":"","serverId":"391","state":4,"count_rent":0},{"tarif":{"sumDay":800,"sumHour":300,"sumTsHour":1200},"idGroup":1,"idParent":19,"model":"Щетка от пылесоса Ке","number":"5004","numberFrame":"","serverId":"392","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":19,"model":"Мойка Керхер К 2.15","number":"5005","numberFrame":"","serverId":"393","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":19,"model":"Пистолет от мойки Ке","number":"5006","numberFrame":"","serverId":"394","state":4,"count_rent":0},{"tarif":{"sumDay":800,"sumHour":300,"sumTsHour":1200},"idGroup":1,"idParent":19,"model":"Насадка для пистолет","number":"5007","numberFrame":"","serverId":"395","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":19,"model":"Насадка для пистолет","number":"5008","numberFrame":"","serverId":"396","state":4,"count_rent":0},{"tarif":{"sumDay":100,"sumHour":100,"sumTsHour":120},"idGroup":1,"idParent":19,"model":"Шланг от мойки Керхе","number":"5009","numberFrame":"","serverId":"397","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"KHS Alite 150","number":"29","numberFrame":"v12d05353","serverId":"68","state":3,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"stels navigator 810","number":"41","numberFrame":"xl 12c087120","serverId":"69","state":4,"count_rent":0},{"tarif":{"sumDay":150,"sumHour":50,"sumTsHour":350},"idGroup":1,"idParent":20,"model":"stels navigator 500","number":"51","numberFrame":"li 101201893","serverId":"70","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"Pegas","number":"85","numberFrame":"uv06300888","serverId":"73","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"Schwinn Roadster tri","number":"109","numberFrame":"","serverId":"75","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":20,"model":"trek alpaha","number":"133","numberFrame":"","serverId":"76","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"stels navigator 610","number":"139","numberFrame":"XL13KO31915","serverId":"77","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"stels navigator 610","number":"140","numberFrame":"XL13KO31414","serverId":"78","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"stels navigator 610","number":"142","numberFrame":"XL13KO036921","serverId":"79","state":4,"count_rent":0},{"tarif":{"sumDay":150,"sumHour":50,"sumTsHour":350},"idGroup":1,"idParent":20,"model":"stels navigator 610","number":"143","numberFrame":"XL13KO37168","serverId":"80","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"stels navigator 610","number":"144","numberFrame":"XL13KO36712","serverId":"81","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":20,"model":"next","number":"156","numberFrame":"A36970","serverId":"82","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":20,"model":"ТорнадоФ","number":"106","numberFrame":"","serverId":"84","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":20,"model":"Пегас ","number":"107","numberFrame":" JS0710047","serverId":"85","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":20,"model":"детск. красный атлан","number":"128","numberFrame":"","serverId":"86","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"MBK","number":"164","numberFrame":"wkm626914a","serverId":"87","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":20,"model":"Scott YZ3","number":"102","numberFrame":"TY4131608","serverId":"88","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":20,"model":"stels navigator 610","number":"100","numberFrame":"XL13L057482","serverId":"89","state":4,"count_rent":0},{"tarif":{"sumDay":150,"sumHour":50,"sumTsHour":350},"idGroup":1,"idParent":20,"model":"Sibvelz","number":"20","numberFrame":"pl1105035336","serverId":"90","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":20,"model":"stels navigator 500","number":"43","numberFrame":"xl 11 0032956","serverId":"91","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"stels navigator 500","number":"48","numberFrame":"xl 10l053873","serverId":"92","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":20,"model":"viper evolution 100","number":"74","numberFrame":"at60301748","serverId":"93","state":4,"count_rent":0},{"tarif":{"sumDay":150,"sumHour":50,"sumTsHour":350},"idGroup":1,"idParent":20,"model":"nordway uptown","number":"62","numberFrame":"k60739886","serverId":"94","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"stels navigator 610","number":"95","numberFrame":"XL13L056761","serverId":"96","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":20,"model":"kross hexagon x4","number":"69","numberFrame":"y2ao2909","serverId":"97","state":4,"count_rent":0},{"tarif":{"sumDay":700,"sumHour":250,"sumTsHour":1000},"idGroup":1,"idParent":20,"model":"merida matts TFS100","number":"70","numberFrame":"wc339706h","serverId":"98","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"Scott boulder","number":"77","numberFrame":"gc493383","serverId":"100","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":20,"model":"Giant Boulder 3 Woma","number":"80","numberFrame":"po8j0886","serverId":"101","state":4,"count_rent":0},{"tarif":{"sumDay":150,"sumHour":50,"sumTsHour":350},"idGroup":1,"idParent":20,"model":"stels navigator 850","number":"40","numberFrame":"xl12s086867","serverId":"102","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"stels navigator 610","number":"141","numberFrame":"XL13KO36964","serverId":"113","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"Novatrach Taxi детск","number":"188","numberFrame":"IK34155515","serverId":"114","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"del sol ","number":"158","numberFrame":" XDS1420802","serverId":"115","state":4,"count_rent":0},{"tarif":{"sumDay":700,"sumHour":250,"sumTsHour":1000},"idGroup":1,"idParent":20,"model":"Nordway aktiv 80 син","number":"181","serverId":"116","state":4,"count_rent":0},{"tarif":{"sumDay":600,"sumHour":150,"sumTsHour":800},"idGroup":1,"idParent":20,"model":"stels navigator 500","number":"44","numberFrame":"xl 11c032536","serverId":"117","state":4,"count_rent":0},{"tarif":{"sumDay":800,"sumHour":300,"sumTsHour":1200},"idGroup":1,"idParent":20,"model":"Stern Maya","number":"88","numberFrame":"V795859","serverId":"118","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":20,"model":"Велосипед детский цы","number":"155","numberFrame":"","serverId":"398","state":4,"count_rent":0},{"tarif":{"sumDay":300,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"Scott YZ3","number":"78","numberFrame":"ty 4134089","serverId":"399","state":4,"count_rent":0},{"idGroup":1,"idParent":20,"model":"KHS Alite 150","number":"28","numberFrame":"v12d02658","serverId":"67","state":4,"count_rent":0},{"idGroup":1,"idParent":20,"model":"element active","number":"66","numberFrame":"RA811031A","serverId":"95","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":21,"model":"палатка","serverId":"400","state":4,"count_rent":0},{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":600},"idGroup":1,"idParent":21,"model":"палатка","serverId":"401","state":4,"count_rent":0},{"tarif":{"sumDay":500,"sumHour":100,"sumTsHour":700},"idGroup":1,"idParent":21,"model":"палатка","serverId":"402","state":4,"count_rent":0},{"tarif":{"name":"Gopro 3+","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":21,"model":"палатка","serverId":"403","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":21,"model":"палатка","serverId":"404","state":4,"count_rent":0},{"tarif":{"sumDay":350,"sumHour":50,"sumTsHour":500},"idGroup":1,"idParent":21,"model":"спальный мешок","serverId":"405","state":4,"count_rent":0}]';
      
      
      //setLogToFile("Admin ID: "+$idAdmin+" JSON: "+$json);
      setLogToFile(" JSON: "+$json);
      
      $managerDB = new ManagerDB();
      $managerDB->commitInventories($idAdmin, 1, $json);
    
    }
);

$app->get('/clients',
    function () {
        
      $managerDB = new ManagerDB();
      $managerDB->reqClients();        
        
    }
);

$app->get('/clients/:phoneNumber',
    function ($phoneNumber) {
        
      $managerDB = new ManagerDB();
      $managerDB->reqGetClients($phoneNumber);        
        
    }
);

$app->get('/inventory',
    function () {
        
      $managerDB = new ManagerDB();
      $managerDB->reqGetInventory(null);        
        
    }
);

$app->get('/inventory/:idPoint',
    function ($idPoint) {
        
      $managerDB = new ManagerDB();
      $managerDB->reqGetInventory($idPoint);        
        
    }
);

$app->get('/points',
    function () {
        
      $managerDB = new ManagerDB();
      $managerDB->reqGetAllPoints();        
        
    }
);

$app->get('/breakdown',
    function () {
        
      $managerDB = new ManagerDB();
      $managerDB->reqGetAllBreakdown();        
        
    }
);
// UTIL================================ //
$app->get('/util/rents',
    function () {
        
        //$text = "dsdsds,";
        
        //$trimmed = trim($text, ',');
        
        //echo $trimmed;
        
        set_time_limit(120);
        $pasrserCSV = new ParserCSV();
        $rentsArr = $pasrserCSV->rents();
        
        //print_r($rentsArr);
        
        $managerDB = new ManagerDB();
        //$managerDB->parseRents(null);
        
        foreach( $rentsArr  as $rent ) {
            //$managerDB->parseRents($rent);
        } 
                                 
        
    }
);

$app->get('/util/tarifs',
    function () {
        
        $pasrserCSV = new ParserCSV();
        $tarifsArr = $pasrserCSV->tarifs();
        
        $managerDB = new ManagerDB();
        
        
        foreach( $tarifsArr as $tarifs ) {
            $managerDB->parseTarifs($tarifs);
        }
                                 
        //var_dump($arr);
        
    }
);

$app->get('/util/setgroup',
    function () {
        
        
        $managerDB = new ManagerDB();
        //$managerDB->setGroup();
        
        
    }
);

$app->get('/util/clients',
    function () {
        $pasrserCSV = new ParserCSV();
        $clientsArr = $pasrserCSV->clients();
        $managerDB = new ManagerDB();

        echo "<br><br> Count BL: ".count($clientsArr)."<br><br>";

        foreach( $clientsArr as $client ) {
                
            if(!empty($client[0])) {
                //$managerDB->parseClients($client);                   
            }                
        }
    }
);

$app->get('/util/clients/vip',
    function () {
        $pasrserCSV = new ParserCSV();
        $clientsArr = $pasrserCSV->clientsVip();
        $managerDB = new ManagerDB();

        echo "<br><br> Count BL: ".count($clientsArr)."<br><br>";

        foreach( $clientsArr as $client ) {
                
            if(!empty($client[0])) {
                //$managerDB->parseClientsVip($client);                   
            }                
        }
    }
);

$app->get('/util/inventory',
    function () {
        $pasrserCSV = new ParserCSV();
        $inventoryArr = $pasrserCSV->inventory();
        $managerDB = new ManagerDB();

        foreach( $inventoryArr as $inventory ) {
            if ( !empty($inventory[0]) ) {
                $managerDB->parseInventory($inventory);                   
            }                
        }
    }
);

$app->get('/util/inventory/main',
    function () {
        $pasrserCSV = new ParserCSV();
        $inventoryArr = $pasrserCSV->inventoryMain();
        
        //var_dump($inventoryArr);
        
        
        $managerDB = new ManagerDB();
        foreach( $inventoryArr as $inventory ) {
            if ( !empty($inventory[0]) ) {
                $managerDB->parseInventoryMain($inventory);                   
            }                
        } 
    }
);

$app->get('/util/shift',
    function () {
            
        //$pasrserCSV = new ParserCSV();
        //$inventoryArr = $pasrserCSV->inventory();
        //$managerDB = new ManagerDB();

        
        $testString = "12.322,11T";
        echo preg_replace("/[^0-9,.]/", "", $testString);
        
    }
);

$app->get('/util/admin',
    function () {
        $pasrserCSV = new ParserCSV();
        $adminsArr = $pasrserCSV->admins(); 
         $managerDB = new ManagerDB();
        
        
        foreach( $adminsArr as $admin ) {
            if ( !empty($admin[0]) ) {
                $managerDB->parseAdmins($admin);                   
            }                
        }                    
    }
);

$app->get('/util/shifts',
    function () {
        $pasrserCSV = new ParserCSV();
        $shiftsArr = $pasrserCSV->shifts(); 
        $managerDB = new ManagerDB();
        
        //print_r($shiftsArr);
        //echo preg_replace("/[^0-9,.]/", "", $testString);
        foreach( $shiftsArr as $shift ) {
                
            //echo preg_replace("/[^0-9,.]/", "", $shift[0])."<br>";
            
            
            try {
                
                //$date = strtotime( preg_replace("/[^0-9,.]/", "", $shift[0]));
                $date = preg_replace("/[^0-9,.]/", "", $shift[0]);                    
                    
                if (strpos($date,'2014') !== false || strpos($date,'2015') !== false) {
                
                } else {
                    $date = $date."2015";
                }  
                //echo preg_replace("/[^0-9,.]/", "", $shift[0])."<br>";
                //echo $date."<br>";
                
                //$shiftDate = strtotime( $shift[37]);//." ".$shift[37]."<br>";
                $managerDB->parseShift($date);
                  
            } catch (\exception $e) {
                echo "Exception";
            }
               
        }                    
    }
);

$app->get('/util/inventoryNotMark',
    function () {
        $pasrserCSV = new ParserCSV();
        $inventoryArr = $pasrserCSV->inventory();
        $managerDB = new ManagerDB();


        /*foreach( $inventoryArr as $inventory ) {
            if ( !empty($inventory[0]) ) {
                $managerDB->parseInventory($inventory);                   
            }                
        }*/
    }
);

$app->get('/util/inventory/recoun',     
    function() use ($app) {
        
      $managerDB = new ManagerDB();
      $managerDB->utilRecountInventory();      
                
    }
);

$app->get('/util/clients/recoun',     
    function() use ($app) {
        
      $managerDB = new ManagerDB();
      $managerDB->utilSetCountRents();
                
    }
);

$app->get('/data/collect',
    function () {

       $managerDB = new ManagerDB();
       //$managerDB->testParse();        
       $managerDB->collectData();
    }
);

$app->post('/data/load',
    function () use($app) {
            
       $jsonData = $app->request->post('data');           
       setLogToFile($jsonData);
       $managerDB = new ManagerDB();
       $managerDB->reqDataLoad(88, $jsonData);  

    }
);

$app->get('/inv/recode',
    function () {

       $managerDB = new ManagerDB();
       //$managerDB->testParse();        
       $managerDB->recodingInventory();
    }
);

$app->post('/message',
    function() use ($app) {
          
      $data = $app->request->post('data');        
      
      $managerDB = new ManagerDB();
      $managerDB->reqPostMessage($data);                
});

$app->get('/messages',     
    function() use ($app) {
        
      $managerDB = new ManagerDB();
      $managerDB->reqGetMessage();                
    }
);

$app->get('/rents/breakdowns',     
    function() use ($app) {
        
      $managerDB = new ManagerDB();
      $managerDB->reqBreakdownsInRents();
    }
);

$app->post('/rent/update',     
    function() use ($app) {
      //$managerDB = new ManagerDB();
      //$managerDB->reqRentUpdate();  
    }
);

$app->put('/inventory',     
    function() use ($app) {
        
    $jsonInventory = $app->request->put('inventory');             
    setLogToFile("Inventory PUT: ".$jsonInventory);
                        
    $managerDB = new ManagerDB();
    $managerDB->reqPutInventory($jsonInventory);      
    }
);

$app->get('/dbg/put/inventory',     
    function() use ($app) {
        
      $jsonInventory = '{"tarif":{"name":"","sumDay":300,"sumHour":100,"sumTsHour":500},"idGroup":1,"idParent":20,"model":"KHS Alite 150","number":"29","numberFrame":"v12d05353","serverId":"68","state":2,"countRents":4}';             
      
                          
      $managerDB = new ManagerDB();
      $managerDB->reqPutInventory($jsonInventory);        
    }
);

$app->get('/dbg/rewrite/client',     
    function() use ($app) {
        
      $managerDB = new ManagerDB();
      $managerDB->dbgCreateClient();      
                
    }
);

$app->get('/sqlite',     
    function() use ($app) {
        $db = new SQLite3('files/backupname2.db');             
        $results = $db->query('SELECT * FROM Client');
        //$results = $db->query('DELETE FROM Client');
        
        $i = 0;
        while ($row = $results->fetchArray()) {
            //var_dump($row);
            $i++;
        }      
                 
        echo "Count: ".$i;        
                
    }
);

$app->get('/sqlite/clear',     
    function() use ($app) {
        
      $managerDB = new ManagerDB();
      $managerDB->clearSqlite ();
    }
);

$app->run(); 

function setLogToFile($string) {
            $file = 'log.txt';
            $current = file_get_contents($file);
            $current .= $string."\n";
            file_put_contents($file, $current);                    
    }

?>