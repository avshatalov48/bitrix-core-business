<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
// Light Visual BB Editor
CModule::IncludeModule("fileman");
if(!function_exists('CustomizeLHEForBlogComments')):
	function CustomizeLHEForBlogComments()
	{
			?>
			<script>
			// Rename image button and change Icon
			LHEButtons['Image'].id = 'ImageLink';
			LHEButtons['Image'].src = '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_image_upload.gif';
			LHEButtons['Image'].name = '<?=GetMessage("BLOG_P_IMAGE_LINK")?>';

			LHEButtons['BlogInputVideo'] = {
					id : 'BlogInputVideo',
					src : '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_video.gif',
					name : '<?=GetMessage("FPF_VIDEO")?>',
					title : '<?=GetMessage("FPF_VIDEO")?>',
					handler: function(pBut)
					{
							pBut.pLEditor.OpenDialog({id : 'BlogVideo', obj: false});
					},
					parser: {
							name: 'blogvideo',
							obj: {
									Parse: function(sName, sContent, pLEditor)
									{
											sContent = sContent.replace(/\[VIDEO\s*?width=(\d+)\s*?height=(\d+)\s*\]((?:\s|\S)*?)\[\/VIDEO\]/ig, function(str, w, h, src)
											{
													var
															w = parseInt(w) || 400,
															h = parseInt(h) || 300,
															src = BX.util.trim(src);

													return '<img id="' + pLEditor.SetBxTag(false, {tag: "blogvideo", params: {value : src}}) + '" src="/bitrix/images/1.gif" class="bxed-video" width=' + w + ' height=' + h + ' title="' + BX.message.Video + ": " + src + '" />';
											});
											return sContent;
									},
									UnParse: function(bxTag, pNode, pLEditor)
									{
											if (bxTag.tag == 'blogvideo')
											{
													return "[VIDEO WIDTH=" + pNode.arAttributes["width"] + " HEIGHT=" + pNode.arAttributes["height"] + "]" + bxTag.params.value + "[/VIDEO]";
											}
											return "";
									}
							}
					}
			};

			window.LHEDailogs['BlogVideo'] = function(pObj)
			{
					var str = '<table width="100%"><tr>' +
							'<td class="lhe-dialog-label lhe-label-imp"><label for="' + pObj.pLEditor.id + 'lhed_blog_video_path"><b><?= GetMessage('BPC_VIDEO_P')?>:</b></label></td>' +
							'<td class="lhe-dialog-param">' +
							'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_path" value="" size="30"/>' +
							'</td>' +
					'</tr><tr>' +
							'<td></td>' +
							'<td style="padding: 0!important; font-size: 11px!important;"><?= GetMessage('BPC_VIDEO_PATH_EXAMPLE')?></td>' +
					'</tr><tr>' +
							'<td class="lhe-dialog-label lhe-label-imp"><label for="' + pObj.pLEditor.id + 'lhed_blog_video_width">' + BX.message.ImageSizing + ':</label></td>' +
							'<td class="lhe-dialog-param">' +
									'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_width" value="" size="4"/>' +
									' x ' +
									'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_height" value="" size="4" />' +
							'</td>' +
					'</tr></table>';

					return {
							title: "<?= GetMessage('FPF_VIDEO')?>",
							innerHTML : str,
							width: 480,
							OnLoad: function()
							{
									pObj.pPath = BX(pObj.pLEditor.id + "lhed_blog_video_path");
									pObj.pWidth = BX(pObj.pLEditor.id + "lhed_blog_video_width");
									pObj.pHeight = BX(pObj.pLEditor.id + "lhed_blog_video_height");
							},
							OnSave: function()
							{
									pLEditor = window.oBlogComLHE;

									var
											src = BX.util.trim(pObj.pPath.value),
											w = parseInt(pObj.pWidth.value) || 400,
											h = parseInt(pObj.pHeight.value) || 300;

									if (src == "")
											return;

									if (pLEditor.sEditorMode == 'code' && pLEditor.bBBCode) // BB Codes
									{
											pLEditor.WrapWith("", "", "[VIDEO WIDTH=" + w + " HEIGHT=" + h + "]" + src + "[/VIDEO]");
									}
									else if(pLEditor.sEditorMode == 'html') // WYSIWYG
									{
											pLEditor.InsertHTML('<img id="' + pLEditor.SetBxTag(false, {tag: "blogvideo", params: {value : src}}) + '" src="/bitrix/images/1.gif" class="bxed-video" width=' + w + ' height=' + h + ' title="' + BX.message.Video + ": " + src + '" />');
									}
							}
					};
			};


			LHEButtons['CreateLinkNC'] = {
					id : 'CreateLinkNC',
					src : '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_link.gif',
					name : '<?=GetMessage("BPC_LINK")?>',
					title : '<?=GetMessage("BPC_LINK")?>',
					handler: function(pBut)
					{
							pBut.pLEditor.OpenDialog({id : 'CreateLinkNCDialog', obj: false});
					}
			};

			window.LHEDailogs['CreateLinkNCDialog'] = function(pObj)
			{
					var str = "";
					if(document.getElementById('nocommentreason'))
							str = document.getElementById('nocommentreason').innerHTML;

					return {
							title: "<?= GetMessage('BPC_LINK')?>",
							innerHTML : str,
							width: 480,
							OnLoad: function() {},
							OnSave: function() {}
					};
			};

			// Submit form by ctrl+enter
			window.blogCommentCtrlEnterHandler = function(e)
			{
					oBlogComLHE.SaveContent();
					if (document.forms.form_comment)
							document.forms.form_comment.submit();
			};

			document.forms.form_comment.onsubmit = function()
			{
					oBlogComLHE.SaveContent();
			};
			</script>
			<?
	}

	AddEventHandler("fileman", "OnIncludeLightEditorScript", "CustomizeLHEForBlogComments");
endif;

$arSmiles = array();
if(!empty($arResult["Smiles"]))
{
		foreach($arResult["Smiles"] as $arSmile)
		{
				$arSmiles[] = array(
						'name' => $arSmile["~LANG_NAME"],
						'path' => $arSmile["IMAGE"],
						'code' => str_replace("\\\\","\\",$arSmile["TYPE"])
				);
		}
}
rsort($arSmiles);
$LHE = new CLightHTMLEditor;

$LHE->Show(array(
		'id' => 'LHEBlogCom',
		'height' => $arParams['EDITOR_DEFAULT_HEIGHT'],
		'inputId' => 'comment',
		'inputName' => 'comment',
		'content' => "",
		'bUseFileDialogs' => false,
		'bUseMedialib' => false,
		'toolbarConfig' => array(
				'Bold', 'Italic', 'Underline', 'Strike',
				'ForeColor','FontList', 'FontSizeList',
				'RemoveFormat',
				'Quote', 'Code',
				((!$arResult["NoCommentUrl"]) ? 'CreateLink' : 'CreateLinkNC'),
				((!$arResult["NoCommentUrl"]) ? 'DeleteLink' : ''),
				'Image',
				//'BlogImage',
				(($arResult["allowVideo"] == "Y") ? 'BlogInputVideo' : ''),
				'Table',
				'InsertOrderedList',
				'InsertUnorderedList',
				//'Translit',
				'SmileList',
				'Source'
		),
		'jsObjName' => 'oBlogComLHE',
		'arSmiles' => $arSmiles,
		'smileCountInToolbar' => $arParams['SMILES_COUNT'],
		'bSaveOnBlur' => false,
		//'BBCode' => !$arResult["allow_html"],
		'BBCode' => true,
		'bResizable' => $arParams['EDITOR_RESIZABLE'],
		'bQuoteFromSelection' => true,
		'ctrlEnterHandler' => 'blogCommentCtrlEnterHandler', // Ctrl+Enter handler name in global namespace
		'bSetDefaultCodeView' => $arParams['EDITOR_CODE_DEFAULT'], // Set first view to CODE or to WYSIWYG
		'bBBParseImageSize' => true // [IMG ID=XXX WEIGHT=5 HEIGHT=6],  [IMGWEIGHT=5 HEIGHT=6]/image.gif[/IMG]
));
?>