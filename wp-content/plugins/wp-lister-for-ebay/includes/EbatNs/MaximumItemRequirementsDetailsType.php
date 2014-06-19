<?php
// autogenerated file 10.09.2012 12:58
// $Id: $
// $Log: $
//
//
require_once 'EbatNs_ComplexType.php';

/**
 * [Selling] A means of limiting unpaying or low feedback bidders 
 *
 * @link http://developer.ebay.com/DevZone/XML/docs/Reference/eBay/types/MaximumItemRequirementsDetailsType.html
 *
 */
class MaximumItemRequirementsDetailsType extends EbatNs_ComplexType
{
	/**
	 * @var int
	 */
	protected $MaximumItemCount;
	/**
	 * @var int
	 */
	protected $MinimumFeedbackScore;

	/**
	 * @return int
	 * @param integer $index 
	 */
	function getMaximumItemCount($index = null)
	{
		if ($index !== null) {
			return $this->MaximumItemCount[$index];
		} else {
			return $this->MaximumItemCount;
		}
	}
	/**
	 * @return void
	 * @param int $value 
	 * @param  $index 
	 */
	function setMaximumItemCount($value, $index = null)
	{
		if ($index !== null) {
			$this->MaximumItemCount[$index] = $value;
		} else {
			$this->MaximumItemCount = $value;
		}
	}
	/**
	 * @return void
	 * @param int $value 
	 */
	function addMaximumItemCount($value)
	{
		$this->MaximumItemCount[] = $value;
	}
	/**
	 * @return int
	 * @param integer $index 
	 */
	function getMinimumFeedbackScore($index = null)
	{
		if ($index !== null) {
			return $this->MinimumFeedbackScore[$index];
		} else {
			return $this->MinimumFeedbackScore;
		}
	}
	/**
	 * @return void
	 * @param int $value 
	 * @param  $index 
	 */
	function setMinimumFeedbackScore($value, $index = null)
	{
		if ($index !== null) {
			$this->MinimumFeedbackScore[$index] = $value;
		} else {
			$this->MinimumFeedbackScore = $value;
		}
	}
	/**
	 * @return void
	 * @param int $value 
	 */
	function addMinimumFeedbackScore($value)
	{
		$this->MinimumFeedbackScore[] = $value;
	}
	/**
	 * @return 
	 */
	function __construct()
	{
		parent::__construct('MaximumItemRequirementsDetailsType', 'urn:ebay:apis:eBLBaseComponents');
		if (!isset(self::$_elements[__CLASS__]))
				self::$_elements[__CLASS__] = array_merge(self::$_elements[get_parent_class()],
				array(
					'MaximumItemCount' =>
					array(
						'required' => false,
						'type' => 'int',
						'nsURI' => 'http://www.w3.org/2001/XMLSchema',
						'array' => true,
						'cardinality' => '0..*'
					),
					'MinimumFeedbackScore' =>
					array(
						'required' => false,
						'type' => 'int',
						'nsURI' => 'http://www.w3.org/2001/XMLSchema',
						'array' => true,
						'cardinality' => '0..*'
					)
				));
	}
}
?>
