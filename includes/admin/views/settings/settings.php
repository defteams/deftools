<?php
/**
 * Display the main settings page wrapper.
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}
?>

<div id="deftools-settings" class="wrap">
	<h1 class="screen-reader-text"><?php echo get_admin_page_title(); ?></ha>
	<h2 class="nav-tab-wrapper">
		<?php foreach ( $sections as $tab => $name ) : ?>
			<a href="<?php echo esc_url( add_query_arg( array( 'tab' => $tab ), admin_url( 'admin.php?page=deftools-settings' ) ) ); ?>" class="nav-tab <?php echo $active_tab == $tab ? 'nav-tab-active' : ''; ?>"><?php echo $name; ?></a>
		<?php endforeach ?>
	</h2>
	<?php if ( $group != $active_tab ) : ?>
		<?php /* translators: %s: active settings tab label */ ?>
		<p><a href="<?php echo esc_url( add_query_arg( array( 'tab' => $active_tab ), admin_url( 'admin.php?page=deftools-settings' ) ) ); ?>"><?php printf( __( '&#8592; Return to %s', 'deftools' ), $sections[ $active_tab ] ); ?></a></p>
	<?php endif ?>
	<?php
		/**
		 * Do or render something right before the settings form.
		 *
		 * @since 1.0.0
		 *
		 * @param string $group The settings group we are viewing.
		 */
		do_action( 'deftools_before_admin_settings', $group );
	?>
	<form method="post" action="options.php">
		<table class="form-table">
		<?php
			settings_fields( 'deftools-settings' );

			// do_settings_fields( 'deftools-settings_' . $group, 'deftools-settings_' . $group );

			deftools_do_settings_fields( 'deftools-settings_' . $group, 'deftools-settings_' . $group );
		?>
		</table>
		<?php
			/**
			 * Filter the submit button at the bottom of the settings table.
			 *
			 * @since 1.0.0
			 *
			 * @param string $button The button output.
			 */
			echo apply_filters( 'deftools-settings_button_' . $group, get_submit_button( null, 'primary', 'submit', true, null ) );
		?>
	</form>
	<?php
		/**
		 * Do or render something right after the settings form.
		 *
		 * @since 1.0.0
		 *
		 * @param string $group The settings group we are viewing.
		 */
		do_action( 'deftools_after_admin_settings', $group );
	?>
</div>