<?if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die();
/** @var array $arParams */
/** @var array $arResult */
/** @global CMain $APPLICATION */
/** @global CUser $USER */
/** @global CDatabase $DB */
/** @var CBitrixComponentTemplate $this */
/** @var string $templateName */
/** @var string $templateFile */
/** @var string $templateFolder */
/** @var string $componentPath */
/** @var \Bitrix\Disk\Internals\BaseComponent $component */
CJSCore::Init(array("core"));

$uploadedFile = <<<HTML
<div class="mobile-grid-field-file-item mobile-grid-field-file-#class#" id="diskuf-#id#">
	<div class="mobile-grid-field-file-item-inner">
		<span class="mobile-grid-field-file-preview">
			<span class="files-preview-border"><span class="files-preview-alignment">
				<img class="files-preview" src="#preview_url#" />
			</span></span>
		</span>
		<span class="mobile-grid-field-file-icon icon icon-#ext#"></span>
		<span class="mobile-grid-field-file-name">#name#</span>
		<span class="mobile-grid-field-file-size">#size#</span>
	</div>
</div>
HTML;
$uploadedFile =  preg_replace("/[\n\t]+/", "", $uploadedFile);

?><div id="diskuf-placeholder-<?=$arResult['UID']?>"><?
foreach ($arResult['FILES'] as $file)
{
	if (array_key_exists("IMAGE", $file))
	{
		CFile::ScaleImage(
			$file["IMAGE"]["WIDTH"],
			$file["IMAGE"]["HEIGHT"],
			\Bitrix\Disk\Uf\Controller::$previewParams,
			BX_RESIZE_IMAGE_PROPORTIONAL,
			$bNeedCreatePicture,
			$arSourceSize,
			$arDestinationSize
		);
		$file["width"] = $arDestinationSize["width"];
		$file["height"] = $arDestinationSize["height"];
	}
	$f = $uploadedFile;
	$pat = array("#uid#", "#class#");
	$rep = array($arResult['UID'], (array_key_exists("IMAGE", $file) ? "image" : "file"));
	foreach ($file as $k => $v)
	{
		if($k == 'EXTENSION')
			$k = 'ext';
		$pat[] = "#".mb_strtolower($k)."#"; $rep[] = $v;
	}
	?><?=str_ireplace($pat, $rep, $f);
}
?>
	</div>
<script type="text/javascript">
BX.ready(function(){
	BX.Disk.UFMobile.addView({
		UID : '<?=$arResult['UID']?>'
	});
});
</script>