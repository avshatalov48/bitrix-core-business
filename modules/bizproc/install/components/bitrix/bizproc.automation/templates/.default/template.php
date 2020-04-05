<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true) die();
\Bitrix\Main\Loader::includeModule('socialnetwork');
CUtil::InitJSCore(
	['tooltip', 'admin_interface', 'date', 'uploader', 'file_dialog', 'bp_user_selector', 'bp_field_type']
);
\Bitrix\Main\UI\Extension::load(['ui.buttons', 'ui.hint']);
/**
 * @var array $arResult
 * @var array $arParams
 * @var CBitrixComponentTemplate $this
 */

$titleView = $arResult['TITLE_VIEW'];
$titleEdit = $arResult['TITLE_EDIT'];

if ($arResult['USE_DISK'])
{
	$this->addExternalJs($this->GetFolder().'/disk_uploader.js');
	$this->addExternalCss('/bitrix/js/disk/css/legacy_uf_common.css');
}
$messages = \Bitrix\Main\Localization\Loc::loadLanguageFile(__FILE__);

if (isset($arParams['~MESSAGES']) && is_array($arParams['MESSAGES']))
{
	$messages = $arParams['~MESSAGES'] + $messages;
}

if (!$arResult['WORKFLOW_EDIT_URL'] && \Bitrix\Main\Loader::includeModule('bitrix24'))
{
	\CBitrix24::initLicenseInfoPopupJS();
}

if (\Bitrix\Main\Loader::includeModule('rest'))
{
	CJSCore::Init(['marketplace', 'applayout']);
}

$getHint = function ($messageCode) use ($messages)
{
	$text = isset($messages[$messageCode]) ? $messages[$messageCode] : GetMessage($messageCode);
	return htmlspecialcharsbx(nl2br($text));
};
?>
<div class="automation-base" data-role="automation-base-node">
		<div class="automation-base-node-top">
			<div class="automation-base-node-title"
				data-role="automation-title"
				data-title-view="<?=htmlspecialcharsbx($titleView)?>"
				data-title-edit="<?=htmlspecialcharsbx($titleEdit)?>">
			</div>
			<div class="automation-base-button" data-role="automation-base-toolbar">
				<button class="ui-btn ui-btn-light-border<?if (!$arResult['CAN_EDIT']):?> ui-btn-disabled<?endif?>" data-role="automation-btn-change-view"
					data-label-view="<?=GetMessage('BIZPROC_AUTOMATION_CMP_VIEW')?>" data-label-edit="<?=GetMessage('BIZPROC_AUTOMATION_CMP_AUTOMATION_EDIT')?>">
					<?=GetMessage('BIZPROC_AUTOMATION_CMP_AUTOMATION_EDIT')?>
				</button>
			</div>
		</div>
	<div class="automation-base-node">
		<div class="bizproc-automation-status">
			<div class="bizproc-automation-status-list">
				<? foreach ($arResult['STATUSES'] as $statusId => $status):
					$color = htmlspecialcharsbx($status['COLOR'] ? str_replace('#','',$status['COLOR']) : 'acf2fa');
				?>
				<div class="bizproc-automation-status-list-item">
					<div class="bizproc-automation-status-title" data-role="automation-status-title" data-bgcolor="<?=$color?>">
						<?=htmlspecialcharsbx($status['NAME']?:$status['TITLE'])?>
					</div>
					<div class="bizproc-automation-status-bg" style="background-color: <?='#'.$color?>">
						<span class="bizproc-automation-status-title-right" style="background-image: url(data:image/svg+xml;charset=US-ASCII,%3Csvg%20xmlns%3D%22http%3A//www.w3.org/2000/svg%22%20width%3D%2213%22%20height%3D%2232%22%20viewBox%3D%220%200%2013%2032%22%3E%3Cpath%20fill%3D%22%23<?=$color?>%22%20fill-rule%3D%22evenodd%22%20d%3D%22M0%200h3c2.8%200%204%203%204%203l6%2013-6%2013s-1.06%203-4%203H0V0z%22/%3E%3C/svg%3E)"></span>
					</div>
				</div>
				<?endforeach;?>
				<?if ($arResult['STATUSES_EDIT_URL']):?>
				<a href="<?=htmlspecialcharsbx($arResult['STATUSES_EDIT_URL'])?>"
					class="bizproc-automation-status-list-config"
					<?if ($arResult['FRAME_MODE']):?>target="_blank"<?endif;?>
				></a>
				<?endif;?>
			</div>
		</div>
		<?if (!empty($arResult['AVAILABLE_TRIGGERS'])):?>
		<!-- triggers -->
		<div class="bizproc-automation-status">
			<div class="bizproc-automation-status-name">
				<span class="bizproc-automation-status-name-bg"><?=GetMessage('BIZPROC_AUTOMATION_CMP_TRIGGER_LIST')?>
					<span class="bizproc-automation-status-help" data-hint="<?=$getHint('BIZPROC_AUTOMATION_CMP_TRIGGER_HELP_2')?>"></span>
				</span>
				<span class="bizproc-automation-status-line"></span>
			</div>
			<div class="bizproc-automation-status-list">
			<?foreach (array_keys($arResult['STATUSES']) as $statusId):?>
				<div class="bizproc-automation-status-list-item" data-type="column-trigger">
					<div data-role="trigger-list" class="bizproc-automation-trigger-list" data-status-id="<?=htmlspecialcharsbx($statusId)?>"></div>
					<div data-role="trigger-buttons" data-status-id="<?=htmlspecialcharsbx($statusId)?>" class="bizproc-automation-robot-btn-block"></div>
				</div>
			<?endforeach;?>
			</div>
		</div>
		<?endif;?>
		<!-- robots -->
		<div class="bizproc-automation-status">
			<div class="bizproc-automation-status-name">
				<span class="bizproc-automation-status-name-bg"><?=GetMessage('BIZPROC_AUTOMATION_CMP_ROBOT_LIST')?>
					<span class="bizproc-automation-status-help" data-hint="<?=$getHint('BIZPROC_AUTOMATION_CMP_ROBOT_HELP')?>"></span>
				</span>
				<span class="bizproc-automation-status-line"></span>
			</div>
			<div class="bizproc-automation-status-list">
				<? foreach (array_keys($arResult['STATUSES']) as $statusId):?>
					<div class="bizproc-automation-status-list-item" data-type="column-robot" data-role="automation-template" data-status-id="<?=htmlspecialcharsbx($statusId)?>">
						<div data-role="robot-list" class="bizproc-automation-robot-list" data-status-id="<?=htmlspecialcharsbx($statusId)?>"></div>
						<div data-role="buttons" class="bizproc-automation-robot-btn-block"></div>
					</div>
				<?endforeach;?>
			</div>
		</div>
	</div>
	<div class="bizproc-automation-buttons" data-role="automation-buttons">
		<?$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
			'BUTTONS' =>
			[
				'save',
				[
					'type' => 'custom',
					'layout' => '<input type="submit" class="ui-btn ui-btn-link"  data-role="automation-btn-cancel" name="cancel"  value="'.GetMessage('BIZPROC_AUTOMATION_CMP_CANCEL').'">'
				]
			]
		]);?>
	</div>
	<div hidden style="display: none"><?php //init html editor
		$htmlEditor = new CHTMLEditor;
		$htmlEditor->show([]);
	?>
	</div>
</div>
<script>
	BX.ready(function()
	{
		BX.namespace('BX.Bizproc.Automation');
		if (typeof BX.Bizproc.Automation.Component === 'undefined')
			return;

		var baseNode = document.querySelector('[data-role="automation-base-node"]');
		if (baseNode)
		{
			BX.message(<?=\Bitrix\Main\Web\Json::encode($messages)?>);
			BX.message({
				BIZPROC_AUTOMATION_YES: '<?=GetMessageJS('MAIN_YES')?>',
				BIZPROC_AUTOMATION_NO: '<?=GetMessageJS('MAIN_NO')?>'
			});

			var viewMode = BX.Bizproc.Automation.Component.ViewMode.View;
			if (window.location.hash === '#edit')
			{
				viewMode = BX.Bizproc.Automation.Component.ViewMode.Edit;
			}

			(new BX.Bizproc.Automation.Component(baseNode))
				.init(<?=\Bitrix\Main\Web\Json::encode(array(
					'AJAX_URL' => '/bitrix/components/bitrix/bizproc.automation/ajax.php',
					'WORKFLOW_EDIT_URL' => $arResult['WORKFLOW_EDIT_URL'],
					'CAN_EDIT' => $arResult['CAN_EDIT'],

					'DOCUMENT_TYPE' => $arResult['DOCUMENT_TYPE'],
					'DOCUMENT_CATEGORY_ID' => $arResult['DOCUMENT_CATEGORY_ID'],
					'DOCUMENT_ID' => $arResult['DOCUMENT_ID'],
					'DOCUMENT_SIGNED' => $arResult['DOCUMENT_SIGNED'],
					'DOCUMENT_STATUS' => $arResult['DOCUMENT_STATUS'],
					'DOCUMENT_STATUS_LIST' => array_values($arResult['STATUSES']),
					'DOCUMENT_FIELDS' => $arResult['DOCUMENT_FIELDS'],

					'ENTITY_NAME' => $arResult['ENTITY_NAME'],

					'TRIGGERS' => $arResult['TRIGGERS'],
					'TEMPLATES' => $arResult['TEMPLATES'],
					'AVAILABLE_ROBOTS' => $arResult['AVAILABLE_ROBOTS'],
					'AVAILABLE_TRIGGERS' => $arResult['AVAILABLE_TRIGGERS'],
					'LOG' => $arResult['LOG'],

					'B24_TARIF_ZONE' => $arResult['B24_TARIF_ZONE'],
					'USER_OPTIONS' => $arResult['USER_OPTIONS'],
					'FRAME_MODE' => $arResult['FRAME_MODE'],

					'MARKETPLACE_ROBOT_CATEGORY' => $arParams['MARKETPLACE_ROBOT_CATEGORY'],
					'MARKETPLACE_TRIGGER_PLACEMENT' => $arParams['MARKETPLACE_TRIGGER_PLACEMENT']
				))?>, viewMode);
		}
	});
</script>