<?
/** @global CUser $USER */
/** @global CMain $APPLICATION */
/** @global array $FIELDS */
use Bitrix\Main;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;
use Bitrix\Iblock;
use Bitrix\Catalog;

$prologAbsent = (!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED !== true);
if($prologAbsent)
{
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
}
Loader::includeModule('catalog');
Loc::loadMessages(__FILE__);

$APPLICATION->setTitle(Loc::getMessage('PSL_PAGE_TITLE'));

if(!$USER->canDoOperation('catalog_read') && !$USER->canDoOperation('catalog_view'))
{
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
	ShowError(Loc::getMessage('PSL_ACCESS_DENIED'));
	require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
	die();
}

if(isset($_REQUEST['mode']) && ($_REQUEST['mode'] == 'list' || $_REQUEST['mode'] == 'frame'))
	CFile::disableJSFunction(true);

$tableId = 'tbl_product_subscription_list';
$sortObject = new CAdminSorting($tableId, 'DATE_FROM', 'DESC');
$listObject = new CAdminList($tableId, $sortObject);

if(!isset($by))
	$by = 'DATE_FROM';
if(!isset($order))
	$order = 'DESC';

$filterFields = array(
	'find_id',
	'find_user_id',
	'find_user_contact',
	'find_item_id',
	'find_date_from_1',
	'find_date_from_2',
	'find_date_to_1',
	'find_date_to_2',
	'find_contact_type',
	'find_active'
);
$listObject->initFilter($filterFields);

if(isset($_REQUEST['ITEM_ID']))
	$find_item_id = $_REQUEST['ITEM_ID'];

$filter = array();
if($find_id)
	$filter['=ID'] = $find_id;
if($find_user_id)
	$filter['=USER_ID'] = $find_user_id;
if($find_user_contact)
	$filter['%USER_CONTACT'] = $find_user_contact;
if($find_item_id)
	$filter['=ITEM_ID'] = $find_item_id;
if($find_date_from_1)
	$filter['>=DATE_FROM'] = $find_date_from_1;
if($find_date_to_1)
	$filter['>=DATE_TO'] = $find_date_to_1;
if($find_contact_type)
	$filter['=CONTACT_TYPE'] = $find_contact_type;
if($find_active)
{
	if($find_active == 'Y')
	{
		$filter[] = array(
			'LOGIC' => 'OR',
			array('=DATE_TO' => false),
			array('>DATE_TO' => date($DB->dateFormatToPHP(CLang::getDateFormat('FULL')), time()))
		);
	}
	else
	{
		$filter[] = array(
			'LOGIC' => 'AND',
			array('!=DATE_TO' => false),
			array('<DATE_TO' => date($DB->dateFormatToPHP(CLang::getDateFormat('FULL')), time()))
		);
	}
}
if(!empty($find_date_from_2))
{
	$filter['<=DATE_FROM'] = CIBlock::isShortDate($find_date_from_2) ?
		ConvertTimeStamp(AddTime(MakeTimeStamp($find_date_from_2), 1, 'D'), 'FULL'): $find_date_from_2;
}
if(!empty($find_date_to_2))
{
	$filter['<=DATE_TO'] = CIBlock::isShortDate($find_date_to_2) ?
		ConvertTimeStamp(AddTime(MakeTimeStamp($find_date_to_2), 1, 'D'), 'FULL'): $find_date_to_2;
}

$subscribeManager = new Catalog\Product\SubscribeManager();

if(($listRowId = $listObject->groupAction()))
{
	switch($_REQUEST['action'])
	{
		case 'delete':
			$itemId = 0;
			if(isset($_REQUEST['itemId']))
				$itemId = $_REQUEST['itemId'];
			$subscribeManager->deleteManySubscriptions($listRowId, $itemId);
			break;
		case 'activate':
			$subscribeManager->activateSubscription($listRowId);
			break;
		case 'deactivate':
			$subscribeManager->deactivateSubscription($listRowId);
			break;
	}

	$errorObject = current($subscribeManager->getErrors());
	if($errorObject)
	{
		$listObject->addGroupError($errorObject->getMessage());
	}
}

$headers = array();
$headers['ID'] = array('id' => 'ID', 'content' => 'ID', 'sort' => 'ID', 'default' => true, 'align' => 'center');
$headers['DATE_FROM'] = array('id' => 'DATE_FROM','content' => Loc::getMessage('PSL_DATE_FROM'),
	'sort' => 'DATE_FROM', 'default' => true);
$headers['USER_CONTACT'] = array('id' => 'USER_CONTACT','content' => Loc::getMessage('PSL_USER_CONTACT'),
	'sort' => 'USER_CONTACT', 'default' => true);
$headers['USER_ID'] = array('id' => 'USER_ID', 'content' => Loc::getMessage('PSL_USER'),
	'sort' => 'USER_ID', 'default' => true);
$headers['CONTACT_TYPE'] = array('id' => 'CONTACT_TYPE','content' => Loc::getMessage('PSL_CONTACT_TYPE'),
	'sort' => 'CONTACT_TYPE', 'default' => true, 'align' => 'center');
$headers['ACTIVE'] = array('id' => 'ACTIVE', 'content' => Loc::getMessage('PSL_ACTIVE'),
	'default' => true, 'align' => 'center');
$headers['DATE_TO'] = array('id' => 'DATE_TO','content' => Loc::getMessage('PSL_DATE_TO'),
	'sort' => 'DATE_TO', 'default' => true);
$headers['ITEM_ID'] = array('id' => 'ITEM_ID','content' => Loc::getMessage('PSL_ITEM_ID'),
	'sort' => 'ITEM_ID', 'default' => false, 'align' => 'right');
$headers['PRODUCT_NAME'] = array('id' => 'PRODUCT_NAME','content' => Loc::getMessage('PSL_PRODUCT_NAME'),
	'sort' => 'PRODUCT_NAME', 'default' => true);
$headers['SITE_ID'] = array('id' => 'SITE_ID','content' => Loc::getMessage('PSL_SITE_ID'),
	'sort' => 'SITE_ID', 'default' => true, 'align' => 'center');

$listObject->addHeaders($headers);

$select = array();
$ignoreFields = array('ACTIVE');
$selectFields = array_keys($headers);
$selectFields = array_diff($selectFields, $ignoreFields);
foreach($selectFields as $fieldName)
{
	$select[$fieldName] = $fieldName;
}
$select['PRODUCT_NAME'] = 'IBLOCK_ELEMENT.NAME';
$select['IBLOCK_ID'] = 'IBLOCK_ELEMENT.IBLOCK_ID';

$nav = new Main\UI\AdminPageNavigation('pages-subscription-list');
$queryObject = Catalog\SubscribeTable::getList(array(
	'select' => $select,
	'filter' => $filter,
	'order' => array($by => $order),
	'count_total'=>true,
	'offset' => $nav->getOffset(),
	'limit' => $nav->getLimit()
));
$nav->setRecordCount($queryObject->getCount());
$listObject->setNavigation($nav, Loc::getMessage('PSL_PAGES'));

$contactType = Catalog\SubscribeTable::getContactTypes();
$actionUrl = '&lang='.LANGUAGE_ID;
$listUserData = array();
while($subscribe = $queryObject->fetch())
{
	$subscribe['CONTACT_TYPE'] = $contactType[$subscribe['CONTACT_TYPE']]['NAME'];
	if(!empty($subscribe['USER_ID']))
	{
		$listUserData[$subscribe['USER_ID']][] = $subscribe['ID'];
	}

	$rowList[$subscribe['ID']] = $row = &$listObject->addRow($subscribe['ID'], $subscribe);

	if($subscribeManager->checkSubscriptionActivity($subscribe['DATE_TO']))
	{
		$row->addField('ACTIVE', Loc::getMessage('PSL_FILTER_YES'));
	}
	else
	{
		$row->addField('ACTIVE', Loc::getMessage('PSL_FILTER_NO'));
	}

	if(defined('CATALOG_PRODUCT'))
	{
		$editUrl = CIBlock::getAdminElementEditLink($subscribe['IBLOCK_ID'], $subscribe['ITEM_ID'], array(
			'find_section_section' => -1, 'WF' => 'Y',
			'return_url' => $APPLICATION->getCurPageParam('', array('mode', 'table_id'))));
	}
	else
	{
		$editUrl = CIBlock::getAdminElementEditLink($subscribe['IBLOCK_ID'], $subscribe['ITEM_ID'], array(
			'find_section_section' => -1, 'WF' => 'Y'));
	}
	$row->addField('PRODUCT_NAME',
		'<a href="'.$editUrl.'" target="_blank">'.htmlspecialcharsbx($subscribe['PRODUCT_NAME']).'</a>');

	$actions = array();
	$actionUrl .= '&itemId='.$subscribe['ITEM_ID'];
	$actions[] = array(
		'ICON' => 'delete',
		'TEXT' => Loc::getMessage('PSL_ACTION_DELETE'),
		'ACTION' => "if(confirm('".GetMessageJS('PSL_ACTION_DELETE_CONFIRM')."')) ".
			$listObject->actionDoGroup($subscribe['ID'], 'delete', $actionUrl)
	);
	$actions[] = array(
		'TEXT' => Loc::getMessage('PSL_ACTION_ACTIVATE'),
		'ACTION' => $listObject->actionDoGroup($subscribe['ID'], 'activate')
	);
	$actions[] = array(
		'TEXT' => Loc::getMessage('PSL_ACTION_DEACTIVATE'),
		'ACTION' => $listObject->actionDoGroup($subscribe['ID'], 'deactivate')
	);

	$row->addActions($actions);
}

$listUserId = array_keys($listUserData);
$listUsers = implode(' | ', $listUserId);
$userQuery = CUser::getList($byUser = 'ID', $orderUser = 'ASC',
	array('ID' => $listUsers) ,
	array('FIELDS' => array('ID' ,'LOGIN', 'NAME', 'LAST_NAME')));
while($user = $userQuery->fetch())
{
	if(is_array($listUserData[$user['ID']]))
	{
		foreach($listUserData[$user['ID']] as $subscribeId)
		{
			$userString='<a href="/bitrix/admin/user_edit.php?ID='.$user['ID'].'&lang='.LANGUAGE_ID.'" target="_blank">'.
				CUser::formatName(CSite::getNameFormat(false), $user, true, false).'</a>';
			$rowList[$subscribeId]->addField('USER_ID', $userString);
		}
	}
}

$footerArray = array(array('title' => Loc::getMessage('PSL_LIST_SELECTED'),
	'value' => $queryObject->getCount()));
$listObject->addFooter($footerArray);

$listObject->addGroupActionTable(array(
	'delete' => Loc::getMessage('PSL_ACTION_DELETE'),
	'activate' => Loc::getMessage('PSL_ACTION_ACTIVATE'),
	'deactivate' => Loc::getMessage('PSL_ACTION_DEACTIVATE'),
));

$contextListMenu = array();
$listObject->addAdminContextMenu($contextListMenu);

$listObject->checkListMode();
if($prologAbsent)
	require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');
?>

<form method="GET" name="find_subscribe_form" id="find_subscribe_form" action="<?=$APPLICATION->getCurPageParam()?>">
	<?
	$findFields = array(
		Loc::getMessage('PSL_FILTER_ID'),
		Loc::getMessage('PSL_FILTER_USER_ID'),
		Loc::getMessage('PSL_FILTER_USER_CONTACT'),
		Loc::getMessage('PSL_FILTER_ITEM_ID'),
		Loc::getMessage('PSL_FILTER_DATE_FROM'),
		Loc::getMessage('PSL_FILTER_DATE_TO'),
		Loc::getMessage('PSL_FILTER_CONTACT_TYPE'),
		Loc::getMessage('PSL_FILTER_ACTIVE'),
	);
	$filterUrl = $APPLICATION->getCurPageParam();
	$filterObject = new CAdminFilter($tableId.'_filter', $findFields, array('table_id' => $tableId, 'url' => $filterUrl));
	$filterObject->setDefaultRows(array('find_user_contact', 'find_item_id'));
	$filterObject->begin(); ?>
	<tr>
		<td><?=Loc::getMessage('PSL_FILTER_ID')?></td>
		<td><input type="text" name="find_id" size="11" value="<?=htmlspecialcharsbx($find_id)?>"></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('PSL_FILTER_USER_ID')?></td>
		<td><?=FindUserID('find_user_id', $find_user_id, '', 'find_subscribe_form', '5', '', ' ... ', '', '') ?></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('PSL_FILTER_USER_CONTACT')?></td>
		<td><input type="text" name="find_user_contact" size="40" value="<?=htmlspecialcharsbx($find_user_contact)?>"></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('PSL_FILTER_ITEM_ID')?></td>
		<td><input type="text" name="find_item_id" size="11" value="<?=htmlspecialcharsbx($find_item_id)?>"></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('PSL_FILTER_DATE_FROM')?></td>
		<td><?=CalendarPeriod('find_date_from_1', htmlspecialcharsbx($find_date_from_1),
				'find_date_from_2', htmlspecialcharsbx($find_date_from_2), 'find_subscribe_form', 'Y')?></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('PSL_FILTER_DATE_TO')?></td>
		<td><?=CalendarPeriod('find_date_to_1', htmlspecialcharsbx($find_date_to_1),
				'find_date_to_2', htmlspecialcharsbx($find_date_to_2), 'find_subscribe_form', 'Y')?></td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('PSL_FILTER_CONTACT_TYPE')?></td>
		<td>
			<select name="find_contact_type[]">
				<option value=""><?=Loc::getMessage('PSL_FILTER_ANY')?></option>
				<?
				$contactTypes = !empty($find_contact_type) ? $find_contact_type : array();
				foreach ($contactType as $contactTypeId => $contactTypeData):?>
					<option value="<?=$contactTypeId?>"<?=in_array($contactTypeId, $contactTypes) ? ' selected' : ''?>>
						<?=htmlspecialcharsbx($contactTypeData['NAME']); ?>
					</option>
				<?endforeach; ?>
			</select>
		</td>
	</tr>
	<tr>
		<td><?=Loc::getMessage('PSL_FILTER_ACTIVE')?></td>
		<td>
			<select name="find_active">
				<option value=""><?=Loc::getMessage('PSL_FILTER_ANY')?></option>
				<option value="Y"<?if($find_active=="Y")echo " selected"?>>
					<?=Loc::getMessage('PSL_FILTER_YES')?>
				</option>
				<option value="N"<?if($find_active=="N")echo " selected"?>>
					<?=Loc::getMessage('PSL_FILTER_NO')?>
				</option>
			</select>
		</td>
	</tr>
	<?
	$filterObject->buttons(array('table_id' => $tableId,
		'url' => $APPLICATION->getCurPageParam('', array('ITEM_ID')), 'form' => 'find_subscribe_form'));
	$filterObject->end();
	?>
</form>

<?$listObject->displayList();

if($prologAbsent)
	require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");
