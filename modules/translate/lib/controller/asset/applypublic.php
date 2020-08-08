<?php
namespace Bitrix\Translate\Controller\Asset;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;


/**
 * Harvester of the lang folder disposition.
 */
class ApplyPublic
	extends Translate\Controller\Action
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	/** @var string */
	private $languageId;

	/** @var string */
	private $tmpFolderPath;

	/** @var string */
	private $sourceFolderPath;

	/** @var int */
	private $totalFileCount;

	/** @var string */
	public static $documentRoot;
	/** @var boolean */
	public static $useTranslationRepository;
	/** @var string[] */
	public static $enabledLanguages;
	/** @var string */
	public static $translationRepositoryRoot;

	/** @var string */
	private $seekPath;
	/** @var string */
	private $seekModule;
	/** @var string */
	private $seekType;
	/** @var string[] */
	private $seekAncestors;


	const TARGET_FOLDERS = array(
		'component' => '#BX_ROOT#/components/bitrix',
		'activities' => '#BX_ROOT#/activities/bitrix',
		'gadgets' => '#BX_ROOT#/gadgets/bitrix',
		'wizards' => '#BX_ROOT#/wizards/bitrix',
		'blocks' => '#BX_ROOT#/blocks/bitrix',
		'template' => '#BX_ROOT#/templates',
		'mobileapp' => '#BX_ROOT#/mobileapp',
		'js' => '#BX_ROOT#/js',
	);
	const SOURCE_FOLDERS = array(
		'component' => '/install/components/bitrix',
		'activities' => '/install/activities/bitrix',
		'gadgets' => '/install/gadgets/bitrix',
		'wizards' => '/install/wizards/bitrix',
		'blocks' => '/install/blocks/bitrix',
		'template' => '/install/templates',
		'mobileapp' => '/install/mobileapp',
		'js' => '/install/js',
	);



	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, $config = array())
	{
		$this->keepField([
			'languageId', 'tmpFolderPath', 'totalFileCount', 'sourceFolderPath',
			'seekPath', 'seekModule', 'seekType'
		]);

		parent::__construct($name, $controller, $config);

		self::$useTranslationRepository = Main\Localization\Translation::useTranslationRepository();
		self::$enabledLanguages = Translate\Config::getEnabledLanguages();
		if (self::$useTranslationRepository)
		{
			self::$translationRepositoryRoot = Main\Localization\Translation::getTranslationRepositoryPath();
		}
		self::$documentRoot = rtrim(Translate\IO\Path::tidy(Main\Application::getDocumentRoot()), '/');
	}

	/**
	 * Runs controller action.
	 *
	 * @return array
	 */
	public function run()
	{
		// continue previous process
		$progressParams = $this->getProgressParameters();

		$updatePublic = $this->controller->getRequest()->get('updatePublic');
		if ($updatePublic === null || $updatePublic !== 'Y')
		{
			// finish it
			return array(
				'STATUS' => Translate\Controller\STATUS_COMPLETED,
				'PROCESSED_ITEMS' => $progressParams['totalFileCount'],
				'TOTAL_ITEMS' => $progressParams['totalItems'],
			);
		}


		$this->languageId = $progressParams['languageId'];
		$this->tmpFolderPath = $progressParams['tmpFolderPath'];
		$this->totalFileCount = $progressParams['totalFileCount'];


		if ($this->isNewProcess)
		{
			$this->totalItems = $this->totalFileCount;
			$this->processedItems = 0;

			// language
			$languageId = $this->controller->getRequest()->get('languageId');
			if (empty($languageId))
			{
				$this->addError(new Main\Error(Loc::getMessage('TR_ERROR_SELECT_LANGUAGE')));
			}
			if (!in_array($languageId, self::$enabledLanguages))
			{
				$this->addError(new Main\Error(Loc::getMessage('TR_ERROR_LANGUAGE_ID')));
			}
			else
			{
				$this->languageId = $languageId;
			}

			$this->sourceFolderPath = Translate\IO\Path::tidy($this->tmpFolderPath .'/'. $this->languageId. '/');

			$sourceDirectory = new Translate\IO\Directory($this->sourceFolderPath);
			if (!$sourceDirectory->isExists())
			{
				$this->addError(
					new Error(Loc::getMessage('TR_ERROR_CREATE_TARGET_FOLDER', array('#PATH#' => $this->sourceFolderPath)))
				);
			}

			$this->saveProgressParameters();

			return array(
				'STATUS' => ($this->totalItems > 0 ? Translate\Controller\STATUS_PROGRESS : Translate\Controller\STATUS_COMPLETED),
				'PROCESSED_ITEMS' => 0,
				'TOTAL_ITEMS' => $this->totalItems,
			);
		}

		$this->targetFolderPath = $progressParams['targetFolderPath'];
		$this->seekPath = $progressParams['seekPath'];
		$this->seekModule = $progressParams['seekModule'];
		$this->seekType = $progressParams['seekType'];

		return $this->performStep('runApplying');
	}


	/**
	 * Copying lang files.
	 *
	 * @return array
	 */
	private function runApplying()
	{
		$processedItemCount = 0;

		if (!empty($this->seekPath))
		{
			$this->seekAncestors = array();
			$arr = explode('/', str_replace($this->sourceFolderPath, '', $this->seekPath));
			array_pop($arr);//last file
			$parts = array();
			foreach ($arr as $part)
			{
				$parts[] = $part;
				$this->seekAncestors[] = $this->sourceFolderPath. implode('/', $parts);
			}
		}

		$sourceDirectory = new Translate\IO\Directory($this->sourceFolderPath);
		foreach ($sourceDirectory->getChildren() as $entry)
		{
			$moduleName = $entry->getName();
			if (in_array($moduleName, Translate\IGNORE_FS_NAMES) || !$entry->isDirectory())
			{
				continue;
			}
			if (!empty($this->seekModule))
			{
				if ($this->seekModule !== $moduleName)
				{
					continue;
				}
				$this->seekModule = null;
			}

			foreach (self::SOURCE_FOLDERS as $type => $typeSourcePath)
			{
				if (!empty($this->seekType))
				{
					if ($this->seekType !== $type)
					{
						continue;
					}
					$this->seekType = null;
				}

				$sourceFolder = new Main\IO\Directory($entry->getPhysicalPath(). $typeSourcePath);
				if ($sourceFolder->isExists())
				{
					$targetFolderPath = str_replace('#BX_ROOT#', self::$documentRoot. ''. BX_ROOT, self::TARGET_FOLDERS[$type]);

					foreach ($this->lookThroughTmpFolder($sourceFolder->getPhysicalPath()) as $filePaths)
					{
						foreach ($filePaths as $langFilePath => $sourceFullPath)
						{
							$targetPath =
								$targetFolderPath .'/'.
								str_replace($moduleName. $typeSourcePath .'/', '', dirname($langFilePath));

							$targetFolder = new Main\IO\Directory($targetPath);
							if (!$targetFolder->isExists())
							{
								$targetFolder->create();
							}

							$moduleSourcePath = self::$documentRoot. ''. BX_ROOT. '/modules/'. $langFilePath;
							$source = new Main\IO\File($moduleSourcePath);
							if (!$source->isExists())
							{
								continue;
							}

							$target = new Main\IO\File($targetFolder->getPhysicalPath(). '/'. basename($langFilePath));
							if ($target->isExists())
							{
								$target->markWritable();
							}

							try
							{
								if (function_exists('error_clear_last'))
								{
									\error_clear_last();
								}
								if (\copy($source->getPhysicalPath(), $target->getPhysicalPath()) !== true)
								{
									$error = \error_get_last();
									$this->addError(new Main\Error($error['message'], $error['type']));
									continue;
								}

								$processedItemCount ++;
							}
							catch (Main\IO\IoException $exception)
							{
								$this->addError(new Main\Error($exception->getMessage()));
							}
						}
					}

					if ($this->instanceTimer()->hasTimeLimitReached())
					{
						$this->seekPath = $sourceFullPath;
						$this->seekModule = $moduleName;
						$this->seekType = $type;
						break 2;
					}
				}

				// check user abortion
				if (connection_status() !== CONNECTION_NORMAL)
				{
					throw new Main\SystemException('Process has been broken course user aborted connection.');
				}
			}
		}

		$this->processedItems += $processedItemCount;

		$result = array(
			'PROCESSED_ITEMS' => $this->processedItems,
			'TOTAL_ITEMS' => $this->totalItems,
		);

		if ($this->instanceTimer()->hasTimeLimitReached() !== true)
		{
			$this->declareAccomplishment();
			$this->clearProgressParameters();

			$result['SUMMARY'] = Loc::getMessage('TR_LANGUAGE_DOWNLOADED');
		}

		return $result;
	}


	/**
	 * Runs through tmp folder and copy files into lang folders.
	 *
	 * @param string $tmpFolderFullPath Full path of the temp folder to look through.
	 *
	 * @return \Generator|array
	 */
	private function lookThroughTmpFolder($tmpFolderFullPath)
	{
		$files = [];
		$folders = [];

		$tmpFolderFullPath = Translate\IO\Path::tidy(rtrim($tmpFolderFullPath, '/'));
		$langFolderRelPath = str_replace($this->sourceFolderPath, '', $tmpFolderFullPath);

		$childrenList = Translate\IO\FileSystemHelper::getFileList($tmpFolderFullPath);
		if (!empty($childrenList))
		{
			foreach ($childrenList as $fullPath)
			{
				if (!empty($this->seekPath))
				{
					if ($this->seekPath != $fullPath)
					{
						continue;
					}

					$this->seekPath = null;
					$this->seekAncestors = null;
				}

				$name = basename($fullPath);
				if (in_array($name, Translate\IGNORE_FS_NAMES))
				{
					continue;
				}

				if ((mb_substr($name, -4) === '.php') && is_file($fullPath))
				{
					$files[$langFolderRelPath.'/'.$name] = $fullPath;
				}
			}
		}

		// dir only
		$childrenList = Translate\IO\FileSystemHelper::getFolderList($tmpFolderFullPath);
		if (!empty($childrenList))
		{
			foreach ($childrenList as $fullPath)
			{
				$name = basename($fullPath);
				if (in_array($name, Translate\IGNORE_FS_NAMES))
				{
					continue;
				}

				if (!empty($this->seekPath))
				{
					if (in_array($fullPath, $this->seekAncestors))
					{
						foreach ($this->lookThroughTmpFolder($fullPath) as $subFiles)// go deeper
						{
							yield $subFiles;
						}
					}
					continue;
				}

				if (!is_dir($fullPath))
				{
					continue;
				}

				$relPath = $langFolderRelPath.'/'.$name;

				if (in_array($relPath, Translate\IGNORE_BX_NAMES))
				{
					continue;
				}

				$folders[$relPath] = $fullPath;
			}
		}

		if (count($files) > 0)
		{
			yield $files;
		}

		if (count($folders) > 0)
		{
			foreach ($folders as $subFolderPath)
			{
				foreach ($this->lookThroughTmpFolder($subFolderPath) as $subFiles)// go deeper
				{
					yield $subFiles;
				}
			}
		}
	}


	/**
	 * Returns progress option name
	 *
	 * @return string
	 */
	public function getProgressParameterOptionName()
	{
		$controller = $this->getController();
		return $controller::SETTING_ID;
	}
}