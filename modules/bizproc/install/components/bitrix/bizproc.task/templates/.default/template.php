<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/bizproc/tools.js');
\Bitrix\Main\Loader::includeModule('socialnetwork');

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.viewer',
	'ui.tooltip',
	'socnetlogdest',
	'bp_user_selector',
	'ui.buttons.icons'
]);

$cmpId = RandString();

$showDelegationButton = (
	!$arResult['IsComplete']
	&& ($arResult['isAdmin'] || (int)$arResult['TASK']['DELEGATION_TYPE'] !== CBPTaskDelegationType::None)
	&& IsModuleInstalled('intranet')
);

if (empty($arResult['DOCUMENT_ICON']))
{
	$moduleIcon = 'default';
	if (in_array($arResult['TASK']['MODULE_ID'], array('crm', 'disk', 'iblock', 'lists', 'tasks')))
		$moduleIcon = $arResult['TASK']['MODULE_ID'];

	$arResult['DOCUMENT_ICON'] = $templateFolder.'/images/bp-'.$moduleIcon.'-icon.png';
}
?>
<script>
	BX.message({
		BPAT_DELEGATE_SELECT : '<?=GetMessageJS('BPAT_DELEGATE_SELECT')?>',
		BPAT_DELEGATE_CANCEL : '<?=GetMessageJS('BPAT_DELEGATE_CANCEL')?>'
	});
</script>
<?if ($arParams['POPUP']):?>
<div class="bp-popup-title"><?=htmlspecialcharsbx($arResult["WORKFLOW_TEMPLATE_NAME"])?></div>
<div class="bp-popup">
<?endif?>
<div class="bp-task-page bp-lent <?if (empty($arResult["TASK"]['STARTED_BY_PHOTO_SRC'])):?>no-photo<?endif?>">
	<?if (!empty($arResult["TASK"]['STARTED_BY_PHOTO_SRC'])):?>
	<span class="bp-avatar" bx-tooltip-user-id="<?=(int)$arResult["TASK"]['STARTED_BY']?>" bx-tooltip-classname="intrantet-user-selector-tooltip">
		<img src="<?=$arResult["TASK"]['STARTED_BY_PHOTO_SRC']?>" alt="">
	</span>
	<?endif?>
	<span class="bp-title"><?=$arResult["TASK"]["NAME"]?></span>
	<?if ($arResult["TASK"]["DOCUMENT_NAME"]):?>
	<span class="bp-title-desc">
		<span class="bp-title-desc-icon">
			<img src="<?=htmlspecialcharsbx($arResult['DOCUMENT_ICON'])?>" width="36" border="0" />
		</span>
		<span class=""><?=$arResult["TASK"]["DOCUMENT_NAME"]?></span>
	</span>
	<?endif?>
	<div class="bp-short-process-inner">
		<?$APPLICATION->IncludeComponent(
			"bitrix:bizproc.workflow.faces",
			"",
			array(
				"WORKFLOW_ID" => $arResult["TASK"]["WORKFLOW_ID"],
				"TARGET_TASK_ID" => $arResult["TASK"]["ID"]
			),
			$component
		);
		if ($arResult['ReadOnly']):
			echo '<span class="bp-status"></span>';
		elseif ($arResult["ShowMode"] == "Success"):
			switch ($arResult["TASK"]['USER_STATUS'])
			{
				case CBPTaskUserStatus::Yes:
					echo '<span class="bp-status-ready"><span>'.GetMessage('BPATL_USER_STATUS_YES').'</span></span>';
					break;
				case CBPTaskUserStatus::No:
				case CBPTaskUserStatus::Cancel:
					echo '<span class="bp-status-cancel"><span>'.GetMessage('BPATL_USER_STATUS_NO').'</span></span>';
					break;
				default:
					echo '<span class="bp-status-ready"><span>'.GetMessage('BPATL_USER_STATUS_OK').'</span></span>';
			}
		elseif ($arResult["TASK"]['IS_INLINE'] == 'Y'):?>
			<div class="bp-btn-panel">
				<div class="bp-btn-panel-inner">
				<?php
				if ($arParams['POPUP']):
				foreach ($arResult['TaskControls']['BUTTONS'] as $control):
					$isDecline =
						$control['TARGET_USER_STATUS'] == CBPTaskUserStatus::No
						|| $control['TARGET_USER_STATUS'] == CBPTaskUserStatus::Cancel
					;
					$class = $isDecline ? 'danger' : 'success';
					$icon = $isDecline ? 'cancel' : 'done';
					$props = CUtil::PhpToJSObject(array(
						'TASK_ID' => $arResult["TASK"]['ID'],
						$control['NAME'] => $control['VALUE']
					));
					?>
					<a href="#"
						onclick="return BX.Bizproc.doInlineTask(<?= $props ?>, function(){ if (!!BX.Bizproc.taskPopupInstance) BX.Bizproc.taskPopupInstance.close(); if (BX.Bizproc.taskPopupCallback) return BX.Bizproc.taskPopupCallback(); window.location.reload()}, this)"
						class="ui-btn ui-btn-<?= $class ?> ui-btn-icon-<?= $icon ?>"
					><?= $control['TEXT'] ?></a>
				<?
				endforeach;
				else: ?>
					<form method="post" action="<?=POST_FORM_ACTION_URI?>">
						<?= bitrix_sessid_post() ?>
						<input type="hidden" name="action" value="doTask" />
						<input type="hidden" name="id" value="<?= (int)$arResult["TASK"]["ID"] ?>" />
						<input type="hidden" name="TASK_ID" value="<?= (int)$arResult["TASK"]["ID"] ?>" />
						<input type="hidden" name="workflow_id" value="<?= htmlspecialcharsbx($arResult["TASK"]["WORKFLOW_ID"]) ?>" />
						<input type="hidden" name="back_url" value="<?= htmlspecialcharsbx($arResult['backUrl']) ?>" />
						<?
						foreach ($arResult['TaskControls']['BUTTONS'] as $control):
							$isDecline =
								$control['TARGET_USER_STATUS'] == CBPTaskUserStatus::No
								|| $control['TARGET_USER_STATUS'] == CBPTaskUserStatus::Cancel
							;
							$class = $isDecline ? 'danger' : 'success';
							$icon = $isDecline ? 'cancel' : 'done';
							$props = CUtil::PhpToJSObject(array(
								'TASK_ID' => $arResult["TASK"]['ID'],
								$control['NAME'] => $control['VALUE']
							));
							?>
							<button type="submit" name="<?= htmlspecialcharsbx($control['NAME']) ?>"
									value="<?= htmlspecialcharsbx($control['VALUE']) ?>"
									class="ui-btn ui-btn-<?= $class ?> ui-btn-icon-<?= $icon ?>"
							><?= $control['TEXT'] ?>
							</button>
						<?php
						endforeach;
						?>
					</form>
				<?php endif;?>
				</div>
			</div>
		<?php endif?>
	</div>
	<div class="bp-task-block">
		<?
		if (!empty($arResult["ERROR_MESSAGE"])):
			ShowError($arResult["ERROR_MESSAGE"]);
		endif;
		?>
		<span class="bp-task-block-title"><?=GetMessage("BPATL_TASK_TITLE_1")?>: </span>
		<div class="bp-task-block-description">
		<?
		if ($arResult["TASK"]["DESCRIPTION"] <> ''):
			echo \CBPViewHelper::prepareTaskDescription($arResult["TASK"]["DESCRIPTION"]);
		else:
			echo $arResult["TASK"]["NAME"];
		endif;
		?>
		</div>
		<br /><br />
		<p>
			<?if (!empty($arResult["TASK"]["URL"]["VIEW"])):?>
			<a href="<?=$arResult["TASK"]["URL"]["VIEW"]?>" <?if ($arParams['POPUP']):?>target="_blank" <?endif?>><?=GetMessage("BPAT_GOTO_DOC")?></a>
			<?endif;?>
		</p>
		<?
		if ($showDelegationButton && $arResult["TASK"]['IS_INLINE'] == 'Y'): ?>
			<a href="#"
				class="ui-btn ui-btn-light-border"
				onclick="return BX.Bizproc.showDelegationPopup(this, <?= (int)$arResult["TASK"]["ID"] ?>, <?= (int)$arParams["USER_ID"] ?>)"><span></span><?= GetMessage('BPAT_DELEGATE_LABEL') ?>
			</a>
		<?php
		endif;

		if ($arResult["ShowMode"] != "Success" && $arResult["TASK"]['IS_INLINE'] != 'Y'):
			?>
			<form method="post" name="bp_task_<?=$cmpId?>" action="<?=POST_FORM_ACTION_URI?>" enctype="multipart/form-data"
			<?if ($arParams['POPUP']):?> onsubmit="return BX.Bizproc.postTaskForm(this, event)"<?endif?>>
				<?= bitrix_sessid_post() ?>
				<input type="hidden" name="" value="" id="bp_task_<?=$cmpId?>_submiter">
				<input type="hidden" name="action" value="doTask" />
				<input type="hidden" name="id" value="<?= (int)$arResult["TASK"]["ID"] ?>" />
				<input type="hidden" name="TASK_ID" value="<?= (int)$arResult["TASK"]["ID"] ?>" />
				<input type="hidden" name="workflow_id" value="<?= htmlspecialcharsbx($arResult["TASK"]["WORKFLOW_ID"]) ?>" />
				<input type="hidden" name="back_url" value="<?= htmlspecialcharsbx($arResult['backUrl']) ?>" />
				<table class="bizproc-table-main bizproc-task-table" cellpadding="3" border="0">
					<?= $arResult["TaskForm"]?>
				</table>
				<div class="bizproc-item-buttons">
					<?if (!empty($arResult['TaskControls']['BUTTONS'])):?>
						<?
						foreach ($arResult['TaskControls']['BUTTONS'] as $control):
							$isDecline =
								$control['TARGET_USER_STATUS'] == CBPTaskUserStatus::No
								|| $control['TARGET_USER_STATUS'] == CBPTaskUserStatus::Cancel
							;
							$class = $isDecline ? 'danger' : 'success';
							$props = CUtil::PhpToJSObject(array(
								'TASK_ID' => $arResult["TASK"]['ID'],
								$control['NAME'] => $control['VALUE']
							));
							?>
							<button type="submit" name="<?= htmlspecialcharsbx($control['NAME']) ?>"
									value="<?= htmlspecialcharsbx($control['VALUE']) ?>"
									class="ui-btn ui-btn-<?= $class ?>"
							><?= $control['TEXT'] ?>
							</button>
						<?php
						endforeach;
						?>
					<?else: echo $arResult["TaskFormButtons"]; endif;?>

					<?if ($showDelegationButton):?>
						<a href="#"
							class="ui-btn ui-btn-light-border"
							onclick="return BX.Bizproc.showDelegationPopup(this, <?= (int)$arResult["TASK"]["ID"] ?>, <?= (int)$arParams["USER_ID"] ?>)"><span></span><?= GetMessage('BPAT_DELEGATE_LABEL') ?>
						</a>
					<?endif?>
				</div>
				<script>
					BX.ready(function(){
						var form = document.forms['bp_task_<?=$cmpId?>'],
							submiter = BX('bp_task_<?=$cmpId?>_submiter');
						var children = BX.findChildren(form, {property: {type: 'submit'}}, true);
						for (var i=0; i<children.length; i++)
						{
							var cb = function()
							{
								submiter.name =  this.name;
								submiter.value = this.value;
							};

							BX.bind(children[i], 'click', cb);
							BX.bind(children[i], 'tap', cb);
						}
					});
				</script>
			</form>
		<?
		endif;
		?>
	</div>
	<?if (!$arParams['POPUP']):?>
	<div class="bp-tab-container">
		<div id="bp-task-tabs-header" class="bp-tabs-block">
			<span id="bp-task-tab-1" class="bp-tab bp-tab-active" onclick="return function(){
			var t1 = BX('bp-task-tab-1'),
				t2 = BX('bp-task-tab-2'),
				t1c = BX('bp-task-tab-1-content'),
				t2c = BX('bp-task-tab-2-content');

				BX.addClass(t1, 'bp-tab-active'); BX.removeClass(t2, 'bp-tab-active');
				BX.addClass(t1c, 'active'); BX.removeClass(t2c, 'active');
				return false;
			}()"><?=GetMessage("BPATL_COMMENTS")?></span>
			<span id="bp-task-tab-2" class="bp-tab" onclick="return function(){
			var t1 = BX('bp-task-tab-2'),
				t2 = BX('bp-task-tab-1'),
				t1c = BX('bp-task-tab-2-content'),
				t2c = BX('bp-task-tab-1-content');

				BX.addClass(t1, 'bp-tab-active'); BX.removeClass(t2, 'bp-tab-active');
				BX.addClass(t1c, 'active'); BX.removeClass(t2c, 'active');
				return false;
			}()"><?=GetMessage("BPATL_DOC_HISTORY")?></span>
		</div>

		<div id="bp-task-tabs-content" class="bp-tab-contents">
			<div id="bp-task-tab-1-content" class="bp-tab-content active">

	<?endif?>
				<?
				if (!isset($arParams['IFRAME']) || $arParams['IFRAME'] == 'N'):
					// A < E < I < M < Q < U < Y
					// A - NO ACCESS, E - READ, I - ANSWER
					// M - NEW TOPIC
					// Q - MODERATE, U - EDIT, Y - FULL_ACCESS
					$APPLICATION->IncludeComponent("bitrix:forum.comments", "bitrix24", array(
						"FORUM_ID" => CBPHelper::getForumId(),
						"ENTITY_TYPE" => "WF",
						"ENTITY_ID" => CBPStateService::getWorkflowIntegerId($arResult["TASK"]['WORKFLOW_ID']),
						"ENTITY_XML_ID" => "WF_".$arResult["TASK"]['WORKFLOW_ID'],
						"PERMISSION" => "M",
						"URL_TEMPLATES_PROFILE_VIEW" => "/company/personal/user/#user_id#/",
						"SHOW_RATING" => "Y",
						"SHOW_LINK_TO_MESSAGE" => "N",
						"BIND_VIEWER" => "Y",
						'LHE' => [
							'copilotParams' => [],
							'isCopilotEnabled' => false,
						],
					),
						false,
						array('HIDE_ICONS' => 'Y')
					);
				else:
				?>
				<iframe
					src="/bitrix/components/bitrix/bizproc.task/comments.php?TASK_ID=<?=$arResult['TASK']['ID']?>&USER_ID=<?=$arParams['USER_ID']?>&site_id=<?=SITE_ID?>&sessid=<?=bitrix_sessid()?>"
					frameborder="0"
					width="100%"
					height="0"
					onload="var me = this, resizer = function(f) {
						var innerDoc = f.contentDocument ? f.contentDocument
							: (f.contentWindow? f.contentWindow.document : null);
						if (!innerDoc)
							return false;
						var wrapper = BX.findChild(innerDoc.body, {id: 'wrapper'});
						if (!wrapper)
							return false;
						f.style.height = wrapper.offsetHeight + 24 + 'px';
					};
					resizer(me);
					var interval = setInterval(function(){
						var result = resizer(me);
						if (result === false)
							clearInterval(interval);
					}, 300);">
				</iframe>
				<?
				endif;
	if (!$arParams['POPUP']):?>
			</div>

			<div id="bp-task-tab-2-content" class="bp-tab-content">
				<?
				$APPLICATION->IncludeComponent(
					"bitrix:bizproc.log",
					"",
					array(
						"COMPONENT_VERSION" => 2,
						"ID" => $arResult["TASK"]["WORKFLOW_ID"],
						"SET_TITLE" => "N",
						"INLINE_MODE" => "Y",
						"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
					),
					$component
				);

				$currentBodyClass = $APPLICATION->GetPageProperty("BodyClass", false);
				$currentBodyClass = str_replace('flexible-layout', '', $currentBodyClass);
				$APPLICATION->SetPageProperty("BodyClass", $currentBodyClass);
				?>
			</div>
		</div>
	</div>
	<?endif?>
</div>
