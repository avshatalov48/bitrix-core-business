<?
/** @global CMain $APPLICATION */
use Bitrix\Main\Loader,
	Bitrix\Main\Localization\Loc,
	Bitrix\Main;

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
Loader::includeModule('iblock');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/iblock/prolog.php');

Loc::loadMessages(__FILE__);

$adminListTableID = 'tbl_iblock_redirect_entity';
$adminList = new CAdminList($adminListTableID);
$filterFields = array(
	'ENTITY'
);
$adminList->InitFilter($filterFields);
unset($filterFields);

$entityList = array(
	'ELEMENT' => Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_ENTITY_ELEMENT'),
	'IBLOCK' => Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_ENTITY_IBLOCK')
);
$errors = array();
$entityId = '';
$entityCode = '';
if (isset($ENTITY))
	$entityCode = $ENTITY;

$request = Main\Context::getCurrent()->getRequest();
if ($request->isPost() && check_bitrix_sessid())
{
	$entityId = (int)$request['ID'];
	$entityCode = (string)$request['ENTITY'];
	if ($entityId <= 0)
		$errors[] = Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_ERR_EMPTY_ELEMENT_ID');
	if (!isset($entityList[$entityCode]))
		$errors[] = Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_ERR_EMPTY_ENTITY');

	if (empty($errors))
	{
		$redirectUrl = '';
		switch ($entityCode)
		{
			case 'IBLOCK':
				$iblockIterator = CIBlock::GetList(
					array(),
					array('ID' => $entityId, 'CHECK_PERMISSIONS' => 'Y', 'MIN_PERMISSION' => 'S'),
					false
				);
				$iblock = $iblockIterator->Fetch();
				if (empty($iblock))
				{
					$errors[] = Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_ERR_IBLOCK_NOT_FOUND');
				}
				else
				{
					$redirectUrl = CIBlock::GetAdminElementListLink(
						$iblock['ID'],
						array('find_section_section' => -1, 'WF' => 'Y', 'menu' => null)
					);
				}
				unset($iblock, $iblockIterator);
				break;
			case 'ELEMENT':
				$elementIterator = CIBlockElement::GetList(
					array(),
					array('ID' => $entityId, 'CHECK_PERMISSIONS' => 'Y', 'MIN_PERMISSION' => 'S'),
					false,
					false,
					array('ID', 'IBLOCK_ID', 'WF_PARENT_ELEMENT_ID')
				);
				$element = $elementIterator->Fetch();
				if (empty($element))
				{
					$errors[] = Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_ERR_ELEMENT_NOT_FOUND');
				}
				else
				{
					$redirectUrl = CIBlock::GetAdminElementEditLink(
						$element['IBLOCK_ID'],
						(!empty($element['WF_PARENT_ELEMENT_ID']) ? $element['WF_PARENT_ELEMENT_ID'] : $element['ID']),
						array('find_section_section' => -1, 'WF' => 'Y', 'menu' => null)
					);
				}
				unset($element, $elementIterator);
				break;
		}
		if ($redirectUrl != '')
			LocalRedirect('/bitrix/admin/'.$redirectUrl);
	}
}

$APPLICATION->SetTitle(Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_TITLE'));

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

if (!empty($errors))
{
	$errorMessage = new CAdminMessage(
		array(
			'DETAILS' => implode('<br>', $errors),
			'TYPE' => 'ERROR',
			'HTML' => true
		)
	);
	echo $errorMessage->Show();
	unset($errorMessage);
}

?><form name="find_form" method="POST" action="<?echo $APPLICATION->GetCurPage()?>?lang=<?=LANGUAGE_ID;?>"><?
echo bitrix_sessid_post();
$filter = new CAdminFilter(
	'element_redirect_filter',
	array(
		Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_FILTER_ENTITY'),
		Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_FILTER_ID')
	)
);
$filter->Begin();
?>
<tr>
	<td><?echo Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_FILTER_ENTITY')?></td>
	<td><select name="ENTITY"><?
		foreach ($entityList as $key => $value)
		{
			?><option value="<?=htmlspecialcharsbx($key); ?>"<?=($entityCode == $key ? ' selected' : ''); ?>><?=htmlspecialcharsEx($value); ?></option><?
		}
		unset($key, $value);
		?></select>
	</td>
</tr>
<tr>
	<td><?echo Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_FILTER_ID')?></td>
	<td><input type="text" name="ID" value="<?=htmlspecialcharsbx($request['ID']); ?>"></td>
</tr>
<?
$filter->Buttons(
	array(
		"table_id" => $adminListTableID,
		"url" => $APPLICATION->GetCurPage(),
		"form" => "find_form"
	)
);
$filter->End();
?></form><?

echo BeginNote();
echo Loc::getMessage('BX_IBLOCK_REDIRECT_ENTITY_NOTE');
echo EndNote();

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');