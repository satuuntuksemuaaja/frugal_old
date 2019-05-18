<?php
namespace vl\libraries;
use Log, Exception, StdClass;
class Bluepay
{
    static public $URL = 'https://secure.bluepay.com/interfaces/bp20post';
    protected $transType;
    protected $customerInfo;
    protected $accountType;
    protected $amount;
    protected $rebill = false;
    protected $masterId;
    protected $account; // Payment account (routing or CC)
    protected $ssn;
    protected $birthdate;
    protected $memo;
    protected $customid1;
    protected $customid2;
    protected $orderId;
    protected $invoiceId;


    static public function init($mode, $account, $user, $secret)
    {
        $self = new self;
        $self->account = $account;
        $self->user = $user;
        $self->secret = $secret;
        $self->mode = $mode;
        return $self;
    }

    public function transmit(array $fields)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, self::$URL);
        curl_setopt($ch, CURLOPT_USERAGENT, "BluepayPHP SDK/2.0"); // Cosmetic
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($fields));
        $response = curl_exec($ch);
        parse_str($response);
        if ($STATUS == "1")
        {
          $r = new StdClass();
          $r->transId = $TRANS_ID;
            $r->bpStatus = $STATUS;
            $r->avs = $AVS;
            $r->cvv = $CVV2;
            $r->auth = $AUTH_CODE;
            $r->reason = $MESSAGE;
            $r->rebid = $REBID;
            $r->amount = $fields['AMOUNT'];
            return $r;
        }
        else
            throw new Exception($MESSAGE);
    }

    public function isRefund()
    {
      $this->transType = "REFUND";
      return $this;
    }

    public function isSale()
    {
      $this->transType = "SALE";
      return $this;
    }

    public function isAuth()
    {
      $this->transType = "AUTH";
      return $this;
    }

    public function isCapture()
    {
      $this->transType = "CAPTURE";
      return $this;
    }
    public function getName()
    {
        return (isset($this->customerInfo['first'])) ? $this->customerInfo['first'] : null;
    }

    public function memo($memo)
    {
        $this->memo = $memo;
        return $this;
    }

    public function tps(array &$payload)
    {
        if (!isset($payload['PAYMENT_ACCOUNT'])) $payload['PAYMENT_ACCOUNT'] = null;
        $hash = $this->secret . $this->account . $this->transType .
        $this->amount . $this->masterId . $this->getName() . $payload['PAYMENT_ACCOUNT'];
        return bin2hex( md5($hash, true) );
    }

    public function orderId($orderid)
    {
        $this->orderId = $orderid;
        return $this;
    }

    public function setRecurring($amount = null, $date = null, $cycle = '1 MONTH', $cycles = 0)
    {
        if (!$date)
            $date = date("Y-m-d H:i:s", time());
        if ($amount)
            $this->rebill['amount'] = $amount;
        else
            $this->rebill['amount'] = $this->amount;
        $this->rebill['date'] = $date;
        $this->rebill['cycle'] = $cycle;
        $this->rebill['cycles'] = $cycles;
        return $this;
    }

    public function setCustomer($details)
    {
        if (is_array($details))
        {
            $this->customerInfo['first']    = $details['first'];
            $this->customerInfo['last']     = $details['last'];
            $this->customerInfo['address']    = $details['address'];
            $this->customerInfo['address2']     = (isset($details['address2'])) ? $details['address2'] : null;
            $this->customerInfo['city']     = $details['city'];
            $this->customerInfo['state']    = $details['state'];
            $this->customerInfo['zip']      = $details['zip'];
            $this->customerInfo['country']    = (isset($details['country'])) ? $details['country'] : "US";
            $this->customerInfo['phone']    = $details['phone'];
            $this->customerInfo['email']    = $details['email'];
        }
        else
        {
            $this->customerInfo = $details;
            $this->masterId = $details;
        }
        return $this;
    }

    public function setCard($card, $cvv, $exp)
    {
        $this->customerInfo['card']         = $card;
        $this->customerInfo['cvv']          = $cvv;
        $this->customerInfo['exp']          = $exp;
        $this->accountType                  = 'CREDIT';
        return $this;
    }

    public function setACH($route, $account, $type, $id = null) // C or S
    {
        $this->customerInfo['route']    = $route;
        $this->customerInfo['account']    = $account;
        $this->customerInfo['type']     = $type;
        $this->customerInfo['id']           = $id;
        $this->accountType                  = 'ACH';
        return $this;
    }

    public function setAmount($amount)
    {
        $this->amount =  $amount;
        return $this;
    }

    public function create()
    {
        $payload = [];

        /*
         * Transaction Details
         */

        $payload['CUSTOMER_IP'] = $_SERVER['REMOTE_ADDR'];
        $payload['ACCOUNT_ID'] = $this->account;
        $payload['USER_ID'] = $this->user;
        $payload['TRANS_TYPE'] = $this->transType;
        $payload['PAYMENT_TYPE'] = $this->accountType;
      $payload['MODE'] = $this->mode;
      $payload['MASTER_ID'] = $this->masterId;
      $payload['AMOUNT'] = $this->amount;
      if (!$this->masterId)
      {

          /*
           * Customer Information
           */
          if (is_array($this->customerInfo))
          {
              $payload['NAME1'] = $this->customerInfo['first'];
                $payload['NAME2'] = $this->customerInfo['last'];
                if (isset($this->customerInfo['company']))
                    $payload['COMPANY_NAME'] = $this->customerInfo['company'];
                $payload['ADDR1'] = $this->customerInfo['address'];
                $payload['ADDR2'] = $this->customerInfo['address2'];
                $payload['CITY']  = $this->customerInfo['city'];
                $payload['STATE'] = $this->customerInfo['state'];
                $payload['ZIP']   = $this->customerInfo['zip'];
                $payload['PHONE'] = $this->customerInfo['phone'];
                $payload['EMAIL'] = $this->customerInfo['email'];
                $payload['COUNTRY'] = $this->customerInfo['country'];
          }
            /*
             * Accessory Items
             */
          $payload['SSN'] = $this->ssn;
          $payload['BIRTHDATE'] = $this->birthdate;
          $payload['MEMO'] = $this->memo;
          $payload['CUSTOM_ID'] = $this->customid1;
          $payload['CUSTOM_ID2'] = $this->customid2;
          $payload['ORDER_ID'] = $this->orderId;
          $payload['INVOICE_ID'] = $this->invoiceId;
          $payload['AMOUNT_TIP'] = null;
            $payload['AMOUNT_TAX'] = null;

            /*
             * Rebill Information
             *
             */
            if ($this->rebill)
            {
                $payload['DO_REBILL'] = ($this->rebill) ? 1 : 0;
              $payload['REB_FIRST_DATE'] = $this->rebill['start'];
              $payload['REB_EXPR'] = $this->rebill['expires'];
              $payload['REB_CYCLES'] = $this->rebill['cycles'];
              $payload['REB_AMOUNT'] = $this->rebill['amount'];
            }
            else
                $payload['DO_REBILL'] = 0;

          /*
           * Determine Payment Account To Submit.
           */
            if ($this->accountType == "CREDIT")
            {
              $payload['PAYMENT_ACCOUNT'] = $this->customerInfo['card'];
              $payload['CARD_CVV2'] = $this->customerInfo['cvv'];
              $payload['CARD_EXPIRE'] = $this->customerInfo['exp'];
            }
            else
            {
                $payload['PAYMENT_ACCOUNT'] =  $this->customerInfo['type'].":".$this->customerInfo['route'].":".$this->customerInfo['account'];
                $payload['CUST_ID'] = $this->customerInfo['id'];
                $payload['CUST_ID_STATE'] = $this->customerInfo['state'];
            }

      } // if no masterid.

                $payload['TAMPER_PROOF_SEAL'] = $this->tps($payload);

      return $this->transmit($payload);
    }

}