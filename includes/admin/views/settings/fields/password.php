<?php
/**
 * Display password field.
 */

$value = deftools_get_option( $view_args['key'] );

if ( empty( $value ) ) :
	$value = isset( $view_args['default'] ) ? $view_args['default'] : '';
endif;

?>
<input type="password"
	id="<?php printf( '%s_%s', $view_args['settings'], implode( '_', $view_args['key'] ) ); ?>"
	name="<?php printf( '%s[%s]', $view_args['settings'], $view_args['name'] ); ?>"
	value="<?php echo esc_attr( $value ); ?>"
	class="<?php echo esc_attr( $view_args['classes'] ); ?>"
	<?php echo deftools_get_arbitrary_attributes( $view_args ); ?> />
<?php if ( isset( $view_args['help'] ) ) : ?>
	<div class="deftools-help"><?php echo $view_args['help']; ?></div>
	<?php
endif;
