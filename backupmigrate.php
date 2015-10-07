<?php
/*
	Plugin Name: Backup and Migrate
	Plugin URI: https://github.com/backupmigrate/backup_migrate_wordpress
	Description: Backup the WordPress database and files or migrate them to another environment.
	Author: Ryan Duff
	Version: 0.0.1
	Author URI: https://github.com/backupmigrate/backup_migrate_wordpress
	Text Domain: backupmigrate
	Domain Path: /lang
 */

use BackupMigrate\Core\Config\Config;

require __DIR__.'/vendor/autoload.php';

function backup_migrate_get_service_object( $config_array = [] ) {

	static $bam = NULL;

	// If the static cached object has not been loaded.
	if ($bam === NULL) {

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

add_filter( 'bam_service_locator', 'bam_override_service_locator', 10, 1 );
function bam_override_service_locator( $services ) {

	$services->add('TempFileAdapter',
		new \BackupMigrate\Core\File\TempFileAdapter('/tmp/', 'bam')
	);


	$services->add('TempFileManager',
		new \BackupMigrate\Core\File\TempFileManager($services->get('TempFileAdapter'))
	);

	return $services;

}

add_filter( 'bam_plugin_manager', 'bam_override_plugin_manager', 10, 2 );
function bam_override_plugin_manager( $plugins, $config_array ) {

	$connection = array(
		'host'     => DB_HOST,
		'username' => DB_USER,
		'password' => DB_PASSWORD,
		'database' => DB_NAME,
	);

	$db = new \BackupMigrate\Core\Source\MySQLiSource( new Config( $connection ) );
	$plugins->add('db', $db);

	$plugins->add('download', new \BackupMigrate\WordPress\Destination\WordPressBrowserDownloadDestination());

	return $plugins;

}

$bam = backup_migrate_get_service_object();

$bam->backup( 'db', 'download' );
