<?php
// autogenerated file 30.09.2013 15:20
// $Id: $
// $Log: $
//
require_once 'EbatNs_FacetType.php';

/**
 * Simple type defining all possible pickup methods for the In-Store Pickup 
 * feature. A <strong>PickupMethodCodeType</strong> value is always returned under 
 * the <strong>PickupOptions</strong> and <strong>PickupMethodSelected</strong> 
 * containers.<br/><br/><span class="tablenote"><strong>Note:</strong> At this 
 * time, 'InStorePickup' is the only available pickup method; however, additional 
 * pickup methods may be added to list in future releases. </span><br/><br/><span 
 * class="tablenote"><strong>Note:</strong> At this time, the In-Store Pickup 
 * feature is generally only available to large retail merchants, and can only be 
 * applied to multi-quantity, fixed-price listings. Sellers who are eligible for 
 * the In-Store Pickup feature can start listing items in Production with the 
 * In-Store Pickup option beginning in late September 2013. </span> 
 *
 * @link http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/types/PickupMethodCodeType.html
 *
 * @property string InStorePickup
 * @property string CustomCode
 */
class PickupMethodCodeType extends EbatNs_FacetType
{
	const CodeType_InStorePickup = 'InStorePickup';
	const CodeType_CustomCode = 'CustomCode';

	/**
	 * @return 
	 */
	function __construct()
	{
		parent::__construct('PickupMethodCodeType', 'urn:ebay:apis:eBLBaseComponents');

	}
}

$Facet_PickupMethodCodeType = new PickupMethodCodeType();

?>
