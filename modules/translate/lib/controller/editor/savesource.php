<?php
namespace Bitrix\Translate\Controller\Editor;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Main\Localization\Loc;

/**
 * Update/create language file by php source.
 */
class SaveSource
	extends Operation
{
	/**
	 * Runs controller action.
	 *
	 * @param string $file Path to update.
	 *
	 * @return array
	 */
	public function run($file = '')
	{
		Loc::loadLanguageFile(__DIR__. '/operation.php');
		Loc::loadLanguageFile(__FILE__);

		$result = array();
		if (empty($file))
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_EDIT_FILE_PATH_ERROR')));
			return $result;
		}
		if (!Translate\IO\Path::isLangDir($file, true) || (mb_substr($file, -4) !== '.php'))
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_EDIT_ERROR_FILE_NOT_LANG', array('#FILE#' => $file))));
			return $result;
		}
		if (!Translate\Permission::isAllowPath($file))
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_EDIT_FILE_WRONG_NAME')));
			return $result;
		}

		$request = $this->controller->getRequest();

		$languagesToUpdate = array();

		$enabledLanguagesList = Translate\Config::getEnabledLanguages();

		// languages to update
		$languagesToUpdateTmp = $request->getPost('LANGS');
		if ($languagesToUpdateTmp !== null && is_array($languagesToUpdateTmp) && count($languagesToUpdateTmp) > 0)
		{
			$languagesToUpdate = array_intersect($languagesToUpdateTmp, $enabledLanguagesList);
		}
		unset($languagesToUpdateTmp);

		$currentEncoding = Main\Localization\Translation::getCurrentEncoding();
		$documentRoot = rtrim(Translate\IO\Path::tidy(Main\Application::getDocumentRoot()), '/');

		foreach ($enabledLanguagesList as $langId)
		{
			if (!in_array($langId, $languagesToUpdate))
			{
				continue;
			}

			$langRelPath = Translate\IO\Path::replaceLangId($file, $langId);
			$fullPath = Translate\IO\Path::tidy($documentRoot.'/'.$langRelPath);
			$fullPath = Main\Localization\Translation::convertLangPath($fullPath, $langId);

			$langFile = new Translate\File($fullPath);
			$langFile
				->setLangId($langId)
				->setOperatingEncoding($currentEncoding);

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

			$fileSrcForSave = $request->getPost('SRC_'. $langId);

			if (empty($fileSrcForSave) || !is_string($fileSrcForSave))
			{
				$this->addError(new Main\Error(Loc::getMessage('TR_EDIT_PARAM_ERROR')));
				continue;
			}

			if (!$langFile->lint($fileSrcForSave))
			{
				$this->addErrors($langFile->getErrors());
				continue;
			}

			try
			{
				if (!$langFile->putContents($fileSrcForSave))
				{
					if ($langFile->hasErrors())
					{
						$this->addErrors($langFile->getErrors());
					}
				}
				else
				{
					// check
					if (!$langFile->load() && $langFile->hasErrors())
					{
						$this->addErrors($langFile->getErrors());
					}
					elseif ($langFile->count() > 0)
					{
						$langFile->updatePhraseIndex();
					}
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
			}
		}

		if (!$this->hasErrors())
		{
			$result['SUMMARY'] = Loc::getMessage('TR_EDIT_SAVING_COMPLETED');
		}

		return $result;
	}
}