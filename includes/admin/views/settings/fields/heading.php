<?php
/**
 * Display section heading in settings area.
 */

if ( isset( $view_args['description'] ) ) : ?>
	<div class="deftools-description"><?php echo $view_args['description']; ?></div>
<?php else : ?>
<hr />
<?php endif; ?>
