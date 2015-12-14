<?php

// Run backup if nonce validates
if ( isset( $_REQUEST['bam_simple_backup'] ) && wp_verify_nonce( $_REQUEST['bam_simple_backup'], 'bam_simple_backup' ) ) {

	$bam = backup_migrate_get_service_object();
	$bam->backup( 'db', 'download' );

}

add_action('admin_menu', 'bam_add_admin_menu');
function bam_add_admin_menu() {

	add_management_page(
			__( 'Backup and Migrate', 'backupmigrate' ),
			__( 'Backup and Migrate', 'backupmigrate' ),
			'manage_options',
			'bam-admin-page',
			'bam_admin_page'
		);

}

function bam_admin_page() {

	echo '<div class="wrap">';
		echo '<h1>' . __( 'Backup and Migrate', 'backupmigrate' ) . '</h1>';

		echo '<form method="post">';
			echo echo wp_nonce_field( 'bam_simple_backup', 'bam_simple_backup' );
			submit_button( __( 'Backup Database', 'backupmigrate' ) );
		echo '</form>';

	echo '</div class="wrap">';

}

function backup_migrate_get_service_object( $config_array = [] ) {

	static $bam = NULL;

	// If the static cached object has not been loaded.
	if ( $bam === NULL ) {

		// Create the environment services.

		// Create the service locator
		$services = new \BackupMigrate\Core\Service\ServiceLocator();

		// Allow other wordpress plugins to add services.
		$services = apply_filters( 'bam_service_locator', $services );

		// Create the plugin manager
		$plugins = new \BackupMigrate\Core\Plugin\PluginManager( $services );

		// Allow other wordpress plugins to add plugins.
		$plugins = apply_filters( 'bam_plugin_manager', $plugins, $config_array );

		// Create the service object.
		$bam = new \BackupMigrate\Core\Main\BackupMigrate( $plugins );

		// Allow other modules to alter the BackupMigrate object
		$bam = apply_filters( 'bam_service_object', $bam, $plugins );

	}

	// Set the configuration overrides if any were passed in.
	if ( $config_array ) {

		$bam->setConfig( new Config( $config_array ) );

	}

  return $bam;

}
