<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

/**
 * @var $APPLICATION \CMain
 * @var array $arResult
 * @var array $arParams
 */

use Bitrix\Disk\Ui\FolderListFilter;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Extension;
use Bitrix\Main\Web\Json;

Extension::load('ui.buttons');

$messages = Loc::loadLanguageFile(__DIR__ . '/files.php');

$diskFilter = new FolderListFilter((int)$arResult['storageId'], true);
$filterId = $diskFilter->getConfig()['FILTER_ID'];
?>

<div class="sn-spaces__toolbar-space_basic">
	<div class="sn-spaces__toolbar-space_left-content">
		<div id="sn-spaces-toolbar-files-btn"></div>
		<div
			class="sn-spaces__toolbar_filter-container ui-ctl ui-ctl-textbox ui-ctl-wa ui-ctl-after-icon ui-ctl-round ui-ctl-transp-white-borderless"
			id="sn-spaces__toolbar_filter-container"
		>
			<?php
			$APPLICATION->IncludeComponent(
				'bitrix:main.ui.filter',
				'',
				array_merge(
					$diskFilter->getConfig(),
					[
						'THEME' => Bitrix\Main\UI\Filter\Theme::SPACES,
					]
				)
			);
			?>
		</div>
	</div>
	<div class="sn-spaces__toolbar-space_right-content">
		<div id="sn-spaces-toolbar-files-settings-btn"></div>
	</div>
</div>

<script>
	BX.ready(function() {
		BX.message(<?= Json::encode($messages) ?>);

		const filesToolbar = new BX.Socialnetwork.Spaces.FilesToolbar({
			diskComponentId: 'folder_list',
			networkDriveLink: '<?= CUtil::JSescape($arResult['networkDriveLink']) ?>',
			pathToUserFilesVolume: '<?= CUtil::JSescape($arResult['pathToUserFilesVolume']) ?>',
			pathToTrash: '<?= CUtil::JSescape($arParams['URL_TO_TRASHCAN_LIST']) ?>',
			documentHandlers: <?= Json::encode($arResult['documentHandlers']) ?>,
			permissions: <?= Json::encode($arResult['permissions']) ?>,
			listAvailableFeatures: <?= Json::encode($arResult['listAvailableFeatures']) ?>,
			featureRestrictionMap: <?= Json::encode($arResult['featureRestrictionMap']) ?>,
			isTrashMode: true,
			filterId: '<?= $filterId ?>',
			filterContainer: document.getElementById('sn-spaces__toolbar_filter-container'),
		});

		filesToolbar.renderCleanBtnTo(document.getElementById('sn-spaces-toolbar-files-btn'));
		filesToolbar.renderSettingsBtnTo(document.getElementById('sn-spaces-toolbar-files-settings-btn'));
	});
</script>
