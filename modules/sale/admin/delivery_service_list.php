<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

use Bitrix\Main\Localization\Loc;

\Bitrix\Main\Loader::includeModule('sale');
Loc::loadMessages(__FILE__);

/** @var  CMain $APPLICATION */
$saleModulePermissions = $APPLICATION->GetGroupRight("sale");
if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(Loc::getMessage("SALE_DSL_ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/sale/prolog.php");

\Bitrix\Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/delivery.js");
$sTableID = "tbl_sale_delivery_list";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminList($sTableID, $oSort);
$adminNotes = array();

if(!isset($by))
	$by = 'ID';
if(!isset($order))
	$order = 'ASC';

$groupId = isset($filter_group) && (isset($set_filter) ||  $set_filter == 'Y') ? $filter_group : -1;

$arFilterFields = array(
	"filter_name",
	"filter_active",
	"filter_class_name",
	"filter_group",
	"filter_site"
);

if(!empty($_REQUEST["SHOW_GROUPS"]) && $_REQUEST["SHOW_GROUPS"] == 'Y')
{
	unset($del_filter);
	$set_filter='Y';
	$filter_class_name = '\Bitrix\Sale\Delivery\Services\Group';
}

$lAdmin->InitFilter($arFilterFields);
$filter_group = $groupId;

$filter = array();
if(strlen($filter_name) > 0) $filter["%NAME"] = Trim($filter_name);
if(strlen($filter_active) > 0) $filter["=ACTIVE"] = Trim($filter_active);
if(intval($filter_group) >= 0) $filter["=PARENT_ID"] = intval($filter_group);

if(strlen($filter_class_name) > 0)
{
	$filter["=CLASS_NAME"] = Trim($filter_class_name);
}
else
{
	$handlersList = \Bitrix\Sale\Delivery\Services\Manager::getHandlersList();

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

		if(strlen($by) > 0 && strlen($order) > 0)
			$params['order'] = array($by => $order);

		$dbResultList = \Bitrix\Sale\Delivery\Services\Table::getList($params);

		while ($arResult = $dbResultList->fetch())
			$arID[] = $arResult['ID'];
	}

	foreach ($arID as $ID)
	{
		if (strlen($ID) <= 0)
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
}

$sitesList = array();

$db = \Bitrix\Main\SiteTable::getList(
	array(
		'filter' => array('ACTIVE' => 'Y'),
		'order' => array('SORT' => 'ASC')
	)
);

while($site = $db->fetch())
	$sitesList[$site['LID']] = $site['NAME'];

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

if(strlen($filter_site) > 0 || in_array('SITES', $arVisibleColumns))
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

$backUrl = urlencode($APPLICATION->GetCurPageParam("", array("mode")));
$dbResultList = \Bitrix\Sale\Delivery\Services\Table::getList($glParams);
$dbResultList = new CAdminResult($dbResultList, $sTableID);

$dbResultList->NavStart();
$lAdmin->NavText($dbResultList->GetNavPrint(GetMessage("SALE_SDL_PRLIST")));

while ($service = $dbResultList->NavNext(true, "f_"))
{
	if(strlen($filter_site) > 0 && isset($f_SITES) && !empty($f_SITES['SITE_ID']) && is_array($f_SITES['SITE_ID']))
		if(!in_array($filter_site, $f_SITES['SITE_ID']))
			continue;

	if(is_callable($service["CLASS_NAME"].'::canHasChildren') && $service["CLASS_NAME"]::canHasChildren()) //has children
	{
		$actUrl = "sale_delivery_service_list.php?lang=".LANG."&filter_group=".$f_ID."&set_filter=Y";
		$row =& $lAdmin->AddRow($f_ID, $service, $actUrl, GetMessage("SALE_SALE_EDIT_DESCR"));

		$row->AddField("NAME", '<a href="'.$actUrl.'" class="adm-list-table-icon-link">'.
				'<span class="adm-submenu-item-link-icon adm-list-table-icon sale_section_icon"></span>'.
				'<span class="adm-list-table-link">'.
					$f_NAME.
				'</span>'.
			'</a>');
	}
	else //has no children
	{
		$actUrl = "sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".$f_PARENT_ID."&ID=".$f_ID."&back_url=".$backUrl;
		$row =& $lAdmin->AddRow($f_ID, $service, $actUrl, GetMessage("SALE_SALE_EDIT_DESCR"));

		$row->AddField("NAME", '<a href="'.$actUrl.'" class="adm-list-table-icon-link">'.
				'<span class="adm-list-table-link">'.
					$f_NAME.
				'</span>'.
			'</a>');
	}

	$row->AddField("ID", $f_ID);

	$logoHtml = intval($f_LOGOTIP) > 0 ? CFile::ShowImage(CFile::GetFileArray($f_LOGOTIP), 150, 150, "border=0", "", false) : "";
	$row->AddField("LOGOTIP", $logoHtml);
	$row->AddField("DESCRIPTION", $f_DESCRIPTION);
	$row->AddField("SORT", $f_SORT);
	$row->AddField("ACTIVE", (($f_ACTIVE=="Y") ? Loc::getMessage("SALE_SDL_YES") : Loc::getMessage("SALE_SDL_NO")));
	$row->AddField("ALLOW_EDIT_SHIPMENT", (($f_ALLOW_EDIT_SHIPMENT=="Y") ? Loc::getMessage("SALE_SDL_YES") : Loc::getMessage("SALE_SDL_NO")));
	$row->AddField("CLASS_NAME", (is_callable($f_CLASS_NAME."::getClassTitle") ? $f_CLASS_NAME::getClassTitle() : "")." [".$f_CLASS_NAME."]");

	$sites = "";

	if(isset($f_SITES) && !empty($f_SITES['SITE_ID']) && is_array($f_SITES['SITE_ID']))
		foreach($f_SITES['SITE_ID'] as $siteId)
			$sites .= $sitesList[$siteId]." (".$siteId.")<br>";

	$row->AddField("SITES", strlen($sites) > 0 ? $sites : Loc::getMessage('SALE_SDL_ALL'));
	$row->AddField("VAT_ID", isset($vatList[$f_VAT_ID]) ? $vatList[$f_VAT_ID] : $vatList[0]);

	$groupNameHtml = "";

	if($f_PARENT_ID > 0)
	{
		$res = \Bitrix\Sale\Delivery\Services\Table::getById($f_PARENT_ID);

		if($group = $res->fetch())
			$groupNameHtml = '<a href="sale_delivery_service_edit.php?lang='.LANG.'&PARENT_ID='.$group["PARENT_ID"].'&ID='.$group["ID"]."&back_url=".$backUrl.'">'.htmlspecialcharsbx($group["NAME"]).'</a>';
	}

	$row->AddField("GROUP_NAME", $groupNameHtml);

	$arActions = Array();
	$arActions[] = array("ICON"=>"copy", "TEXT"=>Loc::getMessage("SALE_SDL_COPY_DESCR"), "ACTION"=>'BX.Sale.Delivery.showGroupsDialog("sale_delivery_service_edit.php?lang='.LANG.'&ID='.$f_ID.'&action=copy","'.$f_PARENT_ID."&back_url=".$backUrl.'");', "DEFAULT"=>true);
	$arActions[] = array("ICON"=>"edit", "TEXT"=>Loc::getMessage("SALE_SDL_EDIT_DESCR"), "ACTION"=>$lAdmin->ActionRedirect("sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".$f_PARENT_ID."&ID=".$f_ID."&back_url=".$backUrl), "DEFAULT"=>true);
	if ($saleModulePermissions >= "W")
	{
		$arActions[] = array("SEPARATOR" => true);
		$arActions[] = array("ICON"=>"delete", "TEXT"=>Loc::getMessage("SALE_SDL_DELETE_DESCR"), "ACTION"=>"if(confirm('".Loc::getMessage('SALE_SDL_CONFIRM_DEL_MESSAGE')."')) ".$lAdmin->ActionDoGroup($f_ID, "delete", "PARENT_ID=".$f_PARENT_ID));
	}

	$row->AddActions($arActions);
}

$lAdmin->AddFooter(
	array(
		array(
			"title" => Loc::getMessage("MAIN_ADMIN_LIST_SELECTED"),
			"value" => $dbResultList->SelectedRowsCount()
		),
		array(
			"counter" => true,
			"title" => Loc::getMessage("MAIN_ADMIN_LIST_CHECKED"),
			"value" => "0"
		),
	)
);

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
		$aContext[] = array(
			"TEXT" => Loc::getMessage("SALE_SDL_ADD_NEW"),
			"TITLE" => Loc::getMessage("SALE_SDL_ADD_NEW_ALT"),
			"LINK" => "sale_delivery_service_edit.php?lang=".LANG."&CLASS_NAME=".urlencode('\Bitrix\Sale\Delivery\Services\Group')."&back_url=".$backUrl,
			"ICON" => "btn_new"
		);

		$aContext[] = array(
			"TEXT" => Loc::getMessage("SALE_SDL_TO_LIST"),
			"LINK" => isset($_GET["back_url"]) ? $_GET["back_url"] : "/bitrix/admin/sale_delivery_service_list.php?lang=".LANGUAGE_ID.
				(!empty($filter_group) ? "&filter_group=".intval($filter_group) : "")."&set_filter=Y",
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
							$menu[] = array(
								"TEXT" => $srvParams["NAME"],
								"LINK" => "sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".(intval($filter["=PARENT_ID"]) > 0 ? $filter["=PARENT_ID"] : 0).
									"&CLASS_NAME=".urlencode($class)."&SERVICE_TYPE=".$srvType."&back_url=".$backUrl
							);
						}
					}
				}
			}
			else
			{
				$menu[] = array(
					"TEXT" => $class::getClassTitle(),
					"LINK" => "sale_delivery_service_edit.php?lang=".LANG."&PARENT_ID=".(intval($filter["=PARENT_ID"]) > 0 ? $filter["=PARENT_ID"] : 0).
						"&CLASS_NAME=".urlencode($class)."&back_url=".$backUrl
				);
			}
		}

		sortByColumn($menu, array("TEXT" => SORT_ASC));

		$aContext[] = array(
			"TEXT" => Loc::getMessage("SALE_SDL_ADD_NEW"),
			"TITLE" => Loc::getMessage("SALE_SDL_ADD_NEW_ALT"),
			"MENU" => $menu,
			"ICON" => "btn_new"
		);

		$aContext[] = array(
			"TEXT" => Loc::getMessage("SALE_SDL_MANAGE_GROUP"),
			"LINK" => $APPLICATION->GetCurPageParam(
				"SHOW_GROUPS=Y".
				"&backurl=".urlencode($APPLICATION->GetCurPageParam()),
				array("filter_class_name", "filter_group", "mode")
			),
			"TITLE" => Loc::getMessage("SALE_SDL_MANAGE_GROUP_ALT")
		);

		/** @global CUser $USER */
		global $USER;
		if($USER->CanDoOperation("install_updates"))
		{
			$aContext[] = array(
				"TEXT" => GetMessage("SALE_SDL_MARKETPLACE_ADD_NEW"),
				"TITLE" => GetMessage("SALE_SDL_MARKETPLACE_ADD_NEW_ALT"),
				"LINK" => "update_system_market.php?category=36&lang=".LANG,
				"ICON" => "btn"
			);
		}
	}

	$lAdmin->AddAdminContextMenu($aContext);
}

$lAdmin->CheckListMode();
$APPLICATION->SetTitle(Loc::getMessage("SALE_SDL_TITLE"));

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

?>
<script language="JavaScript">
	BX.message({
		SALE_DSE_CHOOSE_GROUP_TITLE: '<?=Loc::getMessage("SALE_DSE_CHOOSE_GROUP_TITLE")?>',
		SALE_DSE_CHOOSE_GROUP_HEAD: '<?=Loc::getMessage("SALE_DSE_CHOOSE_GROUP_HEAD")?>',
		SALE_DSE_CHOOSE_GROUP_SAVE: '<?=Loc::getMessage("SALE_DSE_CHOOSE_GROUP_SAVE")?>'
	});
</script>

<form name="find_form" method="GET" action="<?echo $APPLICATION->GetCurPageParam()?>?">
<?
$oFilter = new CAdminFilter(
	$sTableID."_filter",
	array(
		Loc::getMessage("SALE_SDL_FILTER_NAME"),
		Loc::getMessage("SALE_SDL_FILTER_ACTIVE"),
		Loc::getMessage("SALE_SDL_FILTER_CLASS_NAME"),
		Loc::getMessage("SALE_SDL_FILTER_GROUP"),
		Loc::getMessage("SALE_SDL_FILTER_SITE")
	)
);

$oFilter->Begin();
?>
	<tr>
		<td><?=Loc::getMessage("SALE_SDL_FILTER_NAME")?>:</td>
		<td>
			<input type="text" name="filter_name" value="<?=htmlspecialcharsbx($filter_name)?>">
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("SALE_SDL_FILTER_ACTIVE")?>:</td>
		<td>
			<select name="filter_active">
				<option value=""><?=Loc::getMessage("SALE_SDL_ALL")?></option>
				<option value="Y"<?if ($filter_active=="Y") echo " selected"?>><?=Loc::getMessage("SALE_SDL_YES")?></option>
				<option value="N"<?if ($filter_active=="N") echo " selected"?>><?=Loc::getMessage("SALE_SDL_NO")?></option>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("SALE_SDL_FILTER_CLASS_NAME")?>:</td>
		<td>
			<select name="filter_class_name">
				<option value=""></option>
				<?foreach(\Bitrix\Sale\Delivery\Services\Manager::getHandlersList() as $className):?>
					<?if(is_callable($className."::getClassTitle")):?>
						<option value="<?=htmlspecialcharsbx($className)?>" <?=(isset($filter["=CLASS_NAME"]) && $className == $filter["=CLASS_NAME"] ? " selected" : "" )?>><?=htmlspecialcharsbx($className::getClassTitle())?></option>
					<?endif;?>
				<?endforeach;?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("SALE_SDL_FILTER_GROUP")?>:</td>
		<td>
			<?=\Bitrix\Sale\Delivery\Helper::getGroupChooseControl(
				$filter_group,
				"filter_group",
				"",
				true
			)?>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage("SALE_SDL_FILTER_SITE")?>:</td>
		<td>
			<select name="filter_site">
				<option value=""><?=Loc::getMessage('SALE_SDL_ALL')?></option>
				<?foreach($sitesList as $siteId => $siteName):?>
					<option value="<?=$siteId?>"<?=($filter_site == $siteId ? ' selected' : '')?>><?=htmlspecialcharsbx($siteName).' ('.$siteId.')'?></option>
				<?endforeach;?>
			</select>
		</td>
	</tr>
	<?
$oFilter->Buttons(
	array(
		"table_id" => $sTableID,
		"url" => $APPLICATION->GetCurPageParam("", $arFilterFields),
		"form" => "find_form"
	)
);
$oFilter->End();
?>
</form>
<?

if(!empty($adminNotes))
{
	echo BeginNote();
	echo implode('<br>', $adminNotes);
	echo EndNote();
}

$lAdmin->DisplayList();

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");