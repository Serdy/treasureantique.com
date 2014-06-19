function insertShortcode() {
	var type = $('input[name=list_type]:checked', '#shortcode-form').val();
	var shortcode_val = '[auction-list ';
	if ( type ) {
		shortcode_val += ' type="' + type + '"';
	}
	shortcode_val += ']';
	if ( window.tinyMCE ) {
		window.tinyMCE.activeEditor.execCommand( 'mceInsertContent', false, shortcode_val );
		tinyMCEPopup.editor.execCommand( 'mceRepaint' );
		tinyMCEPopup.close();
	}
	return false;
}