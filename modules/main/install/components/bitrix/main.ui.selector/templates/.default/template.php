<?
/**
 * @var CBitrixComponentTemplate $this
 * @var $arParams
 * @var $arResult
 * @global $APPLICATION
 */
$component = $this->getComponent();

if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true)
{
	die();
}

global $USER;

\CJSCore::init(array('socnetlogdest'));

$frame = $this->createFrame()->begin(false);
?>
<script>
	BX.ready(function() {

		var f = function(params) {
			var selectorId = '<?=CUtil::JSEscape($arParams['ID'])?>';
			var inputId = (typeof params != 'undefined' && params.inputId != 'undefined' ? params.inputId : <?=($arParams['BIND_ID'] ? "'".$arParams['BIND_ID']."'" : 'false')?>);
			var containerId = (typeof params != 'undefined' && params.containerId != 'undefined' ? params.containerId : <?=($arParams['CONTAINER_ID'] ? "'".$arParams['CONTAINER_ID']."'" : 'false')?>);
			var bindId = inputId;
			var openDialogWhenInit = (typeof params == 'undefined' || typeof params.openDialogWhenInit == 'undefined' || !!params.openDialogWhenInit);

			if (
				typeof params != 'undefined'
				&& typeof params.id != 'undefined'
				&& params.id != selectorId
			)
			{
				return;
			}

			BX.Main.Selector.create({
				id: selectorId,
				pathToAjax: '<?=$component->getPath()?>/ajax.php',
				inputId: inputId,
				bindId: bindId,
				containerId: containerId,
				tagId: BX('<?=$arParams['TAG_ID']?>'),
				openDialogWhenInit: openDialogWhenInit,
				bindNode: BX('<?=$arParams['BIND_ID']?>'),
				options: <?=\CUtil::phpToJSObject($arParams["OPTIONS"])?>,
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
				items : {
					selected: <?=\CUtil::phpToJSObject($arParams['ITEMS_SELECTED'])?>,
					hidden: <?=\CUtil::phpToJSObject($arParams['ITEMS_HIDDEN'])?>
				},
				entities: {
					users: <?=\CUtil::phpToJSObject($arResult['ENTITIES']['USERS'])?>,
					groups: <?=\CUtil::phpToJSObject($arResult['ENTITIES']['GROUPS'])?>,
					sonetgroups: <?=\CUtil::phpToJSObject($arResult['ENTITIES']['SONETGROUPS'])?>,
					department: <?=\CUtil::phpToJSObject($arResult['ENTITIES']['DEPARTMENTS'])?>
				}
			});
		};

		<?
		if (!empty($arParams["OPTIONS"]["eventInit"]))
		{
			?>
			BX.addCustomEvent(window, "<?=$arParams["OPTIONS"]["eventInit"]?>", f);
			<?
		}
		else
		{
			?>
			f();
			<?
		}
		?>

	});
</script>

<?
$frame->end();
?>