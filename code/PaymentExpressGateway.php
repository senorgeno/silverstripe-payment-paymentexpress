<?php

class PaymentExpressGateway_PxPay extends PaymentGateway_GatewayHosted {

  protected $supportedCurrencies = array(
    'NZD' => 'New Zealand Dollar',
    'USD' => 'United States Dollar',
    'GBP' => 'Great British Pound'
  );

  public function getSupportedCurrencies() {

    $config = $this->getConfig();
    if (isset($config['supported_currencies'])) {
      $this->supportedCurrencies = $config['supported_currencies'];
    }
    return $this->supportedCurrencies;
  }

  public function process($data) {

    $config = $this->getConfig();

    $PxPay_Url    = Config::inst()->get('PaymentExpressGateway_PxPay', 'url');
    $PxPay_Userid = $config['authentication']['user_id'];
    $PxPay_Key    = $config['authentication']['key'];

    $pxpay = new PxPay_Curl($PxPay_Url, $PxPay_Userid, $PxPay_Key);
    $request = new PxPayRequest();

    $request->setAmountInput($data['Amount']);
    $request->setCurrencyInput($data['Currency']);

    //Set PxPay properties
    if (isset($data['Reference'])) $request->setMerchantReference($data['Reference']);
		if (isset($data['EmailAddress'])) $request->setEmailAddress($data['EmailAddress']);

    $request->setUrlFail($this->returnURL);    //Can be a dedicated failure page
    $request->setUrlSuccess($this->returnURL); //Can be a dedicated success page

    //Generate a unique identifier for the transaction
    $request->setTxnId(uniqid('ID')); 
    $request->setTxnType('Purchase');

    //Call makeRequest function to obtain input XML
    $request_string = $pxpay->makeRequest($request);

    //Obtain output XML
    $response = new MifMessage($request_string);

    //Parse output XML
    $url = $response->get_element_text('URI');
    $valid = $response->get_attribute('valid');

    //Redirect to payment page
    Controller::curr()->redirect($url);
  }

}


