<?php
//simple Dom Parser
include "simple_html_dom.php";
include "db.php";
$total_count    = 0;
$total_errors   = 0;
$category       = htmlspecialchars($_POST['franchise_cat']);
$main_URL       = htmlspecialchars($_POST['webite_url']);
$q = "SELECT category_name FROM category WHERE id='$category' limit 1";
$res =$conn ->query($q);
$value = $res->fetch_assoc();
$cat_name =$value['category_name'];
$result_message = [];
$result_errors  = [];

// Defining the basic cURL function
function curl($url)
{
    // Assigning cURL options to an array
    $options = [CURLOPT_RETURNTRANSFER => true,
                // Setting cURL's option to return the webpage data
                CURLOPT_FOLLOWLOCATION => true,
                // Setting cURL to follow 'location' HTTP headers
                CURLOPT_AUTOREFERER    => true,
                // Automatically set the referer where following 'location' HTTP headers
                CURLOPT_CONNECTTIMEOUT => 260,
                // Setting the amount of time (in seconds) before the request times out
                CURLOPT_TIMEOUT        => 120,
                // Setting the maximum amount of time for cURL to execute queries
                CURLOPT_MAXREDIRS      => 12,
                // Setting the maximum number of redirections to follow
                CURLOPT_USERAGENT      => "Mozilla/5.0 (X11; U; Linux i686; en-US; rv:1.9.1a2pre) Gecko/2008073000 Shredder/3.0a2pre ThunderBrowse/3.2.1.8",
                // Setting the useragent
                CURLOPT_URL            => $url,
                // Setting cURL's URL option with the $url variable passed into the function
    ];
    
    $ch = curl_init();  // Initialising cURL
    curl_setopt_array($ch, $options);   // Setting cURL's options using the previously assigned array data in $options
    $data = curl_exec($ch); // Executing the cURL request and assigning the returned data to the $data variable
    curl_close($ch);    // Closing cURL
    
    return $data;   // Returning the data from the function
}

?>
<!doctype html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, user-scalable=no, initial-scale=1.0, maximum-scale=1.0, minimum-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="ie=edge">
    <title>Franchise App</title>
    <link rel="stylesheet" href="./node_modules/bootstrap/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="style.css">
    <link rel="icon" type="image/png" href="favicon.png">
</head>
<body>

<?php
$categories = $conn->query('select id, category_name from category');
if(!$categories){
    echo "Error: " . $sql . "<br>" . $conn->error;
}
?>
<section id="scrapper-form" class="section-wrapper">
    <div class="container">
        <h1>Franchise Data APP</h1>
        <div class="row">
            <form action="index.php" method="post">
                <div class="row">
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="">Website URL *</label>
                            <input type="text" name="webite_url" id="website_url" class="form-control" required>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <label for="">Franchise category *</label>
                            <select name="franchise_cat" id="franchise_cat" class="form-control">
                                <?php
                                if($categories->num_rows > 0){
                                    // output data of each row
                                    while($row = $categories->fetch_assoc()){
                                        echo "<option value='" . $row["id"] . "'>" . $row["category_name"] . "</option>";
                                    }
                                }
                                ?>
                            </select>
                        </div>
                    </div>
                    <div class="col-sm-4">
                        <div class="form-group">
                            <button type="submit" class="btn btn-primary btn-block">Get Data</button>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>
</section>

<?php
if($_SERVER["REQUEST_METHOD"] == "POST"){
    
    //franchise list
    $response = curl($main_URL);//main site url - this is the website the  function will scrap from
    error_reporting(0);
    //this initialize the simple dom
    
    //main site
    $html = new simple_html_dom();
    //business profiles
    $html2 = new simple_html_dom();
    
    $html->load($response);//parsing the main site
    $number = 0;
    
    //looping through the parsed site, and getting each element
    foreach($html->find('div[id^=franchise-tile]') as $link){
        $item             = $link->find('a[itemprop^=url]', 0)->href;//getting all the href inner text from <a itemprop="name"> tags
        $franchise_url [] = $item;
    }
    
    //print_r($franchise_url);
    
    //Getting the profiles
    foreach($franchise_url as $url){
        $res = curl('https://www.franchisegator.com' . $url);
        $html2->load($res);
        foreach($html2->find('div[class^=container]') as $element){
            $itm['img']                = $element->find('img[itemprop^=logo]', 0)->src;
            $itm['title']              = $element->find('div.concept-name h1', 0)->innertext;
            $itm['category']           = $category;//category ID
            $itm['email']              = str_replace(' ', '', $itm['title']) . '@email.com';
            $itm['first-name']         = $itm['title'];
            $itm['last-name']          = 'Company';
            $itm['password']           = 'expobusiness';
            $itm['country']            = 209; //united states
            $itm['phone']              = '18662572973';
            $itm['property-type']      = 'franchise';
            $itm['building']           = $itm['country'];
            $itm['street']             = $itm['country'];
            $itm['landmark']           = $itm['country'];
            $itm['area']               = $itm['country'];
            $itm['zipcode']            = "00000";
            $itm['preferred-location'] = $itm['headquarters'];
            $itm['commission']         = str_replace(['$', ','], ['',
                                                                  ''], $element->find('div[id^=concept-cost] table tr td', 1)->innertext);
            $tr                        = $element->find('div[id^=concept-cost] table tr td', 3)->innertext;
            
            if($tr != ''){
                
                $_tr               = explode('-', str_replace(['Estimated:',
                                                               'plus working capital',
                                                               '$',
                                                               ','], '', $tr));
                $itm['min-amount'] = $_tr[0];
                if($itm['max-amount'] != ''){
                    $itm['max-amount'] = $_tr[1];
                }else {
                    $itm['max-amount'] = $itm['min-amount'];
                }
            }
            
            if($itm['franchise-fee'] != ''){
                $itm['franchise-fee'] = str_replace(['$', ','], ['',
                                                                 ''], $element->find('div[id^=concept-cost] table tr td', 1)->innertext);
            }else {
                $itm['franchise-fee'] = 20000;
            }
            $itm['founded'] = '1111';
            if($itm['headquarters'] == ''){
                $itm['headquarters'] = 'United States';
            }else {
                $itm['headquarters'] = $element->find('td[itemprop^=foundingLocation]', 0)->innertext;
            }
            
            $itm['description'] = $element->find('div[itemprop^=description]', 0)->innertext;
            
            if($itm['img'] != '' && $itm['title'] && $itm['description'] != ''){
                $franchises[] = $itm;
            }
        }
    }
    //inner info
    //print_r($franchises[0]);
    //print_r($arreglo);
    ?>
    <?php
    foreach($franchises as $franchise){
        $detail = $conn->real_escape_string($franchise['description']);
        $set    = "fname ='" . $franchise['first-name'] . "'";
        $set    .= ",lname ='" . $franchise['last-name'] . "'";
        $set    .= ",password ='" . $franchise['password'] . "'";
        $set    .= ",email ='" . $franchise['email'] . "'";
        $set    .= ",contact_no1 ='" . $franchise['phone'] . "'";
        $set    .= ",building ='" . $franchise['building'] . "'";
        $set    .= ",street ='" . $franchise['street'] . "'";
        $set    .= ",landmark ='" . $franchise['landmark'] . "'";
        $set    .= ",area ='" . $franchise['area'] . "'";
        $set    .= ",country ='" . $franchise['country'] . "'";
        $set    .= ",zip_code ='" . $franchise['zipcode'] . "'";
        $set    .= ",title ='" . $franchise['title'] . "'";
        $set    .= ",category ='" . $franchise['category'] . "'";
        $set    .= ",establishment_yr =1111";
        $set    .= ",launched_yr ='" . $franchise['founded'] . "'";
        $set    .= ",headquater ='" . $franchise['headquarters'] . "'";
        $set    .= ",area_req = 0";
        $set    .= ",investment ='" . $franchise['min-amount'] . "-" . $franchise['max-amount'] . "'";
        $set    .= ",brand_fee =" . $franchise['franchise-fee'] . "";
        $set    .= ",commission ='" . $franchise['commission'] . "'";
        $set    .= ",expan_location ='" . $franchise['preferred-location'] . "'";
        $set    .= ",training_loc ='" . $franchise['preferred-location'] . "'";
        $set    .= ",property_type ='" . $franchise['property-type'] . "'";
        $set    .= ",preferred_loc ='" . $franchise['preferred-location'] . "'";
        $set    .= ",img ='" . $franchise['img'] . "'";
        $set    .= ",user_role = 1";
        $set    .= ",lawyer_status = 2";
        $set    .= ",active_status = 1";
        $set    .= ",email_active_status = 1";
        $set    .= ",overall_rate = 0";
        $set    .= ",field_assit = 0";
        $set    .= ",floor_area = 'n/a'";
        $set    .= ",crcdt ='" . date('Y-m-d h:i:s') . "'";
        $set    .= ",chngdt = '" . date('Y-m-d h:i:s') . "'";
        $set    .= ",last_login_date ='" . date('Y-m-d h:i:s') . "'";
        $set    .= ",last_logout_date = '0'";
        $set    .= ",reg_ip ='190.24.45.227'";
        $set    .= ",edit_ip ='190.24.45.227'";
        //   $set.= ',detail ="'.$franchise['description'].'"';
        $set .= ",detail = '" . $detail . "'";
        
        $sql = "insert into register set $set";
        if($conn->multi_query($sql) === true){
            $total_count++;
            $category         = $franchise['category'];
            $result_message[] = "<li>New Record Created Successfully - " . $franchise['title'] . "</li>";
        }else {
            $result_errors[] = "<li style='color: #FF738B'>Error Record Not Created - " . $franchise['title'] . "</li>";
            $total_errors++;
        }
    }
    $conn->close();
    
}
?>

<section id="scrapper-result" class="section-wrapper">
    <div class="container">
        <h1>Results</h1>
        <div class="wrap">
            <div class="row border-w">
                <div class="col-sm-4">
                    <h3 style="color: #5ED1B1">Total Added: <?= $total_count ?></h3>
                </div>
                <div class="col-sm-4">
                    <h3 style="color: #FF738B">Total Errors: <?= $total_errors ?></h3>
                </div>
                <div class="col-sm-4">
                    <h3 style="color: #A1CDF1">Category: <?= $cat_name ?></h3>
                </div>
            </div>

            <div class="row">
                <div class="col-sm-12">
                    <h4>Log Messages:</h4>
                    <div class="message-wrapper border-w">
                        <ul class="result-list">
                            <?php foreach($result_message as $result){
                                echo "$result";
                            } ?>
                            <?php foreach($result_errors as $error){
                                echo "$error";
                            } ?>
                        </ul>
                    </div>
                </div>
            </div>
        </div>
    </div>
</section>

