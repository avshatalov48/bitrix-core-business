<?php
namespace Bitrix\Translate\Controller\Editor;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Translate\Index;
use Bitrix\Main\Localization\Loc;

/**
 * Remove phrases by the ethalon language file.
 */
class CleanEthalon
	extends Translate\Controller\Editor\Operation
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	const SETTING_ID = 'TRANSLATE_CLEAN';

	/** @var string[] */
	private $pathList;

	/** @var int */
	private $seekOffset;

	/** @var string */
	private $seekLangPath;


	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, $config = array())
	{
		$this->keepField(['pathList', 'seekOffset', 'seekLangPath']);

		parent::__construct($name, $controller, $config);
	}


	/**
	 * Runs controller action.
	 *
	 * @param string[] $pathList Path list to update.
	 *
	 * @return array
	 */
	public function run($pathList)
	{
		if ($this->isNewProcess)
		{
			$pathList = preg_split("/[\r\n]+/", $pathList);
			array_walk($pathList, 'trim');
			$pathList = array_unique(array_filter($pathList));

			if (empty($pathList))
			{
				$this->addError(new Main\Error(Loc::getMessage('TR_CLEAN_EMPTY_PATH_LIST')));

				return array(
					'STATUS' => Translate\Controller\STATUS_COMPLETED,
				);
			}

			foreach ($pathList as $testPath)
			{
				if (mb_substr($testPath, -4) === '.php')
				{
					if (Translate\IO\Path::isLangDir($testPath))
					{
						$this->pathList[] = $testPath;
					}
					else
					{
						$this->addError(new Main\Error(Loc::getMessage('TR_CLEAN_FILE_NOT_LANG', array('#FILE#' => $testPath))));
					}
				}
				else
				{
					if (Translate\IO\Path::isLangDir($testPath))
					{
						$this->pathList[] = $testPath;
					}
					else
					{
						// load lang folders
						$pathFilter = array();
						$pathFilter[] = array(
							'LOGIC' => 'OR',
							'=PATH' => rtrim($testPath, '/'),
							'=%PATH' => rtrim($testPath, '/'). '/%'
						);
						$pathLangRes = Index\Internals\PathLangTable::getList(array(
							'filter' => $pathFilter,
							'order' => array('ID' => 'ASC'),
							'select' => ['PATH'],
						));
						while ($pathLang = $pathLangRes->fetch())
						{
							$this->pathList[] = $pathLang['PATH'];
						}
					}
				}
			}

			$this->totalItems = count($this->pathList);
			$this->processedItems = 0;

			if ($this->totalItems == 0)
			{
				return array(
					'STATUS' => Translate\Controller\STATUS_COMPLETED,
					'PROCESSED_ITEMS' => 0,
					'TOTAL_ITEMS' => 0,
				);
			}

			$this->saveProgressParameters();
			$this->isNewProcess = false;
		}

		return $this->performStep('runClearing');
	}

	/**
	 * Collects lang folder paths.
	 *
	 * @return array
	 */
	private function runClearing()
	{
		$processedItemCount = 0;
		for ($pos = ((int)$this->seekOffset > 0 ? (int)$this->seekOffset : 0), $total = count($this->pathList); $pos < $total; $pos ++)
		{
			$testPath = $this->pathList[$pos];

			// file
			if (mb_substr($testPath, -4) === '.php')
			{
				$this->cleanLangFile($testPath);
			}

			// folder
			else
			{
				if (mb_substr($testPath, -5) === '/lang')
				{
					$testPath .= '/#LANG_ID#';
				}
				else
				{
					$testPath = Translate\IO\Path::replaceLangId($testPath, '#LANG_ID#');
				}

				foreach ($this->lookThroughLangFolder($testPath) as $filePaths)
				{
					foreach ($filePaths as $langFilePath => $fullPaths)
					{
						if (!empty($this->seekLangPath))
						{
							if ($this->seekLangPath == $langFilePath)
							{
								$this->seekLangPath = null;
							}

							continue;
						}

						$this->cleanLangFile($langFilePath);

						if ($this->instanceTimer()->hasTimeLimitReached())
						{
							$this->seekLangPath = $langFilePath;
							break 3;
						}
					}

					$this->seekLangPath = null;
				}
			}

			$processedItemCount ++;

			if (isset($this->pathList[$pos + 1]))
			{
				$this->seekOffset = $pos + 1;//next
			}
			else
			{
				$this->seekOffset = null;
				$this->declareAccomplishment();
				$this->clearProgressParameters();
			}

			if ($this->instanceTimer()->hasTimeLimitReached())
			{
				break;
			}
		}

		$this->processedItems += $processedItemCount;

		if ($this->instanceTimer()->hasTimeLimitReached() !== true)
		{
			$this->declareAccomplishment();
			$this->clearProgressParameters();
		}

		return array(
			'PROCESSED_ITEMS' => $this->processedItems,
			'TOTAL_ITEMS' => $this->totalItems,
		);
	}


	/**
	 * Performs changed lang file.
	 *
	 * @param string $relLangPath Relative path to lang file.
	 *
	 * @return void
	 */
	private function cleanLangFile($relLangPath)
	{
		$currentLang = Loc::getCurrentLang();
		$langPath = Translate\IO\Path::replaceLangId($relLangPath, $currentLang);
		$langFullPath = Translate\IO\Path::tidy(self::$documentRoot. '/'. $langPath);
		$langFullPath = Main\Localization\Translation::convertLangPath($langFullPath, $currentLang);

		try
		{
			$ethalonFile = Translate\File::instantiateByPath($langFullPath);
		}
		catch (Main\ArgumentException $ex)
		{
			return;
		}
		if ($ethalonFile instanceof Translate\File)
		{
			$ethalonFile
				->setLangId($currentLang)
				->setOperatingEncoding(Main\Localization\Translation::getSourceEncoding($currentLang));

			$isEthalonExists = false;
			if ($ethalonFile->isExists())
			{
				$isEthalonExists = $ethalonFile->loadTokens() || $ethalonFile->load();
			}
			if (!$isEthalonExists)
			{
				$this->deletePhraseIndex($ethalonFile);
			}

			foreach (self::$enabledLanguagesList as $langId)
			{
				if ($langId == $currentLang)
				{
					continue;
				}

				$langPath = Translate\IO\Path::replaceLangId($relLangPath, $langId);
				$langFullPath = Translate\IO\Path::tidy(self::$documentRoot.'/'.$langPath);
				$langFullPath = Main\Localization\Translation::convertLangPath($langFullPath, $langId);

				try
				{
					$langFile = Translate\File::instantiateByPath($langFullPath);
				}
				catch (Main\ArgumentException $ex)
				{
					continue;
				}
				if ($langFile instanceof Translate\File)
				{
					$langFile
						->setLangId($langId)
						->setOperatingEncoding(Main\Localization\Translation::getSourceEncoding($langId));

					if ($langFile->isExists())
					{
						if ($isEthalonExists && ($langFile->loadTokens() || $langFile->load()))
						{
							$affected = false;
							foreach ($langFile as $code => $phrase)
							{
								if (!isset($ethalonFile[$code]))
								{
									unset($langFile[$code]);
									$affected = true;
								}
							}
							if ($affected)
							{
								$this->updateLangFile($langFile);
								$this->updatePhraseIndex($langFile);
							}
						}
						else
						{
							$this->deletePhraseIndex($langFile);
							$this->deleteLangFile($langFile);
						}
					}
					else
					{
						$langFile->deletePhraseIndex();
					}
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
		return self::SETTING_ID;
	}
}