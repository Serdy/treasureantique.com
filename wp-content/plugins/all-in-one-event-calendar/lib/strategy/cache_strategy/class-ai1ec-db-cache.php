<?php

/**
 *
 * @author Timely Network Inc
 *
 * Concrete implementation for db cache.
 */

class Ai1ec_Db_Cache extends Ai1ec_Cache_Strategy {

	/**
	 * @var Ai1ec_Db_Adapter Instance of database adapter
	 */
	private $db_adapter;

	public function __construct( Ai1ec_Db_Adapter $db_adapter ) {
		$this->db_adapter = $db_adapter;
	}

	/**
	 *
	 * @see Ai1ec_Get_Data_From_Cache::get_data()
	 *
	 */
	public function get_data( $key ) {
		$key  = $this->_key( $key );
		$data = $this->db_adapter->get_data_from_config( $key );
		if ( false === $data ) {
			throw new Ai1ec_Cache_Not_Set_Exception(
				'No data under \'' . $key . '\' present'
			);
		}
		return maybe_unserialize( $data );
	}

	/**
	 *
	 * @see Ai1ec_Write_Data_To_Cache::write_data()
	 *
	 */
	public function write_data( $key, $value ) {
		$key    = $this->_key( $key );
		$result = $this->db_adapter->write_data_to_config(
			$key,
			maybe_serialize( $value )
		);
		if ( false === $result ) {
			$this->inject_logger()->warn( 'Failed to save ' . $key );
			throw new Ai1ec_Cache_Write_Exception(
				'An error occured while saving data to ' . $key
			);
		}
	}

	/**
	 * (non-PHPdoc)
	 * @see Ai1ec_Write_Data_To_Cache::delete_data()
	 */
	public function delete_data( $key ) {
		return $this->db_adapter->delete_data_from_config(
			$this->_key( $key )
		);
	}

	/**
	 *
	 * @see Ai1ec_Write_Data_To_Cache::delete_matching()
	 */
	public function delete_matching( $pattern ) {
		global $wpdb;
		$sql_query = $wpdb->prepare(
			'SELECT option_name FROM ' . $wpdb->options .
			' WHERE option_name LIKE %s',
			'%%' . $pattern . '%%'
		);
		$keys = $wpdb->get_col( $sql_query );
		foreach ( $keys as $key ) {
			$this->db_adapter->delete_data_from_config( $key );
		}
		return count( $keys );
	}

	/**
	 * _key method
	 *
	 * Get safe key name to use within options API
	 *
	 * @param string $key Key to sanitize
	 *
	 * @return string Safe to use key
	 */
	protected function _key( $key ) {
		if ( strlen( $key ) > 53 ) {
			$hash = md5( $key );
			$key  = substr( $key, 0, 16 ) . '_' . $hash;
		}
		return $key;
	}

}
