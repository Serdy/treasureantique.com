jQuery(document).ready(function($) {

    tinymce.create('tinymce.plugins.wpse72394_plugin', {
        init : function(ed, url) {
                // Register command for when button is clicked
                ed.addCommand('wpse72394_insert_shortcode', function() {
                    //selected = tinyMCE.activeEditor.selection.getContent();
                    /*
                    if( selected ){
                        //If text is selected when button is clicked
                        //Wrap shortcode around it.
                        content =  '[shortcode]'+selected+'[/shortcode]';
                    }else{
                        content =  '[shortcode]';
                    }*/
                    var content = "[single-auction]";
                    tinymce.execCommand('mceInsertContent', false, content);
                });
                
                ed.addCommand('wpse72395_insert_shortcode', function() {
                    //selected = tinyMCE.activeEditor.selection.getContent();
                    /*
                    if( selected ){
                        //If text is selected when button is clicked
                        //Wrap shortcode around it.
                        content =  '[shortcode]'+selected+'[/shortcode]';
                    }else{
                        content =  '[shortcode]';
                    }*/
                	
                    ed.windowManager.open( {
                        file : url + '../../../views/popup.php',
                        width : 360 + ed.getLang( 'example.delta_width', 0 ),
                        height : 120 + ed.getLang( 'example.delta_height', 0 ),
                        inline : 1
                    }, {
                        plugin_url : url // Plugin absolute URL
                        //some_custom_arg : 'custom arg' // Custom argument
                    });
                    
                    //var content = "[auction-list type='']";
                    //tinymce.execCommand('mceInsertContent', false, content);
                });
                ed.addCommand('wpse72396_insert_shortcode', function() {
                    var content = "[auction-search]";
                    tinymce.execCommand('mceInsertContent', false, content);
                });
            // Register buttons - trigger above command when clicked
            ed.addButton('wpse72394_button', {title : 'Insert single auction', cmd : 'wpse72394_insert_shortcode', image: url + '/../images/icon.20x20.png' });
            ed.addButton('wpse72395_button', {title : 'Insert auction list', cmd : 'wpse72395_insert_shortcode', image: url + '/../images/auction.list.png' });
          //  ed.addButton('wpse72396_button', {title : 'Insert auction search', cmd : 'wpse72396_insert_shortcode', image: url + '/../images/auction.search.png' });
        }
    });

    // Register our TinyMCE plugin
    // first parameter is the button ID1
    // second parameter must match the first parameter of the tinymce.create() function above
    tinymce.PluginManager.add('wpse72394_button', tinymce.plugins.wpse72394_plugin);
});