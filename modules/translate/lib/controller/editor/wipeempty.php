<?php
namespace Bitrix\Translate\Controller\Editor;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Translate\Index;
use Bitrix\Main\Localization\Loc;

/**
 * Wipe empty parent folders.
 */
class WipeEmpty
	extends Translate\Controller\Editor\Operation
	implements Translate\Controller\ITimeLimit, Translate\Controller\IProcessParameters
{
	use Translate\Controller\Stepper;
	use Translate\Controller\ProcessParams;

	const SETTING_ID = 'WIPE_EMPTY';

	/** @var string[] */
	private $pathList;

	/** @var int */
	private $seekOffset;


	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, $config = array())
	{
		$this->keepField(['pathList', 'seekOffset']);

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
			$pathList = \preg_split("/[\r\n]+/", $pathList);
			\array_walk($pathList, 'trim');
			$pathList = \array_unique(\array_filter($pathList));

			if (empty($pathList))
			{
				$this->addError(new Main\Error(Loc::getMessage('TR_CLEAN_EMPTY_PATH_LIST')));

				return array(
					'STATUS' => Translate\Controller\STATUS_COMPLETED,
				);
			}

			foreach ($pathList as $testPath)
			{
				if (\mb_substr($testPath, -4) === '.php')
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
							'=PATH' => \rtrim($testPath, '/'),
							'=%PATH' => \rtrim($testPath, '/'). '/%'
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

			$this->totalItems = \count($this->pathList);
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

		return $this->performStep('runWiping');
	}

	/**
	 * Collects lang folder paths.
	 *
	 * @return array
	 */
	private function runWiping()
	{
		$processedItemCount = 0;
		for ($pos = ((int)$this->seekOffset > 0 ? (int)$this->seekOffset : 0), $total = count($this->pathList); $pos < $total; $pos ++)
		{
			$testPath = $this->pathList[$pos];

			$isOk = true;

			// file
			if (\mb_substr($testPath, -4) === '.php')
			{
				$testPath = Translate\IO\Path::replaceLangId($testPath, '#LANG_ID#');

				foreach (self::$enabledLanguagesList as $langId)
				{
					$langRelPath = Translate\IO\Path::replaceLangId($testPath, $langId);
					$langFullPath = Translate\IO\Path::tidy(self::$documentRoot.'/'.$langRelPath);
					$langFullPath = Main\Localization\Translation::convertLangPath($langFullPath, $langId);

					if ($this->removeEmptyParents($langFullPath))
					{
						Translate\Index\Internals\FileIndexTable::purge(new Translate\Filter(['path' => $testPath, 'langId' => $langId]));
					}
					else
					{
						$isOk = false;
					}
				}
			}

			// folder
			else
			{
				if (\mb_substr($testPath, -5) === '/lang')
				{
					$testPath .= '/#LANG_ID#';
				}
				else
				{
					$testPath = Translate\IO\Path::replaceLangId($testPath, '#LANG_ID#');
				}

				foreach (self::$enabledLanguagesList as $langId)
				{
					$langRelPath = Translate\IO\Path::replaceLangId($testPath. '/.nonExistentTestFile.php', $langId);
					$langFullPath = Translate\IO\Path::tidy(self::$documentRoot.'/'.$langRelPath);
					$langFullPath = Main\Localization\Translation::convertLangPath($langFullPath, $langId);

					if ($this->removeEmptyParents($langFullPath))
					{
						Translate\Index\Internals\FileIndexTable::purge(new Translate\Filter(['path' => $testPath, 'langId' => $langId]));
					}
					else
					{
						$isOk = false;
					}
				}
			}

			if ($isOk)
			{
				Translate\Index\Internals\PathIndexTable::purge(new Translate\Filter(['path' => $testPath]), false);
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
	 * Performs wiping empty lang folders.
	 *
	 * @param string $langFullPath Full path to lang file.
	 *
	 * @return bool
	 */
	private function removeEmptyParents($langFullPath)
	{
		try
		{
			$langFile = Translate\File::instantiateByPath($langFullPath);
			if ($langFile instanceof Translate\File)
			{
				return $langFile->removeEmptyParents();
			}
		}
		catch (Main\ArgumentException $ex)
		{
		}

		return false;
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