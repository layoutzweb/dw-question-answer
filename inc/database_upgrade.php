<?php  
global $dwqa_db_version;

class DWQA_Database_Upgrade {
	public $db_version = '1.3.3';
	public $questions_index = 'dwqa_questions_index';

	public function __construct() {
		add_action( 'admin_menu', array( $this, 'add_menu' ) );

		add_action( 'wp_ajax_dwqa_upgrade_database', array( $this, 'create_table' ) );
		if ( $this->db_version != get_option( 'dwqa_db_version' ) ) {
			update_option( 'dwqa_db_version', $this->db_version );
		}
	}

	public function table_exists( $name ) {
		global $wpdb;
		$check = wp_cache_get( 'table_exists_' . $name );
		if ( ! $check ) {
			$check = $wpdb->get_var( 'SHOW TABLES LIKE "'. $name .'"' );
			wp_cache_set( 'table_exists_', $check );
		}

		if ( $check == $name ) {
			return true;
		}
		return false;
	}

	/**
	 * Create Index Table
	 */
	public function create_table(){
		global $wpdb;
		$offset = isset( $_GET['offset'] ) ? intval( $_GET['offset'] ) : 0;
		$posts_per_round = 10;

		$dwqa_table = 'dwqa_question_index';

		$questions_count = wp_count_posts( 'dwqa-question' );
		$total = $questions_count->publish + $questions_count->private;
		$start = microtime(true);

		$query_create_table = "CREATE TABLE IF NOT EXISTS {$dwqa_table} (
			`ID` bigint(20) unsigned NOT NULL,
			`title` text NOT NULL,
			`author` bigint(20) unsigned NOT NULL,
			`status` varchar(20) NOT NULL DEFAULT 'publish',
			`last_activity_date` datetime NOT NULL DEFAULT '0000-00-00 00:00:00',
			`last_activity_author` bigint(20) unsigned NOT NULL DEFAULT '0',
			`last_activity_type` varchar(255) NOT NULL DEFAULT 'create',
			`last_activity_id` bigint(20) unsigned NOT NULL DEFAULT '0',
			`answers` varchar(255),
			`answer_count` bigint(20) NOT NULL DEFAULT '0',
			`publish_answer_count` bigint(20) NOT NULL DEFAULT '0',
			`private_answer_count` bigint(20) NOT NULL DEFAULT '0',
			`view_count` bigint(20) NOT NULL DEFAULT '0',
			`vote_count` bigint(20) NOT NULL DEFAULT '0',
			PRIMARY KEY (`ID`)
		);";
		$table = $wpdb->query( $query_create_table );
		if ( $table ) {
			if ( $offset == 0 ) {
				$clear_table = "DELETE FROM {$dwqa_table}";
				$wpdb->query( $clear_table );
			}
			$query_questions_table = "FROM {$wpdb->posts} WHERE post_type = 'dwqa-question' AND post_status IN ( 'publish', 'private' ) ORDER BY post_modified DESC LIMIT {$offset},{$posts_per_round}";
			$query_questions = "SELECT ID " . $query_questions_table;

			// Insert Question ID, title
			$query_insert_questions = "INSERT INTO {$dwqa_table} ( ID, title, author ) SELECT ID, post_title, post_author " . $query_questions_table;
			$wpdb->query( $query_insert_questions );

			// Update View Count
			$query_view_count = "UPDATE {$dwqa_table} as new_table 
									JOIN ( SELECT `insert_questions`.ID, `meta`.meta_value 
											FROM {$wpdb->postmeta} as meta 
											JOIN ( {$query_questions} ) as insert_questions 
											ON `meta`.post_id = `insert_questions`.ID 
											WHERE meta_key = '_dwqa_views' 
									) AS view 
										ON `new_table`.ID = `view`.ID 
								SET `new_table`.view_count = `view`.meta_value";
			$wpdb->query( $query_view_count );

			// //Update Status
			$query_status = "UPDATE {$dwqa_table} as new_table 
								JOIN ( SELECT `insert_questions`.ID, `meta`.meta_value 
										FROM {$wpdb->postmeta} as meta 
										JOIN ( {$query_questions} ) as insert_questions 
											ON `meta`.post_id = `insert_questions`.ID  
										WHERE `meta`.meta_key = '_dwqa_status' 
								) as status 
									ON `new_table`.ID = `status`.ID
							SET `new_table`.status = `status`.meta_value";
			$wpdb->query( $query_status );

			// //Update Vote
			$query_vote_count = "UPDATE {$dwqa_table} as new_table 
								JOIN ( SELECT ID, `meta`.meta_value 
										FROM {$wpdb->postmeta} as meta 
										JOIN ( {$query_questions} ) as insert_questions 
											ON `meta`.post_id = `insert_questions`.ID  
										WHERE `meta`.meta_key = '_dwqa_votes' 
								) as vote 
									ON `new_table`.ID = `vote`.ID
							SET `new_table`.vote_count = `vote`.meta_value";
			$wpdb->query( $query_vote_count );

			// // Publish Answer count
			$query_answer_count ="UPDATE {$dwqa_table} as new_table 
									JOIN ( SELECT 
											Q.ID as question, 
											IF( ISNULL( A.post_modified ), Q.post_modified, 
											max( A.post_modified ) ) as post_modified, 
											IF( ISNULL( A.ID ), 'create', 'answer' ) as last_activity_type,
											GROUP_CONCAT(DISTINCT A.ID SEPARATOR ',') AS answers, 
											count(distinct A.ID) as total, 
											count(distinct (case when A.post_status = 'publish' then A.ID end)) as publish_count, 
											count(distinct (case when A.post_status = 'private' then A.ID end)) as private_count 
										FROM ( SELECT * {$query_questions_table} ) AS Q 
										LEFT JOIN {$wpdb->postmeta} AS N 
											ON Q.ID = N.meta_value AND N.meta_key = '_question' 
										LEFT JOIN {$wpdb->posts} AS A 
											ON N.post_id = A.ID AND A.post_type = 'dwqa-answer' AND A.post_status IN ( 'publish', 'private' ) 
										WHERE Q.post_status IN ( 'publish', 'private' ) AND Q.post_type = 'dwqa-question' GROUP BY Q.ID 
									) as count_table ON `count_table`.question = `new_table`.ID 
								SET `new_table`.private_answer_count = `count_table`.private_count, `new_table`.publish_answer_count = `count_table`.publish_count, `new_table`.answer_count = `count_table`.total,
									`new_table`.answers = `count_table`.answers, `new_table`.last_activity_date = `count_table`.post_modified, `new_table`.last_activity_type = `count_table`.last_activity_type";
			$wpdb->query( $query_answer_count );

			// get last activity author and id
			$query_update_last_activity = "UPDATE {$dwqa_table} as new_table 
											JOIN ( 
												SELECT question.ID, 
													max( if(question.last_activity_date = answer.post_modified, answer.post_author, question.author)) as last_activity_author,
													max(if( question.last_activity_date = answer.post_modified,answer.ID,question.ID)) last_activity_id  
												FROM {$dwqa_table} question 
													JOIN {$wpdb->postmeta} meta on `meta`.meta_value = `question`.ID and `meta`.meta_key = '_question' 
													JOIN {$wpdb->posts} answer on `meta`.post_id = `answer`.ID 
												GROUP BY `question`.ID
											) as last_activity ON new_table.ID = last_activity.ID
											SET new_table.last_activity_author = last_activity.last_activity_author, new_table.last_activity_id = last_activity.last_activity_id
										";
			$wpdb->query( $query_update_last_activity );
		}

		//Executime for single loop
		$time_elapsed_us = microtime(true) - $start; 
		$offset += $posts_per_round;
		if ( $offset > $total ) {
			wp_send_json_error( array( 'message' => 'done' ) );
		}
		wp_send_json_success( array(
			'time' => $time_elapsed_us,
			'next_offset' => $offset
		) );
		
	}

	public function add_menu() {
		add_submenu_page( 'edit.php?post_type=dwqa-question', __( 'DWQA ReIndex', 'dwqa' ), __( 'DWQA ReIndex', 'dwqa' ), 'manage_options', 'dwqa-question', array( $this, 'display' ) );
	}

	public function display() {
		$posts_per_round = 20;
		$questions_count = wp_count_posts( 'dwqa-question' );
		$total = $questions_count->publish + $questions_count->private;
		$answers_count = wp_count_posts( 'dwqa-answer' );
		?>
		<div class="wrap">
			<h2><?php _e( 'Questions Index', 'dwqa' ); ?></h2>
			<form action="" method="post">
				<p><?php printf( __( 'Total questions: %d ( %d publish - %d private )', 'dwqa' ), $total, $questions_count->publish, $questions_count->private ) ?></p>
				<p><?php printf('Total answers: %d ( %d publish - %d private ) ', $answers_count->publish + $answers_count->private, $answers_count->publish, $answers_count->private ) ?></p>
				
				<p><progress id="dwqa-upgrade-database-progress" max="<?php echo $total; ?>" value="80">80/<?php echo $total; ?></progress></p>
				<div><input id="dwqa-upgrade-database" type="button" class="btn btn-primary" value="Index Questions"></div>
			</form>
		</div>
		<script type="text/javascript">
		jQuery(document).ready(function($) {

			var run_upgrade = function( offset ) {
				$.ajax({
					url: '<?php echo admin_url( "admin-ajax.php" ); ?>',
					type: 'GET',
					dataType: 'json',
					data: {
						action: 'dwqa_upgrade_database',
						offset: offset
					},
				})
				.done(function( resp ) {
					if ( resp.success ) {
						console.log( resp.data.next_offset );
						run_upgrade( resp.data.next_offset );
						$('#dwqa-upgrade-database-progress').attr('value', resp.data.next_offset );
					} else {
						console.log( resp.data.message );
						$('#dwqa-upgrade-database').removeAttr('disabled');
					}
					//resend query with new offset
				});
			}

			$('#dwqa-upgrade-database').on( 'click', function(e){
				e.preventDefault();
				$(this).attr('disabled', 'disabled');
				run_upgrade(0);
			});
		});
		</script>
		<?php
	}
}
$GLOBALS['dwqa_database_upgrade'] = new DWQA_Database_Upgrade();

?>