<?php
/**
 * Display notice in settings area.
 */

$notice_type = isset( $view_args['notice_type'] ) ? $view_args['notice_type'] : 'error';

?>
<div class="deftools-notice deftools-inline-notice deftools-notice-<?php echo esc_attr( $notice_type ); ?>" <?php echo deftools_get_arbitrary_attributes( $view_args ); ?>>
	<p><?php echo $view_args['content']; ?></p>
</div>
