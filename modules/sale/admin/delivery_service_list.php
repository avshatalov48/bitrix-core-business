<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\Loader::includeModule('sale');
Loc::loadMessages(__FILE__);

$publicMode = $adminPage->publicMode || $adminSidePanelHelper->isPublicSidePanel();
$selfFolderUrl = $adminPage->getSelfFolderUrl();

/** @var  CMain $APPLICATION */
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_DSL_ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/delivery.js");
$sTableID = "tbl_sale_delivery_list";
$oSort = new CAdminUiSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);
$adminNotes = array();

//Base::isHandlerCompatible() - small temporary hack usage to know if we can use locations.
if((int)\Bitrix\Main\Config\Option::get('sale', 'location', 0) <= 0 && \Bitrix\Sale\Delivery\Services\Base::isHandlerCompatible())
{
	$settingsUrl = ($publicMode ? "/crm/configs/sale/?type=common" : "/bitrix/admin/settings.php?lang=".LANGUAGE_ID."&mid=sale");
	$adminNotes[] = Loc::getMessage('SALE_SDL_LOCATION_NOTE');
}

global $by, $order;
if(!isset($by))
	$by = 'ID';
if(!isset($order))
	$order = 'ASC';

$groupId = intval(isset($filter_group) && (isset($apply_filter) ||  $apply_filter == 'Y') ? $filter_group : -1);

$handlersList = \Bitrix\Sale\Delivery\Services\Manager::getHandlersList();
$listTypes = array();
foreach ($handlersList as $handlerClass)
{
	if (is_callable($handlerClass."::getClassTitle"))
	{
		$listTypes[$handlerClass] = $handlerClass::getClassTitle();
	}
}
$groups = array(
	"" => GetMessage("SALE_SDL_ALL"),
	"0" => GetMessage("SALE_SDL_UPPER_LEVELL")
);
$groupsQueryObject = \Bitrix\Sale\Delivery\Services\Table::getList(array(
	"filter" => array("=CLASS_NAME" => '\Bitrix\Sale\Delivery\Services\Group'),
	"select" => array("ID", "NAME", "PARENT_ID"),
	"order" => array("PARENT_ID" => "ASC", "NAME" => "ASC")
));
while ($group = $groupsQueryObject->fetch())
{
	$groups[$group["ID"]] = $group;
}
$sitesList = array();
$db = \Bitrix\Main\SiteTable::getList(array('filter' => array('ACTIVE' => 'Y'), 'order' => array('SORT' => 'ASC')));
while($site = $db->fetch())
	$sitesList[$site['LID']] = $site['NAME'];

$filterFields = array(
	array(
		"id" => "NAME",
		"name" => GetMessage("SALE_SDL_FILTER_NAME"),
		"filterable" => "%",
		"quickSearch" => "%",
		"default" => true
	),
	array(
		"id" => "ACTIVE",
		"name" => GetMessage("SALE_SDL_FILTER_ACTIVE"),
		"type" => "list",
		"items" => array(
			"Y" => GetMessage("SALE_SDL_YES"),
			"N" => GetMessage("SALE_SDL_NO")
		),
		"filterable" => "="
	),
	array(
		"id" => "CLASS_NAME",
		"name" => GetMessage("SALE_SDL_FILTER_CLASS_NAME"),
		"type" => "list",
		"items" => $listTypes,
		"filterable" => "="
	),
	array(
		"id" => "PARENT_ID",
		"name" => GetMessage("SALE_SDL_FILTER_GROUP"),
		"type" => "list",
		"items" => $groups,
		"filterable" => "="
	),
	array(
		"id" => "LID",
		"name" => GetMessage("SALE_SDL_FILTER_SITE"),
		"type" => "list",
		"items" => $sitesList,
		"filterable" => ""
	),
);

$filter = array();

$lAdmin->AddFilter($filterFields, $filter);

if ($groupId >= 0 && !Bitrix\Main\Grid\Context::isInternalRequest())
{
	$filter["=PARENT_ID"] = $groupId;
}

if(!empty($_REQUEST["SHOW_GROUPS"]) && $_REQUEST["SHOW_GROUPS"] == 'Y')
{
	$filter["=CLASS_NAME"] = '\Bitrix\Sale\Delivery\Services\Group';
}

if (empty($filter["=CLASS_NAME"]))
{
	$filter['!=CLASS_NAME'] = array(
		'\Bitrix\Sale\Delivery\Services\Group',
		'\Bitrix\Sale\Delivery\Services\EmptyDeliveryService'
	);

	/** @var \Bitrix\Sale\Delivery\Services\Base $handlerClass */
	foreach($handlersList as $handlerClass)
	{
		if($handlerClass::isProfile() && !in_array($handlerClass, $filter['!=CLASS_NAME']))
		{
			$filter['!=CLASS_NAME'][] = $handlerClass;
		}
	}
}

if (($arID = $lAdmin->GroupAction()) && $saleModulePermissions >= "W")
{
	if ($_REQUEST['action_target']=='selected')
	{
		$arID = Array();
		$params = array(
			'filter' => $filter,
			'select' => array("ID")
		);

		if($by <> '' && $order <> '')
			$params['order'] = array($by => $order);

		$dbResultList = \Bitrix\Sale\Delivery\Services\Table::getList($params);

		while ($arResult = $dbResultList->fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if ($ID == '')
			continue;

		switch ($_REQUEST['action'])
		{
			case "delete":
				$res = \Bitrix\Sale\Delivery\Services\Manager::delete($ID);

				if (!$res->isSuccess())
				{
					if ($ex = $APPLICATION->GetException())
						$lAdmin->AddGroupError($ex->GetString(), $ID);
					else
						$lAdmin->AddGroupError(Loc::getMessage("SALE_SDL_ERROR_DELETE"), $ID);
				}

				break;

			case "activate":
			case "deactivate":
				$arFields = array(
					"ACTIVE" => (($_REQUEST['action']=="activate") ? "Y" : "N")
				);

				$res = \Bitrix\Sale\Delivery\Services\Manager::update($ID, $arFields);

				if (!$res->isSuccess())
				{
					if ($errors = $res->getErrors())
						$lAdmin->AddGroupError(implode("<br>/n", $errors), $ID);
					else
						$lAdmin->AddGroupError(Loc::getMessage("SALE_SDL_ERROR_UPDATE"), $ID);
				}
				else
				{
					\Bitrix\Sale\Delivery\Services\Manager::setChildrenFieldsValues(
						$ID,
						$arFields
					);
				}

				break;
		}
	}

	if ($lAdmin->hasGroupErrors())
	{
		$adminSidePanelHelper->sendJsonErrorResponse($lAdmin->getGroupErrors());
	}
	else
	{
		$adminSidePanelHelper->sendSuccessResponse();
	}
}

$vatList = array(
	0 => Loc::getMessage('SALE_SDL_NO_VAT')
);

if(\Bitrix\Main\Loader::includeModule('catalog'))
{
	$dbRes = \Bitrix\Catalog\VatTable::getList(array(
		'filter' => array('ACTIVE' => 'Y'),
		'order' => array('SORT' => 'ASC')
	));

	while($vat = $dbRes->fetch())
		$vatList[$vat['ID']] = $vat['NAME'];
}

$siteId = "";
if ($filter["LID"] <> '')
{
	$siteId = $filter["LID"];
	unset($filter["LID"]);
}

$glParams = array(
	'filter' => $filter,
	'order' => array($by => $order)
);

$lAdmin->AddHeaders(array(
	array("id"=>"NAME", "content"=>Loc::getMessage("SALE_SDL_NAME"),  "sort"=>"NAME", "default" => true),
	array("id"=>"DESCRIPTION", "content"=>Loc::getMessage("SALE_SDL_DESCRIPTION"),  "sort"=>"", "default" => true),
	array("id"=>"LOGOTIP", "content"=>Loc::getMessage("SALE_SDL_LOGOTIP"), "sort"=>"", "default"=>true),
	array("id"=>"GROUP_NAME", "content"=>Loc::getMessage("SALE_SDL_GROUP_NAME"),  "sort"=>"PARENT.NAME", "default"=>true),
	array("id"=>"ID", "content"=>"ID", 	"sort"=>"ID", "default"=>true),
	array("id"=>"SORT", "content"=>Loc::getMessage("SALE_SDL_SORT"),  "sort"=>"SORT", "default"=>true),
	array("id"=>"ACTIVE", "content"=>Loc::getMessage("SALE_SDL_ACTIVE"),  "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"ALLOW_EDIT_SHIPMENT", "content"=>Loc::getMessage("SALE_SDL_ALLOW_EDIT_SHIPMENT"),  "sort"=>"ALLOW_EDIT_SHIPMENT", "default"=>false),
	array("id"=>"CLASS_NAME", "content"=>Loc::getMessage("SALE_SDL_CLASS_NAME"),  "sort"=>"CLASS_NAME", "default"=>false),
	array("id"=>"SITES", "content"=>Loc::getMessage("SALE_SDL_SITES"), "default"=>false),
	array("id"=>"VAT_ID", "content"=>Loc::getMessage("SALE_SDL_VAT_ID"), "default"=>false)
));

$arVisibleColumns = $lAdmin->GetVisibleHeaderColumns();

if (in_array('SITES', $arVisibleColumns))
{
	$glParams['runtime'] = array(
		'RESTRICTION_BY_SITE' => array(
			'data_type' => 'Bitrix\Sale\Internals\ServiceRestrictionTable',
			'reference' => array(
				'ref.SERVICE_ID' => 'this.ID',
				'ref.SERVICE_TYPE' => array('?', \Bitrix\Sale\Delivery\Restrictions\Manager::SERVICE_TYPE_SHIPMENT),
				'ref.CLASS_NAME' => array('?', '\Bitrix\Sale\Delivery\Restrictions\BySite')
			),
			'join_type' => 'left'
		)
	);

	$glParams['select'] = array(
		'*',
		'SITES' => 'RESTRICTION_BY_SITE.PARAMS'
	);
}

$backUrl = urlencode($APPLICATION->GetCurPageParam("", array("mode", "internal", "grid_id", "grid_action", "bxajaxid", "sessid"))); //todo replace to $lAdmin->getCurPageParam()
$dbResultList = \Bitrix\Sale\Delivery\Services\Table::getList($glParams);

$result = [];
while ($service = $dbResultList->fetch())
{
	if($siteId <> '')
	{
		$dbRestriction = \Bitrix\Sale\Internals\ServiceRestrictionTable::getList(array(
			'filter' => array(
				'=SERVICE_ID' => $service['ID'],
				'=SERVICE_TYPE' => \Bitrix\Sale\Services\PaySystem\Restrictions\Manager::SERVICE_TYPE_SHIPMENT,
				'=CLASS_NAME' => '\Bitrix\Sale\Delivery\Restrictions\BySite'
			)
		));

		while ($restriction = $dbRestriction->fetch())
		{
			if (!\Bitrix\Sale\Delivery\Restrictions\BySite::check($siteId, $restriction['PARAMS']))
			{
				continue(2);
			}
		}
	}

	$result[] = $service;
}

$dbResultList = new CDBResult();
$dbResultList->InitFromArray($result);
$dbResultList = new CAdminUiResult($dbResultList, $sTableID);

$dbResultList->NavStart();
$lAdmin->SetNavigationParams($dbResultList, array("BASE_LINK" => $selfFolderUrl."sale_delivery_service_list.php"));

while ($service = $dbResultList->NavNext(false))
{
	if(is_callable($service["CLASS_NAME"].'::canHasChildren') && $service["CLASS_NAME"]::canHasChildren()) //has children
	{
		$actUrl = $selfFolderUrl."sale_delivery_service_list.php?lang=".LANGUAGE_ID."&PARENT_ID=".$service["ID"]."&apply_filter=Y";
		$actUrl = $adminSidePanelHelper->editUrlToPublicPage($actUrl);
		$row =& $lAdmin->AddRow($service["ID"], $service, $actUrl, GetMessage("SALE_SALE_EDIT_DESCR"));

		$row->AddField("NAME", '<a href="'.$actUrl.'" class="adm-list-table-icon-link">'.
				'<span class="adm-submenu-item-link-icon adm-list-table-icon sale_section_icon"></span>'.
				'<span class="adm-list-table-link">'.
					htmlspecialcharsbx($service["NAME"]).
				'</span>'.
			'</a>');
	}
	else //has no children
	{
		$actUrl = $selfFolderUrl."sale_delivery_service_edit.php?lang=".LANGUAGE_ID."&PARENT_ID=".$service["PARENT_ID"]."&ID=".$service["ID"]."&back_url=".$backUrl;
		$actUrl = $adminSidePanelHelper->editUrlToPublicPage($actUrl);
		$row =& $lAdmin->AddRow($service["ID"], $service, $actUrl, GetMessage("SALE_SALE_EDIT_DESCR"));

		$row->AddField("NAME", '<a href="'.$actUrl.'" class="adm-list-table-icon-link">'.
				'<span class="adm-list-table-link">'.
					htmlspecialcharsbx($service["NAME"]).
				'</span>'.
			'</a>');
	}

	$row->AddField("ID", $service["ID"]);

	$logoHtml = intval($service["LOGOTIP"]) > 0 ? CFile::ShowImage(CFile::GetFileArray($service["LOGOTIP"]), 150, 150, "border=0", "", false) : "";
	$row->AddField("LOGOTIP", $logoHtml);
	$row->AddField("DESCRIPTION", $service["DESCRIPTION"], false, true);
	$row->AddField("SORT", $service["SORT"]);
	$row->AddField("ACTIVE", (($service["ACTIVE"]=="Y") ? Loc::getMessage("SALE_SDL_YES") : Loc::getMessage("SALE_SDL_NO")));
	$row->AddField("ALLOW_EDIT_SHIPMENT", (($service["ALLOW_EDIT_SHIPMENT"]=="Y") ? Loc::getMessage("SALE_SDL_YES") : Loc::getMessage("SALE_SDL_NO")));
	$row->AddField("CLASS_NAME", (is_callable($service["CLASS_NAME"]."::getClassTitle") ? $service["CLASS_NAME"]::getClassTitle() : "")." [".$service["CLASS_NAME"]."]");

	$sites = "";

	if(isset($service["SITES"]) && !empty($service["SITES"]['SITE_ID']) && is_array($service["SITES"]['SITE_ID']))
		foreach($service["SITES"]['SITE_ID'] as $lid)
			$sites .= $sitesList[$lid]." (".$lid.")<br>";

	$row->AddField("SITES", $sites <> '' ? $sites : Loc::getMessage('SALE_SDL_ALL'));
	$row->AddField("VAT_ID", isset($vatList[$service["VAT_ID"]]) ? $vatList[$service["VAT_ID"]] : $vatList[0]);

	$groupNameHtml = "";

	if ($service["PARENT_ID"] > 0)
	{
		$res = \Bitrix\Sale\Delivery\Services\Table::getById($service["PARENT_ID"]);

		if ($group = $res->fetch())
		{
			$groupEditUrl = $selfFolderUrl.'sale_delivery_service_edit.php?lang='.LANGUAGE_ID.'&PARENT_ID='.
				$group["PARENT_ID"].'&ID='.$group["ID"]."&back_url=".$backUrl;
			$groupEditUrl = $adminSidePanelHelper->editUrlToPublicPage($groupEditUrl);
			$groupNameHtml = '<a href="'.$groupEditUrl.'">'.htmlspecialcharsbx($group["NAME"]).'</a>';
		}
	}

	$row->AddField("GROUP_NAME", $groupNameHtml);

	$arActions = Array();
	if (!$publicMode)
	{
		$arActions[] = array(
			"ICON" => "copy",
			"TEXT" => Loc::getMessage("SALE_SDL_COPY_DESCR"),
			"ACTION" => 'BX.Sale.Delivery.showGroupsDialog("sale_delivery_service_edit.php?lang='.LANGUAGE_ID.
				'&ID='.$service["ID"].'&action=copy","'.$service["PARENT_ID"]."&back_url=".$backUrl.'");',
			"DEFAULT" => true
		);
	}
	$editUrl = $selfFolderUrl."sale_delivery_service_edit.php?lang=".LANGUAGE_ID."&PARENT_ID=".
		$service["PARENT_ID"]."&ID=".$service["ID"]."&back_url=".$backUrl;
	$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
	$arActions[] = array(
		"ICON" => "edit",
		"TEXT" => Loc::getMessage("SALE_SDL_EDIT_DESCR"),
		"LINK" => $editUrl,
		"DEFAULT" => true
	);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array(
			"ICON" => "delete",
			"TEXT" => Loc::getMessage("SALE_SDL_DELETE_DESCR"),
			"ACTION" => "if(confirm('".Loc::getMessage('SALE_SDL_CONFIRM_DEL_MESSAGE')."')) ".
				$lAdmin->ActionDoGroup($service["ID"], "delete", "PARENT_ID=".$service["PARENT_ID"])
		);
	}

	$row->AddActions($arActions);
}

$lAdmin->AddGroupActionTable(
	array(
		"delete" => Loc::getMessage("MAIN_ADMIN_LIST_DELETE"),
		"activate" => Loc::getMessage("MAIN_ADMIN_LIST_ACTIVATE"),
		"deactivate" => Loc::getMessage("MAIN_ADMIN_LIST_DEACTIVATE"),
	)
);

if ($saleModulePermissions == "W")
{
	$aContext = array();

	if(isset($filter["=CLASS_NAME"]) && $filter["=CLASS_NAME"] == '\Bitrix\Sale\Delivery\Services\Group')
	{
		$addUrl = "sale_delivery_service_edit.php?lang=".LANGUAGE_ID."&CLASS_NAME=".urlencode('\Bitrix\Sale\Delivery\Services\Group')."&back_url=".$backUrl;
		$addUrl = $adminSidePanelHelper->editUrlToPublicPage($addUrl);
		$aContext[] = array(
			"TEXT" => Loc::getMessage("SALE_SDL_ADD_NEW"),
			"TITLE" => Loc::getMessage("SALE_SDL_ADD_NEW_ALT"),
			"LINK" => $addUrl,
			"ICON" => "btn_new"
		);
		$listUrl = isset($_GET["back_url"]) ? $_GET["back_url"] : $selfFolderUrl."sale_delivery_service_list.php?lang=".LANGUAGE_ID.
			(!empty($groupId) ? "&PARENT_ID=".intval($groupId) : "")."&apply_filter=Y";
		$listUrl = $adminSidePanelHelper->editUrlToPublicPage($listUrl);
		$aContext[] = array(
			"TEXT" => Loc::getMessage("SALE_SDL_TO_LIST"),
			"LINK" => $listUrl,
			"TITLE" => Loc::getMessage("SALE_SDL_TO_LIST_ALT"),
		);

	}
	else
	{
		$classNamesList = \Bitrix\Sale\Delivery\Services\Manager::getHandlersList();

		$classesToExclude = array(
			'\Bitrix\Sale\Delivery\Services\AutomaticProfile',
			'\Bitrix\Sale\Delivery\Services\Group'
		);

		if(\Bitrix\Sale\Delivery\Services\EmptyDeliveryService::getEmptyDeliveryServiceId() > 0)
			$classesToExclude[] = '\Bitrix\Sale\Delivery\Services\EmptyDeliveryService';

		$menu = array();

		/** @var \Bitrix\Sale\Delivery\Services\Base $class */

		foreach($classNamesList as $class)
		{
			if(in_array($class, $classesToExclude))
				continue;

			if($class::isProfile())
				continue;

			$supportedServices = $class::getSupportedServicesList();

			$restServices = [];
			$isRest = ($class === "\\".\Sale\Handlers\Delivery\RestHandler::class);
			if ($isRest)
			{
				$restServices = \Bitrix\Sale\Delivery\Services\Manager::getRestHandlerList();
			}

			if(is_array($supportedServices) && !empty($supportedServices))
			{
				if(!empty($supportedServices['ERRORS']) && is_array($supportedServices['ERRORS']))
					foreach($supportedServices['ERRORS'] as $error)
						$lAdmin->AddGroupError($error);

				unset($supportedServices['ERRORS']);

				if(!empty($supportedServices['NOTES']) && is_array($supportedServices['NOTES']))
					foreach($supportedServices['NOTES'] as $note)
						$adminNotes[] = $note;

				unset($supportedServices['NOTES']);

				if(is_array($supportedServices))
				{
					foreach($supportedServices as $srvType => $srvParams)
					{
						if(!empty($srvParams["NAME"]))
						{
							$editUrl = $selfFolderUrl."sale_delivery_service_edit.php?lang=".LANGUAGE_ID."&PARENT_ID=".
								(intval($filter["=PARENT_ID"]) > 0 ? $filter["=PARENT_ID"] : 0)."&CLASS_NAME=".
								urlencode($class)."&SERVICE_TYPE=".$srvType."&back_url=".$backUrl;
							$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
							$menu[] = array(
								"TEXT" => $srvParams["NAME"],
								"LINK" => $editUrl
							);
						}
					}
				}
			}
			elseif ($restServices)
			{
				foreach ($restServices as $restService)
				{
					$editUrl = $selfFolderUrl."sale_delivery_service_edit.php?lang=".LANGUAGE_ID."&PARENT_ID=".(intval($filter["=PARENT_ID"]) > 0 ? $filter["=PARENT_ID"] : 0).
						"&CLASS_NAME=".urlencode($class)."&REST_CODE=".$restService['CODE']."&back_url=".$backUrl;
					$editUrl = $adminSidePanelHelper->editUrlToPublicPage($editUrl);
					$menu[] = array(
							"TEXT" => $restService["NAME"],
							"LINK" => $editUrl
					);
				}
			}
			else
			{
				if ($isRest)
				{
					continue;
				}

				$editUrl = $selfFolderUrl."sale_delivery_service_edit.php?lang=".LANGUAGE_ID."&PARENT_ID=".(intval($filter["=PARENT_ID"]) > 0 ? $filter["=PARENT_ID"] : 0).
					"&CLASS_NAME=".urlencode($class)."&back_url=".$backUrl;
				$menu[] = array(
					"TEXT" => $class::getClassTitle(),
					"LINK" => $adminSidePanelHelper->editUrlToPublicPage($editUrl)
				);
			}
		}

		sortByColumn($menu, array("TEXT" => SORT_ASC));

		$aContext[] = array(
			"TEXT" => Loc::getMessage("SALE_SDL_ADD_NEW"),
			"TITLE" => Loc::getMessage("SALE_SDL_ADD_NEW_ALT"),
			"DISABLE" => true,
			"MENU" => $menu,
			"ICON" => "btn_new"
		);

		/** @global CUser $USER */
		global $USER;
		if ($USER->CanDoOperation("install_updates") && !$publicMode)
		{
			$aContext[] = array(
				"TEXT" => GetMessage("SALE_SDL_MARKETPLACE_ADD_NEW"),
				"TITLE" => GetMessage("SALE_SDL_MARKETPLACE_ADD_NEW_ALT"),
				"LINK" => "update_system_market.php?category=36&lang=".LANGUAGE_ID,
				"ICON" => "btn"
			);
		}
	}

	$lAdmin->setContextSettings(array("pagePath" => $selfFolderUrl."sale_delivery_service_list.php"));
	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();
$APPLICATION->SetTitle(Loc::getMessage("SALE_SDL_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
if (!$publicMode && \Bitrix\Sale\Update\CrmEntityCreatorStepper::isNeedStub())
{
	$APPLICATION->IncludeComponent("bitrix:sale.admin.page.stub", ".default");
}
else
{
	$lAdmin->DisplayFilter($filterFields);

	if(!empty($adminNotes))
	{
		echo BeginNote();
		echo implode('<br>', $adminNotes);
		echo EndNote();
	}

	$lAdmin->DisplayList();

	?>
	<script language="JavaScript">
		BX.message({
			SALE_DSE_CHOOSE_GROUP_TITLE: '<?=Loc::getMessage("SALE_DSE_CHOOSE_GROUP_TITLE")?>',
			SALE_DSE_CHOOSE_GROUP_HEAD: '<?=Loc::getMessage("SALE_DSE_CHOOSE_GROUP_HEAD")?>',
			SALE_DSE_CHOOSE_GROUP_SAVE: '<?=Loc::getMessage("SALE_DSE_CHOOSE_GROUP_SAVE")?>'
		});
	</script>
	<?
}
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");