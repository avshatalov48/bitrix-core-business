<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}
/**
 * @var array $arParams
 * @var array $arResult
 * @var \CBitrixComponentTemplate $this
 * @var \TranslateListComponent $component
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Translate;

$component = $this->getComponent();
if ($component->hasErrors())
{
	$arResult['ERROR_MESSAGE'] = $component->getFirstError()->getMessage();
}
if ($component->hasWarnings())
{
	$arResult['WARNING_MESSAGE'] = $component->getFirstWarning()->getMessage();
}


if (defined('SITE_TEMPLATE_ID') && SITE_TEMPLATE_ID === 'bitrix24')
{
	$iconDir = '<div class="ui-icon ui-icon-file-folder ui-icon-xs" title="'.Loc::getMessage("TR_FOLDER_TITLE").'"><i></i></div>';
	$iconDirUp = '<div class="ui-icon ui-icon-file-folder ui-icon-file-folder-up ui-icon-xs" title="'.Loc::getMessage("TR_UP_TITLE").'"><i></i></div>';
	$iconFile = '<div class="ui-icon ui-icon-file-php ui-icon-xs" title="'.Loc::getMessage("TR_FILE_TITLE").'"><i></i></div>';
}
else
{
	$iconDir = '<span class="adm-submenu-item-link-icon translate-icon translate-icon-folder" title="'.Loc::getMessage("TR_FOLDER_TITLE").'"></span>';
	$iconDirUp = '<span class="adm-submenu-item-link-icon translate-icon translate-icon-folder-up" title="'.Loc::getMessage("TR_UP_TITLE").'"></span>';
	$iconFile = '<span class="adm-submenu-item-link-icon translate-icon translate-icon-file" title="'.Loc::getMessage("TR_FILE_TITLE").'"></span>';
}

if (!empty($arResult['GRID_DATA']))
{
	$highlightSearchedCode = false;
	$highlightSearchedPhrase = false;
	$showCountPhrases = false;
	$showCountFiles = false;
	$showUntranslatedPhrases = false;
	$showUntranslatedFiles = false;
	$showCountPhrases = false;
	$showDiffLinks = false;

	// view mode
	if ($arResult['ACTION'] === \TranslateListComponent::ACTION_SEARCH_PHRASE)
	{
		$highlightSearchedCode = $arResult['HIGHLIGHT_SEARCHED_CODE'] ?? false;
		$highlightSearchedPhrase = $arResult['HIGHLIGHT_SEARCHED_PHRASE'] ?? false;
	}
	else
	{
		$showCountPhrases = $arParams['SHOW_COUNT_PHRASES'] ?? false;
		$showCountFiles = $arParams['SHOW_COUNT_FILES'] ?? false;
		$showUntranslatedPhrases = $arParams['SHOW_UNTRANSLATED_PHRASES'] ?? false;
		$showUntranslatedFiles = $arParams['SHOW_UNTRANSLATED_FILES'] ?? false;
	}
	if ($arResult['ACTION'] === \TranslateListComponent::ACTION_FILE_LIST)
	{
		$showDiffLinks = $arParams['SHOW_DIFF_LINKS'] ?? false;
	}

	if ($showCountFiles)
	{
		$dataTitle = Loc::getMessage('TR_INDEX_VIEW_MODE_TITLE_COUNT_FILES');
	}
	elseif ($showUntranslatedPhrases)
	{
		$dataTitle = Loc::getMessage('TR_INDEX_VIEW_MODE_TITLE_UNTRANSLATED');
	}
	elseif ($showUntranslatedFiles)
	{
		$dataTitle = Loc::getMessage('TR_INDEX_VIEW_MODE_TITLE_UNTRANSLATED_FILES');
	}
	else // $showCountPhrases
	{
		$dataTitle = Loc::getMessage('TR_INDEX_VIEW_MODE_TITLE_COUNT_PHRASES');
	}

	$dataEmptyEthalonTitle = Loc::getMessage('TR_INDEX_ERROR_ETHALON_NOT_FOUND');
	$dataEmptyTitle = Loc::getMessage('TR_INDEX_ERROR_TRANSLATION_NOT_FOUND');
	$sourceFileNotExistTitle = Loc::getMessage('TR_INDEX_ERROR_SOURCE_FILE_NOT_FOUND');
	$sourceFolderNotExistTitle = Loc::getMessage('TR_INDEX_ERROR_SOURCE_FOLDER_NOT_FOUND');
	$dataOkTitle = Loc::getMessage('TR_INDEX_TRANSLATION_OK');
	$dataUselessTitle = Loc::getMessage('TR_INDEX_TRANSLATION_USELESS');
	$dataMoreTitle = Loc::getMessage('TR_INDEX_TRANSLATION_MORE');
	$dataLessTitle = Loc::getMessage('TR_INDEX_TRANSLATION_LESS');

	$fileEmptyEthalonTitle = Loc::getMessage('TR_INDEX_ERROR_ETHALON_FILE_NOT_FOUND');
	$fileEmptyTitle = Loc::getMessage('TR_INDEX_ERROR_TRANSLATION_FILE_NOT_FOUND');
	$fileOkTitle = Loc::getMessage('TR_INDEX_TRANSLATION_FILE_OK');

	$formatIconWarning = static function ($title)
	{
		return '<span class="ui-icon ui-icon-xs translate-icon translate-icon-warning" title="'. $title. '"></span>';
	};
	$formatIconError = static function ($title)
	{
		return '<span class="ui-icon ui-icon-xs translate-icon translate-icon-error" title="'. $title. '"></span>';
	};
	$formatIconOk = static function ($title)
	{
		return '<span class="ui-icon ui-icon-xs translate-icon translate-icon-ok" title="'. $title. '"></span>';
	};
	$formatDiff = static function ($diff, $isObligatory = true) use ($dataMoreTitle, $dataLessTitle, $dataUselessTitle)
	{
		if ($diff > 0 && !$isObligatory)
		{
			return '<span title="'. $dataUselessTitle. '" class="translate-error-more">'. $diff. '</span>';
		}
		if ($diff > 0 && $isObligatory)
		{
			return '<span title="'. $dataMoreTitle. '" class="translate-error-more">'. $diff. '</span>';
		}
		if ($diff < 0 && $isObligatory)
		{
			return '<span title="'. $dataLessTitle. '" class="translate-error-less">'. abs($diff). '</span>';
		}
		return '';
	};
	$formatDiffRounded = static function ($diff, $isObligatory = true) use ($dataMoreTitle, $dataLessTitle, $dataUselessTitle)
	{
		if ($diff > 0 && !$isObligatory)
		{
			return '<span class="translate-error"><span title="'. $dataUselessTitle. '" class="translate-error-more">'. $diff. '</span></span>';
		}
		if ($diff > 0 && $isObligatory)
		{
			return '<span class="translate-error"><span title="'. $dataMoreTitle. '" class="translate-error-more">'. $diff. '</span></span>';
		}
		if ($diff < 0 && $isObligatory)
		{
			return '<span class="translate-error"><span title="'. $dataLessTitle. '" class="translate-error-less">'. abs($diff). '</span></span>';
		}
		return '';
	};
	$formatDeficiencyExcessRounded = static function ($deficiency, $excess) use ($dataMoreTitle, $dataLessTitle)
	{
		if ($deficiency > 0 && $excess > 0)
		{
			return
				'<span class="translate-error">'.
					'<span title="'. $dataMoreTitle. '" class="translate-error-less">'. $deficiency. '</span>'.
					'<span class="translate-error-split"></span>'.
					'<span title="'. $dataMoreTitle. '" class="translate-error-more">'. $excess. '</span>'.
				'</span>';
		}
		if ($deficiency > 0)
		{
			return '<span class="translate-error"><span title="'. $dataLessTitle. '" class="translate-error-less">'. $deficiency. '</span></span>';
		}
		if ($excess > 0)
		{
			return '<span class="translate-error"><span title="'. $dataMoreTitle. '" class="translate-error-more">'. $excess. '</span></span>';
		}
		return '';
	};

	$formatSearchedCode = static function($value, $search, $case, $startTag = '<span class="translate-highlight">', $endTag = '</span>')
	{
		if (!empty($search))
		{
			$modifier = ($case ? '' : 'i');
			$search = preg_quote($search, '/');
			return preg_replace('/('.$search.')/u'.$modifier, "{$startTag}\\1{$endTag}", $value);
		}
		return $value;
	};

	$formatSearchedPart = static function($value, $search, $case, $startTag = '<span class="translate-highlight">', $endTag = '</span>')
	{
		if (!empty($search) && !empty($value))
		{
			$modifier = ($case ? '' : 'i');
			$search = preg_quote($search, '/');
			if (preg_match('/[\s]+/', $search))
			{
				return preg_replace('/('.$search.')/u'.$modifier, "{$startTag}\\1{$endTag}", $value);
			}
			return preg_replace('/(\b'.$search.'\b)/u'.$modifier, "{$startTag}\\1{$endTag}", $value);
		}
		return $value;
	};
	$formatSearchedPhrase = static function($value, $search, $method, $case) use ($formatSearchedPart)
	{
		if (!empty($search) && !empty($value))
		{
			if (preg_match('/[\s]+/', $search))
			{
				$searchParts = [$search];
			}
			else
			{
				$searchParts = preg_split('/[\s]+/', $search);
			}

			$rnd = microtime();
			$startTag = "<!--#$rnd#-->";
			$endTag = "<!--/#$rnd#-->";
			foreach ($searchParts as $searchPart)
			{
				if (!empty($searchPart))
				{
					$value = $formatSearchedPart($value, $searchPart, $case, $startTag, $endTag);
				}
			}

			return str_replace([$startTag, $endTag], ['<span class="translate-highlight">', '</span>'], $value);
		}
		return $value;
	};


	$styles = [];
	foreach ($arResult['HEADERS'] as $header)
	{
		$styles[$header['id']] = $header['class'];
	}

	$languageUpperKeys = array_combine($arResult['LANGUAGES'], array_map('mb_strtoupper', $arResult['LANGUAGES']));

	$id = 1;
	foreach ($arResult['GRID_DATA'] as &$row)
	{
		$row['columnClasses'] = $styles;
		$row['actions'] = [];

		$actions = &$row['actions'];
		$columns = &$row['columns'];
		$index = &$row['index'];

		$columns['PATH'] = str_replace('#LANG_ID#', $arParams['CURRENT_LANG'], $columns['PATH']);
		if (isset($row['attrs']['data-path']))
		{
			$row['attrs']['data-path'] = str_replace('#LANG_ID#', $arParams['CURRENT_LANG'], $row['attrs']['data-path']);
		}

		if (isset($columns['IS_UP']) && $columns['IS_UP'] === true)
		{
			$row['id'] = $columns['ID'] = 'p0';
		}
		elseif (isset($columns['IS_DIR']) && $columns['IS_DIR'] === true)
		{
			$row['id'] = $columns['ID'] = 'p'.($id ++);

			$actions[] = array(
				'default' => true,
				'text' => Loc::getMessage('TR_PATH_GO'),
				'onclick' => 'BX.Translate.PathList.rowGridClick('.\CUtil::PhpToJSObject([
						'rowId' => $row['id'],
						'action' => \TranslateListComponent::ACTION_FILE_LIST,
					]).');'
			);
		}
		else
		{
			$row['id'] = $columns['ID'] = 'f'.($id++);

			$actions[] = array(
				'default' => true,
				'text' => Loc::getMessage('TR_MESSAGE_EDIT'),
				'onclick' => 'BX.Translate.PathList.rowGridClick('.\CUtil::PhpToJSObject([
						'rowId' => $row['id'],
						'action' => \TranslateListComponent::ACTION_EDIT,
					]).');'
			);

			if ($arResult['ALLOW_EDIT_SOURCE'])
			{
				$actions[] = array(
					'text' => Loc::getMessage('TR_FILE_SHOW'),
					'href' => $arParams['SHOW_SOURCE_PATH'].
						'?file='.htmlspecialcharsbx($columns['PATH']).
						'&path='.htmlspecialcharsbx($arResult['PATH']).
						'&lang='.$arParams['CURRENT_LANG'],
				);

				$actions[] = array(
					'text' => Loc::getMessage('TR_FILE_EDIT'),
					'href' => $arParams['EDIT_SOURCE_PATH'].
						'?file='.htmlspecialcharsbx($columns['PATH']).
						'&path='.htmlspecialcharsbx($arResult['PATH']).
						'&lang='.$arParams['CURRENT_LANG'],
				);
			}
		}

		if (isset($columns['IS_UP']) && $columns['IS_UP'] === true)
		{
			$columns['TITLE'] =
				'<a href="'.htmlspecialcharsbx($arParams['LIST_PATH']).
					'?lang='.$arParams['CURRENT_LANG'].
					'&path='.htmlspecialcharsbx($columns['PATH']).'" '.
					' title="'.Loc::getMessage("TR_UP_TITLE").'"'.
					' data-rowId="p0"'.
					' class="adm-list-table-icon-link translate-link-grid">'.
					$iconDirUp. '..'.
				'</a>';
		}
		elseif (isset($columns['IS_DIR']) && $columns['IS_DIR'] === true)
		{
			$columns['TITLE'] =
				'<a href="'.htmlspecialcharsbx($arParams['LIST_PATH']).
					'?lang='.$arParams['CURRENT_LANG'].
					'&path='.htmlspecialcharsbx($columns['PATH']).'" '.
					' title="'.Loc::getMessage("TR_FOLDER_TITLE").'"'.
					' data-rowId="'.$columns['ID'].'"'.
					' class="adm-list-table-icon-link translate-link-grid">'.
					$iconDir.
					($columns['IS_EXIST'] === false ? $formatIconError($sourceFolderNotExistTitle) : '').
					$columns['TITLE'].
				'</a>'
			;
		}
		else
		{
			$highlightHash = '';
			if ($highlightSearchedPhrase || $highlightSearchedCode)
			{
				$hash = preg_replace("/[^a-z1-9_]+/i", '', $columns['PHRASE_CODE']);
			}
			$columns['TITLE'] =
				'<a href="'.htmlspecialcharsbx($arParams['EDIT_PATH']).
					'?lang='.$arParams['CURRENT_LANG'].
					'&file='.htmlspecialcharsbx($columns['PATH']).
					'&path='.htmlspecialcharsbx($arResult['PATH']).
					'&tabId='.htmlspecialcharsbx($arParams['TAB_ID']).
					(($highlightSearchedPhrase || $highlightSearchedCode) ? '&highlight='.htmlspecialcharsbx($hash) : '').
					'" '.
					' title="'.Loc::getMessage("TR_FILE_TITLE").'"'.
					' data-rowId="'.$columns['ID'].'"'.
					' class="adm-list-table-icon-link translate-link-edit">'.
					$iconFile.
					($columns['IS_EXIST'] === false ? $formatIconError($sourceFileNotExistTitle) : '').
					$columns['TITLE'].
				'</a>'
			;
		}

		if (isset($columns['IS_DIR']) && $columns['IS_DIR'] === true)
		{
			if (!isset($index))
			{
				continue;
			}

			$ethalonExists = !empty($columns[mb_strtoupper($arParams['CURRENT_LANG']).'_LANG']);

			$settings = !empty($row['settings']) ? $row['settings'] : [];

			foreach ($languageUpperKeys as $langId => $langUpper)
			{
				$columnId = "{$langUpper}_LANG";
				$columnExcess = "{$langUpper}_EXCESS";
				$columnDeficiency = "{$langUpper}_DEFICIENCY";

				$isObligatory = true;
				if (!empty($settings[Translate\Settings::OPTION_LANGUAGES]))
				{
					$isObligatory = in_array($langId, $settings[Translate\Settings::OPTION_LANGUAGES], true);
				}

				$value = !empty($columns[$columnId]) ? $columns[$columnId] : null;
				$excess = !empty($columns[$columnExcess]) ? $columns[$columnExcess] : null;
				$deficiency = !empty($columns[$columnDeficiency]) && $isObligatory ? $columns[$columnDeficiency] : null;


				if ($showCountPhrases || $showCountFiles)
				{
					$columns[$columnId] = '';
					if ($value === 0 || $value === null)
					{
						if ($isObligatory)
						{
							if ($langId === $arParams['CURRENT_LANG'])
							{
								$columns[$columnId] = $formatIconWarning($dataEmptyEthalonTitle);
							}
							elseif (!$ethalonExists || $excess > 0)
							{
								$columns[$columnId] = $formatIconWarning($dataEmptyTitle);
							}
							elseif ($ethalonExists && $deficiency > 0)
							{
								$columns[$columnId] = $formatIconError($dataEmptyTitle);
							}
						}
					}
					else
					{
						$columns[$columnId] = $value .'&nbsp;';
					}

					if (!$showDiffLinks)
					{
						if ($deficiency > 0 || $excess > 0)
						{
							$columns[$columnId] .= $formatDeficiencyExcessRounded($deficiency, $excess);
						}
					}
				}
				elseif ($showUntranslatedPhrases || $showUntranslatedFiles)
				{
					$columns[$columnId] = '';
					if ($value === 0 || $value === null)
					{
						if ($isObligatory)
						{
							if ($langId === $arParams['CURRENT_LANG'])
							{
								$columns[$columnId] = $formatIconWarning($dataEmptyEthalonTitle);
							}
							elseif (!$ethalonExists || $excess > 0)
							{
								$columns[$columnId] = $formatIconWarning($dataEmptyTitle);
							}
							elseif ($ethalonExists && $deficiency > 0)
							{
								$columns[$columnId] = $formatIconError($dataEmptyTitle);
							}

							if (!$showDiffLinks && $deficiency > 0)
							{
								$columns[$columnId] .= $formatDiffRounded(-$deficiency);
							}
						}
					}
					elseif (!$showDiffLinks && $deficiency > 0)
					{
						$columns[$columnId] = $formatDiff(- $deficiency);
					}
				}

				if ($showDiffLinks)
				{
					if (isset($index, $index[$langId]['deficiency_links']) && count($index[$langId]['deficiency_links']) > 0)
					{
						$columns[$columnId] .= '<div class="translate-link-diff">';
						$columns[$columnId] .= $formatDiff(- $deficiency). ': ';

						foreach ($index[$langId]['deficiency_links'] as $i => $link)
						{
							$path = htmlspecialcharsbx(str_replace('#LANG_ID#', $arParams['CURRENT_LANG'], $link['path']));

							$columns[$columnId] .= ($i > 0 ? ', ' : '');
							$columns[$columnId] .=
								'<a href="'.htmlspecialcharsbx($arParams['EDIT_PATH']).
								'?lang='.$arParams['CURRENT_LANG'].
								'&path='.htmlspecialcharsbx($arResult['PATH']).
								'&tabId='.htmlspecialcharsbx($arParams['TAB_ID']).
								'&file='.$path. '" '.
								' title="'.$path.'"'.
								' class="translate-link-edit">'.
								$link['deficiency'].
								'</a>';
						}
						unset($i, $link, $path);

						if (isset($index[$langId]['deficiency_links_more']))
						{
							$columns[$columnId] .= '...';
						}

						$columns[$columnId] .= '</div>';
					}
					if (isset($index, $index[$langId]['excess_links']) && count($index[$langId]['excess_links']) > 0)
					{
						$columns[$columnId] .= '<div class="translate-link-diff">';
						$columns[$columnId] .= $formatDiff($excess). ': ';

						foreach ($index[$langId]['excess_links'] as $i => $link)
						{
							$path = htmlspecialcharsbx(str_replace('#LANG_ID#', $arParams['CURRENT_LANG'], $link['path']));

							$columns[$columnId] .= ($i > 0 ? ', ' : '');
							$columns[$columnId] .=
								'<a href="'.htmlspecialcharsbx($arParams['EDIT_PATH']).
								'?lang='.$arParams['CURRENT_LANG'].
								'&path='.htmlspecialcharsbx($arResult['PATH']).
								'&tabId='.htmlspecialcharsbx($arParams['TAB_ID']).
								'&file='.$path. '" '.
								' title="'.$path.'"'.
								' class="translate-link-edit">'.
								$link['excess'].
								'</a>';
						}
						unset($i, $link, $path);

						if (isset($index[$langId]['excess_links_more']))
						{
							$columns[$columnId] .= '...';
						}

						$columns[$columnId] .= '</div>';
					}
				}

			}
		}
		elseif (isset($columns['IS_FILE']) && $columns['IS_FILE'] === true)
		{
			$ethalonExists = !empty($columns[mb_strtoupper($arParams['CURRENT_LANG']).'_LANG']);

			if ($highlightSearchedCode)
			{
				$columns['PHRASE_CODE'] =
					$formatSearchedCode($columns['PHRASE_CODE'], $arResult['CODE_SEARCH'], $arResult['CODE_SEARCH_CASE']);
			}

			$settings = !empty($row['settings']) ? $row['settings'] : [];

			foreach ($languageUpperKeys as $langId => $langUpper)
			{
				$columnId = "{$langUpper}_LANG";
				$columnExcess = "{$langUpper}_EXCESS";
				$columnDeficiency = "{$langUpper}_DEFICIENCY";

				$value = !empty($columns[$columnId]) ? $columns[$columnId] : null;
				$excess = !empty($columns[$columnExcess]) ? $columns[$columnExcess] : null;
				$deficiency = !empty($columns[$columnDeficiency]) ? $columns[$columnDeficiency] : null;

				$isObligatory = true;
				if (!empty($settings[Translate\Settings::OPTION_LANGUAGES]))
				{
					$isObligatory = in_array($langId, $settings[Translate\Settings::OPTION_LANGUAGES], true);
				}

				if ($highlightSearchedPhrase || $highlightSearchedCode)
				{
					if (empty($value) && $isObligatory)
					{
						if (isset($arParams['CURRENT_LANG']) && $langId === $arParams['CURRENT_LANG'])
						{
							$columns[$columnId] = $formatIconWarning($dataEmptyEthalonTitle);
						}
						elseif (!$ethalonExists)
						{
							$columns[$columnId] = $formatIconWarning($dataEmptyTitle);
						}
						else
						{
							$columns[$columnId] = $formatIconError($dataEmptyTitle);
						}
					}
					elseif (isset($arResult['PHRASE_SEARCH_LANGUAGE_ID']) && $langId === $arResult['PHRASE_SEARCH_LANGUAGE_ID'])
					{
						$columns[$columnId] =
							$formatSearchedPhrase(
								$value,
								$arResult['PHRASE_SEARCH'],
								$arResult['PHRASE_SEARCH_METHOD'],
								$arResult['PHRASE_SEARCH_CASE']
							);
					}
				}
				elseif ($showCountPhrases)
				{
					if ($value === 0 || $value === null)
					{
						if ($isObligatory)
						{
							if ($langId === $arParams['CURRENT_LANG'])
							{
								$columns[$columnId] = $formatIconWarning($dataEmptyEthalonTitle);
							}
							elseif (!$ethalonExists)
							{
								$columns[$columnId] = $formatIconWarning($dataEmptyTitle);
							}
							else
							{
								$columns[$columnId] = $formatIconError($dataEmptyTitle);
							}
						}
						else
						{
							$columns[$columnId] = '';
						}
					}
					else
					{
						$columns[$columnId] = $value .'&nbsp;';
					}

					if ($isObligatory)
					{
						if ($deficiency > 0 || $excess > 0)
						{
							$columns[$columnId] .= $formatDeficiencyExcessRounded($deficiency, $excess);
						}
					}
					elseif ($excess > 0)
					{
						$columns[$columnId] .= $formatDiffRounded($excess, $isObligatory);
					}
				}
				elseif ($showCountFiles)
				{
					if ($value === 0 || $value === null)
					{
						if ($isObligatory)
						{
							if ($langId === $arParams['CURRENT_LANG'])
							{
								$columns[$columnId] = $formatIconWarning($dataEmptyEthalonTitle);
							}
							elseif (!$ethalonExists)
							{
								$columns[$columnId] = $formatIconWarning($dataEmptyTitle);
							}
							else
							{
								$columns[$columnId] = $formatIconError($dataEmptyTitle);
							}
						}
					}
					else
					{
						$columns[$columnId] = $formatIconOk($fileOkTitle);
					}
				}
				elseif ($showUntranslatedPhrases)
				{
					if ($value === 0 || $value === null)
					{
						if ($isObligatory)
						{
							if ($langId === $arParams['CURRENT_LANG'])
							{
								$columns[$columnId] = $formatIconWarning($dataEmptyEthalonTitle);
							}
							elseif (!$ethalonExists)
							{
								$columns[$columnId] = $formatIconWarning($dataEmptyTitle);
							}
							else
							{
								$columns[$columnId] = $formatIconError($dataEmptyTitle);
							}
							if ($deficiency > 0)
							{
								$columns[$columnId] .= $formatDiffRounded(-$deficiency);
							}
						}
					}
					else
					{
						$columns[$columnId] = '';
						if ($deficiency > 0 && $isObligatory)
						{
							$columns[$columnId] = $formatDiff(- $deficiency);
						}
					}
				}
				elseif ($showUntranslatedFiles)
				{
					if ($value === 0 || $value === null)
					{
						if ($isObligatory)
						{
							if ($langId == $arParams['CURRENT_LANG'])
							{
								$columns[$columnId] = $formatIconWarning($dataEmptyEthalonTitle);
							}
							elseif (!$ethalonExists)
							{
								$columns[$columnId] = $formatIconWarning($dataEmptyTitle);
							}
							else
							{
								$columns[$columnId] = $formatIconError($dataEmptyTitle);
							}
						}
					}
					else
					{
						$columns[$columnId] = $formatIconOk($fileOkTitle);
					}
				}
			}
		}
	}
	unset($row);

	array_unshift(
		$arResult['GRID_DATA'],
		array(
			'editable' => false,
			'not_count' => true,
			'custom' => '<span id="bx-translate-list-params" data-actionmode="'.$arResult['ACTION'].'" data-tabid="'.$arParams['TAB_ID'].'" style="display:none;"></span>'
		)
	);

	if ($arResult['ACTION'] === \TranslateListComponent::ACTION_FILE_LIST)
	{
		if (!$arResult['IS_INDEXED'])
		{
			array_unshift(
				$arResult['GRID_DATA'],
				array(
					'editable' => false,
					'not_count' => true,
					'custom' =>
						'<div class="translate-error-noindex">'.
						Loc::getMessage('TR_INDEX_ERROR_NOINDEX').
						' <span class="translate-link-like translate-start-indexing">'.Loc::getMessage('TR_INDEX_START_INDEXING').'</span>'.
						'</div>'
				)
			);
		}
	}
}


