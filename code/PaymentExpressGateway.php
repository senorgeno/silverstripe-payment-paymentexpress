<?php

/**
 * Paystation 3 party gateway, payment is processed on the gateway.
 * 
 * http://www.paystation.co.nz/cms_show_download.php?id=8
 */
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

  /**
   * Process the payment by redirecting user to Paystation
   * 
   * @param Array $data
   */
  public function process($data) {

    $config = $this->getConfig();

    $PxPay_Url    = Config::inst()->get('PaymentExpressGateway_PxPay', 'url');
    $PxPay_Userid = $config['authentication']['user_id'];
    $PxPay_Key    = $config['authentication']['key'];

    $pxpay = new PxPay_Curl($PxPay_Url, $PxPay_Userid, $PxPay_Key);
    $request = new PxPayRequest();

    // TODO: Need to set email address and merchant reference

    //Calculate AmountInput
    $amount = $data['Amount'];
    $currency = $data['Currency'];

    //Generate a unique identifier for the transaction
    $TxnId = uniqid("ID");

    //Set PxPay properties
    $request->setMerchantReference('merchant ref');
    $request->setEmailAddress('test@example.com');

    $request->setAmountInput($amount);
    $request->setCurrencyInput($currency);

    $request->setUrlFail($this->returnURL);    //Can be a dedicated failure page
    $request->setUrlSuccess($this->returnURL); //Can be a dedicated success page

    $request->setTxnType("Purchase");
    $request->setTxnId($TxnId); 

    //Call makeRequest function to obtain input XML
    $request_string = $pxpay->makeRequest($request);

    //Obtain output XML
    $response = new MifMessage($request_string);

    //Parse output XML
    $url = $response->get_element_text("URI");
    $valid = $response->get_attribute("valid");

    //Redirect to payment page
    Controller::curr()->redirect($url);
  }

}


