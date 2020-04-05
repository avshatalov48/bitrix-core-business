<?

use Bitrix\Main\Config\Option;
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
	<input type="hidden" name="install" value="Y">
	<input type="hidden" name="step" value="2">

	<table cellpadding="3" cellspacing="0" border="0">
		<?

		if (Option::get('conversion', 'GENERATE_INITIAL_DATA', 'undefined') == 'undefined')
		{
			?>
			<tr>
				<td>
					<input type="checkbox" name="GENERATE_INITIAL_DATA" value="Y" id="GENERATE_INITIAL_DATA">
				</td>
				<td>
					<p>
						<label for="GENERATE_INITIAL_DATA"><?=Loc::getMessage('CONVERSION_GENERATE_INITIAL_DATA')?></label>
					</p>
				</td>
			</tr>
			<?
		}

		?>
	</table>

	<br>
	<input type="submit" name="" value="<?=Loc::getMessage('MOD_INSTALL_BUTTON')?>">

</form>
