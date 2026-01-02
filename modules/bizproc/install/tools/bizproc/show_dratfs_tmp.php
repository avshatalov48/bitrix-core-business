<?php

use Bitrix\Main\Loader;
use Bitrix\Bizproc\Workflow\Template\WorkflowTemplateDraftTable;
use Bitrix\Main\Grid\Options;
use Bitrix\Main\Grid\Panel\Snippet;
use Bitrix\UI\Toolbar\Facade\Toolbar;
use Bitrix\UI\Buttons\CreateButton;

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/header.php");

if (!Loader::includeModule('bizproc'))
{
	die('bizproc not installed');
}

global $APPLICATION;
$APPLICATION->SetTitle('Черновики');

$saveButton = CreateButton::create([
	'text' => 'Сохранить черновик',
	'className' => 'ui-btn-no-caps',
	'click' => new \Bitrix\UI\Buttons\JsCode(
		"window.top.BCPSaveTemplateDraft().then(() => {
            const grid = BX.Main.gridManager && BX.Main.gridManager.getInstanceById('workflow_template_draft_grid');
            if (grid) {grid.reload()}
        }).catch((response) => {
            alert(response.errors[0].message);
        });"
	),
]);

Toolbar::addButton($saveButton, \Bitrix\UI\Toolbar\ButtonLocation::AFTER_TITLE);

$gridId = 'workflow_template_draft_grid';
$gridOptions = new Options($gridId);
$navParams = $gridOptions->GetNavParams();
$nav = new \Bitrix\Main\UI\PageNavigation($gridId);
$nav->allowAllRecords(true)->setPageSize($navParams['nPageSize'])->initFromUri();

$request = \Bitrix\Main\Application::getInstance()->getContext()->getRequest();
$templateId = $request->get('templateId');
if (isset($request['signedDocumentType']))
{
	$unsignedDocument = CBPDocument::unSignDocumentType($request['signedDocumentType']);
}
else
{
	die('signedDocumentType not found');
}

$query = WorkflowTemplateDraftTable::getList([
	'filter' => [
		'=MODULE_ID' => $unsignedDocument[0],
		'=ENTITY' => $unsignedDocument[1],
		'=DOCUMENT_TYPE' => $unsignedDocument[2],
	],
	'select' => ['ID', 'CREATED', 'USER_ID', 'TEMPLATE_DATA', 'TEMPLATE_ID', 'STATUS'],
	'order' => ['CREATED' => 'DESC'],
	'offset' => $nav->getOffset(),
	'limit' => $nav->getLimit(),
	'count_total' => true
]);

$rows = [];
$jsHandlerEdit = "window.top.BX.Bizproc.WorkflowEditComponent.Globals.loadDraft('%s');";

while ($item = $query->fetch())
{
	$status = match ((int)$item['STATUS'])
	{
		WorkflowTemplateDraftTable::STATUS_DRAFT => 'Черновик',
		WorkflowTemplateDraftTable::STATUS_AUTOSAVE => 'Автосохранение',
	};

	$rows[] = [
		'id' => $item['ID'],
		'data' => $item,
		'columns' => [
			'NAME' => htmlspecialcharsbx($item['TEMPLATE_DATA']['NAME']),
			'CREATED' => htmlspecialcharsbx($item['CREATED']),
			'USER_ID' => \CBPViewHelper::getUserFullNameById($item['USER_ID']),
			'TEMPLATE_ID' => htmlspecialcharsbx($item['TEMPLATE_ID']),
			'STATUS' => htmlspecialcharsbx($status),
		],
		'actions' => [
			[
				'text' => 'Загрузить',
				'onclick' => sprintf($jsHandlerEdit, $item['ID']),
			],
		],
	];
}

$nav->setRecordCount($query->getCount());

$columns = [
	['id' => 'ID', 'name' => 'ID', 'default' => true, 'sort' => 'ID'],
	['id' => 'NAME', 'name' => 'Название', 'default' => true, 'sort' => 'NAME'],
	['id' => 'CREATED', 'name' => 'Дата создания', 'default' => true, 'sort' => 'CREATED'],
	['id' => 'USER_ID', 'name' => 'Пользователь', 'default' => true, 'sort' => 'USER_ID'],
	['id' => 'TEMPLATE_ID', 'name' => 'ID шаблона', 'default' => true],
	['id' => 'STATUS', 'name' => 'Статус', 'default' => true],
];
?>
	<div>
		<?php
		$APPLICATION->IncludeComponent('bitrix:ui.sidepanel.wrapper', '', [
			'POPUP_COMPONENT_NAME' => 'bitrix:main.ui.grid',
			'POPUP_COMPONENT_TEMPLATE_NAME' => '',
			'POPUP_COMPONENT_PARAMS' =>  [
				'GRID_ID' => $gridId,
				'COLUMNS' => $columns,
				'ROWS' => $rows,
				'NAV_OBJECT' => $nav,
				'AJAX_MODE' => 'Y',
				'AJAX_OPTION_JUMP' => 'N',
				'AJAX_OPTION_HISTORY' => 'N',
				'PAGE_SIZES' => [
					['NAME' => '5', 'VALUE' => '5'],
					['NAME' => '10', 'VALUE' => '10'],
					['NAME' => '20', 'VALUE' => '20'],
					['NAME' => '50', 'VALUE' => '50'],
				],
				'SHOW_ROW_CHECKBOXES' => false,
				'SHOW_CHECK_ALL_CHECKBOXES' => false,
				'SHOW_ROW_ACTIONS_MENU' => true,
				'SHOW_GRID_SETTINGS_MENU' => true,
				'SHOW_NAVIGATION_PANEL' => true,
				'SHOW_PAGINATION' => true,
				'SHOW_TOTAL_COUNTER' => true,
				'ALLOW_COLUMNS_SORT' => true,
				'ALLOW_COLUMNS_RESIZE' => true,
				'ALLOW_HORIZONTAL_SCROLL' => true,
				'ALLOW_SORT' => true,
				'ALLOW_PIN_HEADER' => true,
				'SHOW_ACTION_PANEL' => true,
				'ACTION_PANEL' => [
					'GROUPS' => [
						'TYPE' => [
							'ITEMS' => [
								(new Snippet())->getRemoveButton(),
							],
						],
					],
				],
			],
			'USE_PADDING' => true,
			'USE_UI_TOOLBAR' => 'Y',
		]);


		?>
	</div>
<?php

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_after.php');

require($_SERVER["DOCUMENT_ROOT"] . "/bitrix/footer.php");
