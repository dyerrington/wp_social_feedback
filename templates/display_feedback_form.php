<?php

$site_url           =   get_bloginfo('wpurl');
$form_description   =   get_option('fdb_form_description');
$form_response      =   get_option('fdb_form_response');
$form_not_logged_in =   get_option('fdb_form_not_logged_in');

$plugin_url         =   FDB_PLUGIN_URL;
$user               =   get_userdata(get_current_user_id());

// TDB - move JS to different file..

?>

<script>

/* <![CDATA[ */
    var ajaxurl =   "<?php echo $site_url ?>/wp-admin/admin-ajax.php";
/* ]]> */

jQuery(document).ready(function() {
   
    jQuery('#feedback-button').click(function() {
       
        jQuery('#feedback-form-dialog')
            .dialog({ 
                modal: true,
                zIndex: 9999, 
                width: 500, 
                buttons: { 
                    "Submit": function() { jQuery("#feedback-form").submit(); },
                    "Cancel": function() { jQuery(this).dialog("close"); } 
                }
            });
            
        jQuery('.ui-widget-overlay').click(function() { jQuery("#feedback-form-dialog").dialog("close"); });
        
        // fancy up the buttons with icons
        var btnCancel = jQuery('.ui-dialog-buttonpane').find('button:contains("Cancel")');
        btnCancel.prepend('<span style="float:left; margin-top: 2px;" class="ui-icon ui-icon-trash"></span>');
        btnCancel.width(btnCancel.width() + 25);
        
        var btnSubmit = jQuery('.ui-dialog-buttonpane').find('button:contains("Submit")');
        btnSubmit.prepend('<span style="float:left; margin-top: 2px;" class="ui-icon ui-icon-comment"></span>');
        btnSubmit.width(btnSubmit.width() + 25);
            
        // turn off dialog toolbar - personal taste
        jQuery(".ui-dialog-titlebar").hide();
    });
    
    jQuery("#feedback-form").validate();
    jQuery('#feedback-form').submit(function() {
      
        var values = {action: 'insert_request'};
        jQuery.each(jQuery(this).serializeArray(), function(i, field) {
            values[field.name] = field.value;
        });
        
        console.log(values);
        
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: values,
            beforeSend:function() {
                jQuery('#feedback-form-loading').dialog({modal: true, height: 50});
                jQuery(".ui-dialog-titlebar").hide();
            },
            success: function(response) {
                jQuery('#feedback-form-loading').dialog('close');
                jQuery('#feedback-form-complete').show();
                jQuery('#feedback-form').hide();
            },
            //dataType: dataType
        });
    });
    //jQuery( "#request-nav" ).buttonset();
    
    jQuery("#vote-up, #vote-down").click(function() {
    
        var vote    =   (jQuery(this).attr('id') == "vote-up") ?
                        1 : -1;
        
        var values = {
            action:     'insert_vote',
            post_id:    jQuery(this).attr('title'),
            vote:       vote
        };
        
        jQuery.ajax({
            type: 'POST',
            url: ajaxurl,
            data: values,
            beforeSend: function() {
                jQuery('#vote-count').hide();
                jQuery('#vote-processing').show();
            },
            complete: function(response) {
                jQuery('#vote-count').show();
                jQuery('#vote-processing').hide();
            },
            success: function(response) {
                jQuery('#vote-count').html(response['votes']);
            },
            //dataType: dataType
        });
    });
    
    
});
</script>

<div id="feedback-button" class="fdb-vertical-text">Feedback</div>

<div id="feedback-form-dialog" title="Feedback Form">

    <span id="feedback-form-contents">
    
    <?php if ( is_user_logged_in() ): ?>
    
        <h1 class="entry-title">Feedback</h1>
       
        <p><?php echo $form_description ?></p>
        <form id="feedback-form" onSubmit="return false;">
            <!-- <label for="user_name">Name</label><br>
            <input type="text" name="user_name"><br> -->
            Logged in as:
            <div class="feedback-avatar">
                <?php echo get_avatar($user->ID, 35) ?> <strong><?php echo $user->nickname ?></strong>
            </div>
            <div class="feedback-form">
                <label for="request_title">Request or Feature</label><br>
                <input type="text" name="request_title" class="required"><br>
                <label for="request_summary">Tell us more</label><br>
                <textarea name="request_summary" class="required" rows="8"></textarea>
            </div>
            <button id="feedback-form-button" value="Submit">Submit</button>
        </form>
        
    <?php else: ?>
        
        <?php echo $form_not_logged_in ?>
    
    <?php endif; ?>
    </span>
    
    <div id="feedback-form-loading">
        <img src="<?php echo $plugin_url ?>/images/form-loading.gif">Submitting...
    </div>
    
    <div id="feedback-form-complete">
        <img src="<?php echo $plugin_url ?>/images/featured.png"><?php echo $form_response ?>
    </div>
    
</div>
