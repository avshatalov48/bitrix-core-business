;(function(window){
	BX.Idea = (!!BX.Idea ? BX.Idea : {});
	BX.Idea.obj = (!!BX.Idea.obj ? BX.Idea.obj : {});
	if (!!BX.Idea["customizeEditor"] || !!top.BX.Idea["customizeEditor"])
		return;

	BX.addCustomEvent(window, 'LHE_OnBeforeParsersInit', function(editor) { if (BX.Idea.obj[editor.id] === true) { BX.Idea.LHE_OnBeforeParsersInit(editor); } } );
	BX.addCustomEvent(window, 'LHE_OnInit', function(editor) { if (BX.Idea.obj[editor.id] === true) { BX.Idea.LHE_OnInit(editor); } } );
	for(var ii in BX.Idea.obj)
	{
		if (BX.Idea.obj.hasOwnProperty(ii))
		{
			BX.Idea.LHE_OnBeforeParsersInit(BX.Idea.obj[ii]);
			BX.Idea.LHE_OnInit(BX.Idea.obj[ii], true);
		}
	}
	BX.Idea.LHE_OnBeforeParsersInit = function(pLEditor)
	{
		BX.Idea[pLEditor.id + "Settings"] = (!!BX.Idea[pLEditor.id + "Settings"] ? BX.Idea[pLEditor.id + "Settings"] : {});
		pLEditor.insertImage = function(imageId)
		{
			var img = BX(imageId);
			imageId = img.id;
			if (!pLEditor.arBlogImages[imageId])
				return false;

			if (pLEditor.sEditorMode == 'code' && pLEditor.bBBCode) // BB Codes
				pLEditor.WrapWith("", "", "[IMG ID=" + imageId + "]");
			else if(pLEditor.sEditorMode == 'html') // WYSIWYG
			{
				pLEditor.InsertHTML('<img id="' + pLEditor.SetBxTag(false, {tag: "blogImage", params: {value : imageId}}) +
					'" src="' + pLEditor.arBlogImages[imageId].src +
					'" title="' + (pLEditor.arBlogImages[imageId].pTitle.value || "") +
					'"' + (BX.Idea[pLEditor.id + "Settings"]["IMAGE_MAX_WIDTH"] > 0 ?
					' style="max-width: ' + BX.Idea[pLEditor.id + "Settings"]["IMAGE_MAX_WIDTH"] + 'px;" ' : '') +
					'>');
				if (pLEditor.arConfig.bResizable !== false)
					setTimeout(function(){pLEditor.AutoResize();}, 500);
			}
		};
		pLEditor.bindImage = function(img)
		{
			img = BX(img);
			if (!!img && !img.hasAttribute("bx-idea-bound"))
			{
				img.setAttribute("bx-idea-bound", "Y");
				BX.adjust(img, {
					events : {
						click : BX.delegate(function(){this.insertImage(img.id);}, pLEditor)
					},
					style : {
						cursor : "pointer"
					}
				});
				pLEditor.arBlogImages[img.id] = {
					src : img.src,
					pTitle: BX.findChild(pLEditor.pBlogPostImage, {attribute : {name: 'IMAGE_ID_title[' + img.id + ']'}}, true) || {}
				};
			}
		};
		if (!pLEditor.arBlogImages)
			pLEditor.arBlogImages = {};
		if (!pLEditor.pBlogPostImage)
			pLEditor.pBlogPostImage = BX('blog-post-image');
		var img;
		if (BX.Idea[pLEditor.id + 'Images'].length > 0)
		{
			while ((img = BX.Idea[pLEditor.id + 'Images'].shift()) && !!img)
			{
				pLEditor.bindImage(img);
			}
		}
	};
	BX.Idea.LHE_OnInit = function(pLEditor)
	{
		BX.Idea.obj[pLEditor.id] = pLEditor;
		pLEditor.pForm = (!!pLEditor.pTextarea ? pLEditor.pTextarea.form : false);
		if (!pLEditor.pForm)
			return false;
		BX.addCustomEvent(pLEditor.pForm, "OnSubmitForm", function(){pLEditor.SaveContent();});
		pLEditor.transliterateOldValue = null;
		pLEditor.transliterate = function(form)
		{
			var from = form.POST_TITLE, to = form.CODE;
			if(from && to && pLEditor.transliterateOldValue != from.value)
			{
				BX.translit(from.value, {
					max_len : 70,
					change_case : 'L',
					replace_space : '-',
					replace_other : '',
					delete_repeat_replace : true,
					use_google : (!!form.USE_GOOGLE_CODE && form.USE_GOOGLE_CODE.value === "Y"),
					callback : function(result){
						to.value = result;
						setTimeout(function(){pLEditor.transliterate(form);}, 250);
					}
				});
				pLEditor.transliterateOldValue = from.value;
			}
			else
			{
				setTimeout(function(){pLEditor.transliterate(form);}, 250);
			}
		};
		pLEditor.transliterate(pLEditor.pForm);
		BX.Idea.obj[pLEditor.id] = pLEditor;
		if (!!BX.Idea[pLEditor.id + "Settings"]["FORM_NAME"] && !!document.forms[BX.Idea[pLEditor.id + "Settings"]["FORM_NAME"]])
		{
			var form = document.forms[BX.Idea[pLEditor.id + "Settings"]["FORM_NAME"]], node;
			form["POST_TITLE"].focus();
			for (var ii in form.elements)
			{
				node = form.elements[ii];
				if (!!form.elements.hasOwnProperty && form.elements.hasOwnProperty(ii) &&
					node.tagName == "SELECT" && node.name == "UF_STATUS" && node.options && node.options[0].value == "")
				{
					node.options[0].parentNode.removeChild(node.options[0]);
				}
			}
		}
	};

	BX.Idea.customizeEditor = function(id)
	{
		window.LHEButtons['BlogImage'] ={
			id : 'Image', // Standart image icon from editor-s CSS
			name : BX.message('Image'),
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
						sContent = sContent.replace(/\[IMG ID=(\d+)(?:\s*?WIDTH=(\d+))?(?:\s*?HEIGHT=(\d+))?\]/ig, function(str, id, width, height)
						{
							if (!pLEditor.arBlogImages)
								pLEditor.arBlogImages = {};
							if (!pLEditor.pBlogPostImage)
								pLEditor.pBlogPostImage = BX('blog-post-image');
							if (!pLEditor.arBlogImages[id])
								return str;

							width = parseInt(width);
							height = parseInt(height);

							var strSize = "",
								imageSrc = pLEditor.arBlogImages[id].src,
								imageTitle = pLEditor.arBlogImages[id].pTitle.value || "";

							if (pLEditor.bBBParseImageSize)
							{
								if (width)
									strSize = " width=\"" + width + "\"";
								if (height)
									strSize = " height=\"" + height + "\"";
							}

							return '<img id="' + pLEditor.SetBxTag(false, {tag: "blogimage", params: {value : id}}) +
								'" src="' + imageSrc +
								'" title="' + imageTitle +
								'" ' + strSize +
								(BX.Idea[pLEditor.id + "Settings"]["IMAGE_MAX_WIDTH"] > 0 ?
									' style="max-width: ' + BX.Idea[pLEditor.id + "Settings"]["IMAGE_MAX_WIDTH"] + 'px;" ' : '') +
								' />';
						});
						return sContent;
					},

					/**
					 * @return {string}
					 */
					UnParse: function(bxTag, pNode, pLEditor)
					{
						if (bxTag.tag == 'blogimage')
						{
							var
								width = parseInt(pNode.arAttributes['width']),
								height = parseInt(pNode.arAttributes['height']),
								strSize = "" + (width ? ' WIDTH=' + width : '') + (height ? ' HEIGHT=' + height : '');

							return '[IMG ID=' + bxTag.params.value + strSize + ']';
						}
						return "";
					}
				}
			}
		};

		window.LHEButtons['Image'].src = '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_image_upload.gif';
		window.LHEButtons['Image'].name = BX.message('BLOG_P_IMAGE_LINK');

		window.LHEButtons['BlogInputVideo'] = {
			id : 'BlogInputVideo',
			src : '/bitrix/components/bitrix/blog/templates/.default/images/bbcode/font_video.gif',
			name : BX.message('BLOG_P_IMAGE_LINK'),
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
							w = parseInt(w) || 400;
							h = parseInt(h) || 300;
							src = BX.util.trim(src);

							return '<img id="' + pLEditor.SetBxTag(false, {tag: "blogvideo", params: {value : src}}) + '" src="/bitrix/images/1.gif" class="bxed-video" width=' + w + ' height=' + h + ' title="' + BX.message.Video + ": " + src + '" />';
						});
						return sContent;
					},
					/**
					 * @return {string}
					 */
					UnParse: function(bxTag, pNode)
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
					'<td class="lhe-dialog-label lhe-label-imp">' + BX.message('BLOG_IMAGE') + ':</td>' +
					'<td class="lhe-dialog-param">' +
						'<form id="' + pObj.pLEditor.id + 'img_upload_form" action="' + BX.message('POST_FORM_ACTION_URI') + '" method="post" enctype="multipart/form-data" style="margin: 0!important; padding: 0!important;">' +
							'<input type="hidden" value="' + BX.bitrix_sessid() + '" name="sessid"/>' +
							'<input type="file" size="30" name="BLOG_UPLOAD_FILE" id="bx_lhed_blog_img_input" />' +
							'<input type="hidden" value="Y" name="blog_upload_image"/>' +
							'<input type="hidden" value="Y" name="do_upload"/>' +
						'</form>'+
					'</td>' +
				'</tr><tr id="' + pObj.pLEditor.id + 'lhed_blog_notice">' +
					'<td colSpan="2" style="padding: 0 0 20px 25px !important; font-size: 11px!important;">' + BX.message('BPC_IMAGE_SIZE_NOTICE') + '</td>' +
				'</tr></table>';

			return {
				title: BX.message('BLOG_P_IMAGE_UPLOAD'),
				innerHTML : str,
				width: 500,
				OnLoad: function()
				{
					pObj.pInput = BX('bx_lhed_blog_img_input');
					pObj.pImgForm = BX(pObj.pLEditor.id + 'img_upload_form');
					pObj.pLEditor.focus(pObj.pInput);
					window.obLHEDialog.adjustSizeEx();
				},
				OnSave: function()
				{
					if (pObj.pInput && pObj.pImgForm && pObj.pInput.value != "")
					{
						BX.showWait('bx_lhed_blog_img_input');
						BX('lhed_blog_image_error').style.display = 'none';
						BX('lhed_blog_image_error').innerHTML = '';
						BX.ajax.submit(pObj.pImgForm, function() {
							BX.closeWait();
							if (window.bxBlogImageId)
							{
								window.obLHEDialog.Close();
								pObj.pLEditor.bindImage(window["bxBlogImageId"]);
								window.bxBlogImageId = false;
								delete window.bxBlogImageId;
							}
							else if(window["bxBlogImageError"])
							{
								BX('lhed_blog_image_error').innerHTML = window["bxBlogImageError"];
								BX('lhed_blog_image_error').style.display = 'block';
								delete window["bxBlogImageError"];
								window.obLHEDialog.adjustSizeEx();
							}
						});
						return false;
					}
				}
			};
		};

		window.LHEDailogs['BlogVideo'] = function(pObj)
		{
			var str = '<table width="100%"><tr>' +
				'<td class="lhe-dialog-label lhe-label-imp"><label for="' + pObj.pLEditor.id + 'lhed_blog_video_path"><b>' + BX.message('BPC_VIDEO_P') + ':</b></label></td>' +
				'<td class="lhe-dialog-param">' +
				'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_path" value="" size="30"/>' +
				'</td>' +
			'</tr><tr>' +
				'<td></td>' +
				'<td style="padding: 0!important; font-size: 11px!important;">' + BX.message('BPC_VIDEO_PATH_EXAMPLE') + '</td>' +
			'</tr><tr>' +
				'<td class="lhe-dialog-label lhe-label-imp"><label for="' + pObj.pLEditor.id + 'lhed_blog_video_width">' + BX.message('ImageSizing') + ':</label></td>' +
				'<td class="lhe-dialog-param">' +
					'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_width" value="" size="4"/>' +
					' x ' +
					'<input id="' + pObj.pLEditor.id + 'lhed_blog_video_height" value="" size="4" />' +
				'</td>' +
			'</tr></table>';

			return {
				title: BX.message('FPF_VIDEO'),
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
					var pLEditor = pObj.pLEditor,
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
						if (pLEditor.arConfig.bResizable !== false)
							setTimeout('pLEditor.AutoResize();', 500);
					}
				}
			};
		};
	};
})(window);
