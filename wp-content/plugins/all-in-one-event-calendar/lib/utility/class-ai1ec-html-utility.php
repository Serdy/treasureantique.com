<?php

/**
 * HTML enhancement utility library
 *
 * @author     Timely Network Inc
 * @since      1.9
 *
 * @package    AllInOneEventCalendar
 * @subpackage AllInOneEventCalendar.Lib.Utility
 */
class Ai1ec_Html_Utility
{

	/**
	 * @constant string HTTP standard EOL (CRLF)
	 */
	const EOL = "\r\n";

	/**
	 * Converts spaces to {{&nbsp;}} HTML entities.
	 *
	 * @param string $input Text to modify
	 *
	 * @param string HTML with spaces replaced with {{&nbsp;}} entities
	 */
	static public function nbsp( $input ) {
		return str_replace( ' ', '&nbsp;', $input );
	}

	/**
	 * Sanitizes given string to be escaped for inclusion in an HTML page.
	 *
	 * @param string $input HTML to sanitize
	 *
	 * @param string Sane HTML to use on screen
	 */
	static public function escape( $input ) {
		return esc_html( $input );
	}

	/**
	 * Returns HTML link tag, given rel(ation) and URI (href) for it.
	 *
	 * @param string $type Relation type for a link
	 * @param string $link URI to be rendered in `href` attribute
	 *
	 * @return string Rendered link element
	 */
	static public function link( $type, $link ) {
		return sprintf(
			'<link rel="%s" href="%s" />' . self::EOL,
			esc_attr( $type ),
			esc_url( $link )
		);
	}

	/**
	 * Returns HTML link tag as a canonical URL, to be output in header, for
	 * given URI.
	 *
	 * @param string $link URI to be outputed on canonical link
	 *
	 * @return string Rendered canonical URL
	 */
	static public function canonical_link( $link ) {
		return self::link( 'canonical', $link );
	}

}
