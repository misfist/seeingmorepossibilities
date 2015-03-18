<?php

// WP_Testimonial class

final class WP_Testimonial {

	/* Members */
	public $client;
	public $company;
	public $email;
	public $website;
    public $category;

	// Constructor
	public function __construct ( $post_id = null ) {

		if( !is_null( $post_id ) ) {

			$testimonial = self::get_instance( $post_id );
			$meta = get_post_meta( $testimonial->ID, '' );
            //Added - Pea 3/14/2015
            $term_list = wp_get_post_terms( $testimonial->ID, 'testimonial_category', array("fields" => "slugs") );

			// Copy WP_Post public members
			foreach( $testimonial as $key => $value )
				$this->$key = $value;

			// Assign WP_Testimonial specific members
			$this->client = $meta['testimonial_client_name'][0];
			$this->company = $meta['testimonial_client_company_name'][0];
			$this->email = $meta['testimonial_client_email'][0];
			$this->website = $meta['testimonial_client_company_website'][0];
            //Added - Pea 3/14/2015
            $this->category = $term_list[0];

		}

	}

	/**
	 * Render a testimonial.
	 *
	 * @param string $context
	 * @return string
	 */
	public function render( $context = 'shortcode' ) {

		do_action( 'ct_before_render_testimonial', $this, $context );

		// Allow plugins/themes to completely filter how a testimonial is rendered.
		// If this filter returns 1 character or more, it will override the default render process
		$pre_render = apply_filters( 'ct_pre_render_testimonial', '', $this, $context );

		if ( strlen( $pre_render ) >= 1 ) {
			echo $pre_render;
		}
		else {

			ob_start();
			?>
			<div class="single-testimonial testimonial-<?php echo $this->ID; ?> category-<?php echo $this->category; ?>">

				<h3 class="testimonial-title"><?php echo $this->post_title; ?></h3>

				<blockquote class="testimonial-content">

					<?php if( has_post_thumbnail( $this->ID ) ): $image = wp_get_attachment_image_src( get_post_thumbnail_id( $this->ID ), array( 200, 200 ) ); ?>
					<img src="<?php echo $image[0]; ?>" width="<?php echo $image[1]; ?>" height="<?php echo $image[2]; ?>" />
					<?php endif; ?>
                    
                    <p>
					<?php

					if( isset( $this->word_limit ) && $this->word_limit > 0 ) {

						$words = explode( ' ', $this->post_content );
						echo implode( ' ',
							( count( $words ) <= $this->word_limit ? $words : array_slice( $words, 0, $this->word_limit ) )
						) . '... <a href="' . get_permalink( $this->ID ) . '">Read More</a>';

					}
					else echo $this->post_content;

					?>
                    
                    </p>    
                    
                    <div class="testimonial-client meta">
                        <cite class="testimonial-client-name"><?php echo $this->client; ?></cite>
                        <?php if( !empty( $this->company ) ): ?>
                            <cite class="testimonial-client-title"><?php echo $this->company; ?></cite>
                        <?php endif; ?>
                    </div>

				</blockquote>

                <?php if( testimonial_has_permission( $this->ID ) ): ?>
                <?php echo sprintf( '<div class="testimonial-contact vcard"><span class="website"><a href="%s" class="testimonial-web">%s</a></span> <span class="email"><a href="mailto:%s" class="testimonial-email">%s</a></span></div>', $this->website, $this->website, $this->email, $this->email ); ?>
                <?php endif; ?>


			</div>

			<?php

			// Allow plugins and themes to filter the default testimonial render markup
			echo apply_filters( 'ct_render_testimonial', ob_get_clean(), $this, $context );

		}

		do_action( 'ct_after_render_testimonial', $this, $context );

	}

	public static function get_instance ( $post_id ) {

		return WP_Post::get_instance( $post_id );

	}

}

?>
