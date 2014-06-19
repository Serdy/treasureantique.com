<?php
// autogenerated file 30.09.2013 15:20
// $Id: $
// $Log: $
//
//
require_once 'EbatNs_ComplexType.php';
require_once 'UserIdentityType.php';
require_once 'AmountType.php';
require_once 'PaymentTransactionStatusCodeType.php';
require_once 'TransactionReferenceType.php';

/**
 * Contains detaled payment transaction information. 
 *
 * @link http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/types/PaymentTransactionCodeType.html
 *
 */
class PaymentTransactionCodeType extends EbatNs_ComplexType
{
	/**
	 * @var PaymentTransactionStatusCodeType
	 */
	protected $PaymentStatus;
	/**
	 * @var UserIdentityType
	 */
	protected $Payer;
	/**
	 * @var UserIdentityType
	 */
	protected $Payee;
	/**
	 * @var dateTime
	 */
	protected $PaymentTime;
	/**
	 * @var AmountType
	 */
	protected $PaymentAmount;
	/**
	 * @var TransactionReferenceType
	 */
	protected $ReferenceID;
	/**
	 * @var AmountType
	 */
	protected $FeeOrCreditAmount;

	/**
	 * @return PaymentTransactionStatusCodeType
	 */
	function getPaymentStatus()
	{
		return $this->PaymentStatus;
	}
	/**
	 * @return void
	 * @param PaymentTransactionStatusCodeType $value 
	 */
	function setPaymentStatus($value)
	{
		$this->PaymentStatus = $value;
	}
	/**
	 * @return UserIdentityType
	 */
	function getPayer()
	{
		return $this->Payer;
	}
	/**
	 * @return void
	 * @param UserIdentityType $value 
	 */
	function setPayer($value)
	{
		$this->Payer = $value;
	}
	/**
	 * @return UserIdentityType
	 */
	function getPayee()
	{
		return $this->Payee;
	}
	/**
	 * @return void
	 * @param UserIdentityType $value 
	 */
	function setPayee($value)
	{
		$this->Payee = $value;
	}
	/**
	 * @return dateTime
	 */
	function getPaymentTime()
	{
		return $this->PaymentTime;
	}
	/**
	 * @return void
	 * @param dateTime $value 
	 */
	function setPaymentTime($value)
	{
		$this->PaymentTime = $value;
	}
	/**
	 * @return AmountType
	 */
	function getPaymentAmount()
	{
		return $this->PaymentAmount;
	}
	/**
	 * @return void
	 * @param AmountType $value 
	 */
	function setPaymentAmount($value)
	{
		$this->PaymentAmount = $value;
	}
	/**
	 * @return TransactionReferenceType
	 */
	function getReferenceID()
	{
		return $this->ReferenceID;
	}
	/**
	 * @return void
	 * @param TransactionReferenceType $value 
	 */
	function setReferenceID($value)
	{
		$this->ReferenceID = $value;
	}
	/**
	 * @return AmountType
	 */
	function getFeeOrCreditAmount()
	{
		return $this->FeeOrCreditAmount;
	}
	/**
	 * @return void
	 * @param AmountType $value 
	 */
	function setFeeOrCreditAmount($value)
	{
		$this->FeeOrCreditAmount = $value;
	}
	/**
	 * @return 
	 */
	function __construct()
	{
		parent::__construct('PaymentTransactionCodeType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
				self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
				array(
					'PaymentStatus' =>
					array(
						'required' => false,
						'type' => 'PaymentTransactionStatusCodeType',
						'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
						'array' => false,
						'cardinality' => '0..1'
					),
					'Payer' =>
					array(
						'required' => false,
						'type' => 'UserIdentityType',
						'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
						'array' => false,
						'cardinality' => '0..1'
					),
					'Payee' =>
					array(
						'required' => false,
						'type' => 'UserIdentityType',
						'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
						'array' => false,
						'cardinality' => '0..1'
					),
					'PaymentTime' =>
					array(
						'required' => false,
						'type' => 'dateTime',
						'nsURI' => 'http://www.w3.org/2001/XMLSchema',
						'array' => false,
						'cardinality' => '0..1'
					),
					'PaymentAmount' =>
					array(
						'required' => false,
						'type' => 'AmountType',
						'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
						'array' => false,
						'cardinality' => '0..1'
					),
					'ReferenceID' =>
					array(
						'required' => false,
						'type' => 'TransactionReferenceType',
						'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
						'array' => false,
						'cardinality' => '0..1'
					),
					'FeeOrCreditAmount' =>
					array(
						'required' => false,
						'type' => 'AmountType',
						'nsURI' => 'urn:ebay:apis:eBLBaseComponents',
						'array' => false,
						'cardinality' => '0..1'
					)
				));
	}
}
?>
