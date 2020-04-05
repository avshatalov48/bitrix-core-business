<?
use \Bitrix\Sale\Internals\CompanyTable;
use \Bitrix\Main\Application;
use Bitrix\Sale\Services\Company;
use Bitrix\Main;
use Bitrix\Main\Config;
use Bitrix\Main\Localization\Loc;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$saleModulePermissions = $APPLICATION->GetGroupRight("sale");

if ($saleModulePermissions < "W")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

global $USER_FIELD_MANAGER, $USER;
IncludeModuleLangFile(__FILE__);

Main\Loader::includeModule('sale');

$documentRoot = Application::getDocumentRoot();
$lang = Application::getInstance()->getContext()->getLanguage();
$request = Application::getInstance()->getContext()->getRequest();
$id = intval($request->get("ID"));
$company = array();

$errorMessage = '';

if ($request->isPost() && $request->getPost("update") && check_bitrix_sessid() && $saleModulePermissions == 'W')
{
	$name = $request->getPost('NAME');
	$locationId = $request->getPost('LOCATION_ID');

	if (empty($name))
		$errorMessage .= GetMessage('ERROR_NO_NAME')."\n";

	if (empty($errorMessage))
	{
		$uFields = array();
		$USER_FIELD_MANAGER->EditFormAddFields(CompanyTable::getUfId(), $uFields);

		$fields = array(
			'NAME' => $name,
			'LOCATION_ID' => $locationId,
			'ADDRESS' => $request->getPost('ADDRESS'),
			'CODE' => $request->getPost('CODE'),
			'ACTIVE' => ($request->getPost('ACTIVE') !== null) ? 'Y' : 'N'
		);

		if ($request->getPost('SORT') > 0)
			$fields['SORT'] = $request->getPost('SORT');

		$fields = array_merge($fields, $uFields);

		if ($id > 0)
		{
			\Bitrix\Sale\Internals\CompanyGroupTable::deleteByCompanyId($id);
			\Bitrix\Sale\Internals\CompanyResponsibleGroupTable::deleteByCompanyId($id);
		}


		$result = null;
		if ($id > 0)
		{
			$fields['DATE_MODIFY'] = new \Bitrix\Main\Type\DateTime();
			$fields['MODIFIED_BY'] = $USER->GetID();
			$result = CompanyTable::update($id, $fields);
		}
		else
		{
			$fields['XML_ID'] = $request->getPost('XML_ID');
			$fields['DATE_CREATE'] = new \Bitrix\Main\Type\DateTime();
			$fields['CREATED_BY'] = $USER->GetID();
			$result = CompanyTable::add($fields);
		}

		if ($result && $result->isSuccess())
		{
			$id = $result->getId();

			if ($groups = $request->getPost('GROUPS'))
			{
				foreach ($groups as $groupId)
				{
					$r = \Bitrix\Sale\Internals\CompanyGroupTable::add(array(
																		   'COMPANY_ID' => $id,
																		   'GROUP_ID' => $groupId,
																	   ));

				}

			}

			if ($responsibleGroups = $request->getPost('RESPONSIBLE_GROUPS'))
			{
				foreach ($responsibleGroups as $groupId)
				{
					$r = \Bitrix\Sale\Internals\CompanyResponsibleGroupTable::add(array(
																		   'COMPANY_ID' => $id,
																		   'GROUP_ID' => $groupId,
																	   ));

				}

			}

			if (strlen($request->getPost("apply")) == 0)
				LocalRedirect("/bitrix/admin/sale_company.php?lang=".$lang."&".GetFilterParams("filter_", false));
			else
				LocalRedirect("/bitrix/admin/sale_company_edit.php?lang=".$lang."&ID=".$id."&".GetFilterParams("filter_", false));
		}
		else
		{
			$errorMessage .= join("\n", $result->getErrorMessages());
		}
	}
}

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

Main\Page\Asset::getInstance()->addJs("/bitrix/js/sale/company.js");

if ($errorMessage !== '')
	CAdminMessage::ShowMessage($errorMessage);
if ($id > 0)
{
	$select = array('*', 'CREATED', 'MODIFIED');
	$fields = $USER_FIELD_MANAGER->GetUserFields(CompanyTable::getUfId());
	foreach ($fields as $field)
		$select[] = $field['FIELD_NAME'];

	$params = array(
		'select' => $select,
		'filter' => array('ID' => $id)
	);
	$res = CompanyTable::getList($params);
	$company = $res->fetch();

	$APPLICATION->SetTitle(str_replace("#NAME#", $company['NAME'], GetMessage("COMPANY_TITLE_UPDATE")));
}
else
{
	$APPLICATION->SetTitle(GetMessage("COMPANY_TITLE_ADD"));
}

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("COMPANY_TAB"), "TITLE" => GetMessage("COMPANY_TAB_DESCR")),
	array("DIV" => "edit2", "TAB" => GetMessage("COMPANY_USER_FIELD_TAB"), "TITLE" => GetMessage("COMPANY_USER_FIELD_TAB_DESCR")),
	array("DIV" => "edit3", "TAB" => GetMessage("COMPANY_RULES_USE_TAB"), "TITLE" => GetMessage("COMPANY_RULES_USE_TAB_DESCR")),
);
$tabControl = new CAdminForm("company_edit", $aTabs);
$tabControl->BeginPrologContent();
echo $USER_FIELD_MANAGER->ShowScript();
$tabControl->EndPrologContent();

$tabControl->BeginEpilogContent();
echo bitrix_sessid_post();
?>
<input type="hidden" name="update" value="Y">
<input type="hidden" name="lang" value="<?=$lang;?>">
<input type="hidden" name="ID" value="<?=$id;?>">
<?
$tabControl->EndEpilogContent();
$tabControl->Begin(array("FORM_ACTION" => $APPLICATION->GetCurPage()."?ID=".$id."&lang=".$lang));

$tabControl->BeginNextFormTab();

$fields = ($request->isPost()) ? $_POST : $company;

$tabControl->AddViewField("ID", "ID:", $company['ID']);
if ($id > 0)
{
	$createdBy = htmlspecialcharsbx($company['SALE_INTERNALS_COMPANY_CREATED_LAST_NAME']).' '.htmlspecialcharsbx($company['SALE_INTERNALS_COMPANY_CREATED_NAME']);
	$modifiedBy = htmlspecialcharsbx($company['SALE_INTERNALS_COMPANY_CREATED_LAST_NAME']).' '.htmlspecialcharsbx($company['SALE_INTERNALS_COMPANY_CREATED_NAME']);
	$tabControl->AddViewField('DATE_CREATE', GetMessage("COMPANY_DATE_CREATE"), $company['DATE_CREATE']);
	$tabControl->AddViewField('DATE_MODIFY', GetMessage("COMPANY_DATE_MODIFY"), $company['DATE_MODIFY']);
	$tabControl->AddViewField('CREATED_BY', GetMessage("COMPANY_CREATED_BY"), $createdBy);
	if ($modifiedBy)
		$tabControl->AddViewField('MODIFIED_BY', GetMessage("COMPANY_MODIFIED_BY"), $modifiedBy);
}
$tabControl->AddCheckBoxField("ACTIVE", GetMessage("COMPANY_ACTIVE"), false, 'Y', $fields['ACTIVE'] != 'N');
$tabControl->AddEditField("NAME", GetMessage("COMPANY_NAME"), true, array('size' => 120), htmlspecialcharsbx($fields['NAME']));

$tabControl->BeginCustomField('LOCATIONS', GetMessage("COMPANY_LOCATION_ID"));
if ($saleModulePermissions >= 'W'):?>
	<tr>
		<td style="vertical-align: top"><?=GetMessage("COMPANY_LOCATION_ID");?></td>
		<td>
			<?$APPLICATION->IncludeComponent("bitrix:sale.location.selector.".\Bitrix\Sale\Location\Admin\LocationHelper::getWidgetAppearance(), "", array(
					"ID" => "",
					"CODE" => $fields['LOCATION_ID'],
					"INPUT_NAME" => "LOCATION_ID",
					"PROVIDE_LINK_BY" => "code",
					"SHOW_ADMIN_CONTROLS" => 'Y',
					"SELECT_WHEN_SINGLE" => 'N',
					"FILTER_BY_SITE" => 'Y',
					"FILTER_SITE_ID" => Application::getInstance()->getContext()->getSite(),
					"SHOW_DEFAULT_LOCATIONS" => 'N',
					"SEARCH_BY_PRIMARY" => 'Y'
				),
				false
			);?>
		</td>
	</tr>
<?
else:
	try
	{
		$res = \Bitrix\Sale\Location\LocationTable::getPathToNodeByCode(
				$fields['LOCATION_ID'],
				array(
					'select' => array('CHAIN' => 'NAME.NAME'),
					'filter' => array('NAME.LANGUAGE_ID' => $lang)
				)
		);

		$chain = array();
		while ($item = $res->fetch())
			$chain[] = $item['CHAIN'];

		$path = implode(', ', array_reverse($chain));
	}
	catch (Main\SystemException $e)
	{
		$path = '';
	}
?>
	<tr>
		<td><?=GetMessage("COMPANY_LOCATION");?></td>
		<td><?=$path;?></td>
	</tr>
<?
endif;
$tabControl->EndCustomField('LOCATIONS', '');

$tabControl->AddTextField("ADDRESS", GetMessage("COMPANY_LOCATION"), htmlspecialcharsbx($fields['ADDRESS']), array('cols' => 60, 'rows' => 5));

$tabControl->BeginCustomField('USER_GROUPS', GetMessage("COMPANY_GROUPS"));
$currentGroups = array();
if ($id > 0)
{
	$resCompayGroup = \Bitrix\Sale\Internals\CompanyGroupTable::getList(array(
		'filter' => array('=COMPANY_ID' => $id),
		'select' => array('GROUP_ID')
	));
	while($companyGroup = $resCompayGroup->fetch())
	{
		$currentGroups[] = $companyGroup['GROUP_ID'];
	}
}

$b = "c_sort";
$o = "asc";
$userGroupList = array();
$resGroups = CGroup::GetList($b, $o, array("ANONYMOUS" => "N"));
while ($groupData = $resGroups->Fetch())
{
	$groupData["ID"] = (int)$groupData["ID"];
	$userGroupList[] = $groupData;
}

?>
<tr>
	<td style="vertical-align: top"><?=GetMessage("COMPANY_GROUPS");?></td>
	<td>
		<select name="GROUPS[]" multiple size="5">
			<?
			foreach ($userGroupList as $userGroupData)
			{
				?><option value="<?= $userGroupData["ID"] ?>"<?if (in_array($userGroupData["ID"], $currentGroups)) echo " selected";?>><?= htmlspecialcharsEx($userGroupData["NAME"]) ?></option><?
			}
			?>
		</select>
	</td>
</tr>
<?
$tabControl->EndCustomField('USER_GROUPS', '');

$tabControl->BeginCustomField('RESPONSIBLE_USER_GROUPS', GetMessage("COMPANY_RESPONSIBLE_GROUPS"));
$currentResponsibleGroups = array();
if ($id > 0)
{
	$resCompayGroup = \Bitrix\Sale\Internals\CompanyResponsibleGroupTable::getList(array(
		'filter' => array('=COMPANY_ID' => $id),
		'select' => array('GROUP_ID')
	));
	while($companyGroup = $resCompayGroup->fetch())
	{
		$currentResponsibleGroups[] = $companyGroup['GROUP_ID'];
	}
}

$b = "c_sort";
$o = "asc";
$userGroupList = array();
$resGroups = CGroup::GetList($b, $o, array("ANONYMOUS" => "N"));
while ($groupData = $resGroups->Fetch())
{
	$groupData["ID"] = (int)$groupData["ID"];
	$userGroupList[] = $groupData;
}

?>
<tr>
	<td style="vertical-align: top"><?=GetMessage("COMPANY_RESPONSIBLE_GROUPS");?></td>
	<td>
		<select name="RESPONSIBLE_GROUPS[]" multiple size="5">
			<?
			foreach ($userGroupList as $userGroupData)
			{
				?><option value="<?= $userGroupData["ID"] ?>"<?if (in_array($userGroupData["ID"], $currentResponsibleGroups)) echo " selected";?>><?= htmlspecialcharsEx($userGroupData["NAME"]) ?></option><?
			}
			?>
		</select>
	</td>
</tr>
<?
$tabControl->EndCustomField('RESPONSIBLE_USER_GROUPS', '');
$tabControl->AddEditField("SORT", GetMessage("COMPANY_SORT"), false, array('size' => 30), $fields['SORT']);
$tabControl->AddEditField("CODE", GetMessage("COMPANY_CODE"), false, array('size' => 30), htmlspecialcharsbx($fields['CODE']));

$tabControl->BeginNextFormTab();
$tabControl->ShowUserFieldsWithReadyData(CompanyTable::getUfId(), $fields, false, 'ID');

if ($id > 0):
	$tabControl->BeginNextFormTab();
	$tabControl->BeginCustomField('COMPANY_RULES', GetMessage("COMPANY_RULES"));

	ob_start();
	require_once($documentRoot."/bitrix/modules/sale/admin/company_rules_list.php");
	$companyRules = ob_get_contents();
	ob_end_clean();
?>
	<tr>
		<td id="sale-company-rules-container"><?=$companyRules?></td>
	</tr>
<?
$tabControl->EndCustomField('COMPANY_RULES');
endif;

$tabControl->Buttons(array(
	"disabled" => ($saleModulePermissions < 'W'),
	"back_url" => "sale_company.php?lang=".$lang
));

$tabControl->Show();
?>

<script language="JavaScript">
	BX.message({
		SALE_COMPANY_RULE_TITLE: '<?=Loc::getMessage("SALE_COMPANY_RULE_TITLE")?>',
		SALE_COMPANY_RULE_SAVE: '<?=Loc::getMessage("SALE_COMPANY_RULE_SAVE")?>',
		SALE_PS_MODE: '<?=Loc::getMessage("F_PS_MODE")?>',
		SALE_BT_DEL: '<?=Loc::getMessage("SPS_LOGOTIP_DEL")?>'
	});

	var i;
	var isFormChange = false;
	var companyForm = BX('company_edit_form');

	var inputList = BX.findChildren(companyForm, {tag : 'input'}, true);
	for (i in inputList)
	{
		if (inputList.hasOwnProperty(i))
			BX.bind(inputList[i], 'change', function () {isFormChange = true});
	}

	var selectList = BX.findChildren(companyForm, {tag : 'select'}, true);
	for (i in selectList)
	{
		if (selectList.hasOwnProperty(i))
			BX.bind(selectList[i], 'change', function () {isFormChange = true});
	}

	var hrefList = BX.findChildren(companyForm, {tag : 'a'}, true);
	for (i in hrefList)
	{
		if (hrefList.hasOwnProperty(i))
		{
			if (hrefList[i].getAttribute('target') != '_blank' && hrefList[i].getAttribute('href').indexOf('javascript') < 0)
			{
				BX.bind(hrefList[i], 'click', function (e)
				{
					if (isFormChange)
					{
						if (!confirm(<?=Loc::getMessage('SALE_COMPANY_CHANGE_FORM_VALUE');?>))
						{
							if (!e) e = window.event;
							e.preventDefault();
						}
					}
				});
			}
		}
	}

</script>
<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>
