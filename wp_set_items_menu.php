<?php
/**
 * Create Menu/Items in WORDPRESS
 *
 * This function can create a menu and add items
 *
 * @since 1.0.0
 * @author Morgan JOURDIN <mj.technologie@gmail.com>
 *
 * @param string $name (optional)
 * @param array $args (required)
 * @param array $location (optional)
 * @return N/A|string error message
 */
function wp_set_items_menu($name, $args = array(), $location = array()) {
  global $wpdb;

  $new_menu_ID = (!is_nav_menu($name)) ? wp_create_nav_menu($name) : wp_get_nav_menu_object($name)->term_id;

  if(is_array($args) && empty($args))
    return new WP_Error( 'invalid_array', __( 'The array is empty or is not a array. Please insert the require array.' ) );

  foreach ($args as $key => $value) {
    //Insert new post
    $new_post = array(
      'post_title'    => '',
      'post_status' => 'draft',
      'comment_status' => 'closed',
      'ping_status' => 'closed',
      'post_parent' => 0,
      'menu_order' => $value['menu_order'],
      'post_type' => 'nav_menu_item',
      'comment_count' => 0
    );
    $new_post_ID = wp_insert_post($new_post);

    if(is_wp_error($new_post_ID)) //if the new posts is created
      return new WP_Error( 'invalid_insert_post', __( 'The post is not created.' ) );

    //update the post with new infos.
    $update = wp_update_post(array(
      'ID' => $new_post_ID,
      'post_status' => 'publish',
      'post_name' => $new_post_ID,
      'guid' => get_option( 'siteurl' ).'/?p='.$new_post_ID
    ));

    if(is_wp_error($update))
      return new WP_Error( 'invalid_updat_post', __( 'The post is not update.' ) );

    //Prepare item postmeta
    $menu_item = array(
      '_menu_item_type' => $value['type'],
      '_menu_item_menu_item_parent' => 0,
      '_menu_item_object_id' => ($value['type'] !== 'post_type_archive') ? $value['ID'] : '-31', //-31 is wordpress defaut value
      '_menu_item_object' => $value['object'],
      '_menu_item_target' => '',
      '_menu_item_classes' => unserialize('a:1:{i:0;s:0:"";}'),
      '_menu_item_xfn' => '',
      '_menu_item_url' => ''
    );

    foreach ($menu_item as $k => $val) {
      $add = add_post_meta($new_post_ID, $k, $val, true);
      if($add === false)
        return new WP_Error( 'invalid_add_postmeta', __( 'The postmeta "'.$k.'" is not created.' ) );
    }

    //insert relationships (important)
    $insert = $wpdb->insert( $wpdb->term_relationships, array( 'object_id' => (int)$new_post_ID, 'term_taxonomy_id' => (int)$new_menu_ID ), array( '%d', '%d' ) );
    if($insert === false)
      return new WP_Error( 'invalid_insert_relationship', __( 'The relationship is not insert.' ) );

    //update count of items in new menu
    $results = $wpdb->get_results("SELECT count FROM ".$wpdb->prefix."term_taxonomy WHERE term_id=".(int)wp_get_nav_menu_object($name)->term_id);
		if($results === false)
      return new WP_Error( 'invalid_select_count', __( 'The request not work.' ) );

		if(count($results) > 0) $results = (int)$results[0]->count + 1;
    else $results = 1;

    $update = $wpdb->update( $wpdb->term_taxonomy, array( 'count' => $results ), array( 'term_id' => $new_menu_ID ), array( '%d' ), array( '%d' ) );
    if($update === false)
      return new WP_Error( 'invalid_update_taxonomyterm', __( 'The counter is not update.' ) );

	}

	//update location menu
	if(!empty($location) && $location['bool']){
		$update = update_option('theme_mods_treehouse', unserialize('a:3:{i:0;b:0;s:18:"custom_css_post_id";i:-1;s:18:"nav_menu_locations";a:1:{s:11:"'.$location['slug'].'";i:'.$new_menu_ID.';}}'));
		if($update === false)
			return new WP_Error( 'invalid_update_option', __( 'The option "theme_mods_treehouse" is not update.' ) );
	}
}
?>
