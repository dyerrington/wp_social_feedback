<?php

    function get_time_since($time) {
        
        $time   =   time() - $time; // to get the time since that moment
    
        $tokens =   array (
            31536000 => 'year',
            2592000 => 'month',
            604800 => 'week',
            86400 => 'day',
            3600 => 'hour',
            60 => 'minute',
            1 => 'second'
        );
    
        foreach ($tokens as $unit => $text) {
            if ($time < $unit) continue;
            $numberOfUnits = floor($time / $unit);
            return $numberOfUnits.' '.$text.(($numberOfUnits>1)?'s':'');
        }

    }
    
	$paged          =    (get_query_var('paged')) ? get_query_var('paged') : 1;
	$current_url    =    get_permalink(get_query_var('page_id'));
	$orderby        =    (get_query_var('orderby')) ? get_query_var('orderby') : 'date';
	$order          =    (get_query_var('order')) ? get_query_var('order') : 'desc';
	
    // set active states for css classes
    $order_active   =   array('latest' => '', 'votes' => '', 'comment_count' => '');
    $allowed_orders =   array('asc', 'desc');
    
    if(isset($_GET['orderby']) && in_array($_GET['orderby'], array_keys($order_active))) {
        $order_active[$_GET['orderby']] =   'ui-state-active';
    }

    
    // set default
    if(!in_array('ui-state-active', $order_active)) {
        $order_active['latest'] =   'ui-state-active';
    }
    
    $query_options  =   array(  'post_type'     =>  'request',
							    'post_status'   =>  'published',
								'paged'         =>  $paged,
								'posts_per_page'=>  8,
								'orderby'       =>  $orderby,
								'order'         =>  $order,
								'caller_get_posts' =>  1
								);
	
	if($orderby == 'votes') {
	    $query_options['meta_key']    =    'votes';
	    $query_options['orderby']     =    'meta_value';
	    unset($query_options['order']);
	}
	
	$loop = new WP_Query($query_options);

    $big = 999999999; // need an unlikely integer
    
    $link_options       =   array(
                                'base' => str_replace( $big, '%#%', get_pagenum_link( $big ) ),
                                'format' => '?paged=%#%',
                                'current' => max( 1, get_query_var('paged') ),
                                'total' => $loop->max_num_pages
                            );
    
    $pagination_links   =   paginate_links($link_options);
    
?>
	<div id="request-container">
	<div id="request-nav" >
	    <a href="<?php echo $current_url ?>&orderby=date&order=desc" class="<?php echo $order_active['latest'] ?>"><span class="ui-icon ui-icon-clock"></span>Latest</a> 
        <a href="<?php echo $current_url ?>&orderby=votes&order=meta_value" class="<?php echo $order_active['votes'] ?>"><span class="ui-icon ui-icon-star"></span>Most Votes</a> 
        <a href="<?php echo $current_url ?>&orderby=comment_count&order=desc" class="<?php echo $order_active['comment_count'] ?>"><span class="ui-icon ui-icon-comment"></span>Most Comments</a>
	</div>
	
	<br clear="all"><!-- css.. grumble grumble -->
	
    <div class="fdb-pagination"></div>
    
    <ul id="post-results">

	<?php while ( $loop->have_posts() ) : $loop->the_post(); ?>
	<?php
	
		$custom              =    get_post_custom($post->ID);
		$number_of_comments  =    get_comments_number($post->ID);
		$user_info           =    get_userdata($custom["user_id"][0]);
		$time_elapsed        =    get_time_since(strtotime(the_date('F j, Y g:i a','','', false)));
		
	?>

			<li id="blog-<?php the_ID(); ?>">
			
			<div class="request-votes">
			    <div class="request-votes-count <?php if($custom['votes'][0] > 0) echo "has-votes"; ?>">
			        <?php echo $custom['votes'][0] ?>
			    </div>
			    <span class="request-votes-label">Votes</span>
			</div>
			
			<div class="request-votes">
			    <div class="request-comments-count <?php if($number_of_comments > 0) echo "has-comments"; ?>">
			        <?php echo $number_of_comments ?>
			    </div>
			    <span class="request-votes-label">Comments</span>
			</div>
			
			<div class="request-avatar">
			    <?php echo get_avatar($custom["user_id"][0], 50) ?>
			</div>
			
			<em>
			    <a href="<?php the_permalink() ?>"><?php the_title(); ?></a> <!--<?php the_excerpt() ?> -->
			    <br>
			    By <?php echo $user_info->display_name ?>
			</em>
			<span class="time-elapsed"><?php echo $time_elapsed; ?> ago</span> 
			<br clear="all">
			</li>
        <?php endwhile; ?>
        
        <div class="fdb-pagination" style="border-top: none;">
            <?php echo $pagination_links; ?>
        </div>
            
		</ul>

	</div>

