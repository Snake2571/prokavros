<?session_start();    
    ini_set("display_errors", "1");
    error_reporting(E_ALL);
    
    require 'utils/ManagerDB.php';
    
    $managerDB = new ManagerDB();
    
    $actualLink = "http://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
    
    $moduleForDate = " <form action='".$actualLink."' method='post'>
    
                            <input type='number' placeholder='day' name='day' />    
                            <input type='number' placeholder='month' name='month'/>
                            <input type='number' placeholder='year' name='year'/> 
    
                            <input type='submit' value='Sends'/>
                        </form> ";
    
    $moduleForNumber = " <form action='".$actualLink."' method='post'>
    
                            <input type='number' placeholder='phone' name='number' />    
                            
                            <input type='submit' value='Sends'/>
                        </form> ";
    
    $moduleForInventory = " <form action='".$actualLink."' method='post'>
    
                            <input type='number' placeholder='inventory number' name='inventoryNumber' />    
                            
                            <input type='submit' value='Sends'/>
                        </form> ";       
                        
    $itemMenuAll = mb_convert_encoding('Все', 'WINDOWS-1251', 'UTF-8');
    $itemMenuInventory = mb_convert_encoding('По инвентарю', 'WINDOWS-1251', 'UTF-8');
    $itemMenuClient = mb_convert_encoding('По клиенту', 'WINDOWS-1251', 'UTF-8');
    $itemMenuDate = mb_convert_encoding('По дате', 'WINDOWS-1251', 'UTF-8');
                        
    /*function download_send_headers($filename) {
        // disable caching
        $now = gmdate("D, d M Y H:i:s");
        header("Expires: Tue, 03 Jul 2001 06:00:00 GMT");
        header("Cache-Control: max-age=0, no-cache, must-revalidate, proxy-revalidate");
        header("Last-Modified: {$now} GMT");
    
        // force download  
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
    
        // disposition / encoding on response body
        header("Content-Disposition: attachment;filename={$filename}");
        header("Content-Transfer-Encoding: binary");
    }*/
    
    
    function array2csv($list) {
        
        if (count($list) == 0) {
         return null;
       }
        
        
        $fp = fopen('temp/file.csv', 'w');
        
        foreach ($list as $line) {
            $lineString = $line->clientName.";"
                                .$line->clientPhone.";"
                                .$line->inventoryNumber.";"
                                .$line->inventoryModel;
            
            //echo $lineString."<br>";
            //fputcsv($fp, explode(';', $lineString ));
            fputcsv($fp, get_object_vars($line), ';');
        }
        
        fclose($fp);
        
        $csvUrl = "http://$_SERVER[HTTP_HOST]/api/temp/file.csv";
        header("Location: $csvUrl");
        die();
    }
    
    if ( isset($_GET['exit']) )  
        unset($_SESSION['authorization']);
    
    $authorization = isset($_SESSION['authorization']) && $_SESSION['authorization']==true;
    
    if( isset($_POST['login']) && isset($_POST['password']) ) {
        //$result = $managerDB->authAdmin($_POST['login'], $_POST['password']);
        $authorization = $managerDB->checkAuthData($_POST['login'], $_POST['password']);
        $_SESSION['authorization'] = $authorization; 
        //echo $authorization;
    }
                        
?>

<html>
    <head>
        <link rel="stylesheet" href="styles/style.css">
        <title>Administrator panel</title>
    </head>

    <body>
    
    <? if (!$authorization) { ?>
        <center>
            <form action=<? echo $actualLink ?> method='post'>
        
                <input type='text' placeholder='login' name='login' />    
                <input type='password' placeholder='password' name='password'/>
                
                <input type='submit' value='Sends'/>
            </form>
        </center> 
    <?} else {?>
    <a href='?exit=1'> Exit </a>
        <center>
            <div class='main'>
                <div class='menu'>
                    <ul>
                        <li><a href='?all=1'> <? echo $itemMenuAll; ?> </a></li>
                        <li><a href='?byinventory=1'> <? echo $itemMenuInventory; ?> </a></li>
                        <li><a href='?byclient=1'> <? echo $itemMenuClient; ?> </a></li>
                        <li><a href='?bydate=1'> <? echo $itemMenuDate; ?> </a></li>
                    </ul>
                </div>
                
                <div class='control_panel'>
                    <?
                        if ( isset($_GET['bydate']) )
                            echo $moduleForDate;
                        else if ( isset($_GET['byclient']) )            
                            echo $moduleForNumber; 
                        else if (isset($_GET['byinventory']) )
                            echo $moduleForInventory; 
                    ?>
                
                </div>
            
                <div class='content'>
                    
                    <?
                      
                        $rents = null;
                        $urlDownloadCSV = null;
                        
                        if ( isset($_POST['day']) && isset($_POST['month']) && isset($_POST['year']) ) {
                            $rents = $managerDB->reqGetRentsByDate($_POST['year']."-".$_POST['month']."-".$_POST['day']);                            
                            $urlDownloadCSV = "?day=".$_POST['day']."&month=".$_POST['month']."&year=".$_POST['year'];
                        
                        } else if ( isset($_POST['number']) ) {
                            $rents = $managerDB->reqGetRentsByClient($_POST['number']);
                            $urlDownloadCSV = "?number=".$_POST['number'];
                            
                        } else if ( isset($_POST['inventoryNumber']) ) {
                            $rents = $managerDB->reqGetRentsByInventory($_POST['inventoryNumber']);
                            $urlDownloadCSV = "?inventoryNumber=".$_POST['inventoryNumber'];
                        } else if (isset($_GET['all'])) {
                            $rents = $managerDB->reqGetAllRentsArr(); 
                            $urlDownloadCSV = "?parseall";
                        } else if (isset($_GET['number'])) {
                            $rents = $managerDB->reqGetRentsByClient($_GET['number']);
                            //download_send_headers("data_export_" . date("Y-m-d") . ".csv");
                            array2csv($rents);
                        } else if (isset($_GET['inventoryNumber'])) {
                            $rents = $managerDB->reqGetRentsByInventory($_GET['inventoryNumber']);
                            array2csv($rents);
                        } else if ( isset($_GET['day']) && isset($_GET['month']) && isset($_GET['year']) ) {
                            $rents = $managerDB->reqGetRentsByDate($_GET['year']."-".$_GET['month']."-".$_GET['day']);
                            array2csv($rents);                              
                        } else if(isset($_GET['parseall'])){
                            $rents = $managerDB->reqGetAllRentsArr();
                            array2csv($rents); 
                        }
                        
                        if ( !is_null($rents) ) {
                            echo "<a href='".$urlDownloadCSV."'>Save to CSV</a>";
                            echo "<table border='1'>";

                            foreach($rents as $rent) {
                                echo "<tr>";
                                echo "<td>".$rent->shiftDate."</td>";
                                echo "<td>".$rent->inventoryNumber."</td>";
                                echo "<td>".$rent->inventoryModel."</td>";
                                echo "<td>".$rent->clientPhone."</td>";
                                echo "<td>".$rent->clientName."</td>";
                                echo "<td>".$rent->expense."</td>";
                                echo "</tr>";
                            }

                            echo "</table>";
                        }
                    ?>
                    
                </div>
            </div>  
        </center>
        <?}?>
    </body>

</html>