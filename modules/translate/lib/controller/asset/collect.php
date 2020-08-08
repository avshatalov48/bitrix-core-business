<?php
namespace Bitrix\Translate\Controller\Asset;

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;
use Bitrix\Translate\Index;


/**
 * Harvester of the lang folder disposition.
 */
class Collect
	extends Translate\Controller\Action
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	/** @var string */
	private $seekPathLangId;

	/** @var string */
	private $languageId;

	/** @var bool */
	private $convertEncoding;

	/** @var string */
	private $encoding;

	/** @var string */
	private $assemblyDate;

	/** @var bool */
	private $packFile;

	/** @var string */
	private $tmpFolderPath;

	/** @var int */
	private $totalFileCount;

	/** @var string */
	public static $documentRoot;
	/** @var boolean */
	public static $useTranslationRepository;
	/** @var string[] */
	public static $enabledLanguages;
	/** @var string[] */
	public static $allowedEncodings;
	/** @var string[] */
	public static $translationRepositoryLanguages;


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
			'seekPathLangId', 'convertEncoding', 'encoding', 'assemblyDate',
			'languageId', 'packFile', 'tmpFolderPath', 'totalFileCount'
		]);

		parent::__construct($name, $controller, $config);

		self::$useTranslationRepository = Main\Localization\Translation::useTranslationRepository();
		self::$enabledLanguages = Translate\Config::getEnabledLanguages();
		self::$allowedEncodings = Translate\Config::getAllowedEncodings();
		self::$documentRoot = rtrim(Translate\IO\Path::tidy(Main\Application::getDocumentRoot()), '/');

		if (self::$useTranslationRepository)
		{
			self::$translationRepositoryLanguages = Translate\Config::getTranslationRepositoryLanguages();
		}
	}

	/**
	 * Runs controller action.
	 *
	 * @param string $path Stating path.
	 * @param boolean $runBefore Flag to run onBeforeRun event handler.
	 * @return array
	 */
	public function run($path = '', $runBefore = false)
	{
		if ($runBefore)
		{
			$this->onBeforeRun();
		}

		if (empty($path))
		{
			$path = Grabber::START_PATH;
		}

		$path = '/'. trim($path, '/.\\');

		if ($this->isNewProcess)
		{
			$this->clearProgressParameters();
			$this->totalItems = 0;
			$this->processedItems = 0;
			$this->totalFileCount = 0;

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
			$this->convertEncoding = ($this->controller->getRequest()->get('convertEncoding') === 'Y');

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
					$encodingIn = Main\Localization\Translation::getSourceEncoding($this->languageId);
					$encodingOut = $this->encoding;
					if ($encodingIn === 'utf-8' && $encodingOut !== 'utf-8')
					{
						$this->addError(new Error(Loc::getMessage('TR_ERROR_LANGUAGE_CHARSET_NON_UTF')));
					}
				}
				elseif (Translate\Config::isUtfMode())
				{
					$encodingIn = 'utf-8';
					$encodingOut = $this->encoding;
					if (Translate\Config::getCultureEncoding($this->languageId) !== 'utf-8')
					{
						$this->addError(new Error(Loc::getMessage('TR_ERROR_LANGUAGE_CHARSET_NON_UTF')));
					}
				}
				else
				{
					$encodingIn = Translate\Config::getCultureEncoding($this->languageId);
					if (!$encodingIn)
					{
						$encodingIn = Main\Localization\Translation::getCurrentEncoding();
					}
					$this->encoding = $encodingOut = 'utf-8';
				}

				$this->convertEncoding = (mb_strtolower($encodingIn) !== mb_strtolower($encodingOut));
			}

			// assembly date
			$assemblyDate = $this->controller->getRequest()->get('assemblyDate');
			if ($assemblyDate !== null && preg_replace("/[\D]+/", "", $assemblyDate) && mb_strlen($assemblyDate) == 8)
			{
				$this->assemblyDate = $assemblyDate;
			}
			else
			{
				$this->addError(new Main\Error(Loc::getMessage('TR_ERROR_LANGUAGE_DATE')));
			}


			// pack
			$this->packFile = ($this->controller->getRequest()->get('packFile') === 'Y');


			if (!$this->hasErrors())
			{
				$exportFolder = Translate\Config::getExportFolder();
				if (!empty($exportFolder))
				{
					$tempDir = new Translate\IO\Directory($exportFolder.'/'.$this->languageId);
					if ($tempDir->isExists())
					{
						$tempDir->wipe();
					}
					else
					{
						$tempDir->create();
					}
				}
				else
				{
					$tempDir = Translate\IO\Directory::generateTemporalDirectory('translate');
					$tempDir = $tempDir->createSubdirectory($this->languageId);
				}
				$this->tmpFolderPath = $tempDir->getPhysicalPath(). '/';
				if (!$tempDir->isExists())
				{
					$this->addError(
						new Error(Loc::getMessage('TR_ERROR_CREATE_TEMP_FOLDER', array('#PATH#' => $this->tmpFolderPath)))
					);
				}
			}

			if (!$this->hasErrors())
			{
				$fileDateMarkFullPath = $this->tmpFolderPath.
										str_replace('#LANG_ID#', $this->languageId, Translate\SUPD_LANG_DATE_MARK);

				Translate\IO\Path::checkCreatePath(dirname($fileDateMarkFullPath));

				$fileDateMark = new Main\IO\File($fileDateMarkFullPath);
				if ($fileDateMark->putContents($assemblyDate) === false)
				{
					$this->addError(
						new Error(Loc::getMessage('TR_ERROR_OPEN_FILE', array('#FILE#' => $fileDateMarkFullPath)))
					);
				}
			}

			$this->totalItems = (int)Index\Internals\PathLangTable::getCount(array('=%PATH' => $path.'%'));
			$this->processedItems = 0;

			$this->saveProgressParameters();

			return array(
				'STATUS' => ($this->totalItems > 0 ? Translate\Controller\STATUS_PROGRESS : Translate\Controller\STATUS_COMPLETED),
				'PROCESSED_ITEMS' => 0,
				'TOTAL_ITEMS' => $this->totalItems,
			);
		}

		$progressParams = $this->getProgressParameters();

		$this->languageId = $progressParams['languageId'];
		$this->convertEncoding = $progressParams['convertEncoding'];
		$this->encoding = $progressParams['encoding'];
		$this->assemblyDate = $progressParams['assemblyDate'];
		$this->packFile = $progressParams['packFile'];
		$this->tmpFolderPath = $progressParams['tmpFolderPath'];

		if (isset($progressParams['totalItems']) && (int)$progressParams['totalItems'] > 0)
		{
			$this->totalItems = (int)$progressParams['totalItems'];
			$this->processedItems = (int)$progressParams['processedItems'];
			$this->totalFileCount = (int)$progressParams['totalFileCount'];
		}

		if (isset($progressParams['seekPathLangId']))
		{
			$this->seekPathLangId = $progressParams['seekPathLangId'];
		}

		return $this->performStep('runCollecting', array('path' => $path));
	}


	/**
	 * Copying lang files.
	 *
	 * @param array $params Parameters.
	 * @return array
	 */
	private function runCollecting(array $params = [])
	{
		if ($this->convertEncoding)
		{
			$sourceEncoding = Main\Localization\Translation::getSourceEncoding($this->languageId);
		}

		if (isset($params['path']))
		{
			$path = $params['path'];
		}
		else
		{
			$path = Grabber::START_PATH;
		}

		$pathFilter = array(
			'=%PATH' => $path.'%'
		);
		if (!empty($this->seekPathLangId))
		{
			$pathFilter['>ID'] = $this->seekPathLangId;
		}

		$cachePathLangRes = Index\Internals\PathLangTable::getList(array(
			'filter' => $pathFilter,
			'order' => array('ID' => 'ASC'),
			'select' => ['ID', 'PATH'],
		));
		$processedItemCount = 0;
		while ($pathLang = $cachePathLangRes->fetch())
		{
			foreach ($this->lookThroughLangFolder($pathLang['PATH']. '/'.$this->languageId) as $filePaths)
			{
				foreach ($filePaths as $langFilePath => $fullPath)
				{
					$targetFolder = new Main\IO\Directory($this->tmpFolderPath. dirname($langFilePath));
					if (!$targetFolder->isExists())
					{
						$targetFolder->create();
					}

					$source = new Main\IO\File($fullPath);
					$target = new Main\IO\File($targetFolder->getPhysicalPath(). '/'. basename($langFilePath));

					try
					{
						$content = $source->getContents();
						$content = str_replace(array("\r\n", "\r"), array("\n", "\n"), $content);

						if ($this->convertEncoding)
						{
							$errorMessage = '';
							$content = Main\Text\Encoding::convertEncoding($content, $sourceEncoding, $this->encoding, $errorMessage);
							if (!$content && !empty($errorMessage))
							{
								$this->addError(new Main\Error($errorMessage));
							}
						}

						$target->putContents($content);
						$this->totalFileCount ++;
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
			}

			$processedItemCount ++;

			if ($this->instanceTimer()->hasTimeLimitReached())
			{
				$this->seekPathLangId = (int)$pathLang['ID'];
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

			// we have to continue process in next action
			$this->processToken = null;
			$this->seekPathLangId = null;
			$this->saveProgressParameters();

			$messagePlaceholders = array(
				'#TOTAL_FILES#' => $this->totalFileCount,
				'#LANG#' => mb_strtoupper($this->languageId),
				'#PATH#' => '~tmp~',
			);
			$result['SUMMARY'] = Loc::getMessage('TR_LANGUAGE_COLLECTED_FOLDER', $messagePlaceholders);
		}

		return $result;
	}


	/**
	 * Runs through lang folder and collects full path to lang files.
	 *
	 * @param string $langFolderRelPath Relative project path of the language folder.
	 *
	 * @return \Generator|array
	 */
	private function lookThroughLangFolder($langFolderRelPath)
	{
		$files = [];
		$folders = [];

		$langFolderFullPath = Translate\IO\Path::tidy(self::$documentRoot.'/'.$langFolderRelPath);

		$storeFolderRelPath = str_replace(Grabber::START_PATH, '', $langFolderRelPath);

		if (self::$useTranslationRepository && in_array($this->languageId, self::$translationRepositoryLanguages))
		{
			$langFolderFullPath = Main\Localization\Translation::convertLangPath($langFolderFullPath, $this->languageId);
		}

		$childrenList = Translate\IO\FileSystemHelper::getFileList($langFolderFullPath);
		if (!empty($childrenList))
		{
			foreach ($childrenList as $fullPath)
			{
				$name = basename($fullPath);
				if (in_array($name, Translate\IGNORE_FS_NAMES))
				{
					continue;
				}

				if ((mb_substr($name, -4) === '.php') && is_file($fullPath))
				{
					$files[$storeFolderRelPath.'/'.$name] = $fullPath;
				}
			}
		}

		// dir only
		$childrenList = Translate\IO\FileSystemHelper::getFolderList($langFolderFullPath);
		if (!empty($childrenList))
		{
			$ignoreDev = implode('|', Translate\IGNORE_MODULE_NAMES);
			foreach ($childrenList as $fullPath)
			{
				$name = basename($fullPath);
				if (in_array($name, Translate\IGNORE_FS_NAMES))
				{
					continue;
				}

				$relPath = $langFolderRelPath.'/'.$name;

				if (!is_dir($fullPath))
				{
					continue;
				}

				if (in_array($relPath, Translate\IGNORE_BX_NAMES))
				{
					continue;
				}

				// /bitrix/modules/[smth]/dev/
				if (preg_match("#^bitrix/modules/[^/]+/({$ignoreDev})#", trim($relPath, '/')))
				{
					continue;
				}

				if (in_array($name, Translate\IGNORE_LANG_NAMES))
				{
					continue;
				}

				$folders[$langFolderRelPath.'/'.$name] = $langFolderRelPath.'/'.$name;
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
				foreach ($this->lookThroughLangFolder($subFolderPath) as $subFiles)// go deeper
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