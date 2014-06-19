<?php

return array(
	'rootLogger' => array(
		'appenders' => array( 'default' ),
	),
	'renderers' => array(
		array(
			'renderedClass'  => 'Ai1ec_Apc_Cache',
			'renderingClass' => 'Ai1ec_Log_Render_Apc',
		),
	),
	'loggers'    => array(
		'cache' => array(
			'appenders' => array( 'default' ),
		),
	),
	'appenders'  => array(
		'default' => array(
			'class' => 'Ai1ec_Log_Appender_Wpdb',
		),
	),
);
