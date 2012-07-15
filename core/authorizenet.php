<?php
/**
* Easily interact with the Authorize.Net AIM API.
*
* Example Authorize and Capture Transaction against the Sandbox:
* <code>
* <?php require_once 'AuthorizeNet.php'
* $sale = new AuthorizeNetAIM;
* $sale->setFields(
*     array(
*    'amount' => '4.99',
*    'card_num' => '411111111111111',
*    'exp_date' => '0515'
*    )
* );
* $response = $sale->authorizeAndCapture();
* if ($response->approved) {
*     echo "Sale successful!"; } else {
*     echo $response->error_message;
* }
* ?>
* </code>
*
* Note: To send requests to the live gateway, either define this:
* define("AUTHORIZENET_SANDBOX", false);
*   -- OR --
* $sale = new AuthorizeNetAIM;
* $sale->setSandbox(false);
*
* @package    AuthorizeNet
* @subpackage AuthorizeNetAIM
* @link       http://www.authorize.net/support/AIM_guide.pdf AIM Guide
*/

/**
* Exception class for AuthorizeNet PHP SDK.
*
* @package AuthorizeNet
*/
class AuthorizeNetException extends Exception
{
}

/**
* Classes for the various AuthorizeNet data types.
*
* @package    AuthorizeNet
* @subpackage AuthorizeNetCIM
*/


/**
* A class that contains all fields for a CIM Customer Profile.
*
* @package    AuthorizeNet
* @subpackage AuthorizeNetCIM
*/
class AuthorizeNetCustomer
{
	public $merchantCustomerId;
	public $description;
	public $email;
	public $paymentProfiles = array();
	public $shipToList = array();
	public $customerProfileId;

}

/**
* A class that contains all fields for a CIM Address.
*
* @package    AuthorizeNet
* @subpackage AuthorizeNetCIM
*/
class AuthorizeNetAddress
{
	public $firstName;
	public $lastName;
	public $company;
	public $address;
	public $city;
	public $state;
	public $zip;
	public $country;
	public $phoneNumber;
	public $faxNumber;
	public $customerAddressId;
}

/**
* A class that contains all fields for a CIM Payment Profile.
*
* @package    AuthorizeNet
* @subpackage AuthorizeNetCIM
*/
class AuthorizeNetPaymentProfile
{

	public $customerType;
	public $billTo;
	public $payment;
	public $customerPaymentProfileId;

	public function __construct()
	{
		$this->billTo = new AuthorizeNetAddress;
		$this->payment = new AuthorizeNetPayment;
	}

}

/**
* A class that contains all fields for a CIM Payment Type.
*
* @package    AuthorizeNet
* @subpackage AuthorizeNetCIM
*/
class AuthorizeNetPayment
{
	public $creditCard;
	public $bankAccount;

	public function __construct()
	{
		$this->creditCard = new AuthorizeNetCreditCard;
		$this->bankAccount = new AuthorizeNetBankAccount;
	}
}

/**
* A class that contains all fields for a CIM Transaction.
*
* @package    AuthorizeNet
* @subpackage AuthorizeNetCIM
*/
class AuthorizeNetTransaction
{
	public $amount;
	public $tax;
	public $shipping;
	public $duty;
	public $lineItems = array();
	public $customerProfileId;
	public $customerPaymentProfileId;
	public $customerShippingAddressId;
	public $creditCardNumberMasked;
	public $bankRoutingNumberMasked;
	public $bankAccountNumberMasked;
	public $order;
	public $taxExempt;
	public $recurringBilling;
	public $cardCode;
	public $splitTenderId;
	public $approvalCode;
	public $transId;

	public function __construct()
	{
		$this->tax = (object)array();
		$this->tax->amount = "";
		$this->tax->name = "";
		$this->tax->description = "";

		$this->shipping = (object)array();
		$this->shipping->amount = "";
		$this->shipping->name = "";
		$this->shipping->description = "";

		$this->duty = (object)array();
		$this->duty->amount = "";
		$this->duty->name = "";
		$this->duty->description = "";

		// line items

		$this->order = (object)array();
		$this->order->invoiceNumber = "";
		$this->order->description = "";
		$this->order->purchaseOrderNumber = "";
	}

}

/**
* A class that contains all fields for a CIM Transaction Line Item.
*
* @package    AuthorizeNet
* @subpackage AuthorizeNetCIM
*/
class AuthorizeNetLineItem
{
	public $itemId;
	public $name;
	public $description;
	public $quantity;
	public $unitPrice;
	public $taxable;

}

/**
* A class that contains all fields for a CIM Credit Card.
*
* @package    AuthorizeNet
* @subpackage AuthorizeNetCIM
*/
class AuthorizeNetCreditCard
{
	public $cardNumber;
	public $expirationDate;
	public $cardCode;
}

/**
* A class that contains all fields for a CIM Bank Account.
*
* @package    AuthorizeNet
* @subpackage AuthorizeNetCIM
*/
class AuthorizeNetBankAccount
{
	public $accountType;
	public $routingNumber;
	public $accountNumber;
	public $nameOnAccount;
	public $echeckType;
	public $bankName;
}

/**
* A class that contains all fields for an AuthorizeNet ARB Subscription.
*
* @package    AuthorizeNet
* @subpackage AuthorizeNetARB
*/
class AuthorizeNet_Subscription
{

	public $name;
	public $intervalLength;
	public $intervalUnit;
	public $startDate;
	public $totalOccurrences;
	public $trialOccurrences;
	public $amount;
	public $trialAmount;
	public $creditCardCardNumber;
	public $creditCardExpirationDate;
	public $creditCardCardCode;
	public $bankAccountAccountType;
	public $bankAccountRoutingNumber;
	public $bankAccountAccountNumber;
	public $bankAccountNameOnAccount;
	public $bankAccountEcheckType;
	public $bankAccountBankName;
	public $orderInvoiceNumber;
	public $orderDescription;
	public $customerId;
	public $customerEmail;
	public $customerPhoneNumber;
	public $customerFaxNumber;
	public $billToFirstName;
	public $billToLastName;
	public $billToCompany;
	public $billToAddress;
	public $billToCity;
	public $billToState;
	public $billToZip;
	public $billToCountry;
	public $shipToFirstName;
	public $shipToLastName;
	public $shipToCompany;
	public $shipToAddress;
	public $shipToCity;
	public $shipToState;
	public $shipToZip;
	public $shipToCountry;

	public function getXml()
	{
		$xml = "<subscription>
		<name>{$this->name}</name>
		<paymentSchedule>
		<interval>
		<length>{$this->intervalLength}</length>
		<unit>{$this->intervalUnit}</unit>
		</interval>
		<startDate>{$this->startDate}</startDate>
		<totalOccurrences>{$this->totalOccurrences}</totalOccurrences>
		<trialOccurrences>{$this->trialOccurrences}</trialOccurrences>
		</paymentSchedule>
		<amount>{$this->amount}</amount>
		<trialAmount>{$this->trialAmount}</trialAmount>
		<payment>
		<creditCard>
		<cardNumber>{$this->creditCardCardNumber}</cardNumber>
		<expirationDate>{$this->creditCardExpirationDate}</expirationDate>
		<cardCode>{$this->creditCardCardCode}</cardCode>
		</creditCard>
		<bankAccount>
		<accountType>{$this->bankAccountAccountType}</accountType>
		<routingNumber>{$this->bankAccountRoutingNumber}</routingNumber>
		<accountNumber>{$this->bankAccountAccountNumber}</accountNumber>
		<nameOnAccount>{$this->bankAccountNameOnAccount}</nameOnAccount>
		<echeckType>{$this->bankAccountEcheckType}</echeckType>
		<bankName>{$this->bankAccountBankName}</bankName>
		</bankAccount>
		</payment>
		<order>
		<invoiceNumber>{$this->orderInvoiceNumber}</invoiceNumber>
		<description>{$this->orderDescription}</description>
		</order>
		<customer>
		<id>{$this->customerId}</id>
		<email>{$this->customerEmail}</email>
		<phoneNumber>{$this->customerPhoneNumber}</phoneNumber>
		<faxNumber>{$this->customerFaxNumber}</faxNumber>
		</customer>
		<billTo>
		<firstName>{$this->billToFirstName}</firstName>
		<lastName>{$this->billToLastName}</lastName>
		<company>{$this->billToCompany}</company>
		<address>{$this->billToAddress}</address>
		<city>{$this->billToCity}</city>
		<state>{$this->billToState}</state>
		<zip>{$this->billToZip}</zip>
		<country>{$this->billToCountry}</country>
		</billTo>
		<shipTo>
		<firstName>{$this->shipToFirstName}</firstName>
		<lastName>{$this->shipToLastName}</lastName>
		<company>{$this->shipToCompany}</company>
		<address>{$this->shipToAddress}</address>
		<city>{$this->shipToCity}</city>
		<state>{$this->shipToState}</state>
		<zip>{$this->shipToZip}</zip>
		<country>{$this->shipToCountry}</country>
		</shipTo>
		</subscription>";

		$xml_clean = "";
		// Remove any blank child elements
		foreach (preg_split("/(\r?\n)/", $xml) as $key => $line) {
			if (!preg_match('/><\//', $line)) {
				$xml_clean .= $line . "\n";
			}
		}

		// Remove any blank parent elements
		$element_removed = 1;
		// Recursively repeat if a change is made
		while ($element_removed) {
			$element_removed = 0;
			if (preg_match('/<[a-z]+>[\r?\n]+\s*<\/[a-z]+>/i', $xml_clean)) {
				$xml_clean = preg_replace('/<[a-z]+>[\r?\n]+\s*<\/[a-z]+>/i', '', $xml_clean);
				$element_removed = 1;
			}
		}

		// Remove any blank lines
		// $xml_clean = preg_replace('/\r\n[\s]+\r\n/','',$xml_clean);
		return $xml_clean;
	}
}

/**
* Sends requests to the Authorize.Net gateways.
*
* @package    AuthorizeNet
* @subpackage AuthorizeNetRequest
*/
abstract class AuthorizeNetRequest
{

	protected $_api_login;
	protected $_transaction_key;
	protected $_post_string;
	public $VERIFY_PEER = false; // Set to false if getting connection errors.
	protected $_sandbox = true;
	protected $_log_file = false;

	/**
	* Set the _post_string
	*/
	abstract protected function _setPostString();

	/**
	* Handle the response string
	*/
	abstract protected function _handleResponse($string);

	/**
	* Get the post url. We need this because until 5.3 you
	* you could not access child constants in a parent class.
	*/
	abstract protected function _getPostUrl();

	/**
	* Constructor.
	*
	* @param string $api_login_id       The Merchant's API Login ID.
	* @param string $transaction_key The Merchant's Transaction Key.
	*/
	public function __construct($api_login_id = false, $transaction_key = false)
	{
		$this->_api_login = ($api_login_id ? $api_login_id : (defined('AUTHORIZENET_API_LOGIN_ID') ? AUTHORIZENET_API_LOGIN_ID : ""));
		$this->_transaction_key = ($transaction_key ? $transaction_key : (defined('AUTHORIZENET_TRANSACTION_KEY') ? AUTHORIZENET_TRANSACTION_KEY : ""));
		$this->_sandbox = (defined('AUTHORIZENET_SANDBOX') ? AUTHORIZENET_SANDBOX : true);
		$this->_log_file = (defined('AUTHORIZENET_LOG_FILE') ? AUTHORIZENET_LOG_FILE : false);
	}

	/**
	* Alter the gateway url.
	*
	* @param bool $bool Use the Sandbox.
	*/
	public function setSandbox($bool)
	{
		$this->_sandbox = $bool;
	}

	/**
	* Set a log file.
	*
	* @param string $filepath Path to log file.
	*/
	public function setLogFile($filepath)
	{
		$this->_log_file = $filepath;
	}

	/**
	* Return the post string.
	*
	* @return string
	*/
	public function getPostString()
	{
		return $this->_post_string;
	}

	/**
	* Posts the request to AuthorizeNet & returns response.
	*
	* @return AuthorizeNetARB_Response The response.
	*/
	protected function _sendRequest()
	{
		$this->_setPostString();
		$post_url = $this->_getPostUrl();
		$curl_request = curl_init($post_url);
		curl_setopt($curl_request, CURLOPT_POSTFIELDS, $this->_post_string);
		curl_setopt($curl_request, CURLOPT_HEADER, 0);
		curl_setopt($curl_request, CURLOPT_TIMEOUT, 45);
		curl_setopt($curl_request, CURLOPT_RETURNTRANSFER, 1);
		curl_setopt($curl_request, CURLOPT_SSL_VERIFYHOST, 2);
		if ($this->VERIFY_PEER) {
			curl_setopt($curl_request, CURLOPT_CAINFO, dirname(dirname(__FILE__)) . '/ssl/cert.pem');
		} else {
			curl_setopt($curl_request, CURLOPT_SSL_VERIFYPEER, false);
		}

		if (preg_match('/xml/',$post_url)) {
			curl_setopt($curl_request, CURLOPT_HTTPHEADER, Array("Content-Type: text/xml"));
		}

		$response = curl_exec($curl_request);

		if ($this->_log_file) {

			if ($curl_error = curl_error($curl_request)) {
				file_put_contents($this->_log_file, "----CURL ERROR----\n$curl_error\n\n", FILE_APPEND);
			}
			// Do not log requests that could contain CC info.
			// file_put_contents($this->_log_file, "----Request----\n{$this->_post_string}\n", FILE_APPEND);

			file_put_contents($this->_log_file, "----Response----\n$response\n\n", FILE_APPEND);
		}
		curl_close($curl_request);

		return $this->_handleResponse($response);
	}

}

/**
* Base class for the AuthorizeNet AIM & SIM Responses.
*
* @package    AuthorizeNet
* @subpackage    AuthorizeNetResponse
*/


/**
* Parses an AuthorizeNet Response.
*
* @package AuthorizeNet
* @subpackage    AuthorizeNetResponse
*/
class AuthorizeNetResponse
{

	const APPROVED = 1;
	const DECLINED = 2;
	const ERROR = 3;
	const HELD = 4;

	public $approved;
	public $declined;
	public $error;
	public $held;
	public $response_code;
	public $response_subcode;
	public $response_reason_code;
	public $response_reason_text;
	public $authorization_code;
	public $avs_response;
	public $transaction_id;
	public $invoice_number;
	public $description;
	public $amount;
	public $method;
	public $transaction_type;
	public $customer_id;
	public $first_name;
	public $last_name;
	public $company;
	public $address;
	public $city;
	public $state;
	public $zip_code;
	public $country;
	public $phone;
	public $fax;
	public $email_address;
	public $ship_to_first_name;
	public $ship_to_last_name;
	public $ship_to_company;
	public $ship_to_address;
	public $ship_to_city;
	public $ship_to_state;
	public $ship_to_zip_code;
	public $ship_to_country;
	public $tax;
	public $duty;
	public $freight;
	public $tax_exempt;
	public $purchase_order_number;
	public $md5_hash;
	public $card_code_response;
	public $cavv_response; // cardholder_authentication_verification_response
	public $account_number;
	public $card_type;
	public $split_tender_id;
	public $requested_amount;
	public $balance_on_card;
	public $response; // The response string from AuthorizeNet.

}



/**
* Builds and sends an AuthorizeNet AIM Request.
*
* @package    AuthorizeNet
* @subpackage AuthorizeNetAIM
*/
class AuthorizeNetAIM extends AuthorizeNetRequest
{

	const LIVE_URL = 'https://secure.authorize.net/gateway/transact.dll';
	const SANDBOX_URL = 'https://test.authorize.net/gateway/transact.dll';

	/**
	* Holds all the x_* name/values that will be posted in the request.
	* Default values are provided for best practice fields.
	*/
	protected $_x_post_fields = array(
	"version" => "3.1",
	"delim_char" => ",",
	"delim_data" => "TRUE",
	"relay_response" => "FALSE",
	"encap_char" => "|",
	);

	/**
	* Only used if merchant wants to send multiple line items about the charge.
	*/
	private $_additional_line_items = array();

	/**
	* Only used if merchant wants to send custom fields.
	*/
	private $_custom_fields = array();

	/**
	* Checks to make sure a field is actually in the API before setting.
	* Set to false to skip this check.
	*/
	public $verify_x_fields = true;

	/**
	* A list of all fields in the AIM API.
	* Used to warn user if they try to set a field not offered in the API.
	*/
	private $_all_aim_fields = array("address","allow_partial_auth","amount",
	"auth_code","authentication_indicator", "bank_aba_code","bank_acct_name",
	"bank_acct_num","bank_acct_type","bank_check_number","bank_name",
	"card_code","card_num","cardholder_authentication_value","city","company",
	"country","cust_id","customer_ip","delim_char","delim_data","description",
	"duplicate_window","duty","echeck_type","email","email_customer",
	"encap_char","exp_date","fax","first_name","footer_email_receipt",
	"freight","header_email_receipt","invoice_num","last_name","line_item",
	"login","method","phone","po_num","recurring_billing","relay_response",
	"ship_to_address","ship_to_city","ship_to_company","ship_to_country",
	"ship_to_first_name","ship_to_last_name","ship_to_state","ship_to_zip",
	"split_tender_id","state","tax","tax_exempt","test_request","tran_key",
	"trans_id","type","version","zip"
	);

	/**
	* Do an AUTH_CAPTURE transaction.
	*
	* Required "x_" fields: card_num, exp_date, amount
	*
	* @param string $amount   The dollar amount to charge
	* @param string $card_num The credit card number
	* @param string $exp_date CC expiration date
	*
	* @return AuthorizeNetAIM_Response
	*/
	public function authorizeAndCapture($amount = false, $card_num = false, $exp_date = false)
	{
		($amount ? $this->amount = $amount : null);
		($card_num ? $this->card_num = $card_num : null);
		($exp_date ? $this->exp_date = $exp_date : null);
		$this->type = "AUTH_CAPTURE";
		return $this->_sendRequest();
	}

	/**
	* Do a PRIOR_AUTH_CAPTURE transaction.
	*
	* Required "x_" field: trans_id(The transaction id of the prior auth, unless split
	* tender, then set x_split_tender_id manually.)
	* amount (only if lesser than original auth)
	*
	* @param string $trans_id Transaction id to charge
	* @param string $amount   Dollar amount to charge if lesser than auth
	*
	* @return AuthorizeNetAIM_Response
	*/
	public function priorAuthCapture($trans_id = false, $amount = false)
	{
		($trans_id ? $this->trans_id = $trans_id : null);
		($amount ? $this->amount = $amount : null);
		$this->type = "PRIOR_AUTH_CAPTURE";
		return $this->_sendRequest();
	}

	/**
	* Do an AUTH_ONLY transaction.
	*
	* Required "x_" fields: card_num, exp_date, amount
	*
	* @param string $amount   The dollar amount to charge
	* @param string $card_num The credit card number
	* @param string $exp_date CC expiration date
	*
	* @return AuthorizeNetAIM_Response
	*/
	public function authorizeOnly($amount = false, $card_num = false, $exp_date = false)
	{
		($amount ? $this->amount = $amount : null);
		($card_num ? $this->card_num = $card_num : null);
		($exp_date ? $this->exp_date = $exp_date : null);
		$this->type = "AUTH_ONLY";
		return $this->_sendRequest();
	}

	/**
	* Do a VOID transaction.
	*
	* Required "x_" field: trans_id(The transaction id of the prior auth, unless split
	* tender, then set x_split_tender_id manually.)
	*
	* @param string $trans_id Transaction id to void
	*
	* @return AuthorizeNetAIM_Response
	*/
	public function void($trans_id = false)
	{
		($trans_id ? $this->trans_id = $trans_id : null);
		$this->type = "VOID";
		return $this->_sendRequest();
	}

	/**
	* Do a CAPTURE_ONLY transaction.
	*
	* Required "x_" fields: auth_code, amount, card_num , exp_date
	*
	* @param string $auth_code The auth code
	* @param string $amount    The dollar amount to charge
	* @param string $card_num  The last 4 of credit card number
	* @param string $exp_date  CC expiration date
	*
	* @return AuthorizeNetAIM_Response
	*/
	public function captureOnly($auth_code = false, $amount = false, $card_num = false, $exp_date = false)
	{
		($auth_code ? $this->auth_code = $auth_code : null);
		($amount ? $this->amount = $amount : null);
		($card_num ? $this->card_num = $card_num : null);
		($exp_date ? $this->exp_date = $exp_date : null);
		$this->type = "CAPTURE_ONLY";
		return $this->_sendRequest();
	}

	/**
	* Do a CREDIT transaction.
	*
	* Required "x_" fields: trans_id, amount, card_num (just the last 4)
	*
	* @param string $trans_id Transaction id to credit
	* @param string $amount   The dollar amount to credit
	* @param string $card_num The last 4 of credit card number
	*
	* @return AuthorizeNetAIM_Response
	*/
	public function credit($trans_id = false, $amount = false, $card_num = false)
	{
		($trans_id ? $this->trans_id = $trans_id : null);
		($amount ? $this->amount = $amount : null);
		($card_num ? $this->card_num = $card_num : null);
		$this->type = "CREDIT";
		return $this->_sendRequest();
	}

	/**
	* Alternative syntax for setting x_ fields.
	*
	* Usage: $sale->method = "echeck";
	*
	* @param string $name
	* @param string $value
	*/
	public function __set($name, $value)
	{
		$this->setField($name, $value);
	}

	/**
	* Quickly set multiple fields.
	*
	* Note: The prefix x_ will be added to all fields. If you want to set a
	* custom field without the x_ prefix, use setCustomField or setCustomFields.
	*
	* @param array $fields Takes an array or object.
	*/
	public function setFields($fields)
	{
		$array = (array)$fields;
		foreach ($array as $key => $value) {
			$this->setField($key, $value);
		}
	}

	/**
	* Quickly set multiple custom fields.
	*
	* @param array $fields
	*/
	public function setCustomFields($fields)
	{
		$array = (array)$fields;
		foreach ($array as $key => $value) {
			$this->setCustomField($key, $value);
		}
	}

	/**
	* Add a line item.
	*
	* @param string $item_id
	* @param string $item_name
	* @param string $item_description
	* @param string $item_quantity
	* @param string $item_unit_price
	* @param string $item_taxable
	*/
	public function addLineItem($item_id, $item_name, $item_description, $item_quantity, $item_unit_price, $item_taxable)
	{
		$line_item = "";
		$delimiter = "";
		foreach (func_get_args() as $key => $value) {
			$line_item .= $delimiter . $value;
			$delimiter = "<|>";
		}
		$this->_additional_line_items[] = $line_item;
	}

	/**
	* Use ECHECK as payment type.
	*/
	public function setECheck($bank_aba_code, $bank_acct_num, $bank_acct_type, $bank_name, $bank_acct_name, $echeck_type = 'WEB')
	{
		$this->setFields(
		array(
		'method' => 'echeck',
		'bank_aba_code' => $bank_aba_code,
		'bank_acct_num' => $bank_acct_num,
		'bank_acct_type' => $bank_acct_type,
		'bank_name' => $bank_name,
		'bank_acct_name' => $bank_acct_type,
		'echeck_type' => $echeck_type,
		)
		);
	}

	/**
	* Set an individual name/value pair. This will append x_ to the name
	* before posting.
	*
	* @param string $name
	* @param string $value
	*/
	public function setField($name, $value)
	{
		if ($this->verify_x_fields) {
			if (in_array($name, $this->_all_aim_fields)) {
				$this->_x_post_fields[$name] = $value;
			} else {
				throw new AuthorizeNetException("Error: no field $name exists in the AIM API.
				To set a custom field use setCustomField('field','value') instead.");
			}
		} else {
			$this->_x_post_fields[$name] = $value;
		}
	}

	/**
	* Set a custom field. Note: the x_ prefix will not be added to
	* your custom field if you use this method.
	*
	* @param string $name
	* @param string $value
	*/
	public function setCustomField($name, $value)
	{
		$this->_custom_fields[$name] = $value;
	}

	/**
	* Unset an x_ field.
	*
	* @param string $name Field to unset.
	*/
	public function unsetField($name)
	{
		unset($this->_x_post_fields[$name]);
	}

	/**
	*
	*
	* @param string $response
	*
	* @return AuthorizeNetAIM_Response
	*/
	protected function _handleResponse($response)
	{
		return new AuthorizeNetAIM_Response($response, $this->_x_post_fields['delim_char'], $this->_x_post_fields['encap_char'], $this->_custom_fields);
	}

	/**
	* @return string
	*/
	protected function _getPostUrl()
	{
		return ($this->_sandbox ? self::SANDBOX_URL : self::LIVE_URL);
	}

	/**
	* Converts the x_post_fields array into a string suitable for posting.
	*/
	protected function _setPostString()
	{
		$this->_x_post_fields['login'] = $this->_api_login;
		$this->_x_post_fields['tran_key'] = $this->_transaction_key;
		$this->_post_string = "";
		foreach ($this->_x_post_fields as $key => $value) {
			$this->_post_string .= "x_$key=" . urlencode($value) . "&";
		}
		// Add line items
		foreach ($this->_additional_line_items as $key => $value) {
			$this->_post_string .= "x_line_item=" . urlencode($value) . "&";
		}
		// Add custom fields
		foreach ($this->_custom_fields as $key => $value) {
			$this->_post_string .= "$key=" . urlencode($value) . "&";
		}
		$this->_post_string = rtrim($this->_post_string, "& ");
	}
}

/**
* Parses an AuthorizeNet AIM Response.
*
* @package    AuthorizeNet
* @subpackage AuthorizeNetAIM
*/
class AuthorizeNetAIM_Response extends AuthorizeNetResponse
{
	private $_response_array = array(); // An array with the split response.

	/**
	* Constructor. Parses the AuthorizeNet response string.
	*
	* @param string $response      The response from the AuthNet server.
	* @param string $delimiter     The delimiter used (default is ",")
	* @param string $encap_char    The encap_char used (default is "|")
	* @param array  $custom_fields Any custom fields set in the request.
	*/
	public function __construct($response, $delimiter, $encap_char, $custom_fields)
	{
		if ($response) {

			// Split Array
			$this->response = $response;
			if ($encap_char) {
				$this->_response_array = explode($encap_char.$delimiter.$encap_char, substr($response, 1, -1));
			} else {
				$this->_response_array = explode($delimiter, $response);
			}

			/**
			* If AuthorizeNet doesn't return a delimited response.
			*/
			if (count($this->_response_array) < 10) {
				$this->approved = false;
				$this->error = true;
				$this->error_message = "Unrecognized response from AuthorizeNet: $response";
				return;
			}



			// Set all fields
			$this->response_code        = $this->_response_array[0];
			$this->response_subcode     = $this->_response_array[1];
			$this->response_reason_code = $this->_response_array[2];
			$this->response_reason_text = $this->_response_array[3];
			$this->authorization_code   = $this->_response_array[4];
			$this->avs_response         = $this->_response_array[5];
			$this->transaction_id       = $this->_response_array[6];
			$this->invoice_number       = $this->_response_array[7];
			$this->description          = $this->_response_array[8];
			$this->amount               = $this->_response_array[9];
			$this->method               = $this->_response_array[10];
			$this->transaction_type     = $this->_response_array[11];
			$this->customer_id          = $this->_response_array[12];
			$this->first_name           = $this->_response_array[13];
			$this->last_name            = $this->_response_array[14];
			$this->company              = $this->_response_array[15];
			$this->address              = $this->_response_array[16];
			$this->city                 = $this->_response_array[17];
			$this->state                = $this->_response_array[18];
			$this->zip_code             = $this->_response_array[19];
			$this->country              = $this->_response_array[20];
			$this->phone                = $this->_response_array[21];
			$this->fax                  = $this->_response_array[22];
			$this->email_address        = $this->_response_array[23];
			$this->ship_to_first_name   = $this->_response_array[24];
			$this->ship_to_last_name    = $this->_response_array[25];
			$this->ship_to_company      = $this->_response_array[26];
			$this->ship_to_address      = $this->_response_array[27];
			$this->ship_to_city         = $this->_response_array[28];
			$this->ship_to_state        = $this->_response_array[29];
			$this->ship_to_zip_code     = $this->_response_array[30];
			$this->ship_to_country      = $this->_response_array[31];
			$this->tax                  = $this->_response_array[32];
			$this->duty                 = $this->_response_array[33];
			$this->freight              = $this->_response_array[34];
			$this->tax_exempt           = $this->_response_array[35];
			$this->purchase_order_number= $this->_response_array[36];
			$this->md5_hash             = $this->_response_array[37];
			$this->card_code_response   = $this->_response_array[38];
			$this->cavv_response        = $this->_response_array[39];
			$this->account_number       = $this->_response_array[50];
			$this->card_type            = $this->_response_array[51];
			$this->split_tender_id      = $this->_response_array[52];
			$this->requested_amount     = $this->_response_array[53];
			$this->balance_on_card      = $this->_response_array[54];

			$this->approved = ($this->response_code == self::APPROVED);
			$this->declined = ($this->response_code == self::DECLINED);
			$this->error    = ($this->response_code == self::ERROR);
			$this->held     = ($this->response_code == self::HELD);

			// Set custom fields
			if ($count = count($custom_fields)) {
				$custom_fields_response = array_slice($this->_response_array, -$count, $count);
				$i = 0;
				foreach ($custom_fields as $key => $value) {
					$this->$key = $custom_fields_response[$i];
					$i++;
				}
			}

			if ($this->error) {
				$this->error_message = "AuthorizeNet Error:
				Response Code: ".$this->response_code."
				Response Subcode: ".$this->response_subcode."
				Response Reason Code: ".$this->response_reason_code."
				Response Reason Text: ".$this->response_reason_text."
				";
			}
		} else {
			$this->approved = false;
			$this->error = true;
			$this->error_message = "Error connecting to AuthorizeNet";
		}
	}

}

