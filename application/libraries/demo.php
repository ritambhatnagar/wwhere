<?php
//ini_set('memory_limit','512M');
//ini_set('display_errors', true);
//error_reporting(-1);
function flightrequest($from, $to, $cabin, $depart, $return){

//$depart=date('Y-m-dTh:i:s',$_REQUEST['departdate']);
//$date=date_create($_REQUEST['departdate']);

//$depart= date_format($date,"Y-m-d H:i:s");
 //$depart = explode('+', $depart);
if(isset($depart)){
    //$depart=$_REQUEST['departdate'];
    $depart=date('Y-m-d\Th:i:s',  strtotime($depart));
}
//$str=  strtotime($depart);

//error_reporting(E_ALL ^ (E_NOTICE | E_WARNING));
/*
 * uAPI sample communication in php language 
 * 
 * This example requires the cURL library to be installed and working. 
 * 
 * Please note, in the sample code below, the variable $CREDENTIALS is created by binding your username and password together with a colon e.g. 
 * 
 * $auth = base64_encode("Universal API/API1234567:mypassword"); 
 * 
 * The variable $TARGETBRANCH should be set to the TargetBranch provided by Travelport. 
 * 
 * (C) 2011 Travelport, Inc. 
 * This code is for illustration purposes only. 
 */
$TARGETBRANCH = 'P105219';
$CREDENTIALS = 'Universal API/uAPI3839144488:BBsrEbArNTYBcMJ6x652FDA32';
$message = '<s:Envelope xmlns:s="http://schemas.xmlsoap.org/soap/envelope/">
  <s:Header/>
  <s:Body xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema">
    <LowFareSearchReq TargetBranch="' . $TARGETBRANCH . '" xmlns="http://www.travelport.com/schema/air_v20_0">
      <BillingPointOfSaleInfo OriginApplication="UAPI" xmlns="http://www.travelport.com/schema/common_v17_0" ></BillingPointOfSaleInfo>
      <SearchAirLeg>
        <SearchOrigin>
          <CityOrAirport Code="'.$from.'" xmlns="http://www.travelport.com/schema/common_v17_0" ></CityOrAirport>
        </SearchOrigin>
        <SearchDestination>
          <CityOrAirport Code="'.$to.'" xmlns="http://www.travelport.com/schema/common_v17_0" ></CityOrAirport>
        </SearchDestination>
        <SearchDepTime PreferredTime="'.$depart.'" ></SearchDepTime>
        <AirLegModifiers>
          <PreferredCabins>
            <CabinClass Type="'.$cabin.'" ></CabinClass>
          </PreferredCabins>
        </AirLegModifiers>
      </SearchAirLeg>';
    if(isset($return) && $return!=''){
       // $date=date_create($_REQUEST['returndate']);
         
       
        $return = date('Y-m-d\Th:i:s', strtotime($return));
       

        
    $message.='<SearchAirLeg>
        <SearchOrigin>
          <CityOrAirport Code="'.$to.'" xmlns="http://www.travelport.com/schema/common_v17_0" ></CityOrAirport>
        </SearchOrigin>
        <SearchDestination>
          <CityOrAirport Code="'.$from.'" xmlns="http://www.travelport.com/schema/common_v17_0" ></CityOrAirport>
        </SearchDestination>
        <SearchDepTime PreferredTime="'.$return.'" ></SearchDepTime>
        <AirLegModifiers>
          <PreferredCabins>
            <CabinClass Type="'.$cabin.'" ></CabinClass>
          </PreferredCabins>
        </AirLegModifiers>
      </SearchAirLeg>';
    }
    $message.='<AirSearchModifiers>
        <PreferredProviders>
          <Provider Code="1G" xmlns="http://www.travelport.com/schema/common_v17_0" ></Provider>
        </PreferredProviders>
      </AirSearchModifiers>
      <SearchPassenger Code="ADT" xmlns="http://www.travelport.com/schema/common_v17_0" ></SearchPassenger>
    </LowFareSearchReq>
  </s:Body>
</s:Envelope>';
  //echo $message;exit;

$auth = base64_encode("$CREDENTIALS");
$soap_do = curl_init("https://apac.universal-api.pp.travelport.com/B2BGateway/connect/uAPI/AirService");
$header = array(
    "Content-Type: text/xml;charset=UTF-8",
    "Accept: gzip,deflate",
    "Cache-Control: no-cache",
    "Pragma: no-cache",
    "SOAPAction: \"\"",
    "Authorization: Basic $auth",
    "Content-length: " . strlen($message),
);
//curl_setopt($soap_do, CURLOPT_CONNECTTIMEOUT, 60);
//curl_setopt($soap_do, CURLOPT_TIMEOUT, 60);
curl_setopt($soap_do, CURLOPT_RETURNTRANSFER, TRUE);
curl_setopt($soap_do, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($soap_do, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($soap_do, CURLOPT_POST, true);
curl_setopt($soap_do, CURLOPT_POSTFIELDS, $message);
curl_setopt($soap_do, CURLOPT_HTTPHEADER, $header);
$respons = curl_exec($soap_do);
curl_close($soap_do);

//echo '<pre>';
return xml2array($respons);
}

function xml2array($respons, $get_attributes = 1, $priority = 'tag') {
    $doc = new DOMDocument();
    $doc->loadXML($respons);
    $contents = $doc->saveXML();
    
    $parser = xml_parser_create('');
    xml_parser_set_option($parser, XML_OPTION_TARGET_ENCODING, "UTF-8");
    xml_parser_set_option($parser, XML_OPTION_CASE_FOLDING, 0);
    xml_parser_set_option($parser, XML_OPTION_SKIP_WHITE, 1);
    xml_parse_into_struct($parser, trim($contents), $xml_values);
    xml_parser_free($parser);
    if (!$xml_values)
        return; //Hmm...
    $xml_array = array();
    $parents = array();
    $opened_tags = array();
    $arr = array();
    $current = & $xml_array;
    $repeated_tag_index = array();
    foreach ($xml_values as $data) {
        unset($attributes, $value);
        extract($data);
        $result = array();
        $attributes_data = array();
        if (isset($value)) {
            if ($priority == 'tag')
                $result = $value;
            else
                $result['value'] = $value;
        }
        if (isset($attributes) and $get_attributes) {
            foreach ($attributes as $attr => $val) {
                if ($priority == 'tag')
                    $attributes_data[$attr] = $val;
                else
                    $result['attr'][$attr] = $val; //Set all the attributes in a array called 'attr'
            }
        }
        if ($type == "open") {
            $parent[$level - 1] = & $current;
            if (!is_array($current) or ( !in_array($tag, array_keys($current)))) {
                $current[$tag] = $result;
                if ($attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                $current = & $current[$tag];
            }
            else {
                if (isset($current[$tag][0])) {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    $repeated_tag_index[$tag . '_' . $level] ++;
                } else {
                    $current[$tag] = array(
                        $current[$tag],
                        $result
                    );
                    $repeated_tag_index[$tag . '_' . $level] = 2;
                    if (isset($current[$tag . '_attr'])) {
                        $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                        unset($current[$tag . '_attr']);
                    }
                }
                $last_item_index = $repeated_tag_index[$tag . '_' . $level] - 1;
                $current = & $current[$tag][$last_item_index];
            }
        } elseif ($type == "complete") {
            if (!isset($current[$tag])) {
                $current[$tag] = $result;
                $repeated_tag_index[$tag . '_' . $level] = 1;
                if ($priority == 'tag' and $attributes_data)
                    $current[$tag . '_attr'] = $attributes_data;
            }
            else {
                if (isset($current[$tag][0]) and is_array($current[$tag])) {
                    $current[$tag][$repeated_tag_index[$tag . '_' . $level]] = $result;
                    if ($priority == 'tag' and $get_attributes and $attributes_data) {
                        $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                    }
                    $repeated_tag_index[$tag . '_' . $level] ++;
                } else {
                    $current[$tag] = array(
                        $current[$tag],
                        $result
                    );
                    $repeated_tag_index[$tag . '_' . $level] = 1;
                    if ($priority == 'tag' and $get_attributes) {
                        if (isset($current[$tag . '_attr'])) {
                            $current[$tag]['0_attr'] = $current[$tag . '_attr'];
                            unset($current[$tag . '_attr']);
                        }
                        if ($attributes_data) {
                            $current[$tag][$repeated_tag_index[$tag . '_' . $level] . '_attr'] = $attributes_data;
                        }
                    }
                    $repeated_tag_index[$tag . '_' . $level] ++; //0 and 1 index is already taken
                }
            }
        } elseif ($type == 'close') {
            $current = & $parent[$level - 1];
        }
    }
    return ($xml_array);
}
?>