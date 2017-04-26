(function() {
	tinymce.create('tinymce.plugins.nivoslider', {
		init : function( editor ) {
			editor.addButton('nivoslider', function() {
				var galleries = nivoslider.galleries;
				galleries = jQuery.parseJSON( galleries );

				var items = []
				for (var i in galleries) {
					items.push({
						text: galleries[i].name,
						value: galleries[i].id
					});
				}

				return {
					type: 'listbox',
					text:  'Nivo Sliders',
					values: items,
					fixedWidth: true,
					onselect: function(v) {
						if (v != '') {
							editor.insertContent( '[nivoslider id="' + this.value() + '"]' );
						}
						return false;
					}
				};
			});
		},
		createControl : function(n, cm) {
			switch (n) {
				case 'nivoslider':
					var mlb = cm.createListBox( 'nivoslider' , {
						title : 'Nivo Sliders',
						onselect : function(v) {
							if (v != '') {
								tinyMCE.execCommand( 'mceInsertContent', false, '[nivoslider id="' + v + '"]' );
							}
							return false;
						}
					});
					var galleries = nivoslider.galleries;
					galleries = jQuery.parseJSON( galleries );
					for (var i in galleries) {
						mlb.add( galleries[i].name, galleries[i].id );
					}
					return mlb;
			}
			return null;
		},
		getInfo : function() {
			return {
				longname : 'Nivo Slider Shortcode',
				author : 'ThemeIsle',
				authorurl : 'http://www.themeisle.com',
				infourl : 'http://www.themeisle.com/plugins/nivo-slider',
				version : '3.0'
			};
		}
	});
	tinymce.PluginManager.add( 'nivoslider', tinymce.plugins.nivoslider );
})();
