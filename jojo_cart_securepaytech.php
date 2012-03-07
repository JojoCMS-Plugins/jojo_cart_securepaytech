<?php
/**
 *                    Jojo CMS
 *                ================
 *
 * Copyright 2008 Harvey Kane <code@ragepank.com>
 * Copyright 2008 Michael Holt <code@gardyneholt.co.nz>
 *
 * See the enclosed file license.txt for license information (LGPL). If you
 * did not receive this file, see http://www.fsf.org/copyleft/lgpl.html.
 *
 * @author  Harvey Kane <code@ragepank.com>
 * @license http://www.fsf.org/copyleft/lgpl.html GNU Lesser General Public License
 * @link    http://www.jojocms.org JojoCMS
 */

define('_SECUREPAYTECH_CURRENCY', 'NZD'); //Currently hard-coded to NZD

class jojo_plugin_jojo_cart_securepaytech extends JOJO_Plugin
{
   function getPaymentOptions()
    {
        /* ensure the order currency is the same as SecurePayTech currency */
        $currency = call_user_func(array(Jojo_Cart_Class, 'getCartCurrency'));
        if ($currency != _SECUREPAYTECH_CURRENCY) return array();

        global $smarty;
        $options = array();

        /* get available card types (specified in options) */
        $cardtypes = explode(',', Jojo::getOption('securepaytech_card_types', 'visa,mastercard'));
        $cardimages = array();

        /* uppercase first letter of each card type */
        foreach ($cardtypes as $k => $v) {
            $cardtypes[$k] = trim(ucwords($v));
            if ($cardtypes[$k] == 'Visa') {
                $cardimages[$k] = '<img class="creditcard-icon" src="images/creditcardvisa.gif" alt="Visa" />';
            } elseif ($cardtypes[$k] == 'Mastercard') {
                $cardimages[$k] = '<img class="creditcard-icon" src="images/creditcardmastercard.gif" alt="Mastercard" />';
            } elseif ($cardtypes[$k] == 'Amex') {
                $cardimages[$k] = '<img class="creditcard-icon" src="images/creditcardamex.gif" alt="American Express" />';
            }
        }

        $smarty->assign('cardtypes', $cardtypes);
        $options[] = array('id' => 'securepaytech', 'label' => 'Pay now by Credit card '.implode(', ', $cardimages), 'html' => $smarty->fetch('jojo_cart_securepaytech_checkout.tpl'));
        return $options;
    }

   /*
    * Determines whether this payment plugin is active for the current payment.
    */
    function isActive()
    {
        /* Look for a post variable specifying DPS */
        return (Jojo::getFormData('handler', false) == 'securepaytech') ? true : false;
    }

   /*
    * Process the credit card based on POST data. Return array(success, receipt, errors)
    */
   function process()
   {
      global $smarty;

      $testmode = call_user_func(array(Jojo_Cart_Class, 'isTestMode'));

      $errors  = array();

      /* ensure the order currency is the same as SecurePayTech currency */
      $currency = call_user_func(array(Jojo_Cart_Class, 'getCartCurrency'));
      if ($currency != _SECUREPAYTECH_CURRENCY) {
          return array(
                      'success' => false,
                      'receipt' => array(),
                      'errors'  => array('SecurePayTech is only able to process transactions in '._SECUREPAYTECH_CURRENCY.'.')
                      );
      }

      /* merchant ID and password are stored as Jojo options */
      if ($testmode) {
          $merchantID  = 'TESTDIGISPL1';
          $merchantKey = 'd557591484cb2cd12bba445aba420d2c69cd6a88';
      } else {
          $merchantID  = Jojo::getOption('securepaytech_merchant_id');
          $merchantKey = Jojo::getOption('securepaytech_password');
      }

      /* card types are represented as numeric codes */
      $cardtype = strtolower(Jojo::getFormData('cardType', false));
      switch ($cardtype) {
      case 'visa':
          $cardtypeid = 1;
          break;
      case 'mastercard':
          $cardtypeid = 2;
          break;
      case 'amex':
          $cardtypeid = 3;
          break;
      case 'diners':
          $cardtypeid = 4;
          break;
      default:
          $cardtypeid = false;
      }

      /* strip dashes and spaces from card number */
      $cardnumber = Jojo::getFormData('cardNumber', '');
      $cardnumber = str_replace('-', '', $cardnumber);
      $cardnumber = str_replace(' ', '', $cardnumber);

      /* prepare data to send to securepaytech */
      $amount = number_format(call_user_func(array(Jojo_Cart_Class, 'total')), 2, '.', '');
      $token = Jojo::getFormData('token', '');
      $postvars = array(
                         'OrderReference' => $token,
                         'CardNumber'     => $cardnumber,
                         'CardExpiry'     => Jojo::getFormData('cardExpiryMonth', '').Jojo::getFormData('cardExpiryYear', ''),
                         'CardHolderName' => Jojo::getFormData('cardName', ''),
                         'CardType'       => $cardtypeid,
                         'MerchantID'     => $merchantID,
                         'MerchantKey'    => $merchantKey,
                         'Amount'         => $amount,
                         'Currency'       => _SECUREPAYTECH_CURRENCY,
                         'EnableCSC'      => 'true',
                         'CSC'            => Jojo::getFormData('CSC', '')
                    );

      /* error checking */
      $response = explode(',', jojo_plugin_jojo_cart_securepaytech::http_post("https", "tx.securepaytech.com", 8443, "/web/HttpPostPurchase", $postvars));

      $receipt  = array();

      /* accepted */
      $result = array();
      if ($response[0] == '1') {
          $success = true;
          $result['resultCode']      = $response[0];
          $result['merchTxnRef']     = $response[1];
          $result['receiptNo']       = $response[2];
          $result['transactionNo']   = $response[3];
          $result['authorizationID'] = $response[4];
          $result['batchNo']         = $response[5];


          /* prepare receipt */
          $receipt = array(
                            'Merchant name'                  => Jojo::getOption('cart_merchant_name', _SITETITLE),
                            'Merchant address'               => Jojo::getOption('cart_merchant_address', 'unspecified'),
                            'Merchant ID'                    => $merchantID,
                            'Result code'                    => $result['resultCode'],
                            'Merchant transaction reference' => $result['merchTxnRef'],
                            //'Transaction number'             => $result['transactionNo'],
                            //'Receipt number'                 => $result['receiptNo'],
                            //'Authorization ID'               => $result['authorizationID'],
                            //'Batch number'                   => $result['batchNo'],
                            'Purchase amount'                => _SECUREPAYTECH_CURRENCY.$amount,
                            'Transaction date'               => date('d M Y, h:i'),
                            'Result'                         => 'ACCEPTED',
            );

      /* insufficient funds */
      } elseif ($response[0] == '2') {
          $success               = false;
          $result['merchTxnRef'] = $response[1];
          $errors[]              = 'Insufficient funds';
          $result['failReason']  = 'Insufficient funds';
          $receipt = array(

                          'Transaction date'               => date('d M Y, h:i'),
                          'Merchant name'                  => Jojo::getOption('cart_merchant_name', _SITETITLE),
                          'Merchant address'               => Jojo::getOption('cart_merchant_address', 'unspecified'),
                          'Merchant ID'                    => $merchantID,
                          'Merchant transaction reference' => $result['merchTxnRef'],
                          'Transaction token'              => $token,
                          'Purchase amount'                => _SECUREPAYTECH_CURRENCY.$amount,
                          'Result'                         => 'DECLINED',
                          'Failure reason'                 => 'Insufficient funds',
                          );
      /* Card expired */
      } elseif ($response[0] == '3') {
          $success               = false;
          $result['merchTxnRef'] = $response[1];
          $errors[]              = 'Card expired';
          $result['failReason']  = 'Card expired';
          $receipt = array(
                          'Transaction date'               => date('d M Y, h:i'),
                          'Merchant name'                  => Jojo::getOption('cart_merchant_name', _SITETITLE),
                          'Merchant address'               => Jojo::getOption('cart_merchant_address', 'unspecified'),
                          'Merchant ID'                    => $merchantID,
                          'Merchant transaction reference' => $result['merchTxnRef'],
                          'Transaction token'              => $token,
                          'Purchase amount'                => _SECUREPAYTECH_CURRENCY.$amount,
                          'Result'                         => 'DECLINED',
                          'Failure reason'                 => 'Card expired',
                          );
      /* Card declined */
      } elseif ($response[0] == '4') {
          $success               = false;
          $result['merchTxnRef'] = $response[1];
          $errors[]              = 'Card declined';
          $result['failReason']  = 'Card declined';
          $receipt = array(
                          'Transaction date'               => date('d M Y, h:i'),
                          'Merchant name'                  => Jojo::getOption('cart_merchant_name', _SITETITLE),
                          'Merchant address'               => Jojo::getOption('cart_merchant_address', 'unspecified'),
                          'Merchant ID'                    => $merchantID,
                          'Merchant transaction reference' => $result['merchTxnRef'],
                          'Transaction token'              => $token,
                          'Purchase amount'                => _SECUREPAYTECH_CURRENCY.$amount,
                          'Result'                         => 'DECLINED',
                          'Failure reason'                 => 'Card declined',
                          );
      } else {
          $success              = false;
          $errors[]             = $response[1];
          $result['failReason'] = $response[1];
      }

      $smarty->assign('result', $result);

      $message = ($success) ? 'Thank you for your payment via Credit Card.': '';

      return array(
                 'success' => $success,
                 'receipt' => $receipt,
                 'errors'  => $errors,
                 'message' => $message
    );
   }

   function http_post($method, $server, $port, $url, $vars)
    {
        $postdata = "";
        foreach($vars as $key => $value) {
            $postdata .= urlencode($key) . "=" . urlencode($value) . "&";
        }
        $postdata = substr($postdata,0,-1);
        $content_length = strlen($postdata);
        $headers = "POST $url HTTP/1.1\r\n".
        "Accept: */*\r\n".
        "Accept-Language: en-nz\r\n".
        "Content-Type: application/x-www-form-urlencoded\r\n".
        "Host: $server\r\n".
        "Connection: Keep-Alive\r\n".
        "Cache-Control: no-cache\r\n".
        "Content-Length: $content_length\r\n\r\n";
        $ch = curl_init();
        curl_setopt ($ch, CURLOPT_URL, $method . '://' . $server .":". $port . $url);
        curl_setopt ($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt ($ch, CURLOPT_POST, 1);
        curl_setopt ($ch, CURLOPT_POSTFIELDS, $postdata);
        $ret = curl_exec($ch);
        curl_close($ch);
        return $ret;
    }
}