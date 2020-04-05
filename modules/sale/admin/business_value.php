<?
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');

$readOnly = $APPLICATION->GetGroupRight('sale') < 'W';

if ($readOnly)
	$APPLICATION->AuthForm(GetMessage('ACCESS_DENIED'));

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/prolog.php');
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/include.php');

use	Bitrix\Sale\BusinessValue;
use	Bitrix\Sale\Helpers\Admin\BusinessValueControl;
use Bitrix\Sale\Internals\BusinessValueTable;
use Bitrix\Sale\Internals\BusinessValuePersonDomainTable;
use	Bitrix\Sale\Internals\Input;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

$adminAjaxHelper->sendJsonSuccessResponse();

require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/sale/lib/helpers/admin/businessvalue.php');

$isSuccess = true;

$businessValueControl = new BusinessValueControl('bizval');

if ($_SERVER['REQUEST_METHOD'] == 'POST'
	&& ! $readOnly
	&& check_bitrix_sessid()
	&& ($_POST['save'] || $_POST['apply']))
{
	if ($isSuccess = $businessValueControl->setMapFromPost())
		$businessValueControl->saveMap();
}

$filter = BusinessValueControl::getFilter(isset($_GET['del_filter']) ? null : ($_GET['FILTER'] ?: $_POST['FILTER']));
$filter['HIDE_FILLED_CODES'] = false;

// VIEW ////////////////////////////////////////////////////////////////////////////////////////////////////////////////

$APPLICATION->SetTitle(Loc::getMessage('BIZVAL_PAGE_TITLE'));

require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_after.php');

if (! $isSuccess)
{
	call_user_func(function () {
		$m = new CAdminMessage(Loc::getMessage('BIZVAL_PAGE_ERRORS'));
		echo $m->Show();
	});
}

$consumerInput = BusinessValueControl::getConsumerInput();
$listConsumer = array();
foreach ($consumerInput["OPTIONS"] as $key => $value)
{
	if (is_array($value))
	{
		foreach ($value as $k => $val)
		{
			$listConsumer[$k] = $val;
		}
	}
	else
	{
		$listConsumer[$key] = $value;
	}
}

$sTableID = "tbl_sale_business_value";
$oSort = new CAdminSorting($sTableID, "ID", "asc");
$lAdmin = new CAdminUiList($sTableID, $oSort);

$filterFields = array(
	array(
		"id" => "CONSUMER_KEY",
		"name" => GetMessage("BIZVAL_PAGE_CODES"),
		"type" => "list",
		"items" => $listConsumer,
		"filterable" => "",
		"default" => true
	),
);

$lAdmin->AddFilter($filterFields, $filter);

$lAdmin->DisplayFilter($filterFields);

$actionParams = '?lang='.LANGUAGE_ID;
if ($adminSidePanelHelper->isSidePanel())
{
	$actionParams .= "&IFRAME=Y&IFRAME_TYPE=SIDE_SLIDER";
}

?>
	<script type="text/javascript">
		if (!window['filter_<?=$sTableID?>'] || !BX.is_subclass_of(window['filter_<?=$sTableID?>'], BX.adminUiFilter))
		{
			window['filter_<?=$sTableID?>'] = new BX.adminUiFilter('<?=$sTableID?>', <?=CUtil::PhpToJsObject(array())?>);
		}
	</script>

	<form method="POST" id="bizvalTabs_form" name="bizvalTabs_form" action="
		<?=$APPLICATION->GetCurPage().$actionParams?>" enctype="multipart/form-data">

		<?=bitrix_sessid_post()?>

		<input type="hidden" name="FILTER[CODE_KEY]" value="<?=$filter['CODE_KEY']?>">
		<input type="hidden" name="FILTER[CONSUMER_KEY]" value="<?=$filter['CONSUMER_KEY']?>">
		<input type="hidden" name="FILTER[PROVIDER_KEY]" value="<?=$filter['PROVIDER_KEY']?>">
		<input type="hidden" name="FILTER[PROVIDER_VALUE]" value="<?=$filter['PROVIDER_VALUE']?>">

		<?$businessValueControl->renderMap($filter)?>

		<div class="adm-detail-content-btns-wrap">
			<div class="adm-detail-content-btns">
				<?
				echo '<input'.($aParams["disabled"] === true? " disabled":"")
						.' type="submit" name="apply" value="'.GetMessage("admin_lib_edit_apply").'" title="'
						.GetMessage("admin_lib_edit_apply_title").'" class="adm-btn-save" />';

				?>
			</div>
		</div>

	</form>
<?
require($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/epilog_admin.php');
