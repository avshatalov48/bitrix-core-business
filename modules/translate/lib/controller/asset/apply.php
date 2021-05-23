<?php
namespace Bitrix\Translate\Controller\Asset;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;


/**
 * Harvester of the lang folder disposition.
 */
class Apply
	extends Translate\Controller\Action
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	/** @var string */
	private $languageId;

	/** @var bool */
	private $convertEncoding;

	/** @var string */
	private $encoding;

	/** @var string */
	private $encodingIn;

	/** @var string */
	private $encodingOut;

	/** @var string */
	private $tmpFolderPath;

	/** @var string */
	private $targetFolderPath;

	/** @var string */
	private $sourceFolderPath;

	/** @var int */
	private $totalFileCount;

	/** @var boolean */
	public static $useTranslationRepository;
	/** @var string[] */
	public static $enabledLanguages;
	/** @var string[] */
	public static $allowedEncodings;
	/** @var string */
	public static $translationRepositoryRoot;


	/** @var string */
	private $seekPath;
	/** @var string[] */
	private $seekAncestors;


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
			'languageId', 'convertEncoding', 'encoding', 'encodingIn', 'encodingOut',
			'tmpFolderPath', 'totalFileCount', 'sourceFolderPath', 'targetFolderPath', 'seekPath'
		]);

		parent::__construct($name, $controller, $config);


		self::$useTranslationRepository = Main\Localization\Translation::useTranslationRepository();
		self::$enabledLanguages = Translate\Config::getEnabledLanguages();
		self::$allowedEncodings = Translate\Config::getAllowedEncodings();

		if (self::$useTranslationRepository)
		{
			self::$translationRepositoryRoot = Main\Localization\Translation::getTranslationRepositoryPath();
		}
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

			// convert encoding
			$this->convertEncoding = ($this->controller->getRequest()->get('localizeEncoding') === 'Y');

			//  encoding
			$encoding = $this->controller->getRequest()->get('encoding');
			if ($encoding !== null && in_array($encoding, self::$allowedEncodings))
			{
				$this->encoding = $encoding;
			}
			elseif ($this->convertEncoding)
			{
				$this->addError(new Main\Error(Loc::getMessage('TR_ERROR_ENCODING')));
			}

			if ($this->convertEncoding)
			{
				if (self::$useTranslationRepository)
				{
					$encodingIn = $this->encoding;
					$encodingOut = Main\Localization\Translation::getSourceEncoding($this->languageId);
				}
				elseif (Translate\Config::isUtfMode())
				{
					$encodingIn = $this->encoding;
					$encodingOut = 'utf-8';
				}
				else
				{
					$encodingIn = 'utf-8';
					$encodingOut = Translate\Config::getCultureEncoding($this->languageId);
					if (!$encodingOut)
					{
						$encodingOut = Main\Localization\Translation::getCurrentEncoding();
					}
				}
				$this->convertEncoding = (mb_strtolower($encodingIn) !== mb_strtolower($encodingOut));
				$this->encodingIn = $encodingIn;
				$this->encodingOut = $encodingOut;
			}

			$this->sourceFolderPath = Translate\IO\Path::tidy($this->tmpFolderPath .'/'. $this->languageId. '/');

			$sourceDirectory = new Translate\IO\Directory($this->sourceFolderPath);
			if (!$sourceDirectory->isExists())
			{
				$this->addError(
					new Error(Loc::getMessage('TR_ERROR_CREATE_TARGET_FOLDER', array('#PATH#' => $this->sourceFolderPath)))
				);
			}

			if (
				self::$useTranslationRepository &&
				Main\Localization\Translation::isDefaultTranslationLang($this->languageId) !== true
			)
			{
				$this->targetFolderPath = Translate\IO\Path::tidy(self::$translationRepositoryRoot. '/'. $this->languageId.'/');
				$targetFolder = new Translate\IO\Directory($this->targetFolderPath);
				if (!$targetFolder->isExists())
				{
					$targetFolder->create();
				}
			}
			else
			{
				$this->targetFolderPath = Main\Application::getDocumentRoot().'/bitrix/modules/';
			}

			$this->saveProgressParameters();

			return array(
				'STATUS' => ($this->totalItems > 0 ? Translate\Controller\STATUS_PROGRESS : Translate\Controller\STATUS_COMPLETED),
				'PROCESSED_ITEMS' => 0,
				'TOTAL_ITEMS' => $this->totalItems,
			);
		}

		$this->targetFolderPath = $progressParams['targetFolderPath'];
		$this->convertEncoding = $progressParams['convertEncoding'];
		$this->encodingIn = $progressParams['encodingIn'];
		$this->encodingOut = $progressParams['encodingOut'];
		$this->seekPath = $progressParams['seekPath'];

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

		foreach ($this->lookThroughTmpFolder($this->sourceFolderPath) as $filePaths)
		{
			foreach ($filePaths as $langFilePath => $fullPath)
			{
				$targetFolder = new Main\IO\Directory($this->targetFolderPath. dirname($langFilePath));
				if (!$targetFolder->isExists())
				{
					$targetFolder->create();
				}

				$source = new Main\IO\File($fullPath);
				$target = new Main\IO\File($targetFolder->getPhysicalPath(). '/'. basename($langFilePath));
				if ($target->isExists())
				{
					$target->markWritable();
				}

				try
				{
					if ($this->convertEncoding)
					{
						$content = $source->getContents();
						$content = str_replace(array("\r\n", "\r"), array("\n", "\n"), $content);

						$content = Main\Text\Encoding::convertEncoding($content, $this->encodingIn, $this->encodingOut);
						$target->putContents($content);
					}
					else
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
					}

					$processedItemCount ++;
				}
				catch (Main\IO\IoException $exception)
				{
					$this->addError(new Main\Error($exception->getMessage()));
				}

				// check user abortion
				if (connection_status() !== CONNECTION_NORMAL)
				{
					throw new Main\SystemException('Process has been broken course user aborted connection.');
				}
			}

			if ($this->instanceTimer()->hasTimeLimitReached())
			{
				$this->seekPath = $fullPath;
				break;
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

			$updatePublic = $this->controller->getRequest()->get('updatePublic');
			if ($updatePublic === 'Y')
			{
				// we have to continue process in next action
				$this->processToken = null;
			}
			else
			{
				$this->clearProgressParameters();
			}

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