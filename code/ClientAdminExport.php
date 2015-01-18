<?php

class ClientAdminExport extends LeftAndMainExtension {

	private static $allowed_actions = array(
		'assetbackup','dbbackup'
	);

	/**
	* @param Form $form
	* @return Form $form
	*/
	public function updateEditForm(Form $form) {
		$assetbackupButton = new LiteralField(
			'AssetBackupButton',
			sprintf(
			'<a class="ss-ui-button ss-ui-action ui-button-text-icon-primary" data-icon="arrow-circle-135-left" title="%s" href="%s">%s</a>',
			'Performs an asset backup in ZIP format. Useful if you want all assets and have no FTP access',
			$this->owner->Link('assetbackup'),
			'Backup Asset files'
			)
		);

		$dbbackupButton = new LiteralField(
			'DBBackupButton',
			sprintf(
			'<a class="ss-ui-button ss-ui-action ui-button-text-icon-primary" data-icon="arrow-circle-135-left" title="%s" href="%s">%s</a>',
			'Performs a database backup in ZIP format. Useful if you want a dump of the latest version of the database',
			$this->owner->Link('dbbackup'),
			'Backup Database'
			)
		);

		if($field = $this->fieldByExtraClass($form->Fields(), 'cms-actions-row')) {
			$field->push($assetbackupButton);
			$field->push($dbbackupButton);
		}

		return $form;
	}


	/**
	* Recursively search & return a field by 'extra class' from FieldList.
	*
	* @todo Could be added as a FieldList extension but it's a bit overkill for the sake of a button
	*
	* @param FieldList $fields
	* @param $class The extra class name to search for
	* @return FormField|null
	*/
	public function fieldByExtraClass(FieldList $fields, $class) {
		foreach($fields as $field)  {
			if($field->extraClasses){
				if(in_array($class, $field->extraClasses)) {
					return $field;
				}
			}
			if(method_exists($field, 'FieldList')) {
				return $this->fieldByExtraClass($field->FieldList(), $class);
			}
		}
	}

	/**
	* @return SS_HTTPRequest
	*/
	public function dbbackup() {
		$mysqldump = 'mysqldump';
		// TODO: check if mysqldump binaries exists
		global $databaseConfig;
		$siteConfig = SiteConfig::current_site_config();

		// Backup database
		$name = $databaseConfig['database'].'-'.date('Y-m-d-h-i-s').'.sql';
		$tempName = ASSETS_PATH.DIRECTORY_SEPARATOR.$name;
		$command = sprintf(
		'%s --user=%s --password=%s %s > %s',
		$mysqldump,
		escapeshellarg($databaseConfig['username']),
		($databaseConfig['password'] ? escapeshellarg($databaseConfig['password']):''),
		escapeshellarg($databaseConfig['database']),
		escapeshellarg($tempName)
		);
		shell_exec($command);
		$zipName = $databaseConfig['database'].'-'.date('Y-m-d-h-i-s').'.zip';
		$zipTmpName = ASSETS_PATH . DIRECTORY_SEPARATOR . $zipName;
		$zip = new ZipArchive();

		if(!$zip->open($zipTmpName, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
			user_error('Client Export Extension: Unable to read/write temporary zip archive', E_USER_ERROR);
			return;
		}

		$zip->addFile($tempName, $zipName);

		if(!$zip->status == ZipArchive::ER_OK) {
			user_error('Client Export Extension: ZipArchive returned an error other than OK', E_USER_ERROR);
			return;
		}

		$zip->close();

		ob_flush(); // fix browser crash(?)

		$content = file_get_contents($zipTmpName);
		unlink($zipTmpName);
		// Leave the database Backup in Folder for now TODO: set via config
		// unlink($tempName);

		return SS_HTTPRequest::send_file($content, $zipName);
	}

	/**
	* @return SS_HTTPRequest
	*/
	public function assetbackup() {
		$name = 'assets_' . SS_DateTime::now()->Format('Y-m-d') . '.zip';
		$tmpName = TEMP_FOLDER . '/' . $name;
		$zip = new ZipArchive();

		if(!$zip->open($tmpName, ZipArchive::CREATE | ZipArchive::OVERWRITE)) {
			user_error('Client Export Extension: Unable to read/write temporary zip archive', E_USER_ERROR);
			return;
		}

		$files = new RecursiveIteratorIterator(
		new RecursiveDirectoryIterator(
		ASSETS_PATH,
		RecursiveDirectoryIterator::SKIP_DOTS
		)
		);

		foreach($files as $file) {
			$local = str_replace(ASSETS_PATH . '/', '', $file);
			$zip->addFile($file, $local);
		}

		if(!$zip->status == ZipArchive::ER_OK) {
			user_error('Client Export Extension: ZipArchive returned an error other than OK', E_USER_ERROR);
			return;
		}

		$zip->close();

		ob_flush(); // fix browser crash(?)

		$content = file_get_contents($tmpName);
		unlink($tmpName);

		return SS_HTTPRequest::send_file($content, $name);
	}
}
