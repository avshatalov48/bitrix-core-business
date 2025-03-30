<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var CAllMain $APPLICATION */
/** @var array $arResult */

/** @var \Bitrix\Bizproc\UI\WorkflowUserView $workflowView */
$workflowView = $arResult['workflow'] ?? null;
$workflowId = $workflowView?->getId();

$task = $arResult['task'] ?? null;
$isMyTask = $arResult['isMyTask'] ?? null;

$taskButtons = $isMyTask ? ($task['controls']['buttons'] ?? null) : null;
$taskFields = $isMyTask ? ($task['controls']['fields'] ?? null) : null;

$canDelegate = ($task
	&& $task['isRunning']
	&& (int)$task['delegationType'] !== CBPTaskDelegationType::ExactlyNone
	&& ($arResult['isAdmin'] || (int)$task['delegationType'] !== CBPTaskDelegationType::None)
);

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Loader;

\Bitrix\Main\UI\Extension::load([
	'ui.buttons',
	'ui.forms',
	'ui.layout-form',
	'ui.tabs',
	'main.core',
	'ui.alerts',
	'ui.dialogs.messagebox',
	'bizproc.workflow.timeline',
	'ui.hint',
]);
?>

<div class="bp-workflow-info__wrapper">
	<div class="bp-workflow-info__main">
		<div class="bp-workflow-info__title">
			<span class="bp-workflow-info__title-inner"><?= htmlspecialcharsbx($workflowView->getTypeName()) ?></span>
<!--			<span class="bp-workflow-info__copy-btn"></span>-->
		</div>
		<div class="bp-workflow-info__content">
			<div class="bp-workflow-info__tabs">
				<div class="ui-tabs__tabs-header-container">
					<span class="ui-tabs__tab-header-container --header-active">
						<span><?= Loc::getMessage('BPWFI_SLIDER_TASK') ?></span>
					</span>
					<span class="ui-tabs__tab-header-container">
						<span onclick="BX.Bizproc.Workflow.Timeline.open({ workflowId: '<?= CUtil::JSEscape($workflowId) ?>' });"><?= Loc::getMessage('BPWFI_SLIDER_TIMELINE_MSGVER_1') ?></span>
					</span>
					<span class="ui-tabs__tab-header-container">
						<a href="<?= htmlspecialcharsbx($arResult['documentUrl']) ?>" target="_blank" style="color: var(--ui-color-base-90);"><?= Loc::getMessage('BPWFI_SLIDER_DOCUMENT') ?></a>
					</span>
				</div>
				<?php if (!$isMyTask): ?>
					<div class="bp-workflow-info__warning">
						<div class="ui-alert ui-alert-icon-danger ui-alert-primary">
							<span class="ui-alert-message"><?= htmlspecialcharsbx(
								Loc::getMessage('BPWFI_SLIDER_NOT_MY_TASK', ['#USER#' => $arResult['userName']])
							) ?></span>
						</div>
					</div>
				<?php endif; ?>
				<div class="ui-tabs__tabs-body-container">
					<div class="bp-workflow-info__tabs-inner">
						<div class="bp-workflow-info__tabs-block">
							<div class="bp-workflow-info__label"><?= Loc::getMessage('BPWFI_SLIDER_NAME') ?></div>
							<div class="bp-workflow-info__subject"><?= htmlspecialcharsbx($task['name'] ?? $workflowView->getName()) ?></div>
						</div>
						<div class="bp-workflow-info__tabs-block">
							<div class="bp-workflow-info__label"><?= Loc::getMessage('BPWFI_SLIDER_TYPE') ?></div>
							<div class="bp-workflow-info__text"><?= htmlspecialcharsbx($workflowView->getTypeName()) ?></div>
						</div>
						<?php
						if (isset($task['description']))
						{
							$description = \CBPViewHelper::prepareTaskDescription(
								\CBPHelper::convertBBtoText(
									preg_replace('|\n+|', "\n", trim($task['description']))
								)
							);
						}
						else
						{
							$description = $workflowView->getDescription();
						}

						?>
						<div class="bp-workflow-info__tabs-block<?= !$description ? ' block-hidden' : ''?>">
							<div class="bp-workflow-info__label"><?= Loc::getMessage('BPWFI_SLIDER_DESCRIPTION') ?></div>
							<div class="bp-workflow-info__desc">
								<div class="bp-workflow-info__desc-inner">
									<?= $description ?>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
			<?php
				$documentService = CBPRuntime::getRuntime()->getDocumentService();
				$documentType = $arResult['documentType'];
			?>
			<div class="bp-workflow-info__editor<?= !$taskFields ? ' block-hidden' : ''?>">
				<div class="bp-workflow-info__editor-title"><?= Loc::getMessage('BPWFI_SLIDER_FIELDS_TITLE') ?></div>
				<form class="ui-form" name="task-form" enctype="multipart/form-data">
					<?php
					if ($taskFields):
					foreach ($taskFields as $field):
						$cid = str_replace('[]', '', $field['Id']);
						?>
						<div class="ui-form-row" data-cid="<?= htmlspecialcharsbx($cid) ?>">
							<div class="ui-form-label <?= $field['Required'] ? '--required' : '' ?>">
								<div class="ui-ctl-label-text"><?= htmlspecialcharsbx($field['Name']) ?></div>
							</div>
							<div class="ui-form-content">
								<?= $documentService->getFieldInputControl(
									$documentType,
									$field,
									$field['FieldId'] ?? $field['Id'],
									$field['Default'] ?? null,
									false,
									true
								); ?>
<!--								<div class="ui-form-notice">error</div>-->
							</div>
						</div>
					<?php endforeach; ?>
					<?php endif; ?>
				</form>
			</div>
		</div>
	</div>
	<?php

	$all = 0;
	if (Loader::includeModule('forum'))
	{
		$topic = \CForumTopic::getList([], ['XML_ID' => 'WF_' . $workflowId])->fetch() ?: [];
		$all = (int)($topic['POSTS'] ?? 0);
	}
	?>
	<div class="bp-workflow-info__comments <?= $all === 0 ? '--empty' : '' ?>">
		<div class="bp-workflow-info__subtitle_outer">
			<div class="bp-workflow-info__subtitle">
				<div class="bp-workflow-info__subtitle-inner"><?= Loc::getMessage('BPWFI_SLIDER_DISCUSSION_TITLE') ?></div>
				<span class="bp-workflow-info__count"><?= $all
						? Loc::getMessage('BPWFI_SLIDER_DISCUSSION_COMMENTS_COUNT', ['#COMMENTS_COUNT#' => $all])
						: Loc::getMessage('BPWFI_SLIDER_DISCUSSION_ZERO_COMMENTS_COUNT')
					?></span>
			</div>
		</div>
		<?php if ($all === 0): ?>
		<div class="bp-workflow-info__comment-block">
			<div class="bp-workflow-info__banner">
				<div class="bp-workflow-info__banner-logo"></div>
				<div class="bp-workflow-info__banner-title"><?= Loc::getMessage('BPWFI_SLIDER_BANNER_TITLE') ?></div>
					<div class="bp-workflow-info__banner-text"><?= Loc::getMessage('BPWFI_SLIDER_BANNER_BODY') ?></div>
			</div>
		</div>
		<?php endif; ?>
		<div class="bp-workflow-info__comment-block_forum" onclick="BX.Dom.removeClass(document.querySelector('.bp-workflow-info__comments'), '--empty')">
			<?php $APPLICATION->IncludeComponent("bitrix:forum.comments", "bitrix24", [
					"FORUM_ID" => CBPHelper::getForumId(),
					"ENTITY_TYPE" => "WF",
					"ENTITY_ID" => CBPStateService::getWorkflowIntegerId($workflowId),
					"ENTITY_XML_ID" => "WF_" . $workflowId,
					"PERMISSION" => "M",
					"URL_TEMPLATES_PROFILE_VIEW" => "/company/personal/user/#user_id#/",
					"SHOW_RATING" => "Y",
					"SHOW_LINK_TO_MESSAGE" => "N",
					"BIND_VIEWER" => "Y",
					'LHE' => [
						'copilotParams' => [],
						'isCopilotEnabled' => false,
					],
				],
				false,
				['HIDE_ICONS' => 'Y']
			); ?>
		</div>
		<?php if ($taskButtons || $canDelegate):

			$APPLICATION->IncludeComponent('bitrix:ui.button.panel', '', [
				'BUTTONS' => [
					[
						'TYPE' => 'custom',
						'LAYOUT' => '<div data-role="buttons-panel"></div>'
					]
				],
			]);
		endif; ?>
	</div>
</div>

<script>
	BX.ready(() => {

		BX.message({
			BPWFI_SLIDER_BUTTON_DELEGATE: '<?= CUtil::JSEscape(Loc::getMessage('BPWFI_SLIDER_BUTTON_DELEGATE')) ?>',
			BPWFI_SLIDER_CONFIRM_TITLE: '<?= CUtil::JSEscape(Loc::getMessage('BPWFI_SLIDER_CONFIRM_TITLE')) ?>',
			BPWFI_SLIDER_CONFIRM_DESCRIPTION: '<?= CUtil::JSEscape(Loc::getMessage('BPWFI_SLIDER_CONFIRM_DESCRIPTION')) ?>',
			BPWFI_SLIDER_CONFIRM_ACCEPT: '<?= CUtil::JSEscape(Loc::getMessage('BPWFI_SLIDER_CONFIRM_ACCEPT')) ?>',
			BPWFI_SLIDER_CONFIRM_CANCEL: '<?= CUtil::JSEscape(Loc::getMessage('BPWFI_SLIDER_CONFIRM_CANCEL')) ?>',
		});
		BX.Bizproc.Component.WorkflowInfo.Instance = new BX.Bizproc.Component.WorkflowInfo({
			currentUserId: BX.message('USER_ID'),
			workflowId: '<?= CUtil::JSEscape($workflowId) ?>',
			taskId: <?= (int)($task['id'] ?? 0) ?>,
			taskUserId: <?= (int)($task['userId'] ?? 0) ?>,
			taskButtons: <?= \Bitrix\Main\Web\Json::encode($taskButtons) ?>,
			taskForm: document.forms['task-form'],
			buttonsPanel: document.querySelector('[data-role="buttons-panel"]'),
			workflowContent: document.querySelector('.bp-workflow-info__content'),
			canDelegateTask: <?= $canDelegate ? 'true' : 'false' ?>,
		});
		BX.Bizproc.Component.WorkflowInfo.Instance.init();
	})
</script>
