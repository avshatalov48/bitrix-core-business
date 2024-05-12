<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.viewer',
	'ui.buttons.icons'
]);

\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/bizproc/tools.js');
$cmpId = RandString();
$jsCallback = <<<SCRIPT
	function()
	{
		var wrapperNode = BX('{$cmpId}_wf_livefeed').parentNode;
		BX.addClass(wrapperNode, 'bp-livefeed-wrapper-loading');
		BX.ajax({
			'method': 'POST',
			'dataType': 'html',
			'url': '/bitrix/components/bitrix/bizproc.workflow.livefeed/ajax.php',
			'data':  {WORKFLOW_ID: '{$arParams['WORKFLOW_ID']}'},
			'onsuccess': function(html){
				wrapperNode.innerHTML = html;
				BX.removeClass(wrapperNode, 'bp-livefeed-wrapper-loading');
			}
		});
	}
SCRIPT;
?>
<?if (!$arResult['noWrap']):?>
<div class="bp-livefeed-wrapper">
<?endif;?>
<div class="bp-post bp-lent bp-post-livefeed" id="<?=$cmpId?>_wf_livefeed">
	<div id="<?=$cmpId?>_steps" class="bp-short-process-inner bp-opacity-animation bp-hidden">
		<?$APPLICATION->IncludeComponent(
			"bitrix:bizproc.workflow.faces",
			"",
			array(
				"WORKFLOW_ID" => $arParams["~WORKFLOW_ID"],
				'WORKFLOW_STATE_INFO' => $arResult["WORKFLOW_STATE_INFO"]
			),
			$component
		);
		?>
		<span id="<?=$cmpId?>_user_status_yes" class="bp-status-ready bp-opacity-animation bp-hidden" style="display: none">
			<span><?=GetMessage('BPATL_USER_STATUS_YES')?></span>
		</span>
		<span id="<?=$cmpId?>_user_status_no" class="bp-status-cancel bp-opacity-animation bp-hidden" style="display: none">
			<span><?=GetMessage('BPATL_USER_STATUS_NO')?></span>
		</span>
		<span id="<?=$cmpId?>_user_status_ok" class="bp-status-ready bp-opacity-animation bp-hidden" style="display: none">
			<span><?=GetMessage('BPATL_USER_STATUS_OK')?></span>
		</span>
		<span id="<?=$cmpId?>_wf_status" class="bp-status bp-opacity-animation bp-hidden" style="display: none">
			<span class="bp-status-inner"><span><?=htmlspecialcharsbx($arResult["WORKFLOW_STATE_INFO"]['STATE_TITLE'])?></span></span>
		</span>
		<?foreach ($arResult['TASKS']['RUNNING'] as $task):?>
		<div id="<?=$cmpId?>_task_buttons_<?=$task['ID']?>" class="bp-btn-panel bp-opacity-animation bp-hidden" style="display: none">
			<span class="bp-btn-panel-inner">
			<? if ($task['IS_INLINE'] == 'Y'):
				foreach ($task['BUTTONS'] as $control):
					$isDecline =
						$control['TARGET_USER_STATUS'] == CBPTaskUserStatus::No
						|| $control['TARGET_USER_STATUS'] == CBPTaskUserStatus::Cancel
					;
					$class = $isDecline ? 'danger' : 'success';
					$icon = $isDecline ? 'cancel' : 'done';
					$props = CUtil::PhpToJSObject([
						'TASK_ID' => $task['ID'],
						$control['NAME'] => $control['VALUE'],
					]);
					?>
					<a href="#" onclick="return BX.Bizproc.doInlineTask(<?= $props ?>, <?= $jsCallback ?>, this)"
						class="ui-btn ui-btn-<?= $class ?> ui-btn-icon-<?= $icon ?>"
						><?= $control['TEXT'] ?>
					</a>
				<?php
				endforeach;
			else:?>
				<a
					href="#"
					class="ui-btn ui-btn-primary"
					onclick="return BX.Bizproc.showTaskPopup(<?= $task['ID'] ?>, <?= $jsCallback ?>, null, this, true)"
				><?= GetMessage("BPATL_BEGIN") ?></a>
			<?php
			endif;
			?>
			</span>
		</div>
		<?endforeach;?>
	</div>
	<?foreach ($arResult['TASKS']['RUNNING'] as $task):?>
		<div id="<?=$cmpId?>_task_block_<?=$task['ID']?>" class="bp-task-block bp-opacity-animation bp-hidden" style="display: none">
			<span class="bp-task-block-title"><?=GetMessage("BPATL_TASK_TITLE_MSGVER_1")?>: </span>
			<?=$task['NAME']?>
			<? if ($task['DESCRIPTION']):?>
			<p>
				<?=\CBPViewHelper::prepareTaskDescription($task['DESCRIPTION'])?>
			</p>
			<?endif?>
			<p><a href="javascript:void(0);" onclick="return BX.Bizproc.showTaskPopup(<?=$task['ID']?>, <?=$jsCallback?>, null, this, true)"><?=GetMessage("BPATL_TASK_LINK_TITLE")?></a></p>
		</div>
	<?endforeach;?>
	<?
	$jsTasks = array('RUNNING' => array(), 'COMPLETED' => array());
	foreach ($arResult['TASKS']['RUNNING'] as $task)
	{
		$jsTask = array(
			'ID' => $task['ID'],
			'USERS' => array()
		);
		foreach ($task['USERS'] as $u)
		{
			$jsTask['USERS'][] = array(
				'USER_ID' => $u['USER_ID'],
				'STATUS' => $u['STATUS']
			);
		}
		$jsTasks['RUNNING'][] = $jsTask;
	}
	if (isset($arResult['TASKS']['COMPLETED'][0]))
	{
		$jsTask = array(
			'ID' => $arResult['TASKS']['COMPLETED'][0]['ID'],
			'USERS' => array()
		);
		foreach ($arResult['TASKS']['COMPLETED'][0]['USERS'] as $u)
		{
			$jsTask['USERS'][] = array(
				'USER_ID' => $u['USER_ID'],
				'STATUS' => $u['STATUS']
			);
		}
		$jsTasks['COMPLETED'][] = $jsTask;
	}
	?>
	<script>
		BX.ready(function() {
			var cmpId = '<?=$cmpId?>',
				tasks = <?=CUtil::PhpToJSObject($jsTasks)?>,
				userId = '<?=$arResult['USER_ID']?>',
				statusWaiting = '<?=CBPTaskUserStatus::Waiting?>',
				statusYes = '<?=CBPTaskUserStatus::Yes?>',
				statusNo = '<?=CBPTaskUserStatus::No?>',
				statusOk = '<?=CBPTaskUserStatus::Ok?>',
				statusCancel = '<?=CBPTaskUserStatus::Cancel?>',
				userStatus = false,
				wfCompleted = <?= empty($arResult['WORKFLOW_STATE_INFO']['STATUS']) ? 'true' : 'false' ?>,
				taskId = false;

			if (BX(cmpId+'_steps'))
				BX.removeClass(BX(cmpId+'_steps'), 'bp-hidden');

			if (BX.message('USER_ID'))
				userId = BX.message('USER_ID');

			var getUserFromTask = function (task, userId)
			{
				for (var i = 0, l = task.USERS.length; i < l; ++i)
				{
					if (task.USERS[i]['USER_ID'] == userId)
						return task.USERS[i];
				}
				return null;
			};

			if (tasks['RUNNING'].length)
			{
				for (var i = 0, l = tasks.RUNNING.length; i < l; ++i)
				{
					var task = tasks.RUNNING[i];
					var user = getUserFromTask(task, userId);
					if (user)
					{
						if (user.STATUS > statusWaiting)
							userStatus = user.STATUS;
						else
						{
							userStatus = false;
							taskId = task.ID;
							BX(cmpId+'_task_buttons_'+task.ID).style.display = '';
							BX(cmpId+'_task_block_'+task.ID).style.display = '';

							setTimeout(function(){
								BX.removeClass(BX(cmpId+'_task_buttons_'+task.ID), 'bp-hidden');
								BX.removeClass(BX(cmpId+'_task_block_'+task.ID), 'bp-hidden');
							}, 10);

							break;
						}
					}
				}
			}
			else if (tasks['COMPLETED'].length && !wfCompleted)
			{
				var user = getUserFromTask(tasks['COMPLETED'][0], userId);
				if (user && user.STATUS > statusWaiting)
				{
					userStatus = user.STATUS;
				}
			}
			if (userStatus !== false)
			{
				switch (userStatus)
				{
					case statusYes:
						BX(cmpId+'_user_status_yes').style.display = '';
						setTimeout(function(){BX.removeClass(BX(cmpId+'_user_status_yes'), 'bp-hidden');}, 10);
						break;
					case statusNo:
					case statusCancel:
						BX(cmpId+'_user_status_no').style.display = '';
						setTimeout(function(){BX.removeClass(BX(cmpId+'_user_status_no'), 'bp-hidden');}, 10);
						break;
					default:
						BX(cmpId+'_user_status_ok').style.display = '';
						setTimeout(function(){BX.removeClass(BX(cmpId+'_user_status_ok'), 'bp-hidden');}, 10);
						break;
				}
			}
			if (!(userStatus || taskId))
			{
				BX(cmpId+'_wf_status').style.display = '';
				setTimeout(function(){BX.removeClass(BX(cmpId+'_wf_status'), 'bp-hidden');}, 10);
			}
		});
	</script>
</div>
<?if (!$arResult['noWrap']):?>
	</div>
<?endif;?>