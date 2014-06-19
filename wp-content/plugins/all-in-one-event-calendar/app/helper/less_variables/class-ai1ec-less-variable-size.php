<?php

/**
 *
 * @author Timely Network Inc
 *        
 *        
 */

class Ai1ec_Less_Variable_Size extends Ai1ec_Less_Variable {
	
	public function __construct( array $params ) {
		parent::__construct( $params );
	}
	
	/**
	 *
	 * @see Ai1ec_Renderable::render()
	 *
	 */
	public function render() {
		$input = Ai1ec_Helper_Factory::create_input_instance();
		$input->set_name( $this->id );
		$input->set_id( $this->id );
		$input->add_class( 'ai1ec-less-variable-size' );
		$input->set_value( $this->value );
		$input->set_attribute(
			'placeholder',
			__( "Enter size in em, px or %", AI1EC_PLUGIN_NAME )
		);
		echo $this->render_opening_of_control_group();
		$input->render();
		echo $this->render_closing_of_control_group();
	}
}