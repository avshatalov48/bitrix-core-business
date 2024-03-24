<?php

use Bitrix\Conversion\Config;
use Bitrix\Currency\CurrencyManager;
use Bitrix\Main\Loader;
use Bitrix\Main\Localization\Loc;

Loc::loadMessages($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/options.php');
Loc::loadMessages(__FILE__);

$MOD_RIGHT = $APPLICATION->getGroupRight('conversion');
if ($MOD_RIGHT < 'R')
	return;

$modules = Config::getModules();

// TODO all modules with attributes must be active
unset($modules['conversion'], $modules['abtest'], $modules['sender'], $modules['seo']);

$currency = Config::getBaseCurrency();

if (! (Loader::includeModule('currency') && ($currencies = CurrencyManager::getCurrencyList())))
{
	$currencies = array($currency => $currency);
}

if ($MOD_RIGHT >= 'W' && check_bitrix_sessid())
{
	if ($REQUEST_METHOD == 'POST' && $Update.$Apply.$RestoreDefaults <> '')
	{
		if ($RestoreDefaults <> '')
		{
			Config::setBaseCurrency(null);
			$currency = Config::getBaseCurrency();

			Config::setModules(array());
			$modules = Config::getModules();
		}
		else
		{
			if ($currencies[$_POST['CURRENCY']])
			{
				$currency = $_POST['CURRENCY'];
				Config::setBaseCurrency($currency);
			}

			foreach ($modules as $name => $config)
			{
				$modules[$name]['ACTIVE'] = isset($_POST['MODULE'][$name]['ACTIVE']);
			}
			Config::setModules($modules);
		}

	//	if(strlen($Update)>0 && strlen($_REQUEST["back_url_settings"])>0)
	//		LocalRedirect($_REQUEST["back_url_settings"]);
	//	else
	//		LocalRedirect($APPLICATION->GetCurPage()."?mid=".urlencode($mid)."&lang=".urlencode(LANGUAGE_ID)."&back_url_settings=".urlencode($_REQUEST["back_url_settings"])."&".$tabControl->ActiveTabParam());
	}
}

// VIEW

$tabControl = new CAdminTabControl('tabControl', array(
	array('DIV' => 'edit1', 'TAB' => Loc::getMessage('MAIN_TAB_SET'), 'ICON' => 'ib_settings', 'TITLE' => Loc::getMessage('MAIN_TAB_TITLE_SET')),
	array('DIV' => 'edit2', 'TAB' => Loc::getMessage('CONVERSION_TAB_MODULES_NAME'), 'ICON' => 'ib_settings', 'TITLE' => Loc::getMessage('CONVERSION_TAB_MODULES_DESC')),
));

$tabControl->Begin();

// If saved currency in 'conversion' module, not exist in currency list, then show empty currency.
if (!isset($currencies[$currency]))
{
	$currency = '';
	$currencies = ['' => Loc::getMessage('CONVERSION_CURRENCY_NOT_SELECTED')] + $currencies;
}

?>
<form method="post" action="<?=$APPLICATION->GetCurPage()?>?mid=<?=urlencode($mid)?>&amp;lang=<?echo LANGUAGE_ID?>">
	<?=bitrix_sessid_post()?>

	<?$tabControl->BeginNextTab()?>

	<tr>
		<td width="40%"><?=Loc::getMessage('CONVERSION_CURRENCY')?>:</td>
		<td width="60%">
			<select name="CURRENCY">
				<?

				foreach ($currencies as $key => $name)
				{
					?><option value="<?=$key?>"<?=$key == $currency ? ' selected' : ''?>><?=htmlspecialcharsex($name)?></option><?
				}

				?>
			</select>
		</td>
	</tr>

	<?$tabControl->BeginNextTab()?>

	<tr>
		<td width="40%"><strong><?=Loc::getMessage('CONVERSION_TAB_MODULES_MODULE_NAME')?></strong></td>
		<td width="60%"><strong><?=Loc::getMessage('CONVERSION_TAB_MODULES_ACTIVE')?></strong></td>
	</tr>
	<?

	foreach ($modules as $name => $config)
	{
		?>
		<tr>
			<td width="40%">
				<?

				$title = $name;
				if ($info = \CModule::createModuleObject($name))
				{
					if (!empty($info->MODULE_NAME))
						$title = $info->MODULE_NAME;
				}

				echo $title;

				?>
			</td>
			<td width="60%">
				<input type="checkbox" name="MODULE[<?=$name?>][ACTIVE]" value="1"<?=$config['ACTIVE'] ? ' checked' : ''?>>
			</td>
		</tr>
		<?
	}

	?>

	<?$tabControl->Buttons()?>

	<input type="submit" name="Update" <? if ($MOD_RIGHT < 'W') echo 'disabled'; ?> value="<?=GetMessage("MAIN_SAVE")?>" title="<?=GetMessage("MAIN_OPT_SAVE_TITLE")?>" class="adm-btn-save">
	<input type="submit" name="Apply" <? if ($MOD_RIGHT < 'W') echo 'disabled'; ?> value="<?=GetMessage("MAIN_OPT_APPLY")?>" title="<?=GetMessage("MAIN_OPT_APPLY_TITLE")?>">
	<?if($_REQUEST["back_url_settings"] <> ''):?>
		<input type="button" name="Cancel" value="<?=GetMessage("MAIN_OPT_CANCEL")?>" title="<?=GetMessage("MAIN_OPT_CANCEL_TITLE")?>" onclick="window.location='<?echo htmlspecialcharsbx(CUtil::addslashes($_REQUEST["back_url_settings"]))?>'">
		<input type="hidden" name="back_url_settings" value="<?=htmlspecialcharsbx($_REQUEST["back_url_settings"])?>">
	<?endif?>
	<input type="submit" name="RestoreDefaults" <? if ($MOD_RIGHT < 'W') echo 'disabled'; ?> title="<?echo GetMessage("MAIN_HINT_RESTORE_DEFAULTS")?>" OnClick="return confirm('<?echo AddSlashes(GetMessage("MAIN_HINT_RESTORE_DEFAULTS_WARNING"))?>')" value="<?echo GetMessage("MAIN_RESTORE_DEFAULTS")?>">

	<?$tabControl->End()?>
</form>
