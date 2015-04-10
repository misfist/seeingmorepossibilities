<?php
/*
Plugin Name: Posts by Category Widget (SMP)
Plugin URI: http://seeingmorepossibilities.com
Description: Allows you to display in a widget a post from each category.
Author: Pea
Version: 0.1
Author URI: https://patricia-lutz.com
*/

// Ensure WordPress has been bootstrapped
/************* POSTS BY CATEGORY WIDGET *****************/

add_action( 'widgets_init', 'smp_posts_by_category_widget' );
// Create the widget 
class smp_posts_by_category extends WP_Widget {

	function __construct() {
		parent::__construct(
		// Base ID of your widget
		'smp_widget', 

		// Widget name will appear in UI
		__('Posts by Category', 'smp_posts_by_category'), 

		// Widget description
		array( 'description' => __( 'Recent posts by category', 'smp_posts_by_category' ), ) 
		);
	}

	// Create widget front-end
	public function widget( $args, $instance ) {
		$title = apply_filters( 'widget_title', $instance['title'] );
		// before and after widget arguments are defined by themes
		echo $args['before_widget'];
		if ( ! empty( $title ) )
			echo $args['before_title'] . $title . $args['after_title'];

		// This is where you run the code and display the output
		echo __( smp_get_category_posts() , 'smp_posts_by_category' );
		
		echo $args['after_widget'];
	}
		
	// Widget Backend 
	public function form( $instance ) {
		if ( isset( $instance[ 'title' ] ) ) {
			$title = $instance[ 'title' ];
		}
		else {
			$title = __( 'New title', 'smp_posts_by_category' );
		}
		// Widget admin form
		?>
		<p>
		<label for="<?php echo $this->get_field_id( 'title' ); ?>"><?php _e( 'Title:' ); ?></label> 
		<input class="widefat" id="<?php echo $this->get_field_id( 'title' ); ?>" name="<?php echo $this->get_field_name( 'title' ); ?>" type="text" value="<?php echo esc_attr( $title ); ?>" />
		</p>
	<?php 
	}
	
	// Update widget replacing old instances with new
	public function update( $new_instance, $old_instance ) {
		$instance = array();
		$instance['title'] = ( ! empty( $new_instance['title'] ) ) ? strip_tags( $new_instance['title'] ) : '';
		return $instance;
	}
	
} // Class ends here

// Register and load the widget
function smp_posts_by_category_widget() {
	register_widget( 'smp_posts_by_category' );
}

// Input: None
// Output: Each category and most recent post, rendered as HTML list
function smp_get_category_posts() {
	
	$categories = get_categories();
	$html = '<ul class="category-post-list">';
	foreach($categories as $category) {
		$html .= '<li class="category-item ' . $category->slug . '">';
		$html .= '<h3 class="category-name">' . $category->name . '</h3>';
		
		$postargs = array( 
			'posts_per_page' => 1, 
			'category' => $category->term_id,
			'type' => 'post'
		);
		$catposts = get_posts( $postargs );
		
		$html .= '<ul class="post-list">';
		foreach($catposts as $post) {
			setup_postdata( $post );
			
			$excerpt = smp_auto_excerpt($post->post_content, get_permalink( $post->ID ));
						
			$html .= '<li id="post-' . $post->ID . '">';
			$html .=  '<a href="' . get_permalink( $post->ID ) . '"><h4 class="post-title">' . $post->post_title . '</h4></a>';
			//$html .= get_the_excerpt($post->ID);
			$html .= strip_shortcodes($excerpt);
			$html .= '</li>';
		}
		$html .= '</ul>';
		
		$html .= '</li>';
	}
	return $html;
}

function smp_auto_excerpt($text, $permalink) {
// Creates an excerpt if needed; and shortens the manual excerpt as well
	global $post;
	  $raw_excerpt = $text;
	  if ( '' == $text ) {
		$text = get_the_content('');
		$text = strip_shortcodes( $text );
		$text = apply_filters('the_content', $text);
		$text = str_replace(']]>', ']]&gt;', $text);
	  }

	$excerpt_length = apply_filters('excerpt_length', 20);
	$excerpt_more = apply_filters('excerpt_more', ' <a href="' . $permalink . '" class="read-more">Read More</a>');
	$excerpt = wp_trim_words( $text, $excerpt_length, $excerpt_more );
	return apply_filters('wp_trim_excerpt', $excerpt, $raw_excerpt);
}
 
add_filter('get_the_excerpt', 'smp_auto_excerpt');


?>
