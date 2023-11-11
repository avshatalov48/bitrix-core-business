<?php
namespace Bitrix\Translate\Controller\Editor;

use Bitrix\Main;
use Bitrix\Translate;
use Bitrix\Main\Localization\Loc;

/**
 * Update/create language file.
 */
class SaveFile
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

		$result = [];
		if (empty($file))
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_EDIT_FILE_PATH_ERROR')));
			return $result;
		}
		$normalized = Main\IO\Path::normalize($file);
		if ($normalized != $file)
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_EDIT_FILE_WRONG_NAME')));
			return $result;
		}
		$file = $normalized;
		if (!Translate\IO\Path::isLangDir($file, true) || !Translate\IO\Path::isPhpFile($file))
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_EDIT_ERROR_FILE_NOT_LANG', ['#FILE#' => $file])));
			return $result;
		}
		if (!Translate\Permission::isAllowPath($file))
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_EDIT_FILE_WRONG_NAME')));
			return $result;
		}

		$request = $this->controller->getRequest();

		$phraseIdsToDrop = $languagesToDrop = $phraseIdsToUpdate = $languagesToUpdate = [];

		$enabledLanguagesList = Translate\Config::getEnabledLanguages();

		$currentEncoding = Main\Localization\Translation::getCurrentEncoding();
		$currentLang = Loc::getCurrentLang();
		$limitEncoding = !($currentEncoding === 'utf-8' || Main\Localization\Translation::useTranslationRepository());

		$isEncodingCompatible = function ($langId) use ($limitEncoding, $currentEncoding, $currentLang)
		{
			$compatible = true;
			if ($limitEncoding)
			{
				$compatible = (
					$langId == $currentLang ||
					Translate\Config::getCultureEncoding($langId) == $currentEncoding ||
					$langId === 'en'
				);
			}

			return $compatible;
		};

		// codes to drop
		$phraseIdsToDropTmp = $request->getPost('DROP');
		if ($phraseIdsToDropTmp !== null && \is_array($phraseIdsToDropTmp) && \count($phraseIdsToDropTmp) > 0)
		{
			$phraseIdsToDrop = $phraseIdsToDropTmp;
			$languagesToDrop = $enabledLanguagesList;
		}
		unset($phraseIdsToDropTmp);

		// codes to update
		$phraseIdsToUpdateTmp = $request->getPost('KEYS');
		if ($phraseIdsToUpdateTmp !== null && \is_array($phraseIdsToUpdateTmp) && \count($phraseIdsToUpdateTmp) > 0)
		{
			$phraseIdsToUpdate = $phraseIdsToUpdateTmp;
		}
		unset($phraseIdsToUpdateTmp);

		// languages to update
		$languagesToUpdateTmp = $request->getPost('LANGS');
		if ($languagesToUpdateTmp !== null && \is_array($languagesToUpdateTmp) && \count($languagesToUpdateTmp) > 0)
		{
			$languagesToUpdate = \array_intersect($languagesToUpdateTmp, $enabledLanguagesList);
		}
		unset($languagesToUpdateTmp);

		// check
		if (empty($phraseIdsToUpdate) && empty($phraseIdsToDrop))
		{
			$result['SUMMARY'] = Loc::getMessage('TR_EDIT_SAVING_COMPLETED');
			return $result;
		}
		if (!empty($phraseIdsToUpdate) && empty($languagesToUpdate))
		{
			$this->addError(new Main\Error(Loc::getMessage('TR_EDIT_PARAM_ERROR')));
			return $result;
		}

		$documentRoot = rtrim(Translate\IO\Path::tidy(Main\Application::getDocumentRoot()), '/');

		$result['DROPPED'] = [];
		$result['UPDATED'] = [];
		$result['CLEANED'] = [];

		foreach ($enabledLanguagesList as $langId)
		{
			if (!\in_array($langId, $languagesToUpdate) && !\in_array($langId, $languagesToDrop))
			{
				continue;
			}

			$langRelPath = Translate\IO\Path::replaceLangId($file, $langId);
			$fullPath = Translate\IO\Path::tidy($documentRoot. '/'. $langRelPath);
			$fullPath = Main\Localization\Translation::convertLangPath($fullPath, $langId);

			$langFile = new Translate\File($fullPath);
			$langFile->setLangId($langId);


			// update and drop
			if (\in_array($langId, $languagesToUpdate))
			{
				$langFile->setOperatingEncoding($currentEncoding);
			}
			// just drop
			elseif (\in_array($langId, $languagesToDrop))
			{
				$langFile->setOperatingEncoding(Main\Localization\Translation::getSourceEncoding($langId));
			}

			if (!$langFile->loadTokens())
			{
				if (!$langFile->load() && $langFile->hasErrors())
				{
					foreach ($langFile->getErrors() as $error)
					{
						if ($error->getCode() !== 'EMPTY_CONTENT')
						{
							$this->addError($error);
						}
					}
				}
			}
			if (\count($this->getErrors()) > 0)
			{
				continue;
			}

			$hasDataToUpdate = false;

			// drop phrases
			if (\in_array($langId, $languagesToDrop))
			{
				foreach ($phraseIdsToDrop as $phraseId)
				{
					if (isset($langFile[$phraseId]))
					{
						unset($langFile[$phraseId]);

						$hasDataToUpdate = true;
						if (!\in_array($phraseId, $result['DROPPED']))
						{
							$result['DROPPED'][] = $phraseId;
						}
					}
				}
			}

			// set phrases
			if (\in_array($langId, $languagesToUpdate) && $isEncodingCompatible($langId))
			{
				foreach ($phraseIdsToUpdate as $phraseId)
				{
					// has been deleted
					if (\in_array($phraseId, $phraseIdsToDrop))
					{
						continue;
					}

					$fldName = $this->generateFieldName($phraseId, $langId);
					if (!isset($request[$fldName]))
					{
						continue;
					}
					$inpValue = $request->getPost($fldName);

					/** @var \ArrayAccess $langFile */
					if (!empty($inpValue) || $inpValue === '0')
					{
						if ($langFile[$phraseId] !== $inpValue)
						{
							$langFile[$phraseId] = $inpValue;

							$hasDataToUpdate = true;
							if (!\in_array($fldName, $result['UPDATED']))
							{
								$result['UPDATED'][] = $fldName;
							}
						}
					}
					// remove empty
					elseif (isset($langFile[$phraseId]) && $inpValue === '')
					{
						unset($langFile[$phraseId]);

						$hasDataToUpdate = true;
						if (!\in_array($fldName, $result['CLEANED']))
						{
							$result['CLEANED'][] = $fldName;
						}
					}
				}
			}

			if ($hasDataToUpdate)
			{
				if ($langFile->count() > 0)
				{
					if ($this->updateLangFile($langFile))
					{
						$langFile->updatePhraseIndex();
					}
				}
				else
				{
					if ($langFile->isExists())
					{
						$langFile->deletePhraseIndex();
						if ($this->deleteLangFile($langFile))
						{
							$langFile->removeEmptyParents();
						}
					}
				}
			}
		}

		if (!$this->hasErrors())
		{
			$result['SUMMARY'] = Loc::getMessage('TR_EDIT_SAVING_COMPLETED');
		}

		return $result;
	}

	/**
	 * Generates name for form field.
	 *
	 * @param string $phraseId Phrase code.
	 * @param string $suffix Append name with.
	 * @param string $prefix Start name with.
	 *
	 * @return string
	 */
	private function generateFieldName($phraseId, $suffix = '', $prefix = '')
	{
		return
			(!empty($prefix) ? $prefix. '_' : '').
			\str_replace(['.', '-', ' '], '_', $phraseId).
			(!empty($suffix) ? '_'.$suffix : '')
		;
	}
}