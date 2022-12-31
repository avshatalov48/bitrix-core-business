<?php

namespace Bitrix\Landing\Assets;

use Bitrix\Landing\Agent;
use Bitrix\Landing\File;
use Bitrix\Landing\Site;
use Bitrix\Landing\Manager;
use Bitrix\Main;
use Bitrix\Main\FileTable;
use Bitrix\Main\Security\Random;
use Bitrix\Main\Web\HttpClient;
use Bitrix\Main\Web\WebPacker;

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
	 * @var string
	 */
	protected $filename;

	/**
	 * Unique string of current assets package
	 * @var string
	 */
	protected $packageHash;

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

				// dbg tmp log
				$file = \CFile::getFileArray($this->fileId);
				$request = new HttpClient(["redirect" => false,]);
				$request->query(HttpClient::HTTP_HEAD, $file['SRC']);
				if ($request->getStatus() !== 200)
				{
					\Bitrix\Landing\Debug::logToFile(
						"[lndgdbg] afterBuildFile, create new {$this->fileId} for lid {$this->landingId} with ORIG_NAME {$file['ORIGINAL_NAME']} and it not exists"
					);
				}
				$fileMsg = "File {$this->fileId} with NAME {$file['FILE_NAME']} and ORIG_NAME {$file['ORIGINAL_NAME']}";
				$duplicateMsg = "Has no duplicates";
				$original = \Bitrix\Main\File\Internal\FileDuplicateTable::query()
					->addSelect("ORIGINAL_ID")
					->where("DUPLICATE_ID", $this->fileId)
					->fetch();
				if ($original && $original['ORIGINAL_ID'])
				{
					$fileOrig = \CFile::getFileArray((int)$original['ORIGINAL_ID']);
					$duplicateMsg = "It is duplicate of orig {$original['ORIGINAL_ID']} with NAME {$fileOrig['FILE_NAME']} and ORIG_NAME {$fileOrig['ORIGINAL_NAME']}";
				}
				\Bitrix\Landing\Debug::logToFile(
					"[lndgdbg] afterBuildFile for lid {$this->landingId}. {$fileMsg}. {$duplicateMsg}."
				);
			}
		}
	}

	/**
	 * Prepare fileController for build
	 */
	protected function configureFile(): void
	{
		$msgAdd = '';
		if ($fileId = $this->findExistFile())
		{
			$this->fileId = $fileId;
			$file = \CFile::GetByID($fileId)->Fetch();
			$this->setFileName($file['ORIGINAL_NAME'] ?: $this->filename);
		}

		\Bitrix\Landing\Debug::logToFile(
			"[lndgdbg] configureFile for lid {$this->landingId}."
		);

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
				$currentFile = \CFile::GetByID($fileId)->Fetch();
				$msg = "Current file {$fileId} with ORIG_NAME {$currentFile['ORIGINAL_NAME']}. ";

				$currentName = self::DEFAULT_NAME . '_' . $this->packageHash;
				$msg .= "Hash name {$currentName} ";
				$msg .= strpos($currentFile['ORIGINAL_NAME'], $currentName) === 0
					? 'and it MATCH with file.'
					: 'and it NOT MATCH with file.'
				;

				\Bitrix\Landing\Debug::logToFile(
					"[lndgdbg] findExistFile for lid {$this->landingId}. {$msg}"
				);

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