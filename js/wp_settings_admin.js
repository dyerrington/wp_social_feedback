jQuery(function() {
    console.log('loaded!');
    
    var jqueryui_themes =   [
        'base', 
        'black-tie', 
        'blitzer', 
        'cupertino', 
        'dark-hive', 
        'dot-luv', 
        'eggplant', 
        'excite-bike', 
        'flick', 
        'hot-sneaks', 
        'humanity', 
        'le-frog', 
        'mint-choc', 
        'overcast', 
        'pepper-grinder',
        'redmond', 
        'smoothness', 
        'south-street', 
        'start', 
        'sunny', 
        'swanky-purse', 
        'trontastic', 
        'ui-darkness', 
        'ui-lightness', 
        'vader'
    ];
    
    for(index in jqueryui_themes) {
        console.log(jqueryui_themes[index]);
        jQuery('#fdb_jqueryui_theme').append('<option>' + jqueryui_themes[index] + '</option>');
    }
    
    jQuery('#fdb_jqueryui_theme').val(jQuery('#fdb_jqueryui_theme_selected').val());
    
    
});
