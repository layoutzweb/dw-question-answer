<?php

/**
 * Print class for question detail container
 */
function dwqa_breadcrumb() {
	global $dwqa_general_settings;
	$title = get_the_title( $dwqa_general_settings['pages']['archive-question'] );
	$search = isset( $_GET['qs'] ) ? $_GET['qs'] : false;
	$author = isset( $_GET['user'] ) ? $_GET['user'] : false;

	if ( !is_singular( 'dwqa-question' ) ) {
		$term = get_query_var( 'dwqa-question_category' ) ? get_query_var( 'dwqa-question_category' ) : ( get_query_var( 'dwqa-question_tag' ) ? get_query_var( 'dwqa-question_tag' ) : false );
		$term = get_term_by( 'slug', $term, get_query_var( 'taxonomy' ) );
		$tax_name = 'dwqa-question_tag' == get_query_var( 'taxonomy' ) ? __( 'Tag', 'dwqa' ) : __( 'Category', 'dwqa' );
	} else {
		$term = wp_get_post_terms( get_the_ID(), 'dwqa-question_category' );
		if ( $term ) {
			$term = $term[0];
			$tax_name = __( 'Category', 'dwqa' );
		}
	}

	if ( is_singular( 'dwqa-question' ) || $search || $author || $term ) {
		echo '<div class="dwqa-breadcrumbs">';
	}

	if ( $term || is_singular( 'dwqa-question' ) || $search || $author ) {
		echo '<a href="'. get_permalink( $dwqa_general_settings['pages']['archive-question'] ) .'">' . $title . '</a>';
	}

	if ( $term ) {
		echo '<span class="dwqa-sep"> &rsaquo; </span>';
		if ( is_singular( 'dwqa-question' ) ) {
			echo '<a href="'. esc_url( get_term_link( $term, get_query_var( 'taxonomy' ) ) ) .'">' . $tax_name . ': ' . $term->name . '</a>';
		} else {
			echo '<span class="dwqa-current">' . $tax_name . ': ' . $term->name . '</span>';
		}
	}

	if ( $search ) {
		echo '<span class="dwqa-sep"> &rsaquo; </span>';
		printf( '<span class="dwqa-current">%s "%s"</span>', __( 'Showing search results for', 'dwqa' ), rawurldecode( $search ) );
	}

	if ( $author ) {
		echo '<span class="dwqa-sep"> &rsaquo; </span>';
		printf( '<span class="dwqa-current">%s "%s"</span>', __( 'Author', 'dwqa' ), rawurldecode( $author ) );
	}

	if ( is_singular( 'dwqa-question' ) ) {
		echo '<span class="dwqa-sep"> &rsaquo; </span>';
		if ( !dwqa_is_edit() ) {
			echo '<span class="dwqa-current">' . get_the_title() . '</span>';
		} else {
			echo '<a href="'. get_permalink() .'">'. get_the_title() .'</a>';
			echo '<span class="dwqa-sep"> &rsaquo; </span>';
			echo '<span class="dwqa-current">'. __( 'Edit', 'dwqa' ) .'</span>';
		}
	}
	if ( is_singular( 'dwqa-question' ) || $search || $author || $term ) {
		echo '</div>';
	}
}
add_action( 'dwqa_before_questions_archive', 'dwqa_breadcrumb' );
add_action( 'dwqa_before_single_question', 'dwqa_breadcrumb' );

function dwqa_archive_question_filter_layout() {
	global $dwqa_general_settings;
	$sort = isset( $_GET['sort'] ) ? $_GET['sort'] : '';
	$filter = isset( $_GET['filter'] ) ? $_GET['filter'] : 'all';
	ob_start();
	?>
	<div class="dwqa-question-filter">
		<span><?php _e( 'Filter:', 'dwqa' ); ?></span>
		<?php if ( !isset( $_GET['user'] ) ) : ?>
			<a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'all' ) ) ) ?>" class="<?php echo 'all' == $filter ? 'active' : '' ?>"><?php _e( 'All', 'dwqa' ); ?></a>
			<a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'open' ) ) ) ?>" class="<?php echo 'open' == $filter ? 'active' : '' ?>"><?php _e( 'Open', 'dwqa' ); ?></a>
			<a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'resolved' ) ) ) ?>" class="<?php echo 'resolved' == $filter ? 'active' : '' ?>"><?php _e( 'Resolved', 'dwqa' ); ?></a>
			<a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'closed' ) ) ) ?>" class="<?php echo 'closed' == $filter ? 'active' : '' ?>"><?php _e( 'Closed', 'dwqa' ); ?></a>
			<a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'unanswered' ) ) ) ?>" class="<?php echo 'unanswered' == $filter ? 'active' : '' ?>"><?php _e( 'Unanswered', 'dwqa' ); ?></a>
			<?php if ( is_user_logged_in() ) : ?>
				<a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'my-questions' ) ) ) ?>" class="<?php echo 'my-questions' == $filter ? 'active' : '' ?>"><?php _e( 'My questions', 'dwqa' ); ?></a>
				<a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'my-subscribes' ) ) ) ?>" class="<?php echo 'my-subscribes' == $filter ? 'active' : '' ?>"><?php _e( 'My subscribes', 'dwqa' ); ?></a>
			<?php endif; ?>
		<?php else : ?>
			<a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'all' ) ) ) ?>" class="<?php echo 'all' == $filter ? 'active' : '' ?>"><?php _e( 'Questions', 'dwqa' ); ?></a>
			<a href="<?php echo esc_url( add_query_arg( array( 'filter' => 'subscribes' ) ) ) ?>" class="<?php echo 'subscribes' == $filter ? 'active' : '' ?>"><?php _e( 'Subscribes', 'dwqa' ); ?></a>
		<?php endif; ?>
		<select id="dwqa-sort-by" class="dwqa-sort-by" onchange="this.options[this.selectedIndex].value && (window.location = this.options[this.selectedIndex].value);">
			<option selected disabled><?php _e( 'Sort by', 'dwqa' ); ?></option>
			<option <?php selected( $sort, 'views' ) ?> value="<?php echo esc_url( add_query_arg( array( 'sort' => 'views' ) ) ) ?>"><?php _e( 'Views', 'dwqa' ) ?></option>
			<option <?php selected( $sort, 'answers' ) ?> value="<?php echo esc_url( add_query_arg( array( 'sort' => 'answers' ) ) ) ?>"><?php _e( 'Answers', 'dwqa' ); ?></option>
			<option <?php selected( $sort, 'votes' ) ?> value="<?php echo esc_url( add_query_arg( array( 'sort' => 'votes' ) ) ) ?>"><?php _e( 'Votes', 'dwqa' ) ?></option>
		</select>
	</div>
	<?php
	echo apply_filters( 'dwqa_archive_question_filter_layout', ob_get_clean() );
}
add_action( 'dwqa_before_questions_archive', 'dwqa_archive_question_filter_layout', 12 );

function dwqa_search_form() {
	?>
	<form id="dwqa-search" class="dwqa-search">
		<input data-nonce="<?php echo wp_create_nonce( '_dwqa_filter_nonce' ) ?>" type="text" placeholder="<?php _e( 'What do you want to know?', 'dwqa' ); ?>" name="qs" value="<?php echo isset( $_GET['qs'] ) ? $_GET['qs'] : '' ?>">
	</form>
	<?php
}
add_action( 'dwqa_before_questions_archive', 'dwqa_search_form', 11 );

function dwqa_class_for_question_details_container(){
	$class = array();
	$class[] = 'question-details';
	$class = apply_filters( 'dwqa-class-questions-details-container', $class );
	echo implode( ' ', $class );
}

add_action( 'dwqa_after_answers_list', 'dwqa_answer_paginate_link' );
function dwqa_answer_paginate_link() {
	global $wp_query;
	$question_url = get_permalink();
	$page = isset( $_GET['ans-page'] ) ? $_GET['ans-page'] : 1;

	$args = array(
		'base' => add_query_arg( 'ans-page', '%#%', $question_url ),
		'format' => '',
		'current' => $page,
		'total' => $wp_query->dwqa_answers->max_num_pages
	);

	$paginate = paginate_links( $args );
	$paginate = str_replace( 'page-number', 'dwqa-page-number', $paginate );
	$paginate = str_replace( 'current', 'dwqa-current', $paginate );
	$paginate = str_replace( 'next', 'dwqa-next', $paginate );
	$paginate = str_replace( 'prev ', 'dwqa-prev ', $paginate );
	$paginate = str_replace( 'dots', 'dwqa-dots', $paginate );

	if ( $wp_query->dwqa_answers->max_num_pages > 1 ) {
		echo '<div class="dwqa-pagination">';
		echo $paginate;
		echo '</div>';
	}
}

function dwqa_question_paginate_link() {
	global $wp_query, $dwqa_general_settings;

	$archive_question_url = get_permalink( $dwqa_general_settings['pages']['archive-question'] );
	$page_text = dwqa_is_front_page() ? 'page' : 'paged';
	$page = get_query_var( $page_text ) ? get_query_var( $page_text ) : 1;

	$args = array(
		'base' => add_query_arg( $page_text, '%#%', $archive_question_url ),
		'format' => '',
		'current' => $page,
		'total' => $wp_query->dwqa_questions->max_num_pages
	);

	$paginate = paginate_links( $args );
	$paginate = str_replace( 'page-number', 'dwqa-page-number', $paginate );
	$paginate = str_replace( 'current', 'dwqa-current', $paginate );
	$paginate = str_replace( 'next', 'dwqa-next', $paginate );
	$paginate = str_replace( 'prev ', 'dwqa-prev ', $paginate );
	$paginate = str_replace( 'dots', 'dwqa-dots', $paginate );

	if ( $wp_query->dwqa_questions->max_num_pages > 1 ) {
		echo '<div class="dwqa-pagination">';
		echo $paginate;
		echo '</div>';
	}
}

function dwqa_question_button_action() {
	$html = '';
	if ( is_user_logged_in() ) {
		$followed = dwqa_is_followed() ? 'followed' : 'follow';
		$text = __( 'Subscribe', 'dwqa' );
		$html .= '<label for="dwqa-favorites">';
		$html .= '<input type="checkbox" id="dwqa-favorites" data-post="'. get_the_ID() .'" data-nonce="'. wp_create_nonce( '_dwqa_follow_question' ) .'" value="'. $followed .'" '. checked( $followed, 'followed', false ) .'/>';
		$html .= '<span>' . $text . '</span>';
		$html .= '</label>';
		if ( dwqa_current_user_can( 'edit_question' ) ) {
			$html .= '<a class="dwqa_edit_question" href="'. add_query_arg( array( 'edit' => get_the_ID() ), get_permalink() ) .'">' . __( 'Edit', 'dwqa' ) . '</a> ';
		}

		if ( dwqa_current_user_can( 'delete_question' ) ) {
			$action_url = add_query_arg( array( 'action' => 'dwqa_delete_question', 'question_id' => get_the_ID() ), admin_url( 'admin-ajax.php' ) );
			$html .= '<a class="dwqa_delete_question" href="'. wp_nonce_url( $action_url, '_dwqa_action_remove_question_nonce' ) .'">' . __( 'Delete', 'dwqa' ) . '</a> ';
		}
	}

	echo apply_filters( 'dwqa_question_button_action', $html );
}

function dwqa_answer_button_action() {
	$html = '';
	if ( is_user_logged_in() ) {
		if ( dwqa_current_user_can( 'edit_answer' ) ) {
			$parent_id = dwqa_get_question_from_answer_id();
			$html .= '<a class="dwqa_edit_question" href="'. add_query_arg( array( 'edit' => get_the_ID() ), get_permalink( $parent_id ) ) .'">' . __( 'Edit', 'dwqa' ) . '</a> ';
		}

		if ( dwqa_current_user_can( 'delete_answer' ) ) {
			$action_url = add_query_arg( array( 'action' => 'dwqa_delete_answer', 'answer_id' => get_the_ID() ), admin_url( 'admin-ajax.php' ) );
			$html .= '<a class="dwqa_delete_answer" href="'. wp_nonce_url( $action_url, '_dwqa_action_remove_answer_nonce' ) .'">' . __( 'Delete', 'dwqa' ) . '</a> ';
		}
	}

	echo apply_filters( 'dwqa_answer_button_action', $html );
}


function dwqa_question_add_class( $classes, $class, $post_id ){
	if ( get_post_type( $post_id ) == 'dwqa-question' ) {

		$have_new_reply = dwqa_have_new_reply();
		if ( $have_new_reply == 'staff-answered' ) {
			$classes[] = 'staff-answered';
		}
	}
	return $classes;
}
add_action( 'post_class', 'dwqa_question_add_class', 10, 3 );

/**
 * callback for comment of question
 */
function dwqa_answer_comment_callback( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	global $post;

	if ( get_user_by( 'id', $comment->user_id ) ) {
		dwqa_load_template( 'content', 'comment' );
	}
}


function dwqa_question_comment_callback( $comment, $args, $depth ) {
	$GLOBALS['comment'] = $comment;
	global $post;
	dwqa_load_template( 'content', 'comment' );
}


function dwqa_single_postclass( $post_class ){
	global $post, $current_user;

	if ( get_post_type( $post ) == 'dwqa-answer' ) {
		$post_class[] = 'dwqa-answer';
		$post_class[] = 'dwqa-status-' . get_post_status( $post->ID );

		if ( dwqa_is_answer_flag( $post->ID ) ) {
			$post_class[] = 'answer-flagged-content';
		}
		if ( user_can( $post->post_author, 'edit_published_posts' ) ) {
			$post_class[] = 'staff';
		}
		$question_id = get_post_meta( $post->ID, '_question', true );
		$best_answer_id = dwqa_get_the_best_answer( $question_id );
		if ( $best_answer_id && $best_answer_id == $post->ID ) {
			$post_class[] = 'best-answer';
		}

		if ( ! is_user_logged_in() ||  $current_user->ID != $post->ID || ! current_user_can( 'edit_posts' ) ) {
			$post_class[] = 'dwqa-no-click';
		}
	}

	if ( get_post_type( $post ) == 'dwqa-answer' && get_post_type( $post ) == 'dwqa-question' ) {
		if ( in_array( 'hentry', $post_class ) ) {
			unset( $post_class );
		}
	}

	return $post_class;
}
add_action( 'post_class', 'dwqa_single_postclass' );

function dwqa_require_field_submit_question(){
	?>
	<input type="hidden" name="dwqa-action" value="dwqa-submit-question" />
	<?php
		wp_nonce_field( 'dwqa-submit-question-nonce-#!' );
		$subscriber = get_role( 'subscriber' );
	?>
	<?php if ( ! is_user_logged_in() && ! dwqa_current_user_can( 'post_question' ) ) { ?>
	<input type="hidden" name="login-type" id="login-type" value="sign-up" autocomplete="off">
	<div class="question-register clearfix">
		<label for="user-email"><?php _e( 'You need an account to submit question and get answers. Create one:','dwqa' ) ?></label>
		<div class="register-email register-input">
			<input type="text" size="20" value="" class="input" placeholder="<?php _e( 'Type your email','dwqa' ) ?>" name="user-email">
		</div>
		<div class="register-username register-input">
			<input type="text" size="20" value="" class="input" placeholder="Choose an username" name="user-name-signup" id="user-name-signup">
		</div>
		<div class="login-switch"><?php _e( 'Already a member?','dwqa' ) ?> <a class="credential-form-toggle" href="<?php echo wp_login_url(); ?>"><?php _e( 'Log In','dwqa' ) ?></a></div>
	</div>

	<div class="question-login clearfix dwqa-hide">
		<label for="user-name"><?php _e( 'Login to submit your question','dwqa' ) ?></label>
		<div class="login-username login-input">
			<input type="text" size="20" value="" class="input" placeholder="<?php _e( 'Type your username','dwqa' ) ?>" id="user-name" name="user-name">
		</div>
		<div class="login-password login-input">
			<input type="password" size="20" value="" class="input" placeholder="<?php _e( 'Type your password','dwqa' ) ?>" id="user-password" name="user-password">
		</div>
		<div class="login-switch"><?php _e( 'Not yet a member?','dwqa' ) ?> <a class="credential-form-toggle" href="javascript:void( 0 );" title="<?php _e( 'Register','dwqa' ) ?>"><?php _e( 'Register','dwqa' ) ?></a></div>
	</div>
	<?php } else if ( ! is_user_logged_in() && dwqa_current_user_can( 'post_question' ) ) { ?>
	<div class="user-email">
		<label for="user-email" title="<?php _e( 'Enter your email to receive notification regarding your question. Your email is safe with us and will not be published.','dwqa' ) ?>"><?php _e( 'Your email *','dwqa' ) ?></label>
		<input type="email" name="_dwqa_anonymous_email" id="_dwqa_anonymous_email" class="large-text" placeholder="<?php _e( 'Email address ...','dwqa' ) ?>" required>
		<span><?php printf( __( 'or <strong><a href="%s">login</a></strong> to submit question', 'dwqa' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( get_the_ID() ) ) ) ) ?></span>
	</div>
	<?php  }
}
add_action( 'dwqa_submit_question_ui', 'dwqa_require_field_submit_question' );

function dwqa_require_field_submit_answer( $question_id ){
	// Nonce field for Sercure Answer Submit
	wp_nonce_field( '_dwqa_add_new_answer' );
	?>
	<input type="hidden" name="question" value="<?php echo $question_id; ?>" />
	<input type="hidden" name="answer-id" value="0" >
	<input type="hidden" name="dwqa-action" value="add-answer" />
	<?php if ( ! is_user_logged_in() ) { ?>
	<label for="answer_notify"><input type="checkbox" name="answer_notify" /> Notify me when have new comment to my answer</label>
	<div class="dwqa-answer-signin dwqa-hide">
		<input type="text" name="user-email" id="user-email" placeholder="<?php _e( 'Type your email','dwqa' ) ?>">
	</div>
	<?php } ?>
	<?php
}
add_action( 'dwqa_submit_answer_ui', 'dwqa_require_field_submit_answer' );


function dwqa_title( $title ){
	if ( defined( 'DWQA_FILTERING' ) && DWQA_FILTERING ) {
		global $post;
		if ( isset( $post->post_type ) && 'dwqa-question' == $post->post_type && isset( $post->post_status ) && 'private' == $post->post_status ) {
			$private_title_format = apply_filters( 'private_title_format', __( 'Private: %s' ) );
			$title = sprintf( $private_title_format, $title );
		}
	}
	return $title;
}
add_action( 'the_title', 'dwqa_title' );

function dwqa_body_class( $classes ) {
	global $post, $dwqa_options;
	if ( ( $dwqa_options['pages']['archive-question'] && is_page( $dwqa_options['pages']['archive-question'] )  )
		|| ( is_archive() &&  ( 'dwqa-question' == get_post_type()
				|| 'dwqa-question' == get_query_var( 'post_type' )
				|| 'dwqa-question_category' == get_query_var( 'taxonomy' )
				|| 'dwqa-question_tag' == get_query_var( 'taxonomy' ) ) )
	){
		$classes[] = 'list-dwqa-question';
	}

	if ( $dwqa_options['pages']['submit-question'] && is_page( $dwqa_options['pages']['submit-question'] ) ){
		$classes[] = 'submit-dwqa-question';
	}
	return $classes;
}
add_filter( 'body_class', 'dwqa_body_class' );


function dwqa_paged_query(){
	$paged = get_query_var( 'paged' ) ? get_query_var( 'paged' ) : 1;
	echo '<div><input type="hidden" name="dwqa-paged" id="dwqa-paged" value="'.$paged.'" ></div>';
}
add_action( 'dwqa-prepare-archive-posts', 'dwqa_paged_query' );


/**
 * Add Icon for DW Question Answer Menu In Dashboard
 */
function dwqa_add_guide_menu_icons_styles(){
	echo '<style type="text/css">#adminmenu .menu-icon-dwqa-question div.wp-menu-image:before {content: "\f223";}</style>';
}
add_action( 'admin_head', 'dwqa_add_guide_menu_icons_styles' );

function dwqa_load_template( $name, $extend = false, $include = true ){
	global $dwqa;
	$dwqa->template->load_template( $name, $extend, $include );
}


/**
 * Enqueue all scripts for plugins on front-end
 * @return void
 */
function dwqa_enqueue_scripts(){
    global $dwqa, $dwqa_options, $script_version, $dwqa_sript_vars, $dwqa_general_settings;
    $template_name = $dwqa->template->get_template();

	$question_category_rewrite = $dwqa_general_settings['question-category-rewrite'];
    $question_category_rewrite = $question_category_rewrite ? $question_category_rewrite : 'question-category';
	$question_tag_rewrite = $dwqa_general_settings['question-tag-rewrite'];
    $question_tag_rewrite = $question_tag_rewrite ? $question_tag_rewrite : 'question-tag';

    $assets_folder = DWQA_URI . 'templates/assets/';
    wp_enqueue_script( 'jquery' );
    if( is_singular( 'dwqa-question' ) ) {
        wp_enqueue_script( 'jquery-effects-core' );
        wp_enqueue_script( 'jquery-effects-highlight' );
    }
    $script_version = $dwqa->get_last_update();

    // Enqueue style
    wp_enqueue_style( 'dwqa-style', $assets_folder . 'css/style.css', array(), $script_version );
    // Enqueue for single question page
    if( is_single() && 'dwqa-question' == get_post_type() ) {
        // js
        wp_enqueue_script( 'dwqa-single-question', $assets_folder . 'js/dwqa-single-question.js', array('jquery'), $script_version, true );
        $single_script_vars = $dwqa_sript_vars;
        $single_script_vars['question_id'] = get_the_ID();
        wp_localize_script( 'dwqa-single-question', 'dwqa', $single_script_vars );
    }

    $question_category = get_query_var( 'dwqa-question_category' );
    if ( $question_category ) {
		$question_category_rewrite = $dwqa_options['question-category-rewrite'] ? $dwqa_options['question-category-rewrite'] : 'question-category';
    	$dwqa_sript_vars['taxonomy'][$question_category_rewrite] = $question_category;
    }
    $question_tag = get_query_var( 'dwqa-question_tag' );
    if ( $question_tag ) {
		$question_tag_rewrite = $dwqa_options['question-tag-rewrite'] ? $dwqa_options['question-tag-rewrite'] : 'question-category';
    	$dwqa_sript_vars['taxonomy'][$question_tag_rewrite] = $question_tag;
    }
    if( (is_archive() && 'dwqa-question' == get_post_type()) || ( isset( $dwqa_options['pages']['archive-question'] ) && is_page( $dwqa_options['pages']['archive-question'] ) ) ) {
        wp_enqueue_script( 'dwqa-questions-list', $assets_folder . 'js/dwqa-questions-list.js', array( 'jquery' ), $script_version, true );
        wp_localize_script( 'dwqa-questions-list', 'dwqa', $dwqa_sript_vars );
    }

    if( isset($dwqa_options['pages']['submit-question'])
        && is_page( $dwqa_options['pages']['submit-question'] ) ) {
    	wp_enqueue_script( 'jquery-ui-autocomplete' );
        wp_enqueue_script( 'dwqa-submit-question', $assets_folder . 'js/dwqa-submit-question.js', array( 'jquery', 'jquery-ui-autocomplete' ), $script_version, true );
        wp_localize_script( 'dwqa-submit-question', 'dwqa', $dwqa_sript_vars );
    }
}
add_action( 'wp_enqueue_scripts', 'dwqa_enqueue_scripts' );

function dwqa_comment_form( $args = array(), $post_id = null ) {
	if ( null === $post_id )
		$post_id = get_the_ID();
	else
		$id = $post_id;
	$commenter = wp_get_current_commenter();
	$user = wp_get_current_user();
	$user_identity = $user->exists() ? $user->display_name : '';
	$args = wp_parse_args( $args );
	if ( ! isset( $args['format'] ) )
		$args['format'] = current_theme_supports( 'html5', 'comment-form' ) ? 'html5' : 'xhtml';
	$req      = get_option( 'require_name_email' );
	$aria_req = ( $req ? " aria-required='true'" : '' );
	$html5    = 'html5' === $args['format'];
	$fields   = array(
		'email'  => '<p class="comment-form-email"><label for="email">' . __( 'Email' ) . ( $req ? ' <span class="required">*</span>' : '' ) . '</label> ' .
					'<input id="email-'.$post_id.'" name="email" ' . ( $html5 ? 'type="email"' : 'type="text"' ) . ' value="' . esc_attr(  $commenter['comment_author_email'] ) . '" size="30"' . $aria_req . ' /></p>'
	);
	$required_text = sprintf( ' ' . __( 'Required fields are marked %s' ), '<span class="required">*</span>' );
	/**
	 * Filter the default comment form fields.
	 *
	 * @since 3.0.0
	 *
	 * @param array $fields The default comment fields.
	 */
	$fields = apply_filters( 'comment_form_default_fields', $fields );
	$defaults = array(
		'fields'               => $fields,
		'comment_field'        => '<p class="comment-form-comment"><label for="comment">' . _x( 'Comment', 'noun' ) . '</label> <textarea id="comment" name="comment" cols="45" rows="8" aria-required="true"></textarea></p>',
		'must_log_in'          => '<p class="must-log-in">' . sprintf( __( 'You must be <a href="%s">logged in</a> to post a comment.','dwqa' ), wp_login_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
		'logged_in_as'         => '<p class="logged-in-as">' . sprintf( __( 'Logged in as <a href="%1$s">%2$s</a>. <a href="%3$s" title="Log out of this account">Log out?</a>' ), get_edit_user_link(), $user_identity, wp_logout_url( apply_filters( 'the_permalink', get_permalink( $post_id ) ) ) ) . '</p>',
		'comment_notes_before' => '<p class="comment-notes">' . __( 'Your email address will not be published.','dwqa' ) . ( $req ? $required_text : '' ) . '</p>',
		'comment_notes_after'  => '<p class="form-allowed-tags">' . sprintf( __( 'You may use these <abbr title="HyperText Markup Language">HTML</abbr> tags and attributes: %s','dwqa' ), ' <code>' . allowed_tags() . '</code>' ) . '</p>',
		'id_form'              => 'commentform',
		'id_submit'            => 'submit',
		'title_reply'          => __( 'Leave a Reply','dwqa' ),
		'title_reply_to'       => __( 'Leave a Reply to %s','dwqa' ),
		'cancel_reply_link'    => __( 'Cancel reply', 'dwqa' ),
		'label_submit'         => __( 'Post Comment', 'dwqa' ),
		'format'               => 'xhtml',
	);
	/**
	 * Filter the comment form default arguments.
	 *
	 * Use 'comment_form_default_fields' to filter the comment fields.
	 *
	 * @since 3.0.0
	 *
	 * @param array $defaults The default comment form arguments.
	 */
	$args = wp_parse_args( $args, apply_filters( 'comment_form_defaults', $defaults ) );
	if ( comments_open( $post_id ) ) :
		/**
		 * Fires before the comment form.
		 *
		 * @since 3.0.0
		 */
		do_action( 'comment_form_before' );
		?>
		<div id="dwqa-respond" class="dwqa-comment-form">
		<?php if ( !dwqa_current_user_can( 'post_comment' ) ) : ?>
			<?php echo $args['must_log_in']; ?>
			<?php
			/**
			 * Fires after the HTML-formatted 'must log in after' message in the comment form.
			 *
			 * @since 3.0.0
			 */
			do_action( 'comment_form_must_log_in_after' );
			?>
		<?php else : ?>
			<form method="post" id="<?php echo esc_attr( $args['id_form'] ); ?>" class="comment-form"<?php echo $html5 ? ' novalidate' : ''; ?>>
			<?php
			/**
			 * Fires at the top of the comment form, inside the <form> tag.
			 *
			 * @since 3.0.0
			 */
			do_action( 'comment_form_top' );
			?>
			<?php if ( is_user_logged_in() ) : ?>
				<?php
				/**
				 * Filter the 'logged in' message for the comment form for display.
				 *
				 * @since 3.0.0
				 *
				 * @param string $args['logged_in_as'] The logged-in-as HTML-formatted message.
				 * @param array  $commenter            An array containing the comment author's username, email, and URL.
				 * @param string $user_identity        If the commenter is a registered user, the display name, blank otherwise.
				 */
				echo apply_filters( 'comment_form_logged_in', $args['logged_in_as'], $commenter, $user_identity );
				?>
				<?php
				/**
				 * Fires after the is_user_logged_in() check in the comment form.
				 *
				 * @since 3.0.0
				 *
				 * @param array  $commenter     An array containing the comment author's username, email, and URL.
				 * @param string $user_identity If the commenter is a registered user, the display name, blank otherwise.
				 */
				do_action( 'comment_form_logged_in_after', $commenter, $user_identity );
				?>
			<?php else : ?>
				<?php echo $args['comment_notes_before']; ?>
				<?php
				/**
				 * Fires before the comment fields in the comment form.
				 *
				 * @since 3.0.0
				 */
				do_action( 'comment_form_before_fields' );
				echo '<div class="dwqa-anonymous-fields">';
				foreach ( (array ) $args['fields'] as $name => $field ) {
					/**
					 * Filter a comment form field for display.
					 *
					 * The dynamic portion of the filter hook, $name, refers to the name
					 * of the comment form field. Such as 'author', 'email', or 'url'.
					 *
					 * @since 3.0.0
					 *
					 * @param string $field The HTML-formatted output of the comment form field.
					 */
					echo apply_filters( "comment_form_field_{$name}", $field ) . "\n";
				}
				echo '</div>';
				/**
				 * Fires after the comment fields in the comment form.
				 *
				 * @since 3.0.0
				 */
				do_action( 'comment_form_after_fields' );
				?>
			<?php endif; ?>
			<?php
			/**
			 * Filter the content of the comment textarea field for display.
			 *
			 * @since 3.0.0
			 *
			 * @param string $args['comment_field'] The content of the comment textarea field.
			 */
			echo apply_filters( 'comment_form_field_comment', $args['comment_field'] );
			?>
			<input name="comment-submit" type="submit" id="<?php echo esc_attr( $args['id_submit'] ); ?>" value="<?php echo esc_attr( $args['label_submit'] ); ?>" class="dwqa-btn dwqa-btn-primary" />
			<?php comment_id_fields( $post_id ); ?>
			<?php
			/**
			 * Fires at the bottom of the comment form, inside the closing </form> tag.
			 *
			 * @since 1.5.0
			 *
			 * @param int $post_id The post ID.
			 */
			do_action( 'comment_form', $post_id );
			?>
			</form>
		<?php endif; ?>
		</div><!-- #respond -->
		<?php
		/**
		 * Fires after the comment form.
		 *
		 * @since 3.0.0
		 */
		do_action( 'comment_form_after' );
	else :
		/**
		 * Fires after the comment form if comments are closed.
		 *
		 * @since 3.0.0
		 */
		do_action( 'comment_form_comments_closed' );
	endif;
}

function dwqa_display_sticky_questions(){
	$sticky_questions = get_option( 'dwqa_sticky_questions', array() );
	if ( ! empty( $sticky_questions ) ) {
		$query = array(
			'post_type' => 'dwqa-question',
			'post__in' => $sticky_questions,
			'posts_per_page' => 40,
		);
		$sticky_questions = new WP_Query( $query );
		?>
		<div class="sticky-questions">
			<?php while ( $sticky_questions->have_posts() ) : $sticky_questions->the_post(); ?>
				<?php dwqa_load_template( 'content', 'question' ); ?>
			<?php endwhile; ?>
		</div>
		<?php
		wp_reset_postdata();
	}
}
add_action( 'dwqa-before-question-list', 'dwqa_display_sticky_questions' );

function dwqa_is_sticky( $question_id = false ) {
	if ( ! $question_id ) {
		$question_id = get_the_ID();
	}
	$sticky_questions = get_option( 'dwqa_sticky_questions', array() );
	if ( in_array( $question_id, $sticky_questions ) ) {
		return true;
	}
	return false;
}


function dwqa_question_states( $states, $post ){
	if ( dwqa_is_sticky( $post->ID ) && 'dwqa-question' == get_post_type( $post->ID ) ) {
		$states[] = __( 'Sticky Question','dwqa' );
	}
	return $states;
}
add_filter( 'display_post_states', 'dwqa_question_states', 10, 2 );


function dwqa_get_ask_question_link( $echo = true, $label = false, $class = false ){
	global $dwqa_options;
	$submit_question_link = get_permalink( $dwqa_options['pages']['submit-question'] );
	if ( $dwqa_options['pages']['submit-question'] && $submit_question_link ) {


		if ( dwqa_current_user_can( 'post_question' ) ) {
			$label = $label ? $label : __( 'Ask a question', 'dwqa' );
		} elseif ( ! is_user_logged_in() ) {
			$label = $label ? $label : __( 'Login to ask a question', 'dwqa' );
			$submit_question_link = wp_login_url( $submit_question_link );
		} else {
			return false;
		}
		//Add filter to change ask question link text
		$label = apply_filters( 'dwqa_ask_question_link_label', $label );

		$class = $class ? $class  : 'dwqa-btn-success';
		$button = '<a href="'.$submit_question_link.'" class="dwqa-btn '.$class.'">'.$label.'</a>';
		$button = apply_filters( 'dwqa_ask_question_link', $button, $submit_question_link );
		if ( ! $echo ) {
			return $button;
		}
		echo $button;
	}
}

function dwqa_get_template( $template = false ) {
	$templates = apply_filters( 'dwqa_get_template', array(
		'page.php',
		'single-dwqa-question',
		'single.php',
		'index.php',
	) );

	if ( isset( $template ) && file_exists( trailingslashit( get_template_directory() ) . $template ) ) {
		return trailingslashit( get_template_directory() ) . $template;
	}

	$old_template = $template;
	foreach ( $templates as $template ) {
		if ( $template == $old_template ) {
			continue;
		}
		if ( file_exists( trailingslashit( get_template_directory() ) . $template ) ) {
			return trailingslashit( get_template_directory() ) . $template;
		}
	}
	return false;
}

function dwqa_has_sidebar_template() {
	global $dwqa_options, $dwqa_template;
	$template = get_stylesheet_directory() . '/dwqa-templates/';
	if ( is_single() && file_exists( $template . '/sidebar-single.php' ) ) {
		include $template . '/sidebar-single.php';
		return;
	} elseif ( is_single() ) {
		if ( file_exists( DWQA_DIR . 'inc/templates/'.$dwqa_template.'/sidebar-single.php' ) ) {
			include DWQA_DIR . 'inc/templates/'.$dwqa_template.'/sidebar-single.php';
		} else {
			get_sidebar();
		}
		return;
	}

	return;
}

add_action( 'dwqa_after_single_question_content', 'dwqa_load_answers' );
function dwqa_load_answers() {
	global $dwqa;
	$dwqa->template->load_template( 'answers' );
}

class DWQA_Template {
	private $active = 'default';
	private $page_template = 'page.php';
	public $filters;

	public function __construct() {
		$this->filters = new stdClass();
		add_filter( 'template_include', array( $this, 'question_content' ) );
		//add_filter( 'term_link', array( $this, 'force_term_link_to_setting_page' ), 10, 3 );
		add_filter( 'comments_open', array( $this, 'close_default_comment' ) );

		//Template Include Hook
		add_filter( 'single_template', array( $this, 'redirect_answer_to_question' ), 20 );
		add_filter( 'comments_template', array( $this, 'generate_template_for_comment_form' ), 20 );

		//Wrapper
		add_action( 'dwqa_before_page', array( $this, 'start_wrapper_content' ) );
		add_action( 'dwqa_after_page', array( $this, 'end_wrapper_content' ) );
	}

	public function start_wrapper_content() {
		$this->load_template( 'content', 'start-wrapper' );
		echo '<div class="dwqa-container" >';
	}

	public function end_wrapper_content() {
		echo '</div>';
		$this->load_template( 'content', 'end-wrapper' );
		wp_reset_query();
	}


	public function redirect_answer_to_question( $template ) {
		global $post, $dwqa_options;
		if ( is_singular( 'dwqa-answer' ) ) {
			$question_id = get_post_meta( $post->ID, '_question', true );
			if ( $question_id ) {
				wp_safe_redirect( get_permalink( $question_id ) );
				exit( 0 );
			}
		}
		return $template;
	}

	public function generate_template_for_comment_form( $comment_template ) {
		if (  is_single() && ('dwqa-question' == get_post_type() || 'dwqa-answer' == get_post_type() ) ) {
			return $this->load_template( 'comments', false, false );
		}
		return $comment_template;
	}

	public function page_template_body_class( $classes ) {
		$classes[] = 'page-template';

		$template_slug  = $this->page_template;
		$template_parts = explode( '/', $template_slug );

		foreach ( $template_parts as $part ) {
			$classes[] = 'page-template-' . sanitize_html_class( str_replace( array( '.', '/' ), '-', basename( $part, '.php' ) ) );
			$classes[] = sanitize_html_class( str_replace( array( '.', '/' ), '-', basename( $part, '.php' ) ) );
		}
		$classes[] = 'page-template-' . sanitize_html_class( str_replace( '.', '-', $template_slug ) );

		return $classes;
	}

	public function question_content( $template ) {
		$dwqa_options = get_option( 'dwqa_options' );
		$template_folder = trailingslashit( get_template_directory() );
		if ( isset( $dwqa_options['pages']['archive-question'] ) ) {
			$page_template = get_post_meta( $dwqa_options['pages']['archive-question'], '_wp_page_template', true );
		}

		$page_template = isset( $page_template ) && !empty( $page_template ) ? $page_template : 'page.php';
		$this->page_template = $page_template;

		if ( is_singular( 'dwqa-question' ) ) {
			ob_start();

			remove_filter( 'comments_open', array( $this, 'close_default_comment' ) );

			echo '<div class="dwqa-container" >';
			$this->load_template( 'single', 'question' );
			echo '</div>';

			$content = ob_get_contents();

			add_filter( 'comments_open', array( $this, 'close_default_comment' ) );

			ob_end_clean();

			// Reset post
			global $post, $current_user;

			$this->reset_content( array(
				'ID'             => $post->ID,
				'post_title'     => $post->post_title,
				'post_author'    => 0,
				'post_date'      => $post->post_date,
				'post_content'   => $content,
				'post_type'      => 'dwqa-question',
				'post_status'    => $post->post_status,
				'is_single'      => true,
			) );

			$single_template = isset( $dwqa_options['single-template'] ) ? $dwqa_options['single-template'] : false;

			$this->remove_all_filters( 'the_content' );
			add_filter( 'body_class', array( $this, 'page_template_body_class' ) );
			return dwqa_get_template( $page_template );
		}
		if ( is_tax( 'dwqa-question_category' ) || is_tax( 'dwqa-question_tag' ) || is_post_type_archive( 'dwqa-question' ) || is_post_type_archive( 'dwqa-answer' ) ) {

			global $wp_query;
			$post_id = isset( $dwqa_options['pages']['archive-question'] ) ? $dwqa_options['pages']['archive-question'] : 0;
			if ( $post_id ) {
				$page = get_page( $post_id );
				if ( is_tax( 'dwqa-question_category' ) || is_tax( 'dwqa-question_tag' ) ) {
					$page->is_tax = true;
				}
				$this->reset_content( $page );
				add_filter( 'body_class', array( $this, 'page_template_body_class' ) );
				return dwqa_get_template( $page_template );
			}
		}

		if ( is_page( $dwqa_options['pages']['archive-question'] ) ) {
			global $wp_query;
			$wp_query->is_archive = true;
		}

		return $template;
	}

	public function reset_content( $args ) {
		global $wp_query, $post;
		if ( isset( $wp_query->post ) ) {
			$dummy = wp_parse_args( $args, array(
				'ID'                    => $wp_query->post->ID,
				'post_status'           => $wp_query->post->post_status,
				'post_author'           => $wp_query->post->post_author,
				'post_parent'           => $wp_query->post->post_parent,
				'post_type'             => $wp_query->post->post_type,
				'post_date'             => $wp_query->post->post_date,
				'post_date_gmt'         => $wp_query->post->post_date_gmt,
				'post_modified'         => $wp_query->post->post_modified,
				'post_modified_gmt'     => $wp_query->post->post_modified_gmt,
				'post_content'          => $wp_query->post->post_content,
				'post_title'            => $wp_query->post->post_title,
				'post_excerpt'          => $wp_query->post->post_excerpt,
				'post_content_filtered' => $wp_query->post->post_content_filtered,
				'post_mime_type'        => $wp_query->post->post_mime_type,
				'post_password'         => $wp_query->post->post_password,
				'post_name'             => $wp_query->post->post_name,
				'guid'                  => $wp_query->post->guid,
				'menu_order'            => $wp_query->post->menu_order,
				'pinged'                => $wp_query->post->pinged,
				'to_ping'               => $wp_query->post->to_ping,
				'ping_status'           => $wp_query->post->ping_status,
				'comment_status'        => $wp_query->post->comment_status,
				'comment_count'         => $wp_query->post->comment_count,
				'filter'                => $wp_query->post->filter,

				'is_404'                => false,
				'is_page'               => false,
				'is_single'             => false,
				'is_archive'            => false,
				'is_tax'                => false,
				'current_comment'		=> $wp_query->post->comment_count,
			) );
		} else {
			$dummy = wp_parse_args( $args, array(
				'ID'                    => -1,
				'post_status'           => 'private',
				'post_author'           => 0,
				'post_parent'           => 0,
				'post_type'             => 'page',
				'post_date'             => 0,
				'post_date_gmt'         => 0,
				'post_modified'         => 0,
				'post_modified_gmt'     => 0,
				'post_content'          => '',
				'post_title'            => '',
				'post_excerpt'          => '',
				'post_content_filtered' => '',
				'post_mime_type'        => '',
				'post_password'         => '',
				'post_name'             => '',
				'guid'                  => '',
				'menu_order'            => 0,
				'pinged'                => '',
				'to_ping'               => '',
				'ping_status'           => '',
				'comment_status'        => 'closed',
				'comment_count'         => 0,
				'filter'                => 'raw',

				'is_404'                => false,
				'is_page'               => false,
				'is_single'             => false,
				'is_archive'            => false,
				'is_tax'                => false,
				'current_comment'		=> 0,
			) );
		}
		// Bail if dummy post is empty
		if ( empty( $dummy ) ) {
			return;
		}
		// Set the $post global
		$post = new WP_Post( (object ) $dummy );
		setup_postdata( $post );
		// Copy the new post global into the main $wp_query
		$wp_query->post       = $post;
		$wp_query->posts      = array( $post );

		// Prevent comments form from appearing
		$wp_query->post_count 		= 1;
		$wp_query->is_404     		= $dummy['is_404'];
		$wp_query->is_page    		= $dummy['is_page'];
		$wp_query->is_single  		= $dummy['is_single'];
		$wp_query->is_archive 		= $dummy['is_archive'];
		$wp_query->is_tax     		= $dummy['is_tax'];
		$wp_query->current_comment 	= $dummy['current_comment'];

	}

	public function close_default_comment( $open ) {
		global $dwqa_options;
		if ( is_singular( 'dwqa-question' ) || is_singular( 'dwqa-answer' ) || ( $dwqa_options['pages']['archive-question'] && is_page( $dwqa_options['pages']['archive-question'] ) ) || ( $dwqa_options['pages']['submit-question'] && is_page( $dwqa_options['pages']['submit-question'] ) ) ) {
			return false;
		}
		return $open;
	}

	public function remove_all_filters( $tag, $priority = false ) {
		global $wp_filter, $merged_filters;

		// Filters exist
		if ( isset( $wp_filter[$tag] ) ) {

			// Filters exist in this priority
			if ( ! empty( $priority ) && isset( $wp_filter[$tag][$priority] ) ) {

				// Store filters in a backup
				$this->filters->wp_filter[$tag][$priority] = $wp_filter[$tag][$priority];

				// Unset the filters
				unset( $wp_filter[$tag][$priority] );

				// Priority is empty
			} else {

				// Store filters in a backup
				$this->filters->wp_filter[$tag] = $wp_filter[$tag];

				// Unset the filters
				unset( $wp_filter[$tag] );
			}
		}

		// Check merged filters
		if ( isset( $merged_filters[$tag] ) ) {

			// Store filters in a backup
			$this->filters->merged_filters[$tag] = $merged_filters[$tag];

			// Unset the filters
			unset( $merged_filters[$tag] );
		}

		return true;
	}

	public function restore_all_filters( $tag, $priority = false ) {
		global $wp_filter, $merged_filters;

		// Filters exist
		if ( isset( $this->filters->wp_filter[$tag] ) ) {

			// Filters exist in this priority
			if ( ! empty( $priority ) && isset( $this->filters->wp_filter[$tag][$priority] ) ) {

				// Store filters in a backup
				$wp_filter[$tag][$priority] = $this->filters->wp_filter[$tag][$priority];

				// Unset the filters
				unset( $this->filters->wp_filter[$tag][$priority] );
				// Priority is empty
			} else {

				// Store filters in a backup
				$wp_filter[$tag] = $this->filters->wp_filter[$tag];

				// Unset the filters
				unset( $this->filters->wp_filter[$tag] );
			}
		}

		// Check merged filters
		if ( isset( $this->filters->merged_filters[$tag] ) ) {

			// Store filters in a backup
			$merged_filters[$tag] = $this->filters->merged_filters[$tag];

			// Unset the filters
			unset( $this->filters->merged_filters[$tag] );
		}

		return true;
	}

	public function get_template() {
		return $this->active;
	}

	public function load_template( $name, $extend = false, $include = true ) {
		$check = true;
		if ( $extend ) {
			$name .= '-' . $extend;
		}

		if ( $name == 'question-submit-form' && ! dwqa_current_user_can( 'post_question' ) ) {
			echo '<div class="alert">'.__( 'You do not have permission to submit a question','dwqa' ).'</div>';
			return false;
		}

		$template = get_stylesheet_directory() . '/dwqa-templates/'.$name.'.php';
		if ( ! file_exists( $template ) ) {
			$template = DWQA_DIR . 'templates/'.$name.'.php';
		}
		$template = apply_filters( 'dwqa-load-template', $template, $name );

		if ( ! file_exists( $template ) ) {
			_doing_it_wrong( __FUNCTION__, sprintf( "<strong>%s</strong> does not exists in <code>%s</code>.", $name, $template ), '1.4.0' );
			return false;
		}

		if ( ! $include ) {
			return $template;
		}
		include $template;
	}
}

function dwqa_get_mail_template( $option, $name = '' ) {
	if ( ! $name ) {
		return '';
	}
	$template = get_option( $option );
	if ( $template ) {
		return $template;
	} else {
		if ( file_exists( DWQA_DIR . 'templates/email/'.$name.'.html' ) ) {
			ob_start();
			load_template( DWQA_DIR . 'templates/email/'.$name.'.html', false );
			$template = ob_get_contents();
			ob_end_clean();
			return $template;
		} else {
			return '';
		}
	}
}

function dwqa_vote_best_answer_button() {
	global $current_user;
	$question_id = get_post_meta( get_the_ID(), '_question', true );
	$question = get_post( $question_id );
		$best_answer = dwqa_get_the_best_answer( $question_id );
		$data = is_user_logged_in() && ( $current_user->ID == $question->post_author || current_user_can( 'edit_posts' ) ) ? 'data-answer="'.get_the_ID().'" data-nonce="'.wp_create_nonce( '_dwqa_vote_best_answer' ).'" data-ajax="true"' : 'data-ajax="false"';
	if ( get_post_status( get_the_ID() ) != 'publish' ) {
		return false;
	}
	if ( $best_answer == get_the_ID() || ( is_user_logged_in() && ( $current_user->ID == $question->post_author || current_user_can( 'edit_posts' ) ) ) ) {
		?>
		<div class="entry-vote-best <?php echo $best_answer == get_the_ID() ? 'active' : ''; ?>" <?php echo $data ?> >
			<a href="javascript:void( 0 );" title="<?php _e( 'Choose as the best answer','dwqa' ) ?>">
				<div class="entry-vote-best-bg"></div>
				<i class="icon-thumbs-up"></i>
			</a>
		</div>
		<?php
	}
}
