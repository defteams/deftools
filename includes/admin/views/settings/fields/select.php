<?php
/**
 * Display select field.
 */

$value = deftools_get_option( $view_args['key'] );

if ( empty( $value ) ) {
	$value = isset( $view_args['default'] ) ? $view_args['default'] : '';
}

?>
<select id="<?php printf( '%s_%s', $view_args['settings'], implode( '_', $view_args['key'] ) ); ?>"
	name="<?php printf( '%s[%s]', $view_args['settings'], $view_args['name'] ); ?>"
	class="<?php echo esc_attr( $view_args['classes'] ); ?>"
	<?php echo deftools_get_arbitrary_attributes( $view_args ); ?>
	>
	<?php
	foreach ( $view_args['options'] as $key => $option ) :
		if ( is_array( $option ) ) :
			$label = isset( $option['label'] ) ? $option['label'] : '';
			?>
			<optgroup label="<?php echo $label; ?>">
			<?php foreach ( $option['options'] as $k => $opt ) : ?>
				<option value="<?php echo $k; ?>" <?php selected( $k, $value ); ?>><?php echo $opt; ?></option>
			<?php endforeach ?>
			</optgroup>
		<?php else : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( $key, $value ); ?>><?php echo $option; ?></option>
		<?php
		endif;
	endforeach
	?>
</select>
<?php if ( isset( $view_args['help'] ) ) : ?>
	<div class="deftools-help"><?php echo $view_args['help']; ?></div>
<?php
endif;
