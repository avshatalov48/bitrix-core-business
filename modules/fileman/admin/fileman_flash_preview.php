<?
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_before.php");
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/prolog.php");

if (!$USER->CanDoOperation('fileman_view_file_structure'))
	$APPLICATION->AuthForm(GetMessage("ACCESS_DENIED"));

require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/fileman/include.php");

if(CModule::IncludeModule("compression"))
	CCompress::Disable2048Spaces();

if (isset($_GET['path']))
{
	$flashExtensions = array("swf", "spl");
	$extension = GetFileExtension(strtolower($path));

	if(!in_array($extension, $flashExtensions))
		return false;

	$site = CFileMan::__CheckSite($site);
	if(!$site)
		$site = CSite::GetSiteByFullPath($_SERVER["DOCUMENT_ROOT"].$path);

	$io = CBXVirtualIo::GetInstance();

	$documentRoot = CSite::GetSiteDocRoot($site);
	$path = $io->CombinePath("/", $path);
	$abs_path = $documentRoot.$path;
	$arPath = Array($site, $path);

	if ($io->FileExists($abs_path) && $USER->CanDoFileOperation('fm_view_file', $arPath))
	{
		$width = isset($width) ? 'width="'.htmlspecialcharsex($width).'"' : '';
		$height = isset($height) ? 'height="'.htmlspecialcharsex($height).'"' : '';
		?>
<HTML><HEAD></HEAD><BODY>
<embed id="flash_preview" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" name="preview_flash"
quality="high" <?=$width?> <?=$height?> src="<?=htmlspecialcharsex($path)?>" />
</BODY></HTML>
		<?
	}
}
?>