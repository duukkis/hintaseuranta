<?php

include("simple_html_dom.php");

// test strig
$q = "kuulokkeet";

$urls = array(
  array(
    "store" => "Tokmanni",
    "url" => "https://www.tokmanni.fi/catalogsearch/result/?q=".$q,
    "appendToUrl" => "",
    "name" => array("elem" => "a.product-item-link", "data" => "innertext"),
    "itemlink" => array("elem" => "a.product-item-link", "data" => "href"),
    "price" => array("elem" => "span.price-wrapper[data-price-type=finalPrice]", "data" => "data-price-amount"),
    "desc" => array("elem" => "span.brand-name", "data" => "innertext"),
    "picture" => array("elem" => "div.embed-responsive-content img", "data" => "src"),
  ),
  array(
    "store" => "Motonet",
    "url" => "https://www.motonet.fi/fi/haku?q=".$q,
    "appendToUrl" => "",
    "name" => array("elem" => "div.tavaratalotitle h3 a", "data" => "innertext"),
    "itemlink" => array("elem" => "div.tavaratalokuva a", "data" => "href"),
    "price" => array("elem" => "div.hinta", "data" => "innertext"),
    "desc" => array("elem" => "", "data" => ""),
    "picture" => array("elem" => "img.ProductListImage", "data" => "src"),
  ),
);


//----------------------------------------------------------------------------------------
// start helpers
//----------------------------------------------------------------------------------------


function cleanUp($str){
  return $str;
}

function cleanData($str, $type){
  switch($type){
    case "price":
      $repl_these = array("&euro;", ",");
      $with_these = array("", ".");
      $str = str_replace($repl_these, $with_these, $str);
      break;
    default:
      break;
  }
  $str = strip_tags($str);
  return trim($str);
}

/**
* helper to sort em based on title
*/
function cmp($a, $b) {
  return (float) $a["price"] > (float) $b["price"];
}


//----------------------------------------------------------------------------------------
// end helpers
//----------------------------------------------------------------------------------------



//----------------------------------------------------------------------------------------
// data fetch
//----------------------------------------------------------------------------------------
$data = array();
$items = array("name", "itemlink", "price", "desc", "picture");

foreach ($urls AS $i => $item) {
  $local = "data/".$item["store"]."_".$q.".html";
  if(!file_exists($local)){ // use local
    $c = file_get_contents($item["url"]);
    file_put_contents($local, $c);
  } else {
    $c = file_get_contents($local);
  }
  $c = cleanUp($c);
  $html = str_get_html($c);
  $pre_index = count($data);
  foreach($items AS $k => $it){
    $index = $pre_index;
    if(!empty($item[$it]["elem"])){
      foreach($html->find($item[$it]["elem"]) AS $elem){
        if(!isset($data[$index]["store"])){
          $data[$index]["store"] = $item["store"];
        }
        $data[$index][$it] = cleanData($elem->getAttribute($item[$it]["data"]), $it);
        $index++;
      }
    }
  }
}

// sort by orice
uasort($data, "cmp");

//----------------------------------------------------------------------------------------
// data print
//----------------------------------------------------------------------------------------

$result = "<table>";
foreach($data AS $k => $v){
  $result .= 
    '<tr><td><img src="'.$v["picture"].'" style="max-width: 200px; max-height: 200px;" /></td><td><a href="'.$v["itemlink"].'">'.$v["name"].'</a></td><td>'.$v["desc"].'</td><td>'.$v["price"].'e</td><td>'.$v["store"].'</td></tr>'.PHP_EOL;
}
$result .= "</table>";

file_put_contents("result.html", $result);
echo "done".PHP_EOL;
// print_r($data);