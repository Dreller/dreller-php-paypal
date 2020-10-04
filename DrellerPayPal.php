<?php
# Class by Dreller
# https://github.com/Dreller
#
# Thank you to John C for his tool 'Curl-to-PHP' !
# https://incarnate.github.io/curl-to-php/
# https://github.com/incarnate/curl-to-php

class DrellerPayPal{

  private $myToken    = '';   # Auth. Token
  private $apiURL     = '';   # URL base
  private $ppEvents       ;   # PayPal Event Codes
  private $ppStatus       ;   # PayPal Status Codes


## Construction ################################################################
# On construction of instance, the class calls PayPal for a Token, that will
# be used for further functions.
  public function __construct($cID = '', $cSec = '', $sandbox = false){
    # Establish the base URL for APIs Calls.
    $this->apiURL = 'https://api'.($sandbox != false?'.sandbox':'').'.paypal.com/';

    # Call PayPal and ask for a Bearer Token
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $this->apiURL.'v1/oauth2/token');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=client_credentials");
    curl_setopt($ch, CURLOPT_USERPWD, $cID . ":" . $cSec);
    $headers = array();
    $headers[] = 'Accept: application/json';
    $headers[] = 'Accept-Language: en_US';
    $headers[] = 'Content-Type: application/x-www-form-urlencoded';
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    $resp = json_decode($result, true);
    $this->myToken = $resp['access_token'];

    # Load reference lists
    $this->ppEvents = parse_ini_file("PayPalEventCodes.ini", true);
    $this->ppStatus = parse_ini_file("PayPalStatusCodes.ini");

  }

## Utilities ###################################################################

/**
 * Catch a value from an Array.
 * @param string $valueName   Name of item to look for.
 * @param array $array        Name of array that contains datas.
 * @param mixed $returning  Value to return if item is not in the array.
 * @return mixed
 */
protected function catchInArray($valueName, &$array, $returning = ''){
    $tmp = $returning;
    if( isset($array[$valueName]) && $array[$valueName] != $returning ){
        $tmp = trim($array[$valueName]);
    }
    return $tmp;
}

/**
 * Send cURL Call to PayPal and return the result
 * @param string $url       URL to call
 * @return mixed
 */
protected function callPayPal($url){
  # TODO
}


## Functions for Transactions Lookups ##########################################

/**
 * Retrieve transactions from today to T-30.
 */
 public function getTransactionsCurrent(){
   $options = Array(
     "from"    => date('Y-m-d', strtotime('-30 days')),
     "to"      => date('Y-m-d')
   );
   return $this->getTransactions($options);
 }

  protected function getTransactions($options = null){
    if( !is_array($options) ){
      $options = Array();
    }
    $from   = $this->catchInArray('from', $options, date('Y-m-d', strtotime('-30 days')));
    $to     = $this->catchInArray('to', $options, date('Y-m-d'));

    $url    = $this->apiURL . 'v1/reporting/transactions?';
    $url   .= 'start_date=' . $from . 'T00:00:01-0000';
    $url   .= '&end_date=' . $to . 'T23:59:59-0000';
    $url   .= '&fields=transaction_info';
    $url   .= '&page_size=100';
    $url   .= '&page=1';

    $ch     = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
    $headers = array();
    $headers[] = 'Content-Type: application/json';
    $headers[] = 'Authorization: Bearer ' . $this->myToken;
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    return json_decode($result, true);
    # Use the result as an Array, like this:
    # foreach( $var['transaction_details'] as $trans ){
    #   $tran = $trans['transaction_info'];
    #   $transactionID = $tran['transaction_id'];
    # }
  }

  public function transPayPalEvent($code){
    $section = substr($code, 0, 3);
    $message = substr($code, -2, 2);
    return $this->ppEvents[$section]['XX'] . ' / ' . $this->ppEvents[$section][$message];
  }
  public function transPayPalStatus($code){
    return $this->ppStatus[$code];
  }

## Functions for Orders #######################################################

public function getOrderDetails($orderID = ''){

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $this->apiURL . 'v2/checkout/orders/' . $orderID);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
  curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'GET');
  $headers = array();
  $headers[] = 'Content-Type: application/json';
  $headers[] = 'Authorization: Bearer ' . $this->myToken;
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);

  $result = curl_exec($ch);
  if (curl_errno($ch)) {
      echo 'Error:' . curl_error($ch);
  }
  curl_close($ch);
  return json_decode($result, true);
}


}
