jQuery(document).ready(function($){

    var proFeatureDesc = $('.manual.description' ).find('.dev7-pro-feature');
    $('.manual.description' ).html('').append(proFeatureDesc);

    /**
    * Nivo Themes
    *
    *
    **/

    $('select[name="nivo_settings[theme]"]').change(function(){
        nivo_theme_thumbs_enabled();
    });

    nivo_theme_thumbs_enabled();

    function nivo_theme_thumbs_enabled(){
        var current_theme = $('select[name="nivo_settings[theme]"] option:selected').val();
        var controlNavThumbs = $('input[name="nivo_settings[controlNavThumbs]"]');

        $('.dev7_thumb_nav,.dev7_thumb_size').show();
        if(dev7plugin.themes[current_theme] != undefined){
            if(dev7plugin.themes[current_theme].theme_details.SupportsThumbs == 'false'){
                controlNavThumbs.attr('checked', false);
                $('.dev7_thumb_nav,.dev7_thumb_size').hide();
            }
        }

    }


});