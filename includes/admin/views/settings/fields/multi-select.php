<?php
/**
 * Display select field.
 */

$value = deftools_get_option( $view_args['key'], array() );

if ( empty( $value ) ) {
	$value = isset( $view_args['default'] ) ? $view_args['default'] : array();
}

?>
<select id="<?php printf( '%s_%s', $view_args['settings'], implode( '_', $view_args['key'] ) ); ?>"
	name="<?php printf( '%s[%s][]', $view_args['settings'], $view_args['name'] ); ?>"
	class="<?php echo esc_attr( $view_args['classes'] ); ?>"
	<?php echo deftools_get_arbitrary_attributes( $view_args ); ?>
	multiple="multiple"
	>
	<?php
	foreach ( $view_args['options'] as $key => $option ) :
		if ( is_array( $option ) ) :
			$label = isset( $option['label'] ) ? $option['label'] : '';
			?>
			<optgroup label="<?php echo $label; ?>">
			<?php foreach ( $option['options'] as $k => $opt ) : ?>
				<option value="<?php echo $k; ?>" <?php selected( in_array( $k, $value ) ); ?>><?php echo $opt; ?></option>
			<?php endforeach ?>
			</optgroup>
		<?php else : ?>
			<option value="<?php echo esc_attr( $key ); ?>" <?php selected( in_array( $key, $value ) ); ?>><?php echo $option; ?></option>
		<?php
		endif;
	endforeach
	?>
</select>
<?php if ( isset( $view_args['help'] ) ) : ?>
	<div class="deftools-help"><?php echo $view_args['help']; ?></div>
<?php
endif;
