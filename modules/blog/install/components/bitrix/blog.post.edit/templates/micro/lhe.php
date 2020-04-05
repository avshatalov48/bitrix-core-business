<?if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();?>
<?
if(CModule::IncludeModule("fileman"))
{
	?>
	<script>
	BX.message({'BLOG_POST_AUTOSAVE':'<?=GetMessage("BLOG_POST_AUTOSAVE")?>'});
	var arImages = Array();
	var arImagesId = Array();
	<?
	$i = 0;
	foreach($arResult["Images"] as $aImg)
	{
		?>
		arImages['<?=$i?>'] = '<?=CUtil::JSEscape($aImg["PARAMS"]["SRC"])?>';
		arImagesId['<?=$i?>'] = '<?=$aImg["ID"]?>';
		<?
		$i++;
	}
	?>
	</script>

	<?
	function CustomizeLightEditorForBlog()
	{
		?>
		<script>
		LHEButtons['BlogImage'] ={
			id : 'Image', // Standart image icon from editor-s CSS
			name : BX.message.Image,
			handler: function(pBut)
			{
				pBut.pLEditor.OpenDialog({id : 'BlogImage', obj: false});
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

						sContent = sContent.replace(/\[IMG ID=(\d+)(?:\s*?WIDTH=(\d+)\s*?HEIGHT=(\d+))?\]/ig, function(str, id, width, height)
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

		// Rename image button and change Icon
		LHEButtons['Image'].id = 'ImageLink';
		LHEButtons['Image'].src = '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_image_upload.gif';
		LHEButtons['Image'].name = '<?=GetMessage("BLOG_P_IMAGE_LINK")?>';

		LHEButtons['BlogInputVideo'] = {
			id : 'BlogInputVideo',
			src : '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/icon_video.gif',
			name : '<?=GetMessage("FPF_VIDEO")?>',
			handler: function(pBut)
			{
				pBut.pLEditor.OpenDialog({id : 'BlogVideo', obj: false});
			},
			OnBeforeCreate: function(pLEditor, pBut)
				{
					// Disable in non BBCode mode in html
					pBut.disableOnCodeView = !pLEditor.bBBCode || pLEditor.arConfig.bConvertContentFromBBCodes;
					return pBut;
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

		window.LHEDailogs['BlogImage'] = function(pObj)
		{
			var str = 
				'<span class="errortext" id="lhed_blog_image_error" style="display:none;"></span>' +
				'<table width="100%"><tr>' +
				'<td class="lhe-dialog-label lhe-label-imp"><?= GetMessage('BLOG_IMAGE')?>:</td>' +
				'<td class="lhe-dialog-param">' +
				'<form id="' + pObj.pLEditor.id + 'img_upload_form" action="<?=CUtil::JSEscape(POST_FORM_ACTION_URI)?>" method="post" enctype="multipart/form-data" style="margin: 0!important; padding: 0!important;">' +
				'<?=bitrix_sessid_post()?>' +
				'<input type="file" size="30" name="BLOG_UPLOAD_FILE" id="bx_lhed_blog_img_input" />' +
				'<input type="hidden" value="Y" name="blog_upload_image"/>' +
				'<input type="hidden" value="Y" name="do_upload"/>' +
				'</form>'+
				'</td>' +
				'</tr><tr id="' + pObj.pLEditor.id + 'lhed_blog_notice">' +
				'<td colSpan="2" style="padding: 0 0 20px 25px !important; font-size: 11px!important;"><?= GetMessage('BPC_IMAGE_SIZE_NOTICE', Array('#SIZE#' => DoubleVal(COption::GetOptionString("blog", "image_max_size", 1000000)/1000000)))?></td>' +
			'</tr></table>';

			return {
				title: "<?= GetMessage('BLOG_P_IMAGE_UPLOAD')?>",
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
			pLEditor = window.oBlogLHE;
			var strSize = '';

			if (!pLEditor.arBlogImages[imageId])
			{
				if(width > 0)
				{
					if(pLEditor.arConfig.width && pLEditor.arConfig.width.indexOf('%') <= 0)
						widthC = parseInt(pLEditor.arConfig.width)*0.8;
					else
						widthC = 400;
					if(width > widthC)
						strSize = ' width="80%"';
				}
					
				var imgId = window.arImagesId[imageId-1];

				var src  = window.arImages[imageId-1];
				imageId = imgId;
			
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

		//
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

					pObj.pLEditor.focus(pObj.pPath);
				},
				OnSave: function()
				{
					pLEditor = window.oBlogLHE;

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

		window.checkConsent = function()
		{
//			consent was set previously
			if(document.forms.REPLIER.consent == true)
			{
				if(document.forms.REPLIER)
					document.forms.REPLIER.submit();
			}
			else
			{
//				to listen consent answer if they not set already
				var control = BX.UserConsent.load(BX("blog-post-edit-micro-form"));

//				add new accept event with form submit
				BX.addCustomEvent(
					control,
					BX.UserConsent.events.save,
					BX.proxy(function () {
						document.forms.REPLIER.consent = true; if(document.forms.REPLIER) document.forms.REPLIER.submit();
						}, this)
				);
				BX.addCustomEvent(
					control,
					BX.UserConsent.events.refused,
					BX.proxy(function () {document.forms.REPLIER.consent = false;}, this)
				);

//				to open consent form if needed
				BX.onCustomEvent(this, 'OnUCFormCheckConsent', []);
			}
		};
		
		document.forms.REPLIER.onsubmit = function()
		{
			oBlogLHE.SaveContent();
			BX('add-microblog').disabled = true;
		};
		</script>
		<?
	}
	?>
	<script>
//	Submit form by ctrl+enter
	window.blogCtrlEnterHandler = function(e)
	{
		oBlogLHE.SaveContent();
		<?if ($arParams['USER_CONSENT'] == 'Y' && (empty($arResult["User"]) || !$arParams['USER_CONSENT_WAS_GIVEN'])):?>
			window.checkConsent();
		<?else:?>
			if (document.forms.REPLIER)
				document.forms.REPLIER.submit();
		<?endif;?>
	};
	</script>
	<?
	
	AddEventHandler("fileman", "OnIncludeLightEditorScript", "CustomizeLightEditorForBlog");

	$arSmiles = array();
	if(!empty($arResult["Smiles"]))
	{
		foreach($arResult["Smiles"] as $arSmile)
		{
			$arSmiles[] = array(
				'name' => $arSmile["~LANG_NAME"],
				'path' => "/bitrix/images/blog/smile/".$arSmile["IMAGE"],
				'code' => str_replace("\\\\","\\",$arSmile["TYPE"])
			);
		}
	}
	?>
	<div id="edit-post-text">
	<?
	$bbCode = true;
	if($arResult["allow_html"] == "Y" && (($arResult["PostToShow"]["DETAIL_TEXT_TYPE"] == "html" && $_REQUEST["load_editor"] != "N") || $_REQUEST["load_editor"] == "Y"))
		$bbCode = false;

	// Detect necessity of first convertion content from BB-code to HTML in editor.
	$bConvertContentFromBBCodes = !$bbCode && $_REQUEST["load_editor"] == "Y" && 
	!isset($_REQUEST['preview']) && !isset($_REQUEST['save']) && !isset($_REQUEST['apply']) && !isset($_REQUEST['draft']);
	$LHE = new CLightHTMLEditor;
	$LHE->Show(array(
		'id' => 'LHEBlogId',
		'width' => '100%',
		//'height' => $arParams['EDITOR_DEFAULT_HEIGHT'],
		'height' => '120px',
		'inputId' => 'POST_MESSAGE_HTML',
		'inputName' => 'POST_MESSAGE',
		'content' => $arResult["PostToShow"]["~DETAIL_TEXT"],
		'bUseFileDialogs' => false,
		'bUseMedialib' => false,
		'toolbarConfig' => array(
			'BlogImage', 
			'CreateLink',
			(($arResult["allowVideo"] == "Y") ? 'BlogInputVideo' : ''), 
			//'SmileList',
		),
		'jsObjName' => 'oBlogLHE',
		'arSmiles' => $arSmiles,
		'smileCountInToolbar' => 1,
		'bSaveOnBlur' => false,
		'BBCode' => $bbCode,
		'bConvertContentFromBBCodes' => $bConvertContentFromBBCodes, 
		'bQuoteFromSelection' => true, // Make quote from any text in the page
		'bResizable' => true,
		'bAutoResize' => true,
		'autoResizeOffset' => 40,
		'autoResizeMaxHeight' => 300,
		'controlButtonsHeight' => 0,
		'autoResizeSaveSize' => false,
		'ctrlEnterHandler' => 'blogCtrlEnterHandler', // Ctrl+Enter handler name in global namespace
		'bSetDefaultCodeView' => false, // Set first view to CODE or to WYSIWYG
		'bBBParseImageSize' => true // [IMG ID=XXX WEIGHT=5 HEIGHT=6],  [IMGWEIGHT=5 HEIGHT=6]/image.gif[/IMG]
	));
	?></div><?
}
?>
<script>
var bShow = false;
function blogCheckLength()
{
	if(window.oBlogLHE)
	{
		if(!bShow)
		{
			var el = BX.findChild(BX("bxlhe_frame_LHEBlogId"), {className: "lhe-stat-toolbar-cont"}, true);
			elTr = BX.findParent(BX.findParent(el));
			elTr.style.display = "none";
			BX("blog-post-micro-lhe-but").appendChild(el);
			if(BX("slog-mb-hide"))
				BX("blog-post-micro-lhe-hide").innerHTML = BX("slog-mb-hide").innerHTML;
			<?
			/*
			if(COption::GetOptionString("blog", "use_autosave", "Y") == "Y")
			{
				?>BlogPostAutoSaveIcon();<?
			}
			*/
			?>
			
			document.getElementById("blog-post-edit-micro-form").style.display = "block";
			bShow = true;
		}
		window.oBlogLHE.SetContent('<?=CUtil::JSEscape($arResult["PostToShow"]["~DETAIL_TEXT"])?>');
		window.oBlogLHE.CreateFrame(); // We need to recreate editable frame after reappending editor container
		window.oBlogLHE.SetEditorContent(window.oBlogLHE.content);			
		window.oBlogLHE.SetFocus(); 
		//window.oBlogLHE.AutoResize();
	}
	else
		setTimeout("blogCheckLength()", 1000);
}
setTimeout("blogCheckLength()", 1000);
BX.addCustomEvent('onSocNetLogMoveBody', function() {blogCheckLength();});
</script>