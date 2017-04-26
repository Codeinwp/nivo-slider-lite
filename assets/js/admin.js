jQuery( document ).ready(function($){

	/**
	* Nivo Themes
	*
	*
	**/

	$( 'select[name="nivo_settings[theme]"]' ).change(function(){
		nivo_theme_thumbs_enabled();
	});

	nivo_theme_thumbs_enabled();

	function nivo_theme_thumbs_enabled(){
		var current_theme = $( 'select[name="nivo_settings[theme]"] option:selected' ).val();
		var controlNavThumbs = $( 'input[name="nivo_settings[controlNavThumbs]"]' );

		$( '.nivo_thumb_nav,.nivo_thumb_size' ).show();
		if (nivo_plugin.themes[current_theme] != undefined) {
			if (nivo_plugin.themes[current_theme].theme_details.SupportsThumbs == 'false') {
				controlNavThumbs.attr( 'checked', false );
				$( '.nivo_thumb_nav,.nivo_thumb_size' ).hide();
			}
		}

	}

});
