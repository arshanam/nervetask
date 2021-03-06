<?php

	$statuses = get_terms( 'nervetask_status', array( 'hide_empty' => 0, 'orderby' => 'slug' ) );
	$assigned_statuses = wp_get_object_terms( get_the_ID(), 'nervetask_status', array( 'fields' => 'ids' ) );
?>

<form class="nervetask-update-status form-horizontal" role="form" method="post">

	<div>
		<?php if ( ! empty( $assigned_statuses ) ) { ?>
			<strong>Status</strong>:
			<span class="task-status">
			<?php foreach ( $assigned_statuses as $status ) { $status = get_term_by( 'id', $status, 'nervetask_status' );  ?>
				<?php if( current_user_can( 'edit_posts' ) ) { ?><a type="button" data-toggle="collapse" data-target="#task-meta-status-options" href="#"><?php } ?>
					<strong><?php echo esc_html( $status->name ); ?></strong>
				<?php if( current_user_can( 'edit_posts' ) ) { ?></a><?php } ?>
			<?php } ?>
			</span>
			
		<?php } else { ?>
			<span class="task-status">There is no assigned status</span>
		<?php } ?>
	</div>

	<div class="collapse" id="task-meta-status-options">

	<?php if ( ! empty( $statuses ) ) { ?>

		<div class="form-group">

			<div class="control-input">

				<select size="11" name="status[]" class="chosen-select">

				<?php foreach ( $statuses as $status ) { ?>

					<?php
					if ( in_array($status->term_id, $assigned_statuses ) ) {
						$selected = ' selected';
					} else {
						$selected = false;
					}
					?>
					<option value ="<?php echo $status->term_id; ?>"<?php echo $selected; ?>><?php echo $status->name; ?></option>

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
		<p>There are no statuses</p>
	<?php } ?>

	</div>

	<input type="hidden" name="action" value="nervetask">
	<input type="hidden" name="controller" value="nervetask_update_status">
	<input type="hidden" name="post_id" value="<?php the_ID(); ?>">
	<input type="hidden" name="security" value="<?php echo wp_create_nonce( 'nervetask_update_status' ); ?>">

</form>