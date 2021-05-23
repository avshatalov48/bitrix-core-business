<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
// Light Visual BB Editor
if(CModule::IncludeModule("fileman"))
{
	?>
	<script>
	// Submit form by ctrl+enter
	window.blogCommentCtrlEnterHandler = function(e)
	{
		submitComment();
	};
	</script>
	<?
	$LHE = new CLightHTMLEditor;
	$LHE->Show(array(
		'id' => 'LHEPhotoBlogCom',
		'width' => $arParams['EDITOR_WIDTH'],
		'height' => $arParams['EDITOR_DEFAULT_HEIGHT'],
		'inputId' => 'comment',
		'inputName' => 'comment',
		'content' => "",
		'bUseFileDialogs' => false,
		'bUseMedialib' => false,
		'toolbarConfig' => array(
			'Bold', 'Italic', 'Underline', 'Strike', 'Quote'
			//'Source'
		),
		'jsObjName' => 'oBlogComLHE',
		'bSaveOnBlur' => false,
		'BBCode' => true,
		'bResizable' => false,
		'bQuoteFromSelection' => true,
		'ctrlEnterHandler' => 'blogCommentCtrlEnterHandler', // Ctrl+Enter handler name in global namespace
		'bSetDefaultCodeView' => false, // Set first view to CODE or to WYSIWYG
		'bBBParseImageSize' => true // [IMG ID=XXX WEIGHT=5 HEIGHT=6],  [IMGWEIGHT=5 HEIGHT=6]/image.gif[/IMG]
	));
}
?>