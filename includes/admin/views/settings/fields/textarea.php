<?php
/**
 * Display textarea field.
 */

$value = deftools_get_option( $view_args['key'] );

if ( empty( $value ) ) :
	$value = isset( $view_args['default'] ) ? $view_args['default'] : '';
endif;

$rows = isset( $field['rows'] ) ? $field['rows'] : 4;
?>

<textarea
	id="<?php printf( '%s_%s', $view_args['settings'], implode( '_', $view_args['key'] ) ); ?>"
	name="<?php printf( '%s[%s]', $view_args['settings'], $view_args['name'] ); ?>"
	class="<?php echo esc_attr( $view_args['classes'] ); ?>"
	rows="<?php echo absint( $rows ); ?>"
	<?php echo deftools_get_arbitrary_attributes( $view_args ); ?>><?php echo esc_textarea( $value ); ?></textarea>

<?php if ( isset( $view_args['help'] ) ) : ?>

	<div class="deftools-help"><?php echo $view_args['help']; ?></div>

	<?php
endif;
