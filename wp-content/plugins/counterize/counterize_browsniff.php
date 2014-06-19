<?php
/*
Browser Sniff modified for Counterize by GabSoftware (http://www.gabsoftware.com/)
based on a wordpress plugin by Iman Nurchyo (http://priyadi.net/)
which is available at http://priyadi.net/archives/2005/03/29/wordpress-browser-detection-plugin/
but unfortunately it is not updated anymore.
*/

// security check
if( ! defined( 'WP_PLUGIN_DIR' ) )
{
	die( 'There is nothing to see here... Try again.' );
}

function counterize_print_browser( $before = '', $after = '', $image = false, $between = 'on' )
{
	global $user_ID, $post, $comment;
	get_currentuserinfo();
	if( !$comment->comment_agent )
	{
		return;
	}
	if( user_can_edit_post_comments( $user_ID, $post->ID ) )
	{
		$uastring = " <a href='#' title='" . htmlspecialchars( $comment->comment_agent ) . "'>*</a>";
	}
	$string = counterize_browser_string( $comment->comment_agent, $image, $between );
	echo $before . $string . $uastring . $after;
}

function counterize_browser_string( $ua, $image = false, $between = 'on' )
{
	list(
		$browser_name, $browser_code, $browser_ver, $browser_url,
		$os_name, $os_code, $os_ver, $os_url,
		$pda_name, $pda_code, $pda_ver, $pda_url
	) = counterize_detect_browser( $ua );
	$string = counterize_friendly_string(
		$browser_name, $browser_code, $browser_ver, $browser_url,
		$os_name, $os_code, $os_ver, $os_url,
		$pda_name, $pda_code, $pda_ver, $pda_url, $image, $between
	);
	if( ! $string )
	{
		$string = __( 'Unknown browser:<br />', COUNTERIZE_TD ) . $ua;
	}
	return $string;
}

function counterize_detect_browser( $ua )
{
	$browser_name = __( 'Unknown', COUNTERIZE_TD );
	$browser_code = 'unknown';
	$browser_ver  = '';
	$browser_url  = '';
	$os_name = __( 'Unknown', COUNTERIZE_TD );
	$os_code = 'unknown';
	$os_ver = '';
	$os_url = '';
	$pda_name = '';
	$pda_code = '';
	$pda_ver = '';
	$pda_url = '';
	$ua = preg_replace( '/FunWebProducts/i', '', $ua );



	/* perform an OS detection */
	list( $os_name, $os_code, $os_ver, $os_url ) = counterize_windows_detect_os( $ua );
	if( $os_code=='unknown' )
	{
		list( $os_name, $os_code, $os_ver, $os_url ) = counterize_unix_detect_os( $ua );
	}
	if( $os_code=='unknown' )
	{
		list( $os_name, $os_code, $os_ver, $os_url, $pda_name, $pda_code, $pda_ver, $pda_url ) = counterize_pda_detect_os( $ua );
	}


	/* here begins the browser detection */
	if( preg_match( '#MovableType[ /]([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'MovableType';
		$browser_code = 'mt';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.movabletype.org/';
	}
	elseif( preg_match( '#WordPress[ /\.]([a-zA-Z0-9\.]*)#i', $ua, $matches ) )
	{
		$browser_name = 'WordPress';
		$browser_code = 'wp';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.wordpress.org/';
	}
	elseif( preg_match( '#typepad[ /]([a-zA-Z0-9\.]*)#i', $ua, $matches ) )
	{
		$browser_name = 'TypePad';
		$browser_code = 'typepad';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.typepad.com/';
	}
	elseif( preg_match( '#ABrowse[ /]([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'ABrowse';
		$browser_code = 'abrowse';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.syllable.org/';
	}
	elseif( preg_match( '/Acoo Browser/i', $ua ) )
	{
		$browser_name = 'Acoo Browser';
		$browser_code = 'acoo';
		$browser_url = 'http://www.acoobrowser.com/';
	}
	elseif( preg_match( '#amaya/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Amaya';
		$browser_code = 'amaya';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.w3.org/Amaya/';
	}
	elseif( preg_match( '/America Online Browser ([0-9\.]+)/i', $ua, $matches ) || preg_match( '/AOL ([0-9\.]+)/i', $ua, $matches ) || preg_match( '#Cheshire/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'AOL Browser';
		$browser_code = 'aol';
		$browser_ver = $matches[1];
		if( preg_match( '/rev([0-9\.]+)/i', $ua, $matches ) )
		{
			$browser_ver = $matches[1];
		}
		$browser_url = 'http://www.aol.com/';
	}
	elseif( preg_match( '#AmigaVoyager/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Amiga Voyager';
		$browser_code = 'amigavoyager';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.vapor.com/voyager/';
	}
	elseif( preg_match( '#Arora/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Arora';
		$browser_code = 'arora';
		$browser_ver = $matches[1];
		$browser_url = 'http://code.google.com/p/arora/';
	}
	elseif( preg_match( '#Beonex/([a-zA-Z0-9\.-]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Beonex Communicator';
		$browser_code = 'beonex';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.beonex.com/communicator/';
	}
	elseif( preg_match( '/Browzar/i', $ua ) )
	{
		$browser_name = 'Browzar';
		$browser_code = 'browzar';
		$browser_url = 'http://www.browzar.com/';
	}
	elseif( preg_match( '/drupal/i', $ua ) )
	{
		$browser_name = 'Drupal';
		$browser_code = 'drupal';
		$browser_url = 'http://drupal.org/';
	}
	elseif( preg_match( '#BrowserNG/([a-zA-Z0-9\.]+)#i', $ua, $matches ) || preg_match( '#NokiaBrowser/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Nokia BrowserNG';
		$browser_ver = $matches[1];
		$browser_code = 'nokia';
		$browser_url = 'http://symbian.nokia.com/';
	}
	elseif( preg_match( '/avantbrowser\.com/i', $ua ) )
	{
		$browser_name = 'Avant Browser';
		$browser_code = 'avantbrowser';
		$browser_url = 'http://www.avantbrowser.com/';
	}
	elseif( preg_match( '#(Camino|Chimera)[ /]([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Camino';
		$browser_code = 'camino';
		$browser_ver = $matches[2];
		$browser_url = 'http://caminobrowser.org/';
		$os_name = 'Mac OS';
		$os_code = 'macos';
		$os_ver = 'X';
		$os_url = 'http://www.apple.com/macosx/';
	}
	elseif( preg_match( '/Charon/i', $ua ) )
	{
		$browser_name = 'Charon';
		$browser_code = 'charon';
		$browser_url = 'http://www.vitanuova.com/inferno/man/1/charon.html';
	}
	elseif( preg_match( '#CometBird/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'CometBird';
		$browser_code = 'cometbird';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.cometbird.com/';
	}
	elseif( preg_match( '/SlimBrowser/i', $ua ) )
	{
		$browser_name = 'SlimBrowser';
		$browser_code = 'slimbrowser';
		$browser_url = 'http://www.slimbrowser.net/';
	}
	elseif( preg_match( '#Crazy Browser ([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Crazy Browser';
		$browser_code = 'crazybrowser';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.crazybrowser.com/';
	}
	elseif( preg_match( '#Conkeror/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Conkeror';
		$browser_code = 'conkeror';
		$browser_ver = $matches[1];
		$browser_url = 'http://conkeror.org/';
	}
	elseif( preg_match( '#Cyberdog/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Cyberdog';
		$browser_code = 'cyberdog';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.cyberdog.org/';
	}
	elseif( preg_match( '/Deepnet Explorer ([0-9\.]+)/i', $ua, $matches ) )
	{
		$browser_name = 'Deepnet Explorer';
		$browser_code = 'deepnet';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.deepnetexplorer.com/';
	}
	elseif( preg_match( '#Deskbrowse/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'DeskBrowse';
		$browser_code = 'deskbrowse';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.deskbrowse.org/';
		$os_name = 'Mac OS';
		$os_code = 'macos';
		$os_ver = 'X';
		$os_url = 'http://www.apple.com/macosx/';
	}
	elseif( preg_match( '/anonymouse/i', $ua, $matches ) )
	{
		$browser_name = 'Anonymouse';
		$browser_code = 'anonymouse';
		$browser_url = 'http://anonymouse.org/';
	}
	elseif( preg_match( '/PHP/', $ua, $matches ) )
	{
		$browser_name = 'PHP';
		$browser_code = 'php';
		$browser_url = 'http://www.php.net/';
	}
	elseif( preg_match( '/danger hiptop/i', $ua ) )
	{
		$browser_name = 'Danger HipTop';
		$browser_code = 'danger';
		$browser_url = 'http://www.hiptop.com/';
	}
	elseif( preg_match( '#w3m/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'W3M';
		$browser_code = 'w3m';
		$browser_ver = $matches[1];
		$browser_url = 'http://w3m.sourceforge.net/';
	}
	elseif( preg_match( '/Shiira/i', $ua ) )
	{
		$browser_name = 'Shiira';
		$browser_code = 'shiira';
		if( preg_match( '#Shiira[ /]([0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_ver = $matches[1];
		}
		$browser_url = 'http://shiira.jp/';
		$os_name = 'Mac OS';
		$os_code = 'macos';
		$os_ver = 'X';
		$os_url = 'http://www.apple.com/macosx/';
	}
	elseif( preg_match( '#Dillo[ /]([a-zA-Z0-9\.-]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Dillo';
		$browser_code = 'dillo';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.dillo.org/';
	}
	elseif( preg_match( '#Dooble/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Dooble';
		$browser_code = 'dooble';
		$browser_ver = $matches[1];
		$browser_url = 'http://dooble.sourceforge.net/';
	}
	elseif( preg_match( '/Enigma Browser/i', $ua ) )
	{
		$browser_name = 'Enigma Browser';
		$browser_code = 'enigma';
		$browser_url = 'http://www.suttondesigns.com/';
	}
	elseif( preg_match( '/Element Browser ([0-9\.]+)/i', $ua, $matches ) )
	{
		$browser_name = 'Element Browser';
		$browser_code = 'elementbrowser';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.elementsoftware.co.uk/';
	}
	elseif( preg_match( '#Surf[ /]([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Surf';
		$browser_code = 'surf';
		$browser_ver = $matches[1];
		$browser_url = 'http://surf.suckless.org/';
	}
	elseif( preg_match( '#Epiphany/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Epiphany';
		$browser_code = 'epiphany';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.gnome.org/projects/epiphany/';
	}
	elseif( preg_match( '/Escape ([0-9\.]+)/i', $ua, $matches ) )
	{
		$browser_name = 'Escape';
		$browser_code = 'escape';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.espial.com/products/evo_browser/';
	}
	elseif( preg_match( '/FDM ([0-9\.x]+)/i', $ua, $matches ) )
	{
		$browser_name = 'Free Download Manager';
		$browser_code = 'fdm';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.freedownloadmanager.org/';
		$os_name = 'Windows';
		$os_code = 'windows';
		$os_url = 'http://www.microsoft.com/windows/';
	}
	elseif( preg_match( '/GreenBrowser/i', $ua ) )
	{
		$browser_name = 'GreenBrowser';
		$browser_code = 'greenbrowser';
		$browser_url = 'http://www.morequick.com/';
	}
	elseif( preg_match( '#Hana/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Hana';
		$browser_code = 'hana';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.macupdate.com/app/mac/25387/hana';
	}
	elseif( preg_match( '#HotJava/([0-9\.]+)#i', $ua, $matches ) || preg_match( '#Mozilla/([0-9\.]+) (x86 [en] Windows NT 5.1; Sun)#i', $ua, $matches ) )
	{
		$browser_name = 'HotJava';
		$browser_code = 'hotjava';
		$browser_ver = $matches[1];
		$browser_url = 'http://java.sun.com/products/archive/hotjava/index.html';
	}
	elseif( preg_match( '#IBM WebExplorer[ ]?/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'IBM WebExplorer';
		$browser_code = 'webexplorer';
		$browser_ver = $matches[1];
		$browser_url = 'http://en.wikipedia.org/wiki/IBM_WebExplorer';
	}
	elseif( preg_match( '#IBrowse/([A-Za-z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'IBrowse';
		$browser_code = 'ibrowse';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.ibrowse-dev.net/';
	}
	elseif( preg_match( '#UP.Browser/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Openwave UP.Browser';
		$browser_code = 'openwave';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.openwave.com/';
	}
	elseif( preg_match( '#DoCoMo/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'DoCoMo';
		$browser_code = 'docomo';
		$browser_ver = $matches[1];
		if( $browser_ver == '1.0' )
		{
			preg_match( '#DoCoMo/([a-zA-Z0-9\.]+)/([a-zA-Z0-9\.]+)#i', $ua, $matches );
			$browser_ver = $matches[2];
		}
		elseif( $browser_ver == '2.0' )
		{
			preg_match( '#DoCoMo/([a-zA-Z0-9\.]+) ([a-zA-Z0-9\.]+)#i', $ua, $matches );
			$browser_ver = $matches[2];
		}
		$browser_url = 'http://www.nttdocomo.com/';
	}
	elseif( preg_match( '#(bonecho)/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Unbranded Firefox';
		$browser_code = 'bonecho';
		$browser_ver = $matches[2];
		$browser_url = 'http://www.mozilla.org/projects/bonecho/';
	}
	elseif( preg_match( '#Iceape/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Mozilla Iceape';
		$browser_code = 'iceape';
		$browser_ver = $matches[1];
		$browser_url = 'http://packages.debian.org/squeeze/iceape';
	}
	elseif( preg_match( '#iNet Browser ([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'iNet Browser';
		$browser_code = 'inet';
		$browser_ver = $matches[1];
		$browser_url = '';
	}
	elseif( preg_match( '#IceCat/([a-zA-Z0-9\.-]+)#i', $ua, $matches ) )
	{
		$browser_name = 'GNU IceCat';
		$browser_code = 'icecat';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.gnu.org/software/icecat/';
	}
	elseif( preg_match( '/IceWeasel/i', $ua ) )
	{
		$browser_name = 'Debian IceWeasel';
		$browser_code = 'iceweasel';
		if( preg_match( '#IceWeasel/([a-zA-Z0-9\.-]+)#i', $ua, $matches ) )
		{
			$browser_ver = $matches[1];
		}
		$browser_url = 'http://www.geticeweasel.org/';
	}
	elseif( preg_match( '/SeaMonkey/i', $ua ) )
	{
		$browser_name = 'Mozilla SeaMonkey';
		$browser_code = 'seamonkey';
		if( preg_match( '#SeaMonkey/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_ver = $matches[1];
		}
		$browser_url = 'http://www.seamonkey-project.org/';
	}
	elseif( preg_match( '#Kazehakase/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Kazehakase';
		$browser_code = 'kazehakase';
		$browser_ver = $matches[1];
		$browser_url = 'http://kazehakase.sourceforge.jp/';
	}
	elseif( preg_match( '#Flock/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Flock';
		$browser_code = 'flock';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.flock.com/';
	}
	elseif( preg_match( '#Fluid/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Fluid';
		$browser_code = 'fluid';
		$browser_ver = $matches[1];
		$browser_url = 'http://fluidapp.com/';
	}
	elseif( preg_match( '#PLAYSTATION 3; ([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Playstation 3';
		$browser_code = 'ps3';
		$browser_ver = $matches[1];
		$browser_url = 'http://playstation.com/';
	}
	elseif( preg_match( '#Polaris ([0-9\.]+)(.+)Samsung ([a-zA-Z0-9-])#i', $ua, $matches ) )
	{
		$browser_name = 'Polaris';
		$browser_code = 'polaris';
		$browser_ver = $matches[1];
		$browser_url = 'http://samsung.com/';
		$pda_name = 'Samsung';
		$pda_code = 'samsung';
		$pda_ver = $matches[2];
		$pda_url = 'http://www.samsung.com/';
	}
	elseif( preg_match( '#Firebird/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Mozilla Firebird';
		$browser_code = 'firebird';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.mozilla.org/projects/firebird/';
	}
	elseif( preg_match( '#Phoenix/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Mozilla Phoenix';
		$browser_code = 'phoenix';
		$browser_ver = $matches[1];
		$browser_url = 'http://www-archive.mozilla.org/projects/phoenix/phoenix-release-notes.html';
	}
	elseif( preg_match( '#Pogo/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Pogo';
		$browser_code = 'pogo';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.att.com/';
	}
	elseif( preg_match( '#GranParadiso/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'GranParadiso';
		$browser_code = 'granparadiso';
		$browser_ver = $matches[1];
		$browser_url = 'http://en.wikipedia.org/wiki/Firefox_3#Development';
	}
	elseif( preg_match( '#Lorentz/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Lorentz';
		$browser_code = 'lorentz';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.mozilla.com/en-US/firefox/lorentz/';
	}
	elseif( preg_match( '#Madfox/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Madfox';
		$browser_code = 'madfox';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.macupdate.com/app/mac/17936/madfox';
	}
	elseif( preg_match( '#SafeFox/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'SafeFox';
		$browser_code = 'safefox';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.brothersoft.com/safefox-286319.html';
	}
	elseif( preg_match( '#Firefox/([a-zA-Z0-9\.]+) \(Swiftfox\)#i', $ua, $matches ) )
	{
		$browser_name = 'Swiftfox';
		$browser_code = 'swiftfox';
		$browser_ver = $matches[1];
		$browser_url = 'http://getswiftfox.com/';
	}
	elseif( preg_match( '#Minefield/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Minefield';
		$browser_code = 'minefield';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.mozilla.org/projects/minefield/';
	}
	elseif( preg_match( '#Namoroka/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Namoroka';
		$browser_code = 'namoroka';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.mozilla.org/projects/namoroka/';
	}
	elseif( preg_match( '#Palemoon/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Palemoon';
		$browser_code = 'palemoon';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.palemoon.org/';
	}
	elseif( preg_match( '#Prism/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Prism';
		$browser_code = 'prism';
		$browser_ver = $matches[1];
		$browser_url = 'http://prism.mozillalabs.com/';
	}
	elseif( preg_match( '#Shiretoko/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Shiretoko';
		$browser_code = 'shiretoko';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.mozilla.org/projects/shiretoko/';
	}
	elseif( preg_match( '#Sylera/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Sylera';
		$browser_code = 'sylera';
		$browser_ver = $matches[1];
		$browser_url = 'http://ja.wikipedia.org/wiki/Sylera';
	}
	elseif( preg_match( '#Vonkeror/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Vonkeror';
		$browser_code = 'vonkeror';
		$browser_ver = $matches[1];
		$browser_url = 'http://zzo38computer.cjb.net/vonkeror/';
	}
	elseif( preg_match( '#WeltweitimnetzBrowser/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Weltweitimnetz Browser';
		$browser_code = 'weltweitimnetzbrowser';
		$browser_ver = $matches[1];
		$browser_url = 'http://code.google.com/p/weltweitimnetz-browser/';
	}
	elseif( preg_match( '#Wyzo/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Wyzo';
		$browser_code = 'wyzo';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.wyzo.com/';
	}
	elseif( preg_match( '#Minimo/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Minimo';
		$browser_code = 'mozilla';
		$browser_ver = $matches[1];
		$browser_url = 'http://www-archive.mozilla.org/projects/minimo/';
	}
	elseif( preg_match( '#MultiZilla(/| v)([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'MultiZilla';
		$browser_code = 'mozilla';
		$browser_ver = $matches[2];
		$browser_url = 'http://multizilla.mozdev.org/';
	}
	elseif( preg_match( '#Navscape/([a-zA-Z0-9\.-]+)#i', $ua, $matches ) || preg_match( '#NavscapeNavigator/([a-zA-Z0-9\.-]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Navscape';
		$browser_code = 'navscape';
		$browser_ver = $matches[1];
		$browser_url = 'http://sourceforge.net/projects/navscape/';
	}
	elseif( preg_match( '#PSP \(PlayStation Portable\); ([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$pda_name = 'Sony PSP';
		$pda_code = 'sony-psp';
		$pda_ver = $matches[1];
		$pda_url = 'http://us.playstation.com/psp/';
	}
	elseif( preg_match( '#Galeon/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Galeon';
		$browser_code = 'galeon';
		$browser_ver = $matches[1];
		$browser_url = 'http://galeon.sourceforge.net/';
	}
	elseif( preg_match( '#Orca/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Orca';
		$browser_code = 'orca';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.orcabrowser.com/';
	}
	elseif( preg_match( '/Oregano ([0-9\.]+)/i', $ua, $matches ) )
	{
		$browser_name = 'Oregano';
		$browser_code = 'oregano';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.castle.org.uk/oregano/';
	}
	elseif( preg_match( '#Sleipnir[/ ]([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Sleipnir';
		$browser_code = 'sleipnir';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.fenrir-inc.com/global/sleipnir/';
	}
	elseif( preg_match( '#Firefox/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Mozilla Firefox';
		$browser_code = 'firefox';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.mozilla.com/firefox/';
	}
	elseif( preg_match( '/iPad/i', $ua ) )
	{
		$browser_name = 'iPad';
		$browser_code = 'ipad';
		$browser_url = 'http://www.apple.com/ipad/';
	}
	elseif( preg_match( '/iPod/i', $ua ) )
	{
		$browser_name = 'iPod';
		$browser_code = 'ipod';
		$browser_url = 'http://www.apple.com/ipod/';
	}
	elseif( preg_match( '/iPhone/i', $ua ) )
	{
		$browser_name = 'iPhone';
		$browser_code = 'iphone';
		$browser_url = 'http://www.apple.com/iphone/';
	}
	elseif( preg_match( '#iCab[/ ]([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'iCab';
		$browser_code = 'icab';
		$browser_ver = $matches[1];
		$browser_url = '';
		$os_name = 'Mac OS';
		$os_code = 'macos';
		if( preg_match( '/Mac OS X/i', $ua ) )
		{
				$os_ver = 'X';
		}
		$os_url = 'http://www.apple.com/macosx/';
	}
	elseif( preg_match( '#K-Meleon/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'K-Meleon';
		$browser_code = 'kmeleon';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.icab.de/';
	}
	elseif( preg_match( '#K-Ninja/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'K-Ninja';
		$browser_code = 'kninja';
		$browser_ver = $matches[1];
		$browser_url = 'http://en.wikipedia.org/wiki/K-Meleon#K-Ninja.2C_KMLite';
	}
	elseif( preg_match( '#Kapiko/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Kapiko';
		$browser_code = 'kapiko';
		$browser_ver = $matches[1];
		$browser_url = 'http://sites.google.com/site/kapikoproject/';
	}
	elseif( preg_match( '/KKMAN[ ]?([0-9\.]+)/i', $ua, $matches ) )
	{
		$browser_name = 'KKman';
		$browser_code = 'kkman';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.kkbox.com.tw/kkman/index.html';
	}
	elseif( preg_match( '#KMLite/([a-zA-Z0-9\.]+)#i', $ua ) )
	{
		$browser_name = 'KMLite';
		$browser_code = 'kmlite';
		$browser_ver = $matches[1];
		$browser_url = 'http://en.wikipedia.org/wiki/K-Meleon#K-Ninja.2C_KMLite';
	}
	elseif( preg_match( '#Konqueror/([a-zA-Z0-9\.-]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Konqueror';
		$browser_code = 'konqueror';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.konqueror.org/';
	}
	elseif( preg_match( '/LeechCraft/i', $ua ) )
	{
		$browser_name = 'LeechCraft';
		$browser_code = 'leechcraft';
		if( preg_match( '#LeechCraft/Poshuku ([a-zA-Z0-9\.-]+)#i', $ua, $matches ) || preg_match( '#LeechCraft/([a-zA-Z0-9\.-]+)#i', $ua, $matches ) )
		{
			$browser_ver = $matches[1];
		}
		$browser_url = 'http://leechcraft.org/';
	}
	elseif( preg_match( '/Lynx/i', $ua ) )
	{
		$browser_name = 'Lynx';
		$browser_code = 'lynx';
		if( preg_match( '#Lynx/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_ver = $matches[1];
		}
		$browser_url = 'http://en.wikipedia.org/wiki/Lynx_(web_browser)';
	}
	elseif( preg_match( '#ELinks[/ ]([a-zA-Z0-9\.-]+)#i', $ua, $matches ) || preg_match( '#ELinks \(([a-zA-Z0-9\.-]+)#i', $ua, $matches ) )
	{
		$browser_name = 'ELinks';
		$browser_code = 'elinks';
		$browser_ver = $matches[1];
		$browser_url = 'http://elinks.or.cz/';
	}
	elseif( preg_match( '/Links/i', $ua ) )
	{
		$browser_name = 'Links';
		$browser_code = 'links';
		if( preg_match( '#Links \(([a-zA-Z0-9\.]+)#i', $ua, $matches ) || preg_match( '#Links ([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_ver = $matches[1];
		}
		$browser_url = 'http://www.jikos.cz/~mikulas/links/';
	}
	elseif( preg_match( '#Lobo/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Lobo';
		$browser_code = 'lobo';
		$browser_ver = $matches[1];
		$browser_url = 'http://lobobrowser.org/';
	}
	elseif( preg_match( '#lolifox/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Lolifox';
		$browser_code = 'lolifox';
		$browser_ver = $matches[1];
		$browser_url = 'http://lolifox.com/';
	}
	elseif( preg_match( '#Lunascape[/ ]([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Lunascape';
		$browser_code = 'lunascape';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.lunascape.tv/';
	}
	elseif( preg_match( '/MyIE2/i', $ua ) )
	{
		$browser_name = 'MyIE2';
		$browser_code = 'myie2';
		$browser_url = 'http://www.myie2.com/';
	}
	elseif( preg_match( '/Maxthon/i', $ua ) )
	{
		$browser_name = 'Maxthon';
		$browser_code = 'maxthon';
		if( preg_match( '#Maxthon[/ ]([0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_ver = $matches[1];
		}
		$browser_url = 'http://www.maxthon.com/';
	}
	elseif( preg_match( '#NCSA[ _]Mosaic/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'NCSA Mosaic';
		$browser_code = 'mosaic';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.ncsa.illinois.edu/Projects/mosaic.html';
	}
	elseif( preg_match( '#NetPositive/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'NetPositive';
		$browser_code = 'netpositive';
		$browser_ver = $matches[1];
		$browser_url = 'http://en.wikipedia.org/wiki/NetPositive';
		$os_name = 'BeOS';
		$os_code = 'beos';
		$os_url = 'http://en.wikipedia.org/wiki/BeOS';
	}
	elseif( preg_match( '#NetSurf/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'NetSurf';
		$browser_code = 'netsurf';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.netsurf-browser.org/';
	}
	elseif( preg_match( '/OmniWeb/i', $ua ) )
	{
		$browser_name = 'OmniWeb';
		$browser_code = 'omniweb';
		if( preg_match( '#OmniWeb/[v]?([a-zA-Z0-9\.-]+)#i', $ua, $matches ) )
		{
			$browser_ver = $matches[1];
		}
		$browser_url = 'http://www.omnigroup.com/applications/omniweb/';
		$os_name = 'Mac OS';
		$os_code = 'macos';
		$os_ver = 'X';
		$os_url = 'http://www.apple.com/macosx/';
	}
	elseif( preg_match( '/Midori/i', $ua ) )
	{
		$browser_name = 'Midori';
		$browser_code = 'midori';
		if( preg_match( '#Midori/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_ver = $matches[1];
		}
		$browser_url = 'http://www.twotoasts.de/index.php?/pages/midori_summary.html';
	}
	elseif( preg_match( '#myibrow/([	a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'My Internet Browser';
		$browser_code = 'myibrow';
		$browser_ver = $matches[1];
		$browser_url = 'http://sourceforge.net/projects/myibrow';
	}
	elseif( preg_match( '#Chrome/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Chrome';
		$browser_code = 'chrome';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.google.com/chrome/';
		if( preg_match( '#ChromePlus/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_name = 'ChromePlus';
			$browser_ver = $matches[1];
			$browser_url = 'http://www.chromeplus.org/';
		}
		if( preg_match( '#ChromePlus#i', $ua, $matches ) )
		{
			$browser_name = 'ChromePlus';
			$browser_url = 'http://www.chromeplus.org/';
		}
		if( preg_match( '#Comodo_Dragon/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_name = 'Comodo Dragon';
			$browser_ver = $matches[1];
			$browser_url = 'http://www.comodo.com/home/browsers-toolbars/browser.php';
		}
		if( preg_match( '#Iron/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_name = 'SRWare Iron';
			$browser_code = 'iron';
			$browser_ver = $matches[1];
			$browser_url = 'http://www.srware.net/en/software_srware_iron.php';
		}
		if( preg_match( '#RockMelt/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_name = 'RockMelt';
			$browser_code = 'rockmelt';
			$browser_ver = $matches[1];
			$browser_url = 'http://www.rockmelt.com/';
		}
		if( preg_match( '#Chromium/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_name = 'Chromium';
			$browser_code = 'chromium';
			$browser_ver = $matches[1];
			$browser_url = 'http://www.chromium.org/';
		}
	}
	elseif( preg_match( '/rekonq/i', $ua ) )
	{
		$browser_name = 'Rekonq';
		$browser_code = 'rekonq';
		$browser_url = 'http://rekonq.kde.org/';
	}
	elseif( preg_match( '#Stainless/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'luakit';
		$browser_code = 'luakit';
		$browser_ver = $matches[1];
		$browser_url = 'http://luakit.org/projects/luakit/';
	}
	elseif( preg_match( '#luakit/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Stainless';
		$browser_code = 'stainless';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.stainlessapp.com/';
	}
	elseif( preg_match( '/TheWorld/i', $ua ) )
	{
		$browser_name = 'TheWorld Browser';
		$browser_code = 'theworld';
		$browser_url = 'http://www.ioage.com/en/';
	}
	elseif( preg_match( '#ScribdReader/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Scribd Reader';
		$browser_code = 'scribd';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.scribd.com/';
	}
	elseif( preg_match( '#(Sunrise|SunriseBrowser)/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Sunrise Browser';
		$browser_code = 'sunrise';
		$browser_ver = $matches[2];
		$browser_url = 'http://www.sunrisebrowser.com/';
	}
	elseif( preg_match( '#wOSBrowser/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'WebOS Browser';
		$browser_code = 'webos';
		$browser_ver = $matches[1];
		$browser_url = 'http://en.wikipedia.org/wiki/WebOS#Web_browser';
	}
	elseif( preg_match( '#(.+)\(webOS/([0-9\.]);(.+) Pixi/([0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Palm Pixi';
		$browser_code = 'palm';
		$browser_ver = $matches[4];
		$browser_url = 'http://en.wikipedia.org/wiki/Palm_Pixi';
		$os_name = 'WebOS';
		$os_code = 'webos';
		$os_ver = $matches[2];
		$os_url = 'http://en.wikipedia.org/wiki/WebOS';
	}
	elseif( preg_match( '#Safari/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Safari';
		$browser_code = 'safari';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.apple.com/safari/';
		if( preg_match( '#Version/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_ver = $matches[1];
		}
		if( preg_match( '/Mobile/i', $ua ) )
		{
			$browser_name = 'Mobile Safari';
			$browser_url = 'http://www.apple.com/iphone/features/safari.html';
		}
	}
	elseif( preg_match( '#NetNewsWire/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'NetNewsWire';
		$browser_code = 'netnewswire';
		$browser_ver = $matches[1];
		$browser_url = 'http://netnewswireapp.com/';
		$os_name = 'Mac OS';
		$os_code = 'macos';
		$os_ver = 'X';
		$os_url = 'http://www.apple.com/macosx/';
	}
	elseif( preg_match( '#NSPlayer/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'NSPlayer';
		$browser_code = 'nsplayer';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.nsplayer.org/';
	}
	elseif( preg_match( '#QtWeb Internet Browser/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'QtWeb Internet Browser';
		$browser_code = 'qtweb';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.qtweb.net/';
	}
	elseif( preg_match( '/opera mini/i', $ua ) )
	{
		$browser_name = 'Opera Mini';
		$browser_code = 'opera';
		if( !preg_match( '#Version/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			preg_match( '#Opera/([a-zA-Z0-9\.]+)#i', $ua, $matches );
		}
		$browser_ver = $matches[1];
		$browser_url = 'http://www.opera.com/mobile/';
	}
	elseif( preg_match( '/Opera/i', $ua, $matches ) )
	{
		$browser_name = 'Opera';
		$browser_code = 'opera';
		if( preg_match( '#Opera[ /]([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_ver = $matches[1];
		}
		if( preg_match( '#Version/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_ver = $matches[1];
		}
		$browser_url = 'http://www.opera.com/';
	}
	elseif( preg_match( '/WebPro/i', $ua ) )
	{
		$browser_name = 'WebPro';
		$browser_code = 'webpro';
		if( preg_match( '#WebPro/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			$browser_ver = $matches[1];
		}
		$browser_url = 'http://www.novarra.com/';
		$os_name = 'PalmOS';
		$os_code = 'palmos';
		$os_url = 'http://www.palm.com/';
	}
	elseif( preg_match( '#Netfront/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Netfront';
		$browser_code = 'netfront';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.access-company.com/products/mobile_solutions/netfrontmobile/browser/index.html';
	}
	elseif( preg_match( '#Xiino/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Xiino';
		$browser_code = 'xiino';
		$browser_ver = $matches[1];
		$browser_url = '';
	}
	elseif( preg_match( '/Blackberry/i', $ua ) )
	{
		$pda_name = 'Blackberry';
		$pda_code = 'blackberry';
		$pda_url = 'http://us.blackberry.com/';
		if( preg_match( '/Blackberry([a-z0-9]+)/i', $ua, $matches ) )
		{
			$pda_ver = $matches[1];
			if( preg_match( '#Blackberry([a-z0-9]+)/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
			{
				$browser_name = 'Blackberry';
				$browser_code = 'blackberry';
				$browser_ver = $matches[2];
				$browser_url = 'http://us.blackberry.com/';
			}
		}
		else
		{
			if( preg_match( '#Blackberry/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
			{
				$browser_name = 'Blackberry';
				$browser_code = 'blackberry';
				$browser_ver = $matches[2];
				$browser_url = 'http://www.blackberry.com/';
			}
		}
	}
	elseif( preg_match( '#SPV ([0-9a-zA-Z.]+)#i', $ua, $matches ) )
	{
		$pda_name = 'Orange SPV';
		$pda_code = 'orange';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.orange.com/';
	}
	elseif( preg_match( '#LGE-([a-zA-Z0-9]+)#i', $ua, $matches ) )
	{
		$pda_name = 'LG';
		$pda_code = 'lg';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.lg.com/';
	}
	elseif( preg_match( '#MOT-([a-zA-Z0-9/_\.]+) MIB/(0-9\.)/#i', $ua, $matches ) )
	{
		$pda_name = 'Motorola';
		$pda_code = 'motorola';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.motorola.com/';
		$browser_name = 'Motorola Internet Browser';
		$browser_code = 'motorola';
		$browser_ver = $matches[2];
		$browser_url = 'http://www.motorola.com/';
	}
	elseif( preg_match( '#MOT-([a-zA-Z0-9]+)#i', $ua, $matches ) )
	{
		$pda_name = 'Motorola';
		$pda_code = 'motorola';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.motorola.com/';
	}
	elseif( preg_match( '#Nokia([a-zA-Z0-9-]+)/([a-zA-Z0-9]+)#i', $ua, $matches ) )
	{
		$pda_name = 'Nokia';
		$pda_code = 'nokia';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.nokia.com/';
		$browser_name = 'Nokia';
		$browser_code = 'nokia';
		$browser_ver = $matches[2];
		$browser_url = 'http://www.nokia.com/';
	}
	elseif( preg_match( '#Nokia[ ]?([a-zA-Z0-9]+)#i', $ua, $matches ) )
	{
		$pda_name = 'Nokia';
		$pda_code = 'nokia';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.nokia.com/';
	}
	elseif( preg_match( '/NokiaN-Gage/i', $ua ) )
	{
		$pda_name = 'Nokia';
		$pda_code = 'nokia';
		$pda_ver = 'N-Gage';
		$pda_url = 'http://www.n-gage.com/';
	}
	elseif( preg_match( '#Blazer[ /]?([a-zA-Z0-9\.]*)#i', $ua, $matches ) )
	{
		$browser_name = 'Blazer';
		$browser_code = 'blazer';
		$browser_ver = $matches[1];
		$browser_url = 'http://en.wikipedia.org/wiki/Blazer_(web_browser)';
		$os_name = 'Palm OS';
		$os_code = 'palm';
		$os_url = 'http://www.palm.com/';
	}
	elseif( preg_match( '/MAUI_WAP_Browser/i', $ua ) )
	{
		$browser_name = 'Siemens MAUI_WAP Browser';
		$browser_code = 'siemens';
		$browser_url = 'http://www.siemens.com/';
	}
	elseif( preg_match( '/ibisBrowser/i', $ua ) )
	{
		$browser_name = 'ibisBrowserDx';
		$browser_code = 'ibis';
		$browser_url = 'http://ibis.ne.jp/en/browser/';
	}
	elseif( preg_match( '#SIE-([a-zA-Z0-9]+)#i', $ua, $matches ) )
	{
		$pda_name = 'Siemens';
		$pda_code = 'siemens';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.siemens.com/';
	}
	elseif( preg_match( '#SEC-([a-zA-Z0-9]+)#i', $ua, $matches ) )
	{
		$pda_name = 'Samsung';
		$pda_code = 'samsung';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.samsung.com/';
	}
	elseif( preg_match( '#SAMSUNG-(S.H-[a-zA-Z0-9]+)#i', $ua, $matches ) )
	{
		$pda_name = 'Samsung';
		$pda_code = 'samsung';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.samsung.com/';
	}
	elseif( preg_match( '#Samsung sam-(r[0-9]+)#i', $ua, $matches ) )
	{
		$pda_name = 'Samsung';
		$pda_code = 'samsung';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.samsung.com/';
	}
	elseif( preg_match( '#SonyEricsson(.+)/(.+) SEMC-Browser/([0-9\.])#i', $ua, $matches ) )
	{
		$pda_name = 'SonyEricsson';
		$pda_code = 'sonyericsson';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.sonyericsson.com/';
		$browser_name = 'SEMC-Browser';
		$browser_code = 'sonyericsson';
		$browser_ver = $matches[3];
		$browser_url = 'http://www.sonyericsson.com/';
	}
	elseif( preg_match( '#SonyEricsson ?([a-zA-Z0-9]+)#i', $ua, $matches ) )
	{
		$pda_name = 'SonyEricsson';
		$pda_code = 'sonyericsson';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.sonyericsson.com/';
	}
	elseif( preg_match( '/(j2me|midp)/i', $ua ) )
	{
		$browser_name = 'J2ME/MIDP Browser';
		$browser_code = 'j2me';
		$browser_url = 'http://en.wikipedia.org/wiki/Java_Platform,_Micro_Edition';
	}
	elseif( preg_match( '/iRider ([0-9\.]+)/i', $ua, $matches ) )
	{
		$browser_name = 'iRider';
		$browser_code = 'irider';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.irider.com/';
	}
	elseif( preg_match( '/TencentTraveler ([0-9\.]+)/i', $ua, $matches ) )
	{
		$browser_name = 'Tencent Traveler';
		$browser_code = 'tencenttraveler';
		$browser_ver = $matches[1];
		$browser_url = 'http://tt.qq.com/';
	}
	elseif( preg_match( '/Uzbl/i', $ua ) )
	{
		$browser_name = 'Uzbl';
		$browser_code = 'uzbl';
		$browser_url = 'http://www.uzbl.org/';
	}
	elseif( preg_match( '/MSIE ([a-zA-Z0-9\.]+)/i', $ua, $matches ) )
	{
		$browser_name = 'Internet Explorer';
		$browser_code = 'ie';
		$browser_ver = $matches[1];
		$browser_url = 'http://windows.microsoft.com/en-US/internet-explorer/products/ie/home';
	}
	elseif( preg_match( '#Microsoft Internet Explorer/4\.0b1 \(Windows 95\)#i', $ua, $matches ) )
	{
		$browser_name = 'Internet Explorer';
		$browser_code = 'ie';
		$browser_ver = '1.0';
		$browser_url = 'http://windows.microsoft.com/en-US/internet-explorer/products/ie/home';
	}
	elseif( preg_match( '#Microsoft Pocket Internet Explorer/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Pocket Internet Explorer';
		$browser_code = 'ie';
		$browser_ver = $matches[1];
		$browser_url = 'http://msdn.microsoft.com/en-us/library/ms924677.aspx';
	}
	elseif( preg_match( '#Netscape[0-9]?/([a-zA-Z0-9\.]+)#i', $ua, $matches ) || preg_match( '#Navigator/([a-zA-Z0-9\.]+)#i', $ua, $matches ) || preg_match( '#Netscape ([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Netscape Navigator';
		$browser_code = 'netscape';
		$browser_ver = $matches[1];
		$browser_url = 'http://browser.netscape.com/';
	}
	elseif( preg_match( '#^Mozilla/5.0#i', $ua ) && preg_match( '#rv:([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Mozilla';
		$browser_code = 'mozilla';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.mozilla.org/';
	}
	elseif( preg_match( '#^Mozilla/([2-4]\.[a-zA-Z0-9-]+) \[#i', $ua, $matches ) || preg_match( '#^Mozilla/([2-4]\.[a-zA-Z0-9-]+) \(Macintosh#i', $ua, $matches ) || preg_match( '#^Mozilla/(0.91 Beta) \(Windows\)#i', $ua, $matches ) || preg_match( '#^Mozilla/(0.6 Beta) \(Windows\)#i', $ua, $matches ) )
	{
		$browser_name = 'Netscape Navigator';
		$browser_code = 'netscape';
		$browser_ver = $matches[1];
		$browser_url = 'http://browser.netscape.com/';
	}
	elseif( preg_match( '#vlc/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'VLC';
		$browser_code = 'vlc';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.videolan.org/vlc/';
	}
	elseif( preg_match( '#Java/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Java-based browser';
		$browser_code = 'java';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.java.com/';
	}
	elseif( preg_match( '#360%20Browser/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = '360 Web Browser';
		$browser_code = '360';
		$browser_ver = $matches[1];
		$browser_url = 'http://360.digitalpoke.com/';
	}
	elseif( preg_match( '#Instapaper/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
	{
		$browser_name = 'Instapaper';
		$browser_code = 'instapaper';
		$browser_ver = $matches[1];
		$browser_url = 'http://www.instapaper.com/';
	}
	elseif( preg_match( '/Printfriendly/i', $ua ) )
	{
		$browser_name = 'Printfriendly';
		$browser_code = 'printfriendly';
		$browser_url = 'http://www.printfriendly.com/';
	}
	elseif( preg_match( '#My RSS Reader#i', $ua ) )
	{
		$browser_name = 'RSS Reader';
		$browser_code = 'rss';
		$browser_url = 'http://en.wikipedia.org/wiki/RSS';
	}
	/* vars:
		$browser_name
		$browser_code
		$browser_ver
		$browser_url
		$os_name
		$os_code
		$os_ver
		$os_url
		$pda_name
		$pda_code
		$pda_ver
		$pda_url
	*/

	return array( $browser_name, $browser_code, $browser_ver, $browser_url, $os_name, $os_code, $os_ver, $os_url, $pda_name, $pda_code, $pda_ver, $pda_url );
}

//return the url of the image corresponding to the specified code
function counterize_get_image_url( $code )
{
	if( file_exists( COUNTERIZE_IMAGE_DIR . "/{$code}.png" ) )
	{
		return COUNTERIZE_IMAGE_URL . "/{$code}.png";
	}

	return '';
}

//return an IMG tag
function counterize_get_image_tag ( $code, $alt, $url='' )
{
	$alt = htmlspecialchars( $alt );
	$code = htmlspecialchars( $code );
	$res = '';
	$hasurl=false;

	if( isset( $url ) && strlen( $url ) > 0 )
	{
		$hasurl = true;
	}

	if( file_exists( COUNTERIZE_IMAGE_DIR . "/{$code}.png" ) )
	{
		$res .= "
								<img
									src='" . COUNTERIZE_IMAGE_URL . "/{$code}.png'
									alt='{$alt}'
									" . ( $hasurl ? 'title="' . __( 'Click here to visit this product homepage', COUNTERIZE_TD ) . '"' : '' ) . "
									width='" . COUNTERIZE_ICON_SIZE . "'
									height='" . COUNTERIZE_ICON_SIZE . "'
									class='browsericon'
								 />
									";
	}

	return $res;
}

function counterize_friendly_string (
	$browser_name = ''	, $browser_code = '', $browser_ver = ''	, $browser_url = '',
	$os_name = ''		, $os_code = ''		, $os_ver = ''		, $os_url = '',
	$pda_name= ''		, $pda_code = ''	, $pda_ver = ''		, $pda_url = '',		$image = false, $between = 'on' )
{
	$out = '';
	$browser_name = htmlspecialchars( $browser_name );
	$browser_code = htmlspecialchars( $browser_code );
	$browser_ver = htmlspecialchars( $browser_ver );
	$browser_url = htmlspecialchars( $browser_url );
	$os_name = htmlspecialchars( $os_name );
	$os_code = htmlspecialchars( $os_code );
	$os_ver = htmlspecialchars( $os_ver );
	$os_url = htmlspecialchars( $os_url );
	$pda_name = htmlspecialchars( $pda_name );
	$pda_code = htmlspecialchars( $pda_code );
	$pda_ver = htmlspecialchars( $pda_ver );
	$pda_url = htmlspecialchars( $pda_url );
	$between = $between;
	if( $browser_name && $pda_name )
	{
		if( $image )
		{
			$out .= counterize_get_image_tag( $browser_code, $browser_name, $browser_url );
		}
		$out .= $browser_name;
		if( $browser_ver )
		{
			$out .= " {$browser_ver}";
		}
		$out .= " {$between} ";
		if( $image )
		{
			$out .= counterize_get_image_tag( $pda_code, $pda_name, $pda_url );
		}
		$out .= " {$pda_name}";
		if( $pda_ver )
		{
			$out .= " {$pda_ver}";
		}
	}
	elseif( $browser_name && $os_name )
	{
		if( $image )
		{
			$out .= counterize_get_image_tag( $browser_code, $browser_name, $browser_url );
		}
		$out .= $browser_name;
		if( $browser_ver )
		{
			$out .= " {$browser_ver}";
		}
		$out .= " {$between} ";
		if( $image )
		{
			$out .= counterize_get_image_tag( $os_code, $os_name, $os_url );
		}
		$out .= " {$os_name}";
		if( $os_ver )
		{
			$out .= " {$os_ver}";
		}
	}
	elseif( $browser_name )
	{
		if( $image )
		{
			$out .= counterize_get_image_tag( $browser_code, $browser_name, $browser_url );
		}
		$out .= $browser_name;
		if( $browser_ver )
		{
			$out .= " {$browser_ver}";
		}
	}
	elseif( $os_name )
	{
		if( $image )
		{
			$out .= counterize_get_image_tag( $os_code, $os_name, $os_url );
		}
		$out .= $os_name;
		if( $os_ver )
		{
			$out .= " {$os_ver}";
		}
	}
	elseif( $pda_name )
	{
		if( $image )
		{
			$out .= counterize_get_image_tag( $pda_code, $pda_name, $pda_url );
		}
		$out .= $pda_name;
		if( $pda_ver )
		{
			$out .= " {$pda_ver}";
		}
	}
	return $out;
}

function counterize_windows_detect_os ( $ua )
{
	$os_name = 'Unknown';
	$os_code = 'unknown';
	$os_ver  = '';
	$os_url  = '';
	$pda_name= '';
	$pda_code= '';
	$pda_ver = '';
	$pda_url = '';

	if( preg_match( '/Windows/i', $ua ) || preg_match( '/Win32/i', $ua ) || preg_match( '/Win64/i', $ua ) )
	{
		$os_name = 'Windows';
		$os_code = 'windows';
		$os_url = 'http://www.microsoft.com/windows/';
	}

	if( preg_match( '/Windows 3\.1/i', $ua ) || preg_match( '/Win31/i', $ua ) )
	{
		$os_ver = '3.1';
	}
	elseif( preg_match( '/Windows 95/i', $ua ) || preg_match( '/Win95/i', $ua ) )
	{
		$os_ver = '95';
	}
	elseif( preg_match( '/Windows NT 5\.0/i', $ua ) || preg_match( '/Windows 2000/i', $ua ) )
	{
		$os_ver = '2000';
	}
	elseif( preg_match( '/Win 9x 4\.90/i', $ua ) || preg_match( '/Windows ME/i', $ua ) )
	{
		$os_ver = 'ME';
	}
	elseif( preg_match( '/Windows.98/i', $ua ) || preg_match( '/Win98/i', $ua ) )
	{
		$os_ver = '98';
	}
	elseif( preg_match( '/Windows NT 5\.1/i', $ua ) || preg_match( '/Windows XP/i', $ua ) )
	{
		$os_ver = 'XP';
		$os_url = 'http://windows.microsoft.com/en-US/windows/products/windows-xp';
	}
	elseif( preg_match( '/Windows NT 5\.2/i', $ua ) )
	{
		if( preg_match( '/(Win64|WOW64)/i', $ua ) )
		{
			$os_ver = 'XP 64 bit';
			$os_url = 'http://windows.microsoft.com/en-US/windows/products/windows-xp';
		}
		else
		{
			$os_ver = 'Server 2003';
			$os_url = 'http://technet.microsoft.com/en-us/windowsserver/bb512919.aspx';
		}
	}
	elseif( preg_match( '/Windows NT 6\.0/i', $ua ) || preg_match( '/Windows Vista/i', $ua ) )
	{
		$os_ver = 'Vista';
		if( preg_match( '/Win64/i', $ua ) || preg_match( '/WOW64/i', $ua ) )
		{
			$os_ver .= ' 64 bit';
		}
		$os_url = 'http://windows.microsoft.com/en-US/windows-vista/products/home';
	}
	elseif( preg_match( '/Windows NT 6\.1/i', $ua ) )
	{
		$os_ver = '7';
		if( preg_match( '/Win64/i', $ua ) || preg_match( '/WOW64/i', $ua ) )
		{
			$os_ver .= ' 64 bit';
		}
		$os_url = 'http://windows.microsoft.com/en-US/windows7/products/home';
	}
	elseif( preg_match( '/Windows NT 6\.2/i', $ua ) )
	{
		$os_ver = '8';
		if( preg_match( '/Win64/i', $ua ) || preg_match( '/WOW64/i', $ua ) )
		{
			$os_ver .= ' 64 bit';
		}
	}
	elseif( preg_match( '/Windows NT 4\.0/i', $ua ) || preg_match( '/WinNT4\.0/i', $ua ) )
	{
		$os_ver = 'NT 4.0';
	}
	elseif( preg_match( '/Windows NT/i', $ua ) || preg_match( '/WinNT/i', $ua ) )
	{
		$os_ver = 'NT';
	}
	elseif( preg_match( '/WindowsMobile/i', $ua ) )
	{
		list( $os_name, $os_code, $os_ver, $os_url, $pda_name, $pda_code, $pda_ver, $pda_url) = counterize_pda_detect_os( $ua );
		$os_name = 'Windows Mobile';
		$os_ver = '';
		$os_url = 'http://www.microsoft.com/windowsphone/';
	}
	elseif( preg_match( '/Windows CE/i', $ua ) )
	{
		list( $os_name, $os_code, $os_ver, $os_url, $pda_name, $pda_code, $pda_ver, $pda_url) = counterize_pda_detect_os( $ua );
		$os_ver = 'CE';
		if( preg_match( '/PPC/i', $ua ) )
		{
			$os_name = 'Microsoft PocketPC';
			$os_ver = 'CE';
		}
		if( preg_match( '/smartphone/i', $ua ) )
		{
			$os_name = 'Microsoft Smartphone';
			$os_ver = 'CE';
		}
	}
	return array( $os_name, $os_code, $os_ver, $os_url, $pda_name, $pda_code, $pda_ver, $pda_url );
}

function counterize_unix_detect_os ( $ua )
{
	$os_name = 'Unknown';
	$os_code = 'unknown';
	$os_ver  = '';
	$os_url  = '';

	$pda_name = '';
	$pda_code = '';
	$pda_ver = '';
	$pda_url = '';

	if( preg_match( '/Linux/i', $ua ) )
	{
		$os_name = 'Linux';
		$os_code = 'linux';
		$os_url  = 'http://en.wikipedia.org/wiki/Linux';
		if( preg_match( '/Android/i', $ua ) )
		{
			list( $os_name, $os_code, $os_ver, $os_url, $pda_name, $pda_code, $pda_ver, $pda_url ) = counterize_pda_detect_os( $ua );
		}
		elseif( preg_match( '/Mandrake/i', $ua ) )
		{
			$os_code = 'mandrake';
			$os_name = 'Mandrake Linux';
			$os_url = 'http://www.mandriva.com/';
		}
		elseif( preg_match( '/Mandriva/i', $ua ) )
		{
			$os_code = 'mandriva';
			$os_name = 'Mandriva Linux';
			$os_url = 'http://www.mandriva.com/';
		}
		elseif( preg_match( '/SuSE/i', $ua ) )
		{
			$os_code = 'suse';
			$os_name = 'SuSE Linux';
			$os_url = 'http://www.opensuse.org/';
		}
		elseif( preg_match( '/Novell/i', $ua ) )
		{
			$os_code = 'novell';
			$os_name = 'Novell Linux';
			$os_url = 'http://www.novell.com/linux/';
		}
		elseif( preg_match( '/Kubuntu/i', $ua ) )
		{
			$os_code = 'kubuntu';
			$os_name = 'Kubuntu Linux';
			if( preg_match( '#Kubuntu/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
			{
				$os_ver = $matches[1];
			}
			$os_url = 'http://www.kubuntu.org/';
		}
		elseif( preg_match( '/Xubuntu/i', $ua ) )
		{
			$os_code = 'xubuntu';
			$os_name = 'Xubuntu Linux';
			if( preg_match( '#Xubuntu/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
			{
				$os_ver = $matches[1];
			}
			$os_url = 'http://www.xubuntu.org/';
		}
		elseif( preg_match( '/Edubuntu/i', $ua ) )
		{
			$os_code = 'edubuntu';
			$os_name = 'Edubuntu Linux';
			if( preg_match( '#Edubuntu/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
			{
				$os_ver = $matches[1];
			}
			$os_url = 'http://www.edubuntu.org/';
		}
		elseif( preg_match( '/Ubuntu/i', $ua ) )
		{
			$os_code = 'ubuntu';
			$os_name = 'Ubuntu Linux';
			if( preg_match( '#Ubuntu/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
			{
				$os_ver = $matches[1];
			}
			$os_url = 'http://www.ubuntu.com/';
		}
		elseif( preg_match( '/Debian/i', $ua ) )
		{
			$os_code = 'debian';
			$os_name = 'Debian GNU/Linux';
			if( preg_match( '#Debian/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
			{
				$os_ver = $matches[1];
			}
			$os_url = 'http://www.debian.org/';
		}
		elseif( preg_match( '/Mint/i', $ua ) )
		{
			$os_code = 'mint';
			$os_name = 'Linux Mint';
			if( preg_match( '#Mint/([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
			{
				$os_ver = $matches[1];
			}
			$os_url = 'http://www.linuxmint.com/';
		}
		elseif( preg_match( '/Red ?Hat/i', $ua ) )
		{
			$os_code = 'redhat';
			$os_name = 'RedHat Linux';
			if( preg_match( '#Red ?Hat/([a-zA-Z0-9\.-]+)#i', $ua, $matches ) )
			{
				$os_ver = $matches[1];
			}
			$os_url = 'http://www.redhat.com/';
		}
		elseif( preg_match( '/Gentoo/i', $ua ) )
		{
			$os_code = 'gentoo';
			$os_name = 'Gentoo Linux';
			$os_url = 'http://www.gentoo.org/';
		}
		elseif( preg_match( '/CentOS/i', $ua ) )
		{
			$os_code = 'centos';
			$os_name = 'CentOS Linux';
			if( preg_match( '#CentOS/([a-zA-Z0-9\.-]+)#i', $ua, $matches ) )
			{
				$os_ver = $matches[1];
			}
			$os_url = 'http://www.centos.org/';
		}
		elseif( preg_match( '/Fedora/i', $ua ) )
		{
			$os_code = 'fedora';
			$os_name = 'Fedora Linux';
			if( preg_match( '#\.fc([0-9]+)#i', $ua, $matches ) )
			{
				$os_ver = $matches[1];
			}
			$os_url = 'http://fedoraproject.org/';
		}
		elseif( preg_match( '/MEPIS/i', $ua ) )
		{
			$os_name = 'MEPIS Linux';
			$os_code = 'mepis';
			$os_url = 'http://www.mepis.org/';
		}
		elseif( preg_match( '/Knoppix/i', $ua ) )
		{
			$os_name = 'Knoppix Linux';
			$os_code = 'knoppix';
			$os_url = 'http://www.knoppix.org/';
		}
		elseif( preg_match( '/Sabayon/i', $ua ) )
		{
			$os_name = 'Sabayon Linux';
			$os_code = 'sabayon';
			$os_url = 'http://www.sabayon.org/';
		}
		elseif( preg_match( '/Slackware/i', $ua ) )
		{
			$os_code = 'slackware';
			$os_name = 'Slackware Linux';
			$os_url = 'http://www.slackware.com/';
		}
		elseif( preg_match( '/Xandros/i', $ua ) )
		{
			$os_name = 'Xandros Linux';
			$os_code = 'xandros';
			$os_url = 'http://www.xandros.com/';
		}
		elseif( preg_match( '/Kanotix/i', $ua ) )
		{
			$os_name = 'Kanotix Linux';
			$os_code = 'kanotix';
			$os_url = 'http://www.kanotix.com/';
		}
		elseif( preg_match( '#Trisquel[/ ]([0-9\.]+)#i', $ua, $matches ) )
		{
			$os_name = 'Trisquel Linux';
			$os_code = 'trisquel';
			$os_ver = $matches[1];
			$os_url = 'http://trisquel.info/';
		}
		elseif( preg_match( '#Pardus/([0-9]+)#i', $ua, $matches ) )
		{
			$os_name = 'Pardus Linux';
			$os_code = 'pardus';
			$os_ver = $matches[1];
			$os_url = 'http://www.pardus.org.tr/';
		}
		elseif( preg_match( '#YellowDog/([a-z0-9\.\-]+)#i', $ua, $matches ) )
		{
			$os_name = 'Yellow Dog Linux';
			$os_code = 'yellowdoglinux';
			$os_ver = $matches[1];
			$os_url = 'http://www.yellowdoglinux.com/';
		}
		elseif( preg_match( '#PCLinuxOS/([a-z0-9\.\-]+)#i', $ua, $matches ) )
		{
			$os_name = 'PCLinuxOS';
			$os_code = 'pclinuxos';
			$os_ver = $matches[1];
			$os_url = 'http://www.pclinuxos.com/';
		}
		elseif( preg_match( '#Linux ([0-9\.]+)-ARCH#i', $ua ) )
		{
			$os_name = 'Arch Linux';
			$os_code = 'archlinux';
			$os_url = 'http://www.archlinux.org/';
		}
		elseif( preg_match( '#Joli OS/([0-9\.]+)#i', $ua, $matches ) )
		{
			$os_name = 'Joli OS';
			$os_code = 'jolios';
			$os_ver = $matches[1];
			$os_url = 'http://www.jolicloud.com/';
		}
		elseif( preg_match( '#hpwOS/([0-9\.]+)#i', $ua, $matches ) )
		{
			$os_name = 'WebOS';
			$os_code = 'webos';
			$os_ver = $matches[1];
			$os_url = 'http://en.wikipedia.org/wiki/WebOS';
		}
		elseif( preg_match( '#SliTaz GNU/Linux#i', $ua ) )
		{
			$os_name = 'SliTaz GNU/Linux';
			$os_code = 'slitaz';
			$os_url = 'http://www.slitaz.org/';
		}
		elseif( preg_match( '#Ultimate Edition[/ ]([0-9\.]+)#i', $ua, $matches ) )
		{
			$os_name = 'Ultimate Edition Linux';
			$os_code = 'ultimate';
			$os_ver = $matches[1];
			$os_url = 'http://distrowatch.com/table.php?distribution=ultimate';
		}
	}
	elseif( preg_match( '/FreeBSD/i', $ua ) )
	{
		$os_name = 'FreeBSD';
		$os_code = 'freebsd';
		if( preg_match( '#FreeBSD[/ ]([0-9\.]+)#i', $ua, $matches ) )
		{
			$os_ver = $matches[1];
		}
		$os_url = 'http://www.freebsd.org/';
	}
	elseif( preg_match( '/NetBSD/i', $ua ) )
	{
		$os_name = 'NetBSD';
		$os_code = 'netbsd';
		$os_url = 'http://www.netbsd.org/';
	}
	elseif( preg_match( '/OpenBSD/i', $ua ) )
	{
		$os_name = 'OpenBSD';
		$os_code = 'openbsd';
		if( preg_match( '#OpenBSD[/ ]([0-9\.]+)#i', $ua, $matches ) )
		{
			$os_ver = $matches[1];
		}
		$os_url = 'http://www.openbsd.org/';
	}
	elseif( preg_match( '/Darwin/i', $ua ) )
	{
		$os_code = 'darwin';
		$os_name = 'Darwin';
		if( preg_match( '#Darwin[ /]?([a-zA-Z0-9\.]+)#i', $ua, $matches ) )
		{
			$os_ver = $matches[1];
		}
		$os_url = 'http://en.wikipedia.org/wiki/Darwin_(operating_system)';
	}
	elseif( preg_match( '/IRIX/i', $ua ) )
	{
		$os_name = 'SGI IRIX';
		$os_code = 'sgi';
		$os_url = 'http://www.sgi.com/products/software/irix/';
	}
	elseif( preg_match( '/SunOS/i', $ua ) )
	{
		$os_name = 'Solaris';
		$os_code = 'sun';
		if( preg_match( '#SunOS[ /]([0-9\.]+)#i', $ua, $matches ) )
		{
			$os_ver = $matches[1];
		}
		$os_url = 'http://www.oracle.com/us/solaris/index.html';
	}
	elseif( preg_match( '/iPhone OS ([0-9\._]+)/i', $ua, $matches ) )
	{
		$os_name = 'iPhone OS';
		$os_code = 'iphone';
		$os_ver = $matches[1];
		$os_url = 'http://www.apple.com/iphone/';
	}
	elseif( preg_match( '/Mac OS X/i', $ua ) )
	{
		$os_name = 'Mac OS';
		$os_code = 'macos';
		$os_ver = 'X';
		$os_url = 'http://www.apple.com/macosx/';
	}
	elseif( preg_match( '/Star-Blade OS/i', $ua ) )
	{
		$os_name = 'Star-Blade OS';
		$os_code = 'starbladeos';
		$os_url = 'http://linkstechblog.com/';
	}
	elseif( preg_match( '/Amiga/i', $ua ) )
	{
		$os_name = 'AmigaOS';
		$os_code = 'amigaos';
		if( preg_match( '#AmigaOS[ ]?([0-9\.]+)#i', $ua, $matches ) )
		{
			$os_ver = $matches[1];
		}
		$os_url = 'http://hyperion-entertainment.biz/';
	}
	elseif( preg_match( '/Macintosh/i', $ua ) )
	{
		$os_name = 'Mac OS';
		$os_code = 'macos';
		$os_url = 'http://www.apple.com/macosx/';
	}
	elseif( preg_match( '/GNU/i', $ua ) )
	{
		$os_name = 'GNU';
		$os_code = 'gnu';
		$os_url = 'http://www.gnu.org/';
	}
	elseif( preg_match( '/Inferno/i', $ua ) )
	{
		$os_name = 'Inferno';
		$os_code = 'inferno';
		$os_url = 'http://www.vitanuova.com/inferno/';
	}
	elseif( preg_match( '/Unix/i', $ua ) )
	{
		$os_name = 'UNIX';
		$os_code = 'unix';
		$os_url = 'http://en.wikipedia.org/wiki/Unix';
	}

	if( preg_match( '/amd64/i', $ua ) || preg_match( '/x86_64/i', $ua ) )
	{
		$os_ver .= ( $os_ver == '' ? '' : ' ' ) . '64 bit';
	}
	if( preg_match( '/ppc/i', $ua ) )
	{
		$os_ver .= ( $os_ver == '' ? '' : ' ' ) . 'PowerPC';
	}
	if( preg_match( '/arm/i', $ua ) )
	{
		$os_ver .= ( $os_ver == '' ? '' : ' ' ) . 'ARM';
	}

	return array( $os_name, $os_code, $os_ver, $os_url, $pda_name, $pda_code, $pda_ver, $pda_url );
}

function counterize_pda_detect_os ( $ua )
{
	$os_name = 'Unknown';
	$os_code = 'unknown';
	$os_ver  = '';
	$os_url  = '';
	$pda_name = '';
	$pda_code = '';
	$pda_ver  = '';
	$pda_url  = '';
	if( preg_match( '/Android/i', $ua ) )
	{
		$os_name = 'Android';
		$os_code = 'android';
		if( preg_match( '#Android[ /]([a-zA-Z0-9\.-]+)#i', $ua, $matches ) )
		{
			$os_ver = $matches[1];
		}
		$os_url = 'http://www.android.com/';
	}
	if( preg_match( '/PalmOS/i', $ua ) )
	{
		$os_name = 'Palm OS';
		$os_code = 'palm';
		$os_url = 'http://www.palm.com/';
	}
	elseif( preg_match( '/Windows CE/i', $ua ) )
	{
		$os_name = 'Windows CE';
		$os_code = 'windows';
		$os_url = 'http://en.wikipedia.org/wiki/Windows_CE';
	}
	elseif( preg_match( '/WindowsMobile/i', $ua ) )
	{
		$os_name = 'Windows Mobile';
		$os_code = 'windows';
		$os_url = 'http://microsoft.com/windowsmobile/';
	}
	elseif( preg_match( '/QtEmbedded/i', $ua ) )
	{
		$os_name = 'Qtopia';
		$os_code = 'linux';
		$os_url = 'http://qpe.sourceforge.net/';
	}
	elseif( preg_match( '/Zaurus/i', $ua ) )
	{
		$os_name = 'Linux';
		$os_code = 'linux';
		$os_url = 'http://www.openzaurus.org/';
	}
	elseif( preg_match( '/Symbian/i', $ua ) || preg_match( '#Symbian/([a-zA-Z0-9]+)#i', $ua, $matches ) )
	{
		$os_name = 'Symbian OS';
		$os_code = 'symbian';
		if( isset( $matches[1] ) )
		{
			$os_ver = $matches[1];
		}
		$os_url = 'http://symbian.nokia.com/';
	}

	if( preg_match( '#PalmOS/sony/model#i', $ua ) )
	{
		$pda_name = 'Sony Clie';
		$pda_code = 'sony';
		$pda_url = 'http://en.wikipedia.org/wiki/CLIÉ';
	}
	elseif( preg_match( '/Zaurus ([a-zA-Z0-9\.]+)/i', $ua, $matches ) )
	{
		$pda_name = 'Sharp Zaurus ' . $matches[1];
		$pda_code = 'zaurus';
		$pda_ver = $matches[1];
		$pda_url = 'http://en.wikipedia.org/wiki/Sharp_Zaurus';
	}
	elseif( preg_match( '/Series ([0-9]+)/i', $ua, $matches ) )
	{
		$pda_name = 'Series';
		$pda_code = 'nokia';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.nokia.com/';
	}
	elseif( preg_match( '/Nokia ([0-9]+)/i', $ua, $matches ) || preg_match( '/Nokia([a-zA-Z0-9]+)/i', $ua, $matches ) )
	{
		$pda_name = 'Nokia';
		$pda_code = 'nokia';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.nokia.com/';
	}
	elseif( preg_match( '/SIE-([a-zA-Z0-9]+)/i', $ua, $matches ) )
	{
		$pda_name = 'Siemens';
		$pda_code = 'siemens';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.siemens.com/';
	}
	elseif( preg_match( '/dopod([a-zA-Z0-9]+)/i', $ua, $matches ) )
	{
		$pda_name = 'Dopod';
		$pda_code = 'dopod';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.dopod.com/';
	}
	elseif( preg_match( '/o2 xda ([a-zA-Z0-9 ]+);/i', $ua, $matches ) )
	{
		$pda_name = 'O2 XDA';
		$pda_code = 'o2';
		$pda_ver = $matches[1];
		$pda_url = 'http://xda.o2.co.uk/';
	}
	elseif( preg_match( '/SEC-([a-zA-Z0-9]+)/i', $ua, $matches ) )
	{
		$pda_name = 'Samsung';
		$pda_code = 'samsung';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.samsung.com/';
	}
	elseif( preg_match( '/SonyEricsson ?([a-zA-Z0-9]+)/i', $ua, $matches ) )
	{
		$pda_name = 'SonyEricsson';
		$pda_code = 'sonyericsson';
		$pda_ver = $matches[1];
		$pda_url = 'http://www.sonyericsson.com/';
	}
	elseif( preg_match( '/Wii/i', $ua, $matches ) )
	{
		$pda_name = 'Nintendo Wii';
		$pda_code = 'wii';
		$pda_url = 'http://www.nintendo.com/wii';
	}
	return array( $os_name, $os_code, $os_ver, $os_url, $pda_name, $pda_code, $pda_ver, $pda_url );
}

?>