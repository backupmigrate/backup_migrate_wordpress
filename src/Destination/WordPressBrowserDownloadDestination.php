<?php
/**
 * @file
 * Contains WordPress\WordPressBrowserDownloadDestination
 */

namespace BackupMigrate\WordPress\Destination;

use \BackupMigrate\Core\Destination\BrowserDownloadDestination;
use BackupMigrate\Core\File\BackupFileReadableInterface;

/**
 * Class WordPressBrowserDownloadDestination
 * @package BackupMigrate\WordPress\Destination
 */
class WordPressBrowserDownloadDestination extends BrowserDownloadDestination {

	function saveFile(BackupFileReadableInterface $file) {

		parent::saveFile($file);

		exit();

	}

}
