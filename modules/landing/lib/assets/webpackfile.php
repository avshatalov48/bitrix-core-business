<?php

namespace Bitrix\Landing\Assets;

use Bitrix\Landing\File;
use Bitrix\Main;
use Bitrix\Main\FileTable;
use Bitrix\Main\Web\WebPacker;

class WebpackFile
{
	protected const MODULE_ID = 'landing';
	protected const CORE_EXTENSION = 'ui.webpacker';
	protected const LANG_RESOURCE = '/bitrix/js/landing/webpackassets/message_loader.js';

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
	 * @var WebPacker\Resource\Profile
	 */
	protected $profile;

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
		$this->profile = new WebPacker\Resource\Profile();
	}

	/**
	 * Assets created for every landing.
	 * @param int $lid - id of landing
	 */
	public function setLandingId(int $lid): void
	{
		$this->landingId = $lid;
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
	public function addResource(string $resource): void
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
		$fileQuery = FileTable::query()
			->addSelect('ID')
			->addSelect('ORIGINAL_NAME')
			->where('MODULE_ID', self::MODULE_ID)
			->where('ORIGINAL_NAME', $this->getFileName())
		;
		// if have not landing ID - old variant, find something
		if ($this->landingId)
		{
			if ($fileIds = File::getFilesFromAsset($this->landingId))
			{
				$fileQuery->whereIn('ID', $fileIds);
			}
			else
			{
				return false;
			}
		}

		if ($file = $fileQuery->fetch())
		{
			return $file['ID'];
		}

		return false;
	}

	public function setUseLang(): void
	{
		$this->profile->useAllLangs(true);
		$this->addResource(self::LANG_RESOURCE);
	}

	protected function configureResources(): void
	{
		$this->fileController->addExtension(self::CORE_EXTENSION);    // need core ext always
		$this->fileController->addModule(
			new WebPacker\Module(
				WebpackBuilder::PACKAGE_NAME,
				$this->package,
				$this->profile
			)
		);
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
	 * @throws Main\SystemException
	 */
	public static function markToRebuild($lid): void
	{
		if (!$lid || empty($lid))
		{
			throw new Main\ArgumentException('LID must be int or array of int', 'lid');
		}

		File::markAssetToRebuild($lid);
	}

	/**
	 * * Mark webpack files for landing as "need rebuild", but not delete them. File will be exist until not created new file.
	 */
	public static function markAllToRebuild(): void
	{
		File::markAssetToRebuild();
	}
}