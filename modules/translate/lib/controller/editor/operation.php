<?php
namespace Bitrix\Translate\Controller\Editor;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Translate\Index;
use Bitrix\Main\Localization\Loc;

/**
 * Common operation with language file.
 */
class Operation
	extends Translate\Controller\Action
{
	/** @var string[] */
	static $enabledLanguagesList;
	/** @var string */
	static $documentRoot;

	/**
	 * \Bitrix\Main\Engine\Action constructor.
	 *
	 * @param string $name Action name.
	 * @param Main\Engine\Controller $controller Parent controller object.
	 * @param array $config Additional configuration.
	 */
	public function __construct($name, Main\Engine\Controller $controller, $config = array())
	{
		self::$enabledLanguagesList = Translate\Config::getEnabledLanguages();
		self::$documentRoot = rtrim(Translate\IO\Path::tidy(Main\Application::getDocumentRoot()), '/');

		parent::__construct($name, $controller, $config);
	}

	/**
	 * Saves changed lang file.
	 *
	 * @param Translate\File $langFile File to update.
	 *
	 * @return bool
	 */
	protected function updateLangFile(Translate\File $langFile)
	{
		// backup
		if ($langFile->isExists() && Translate\Config::needToBackUpFiles())
		{
			if (!$langFile->backup())
			{
				$this->addError(new Main\Error(
					Loc::getMessage('TR_CREATE_BACKUP_ERROR', ['#FILE#' => $langFile->getPath()])
				));
			}
		}

		// sort phrases by key
		if (Translate\Config::needToSortPhrases())
		{
			if (in_array($langFile->getLangId(), Translate\Config::getNonSortPhraseLanguages()) === false)
			{
				$langFile->sortPhrases();
			}
		}

		try
		{
			if (!$langFile->save())
			{
				if ($langFile->hasErrors())
				{
					$this->addErrors($langFile->getErrors());
				}

				return false;
			}
		}
		catch (Main\IO\IoException $exception)
		{
			if (!$langFile->isExists())
			{
				$this->addError(new Main\Error(
					Loc::getMessage('TR_ERROR_WRITE_CREATE', ['#FILE#' => $langFile->getPath()])
				));
			}
			else
			{
				$this->addError(new Main\Error(
					Loc::getMessage('TR_ERROR_WRITE_UPDATE', ['#FILE#' => $langFile->getPath()])
				));
			}

			return false;
		}

		return true;
	}

	/**
	 * Delete lang file.
	 *
	 * @param Translate\File $langFile File to update.
	 *
	 * @return bool
	 */
	protected function deleteLangFile(Translate\File $langFile)
	{
		// backup
		if ($langFile->isExists() && Translate\Config::needToBackUpFiles())
		{
			if (!$langFile->backup())
			{
				$this->addError(new Main\Error(
					Loc::getMessage('TR_CREATE_BACKUP_ERROR', ['#FILE#' => $langFile->getPath()])
				));
			}
		}

		try
		{
			if (!$langFile->delete())
			{
				if ($langFile->hasErrors())
				{
					$this->addErrors($langFile->getErrors());
				}
				else
				{
					$this->addError(new Main\Error(
						Loc::getMessage('TR_ERROR_DELETE', ['#FILE#' => $langFile->getPath()])
					));
				}

				return false;
			}
		}
		catch (Main\IO\IoException $exception)
		{
			$this->addError(new Main\Error(
				Loc::getMessage('TR_ERROR_DELETE', ['#FILE#' => $langFile->getPath()])
			));

			return false;
		}

		return true;
	}


	/**
	 * Updates phrase index.
	 *
	 * @param Translate\File $langFile File to update index of.
	 *
	 * @return bool
	 */
	protected function updatePhraseIndex(Translate\File $langFile)
	{
		$langFile->updatePhraseIndex();

		return true;
	}

	/**
	 * Drops phrase index.
	 *
	 * @param Translate\File $langFile File to update index of.
	 *
	 * @return bool
	 */
	protected function deletePhraseIndex(Translate\File $langFile)
	{
		$langFile->deletePhraseIndex();

		return true;
	}

	/**
	 * Runs through lang folder and collects full path to lang files.
	 *
	 * @param string $langPath Relative project path of the language folder.
	 *
	 * @return \Generator|array
	 */
	protected function lookThroughLangFolder($langPath)
	{
		$files = [];
		$folders = [];

		foreach (self::$enabledLanguagesList as $langId)
		{
			$langFolderRelPath = Translate\IO\Path::replaceLangId($langPath, $langId);
			$langFolderFullPath = Translate\IO\Path::tidy(self::$documentRoot.'/'.$langFolderRelPath);
			$langFolderFullPath = Main\Localization\Translation::convertLangPath($langFolderFullPath, $langId);

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
						$files[$langPath.'/'.$name][$langId] = $fullPath;
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
					if (preg_match("#^bitrix/modules/[^/]+/({$ignoreDev})$#", trim($relPath, '/')))
					{
						continue;
					}

					if (in_array($name, Translate\IGNORE_LANG_NAMES))
					{
						continue;
					}

					$folders[$langPath.'/'.$name] = $langPath.'/'.$name;
				}
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
}