<?php
// autogenerated file 10.09.2012 12:58
// $Id: $
// $Log: $
//
require_once 'EbatNs_FacetType.php';

/**
 * This enumeration type consist of the applicable values that may be used in the 
 * <b>RestockingFeeValueOption</b> field of Add/Revise/Relist API calls. 
 *
 * @link http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/types/RestockingFeeCodeType.html
 *
 * @property string NoRestockingFee
 * @property string Percent_10
 * @property string Percent_15
 * @property string Percent_20
 * @property string Percent_25
 * @property string CustomCode
 */
class RestockingFeeCodeType extends EbatNs_FacetType
{
	const CodeType_NoRestockingFee = 'NoRestockingFee';
	const CodeType_Percent_10 = 'Percent_10';
	const CodeType_Percent_15 = 'Percent_15';
	const CodeType_Percent_20 = 'Percent_20';
	const CodeType_Percent_25 = 'Percent_25';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 */
	function __construct()
	{
		parent::__construct('RestockingFeeCodeType', 'urn:ebay:apis:eBLBaseComponents');

	}
}

$Facet_RestockingFeeCodeType = new RestockingFeeCodeType();

?>
