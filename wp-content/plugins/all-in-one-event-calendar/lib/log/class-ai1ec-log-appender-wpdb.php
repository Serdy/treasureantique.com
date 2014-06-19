<?php

/**
 * Appender using `wpdb` for writing log entries
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012.12.07
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Logging.Appender
 */
class Ai1ec_Log_Appender_Wpdb extends LoggerAppender
{

	/**
	 * @constant string Current database version identifier
	 */
	const DB_VERSION = 'a1';

	/**
	 * @var wpdb Instance of `wpdb` used for queries
	 */
	protected $_db   = NULL;

	/**
	 * @var array List of patterns to convert for insertions (in table order)
	 */
	protected $_pattern = array(
		'%server{REMOTE_ADDR}',
		'%date{U}',
		'%server{REQUEST_METHOD}',
		'%server{HTTP_HOST}',
		'%server{REQUEST_URI}',
		'%logger',
		'%level',
		'%message',
		'%pid',
		'%file',
		'%line',
	);

	/**
	 * @param array List of converter object to flatten logging event
	 */
	protected $_converters = array();

	/**
	 * activateOptions method
	 *
	 * Inherited method triggered after object construction, which shall set
	 * status of appender openness ($this->closed)
	 *
	 * @return bool True if appender is open
	 */
	public function activateOptions() {
		global $wpdb;
		$this->_db    = $wpdb;
		$this->closed = false;
		if ( ! $this->_create_table() ) {
			$this->closed = true;
		} else {
			$converter_map = LoggerLayoutPattern::getDefaultConverterMap();
			foreach ( $this->_pattern as $pattern ) {
				$parser = new LoggerPatternParser( $pattern, $converter_map );
				$this->_converters[] = $parser->parse();
			}
		}
		return ! $this->closed;
	}

	/**
	 * prune method
	 *
	 * Delete entries previously extracted via {@see self::get_commited()}
	 *
	 * @param array $entries List of entries (records) to delete
	 *
	 * @return bool Success
	 */
	public function prune( array $entries ) {
		$id_list = array();
		foreach ( $entries as $row ) {
			$id = (int)$row['id'];
			$id_list[$id] = $id;
		}
		if ( empty( $id_list ) ) {
			return false;
		}
		$sql_query = 'DELETE FROM ' . $this->_table() .
			' WHERE id IN (' . implode( ',', $id_list ) . ')';
		$this->_db->query( $sql_query );
		return true;
	}

	/**
	 * get_commited method
	 *
	 * Get a list of log entries commited to database.
	 * If there are more entries, than defined threshold, intermediate entries
	 * will be silently discarded. If you are using sharded MySQL installation
	 * (with more than one instance) - this calculation will be invalid, as it
	 * relies on consecutive IDs being generated.
	 *
	 * @return array List of log entries in database as associative array each
	 */
	public function get_commited() {
		$threshold = 1000;
		$use_table = $this->_table();
		$sql_query = '
			SELECT MIN(id) AS minid, MAX(id) AS maxid
			FROM ' . $use_table;
		$range = $this->_db->get_row( $sql_query );
		if (
			! empty( $range->maxid ) &&
			( $range->maxid - $range->minid ) > $threshold
		) {
			$sql_query = 'DELETE FROM ' . $use_table . ' WHERE id < %d';
			$sql_query = $this->_db->prepare(
				$sql_query,
				( $range->maxid - $threshold )
			);
			$this->_db->query( $sql_query );
		}

		$sql_query = '
			SELECT
				id,
				remote_addr,
				the_time,
				http_method,
				srv_host,
				request_uri,
				logger,
				err_level,
				message,
				thread,
				last_file,
				last_line
			FROM ' . $use_table . '
			ORDER BY id ASC
		';
		return $this->_db->get_results( $sql_query, ARRAY_A );
	}

	/**
	 * append method
	 *
	 * Method to write logging event into database.
	 * Serializes event object and formats SQL query appropriately.
	 *
	 * @param LoggerLoggingEvent $event Instance of logging event to log
	 *
	 * @return bool Success
	 */
	protected function append( LoggerLoggingEvent $event ) {
		$sql_query = 'INSERT INTO ' . $this->_table() . ' SET ' .
			'remote_addr = INET_ATON( %s ), ' .
			'the_time    = %d, ' .
			'http_method = %s, ' .
			'srv_host    = %s, ' .
			'request_uri = %s, ' .
			'logger      = %s, ' .
			'err_level   = %s, ' .
			'message     = %s, ' .
			'thread      = %d, ' .
			'last_file   = %s, ' .
			'last_line   = %d';
		$arguments = $this->_format( $event );
		$sql_query = $this->_db->prepare( $sql_query, $arguments );
		return $this->_db->query( $sql_query );
	}

	/**
	 * _format method
	 *
	 * Method is solely responsible for scalarazing event into a list of
	 * DB column entries, to be used in query.
	 * No escaping is applied here.
	 *
	 * @param LoggerLoggingEvent $event Instance of logging event to log
	 *
	 * @return array List of field entities to use in formatting SQL query
	 */
	protected function _format( LoggerLoggingEvent $event ) {
		$args = array();
		foreach ( $this->_converters as $converter ) {
			$buffer = '';
			while ( NULL !== $converter ) {
				$converter->format( $buffer, $event );
				$converter = $converter->next;
			}
			$args[] = $buffer;
		}
		return $args;
	}

	/**
	 * _create_table method
	 *
	 * Method check current DB version, and updates table, if necessary.
	 *
	 * @return bool Success / validity
	 */
	protected function _create_table() {
		$success = true;
		$option  = 'ai1ec_log_db_ver';
		if ( Ai1ec_Meta::get_option( $option ) != self::DB_VERSION ) {
			$sql_query = '
				CREATE TABLE ' . $this->_table() . ' (
					id          BIGINT(20) UNSIGNED NOT NULL AUTO_INCREMENT,
					remote_addr INT(10) UNSIGNED NOT NULL,
					the_time    INT(10) UNSIGNED NOT NULL,
					http_method VARCHAR(10) NOT NULL,
					srv_host    VARCHAR(200) NOT NULL,
					request_uri VARCHAR(255) NOT NULL,
					logger      VARCHAR(255) NOT NULL,
					err_level   VARCHAR(32) NOT NULL,
					message     VARCHAR(4000) NOT NULL,
					thread      INT(10) UNSIGNED NOT NULL,
					last_file   VARCHAR(255) NOT NULL,
					last_line   SMALLINT(5) UNSIGNED NOT NULL,
					PRIMARY KEY (id)
				) ENGINE=InnoDB CHARACTER SET ascii COLLATE ascii_general_ci;
			';
			if ( Ai1ec_Database::instance()->apply_delta( $sql_query ) ) {
				update_option( $option, self::DB_VERSION );
			} else {
				trigger_error(
					'Failed to upgrade/install Logger database',
					E_USER_WARNING
				);
				$success = false;
			}
		}
		return $success;
	}

	/**
	 * _table method
	 *
	 * Get name of table used by this appender
	 *
	 * @return string Fully qualified table name
	 */
	protected function _table() {
		return $this->_db->prefix . 'ai1ec_logging';
	}

}
