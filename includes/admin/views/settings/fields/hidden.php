<?php
/**
 * Add a hidden field in settings area.
 */

?>
<input type="hidden"
	id="<?php printf( '%s_%s', $view_args['settings'], implode( '_', $view_args['key'] ) ); ?>"
	name="<?php printf( '%s[%s]', $view_args['settings'], $view_args['name'] ); ?>"
	value="<?php echo esc_attr( $view_args['value'] ); ?>"
/>
