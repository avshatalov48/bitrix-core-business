<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();
/** @var CBitrixComponentTemplate $this */
/** @var array $arParams */
/** @var array $arResult */
/** @global CDatabase $DB */
/** @global CUser $USER */
/** @global CMain $APPLICATION */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\ModuleManager;
use Bitrix\Main\Loader;
use Bitrix\Main\UI;

UI\Extension::load("ui.buttons");
UI\Extension::load("ui.alerts");
UI\Extension::load("socialnetwork.common");

if ($arResult["NEED_AUTH"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif (strlen($arResult["FatalError"])>0)
{
	?><span class='errortext'><?=$arResult["FatalError"]?></span><br /><br /><?
}
else
{
	$jsCoreExtensionList = array('socnetlogdest', 'popup', 'fx');
	if ($arResult["intranetInstalled"])
	{
		$jsCoreExtensionList = array_merge($jsCoreExtensionList, array('ui_date', 'date'));
	}

	CJSCore::Init($jsCoreExtensionList);
	$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/main.post.form/templates/.default/style.css");
	$APPLICATION->SetAdditionalCSS("/bitrix/components/bitrix/socialnetwork.blog.post.edit/templates/.default/style.css");

	?><div id="sonet_group_create_error_block" class="ui-alert ui-alert-xs ui-alert-danger ui-alert-icon-danger<?=(strlen($arResult["ErrorMessage"]) > 0 ? "" : " sonet-ui-form-error-block-invisible")?>"><?=$arResult["ErrorMessage"]?></div><?

	if ($arResult["ShowForm"] == "Input")
	{
		$bodyClass = $APPLICATION->GetPageProperty("BodyClass");
		$APPLICATION->SetPageProperty("BodyClass", ($bodyClass ? $bodyClass." " : "")."social-group-create-body");

		if (
			$arResult["IS_IFRAME"] 
			&& $arResult["CALLBACK"] == "REFRESH"
		)
		{
			$APPLICATION->RestartBuffer();
			?><script>
				top.BX.onCustomEvent('onSonetIframeCallbackRefresh');
			</script><?
			die();
		}
		elseif (
			$arResult["IS_IFRAME"]
			&& $arResult["CALLBACK"] == "GROUP"
		)
		{
			$APPLICATION->RestartBuffer();
			?><script>
				top.BX.onCustomEvent('onSonetIframeCallbackGroup', [<?=intval($_GET["GROUP_ID"])?>]);
			</script><?
			die();
		}

		?><script><?

			if (
				$arResult["IS_IFRAME"]
				&& $arResult["CALLBACK"] == "EDIT"
			)
			{
				// this situation is impossible now but this code may be needed in the future
				?>
				(function() {
					var iframePopup = window.top.BX.SonetIFramePopup;
					if (iframePopup)
					{
						BX.adjust(iframePopup.title, {text: BX.message("SONET_GROUP_TITLE_EDIT").replace('#GROUP_NAME#', BX.message("SONET_GROUP_TITLE"))});
					}
				})();
				<?
			}
			?>
			top.BXExtranetMailList = [];

			BX.message({
				SONET_GCE_T_NAME2: '<?=GetMessageJS('SONET_GCE_T_NAME2')?>',
				SONET_GCE_T_NAME2_PROJECT: '<?=GetMessageJS('SONET_GCE_T_NAME2_PROJECT')?>',
				SONET_GCE_T_TITLE_CREATE: '<?=GetMessageJS('SONET_GCE_T_TITLE_CREATE')?>',
				SONET_GCE_T_TITLE_CREATE_PROJECT: '<?=GetMessageJS('SONET_GCE_T_TITLE_CREATE_PROJECT')?>',
				SONET_GCE_T_TITLE_EDIT: '<?=GetMessageJS('SONET_GCE_T_TITLE_EDIT')?>',
				SONET_GCE_T_TITLE_EDIT_PROJECT: '<?=GetMessageJS('SONET_GCE_T_TITLE_EDIT_PROJECT')?>',
				SONET_GCE_T_DO_CREATE: '<?=GetMessageJS('SONET_GCE_T_DO_CREATE')?>',
				SONET_GCE_T_DO_CREATE_PROJECT: '<?=GetMessageJS('SONET_GCE_T_DO_CREATE_PROJECT')?>',
				SONET_GROUP_TITLE_EDIT : '<?=CUtil::JSEscape(GetMessage("SONET_GCE_T_TITLE_EDIT"))?>',
				SONET_GCE_T_DEST_EXTRANET_SELECTOR_INVITE : '<?=GetMessageJS("SONET_GCE_T_DEST_EXTRANET_SELECTOR_INVITE")?>',
				SONET_GCE_T_DEST_EXTRANET_SELECTOR_ADD : '<?=GetMessageJS("SONET_GCE_T_DEST_EXTRANET_SELECTOR_ADD")?>',
				SONET_GCE_T_DEST_LINK_1 : '<?=GetMessageJS("SONET_GCE_T_ADD_EMPLOYEE")?>',
				SONET_GCE_T_DEST_LINK_2 : '<?=GetMessageJS('SONET_GCE_T_DEST_LINK_2')?>',
				SONET_GCE_T_TAG_ADD: '<?=GetMessageJS("SONET_GCE_T_TAG_ADD")?>',
				SONET_GCE_T_AJAX_ERROR:  '<?=GetMessageJS('SONET_GCE_T_AJAX_ERROR')?>'
				<?
				if (array_key_exists("POST", $arResult) && array_key_exists("NAME", $arResult["POST"]) && strlen($arResult["POST"]["NAME"]) > 0)
				{
					?>
					, SONET_GROUP_TITLE : '<?=CUtil::JSEscape($arResult["POST"]["NAME"])?>'
					<?
				}
				?>
			});

			BX.ready(
				function()
				{
					BX.BXGCE.types = <?=CUtil::phpToJSObject($arResult['Types'])?>;
					BX.BXGCE.arUserSelector = [];
					BX.BXGCE.init({
						groupId: <?=intval($arParams["GROUP_ID"])?>,
						config: <?=CUtil::phpToJSObject($arResult['ClientConfig'])?>,
						avatarUploaderId: '<?=$arResult['AVATAR_UPLOADER_CID']?>'
					});

					if (BX("USERS_employee_section_extranet"))
					{
						BX("USERS_employee_section_extranet").style.display = "<?=($arResult["POST"]["IS_EXTRANET_GROUP"] == "Y" ? "inline-block" : "none")?>";
					}
				}
			);
		</script><?

		if (
			is_array($arResult["ErrorFields"])
			&& count($arResult["ErrorFields"]) > 0
		)
		{
			$bHasUserFieldError = false;
			foreach ($arResult["GROUP_PROPERTIES"] as $FIELD_NAME => $arUserField)
			{
				if (in_array($FIELD_NAME, $arResult["ErrorFields"]))
				{
					$bHasUserFieldError = true;
					break;
				}
			}
		}

		?><form method="post" name="sonet_group_create_popup_form" id="sonet_group_create_popup_form" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data"><?
			?><input type="hidden" name="ajax_request" value="Y"><?
			?><input type="hidden" name="save" value="Y"><?
			?><?=bitrix_sessid_post()?><?
			?><div id="sonet_group_create_popup" class="sonet-group-create-popup"><?

				if (
					$arResult["USE_PRESETS"] == 'Y'
					&& (
						empty($arResult["TAB"])
						|| $arResult["TAB"] == "edit"
					)
				)
				{
					?><div id="sonet_group_create_form_step_1" style="display: <?=($arResult['step1Display'] ? 'block' : 'none')?>;">
						<div id="sonet_group_create_step_1_content">
							<div class="social-group-create-container first-step"><?

								$typeCode = \Bitrix\Socialnetwork\Item\Workgroup::getTypeCodeByParams(array(
									'typesList' => $arResult['Types'],
									'fields' => $arResult['POST']
								));

								foreach($arResult["TypeRowList"] as $rowCode)
								{
									?><div class="social-group-create-inner">
										<div class="social-group-create-title"><?=$arResult["TypeRowNameList"][$rowCode]?></div>
										<div class="social-group-tile-container"><?
											foreach($arResult[$rowCode] as $code => $type)
											{
												$selected = ($typeCode == $code);
												?><div class="social-group-tile-item" bx-type="<?=htmlspecialcharsbx($code)?>">
												<a href="#" class="social-group-tile-item-inner">
													<span class="social-group-tile-item-title"><?=htmlspecialcharsex($type["NAME"])?></span>
													<span class="social-group-tile-item-cover social-group-tile-item-cover-back<?=(!empty($type["TILE_CLASS"]) ? " ".htmlspecialcharsbx($type["TILE_CLASS"]) : "")?>"></span>
													<span class="social-group-tile-item-description"><?=htmlspecialcharsex($type["DESCRIPTION"])?></span>
												</a>
												</div><?
											}
											?></div>
									</div><?
								}

								?></div>
						</div>
					</div><?
				}

				?><div id="sonet_group_create_form_step_2" class="social-group-create-container second-step" style="display: <?=($arResult["step1Display"] ? 'none' : 'block')?>;"><div class="social-group-create-form"><?

					if (!array_key_exists("TAB", $arResult) || $arResult["TAB"] == "edit")
					{
						if ($arParams["GROUP_ID"] > 0)
						{
							$strSubmitButtonTitle = Loc::getMessage("SONET_GCE_T_DO_EDIT");
							$actionType = "edit";
						}
						else
						{
							$strSubmitButtonTitle = Loc::getMessage("SONET_GCE_T_DO_CREATE");
							$actionType = "create";
						}

						?><div class="social-group-create-info">
							<div class="social-group-create-info-panel">
								<div class="social-group-create-info-panel-title">
									<input type="text" id="GROUP_NAME_input"
											name="GROUP_NAME"
											value="<?=(strlen($arResult["POST"]["NAME"]) > 0 ? $arResult["POST"]["NAME"] : '');?>"
											placeholder="<?=Loc::getMessage($arResult["POST"]["PROJECT"] == "Y" ? "SONET_GCE_T_NAME2_PROJECT" : "SONET_GCE_T_NAME2");?>"
									/>
								</div>
							</div>
							<div class="social-group-create-info-editor">
								<div class="social-group-create-add-task">
									<textarea name="GROUP_DESCRIPTION"
												class="social-group-create-description"
												cols="30" rows="10"
									><?=(strlen($arResult["POST"]["DESCRIPTION"]) > 0 ? $arResult["POST"]["DESCRIPTION"] : '');?></textarea>
									<div class="social-group-create-separator-line"></div>
								</div>
							</div>
						</div><?

					}

					?><div class="social-group-create-options"><?
						if (!array_key_exists("TAB", $arResult) || $arResult["TAB"] == "edit")
						{
							?><div class="social-group-create-options-item social-group-create-options-item-upload">
								<div class="social-group-create-options-item-column-left">
									<div class="social-group-create-options-item-name"><?=Loc::getMessage('SONET_GCE_T_IMAGE2')?></div>
								</div>
								<div class="social-group-create-options-item-column-right">
									<div class="social-group-create-options-item-column-one">
										<div id="GROUP_IMAGE_ID_block" class="social-group-create-link-upload<?=(in_array("GROUP_IMAGE_ID", $arResult["ErrorFields"]) ? " sonet-group-create-popup-field-upload-error" : "")?><?=(!empty($arResult["POST"]["IMAGE_ID"]) ? " social-group-create-link-upload-set" : "")?>"><?
											$APPLICATION->IncludeComponent('bitrix:main.file.input', '.default', array(
												'INPUT_NAME' => 'GROUP_IMAGE_ID',
												'INPUT_NAME_UNSAVED' => 'GROUP_IMAGE_ID_UNSAVED',
												'CONTROL_ID' => $arResult['AVATAR_UPLOADER_CID'],
												'INPUT_VALUE' => $arResult["POST"]["IMAGE_ID"],
												'MULTIPLE' => 'N',
												'ALLOW_UPLOAD' => 'I',
												'INPUT_CAPTION' => GetMessage("SONET_GCE_T_UPLOAD_IMAGE"),
												'SHOW_AVATAR_EDITOR' => 'Y',
												'ENABLE_CAMERA' => 'N'
											));
										?></div>
									</div>
								</div>
							</div><?

							if ($arResult["intranetInstalled"])
							{
								?><div id="IS_PROJECT_block" class="<?=($arResult["POST"]["PROJECT"] == "Y" ? " sgcp-switch-project" : "")?>">
									<div class="social-group-create-options-item sgcp-flex-project">
										<div class="social-group-create-options-item-column-left">
											<div class="social-group-create-options-item-name"><?= GetMessage("SONET_GCE_T_PARAMS_PROJECT_DATE") ?></div>
										</div>
										<div class="social-group-create-options-item-column-right">
											<div class="social-group-create-options-item-column-one">
												<div class="social-group-create-field-container social-group-create-field-container-datetime social-group-create-field-datetime">
													<span class="main-ui-control main-ui-date main-grid-panel-date">
														<span class="main-ui-date-button"></span>
														<input type="text" name="PROJECT_DATE_START" autocomplete="off" data-time="" class="main-ui-control-input main-ui-date-input" value="<?=(!empty($arResult["POST"]["PROJECT_DATE_START"]) ? ConvertTimeStamp(MakeTimeStamp($arResult["POST"]["PROJECT_DATE_START"]), 'SHORT') : "")?>">
														<div class="main-ui-control-value-delete<?=empty($arResult["POST"]["PROJECT_DATE_START"]) ? " main-ui-hide" : ""?>">
															<span class="main-ui-control-value-delete-item"></span>
														</div>
													</span>
													<div class="social-group-create-field-block social-group-create-field-block-between"></div>
													<span class="main-ui-control main-ui-date main-grid-panel-date">
														<span class="main-ui-date-button"></span>
														<input type="text" name="PROJECT_DATE_FINISH" autocomplete="off" data-time="" class="main-ui-control-input main-ui-date-input" value="<?=(!empty($arResult["POST"]["PROJECT_DATE_FINISH"]) ? ConvertTimeStamp(MakeTimeStamp($arResult["POST"]["PROJECT_DATE_FINISH"]), 'SHORT') : "")?>">
														<div class="main-ui-control-value-delete<?=empty($arResult["POST"]["PROJECT_DATE_FINISH"]) ? " main-ui-hide" : ""?>">
															<span class="main-ui-control-value-delete-item"></span>
														</div>
													</span>
												</div>
											</div>
										</div>
									</div>
								</div><?
							}

							?><div class="social-group-create-options-item">
								<div id="GROUP_OWNER_LABEL_block" class="social-group-create-options-item-column-left<?=($arResult["POST"]["PROJECT"] == "Y" ? " sgcp-switch-project" : "")?>">
									<div class="social-group-create-options-item-name sgcp-block-nonproject"><?=GetMessage("SONET_GCE_T_DEST_TITLE_OWNER")?></div>
									<div class="social-group-create-options-item-name sgcp-block-project"><?=GetMessage("SONET_GCE_T_DEST_TITLE_OWNER_PROJECT")?></div>
								</div>

								<div class="social-group-create-options-item-column-right">
									<div class="social-group-create-options-item-column-one social-group-create-form-control-block"><?

										// owner
										$selectorName = "group_create_owner_".randString(6);

										?><div class="social-group-create-control-inner social-group-create-form-field feed-add-post-destination-wrap<?=(in_array("OWNER", $arResult["ErrorFields"]) ? " sonet-group-create-tabs-text-error" : "")?>" id="cont_<?=$selectorName?>">
											<span id="sonet_group_create_popup_users_item_post_<?=$selectorName?>"></span>
											<span class="feed-add-destination-input-box" id="sonet_group_create_popup_users_input_box_post_<?=$selectorName?>">
												<input type="text" value="" class="feed-add-destination-inp" id="sonet_group_create_popup_users_input_post_<?=$selectorName?>">
											</span>
											<a href="#" class="feed-add-destination-link" id="sonet_group_create_popup_users_tag_post_<?=$selectorName?>"><?=GetMessage("SONET_GCE_T_ADD_OWNER")?></a><?
										?></div><?

										?><script>
											BX.ready(function () {
												var instance = new BX.BXGCESelectorInstance({
													single: true,
													controlName: 'OWNER_CODE',
													tagLinkText1: '<?=GetMessageJs("SONET_GCE_T_ADD_OWNER")?>',
													tagLinkText2: '<?=GetMessageJs("SONET_GCE_T_ADD_OWNER")?>'
												});
												instance.init({
													id: '<?=CUtil::JSEscape($selectorName)?>',
													contId: 'cont_<?=CUtil::JSEscape($selectorName)?>',
													bindId: 'cont_<?=CUtil::JSEscape($selectorName)?>',
													tagId: 'sonet_group_create_popup_users_tag_post_<?=CUtil::JSEscape($selectorName)?>',
													bindNode: BX('cont_<?=CUtil::JSEscape($selectorName)?>')
												});
												BX.BXGCESelectorManager.controls['<?=CUtil::JSEscape($selectorName)?>'] = instance;
											});
										</script><?

										$APPLICATION->IncludeComponent(
											"bitrix:main.ui.selector",
											".default",
											array(
												'ID' => $selectorName,
												'BIND_ID' => 'sonet_group_create_popup_users_input_post_'.$selectorName,
												'ITEMS_SELECTED' => (!empty($arResult["POST"]) && !empty($arResult["POST"]["OWNER_ID"]) ? array('U'.$arResult["POST"]["OWNER_ID"] => 'users') : array('U'.$arResult["currentUserId"] => 'users')),
												'CALLBACK' => array(
													'select' => 'BX.BXGCE.selectCallback',
													'unSelect' => 'BX.BXGCE.unSelectCallback',
													'openDialog' => "BX.delegate(BX.BXGCE.openDialogCallback, {
														inputBoxName: 'sonet_group_create_popup_users_input_box_post_".$selectorName."',
														inputName: 'sonet_group_create_popup_users_input_post_".$selectorName."',
														tagInputName: 'sonet_group_create_popup_users_tag_post_".$selectorName."'
													})",
													'closeDialog' => "BX.delegate(BX.BXGCE.closeDialogCallback, {
														inputBoxName: 'sonet_group_create_popup_users_input_box_post_".$selectorName."',
														inputName: 'sonet_group_create_popup_users_input_post_".$selectorName."',
														tagInputName: 'sonet_group_create_popup_users_tag_post_".$selectorName."'
													})",
													'openSearch' => "BX.delegate(BX.BXGCE.openDialogCallback, {
														inputBoxName: 'sonet_group_create_popup_users_input_box_post_".$selectorName."',
														inputName: 'sonet_group_create_popup_users_input_post_".$selectorName."',
														tagInputName: 'sonet_group_create_popup_users_tag_post_".$selectorName."'
													})"
												),
												'OPTIONS' => array(
													'useNewCallback' => 'Y',
													'extranetContext' => ($arResult["bExtranetInstalled"] ? 'I' : false),
													'eventInit' => 'BX.BXGCE:init',
													'eventOpen' => 'BX.BXGCE:open',
													'context' => $arResult['destinationContextOwner'],
													'contextCode' => 'U',
													'useSearch' => 'N',
													'userNameTemplate' => CUtil::JSEscape($arParams["NAME_TEMPLATE"]),
													'useClientDatabase' => 'Y',
													'allowEmailInvitation' => 'N',
													'enableAll' => 'N',
													'enableDepartments' => 'N',
													'enableSonetgroups' => 'N',
													'departmentSelectDisable' => 'Y',
													'allowAddUser' => 'N',
													'allowAddCrmContact' => 'N',
													'allowAddSocNetGroup' => 'N',
													'allowSearchEmailUsers' => 'N',
													'allowSearchCrmEmailUsers' => 'N',
													'allowSearchNetworkUsers' => 'N',
													'allowSonetGroupsAjaxSearchFeatures' => 'N'
												)
											),
											false,
											array("HIDE_ICONS" => "Y")
										);

										?><script>
											BX.ready(function () {
												BX.onCustomEvent(window, 'BX.BXGCE:init', [ {
													id: '<?=CUtil::JSEscape($selectorName)?>',
													inputId: 'sonet_group_create_popup_users_input_post_<?=CUtil::JSEscape($selectorName)?>',
													containerId: 'sonet_group_create_popup_users_input_box_post_<?=CUtil::JSEscape($selectorName)?>',
													openDialogWhenInit: false
												} ]);
											});
										</script><?
										?><span id="GROUP_MODERATORS_SWITCH_LABEL_block" class="social-group-create-text<?=($arResult["POST"]["PROJECT"] == "Y" ? " sgcp-switch-project" : "")?>">
											<a id="GROUP_MODERATORS_switch" href="#" class="social-group-create-text-link sgcp-inlineblock-nonproject"><?=GetMessage("SONET_GCE_T_MODERATORS_SWITCH")?></a>
											<a id="GROUP_MODERATORS_PROJECT_switch" href="#" class="social-group-create-text-link sgcp-inlineblock-project"><?=GetMessage("SONET_GCE_T_MODERATORS_SWITCH_PROJECT")?></a>
										</span><?
									?></div>
								</div>
							</div><? // owner block

							?><div class="social-group-create-openable-block-outer invisible" id="GROUP_MODERATORS_block_container"><div class="social-group-create-options-item" id="GROUP_MODERATORS_block">
								<div id="GROUP_MODERATORS_LABEL_block" class="social-group-create-options-item-column-left<?=($arResult["POST"]["PROJECT"] == "Y" ? " sgcp-switch-project" : "")?>">
									<div class="social-group-create-options-item-name sgcp-block-nonproject"><?=GetMessage("SONET_GCE_T_DEST_TITLE_MODERATORS")?></div>
									<div class="social-group-create-options-item-name sgcp-block-project"><?=GetMessage("SONET_GCE_T_DEST_TITLE_MODERATORS_PROJECT")?></div>
								</div>

								<div class="social-group-create-options-item-column-right">
									<div class="social-group-create-options-item-column-one social-group-create-form-control-block"><?
										// moderators
										$selectorName = "group_create_moderators_".randString(6);

										?><div class="social-group-create-control-inner social-group-create-form-field feed-add-post-destination-wrap<?=(in_array("MODERATORS", $arResult["ErrorFields"]) ? " sonet-group-create-tabs-text-error" : "")?>" id="cont_<?=$selectorName?>">
											<span id="sonet_group_create_popup_users_item_post_<?=$selectorName?>"></span>
											<span class="feed-add-destination-input-box" id="sonet_group_create_popup_users_input_box_post_<?=$selectorName?>">
												<input type="text" value="" class="feed-add-destination-inp" id="sonet_group_create_popup_users_input_post_<?=$selectorName?>">
											</span>
											<a href="#" class="feed-add-destination-link" id="sonet_group_create_popup_users_tag_post_<?=$selectorName?>"><?=GetMessage("SONET_GCE_T_ADD_EMPLOYEE")?></a><?
										?></div><?

										?><script>
											BX.ready(function () {
												var instance = new BX.BXGCESelectorInstance({
													single: false,
													controlName: 'MODERATOR_CODES[]',
													tagLinkText1: '<?=GetMessageJs("SONET_GCE_T_ADD_EMPLOYEE")?>',
													tagLinkText2: '<?=GetMessageJs("SONET_GCE_T_DEST_LINK_2")?>'
												});
												instance.init({
													id: '<?=CUtil::JSEscape($selectorName)?>',
													contId: 'cont_<?=CUtil::JSEscape($selectorName)?>',
													bindId: 'cont_<?=CUtil::JSEscape($selectorName)?>',
													tagId: 'sonet_group_create_popup_users_tag_post_<?=CUtil::JSEscape($selectorName)?>',
													bindNode: BX('cont_<?=CUtil::JSEscape($selectorName)?>')
												});
												BX.BXGCESelectorManager.controls['<?=CUtil::JSEscape($selectorName)?>'] = instance;
											});
										</script><?

										$moderatorsList = array();
										if (
											!empty($arResult["POST"])
											&& !empty($arResult["POST"]["MODERATOR_IDS"])
											&& is_array($arResult["POST"]["MODERATOR_IDS"])
										)
										{
											foreach($arResult["POST"]["MODERATOR_IDS"] as $moderatorId)
											{
												$moderatorsList['U'.$moderatorId] = 'users';
											}
										}

										$APPLICATION->IncludeComponent(
											"bitrix:main.ui.selector",
											".default",
											array(
												'ID' => $selectorName,
												'BIND_ID' => 'sonet_group_create_popup_users_input_post_'.$selectorName,
												'ITEMS_SELECTED' => $moderatorsList,
												'CALLBACK' => array(
													'select' => 'BX.BXGCE.selectCallback',
													'unSelect' => 'BX.BXGCE.unSelectCallback',
													'openDialog' => "BX.delegate(BX.BXGCE.openDialogCallback, {
														inputBoxName: 'sonet_group_create_popup_users_input_box_post_".$selectorName."',
														inputName: 'sonet_group_create_popup_users_input_post_".$selectorName."',
														tagInputName: 'sonet_group_create_popup_users_tag_post_".$selectorName."'
													})",
													'closeDialog' => "BX.delegate(BX.BXGCE.closeDialogCallback, {
														inputBoxName: 'sonet_group_create_popup_users_input_box_post_".$selectorName."',
														inputName: 'sonet_group_create_popup_users_input_post_".$selectorName."',
														tagInputName: 'sonet_group_create_popup_users_tag_post_".$selectorName."'
													})",
													'openSearch' => "BX.delegate(BX.BXGCE.openDialogCallback, {
														inputBoxName: 'sonet_group_create_popup_users_input_box_post_".$selectorName."',
														inputName: 'sonet_group_create_popup_users_input_post_".$selectorName."',
														tagInputName: 'sonet_group_create_popup_users_tag_post_".$selectorName."'
													})"
												),
												'OPTIONS' => array(
													'useNewCallback' => 'Y',
													'eventInit' => 'BX.BXGCE:init',
													'eventOpen' => 'BX.BXGCE:open',
													'context' => $arResult['destinationContextModerators'],
													'contextCode' => 'U',
													'useSearch' => 'N',
													'userNameTemplate' => CUtil::JSEscape($arParams["NAME_TEMPLATE"]),
													'useClientDatabase' => 'Y',
													'allowEmailInvitation' => 'N',
													'enableAll' => 'N',
													'enableDepartments' => 'N',
													'enableSonetgroups' => 'N',
													'departmentSelectDisable' => 'Y',
													'allowAddUser' => 'N',
													'allowAddCrmContact' => 'N',
													'allowAddSocNetGroup' => 'N',
													'allowSearchEmailUsers' => 'N',
													'allowSearchCrmEmailUsers' => 'N',
													'allowSearchNetworkUsers' => 'N',
													'allowSonetGroupsAjaxSearchFeatures' => 'N'
												)
											),
											false,
											array("HIDE_ICONS" => "Y")
										);
										?><script>
											BX.ready(function () {
												BX.onCustomEvent(window, 'BX.BXGCE:init', [ {
													id: '<?=CUtil::JSEscape($selectorName)?>',
													inputId: 'sonet_group_create_popup_users_input_post_<?=CUtil::JSEscape($selectorName)?>',
													containerId: 'sonet_group_create_popup_users_input_box_post_<?=CUtil::JSEscape($selectorName)?>',
													openDialogWhenInit: false
												} ]);
											});
										</script><?

									?></div>
								</div>
							</div></div><? // GROUP_MODERATORS_block

						} // create or edit

						if (!array_key_exists("TAB", $arResult) || $arResult["TAB"] == "invite")
						{
							if ($arResult["TAB"] == "invite")
							{
								$strSubmitButtonTitle = GetMessage("SONET_GCE_T_DO_INVITE");
								$actionType = "invite";
							}

							$selectorName = randString(6);

							?><div class="social-group-create-options-item">
								<div class="social-group-create-options-item-column-left">
									<div class="social-group-create-options-item-name"><?=Loc::getMessage($arResult["intranetInstalled"] ? "SONET_GCE_T_DEST_TITLE_EMPLOYEE2" : "SONET_GCE_T_DEST_TITLE_USER2")?></div>
								</div>
								<div class="social-group-create-options-item-column-right">
									<div class="social-group-create-options-item-column-one social-group-create-form-control-block"><?
										?><div class="social-group-create-control-inner social-group-create-form-field feed-add-post-destination-wrap<?=(in_array("USERS", $arResult["ErrorFields"]) ? " sonet-group-create-tabs-text-error" : "")?>" id="sonet_group_create_popup_users_container_post_<?=$selectorName?>">
											<span id="sonet_group_create_popup_users_item_post_<?=$selectorName?>"></span>
											<span class="feed-add-destination-input-box" id="sonet_group_create_popup_users_input_box_post_<?=$selectorName?>">
												<input type="text" value="" class="feed-add-destination-inp" id="sonet_group_create_popup_users_input_post_<?=$selectorName?>">
											</span>
											<a href="#" class="feed-add-destination-link" id="sonet_group_create_popup_users_tag_post_<?=$selectorName?>"><?=GetMessage("SONET_GCE_T_ADD_EMPLOYEE")?></a><?

											$arValue = ($arResult["POST"]["USER_CODES"] ? $arResult["POST"]["USER_CODES"] : array());
											$arStructure = CSocNetLogDestination::GetStucture(array(
												"LAZY_LOAD" => true,
												"DEPARTMENT_ID" => (isset($arResult["siteDepartmentID"]) && intval($arResult["siteDepartmentID"]) > 0 ? intval($arResult["siteDepartmentID"]) : false)
											));

											?><script>

												var department = <?=($arStructure && !empty($arStructure['department']) ? CUtil::PhpToJSObject($arStructure['department']) : '{}')?>;
												var lastUsers = <?=(empty($arResult["DEST_USERS_LAST"])? '{}': CUtil::PhpToJSObject($arResult["DEST_USERS_LAST"]))?>;
												var departmentRelation = null;

												<?
												if (!$arStructure || empty($arStructure['department_relation']))
												{
													?>
													var relation = {};
													for(var iid in department)
													{
														var p = department[iid]['parent'];
														if (!relation[p])
															relation[p] = [];
														relation[p][relation[p].length] = iid;
													}

													function makeDepartmentTree(id, relation)
													{
														var arRelations = {};

														if (relation[id])
														{
															for (var x in relation[id])
															{
																var relId = relation[id][x];
																var arItems = [];
																if (relation[relId] && relation[relId].length > 0)
																	arItems = makeDepartmentTree(relId, relation);

																arRelations[relId] = {
																	id: relId,
																	type: 'category',
																	items: arItems
																};
															}
														}

														return arRelations;
													}

													departmentRelation = makeDepartmentTree(<?=(isset($arResult["siteDepartmentID"]) && intval($arResult["siteDepartmentID"]) ? "department['DR".intval($arResult["siteDepartmentID"])."'].parent" : "'DR0'")?>, relation);
													<?
												}
												else
												{
													?>
													departmentRelation = <?=CUtil::PhpToJSObject($arStructure['department_relation'])?>;
													<?
												}
												?>
												BX.ready(function() {
													BX.SocNetLogDestination.init({
														name : '<?=$selectorName?>',
														searchInput : BX('sonet_group_create_popup_users_input_post_<?=$selectorName?>'),
														departmentSelectDisable : <?=(isset($arResult["GROUP_PROPERTIES"]["UF_SG_DEPT"]) && !$arResult["bExtranet"] ? 'false' : 'true')?>,
														userSearchArea : <?=($arResult["bExtranetInstalled"] ? "'I'" : "false")?>,
														extranetUser :  false, // ??
														allowAddSocNetGroup: false,
														allowSearchSelf: false,
														siteDepartmentID : <?=(isset($arResult["siteDepartmentID"]) && intval($arResult["siteDepartmentID"]) > 0 ? intval($arResult["siteDepartmentID"]) : "false")?>,
														bindMainPopup : {
															node : BX('sonet_group_create_popup_users_container_post_<?=$selectorName?>'),
															offsetTop : '5px',
															offsetLeft: '15px'
														},
														bindSearchPopup : {
															node : BX('sonet_group_create_popup_users_container_post_<?=$selectorName?>'),
															offsetTop : '5px',
															offsetLeft: '15px'
														},
														callback : {
															select : BX.BXGCE.selectCallbackOld,
															unSelect : BX.delegate(BX.BXGCE.unSelectCallbackOld, {
																formName: '<?=$selectorName?>',
																inputContainerName: 'sonet_group_create_popup_users_item_post_<?=$selectorName?>',
																inputName: 'sonet_group_create_popup_users_input_post_<?=$selectorName?>',
																tagInputName: 'sonet_group_create_popup_users_tag_post_<?=$selectorName?>',
																tagLink1: BX.message('SONET_GCE_T_DEST_LINK_1'),
																tagLink2: BX.message('SONET_GCE_T_DEST_LINK_2')
															}),
															openDialog : BX.delegate(BX.BXGCE.openDialogCallbackOld, {
																inputBoxName: 'sonet_group_create_popup_users_input_box_post_<?=$selectorName?>',
																inputName: 'sonet_group_create_popup_users_input_post_<?=$selectorName?>',
																tagInputName: 'sonet_group_create_popup_users_tag_post_<?=$selectorName?>'
															}),
															closeDialog : BX.delegate(BX.SocNetLogDestination.BXfpCloseDialogCallback, {
																inputBoxName: 'sonet_group_create_popup_users_input_box_post_<?=$selectorName?>',
																inputName: 'sonet_group_create_popup_users_input_post_<?=$selectorName?>',
																tagInputName: 'sonet_group_create_popup_users_tag_post_<?=$selectorName?>'
															}),
															openSearch : BX.delegate(BX.BXGCE.openDialogCallbackOld, {
																inputBoxName: 'sonet_group_create_popup_users_input_box_post_<?=$selectorName?>',
																inputName: 'sonet_group_create_popup_users_input_post_<?=$selectorName?>',
																tagInputName: 'sonet_group_create_popup_users_tag_post_<?=$selectorName?>'
															})
														},
														items : {
															users : <?=(
																$arResult["bExtranetInstalled"]
																&& strlen(COption::GetOptionString("extranet", "extranet_site")) > 0
																	? (is_array($arResult["POST"]["USERS_FOR_JS_I"]) && !empty($arResult["POST"]["USERS_FOR_JS_I"]) ? CUtil::PhpToJSObject($arResult["POST"]["USERS_FOR_JS_I"]) : '{}')
																	: (is_array($arResult["POST"]["USERS_FOR_JS"]) && !empty($arResult["POST"]["USERS_FOR_JS"]) ? CUtil::PhpToJSObject($arResult["POST"]["USERS_FOR_JS"]) : '{}')
															)?>,
															groups : {},
															sonetgroups : {},
															department : department,
															departmentRelation : departmentRelation
														},
														itemsLast : {
															users : lastUsers,
															sonetgroups : {},
															department : {},
															groups : {}
														},
														itemsSelected : <?=(empty($arValue)? '{}': CUtil::PhpToJSObject($arValue))?>,
														destSort : <?=CUtil::PhpToJSObject($arResult["DEST_SORT"])?>
													});
													BX.BXGCE.arUserSelector.push('<?=$selectorName?>');
													BX.bind(BX('sonet_group_create_popup_users_input_post_<?=$selectorName?>'), 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
														formName: '<?=$selectorName?>',
														inputName: 'sonet_group_create_popup_users_input_post_<?=$selectorName?>',
														tagInputName: 'sonet_group_create_popup_users_tag_post_<?=$selectorName?>'
													}));
													BX.bind(BX('sonet_group_create_popup_users_input_post_<?=$selectorName?>'), 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
														formName: '<?=$selectorName?>',
														inputName: 'sonet_group_create_popup_users_input_post_<?=$selectorName?>'
													}));
													BX.bind(BX('sonet_group_create_popup_users_input_post_<?=$selectorName?>'), 'blur', BX.delegate(BX.SocNetLogDestination.BXfpBlurInput, {
														inputBoxName: 'sonet_group_create_popup_users_input_box_post_<?=$selectorName?>',
														tagInputName: 'sonet_group_create_popup_users_tag_post_<?=$selectorName?>'
													}));
													BX.bind(BX('sonet_group_create_popup_users_input_post_<?=$selectorName?>'), 'click', function(e) {
														BX.BXGCE.setSelector('<?=$selectorName?>');
														BX.SocNetLogDestination.openDialog('<?=$selectorName?>');
														BX.PreventDefault(e);
													});
													BX.bind(BX('sonet_group_create_popup_users_container_post_<?=$selectorName?>'), 'click', function(e) {
														BX.BXGCE.setSelector('<?=$selectorName?>');
														BX.SocNetLogDestination.openDialog('<?=$selectorName?>');
														BX.PreventDefault(e);
													});
													<?
													if (
														$arResult["POST"]["IS_EXTRANET_GROUP"] != "Y"
														&& $arResult["TAB"] == "invite"
													)
													{
														?>
														BX.SocNetLogDestination.openDialog('<?=$selectorName?>');
														<?
													}
													?>
												});
											</script>
										</div><?
										?><input type="hidden" name="NEW_INVITE_FORM" value="Y">
									</div><?

									if (isset($arResult["GROUP_PROPERTIES"]["UF_SG_DEPT"]) && !$arResult["bExtranet"])
									{
										?><div class="social-group-create-options-add-dept-hint <?=($arResult["POST"]["PROJECT"] == "Y" ? " sgcp-switch-project" : "")?>" id="GROUP_ADD_DEPT_HINT_block">
											<div class="sgcp-block-nonproject"><?=Loc::getMessage('SONET_GCE_T_ADD_DEPT_HINT')?></div>
											<div class="sgcp-block-project"><?=Loc::getMessage('SONET_GCE_T_ADD_DEPT_HINT_PROJECT')?></div>
										</div><?
									}

								?></div>
							</div><?

							if (
								$arResult["bExtranetInstalled"]
								&& Loader::includeModule("intranet")
								&& strlen(COption::GetOptionString("extranet", "extranet_site")) > 0
								&& (
									empty($arResult["TAB"])
									|| (
										$arResult["TAB"] == "invite"
										&& $arResult["POST"]["IS_EXTRANET_GROUP"] == "Y"
									)
								)
							)
							{
								?><div id="INVITE_EXTRANET_block_container" class="social-group-create-openable-block-outer<?=($arResult["POST"]["IS_EXTRANET_GROUP"] == "Y" ? "" : " invisible")?>"><div id="INVITE_EXTRANET_block" class="social-group-create-options-item social-group-create-form-field-list-block" style="display: flex;">
									<div class="social-group-create-options-item-column-left">
										<div class="social-group-create-options-item-name"><?=Loc::getMessage("SONET_GCE_T_DEST_TITLE_EXTERNAL2")?></div>
									</div>
									<div class="social-group-create-options-item-column-right">
										<div class="social-group-create-options-item-column-one social-group-create-form-control-block flex-wrap"><?

											$selectorName = randString(6);

											?><div class="invite-dialog-inv-form">
												<div class="sonet-group-create-popup-users-title"><?=GetMessage("SONET_GCE_T_DEST_TITLE_EXTRANET")?></div>
												<div class="ocial-group-create-control-inner social-group-create-form-field feed-add-post-destination-wrap" id="sonet_group_create_popup_users_container_post_<?=$selectorName?>">
													<span id="sonet_group_create_popup_users_item_post_<?=$selectorName?>"></span>
													<span class="feed-add-destination-input-box" id="sonet_group_create_popup_users_input_box_post_<?=$selectorName?>">
														<input type="text" value="" class="feed-add-destination-inp" id="sonet_group_create_popup_users_input_post_<?=$selectorName?>">
													</span>
													<a href="#" class="feed-add-destination-link" id="sonet_group_create_popup_users_tag_post_<?=$selectorName?>"><?=GetMessage("SONET_GCE_T_ADD_EXTRANET")?></a>
													<script><?
														$arStructure = array(
															'department' => array(
																'EX' => array(
																	'id' => 'EX',
																	'entityId' => 'EX',
																	'name' => GetMessage('SONET_GCE_T_DEST_EXTRANET'),
																	'parent' => 'DR0'
																)
															),
															'department_relation' => array(
																'EX' => array(
																	'id' => 'EX',
																	'items' => array(),
																	'type' => 'category'
																)
															)
														);
														?>
														var departmentExtranet = <?=CUtil::PhpToJSObject($arStructure['department'])?>;
														var departmentRelationExtranet = <?=CUtil::PhpToJSObject($arStructure['department_relation'])?>;

														BX.ready(function() {
															BX.SocNetLogDestination.init({
																name : '<?=$selectorName?>',
																searchInput : BX('sonet_group_create_popup_users_input_post_<?=$selectorName?>'),
																departmentSelectDisable : true,
																userSearchArea : 'E',
																extranetUser :  false, // ??
																allowAddSocNetGroup: false,
																bindMainPopup : {
																	node : BX('sonet_group_create_popup_users_container_post_<?=$selectorName?>'),
																	offsetTop : '5px',
																	offsetLeft: '15px'
																},
																bindSearchPopup : {
																	node : BX('sonet_group_create_popup_users_container_post_<?=$selectorName?>'),
																	offsetTop : '5px',
																	offsetLeft: '15px'
																},
																callback : {
																	select : BX.BXGCE.selectCallbackOld,
																	unSelect : BX.delegate(BX.BXGCE.unSelectCallbackOld, {
																		formName: '<?=$selectorName?>',
																		inputContainerName: 'sonet_group_create_popup_users_item_post_<?=$selectorName?>',
																		inputName: 'sonet_group_create_popup_users_input_post_<?=$selectorName?>',
																		tagInputName: 'sonet_group_create_popup_users_tag_post_<?=$selectorName?>',
																		tagLink1: BX.message('SONET_GCE_T_DEST_LINK_1'),
																		tagLink2: BX.message('SONET_GCE_T_DEST_LINK_2')
																	}),
																	openDialog : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
																		inputBoxName: 'sonet_group_create_popup_users_input_box_post_<?=$selectorName?>',
																		inputName: 'sonet_group_create_popup_users_input_post_<?=$selectorName?>',
																		tagInputName: 'sonet_group_create_popup_users_tag_post_<?=$selectorName?>'
																	}),
																	closeDialog : BX.delegate(BX.SocNetLogDestination.BXfpCloseDialogCallback, {
																		inputBoxName: 'sonet_group_create_popup_users_input_box_post_<?=$selectorName?>',
																		inputName: 'sonet_group_create_popup_users_input_post_<?=$selectorName?>',
																		tagInputName: 'sonet_group_create_popup_users_tag_post_<?=$selectorName?>'
																	}),
																	openSearch : BX.delegate(BX.SocNetLogDestination.BXfpOpenDialogCallback, {
																		inputBoxName: 'sonet_group_create_popup_users_input_box_post_<?=$selectorName?>',
																		inputName: 'sonet_group_create_popup_users_input_post_<?=$selectorName?>',
																		tagInputName: 'sonet_group_create_popup_users_tag_post_<?=$selectorName?>'
																	})
																},
																items : {
																	users : <?=(is_array($arResult["POST"]["USERS_FOR_JS_E"]) && !empty($arResult["POST"]["USERS_FOR_JS_E"]) ? CUtil::PhpToJSObject($arResult["POST"]["USERS_FOR_JS_E"]) : '{}')?>,
																	groups : {},
																	sonetgroups : {},
																	department : departmentExtranet,
																	departmentRelation : departmentRelationExtranet
																},
																itemsLast : {
																	users : lastUsers,
																	sonetgroups : {},
																	department : {},
																	groups : {}
																},
																itemsSelected : <?=(empty($arValue)? '{}': CUtil::PhpToJSObject($arValue))?>
															});
															BX.BXGCE.arUserSelector.push('<?=$selectorName?>');
															BX.bind(BX('sonet_group_create_popup_users_input_post_<?=$selectorName?>'), 'keyup', BX.delegate(BX.SocNetLogDestination.BXfpSearch, {
																formName: '<?=$selectorName?>',
																inputName: 'sonet_group_create_popup_users_input_post_<?=$selectorName?>',
																tagInputName: 'sonet_group_create_popup_users_tag_post_<?=$selectorName?>'
															}));
															BX.bind(BX('sonet_group_create_popup_users_input_post_<?=$selectorName?>'), 'keydown', BX.delegate(BX.SocNetLogDestination.BXfpSearchBefore, {
																formName: '<?=$selectorName?>',
																inputName: 'sonet_group_create_popup_users_input_post_<?=$selectorName?>'
															}));
															BX.bind(BX('sonet_group_create_popup_users_input_post_<?=$selectorName?>'), 'blur', BX.delegate(BX.SocNetLogDestination.BXfpBlurInput, {
																inputBoxName: 'sonet_group_create_popup_users_input_box_post_<?=$selectorName?>',
																tagInputName: 'sonet_group_create_popup_users_tag_post_<?=$selectorName?>'
															}));
															BX.bind(BX('sonet_group_create_popup_users_input_post_<?=$selectorName?>'), 'click', function(e) {
																BX.BXGCE.setSelector('<?=$selectorName?>');
																BX.SocNetLogDestination.openDialog('<?=$selectorName?>');
																BX.PreventDefault(e);
															});
															BX.bind(BX('sonet_group_create_popup_users_container_post_<?=$selectorName?>'), 'click', function(e) {
																BX.BXGCE.setSelector('<?=$selectorName?>');
																BX.SocNetLogDestination.openDialog('<?=$selectorName?>');
																BX.PreventDefault(e);
															});
														});
													</script>
												</div>
												<div id="sonet_group_create_popup_action_title" class="invite-dialog-inv-block"><?=GetMessage(
													'SONET_GCE_T_DEST_EXTRANET_SELECTOR',
													array(
														'#ACTION#' => '<a href="javascript:void(0);" id="sonet_group_create_popup_action_title_link" class="invite-dialog-inv-link" data-action="invite">'.GetMessage('SONET_GCE_T_DEST_EXTRANET_SELECTOR_INVITE').'</a>'
													)
												)?></div><?
												?><div id="sonet_group_create_popup_action_block_invite" style="display: <?=(isset($arResult["POST"]["EXTRANET_INVITE_ACTION"]) && $arResult["POST"]["EXTRANET_INVITE_ACTION"] == "add" ? "none" : "block")?>;"><?

													if(strlen($arResult["WarningMessage"]) > 0)
													{
														?><div class='errortext'><?=$arResult["WarningMessage"]?></div><?
													}

													?><table class="invite-dialog-inv-form-table">
														<tr>
															<td class="invite-dialog-inv-form-l" style="vertical-align: top;">
																<label for="EMAILS"><?echo GetMessage("SONET_GCE_T_DEST_EXTRANET_EMAIL_SHORT")?></label>
															</td>
															<td class="invite-dialog-inv-form-r">
																<textarea
																		rows="5"
																		type="text"
																		name="EMAILS"
																		id="EMAILS"
																		class="invite-dialog-inv-form-textarea"
																		onblur="if(this.value == ''){BX.removeClass(this, 'invite-dialog-inv-form-textarea-active'); this.value = this.value.replace(new RegExp(/^$/), '<?=GetMessage("SONET_GCE_T_EMAILS_DESCR")?>')}"
																		onfocus="BX.addClass(this, 'invite-dialog-inv-form-textarea-active'); this.value = this.value.replace('<?=GetMessage("SONET_GCE_T_EMAILS_DESCR")?>', '')"
																><?=(strlen($arResult["POST"]["EMAILS"]) > 0 ? htmlspecialcharsbx($arResult["POST"]["EMAILS"]) : GetMessage("SONET_GCE_T_EMAILS_DESCR"));?></textarea>
															</td>
														</tr>
													</table>
												</div>
												<div id="sonet_group_create_popup_action_block_add" style="display: <?=(isset($arResult["POST"]["EXTRANET_INVITE_ACTION"]) && $arResult["POST"]["EXTRANET_INVITE_ACTION"] == "add" ? "block" : "none")?>;"><?

													?><table class="invite-dialog-inv-form-table">
														<tr>
															<td class="invite-dialog-inv-form-l">
																<label for="ADD_EMAIL"><?echo GetMessage("SONET_GCE_T_DEST_EXTRANET_ADD_EMAIL_TITLE")?></label>
															</td>
															<td class="invite-dialog-inv-form-r">
																<input type="text" name="ADD_EMAIL" id="ADD_EMAIL" class="invite-dialog-inv-form-inp" value="<?echo htmlspecialcharsbx($_POST["ADD_EMAIL"])?>">
															</td>
														</tr>
														<tr>
															<td class="invite-dialog-inv-form-l">
																<label for="ADD_NAME"><?echo GetMessage("SONET_GCE_T_DEST_EXTRANET_ADD_NAME_TITLE")?></label>
															</td>
															<td class="invite-dialog-inv-form-r">
																<input type="text" name="ADD_NAME" id="ADD_NAME" class="invite-dialog-inv-form-inp" value="<?echo htmlspecialcharsbx($_POST["ADD_NAME"])?>">
															</td>
														</tr>
														<tr>
															<td class="invite-dialog-inv-form-l">
																<label for="ADD_LAST_NAME"><?echo GetMessage("SONET_GCE_T_DEST_EXTRANET_ADD_LAST_NAME_TITLE")?></label>
															</td>
															<td class="invite-dialog-inv-form-r">
																<input type="text" name="ADD_LAST_NAME" id="ADD_LAST_NAME" class="invite-dialog-inv-form-inp" value="<?echo htmlspecialcharsbx($_POST["ADD_LAST_NAME"])?>">
															</td>
														</tr>
														<tr class="invite-dialog-inv-form-footer">
															<td class="invite-dialog-inv-form-l">&nbsp;</td>
															<td class="invite-dialog-inv-form-r">
																<div class="invite-dialog-inv-form-checkbox-wrap">
																	<input type="checkbox" name="ADD_SEND_PASSWORD" id="ADD_SEND_PASSWORD" value="Y" class="invite-dialog-inv-form-checkbox"><label class="invite-dialog-inv-form-checkbox-label" for="ADD_SEND_PASSWORD"><?echo GetMessage("SONET_GCE_T_DEST_EXTRANET_ADD_SEND_PASSWORD_TITLE")?></label>
																</div>
															</td>
														</tr>
													</table><?

												?></div>
												<script>
													BX.ready(function() {
														BX.BXGCE.bindActionLink(BX("sonet_group_create_popup_action_title_link"));
													});
												</script><?
											?></div><?

											?><div id="sonet_group_create_popup_action_block_invite_2" style="display: flex; flex-wrap: wrap;"><?
												?><div class="invite-dialog-inv-text-bold" style="width: 100%;"><label for="MESSAGE_TEXT"><?echo GetMessage("SONET_GCE_T_DEST_EXTRANET_INVITE_MESSAGE_TITLE")?></label></div>
												<textarea rows="5" type="text" name="MESSAGE_TEXT" id="MESSAGE_TEXT" class="invite-dialog-inv-form-textarea invite-dialog-inv-form-textarea-active"<?=($arResult["messageTextDisabled"] ? " disabled readonly" : "")?>><?
													echo $arResult["inviteMessageText"];
												?></textarea><?
											?></div><?

											?><input type="hidden" id="EXTRANET_INVITE_ACTION" name="EXTRANET_INVITE_ACTION" value="invite"><?

										?></div>
									</div>
								</div></div><? // INVITE_EXTRANET_block
							}
						} // create or invite

						if (!array_key_exists("TAB", $arResult) || $arResult["TAB"] == "edit")
						{
							if (count($arResult["Subjects"]) == 1)
							{
								$arKeysTmp = array_keys($arResult["Subjects"]);
								?><input type="hidden" name="GROUP_SUBJECT_ID" value="<?=$arKeysTmp[0]?>"><?
							}
							else
							{
								?><div class="social-group-create-options-item">
									<div class="social-group-create-options-item-column-left">
										<div id="GROUP_SUBJECT_ID_LABEL_block" class="social-group-create-options-item-name<?=($arResult["POST"]["PROJECT"] == "Y" ? " sgcp-switch-project" : "")?>">
											<div class="sonet-group-create-popup-form-add-title sgcp-block-nonproject"><?=Loc::getMessage("SONET_GCE_T_SUBJECT")?></div>
											<div class="sonet-group-create-popup-form-add-title sgcp-block-project"><?=Loc::getMessage("SONET_GCE_T_SUBJECT_PROJECT")?></div>
										</div>
									</div>
									<div class="social-group-create-options-item-column-right">
										<div class="social-group-create-field-block">
											<select name="GROUP_SUBJECT_ID" id="GROUP_SUBJECT_ID" class="social-group-create-field social-group-create-field-select">
												<option value=""><?= Loc::getMessage("SONET_GCE_T_TO_SELECT") ?></option><?
												foreach ($arResult["Subjects"] as $key => $value)
												{
													?><option value="<?=$key?>"<?=($key == $arResult["POST"]["SUBJECT_ID"]) ? " selected" : "" ?>><?=$value?></option><?
												}
												?></select>
										</div>
									</div>
								</div><?
							}
						}

					?></div><? // social-group-create-options

					if (!array_key_exists("TAB", $arResult) || $arResult["TAB"] == "edit")
					{
						?><div class="social-group-create-additional-block">
							<div class="social-group-create-additional-alt<?=($arResult['openAdditional'] ? ' opened' : '')?>" id="switch_additional">
								<div class="social-group-create-additional-alt-more"><?=Loc::getMessage('SONET_GCE_T_ADDITIONAL_SWITCH')?></div>
								<div class="social-group-create-additional-alt-promo">
									<span class="social-group-create-additional-alt-promo-text" bx-block-id="features"><?=Loc::getMessage('SONET_GCE_T_FEATURES_SWITCH')?></span>
									<?
									if ($arResult["POST"]["CLOSED"] != "Y")
									{
										?><span class="social-group-create-additional-alt-promo-text" bx-block-id="initperms"><?=Loc::getMessage('SONET_GCE_T_PERMS_SWITCH')?></span><?
									}
									if ($arParams["USE_KEYWORDS"] == "Y")
									{
										?><span class="social-group-create-additional-alt-promo-text" bx-block-id="tags"><?=Loc::getMessage('SONET_GCE_T_KEYWORDS_SWITCH')?></span><?
									}
									?>
									<span class="social-group-create-additional-alt-promo-text" bx-block-id="type"><?=Loc::getMessage('SONET_GCE_T_TYPE_SWITCH')?></span>
								</div>
							</div>
							<div class="social-group-create-openable-block-outer<?=($arResult['openAdditional'] ? '' : ' invisible')?>" id="block_additional">
								<div class="social-group-create-options social-group-create-options-more social-group-create-openable-block" id="block_additional_inner">
									<div class="social-group-create-additional-block-item" id="additional-block-features">
										<div class="social-group-create-options-item social-group-create-form-field-list-block">
											<div class="social-group-create-options-item-column-left">
												<div class="social-group-create-options-item-name"><?=Loc::getMessage('SONET_GCE_TAB_2')?></div>
											</div>
											<div class="social-group-create-options-item-column-right">
												<div class="social-group-create-options-item-column-one">
													<div class="social-group-create-form-field-list"><?

														foreach ($arResult["POST"]["FEATURES"] as $feature => $arFeature)
														{
															$customTitle = false;
															$featureTitle = $featureTitleOriginal = (
																isset($arResult["arSocNetFeaturesSettings"][$feature]["title"])
																&& strlen($arResult["arSocNetFeaturesSettings"][$feature]["title"]) > 0
																	? $arResult["arSocNetFeaturesSettings"][$feature]["title"]
																	: Loc::getMessage("SONET_FEATURES_".$feature."_GROUP")
															);

															if (empty($featureTitle))
															{
																$featureTitle = $featureTitleOriginal = Loc::getMessage("SONET_FEATURES_".$feature);
															}

															if (strlen($arResult["POST"]["FEATURES"][$feature]["FeatureName"]) > 0)
															{
																$customTitle = ($arResult["POST"]["FEATURES"][$feature]["FeatureName"] != $featureTitle);
																$featureTitle = $arResult["POST"]["FEATURES"][$feature]["FeatureName"];
															}

															?><div class="social-group-create-form-field-list-item<?=($customTitle ? ' custom-value' : '')?>">
																<input name="<?=htmlspecialcharsbx($feature)?>_active" type="checkbox" class="social-group-create-form-field-list-input" value="Y" <?=($arFeature["Active"] ? 'checked' : '')?>>
																<span class="social-group-create-form-field-list-name"><label class="social-group-create-form-field-list-label"><?=htmlspecialcharsex($featureTitleOriginal)?></label></span>
																<input type="text" name="<?=htmlspecialcharsbx($feature)?>_name" class="social-group-create-form-field-input-text" value="<?=($customTitle ? $featureTitle : '')?>">
																<span class="social-group-create-form-pencil"></span>
																<span class="social-group-create-form-field-cancel"></span>
															</div><?
														}

													?></div>
												</div>
											</div>
										</div>
									</div><?

									if ($arResult["POST"]["CLOSED"] != "Y")
									{
										?><div class="social-group-create-additional-block-item" id="additional-block-initperms">
											<div class="social-group-create-options-item">
												<div class="social-group-create-options-item-column-left">
													<div id="GROUP_INVITE_PERMS_LABEL_block" class="social-group-create-options-item-name<?=($arResult["POST"]["PROJECT"] == "Y" ? " sgcp-switch-project" : "")?>">
														<div class="sonet-group-create-popup-form-add-title sgcp-block-nonproject"><?=Loc::getMessage("SONET_GCE_T_INVITE2")?></div>
														<div class="sonet-group-create-popup-form-add-title sgcp-block-project"><?=Loc::getMessage("SONET_GCE_T_INVITE2_PROJECT")?></div>
													</div>
												</div>
												<div id="GROUP_INVITE_PERMS_block" class="social-group-create-options-item-column-right<?=($arResult["POST"]["PROJECT"] == "Y" ? " sgcp-switch-project" : "")?>">
													<div class="social-group-create-field-block sgcp-flex-nonproject">
														<select name="GROUP_INITIATE_PERMS" id="GROUP_INITIATE_PERMS" class="social-group-create-field social-group-create-field-select">
															<option value=""><?= GetMessage("SONET_GCE_T_TO_SELECT") ?></option><?
															foreach ($arResult["InitiatePerms"] as $key => $value)
															{
																?><option id="GROUP_INITIATE_PERMS_OPTION_<?=$key?>" value="<?=$key?>"<?=($key == $arResult["POST"]["INITIATE_PERMS"]) ? " selected" : "" ?>><?=$value?></option><?
															}
														?></select>
													</div>
													<div class="social-group-create-field-block sgcp-flex-project">
														<select name="GROUP_INITIATE_PERMS" id="GROUP_INITIATE_PERMS_PROJECT" class="social-group-create-field social-group-create-field-select">
															<option value=""><?= GetMessage("SONET_GCE_T_TO_SELECT") ?></option><?
															foreach ($arResult["InitiatePermsProject"] as $key => $value)
															{
																?><option id="GROUP_INITIATE_PERMS_OPTION_PROJECT_<?=$key?>" value="<?=$key?>"<?=($key == $arResult["POST"]["INITIATE_PERMS"]) ? " selected" : "" ?>><?=$value?></option><?
															}
														?></select>
													</div>
												</div>
											</div>
										</div><?
									}
									else
									{
										?><input type="hidden" value="<?=$arResult["POST"]["INITIATE_PERMS"]?>" name="GROUP_INITIATE_PERMS"><?
									}

									if (
										$arResult["POST"]["CLOSED"] != "Y"
										&& !$arResult["bExtranet"]
										&& !IsModuleInstalled("im")
									)
									{
										?><div class="social-group-create-additional-block-item" id="additional-block-spamperms">
											<div class="social-group-create-options-item">
												<div class="social-group-create-options-item-column-left">
													<div class="social-group-create-options-item-name">
														<div class="sonet-group-create-popup-form-add-title"><?=Loc::getMessage("SONET_GCE_T_SPAM_PERMS")?></div>
													</div>
												</div>
												<div class="social-group-create-options-item-column-right">
													<div class="social-group-create-field-block">
														<select name="GROUP_SPAM_PERMS" class="social-group-create-field social-group-create-field-select">
															<option value=""><?= Loc::getMessage("SONET_GCE_T_TO_SELECT") ?></option><?
															foreach ($arResult["SpamPerms"] as $key => $value)
															{
																?><option value="<?=$key?>"<?=($key == $arResult["POST"]["SPAM_PERMS"]) ? " selected" : "" ?>><?=$value?></option><?
															}
														?></select>
													</div>
												</div>
											</div>
										</div><?
									}
									else
									{
										?><input type="hidden" value="<?=$arResult["POST"]["SPAM_PERMS"]?>" name="GROUP_SPAM_PERMS"><?
									}

									if ($arParams["USE_KEYWORDS"] == "Y")
									{
										$tagsList = explode(',', $arResult["POST"]["KEYWORDS"]);

										$tags = "";
										$tagsInput = "";
										foreach($tagsList as $val)
										{
											$val = trim($val);
											if(strlen($val) > 0)
											{
												$tags .= '<span class="js-id-tdp-mem-sel-is-items social-group-create-sliders-h-invisible" data-tag="'.htmlspecialcharsbx($val).'">'.
													'<span class="js-id-tdp-mem-sel-is-item social-group-create-form-field-item">'.
														'<a href="#" class="social-group-create-form-field-item-text">'.htmlspecialcharsEx($val).'</a>'.
														'<span class="js-id-tdp-mem-sel-is-item-delete social-group-create-form-field-item-delete"></span>'.
													'</span>'.
												'</span>';

												if ($tagsInput != "")
												{
													$tagsInput .= ",";
												}
												$tagsInput .= htmlspecialcharsbx($val);
											}
										}

										?><div class="social-group-create-additional-block-item" id="additional-block-tags">
											<div class="social-group-create-options-item">
												<div class="social-group-create-options-item-column-left">
													<div class="social-group-create-options-item-name"><?=Loc::getMessage("SONET_GCE_T_KEYWORDS")?></div>
												</div>
												<div class="social-group-create-options-item-column-right">
													<div class="social-group-create-control-inner social-group-create-form-field inline t-filled tdp-mem-sel-is-empty-false t-min tdp-mem-sel-is-min">
													<span class="social-group-create-form-field-controls" id="group-tags-container">
														<?=$tags?>
														<a href="javascript:void(0);" id="group-tags-add-new" class="js-id-tdp-mem-sel-is-open-form social-group-create-form-field-when-filled social-group-create-form-field-link add"><?=Loc::getMessage("SONET_GCE_T_KEYWORDS_ADD_TAG")?></a>
													</span>
														<input type="hidden" name="GROUP_KEYWORDS" id="GROUP_KEYWORDS" value="<?=$tagsInput?>,">
													</div>
													<div id="sgcp-tags-popup-content" style="display: none;"><?
														if (ModuleManager::isModuleInstalled("search"))
														{
															$APPLICATION->IncludeComponent(
																"bitrix:search.tags.input",
																".default",
																Array(
																	"NAME" => "GROUP_KEYWORDS-popup-input",
																	"ID" => "GROUP_KEYWORDS-popup-input",
																	"VALUE" => "",
																	"arrFILTER" => "socialnetwork",
																	"PAGE_ELEMENTS" => "10",
																	"SORT_BY_CNT" => "Y",
																)
															);
															?>
															<script>
																new BX.BXGCETagsForm({
																	containerNodeId: 'group-tags-container',
																	hiddenFieldId: 'GROUP_KEYWORDS',
																	addNewLinkId: 'group-tags-add-new',
																	popupContentNodeId: 'sgcp-tags-popup-content'
																});
															</script>
															<?
														}
														else
														{
															?><input type="text" name="GROUP_KEYWORDS" style="width:98%" value="<?= $arResult["POST"]["KEYWORDS"]; ?>"><?
														}
													?></div>
												</div>
											</div>
										</div><?
									}

									?><div class="social-group-create-additional-block-item" id="additional-block-type">
										<div class="social-group-create-options-item social-group-create-form-field-list-block">

											<div id="GROUP_TYPE_LABEL_block" class="social-group-create-options-item-column-left<?=($arResult["POST"]["PROJECT"] == "Y" ? " sgcp-switch-project" : "")?>">
												<div class="social-group-create-options-item-name sgcp-block-nonproject"><?=GetMessage("SONET_GCE_T_TITLE_TYPE")?></div>
												<div class="social-group-create-options-item-name sgcp-block-project"><?=GetMessage("SONET_GCE_T_TITLE_TYPE_PROJECT")?></div>
											</div>
											<div class="social-group-create-options-item-column-right">
												<div class="social-group-create-options-item-column-one">
													<div class="social-group-create-form-field-list"><?

														if (
															!$arResult["bExtranet"]
															|| intval($arResult["GROUP_ID"]) > 0
														)
														{
															if (
																$arResult["hidePresetSettings"]
																|| $arResult["bExtranet"]
															)
															{
																?><input type="hidden" value="<?=($arResult["POST"]["VISIBLE"] == "Y") ? "Y" : "N"?>" name="GROUP_VISIBLE" id="GROUP_VISIBLE"><?
															}
															elseif (!$arResult["bExtranet"])
															{
																?><div class="social-group-create-form-field-list-item">
																<label class="social-group-create-form-field-list-label<?=($arResult["POST"]["PROJECT"] == "Y" ? ' sgcp-switch-project' : '')?>" id="GROUP_VISIBLE_LABEL_block">
																	<input type="checkbox" id="GROUP_VISIBLE" name="GROUP_VISIBLE" class="social-group-create-form-field-list-input" value="Y" <?= ($arResult["POST"]["VISIBLE"] == "Y") ? " checked" : ""?>>
																	<span class="social-group-create-form-field-list-name sgcp-inlineblock-nonproject" title="<?=Loc::getMessage("SONET_GCE_T_PARAMS_VIS2_HINT")?>"><?=Loc::getMessage("SONET_GCE_T_PARAMS_VIS2") ?></span>
																	<span class="social-group-create-form-field-list-name sgcp-inlineblock-project" title="<?=Loc::getMessage("SONET_GCE_T_PARAMS_VIS2_HINT_PROJECT")?>"><?=Loc::getMessage("SONET_GCE_T_PARAMS_VIS2_PROJECT")?></span>
																</label>
																</div><?
															}
														}

														if (!$arResult["bExtranet"])
														{
															?><div class="social-group-create-form-field-list-item">
															<label class="social-group-create-form-field-list-label<?=($arResult["POST"]["PROJECT"] == "Y" ? ' sgcp-switch-project' : '')?>" id="GROUP_OPENED_LABEL_block">
																<input type="checkbox" id="GROUP_OPENED" value="Y" name="GROUP_OPENED" class="social-group-create-form-field-list-input" <?= ($arResult["POST"]["OPENED"] == "Y") ? " checked" : ""?><?= ($arResult["POST"]["VISIBLE"] == "Y") ? "" : " disabled"?>>
																<span class="social-group-create-form-field-list-name sgcp-inlineblock-nonproject" title="<?=Loc::getMessage("SONET_GCE_T_PARAMS_OPEN2_HINT")?>"><?=Loc::getMessage("SONET_GCE_T_PARAMS_OPEN2") ?></span>
																<span class="social-group-create-form-field-list-name sgcp-inlineblock-project" title="<?=Loc::getMessage("SONET_GCE_T_PARAMS_OPEN2_HINT_PROJECT")?>"><?=Loc::getMessage("SONET_GCE_T_PARAMS_OPEN2_PROJECT") ?></span>
															</label>
															</div><?
														}
														else
														{
															?><input type="hidden" value="N" name="GROUP_OPENED" id="GROUP_OPENED"><?
														}

														if (
															!$arResult["bExtranet"]
															|| intval($arResult["GROUP_ID"]) > 0
														)
														{
															if ($arParams["GROUP_ID"] > 0)
															{
																?><div class="social-group-create-form-field-list-item">
																<label class="social-group-create-form-field-list-label<?=($arResult["POST"]["PROJECT"] == "Y" ? ' sgcp-switch-project' : '')?>" id="GROUP_CLOSED_LABEL_block">
																	<input type="checkbox" id="GROUP_CLOSED" name="GROUP_CLOSED" class="social-group-create-form-field-list-input" value="Y" <?= ($arResult["POST"]["CLOSED"] == "Y") ? " checked" : ""?>>
																	<span class="social-group-create-form-field-list-name sgcp-inlineblock-nonproject" title="<?=Loc::getMessage("SONET_GCE_T_PARAMS_CLOSED2_HINT")?>"><?=Loc::getMessage("SONET_GCE_T_PARAMS_CLOSED2") ?></span>
																	<span class="social-group-create-form-field-list-name sgcp-inlineblock-project" title="<?=Loc::getMessage("SONET_GCE_T_PARAMS_CLOSED2_HINT_PROJECT")?>"><?=Loc::getMessage("SONET_GCE_T_PARAMS_CLOSED2_PROJECT") ?></span>
																</label>
																</div><?
															}
															else
															{
																?><input type="hidden" value="<?=($arResult["POST"]["CLOSED"] == "Y") ? "Y" : "N"?>" name="GROUP_CLOSED"><?
															}
														}

														if ($arResult["bExtranetInstalled"])
														{
															if ($arResult["hidePresetSettings"])
															{
																?><input type="hidden" value="<?=($arResult["POST"]["IS_EXTRANET_GROUP"] == "Y" ? "Y" : "N")?>" name="IS_EXTRANET_GROUP" id="IS_EXTRANET_GROUP"><?
															}
															else
															{
																?><div class="social-group-create-form-field-list-item">
																	<label class="social-group-create-form-field-list-label<?=($arResult["POST"]["PROJECT"] == "Y" ? ' sgcp-switch-project' : '')?>" id="GROUP_EXTRANET_LABEL_block">
																		<input type="checkbox" id="IS_EXTRANET_GROUP" name="IS_EXTRANET_GROUP" class="social-group-create-form-field-list-input" value="Y"<?=($arResult["POST"]["IS_EXTRANET_GROUP"] == "Y" ? " checked" : "")?>>
																		<span class="social-group-create-form-field-list-name sgcp-inlineblock-nonproject" title="<?=Loc::getMessage("SONET_GCE_T_IS_EXTRANET_GROUP2_HINT")?>"><?=Loc::getMessage("SONET_GCE_T_IS_EXTRANET_GROUP2") ?></span>
																		<span class="social-group-create-form-field-list-name sgcp-inlineblock-project" title="<?=Loc::getMessage("SONET_GCE_T_IS_EXTRANET_GROUP2_HINT_PROJECT")?>"><?=Loc::getMessage("SONET_GCE_T_IS_EXTRANET_GROUP2_PROJECT") ?></span>
																	</label>
																</div><?
															}
														}

														if ($arResult["intranetInstalled"])
														{
															if ($arResult["hidePresetSettings"])
															{
																?><input type="hidden" id="GROUP_PROJECT" value="<?=($arResult["POST"]["PROJECT"] == "Y") ? "Y" : "N"?>" name="GROUP_PROJECT"><?
															}
															else
															{
																?><div class="social-group-create-form-field-list-item">
																	<label class="social-group-create-form-field-list-label">
																		<input type="checkbox" id="GROUP_PROJECT" name="GROUP_PROJECT" value="Y" class="social-group-create-form-field-list-input" onclick="BXSwitchProject(this.checked)" <?= ($arResult["POST"]["PROJECT"] == "Y") ? " checked" : ""?>>
																		<span class="social-group-create-form-field-list-name"><?=Loc::getMessage("SONET_GCE_T_PARAMS_PROJECT") ?></span>
																	</label>
																</div><?
															}
														}

													?></div>
												</div>
											</div>
										</div>
									</div><?

									if (!empty($arResult["GROUP_PROPERTIES_NON_MANDATORY"]))
									{
										foreach ($arResult["GROUP_PROPERTIES_NON_MANDATORY"] as $FIELD_NAME => $arUserField)
										{
											if ($FIELD_NAME == "UF_SG_DEPT")
											{
												continue;
											}
											?><div class="social-group-create-options-item">
												<div class="social-group-create-options-item-column-left">
													<div class="social-group-create-options-item-name"><?=htmlspecialcharsex($arUserField["EDIT_FORM_LABEL"])?></div>
												</div>
												<div class="social-group-create-options-item-column-right">
												<div class="social-group-create-options-item-column-one social-group-create-form-control-block"><?
													$APPLICATION->IncludeComponent(
														"bitrix:system.field.edit",
														$arUserField["USER_TYPE"]["USER_TYPE_ID"],
														array("bVarsFromForm" => $arResult["bVarsFromForm"], "arUserField" => $arUserField),
														null,
														array("HIDE_ICONS"=>"Y")
													);
													?></div>
												</div><?
											?></div><?
										}
									}
								// social-group-create-options-item
								?></div>
							</div><?

							if (!empty($arResult["GROUP_PROPERTIES_MANDATORY"]))
							{
								?><div class="social-group-create-options"><?
									foreach ($arResult["GROUP_PROPERTIES_MANDATORY"] as $FIELD_NAME => $arUserField)
									{
										?><div class="social-group-create-options-item">
											<div class="social-group-create-options-item-column-left">
												<div class="social-group-create-options-item-name"><?=htmlspecialcharsex($arUserField["EDIT_FORM_LABEL"])?></div>
											</div>
											<div class="social-group-create-options-item-column-right">
												<div class="social-group-create-options-item-column-one social-group-create-form-control-block"><?
													$APPLICATION->IncludeComponent(
														"bitrix:system.field.edit",
														$arUserField["USER_TYPE"]["USER_TYPE_ID"],
														array("bVarsFromForm" => $arResult["bVarsFromForm"], "arUserField" => $arUserField),
														null,
														array("HIDE_ICONS"=>"Y")
													);
												?></div>
											</div><?
										?></div><?
									}
								?></div><?
							}
						?></div><?
					} // create or edit

					?><div class="sonet-slider-footer-fixed">
						<input type="hidden" name="SONET_USER_ID" value="<?=$arResult["currentUserId"]?>">
						<input type="hidden" name="SONET_GROUP_ID" id="SONET_GROUP_ID" value="<?=intval($arResult["GROUP_ID"])?>">
						<input type="hidden" name="TAB" value="<?=htmlspecialcharsbx(CUtil::JSEscape($arResult["TAB"]))?>">
						<div class="social-group-create-buttons"><?
							?><span class="sonet-ui-btn-cont sonet-ui-btn-cont-center"><?
								?><button class="ui-btn ui-btn-success ui-btn-md" id="sonet_group_create_popup_form_button_submit" bx-action-type="<?=(isset($actionType) ? $actionType : 'none')?>"><?=$strSubmitButtonTitle?></button><?

								if (
									$arResult["USE_PRESETS"] == 'Y'
									&& $arParams["GROUP_ID"] <= 0
									&& (
										empty($arResult["TAB"])
										|| $arResult["TAB"] == "edit"
									)
								)
								{
									?><button class="ui-btn ui-btn-link" id="sonet_group_create_popup_form_button_step_2_back"><?=Loc::getMessage("SONET_GCE_T_T_CANCEL")?></button><?
								}
								else
								{
									?><button class="ui-btn ui-btn-link" id="sonet_group_create_popup_form_button_step_2_cancel"><?=Loc::getMessage("SONET_GCE_T_T_CANCEL")?></button><?
								}

								if (false && $arResult["templateEditMode"] != 'Y')
								{
									?><input type="checkbox" class="task-edit-add-template-checkbox" id="SAVE_AS_TEMPLATE" name="SAVE_AS_TEMPLATE" value="Y"><?
								}

							?></span><? // class="popup-window-buttons"
						?></div>
					</div><? // sonet-slider-footer-fixed

				?></div></div><? // sonet_group_create_form_step_2 & .social-group-create-form
			?></div>
		</form>
		<?
	}
	else
	{
		?><?= GetMessage($arParams["GROUP_ID"] > 0? "SONET_GCE_T_SUCCESS_EDIT" : "SONET_GCE_T_SUCCESS_CREATE")?><?
		?><br><br>
		<a href="<?= $arResult["Urls"]["NewGroup"] ?>"><?= $arResult["POST"]["NAME"]; ?></a><?
	}
}
?>