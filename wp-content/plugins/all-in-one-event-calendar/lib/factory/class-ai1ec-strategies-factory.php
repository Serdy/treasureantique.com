<?php

/** 
 * @author Timely Network Inc
 * 
 * 
 */

class Ai1ec_Strategies_Factory {

	/**
	 * @var array Map of cache directories and writability
	 */
	private static $cache_directories = array();

	/**
	 * create_cache_startegy_instance method
	 *
	 * Method to instantiate new cache strategy object
	 *
	 * @param string $cache_directory Cache directory to use
	 * @param bool   $skip_small_bits Set to true, to ignore small entities
	 *                                cache engines, as APC [optional=false]
	 *
	 * @return Ai1ec_Cache_Strategy Instantiated writer
	 */
	public static function create_cache_startegy_instance(
		$cache_directory = NULL,
		$skip_small_bits = false
	) {
		if ( true !== $skip_small_bits && Ai1ec_Apc_Cache::is_available() ) {
			return new Ai1ec_Apc_Cache();
		} else if (
			NULL !== $cache_directory &&
			self::_is_cache_dir_writable( $cache_directory )
		) {
			return new Ai1ec_File_Cache( $cache_directory );
		} else if ( true !== $skip_small_bits ) {
			return new Ai1ec_Db_Cache(
				Ai1ec_Adapters_Factory::create_db_adapter_instance()
			);
		} else {
			return new Ai1ec_Void_Cache();
		}
	}

	/**
	 * @param string $key_for_persistance
	 * @param Ai1ec_Cache_Strategy $cache_strategy
	 * @param string $cache_directory
	 * @return Ai1ec_Persistence_Context
	 */
	public static function create_persistence_context( 
		$key_for_persistance,
		$cache_directory = null
	) {
		return new Ai1ec_Persistence_Context( 
			$key_for_persistance, 
			self::create_cache_startegy_instance( $cache_directory )
		);
	}

	/**
	 * create_blob_persistence_context method
	 *
	 * Create new Ai1ec_Persistence_Context instance suited for BLOB, or
	 * literary any large objects storage
	 *
	 * @param string $key_for_persistance Storage key to be used
	 * @param string $cache_directory     Path to base storage directory
	 *
	 * @return Ai1ec_Persistence_Context Cache storage instance
	 */
	public static function create_blob_persistence_context(
		$key_for_persistance,
		$cache_directory
	) {
		return new Ai1ec_Persistence_Context(
			$key_for_persistance,
			self::create_cache_startegy_instance( $cache_directory, true )
		);
	}

	/**
	 * _is_cache_dir_writable method
	 *
	 * Check if given cache directory is writable.
	 *
	 * @param string $directory A path to check for writability
	 *
	 * @return bool Writability
	 */
	protected static function _is_cache_dir_writable( $directory ) {
		if ( ! isset( self::$cache_directories[$directory] ) ) {
			self::$cache_directories[$directory] =
				Ai1ec_Filesystem_Utility::is_writable(
					$directory
				);
		}
		return self::$cache_directories[$directory];
	}

}
