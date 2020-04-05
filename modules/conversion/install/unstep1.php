<?

use Bitrix\Main\Localization\Loc;

if (! check_bitrix_sessid())
{
	return;
}

?>
<form action="<?=$APPLICATION->GetCurPage()?>">

	<?=bitrix_sessid_post()?>
	<input type="hidden" name="lang" value="<?=LANG?>">
	<input type="hidden" name="id" value="conversion">
	<input type="hidden" name="uninstall" value="Y">
	<input type="hidden" name="step" value="2">

	<table cellpadding="3" cellspacing="0" border="0">
		<tr>
			<td>
				<input type="checkbox" name="SAVE_TABLES" value="Y" checked id="SAVE_TABLES">
			</td>
			<td>
				<p>
					<label for="SAVE_TABLES"><?=Loc::getMessage('MOD_UNINST_SAVE_TABLES')?></label>
				</p>
			</td>
		</tr>
	</table>

	<br>
	<input type="submit" name="" value="<?=Loc::getMessage('MOD_DELETE')?>">

</form>
