<?
/**
 * @global \CUser $USER
 * @global \CMain $APPLICATION
 * @global \CDatabase $DB
 */

use Bitrix\Main\Localization\Loc;
use Bitrix\Main\UI\Copyright;
use Bitrix\Main\Text\HtmlFilter;

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");

$APPLICATION->SetTitle(GetMessage("main_copyright_title"));
require_once($_SERVER["DOCUMENT_ROOT"].BX_ROOT."/modules/main/include/prolog_admin_after.php");

\Bitrix\Main\UI\Extension::load("ui.dialogs.messagebox");
?>
<div style="
	padding: 0 20px 20px 20px;
    border: solid 1px #c5cecf;
    border-radius: 4px;
    background: #fff;
">

<h2><?echo Loc::getMessage("main_copyright_license")?></h2>

	<div style="
	    padding: 20px;
		border: 1px solid #3bc8f5;
		border-radius: 4px;
	">
	<?
	$bitrixCopyright = Copyright::getBitrixCopyright();

	echo Loc::getMessage("main_copyright_bitrix_license", [
		"#PRODUCT#" => $bitrixCopyright->getProductName(),
		"#URL#" => '<a href="'.HtmlFilter::encode($bitrixCopyright->getLicenceUrl()).'" target="_blank">',
		"#/URL#" => '</a>',
	])?>
	</div>

<h2><?echo Loc::getMessage("main_copyright_3d_party")?></h2>

<table class="list-table">
	<tr class="heading">
		<td><?echo Loc::getMessage("main_copyright_program")?></td>
		<td><?echo Loc::getMessage("main_copyright_owner")?></td>
		<td><?echo Loc::getMessage("main_copyright_program_license")?></td>
	</tr>
<?foreach(Copyright::getThirdPartySoftware() as $i => $software):?>
	<tr>
		<td><?
			if($software->getProductUrl())
			{
				echo '<a href="'.HtmlFilter::encode($software->getProductUrl()).'" target="_blank">'.HtmlFilter::encode($software->getProductName()).'</a>';
			}
			else
			{
				echo HtmlFilter::encode($software->getProductName());
			}
			?></td>
		<td><?
			if($software->getCopyrightUrl())
			{
				echo '<a href="'.HtmlFilter::encode($software->getCopyrightUrl()).'" target="_blank">'.HtmlFilter::encode($software->getCopyright()).'</a>';
			}
			else
			{
				echo HtmlFilter::encode($software->getCopyright());
			}
			?></td>
		<td><?
			if($software->getLicenceUrl())
			{
				echo '<a href="'.HtmlFilter::encode($software->getLicenceUrl()).'" target="_blank">'.HtmlFilter::encode($software->getLicence()).'</a>';
			}
			else
			{
				if(($text = $software->getLicenceText()))
				{
					echo '<div id="bx_licence_'.$i.'" style="display: none;"><div style="font-family: monospace; font-size: small; max-height: 500px;">'.nl2br(HtmlFilter::encode($text)).'</div></div>';
					echo '<a href="javascript:void(0);" onclick="BX.UI.Dialogs.MessageBox.alert(BX(\'bx_licence_'.$i.'\').innerHTML, \''.CUtil::JSEscape(Loc::getMessage("main_copyright_popup_title")).'\');">'.HtmlFilter::encode($software->getLicence()).'</a>';
				}
				else
				{
					echo HtmlFilter::encode($software->getLicence());
				}
			}
			?></td>
	</tr>
<?endforeach;?>
</table>

</div>

<?require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php");