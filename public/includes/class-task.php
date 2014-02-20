<?php
/**
 * NerveTask.
 *
 * @package   NerveTask
 * @author    Patrick Daly <patrick@developdaly.com>
 * @license   GPL-2.0+
 * @link      http://nervetask.com
 * @copyright 2014 NerveTask
 */

/**
 * @package NerveTask
 * @author  Patrick Daly <patrick@developdaly.com>
 */
class NerveTask_Task {

	/**
	 * Instance of this class.
	 *
	 * @since    0.1.0
	 *
	 * @var      object
	 */
	protected static $instance = null;

	/**
	 * Initialize the plugin by setting localization and loading public scripts
	 * and styles.
	 *
	 * @since     0.1.0
	 */
	private function __construct() {

		if ( is_admin() && ( defined('DOING_AJAX') && DOING_AJAX ) ) {
			add_action( 'wp_ajax_nopriv_nervetask',	array( $this, 'go' ) );
			add_action( 'wp_ajax_nervetask',		array( $this, 'go' ) );
		} else {
			add_action( 'init',	array( $this, 'go' ) );
		}
	}

	/**
	 * Return an instance of this class.
	 *
	 * @since     0.1.0
	 *
	 * @return    object    A single instance of this class.
	 */
	public static function get_instance() {

		// If the single instance hasn't been set, set it now.
		if ( null == self::$instance ) {
			self::$instance = new self;
		}

		return self::$instance;
	}

	/**
	 * This is the first function in the task process. It will route actions
	 * based on the controller value.
	 *
	 * First checks if the user is being added through a regular $_POST and
	 * performs a standard page refresh.
	 *
	 * If an ajax referrer is set and valid then proceed instead by dying
	 * and returning a response for the client.
	 *
	 * @since    0.1.0
	 */
	public function go() {

		if ( !empty( $_POST ) || ( defined('DOING_AJAX') && DOING_AJAX ) ) {

			if( $_POST['controller'] == 'nervetask_new_task' ) {
				$result = self::new_task($_POST);
			}
			if( $_POST['controller'] == 'nervetask_update_content' ) {
				$result = self::update_content($_POST);
			}
			if( $_POST['controller'] == 'nervetask_insert_comment' ) {
				$result = self::insert_comment($_POST);
			}
			if( $_POST['controller'] == 'nervetask_update_assignees' ) {
				$result = self::update_assignees($_POST);
			}
			if( $_POST['controller'] == 'nervetask_update_status' ) {
				$result = self::update_status($_POST);
			}
			if( $_POST['controller'] == 'nervetask_update_priority' ) {
				$result = self::update_priority($_POST);
			}
			if( $_POST['controller'] == 'nervetask_update_category' ) {
				$result = self::update_category($_POST);
			}

			// If this is an ajax request
			if ( defined('DOING_AJAX') && DOING_AJAX ) {

				if ( isset( $result ) ) {
					die( json_encode( $result ) );
				} else {
					die(
						json_encode(
							array(
								'success' => false,
								'message' => __( 'An error occured. Please refresh the page and try again.' )
							)
						)
					);
				}
			}
		}
	}

	/**
	 * Inserts a new task.
	 *
	 * @since    0.1.0
	 */
	public function new_task( $data ) {

		if( empty( $data ) ) {
			return;
		}

		// If the current user can't publish posts stop
		if ( !current_user_can('publish_posts') ) {
			$output = 'You don\'t have proper permissions to create a new task. :(';
			return $output;
		}

		if( isset( $data['nervetask-new-task-content'] ) ) {
			$post_content = $data['nervetask-new-task-content'];
		} else {
			$post_content = '';
		}
		$post_status	= 'publish';
		if( isset( $data['nervetask-new-task-title'] ) ) {
			$post_title = $data['nervetask-new-task-title'];
		} else {
			$post_title = '';
		}
		$post_type		= 'nervetask';

		$args = array(
			'post_content'  => $post_content,
			'post_status'	=> $post_status,
			'post_title'    => $post_title,
			'post_type'		=> $post_type,
		);

		// Insert the new task and get its ID
		$post_id = wp_insert_post( $args );

		// TODO: Retrieve default status and priority from options
		wp_set_post_terms( $post_id, array( 'new' ),	'nervetask_status' );
		wp_set_post_terms( $post_id, array( 'normal' ),	'nervetask_priority' );

		// If the task inserted succesffully
		if ( $post_id != 0 ) {

			$post = get_post( $post_id );

			$output = array(
				'status'	=> 'success',
				'message'	=> __('Success!'),
				'post'		=> $post
			);

		} else {
			$output = 'There was an error while creating a new task. Please refresh the page and try again.';
		}

		return $output;
	}

	/**
	 * Updates a task's content.
	 *
	 * @since    0.1.0
	 */
	public function update_content( $data ) {

		if( empty( $data ) ) {
			return;
		}

		// If the current user can't publish posts stop
		if ( !current_user_can('edit_posts') ) {
			$output = 'You don\'t have proper permissions to update this task. :(';
			return $output;
		}

		$post_id	= $data['post_id'];

		if( isset( $data['nervetask-new-task-content'] ) ) {
			$post_content = $data['nervetask-new-task-content'];
		} else {
			$post_content = '';
		}

		$args = array(
			'ID'			=> $post_id,
			'post_content'  => $post_content
		);

		$post_id = wp_update_post( $args );

		// If the content updated succesffully
		if ( $post_id != 0 ) {

			$post = get_post( $post_id );

			$output = array(
				'status'	=> 'success',
				'message'	=> __('Success!'),
				'post'		=> $post
			);

		} else {
			$output = 'There was an error while creating a new task. Please refresh the page and try again.';
		}
	}

	/**
	 * Inserts a new comment.
	 *
	 * @since    0.1.0
	 */
	public function insert_comment( $data ) {

		if( empty( $data ) ) {
			return;
		}

		// If the current user can't publish posts stop
		if ( !current_user_can('read') ) {
			$output = 'You don\'t have proper permissions to insert this comment. :(';
			return $output;
		}

		if( isset( $data['post_id'] ) ) {
			$comment_post_id = $data['post_id'];
		} else {
			return;
		}
		if( isset( $_POST['nervetask-new-comment-content'] ) ) {
			$comment_content = $data['nervetask-new-comment-content'];
		} else {
			$comment_content = '';
		}

		$args = array(
			'comment_post_ID'	=> $comment_post_id,
			'comment_content'	=> $comment_content
		);

		$comment_id = wp_insert_comment ( $args );

		// If the comment inserted succesffully
		if ( $comment_id != 0 ) {

			$comment = get_comment( $comment_id );

			$output = array(
				'status'	=> 'success',
				'message'	=> __('Success!'),
				'comment'	=> $comment
			);
		} else {
			$output = 'There was an error while creating a new task. Please refresh the page and try again.';
		}
	}

	/**
	 * Updates the users assigned to a task.
	 *
	 * @since    0.1.0
	 */
	public function update_assignees( $data ) {

		if( empty( $data ) ) {
			return;
		}

		// If the current user can't edit posts stop
		if ( !current_user_can('edit_posts') ) {
			$output = 'You don\'t have proper permissions to update the assignees of this task. :(';
			return $output;
		}

		$users		= $data['users'];
		$post_id	= $data['post_id'];
		$all_users	= get_users();

		foreach ($all_users as $all_user) {
			p2p_type('nervetask_to_user')->disconnect( $post_id, $all_user->ID );
		}

		foreach ($users as $user) {
			p2p_type('nervetask_to_user')->connect( $post_id, $user, array( 'date' => current_time( 'mysql' ) ) );
		}

		$users = get_users(
			array(
				'connected_type' => 'nervetask_to_user',
				'connected_items' => $post_id
			)
		);

		// If the assignee updated succesffully
		if ( !empty( $users ) ) {
			$output = array(
				'status'	=> 'success',
				'message'	=> __('Success!'),
				'users'		=> $users
			);
		} else {
			$output = 'There was an error while creating a new task. Please refresh the page and try again.';
		}

		return $output;
	}

	/**
	 * Updates the status of a task.
	 *
	 * @since    0.1.0
	 */
	public function update_status( $data ) {

		if( empty( $data ) ) {
			return;
		}

		// If the current user can't edit posts stop
		if ( !current_user_can('edit_posts') ) {
			$output = 'You don\'t have proper permissions to update the status of this task. :(';
			return $output;
		}

		$status		= $data['status'];
		$post_id	= $data['post_id'];

		// Convert array values from strings to integers
		$status = array_map(
			create_function('$value', 'return (int)$value;'),
			$status
		);

		// Update the terms
		$result = wp_set_post_terms( $post_id, $status, 'nervetask_status' );

		// If the status updated succesffully
		if ( $result ) {

			$terms = get_the_terms( $post_id, 'nervetask_status' );

			$output = array(
				'status'	=> 'success',
				'message'	=> __('Success!'),
				'terms'		=> $terms
			);

		} else {
			$output = 'There was an error while creating a new task. Please refresh the page and try again.';
		}

		return $output;
	}

	/**
	 * Updates the priority of a task.
	 *
	 * @since    0.1.0
	 */
	public function update_priority( $data ) {

		if( empty( $data ) ) {
			return;
		}

		// If the current user can't edit posts stop
		if ( !current_user_can('edit_posts') ) {
			$output = 'You don\'t have proper permissions to update the priority task. :(';
			return $output;
		}

		$priority	= $data['priority'];
		$post_id	= $data['post_id'];

		// Convert array values from strings to integers
		$priority = array_map(
			create_function('$value', 'return (int)$value;'),
			$priority
		);

		// Update the terms
		$result = wp_set_post_terms( $post_id, $priority, 'nervetask_priority' );

		// If the priority updated succesffully
		if ( $result ) {

			$terms = get_the_terms( $post_id, 'nervetask_priority' );

			$output = array(
				'status'	=> 'success',
				'message'	=> __('Success!'),
				'terms'		=> $terms
			);

		} else {
			$output = 'There was an error while creating a new task. Please refresh the page and try again.';
		}

		return $output;
	}
	/**
	 * Updates the category of a task.
	 *
	 * @since    0.1.0
	 */
	public function update_category( $data ) {

		if( empty( $data ) ) {
			return;
		}

		// If the current user can't edit posts stop
		if ( !current_user_can('edit_posts') ) {
			$output = 'You don\'t have proper permissions to update the category of this task. :(';
			return $output;
		}

		$category	= $data['category'];
		$post_id	= $data['post_id'];

		// Convert array values from strings to integers
		$category = array_map(
			create_function('$value', 'return (int)$value;'),
			$category
		);

		// Update the terms
		$result = wp_set_post_terms( $post_id, $category, 'nervetask_category' );

		// If the category succesffully
		if ( $result ) {

			$terms = get_the_terms( $post_id, 'nervetask_category' );

			$output = array(
				'status'	=> 'success',
				'message'	=> __('Success!'),
				'terms'		=> $terms
			);

		} else {
			$output = 'There was an error while creating a new task. Please refresh the page and try again.';
		}

		return $output;
	}
}