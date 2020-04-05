<?
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();
/** @var CBitrixComponent $this */
/** @var array $arParams */
/** @var array $arResult */
/** @var string $componentPath */
/** @var string $componentName */
/** @var string $componentTemplate */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global CCacheManager $CACHE_MANAGER */
use Bitrix\Main\Loader;
use Bitrix\Iblock;
use Bitrix\Main;

if (!isset($arParams['CACHE_TIME']))
	$arParams['CACHE_TIME'] = 36000000;
$arParams['CACHE_GROUPS'] = trim($arParams['CACHE_GROUPS']);
if (!isset($arParams['CACHE_GROUPS']) || $arParams['CACHE_GROUPS'] != 'N')
	$arParams['CACHE_GROUPS'] = 'Y';

$arParams['IBLOCK_TYPE']= trim($arParams['IBLOCK_TYPE']);
$arParams['IBLOCK_ID'] = intval($arParams['IBLOCK_ID']);
$arParams['ELEMENT_ID'] = intval($arParams['ELEMENT_ID']);
$arParams['ELEMENT_CODE'] = ($arParams['ELEMENT_ID'] > 0 ? '' : trim($arParams['ELEMENT_CODE']));
$arParams['URL_TO_COMMENT'] = trim($arParams['URL_TO_COMMENT']);
$arParams['WIDTH'] = intval($arParams["WIDTH"]);
$arParams['COMMENTS_COUNT'] = intval($arParams['COMMENTS_COUNT']);
$arParams['SHOW_DEACTIVATED'] = (isset($arParams['SHOW_DEACTIVATED']) && $arParams['SHOW_DEACTIVATED'] == 'Y' ? 'Y' : 'N');
$arParams['CHECK_DATES'] = (isset($arParams['CHECK_DATES']) && $arParams['CHECK_DATES'] == 'N' ? 'N' : 'Y');
$arParams['BLOG_USE'] = (isset($arParams['BLOG_USE']) && $arParams['BLOG_USE'] === 'Y' ? 'Y' : 'N');
$arParams['FB_USE'] = (isset($arParams['FB_USE']) && $arParams['FB_USE'] === 'Y' ? 'Y' : 'N');
$arParams['VK_USE'] = (isset($arParams['VK_USE']) && $arParams['VK_USE'] === 'Y' ? 'Y' : 'N');
if ($arParams['BLOG_USE'] == 'Y')
{
	$arParams['BLOG_FROM_AJAX'] = (isset($arParams['BLOG_FROM_AJAX']) && $arParams['BLOG_FROM_AJAX'] == 'Y' ? 'Y' : 'N');
	$arParams['BLOG_TITLE'] = trim($arParams['BLOG_TITLE']);
	$arParams['BLOG_URL'] = trim($arParams['BLOG_URL']);
	if ($arParams['BLOG_URL'] === '')
		$arParams['BLOG_URL'] = 'catalog_comments';
	$arParams['PATH_TO_SMILE'] = trim($arParams['PATH_TO_SMILE']);
	if ($arParams['PATH_TO_SMILE'] === '')
		$arParams['PATH_TO_SMILE'] = '/bitrix/images/blog/smile/';
	$arParams['EMAIL_NOTIFY'] = (isset($arParams['EMAIL_NOTIFY']) && $arParams['EMAIL_NOTIFY'] == 'Y' ? 'Y' : 'N');
	$arParams['AJAX_POST'] = (isset($arParams['AJAX_POST']) && $arParams['AJAX_POST'] == 'Y' ? 'Y' : 'N');
	$arParams['SHOW_SPAM'] = (isset($arParams['SHOW_SPAM']) && $arParams['SHOW_SPAM'] == 'N' ? 'N' : 'Y');
	$arParams['SHOW_RATING'] = (isset($arParams['SHOW_RATING']) && $arParams['SHOW_RATING'] == 'Y' ? 'Y' : 'N');
	$arParams['RATING_TYPE'] = (isset($arParams['RATING_TYPE']) ? trim($arParams['RATING_TYPE']) : '');
}
else
{
	$arParams['BLOG_FROM_AJAX'] = 'N';
	$arParams['BLOG_TITLE'] = '';
	$arParams['BLOG_URL'] = 'catalog_comments';
	$arParams['PATH_TO_SMILE'] = '/bitrix/images/blog/smile/';
	$arParams['EMAIL_NOTIFY'] = 'N';
	$arParams['AJAX_POST'] = 'N';
	$arParams['SHOW_SPAM'] = 'N';
	$arParams['SHOW_RATING'] = 'N';
	$arParams['RATING_TYPE'] = '';
}
if ($arParams['BLOG_USE'] == 'Y' && $arParams['BLOG_FROM_AJAX'] == 'Y')
{
	$arParams['FB_USE'] = 'N';
	$arParams['VK_USE'] = 'N';
	$arParams['CACHE_GROUPS'] = 'Y';
}

if ($arParams['FB_USE'] == 'Y')
{
	$arParams['FB_TITLE'] = trim($arParams['FB_TITLE']);
	if ($arParams['FB_TITLE'] === '')
		$arParams['FB_TITLE'] = 'Facebook';
	$arParams['FB_USER_ADMIN_ID'] = trim($arParams['FB_USER_ADMIN_ID']);
	$arParams['FB_APP_ID'] = trim($arParams['FB_APP_ID']);
	$arParams['FB_COLORSCHEME'] = (isset($arParams['FB_COLORSCHEME']) && $arParams['FB_COLORSCHEME'] == 'dark' ? 'dark' : 'light');
	$arParams['FB_ORDER_BY'] = trim($arParams['FB_ORDER_BY']);
}
else
{
	$arParams['FB_TITLE'] = 'Facebook';
	$arParams['FB_USER_ADMIN_ID'] = '';
	$arParams['FB_APP_ID'] = '';
	$arParams['FB_COLORSCHEME'] = 'light';
	$arParams['FB_ORDER_BY'] = '';
}
if ($arParams['VK_USE'] == 'Y')
{
	$arParams['VK_TITLE'] = trim($arParams['VK_TITLE']);
	$arParams['VK_API_ID'] = trim($arParams['VK_API_ID']);
}
else
{
	$arParams['VK_TITLE'] = '';
	$arParams['VK_API_ID'] = '';
}

if ($this->StartResultCache(false, ($arParams['CACHE_GROUPS'] === 'N' ? false: $USER->GetGroups())))
{
	if (!Loader::includeModule("iblock"))
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_CSC_MODULE_NOT_INSTALLED"));
		return 0;
	}

	$arResultModules = array(
		'iblock' => true,
		'blog' => false
	);
	if ($arParams['BLOG_USE'] == 'Y')
		$arResultModules['blog'] = Loader::includeModule('blog');

	$arParams['BLOG_USE'] = ($arResultModules['blog'] ? 'Y' : 'N');
	$arResult['BLOG_USE'] = $arResultModules['blog'];
	$arResult['BLOG_FROM_AJAX'] = $arResult['BLOG_USE'] && ($arParams['BLOG_FROM_AJAX'] == 'Y');

	$arResult['ELEMENT'] = array();
	$arResult['ERRORS'] = array();
	$arResult['MODULES'] = $arResultModules;

	if ($arParams["ELEMENT_ID"] <= 0)
	{
		if ($arParams["ELEMENT_CODE"] !== '')
		{
			$findFilter = array(
				"IBLOCK_ID" => $arParams["IBLOCK_ID"],
				"IBLOCK_LID" => SITE_ID,
				"IBLOCK_ACTIVE" => "Y",
				"CHECK_PERMISSIONS" => "Y",
				"MIN_PERMISSION" => 'R'
			);
			if ($arParams['CHECK_DATES'] != 'N')
				$findFilter['ACTIVE_DATE'] = 'Y';
			if ($arParams["SHOW_DEACTIVATED"] !== "Y")
				$findFilter["ACTIVE"] = "Y";

			$arParams["ELEMENT_ID"] = CIBlockFindTools::GetElementID(
				$arParams["ELEMENT_ID"],
				$arParams["~ELEMENT_CODE"],
				false,
				false,
				$findFilter
			);
		}
	}
	if($arParams["ELEMENT_ID"] > 0)
	{
		$blogGroupID = 0;
		$blogID = 0;
		$propBlogPostID = 0;
		$propBlogCommentsCountID = 0;
		$arResult['BLOG_DATA'] = array(
			'BLOG_URL' => $arParams['BLOG_URL'],
			'BLOG_ID' => 0,
			'BLOG_POST_ID_PROP' => 0,
			'BLOG_COMMENTS_COUNT_PROP' => 0,
			'BLOG_POST_ID' => 0,
			'IBLOCK_SITES' => array()
		);

		if ($arResultModules['blog'])
		{
			$siteIterator = Iblock\IblockSiteTable::getList(array(
				'select' => array('SITE_ID'),
				'filter' => array('=IBLOCK_ID' => $arParams['IBLOCK_ID'])
			));
			while ($iblockSite = $siteIterator->fetch())
				$arResult['BLOG_DATA']['IBLOCK_SITES'][] = $iblockSite['SITE_ID'];
			unset($iblockSite, $siteIterator);

			$newBlog = false;
			$blogExist = false;
			$blogGroupExist = false;
			$blogIterator = CBlog::GetList(
				array(),
				array('URL' => $arResult['BLOG_DATA']['BLOG_URL']),
				false,
				false,
				array('ID', 'GROUP_ID', 'EMAIL_NOTIFY', 'GROUP_SITE_ID')
			);
			if ($blog = $blogIterator->Fetch())
			{
				if ($blog['GROUP_SITE_ID'] == SITE_ID || in_array($blog['GROUP_SITE_ID'], $arResult['BLOG_DATA']['IBLOCK_SITES']))
				{
					$blogExist = true;
					$blogGroupExist = true;
				}
				else
				{
					$newBlog = true;
					$arResult['BLOG_DATA']['BLOG_URL'] .= '_'.SITE_ID;
				}
			}
			unset($blogIterator);
			if (!$blogExist)
			{
				$blogIterator = CBlog::GetList(
					array(),
					array('URL' => $arResult['BLOG_DATA']['BLOG_URL']),
					false,
					false,
					array('ID', 'GROUP_ID', 'EMAIL_NOTIFY', 'GROUP_SITE_ID')
				);
				if ($blog = $blogIterator->Fetch())
				{
					if ($blog['GROUP_SITE_ID'] == SITE_ID || in_array($blog['GROUP_SITE_ID'], $arResult['BLOG_DATA']['IBLOCK_SITES']))
					{
						$blogExist = true;
						$blogGroupExist = true;
					}
					else
					{
						$newBlog = true;
						$arResult['BLOG_DATA']['BLOG_URL'] .= '_'.$this->randString();
					}
				}
				unset($blogIterator);
			}
			if ($blogGroupExist)
			{
				$blogGroupID = (int)$blog['GROUP_ID'];
				$blogID = (int)$blog['ID'];
			}
			else
			{
				if ($arParams['BLOG_FROM_AJAX'] === 'N')
				{
					$fields = array(
						'SITE_ID' => SITE_ID,
						'NAME' => GetMessage('IBLOCK_CSC_BLOG_GROUP_NAME')
					);
					$blogGroupIterator = CBlogGroup::GetList(array(), $fields, false, false, array('ID'));
					if ($blogGroup = $blogGroupIterator->Fetch())
					{
						$blogGroupID = (int)$blogGroup['ID'];
					}
					else
					{
						$blogGroupID = (int)CBlogGroup::Add($fields);
						if ($blogGroupID == 0)
						{
							if ($ex = $APPLICATION->GetException())
								$arResult["ERRORS"][] = $ex->GetString();
							else
								$arResult["ERRORS"][] = GetMessage("IBLOCK_CSC_BLOG_GROUP_CREATE_ERROR");
						}
					}
					unset($fields);
					if ($blogGroupID > 0)
					{
						if (!$blogExist)
						{
							$fields = array(
								"NAME" => GetMessage("IBLOCK_CSC_BLOG_NAME"),
								"DESCRIPTION" => GetMessage("IBLOCK_CSC_BLOG_DESCRIPTION"),
								"GROUP_ID" => $blogGroupID,
								"ENABLE_COMMENTS" => 'Y',
								"ENABLE_IMG_VERIF" => 'Y',
								"EMAIL_NOTIFY" => $arParams['EMAIL_NOTIFY'],
								"URL" => $arResult['BLOG_DATA']['BLOG_URL'],
								"ACTIVE" => "Y",
								"OWNER_ID" => 1,
								"SEARCH_INDEX" => "N",
								"AUTO_GROUPS" => "N",
								"PERMS_POST" => array(
									1 => BLOG_PERMS_READ,
									2 => BLOG_PERMS_READ
								),
								"PERMS_COMMENT" => array(
									1 => BLOG_PERMS_WRITE,
									2 => BLOG_PERMS_WRITE
								),
								"=DATE_CREATE" => $DB->GetNowFunction(),
								"=DATE_UPDATE" => $DB->GetNowFunction()
							);

							$blogID = (int)CBlog::Add($fields);
							unset($fields);

							if ($blogID == 0)
							{
								if ($ex = $APPLICATION->GetException())
									$arResult["ERRORS"][] = $ex->GetString();
								else
									$arResult["ERRORS"][] = GetMessage("IBLOCK_CSC_BLOG_CREATE_ERROR");
							}
						}
					}
				}
			}
			if ($blogExist)
			{
				if ($arParams['BLOG_FROM_AJAX'] === 'N')
				{
					if ($blog['EMAIL_NOTIFY'] != $arParams['EMAIL_NOTIFY'])
						CBlog::Update($blogID, array('EMAIL_NOTIFY' => $arParams['EMAIL_NOTIFY']));
				}
			}

			if ($blogID > 0)
			{
				$arResult['BLOG_DATA']['BLOG_ID'] = $blogID;
				$propertyIterator = Iblock\PropertyTable::getList(array(
					'select' => array('ID', 'CODE'),
					'filter' => array(
						'=IBLOCK_ID' => $arParams['IBLOCK_ID'],
						'=PROPERTY_TYPE' => Iblock\PropertyTable::TYPE_NUMBER,
						'=MULTIPLE' => 'N',
						'=CODE' => array(CIBlockPropertyTools::CODE_BLOG_POST, CIBlockPropertyTools::CODE_BLOG_COMMENTS_COUNT)
					)
				));
				while ($propIBlock = $propertyIterator->fetch())
				{
					if ($propIBlock['CODE'] == CIBlockPropertyTools::CODE_BLOG_POST)
						$propBlogPostID = (int)$propIBlock['ID'];
					elseif ($propIBlock['CODE'] == CIBlockPropertyTools::CODE_BLOG_COMMENTS_COUNT)
						$propBlogCommentsCountID = (int)$propIBlock['ID'];
				}
				unset($propIBlock, $propertyIterator);
				if (($propBlogPostID == 0 || $propBlogCommentsCountID == 0) && $arParams['BLOG_FROM_AJAX'] === 'N')
				{
					if ($propBlogPostID == 0)
					{
						$propBlogPostID = (int)CIBlockPropertyTools::createProperty(
							$arParams['IBLOCK_ID'],
							CIBlockPropertyTools::CODE_BLOG_POST
						);
						if ($propBlogPostID == 0)
						{
							$arResult['ERRORS'] = array_merge($arResult['ERRORS'], CIBlockPropertyTools::getErrors());
							CIBlockPropertyTools::clearErrors();
						}
					}
					if ($propBlogCommentsCountID == 0)
					{
						$propBlogCommentsCountID = (int)CIBlockPropertyTools::createProperty(
							$arParams['IBLOCK_ID'],
							CIBlockPropertyTools::CODE_BLOG_COMMENTS_COUNT
						);
						if ($propBlogCommentsCountID == 0)
						{
							$arResult['ERRORS'] = array_merge($arResult['ERRORS'], CIBlockPropertyTools::getErrors());
							CIBlockPropertyTools::clearErrors();
						}
					}
				}
				$arResult['BLOG_DATA']['BLOG_POST_ID_PROP'] = $propBlogPostID;
				$arResult['BLOG_DATA']['BLOG_COMMENTS_COUNT_PROP'] = $propBlogCommentsCountID;
			}
			if (
				$arResult['BLOG_DATA']['BLOG_ID'] == 0
				|| $arResult['BLOG_DATA']['BLOG_POST_ID_PROP'] == 0
				|| $arResult['BLOG_DATA']['BLOG_COMMENTS_COUNT_PROP'] == 0
			)
			{
				$arResult['BLOG_USE'] = false;
				$arResult['BLOG_FROM_AJAX'] = false;
			}
		}

		$arSelect = array(
			"ID",
			"IBLOCK_ID",
			"NAME",
			"PREVIEW_TEXT",
			"DETAIL_PAGE_URL",
			"PREVIEW_TEXT_TYPE",
			"DATE_CREATE",
			"CREATED_BY"
		);
		if ($arResult['BLOG_USE'])
		{
			$arSelect[] = 'PROPERTY_'.$arResult['BLOG_DATA']['BLOG_POST_ID_PROP'];
			$arSelect[] = 'PROPERTY_'.$arResult['BLOG_DATA']['BLOG_COMMENTS_COUNT_PROP'];
		}

		$arFilter = array(
			"ID" => $arParams["ELEMENT_ID"],
			"IBLOCK_ID" => $arParams["IBLOCK_ID"],
			"IBLOCK_LID" => SITE_ID,
			"IBLOCK_ACTIVE" => "Y",
			"CHECK_PERMISSIONS" => "Y",
			"MIN_PERMISSION" => 'R',
			"SHOW_HISTORY" => "Y"
		);
		if ($arParams['CHECK_DATES'] != 'N')
			$arFilter['ACTIVE_DATE'] = 'Y';
		if ($arParams["SHOW_DEACTIVATED"] !== "Y")
			$arFilter["ACTIVE"] = "Y";

		$rsElement = CIBlockElement::GetList(
			array(),
			$arFilter,
			false,
			false,
			$arSelect
		);
		if ($arElement = $rsElement->GetNext())
		{
			$arResult['ELEMENT'] = $arElement;
			if ($arResult['BLOG_USE'])
			{
				$postID = (int)$arElement['PROPERTY_'.$arResult['BLOG_DATA']['BLOG_POST_ID_PROP'].'_VALUE'];
				$commentsCount = (int)$arElement['PROPERTY_'.$arResult['BLOG_DATA']['BLOG_COMMENTS_COUNT_PROP'].'_VALUE'];
				if ($postID > 0)
				{
					$rsPosts = CBlogPost::GetList(
						array(),
						array('ID' => $postID, 'BLOG_ID' => $arResult['BLOG_DATA']['BLOG_ID']),
						false,
						false,
						array('ID', 'BLOG_ID', 'NUM_COMMENTS')
					);
					if ($postInfo = $rsPosts->Fetch())
					{
						$postInfo['NUM_COMMENTS'] = (int)$postInfo['NUM_COMMENTS'];
						if ($postInfo['NUM_COMMENTS'] >= 0 && $postInfo['NUM_COMMENTS'] != $commentsCount)
						{
							CIBlockElement::SetPropertyValues($arResult['ELEMENT']['ID'], $arResult['ELEMENT']['IBLOCK_ID'], $postInfo['NUM_COMMENTS'], $arResult['BLOG_DATA']['BLOG_COMMENTS_COUNT_PROP']);
							$commentsCount = $postInfo['NUM_COMMENTS'];
						}
					}
					else
					{
						$postID = 0;
					}
					unset($rsPosts);
				}
				if ($postID == 0 && $arParams['BLOG_FROM_AJAX'] === 'N')
				{
					$ownerID = 1;
					if (!empty($arResult['ELEMENT']['CREATED_BY']))
					{
						$ownersIterator = Main\UserTable::getList(array(
							'select' => array('ID'),
							'filter' => array('=ID' => $arResult['ELEMENT']['CREATED_BY'])
						));
						if ($owner = $ownersIterator->fetch())
							$ownerID = $owner['ID'];
						unset($owner, $ownersIterator);
					}

					$arFields = array(
						'TITLE' => $arResult['ELEMENT']['~NAME'],
						'DETAIL_TEXT' =>
							"[URL=http://".$_SERVER['HTTP_HOST'].$arResult["ELEMENT"]["~DETAIL_PAGE_URL"]."]".$arResult["ELEMENT"]["~NAME"]."[/URL]\n".
							($arResult["ELEMENT"]["~PREVIEW_TEXT"] != '' ? $arResult["ELEMENT"]["~PREVIEW_TEXT"] : '')."\n",
						'PUBLISH_STATUS' => BLOG_PUBLISH_STATUS_PUBLISH,
						"PERMS_POST" => array(),
						"PERMS_COMMENT" => array(),
						"=DATE_CREATE" => $DB->GetNowFunction(),
						"=DATE_PUBLISH" => $DB->GetNowFunction(),
						"AUTHOR_ID" => $ownerID,
						"BLOG_ID" => $arResult['BLOG_DATA']['BLOG_ID'],
						"ENABLE_TRACKBACK" => "N"
					);
					$postID = (int)CBlogPost::Add($arFields);
					if ($postID > 0)
						CIBlockElement::SetPropertyValues($arResult['ELEMENT']['ID'], $arResult['ELEMENT']['IBLOCK_ID'], $postID, $arResult['BLOG_DATA']['BLOG_POST_ID_PROP']);
				}
				$arResult['BLOG_DATA']['BLOG_POST_ID'] = $postID;
				$arResult['COMMENT_ID'] = $postID;
			}

			$protocol = (CMain::IsHTTPS()) ? 'https://' : 'http://';

			if ($arParams['URL_TO_COMMENT'] !== '')
				$arResult['URL_TO_COMMENT'] = $arParams['URL_TO_COMMENT'];
			elseif (!empty($arResult['ELEMENT']['~DETAIL_PAGE_URL']))
				$arResult['URL_TO_COMMENT'] = $protocol.$_SERVER["HTTP_HOST"].$arResult['ELEMENT']['~DETAIL_PAGE_URL'];
			else
				$arResult['URL_TO_COMMENT'] = $protocol.$_SERVER["HTTP_HOST"].$_SERVER["REQUEST_URI"];

			if(!isset($arParams["AJAX_POST"]) || trim($arParams["AJAX_POST"]) == "")
				$arParams["AJAX_POST"] = 'N';

			if($arParams["WIDTH"] > 0)
				$arResult["WIDTH"] = $arParams["WIDTH"];

			$this->IncludeComponentTemplate();
		}
		else
		{
			$this->AbortResultCache();
			ShowError(GetMessage('IBLOCK_CSC_ELEMENT_NOT_FOUND'));
			return 0;
		}
	}
	else
	{
		$this->AbortResultCache();
		ShowError(GetMessage("IBLOCK_CSC_ELEMENT_NOT_FOUND"));
		return 0;
	}
}