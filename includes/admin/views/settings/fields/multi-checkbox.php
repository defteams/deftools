<?php
/**
 * Display a series of checkboxes.
 */

$value = deftools_get_option( $view_args['key'], array() );

if ( empty( $value ) ) :
	$value = isset( $view_args['default'] ) ? (array) $view_args['default'] : array();
endif;

if ( ! is_array( $value ) ) {
	$value = (array) $value;
}

$setting_attributes = '';

if ( array_key_exists( 'attrs', $view_args ) ) {

	$setting_attributes_keys = array( 'data-trigger-key', 'data-trigger-change-type', 'data-trigger-value' );

	foreach ( $setting_attributes_keys as $key ) {

		if ( ! array_key_exists( $key, $view_args['attrs'] ) ) {
			continue;
		}

		$setting_attributes .= sprintf( ' %s="%s"', $key, esc_attr( $view_args['attrs'][ $key ] ) );

		unset( $view_args['attrs'][ $key ] );

	}
}

?>
<ul class="deftools-checkbox-list <?php echo esc_attr( $view_args['classes'] ); ?>" <?php echo $setting_attributes; ?>>

	<?php foreach ( $view_args['options'] as $option => $label ) : ?>

		<li><input type="checkbox"
				id="<?php printf( '%s_%s_%s', implode( '_', $view_args['settings'], $view_args['key'] ), $option ); ?>"
				name="<?php printf( '%s[%s][]', $view_args['settings'], $view_args['name'] ); ?>"
				value="<?php echo esc_attr( $option ); ?>"
				<?php checked( in_array( $option, $value ) ); ?>
				<?php echo deftools_get_arbitrary_attributes( $view_args ); ?>
			/>
			<?php echo $label; ?>
		</li>

	<?php endforeach ?>

</ul>
<?php if ( isset( $view_args['help'] ) ) : ?>
	<div class="deftools-help"><?php echo $view_args['help']; ?></div>
	<?php
endif;
