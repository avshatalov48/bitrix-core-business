<?
use \Bitrix\Main\Localization\Loc;
use \Bitrix\Sale\PaySystem;

Loc::loadMessages(__FILE__);

\Bitrix\Main\Loader::includeModule('sale');

global $APPLICATION;

$application = \Bitrix\Main\Application::getInstance();
$context = $application->getContext();
$request = $context->getRequest();
$shopId = $request->get("shop_id");
$companyName = $request->get("company_name");
$handler = $request->get("handler");
$errorMsg = '';

\CUtil::InitJSCore();

if ($request->get("csr") === 'Y')
{
	PaySystem\YandexCert::getCsr($shopId);
}

if ($request->get("generate") === 'Y')
{
	$companyName = $request->get('company_name');
	if ($companyName && !preg_match('/[^a-zA-Z]+/', $companyName))
	{
		PaySystem\YandexCert::generate($shopId, $companyName);
		LocalRedirect($APPLICATION->GetCurPage().'?shop_id='.$shopId."&handler=".$handler.'&lang='.LANG);
	}
	else
	{
		$errorMsg = Loc::getMessage('SALE_YANDEX_RETURN_ERROR_CN');
	}
}

if (($request->getPost("Update") || $request->getPost("Apply")) && check_bitrix_sessid())
{
	if ($request->get('SETTINGS_CLEAR') || $request->get('SETTINGS_CLEAR_ALL'))
	{
		$all = $request->get('SETTINGS_CLEAR_ALL') !== null;
		PaySystem\YandexCert::clear($shopId, $all);
	}

	$certFile = $request->getFile("CERT_FILE");
	if (file_exists($certFile['tmp_name']))
		PaySystem\YandexCert::setCert($certFile, $shopId);

	if (PaySystem\YandexCert::$errors)
	{
		foreach (PaySystem\YandexCert::$errors as $error)
			$errorMsg .= $error."<br>\n";
	}

	if ($errorMsg === '')
	{
		LocalRedirect($APPLICATION->GetCurPage().'?shop_id='.$shopId.'&handler='.$handler.'&lang='.LANG);
	}
}

if ($errorMsg !== '')
	CAdminMessage::ShowMessage(array("DETAILS"=>$errorMsg, "TYPE"=>"ERROR", "HTML"=>true));

$personTypeTabs = array();
$personTypeTabs[] = array(
	"PERSON_TYPE" => 0,
	"DIV" => 0,
	"TAB" => Loc::getMessage('SALE_YANDEX_RETURN_PT'),
	"TITLE" => Loc::getMessage("SALE_YANDEX_RETURN_TITLE")
);

$tabRControl = new \CAdminTabControl("tabRControl", $personTypeTabs);
$tabRControl->Begin();?>

<form method="POST" enctype="multipart/form-data"
	  action="<?=$APPLICATION->GetCurPage()?>?shop_id=<?=$shopId;?>&handler=<?=$handler;?>&lang=<?echo LANG?>"
	  xmlns="http://www.w3.org/1999/html">
<?
	echo bitrix_sessid_post();
	$tabRControl->BeginNextTab();

	$strCN = PaySystem\YandexCert::getCn($shopId);
?>
	<tr class="heading">
		<td colspan="2"><?=Loc::getMessage('SALE_YANDEX_RETURN_SUBTITLE');?></td>
	</tr>
	<tr>
		<td width="40%" class="adm-detail-content-cell-l"><?=Loc::getMessage("SALE_YANDEX_RETURN_CERT")?>:</td>

		<td width="60%" class="adm-detail-content-cell-r">
			<?if (!PaySystem\YandexCert::isLoaded($shopId)):?>
				<input type="file" name="CERT_FILE" size="40"><br>
				<?=Loc::getMessage('SALE_YANDEX_RETURN_TEXT_CLEAR_ALL')?>
				<input id=SETTINGS_CLEAR_ALL' type="checkbox" name='SETTINGS_CLEAR_ALL'>
			<?else:?>
				<?=Loc::getMessage('SALE_YANDEX_RETURN_TEXT_SUCCESS')?><br>
				<?=Loc::getMessage('SALE_YANDEX_RETURN_TEXT_CLEAR')?>
				<input id='SETTINGS_CLEAR' type="checkbox" name='SETTINGS_CLEAR'>
			<?endif;?>
			<br>
		</td>
	</tr>
	<tr>
		<td colspan="2"><?=Loc::getMessage("SALE_YANDEX_RETURN_HELP")?></td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=Loc::getMessage("SALE_YANDEX_RETURN_HOW")?></td>
	</tr>
	<tr>
		<td colspan="2">
			<ol>
				<li><?=Loc::getMessage("SALE_YANDEX_RETURN_HOW_ITEM0");?></li>
				<li><?=Loc::getMessage("SALE_YANDEX_RETURN_HOW_ITEM1")?></li>
				<li><?=Loc::getMessage("SALE_YANDEX_RETURN_HOW_ITEM2")?></li>
				<li><?=Loc::getMessage("SALE_YANDEX_RETURN_HOW_ITEM3")?></li>
				<li><?=Loc::getMessage("SALE_YANDEX_RETURN_HOW_ITEM4")?></li>
				<li><?=Loc::getMessage("SALE_YANDEX_RETURN_HOW_ITEM5")?></li>
			</ol>
		</td>
	</tr>
	<tr class="heading">
		<td colspan="2"><?=Loc::getMessage("SALE_YANDEX_RETURN_STATEMENT")?></td>
	</tr>

	<? if ($strCN):?>
		<tr>
			<td class="adm-detail-valign-top adm-detail-content-cell-l"><strong><?=Loc::getMessage("SALE_YANDEX_RETURN_STATEMENT_CN")?></strong>:</td>
			<td class="adm-detail-content-cell-r"><?=$strCN?></td>
		</tr>
		<tr>
			<td class="adm-detail-valign-top adm-detail-content-cell-l"><strong><?=Loc::getMessage("SALE_YANDEX_RETURN_CSR")?></strong>:</td>
			<td class="adm-detail-content-cell-r"><?=sprintf(Loc::getMessage("SALE_YANDEX_RETURN_CSR_DOWNLOAD"), $APPLICATION->GetCurPage()."?lang=ru&csr=Y&shop_id=".$shopId."&handler=".$handler)?></td>
		</tr>
		<tr>
			<td class="adm-detail-valign-top adm-detail-content-cell-l"><strong><?=Loc::getMessage("SALE_YANDEX_RETURN_STATEMENT_SIGN")?></strong>:</td>
			<td class="adm-detail-content-cell-r">
				<textarea cols="55" disabled="" rows="13" >
					<?=PaySystem\YandexCert::getSign($shopId)?>
				</textarea>
			</td>
		</tr>
		<tr>
			<td class="adm-detail-valign-top adm-detail-content-cell-l"><strong><?=Loc::getMessage("SALE_YANDEX_RETURN_STATEMENT_CAUSE")?></strong>:</td>
			<td class="adm-detail-content-cell-r"><?=Loc::getMessage("SALE_YANDEX_RETURN_STATEMENT_CAUSE_VAL")?></td>
		</tr>
	<?else:?>
		<tr align="center">
			<td class="adm-detail-valign-top" colspan="2">
				<?=Loc::getMessage('SALE_YANDEX_RETURN_COMPANY_NAME')?>: <input type="text" name="company_name" value="">
			</td>
		</tr>
		<tr align="center">
			<td class="adm-detail-valign-top" colspan="2">
				<input type="submit" name="generate" value="<?=Loc::getMessage("SALE_YANDEX_RETURN_GENERATE")?>">
				<input type="hidden" name="generate" value="Y">
			</td>
		</tr>
	<?endif;?>

	<?$tabRControl->EndTab();?>

	<? if ($strCN):?>
		<?$tabRControl->Buttons();?>
		<input type="submit" name="Update" value="<?=Loc::getMessage("SALE_YANDEX_RETURN_SAVE")?>">
		<input type="hidden" name="Update" value="Y">
	<?endif;?>
</form>

<?$tabRControl->End();