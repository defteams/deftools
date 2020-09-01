<?php
/**
 * Display radio field.
 */

$default = array_key_exists( 'default', $view_args ) ? $view_args['default'] : false;
$value   = deftools_get_option( $view_args['key'], $default );

?>
<ul class="deftools-radio-list <?php echo esc_attr( $view_args['classes'] ); ?>">
	<?php foreach ( $view_args['options'] as $option => $label ) : ?>
		<li><input type="radio"
				id="<?php printf( '%s_%s_%s', implode( '_', $view_args['settings'], $view_args['key'] ), $option ); ?>"
				name="<?php printf( '%s[%s]', $view_args['settings'], $view_args['name'] ); ?>"
				value="<?php echo esc_attr( $option ); ?>"
				<?php checked( $value, $option ); ?>
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
