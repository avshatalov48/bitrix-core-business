<?php

use Bitrix\Main\Web\Json;

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var CBitrixComponentTemplate $this
 * @var $arParams
 * @var $arResult
 * @global $APPLICATION
 */
$component = $this->getComponent();

global $USER;

CJSCore::init([ 'translit', 'socnetlogdest' ]);
\Bitrix\Main\UI\Extension::load("ui.selector");

$frame = $this->createFrame()->begin(false);

if (!empty($arParams['LOAD_JS']))
{
	?><script src="<?=htmlspecialcharsbx($this->getFolder()).'/script.js'?>"></script><?php
}
?>
<script>
	BX.ready(function() {

		var f = function(params) {
			var selectorId = '<?=CUtil::JSEscape($arParams['ID'])?>';
			var inputId = (
				BX.type.isNotEmptyObject(params)
				&& BX.type.isNotEmptyString(params.inputId)
					? params.inputId
					: <?=($arParams['BIND_ID'] ? "'".$arParams['BIND_ID']."'" : 'false')?>);
			var inputBoxId = <?=(!empty($arParams['INPUT_BOX_ID']) ? "'".$arParams['INPUT_BOX_ID']."'" : 'false')?>;
			var inputContainerId = <?=(!empty($arParams['INPUT_CONTAINER_ID']) ? "'".$arParams['INPUT_CONTAINER_ID']."'" : 'false')?>;
			var containerId = (typeof params != 'undefined' && params.containerId != 'undefined' ? params.containerId : <?=($arParams['CONTAINER_ID'] ? "'".$arParams['CONTAINER_ID']."'" : 'false')?>);
			var bindId = (containerId ? containerId : inputId);
			var openDialogWhenInit = (
				typeof params == 'undefined'
				|| typeof params.openDialogWhenInit == 'undefined'
				|| !!params.openDialogWhenInit
			);

			var fieldName = <?=(!empty($arParams['FIELD_NAME']) ? "'".$arParams['FIELD_NAME']."'" : 'false')?>;

			if (
				BX.type.isNotEmptyObject(params)
				&& typeof params.id != 'undefined'
				&& params.id != selectorId
			)
			{
				return;
			}

			BX.Main.SelectorV2.create({
				apiVersion: <?=(!empty($arParams["API_VERSION"]) ? (int)$arParams["API_VERSION"] : 2)?>,
				id: selectorId,
				fieldName: fieldName,
				pathToAjax: '<?=$component->getPath()?>/ajax.php',
				inputId: inputId,
				inputBoxId: inputBoxId,
				inputContainerId: inputContainerId,
				bindId: bindId,
				containerId: containerId,
				tagId: BX('<?= ($arParams['TAG_ID'] ?? '')?>'),
				openDialogWhenInit: openDialogWhenInit,
				bindNode: BX('<?=$arParams['BIND_ID']?>'),
				options: <?= Json::encode($arParams["OPTIONS"]) ?>,
				callback : {
					select: <?=(!empty($arParams["CALLBACK"]["select"]) ? $arParams["CALLBACK"]["select"] : 'null')?>,
					unSelect: <?=(!empty($arParams["CALLBACK"]["unSelect"]) ? $arParams["CALLBACK"]["unSelect"] : 'null')?>,
					openDialog: <?=(!empty($arParams["CALLBACK"]["openDialog"]) ? $arParams["CALLBACK"]["openDialog"] : 'null')?>,
					closeDialog: <?=(!empty($arParams["CALLBACK"]["closeDialog"]) ? $arParams["CALLBACK"]["closeDialog"] : 'null')?>,
					openSearch: <?=(!empty($arParams["CALLBACK"]["openSearch"]) ? $arParams["CALLBACK"]["openSearch"] : 'null')?>,
					closeSearch: <?=(!empty($arParams["CALLBACK"]["closeSearch"]) ? $arParams["CALLBACK"]["closeSearch"] : 'null')?>,
					openEmailAdd: <?=(!empty($arParams["CALLBACK"]["openEmailAdd"]) ? $arParams["CALLBACK"]["openEmailAdd"] : 'null')?>,
					closeEmailAdd: <?=(!empty($arParams["CALLBACK"]["closeEmailAdd"]) ? $arParams["CALLBACK"]["closeEmailAdd"] : 'null')?>
				},
				callbackBefore : {
					select: <?=(!empty($arParams["CALLBACK_BEFORE"]) && !empty($arParams["CALLBACK_BEFORE"]["select"]) ? $arParams["CALLBACK_BEFORE"]["select"] : 'null')?>,
					openDialog: <?=(!empty($arParams["CALLBACK_BEFORE"]) && !empty($arParams["CALLBACK_BEFORE"]["openDialog"]) ? $arParams["CALLBACK_BEFORE"]["openDialog"] : 'null')?>,
					context: <?=(!empty($arParams["CALLBACK_BEFORE"]) && !empty($arParams["CALLBACK_BEFORE"]["context"]) ? $arParams["CALLBACK_BEFORE"]["context"] : 'null')?>,
				},
				items : {
					selected: <?= Json::encode($arParams['ITEMS_SELECTED']) ?>,
					undeletable: <?= Json::encode($arParams['ITEMS_UNDELETABLE']) ?>,
					hidden: <?= Json::encode($arParams['ITEMS_HIDDEN']) ?>
				},
				entities: {
					users: <?= Json::encode($arResult['ENTITIES']['USERS'] ?? []) ?>,
					groups: <?= Json::encode($arResult['ENTITIES']['GROUPS'] ?? []) ?>,
					sonetgroups: <?= Json::encode($arResult['ENTITIES']['SONETGROUPS'] ?? []) ?>,
					department: <?= Json::encode($arResult['ENTITIES']['DEPARTMENTS'] ?? []) ?>
				}
			});

			BX.removeCustomEvent(window, "<?=$arParams["OPTIONS"]["eventInit"]?>", arguments.callee);
		};

		<?php
		if (!empty($arParams["OPTIONS"]["eventInit"]))
		{
			?>
			BX.addCustomEvent(window, "<?=$arParams["OPTIONS"]["eventInit"]?>", f);
			<?php
		}
		else
		{
			?>
			f();
			<?php
		}
		?>

	});
</script>

<?php
$frame->end();
