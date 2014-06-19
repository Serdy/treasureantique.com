<?php

/**
 * Shortcut class to render APC configuration/status line
 *
 * @author     Justas Butkus <justas@butkus.lt>
 * @since      2012.12.06
 *
 * @package    AllInOneCalendar
 * @subpackage AllInOneCalendar.Logging.Renderer
 */
class Ai1ec_Log_Render_Apc implements LoggerRenderer
{

	/**
	 * render method
	 *
	 * Generate output of APC memory statistics, if accessible
	 *
	 * @param Ai1ec_Apc_Cache $instance Allegedly used Apc_Cache instance
	 *
	 * @return string Output to include in log, with APC statistics
	 */
	public function render( $instance ) {
		$output = '';
		if ( is_callable( 'ini_get' ) ) {
			$output .= 'SHM:' . ini_get( 'apc.shm_size' );
		}
		if ( function_exists( 'apc_sma_info' ) ) {
			$sma_info = apc_sma_info();
			$output  .= ( $sma_info['seg_size'] - $sma_info['avail_mem'] ) .
				'b / ' . $sma_info['seg_size'] . 'b over ' .
				$sma_info['num_seg'] . ' segments';
			unset( $sma_info );
		}
		return $output;
	}

}
