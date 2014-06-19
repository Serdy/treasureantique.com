<?php
header( 'Content-type: text/javascript' );

if( ! defined( 'WP_PLUGIN_DIR' ) )
{
	// Make sure that the WordPress bootstrap has run before continuing.
	require_once( dirname( dirname( dirname( dirname( __FILE__ ) ) ) ) . '/wp-load.php');
}
$counterize_mce_form  = '';
?>/*
 * Name: Counterize plugin for TinyMCE
 * Description: Adds a button in the Visual editor to insert Counterize shortcodes
 * Author: Gabriel Hautclocq
 * Homepage: http://www.gabsoftware.com/
 */
(
	function()
	{
		tinymce.create
		(
			'tinymce.plugins.counterize',
			{
				"init" : function( ed, url )
				{
					// Register the command so that it can be invoked by using tinyMCE.activeEditor.execCommand('mceCounterize');
					ed.addCommand
					(
						"mceCounterize",
						function()
						{
							var width = jQuery(window).width(), H = jQuery(window).height(), W = ( 720 < width ) ? 720 : width;
							W = W - 80;
							H = H - 84;
							tb_show( "Counterize Shortcode", "#TB_inline?width=" + W + "&height=" + H + "&inlineId=counterize-form" );
						}
					);

					ed.addButton
					(
						"counterize",
						{
							"title" : "Add a Counterize shortcode",
							"image" : url + "/bar_chart_20x20.png",
							"cmd"   : "mceCounterize"
						}
					);
				},
				"createControl" : function( n, cm )
				{
					return null;
				},
				"getInfo" : function()
				{
					return {
						"longname"  : "Counterize TinyMCE plugin",
						"author"    : "Gabriel Hautclocq",
						"authorurl" : "http://www.gabsoftware.com/",
						"infourl"   : "http://www.gabsoftware.com/products/scripts/counterize",
						"version"   : "1.0.0"
					};
				}
			}
		);
		tinymce.PluginManager.add( "counterize", tinymce.plugins.counterize );

		// executes this when the DOM is ready
		jQuery
		(
			function()
			{
				// creates a form to be displayed everytime the button is clicked
				// you should achieve this using AJAX instead of direct html code like this
				var form = jQuery
				(
					"<?php $counterize_mce_form = apply_filters( 'counterize_mce_js_before_form_filter', $counterize_mce_form ) . <<< ENDFORM
<div id=\"counterize-form\">\
						<table id=\"counterize-table\" class=\"form-table\">\

ENDFORM;
							$counterize_mce_form = apply_filters( 'counterize_mce_js_before_fields_filter', $counterize_mce_form );
							$counterize_mce_form .= <<< ENDFORM
							<tr>\
								<th><label for=\"counterize-type\">Type</label></th>\
								<td>\
									<select id=\"counterize-type\" name=\"type\">\
										<option value=\"copyright\" selected=\"selected\">Copyright</option>\

ENDFORM;
										$counterize_mce_form = apply_filters( 'counterize_mce_js_type_filter', $counterize_mce_form );
										$counterize_mce_form .= <<< ENDFORM
										<option value=\"all\">All stats</option>\
									</select><br />\
									<small>Choose what you want to display.</small>\
								</td>\
							</tr>\
							<tr>\
								<th><label for=\"counterize-items\">Number of items</label></th>\
								<td>\
									<input type=\"number\" min=\"1\" name=\"items\" id=\"counterize-items\" value=\"10\" /><br />\
									<small>Specify how many items you want to display in the stats.</small>\
								</td>\
							</tr>\
							<tr>\
								<th><label for=\"counterize-subitems\">Number of sub-items</label></th>\
								<td>\
									<input type=\"number\" min=\"1\" name=\"subitems\" id=\"counterize-subitems\" value=\"15\" /><br />\
									<small>Specify how many sub-items you want to display in the collapsible stats.</small>\
								</td>\
							</tr>\
							<tr>\
								<th><label for=\"counterize-version\">Show versions</label></th>\
								<td>\
									<input type=\"checkbox\" name=\"version\" id=\"counterize-version\" value=\"yes\" checked=\"checked\" /><br />\
									<small>Check to display OS and browsers versions</small>\
								</td>\
							</tr>\
							<tr>\
								<th><label for=\"counterize-collapsible\">Collapsible stats</label></th>\
								<td>\
									<input type=\"checkbox\" name=\"collapsible\" id=\"counterize-collapsible\" value=\"yes\" /><br />\
									<small>Each item can be expanded to show subitems</small>\
								</td>\
							</tr>\
							<tr>\
								<th><label for=\"counterize-print_header\">Print header</label></th>\
								<td>\
									<input type=\"checkbox\" name=\"print_header\" id=\"counterize-print_header\" value=\"yes\" checked=\"checked\" /><br />\
									<small>Check if you want to show a header before the stats</small>\
								</td>\
							</tr>\
							<tr>\
								<th><label for=\"counterize-header\">Header content</label></th>\
								<td>\
									<input type=\"text\" name=\"header\" id=\"counterize-header\" value=\"\" /><br />\
									<small>Set to non-empty string to override the default header</small>\
								</td>\
							</tr>\
							<tr>\
								<th><label for=\"counterize-period\">Period</label></th>\
								<td>\
									<input type=\"radio\" name=\"period\" id=\"counterize-period-nothing\"       value=\"\" checked=\"checked\" /><label for=\"counterize-period-nothing\">None</label><br />\
									<input type=\"radio\" name=\"period\" id=\"counterize-period-24h\"           value=\"24h\" /><label for=\"counterize-period-24h\">24 Hours</label><br />\

ENDFORM;
									$counterize_mce_form = apply_filters( 'counterize_mce_js_period_filter', $counterize_mce_form );
									$counterize_mce_form .= <<< ENDFORM
									<small>Choose a period (4 last are for Traffic only)</small>\
								</td>\
							</tr>\
							<tr>\
								<th><label for=\"counterize-tn_width\">Post thumbnail width</label></th>\
								<td>\
									<input type=\"number\" min=\"1\" name=\"tn_width\" id=\"counterize-tn_width\" value=\"50\" /><br />\
									<small>Width of the post thumbnail.</small>\
								</td>\
							</tr>\
							<tr>\
								<th><label for=\"counterize-tn_height\">Post thumbnail height</label></th>\
								<td>\
									<input type=\"number\" min=\"1\" name=\"tn_height\" id=\"counterize-tn_height\" value=\"50\" /><br />\
									<small>Height of the post thumbnail.</small>\
								</td>\
							</tr>\

ENDFORM;
							$counterize_mce_form = apply_filters( 'counterize_mce_js_after_fields_filter', $counterize_mce_form );
							$counterize_mce_form .= <<< ENDFORM
						</table>\
						<p class=\"submit\">\
							<input type=\"button\" id=\"counterize-submit\" class=\"button-primary\" value=\"Insert Shortcode\" name=\"submit\" />\
						</p>\
					</div>
ENDFORM;
					echo apply_filters( 'counterize_mce_js_after_form_filter', $counterize_mce_form );
					?>"
				);

				var table = form.find( "table" );
				form.appendTo( "body" ).hide();

				// handles the click event of the submit button
				form.find( "#counterize-submit" ).click
				(
					function()
					{
						// defines the options and their default values
						// again, this is not the most elegant way to do this
						// but well, this gets the job done nonetheless
						var options =
						{
<?php
							$options = Array
							(
								'type'         => 'copyright',
								'items'        => '10',
								'subitems'     => '15',
								'version'      => 'yes',
								'collapsible'  => 'no',
								'print_header' => 'yes',
								'header'       => '',
								'period'       => '',
								'tn_width'     => '50',
								'tn_height'    => '50'
							);
							$options = apply_filters( 'counterize_mce_js_options_filter', $options );
							$last_key = end( array_keys( $options ) );
							foreach( $options as $key => $value )
							{
								$comma = ',';
								if( $key === $last_key )
								{
									$comma = '';
								}
								echo <<< END
								"{$key}" : "{$value}"{$comma}

END;
							}
							?>

						};
						var shortcode = "[counterize";

						for( var index in options )
						{
							if( jQuery.inArray( index, [<?php
							$indexes = Array
							(
								'period'
							);
							$indexes = apply_filters( 'counterize_mce_js_radiobutton_filter', $indexes );
							$last_key = end( array_keys( $indexes ) );
							foreach( $indexes as $key => $value )
							{
								$comma = ', ';
								if( $key === $last_key )
								{
									$comma = '';
								}
								echo "\"{$value}\"{$comma}";
							}
							?>] ) !== -1 )
							{
								//radio button case
								var value = table.find( "input[name='" + index + "']:checked" ).val();
							}
							else if( jQuery.inArray( index, [<?php
							$indexes = Array
							(
								'version',
								'collapsible',
								'print_header'
							);
							$indexes = apply_filters( 'counterize_mce_js_checkbox_filter', $indexes );
							$last_key = end( array_keys( $indexes ) );
							foreach( $indexes as $key => $value )
							{
								$comma = ', ';
								if( $key === $last_key )
								{
									$comma = '';
								}
								echo "\"{$value}\"{$comma}";
							}
							?>] ) !== -1 )
							{
								//checkboxes case
								var value = ( table.find( "#counterize-" + index ).attr( 'checked' ) != undefined ? "yes" : "no" );
							}
							else
							{
								//other input fields case
								var value = table.find( "#counterize-" + index ).val();
							}

							// attaches the attribute to the shortcode only if it's different from the default value
							if ( value !== options[index] )
							{
								shortcode += " " + index + "=\"" + value + "\"";
							}
						}

						shortcode += "]";

						// inserts the shortcode into the active editor
						tinyMCE.activeEditor.execCommand( "mceInsertContent", 0, shortcode );

						// closes Thickbox
						tb_remove();
					}
				);
			}
		);
	}
)();
//alert( "If you see this message, the TinyMCE plugin is error-free. You can comment this line out." );