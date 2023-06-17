<?php
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */
use Bitrix\Main;
use Bitrix\Main\Context;
use Bitrix\Main\Loader;
use Bitrix\Iblock;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
Loader::includeModule("iblock");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/iblock/prolog.php");
IncludeModuleLangFile(__FILE__);

$request = Context::getCurrent()->getRequest();

$type = (string)$request->get('type');

$arIBTYPE = CIBlockType::GetByIDLang($type, LANGUAGE_ID);
if ($arIBTYPE === false)
{
	LocalRedirect("/bitrix/admin/iblock_type_admin.php?lang=" . LANGUAGE_ID);
}

$bBizproc = Loader::includeModule("bizproc");
$bWorkflow = Loader::includeModule("workflow");

$isAdminSection = $request->get('admin') === 'Y';

$sTableID =
	$isAdminSection
		? "tbl_iblock_admin_" . md5($type)
		: "tbl_iblock_".md5($type)
;

$oSort = new CAdminUiSorting($sTableID, "TIMESTAMP_X", "DESC");
$by = mb_strtoupper($oSort->getField());
$order = mb_strtoupper($oSort->getOrder());
$arOrder =
	$by === "ID"
		? [$by => $order]
		: [$by => $order, "ID" => "ASC"]
;

$lAdmin = new CAdminUiList($sTableID, $oSort);

/* Prepare data for new filter */
$listLang = [];
$siteIterator = Main\SiteTable::getList([
	'select' => [
		'ID',
		'NAME',
		'SORT',
	],
	'order' => [
		'SORT' => 'ASC',
		'ID' => 'ASC',
	]
]);
while ($row = $siteIterator->fetch())
{
	$listLang[$row['ID']] = htmlspecialcharsbx($row['NAME']);
}
unset($row, $siteIterator);

$filterFields = [
	[
		'id' => 'ID',
		'name' => GetMessage('IBLOCK_ADM_FILT_ID'),
		'type' => 'number',
	],
	[
		"id" => "NAME",
		"name" => GetMessage("IBLOCK_ADM_FILT_NAME"),
		"filterable" => "?",
		"quickSearch" => "?",
		"default" => true,
	],
	[
		"id" => "LID",
		"name" => GetMessage("IBLOCK_ADM_FILT_SITE"),
		"type" => "list",
		"items" => $listLang,
		"filterable" => "",
	],
	[
		"id" => "ACTIVE",
		"name" => GetMessage("IBLOCK_ADM_FILT_ACT"),
		"type" => "list",
		"items" => [
			"Y" => GetMessage("IBLOCK_YES"),
			"N" => GetMessage("IBLOCK_NO")
		],
		"filterable" => "",
	],
	[
		"id" => "CODE",
		"name" => GetMessage("IBLOCK_FIELD_CODE"),
		"filterable" => "?",
	],
];

$arFilter = array(
	"TYPE" => $type,
	"MIN_PERMISSION" => "U",
	"CNT_ALL" => "Y",
);
$lAdmin->AddFilter($filterFields, $arFilter);

if ($lAdmin->EditAction())
{
	foreach($lAdmin->GetEditFields() as $ID => $postFields)
	{
		$ID = (int)$ID;

		if (!CIBlockRights::UserHasRightTo($ID, $ID, "iblock_edit"))
		{
			continue;
		}

		$allowedFields = [
			"NAME" => true,
			"SORT" => true,
			"ACTIVE" => true,
			"LIST_PAGE_URL" => true,
			"DETAIL_PAGE_URL" => true,
			"CANONICAL_PAGE_URL" => true,
			"CODE" => true,
			"INDEX_ELEMENT" => true,
			"WORKFLOW" => true,
		];
		$arFields = array_intersect_key($postFields, $allowedFields);
		if (empty($arFields))
		{
			continue;
		}

		$DB->StartTransaction();

		$ib = new CIBlock;
		if (!$ib->Update($ID, $arFields))
		{
			$lAdmin->AddUpdateError(
				GetMessage(
					"IBLOCK_ADM_SAVE_ERROR",
					[
						"#ID#"=>$ID,
						"#ERROR_TEXT#"=>$ib->LAST_ERROR,
					]
				),
				$ID
			);
			$DB->Rollback();
		}
		else
		{
			$DB->Commit();
		}
	}
}

$arID = $lAdmin->GroupAction();
if ($arID)
{
	if($lAdmin->IsGroupActionToAll())
	{
		$rsIBlocks = CIBlock::GetList($arOrder, $arFilter);
		while($arRes = $rsIBlocks->Fetch())
		{
			$arID[] = $arRes['ID'];
		}
		unset($rsIBlocks);
	}

	$action = $lAdmin->GetAction();
	$eventLogIblock = Main\Config\Option::get('iblock', 'event_log_iblock') === 'Y';
	foreach ($arID as $ID)
	{
		$ID = (int)$ID;
		if ($ID <= 0)
		{
			continue;
		}

		switch ($action)
		{
			case Iblock\Grid\ActionType::DELETE:
				if (!CIBlockRights::UserHasRightTo($ID, $ID, "iblock_delete"))
				{
					break;
				}
				@set_time_limit(0);
				$DB->StartTransaction();
				$rsIBlock = CIBlock::GetByID($ID);
				$arIBlock = $rsIBlock->GetNext();
				if (!CIBlock::Delete($ID))
				{
					$DB->Rollback();
					$lAdmin->AddGroupError(GetMessage("IBLOCK_ADM_DELETE_ERROR"), $ID);
				}
				else
				{
					if ($eventLogIblock)
					{
						CEventLog::Log(
							"IBLOCK",
							"IBLOCK_DELETE",
							"iblock",
							$ID,
							serialize([
								"NAME" => $arIBlock["NAME"],
							])
						);
					}
					$DB->Commit();
				}
				break;
			case Iblock\Grid\ActionType::ACTIVATE:
			case Iblock\Grid\ActionType::DEACTIVATE:
				if (!CIBlockRights::UserHasRightTo($ID, $ID, "iblock_delete"))
				{
					break;
				}
				$ob = new CIBlock();
				$arFields = [
					"ACTIVE" => ($action === Iblock\Grid\ActionType::ACTIVATE ? "Y" :"N"),
				];
				if (!$ob->Update($ID, $arFields))
				{
					$lAdmin->AddGroupError(GetMessage("IBLOCK_ADM_UPD_ERROR") . $ob->LAST_ERROR, $ID);
				}
				break;
		}
	}
}

$arHeader = [
	[
		"id"=>"NAME",
		"content"=>GetMessage("IBLOCK_ADM_NAME"),
		"sort"=>"name",
		"default"=>true,
	],
	[
		"id"=>"SORT",
		"content"=>GetMessage("IBLOCK_ADM_SORT"),
		"sort"=>"sort",
		"default"=>true,
		"align"=>"right",
	],
	[
		"id"=>"ACTIVE",
		"content"=>GetMessage("IBLOCK_ADM_ACTIVE"),
		"sort"=>"active",
		"default"=>true,
		"align"=>"center",
	],
	[
		"id"=>"CODE",
		"content"=>GetMessage("IBLOCK_FIELD_CODE"),
		"sort"=>"code",
	],
	[
		"id"=>"LIST_PAGE_URL",
		"content"=>GetMessage("IBLOCK_ADM_HEADER_LIST_URL"),
	],
	[
		"id"=>"DETAIL_PAGE_URL",
		"content"=>GetMessage("IBLOCK_ADM_HEADER_DETAIL_URL"),
	],
	[
		"id"=>"CANONICAL_PAGE_URL",
		"content"=>GetMessage("IBLOCK_ADM_HEADER_CANONICAL_PAGE_URL"),
	],
	[
		"id"=>"ELEMENT_CNT",
		"content"=>GetMessage("IBLOCK_ADM_HEADER_EL"),
		"default"=>true,
		"align"=>"right",
	],
];

if($arIBTYPE["SECTIONS"]=="Y")
	$arHeader[] = array(
		"id"=>"SECTION_CNT",
		"content"=>GetMessage("IBLOCK_ADM_HEADER_SECT"),
		"default"=>true,
		"align"=>"right",
	);

$arHeader[] = array(
	"id"=>"LID",
	"content"=>GetMessage("IBLOCK_ADM_LANG"),
	"sort"=>"lid",
	"default"=>true,
	"align"=>"left",
);
$arHeader[] = array(
	"id"=>"INDEX_ELEMENT",
	"content"=>GetMessage("IBLOCK_ADM_HEADER_TOINDEX"),
);
if($bWorkflow)
	$arHeader[] = array(
		"id"=>"WORKFLOW",
		"content"=>GetMessage("IBLOCK_ADM_HEADER_WORKFLOW"),
	);
$arHeader[] = array(
	"id"=>"TIMESTAMP_X",
	"content"=>GetMessage("IBLOCK_ADM_TIMESTAMP"),
	"sort"=>"timestamp_x",
	"default"=>true,
);
$arHeader[] = array(
	"id"=>"ID",
	"content"=>"ID",
	"sort"=>"id",
	"default"=>true,
	"align"=>"right",
);
if($bBizproc && IsModuleInstalled("bizprocdesigner"))
	$arHeader[] = array(
		"id"=>"WORKFLOW_TEMPLATES",
		"content"=>GetMessage("IBLOCK_ADM_HEADER_BIZPROC"),
		"default"=>true,
		"align"=>"right",
	);

$lAdmin->AddHeaders($arHeader);

$rsIBlocks = CIBlock::GetList($arOrder, $arFilter, false);
$rsIBlocks = new CAdminUiResult($rsIBlocks, $sTableID);
$rsIBlocks->NavStart();

$lAdmin->SetNavigationParams($rsIBlocks);

$urlBuilderManager = Iblock\Url\AdminPage\BuilderManager::getInstance();
$urlBuilder = $urlBuilderManager->getBuilder(Iblock\Url\AdminPage\IblockBuilder::TYPE_ID);

$visibleColumns = array_fill_keys($lAdmin->GetVisibleHeaderColumns(), true);

while ($iblock = $rsIBlocks->Fetch())
{
	$iblock['ID'] = (int)$iblock['ID'];

	$urlBuilder->setIblockId($iblock['ID']);

	$iblockEditRight = CIBlockRights::UserHasRightTo($iblock['ID'], $iblock['ID'], 'iblock_edit');
	$allowIblockEdit =
		$isAdminSection
		&& $iblockEditRight
	;
	$allowIblockDelete =
		$isAdminSection
		&& CIBlockRights::UserHasRightTo($iblock['ID'], $iblock['ID'], "iblock_delete")
	;

	$allowSections = $arIBTYPE['SECTIONS'] === 'Y';

	$urlOptions =
		$allowSections
			? ['find_section_section' => 0]
			: ['find_section_section' => -1]
	;
	$listUrl = $urlBuilder->getElementListUrl(0, $urlOptions);

	$encodedCurrentPage = urlencode($APPLICATION->GetCurPageParam());

	if ($allowIblockEdit)
	{
		$row =& $lAdmin->AddRow(
			$iblock['ID'],
			$iblock,
			'iblock_edit.php?ID=' . $iblock['ID'] . '&type=' . htmlspecialcharsbx($type)
				.'&lang=' . LANGUAGE_ID . '&admin=' . ($isAdminSection ? 'Y': 'N')
			,
			GetMessage("IBLOCK_ADM_TO_EDIT")
		);
	}
	else
	{
		$urlOptions =
			$arIBTYPE["SECTIONS"] === "Y"
				? ['find_section_section' => 0]
				: ['find_section_section' => -1]
		;

		$row =& $lAdmin->AddRow(
			$iblock['ID'],
			$iblock,
			$listUrl,
			GetMessage("IBLOCK_ADM_TO_EL_LIST")
		);
	}

	if (isset($visibleColumns['LID']))
	{
		$siteList = [];
		$siteIterator = Iblock\IblockSiteTable::getList([
			'select' => [
				'SITE_ID',
			],
			'filter' => [
				'=IBLOCK_ID' => $iblock['ID'],
			]
		]);
		while ($site = $siteIterator->fetch())
		{
			$siteList[] = $site['SITE_ID'];
		}
		unset($site, $siteIterator);

		$row->AddViewField('LID', htmlspecialcharsbx(implode(' / ', $siteList)));
		unset($siteList);
	}

	if ($allowIblockEdit)
	{
		$row->AddViewField("ID", $iblock['ID']);

		$row->AddInputField("NAME", ["size"=>"35"]);
		$row->AddViewField(
			"NAME",
			'<a href="iblock_edit.php?ID=' . $iblock['ID'] . '&type='.htmlspecialcharsbx($type)
				. '&lang=' . LANGUAGE_ID . '&admin=' . ($isAdminSection ? 'Y': 'N')
				. '" title="' . GetMessage("IBLOCK_ADM_TO_EDIT").'">' . htmlspecialcharsbx($iblock['NAME']) . '</a>'
		);

		$row->AddInputField("SORT", ["size"=>"3"]);
		$row->AddCheckField("ACTIVE");
		$row->AddInputField("CODE");
		$row->AddInputField("LIST_PAGE_URL");
		$row->AddInputField("DETAIL_PAGE_URL");
		$row->AddInputField("CANONICAL_PAGE_URL");
		$row->AddCheckField("INDEX_ELEMENT");
		if ($bWorkflow)
		{
			$row->AddCheckField("WORKFLOW");
		}
	}
	else
	{
		$row->AddViewField(
			'NAME',
			'<a href="' . htmlspecialcharsbx($listUrl)
				. '" title="' . GetMessage('IBLOCK_ADM_TO_ELLIST')
				. '">' . htmlspecialcharsbx($iblock['NAME']) . '</a>'
		);
		$row->AddCheckField("ACTIVE", false);
		$row->AddCheckField("INDEX_ELEMENT", false);
		if ($bWorkflow)
		{
			$row->AddCheckField("WORKFLOW", false);
		}
	}

	if (isset($visibleColumns['ELEMENT_CNT']))
	{
		$row->AddViewField(
			'ELEMENT_CNT',
			'<a href="' . htmlspecialcharsbx($listUrl)
				. '" title="' . GetMessage('IBLOCK_ADM_TO_ELLIST') . '">'
				. CIBlock::GetElementCount($iblock['ID']) . '</a>'
		);
	}

	if ($allowSections && isset($visibleColumns['SECTION_CNT']))
	{
		$sectionUrl = $urlBuilder->getSectionListUrl(0);
		$row->AddViewField(
			'SECTION_CNT',
			'<a href="' . htmlspecialcharsbx($sectionUrl)
				. '" title="' . GetMessage("IBLOCK_ADM_TO_SECTLIST")
				. '">' .CIBlockSection::GetCount(['IBLOCK_ID' => $iblock['ID']])
				. '</a>'
		);
	}

	if(
		$bBizproc
		&& $iblock["BIZPROC"] == "Y"
		&& isset($visibleColumns['WORKFLOW_TEMPLATES'])
		&& IsModuleInstalled("bizprocdesigner")
	)
	{
		$cnt = CBPDocument::GetNumberOfWorkflowTemplatesForDocumentType(
			array("iblock", "CIBlockDocument", "iblock_".$iblock['ID'])
		);
		$row->AddViewField(
			"WORKFLOW_TEMPLATES",
			'<a href="/bitrix/admin/iblock_bizproc_workflow_admin.php?document_type=iblock_' . $iblock['ID']
				.'&lang=' . LANGUAGE_ID
				. '&back_url_list=' . $encodedCurrentPage . '">'
				. $cnt . '</a>'
		);
	}

	$arActions = [];

	if ($allowIblockEdit)
	{
		$arActions[] = [
			"ICON" => "edit",
			"TEXT" => GetMessage("MAIN_ADMIN_MENU_EDIT"),
			"DEFAULT" => $isAdminSection,
			"ACTION" => $lAdmin->ActionRedirect(
				"iblock_edit.php?ID=" . $iblock['ID'] . "&type=" . urlencode($type)
					. "&lang=" . LANGUAGE_ID . "&admin=" . ($isAdminSection ? "Y": "N")
			),
		];
		$arActions[] = [
			"ICON" => "list",
			"TEXT" => GetMessage("IBLOCK_ADM_MENU_PROPERTIES"),
			"ACTION" => $lAdmin->ActionRedirect(
				"iblock_property_admin.php?IBLOCK_ID=" . $iblock['ID'] . "&lang=" . LANGUAGE_ID
					. ($isAdminSection ? "&admin=Y" : "&admin=N")
			),
		];
	}

	if(
		$bBizproc
		&& $iblock["BIZPROC"] == "Y"
		&& $iblockEditRight
		&& IsModuleInstalled("bizprocdesigner")
	)
	{
		$arActions[] = [
			"ICON" => "",
			"TEXT" => GetMessage("IBLOCK_ADM_MENU_BIZPROC"),
			"ACTION" =>
				'window.location="/bitrix/admin/iblock_bizproc_workflow_admin.php?document_type=iblock_' . $iblock['ID']
					. '&lang=' . LANGUAGE_ID
					. '&back_url_list=' . $encodedCurrentPage . '";'
		];
	}

	if ($allowIblockDelete)
	{
		$arActions[] = [
			"ICON" => "delete",
			"TEXT" => GetMessage("MAIN_ADMIN_MENU_DELETE"),
			"ACTION" =>
				"if (confirm('" . GetMessageJS("IBLOCK_ADM_CONFIRM_DEL_MESSAGE") . "')) "
					. $lAdmin->ActionDoGroup(
						$iblock['ID'],
						"delete",
						"&type=" . htmlspecialcharsbx($type)
							. "&lang=" . LANGUAGE_ID
							."&admin=" . ($isAdminSection? "Y": "N")
					)
			,
		];
	}

	if (!empty($arActions))
	{
		$row->AddActions($arActions);
	}
}

$lAdmin->AddFooter([
	[
		"title" => GetMessage("MAIN_ADMIN_LIST_SELECTED"),
		"value" => $rsIBlocks->SelectedRowsCount(),
	],
	[
		"counter" => true,
		"title" => GetMessage("MAIN_ADMIN_LIST_CHECKED"),
		"value" => "0",
	],
]);

if ($USER->IsAdmin() && $isAdminSection)
{
	$aContext = [
		[
			"ICON" => "btn_new",
			"TEXT" => GetMessage("IBLOCK_ADM_TO_ADDIBLOCK"),
			"LINK" => "iblock_edit.php?lang=" . LANGUAGE_ID . "&admin=Y&type=" . urlencode($type),
			"TITLE" => GetMessage("IBLOCK_ADM_TO_ADDIBLOCK_TITLE"),
		],
	];

	$lAdmin->AddAdminContextMenu($aContext);

	$lAdmin->AddGroupActionTable([
		"edit" => true,
		"delete" => true,
		"activate" => GetMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => GetMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	]);
}
else
{
	$lAdmin->AddAdminContextMenu([]);
}

$lAdmin->CheckListMode();

$APPLICATION->SetTitle(GetMessage(
	"IBLOCK_ADM_TITLE",
	["#IBLOCK_TYPE#" => $arIBTYPE["~NAME"]]
));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayFilter($filterFields);
$lAdmin->DisplayList();

if (!$isAdminSection):
	echo
		BeginNote(),
		GetMessage("IBLOCK_ADM_MANAGE_HINT"),
		' <a href="iblock_admin.php?type='.htmlspecialcharsbx($type).'&amp;lang='.LANGUAGE_ID.'&amp;admin=Y">',
		GetMessage("IBLOCK_ADM_MANAGE_HINT_HREF"),
		'</a>.',
		EndNote()
	;
endif;

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
