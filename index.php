<?php
//simple Dom Parser
include "simple_html_dom.php";

// Defining the basic cURL function
function curl($url) {
    // Assigning cURL options to an array
    $options = Array(
        CURLOPT_RETURNTRANSFER => TRUE,  // Setting cURL's option to return the webpage data
        CURLOPT_FOLLOWLOCATION => TRUE,  // Setting cURL to follow 'location' HTTP headers
        CURLOPT_AUTOREFERER => TRUE, // Automatically set the referer where following 'location' HTTP headers
        CURLOPT_CONNECTTIMEOUT => 120,   // Setting the amount of time (in seconds) before the request times out
        CURLOPT_TIMEOUT => 120,  // Setting the maximum amount of time for cURL to execute queries
        CURLOPT_MAXREDIRS => 10, // Setting the maximum number of redirections to follow
        CURLOPT_USERAGENT => "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8",  // Setting the useragent
        CURLOPT_URL => $url, // Setting cURL's URL option with the $url variable passed into the function
    );
    
    $ch = curl_init();  // Initialising cURL
    curl_setopt_array($ch, $options);   // Setting cURL's options using the previously assigned array data in $options
    $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
    curl_close($ch);    // Closing cURL
    return $data;   // Returning the data from the function
}

//franchise list
$response = curl('https://www.franchisegator.com/search.php?industry=13&state=International&investment=');

$html = new simple_html_dom();
$html2 = new simple_html_dom();
$html->load($response);

foreach($html->find('div[id^=franchise-tile]') as $link){
//    $item['image'] = $link->find('img[itemprop^=logo]', 0)->src;
    $item = $link->find('a[itemprop^=url]',0)->href;
//    $item['min_cash'] = str_replace('<i class="fa fa-money fa-2x"></i>','',$link->find('div.concept-cost',0)->innertext);
//    $item['description'] = $link->find('p[itemprop^=description]', 0)->innertext;
//    $business[] = $item;
    echo $item;
     $inner = curl('https://www.franchisegator.com/'.strtolower($item));
     $html2->load($inner);
     foreach($html2 as $element){
         $itm['image'] = $element->find('img[itemprop^=logo]', 0)->src;
         $itm['title'] = $element->
     };
}



//inner info

?>

