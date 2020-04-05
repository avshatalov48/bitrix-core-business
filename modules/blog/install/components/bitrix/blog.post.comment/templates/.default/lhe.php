<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
// Light Visual BB Editor
if(CModule::IncludeModule("fileman"))
{
	?>
	<script>
	var arImages = Array();
	var arImagesId = Array();
	<?
	$i = 0;
	foreach($arResult["Images"] as $aImg)
	{
		?>
		arImages['<?=$i?>'] = '<?=CUtil::JSEscape($aImg["SRC"])?>';
		arImagesId['<?=$i?>'] = '<?=$aImg["ID"]?>';
		<?
		$i++;
	}
	?>
	</script>
	<?

	function CustomizeLHEForBlogComments()
	{
		?>
		<script>
		LHEButtons['BlogImage'] ={
			id : 'Image', // Standart image icon from editor-s CSS
			name : BX.message.Image,
			handler: function(pBut)
			{
				//pBut.pLEditor.OpenDialog({id : 'BlogImage', obj: false});
				blogShowFile();
			},
			OnBeforeCreate: function(pLEditor, pBut)
				{
					// Disable in non BBCode mode in html
					pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
					return pBut;
				},
			parser: {
				name: 'blogimage',
				obj: {
					Parse: function(sName, sContent, pLEditor)
					{
						var i, cnt = arImages.length, j;
						if (!pLEditor.arBlogImages)
							pLEditor.arBlogImages = {};

						for(i = 0; i < cnt; i++)
						{
							if (!pLEditor.arBlogImages[arImagesId[i]])
							{
								pLEditor.arBlogImages[arImagesId[i]] = {
									src : arImages[i],
									pTitle: ''
								};
							}
						}

						sContent = sContent.replace(/\[IMG ID=((?:\s|\S)*?)(?:\s*?WIDTH=(\d+)\s*?HEIGHT=(\d+))?\]/ig, function(str, id, width, height)
						{
							if (!pLEditor.arBlogImages[id])
								return str;

							width = parseInt(width);
							height = parseInt(height);

							var
								strSize = "",
								imageSrc = pLEditor.arBlogImages[id].src,
								imageTitle = "";

							if (width && height && pLEditor.bBBParseImageSize)
								strSize = " width=\"" + width + "\" height=\"" + height + "\"";

							return '<img id="' + pLEditor.SetBxTag(false, {tag: "blogimage", params: {value : id}}) + '" src="' + imageSrc + '" title="' + imageTitle + '" ' + strSize +'>';
						});
						return sContent;
					},
					UnParse: function(bxTag, pNode, pLEditor)
					{	
						if (bxTag.tag == 'blogimage')
						{
							var
								width = parseInt(pNode.arAttributes['width']),
								height = parseInt(pNode.arAttributes['height']),
								strSize = "";

							if (width && height  && pLEditor.bBBParseImageSize)
								strSize = ' WIDTH=' + width + ' HEIGHT=' + height;

							return '[IMG ID=' + bxTag.params.value + strSize + ']';
						}
						return "";
					}
				}
			}
		};
		
		
		window.LHEDailogs['BlogImage'] = function(pObj)
		{
			var str = 
				'<span class="errortext" id="lhed_blog_image_error" style="display:none;"></span>' +
				'<table width="100%"><tr>' +
				'<td class="lhe-dialog-label lhe-label-imp"><?= GetMessageJS('BLOG_IMAGE')?>:</td>' +
				'<td class="lhe-dialog-param">' +
				'<form id="' + pObj.pLEditor.id + 'img_upload_form" action="<?=CUtil::JSEscape(POST_FORM_ACTION_URI)?>" method="post" enctype="multipart/form-data" style="margin: 0!important; padding: 0!important;">' +
				'<?=bitrix_sessid_post()?>' +
				'<input type="file" size="30" name="BLOG_UPLOAD_FILE" id="bx_lhed_blog_img_input" />' +
				'<input type="hidden" value="Y" name="blog_upload_image"/>' +
				'<input type="hidden" name="post" value="Y">' +
				'</form>'+
				'</td>' +
				'</tr><tr id="' + pObj.pLEditor.id + 'lhed_blog_notice">' +
				'<td colSpan="2" style="padding: 0 0 20px 25px !important; font-size: 11px!important;"><?= GetMessageJS('BPC_IMAGE_SIZE_NOTICE', Array('#SIZE#' => DoubleVal(COption::GetOptionString("blog", "image_max_size", 1000000)/1000000)))?></td>' +
			'</tr></table>';

			return {
				title: "<?= GetMessageJS('BLOG_P_IMAGE_UPLOAD')?>",
				innerHTML : str,
				width: 500,
				OnLoad: function()
				{
					pObj.pForm = false;
					pObj.pInput = false;

					pObj.pInput = BX('bx_lhed_blog_img_input');
					pObj.pForm = BX(pObj.pLEditor.id + 'img_upload_form');
					pObj.pLEditor.focus(pObj.pInput);
					
					window.obLHEDialog.adjustSizeEx();
				},
				OnSave: function()
				{
					if (pObj.pInput && pObj.pForm && pObj.pInput.value != "")
					{
						BX.showWait('bx_lhed_blog_img_input');
						BX('lhed_blog_image_error').style.display = 'none';
						BX('lhed_blog_image_error').innerHTML = '';
						BX.ajax.submit(pObj.pForm, function(){
							BX.closeWait();
							if (window.bxBlogImageId)
							{
								window.InsertBlogImage(window.bxBlogImageId, window.bxBlogImageIdWidth);
								window.obLHEDialog.Close();
								window.bxBlogImageId = false;
								window.bxBlogImageIdWidth = false;
							}
							else if(window.bxBlogImageError)
							{
								BX('lhed_blog_image_error').innerHTML = window.bxBlogImageError;
								BX('lhed_blog_image_error').style.display = 'block';
								window.obLHEDialog.adjustSizeEx();
							}
						});

						return false;
					}
				}
			};
		};

		window.InsertBlogImage = function(imageId, width)
		{
			pLEditor = window.oBlogComLHE;
			var strSize = '';
			
			if (!pLEditor.arBlogImages[imageId])
			{
				var imgId = window.arImagesId[imageId-1];

				var src  = window.arImages[imageId-1];
				imageId = imgId;

				if(width > 0)
				{
					if(pLEditor.arConfig.width && pLEditor.arConfig.width.indexOf('%') <= 0)
						widthC = parseInt(pLEditor.arConfig.width)*0.8;
					else
						widthC = 500;
					if(width > widthC)
						strSize = ' width="80%"';
				}

				pLEditor.arBlogImages[imageId] = {
					src : src,
					pTitle: ''
				};
			}

			if (pLEditor.sEditorMode == 'code' && pLEditor.bBBCode) // BB Codes
				pLEditor.WrapWith("", "", "[IMG ID=" + imageId + "]");
			else if(pLEditor.sEditorMode == 'html') // WYSIWYG
			{
				pLEditor.InsertHTML('<img id="' + pLEditor.SetBxTag(false, {tag: "blogImage", params: {value : imageId}}) + '" src="' + pLEditor.arBlogImages[imageId].src + '" title="' + (pLEditor.arBlogImages[imageId].pTitle.value || "") + '"' + strSize + '>');
				setTimeout('pLEditor.AutoResize();', 500);
			}
		}

		
		// Rename image button and change Icon
		LHEButtons['Image'].id = 'ImageLink';
		LHEButtons['Image'].src = '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_image_upload.gif';
		LHEButtons['Image'].name = '<?=GetMessageJS("BLOG_P_IMAGE_LINK")?>';

		LHEButtons['BlogInputVideo'] = {
			id : 'BlogInputVideo',
			src : '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_video.gif',
			name : '<?=GetMessageJS("FPF_VIDEO")?>',
			title : '<?=GetMessageJS("FPF_VIDEO")?>',
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
				'<td class="lhe-dialog-label lhe-label-imp"><label for="' + pObj.pLEditor.id + 'lhed_blog_video_path"><b><?= GetMessageJS('BPC_VIDEO_P')?>:</b></label></td>' +
				'<td class="lhe-dialog-param">' +
				'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_path" value="" size="30"/>' +
				'</td>' +
			'</tr><tr>' +
				'<td></td>' +
				'<td style="padding: 0!important; font-size: 11px!important;"><?= GetMessageJS('BPC_VIDEO_PATH_EXAMPLE')?></td>' +
			'</tr><tr>' +
				'<td class="lhe-dialog-label lhe-label-imp"><label for="' + pObj.pLEditor.id + 'lhed_blog_video_width">' + BX.message.ImageSizing + ':</label></td>' +
				'<td class="lhe-dialog-param">' +
					'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_width" value="" size="4"/>' +
					' x ' +
					'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_height" value="" size="4" />' +
				'</td>' +
			'</tr></table>';

			return {
				title: "<?= GetMessageJS('FPF_VIDEO')?>",
				innerHTML : str,
				width: 480,
				OnLoad: function()
				{
					pObj.pPath = BX(pObj.pLEditor.id + "lhed_blog_video_path");
					pObj.pWidth = BX(pObj.pLEditor.id + "lhed_blog_video_width");
					pObj.pHeight = BX(pObj.pLEditor.id + "lhed_blog_video_height");

					pObj.pLEditor.focus(pObj.pPath);
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
						setTimeout('pLEditor.AutoResize();', 500);
					}
				}
			};
		};


		LHEButtons['CreateLinkNC'] = {
			id : 'CreateLinkNC',
			src : '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_link.gif',
			name : '<?=GetMessageJS("BPC_LINK")?>',
			title : '<?=GetMessageJS("BPC_LINK")?>',
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
				title: "<?= GetMessageJS('BPC_LINK')?>",
				innerHTML : str,
				width: 480,
				OnLoad: function() {},
				OnSave: function() {}
			};
		};
		
		// Submit form by ctrl+enter
		window.blogCommentCtrlEnterHandler = function(e)
		{
			submitComment();
		};
		</script>
		<?
	}

	AddEventHandler("fileman", "OnIncludeLightEditorScript", "CustomizeLHEForBlogComments");
	$arSmiles = array();
	if(!empty($arResult["Smiles"]))
	{
		foreach($arResult["Smiles"] as $arSmile)
		{
			$arSmiles[] = array(
				'name' => $arSmile["~LANG_NAME"],
				'path' => "/bitrix/images/blog/smile/".$arSmile["IMAGE"],
				'code' => str_replace("\\\\","\\",$arSmile["TYPE"]),
				'codes' => str_replace("\\\\","\\",$arSmile["TYPING"])
			);
		}
	}

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
			(($arResult["allowImageUpload"] == "Y") ? 'BlogImage' : ''),
			(($arResult["allowVideo"] == "Y") ? 'BlogInputVideo' : ''),
			'Table', 'Justify',
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
		'BBCode' => true,
		'bResizable' => $arParams['EDITOR_RESIZABLE'],
		'bAutoResize' => true,
		'autoResizeOffset' => 40,
		'autoResizeMaxHeight' => 300,
		'autoResizeSaveSize' => false,
		'bQuoteFromSelection' => true,
		'ctrlEnterHandler' => 'blogCommentCtrlEnterHandler', // Ctrl+Enter handler name in global namespace
		'bSetDefaultCodeView' => $arParams['EDITOR_CODE_DEFAULT'], // Set first view to CODE or to WYSIWYG
		'bBBParseImageSize' => true // [IMG ID=XXX WEIGHT=5 HEIGHT=6],  [IMGWEIGHT=5 HEIGHT=6]/image.gif[/IMG]
	));
}
?>
<script>
function insertBlogImageFile(id)
{
	img = BX.findChild(BX('wd-doc'+id), {'tag': 'img'}, true, false);
	src = img.getAttribute('rel');
	imageId = id+'file';
	pLEditor = window.oBlogComLHE;
	if (!pLEditor.arBlogImages[imageId])
	{
		pLEditor.arBlogImages[imageId] = {
						src : src,
						pTitle: ""
					};
	}
	pLEditor.SetFocus();
	InsertBlogImage(imageId);
}

BX.ready(function() {	
	BX.addCustomEvent(BX('blog-comment-user-fields-UF_BLOG_COMMENT_DOC'), 'OnFileUploadSuccess', function(result, obj){
		if(BX.findChild(BX('form_comment'), {'attr': {id: 'upload-cid'}}, true, false))
			BX.findChild(BX('form_comment'), {'attr': {id: 'upload-cid'}}, true, false).value = obj.CID;

		if(result.element_content_type.substr(0,6) == 'image/')
		{
			img = BX.findChild(BX('wd-doc'+result.element_id), {'tag': 'img'}, true, false);
			
			el = BX.findChild(BX('wd-doc'+result.element_id), {'className': 'feed-add-img-wrap'}, true, false);
			BX.bind(el, "click", function(){insertBlogImageFile(result.element_id);});
			el.style.cursor = "pointer";
			el.title = "<?=GetMessageJS("MPF_IMAGE_TITLE")?>";
			el = BX.findChild(BX('wd-doc'+result.element_id), {'className': 'feed-add-img-title'}, true, false);
			BX.bind(el, "click", function(){insertBlogImageFile(result.element_id);});
			el.style.cursor = "pointer";
			el.title = "<?=GetMessageJS("MPF_IMAGE_TITLE")?>";
			insertBlogImageFile(result.element_id);
		}
	});

	BX.addCustomEvent(BX('blog-comment-user-fields-UF_BLOG_COMMENT_DOC'), 'OnFileUploadRemove', function(result){
		if(BX.findChild(BX('wd-doc'+result), {'tag': 'img'}, true, false))
		{
			pLEditor = window.oBlogComLHE;
			pLEditor.SaveContent();
			content = pLEditor.GetContent();
			content = content.replace(new RegExp('\\[IMG ID='+result+'file\\]','g'), '');
			pLEditor.SetContent(content);
			pLEditor.SetEditorContent(pLEditor.content);
			pLEditor.SetFocus();
			pLEditor.AutoResize();
		}
	});
});
</script>