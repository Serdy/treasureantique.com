<?php

/**
 * Shortcut interface to logging layer
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012.12.06
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Logging.Interface
 */
class Ai1ec_Log
{

	/**
	 * Trigger fatal error message
	 *
	 * @param string|object $object  Originator of the message
	 * @param string|object $message Message being directed to log
	 *
	 * @return bool Success of message delegation
	 */
	static public function fatal( $object, $message ) {
		return self::log( __FUNCTION__, $object, $message );
	}

	/**
	 * Trigger error message
	 *
	 * @param string|object $object  Originator of the message
	 * @param string|object $message Message being directed to log
	 *
	 * @return bool Success of message delegation
	 */
	static public function error( $object, $message ) {
		return self::log( __FUNCTION__, $object, $message );
	}

	/**
	 * Trigger warn(ing) message
	 *
	 * @param string|object $object  Originator of the message
	 * @param string|object $message Message being directed to log
	 *
	 * @return bool Success of message delegation
	 */
	static public function warn( $object, $message ) {
		return self::log( __FUNCTION__, $object, $message );
	}

	/**
	 * Trigger info level message
	 *
	 * @param string|object $object  Originator of the message
	 * @param string|object $message Message being directed to log
	 *
	 * @return bool Success of message delegation
	 */
	static public function info( $object, $message ) {
		return self::log( __FUNCTION__, $object, $message );
	}

	/**
	 * Trigger debug level message
	 *
	 * @param string|object $object  Originator of the message
	 * @param string|object $message Message being directed to log
	 *
	 * @return bool Success of message delegation
	 */
	static public function debug( $object, $message ) {
		return self::log( __FUNCTION__, $object, $message );
	}

	/**
	 * Trigger trace message
	 *
	 * @param string|object $object  Originator of the message
	 * @param string|object $message Message being directed to log
	 *
	 * @return bool Success of message delegation
	 */
	static public function trace( $object, $message ) {
		return self::log( __FUNCTION__, $object, $message );
	}

	/**
	 * Generic messages delegation interface
	 *
	 * Checks given severity level against a map of allowed values and cast
	 * object to class name, if required
	 *
	 * @staticvar array $sev_map Map of allowed severities (all map to true)
	 *
	 * @param string        $severity Message severity
	 * @param string|object $name     Originator of the message
	 * @param string        $message  Message being directed to log
	 *
	 * @return bool Success of message delegation
	 */
	static public function log( $severity, $name, $message ) {
		static $sev_map = array(
			'fatal' => true,
			'error' => true,
			'warn'  => true,
			'info'  => true,
			'debug' => true,
			'trace' => true,
		);
		$success = true;
		if ( ! isset( $sev_map[$severity] ) ) {
			$severity = 'warn';
			$success = false;
		}
		if ( is_object( $name ) ) {
			$name = get_class( $name );
		}
		$name = (string)$name;
		Logger::getLogger( $name )->$severity( $message );
		return $success;
	}

}
