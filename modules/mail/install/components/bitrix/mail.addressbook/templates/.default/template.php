<?php
if (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true)
{
	die();
}

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\Web\Json;

\Bitrix\Main\UI\Extension::load('mail.avatar');

\CJSCore::init("sidepanel");

$APPLICATION->SetTitle(Loc::getMessage('MAIL_ADDRESSBOOK_LIST_PAGE_TITLE'));

$inSidePanel = isset($arResult["IFRAME"]) && $arResult["IFRAME"] === "Y";

$bodyClass = $APPLICATION->getPageProperty('BodyClass', false);

$APPLICATION->setPageProperty(
	'BodyClass',
	trim(sprintf('%s %s', $bodyClass, 'pagetitle-toolbar-field-view pagetitle-mail-view'))
);

$this->setViewTarget('inside_pagetitle');
?>

<button class="ui-btn ui-btn-primary" id="mail-address-book-add-button">
	<?=Loc::getMessage('MAIL_ADDRESSBOOK_ADD_ADDRESSES')?>
</button>

<div class="pagetitle-container mail-addressbook-pagetitle-flexible-space">
	<? $APPLICATION->IncludeComponent(
		'bitrix:main.ui.filter',
		'',
		[
			'GRID_ID' => $arResult['GRID_ID'],
			'FILTER_ID' => $arResult['GRID_ID'],
			'FILTER' => $arResult['FILTER'],
			'ENABLE_LABEL' => true,
		]
	); ?>
</div>

<?php
$this->endViewTarget();

/**
 * @param $item
 * @param $canEdit
 *
 * @return array
 * @throws \Bitrix\Main\ArgumentException
 */
function makeRowForGrid($item, $canEdit)
{
	$contactNameTemplate = '<div class="mail-addressbook-contact-name">
								<div class="mail-ui-avatar" user-name="'.htmlspecialcharsbx($item['NAME']).'" email="'.htmlspecialcharsbx($item['EMAIL']).'"></div>
								<span class="mail-addressbook-contact-name-title">'.htmlspecialcharsbx($item['NAME']).'</span>
							</div>';

	$actions = [];
	$actions[] = [
		'TEXT' => Loc::getMessage('MAIL_ADDRESSBOOK_GRID_ACTION_EDIT'),
		'TITLE' => Loc::getMessage('MAIL_ADDRESSBOOK_GRID_ACTION_EDIT'),
		'ONCLICK' => 'BX.Mail.AddressBook.openEditDialog('.Json::Encode(
				[
					'contactID' => $item['ID'],
					'contactData' => [
						'name' => $item['NAME'],
						'email' => $item['EMAIL'],
					],
				]
			).')',
		'DEFAULT' => true,
	];

	if ($canEdit)
	{
		$actions[] = [
			'TITLE' => Loc::getMessage('MAIL_ADDRESSBOOK_GRID_ACTION_REMOVE'),
			'TEXT' => Loc::getMessage('MAIL_ADDRESSBOOK_GRID_ACTION_REMOVE'),
			'ONCLICK' => 'BX.Mail.AddressBook.openRemoveDialog('.Json::Encode(
					[
						'id' => $item['ID'],
					]
				).',"MAIL_ADDRESSBOOK_LIST")',
			'DEFAULT' => true,
		];
	}

	$item = [
		'data' => [
			"NAME" => $contactNameTemplate,
			"EMAIL" => htmlspecialcharsbx($item['EMAIL']),
		],
		'actions' => $actions,
		'id' => $item['ID'],
	];

	return $item;
}

/**
 * @param $tableRows
 * @param $canEdit
 *
 * @return array
 * @throws \Bitrix\Main\ArgumentException
 */
function makeRowsForGrid($tableRows, $canEdit)
{
	$gridRows = [];
	foreach ($tableRows as $item)
	{
		$gridRows[] = makeRowForGrid($item, $canEdit);
	}

	return $gridRows;
}

$removeButton = new \Bitrix\Main\Grid\Panel\Snippet();
?>
<div class="mail-addressbook-list-grid">
<?php
$APPLICATION->IncludeComponent(
	'bitrix:main.ui.grid',
	'',
	[
		'NAV_OBJECT' => $arResult['NAV_OBJECT'],
		'~NAV_PARAMS' => ['SHOW_ALWAYS' => false],
		'AJAX_MODE' => 'Y',
		'AJAX_OPTION_HISTORY' => 'N',
		'AJAX_OPTION_JUMP' => 'N',
		'AJAX_OPTION_STYLE' => 'N',
		'GRID_ID' => $arResult['GRID_ID'],
		'COLUMNS' => $arResult['COLUMNS'],
		'ROWS' => makeRowsForGrid($arResult['ROWS'], $arResult['canEdit']),
		'SORT' => $arResult['SORT'],
		'SORT_VARS' => $arResult['SORT_VARS'],
		'ALLOW_SORT' => true,
		'TOTAL_ROWS_COUNT' => $arResult['ROWS_COUNT'],
		'ACTION_PANEL' => [
			'GROUPS' => [
				[
					'ITEMS' => [
						$removeButton->getRemoveButton(),
					],
				],
			],
		],
	]
);
?>
</div>
