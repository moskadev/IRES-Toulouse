<?php
/*
Plugin Name: Cool Tag Cloud
Plugin URI: https://wordpress.org/plugins/cool-tag-cloud/
Description: A simple, yet very beautiful tag cloud.
Version: 2.27
Author: WPKube
Author URI: https://www.wpkube.com/
Text Domain: cool-tag-cloud
*/ 

class Cool_Tag_Cloud_Widget extends WP_Widget {

	//defaults
	private $m_defaults = array( 
		'title'         => 'Tags',
		'font-weight'   => 'Normal',
		'font-family'   => 'arial',
		'smallest'      => 10,
		'largest'       => 10,
		'format'        => 'flat',
		'separator'     => '',
		'unit'          => 'px',
		'number'        => 20,
		'orderby'       => 'name',
		'order'         => 'ASC',
		'taxonomy'      => array( 'post_tag' ),
		'exclude'       => '',
        'include'       => '',
        'child_of'      => '',
		'texttransform' => 'none',
		'nofollow'      => 'No',
		'imagestyle'    => 'ctcdefault',
		'imagealign'    => 'ctcleft',
        'animation'     => 'No',
        'on_single_display' => 'global',
        'show_count' => 'no',
        'max_height'    => '',
        'max_height_button_open' => 'Show More',
        'max_height_button_close' => 'Show Less',
        'element' => 'a',
	);

	public function __construct() {
		$l_options = array('description' => __('Cool Tag Cloud widget.', 'cool-tag-cloud'));
		parent::__construct('cool_tag_cloud', 'Cool Tag Cloud', $l_options);
	}

    //render tagcloud
	public function widget($p_args, $p_instance) {
		extract($p_args, EXTR_PREFIX_ALL, 'l_args');

		$show = true;

		if (!empty( $p_instance['title'])) {
			$l_title = $p_instance['title'];
		} else {
			$l_title = '';
		}
		$l_title = apply_filters('widget_title', $l_title);

		echo $l_args_before_widget;
		echo $l_args_before_title . $l_title . $l_args_after_title;

		$l_tag_params = wp_parse_args($p_instance, $this->m_defaults);
		$l_tag_params["echo"] = 0;
        if ($l_tag_params["nofollow"] == 'Yes') {add_filter('wp_tag_cloud', 'ctc_nofollow_tag_cloud');};
        if ($l_tag_params['element'] == 'span') {add_filter('wp_tag_cloud', 'ctc_remove_anchor');};
		if ( $l_tag_params['on_single_display'] == 'local' && ( is_single() || is_singular( array( 'post', 'page' ) ) ) ) {
			$tag_ids = wp_get_post_terms( get_the_ID(), $l_tag_params['taxonomy'], array( 'fields' => 'ids' ) );
			if ( empty( $tag_ids ) ) {
				$show = false;
			}
			if ( ! empty( $l_tag_params['exclude'] ) ) {
				$exlude_tags = explode( ',', str_replace( ' ', '', $l_tag_params['exclude'] ) );
				foreach ( $exlude_tags as $exlude_tag ) {
					foreach ( $tag_ids as $tag_key => $tag_id ) {
						if ( $tag_id == $exlude_tag ) {
							unset( $tag_ids[$tag_key] );
						}
					}
				}
			}
			$l_tag_params['include'] = $tag_ids;
		}
		if ( $l_tag_params['show_count'] == 'yes' ) {
			$l_tag_params['show_count'] = true;
		} else {
			$l_tag_params['show_count'] = false;
		}
		add_filter( 'wp_generate_tag_cloud_data', 'cool_tag_cloud_active_tag' );
		if ( $show ) {
			$l_tag_cloud_text = cool_tag_cloud_wp_tag_cloud( $l_tag_params  );
		} else {
			$l_tag_cloud_text = '';
		}
		remove_filter( 'wp_generate_tag_cloud_data', 'cool_tag_cloud_active_tag' );
        if ($l_tag_params["nofollow"] == 'Yes') {remove_filter('wp_tag_cloud', 'ctc_nofollow_tag_cloud');};
        if ($l_tag_params['element'] == 'span') {remove_filter('wp_tag_cloud', 'ctc_remove_anchor');};
		echo '<div class="cool-tag-cloud">';
		if ($l_tag_params["font-weight"] == "Bold") {echo '<div class="cloudbold">';}
        if ($l_tag_params["animation"] == "Yes") {echo '<div class="animation">';}
		echo '<div class="' . esc_attr( $l_tag_params["imagestyle"] ) . '">'; echo '<div class="' . esc_attr( $l_tag_params["imagealign"] ) . '">';
        echo '<div class="' . esc_attr( $l_tag_params["font-family"] ) . '" style="text-transform:' . esc_attr( $l_tag_params["texttransform"] ) . '!important;">';
        if ( ! empty( $l_tag_params['max_height'] ) ) {
            echo '<div class="cool-tag-cloud-inner" style="max-height:' . intval( $l_tag_params['max_height'] ) . 'px;">';
        }
        echo $l_tag_cloud_text;
        if ( ! empty( $l_tag_params['max_height'] ) ) {
            echo '</div>';
        }
        if ( ! empty( $l_tag_params['max_height'] ) ) {
            echo '<span class="cool-tag-cloud-load-more">
                <span class="cool-tag-cloud-open" onclick="coolTagCloudToggle(this)">' . $l_tag_params['max_height_button_open'] . '</span>
                <span class="cool-tag-cloud-close" onclick="coolTagCloudToggle(this)">' . $l_tag_params['max_height_button_close'] . '</span>
            </span>';
        }
		echo '</div>'; echo '</div>';
		echo '</div>';
        if ($l_tag_params["animation"] == "Yes") {echo '</div>';}
		if ($l_tag_params["font-weight"] == "Bold") {echo '</div>';}
		echo '</div>';

		echo $l_args_after_widget;
	}

    //widget setup
	public function form($p_instance) {

		$l_instance = wp_parse_args($p_instance, $this->m_defaults);
		
		echo '<p>';
		echo '<label for="' . $this->get_field_id('title') . '">' .
			__('Title:', 'cool-tag-cloud') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('title') .
			'" name="' . $this->get_field_name('title') . '" type="text" ' .
			'value="' . __(esc_attr($l_instance['title']), 'cool-tag-cloud') . '" />';
		echo '</p>';

		echo '<p>';
		echo '<label for="' . $this->get_field_id('font-family') . '">' .
			__('Font family:', 'cool-tag-cloud') . '</label>';
		echo '<select class="widefat" id="' . $this->get_field_id('font-family') . 
			'" name="' . $this->get_field_name('font-family') . '">';
		echo '<option ' . selected('arial', $l_instance['font-family'], false) .
			' value="arial">Arial, Helvetica, Sans-serif</option>';
		echo '<option ' . selected('rockwell', $l_instance['font-family'],false) .
			' value="rockwell">Rockwell, Georgia, Serif</option>';
		echo '<option ' . selected('tahoma', $l_instance['font-family'], false) .
			' value="tahoma">Tahoma, Geneva, Sans-serif</option>';
		echo '<option ' . selected('georgia', $l_instance['font-family'], false) .
			' value="georgia">Georgia, Times, Serif</option>';
		echo '<option ' . selected('times', $l_instance['font-family'], false) .
			' value="times">Times, Georgia, Serif</option>';
		echo '<option ' . selected('cambria', $l_instance['font-family'], false) .
			' value="cambria">Cambria, Georgia, Serif</option>';
		echo '<option ' . selected('verdana', $l_instance['font-family'], false) .
			' value="verdana">Verdana, Lucida, Sans-serif</option>';
		echo '<option ' . selected('opensans', $l_instance['font-family'], false) .
			' value="opensans">"Open Sans", Helvetica, Arial</option>';
		echo '</select>';
		echo '</p>';
				
		echo '<p>';
		echo '<label for="' . $this->get_field_id('font-weight') . '">' .
			__('Font weight:', 'cool-tag-cloud') . '</label>';
		echo '<select class="widefat" id="' . $this->get_field_id('font-weight') . 
			'" name="' . $this->get_field_name('font-weight') . '">';
		echo '<option ' . selected('Normal', $l_instance['font-weight'], false) .
			' value="Normal">' . __('Normal', 'cool-tag-cloud') .'</option>';
		echo '<option ' . selected('Bold', $l_instance['font-weight'], false) .
			' value="Bold">' . __('Bold', 'cool-tag-cloud') .'</option>';
		echo '</select>';
		echo '</p>';
				
		echo '<p>';
		echo '<label for="' . $this->get_field_id('smallest') . '">' .
			__('Smallest font size:', 'cool-tag-cloud') . '</label>';
		echo '<select class="widefat" id="' . $this->get_field_id('smallest') . 
			'" name="' . $this->get_field_name('smallest') . '">';
		echo '<option ' . selected('10', $l_instance['smallest'], false) .
			' value="10">10px</option>';
		echo '<option ' . selected('11', $l_instance['smallest'], false) .
			' value="11">11px</option>';
		echo '<option ' . selected('12', $l_instance['smallest'], false) .
			' value="12">12px</option>';
		echo '<option ' . selected('13', $l_instance['smallest'], false) .
			' value="13">13px</option>';
		echo '<option ' . selected('14', $l_instance['smallest'], false) .
			' value="14">14px</option>';
		echo '<option ' . selected('15', $l_instance['smallest'], false) .
			' value="15">15px</option>';
		echo '<option ' . selected('16', $l_instance['smallest'], false) .
			' value="16">16px</option>';
		echo '<option ' . selected('17', $l_instance['smallest'], false) .
			' value="17">17px</option>';
		echo '</select>';
		echo '</p>';
		
		echo '<p>';
		echo '<label for="' . $this->get_field_id('largest') . '">' .
			__('Largest font size:', 'cool-tag-cloud') . '</label>';
		echo '<select class="widefat" id="' . $this->get_field_id( 'largest' ) . 
			'" name="' . $this->get_field_name('largest') . '">';
		echo '<option ' . selected('10', $l_instance['largest'], false) .
			' value="10">10px</option>';
		echo '<option ' . selected('11', $l_instance['largest'], false) .
			' value="11">11px</option>';
		echo '<option ' . selected('12', $l_instance['largest'], false) .
			' value="12">12px</option>';
		echo '<option ' . selected('13', $l_instance['largest'], false) .
			' value="13">13px</option>';
		echo '<option ' . selected('14', $l_instance['largest'], false) .
			' value="14">14px</option>';
		echo '<option ' . selected('15', $l_instance['largest'], false) .
			' value="15">15px</option>';
		echo '<option ' . selected('16', $l_instance['largest'], false) .
			' value="16">16px</option>';
		echo '<option ' . selected('17', $l_instance['largest'], false) .
			' value="17">17px</option>';
		echo '</select>';
		echo '</p>';
		
        echo '<p>';
		echo '<label for="' . $this->get_field_id('imagestyle') . '">' .
			__( 'Image style:', 'cool-tag-cloud' ) . '</label>';
		echo '<select class="widefat" id="' . $this->get_field_id('imagestyle') . 
			'" name="' . $this->get_field_name('imagestyle') . '">';
		echo '<option ' . selected('ctcdefault', $l_instance['imagestyle'], false) .
			' value="ctcdefault">' . __('Default (Orange)', 'cool-tag-cloud') .'</option>';
		echo '<option ' . selected('ctcsilver', $l_instance['imagestyle'], false) .
			' value="ctcsilver">' . __('Silver', 'cool-tag-cloud') .'</option>';
        echo '<option ' . selected('ctcgreen', $l_instance['imagestyle'], false) .
			' value="ctcgreen">' . __('Green', 'cool-tag-cloud') .'</option>';
        echo '<option ' . selected('ctcred', $l_instance['imagestyle'], false) .
			' value="ctcred">' . __('Red', 'cool-tag-cloud') .'</option>';
        echo '<option ' . selected('ctcblue', $l_instance['imagestyle'], false) .
			' value="ctcblue">' . __('Blue', 'cool-tag-cloud') .'</option>';
        echo '<option ' . selected('ctcbrown', $l_instance['imagestyle'], false) .
			' value="ctcbrown">' . __('Brown', 'cool-tag-cloud') .'</option>';
        echo '<option ' . selected('ctcpurple', $l_instance['imagestyle'], false) .
			' value="ctcpurple">' . __('Purple', 'cool-tag-cloud') .'</option>';
        echo '<option ' . selected('ctccyan', $l_instance['imagestyle'], false) .
			' value="ctccyan">' . __('Cyan', 'cool-tag-cloud') .'</option>';
        echo '<option ' . selected('ctclime', $l_instance['imagestyle'], false) .
			' value="ctclime">' . __('Lime', 'cool-tag-cloud') .'</option>';
        echo '<option ' . selected('ctcblack', $l_instance['imagestyle'], false) .
			' value="ctcblack">' . __('Black', 'cool-tag-cloud') .'</option>';
		echo '</select>';
		echo '</p>';
        
		echo '<p>';
		echo '<label for="' . $this->get_field_id('imagealign') . '">' .
			__('Image align:', 'cool-tag-cloud') . '</label>';
		echo '<select class="widefat" id="' . $this->get_field_id('imagealign') . 
			'" name="' . $this->get_field_name( 'imagealign' ) . '">';
		echo '<option ' . selected( 'ctcleft', $l_instance['imagealign'], false) .
			' value="ctcleft">' . __('Left', 'cool-tag-cloud') .'</option>';
		echo '<option ' . selected( 'ctcright', $l_instance['imagealign'], false) .
			' value="ctcright">' . __('Right', 'cool-tag-cloud') .'</option>';
		echo '</select>';
		echo '</p>';
        
        echo '<p>';
		echo '<label for="' . $this->get_field_id('animation') . '">' .
			__('Animation on hover:', 'cool-tag-cloud') . '</label>';
		echo '<select class="widefat" id="' . $this->get_field_id('animation') . 
			'" name="' . $this->get_field_name('animation') . '">';
		echo '<option ' . selected('Yes', $l_instance['animation'], false) .
			' value="Yes">' . __('Yes', 'cool-tag-cloud') .'</option>';
		echo '<option ' . selected('No', $l_instance['animation'], false) .
			' value="No">' . __('No', 'cool-tag-cloud') .'</option>';
		echo '</select>';
		echo '</p>';
		
		echo '<p>';
		echo '<label for="' . $this->get_field_id('texttransform') . '">' .
			__('Text transform:', 'cool-tag-cloud') . '</label>';
		echo '<select class="widefat" id="' . $this->get_field_id('texttransform') . 
			'" name="' . $this->get_field_name('texttransform') . '">';
		echo '<option ' . selected( 'none', $l_instance['texttransform'], false) .
			' value="none">' . __('None', 'cool-tag-cloud') .'</option>';
		echo '<option ' . selected('uppercase', $l_instance['texttransform'], false) .
			' value="uppercase">' . __('Uppercase', 'cool-tag-cloud') .'</option>';
		echo '<option ' . selected('lowercase', $l_instance['texttransform'], false) .
			' value="lowercase">' . __('Lowercase', 'cool-tag-cloud') .'</option>';
		echo '<option ' . selected('capitalize', $l_instance['texttransform'], false) .
			' value="capitalize">' . __('Capitalize', 'cool-tag-cloud') .'</option>';
		echo '</select>';
		echo '</p>';

		echo '<p>';
		echo '<label for="' . $this->get_field_id('number') . '">' .
			__('Maximum tags (0 for no limit):', 'cool-tag-cloud') . '</label>';
		echo '<input class="widefat" id="' . $this->get_field_id('number') .
			'" name="' . $this->get_field_name('number') . '" type="text" ' .
			'value="' . esc_attr($l_instance['number']) . '" />';
		echo '</p>';

		echo '<p>';
		echo '<label for="' . $this->get_field_id('orderby') . '">' .
			__('Order tags by:', 'cool-tag-cloud') . '</label>';
		echo '<select class="widefat" id="' . 
			$this->get_field_id('orderby') .  '" name="' . 
			$this->get_field_name('orderby') . '">';
		echo '<option ' . selected('name', $l_instance['orderby'], false) .' value="name">'. __('Name', 'cool-tag-cloud') . '</option>';
		echo '<option ' . selected('count', $l_instance['orderby'], false) . ' value="count">'. __('Count', 'cool-tag-cloud') . '</option>';
		echo '<option ' . selected('count_name', $l_instance['orderby'], false) . ' value="count_name">'. __('Top tags ordered by name', 'cool-tag-cloud') . '</option>';
		echo '</select>';
		echo '</p>';

		echo '<p>';
		echo '<label for="' . $this->get_field_id('order') . '">' .
			__('Tag order direction:', 'cool-tag-cloud') . '</label>';
		echo '<select class="widefat" id="' . $this->get_field_id('order') . 
			'" name="' . $this->get_field_name( 'order' ) . '">';
		echo '<option ' . selected('ASC', $l_instance['order'], false) .
			' value="ASC">'. __('ascending', 'cool-tag-cloud') . '</option>';
		echo '<option ' . selected('DESC', $l_instance['order'], false) .
			' value="DESC">'. __('descending', 'cool-tag-cloud') . '</option>';
		echo '<option ' . selected('RAND', $l_instance['order'], false) .
			' value="RAND">'. __('random', 'cool-tag-cloud') . '</option>';
		echo '</select>';
		echo '</p>';

        
        $l_current_tax = $this->_get_current_taxonomy($p_instance);
        ?>
        <p>
            <label for="<?php echo $this->get_field_id('taxonomy'); ?>"><?php _e('Taxonomy:', 'cool-tag-cloud'); ?></label>
            <select multiple class="widefat" name="<?php echo $this->get_field_name('taxonomy'); ?>[]" id="<?php echo $this->get_field_id('taxonomy'); ?>">
                <?php foreach ( get_taxonomies() as $l_taxonomy ) : ?>
                    <?php
                        $l_tax = get_taxonomy($l_taxonomy);
                        if ( ! $l_tax->show_tagcloud || empty( $l_tax->labels->name ) ) continue;
                    ?>
                    <option value="<?php echo esc_attr($l_taxonomy); ?>" <?php
                        if ( in_array( $l_taxonomy, $l_current_tax ) ) echo 'selected';
                    ?>><?php echo $l_tax->labels->name; ?></option>
                <?php endforeach; ?>
            </select>
        </p>
        <?php
		
		echo '<p>';
		echo '<label for="' . $this->get_field_id('nofollow') . '">' .
			__( 'Nofollow for tag links:', 'cool-tag-cloud' ) . '</label>';
		echo '<select class="widefat" id="' . $this->get_field_id('nofollow') . 
			'" name="' . $this->get_field_name('nofollow') . '">';
		echo '<option ' . selected('Yes', $l_instance['nofollow'], false) .
			' value="Yes">' . __('Yes', 'cool-tag-cloud') .'</option>';
		echo '<option ' . selected('No', $l_instance['nofollow'], false) .
			' value="No">' . __('No', 'cool-tag-cloud') .'</option>';
		echo '</select>';
		echo '</p>';

		?>
		<p>
			<label for="<?php $this->get_field_id('show_count'); ?>"><?php esc_html_e( 'Show post counts', 'cool-tag-cloud' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('show_count'); ?>" name="<?php echo $this->get_field_name('show_count'); ?>">
				<option value="yes" <?php selected( 'yes', $l_instance['show_count'] ); ?>><?php esc_html_e( 'Yes', 'cool-tag-cloud' ); ?></option>
				<option value="no" <?php selected( 'no', $l_instance['show_count'] ); ?>><?php esc_html_e( 'No', 'cool-tag-cloud' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php $this->get_field_id('on_single_display'); ?>"><?php esc_html_e( 'On single post display', 'cool-tag-cloud' ); ?></label>
			<select class="widefat" id="<?php echo $this->get_field_id('on_single_display'); ?>" name="<?php echo $this->get_field_name('on_single_display'); ?>">
				<option value="global" <?php selected( 'global', $l_instance['on_single_display'] ); ?>><?php esc_html_e( 'All tags', 'cool-tag-cloud' ); ?></option>
				<option value="local" <?php selected( 'local', $l_instance['on_single_display'] ); ?>><?php esc_html_e( 'Tags from the shown post', 'cool-tag-cloud' ); ?></option>
			</select>
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('include'); ?>"><?php esc_html_e( 'Include', 'cool-tag-cloud' ); ?><br><small><?php esc_html_e( 'Enter IDs of the tags separated by a comma. Example: 1,2,3,4', 'cool-tag-cloud' ); ?></small></label>
			<input class="widefat" id="<?php echo $this->get_field_id('include'); ?>" name="<?php echo $this->get_field_name('include'); ?>" type="text" value="<?php echo esc_attr($l_instance['include']); ?>" />
		</p>
		<p>
			<label for="<?php echo $this->get_field_id('exclude'); ?>"><?php esc_html_e( 'Exclude', 'cool-tag-cloud' ); ?><br><small><?php esc_html_e( 'Enter IDs of the tags separated by a comma. Example: 1,2,3,4', 'cool-tag-cloud' ); ?></small></label>
			<input class="widefat" id="<?php echo $this->get_field_id('exclude'); ?>" name="<?php echo $this->get_field_name('exclude'); ?>" type="text" value="<?php echo esc_attr($l_instance['exclude']); ?>" />
		</p>
        <p>
			<label for="<?php echo $this->get_field_id('child_of'); ?>"><?php esc_html_e( 'Child of', 'cool-tag-cloud' ); ?><br><small><?php esc_html_e( 'The ID of the tag/category. Shows children terms.', 'cool-tag-cloud' ); ?></small></label>
			<input class="widefat" id="<?php echo $this->get_field_id('child_of'); ?>" name="<?php echo $this->get_field_name('child_of'); ?>" type="text" value="<?php echo esc_attr($l_instance['child_of']); ?>" />
        </p>
        <p>
			<label for="<?php echo $this->get_field_id('max_height'); ?>"><?php esc_html_e( 'Max Height', 'cool-tag-cloud' ); ?><br><small><?php esc_html_e( 'Limits initial height (in pixels) and adds a button for showing all tags.', 'cool-tag-cloud' ); ?></small></label>
			<input class="widefat" id="<?php echo $this->get_field_id('max_height'); ?>" name="<?php echo $this->get_field_name('max_height'); ?>" type="number" value="<?php echo esc_attr($l_instance['max_height']); ?>" />
        </p>
        <p>
			<label for="<?php echo $this->get_field_id('max_height_button_open'); ?>"><?php esc_html_e( 'Button Show More', 'cool-tag-cloud' ); ?><br><small><?php esc_html_e( 'Button label (when max width set) to show more tags.', 'cool-tag-cloud' ); ?></small></label>
			<input class="widefat" id="<?php echo $this->get_field_id('max_height_button_open'); ?>" name="<?php echo $this->get_field_name('max_height_button_open'); ?>" type="text" value="<?php echo esc_attr($l_instance['max_height_button_open']); ?>" />
        </p>
        <p>
			<label for="<?php echo $this->get_field_id('max_height_button_close'); ?>"><?php esc_html_e( 'Button Show Less', 'cool-tag-cloud' ); ?><br><small><?php esc_html_e( 'Button label (when max width set) to show less tags.', 'cool-tag-cloud' ); ?></small></label>
			<input class="widefat" id="<?php echo $this->get_field_id('max_height_button_close'); ?>" name="<?php echo $this->get_field_name('max_height_button_close'); ?>" type="text" value="<?php echo esc_attr($l_instance['max_height_button_close']); ?>" />
        </p>
        <p>
			<label for="<?php $this->get_field_id('element'); ?>"><?php esc_html_e( 'Link to tag archive', 'cool-tag-cloud' ); ?><br><small><?php esc_html_e( 'If you only want a list of tags that do not link to the archives set this to "No".', 'cool-tag-cloud' ); ?></small></label>
			<select class="widefat" id="<?php echo $this->get_field_id('element'); ?>" name="<?php echo $this->get_field_name('element'); ?>">
				<option value="a" <?php selected( 'a', $l_instance['element'] ); ?>><?php esc_html_e( 'Yes', 'cool-tag-cloud' ); ?></option>
				<option value="span" <?php selected( 'span', $l_instance['element'] ); ?>><?php esc_html_e( 'No', 'cool-tag-cloud' ); ?></option>
			</select>
		</p>
		<?php
		
	}

    //update settings
	public function update($p_new_instance, $p_old_instance) {

		$l_instance['title'] = strip_tags(stripslashes($p_new_instance['title']));

		if ('arial' == $p_new_instance['font-family']) {
			$l_instance['font-family'] = 'arial';
		} else if ('rockwell' == $p_new_instance['font-family']) {
			$l_instance['font-family'] = 'rockwell';
		} else if ('tahoma' == $p_new_instance['font-family']) {
			$l_instance['font-family'] = 'tahoma';
		} else if ('georgia' == $p_new_instance['font-family']) {
			$l_instance['font-family'] = 'georgia';
		} else if ('times' == $p_new_instance['font-family']) {
			$l_instance['font-family'] = 'times';
		} else if ('cambria' == $p_new_instance['font-family']) {
			$l_instance['font-family'] = 'cambria';
		} else if ('verdana' == $p_new_instance['font-family']) {
			$l_instance['font-family'] = 'verdana';
		} else if ('opensans' == $p_new_instance['font-family']) {
			$l_instance['font-family'] = 'opensans';
		} else {
			$l_instance['font-family'] = $p_old_instance['font-family'];
		}
		
		if ('10' == $p_new_instance['smallest']) {
			$l_instance['smallest'] = '10';
		} else if ('11' == $p_new_instance['smallest']) {
			$l_instance['smallest'] = '11';
		} else if ('12' == $p_new_instance['smallest']) {
			$l_instance['smallest'] = '12';
		} else if ('13' == $p_new_instance['smallest']) {
			$l_instance['smallest'] = '13';
		} else if ('14' == $p_new_instance['smallest']) {
			$l_instance['smallest'] = '14';
		} else if ('15' == $p_new_instance['smallest']) {
			$l_instance['smallest'] = '15';
		} else if ('16' == $p_new_instance['smallest']) {
			$l_instance['smallest'] = '16';
		} else if ('17' == $p_new_instance['smallest']) {
			$l_instance['smallest'] = '17';
		} else {
			$l_instance['smallest'] = $p_old_instance['smallest'];
		}
		
		if ('10' == $p_new_instance['largest']) {
			$l_instance['largest'] = '10';
		} else if ('11' == $p_new_instance['largest']) {
			$l_instance['largest'] = '11';
		} else if ('12' == $p_new_instance['largest']) {
			$l_instance['largest'] = '12';
		} else if ('13' == $p_new_instance['largest']) {
			$l_instance['largest'] = '13';
		} else if ('14' == $p_new_instance['largest']) {
			$l_instance['largest'] = '14';
		} else if ('15' == $p_new_instance['largest']) {
			$l_instance['largest'] = '15';
		} else if ('16' == $p_new_instance['largest']) {
			$l_instance['largest'] = '16';
		} else if ('17' == $p_new_instance['largest']) {
			$l_instance['largest'] = '17';
		} else {
			$l_instance['largest'] = $p_old_instance['largest'];
		}
		
        if ('ctcdefault' == $p_new_instance['imagestyle']) {
			$l_instance['imagestyle'] = 'ctcdefault';
		} else if ('ctcsilver' == $p_new_instance['imagestyle']) {
			$l_instance['imagestyle'] = 'ctcsilver';
        } else if ('ctcgreen' == $p_new_instance['imagestyle']) {
			$l_instance['imagestyle'] = 'ctcgreen';
        } else if ('ctcred' == $p_new_instance['imagestyle']) {
			$l_instance['imagestyle'] = 'ctcred';
        } else if ('ctcblue' == $p_new_instance['imagestyle']) {
			$l_instance['imagestyle'] = 'ctcblue';
        } else if ('ctcbrown' == $p_new_instance['imagestyle']) {
			$l_instance['imagestyle'] = 'ctcbrown';
        } else if ('ctcpurple' == $p_new_instance['imagestyle']) {
			$l_instance['imagestyle'] = 'ctcpurple';
        } else if ('ctccyan' == $p_new_instance['imagestyle']) {
			$l_instance['imagestyle'] = 'ctccyan';
        } else if ('ctclime' == $p_new_instance['imagestyle']) {
			$l_instance['imagestyle'] = 'ctclime';
        } else if ('ctcblack' == $p_new_instance['imagestyle']) {
			$l_instance['imagestyle'] = 'ctcblack';
		} else {
			$l_instance['imagestyle'] = $p_old_instance['imagestyle'];
		}
        
		if ('ctcleft' == $p_new_instance['imagealign']) {
			$l_instance['imagealign'] = 'ctcleft';
		} else if ('ctcright' == $p_new_instance['imagealign']) {
			$l_instance['imagealign'] = 'ctcright';
		} else {
			$l_instance['imagealign'] = $p_old_instance['imagealign'];
		}
		
		
		if ('Bold' == $p_new_instance['font-weight']) {
			$l_instance['font-weight'] = 'Bold';
		} else if ('Normal' == $p_new_instance['font-weight']) {
			$l_instance['font-weight'] = 'Normal';
		} else {
			$l_instance['font-weight'] = $p_old_instance['font-weight'];
		}
		
		if ('none' == $p_new_instance['texttransform']) {
			$l_instance['texttransform'] = 'none';
		} else if ('uppercase' == $p_new_instance['texttransform']) {
			$l_instance['texttransform'] = 'uppercase';
		} else if ('lowercase' == $p_new_instance['texttransform']) {
			$l_instance['texttransform'] = 'lowercase';
		} else if ('capitalize' == $p_new_instance['texttransform']) {
			$l_instance['texttransform'] = 'capitalize';
		} else {
			$l_instance['texttransform'] = $p_old_instance['texttransform'];
		}

		if (is_numeric($p_new_instance['number'])) {
			$l_instance['number'] = $p_new_instance['number'] + 0;
		} else {
			$l_instance['number'] = $p_old_instance['number'] + 0;
		}

		if ('name' == $p_new_instance['orderby']) {
			$l_instance['orderby'] = 'name';
		} else if ('count' == $p_new_instance['orderby']) {
			$l_instance['orderby'] = 'count';
		} else if ('count_name' == $p_new_instance['orderby']) {
			$l_instance['orderby'] = 'count_name';
		} else {
			$l_instance['orderby'] = $p_old_instance['orderby'];
		}
		
		if ('ASC' == $p_new_instance['order']) {
			$l_instance['order'] = 'ASC';
		} else if ('DESC' == $p_new_instance['order']) {
			$l_instance['order'] = 'DESC';
		} else if ('RAND' == $p_new_instance['order']) {
			$l_instance['order'] = 'RAND';
		} else {
			$l_instance['order'] = $p_old_instance['order'];
		}

		$l_instance['taxonomy'] = $p_new_instance['taxonomy'];
		
		if ('Yes' == $p_new_instance['nofollow']) {
			$l_instance['nofollow'] = 'Yes';
		} else if ('No' == $p_new_instance['nofollow']) {
			$l_instance['nofollow'] = 'No';
		} else {
			$l_instance['nofollow'] = $p_old_instance['nofollow'];
		}
        
        if ('Yes' == $p_new_instance['animation']) {
			$l_instance['animation'] = 'Yes';
		} else if ('No' == $p_new_instance['animation']) {
			$l_instance['animation'] = 'No';
		} else {
			$l_instance['animation'] = $p_old_instance['animation'];
		}

		$l_instance['on_single_display'] = sanitize_text_field( $p_new_instance['on_single_display'] );

		$l_instance['show_count'] = sanitize_text_field( $p_new_instance['show_count'] );

		$l_instance['include'] = sanitize_text_field( $p_new_instance['include'] );
        $l_instance['exclude'] = sanitize_text_field( $p_new_instance['exclude'] );
        
        $l_instance['child_of'] = sanitize_text_field( $p_new_instance['child_of'] );
        
        $l_instance['max_height'] = sanitize_text_field( $p_new_instance['max_height'] );

        $l_instance['max_height_button_open'] = sanitize_text_field( $p_new_instance['max_height_button_open'] );
        $l_instance['max_height_button_close'] = sanitize_text_field( $p_new_instance['max_height_button_close'] );

        $l_instance['element'] = sanitize_text_field( $p_new_instance['element'] );
		
		return $l_instance;
	}

	//get taxonomy
	function _get_current_taxonomy($p_instance) {

        // if not array make it into an array
        if ( ! empty( $p_instance['taxonomy'] ) && is_string( $p_instance['taxonomy'] ) ) {
            $p_instance['taxonomy'] = array( $p_instance['taxonomy'] );
        }

        if ( ! empty($p_instance['taxonomy'] ) ) {
			return $p_instance['taxonomy'];
        }
        
		return $this->m_defaults['taxonomy'];
	}
}

function ctc_nofollow_tag_cloud($text) {
    return str_replace('<a href=', '<a rel="nofollow" href=',  $text);	
}

function ctc_remove_anchor( $text ) {
    $text = str_replace('<a href=', '<span href=',  $text );
    $text = str_replace('</a>', '</span>',  $text );
    $text = preg_replace('/ href=("|\')(.*?)("|\')/','', $text );
    return $text;
}

function cool_tag_cloud_register_widget() {
	register_widget( "Cool_Tag_Cloud_Widget" );
} add_action('widgets_init', 'cool_tag_cloud_register_widget' );

function cool_tag_cloud_files() {
	
	$purl = plugins_url();

	$important = true;
	if ( ( defined( 'COOL_TAG_CLOUD_IMPORTANT' ) && COOL_TAG_CLOUD_IMPORTANT == false ) || get_option( 'cool_tag_cloud_important' ) == 'no' ) {
		$important = false;
	}

	if ( $important ) {
		wp_enqueue_style('cool-tag-cloud', $purl . '/cool-tag-cloud/inc/cool-tag-cloud.css', array(), '2.25' );
	} else {
		wp_enqueue_style('cool-tag-cloud', $purl . '/cool-tag-cloud/inc/cool-tag-cloud-regular.css', array(), '2.25' );
	}

} add_action('wp_enqueue_scripts', 'cool_tag_cloud_files');

function cool_tag_cloud_setup(){
    load_plugin_textdomain('cool-tag-cloud');
}
add_action('init', 'cool_tag_cloud_setup');

function cool_tag_cloud_sc( $atts = array(), $content = false ) {

	$defaults = array( 
		'font_weight'   => 'normal',
		'font_family'   => 'Arial, Helvetica, sans-serif',
		'smallest'      => 10,
		'largest'       => 10,
		'format'        => 'flat',
		'separator'     => '',
		'unit'          => 'px',
		'number'        => 20,
		'orderby'       => 'name',
		'order'         => 'ASC',
		'taxonomy'      => 'post_tag',
		'exclude'       => null,
		'include'       => null,
		'text_transform' => 'none',
		'nofollow'      => 'no',
		'style'    => 'default',
		'align'    => 'left',
        'animation'     => 'no',
        'on_single_display' => 'global',
        'show_count' => 'no',
        'child_of' => '',
        'max_height' => '',
        'max_height_button_open' => 'Show More',
        'max_height_button_close' => 'Show Less',
        'element' => 'a',
	);

	$show = true;

	ob_start();

		$l_tag_params = wp_parse_args($atts, $defaults);
		$l_tag_params['echo'] = false;
        if ($l_tag_params["nofollow"] == 'yes') {add_filter('wp_tag_cloud', 'ctc_nofollow_tag_cloud');};
        if ($l_tag_params['element'] == 'span') {add_filter('wp_tag_cloud', 'ctc_remove_anchor');};
		if ( $l_tag_params['on_single_display'] == 'local' && ( is_single() || is_singular( array( 'post', 'page' ) ) ) ) {
			$tag_ids = wp_get_post_terms( get_the_ID(), $l_tag_params['taxonomy'], array( 'fields' => 'ids' ) );
			if ( empty( $tag_ids ) ) {
				$show = false;
			}
			if ( ! empty( $l_tag_params['exclude'] ) ) {
				$exlude_tags = explode( ',', str_replace( ' ', '', $l_tag_params['exclude'] ) );
				foreach ( $exlude_tags as $exlude_tag ) {
					foreach ( $tag_ids as $tag_key => $tag_id ) {
						if ( $tag_id == $exlude_tag ) {
							unset( $tag_ids[$tag_key] );
						}
					}
				}
			}
			$l_tag_params['include'] = $tag_ids;
		}
		if ( $l_tag_params['show_count'] == 'yes' ) {
			$l_tag_params['show_count'] = true;
		} else {
			$l_tag_params['show_count'] = false;
        }
		add_filter( 'wp_generate_tag_cloud_data', 'cool_tag_cloud_active_tag' );
		if ( $show ) {
			$l_tag_cloud_text = cool_tag_cloud_wp_tag_cloud( $l_tag_params  );
		} else {
			$l_tag_cloud_text = '';
		}
		remove_filter( 'wp_generate_tag_cloud_data', 'cool_tag_cloud_active_tag' );
        if ($l_tag_params["nofollow"] == 'yes') {remove_filter('wp_tag_cloud', 'ctc_nofollow_tag_cloud');};
        if ($l_tag_params['element'] == 'span') {remove_filter('wp_tag_cloud', 'ctc_remove_anchor');};

		echo '<div class="cool-tag-cloud">';
			if ($l_tag_params["font_weight"] == "bold") {echo '<div class="cloudbold">';}
				if ($l_tag_params["animation"] == "yes") {echo '<div class="animation">';}
					echo '<div class="ctc' . esc_attr( $l_tag_params["style"] ) . '">';
						echo '<div class="ctc' . esc_attr( $l_tag_params["align"] ) . '">';
							echo '<div class="' . esc_attr( $l_tag_params["font_family"] ) . '" style="text-transform:' . esc_attr( $l_tag_params["text_transform"] ) . '!important;">';
                                if ( ! empty( $l_tag_params['max_height'] ) ) {
                                    echo '<div class="cool-tag-cloud-inner" style="max-height:' . intval( $l_tag_params['max_height'] ) . 'px;">';
                                }
                                echo $l_tag_cloud_text;
                                if ( ! empty( $l_tag_params['max_height'] ) ) {
                                    echo '</div>';
                                }
                                if ( ! empty( $l_tag_params['max_height'] ) ) {
                                    echo '<span class="cool-tag-cloud-load-more">
                                        <span class="cool-tag-cloud-open" onclick="coolTagCloudToggle(this)">' . $l_tag_params['max_height_button_open'] . '</span>
                                        <span class="cool-tag-cloud-close" onclick="coolTagCloudToggle(this)">' . $l_tag_params['max_height_button_close'] . '</span>
                                    </span>';
                                }
							echo '</div>';
						echo '</div>';
					echo '</div>';
				if ($l_tag_params["animation"] == "yes") {echo '</div>';}
			if ($l_tag_params["font_weight"] == "bold") {echo '</div>';}
		echo '</div>';		

	$output = ob_get_contents();
	ob_end_clean();

	return $output;

} add_shortcode( 'cool_tag_cloud', 'cool_tag_cloud_sc' );

function cool_tag_cloud_active_tag( $tags_data ) {

	if ( is_singular( 'post' ) ) {
		
		$args = array('orderby' => 'name', 'order' => 'ASC', 'fields' => 'ids');
		$current_post_tags = wp_get_post_terms( get_the_ID(), 'post_tag', $args );
		$current_post_cats = wp_get_post_terms( get_the_ID(), 'category', $args );

		foreach ( $tags_data as $key => $tag ) {
			if ( in_array( $tag['id'], $current_post_tags ) ) {
				$tags_data[$key]['class'] =  $tags_data[$key]['class'] . ' ctc-active';
			}
			if ( in_array( $tag['id'], $current_post_cats ) ) {
				$tags_data[$key]['class'] =  $tags_data[$key]['class'] . ' ctc-active';
			}
		}

	}

	return $tags_data;

}

function cool_tag_cloud_footer_scripts() {
    ?>
    <script>
        function coolTagCloudToggle( element ) {
            var parent = element.closest('.cool-tag-cloud');
            parent.querySelector('.cool-tag-cloud-inner').classList.toggle('cool-tag-cloud-active');
            parent.querySelector( '.cool-tag-cloud-load-more').classList.toggle('cool-tag-cloud-active');
        }
    </script>
    <?php
} add_action( 'wp_footer', 'cool_tag_cloud_footer_scripts' );

/**
 * Modified original wp_tag_cloud to not force order by parameters
 */
function cool_tag_cloud_wp_tag_cloud( $args = '' ) {
	$defaults = array(
		'smallest'   => 8,
		'largest'    => 22,
		'unit'       => 'pt',
		'number'     => 45,
		'format'     => 'flat',
		'separator'  => "\n",
		'orderby'    => 'name',
		'order'      => 'ASC',
		'exclude'    => '',
		'include'    => '',
		'link'       => 'view',
		'taxonomy'   => 'post_tag',
		'post_type'  => '',
		'echo'       => true,
		'show_count' => 0,
	);

	$args = wp_parse_args( $args, $defaults );

	if ( $args['orderby'] == 'count_name' ) { 
		$tags = get_terms(
			array_merge(
				$args,
				array(
					'orderby' => 'count',
					'order'   => 'DESC',
				)
			)
		);
		$args['orderby'] = 'name';
	} else {
		$tags = get_terms( $args );
	}

	if ( empty( $tags ) || is_wp_error( $tags ) ) {
		return;
	}

	foreach ( $tags as $key => $tag ) {
		if ( 'edit' === $args['link'] ) {
			$link = get_edit_term_link( $tag->term_id, $tag->taxonomy, $args['post_type'] );
		} else {
			$link = get_term_link( (int) $tag->term_id, $tag->taxonomy );
		}

		if ( is_wp_error( $link ) ) {
			return;
		}

		$tags[ $key ]->link = $link;
		$tags[ $key ]->id   = $tag->term_id;
	}

	// Here's where those top tags get sorted according to $args.
	$return = wp_generate_tag_cloud( $tags, $args );

	/**
	 * Filters the tag cloud output.
	 *
	 * @since 2.3.0
	 *
	 * @param string|string[] $return Tag cloud as a string or an array, depending on 'format' argument.
	 * @param array           $args   An array of tag cloud arguments. See wp_tag_cloud()
	 *                                for information on accepted arguments.
	 */
	$return = apply_filters( 'wp_tag_cloud', $return, $args );

	if ( 'array' === $args['format'] || empty( $args['echo'] ) ) {
		return $return;
	}

	echo $return;
}