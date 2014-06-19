<?php

/** 
 * @author Timely Network Inc
 * 
 * 
 */

class Ai1ec_Bootstrap_Button extends Ai1ec_Generic_Html_Tag {
	
	/**
	 * @var string
	 */
	private $button_type = 'primary';
	
	/**
	 * @param string $button_type
	 */
	public function set_button_type( $button_type ) {
		$this->button_type = $button_type;
	}

	public function __construct() {
		parent::__construct( 'a' );
		$this->add_class( 'btn' );
	}
	
	/* (non-PHPdoc)
	 * @see Ai1ec_Generic_Html_Tag::render()
	 */
	public function render() {
		if( ! empty( $this->button_type ) ) {
			$this->add_class( "btn-{$this->button_type}" );
		}
		parent::render();
	}
}