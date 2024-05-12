<?php

namespace Bitrix\Landing\Assets;

use Bitrix\Landing\Agent;
use Bitrix\Landing\File;
use Bitrix\Landing\Manager;
use Bitrix\Main;
use Bitrix\Main\FileTable;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Web\WebPacker;

/**
 * Manage webpack files
 */
class WebpackFile
{
	protected const MODULE_ID = 'landing';
	protected const DIR_NAME = 'assets';
	protected const DEFAULT_NAME = 'assets_webpack';
	protected const CORE_EXTENSION = 'ui.webpacker';
	protected const LANG_RESOURCE = '/bitrix/js/landing/webpackassets/message_loader.js';

	/**
	 * @var WebPacker\FileController
	 */
	protected WebPacker\FileController $fileController;

	/**
	 * @var int relation with file and landing in table
	 */
	protected int $landingId;

	/**
	 * @var int|null - ID of file from b_file
	 */
	protected ?int $fileId = null;

	/**
	 * @var WebPacker\Resource\Package
	 */
	protected WebPacker\Resource\Package $package;

	/**
	 * @var WebPacker\Resource\Profile
	 */
	protected WebPacker\Resource\Profile $profile;

	/**
	 * Name of file. If not set - will be using default
	 * @var string
	 */
	protected string $filename;

	/**
	 * Unique string of current assets package
	 * @var string
	 */
	protected string $packageHash;

	/**
	 * For browser cache
	 */
	protected static int $cacheTtl = 86400; // one day

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
	 * Set unique string for current assets package
	 * @param string $hash
	 */
	public function setPackageHash(string $hash): void
	{
		$this->packageHash = $hash;
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
		if($this->packageHash)
		{
			$this->filename = self::DEFAULT_NAME . '_' . $this->packageHash . '_' . time() . '.js';
		}
		else
		{
			$this->filename = self::DEFAULT_NAME . '_' . Random::getString(16) . '.js';
		}

		return $this->filename;
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

				// tmp fixing agent for 149117
				Agent::addUniqueAgent('checkFileExists', [$this->fileId], 86400, 60);
			}
		}
	}

	/**
	 * Prepare fileController for build
	 */
	protected function configureFile(): void
	{
		if ($fileId = $this->findExistFile())
		{
			$this->fileId = $fileId;
			$file = \CFile::GetByID($fileId)->Fetch();
			$this->setFileName($file['ORIGINAL_NAME'] ?: $this->filename);
		}

		$this->fileController->configureFile(
			$this->fileId,
			self::MODULE_ID,
			self::DIR_NAME,
			$this->getFileName()
		);
	}

	/**
	 * Search existing asset file for current landing
	 * @return null|int - ID of file or false if not exist
	 */
	protected function findExistFile(): ?int
	{
		if ($this->landingId)
		{
			foreach(File::getFilesFromAsset($this->landingId) as $fileId)
			{
				if(
					$fileId > 0
					&& $this->packageHash
					&& ($file = \CFile::GetByID($fileId)->Fetch())
					&& strpos($file['ORIGINAL_NAME'], self::DEFAULT_NAME . '_' . $this->packageHash) === 0
				)
				{
					return $fileId;
				}
			}

			return null;
		}

		// if have not landing ID - old variant, find something
		$fileQuery = FileTable::query()
			->addSelect('ID')
			->addSelect('ORIGINAL_NAME')
			->where('MODULE_ID', self::MODULE_ID)
			->where('%ORIGINAL_NAME', self::DEFAULT_NAME)
		;
		$file = $fileQuery->fetch();

		return $file ? $file['ID'] : null;
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
				self::DEFAULT_NAME,
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
		return $this->fileController
			->getLoader()
			->setCacheTtl(self::$cacheTtl)
			->getString();
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

		if (File::markAssetToRebuild($lid))
		{
			Manager::clearCacheForLanding($lid);
		}
	}

	/**
	 * * Mark webpack files for landing as "need rebuild", but not delete them. File will be exist until not created new file.
	 */
	public static function markAllToRebuild(): void
	{
		if (File::markAssetToRebuild())
		{
			Manager::clearCache();
		}
	}
}