<?php

if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var CBitrixComponent $component */

\Bitrix\Main\UI\Extension::load([
	"ui.design-tokens",
	"lists",
	"ui.buttons",
]);

$randString = $arResult['RAND_STRING'];
$jsClass = 'ListsIblockClass_'.$randString;
$claim = $arParams["IBLOCK_TYPE_ID"] === COption::GetOptionString("lists", "livefeed_iblock_type_id");
?>

<input type="hidden" id="bx-lists-select-site" value="<?= SITE_ID ?>" />
<?php
foreach($arResult["ITEMS"] as $item): ?>
	<div class="bp-bx-application">
		<span class="bp-bx-application-link">
			<a href="<?= $item["LIST_URL"]?>"  class="bp-bx-application-icon"><?= $item["IMAGE"] ?></a>
			<span class="bp-bx-application-title-wrapper">
				<a href="<?= $item["LIST_URL"]?>"  class="bp-bx-application-title"><?= htmlspecialcharsbx($item['NAME']) ?></a>
				<?php
				if($claim && $arParams['CAN_EDIT']): ?>
					<span class="bp-bx-application-check">
						<input
							type="checkbox"
							value=""
							id="bx-lists-show-live-feed-<?= intval($item['ID']) ?>"
							<?= intval($item['SHOW_LIVE_FEED']) ? 'checked' : '' ?>
							onclick="javascript:BX.Lists['<?=$jsClass?>'].showLiveFeed(<?= $item['ID'] ?>);"
						>
						<label
							for="bx-lists-show-live-feed-<?= $item['ID'] ?>"
						><?= GetMessage("CT_BLL_TOOLBAR_SHOW_LIVE_FEED_NEW") ?></label>
					</span>
				<?php
				endif; ?>
			</span>
		</span>
	</div>
<?php
endforeach; ?>

<script>
	BX(function () {
		BX.Lists['<?=$jsClass?>'] = new BX.Lists.ListsIblockClass({
			randomString: '<?= $randString ?>'
		});
	});
</script>
