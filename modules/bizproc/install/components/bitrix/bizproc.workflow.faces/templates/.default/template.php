<?php

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

\Bitrix\Main\UI\Extension::load(["ui.tooltip", "ui.icons.b24", "ui.design-tokens", "ui.fonts.opensans"]);

use Bitrix\Main\Web\Uri;

$cmpId = RandString();
if (empty($arResult['WORKFLOW_ID'])):?>
<p style="color:red"><?=GetMessage('BPWLFC_WORKFLOW_NOT_FOUND')?></p>
<?else:?>
<div class="bp-short-process <?if (empty($arResult['TASKS']['COMPLETED']) && !$arResult['STATE_TITLE']) echo 'alone';?>">
	<div class="bp-short-process-steps">
		<?if ($arResult['STATE_TITLE']):?>
		<span class="bp-short-process-finished <?if ($arResult['LAST_USER_STATUS'] == CBPTaskUserStatus::Yes || $arResult['LAST_USER_STATUS'] == CBPTaskUserStatus::Ok):?>process-finished-ready<?endif?>">
			<span>
				<span title="<?=htmlspecialcharsbx($arResult['STATE_TITLE'])?>"><?=htmlspecialcharsbx($arResult['STATE_TITLE'])?></span>
			</span>
		</span>
		<?endif?>
		<a href="javascript:void(0)" class="bp-short-process-step bp-short-process-step-firs"><?
			if (!empty($arResult['STARTED_BY']) && is_array($arResult['STARTED_BY']))
			{
				if ($startedPhoto = CBPViewHelper::getUserPhotoSrc($arResult['STARTED_BY']))
				{
					?><div class="bp-short-process-step-inner ui-icon ui-icon-common-user">
						<i bx-tooltip-user-id="<?=(int)$arResult['STARTED_BY']['ID']?>" bx-tooltip-classname="intrantet-user-selector-tooltip" style="background-image: url('<?= htmlspecialcharsbx(Uri::urnEncode($startedPhoto)) ?>')"></i>
					</div><?
				}
				else
				{
					?><div class="bp-short-process-step-inner ui-icon ui-icon-common-user">
						<i bx-tooltip-user-id="<?=(int)$arResult['STARTED_BY']['ID']?>" bx-tooltip-classname="intrantet-user-selector-tooltip"></i>
					</div><?
				}
			}
			elseif (!empty($arResult['DOCUMENT_ID']) && in_array($arResult['DOCUMENT_ID'][0], array('crm', 'disk', 'lists', 'tasks')))
			{
				?><span class="bp-short-process-step-inner">
					<img src="<?=htmlspecialcharsbx($templateFolder)?>/images/bp-<?=$arResult['DOCUMENT_ID'][0]?>-icon.png"  border="0"/>
				</span><?
			}
			else
			{
				?><span class="bp-short-process-step-inner">
					<img src="<?=htmlspecialcharsbx($templateFolder)?>/images/bp-other-icon.png" border="0" />
				</span><?
			}
		?></a>
		<?if (!empty($arResult['TASKS']['COMPLETED'][0])):
				$task = $arResult['TASKS']['COMPLETED'][0];
				$face = $task['USERS'][0]?>
			<span class="bp-short-prosess-steps-arrow bp-short-prosess-steps-arrow-ready">
				<?if ( $arResult['TASKS']['COMPLETED_CNT'] >= 2):?>
						<a id="<?=$cmpId?>_bp_more_cts" href="#" class="process-step-more"><?=GetMessage('BPWLFC_MORE_1', ['#N#' => $arResult['TASKS']['COMPLETED_CNT'] - 1])?></a>
				<?endif?>
			</span>

			<div class="bp-short-process-step-wrapper">
				<a href="javascript:void(0)" class="bp-short-process-step <?if ($face['STATUS'] == CBPTaskUserStatus::Ok || $face['STATUS'] == CBPTaskUserStatus::Yes) echo 'bp-short-process-step-ready'?><?if ($face['STATUS'] == CBPTaskUserStatus::No || $face['STATUS'] == CBPTaskUserStatus::Cancel) echo 'bp-short-process-step-cancel'?> <?if ($task['USERS_CNT'] > 1) echo 'bp-short-process-step-more'?>" title="<?=$task['NAME']?>"><?
					if ($face['PHOTO_SRC'])
					{
						?>
						<div class="bp-short-process-step-inner ui-icon ui-icon-common-user">
							<i style="background-image: url('<?= htmlspecialcharsbx(Uri::urnEncode($face['PHOTO_SRC'])) ?>')" border="0" bx-tooltip-user-id="<?=(int)$face['USER_ID']?>" bx-tooltip-classname="intrantet-user-selector-tooltip"></i>
						</div>
						<?
					}
					else
					{
						?><div class="bp-short-process-step-inner ui-icon ui-icon-common-user" bx-tooltip-user-id="<?=(int)$face['USER_ID']?>" bx-tooltip-classname="intrantet-user-selector-tooltip">
							<i bx-tooltip-user-id="<?=(int)$face['USER_ID']?>" bx-tooltip-classname="intrantet-user-selector-tooltip"></i>
						</div><?
					}
				?></a>
				<?if ($task['USERS_CNT'] > 1):?>
				<a id="<?=$cmpId?>_bp_more_cfs" href="#" class="process-step-more process-step-more-complete">
					<span class=""><?=GetMessage('BPWLFC_TOTAL_1', ['#N#' => $task['USERS_CNT']])?></span>
				</a>
				<?endif?>
			</div>
			<script>
				BX.ready(function ()
				{
					<?if ($arResult['TASKS']['COMPLETED_CNT'] >= 2):
						$collapsed = $arResult['TASKS']['COMPLETED'];
						array_shift($collapsed);
						$collapsed = CUtil::PhpToJSObject(BizprocWorkflowFaces::prepareTasksForJs($collapsed));
					?>
					BX.bind(BX('<?=$cmpId?>_bp_more_cts'), 'click', function(e){BX.Bizproc.WorkflowFaces.showFaces(<?=$collapsed?>, this, null, true); BX.PreventDefault(e); });
					<?endif?>
					<?if ($task['USERS_CNT'] > 1):?>
					BX.bind(BX('<?=$cmpId?>_bp_more_cfs'), 'click', function(e){BX.Bizproc.WorkflowFaces.showFaces(<?=
						CUtil::PhpToJSObject(BizprocWorkflowFaces::prepareTasksForJs(array($task)))
					?>, this); BX.PreventDefault(e);});
					<?endif?>
				});
			</script>
		<?endif?>
	<?if (!$arResult['STATE_TITLE']):?>
	</div>
	<?endif?>
	<?if (!empty($arResult['TASKS']['RUNNING'][0])):
			$task = $arResult['TASKS']['RUNNING'][0];
			$face = $task['USERS'][0];
			$allFaces = sizeof($arResult['TASKS']['RUNNING_ALL_USERS']);
			$photoSrc = $face['PHOTO_SRC'];
		?>
		<span class="bp-short-prosess-steps-arrow bp-short-prosess-steps-arrow-running <?if ($arResult['TASKS']['RUNNING_CNT'] > 1) echo 'steps-arrow-right-right'?>"></span>
		<span class="bp-short-process-step-wrapper"><?

			?><a href="javascript:void(0)" class="bp-short-process-step <?if ($face['STATUS'] == CBPTaskUserStatus::Ok || $face['STATUS'] == CBPTaskUserStatus::Yes) echo 'bp-short-process-step-ready'?>
			<?if ($face['STATUS'] == CBPTaskUserStatus::No || $face['STATUS'] == CBPTaskUserStatus::Cancel) echo 'bp-short-process-step-cancel'?> <?if ($task['USERS_CNT'] > 1) echo 'bp-short-process-step-more'?>">
				<div class="bp-short-process-step-inner ui-icon ui-icon-common-user">
					<i id="<?=$cmpId?>_face_3_photo_src" <?if ($photoSrc):?>style="background-image: url('<?= htmlspecialcharsbx(Uri::urnEncode($photoSrc)) ?>')" border="0"<?endif;?> bx-tooltip-user-id="<?=(int)$face['USER_ID']?>" bx-tooltip-classname="intrantet-user-selector-tooltip"></i>
				</div>
			</a>
			<? if ($allFaces >= 2):?>
			<a id="<?=$cmpId?>_bp_more_rf" href="#" class="process-step-more process-step-more-running"><span><?=GetMessage('BPWLFC_TOTAL_1', ['#N#' => $allFaces])?></span></a>
			<?endif?>
		</span>
		<script>
			BX.ready(function ()
			{
				<?if ($allFaces > 1):?>
				BX.bind(BX('<?=$cmpId?>_bp_more_rf'), 'click', function(e){BX.Bizproc.WorkflowFaces.showFaces(<?=
						CUtil::PhpToJSObject(BizprocWorkflowFaces::prepareTasksForJs($arResult['TASKS']['RUNNING']))
					?>, this, true); BX.PreventDefault(e);});
				<?endif?>
				var userId = BX.message('USER_ID'),
					allUsers = <?=CUtil::PhpToJSObject($arResult['TASKS']['RUNNING_ALL_USERS'])?>,
					displayedUser = allUsers[0];

				if (userId && allUsers.length > 1)
				{
					for (var i = 0, l = allUsers.length; i < l; ++i)
					{
						var user = allUsers[i];
						if (user['USER_ID'] == userId)
						{
							displayedUser = user;
							break;
						}
					}
				}

				if (!displayedUser)
				{
					return false;
				}

				if (displayedUser['PHOTO_SRC'])
				{
					var face3Element = BX('<?=$cmpId?>_face_3_photo_src');
					if (face3Element)
					{
						face3Element.src = displayedUser['PHOTO_SRC'];
						face3Element.setAttribute('bx-tooltip-user-id', displayedUser['USER_ID']);
					}
				}
			});
		</script>
	<?endif?>
	<?if ($arResult['STATE_TITLE']):?>
	</div>
	<?endif?>
</div>
<?endif?>