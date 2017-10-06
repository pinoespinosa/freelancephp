<!DOCTYPE html>
<html>
<body>
<form action="webscrapper.php" method="post" enctype="multipart/form-data" >   
    <input type="submit" value="Start scan gsmarena" name="submit1"><br>
    <input type="submit" value="Start scan phonearena" name="submit2"><br>
    <input type="submit" value="Start scan devicespecifications" name="submit3"><br>
    <input type="submit" value="Start scan whistleout" name="submit4">

</form>
<br><br>

</body>
</html>



<?php

include 'configFile.php';

include 'printerXML.php';
include 'phoneData.php';
include 'whistleoutData.php';



set_time_limit(0);
ini_set('max_execution_time', 30000);

date_default_timezone_set('America/Buenos_Aires');

define("SERVER_gsmarena", "http://www.gsmarena.com");
define("SERVER_phonearena", "https://www.phonearena.com");
define("SERVER_devicespecifications", "https://www.devicespecifications.com");
define("SERVER_whistleout", "https://www.whistleout.com");


function exception_error_handler($errno, $errstr, $errfile, $errline ){
 #   if (error_reporting() === 0){
 #       return;
 #   }
 #   throw new ErrorException($errstr, 0, $errno, $errfile, $errline);
}

function catchException($e){

#    if (strpos($e, 'file_get_contents') !== false)
#        print 'Error obteniendo los datos de Youtube. Revise que la clave sea valida.';
#    else
#        print '';
}



set_exception_handler('catchException');
set_error_handler("exception_error_handler");


function showCellInfo($contenido)
{
    $lista =  explode($inicio, $contenido);

    for($i=1; $i<count($lista); $i++){
        $lista[$i] =  explode($fin, $lista[$i])[0];
    }

    return $lista;

}


function splitInValue($contenido, $inicio, $fin)
{
    $lista =  explode($inicio, $contenido);

    for($i=1; $i<count($lista); $i++){
        $lista[$i] =  explode($fin, $lista[$i])[0];
    }

    if(isset($lista[1])){
        $valor = $lista[1];
    }
    else {
        $valor = '';
    }


    return $valor;

}

function splitInList($contenido, $inicio, $fin)
{
    $lista =  explode($inicio, $contenido);

    for($i=1; $i<count($lista); $i++){
        $lista[$i] =  explode($fin, $lista[$i])[0];
    }

    return $lista;

}


function invokeGsmarenaData($url)
{
    $info = file_get_contents( $url );
    $info =  splitInList($info,'data-spec="modelname">','>')[1];

    print $url . '<br>';
    print $info . '<br>' . '<br>';

    flush();
    ob_flush();
}


function invokeGsmarena()
{

	echo date(DATE_RFC2822);
    
    $categories = file_get_contents( SERVER_gsmarena . '/makers.php3');
    $categories =  splitInList($categories,'<tr><td><a href=','>');

    for($i=1; $i<count($categories); $i++){

        $nextPage = $categories[$i];

        while ($nextPage){

            $listCellsPage = file_get_contents( SERVER_gsmarena . '/' . $nextPage );
            $listCells =  splitInList($listCellsPage,'<div class="makers">','</ul>')[1];
            $listCells =  splitInList($listCells,'<li><a href="','">');

            for($j=1; $j<count($listCells); $j++){
                invokeGsmarenaData( SERVER_gsmarena . '/' . $listCells[$j] );
            }
        
            if(isset(splitInList($listCellsPage,'<a class="pages-nextPage" href="','"')[1])){
                $nextPage = splitInList($listCellsPage,'<a class="pages-nextPage" href="','"')[1];
            }
            else {
                $nextPage = '';
            }
        }
    }

    print '<br><br>';

	echo date(DATE_RFC2822);

}





// ----------------------------------- https://www.devicespecifications.com ---------------------------------------

function invokeDevicespecificationsData($url, $myfile, $brand)
{

    $url = str_replace("images/model/", "en/model/", $url);

    $info = file_get_contents( $url );
    $title =  splitInList($info,'<p>Model name of the device.</p></td><td>','</td>')[1];

    $description =  splitInValue($info,'<meta name="description" content="','" />');
    $camera =  splitInValue($info,'<b>Camera</b>:','<');
    $size =  splitInValue($info,'<b>Dimensions</b>:','<');
    $battery =  splitInValue($info,'<b>Battery</b>:','<');
    $speed =  splitInValue($info,'<b>CPU</b>:','<');


    printXML($myfile, $url, $title, $description, $brand, '', '', $size, $camera, $battery, $speed, '', '', '', '', '', '', '', '', '', '', '', '');

    flush();
    ob_flush();
}

function invokeDevicespecifications($myfile)
{
    $categories = file_get_contents( SERVER_devicespecifications);
    $categories =  splitInList($categories,'class="brand-listing-container-frontpage"','class="section-header"')[1];
    $categories =  splitInList($categories,'<a href="','</a>');

    for($i=1; $i<count($categories); $i++){

        $nextPage =  explode("\">", $categories[$i])[0];  
        print $nextPage;
        $brand = explode(">", $categories[$i])[1];
        flush();
        ob_flush();
        while ($nextPage){

            $listCellsPage = file_get_contents( $nextPage );
            $listCells =  splitInList($listCellsPage,'<div id="main">','model-listing-container-160')[1];
            $listCells =  splitInList($listCells,'data-src="','/80/main');

            for($j=1; $j<count($listCells); $j++){
                
                if (strpos($listCells[$j], 'model/') !== false) {          
                    print $listCells[$j] . '<br>';
                    flush();
                    ob_flush();
                    invokeDevicespecificationsData( $listCells[$j], $myfile, $brand );
                }
            }
        
            $nextPage = '';

        }
    }

    print '<br><br>';
}



// ----------------------------------- https://www.whistleout.com ---------------------------------------


function invokeWhistleout($myfile)
{

    fwrite($myfile, "<?xml version=\"1.0\"?>\n");
    fwrite($myfile, "    <catalog>\n");

        $totalPagina = file_get_contents( 'https://www.whistleout.com/CellPhones/Phones/Finder');
        $totalPagina = intval(splitInList($totalPagina,'data-results-count="','"')[1]);

        $cantidad = 0;
        $nextPage = SERVER_whistleout . '/Ajax/MobilePhones/PhoneSearchResults/PagedResults?&current=0';
        flush();
        ob_flush();
        while ($nextPage){

            print '<br>' . ($cantidad / $totalPagina *100 ) . '%<br><br>';

            $listCellsPage = file_get_contents( $nextPage );

            if($listCellsPage){
                $listCells =  splitInList($listCellsPage,'<h2 class="mar-0 mar-b-3">','</h2>');

                for($j=1; $j<count($listCells); $j++){
                
                    print $listCells[$j] . '<br>';
                    flush();
                    ob_flush();
                    invokehistleoutData( $listCells[$j] , $myfile);
                }
            }
            $cantidad = $cantidad + 20;

            if ($totalPagina > $cantidad){
                $nextPage = SERVER_whistleout . '/Ajax/MobilePhones/PhoneSearchResults/PagedResults?&current=' . $cantidad ;
            }
            else
            {
                $nextPage = '';
            }
        }
    
    fwrite($myfile, "    </catalog>\n");

}










if(isset($_POST["submit1"])) {

     $file1 = fopen( GSMARENA_FILE_PATH , "w") or die("Unable to open file!");

    	invokeGsmarena($file1);

      fclose($file1);

}


if(isset($_POST["submit2"])) {
    $file2 = fopen( PHONERARENA_FILE_PATH , "w") or die("Unable to open file!");
	invokePhonearena('', $file2);
    fclose($file2);
}


if(isset($_POST["submit3"])) {
    $file3 = fopen( DEVICESPECIFICATIONS_FILE_PATH , "w") or die("Unable to open file!");
    invokeDevicespecifications($file3);
    fclose($file3);
}


if(isset($_POST["submit4"])) {
    $file4 = fopen( WHISLEOUT_FILE_PATH , "w") or die("Unable to open file!");
    invokeWhistleout($file4);
    fclose($file4);
}

?>

