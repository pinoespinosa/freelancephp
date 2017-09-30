<!DOCTYPE html>
<html>
<body>
<form action="webscrapper.php" method="post" enctype="multipart/form-data" >   
    <input type="submit" value="Start scan" name="submit">
</form>
<br><br>

</body>
</html>



<?php


define("SERVER_gsmarena", "http://www.gsmarena.com");
define("SERVER_phonearena", "https://www.phonearena.com");
define("SERVER_devicespecifications", "https://www.devicespecifications.com");
define("SERVER_whistleout", "https://www.whistleout.com");



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
}

function invokePhonearenaData($url, $myfile)
{
    $info = file_get_contents( $url );
    $title =  splitInList($info,'<h1>','</h1>')[1];
    $title =  splitInList($title,'<span>','</span>')[1];

    $description =  splitInValue($info,'<div class="desc" >','</div>');
    $brand =  splitInValue($info,'"brand": {','},');
    $brand =  splitInValue($brand,'"name": "','"');

    $price =  splitInValue($info,'MSRP price:','/li>');
    $price =  '$' . splitInValue($price,'$','<');
    
    $size =  splitInValue($info,'Dimensions:','</ul>');
    $size =  splitInValue($size,'</span></span>','</li>');

    $amazon =  splitInValue($info,'<a class="price"','Buy</span>');
    $amazon =  '$' .splitInValue($amazon,'$','<span>');

    $camera =  splitInValue($info,'Camera:','</ul>');
    $camera =  splitInValue($camera,'</span></span>','</li>');

    $battery =  splitInValue($info,'Capacity:','</ul>');
    $battery =  splitInValue($battery,'<li>','</li>');

    $speed =  splitInValue($info,'System chip:','</ul>');
    $speed =  splitInValue($speed,'<li>','</li>');

    printXML($myfile, $url, $title, $description, $brand, '',  $price, $size, $camera, $battery, $speed, '', '', '', '', '', '', '', '', '', $amazon, '', '');


    flush();
    ob_flush();
}


function invokePhonearena($subpath, $myfile)
{
    $categories = file_get_contents( SERVER_phonearena . '/phones/manufacturers');
    $categories =  splitInList($categories,'<div class="s_hover">','" class="s_thumb"');

    for($i=1; $i<count($categories); $i++){

        $nextPage = explode('a href="', $categories[$i] . $subpath)[1];
        print $nextPage . '<br>';
        flush();
        ob_flush();
        while ($nextPage){

            $listCellsPage = file_get_contents( SERVER_phonearena  . $nextPage );
            $listCells =  splitInList($listCellsPage,'id="phones"','class="s_static"')[1];
            $listCells =  splitInList($listCells,'<a class="s_thumb" href="','"');

            for($j=1; $j<count($listCells); $j++){
                print $listCells[$j] . '<br>';
                flush();
                ob_flush();
                invokePhonearenaData( SERVER_phonearena . $listCells[$j], $myfile );
            }
        
            if(isset(splitInList($listCellsPage,'class="s_next"','</li>')[1])){
               $nextPage = splitInList($listCellsPage,'class="s_next"','</li>')[1];
               $nextPage = splitInList($nextPage,'href="','"')[1];

            }
            else {
                $nextPage = '';
            }
        }
    }

    print '<br><br>';
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

function invokehistleoutData($url, $myfile)
{

    $url =  splitInList($url,'<a href="','"')[1];
    $contenido = file_get_contents(SERVER_whistleout . $url );

    $title =  splitInValue(splitInValue($contenido,'<h1 class="font-800 font-8 font-7-xs font-9-lg mar-0">','</h1>'),'<span>','</span>');
    $description =  splitInValue($contenido,'<p class="mar-0 font-4 font-6-lg font-6-md c-gray-light">','</p>');
    $brand =  splitInValue($contenido,'<li><a href="/CellPhones/Phones/','">');
    $size =  splitInValue($contenido,'fa-diagonal font-5 hidden-xs"></span>','<');
    $camera =  splitInValue($contenido,'fa-picture-o font-5 hidden-xs"></span>','<');
    $battery =  splitInValue($contenido,'fa-battery-half font-5 hidden-xs"></span>','<');

    printXML($myfile, SERVER_whistleout . $url, $title, $description, $brand, '', '', $size, $camera, $battery, '', '', '', '', '', '', '', '', '', '', '', '', '');

    flush();
    ob_flush();
}

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




function printXML($myfile, $id, $title, $description, $brand, $slug, $price, $size, $camera, $battery, $speed, $created_at, $created_by, $created_type, $updated_at, $updated_by, $updated_type, $published_at, $published_by, $state, $amazon, $ebay, $bestbuy)
{

    $description = str_replace("&#39;", "'", trim($description));
    $description = str_replace("&quot;", "\"", $description);

    fwrite($myfile, "        <cellphone>\n");
    fwrite($myfile, "            <id>"          . trim($id)           . "</id>\n");
    fwrite($myfile, "            <title>"       . trim($title)        . "</title>\n");
    fwrite($myfile, "            <description>" . trim($description)  . "</description>\n");
    fwrite($myfile, "            <brand>"       . trim($brand)        . "</brand>\n");
    fwrite($myfile, "            <slug>"        . trim($slug)         . "</slug>\n");
    fwrite($myfile, "            <price>"       . trim($price)        . "</price>\n");
    fwrite($myfile, "            <size>"        . trim($size)         . "</size>\n");
    fwrite($myfile, "            <camera>"      . trim($camera)       . "</camera>\n");
    fwrite($myfile, "            <battery>"     . trim($battery)      . "</battery>\n");
    fwrite($myfile, "            <speed>"       . trim($speed)        . "</speed>\n");
    fwrite($myfile, "            <amazon>"      . trim($amazon)        . "</amazon>\n");

    fwrite($myfile, "        </cellphone>\n");

    flush();
    ob_flush();
}



if(isset($_POST["submit"])) {

     $myfile = fopen("/home/pino/Android/newfile.txt", "w") or die("Unable to open file!");


//    invokePhonearenaTablets();
//    invokePhonearena('/tablets');
//    invokePhonearena('', $myfile);
//    invokeDevicespecifications($myfile);
    invokeWhistleout($myfile);

      fclose($myfile);

}

?>
