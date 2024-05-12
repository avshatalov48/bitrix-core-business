<? if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die();

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

use Bitrix\Main\Localization\Loc;
use \Bitrix\Main\Page\Asset;

\Bitrix\Main\UI\Extension::load([
	'ui.design-tokens',
	'ui.fonts.opensans',
	'lists',
	'ui.entity-selector',
]);

Asset::getInstance()->addJs('/bitrix/components/bitrix/ui.tile.selector/templates/.default/script.js');
Asset::getInstance()->addCss('/bitrix/components/bitrix/ui.tile.selector/templates/.default/style.css');
Asset::getInstance()->addJs('/bitrix/components/bitrix/intranet.user.selector.new/templates/.default/users.js');
Asset::getInstance()->addCss('/bitrix/components/bitrix/intranet.user.selector.new/templates/.default/style.css');
Asset::getInstance()->addJs('/bitrix/components/bitrix/crm.field.element/templates/main.edit/script.js');
Asset::getInstance()->addJs('/bitrix/components/bitrix/main.user.selector/templates/.default/script.js');

Asset::getInstance()->addJs($this->GetFolder().'/right.js');
?>

<div class="bx-lists-total-div-class" id="bx-lists-total-div-id">
	<input type="hidden" id="bx-lists-selected-list" name="IBLOCK_ID">
	<input type="hidden" id="bx-lists-lists-page" value="<?= $arResult['LISTS_URL'] ?>">
	<div id="bx-lists-store-lists" style="display:none"></div>
	<table class="bx-lists-table-title" id="bx-lists-table-title-id">
		<tr>
			<td class="bx-lists-table-td-title">
				<span class="bx-lists-title-desc-icon" id="bx-lists-table-td-title-img"></span>
				<span id="bx-lists-table-td-title"></span>
			</td>
			<td><a href="#" id="bx-lists-settings-btn" class="bx-lists-settings-btn"></a></td>
		</tr>
	</table>
	<input type="hidden" id="bx-lists-template-id" name="TEMPLATE_ID">
</div>
<input type="hidden" id="bx-lists-title-notify-admin-popup">
<input type="hidden" id="bx-lists-check-notify-admin">
<input type="hidden" id="bx-lists-select-site-id" value="<?= SITE_ID ?>" />
<input type="hidden" id="bx-lists-select-site-dir" value="<?= SITE_DIR ?>" />
<input type="hidden" id="bx-lists-random-string" value="<?= $arResult['RAND_STRING'] ?>" />

<? $frame = $this->createFrame("bp-livefeed")->begin(""); ?>
<script>
	BX.ready(function () {
		BX.Lists['LiveFeedClass_<?= $arResult['RAND_STRING']?>'] = new BX.Lists.LiveFeedClass({
			socnetGroupId: '<?= $arResult['SOCNET_GROUP_ID'] ?>',
			randomString: '<?= $arResult['RAND_STRING'] ?>',
			listData: <?= \Bitrix\Main\Web\Json::encode($arResult['LIST_DATA'] ?? null) ?>
		});
		BX.bind(BX('bx-lists-settings-btn'), 'click', function(e) {
			BX.Lists['LiveFeedClass_<?= $arResult['RAND_STRING']?>'].createSettingsDropdown(e);
		});
	});
</script>
<? $frame->end(); ?>

<script>
	BX.message({
		LISTS_JS_STATUS_ACTION_SUCCESS: '<?= GetMessageJS('LISTS_JS_STATUS_ACTION_SUCCESS') ?>',
		LISTS_JS_STATUS_ACTION_ERROR: '<?= GetMessageJS('LISTS_JS_STATUS_ACTION_ERROR') ?>',
		LISTS_ADD_STAFF: '<?=GetMessageJS("LISTS_ADD_STAFF")?>',
		LISTS_ADD_STAFF_MORE: '<?=GetMessageJS("LISTS_ADD_STAFF_MORE")?>',
		LISTS_SELECT_STAFF_SET_RIGHT: '<?=GetMessageJS("LISTS_SELECT_STAFF_SET_RIGHT")?>',
		LISTS_SAVE_BUTTON_SET_RIGHT: '<?=GetMessageJS("LISTS_SAVE_BUTTON_SET_RIGHT")?>',
		LISTS_CANCEL_BUTTON_SET_RIGHT: '<?=GetMessageJS("LISTS_CANCEL_BUTTON_SET_RIGHT")?>',
		LISTS_CANCEL_BUTTON_CLOSE: '<?=GetMessageJS("LISTS_CANCEL_BUTTON_CLOSE")?>',
		LISTS_SELECT_STAFF_SET_RESPONSIBLE: '<?=GetMessageJS("LISTS_SELECT_STAFF_SET_RESPONSIBLE_NEW")?>',
		LISTS_NOTIFY_ADMIN_TITLE_WHY: '<?=GetMessageJS("LISTS_NOTIFY_ADMIN_TITLE_WHY", "#NAME_PROCESSES#")?>',
		LISTS_NOTIFY_ADMIN_TEXT_ONE: '<?=GetMessageJS("LISTS_NOTIFY_ADMIN_TEXT_ONE", "#NAME_PROCESSES#")?>',
		LISTS_NOTIFY_ADMIN_TEXT_TWO: '<?=GetMessageJS("LISTS_NOTIFY_ADMIN_TEXT_TWO", "#NAME_PROCESSES#")?>',
		LISTS_NOTIFY_ADMIN_MESSAGE_BUTTON: '<?=GetMessageJS("LISTS_NOTIFY_ADMIN_MESSAGE_BUTTON")?>',
		LISTS_NOTIFY_ADMIN_MESSAGE: '<?=GetMessageJS("LISTS_NOTIFY_ADMIN_MESSAGE")?>',
		LISTS_CANCEL_BUTTON_INSTALL: '<?=GetMessageJS("LISTS_CANCEL_BUTTON_INSTALL")?>',
		LISTS_TITLE_POPUP_MARKETPLACE: '<?=GetMessageJS("LISTS_TITLE_POPUP_MARKETPLACE")?>',
		LISTS_MARKETPLACE_TITLE_SYSTEM_PROCESSES: '<?=GetMessageJS("LISTS_MARKETPLACE_TITLE_SYSTEM_PROCESSES")?>',
		LISTS_MARKETPLACE_TITLE_USER_PROCESSES: '<?=GetMessageJS("LISTS_MARKETPLACE_TITLE_USER_PROCESSES")?>',
		LISTS_DESIGNER_POPUP_TITLE: '<?=GetMessageJS("LISTS_DESIGNER_POPUP_TITLE")?>',
		LISTS_DESIGNER_POPUP_DESCRIPTION: '<?=GetMessageJS("LISTS_DESIGNER_POPUP_DESCRIPTION")?>'
	});
</script>

<div class="feed-add-lists-right" id="feed-add-lists-right" style="display: none;">
	<div class="feed-add-lists-form">
		<div class="feed-add-post-lists-wrap feed-add-post-destination-wrap" id="feed-add-post-lists-container">
			<span id="feed-add-post-lists-item"></span>
			<span class="feed-add-lists-input-box" id="feed-add-post-lists-input-box">
				<input type="text" value="" class="feed-add-lists-inp" id="feed-add-post-lists-input">
			</span>
			<a href="#" class="feed-add-lists-link" id="bx-lists-tag"><?= Loc::getMessage("LISTS_ADD_STAFF")?></a>
			<script>
				var	BXSocNetLogListsFormName = '<?=$this->randString(6)?>';
				BX.SocNetLogDestination.init({
					'name' : BXSocNetLogListsFormName,
					'searchInput' : BX('feed-add-post-lists-input'),
					'pathToAjax' : '/bitrix/components/bitrix/socialnetwork.blog.post.edit/post.ajax.php',
					'extranetUser' : false,
					'bindMainPopup' : { 'node' : BX('feed-add-post-lists-container'), 'offsetTop' : '-5px', 'offsetLeft': '15px'},
					'bindSearchPopup' : { 'node' : BX('feed-add-post-lists-container'), 'offsetTop' : '-5px', 'offsetLeft': '15px'},
					'departmentSelectDisable' : true,
					'lastTabDisable' : true,
					'callback' : {
						'select' : BXfpListsSelectCallback,
						'unSelect' : BXfpListsUnSelectCallback,
						'openDialog' : BXfpListsOpenDialogCallback,
						'closeDialog' : BXfpListsCloseDialogCallback,
						'openSearch' : BXfpListsOpenDialogCallback,
						'closeSearch' : BXfpListsCloseSearchCallback
					},
					'items' : {
						'users' : {},
						'groups' : {},
						'sonetgroups' : {},
						'department' : <?=CUtil::phpToJSObject($arResult['COMPANY_STRUCTURE']['department']) ?>,
						'departmentRelation' : <?=CUtil::phpToJSObject($arResult['COMPANY_STRUCTURE']['department_relation']) ?>
					},
					'itemsLast' : {
						'users' : {},
						'sonetgroups' : {},
						'department' : {},
						'groups' : {}
					},
					'itemsSelected' : {}
				});
				BX.bind(BX('feed-add-post-lists-input'), 'keyup', BXfpListsSearch);
				BX.bind(BX('feed-add-post-lists-input'), 'keydown', BXfpListsSearchBefore);
				BX.bind(BX('bx-lists-tag'), 'click', function(e){BX.SocNetLogDestination.openDialog(BXSocNetLogListsFormName); BX.PreventDefault(e); });
				BX.bind(BX('feed-add-post-lists-container'), 'click', function(e){BX.SocNetLogDestination.openDialog(BXSocNetLogListsFormName); BX.PreventDefault(e); });
			</script>
		</div>
	</div>
</div>

<div id="bx-lists-notify-admin-popup" style="display:none;">
	<div id="bx-lists-notify-admin-popup-content" class="bx-lists-notify-admin-popup-content">
	</div>
</div>

<div id="bx-lists-marketplace_processes" style="display:none;">
	<div id="bx-lists-marketplace_processes-content" class="bx-lists-marketplace_processes-content">
	</div>
</div>

<div id="bx-lists-designer-template-popup" style="display:none;">
	<div id="bx-lists-designer-template-popup-content" class="bx-lists-designer-template-popup-content">
	</div>
</div>