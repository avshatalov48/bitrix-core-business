<?
define("ADMIN_MODULE_NAME", "sender");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!\Bitrix\Main\Loader::includeModule("sender"))
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("sender_group_adm_tab_grp"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("sender_group_adm_tab_grp_title")),
	array("DIV" => "edit2", "TAB" => GetMessage("sender_group_adm_tab_param"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("sender_group_adm_tab_param_title")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($ID);		// Id of the edited record
$message = null;
$bVarsFromForm = false;

if($REQUEST_METHOD == "POST" && ($save!="" || $apply!="") && $POST_RIGHT=="W" && check_bitrix_sessid())
{
	$arError = array();
	$NAME = trim($NAME);
	$arFields = Array(
		"ACTIVE"	=> ($ACTIVE <> "Y"? "N":"Y"),
		"NAME"		=> $NAME,
		"SORT"		=> $SORT,
		"DESCRIPTION"	=> $DESCRIPTION,
	);

	$res = false;
	if(is_array($CONNECTOR_SETTING) && count($CONNECTOR_SETTING)>0)
	{
		if($ID > 0)
		{
			$groupUpdateDb = \Bitrix\Sender\GroupTable::update($ID, $arFields);
			if($groupUpdateDb->isSuccess())
			{
				$res = ($ID > 0);
			}
			else
			{
				$arError = $groupUpdateDb->getErrorMessages();
			}
		}
		else
		{
			$groupAddDb = \Bitrix\Sender\GroupTable::add($arFields);
			if($groupAddDb->isSuccess())
			{
				$ID = $groupAddDb->getId();
				$res = ($ID > 0);
			}
			else
			{
				$arError = $groupAddDb->getErrorMessages();
			}
		}
	}
	else
	{
		$arError[] = GetMessage('sender_group_conn_not_selected');
	}

	if($res)
	{
		if(is_array($CONNECTOR_SETTING))
		{
			$groupConnectorsDataCount = 0;
			\Bitrix\Sender\GroupConnectorTable::delete(array('GROUP_ID' => $ID));
			$arEndpointList = \Bitrix\Sender\ConnectorManager::getEndpointFromFields($CONNECTOR_SETTING);
			foreach ($arEndpointList as $endpoint)
			{
				$connector = \Bitrix\Sender\ConnectorManager::getConnector($endpoint);
				if ($connector)
				{
					$connector->setFieldValues($endpoint['FIELDS']);
					$connectorDataCount = $connector->getDataCount();
					$arGroupConnectorAdd = array(
						'GROUP_ID' => $ID,
						'NAME' => $connector->getName(),
						'ENDPOINT' => $endpoint,
						'ADDRESS_COUNT' => $connectorDataCount
					);

					$groupConnectorAddDb = \Bitrix\Sender\GroupConnectorTable::add($arGroupConnectorAdd);
					if($groupConnectorAddDb->isSuccess())
					{
						$groupConnectorsDataCount += $connectorDataCount;
					}
				}
			}
			\Bitrix\Sender\GroupTable::update($ID, array('ADDRESS_COUNT' => $groupConnectorsDataCount));
		}

		if($apply!="")
			LocalRedirect("/bitrix/admin/sender_group_edit.php?ID=".$ID."&lang=".LANG."&".$tabControl->ActiveTabParam());
		else
			LocalRedirect("/bitrix/admin/sender_group_admin.php?lang=".LANG);
	}
	else
	{
		if(!empty($arError))
			$message = new CAdminMessage(implode("<br>", $arError));
		$bVarsFromForm = true;
	}

}

//Edit/Add part
ClearVars();
$str_SORT = 100;
$str_ACTIVE = "Y";
$str_VISIBLE = "Y";

if($ID>0)
{
	$rubric = new CDBResult(\Bitrix\Sender\GroupTable::getById($ID));
	if(!$rubric->ExtractFields("str_"))
		$ID=0;
}

$endpointList = array();
if($ID>0)
{
	$groupConnectorDb = \Bitrix\Sender\GroupConnectorTable::getList(array('filter'=>array('GROUP_ID'=>$ID)));
	while($groupConnector = $groupConnectorDb->fetch())
	{
		if(!empty($groupConnector['ENDPOINT']))
			$endpointList[] = $groupConnector['ENDPOINT'];
	}
}

if($bVarsFromForm)
	$DB->InitTableVarsForEdit("b_sender_group", "", "str_");


if(isset($CONNECTOR_SETTING))
	$arConnectorSettings = $CONNECTOR_SETTING;
else
	$arConnectorSettings = array();


if(count($endpointList)>0)
{
	$arConnectorSettings = \Bitrix\Sender\ConnectorManager::getFieldsFromEndpoint($endpointList);
}

$arAvailableConnectors = array();
$arExistedConnectors = array();
$arConnector = \Bitrix\Sender\ConnectorManager::getConnectorList();
/** @var \Bitrix\Sender\Connector $connector */
foreach($arConnector as $connector)
{
	if(array_key_exists($connector->getModuleId(), $arConnectorSettings))
		$arFieldsValues = $arConnectorSettings[$connector->getModuleId()][$connector->getCode()];
	else
		$arFieldsValues = array();

	$connector->setFieldPrefix('CONNECTOR_SETTING');
	$connectorIdCount = 0;

	$arAvailableConnectors[$connector->getId()] = array(
		'ID' => $connector->getId(),
		'NAME' => $connector->getName(),
		'FORM' => $connector->getForm().'<input type="hidden" name="'.$connector->getFieldName('bx_aux_hidden_field').'" value="0">'
	);

	if( array_key_exists($connector->getModuleId(), $arConnectorSettings) )
	{
		if( array_key_exists($connector->getCode(), $arConnectorSettings[$connector->getModuleId()]) )
		{
			$connectorIdCount = 0;
			$arFieldsValuesConnector = $arConnectorSettings[$connector->getModuleId()][$connector->getCode()];
			foreach($arFieldsValuesConnector as $fieldValues)
			{
				$connector->setFieldFormName('post_form');
				$connector->setFieldValues($fieldValues);
				$arExistedConnectors[] = array(
					'ID' => $connector->getId(),
					'NAME' => $connector->getName(),
					'FORM' => str_replace('%CONNECTOR_NUM%', $connectorIdCount, $connector->getForm().'<input type="hidden" name="'.$connector->getFieldName('bx_aux_hidden_field').'" value="0">'),
					'COUNT' => $connector->getDataCount()
				);

				$connectorIdCount++;
			}
		}
	}
}

\CJSCore::Init(array("sender_admin"));
$APPLICATION->SetTitle(($ID>0? GetMessage("sender_group_title_edit").$ID : GetMessage("sender_group_title_add")));
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>GetMessage("sender_group_list"),
		"TITLE"=>GetMessage("sender_group_list_title"),
		"LINK"=>"sender_group_admin.php?lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0)
{
	$aMenu[] = array("SEPARATOR"=>"Y");
	$aMenu[] = array(
		"TEXT"=>GetMessage("sender_group_add"),
		"TITLE"=>GetMessage("sender_group_add_title"),
		"LINK"=>"sender_group_edit.php?lang=".LANG,
		"ICON"=>"btn_new",
	);
	$aMenu[] = array(
		"TEXT"=>GetMessage("sender_group_del"),
		"TITLE"=>GetMessage("sender_group_del_title"),
		"LINK"=>"javascript:if(confirm('".GetMessage("sender_group_del_confirm")."'))window.location='sender_group_admin.php?ID=".$ID."&action=delete&lang=".LANG."&".bitrix_sessid_get()."';",
		"ICON"=>"btn_delete",
	);
}
$context = new CAdminContextMenu($aMenu);
$context->Show();
?>

<?
if($_REQUEST["mess"] == "ok" && $ID>0)
	CAdminMessage::ShowMessage(array("MESSAGE"=>GetMessage("rub_saved"), "TYPE"=>"OK"));

if($message)
	echo $message->Show();
elseif($rubric->LAST_ERROR!="")
	CAdminMessage::ShowMessage($rubric->LAST_ERROR);
?>

<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>" name="post_form">
<?
$tabControl->Begin();
?>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td width="40%"><?echo GetMessage("sender_group_field_active")?></td>
		<td width="60%"><input type="checkbox" name="ACTIVE" value="Y"<?if($str_ACTIVE == "Y") echo " checked"?>></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("sender_group_field_name")?></td>
		<td><input type="text" name="NAME" value="<?echo $str_NAME;?>" size="45" maxlength="100"></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("sender_group_field_sort")?></td>
		<td><input type="text" name="SORT" value="<?echo $str_SORT;?>" size="6"></td>
	</tr>
	<tr>
		<td class="adm-detail-valign-top"><?echo GetMessage("sender_group_field_desc")?></td>
		<td><textarea class="typearea" name="DESCRIPTION" cols="45" rows="5" wrap="VIRTUAL" style="width:100%"><?echo $str_DESCRIPTION; ?></textarea></td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2">
			<div class="adm-info-message">
				<p class="sender-text-description-header">
					<?echo GetMessage("sender_group_conn_title")?>
				</p>
				<p class="sender-text-description-detail">
					<?echo GetMessage("sender_group_conn_desc")?>
				</p>
				<p class="sender-text-description-detail">
					<?echo GetMessage("sender_group_conn_desc_example")?>
				</p>
			</div>
			<br/>
		</td>
	</tr>

	<tr class="adm-detail-required-field">
		<td colspan="2">

		<script type="text/template" id="connector-template">
			<?
			ob_start();
			?><div class="sender-box-list-item sender-box-list-item-hidden connector_form">
				<div class="sender-box-list-item-caption" onclick='ConnectorSettingShowToggle(this);'>
					<span class="sender-box-list-item-caption-image" ></span>
					<span class="sender-box-list-item-caption-name" >%CONNECTOR_NAME%</span>
					<span class="sender-mailing-sprite sender-box-list-item-caption-delete" onclick='ConnectorSettingDelete(this);'></span>
					<span class="sender-box-list-item-caption-additional">
						<span class="sender-box-list-item-caption-additional-less"><?=GetMessage('sender_group_conn_cnt')?>: </span>
						<span class="connector_form_counter">%CONNECTOR_COUNT%</span>
					</span>
				</div>
				<div class="sender-box-list-item-block connector_form_container">
					<div class="sender-box-list-item-block-item">%CONNECTOR_FORM%</div>
				</div>
			</div>
			<?
			$connectorTemplate = ob_get_clean();
			echo $connectorTemplate;
			?>
		</script>

		<script>
			var connectorListToAdd = <?=CUtil::PhpToJSObject($arAvailableConnectors)?>;
			BX.ready(function(){
				ConnectorSettingWatch();
			});
		</script>


		<div class="sender-box-selector">
			<div class="sender-box-selector-control">
				<select id="connector_list_to_add">
					<?
					if(count($arAvailableConnectors)<=0)
					{
						echo GetMessage('sender_group_conn_not_availabe');
					}
					else
					{
						foreach ($arAvailableConnectors as $connectorId => $availableConnector)
						{
							?>
							<option value="<?= htmlspecialcharsbx($availableConnector['ID']) ?>">
								<?= htmlspecialcharsbx($availableConnector['NAME']) ?>
							</option>
						<?
						}
					}
					?>
				</select> &nbsp; <input type="button" value="<?=GetMessage('sender_group_conn_add')?>" onclick="addNewConnector();">
			</div>
		</div>
		<div id="connector_form_container" class="sender-box-list">
			<?
			$groupAddressCount = 0;
			foreach($arExistedConnectors as $existedConnector)
			{
				$existedConnectorTemplateValues = array(
					'%CONNECTOR_NAME%' => $existedConnector['NAME'],
					'%CONNECTOR_COUNT%' => $existedConnector['COUNT'],
					'%CONNECTOR_FORM%' => $existedConnector['FORM'],
				);
				echo str_replace(
					array_keys($existedConnectorTemplateValues),
					array_values($existedConnectorTemplateValues),
					$connectorTemplate
				);

				$groupAddressCount += $existedConnector['COUNT'];
			}
			?>
		</div>
		<div id="connector_form_container" class="sender-group-address-counter">
			<span class="sender-mailing-sprite sender-group-address-counter-img"></span>
			<span class="sender-group-address-counter-text"><?=GetMessage('sender_group_conn_cnt_all')?></span>
			<span id="sender_group_address_counter" class="sender-group-address-counter-cnt"><?=$groupAddressCount?></span>
		</div>
		</td>
	</tr>
<?
$tabControl->Buttons(
	array(
		"disabled"=>($POST_RIGHT<"W"),
		"back_url"=>"sender_group_admin.php?lang=".LANG,

	)
);
?>
<?echo bitrix_sessid_post();?>
<input type="hidden" name="lang" value="<?=LANG?>">
<?if($ID>0 && !$bCopy):?>
	<input type="hidden" name="ID" value="<?=$ID?>">
<?endif;?>
<?
$tabControl->End();
?>

<?
$tabControl->ShowWarnings("post_form", $message);
?>


<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>