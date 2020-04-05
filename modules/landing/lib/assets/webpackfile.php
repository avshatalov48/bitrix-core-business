<?php

namespace Bitrix\Landing\Assets;

use Bitrix\Landing\File;
use Bitrix\Main;
use Bitrix\Main\Web\WebPacker;

class WebpackFile
{
	protected const MODULE_ID = 'landing';
	
	/**
	 * @var WebPacker\FileController
	 */
	protected $fileController;
	/**
	 * @var int relation with file and landing in table
	 */
	protected $landingId;
	/**
	 * @var int - ID of file from b_file
	 */
	protected $fileId;
	
	/**
	 * @var WebPacker\Resource\Package
	 */
	protected $package;
	
	/**
	 * Name of file. If not set - will be using default
	 * @var
	 */
	protected $filename;
	
	/**
	 * WebpackFile constructor.
	 */
	public function __construct()
	{
		$this->fileController = new WebPacker\FileController();
		$this->package = new WebPacker\Resource\Package();
	}
	
	/**
	 * Assets created for every landing.
	 * @param int $lid - id of landing
	 */
	public function setLandingId(int $lid): void
	{
		$this->landingId = (int)$lid;
	}
	
	/**
	 * Set unique name of file. If not set - will be using default
	 * @param string $name
	 */
	public function setFileName(string $name): void
	{
		$this->filename = $name;
	}

	protected function getFileName(): string
	{
		return $this->filename ?: WebpackBuilder::PACKAGE_NAME . WebpackBuilder::PACKAGE_NAME_SUFFIX . '.js';
	}
	
	/**
	 * @param string $resource Relative path to asset.
	 */
	public function addResource($resource): void
	{
		$this->package->addAsset(WebPacker\Resource\Asset::create($resource));
	}
	
	/**
	 * Create new or get existing webpack file.
	 */
	public function build(): void
	{
		$this->configureFile();
		$this->configureResources();
		
		// create new file
		if (!$this->fileId)
		{
			$res = $this->fileController->build();
			if ($res->isSuccess())
			{
				$this->fileId = $res->getId();
				File::addToAsset($this->landingId, $this->fileId);
			}
		}
	}
	
	/**
	 * Prepare fileController for build
	 */
	protected function configureFile(): void
	{
		// todo: js cache lifetime
		if ($fileId = $this->findExistFile())
		{
			$this->fileId = $fileId;
		}
		
		$this->fileController->configureFile(
			$this->fileId,
			self::MODULE_ID,
			'',
			$this->getFileName()
		);
	}
	
	/**
	 * Search existing asset file for current landing
	 * @return bool|int - ID of file or false if not exist
	 */
	protected function findExistFile()
	{
		if($this->landingId)
		{
			$files = File::getFilesFromAsset($this->landingId);
			if (!empty($files))
			{
				return $files[count($files) - 1];    //last
			}
		}

		// old variant - have not landing ID
		else
		{
			$resFile = \CFile::GetList([], [
				'ORIGINAL_NAME' => $this->getFileName(),
				'MODULE_ID' => self::MODULE_ID,
			]);
			if($file = $resFile->Fetch())
			{
				return $file['ID'];
			}
		}

		return false;
	}

	protected function configureResources(): void
	{
		$this->fileController->addExtension('ui.webpacker');    // need core ext always
		$this->fileController->addModule(new WebPacker\Module(WebpackBuilder::PACKAGE_NAME, $this->package));
	}
	
	/**
	 * Return JS-string for load assets pack
	 * File must be builded before
	 * @return string
	 */
	public function getOutput(): string
	{
		return $this->fileController->getLoader()->getString();
	}
	
	/**
	 * Mark webpack files for landing as "need rebuild", but not delete them. File will be exist until not created new file.
	 * @param int|[int] $lid - array of landing IDs.
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */

	public static function markToRebuild($lid): void
	{
		if(!$lid || empty($lid))
		{
			throw new Main\ArgumentException('LID must be int or array of int', 'lid');
		}

		File::markAssetToRebuild($lid);
	}

	/**
	 * * Mark webpack files for landing as "need rebuild", but not delete them. File will be exist until not created new file.
	 * @throws Main\ArgumentException
	 * @throws Main\ObjectPropertyException
	 * @throws Main\SystemException
	 */
	public static function markAllToRebuild(): void
	{
		File::markAssetToRebuild();
	}
}