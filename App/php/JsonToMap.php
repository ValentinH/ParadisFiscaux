<?php
require_once "functions.php";

mysql_connect("127.0.0.1","root", "");
mysql_select_db("ic05");

if(isset($_GET["file"]))
  $file = $_GET["file"];
else
  print_JSON(array("error"=>"Need to provide a valid file"));

if(isset($_GET["lg"]))
  $lang = $_GET["lg"];
else
  print_JSON(array("error"=>"Need to provide a valid lang"));

$file = "../data/".$file;

$json = file_get_contents ($file, FILE_USE_INCLUDE_PATH);
$array = json_decode($json);

$new_array = array(new Entreprise("0000"));
$companies_array = array("0000");
$i = 0;
$countries = array();
$all_countries = array();
foreach($array as $el)
{
  // if the enterprise is not yet in the array
  if(!isset($companies_array[$el->Entreprise]))
  {
    $i = count($companies_array);
    $companies_array[$el->Entreprise] = count($companies_array); 
    $new_array[$i] = new Entreprise($el->Entreprise);   
  }
  else
    $i = $companies_array[$el->Entreprise];

  if(!isset($all_countries[$el->Code]))
    $all_countries[$el->Code] = 0;
  $all_countries[$el->Code] += $el->Valeur;
  
  if(isset($countries[$el->Code]))
    $country = $countries[$el->Code];
  else
  {
    $country = getCountryFromDB($el->Code);
    /*if($country == null)
    {
      $country = getCountry($el->Code, false);
      if(!isset($country["error"]))
        mysql_query("INSERT INTO countries(code, name, french_name, latitude, longitude) VALUES('".trim($el->Code)."', '".trim($country["name"])."', '".trim($country["name"])."', '".$country["latitude"]."', '".$country["longitude"]."');");
      else
        mysql_query("INSERT INTO countries(code, name) VALUES('".trim($el->Code)."', '###');");
    }*/
    $countries[$el->Code] = $country;
  }
  if(!isset($country["name"]))
  {
    array_push($new_array[$i]->countries, new Country("###", array(0, 0), $el->Valeur));
  }
  else
  {
    $name = $country["name"];
    if($lang == "fr")
      $name = $country["french_name"];
    array_push($new_array[$i]->countries, new Country($name, array($country["latitude"], $country["longitude"]), $el->Valeur));   
  }

}
foreach($all_countries as $key=>$value)
{  
  $country = $countries[$key];
  $name = $country["name"];
  if($lang == "fr")
    $name = $country["french_name"];
  array_push($new_array[0]->countries, new Country($name, array($country["latitude"], $country["longitude"]), $value));   
}
//order by enterprise name
usort($new_array, 'cmp');
foreach($new_array as $company)
{  
  usort($company->countries, "cmp_value_desc");
}

$new_array[0]->name = "All Companies";
if($lang == "fr")
  $new_array[0]->name = "Toutes";
print_JSON($new_array);
?>