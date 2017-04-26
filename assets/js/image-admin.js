jQuery( document ).ready(function($){

	if (typeof wp === 'undefined') { return;
	}

	if (typeof wp.media === 'undefined') { return;
	}

	var frame,
		images = wp.media.view.l10n[nivo_plugin.labels.post_type + 'Images'],
		selection = getImages( images );

	if (typeof wp.media.view.Toolbar.UberMedia !== 'undefined') {

		var UberImage = Backbone.Model.extend({
		});

		var UberImages = Backbone.Collection.extend({
			model: UberImage
		});

		var SelectedImages = Backbone.Collection.extend({
			model: UberImage
		});

		wp.media.view.Toolbar.UberMedia = wp.media.view.Toolbar.UberMedia.extend({
			className: 'media-toolbar mmp-toolbar',
			initialize: function() {
				_.defaults( this.options, {
					event: 'mmp_event_insert',
					close: false,
					items: {
						mmp_event_insert: {
							text: wp.media.view.l10n.ubermediaButton,
							style: 'primary',
							id: 'uber-button',
							priority: 80,
							requires: false,
							click: this.insertAction
						}
					}
				});

				wp.media.view.Toolbar.prototype.initialize.apply( this, arguments );
			},

			insertAction: function(){
				if ($( '#uber-button' ).is( '[disabled=disabled]' )) { return;
				}

				$( 'div.ubermedia' ).addClass( 'uber-media-overlay' );
				$( '#method' ).attr( "disabled", 'disabled' );
				$( '#param' ).attr( "disabled", 'disabled' );
				$( '#pagination' ).attr( "disabled", 'disabled' );

				$( '#uber-button' ).attr( "disabled", 'disabled' );
				$( "#uber-button" ).text( 'Adding...' );

				var selectedimages = this.controller.state().props.get( 'custom_data' );
				var selection = new SelectedImages( selectedimages.models );

				var that = this,
					count = 0,
					jqHXRs = [],
					new_ids = '';

				selection.each(function(model){
					count++;

					var modelAttr = model.attributes;
					var fields = $.param( modelAttr );
					data = 'action=uber_pre_insert&nonce=' + uber_media.nonce + '&imgsrc=' + encodeURI( model.get( 'data-full' ) ) + '&postid=' + $( '#post_ID' ).val() + '&nivo_plugin=' + nivo_plugin.labels.post_type + '&' + fields;

					var full = model.get( 'data-full' );
					var imgstr;

					jqHXRs.push(
						$.post(ajaxurl, data,
							function(data){
								if (data.message == 'success') {
									new_ids += data.attachment_id + ',';
								}
							}
						, 'json')
					);
				});

				$.when.apply( this, jqHXRs ).done(function(){

					that.controller.state().props.set( 'custom_data', '' );
					that.controller.state().props.set( 'selected_id', '' );
					that.controller.state().props.set( 'selected_image', '' );

					new_ids = new_ids.substring( 0, new_ids.length - 1 );
					var new_selection = getImages( new_ids );

					var checkExist = setInterval(function() {
						if (new_selection.length) {
							var controller = that.controller,
								state = controller.state(),
								edit = controller.state( 'gallery-edit' );
							edit.get( 'library' ).add( new_selection.models );
							state.trigger( 'reset' );
							controller.setState( 'gallery-edit' );
							clearInterval( checkExist );
						}
					}, 100);

				});
			}

		});
	}

	$( '#manual_images_upload' ).on('click', function(e) {
		e.preventDefault();

		var media = wp.media;
		selection = getImages( $( '#_manual_image_ids' ).val() );
		// Set options for 1st frame render
		var options = {
			state: 'gallery-edit',
			frame: 'post',
			selection: selection
		};

		// Set Links for image meta
		$.ajax({
			url: ajaxurl,
			type: 'POST',
			dataType: 'json',
			data: { action: nivo_plugin.labels.post_type + '_get_meta_link', ids: images,
				nonce: nivo_plugin.nonce },
			success: function(response){
				$.each(response.metalink, function(index, value) {
					selection.get( index ).set( 'metalink', value );
				});
			},
			error: function(response, status, error){
				alert( 'Error: ' + error.replace( /(<([^>]+)>)/ig,"" ) );
			}
		});

		overrideAttachmentDetails();

		frame = wp.media( options ).open();

		// Tweak views
		frame.menu.get( 'view' ).unset( 'cancel' );
		frame.menu.get( 'view' ).unset( 'separateCancel' );

		frame.content.get( 'view' ).sidebar.unset( 'gallery' ); // Hide Gallery Settings in sidebar

		// When we are editing a gallery
		overrideGalleryInsert();
		frame.on( 'toolbar:render:gallery-edit', function() {
			overrideGalleryInsert();
		});

		frame.on( 'content:render:browse', function( browser ) {
			if ( ! browser ) { return;
			}
			// Hide Gallery Settings in sidebar
			browser.sidebar.on('ready', function(){
				browser.sidebar.unset( 'gallery' );
			});
			// Hide filter/search as they don't work
			browser.toolbar.on('ready', function(){
				if (browser.toolbar.controller._state == 'gallery-library') {
					browser.toolbar.$el.hide();
				}
			});
		});

		// Override Attachment Details
		function overrideAttachmentDetails() {

			media.view.Attachment.Details = media.view.Attachment.Details.extend({
				tagName:   'div',
				className: 'attachment-details',
				template:  media.template( 'attachment-details' ),

				events: {
					'change [data-setting]':          'updateSetting',
					'change [data-setting] input':    'updateSetting',
					'change [data-setting] select':   'updateSetting',
					'change [data-setting] textarea': 'updateSetting',
					'change [data-customsetting] input':    'updateCustomSetting',
					'click .delete-attachment':       'deleteAttachment',
					'click .edit-attachment':         'editAttachment',
					'click .refresh-attachment':      'refreshAttachment'
				},

				initialize: function() {
					this.focusManager = new media.view.FocusManager({
						el: this.el
					});

					media.view.Attachment.prototype.initialize.apply( this, arguments );
				},

				render: function() {
					media.view.Attachment.prototype.render.apply( this, arguments );
					this.focusManager.focus();
					return this;
				},

				deleteAttachment: function( event ) {
					event.preventDefault();

					if ( confirm( l10n.warnDelete ) ) {
						this.model.destroy();
					}
				},

				editAttachment: function( event ) {
					this.$el.addClass( 'needs-refresh' );
				},

				refreshAttachment: function( event ) {
					this.$el.removeClass( 'needs-refresh' );
					event.preventDefault();
					this.model.fetch();
				},

				updateCustomSetting: function( event ) {

					var $setting = $( event.target ).closest( '[data-customsetting]' ),
						setting, value;

					if ( ! $setting.length ) {
						return;
					}

					setting = $setting.data( 'customsetting' );
					value   = event.target.value;

					if ( this.model.get( setting ) !== value ) {
						this.model.set( setting, value );
						$.ajax({
							url: ajaxurl,
							type: 'POST',
							dataType: 'json',
							data: { action: nivo_plugin.labels.post_type + '_set_meta_link',
								id:this.model.id,
								metalink: value,
								nonce: nivo_plugin.nonce },
							success: function(response){
							},
							error: function(response, status, error){
								alert( 'Error: ' + error.replace( /(<([^>]+)>)/ig,"" ) );
							}
						});

					}

				}

			});

		}

		// Override insert button
		function overrideGalleryInsert() {
			var editing = frame.state().get( 'editing' );

			frame.toolbar.get( 'view' ).set({
				insert: {
					style: 'primary',
					text: editing ? wp.media.view.l10n.updateGallery : wp.media.view.l10n.insertGallery,

					click: function() {
						var models = frame.state().get( 'library' ),
							ids = '';

						models.each( function( attachment ) {
							ids += attachment.id + ','
						});
						ids = ids.substring( 0, ids.length - 1 );

						this.el.innerHTML = 'Saving'; //'<?php _e("Saving...", "zilla"); ?>';

						$( '#_manual_image_ids' ).val( ids );
						frame.close();

						$( '#' + nivo_plugin.labels.post_type + '-images' ).html( '<li class="loading"><div class="spinner"></div></li>' );

						$.ajax({
							url: ajaxurl,
							type: 'POST',
							dataType: 'json',
							data: { action: nivo_plugin.labels.post_type + '_load_images',
								id:nivo_plugin.post_id,
								source: nivo_plugin.labels.manual_name,
								image_ids: ids,
								nonce: nivo_plugin.nonce },
							success: function(response){
								if (response.error) {
									alert( response.message );
								} else {
									if (response.images) {
										$( '#' + nivo_plugin.labels.post_type + '-images' ).html( '' );
										for (var i in response.images) {
											var image = response.images[i];
											var output = '<li id="attachment-' + image.id + '">' +
												'<img src="' + image.image_src + '" alt="' + image.post_title + '" title="' + image.post_title + '" class="attachment-thumbnail" />';

											$( '#' + nivo_plugin.labels.post_type + '-images' ).append( output );
										}
									}
								}
							},
							error: function(response, status, error){
								alert( 'Error: ' + error.replace( /(<([^>]+)>)/ig,"" ) );
							}
						});

					}
				}
			});
		}

	});

	// Load images
	function getImages(images) {
		if ( ! images) { images = $( '#_manual_image_ids' ).val();
		}

		if ( images ) {
			var shortcode = new wp.shortcode({
				tag:    'gallery',
				attrs:   { ids: images },
				type:   'single'
			});

			var attachments = wp.media.gallery.attachments( shortcode );

			var selection = new wp.media.model.Selection( attachments.models, {
				props:    attachments.props.toJSON(),
				multiple: true
			});

			selection.gallery = attachments.gallery;

			// Fetch the query's attachments, and then break ties from the
			// query to allow for sorting.
			selection.more().done( function() {
				// Break ties with the query.
				selection.props.set( { query: false } );
				selection.unmirror();
				selection.props.unset( 'orderby' );
			});

			return selection;
		}

		return false;
	}

});
