<?php
// autogenerated file 30.09.2013 15:20
// $Id: $
// $Log: $
//
require_once 'EbatNs_FacetType.php';

/**
 *  
 *
 * @link http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/types/TransactionReferenceCodeType.html
 *
 * @property string ExternalTransactionID
 * @property string CustomCode
 */
class TransactionReferenceCodeType extends EbatNs_FacetType
{
	const CodeType_ExternalTransactionID = 'ExternalTransactionID';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 */
	function __construct()
	{
		parent::__construct('TransactionReferenceCodeType', 'urn:ebay:apis:eBLBaseComponents');

	}
}

$Facet_TransactionReferenceCodeType = new TransactionReferenceCodeType();

?>
