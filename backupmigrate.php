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

require __DIR__.'/vendor/autoload.php';

use BackupMigrate\Core\Config\Config;

add_action( 'plugins_loaded', array ( BAM_WordPress::get_instance(), 'plugin_setup' ) );

class BAM_WordPress {
	/**
	 * Plugin instance.
	 *
	 * @see get_instance()
	 * @type object
	 */
	protected static $instance = NULL;

	/**
	 * Access this plugin’s working instance
	 *
	 * @wp-hook plugins_loaded
	 * @since   0.1.0
	 * @return  object of this class
	 */
	public static function get_instance() {

		NULL === self::$instance and self::$instance = new self;

		return self::$instance;

	}


	/**
	 * Used for regular plugin work.
	 *
	 * @wp-hook plugins_loaded
	 * @since   0.1.0
	 * @return  void
	 */
	public function plugin_setup() {

		add_filter( 'bam_service_locator', array( $this, 'bam_override_service_locator' ), 10, 1 );
		add_filter( 'bam_plugin_manager', array( $this, 'bam_override_plugin_manager' ), 10, 2 );

		$bam = $this->backup_migrate_get_service_object();

		$bam->backup( 'db', 'download' );

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


	function bam_override_service_locator( $services ) {

		$services->add( 'TempFileAdapter',
			new \BackupMigrate\Core\File\TempFileAdapter( '/tmp/', 'bam' )
		);


		$services->add( 'TempFileManager',
			new \BackupMigrate\Core\File\TempFileManager( $services->get( 'TempFileAdapter' ) )
		);

		return $services;

	}


	function bam_override_plugin_manager( $plugins, $config_array ) {

		$connection = array(
			'host'     => DB_HOST,
			'username' => DB_USER,
			'password' => DB_PASSWORD,
			'database' => DB_NAME,
		);

		$db = new \BackupMigrate\Core\Source\MySQLiSource( new Config( $connection ) );
		$plugins->add('db', $db);

		$plugins->add( 'download', new \BackupMigrate\WordPress\Destination\WordPressBrowserDownloadDestination() );

		return $plugins;

	}

}
