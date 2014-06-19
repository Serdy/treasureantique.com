<?php

/**
 * This abstract class defines the basic cache operations
 */

abstract class Ai1ec_Cache_Strategy {

	/**
	 * @var Logger Instance of logger object associated with current instance
	 */
	protected $_logger = NULL;

	/**
	 * Accepts logger provided via argument
	 *
	 * @param Logger $logger Instance of logger object
	 *
	 * @return Logger Instance of logger configured past this point
	 */
	public function inject_logger( Logger $logger = NULL ) {
		if ( NULL !== $logger || NULL === $this->_logger ) {
			if ( $logger ) {
				$this->_logger = $logger;
			} else {
				$this->_logger = Logger::getLogger( 'cache' );
			}
		}
		return $this->_logger;
	}

	/**
	 * Retrieves the data store for the passed key
	 *
	 * @param string $key
	 * @throws Ai1ec_Cache_Not_Set_Exception if the key was not set
	 */
	abstract public function get_data( $key );

	/**
	 * Write the data to the persistence Layer
	 *
	 * @throws Ai1ec_Cache_Write_Exception
	 * @param string $key
	 * @param string $value
	 */
	abstract public function write_data( $key, $value );

	/**
	 * Deletes the data associated with the key from the persistence layer.
	 *
	 * @param string $key
	 */
	abstract public function delete_data( $key );

}
