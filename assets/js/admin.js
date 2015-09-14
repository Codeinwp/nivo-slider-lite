jQuery(document).ready(function($){

    function get_image_source_setting() {
		return $('select[name="' + dev7plugin.labels.post_meta_key + '[' + dev7plugin.labels.source_name + ']"] option:selected').val();
	}

    // Edit screen Images
    if (dev7plugin.post_id != '') {
        load_image_source(); // Initial load
    }

    $('select[name="' + dev7plugin.labels.post_meta_key + '[' + dev7plugin.labels.source_name + ']"]').change(function(){
        load_image_source();
		display_params();
    });

	$('select[name="' + dev7plugin.labels.post_meta_key + '[' + dev7plugin.labels.type_name + '_gallery]"]').change(function(){
		load_image_source();
	});

	$('select[name="' + dev7plugin.labels.post_meta_key + '[' + dev7plugin.labels.type_name + '_category]"]').change(function(){
		load_image_source();
	});

	function escapeHTML (unsafe_str) {
		return unsafe_str
			.replace(/&/g, '&amp;')
			.replace(/</g, '&lt;')
			.replace(/>/g, '&gt;')
			.replace(/\"/g, '&quot;')
			.replace(/\'/g, '&#39;'); // '&apos;' is not valid HTML 4
	}

    function load_images( ){
        var image_source = $('select[name="' + dev7plugin.labels.post_meta_key + '[' + dev7plugin.labels.source_name + ']"] option:selected').val();
        var image_method = $('select[name="' + dev7plugin.labels.post_meta_key + '[' + image_source + '_type]"] option:selected').val();
        var image_param = $('#' + image_source + '_' + image_method).val();

        var number_images = $('input[name="' + dev7plugin.labels.post_meta_key + '[number_images]"]').val();

        if (image_param != '') {
            $('#' + dev7plugin.labels.post_type + '-images').html('<li class="loading"><div class="spinner"></div></li>');
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: { action: dev7plugin.labels.post_type + '_load_images',
                        id: dev7plugin.post_id,
                        nonce: dev7plugin.nonce,
                        source: image_source,
                        gallery:$('select[name="' + dev7plugin.labels.post_meta_key + '[' + dev7plugin.labels.type_name + '_gallery]"] option:selected').val(),
                        category:$('select[name="' + dev7plugin.labels.post_meta_key + '[' + dev7plugin.labels.type_name + '_category]"] option:selected').val(),
                       	method:image_method,
                        param:image_param,
                        number_images:number_images
                },
                success: function(response){
                    if(response.error){
                        $('#' + dev7plugin.labels.post_type +'-images').html('');
                        alert(response.message);
                    } else {
                        $('#' + dev7plugin.labels.post_type +'-images').html('');
                        for(var i in response.images){
                            var image = response.images[i];
                            var output = '<li id="attachment-' + image.id + '">' +
                                '<img src="' + image.image_src + '" alt="' + escapeHTML( image.post_title ) + '" title="' + escapeHTML( image.post_title ) + '" class="attachment-thumbnail" />' +
                                '</li>';
                            $('#' + dev7plugin.labels.post_type +'-images').append(output);
                        }
                    }
                },
                error: function(response, status, error){
                    $('#' + dev7plugin.labels.post_type +'-images').html('<li class="loading">Error '+ error.replace(/(<([^>]+)>)/ig,"") +'</li>');
                    //alert('Error: ' + error.replace(/(<([^>]+)>)/ig,""));
                }
            });
        }
    }

    function load_image_source() {
        var selected_source = get_image_source_setting();
		$('.dev7_type').hide();
        if( selected_source != dev7plugin.labels.manual_name){
            $( '#dev7_type_' + selected_source ).show();
            $('.manual.description').hide();
            $('.dev7_non_manual').show();
			$('#manual_images_upload').hide();
        } else {
            $('.manual.description').show();
            $('.dev7_non_manual').hide();
			$('#manual_images_upload').show();
        }

        if( selected_source == 'category' || selected_source == 'sticky' || selected_source == 'custom'){
            $('tr.dev7_captions').show();
        } else {
            $('tr.dev7_captions').hide();
        }

		if( selected_source == 'category' 	||
			selected_source == 'gallery' 	||
			selected_source == 'sticky' 	||
			selected_source == 'custom'		||
			selected_source == dev7plugin.labels.manual_name
			){
			$('tr.wp-image-size').show();
		} else {
			$('tr.wp-image-size').hide();
		}

        load_images();
    }

    // Dependant Select visibilty
    $( '#' + dev7plugin.labels.post_type + '-settings select' ).each( function( index, element ){
		dependant_visible( this );
    });

    $('#' + dev7plugin.labels.post_type + '-settings').on('change', 'select', function(){
		dependant_visible( this );
    });

    $( '#' + dev7plugin.labels.post_type + '-settings input:checkbox' ).each( function( index, element ){
		dependant_visible( this );
    });

    $('#' + dev7plugin.labels.post_type + '-settings').on('change', 'input:checkbox', function(){
		dependant_visible( this );
    });

    function dependant_visible( element ) {
		var id = $(element).attr( 'id' );
        if ($('tr.parent-' + id).length > 0) {

			if ( $(element).is(':checkbox') ) {
                var value = ( $(element).is(':checked') ) ? 'on' : 'off';
            } else if ( $(element).is('select') ) {
                var value = $('option:selected', element).val();
            }
            $('tr.parent-' + id).hide();
            $('tr.parent-' + id + '.' + value).show();
        }
    }

    // Reload images
    $('#' + dev7plugin.labels.post_type + '-settings').on('change', 'input.reload', function(){
        load_images();
    });

    $('#' + dev7plugin.labels.post_type + '-settings').on('change', 'select.reload', function(){
        load_images();
    });


    /**
     * MMP integration
     *
     */

    function display_params() {
        var source = $('select[name="' + dev7plugin.labels.post_meta_key + '[' + dev7plugin.labels.source_name + ']"] option:selected').val();
		var method = $('select[name="' + dev7plugin.labels.post_meta_key + '[' + source + '_type]"] option:selected').val();
		$('.image_source_param').hide();
		if(typeof method != 'undefined') {
			$('#' + source + '_param_' + method).show();
		}
    }

    display_params();

    $('#' + dev7plugin.labels.post_type + '-settings').on('change', 'select.image_source_type', function(){
        display_params();
        load_images();
    });

    $('#' + dev7plugin.labels.post_type + '-settings').on('change', '.image_source_param', function(){
        load_images();
    });

    $('.image_source_param').keydown(function(e){
        if (e.which == 13) {
            e.preventDefault();
            load_images();
        }
    });

    $('input.reload').keydown(function(e){
        if (e.which == 13) {
            e.preventDefault();
            load_images();
        }
    });

	$('.reattach-images').on('click', function(e){
		e.preventDefault();
		var post_id = $(this).attr('data-post');
		$spinner = $(this).next('.spinner');
		if (post_id != '') {
			$spinner.show();
			$(this).hide();
			$.ajax({
				url: ajaxurl,
				type: 'POST',
				dataType: 'json',
				data: { action:         dev7plugin.labels.post_type + '_reattach_images',
					nonce:          dev7plugin.nonce,
					post_id:    post_id
				},
				success: function(response){
					if(response.error){
						alert(response.message);
						$spinner.hide();
					} else {
						// images attached
						window.location = response.redirect;
					}
				},
				error: function(response, status, error){
					alert('Error: ' + error.replace(/(<([^>]+)>)/ig,""));
					$spinner.hide();
				}
			});
		}
	});


    /**
     * License Calls
     *
     *
     **/

    var license_input = dev7plugin.labels.options_key + '[license_key]';
    var license_status = dev7plugin.labels.options_key + '[license_status]';
    $('#activate-license').on('click', function(){
        var license_key = $('input[name="' + license_input + '"]').val();
        if (license_key != '') {
            $('#dev7license .spinner').show();
            document.getElementById("activate-license").disabled = true;
            $.ajax({
                url: ajaxurl,
                type: 'POST',
                dataType: 'json',
                data: { action:         dev7plugin.labels.post_type + '_activate_license',
                        nonce:          dev7plugin.nonce,
                        license_key:    license_key
                },
                success: function(response){
                    if(response.error){
                        alert(response.message);
                        $('#dev7license .spinner').hide();
                        document.getElementById("activate-license").disabled = false;
                    } else {
                        $('input[name="' + license_status + '"]').val(response.license_status);
                        if (response.license_status == 'valid') window.location = response.redirect;
                        else {
                            alert('License ' + response.license_status);
                            $('#dev7license .spinner').hide();
                            document.getElementById("activate-license").disabled = false;
                        }
                    }
                },
                error: function(response, status, error){
                    alert('Error: ' + error.replace(/(<([^>]+)>)/ig,""));
                    $('#dev7license .spinner').hide();
                    document.getElementById("activate-license").disabled = false;
                }
            });
        }
    });

    $('#deactivate-license').on('click', function(){
        $('#dev7license .spinner').show();
        document.getElementById("deactivate-license").disabled = true;
        $.ajax({
            url: ajaxurl,
            type: 'POST',
            dataType: 'json',
            data: { action: dev7plugin.labels.post_type + '_deactivate_license',
                    nonce:  dev7plugin.nonce
            },
            success: function(response){
                if(response.error){
                    alert(response.message);
                    $('#dev7license .spinner').hide();
                    document.getElementById("deactivate-license").disabled = false;
                } else {
                    if (response.license_status == 'deactivated') {
                        $('input[name="' + license_status + '"]').val('');
                    }
                    window.location = response.redirect;
                }
            },
            error: function(response, status, error){
                alert('Error: ' + error.replace(/(<([^>]+)>)/ig,""));
                $('#dev7license .spinner').hide();
                document.getElementById("deactivate-license").disabled = false;
            }
        });

    });

});