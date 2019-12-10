<?php
/**
 * Display checkbox field.
 */

$value = deftools_get_option( $view_args['key'] );

if ( ! strlen( $value ) ) {
	$value = isset( $view_args['default'] ) ? $view_args['default'] : 0;
}

?>
<input type="checkbox"
	id="<?php printf( 'deftools-settings_%s', implode( '_', $view_args['key'] ) ); ?>"
	name="<?php printf( 'deftools-settings[%s]', $view_args['name'] ); ?>"
	class="<?php echo esc_attr( $view_args['classes'] ); ?>"
	<?php checked( $value ); ?>
	<?php echo deftools_get_arbitrary_attributes( $view_args ); ?>/>
<?php if ( isset( $view_args['help'] ) ) : ?>
	<div class="deftools-help"><?php echo $view_args['help']; ?></div>
<?php
endif;
