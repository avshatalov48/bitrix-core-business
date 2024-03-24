<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
\Bitrix\Main\UI\Extension::load("ui.tooltip");

if ($arResult["NeedAuth"] == "Y")
{
	$APPLICATION->AuthForm("");
}
elseif ($arResult["FatalErrorMessage"] <> '')
{
	?>
	<p><span class='errortext' style="color: red;"><?= $arResult["FatalErrorMessage"] ?></span><br/></p>
	<?
}
else
{
	\Bitrix\Main\Page\Asset::getInstance()->addJs('/bitrix/js/bizproc/tools.js');
?>

	<?if ($arParams['POPUP']):?>
	<div class="bp-popup-title"><?=GetMessage('BPWFI_PAGE_TITLE')?></div>
	<div class="bp-popup">
	<?endif?>
	<div class="bp-task-page bp-lent <?if (empty($arResult['startedByPhotoSrc'])):?>no-photo<?endif?>">
		<?if (!empty($arResult['startedByPhotoSrc'])):?>
			<span class="bp-avatar" bx-tooltip-user-id="<?=(int)$arResult['WorkflowState']['STARTED_BY']?>" bx-tooltip-classname="intrantet-user-selector-tooltip">
				<img src="<?=$arResult['startedByPhotoSrc']?>" alt="">
			</span>
		<?endif?>
		<span class="bp-title"><?=htmlspecialcharsbx($arResult['WorkflowState']['TEMPLATE_NAME'])?></span>
	<span class="bp-title-desc">
		<span class="bp-title-desc-icon">
			<?if (empty($arResult['DOCUMENT_ICON'])):?>
				<img src="<?=htmlspecialcharsbx($templateFolder)?>/images/icon-bp-process.png" width="36" height="30" border="0" />
			<?else:?>
				<img src="<?=htmlspecialcharsbx($arResult['DOCUMENT_ICON'])?>" width="36" height="30" border="0" />
			<?endif?>
		</span>
		<span class=""><?=htmlspecialcharsbx($arResult['DOCUMENT_NAME'])?></span>
	</span>
		<div class="bp-short-process-inner">
			<?$APPLICATION->IncludeComponent(
				'bitrix:bizproc.workflow.faces',
				'',
				array(
					'WORKFLOW_ID' => $arResult['WorkflowState']['ID']
				),
				$component
			);
			?>
			<span class="bp-status">
				<span class="bp-status-inner"><span><?=htmlspecialcharsbx($arResult["WorkflowState"]['STATE_TITLE'])?></span></span>
			</span>
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
			}()"><?=GetMessage("BPWFITPL_COMMENTS")?></span>
			<span id="bp-task-tab-2" class="bp-tab" onclick="return function(){
			var t1 = BX('bp-task-tab-2'),
				t2 = BX('bp-task-tab-1'),
				t1c = BX('bp-task-tab-2-content'),
				t2c = BX('bp-task-tab-1-content');

				BX.addClass(t1, 'bp-tab-active'); BX.removeClass(t2, 'bp-tab-active');
				BX.addClass(t1c, 'active'); BX.removeClass(t2c, 'active');
				return false;
			}()"><?=GetMessage("BPWFITPL_DOC_HISTORY")?></span>
			</div>

			<div id="bp-task-tabs-content" class="bp-tab-contents">
				<div id="bp-task-tab-1-content" class="bp-tab-content active">

					<?endif?>
					<?
					// A < E < I < M < Q < U < Y
					// A - NO ACCESS, E - READ, I - ANSWER
					// M - NEW TOPIC
					// Q - MODERATE, U - EDIT, Y - FULL_ACCESS
					$APPLICATION->IncludeComponent("bitrix:forum.comments", "bitrix24", array(
						"FORUM_ID" => CBPHelper::getForumId(),
						"ENTITY_TYPE" => "WF",
						"ENTITY_ID" => CBPStateService::getWorkflowIntegerId($arResult['WorkflowState']['ID']),
						"ENTITY_XML_ID" => "WF_".$arResult['WorkflowState']['ID'],
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
					?>
					<?if (!$arParams['POPUP']):?>
				</div>

				<div id="bp-task-tab-2-content" class="bp-tab-content">
					<?
					$APPLICATION->IncludeComponent(
						"bitrix:bizproc.log",
						"",
						array(
							"COMPONENT_VERSION" => 2,
							"ID" => $arResult['WorkflowState']['ID'],
							"SET_TITLE" => "N",
							"INLINE_MODE" => "Y",
							"NAME_TEMPLATE" => $arParams["NAME_TEMPLATE"]
						),
						$component
					);
					?>
				</div>
			</div>
		</div>
	<?endif?>
	</div>
<?
}
?>