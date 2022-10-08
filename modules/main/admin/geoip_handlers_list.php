<?
/**
 * @global CUser $USER
 * @global CMain $APPLICATION
 */
require_once(__DIR__."/../include/prolog_admin_before.php");

use \Bitrix\Main\Service\GeoIp,
	\Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

if(!$USER->CanDoOperation('view_other_settings'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isAdmin = $USER->CanDoOperation('edit_other_settings');

$sTableID = "tbl_geoip_handlers";
$oSort = new CAdminSorting($sTableID, "SORT", "ASC");

if(!isset($by))
	$by = 'SORT';
if(!isset($order))
	$order = 'ASC';

$lAdmin = new CAdminList($sTableID, $oSort);
$backUrl = urlencode($APPLICATION->GetCurPageParam());

if ($isAdmin)
{
	if(($ids = $lAdmin->GroupAction()))
	{
		foreach($ids as $id)
		{
			$id = intval($id);

			if($id <= 0)
				continue;

			switch($_REQUEST['action'])
			{
				case "delete":
					$res = GeoIp\HandlerTable::delete($id);

					if(!$res->isSuccess())
						$lAdmin->AddGroupError(implode("\n<br>", $res->getErrorMessages()), $id);

					break;
			}
		}
	}
}

$aHeaders = array(
	array("id"=>"ID", "content"=>Loc::getMessage('GEOIP_LIST_F_ID'), "sort"=>"ID", "default"=>false),
	array("id"=>"TITLE", "content"=>Loc::getMessage('GEOIP_LIST_F_TITLE'), "sort"=>"TITLE", "default"=>true),
	array("id"=>"DESCRIPTION", "content"=>Loc::getMessage('GEOIP_LIST_F_DESCRIPTION'), "default"=>true),
	array("id"=>"ACTIVE", "content"=>Loc::getMessage('GEOIP_LIST_F_ACTIVE'), "sort"=>"ACTIVE", "default"=>true),
	array("id"=>"IS_INSTALLED", "content"=>Loc::getMessage('GEOIP_LIST_F_IS_INSTALLED'), "default"=>true),
	array("id"=>"SORT", "content"=>Loc::getMessage('GEOIP_LIST_F_SORT'), "sort"=>"SORT", "default"=>true),
	array("id"=>"LANGUAGES", "content"=>Loc::getMessage('GEOIP_LIST_F_LANG'), "default"=>true),
	array("id"=>"CLASS_NAME", "content"=>Loc::getMessage('GEOIP_LIST_F_CLASS'), "default"=>false)
);

$lAdmin->AddHeaders($aHeaders);

$recordlessHandlers = array();
$handlers = array();

/** @var GeoIp\Base $handler */
foreach(GeoIp\Manager::getHandlers() as $className => $handler)
{
	if($handler->getId() <= 0)
	{
		$recordlessHandlers[] = $className;
		continue;
	}

	$handlers[] = array(
		'ID' => $handler->getId(),
		'TITLE' => $handler->getTitle(),
		'DESCRIPTION' => $handler->getDescription(),
		'ACTIVE' => $handler->isActive() ? Loc::getMessage('GEOIP_LIST_Y') : Loc::getMessage('GEOIP_LIST_N'),
		'IS_INSTALLED' => $handler->isInstalled() ? Loc::getMessage('GEOIP_LIST_Y') : Loc::getMessage('GEOIP_LIST_N'),
		'SORT' => $handler->getSort(),
		'LANGUAGES' => implode(', ', $handler->getSupportedLanguages()),
		'CLASS_NAME' => $className
	);
}

sortByColumn($handlers, array($by => (strtoupper($order) == 'ASC' ? SORT_ASC : SORT_DESC)));

foreach($handlers as $fields)
{
	$row =&$lAdmin->AddRow($fields['ID'], $fields);

	$row->AddViewField("ID", $fields['ID']);
	$row->AddViewField("TITLE", $fields['TITLE']);
	$row->AddViewField("DESCRIPTION", $fields['DESCRIPTION']);
	$row->AddViewField("ACTIVE", $fields['ACTIVE']);
	$row->AddViewField("IS_INSTALLED", $fields['IS_INSTALLED']);
	$row->AddViewField("SORT", $fields['SORT']);
	$row->AddViewField("LANGUAGES", $fields['LANGUAGES']);
	$row->AddViewField("CLASS_NAME", $fields['CLASS_NAME']);

	if ($isAdmin)
	{
		$arActions = array();

		$arActions[] = 	array(
			"ICON" => "edit",
			"DEFAULT" => true,
			"TEXT" => Loc::getMessage('GEOIP_LIST_EDIT'),
			"ACTION" => $lAdmin->ActionRedirect("geoip_handler_edit.php?lang=".LANG."&ID=".$fields['ID']."&CLASS_NAME=".urlencode($fields['CLASS_NAME']))
		);

		$arActions[] = 	array(
			"ICON" => "delete",
			"TEXT" => Loc::getMessage('GEOIP_LIST_DELETE'),
			"ACTION" => "if(confirm('".Loc::getMessage('GEOIP_LIST_DELETE_CONFIRM')."')) ".$lAdmin->ActionDoGroup($fields['ID'], "delete")
		);

		$row->AddActions($arActions);
	}
}

$aContext=array();

if($isAdmin)
{
	$menu = array();

	foreach($recordlessHandlers as $className)
	{
		$handler = GeoIp\Manager::getHandlerByClassName($className);

		if(!$handler)
			continue;

		$menu[] = array(
			"TEXT" => $handler->getTitle(),
			"LINK" => "geoip_handler_edit.php?lang=".LANG."&CLASS_NAME=".urlencode($className)."&back_url=".$backUrl
		);
	}

	if(!empty($menu))
	{
		$aContext = array(
			array(
				"TEXT" => Loc::getMessage('GEOIP_LIST_ADD_HANDLER'),
				"MENU" => $menu,
				"TITLE" => Loc::getMessage('GEOIP_LIST_ADD_HANDLER_T'),
				"ICON" => "btn_new",
			),
		);
	}
}

$lAdmin->AddAdminContextMenu($aContext);
$lAdmin->CheckListMode();

$APPLICATION->SetTitle(Loc::getMessage('GEOIP_LIST_TITLE'));
require_once ($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

$lAdmin->DisplayList();

require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/epilog_admin.php");
