<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
if (!\Bitrix\Main\Loader::includeModule('translate'))
{
	return;
}

use Bitrix\Main;
use Bitrix\Main\Error;
use Bitrix\Main\Localization;
use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;

Main\Loader::includeModule('translate');

class TranslateEditComponent extends Translate\ComponentBase
{
	public const VIEW_MODE_UNTRANSLATED = 'Untranslated';
	public const VIEW_MODE_SHOW_ALL = 'ShowAll';
	public const VIEW_MODE_SOURCE_EDIT = 'SourceEdit';
	public const VIEW_MODE_SOURCE_VIEW = 'SourceView';

	public const TEMPLATE_SOURCE_EDIT = 'source_edit';
	public const TEMPLATE_SOURCE_VIEW = 'source_view';

	/** @var string */
	private $filePath;

	/** @var string */
	private $viewMode;

	/** @var array */
	private $langSettings = array();

	/** @var string */
	private $highlightPhraseId;


	/**
	 * @return void
	 */
	protected function prepareParams()
	{
		parent::prepareParams();
		$paramsIn =& $this->getParams();

		$paramsIn['TAB_ID'] = $this->detectTabId();

		// highlight phrase
		$highlightPhraseId = $this->request->get('highlight');
		if (!empty($highlightPhraseId))
		{
			$this->highlightPhraseId = preg_replace("/[^a-z1-9_]+/i", '', $highlightPhraseId);
			if (!empty($this->highlightPhraseId))
			{
				$paramsIn['HIGHLIGHT_PHRASE_ID'] = $this->highlightPhraseId;
			}
		}


		// view mode
		if (!empty($this->highlightPhraseId))
		{
			$paramsIn['VIEW_MODE'] = self::VIEW_MODE_SHOW_ALL;
			$paramsIn['SHOW_ALL'] = true;
			$paramsIn['SHOW_UNTRANSLATED'] = false;
		}
		else
		{
			$paramsIn['VIEW_MODE'] = $this->detectViewMode();
			$paramsIn['SHOW_ALL'] = (self::VIEW_MODE_SHOW_ALL === $this->viewMode);
			$paramsIn['SHOW_UNTRANSLATED'] = (self::VIEW_MODE_UNTRANSLATED === $this->viewMode);
		}
	}

	/**
	 * @return string
	 */
	protected function detectTabId()
	{
		$tabId = $this->request->get('tabId');
		if (!empty($tabId) && (int)$tabId > 0)
		{
			$this->tabId = (int)$tabId;
		}
		else
		{
			$this->tabId = Translate\Filter::getTabId(true);
		}

		return $this->tabId;
	}

	/**
	 * @return void
	 */
	public function executeComponent()
	{
		if (!$this->checkModuleAvailability() || !$this->checkPermissionEdit())
		{
			return;
		}

		$this->prepareParams();

		if (in_array($this->viewMode, array(self::VIEW_MODE_SOURCE_EDIT, self::VIEW_MODE_SOURCE_VIEW)))
		{
			if (!$this->checkPermissionEditPhp())
			{
				return;
			}
		}

		$paramsIn =& $this->getParams();

		// path
		$this->path = $this->detectPath('path');
		$this->filePath = $this->detectPath('file');
		if (empty($this->filePath))
		{
			$this->addError(new Error(Loc::getMessage('TR_EDIT_FILE_PATH_ERROR'), self::STATUS_DENIED));
			$this->includeComponentTemplate(self::TEMPLATE_ERROR);
			return;
		}
		if (!Translate\IO\Path::isLangDir($this->filePath) || (mb_substr($this->filePath, -4) !== '.php'))
		{
			$this->addError(new Error(Loc::getMessage('TR_EDIT_FILE_NOT_LANG', array('#FILE#' => $this->filePath)), self::STATUS_DENIED));
			$this->includeComponentTemplate(self::TEMPLATE_ERROR);
			return;
		}
		if (!Translate\Permission::isAllowPath($this->filePath))
		{
			$this->addError(new Error(Loc::getMessage('TR_EDIT_FILE_WRONG_NAME'), self::STATUS_DENIED));
			$this->includeComponentTemplate(self::TEMPLATE_ERROR);
			return;
		}

		$this->arResult['FILE_PATH'] = Translate\IO\Path::replaceLangId($this->filePath, Loc::getCurrentLang());
		$this->arResult['PATH'] = Translate\IO\Path::replaceLangId($this->path, Loc::getCurrentLang());

		$this->arResult['CHAIN'] = $this->generateChainLinks($this->arResult['FILE_PATH']);

		$this->arResult['LINK_BACK'] =
			$paramsIn['LIST_PATH'].
			'?lang='.Loc::getCurrentLang().
			'&tabId='.$this->tabId.
			'&path='. htmlspecialcharsbx($this->arResult['PATH']);

		$this->arResult['LINK_EDIT'] =
			$paramsIn['EDIT_PATH'].
			'?lang='.$paramsIn['CURRENT_LANG'].
			'&tabId='.$this->tabId.
			'&file='. htmlspecialcharsbx($this->arResult['FILE_PATH']).
			'&path='. htmlspecialcharsbx($this->arResult['PATH']);

		if ($this->arResult['ALLOW_EDIT_SOURCE'])
		{
			$this->arResult['LINK_SHOW_SOURCE'] =
				$paramsIn['SHOW_SOURCE_PATH'].
				'?lang='.$paramsIn['CURRENT_LANG'].
				'&tabId='.$this->tabId.
				'&file='.htmlspecialcharsbx($this->arResult['FILE_PATH']).
				'&path='.htmlspecialcharsbx($this->arResult['PATH']);

			$this->arResult['LINK_EDIT_SOURCE'] =
				$paramsIn['EDIT_SOURCE_PATH'].
				'?lang='.$paramsIn['CURRENT_LANG'].
				'&tabId='.$this->tabId.
				'&file='.htmlspecialcharsbx($this->arResult['FILE_PATH']).
				'&path='.htmlspecialcharsbx($this->arResult['PATH']);
		}

		// languages
		$this->arResult['LANGUAGES'] = $this->getLanguages();
		$this->arResult['COMPATIBLE_LANGUAGES'] = $this->getCompatibleLanguages();
		$this->arResult['LANGUAGES_TITLE'] = $this->getLanguagesTitle($this->arResult['LANGUAGES']);

		$documentRoot = Main\Application::getDocumentRoot();
		$encodingOut = Localization\Translation::getCurrentEncoding();

		// settings
		$settingsFile = Translate\Settings::instantiateByPath($documentRoot. '/'. $this->filePath);
		if (($settingsFile instanceof Translate\Settings) && $settingsFile->isExists() && $settingsFile->load())
		{
			$this->langSettings = $settingsFile->getOptions($this->filePath);
			$this->arResult['LANG_SETTINGS'] = $this->langSettings;
		}

		if ($this->viewMode === self::VIEW_MODE_SOURCE_VIEW || $this->viewMode === self::VIEW_MODE_SOURCE_EDIT)
		{
			$langRelPath = Translate\IO\Path::replaceLangId($this->filePath, $paramsIn['CURRENT_LANG']);
			$langFullPath = Translate\IO\Path::tidy($documentRoot.'/'.$langRelPath);
			$langFullPath = Main\Localization\Translation::convertLangPath($langFullPath, $paramsIn['CURRENT_LANG']);

			$langFile = new Translate\File($langFullPath);
			$langFile
				->setLangId($paramsIn['CURRENT_LANG'])
				->setOperatingEncoding($encodingOut);

			if ($langFile->isExists())
			{
				$this->arResult['FILE_SOURCE'] = $langFile->getContents();
				$this->arResult['FILE_PHRASE_COUNT'] = $langFile->count(true);
				$this->arResult['FILE_LANGUAGE_ID'] = $langFile->getLangId();
				$this->arResult['FILE_LANGUAGE'] = $this->arResult['LANGUAGES_TITLE'][$langFile->getLangId()];
			}

			if ($this->viewMode === self::VIEW_MODE_SOURCE_VIEW)
			{
				$this->includeComponentTemplate(self::TEMPLATE_SOURCE_VIEW);
			}
			else
			{
				$this->includeComponentTemplate(self::TEMPLATE_SOURCE_EDIT);
			}
		}
		else
		{
			$fullPaths = [];
			foreach ($this->arResult['LANGUAGES'] as $langId)
			{
				$langRelPath = Translate\IO\Path::replaceLangId($this->filePath, $langId);
				$langFullPath = Translate\IO\Path::tidy($documentRoot.'/'.$langRelPath);
				$langFullPath = Main\Localization\Translation::convertLangPath($langFullPath, $langId);
				$fullPaths[$langId] = $langFullPath;
			}

			// lets get phrases
			[$this->arResult['MESSAGES'], $this->arResult['DIFFERENCES']] = $this->mergeLangFiles(
				$fullPaths,
				$this->arResult['LANGUAGES'],
				$encodingOut,
				($this->viewMode == self::VIEW_MODE_UNTRANSLATED)
			);

			if ($paramsIn['SET_TITLE'])
			{
				$this->getApplication()->setTitle(Loc::getMessage('TR_EDIT_TITLE'));
			}

			$this->includeComponentTemplate();
		}
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
	public function generateFieldName($phraseId, $suffix = '', $prefix = '')
	{
		return
			(!empty($prefix) ? $prefix. '_' : '').
			str_replace(['.', '-'], '_', $phraseId).
			(!empty($suffix) ? '_'.$suffix : '');
	}


	/**
	 * Merges all language files into one array.
	 *
	 * @param string[] $fullLangFilePaths Array of full paths to lang files.
	 * @param string[] $languages Array of languages to merge.
	 * @param string $encodingOut Output encoding.
	 * @param boolean $collectUntranslated Leave only untranslated phrases.
	 *
	 * @return array
	 */
	private function mergeLangFiles($fullLangFilePaths, $languages, $encodingOut = null, $collectUntranslated = false)
	{
		$mergedContent = array();
		$langCodes = array();

		$rowLang0 = array();
		foreach ($languages as $langId)
		{
			$rowLang0[$langId] = '';
		}

		foreach ($languages as $langId)
		{
			if (empty($fullLangFilePaths[$langId]))
			{
				continue;
			}

			$fullPath = $fullLangFilePaths[$langId];
			$langFile = new Translate\File($fullPath);
			$langFile->setLangId($langId);

			if (!empty($encodingOut))
			{
				$langFile->setOperatingEncoding($encodingOut);
			}

			if (!$langFile->load())
			{
				continue;
			}

			$langCodes[$langId] = array();
			foreach ($langFile as $code => $phrase)
			{
				if (!isset($mergedContent[$code]))
				{
					$mergedContent[$code] = $rowLang0;
				}
				$mergedContent[$code][$langId] = $phrase;
				$langCodes[$langId][] = $code;
			}
		}

		if ($collectUntranslated)
		{
			foreach ($mergedContent as $code => $row)
			{
				foreach ($row as $langId => $phr)
				{
					$isObligatory = true;
					if (!empty($this->langSettings['languages']))
					{
						$isObligatory = in_array($langId, $this->langSettings['languages'], true);
					}
					if (!isset($phr) || !is_string($phr) || (empty($phr) && $phr !== '0'))
					{
						if ($isObligatory)
						{
							continue 2;
						}
					}
				}
				unset($mergedContent[$code]);
			}
		}

		// calculate the sum and difference of files
		$differences = array();
		$currentLang = Loc::getCurrentLang();
		$ethalonCodes = $langCodes[$currentLang] ?: [];
		foreach ($languages as $langId)
		{
			$isObligatory = true;
			if (!empty($this->langSettings['languages']))
			{
				$isObligatory = in_array($langId, $this->langSettings['languages'], true);
			}

			if ($langId === $currentLang)
			{
				$differences[$currentLang] = array(
					'TOTAL' => count($ethalonCodes),
					'LESS' => 0,
					'MORE' => $isObligatory ? 0 : count($ethalonCodes),
					'CODES' => $ethalonCodes,
				);
			}
			elseif (!isset($langCodes[$langId]))
			{
				$differences[$langId] = array(
					'TOTAL' => 0,
					'LESS' => $isObligatory ? count($ethalonCodes) : 0,
					'MORE' => 0,
					'CODES' => [],
				);
			}
			else
			{
				$differences[$langId] = array(
					'TOTAL' => count($langCodes[$langId]),
					'LESS' => $isObligatory ? count(array_diff($ethalonCodes, $langCodes[$langId])) : 0,
					'MORE' => $isObligatory ? count(array_diff($langCodes[$langId], $ethalonCodes)) : count($langCodes[$langId]),
					'CODES' => $langCodes[$langId],
				);
			}
		}

		return [$mergedContent, $differences];
	}

	/**
	 * Finds requested path from.
	 *
	 * @param string $inpName Input parameter name.
	 * @return string
	 */
	private function detectPath($inpName = 'file')
	{
		// from request
		$path = $this->request->get($inpName);

		if (!empty($path) && !preg_match("#\.\.[\\/]#".BX_UTF_PCRE_MODIFIER, $path))
		{
			$path = Translate\IO\Path::normalize($path);
			if (Translate\Permission::isAllowPath($path))
			{
				$path = Translate\IO\Path::replaceLangId($path, '#LANG_ID#');
			}
		}

		return $path;
	}

	/**
	 *  Restores current view mode.
	 *
	 * @return string
	 */
	private function detectViewMode()
	{
		$paramsIn =& $this->getParams();

		if (!empty($paramsIn['VIEW_MODE']) && in_array($paramsIn['VIEW_MODE'], array(self::VIEW_MODE_SOURCE_EDIT, self::VIEW_MODE_SOURCE_VIEW), true))
		{
			$this->viewMode = $paramsIn['VIEW_MODE'];

			return $this->viewMode;
		}

		if ($this->request->get('viewMode') !== null)
		{
			$viewMode = $this->request->get('viewMode');
		}
		else
		{
			$viewMode = \CUserOptions::getOption('translate', 'edit_mode', '');
		}
		if (!empty($viewMode) && in_array($viewMode, array(self::VIEW_MODE_UNTRANSLATED, self::VIEW_MODE_SHOW_ALL), true))
		{
			$this->viewMode = $viewMode;
			\CUserOptions::setOption('translate', 'edit_mode', $this->viewMode);
		}
		if (empty($this->viewMode))
		{
			$this->viewMode = self::VIEW_MODE_SHOW_ALL;
		}

		return $this->viewMode;
	}
}