<?php
IncludeModuleLangFile(__FILE__);

class CSearchParameters
{
	public static function ConvertParamsToFilter($arParams, $strFilterParamName)
	{
		$exFILTER = [];

		if (!is_array($arParams[$strFilterParamName]) && $arParams[$strFilterParamName] <> '')
		{
			$arParams[$strFilterParamName] = [$arParams[$strFilterParamName]];
		}

		if (is_array($arParams[$strFilterParamName]))
		{
			foreach ($arParams[$strFilterParamName] as $strFILTER)
			{
				switch ($strFILTER)
				{
				case 'main':
					$exFILTER[] = CSearchParameters::_main($arParams[$strFilterParamName . '_main'] ?? null);
					break;
				case 'forum':
					if (IsModuleInstalled('forum'))
					{
						$exFILTER[] = CSearchParameters::_forum($arParams[$strFilterParamName . '_forum'] ?? null);
					}
					break;
				case 'blog':
					$exFILTER[] = CSearchParameters::_blog($arParams[$strFilterParamName . '_blog'] ?? null);
					break;
				case 'microblog':
					$exFILTER[] = [
						'=MODULE_ID' => 'blog',
						'PARAM1' => 'MICROBLOG',
					];
					break;
				case 'socialnetwork':
					$exFILTER[] = CSearchParameters::_socialnetwork($arParams[$strFilterParamName . '_socialnetwork'] ?? null);
					break;
				case 'socialnetwork_user':
					$exFILTER[] = CSearchParameters::_socialnetwork_user($arParams[$strFilterParamName . '_socialnetwork_user'] ?? null);
					break;
				case 'intranet':
					$exFILTER[] = [
						'=MODULE_ID' => 'intranet',
					];
					break;
				case 'crm':
					$exFILTER[] = [
						'=MODULE_ID' => 'crm',
					];
					break;
				case 'disk':
					$exFILTER[] = [
						'=MODULE_ID' => 'disk',
					];
					break;
				case 'no':
					break;
				default:
					if (mb_strpos($strFILTER, 'iblock_') === 0)
					{
						$exFILTER[] = CSearchParameters::_iblock($arParams[$strFilterParamName . '_' . $strFILTER] ?? null, $strFILTER);
					}
					else
					{
						$exFILTER[] = [
							'=MODULE_ID' => $strFILTER,
						];
					}
					break;
				}
			}
		}

		return $exFILTER;
	}

	public static function GetFilterDropDown($bFilter = false)
	{
		if ($bFilter)
		{
			$arrDropdown = [
				'no' => GetMessage('SEARCH_CP_NO_LIMIT'),
				'main' => '[main] ' . GetMessage('SEARCH_CP_STATIC'),
			];
		}
		else
		{
			$arrDropdown = [];
		}

		if (IsModuleInstalled('forum'))
		{
			$arrDropdown['forum'] = '[forum] ' . GetMessage('SEARCH_CP_FORUM');
		}

		if (CModule::IncludeModule('iblock'))
		{
			$rsType = CIBlockType::GetList(['sort' => 'asc'], ['ACTIVE' => 'Y']);
			while ($arr = $rsType->Fetch())
			{
				if ($ar = CIBlockType::GetByIDLang($arr['ID'], LANGUAGE_ID))
				{
					$arrDropdown['iblock_' . $arr['ID']] = '[iblock_' . $arr['ID'] . '] ' . $ar['~NAME'];
				}
			}
		}

		if (IsModuleInstalled('blog'))
		{
			$arrDropdown['blog'] = '[blog] ' . GetMessage('SEARCH_CP_BLOG');
			if ($bFilter)
			{
				$arrDropdown['microblog'] = '[microblog] ' . GetMessage('SEARCH_CP_MICROBLOG');
			}
		}

		if (IsModuleInstalled('socialnetwork'))
		{
			$arrDropdown['socialnetwork'] = '[socialnetwork] ' . GetMessage('SEARCH_CP_SOCNET');
			$arrDropdown['socialnetwork_user'] = '[socialnetwork_user] ' . GetMessage('SEARCH_CP_SOCNET_USER');
		}

		if (IsModuleInstalled('intranet'))
		{
			$arrDropdown['intranet'] = '[intranet] ' . GetMessage('SEARCH_CP_INTRANET_USERS');
		}

		if (IsModuleInstalled('crm'))
		{
			$arrDropdown['crm'] = '[crm] ' . GetMessage('SEARCH_CP_CRM');
		}

		if (IsModuleInstalled('disk'))
		{
			$arrDropdown['disk'] = '[disk] ' . GetMessage('SEARCH_CP_DISK');
		}

		return $arrDropdown;
	}

	public static function GetModulesList()
	{
		$result = [];
		foreach (GetModuleEvents('search', 'OnReindex', true) as $arEvent)
		{
			if (!array_key_exists($arEvent['TO_MODULE_ID'], $result))
			{
				$module = $arEvent['TO_MODULE_ID'];

				if (IsModuleInstalled($module))
				{
					$obModule = CModule::CreateModuleObject($module);
					if ($obModule)
					{
						$result[$module] = '[' . $module . '] ' . $obModule->MODULE_NAME;
					}
				}
			}
		}

		ksort($result);
		return $result;
	}

	public static function AddFilterParams(&$arComponentParameters, $arCurrentValues, $name, $parent, $MULTIPLE = 'Y')
	{
		$arComponentParameters['PARAMETERS'][$name] = [
			'PARENT' => $parent,
			'NAME' => GetMessage('SEARCH_CP_WHERE_FILTER'),
			'TYPE' => 'LIST',
			'MULTIPLE' => $MULTIPLE,
			'VALUES' => CSearchParameters::GetFilterDropDown(true),
			'DEFAULT' => 'all',
			'REFRESH' => 'Y',
		];

		if (!is_array($arCurrentValues[$name]) && $arCurrentValues[$name] <> '')
		{
			$arCurrentValues[$name] = [$arCurrentValues[$name]];
		}

		if (is_array($arCurrentValues[$name]))
		{
			foreach ($arCurrentValues[$name] as $strFILTER)
			{
				if ($strFILTER == 'main')
				{
					$arComponentParameters['PARAMETERS'][$name . '_' . $strFILTER] = [
						'PARENT' => $parent,
						'NAME' => GetMessage('SEARCH_CP_URL'),
						'TYPE' => 'STRING',
						'MULTIPLE' => $MULTIPLE,
						'ADDITIONAL_VALUES' => 'Y',
						'DEFAULT' => '',
					];
				}
				elseif ($strFILTER == 'forum')
				{
					$arrFILTER = [];
					if (CModule::IncludeModule('forum'))
					{
						$arrFILTER['all'] = GetMessage('SEARCH_CP_ALL');
						$rsForum = CForumNew::GetList();
						while ($arForum = $rsForum->Fetch())
						{
							$arrFILTER[$arForum['ID']] = $arForum['NAME'];
						}
					}

					$arComponentParameters['PARAMETERS'][$name . '_' . $strFILTER] = [
						'PARENT' => $parent,
						'NAME' => GetMessage('SEARCH_CP_FORUM'),
						'TYPE' => 'LIST',
						'MULTIPLE' => $MULTIPLE,
						'VALUES' => $arrFILTER,
						'ADDITIONAL_VALUES' => 'N',
						'DEFAULT' => 'all',
					];
				}
				elseif (mb_strpos($strFILTER, 'iblock_') === 0)
				{
					$arrFILTER = [];
					if (CModule::IncludeModule('iblock'))
					{
						$arrFILTER['all'] = GetMessage('SEARCH_CP_ALL');
						$rsIBlock = CIBlock::GetList(['SORT' => 'ASC'], ['TYPE' => mb_substr($strFILTER, 7)]);
						while ($arIBlock = $rsIBlock->Fetch())
						{
							$arrFILTER[$arIBlock['ID']] = $arIBlock['NAME'];
						}
					}

					$arComponentParameters['PARAMETERS'][$name . '_' . $strFILTER] = [
						'PARENT' => $parent,
						'NAME' => GetMessage('SEARCH_CP_IBLOCK_TYPE', ['#TYPE_ID#' => $strFILTER]),
						'TYPE' => 'LIST',
						'MULTIPLE' => $MULTIPLE,
						'VALUES' => $arrFILTER,
						'ADDITIONAL_VALUES' => 'N',
						'DEFAULT' => 'all',
					];
				}
				elseif ($strFILTER == 'blog')
				{
					$arrFILTER = [];
					if (CModule::IncludeModule('blog'))
					{
						$arrFILTER['all'] = GetMessage('SEARCH_CP_ALL');
						$rsBlog = CBlog::GetList();
						while ($arBlog = $rsBlog->Fetch())
						{
							$arrFILTER[$arBlog['ID']] = $arBlog['NAME'];
						}
					}

					$arComponentParameters['PARAMETERS'][$name . '_' . $strFILTER] = [
						'PARENT' => $parent,
						'NAME' => GetMessage('SEARCH_CP_BLOG'),
						'TYPE' => 'LIST',
						'MULTIPLE' => $MULTIPLE,
						'VALUES' => $arrFILTER,
						'ADDITIONAL_VALUES' => 'N',
						'DEFAULT' => 'all',
					];
				}
				elseif ($strFILTER == 'socialnetwork')
				{
					$arrFILTER = [];
					if (CModule::IncludeModule('socialnetwork'))
					{
						$arrFILTER['all'] = GetMessage('SEARCH_CP_ALL');
						$rsGroup = CSocNetGroup::GetList(['ID' => 'DESC'], [], false, false, ['ID', 'NAME']);
						while ($arGroup = $rsGroup->Fetch())
						{
							$arrFILTER[$arGroup['ID']] = $arGroup['NAME'];
						}
					}

					$arComponentParameters['PARAMETERS'][$name . '_' . $strFILTER] = [
						'PARENT' => $parent,
						'NAME' => GetMessage('SEARCH_CP_SOCIALNETWORK_GROUPS'),
						'TYPE' => 'LIST',
						'MULTIPLE' => $MULTIPLE,
						'VALUES' => $arrFILTER,
						'ADDITIONAL_VALUES' => 'N',
						'DEFAULT' => 'all',
					];
				}
				elseif ($strFILTER == 'socialnetwork_user')
				{
					$arComponentParameters['PARAMETERS'][$name . '_' . $strFILTER] = [
						'PARENT' => 'DATA_SOURCE',
						'NAME' => GetMessage('SEARCH_CP_SOCIALNETWORK_USER'),
						'TYPE' => 'STRING',
						'DEFAULT' => '',
					];
				}
			}
		}
	}

	protected static function _main($arParam)
	{
		if (is_array($arParam))
		{
			$arURL = [];
			foreach ($arParam as $strURL)
			{
				$strURL = trim($strURL);
				if ($strURL)
				{
					$arURL[] = $strURL . '%';
				}
			}

			if (count($arURL) > 0)
			{
				return [
					'=MODULE_ID' => 'main',
					'URL' => $arURL,
				];
			}
		}
		elseif (mb_strlen($arParam))
		{
			return [
				'=MODULE_ID' => 'main',
				'URL' => $arParam . '%',
			];
		}

		return [
			'=MODULE_ID' => 'main',
			'URL' => ['/%'],
		];
	}

	protected static function _forum($arParam)
	{
		if (is_array($arParam))
		{
			$arForum = [];
			foreach ($arParam as $strForum)
			{
				if ($strForum != 'all')
				{
					$arForum[] = intval($strForum);
				}
			}

			if (count($arForum) > 0)
			{
				return [
					'=MODULE_ID' => 'forum',
					'PARAM1' => $arForum,
				];
			}
		}
		elseif ($arParam > 0)
		{
			return [
				'=MODULE_ID' => 'forum',
				'PARAM1' => intval($arParam),
			];
		}

		return [
			'=MODULE_ID' => 'forum',
		];
	}

	protected static function _iblock($arParam, $strFILTER)
	{
		if (is_array($arParam))
		{
			$arIBlock = [];
			foreach ($arParam as $strIBlock)
			{
				if ($strIBlock != 'all')
				{
					$arIBlock[] = intval($strIBlock);
				}
			}

			if (count($arIBlock) > 0)
			{
				return [
					'=MODULE_ID' => 'iblock',
					'PARAM1' => mb_substr($strFILTER, 7),
					'PARAM2' => $arIBlock,
				];
			}
		}
		elseif ($arParam > 0)
		{
			return [
				'=MODULE_ID' => 'iblock',
				'PARAM1' => mb_substr($strFILTER, 7),
				'PARAM2' => intval($arParam),
			];
		}

		return [
			'=MODULE_ID' => 'iblock',
			'PARAM1' => mb_substr($strFILTER, 7),
		];
	}

	protected static function _blog($arParam)
	{
		if (is_array($arParam))
		{
			$arBlog = [];
			foreach ($arParam as $strBlog)
			{
				if ($strBlog != 'all')
				{
					$arBlog[] = intval($strBlog);
				}
			}

			if (count($arBlog) > 0)
			{
				return [
					'=MODULE_ID' => 'blog',
					'PARAM1' => 'POST',
					'PARAM2' => $arBlog,
				];
			}
		}
		elseif ($arParam > 0)
		{
			return [
				'=MODULE_ID' => 'blog',
				'PARAM1' => 'POST',
				'PARAM2' => intval($arParam),
			];
		}

		return [
			'=MODULE_ID' => 'blog',
			'!=PARAM1' => 'MICROBLOG',
		];
	}

	protected static function _socialnetwork($arParam)
	{
		if (is_array($arParam))
		{
			$arSCGroups = [];
			foreach ($arParam as $strSCGroup)
			{
				if ($strSCGroup != 'all')
				{
					$arSCGroups[] = intval($strSCGroup);
				}
			}

			if (count($arSCGroups) > 0)
			{
				return [
					'PARAMS' => ['socnet_group' => $arSCGroups],
					'USE_TF_FILTER' => false,
				];
			}
		}
		elseif ($arParam > 0)
		{
			return [
				'PARAMS' => ['socnet_group' => intval($arParam)],
			];
		}

		return [
			'=MODULE_ID' => 'socialnetwork',
			'PARAMS' => ['entity' => 'socnet_group'],
		];
	}

	protected static function _socialnetwork_user($arParam)
	{
		$intSCUser = intval($arParam);
		if ($intSCUser > 0)
		{
			return [
				'PARAMS' => ['socnet_user' => $intSCUser],
				'USE_TF_FILTER' => false,
			];
		}
		else
		{
			return [
				'=MODULE_ID' => 'socialnetwork',
			];
		}
	}
}
