<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use \Bitrix\Landing\Manager;
use \Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

// vars
$tab = $this->getComponent()->request('tab');
$selectId = $this->getComponent()->request('select');
$selectUrl = $this->getComponent()->getUri(['select' => '__id__']);
$tabKb = \Bitrix\Landing\Site\Type::SCOPE_CODE_KNOWLEDGE;
$tabSocGroup = \Bitrix\Landing\Site\Type::SCOPE_CODE_GROUP;

// menu
$menuItems = [
	$tabKb => [
		'NAME' => Loc::getMessage('LANDING_TPL_NOTES_TITLE_COMMON'),
		'ATTRIBUTES' => [
			'href' => $this->getComponent()->getUri(['tab' => $tabKb])
		],
		'ACTIVE' => (!$tab || $tab == $tabKb)
	],
	$tabSocGroup => [
		'NAME' => Loc::getMessage('LANDING_TPL_NOTES_TITLE_GROUP'),
		'ATTRIBUTES' => [
			'href' => $this->getComponent()->getUri(['tab' => $tabSocGroup])
		],
		'ACTIVE' => ($tab == $tabSocGroup)
	]
];
$this->setViewTarget('left-panel');
$APPLICATION->includeComponent(
	'bitrix:ui.sidepanel.wrappermenu',
	'',
	[
		'ID' => 'landing-notes-left-menu',
		'ITEMS' => $menuItems,
		'TITLE' => Loc::getMessage('LANDING_TPL_NOTES_LEFT_TITLE')
	]
);
$this->endViewTarget();

if (!$tab || !isset($menuItems[$tab]))
{
	$menuItemsKeys = array_keys($menuItems);
	$tab = $menuItemsKeys[0];
}
$tab = strtoupper($tab);

// tab content below
?>

<?if ($tab == $tabSocGroup):?>
<style>
	.landing-item-add-new {
		display: none!important;
	}
</style>
<?endif;?>

<?if ($selectId):?>
	<script>
		BX.ready(function()
		{
			if (typeof top.BX.SidePanel !== 'undefined')
			{
				top.BX.onCustomEvent('Landing:onNoteKnowledgeSelect', [<?= intval($selectId);?>, '<?= $tab;?>']);
				top.BX.SidePanel.Instance.close();
			}
		});
	</script>
<?endif;?>

<div class="landing-notes">
	<?$res = $APPLICATION->IncludeComponent(
		'bitrix:landing.sites',
		'.default',
		array(
			'TYPE' => $tab,
			'ACCESS_CODE' => ($tab == strtoupper($tabKb)) ? 'edit' : '',
			'DRAFT_MODE' => 'Y',
			'OVER_TITLE' => Loc::getMessage('LANDING_TPL_NOTES_SELECT'),
			'PAGE_URL_SITE' => str_replace('__id__', '#site_show#', $selectUrl),
			'PAGE_URL_SITE_EDIT' => $this->getComponent()->getUri(['create' => 'Y'])
		),
		$component
	);?>
	<?if (empty($res['SITES']) && $res['ACCESS_SITE_NEW'] == 'N'):?>
	<p><?= $this->getComponent()->getMessageType('LANDING_TPL_NOTES_EMPTY_ALERT');?></p>
	<?endif;?>
</div>

<?php
Manager::setPageTitle(Loc::getMessage('LANDING_TPL_NOTES_TITLE'));