<?php

	$user_query = new WP_User_Query(
		array(
			'fields' => array(
				'ID',
				'display_name'
			)
		)
	);

	// Query for users based on the meta data
	$assigned_user_query = new WP_User_Query(
		array(
			'fields'			=> 'ID',
			'connected_type'	=> 'nervetask_to_user',
			'connected_items'	=> get_queried_object()
		)
	);

?>

<form class="nervetask-update-assignees form-horizontal" role="form" method="post">

	<div>
		<strong><?php _e( 'Assigned to', 'nervetask' ); ?></strong>:
		<strong><span class="task-assigned">
		<?php if ( ! empty( $assigned_user_query->results ) ) { ?>
			<?php foreach ( $assigned_user_query->results as $user ) { $user = get_user_by( 'id', $user ); if( isset( $prefix ) ) { echo $prefix; } ?>
				<?php if( current_user_can( 'edit_posts' ) ) { ?><a type="button" data-toggle="collapse" data-target="#task-meta-assignees-options" href="#"><?php } ?>
				<?php echo esc_html( $user->display_name ); ?>
				<?php if( current_user_can( 'edit_posts' ) ) { ?></a><?php } ?><?php $prefix = ', '; } ?>
		<?php } else { ?>
			<?php if( current_user_can( 'edit_posts' ) ) { ?><a type="button" data-toggle="collapse" data-target="#task-meta-assignees-options" href="#"><?php }?>
			<?php _e( 'None', 'nervetask' ); ?>
			<?php if( current_user_can( 'edit_posts' ) ) { ?></a><?php }?>
		<?php } ?>
		</span></strong>
	</div>

	<div class="collapse" id="task-meta-assignees-options">

	<?php if ( ! empty( $user_query->results ) ) { ?>

		<div class="form-group">

			<div class="control-input">

				<select multiple="multiple" name="users[]" class="chosen-select">

				<?php foreach ( $user_query->results as $user ) { ?>

					<?php
					if ( in_array($user->ID, $assigned_user_query->results ) ) {
						$selected = ' selected';
					} else {
						$selected = false;
					}
					?>
					<option value ="<?php echo $user->ID; ?>"<?php echo $selected; ?>><?php echo $user->display_name; ?></option>

				<?php } ?>
				</select>

			</div>

		</div>

		<div class="form-group">
			<div class="control-input control-submit">
				<button type="submit" class="btn">Update</button>
			</div>
		</div>

	<?php } else { ?>
		<p>There are no users</p>
	<?php } ?>

	</div>

	<input type="hidden" name="action" value="nervetask">
	<input type="hidden" name="controller" value="nervetask_update_assignees">
	<input type="hidden" name="post_id" value="<?php the_ID(); ?>">
	<input type="hidden" name="security" value="<?php echo wp_create_nonce( 'nervetask_update_assignees' ); ?>">

</form>