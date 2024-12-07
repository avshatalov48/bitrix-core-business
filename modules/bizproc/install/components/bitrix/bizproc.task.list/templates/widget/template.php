<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)	die();
/*
Usage example
<?
if(CModule::IncludeModule('bizproc')):
	$APPLICATION->IncludeComponent(
		"bitrix:bizproc.task.list",
		"widget",
		array(
			"COUNTERS_ONLY" => 'Y',
			"USER_ID" => $USER->GetID(),
			"PATH_TO_BP_TASKS" => "/company/personal/bizproc/",
			"PATH_TO_MY_PROCESSES" => "/company/personal/processes/",
		),
		null,
		array("HIDE_ICONS" => "N")
	);
endif;?>
*/
$this->setFrameMode(true);
$this->SetViewTarget("sidebar", 199); // before tasks widget
$frame = $this->createFrame()->begin();

$tasksUrl = '/bizproc/userprocesses/'; /* @override $arParams["PATH_TO_BP_TASKS"] */

if (!empty($arResult['COUNTERS_RUNNING']['lists']['BizprocDocument']) || !empty($arResult['COUNTERS']['*']))
{
	$whiteList = array(
		'lists'  => array('LABEL' => GetMessage('BPTLWGT_MODULE_LISTS'), 'URL' => $tasksUrl.'?SYSTEM_PRESET=active_task&MODULE_ID=lists&apply_filter=Y'),
		'crm'    => array('LABEL' => 'CRM', 'URL' => $tasksUrl.'?SYSTEM_PRESET=active_task&MODULE_ID=crm&apply_filter=Y'),
		'disk'   => array('LABEL' => GetMessage('BPTLWGT_MODULE_DISK'), 'URL' => $tasksUrl.'?SYSTEM_PRESET=active_task&MODULE_ID=disk&apply_filter=Y'),
		'iblock' => array('LABEL' => GetMessage('BPTLWGT_MODULE_IBLOCK'), 'URL' => $tasksUrl.'??SYSTEM_PRESET=active_task&MODULE_ID=lists&apply_filter=Y'),
	)
	?>
	<div class="sidebar-widget sidebar-widget-bp">
		<div class="sidebar-widget-top">
			<div class="sidebar-widget-top-title"><?= GetMessage('BPTLWGT_TITLE') ?></div>
			<!--<div class="plus-icon"></div>-->
		</div>
		<div class="sidebar-widget-item-wrap">
		<span class="task-item task-item-list">
			<a href="<?= htmlspecialcharsbx($tasksUrl) ?>" class="task-item">
				<span class="task-item-text"><?= GetMessage('BPTLWGT_RUNNING') ?></span>
				<span class="task-item-index-wrap">
					<span class="task-item-index"><?= $arResult['COUNTERS']['*'] < 100 ? $arResult['COUNTERS']['*'] : '99+' ?></span>
				</span>
			</a>
			<? foreach ($arResult['COUNTERS'] as $module => $data):
				if (!isset($whiteList[$module]) || empty($data['*']))
					continue;
				?>
				<a href="<?= htmlspecialcharsbx($whiteList[$module]['URL']) ?>" class="task-item">
					<span class="task-item-text"><?= htmlspecialcharsbx($whiteList[$module]['LABEL']) ?></span>
				<span class="task-item-index-wrap">
					<span class="task-item-index"><?= $arResult['COUNTERS'][$module]['*'] < 100 ? $arResult['COUNTERS'][$module]['*'] : '99+' ?></span>
				</span>
				</a>
			<? endforeach;?>
		</span>
			<? if (!empty($arResult['COUNTERS_RUNNING']['lists']['BizprocDocument'])): ?>
				<a class="task-item" href="<?= htmlspecialcharsbx($tasksUrl) ?>">
					<span class="task-item-text"><?= GetMessage('BPTLWGT_MY_PROCESSES') ?></span>
			<span class="task-item-index-wrap">
				<span class="task-item-index"><?= $arResult['COUNTERS_RUNNING']['lists']['BizprocDocument'] < 100 ? $arResult['COUNTERS_RUNNING']['lists']['BizprocDocument'] : '99+' ?></span>
			</span>
				</a>
			<? endif?>
		</div>
	</div>
	<?
}

$frame->end();
$this->EndViewTarget();