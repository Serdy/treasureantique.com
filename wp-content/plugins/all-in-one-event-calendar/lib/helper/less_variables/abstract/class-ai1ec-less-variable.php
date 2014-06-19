<?php

/**
 * @author Timely Network Inc
 */

abstract class Ai1ec_Less_Variable extends Ai1ec_Html_Element {

	/**
	 * @var string
	 */
	protected $id;

	/**
	 * @var string
	 */
	protected $description;

	/**
	 * @var string
	 */
	protected $value;

	/**
	 * @var Ai1ec_Renderable
	 */
	protected $renderable;

	/**
	 * it takes an array of parameters and a renderable. 
	 * 
	 * @param array $params
	 * @param Ai1ec_Renderable $renderable
	 */
	public function __construct( array $params ) {
		parent::__construct();
		$this->id          = $params['id'];
		$this->description = $params['description'];
		$this->value       = $params['value'];

	}

	/**
	 * Render the opening part of the control group html
	 * 
	 * @return string
	 */
	protected function render_opening_of_control_group() {
		$label = $this->description;
		$id = $this->template_adapter->escape_attribute( $this->id );
		$html = <<<HTML
<div class="control-group">
	<label class="control-label" for="$id">$label</label>
	<div class="controls">
HTML;
		return $html;
	}
	
	/**
	 * Render the closing part of the control group html
	 * 
	 * @return string
	 */
	protected function render_closing_of_control_group() {
		return '</div></div>';
	}
}
