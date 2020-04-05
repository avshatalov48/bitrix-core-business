<?
define("ADMIN_MODULE_NAME", "sender");

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

if(!\Bitrix\Main\Loader::includeModule("sender"))
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("MAIN_MODULE_NOT_INSTALLED"));

IncludeModuleLangFile(__FILE__);

$POST_RIGHT = $APPLICATION->GetGroupRight("sender");
if($POST_RIGHT=="D")
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

$isUserHavePhpAccess = $USER->CanDoOperation('edit_php');


$aTabs = array(
	array("DIV" => "edit1", "TAB" => GetMessage("sender_chain_edit_tab_main"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("sender_chain_edit_tab_main_title")),
	array("DIV" => "edit2", "TAB" => GetMessage("sender_chain_edit_tab_message"), "ICON"=>"main_user_edit", "TITLE"=>GetMessage("sender_chain_edit_tab_message_title")),
);
$tabControl = new CAdminTabControl("tabControl", $aTabs);

$ID = intval($_REQUEST['ID']);		// Id of the edited record
$message = null;
$bVarsFromForm = false;


function getSenderItemContainer($id, array $chain = array())
{
	$i = '%SENDER_LETTER_TEMPLATE_BODY_NUM%';

	ob_start();
	?>
	<div class="sender-trigger-chain-container-letter">
		<div class="sender-trigger-status-mailing-time">
			<?=GetMessage("sender_chain_edit_field_time_thr")?> <span class="sender_letter_container_time_text">*</span> <?=GetMessage("sender_chain_edit_field_time_after")?>
			<span class="sender_letter_container_time_text_first">&nbsp;<?=GetMessage("sender_chain_edit_field_time_event")?></span>
			<span style="display: none;" class="sender_letter_container_time_text_nonfirst">&nbsp;<?=GetMessage("sender_chain_edit_field_time_letter")?></span>
			&nbsp;&nbsp;
			<a id="SENDER_TRIGGER_CHAIN_TIME_BNT_<?=$i?>" href="javascript: void(0);" class="sender_letter_container_time_button sender-link-email"><?=GetMessage("sender_chain_edit_field_time_change")?></a>
		</div>
		<div class="sender_letter_container" id="SENDER_TRIGGER_CHAIN_<?=$i?>">
			<input type="hidden" name="CHAIN[<?=$i?>][ID]" value="<?=htmlspecialcharsbx($chain['ID'])?>">
			<input class="sender_letter_container_sorter" type="hidden" name="CHAIN[<?=$i?>][ITEM_SORT]" value="<?=$i?>">
			<input class="sender_letter_container_time" type="hidden" name="CHAIN[<?=$i?>][TIME_SHIFT]" value="<?=intval($chain['TIME_SHIFT'])?>">

			<div class="sender_letter_container_head">
				<div class="sender_letter_container_move"><div class="sender_letter_container_burger"></div></div>
				<div class="sender_letter_container_sorter_view">
					<span class="sender_letter_container_sorter_icon">
						<span class="sender_letter_container_sorter_text"><?=$i?></span>
					</span>
				</div>
				<h3><span class="sender_letter_container_caption"><?=htmlspecialcharsbx($chain['SUBJECT'])?></span></h3>
				<span class="sender_letter_container-info">
					<?if(!empty($chain['ID']) && empty($chain['DATE_INSERT'])):?>
						<span><?=GetMessage("sender_chain_edit_field_created_exists_but_not_save")?></span>
					<?elseif(!empty($chain['ID'])):?>
						<span class="sender_letter_container-create"><?=GetMessage("sender_chain_edit_field_created")?></span>
						<span>
							<?
							echo GetMessage("sender_chain_edit_field_created_text", array(
								'%DATE_CREATE%' => htmlspecialcharsbx(is_object($chain['DATE_INSERT']) ? \Bitrix\Main\Type\Date::createFromTimestamp($chain['DATE_INSERT']->getTimestamp()) : $chain['DATE_INSERT']),
								'%AUTHOR%' => '<a class="sender_letter_container-author" href="/bitrix/admin/user_edit.php?ID='.htmlspecialcharsbx($chain['CREATED_BY']).'&lang='.LANGUAGE_ID.'">'.htmlspecialcharsbx($chain['CREATED_BY_NAME']).' '.htmlspecialcharsbx($chain['CREATED_BY_LAST_NAME']).'</a>',
							));
							?>
						</span>
					<?else:?>
						<span><?=GetMessage("sender_chain_edit_field_created_new")?></span>
					<?endif;?>
				</span>
				<a class="sender_letter_container_button_delete" href="javascript: void(0);" title="<?=GetMessage("sender_chain_edit_field_delete")?>"></a>
				<?if(strlen($chain['SUBJECT'])>0 && strlen($chain['MESSAGE'])>0):?>
					<a class="sender_letter_container_button_show" href="javascript: void(0);">
						<?=GetMessage('SENDER_MAILING_TRIG_LETTER_MESSAGE_SHOW')?>
					</a>
				<?else:?>
					<a class="sender_letter_container_button_show sender_letter_container_button_hide" href="javascript: void(0);">
						<?=GetMessage('SENDER_MAILING_TRIG_LETTER_MESSAGE_HIDE')?>
					</a>
				<?endif;?>
			</div>
			<div class="sender_letter_container_body" <?=((strlen($chain['SUBJECT'])>0 && strlen($chain['MESSAGE'])>0) ? 'style="display:none;"' : '')?>>
				<div class="sender_letter_container_body_tmpl" id="CHAIN_TEMPLATE_NUM_<?=$i?>" <?=(strlen($chain['MESSAGE'])>0 ? 'style="display:none;"' : '')?>>
					<?=\Bitrix\Sender\Preset\Template::getTemplateListHtml('SENDER_TRIGGER_CHAIN_'.$i)?>
				</div>
				<div class="sender_letter_container_body_fields" <?=(strlen($chain['MESSAGE'])>0 ? '' : 'style="display:none;"')?>>
					<table class="trigger_chain_item">
						<tr>
							<td><?echo GetMessage("sender_chain_edit_field_sel_templ")?></td>
							<td>
								<span class="sender-template-message-caption-container"></span>
								&nbsp;
								<a href="javascript:void(0);" class="sender-template-message-caption-container-btn sender-link-email">
									<?echo GetMessage("sender_chain_edit_field_sel_templ_another")?>
								</a>
							</td>
						</tr>
						<tr>
							<td><?echo GetMessage("sender_chain_edit_field_subject")?></td>
							<td>
								<input class="sender_letter_container_subject" type="text" id="CHAIN_<?=$i?>_SUBJECT" name="CHAIN[<?=$i?>][SUBJECT]" value="<?=htmlspecialcharsbx($chain['SUBJECT'])?>">
							</td>
						</tr>
						<tr>
							<td>&nbsp;</td>
							<td>
								<?
								$arPersonalizeList = \Bitrix\Sender\PostingRecipientTable::getPersonalizeList();
								?>
								<?echo GetMessage("sender_chain_edit_field_subject_personalize")?>
								<?foreach($arPersonalizeList as $arPersonalize):?>
								<a class="sender-link-email" onclick="SetAddressToControl('CHAIN_<?=$i?>_SUBJECT', ' #<?=htmlspecialcharsbx($arPersonalize['CODE'])?>#', true)" title="#<?=htmlspecialcharsbx($arPersonalize['CODE'])?># - <?=htmlspecialcharsbx($arPersonalize['DESC'])?>">
									<?=htmlspecialcharsbx($arPersonalize['NAME'])?>
									</a><?=(end($arPersonalizeList)===$arPersonalize ? '' : ',')?>
								<?endforeach?>
								<span style="cursor: pointer;" class="hidden-when-show-template-list-info" onclick="ShowPersonalizeDescDialog(this);">&nbsp;</span>
							</td>
						</tr>
						<tr>
							<td style="font-weight: normal;"><?echo GetMessage("sender_chain_edit_field_priority")?></td>
							<td>
								<input type="text" id="CHAIN_<?=$i?>_PRIORITY" name="CHAIN[<?=$i?>][PRIORITY]" value="<?=htmlspecialcharsbx($chain['PRIORITY'])?>">
								<select onchange="document.getElementById('CHAIN_<?=$i?>_PRIORITY').value=this.value">
									<option value=""></option>
									<option value="1 (Highest)"<?if($str_PRIORITY=='1 (Highest)')echo ' selected'?>><?echo GetMessage("sender_chain_edit_field_priority_1")?></option>
									<option value="3 (Normal)"<?if($str_PRIORITY=='3 (Normal)')echo ' selected'?>><?echo GetMessage("sender_chain_edit_field_priority_3")?></option>
									<option value="5 (Lowest)"<?if($str_PRIORITY=='5 (Lowest)')echo ' selected'?>><?echo GetMessage("sender_chain_edit_field_priority_5")?></option>
								</select>
							</td>
						</tr>
						<tr>
							<td colspan="2">
								<b><?echo GetMessage("sender_chain_edit_field_message")?></b>
								<br>
								<br>
								%SENDER_LETTER_TEMPLATE_MESSAGE%
							</td>
						</tr>
						<tr>
							<td style="font-weight: normal;"><?echo GetMessage("sender_chain_edit_field_linkparams")?></td>
							<td>
								<input class="sender_letter_container_subject" type="text" id="CHAIN_<?=$i?>_LINK_PARAMS" name="CHAIN[<?=$i?>][LINK_PARAMS]" value="<?=htmlspecialcharsbx($chain['LINK_PARAMS'])?>">
							</td>
						</tr>
					</table>
				</div>
			</div>
		</div>
	</div>
	<?

	return ob_get_clean();
}

$personalizeList = \Bitrix\Sender\MailingTable::getChainPersonalizeList($ID);
\Bitrix\Sender\PostingRecipientTable::setPersonalizeList($personalizeList);
if($_REQUEST["action"]=="get_vr" && check_bitrix_sessid())
{
	$letterTemplate = array(
		'BODY' => getSenderItemContainer($ID),
		'FIELDS' => array(
			'MESSAGE' =>  \Bitrix\Sender\TemplateTable::initEditor(array(
				'FIELD_NAME' => 'SENDER_LETTER_TEMPLATE_MESSAGE',
				'FIELD_VALUE' => '',
				'TEMPLATE_TYPE_INPUT' => 'CHAIN[%SENDER_LETTER_TEMPLATE_BODY_NUM%][TEMPLATE_TYPE]',
				'TEMPLATE_TYPE' => '',
				'TEMPLATE_ID_INPUT' => 'CHAIN[%SENDER_LETTER_TEMPLATE_BODY_NUM%][TEMPLATE_ID]',
				'TEMPLATE_ID' => '',
				'HAVE_USER_ACCESS' => !$isUserHavePhpAccess,
				'SHOW_SAVE_TEMPLATE' => false,
			)),
		)
	);

	echo CUtil::PhpToJSObject($letterTemplate);
	exit();
}




if(!is_array($CHAIN)) $CHAIN = array();

$mailingDb = \Bitrix\Sender\MailingTable::getList(array(
	'select' => array('ID', 'NAME', 'EMAIL_FROM', 'ACTIVE', 'TRIGGER_FIELDS'),
	'filter' => array(
		'=IS_TRIGGER' => 'Y',
		'=ID' => $ID
	),
));
if(!$mailing = $mailingDb->fetch())
{
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");
	ShowError(\Bitrix\Main\Localization\Loc::getMessage("SENDER_MAILING_TRIG_ERROR_NOT_FOUND"));
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
	exit();
}


$errorList = array();
$chainFieldsList = array();

if(in_array($_REQUEST["action"], array('activate', 'deactivate')) && check_bitrix_sessid() && $POST_RIGHT>="W")
{
	$fields = array();
	if($_REQUEST["action"] == 'activate')
		$fields['ACTIVE'] = 'Y';
	else
		$fields['ACTIVE'] = 'N';

	\Bitrix\Sender\MailingTable::update($ID, $fields);
	LocalRedirect('/bitrix/admin/sender_mailing_trig_edit.php?ID=' . $ID . '&lang=' . LANGUAGE_ID);
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && is_array($CHAIN) && ($save!="" || $apply!="") && $POST_RIGHT=="W" && check_bitrix_sessid())
{
	if(is_array($_POST)) foreach($_POST as $k => $v)
	{
		if (substr($k, 0, strlen('CHAIN_MESSAGE_')) == "CHAIN_MESSAGE_")
		{
			$chainMessage = explode('_', $k);
			$CHAIN[intval($chainMessage[2])][$chainMessage[1]] = $v;
		}
	}


	$arTriggerSettings = $CHAIN;



	// sort chain
	$arTriggerSettingsTmp = array();
	foreach($arTriggerSettings as $item)
	{
		$arTriggerSettingsTmp[intval($item['ITEM_SORT'])] = $item;
	}
	ksort($arTriggerSettingsTmp);
	$arTriggerSettings = $arTriggerSettingsTmp;

	// format chain fields
	$arTriggerSettingsTmp = array();
	foreach($arTriggerSettings as $item)
	{
		unset($item['ITEM_SORT']);
		$item['EMAIL_FROM'] = $mailing['EMAIL_FROM'];
		$item['CREATED_BY'] = $USER->GetID();
		$arTriggerSettingsTmp[] = $item;
	}
	$arTriggerSettings = $arTriggerSettingsTmp;

	// save chain
	$result = \Bitrix\Sender\MailingTable::updateChain($ID, $arTriggerSettings);
	$errorList = array_merge($errorList, $result->getErrorMessages());

	if(empty($errorList))
	{
		if($save != "")
			LocalRedirect('/bitrix/admin/sender_mailing_trig_admin.php?lang=' . LANGUAGE_ID);
		else
			LocalRedirect('/bitrix/admin/sender_mailing_trig_edit.php?ID=' . $ID . '&lang=' . LANGUAGE_ID);
	}

	$chainFieldsList = $arTriggerSettings;
}
else
{

	$chainFieldsList = \Bitrix\Sender\MailingTable::getChain($ID);
}


$numberChainItem = 0;
$chainFieldsListTmp = array();
foreach($chainFieldsList as $chain)
{
	$chain['ITEM_SORT'] = $numberChainItem++;
	$chainFieldsListTmp[] = $chain;
}
$chainFieldsList = $chainFieldsListTmp;


//Edit/Add part
ClearVars();

\CJSCore::Init(array("sender_admin"));
$APPLICATION->SetTitle(GetMessage("sender_chain_edit_title_edit") . ' "' . $mailing['NAME'] . '"');
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

$aMenu = array(
	array(
		"TEXT"=>GetMessage("sender_chain_edit_list"),
		"TITLE"=>GetMessage("sender_chain_edit_list_title"),
		"LINK"=>"/bitrix/admin/sender_mailing_trig_admin.php?MAILING_ID=".$MAILING_ID."&lang=".LANG,
		"ICON"=>"btn_list",
	)
);
if($ID>0 && $POST_RIGHT>="W")
{
	$aMenu[] = array("SEPARATOR"=>"Y");
}
$context = new CAdminContextMenu($aMenu);
$context->Show();



// show errors
if(!empty($errorList))
{
	$message = new CAdminMessage(implode("<br>", $errorList));
	echo $message->Show();
}

CJSCore::RegisterExt('sender_dragdrop', array('js' => '/bitrix/js/main/core/core_dragdrop.js'));
\CJSCore::Init(array("sender_admin", "sender_dragdrop"));
?>
	<script>
		BX.message({"SENDER_SHOW_TEMPLATE_LIST" : "<?=CUtil::AddSlashes(GetMessage('SENDER_SHOW_TEMPLATE_LIST'))?>"});
		BX.message({"SENDER_MAILING_TRIG_LETTER_MESSAGE_SHOW" : "<?=CUtil::AddSlashes(GetMessage('SENDER_MAILING_TRIG_LETTER_MESSAGE_SHOW'))?>"});
		BX.message({"SENDER_MAILING_TRIG_LETTER_MESSAGE_HIDE" : "<?=CUtil::AddSlashes(GetMessage('SENDER_MAILING_TRIG_LETTER_MESSAGE_HIDE'))?>"});

		function ShowPersonalizeDescDialog(obj)
		{
			var popupWindow = BX.PopupWindowManager.create(
				'sender_personalize_help',
				obj,
				{
					'darkMode': false,
					'closeIcon': true,
					'content': '<div style=\'margin: 7px;\'><?=CUtil::AddSlashes(GetMessage('sender_chain_edit_pers_help'))?></div>'
				}
			);
			if(popupWindow)
			{
				popupWindow.setBindElement(obj);
				popupWindow.show();
			}
		}

		function SendTestMailing()
		{
			var data = {
				'action': 'send_to_me',
				'send_to_me_addr': BX('EMAIL_TO_ME').value
			};
			var url = '/bitrix/admin/sender_mailing_chain_admin.php?MAILING_ID=<?echo $ID?>&IS_TRIGGER=Y&lang=<?echo LANGUAGE_ID?>&<?echo bitrix_sessid_get()?>&action=js_send';
			ShowWaitWindow();
			BX.ajax.post(
				url,
				data,
				function(result){
					CloseWaitWindow();
					document.getElementById('test_mailing_cont').innerHTML = result;
				}
			);
		}

	</script>

<form method="POST" Action="<?echo $APPLICATION->GetCurPage()?>?ID=<?=$ID?>&lang=<?=LANGUAGE_ID?>" name="post_form" enctype="multipart/form-data">
<?
$tabControl->Begin();
?>

<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2">
			<div class="adm-info-message"><?=GetMessage("sender_chain_edit_maintext");?></div>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=GetMessage("sender_chain_edit_state1");?></td>
	</tr>
	<tr class="adm-detail-required-field">
		<td><?echo GetMessage("sender_chain_edit_field_status")?></td>
		<td>
			<div class="sender-mailing-status">
				<span class="sender-mailing-sprite sender-mailing-status-img sender-mailing-status-img-<?=(strtolower($str_STATUS))?>"></span>
				<span class="sender-mailing-status-text sender-mailing-status-text-<?=($mailing['ACTIVE'] == 'Y' ? 'S' : 'N')?>">
					<span>
						<?
						if($mailing['ACTIVE'] == 'Y')
						{
							echo GetMessage("sender_chain_edit_field_status_y");
						}
						else
						{
							echo GetMessage("sender_chain_edit_field_status_n");
						}
						?>
					</span>
				</span>
				<span>
					<?if($POST_RIGHT>="W" && $mailing['ACTIVE'] != 'Y'):?>
						<input style="margin-left: 80px;" type="button"
							value="<?echo GetMessage("sender_chain_edit_field_status_btn_y")?>"
							onclick="window.location='/bitrix/admin/sender_mailing_trig_edit.php?ID=<?=$ID?>&<?=bitrix_sessid_get()?>&action=activate&lang=<?=LANGUAGE_ID?>'"
							title="<?echo GetMessage("sender_chain_edit_btn_send_desc")?>" />
					<?elseif($POST_RIGHT>="W"):?>
						<input style="margin-left: 80px;" type="button"
							value="<?echo GetMessage("sender_chain_edit_field_status_btn_n")?>"
							onclick="window.location='/bitrix/admin/sender_mailing_trig_edit.php?ID=<?=$ID?>&<?=bitrix_sessid_get()?>&action=deactivate&lang=<?=LANGUAGE_ID?>'"
							title="<?echo GetMessage("sender_chain_edit_btn_send_desc")?>" />
					<?endif;?>
				</span>
			</div>
		</td>
	</tr>
	<tr class="adm-detail-required-field">
		<td colspan="2">
			<br>
			<?
			$arEmailFromList = \Bitrix\Sender\MailingChainTable::getEmailToMeList();
			if(!in_array($USER->GetEmail(), $arEmailFromList))
				$arEmailFromList[] = $USER->GetEmail();
			?>
			<table class="sender-test-send">
				<tr>
					<td class="sender-test-send-header"><span class="sender-mailing-sprite sender-test-send-header-img"></span></td>
					<td class="sender-test-send-header">
						<span><?echo GetMessage("sender_chain_edit_test_title")?></span>
						<br>
						<span class="sender-test-send-header-grey"><?=GetMessage("sender_chain_edit_test_desc")?></span>
					</td>
				</tr>
				<tr>
					<td></td>
					<td class="sender-test-caption"><span><?echo GetMessage("sender_chain_edit_test_field_recipient")?></span></td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="text" id="EMAIL_TO_ME" name="EMAIL_TO_ME"
						       value=""
						       placeholder="<?=GetMessage("sender_chain_edit_test_field_recipient_desc")?>"
						       style="width: 600px;">
					</td>
				</tr>
				<tr>
					<td class="sender-test-recent"></td>
					<td class="sender-test-recent"> <?=GetMessage("sender_chain_edit_test_last_recipient")?>
						<?foreach($arEmailFromList as $email):?>
						<a class="sender-link-email"
						   onclick="AddAddressToControl('EMAIL_TO_ME', '<?=CUtil::AddSlashes(htmlspecialcharsbx($email))?>')"
						   ondblclick="DeleteAddressFromControl('EMAIL_TO_ME', '<?=CUtil::AddSlashes(htmlspecialcharsbx($email))?>')"
							>
							<?=htmlspecialcharsbx($email)?>
							</a><?=(end($arEmailFromList)==$email ? '' : ',')?>
						<?endforeach?>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<input type="button" value="<?=GetMessage("sender_chain_edit_test_btn")?>" onclick="SendTestMailing();" <?=($POST_RIGHT>="W" ? "" : "disabled")?>>
					</td>
				</tr>
				<tr>
					<td></td>
					<td>
						<div id="test_mailing_cont"></div>
					</td>
				</tr>
			</table>
		</td>
	</tr>
<?
$tabControl->BeginNextTab();
?>
	<tr>
		<td colspan="2">
			<?
			$dictionaryTimeList = array(
				array(
					'TYPE' => 'MI',
					'TEXT' => GetMessage("sender_chain_edit_dict_time_mi"),
					'VALUE' => 1,
				),
				array(
					'TYPE' => 'HO',
					'TEXT' => GetMessage("sender_chain_edit_dict_time_ho"),
					'VALUE' => 60,
				),
				array(
					'TYPE' => 'DA',
					'TEXT' => GetMessage("sender_chain_edit_dict_time_da"),
					'VALUE' => 60*24,
				),
				array(
					'TYPE' => 'WE',
					'TEXT' => GetMessage("sender_chain_edit_dict_time_we"),
					'VALUE' => 60*24*7,
				),
				array(
					'TYPE' => 'MO',
					'TEXT' => GetMessage("sender_chain_edit_dict_time_mo"),
					'VALUE' => 60*24*30,
				),
			);
			?>
			<div id="SENDER_TIME_DIALOG" class="sender-time-dialog">
				<b><?=GetMessage("sender_chain_edit_time_dialog_title")?></b> <br><br>
				<select id="SENDER_TIME_DIALOG_TYPE">
					<?foreach($dictionaryTimeList as $timeItem):?>
						<option	value="<?=$timeItem['TYPE']?>"><?=$timeItem['TEXT']?></option>
					<?endforeach;?>
				</select>
				&nbsp;&nbsp;&nbsp;
				<input type="text" id="SENDER_TIME_DIALOG_VALUE" value="">
				<br><br>
				<input type="button" id="SENDER_TIME_DIALOG_BTN_SAVE" value="<?=GetMessage("sender_chain_edit_time_dialog_btn_apply")?>" class="adm-btn">
				<a href="javascript: void(0);" id="SENDER_TIME_DIALOG_BTN_CANCEL" class=""><?=GetMessage("sender_chain_edit_time_dialog_btn_cancel")?></a>
			</div>


			<div class="sender-trigger-status-mailing">
				<div class="sender-trigger-status-mailing-title"><?=GetMessage("sender_chain_edit_list_event_start")?></div>
				<div class="sender-mailing-group-container sender-mailing-group-add">
					<span class="sender-mailing-group-container-title">
						<span><?=htmlspecialcharsbx($mailing['TRIGGER_FIELDS']['START']['NAME'])?></span>
					</span>
				</div>
			</div>

			<div id="SENDER_TRIGGER_CHAIN_CONTAINER" class="trigger_chain">
				<?
				$i = 0;
				foreach($chainFieldsList as $chain):
					$i++;

					echo str_replace(
						array(
							'%SENDER_LETTER_TEMPLATE_BODY_NUM%',
							'%SENDER_LETTER_TEMPLATE_MESSAGE%',
							'%sender_letter_template_message%'
						),
						array(
							$i,
							\Bitrix\Sender\TemplateTable::initEditor(array(
								'FIELD_NAME' => 'CHAIN_MESSAGE_'.$i,
								'FIELD_VALUE' => $chain['MESSAGE'],
								'TEMPLATE_TYPE_INPUT' => 'CHAIN['.$i.'][TEMPLATE_TYPE]',
								'TEMPLATE_TYPE' => $chain['TEMPLATE_TYPE'],
								'TEMPLATE_ID_INPUT' => 'CHAIN['.$i.'][TEMPLATE_ID]',
								'TEMPLATE_ID' => $chain['TEMPLATE_ID'],
								'HAVE_USER_ACCESS' => !$isUserHavePhpAccess,
								'SHOW_SAVE_TEMPLATE' => false,
							))
						),
						getSenderItemContainer($ID, $chain)
					);

				endforeach;

				if(count($chainFieldsList) <= 0)
				{
					// fix for load editor when no letters
					\Bitrix\Sender\TemplateTable::initEditor(array(
						'FIELD_NAME' => 'EMPTY_CHAIN_MESSAGE',
						'FIELD_VALUE' => '',
						'HAVE_USER_ACCESS' => !$isUserHavePhpAccess,
						'SHOW_SAVE_TEMPLATE' => false,
					));
				}
				?>

			</div>
			<div class="sender-trigger-add-letter">
				<input type="button" onclick="senderLetterContainer.addItem();" value="<?=GetMessage("sender_chain_edit_list_add")?>">
			</div>

			<div class="sender-trigger-status-mailing sender-trigger-status-mailing-finish">
				<div class="sender-trigger-status-mailing-title"><?=GetMessage("sender_chain_edit_list_event_end")?></div>
				<div class="sender-mailing-group-container sender-mailing-group-ok">
					<span class="sender-mailing-group-container-title">
						<span>
							<?
							if(strlen($mailing['TRIGGER_FIELDS']['END']['NAME']) > 0)
								echo htmlspecialcharsbx($mailing['TRIGGER_FIELDS']['END']['NAME']);
							else
								echo GetMessage("sender_chain_edit_trigger_name_default");
							?>
						</span>
					</span>
				</div>
			</div>

			<script>
				function ShowTemplateListL(obj, isHide)
				{
					var container;
					container = BX.findChild(obj, {'className': 'sender_letter_container_body_tmpl'}, true);
					if(!container) return;
					container.style.display = (isHide ? 'block' : 'none');

					container = BX.findChild(obj, {'className': 'sender_letter_container_body_fields'}, true);
					if(!container) return;
					container.style.display = (!isHide ? 'block' : 'none');
				}

				var letterManager = new SenderLetterManager;
				letterManager.onSetTemplate(function()
				{
					ShowTemplateListL(this);
				});

				letterManager.onShowTemplateList(function(){ ShowTemplateListL(this, true); });
				letterManager.onHideTemplateList(function(){ ShowTemplateListL(this, false); });
			</script>

			<script>
				var dictionarySenderTime = <?=CUtil::PhpToJSObject(array_reverse($dictionaryTimeList));?>;
				var senderLetterContainer = new SenderLetterContainer({'container': BX('SENDER_TRIGGER_CHAIN_CONTAINER')});
				var letterTemplate = {};
				BX.ajax.loadJSON(
					'/bitrix/admin/sender_mailing_trig_edit.php?ID=<?=$ID?>&lang=<?=LANGUAGE_ID?>&action=get_vr&<?echo bitrix_sessid_get()?>',
					{},
					function(data){
						letterTemplate = data;
					}
				);
			</script>

		</td>
	</tr>

<?
$tabControl->Buttons(array(
	"disabled"=>($POST_RIGHT<"W"),
	"back_url"=>"/bitrix/admin/sender_mailing_trig_admin.php?ID=".$ID."&lang=".LANG,
));
?>

<?echo bitrix_sessid_post();?>
	<input type="hidden" name="lang" value="<?=LANG?>">

<?
$tabControl->End();
?>

<?
$tabControl->ShowWarnings("post_form", $message);
?>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");?>