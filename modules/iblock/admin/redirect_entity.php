<?php
/** @global CMain $APPLICATION */
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Main;
use Bitrix\Iblock;

require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_before.php';
Loader::includeModule('iblock');
require_once $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/iblock/prolog.php';

Loc::loadMessages(__FILE__);

$manager = Iblock\Url\AdminPage\BuilderManager::getInstance();
$urlBuilder = $manager->getBuilder(Iblock\Url\AdminPage\IblockBuilder::TYPE_ID);
unset($manager);

$adminListTableID = 'tbl_iblock_redirect_entity';
$adminList = new CAdminList($adminListTableID);
$filterFields = [
	'ENTITY',
	'ID',
];
$currentFilter = $adminList->InitFilter($filterFields);
foreach ($filterFields as $fieldName)
{
	$currentFilter[$fieldName] = (string)($currentFilter[$fieldName] ?? '');
}
unset($filterFields);

$entityList = [
	'ELEMENT' => Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_ENTITY_ELEMENT'),
	'SECTION' => Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_ENTITY_SECTION'),
	'IBLOCK' => Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_ENTITY_IBLOCK')
];
$errors = array();
$entityId = '';
$entityCode = $currentFilter['ENTITY'] ?? '';

$request = Main\Context::getCurrent()->getRequest();
if ($request->isPost() && check_bitrix_sessid())
{
	$entityId = (int)$request['ID'];
	$entityCode = (string)$request['ENTITY'];
	if ($entityId <= 0)
	{
		$errors[] = Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_ERR_EMPTY_ELEMENT_ID');
	}
	if (!isset($entityList[$entityCode]))
	{
		$errors[] = Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_ERR_EMPTY_ENTITY');
	}

	if (empty($errors))
	{
		$redirectUrl = '';
		switch ($entityCode)
		{
			case 'IBLOCK':
				$iterator = CIBlock::GetList(
					[],
					[
						'ID' => $entityId,
						'CHECK_PERMISSIONS' => 'Y',
						'MIN_PERMISSION' => 'S',
					],
					false
				);
				$row = $iterator->Fetch();
				unset($iterator);
				if (empty($row))
				{
					$errors[] = Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_ERR_IBLOCK_NOT_FOUND');
				}
				else
				{
					$urlBuilder->setIblockId((int)$row['ID']);
					$redirectUrl = $urlBuilder->getElementListUrl(
						-1,
						['WF' => 'Y']
					);
				}
				unset($row);
				break;
			case 'SECTION':
				$iterator = CIBlockSection::GetList(
					[],
					[
						'ID' => $entityId,
						'CHECK_PERMISSIONS' => 'Y',
						'MIN_PERMISSION' => 'S',
					],
					false,
					false,
					[
						'ID',
						'IBLOCK_ID',
					]
				);
				$row = $iterator->Fetch();
				unset($iterator);
				if (empty($row))
				{
					$errors[] = Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_ERR_SECTION_NOT_FOUND');
				}
				else
				{
					$urlBuilder->setIblockId((int)$row['IBLOCK_ID']);
					$redirectUrl = $urlBuilder->getSectionDetailUrl(
						(int)$row['ID'],
						['find_section_section' => -1]
					);
				}
				unset($row);
				break;
			case 'ELEMENT':
				$iterator = CIBlockElement::GetList(
					[],
					[
						'ID' => $entityId,
						'CHECK_PERMISSIONS' => 'Y',
						'MIN_PERMISSION' => 'S',
					],
					false,
					false,
					[
						'ID',
						'IBLOCK_ID',
						'WF_PARENT_ELEMENT_ID',
					]
				);
				$row = $iterator->Fetch();
				unset($iterator);
				if (empty($row))
				{
					$errors[] = Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_ERR_ELEMENT_NOT_FOUND');
				}
				else
				{
					$urlBuilder->setIblockId((int)$row['IBLOCK_ID']);
					$redirectUrl = $urlBuilder->getElementDetailUrl(
						(!empty($row['WF_PARENT_ELEMENT_ID'])
							? (int)$row['WF_PARENT_ELEMENT_ID']
							: (int)$row['ID']
						),
						[
							'find_section_section' => -1,
							'WF' => 'Y',
						]
					);
				}
				unset($row);
				break;
		}
		if ($redirectUrl != '')
		{
			LocalRedirect($redirectUrl);
		}
	}
}

$APPLICATION->SetTitle(Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_TITLE'));

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/prolog_admin_after.php';

if (!empty($errors))
{
	$errorMessage = new CAdminMessage([
		'DETAILS' => implode('<br>', $errors),
		'TYPE' => 'ERROR',
		'HTML' => true,
	]);
	echo $errorMessage->Show();
	unset($errorMessage);
}

?><form name="find_form" method="POST" action="<?= $APPLICATION->GetCurPage()?>?lang=<?= LANGUAGE_ID;?>"><?php
echo bitrix_sessid_post();
$filter = new CAdminFilter(
	'element_redirect_filter',
	[
		'ENTITY' => Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_FILTER_ENTITY'),
		'ID' => Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_FILTER_ID'),
	]
);
$filter->SetDefaultRows([
	'ENTITY',
	'ID',
]);
$filter->Begin();
?>
<tr>
	<td><?= Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_FILTER_ENTITY')?></td>
	<td><select name="ENTITY"><?php
		foreach ($entityList as $key => $value)
		{
			?><option value="<?= htmlspecialcharsbx($key); ?>"<?= ($entityCode == $key ? ' selected' : ''); ?>><?= htmlspecialcharsEx($value); ?></option><?php
		}
		unset($key, $value);
		?></select>
	</td>
</tr>
<tr>
	<td><?= Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_FILTER_ID')?></td>
	<td><input type="text" name="ID" value="<?= htmlspecialcharsbx($request['ID']); ?>"></td>
</tr>
<?php
$filter->Buttons(
	[
		'table_id' => $adminListTableID,
		'url' => $APPLICATION->GetCurPage(),
		'form' => 'find_form',
	]
);
$filter->End();
?></form><?php

echo
	BeginNote()
	. Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_NOTE')
	. EndNote()
;

require $_SERVER['DOCUMENT_ROOT'] . '/bitrix/modules/main/include/epilog_admin.php';
