<?php
/*
 * Describe a Counterize feed
 */

 // security check
if( ! defined( 'WP_PLUGIN_DIR' ) )
{
	die( 'There is nothing to see here!' );
}

/*
 * Beginning of CounterizeFeed class
 */
class CounterizeFeed
{
	//String
	//The title of the feed
	public $title;

	//String
	//The caption for the feed data (used as a column header for example)
	public $caption;

	//Integer
	//How many items in the feed data
	public $count = 0;

	//Integer
	//Minimum value of the items
	public $min = 0;

	//Integer
	//Maximum value of the items
	public $max = 0;

	//Integer
	//Sum of the value of all the items
	public $total = 0;

	//String
	//The unit used for feed data (not printed if empty)
	public $unit;

	//Array
	//Array of CounterizeFeedItem
	public $data = array();



	/*
	 * Constructor
	 */
	public function __construct( $title, $caption, $unit = '' )
	{
		$this->title = $title;
		$this->caption = $caption;
		$this->unit = $unit;
	} // end of constructor

	//adds an item to the items array
	public function add_item( $thisitem )
	{
		$this->count++;
		$this->total += $thisitem->value;

		if( $thisitem->value < $this->min )
		{
			$this->min = $thisitem->value;
		}
		if( $thisitem->value > $this->max )
		{
			$this->max = $thisitem->value;
		}
		$this->data[] = $thisitem;
	}

	//adds an item to the items array (2 parameters)
	public function add_item_2( $value, $caption )
	{
		$this->add_item( new CounterizeFeedItem( $value, $caption ) );
	}

	//adds an item to the items array (3 parameters)
	public function add_item_3( $value, $caption, $url )
	{
		$this->add_item( new CounterizeFeedItem( $value, $caption, $url ) );
	}

	//adds an item to the items array (4 parameters)
	public function add_item_4( $value, $caption, $url, $img )
	{
		$this->add_item( new CounterizeFeedItem( $value, $caption, $url, $img ) );
	}

	//adds an item to the items array (5 parameters)
	public function add_item_5( $value, $caption, $url, $img, $feed )
	{
		$this->add_item( new CounterizeFeedItem( $value, $caption, $url, $img, $feed ) );
	}

	//refresh the percentage of all the data items
	public function refresh_percentages()
	{
		foreach( $this->data as $item )
		{
			$item->percent = ( $this->total != 0 ) ? ( $item->value / $this->total ) * 100 : 0;
		}
	}

	/*
	 * Renders a feed in vertical direction.
	 * Note: function counterize_renderstats_vertical() is now deprecated.
	 */
	public function render_feed_vertical( $nofollow = true, $tablewidth = "100%", $shorten = true, $htmlspecialchars_for_label = false, $is_subitem = false, $print_header = true )
	{
		/* check if the feed contains collapsible items */
		$has_collapsible_items = false;
		foreach( $this->data as $item )
		{
			if( isset( $item->feed ) && $item->feed->count > 0 )
			{
				$has_collapsible_items = true;
				break;
			}
		}

		$max_label = counterize_get_option( 'maxwidth' );
		$offset = ( $is_subitem ? 1 : 0 );
		//echo "print_header {" . $print_header ? "1" : "0" . "}";

		if( $print_header === true && $is_subitem === false ): ?>

		<h2><?php echo $this->title . ' &#x202A;(' . $this->count . ')&#x202C;'; ?></h2>

		<?php endif; ?>

		<table width="<?php echo $tablewidth; ?>" summary="<?php _e( 'Statistics', COUNTERIZE_TD ); ?>"<?php if( $is_subitem ) { echo ' class="counterize_subtable"' ;} ?>>

			<tr<?php if( ! $is_subitem ) { echo ' class="alternate"'; } ?>>
				<?php if( $has_collapsible_items || $is_subitem ): ?><td style="width: 2%"><?php if( $is_subitem ) { echo '<small><strong>#</strong></small>'; } else { echo '&nbsp;'; } ?></td><?php endif; ?>
				<td style="width: 25%"><small><strong><?php echo htmlspecialchars( $this->caption ); ?></strong></small></td>
				<td style="width: 10%"><small><strong><?php _e( 'Amount', COUNTERIZE_TD ); ?></strong></small></td>
				<td style="width: 60%"><small><strong><?php _e( 'Percentage', COUNTERIZE_TD ); ?></strong></small></td>
			</tr>

		<?php

		$counter = 0;

		foreach( $this->data as $item )
		{
			$percent = round( $item->percent, 2 );

			$group = ( $this->max > 0 ) ? round( $item->value * 100 / $this->max ) : 0;

			$has_subitems = ( isset( $item->feed ) && $item->feed->count > 0 );
			$uniqueid = uniqid();

			?>

			<tr<?php if( ( $counter + $offset ) % 2 ) { echo ' class="alternate"'; } ?>>

				<?php if( $has_collapsible_items || $is_subitem ): ?><td>

				<?php
				//if the items has a feed, display its feed as subitems
				if( $has_subitems ): ?>

					<span>
						<a id="counterize_parent_<?php echo "{$uniqueid}_{$counter}"; ?>" href="javascript:counterize_fold( 'counterize_parent_<?php echo "{$uniqueid}_{$counter}"; ?>', 'counterize_child_<?php echo "{$uniqueid}_{$counter}"; ?>' );">[&nbsp;+&nbsp;]</a>
					</span>
				<?php elseif( $is_subitem ): ?>
					<small><?php echo ( $counter + 1 ); ?></small>
				<?php else: ?>
					&nbsp;
				<?php endif; ?>

				</td><?php endif; ?>

				<td>
					<small><?php

						//generate the label
						if( strlen( $item->caption ) > $max_label && $shorten == true )
						{
							$label = substr( $item->caption, 0, $max_label ) . '...';
						}
						else
						{
							$label = $item->caption;
						}



						//display the associated picture if present
						if( isset( $item->img ) )
						{
							echo $item->img->render();
						}

						//add a link for the label if an URL is provided, or just display the label otherwise
						if( !empty( $item->url ) )
						{
							echo '<a href="' . htmlspecialchars( $item->url ) . '"';
							if( $nofollow )
							{
								echo ' rel="nofollow"';
							}
							echo ' target="_blank">' . ( $htmlspecialchars_for_label ? htmlspecialchars( $label ) : $label ) . '</a>';
						}
						else
						{
							echo ( $htmlspecialchars_for_label ? htmlspecialchars( $label ) : $label );
						}
						?></small>
				</td>

				<td>
					<small><?php echo htmlspecialchars( $item->value ); ?></small>
				</td>

				<td>
					<?php
						if( $group < 40 )
						{
							$color = 'red';
						}
						elseif( $group < 80 )
						{
							$color = 'yellow';
						}
						elseif( $percent > 99 )
						{
							$color = 'blue';
						}
						else
						{
							$color = 'green';
						}
					?>

					<span class="counterize_chart_bar counterize_chart_bar_horizontal <?php echo $color; ?>"
						  style="width:<?php echo ( $percent * 0.9 ); ?>%;"
						  alt="<?php echo htmlspecialchars
						  ( $this->caption . ' - ' . $item->caption . ' - ' . $item->value . ' - ' . $percent )
						  . ' %'; ?>">&nbsp;</span>
					<small><strong><?php echo htmlspecialchars( $percent ); ?>&nbsp;%</strong></small>
				</td>
			</tr>
			<?php
			//if the item has sub-items, we must render the item feed inside a new line
			if( $has_subitems ): ?>

			<tr id="counterize_child_<?php echo "{$uniqueid}_{$counter}"; ?>" class="collapsed">
				<td colspan="4">

					<?php $item->feed->render_feed_vertical( $nofollow, $tablewidth, $shorten, $htmlspecialchars_for_label, true ); ?>

				</td>
			</tr>
			<?php
			endif;

			$counter++;
		}
		?>

		</table>
		<?php
	}



	/*
	 * Renders a feed in horizontal direction.
	 * Note: function counterize_renderstats() is now deprecated.
	 */
	public function render_feed_horizontal( $max_height = 80, $max_width = "100%", $print_header = true, $print_percents = true )
	{
		$has_negative = $this->min < 0;

		if( $print_header === true ): ?>

		<h2><?php echo $this->title; ?></h2>

		<?php endif; ?>

		<table width="<?php echo $max_width; ?>" summary="<?php _e( 'Statistics', COUNTERIZE_TD ); ?>">

			<tr>

			<?php

			$i = 0;
			foreach( $this->data as $item )
			{
				$percent = round( $item->percent, 2 );

				$group = ( $this->max > 0 ) ? round( $item->value * 100 / $this->max ) : 0;

				if( $item->value != 0 && $this->max != 0 )
				{
					$height = abs( floor( $item->value * $max_height / $this->max ) );
				}
				else
				{
					$height = 0;
				}

				//$group = floor( $height / $max_height * 100 );

				?>

				<td style="width: 3%"<?php if( $i % 2 ) { echo ' class="alternate"'; } ?> align="center" valign="bottom">
					<?php if( ! $has_negative || ( $has_negative && $item->value >= 0 ) ): ?>
					<small>
						<?php echo ( $has_negative ? '+' : '' ) . htmlspecialchars( $item->value ) . ( ! empty( $this->unit ) ? htmlspecialchars( $this->unit ) : '' ); ?>
						<br />

						<?php
							if( $group < 40 )
							{
								$color = 'red';
							}
							elseif( $group < 80 )
							{
								$color = 'yellow';
							}
							elseif( $percent > 99 )
							{
								$color = 'blue';
							}
							else
							{
								$color = 'green';
							}
						?>

						<span class="counterize_chart_bar counterize_chart_bar_vertical <?php echo $color; ?>"
							  style="height: <?php echo $height ?>px;"
							  alt="<?php echo htmlspecialchars
							  ( $this->caption . ' - ' . $item->caption . ' - ' . $item->value . ' - ' . $percent )
							  . ' %'; ?>">&nbsp;</span>

						<?php if( $print_percents ): ?>
						<br />
						<?php echo $percent; ?>
						<?php endif; ?>
					</small>
					<?php endif; ?>
				</td>

				<?php

				$i++;
			}
			?>

			</tr>

			<?php if( $print_percents ): ?>
			<tr>
				<?php
				foreach( $this->data as $item )
				{
					?>

					<td style="width: 3%" align="center" valign="middle"><small>%</small></td>
					<?php
				}
				?>
			</tr>
			<?php endif; ?>

			<?php if( $has_negative ): ?>

			<tr style="border: dotted #999 1px;">

			<?php

			$i = 0;
			foreach( $this->data as $item )
			{
				$percent = round( $item->percent, 2 );

				$group = ( $this->min < 0 ) ? round( $item->value * 100 / $this->min ) * -1 : 0;

				$abs_max = max( abs( $this->min ), abs( $this->max ) );

				if( $item->value != 0 && $abs_max > 0 )
				{
					$height = abs( floor( $item->value * $max_height / $abs_max ) );
				}
				else
				{
					$height = 0;
				}

				//$group = floor( $height / $max_height * 100 );

				?>

				<td style="width: 3%"<?php if( $i % 2 ) { echo ' class="alternate"'; } ?> align="center" valign="top">
					<?php if( $item->value < 0 ): ?>
					<small>
						<?php if( $print_percents ): ?>
						<?php echo $percent; ?><br />
						<?php endif; ?>

						<?php
							if( $group < 40 )
							{
								$color = 'red';
							}
							elseif( $group < 80 )
							{
								$color = 'yellow';
							}
							elseif( $percent > 99 )
							{
								$color = 'blue';
							}
							else
							{
								$color = 'green';
							}
						?>

						<span class="counterize_chart_bar counterize_chart_bar_vertical counterize_chart_bar_vertical_inverted <?php echo $color; ?>"
							  style="height: <?php echo $height ?>px;"
							  alt="<?php echo htmlspecialchars
							  ( $this->caption . ' - ' . $item->caption . ' - ' . $item->value . ' - ' . $percent )
							  . ' %'; ?>">&nbsp;</span>

						<br />
						<?php echo htmlspecialchars( $item->value ) . ( ! empty( $this->unit ) ? htmlspecialchars( $this->unit ) : '' ); ?>
					</small>
					<?php endif; ?>
				</td>

				<?php

				$i++;
			}
			?>

			</tr>

			<?php endif; ?>

			<tr>

			<?php

			$i = 0;
			foreach( $this->data as $item )
			{
				?>

				<td style="width: 3%"<?php if( $i % 2 ) { echo ' class="alternate"'; } ?> align="center">
					<small>
						<strong><?php echo htmlspecialchars( $item->caption ); ?></strong>
					</small>
				</td>

				<?php
				$i++;
			}
			?>

			</tr>

		</table>

		<?php
	}



}
/*
 * End of CounterizeFeed class
 */




/*
 * Beginning of CounterizeFeedItem class
 */
class CounterizeFeedItem
{
	//Float
	//The value of the item
	public $value = 0;

	//Float
	//Percentage of the item
	public $percent = 0;

	//String
	//Caption of the item
	public $caption;

	//String
	//URL applied to the caption
	public $url;

	//CounterizeFeedImg
	//Represent an IMG tag
	public $img;


	/*
	 * For sub-items only
	 */
	//CounterizeFeed
	//An optional CounterizeFeed object
	public $feed;



	//Default constructor
	public function __construct()
	{
		$arguments = func_get_args();
		$nbargs = count( $arguments );
		switch( $nbargs )
		{
			case 0: break; //0 arguments
			case 1: $this->constructor_1( $arguments[0] ); break; //1 argument
			case 2: $this->constructor_2( $arguments[0], $arguments[1] ); break; //2 arguments
			case 3: $this->constructor_3( $arguments[0], $arguments[1], $arguments[2] ); break; //3 arguments
			case 4: $this->constructor_4( $arguments[0], $arguments[1], $arguments[2], $arguments[3] ); break; //4 arguments
			case 5: $this->constructor_5( $arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4] ); break; //5 arguments
		}
	}

	//Constructor with 1 parameter
	public function constructor_1( $value )
	{
		$this->value = $value;
	}

	//Constructor with 2 parameters
	public function constructor_2( $value, $caption )
	{
		$this->caption = $caption;
		$this->constructor_1( $value );
	}

	//Constructor with 3 parameters
	public function constructor_3( $value, $caption, $url )
	{
		$this->url = $url;
		$this->constructor_2( $value, $caption );
	}

	//Constructor with 4 parameters
	public function constructor_4( $value, $caption, $url, $img )
	{
		$this->img = $img;
		$this->constructor_3( $value, $caption, $url );
	}

	//Constructor with 5 parameters
	public function constructor_5( $value, $caption, $url, $img, $feed )
	{
		$this->feed = $feed;
		$this->constructor_4( $value, $caption, $url, $img );
	}

}
/*
 * End of CounterizeFeedItem class
 */


/*
 * Beginning of CounterizeFeedImg class
 */
class CounterizeFeedImg
{
	//String
	//SRC attribute of the IMG tag
	public $src;

	//String
	//ALT attribute of the IMG tag
	public $alt;

	//String
	//TITLE attribute of the IMG tag
	public $title;

	//String
	//CLASS attribute of the IMG tag
	public $class;

	//Integer
	//WIDTH attribute of the IMG tag
	public $width = 0;

	//Integer
	//HEIGHT attribute of the IMG tag
	public $height;


	//Default constructor
	public function __construct()
	{
		$arguments = func_get_args();
		$nbargs = count( $arguments );
		switch( $nbargs )
		{
			case 0: break; //0 arguments
			case 1: $this->constructor_1( $arguments[0] ); break; //1 argument
			case 2: $this->constructor_2( $arguments[0], $arguments[1] ); break; //2 arguments
			case 3: $this->constructor_3( $arguments[0], $arguments[1], $arguments[2] ); break; //3 arguments
			case 4: $this->constructor_4( $arguments[0], $arguments[1], $arguments[2], $arguments[3] ); break; //4 arguments
			case 5: $this->constructor_5( $arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4] ); break; //5 arguments
			case 6: $this->constructor_6( $arguments[0], $arguments[1], $arguments[2], $arguments[3], $arguments[4], $arguments[5] ); break; //6 arguments
		}
	}

	//Constructor with 1 parameter
	public function constructor_1( $src )
	{
		$this->src = $src;
	}

	//Constructor with 2 parameters
	public function constructor_2( $src, $alt )
	{
		$this->alt = $alt;
		$this->constructor_1( $src );
	}

	//Constructor with 3 parameters
	public function constructor_3( $src, $alt, $title )
	{
		$this->title = $title;
		$this->constructor_2( $src, $alt );
	}

	//Constructor with 4 parameters
	public function constructor_4( $src, $alt, $title, $class )
	{
		$this->class = $class;
		$this->constructor_3( $src, $alt, $title );
	}

	//Constructor with 5 parameters
	public function constructor_5( $src, $alt, $title, $class, $width )
	{
		$this->width = $width;
		$this->constructor_4( $src, $alt, $title, $class );
	}

	//Constructor with 6 parameters
	public function constructor_6( $src, $alt, $title, $class, $width, $height )
	{
		$this->height = $height;
		$this->constructor_5( $src, $alt, $title, $class, $width );
	}


	//String
	//Returns the string of the IMG tag
	public function render()
	{

		return "<img src='{$this->src}'"
			. ( !empty( $this->alt ) ? " alt='" . htmlspecialchars( $this->alt ) . "'" : '' )
			. ( !empty( $this->title ) ? " title='" . htmlspecialchars( $this->title ) . "'" : '' )
			. ( !empty( $this->class ) ? " class='" . htmlspecialchars( $this->class ) . "'" : '' )
			. ( $this->width > 0 ? " width='" . $this->width . "'" : '' )
			. ( $this->height > 0 ? " height='" . $this->height . "'" : '' )
			. " />&nbsp;";
	}
}
/*
 * End of CounterizeFeedImg class
 */

?>