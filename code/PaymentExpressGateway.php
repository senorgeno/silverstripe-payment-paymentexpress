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

    //Construct the request
    $request = new PxPayRequest();
    $request->setAmountInput($data['Amount']);
    $request->setCurrencyInput($data['Currency']);

    //Set PxPay properties
    if (isset($data['Reference'])) $request->setMerchantReference($data['Reference']);
		if (isset($data['EmailAddress'])) $request->setEmailAddress($data['EmailAddress']);

    $request->setUrlFail($this->cancelURL);
    $request->setUrlSuccess($this->returnURL);

    //Generate a unique identifier for the transaction
    $request->setTxnId(uniqid('ID')); 
    $request->setTxnType('Purchase');

    //Get encrypted URL from DPS to redirect the user to
    $pxpay = new PxPay_Curl($PxPay_Url, $PxPay_Userid, $PxPay_Key);
    $request_string = $pxpay->makeRequest($request);

    //Obtain output XML
    $response = new MifMessage($request_string);

    //Parse output XML
    $url = $response->get_element_text('URI');
    $valid = $response->get_attribute('valid');

    //Redirect to payment page
    Controller::curr()->redirect($url);
  }

  /**
   * Check that the payment was successful using "Process Response" API (http://www.paymentexpress.com/Technical_Resources/Ecommerce_Hosted/PxPay.aspx).
   * 
   * @param SS_HTTPRequest $request Request from the gateway - transaction response
   * @return PaymentGateway_Result
   */ 
	public function getResponse($request) {
		
		$config = $this->getConfig();

    $PxPay_Url    = Config::inst()->get('PaymentExpressGateway_PxPay', 'url');
    $PxPay_Userid = $config['authentication']['user_id'];
    $PxPay_Key    = $config['authentication']['key'];

		$url = $request->getVar('url');
		$result = $request->getVar('result');
		$userID = $request->getVar('userid');
		
		//Construct the request to check the payment status
    $request = new PxPayLookupRequest();
    $request->setResponse($result);

    //Get encrypted URL from DPS to redirect the user to
    $pxpay = new PxPay_Curl($PxPay_Url, $PxPay_Userid, $PxPay_Key);
    $request_string = $pxpay->makeRequest($request);

    //Obtain output XML
    $response = new MifMessage($request_string);
    
    //Parse output XML
    $success = $response->get_element_text('Success');

    if ($success == 0) {
    	return new PaymentGateway_Failure();
    }
    else {
    	return new PaymentGateway_Success();
    }
	}

}


