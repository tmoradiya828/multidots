<?php
/*
Plugin Name: Library Book Search
Plugin URI: #
Description: This exercise is about creating a book listing page with search and sorting
functionality on front-side of a WordPress website.
Author: Tushar Moradiya
Version: 1.0
textdomain : books
*/

function register_my_cpts_books() {

	/**
	 * Post Type: Books.
	 */

	$labels = [
		"name" => __( "Books", "books" ),
		"singular_name" => __( "Book", "books" ),
		'add_new' => 'Add Book',
        'all_items' => 'All Books',
        'add_new_item' => 'Add Book',
        'edit_item' => 'Edit Book',
        'new_item' => 'New Book',
        'view_item' => 'View Book',
        'search_item_label' => 'Search Books',
        'not_found' => 'No Books Found',
        'not_found_in_trash' => 'No Books Found in Trash',
        'parent_item_colon' => 'Parent Book'
	];

	$args = [
		"label" => __( "Books", "books" ),
		"labels" => $labels,
		"description" => "",
		"public" => true,
		"publicly_queryable" => true,
		"show_ui" => true,
		"show_in_rest" => true,
		"rest_base" => "",
		"rest_controller_class" => "WP_REST_Posts_Controller",
		"has_archive" => false,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"delete_with_user" => false,
		"exclude_from_search" => false,
		"capability_type" => "post",
		"map_meta_cap" => true,
		"hierarchical" => false,
		"rewrite" => [ "slug" => "books", "with_front" => true ],
		"query_var" => true,
		"supports" => [ "title", "editor" ],
	];

	register_post_type( "books", $args );
}

add_action( 'init', 'register_my_cpts_books' );


function register_my_taxes_bk_author() {

	/**
	 * Taxonomy: Authors.
	 */

	$labels = [
		"name" => __( "Authors", "books" ),
		"singular_name" => __( "Author", "books" ),
	];

	$args = [
		"label" => __( "Authors", "books" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'bk_author', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"rest_base" => "bk_author",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => false,
		];
	register_taxonomy( "bk_author", [ "books" ], $args );
}
add_action( 'init', 'register_my_taxes_bk_author' );


function register_my_taxes_bk_publisher() {

	/**
	 * Taxonomy: Publishers.
	 */

	$labels = [
		"name" => __( "Publishers", "books" ),
		"singular_name" => __( "Publisher", "books" ),
	];

	$args = [
		"label" => __( "Publishers", "books" ),
		"labels" => $labels,
		"public" => true,
		"publicly_queryable" => true,
		"hierarchical" => false,
		"show_ui" => true,
		"show_in_menu" => true,
		"show_in_nav_menus" => true,
		"query_var" => true,
		"rewrite" => [ 'slug' => 'bk_publisher', 'with_front' => true, ],
		"show_admin_column" => false,
		"show_in_rest" => true,
		"rest_base" => "bk_publisher",
		"rest_controller_class" => "WP_REST_Terms_Controller",
		"show_in_quick_edit" => false,
		];
	register_taxonomy( "bk_publisher", [ "books" ], $args );
}
add_action( 'init', 'register_my_taxes_bk_publisher' );


/**
 * Register meta box(es).
 */
function wpdocs_register_meta_boxes() {
    add_meta_box( '_book_price', __( 'Book Price', 'books' ), 'price_my_display_callback', 'books' );
    add_meta_box( '_book_rating', __( 'Book Rating', 'books' ), 'rating_my_display_callback', 'books' );
}
add_action( 'add_meta_boxes', 'wpdocs_register_meta_boxes' );
 
/**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function price_my_display_callback( $post ) {
  
	$price = get_post_meta( $post->ID, '_price', true ); // Get the saved values
	?>

		<fieldset>
			<div>
				<label for="_price">
					<?php
						_e( '$', 'books' );
					?>
				</label>
				<input
					type="number"
					name="_price"
					id="_price"
					value="<?php echo esc_attr( $price ); ?>"
				>
			</div>
		</fieldset>

	<?php
	wp_nonce_field( '_price_form_metabox_nonce', '_price_form_metabox_process' );
}
 

 /**
 * Meta box display callback.
 *
 * @param WP_Post $post Current post object.
 */
function rating_my_display_callback( $post ) {
  
	$rating = get_post_meta( $post->ID, '_rating', true ); // Get the saved values
	?>

		<fieldset>
			<div>
				<input
					type="number"
					name="_rating"
					id="_rating"
					value="<?php echo esc_attr( $rating ); ?>"
					min="0" max="5"
				>
			</div>
		</fieldset>

	<?php
	wp_nonce_field( '_rating_form_metabox_nonce', '_rating_form_metabox_process' );
}

/**
 * Save the metabox Price
 * @param  Number $post_id The post ID
 * @param  Array  $post    The post data
 */
function _price_save_metabox( $post_id, $post ) {

	// Verify that our security field exists. If not, bail.
	if ( !isset( $_POST['_price_form_metabox_process'] ) ) return;

	// Verify data came from edit/dashboard screen
	if ( !wp_verify_nonce( $_POST['_price_form_metabox_process'], '_price_form_metabox_nonce' ) ) {
		return $post->ID;
	}

	// Verify user has permission to edit post
	if ( !current_user_can( 'edit_post', $post->ID )) {
		return $post->ID;
	}

	// Check that our custom fields are being passed along
	// This is the `name` value array. We can grab all
	// of the fields and their values at once.
	if ( !isset( $_POST['_price'] ) ) {
		return $post->ID;
	}
	/**
	 * Sanitize the submitted data
	 * This keeps malicious code out of our database.
	 * `wp_filter_post_kses` strips our dangerous server values
	 * and allows through anything you can include a post.
	 */
	$sanitized = wp_filter_post_kses( $_POST['_price'] );
	// Save our submissions to the database
	update_post_meta( $post->ID, '_price', $sanitized );

}
add_action( 'save_post', '_price_save_metabox', 1, 2 );

/**
 * Save the metabox Rating
 * @param  Number $post_id The post ID
 * @param  Array  $post    The post data
 */
function _rating_save_metabox( $post_id, $post ) {

	// Verify that our security field exists. If not, bail.
	if ( !isset( $_POST['_rating_form_metabox_process'] ) ) return;

	// Verify data came from edit/dashboard screen
	if ( !wp_verify_nonce( $_POST['_rating_form_metabox_process'], '_rating_form_metabox_nonce' ) ) {
		return $post->ID;
	}

	// Verify user has permission to edit post
	if ( !current_user_can( 'edit_post', $post->ID )) {
		return $post->ID;
	}

	// Check that our custom fields are being passed along
	// This is the `name` value array. We can grab all
	// of the fields and their values at once.
	if ( !isset( $_POST['_rating'] ) ) {
		return $post->ID;
	}
	/**
	 * Sanitize the submitted data
	 * This keeps malicious code out of our database.
	 * `wp_filter_post_kses` strips our dangerous server values
	 * and allows through anything you can include a post.
	 */
	$sanitized = wp_filter_post_kses( $_POST['_rating'] );
	// Save our submissions to the database
	update_post_meta( $post->ID, '_rating', $sanitized );

}
add_action( 'save_post', '_rating_save_metabox', 1, 2 );

function assets() {

    wp_enqueue_script('jquery-3.5.1', site_url().'/wp-content/plugins/library-book-search/script/jquery-3.5.1.js', ['jquery'], null, true);
    wp_enqueue_script('jquery.dataTables.min', site_url().'/wp-content/plugins/library-book-search/script/jquery.dataTables.min.js', ['jquery'], null, true);
    wp_enqueue_script('book-js', site_url().'/wp-content/plugins/library-book-search/script/custom.js', ['jquery'], null, true);

    wp_enqueue_style( 'jquery-dataTables-css',  site_url().'/wp-content/plugins/library-book-search/css/jquery.dataTables.min.css' );
    wp_enqueue_style( 'font-awesome.min',  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css' );
    wp_enqueue_style( 'book-css',  site_url().'/wp-content/plugins/library-book-search/css/custom.css' );

    wp_localize_script( 'book-js', 'books', array(
        'nonce'    => wp_create_nonce( 'books' ),
        'ajax_url' => admin_url( 'admin-ajax.php' )
    ));
}
add_action('wp_enqueue_scripts', 'assets', 100);

function book_listing(){
	
	$args = array(
	    'posts_per_page'   => -1,// get all post,
	    'post_type' => 'books',
	);
	$listing_book = list_books($args);
 	echo $listing_book; 
}

add_shortcode('book_listing', 'book_listing');

function filter_html(){

	$f_html = '';
	$f_html .= '<form action="'.site_url() .'/wp-admin/admin-ajax.php" method="POST" id="filter">';
	$f_html .= '<div class="row"><h2>Book Search</h2></div>';
	$f_html .= '<div class="row">';
	$f_html .= '<div class="col-6"><label> Book name :</label>';
	$f_html .= '<input type="text" name="bk_name"></div>';
	$f_html .= '<div class="col-6"><label> Author :</label>';
	$f_html .= '<input type="text" name="bk_author"></div>';
	$f_html .= '</div>';
	$f_html .= '<div class="row">';
	$f_html .= '<div class="col-6"><label> Publisher :</label>';
	if( $terms = get_terms( array( 'taxonomy' => 'bk_publisher', 'orderby' => 'name' ) ) ) : 
 
			$f_html .= '<div class="custom-select"><select  name="bk_publisher"><option value="">Select publisher...</option>';
			foreach ( $terms as $term ) :
				$f_html .='<option value="' . $term->term_id . '">' . $term->name . '</option>'; 
			endforeach;
			$f_html .= '</select></div>';
		endif;
	$f_html .= '</div>';
	$f_html .= '<div class="col-6"><label> Rating :</label>';
	$f_html .= '<div class="custom-select"><select  name="bk_rating"><option value="">Select rating...</option>';
	for ($i=1; $i <= 5 ; $i++) { 
	$f_html .='<option value="'.$i.'">'.$i.'</option>'; 
	}
	$f_html .= '</select>';
	$f_html .= '</div>';
	$f_html .= '</div>';
	$f_html .= '</div>';

	$f_html .= '<div class="row"><div class="col-12"><label> Price :</label>';
	$f_html .= '<div class="range-slider">';
  	$f_html .= '<input class="range-slider__range" type="range" name="bk_price" value="100" min="0" max="3000">';
  	$f_html .= '$<span class="range-slider__value">0</span>';
	$f_html .= '</div>';
	$f_html .= '</div></div>';

	$f_html .= '<div class="row"><div class="col-12"><button>Search</button>';
	$f_html .= '<input type="hidden" name="action" value="myfilter"></div></div>';
	$f_html .= '</form>';
	echo $f_html;

}


add_shortcode('filters_posts', 'filter_html');


add_action('wp_ajax_myfilter', 'misha_filter_function'); // wp_ajax_{ACTION HERE} 
add_action('wp_ajax_nopriv_myfilter', 'misha_filter_function');
 
function misha_filter_function(){

	$args = array(
		'posts_per_page'   => -1,// get all post,
	    'post_type' => 'books',
	    'orderby' => 'date', // we will sort posts by date
		'order'	=> 'ASC' // ASC or DESC
	);

	// for taxonomies / categories
	if( isset( $_POST['bk_publisher'] ) && !empty($_POST['bk_publisher']) )
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'bk_publisher',
				'field' => 'id',
				'terms' => $_POST['bk_publisher']
			)
		);

	// for taxonomies / categories
	if( isset( $_POST['bk_author'] ) && !empty($_POST['bk_author']) )
		$args['tax_query'] = array(
			array(
				'taxonomy' => 'bk_author',
				'field' => 'name',
				'terms' => $_POST['bk_author']
			)
		);


	 $args['meta_query'] = array(
	 	 'relation' => 'OR',
      array(
         'key'     => '_rating',
         'value'   => $_POST['bk_rating'],
         'compare'   => 'LIKE',
      ),
      array(
         'key'     => '_price',
         'value'   => $_POST['bk_price'],
         'compare'   => 'LIKE',
      )
   );
	
 	$listing_book = list_books($args);
 	echo $listing_book;
	die();
}

function list_books($args = array()){

	$books_posts = get_posts($args);
	$html = '';
 	$html .= '<table id="example" class="display">';

	$html .= '<thead>
            <tr>
                <th>No</th>
                <th>Book Name</th>
                <th>Price</th>
                <th>Author</th>
                <th>Publisher</th>
                <th>Rating</th>
            </tr>
        </thead>';

 	if( $books_posts) :
	$b_count = 1;
	$html .= '<tbody>';
	foreach ($books_posts as $books_post) { 
		$meta_price = get_post_meta($books_post->ID, '_price', true);
		$meta_rating = get_post_meta($books_post->ID, '_rating', true);
		$s_bk_publisher = wp_get_object_terms($books_post->ID, 'bk_publisher', array("fields" => "all"));
		$s_bk_author = wp_get_object_terms($books_post->ID, 'bk_author', array("fields" => "all"));

		$html .= '<tr>';
		$html .= '<td >'.$b_count.'</td>';
		$html .= '<td ><a href="'.get_permalink($books_post->ID).'" target="_blank">'.$books_post->post_title.'</a></td>';
		$html .= '<td >$'.$meta_price.'</td>';
		$html .= '<td ><a  href="'.get_term_link($s_bk_publisher['0']).'" target="_blank">'.$s_bk_publisher['0']->name.'</a></td>';
		$html .= '<td ><a  href="'.get_term_link($s_bk_author['0']).'" target="_blank">'.$s_bk_author['0']->name.'</a></td>';
		$html .= '<td>';
			for ($i=0; $i < 5 ; $i++) { 
				if($i < $meta_rating ){
					$html .= '<span class="fa fa-star checked"></span>';
				}else{
					$html .= '<span class="fa fa-star"></span>';
				}
			}
		$html .= '</td>';
		$html .= '</tr>';
		$b_count++;
	}
	$html .= '</tbody>';
	else :
		$html .= 'No books found';
	endif;
	$html .= '</table>';
 return $html;
}

