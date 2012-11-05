SilverStripe Payment PaymentExpress Module
===========================================

**Work in progress**

Maintainer Contacts
-------------------
Frank Mullenger (frankmullenger_AT_gmail(dot)com)
* [Deadly Technology Blog](http://deadlytechnology.com/silverstripe/)
* [SwipeStripe Shop](http://swipestripe.com)

Requirements
------------
* SilverStripe 3.0
* Payment module 1.0

Documentation
-------------
Payment Express pxpay integration for payment module

Installation Instructions
-------------------------
1. Place this directory in the root of your SilverStripe installation and call it 'payment-paymentexpress'.
2. Visit yoursite.com/dev/build?flush=1 to rebuild the database.

Usage Overview
--------------
1. Enable in your application YAML config

```yaml
PaymentGateway:
  environment:
    'dev'

PaymentProcessor:
  supported_methods:
    dev:
      - 'PaymentExpressPxPay'
    live:
      - 'PaymentExpressPxPay'
```
2. Configure using your PaymentExpress account details

```yaml
PaymentExpressGateway_PxPay:
  live:
    authentication:
      user_id: 'Payment Express user id here'
      key: 'Payment Express key here'
  dev:
    authentication:
      user_id: 'Payment Express user id here'
      key: 'Payment Express key here'
    # Currencies that you wish to process payments in (usually just one)
    supported_currencies:
      'NZD' : 'New Zealand Dollar'
      'USD' : 'United Statues Dollar'
```

3. Remember to ?flush=1 after changes to the config YAML files