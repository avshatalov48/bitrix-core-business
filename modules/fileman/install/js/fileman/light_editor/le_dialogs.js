window.LHEDailogs = {};

window.LHEDailogs['Anchor'] = function(pObj)
{
	return {
		title: BX.message.AnchorProps,
		innerHTML : '<table>' +
			'<tr>' +
				'<td class="lhe-dialog-label lhe-label-imp">' + BX.message.AnchorName + ':</td>' +
				'<td class="lhe-dialog-param"><input type="text" size="20" value="" id="lhed_anchor_name"></td>' +
			'</tr></table>',
		width: 300,
		OnLoad: function()
		{
			pObj.pName = BX("lhed_anchor_name");
			pObj.pLEditor.focus(pObj.pName);

			var pElement = pObj.pLEditor.GetSelectionObject();
			var value = "";
			if (pElement)
			{
				var bxTag = pObj.pLEditor.GetBxTag(pElement);
				if (bxTag.tag == "anchor" && bxTag.params.value)
				{
					value = bxTag.params.value.replace(/([\s\S]*?name\s*=\s*("|'))([\s\S]*?)(\2[\s\S]*?(?:>\s*?<\/a)?(?:\/?))?>/ig, "$3");
				}
			}
			pObj.pName.value = value;
		},
		OnSave: function()
		{
			var anchorName = pObj.pName.value.replace(/[^\w\d]/gi, '_');
			if(pObj.pSel)
			{
				if(anchorName.length > 0)
					pObj.pSel.id = anchorName;
				else
					pObj.pLEditor.executeCommand('Delete');
			}
			else if(anchorName.length > 0)
			{
				var id = pObj.pLEditor.SetBxTag(false, {tag: "anchor", params: {value : '<a name="' + anchorName + '"></a>'}});
				pObj.pLEditor.InsertHTML('<img id="' + id + '" src="' + pObj.pLEditor.oneGif + '" class="bxed-anchor" />');
			}
		}
	};
}

window.LHEDailogs['Link'] = function(pObj)
{
	var strHref = pObj.pLEditor.arConfig.bUseFileDialogs ? '<input type="text" size="26" value="" id="lhed_link_href"><input type="button" value="..." style="min-width: 20px; max-width: 40px;" onclick="window.LHED_Link_FDOpen();">' : '<input type="text" size="30" value="" id="lhed_link_href">';

	var str = '<table width="100%">' +
	'<tr>' +
		'<td class="lhe-dialog-label lhe-label-imp">' + BX.message.LinkText + ':</td>' +
		'<td class="lhe-dialog-param"><input type="text" size="30" value="" id="lhed_link_text"></td>' +
	'</tr>' +
	'<tr>' +
		'<td class="lhe-dialog-label lhe-label-imp">' + BX.message.LinkHref + ':</td>' +
		'<td class="lhe-dialog-param">' + strHref + '</td>' +
	'</tr>';

	if (!pObj.pLEditor.arConfig.bBBCode)
	{
		str +=
	'<tr>' +
		'<td class="lhe-dialog-label">' + BX.message.LinkTitle + ':</td>' +
		'<td class="lhe-dialog-param"><input type="text" size="30" value="" id="lhed_link_title"></td>' +
	'</tr>' +
	'<tr>' +
		'<td class="lhe-dialog-label">' + BX.message.LinkTarget + '</td>' +
		'<td class="lhe-dialog-param">' +
			'<select id="lhed_link_target">' +
				'<option value="">' + BX.message.LinkTarget_def + '</option>' +
				'<option value="_blank">' + BX.message.LinkTarget_blank + '</option>' +
				'<option value="_parent">' + BX.message.LinkTarget_parent + '</option>' +
				'<option value="_self">' + BX.message.LinkTarget_self + '</option>' +
				'<option value="_top">' + BX.message.LinkTarget_top + '</option>' +
			'</select>' +
		'</td>' +
	'</tr>';
	}
	str += '</table>';

	return {
		title: BX.message.LinkProps,
		innerHTML : str,
		width: 420,
		OnLoad: function()
		{
			pObj._selectionStart = pObj._selectionEnd = null;
			pObj.bNew = true;
			pObj.pText = BX("lhed_link_text");
			pObj.pHref = BX("lhed_link_href");

			pObj.pLEditor.focus(pObj.pHref);

			if (!pObj.pLEditor.bBBCode)
			{
				pObj.pTitle = BX("lhed_link_title");
				pObj.pTarget = BX("lhed_link_target");
			}

			if (pObj.pLEditor.sEditorMode == 'code' && pObj.pLEditor.bBBCode)
			{
				if (pObj.prevTextSelection)
					pObj.pText.value = pObj.prevTextSelection;

				if (pObj.pLEditor.pTextarea.selectionStart != undefined)
				{
					pObj._selectionStart = pObj.pLEditor.pTextarea.selectionStart;
					pObj._selectionEnd = pObj.pLEditor.pTextarea.selectionEnd;
				}
			}
			else // WYSIWYG
			{
				if(!pObj.pSel)
				{
					var bogusImg = pObj.pLEditor.pEditorDocument.getElementById('bx_lhe_temp_bogus_node');
					if (bogusImg)
					{
						pObj.pSel = BX.findParent(bogusImg, {tagName: 'A'});
						bogusImg.parentNode.removeChild(bogusImg);
					}
				}

				var parA = (pObj.pSel && pObj.pSel.tagName.toUpperCase() != 'A') ? BX.findParent(pObj.pSel, {tagName : 'A'}) : false;
				if (parA)
					pObj.pSel = parA;

				pObj.bNew = !pObj.pSel || pObj.pSel.tagName.toUpperCase() != 'A';

				// Select Link
				if (!pObj.bNew && !BX.browser.IsIE())
					pObj.pLEditor.oPrevRange = pObj.pLEditor.SelectElement(pObj.pSel);


				var
					selectedText = false,
					oRange = pObj.pLEditor.oPrevRange;

				// Get selected text
				if (oRange.startContainer && oRange.endContainer) // DOM Model
				{
					if (oRange.startContainer == oRange.endContainer && (oRange.endContainer.nodeType == 3 || oRange.endContainer.nodeType == 1))
						selectedText = oRange.startContainer.textContent.substring(oRange.startOffset, oRange.endOffset) || '';
				}
				else // IE
				{
					if (oRange.text == oRange.htmlText)
						selectedText = oRange.text || '';
				}

				if (pObj.pSel && pObj.pSel.tagName.toUpperCase() == 'IMG')
					selectedText = false;

				if (selectedText === false)
				{
					var textRow = BX.findParent(pObj.pText, {tagName: 'TR'});
					textRow.parentNode.removeChild(textRow);
					pObj.pText = false;
				}
				else
				{
					pObj.pText.value = selectedText || '';
				}

				if (!pObj.bNew)
				{
					var bxTag = pObj.pLEditor.GetBxTag(pObj.pSel);
					if (pObj.pText !== false)
						pObj.pText.value = pObj.pSel.innerHTML;

					if (pObj.pSel && pObj.pSel.childNodes && pObj.pSel.childNodes.length > 0)
					{
						for (var i = 0; i < pObj.pSel.childNodes.length; i++)
						{
							if (pObj.pSel.childNodes[i] && pObj.pSel.childNodes[i].nodeType != 3)
							{
								var textRow = BX.findParent(pObj.pText, {tagName: 'TR'});
								textRow.parentNode.removeChild(textRow);
								pObj.pText = false;
								break;
							}
						}
					}

					if (bxTag.tag == 'a')
					{
						pObj.pHref.value = bxTag.params.href;
						if (!pObj.pLEditor.bBBCode)
						{
							pObj.pTitle.value = bxTag.params.title || '';
							pObj.pTarget.value = bxTag.params.target || '';
						}
					}
					else
					{
						pObj.pHref.value = pObj.pSel.getAttribute('href');
						if (!pObj.pLEditor.bBBCode)
						{
							pObj.pTitle.value = pObj.pSel.getAttribute('title') || '';
							pObj.pTarget.value = pObj.pSel.getAttribute('target') || '';
						}
					}
				}
			}
		},
		OnSave: function()
		{
			var
				link,
				href = pObj.pHref.value;

			if (href.length  < 1) // Need for showing error
				return;

			if (pObj.pText && pObj.pText.value.length <=0)
				pObj.pText.value = href;

			// BB code mode
			if (pObj.pLEditor.sEditorMode == 'code' && pObj.pLEditor.bBBCode)
			{
				if (pObj._selectionStart != undefined && pObj._selectionEnd != undefined)
				{
					pObj.pLEditor.pTextarea.selectionStart = pObj._selectionStart;
					pObj.pLEditor.pTextarea.selectionEnd = pObj._selectionEnd;
				}

				var res = "";
				if (!pObj.pText || pObj.pText && pObj.pText.value == href)
					res = '[URL]' + href + '[/URL]';
				else
					res = '[URL=' + href + ']' + pObj.pText.value + '[/URL]';
				pObj.pLEditor.WrapWith("", "",  res);
			}
			else
			{
				// WYSIWYG mode
				var arlinks = [];
				if (pObj.pSel && pObj.pSel.tagName.toUpperCase() == 'A')
				{
					arlinks[0] = pObj.pSel;
				}
				else
				{
					var sRand = '#'+Math.random().toString().substring(5);
					var pDoc = pObj.pLEditor.pEditorDocument;

					if (pObj.pText !== false) // Simple case
					{
						pObj.pLEditor.InsertHTML('<a id="bx_lhe_' + sRand + '">#</a>');
						arlinks[0] = pDoc.getElementById('bx_lhe_' + sRand);
						arlinks[0].removeAttribute("id");
					}
					else
					{
						pDoc.execCommand('CreateLink', false, sRand);
						var arLinks_ = pDoc.getElementsByTagName('A');
						for(var i = 0; i < arLinks_.length; i++)
							if(arLinks_[i].getAttribute('href', 2) == sRand)
								arlinks.push(arLinks_[i]);
					}
				}

				var oTag, i, l = arlinks.length, link;
				for (i = 0;  i < l; i++)
				{
					link = arlinks[i];
					oTag = false;

					if (pObj.pSel && i == 0)
					{
						oTag = pObj.pLEditor.GetBxTag(link);
						if (oTag.tag != 'a' || !oTag.params)
							oTag = false;
					}

					if (!oTag)
						oTag = {tag: 'a', params: {}};

					oTag.params.href = href;
					if (!pObj.pLEditor.bBBCode)
					{
						oTag.params.title = pObj.pTitle.value;
						oTag.params.target = pObj.pTarget.value;
					}

					pObj.pLEditor.SetBxTag(link, oTag);
					SetAttr(link, 'href', href);
					// Add text
					if (pObj.pText !== false)
						link.innerHTML = BX.util.htmlspecialchars(pObj.pText.value);

					if (!pObj.pLEditor.bBBCode)
					{
						SetAttr(link, 'title', pObj.pTitle.value);
						SetAttr(link, 'target', pObj.pTarget.value);
					}
				}
			}
		}
	};
}

window.LHEDailogs['Image'] = function(pObj)
{
	var sText = '', i, strSrc;

	if (pObj.pLEditor.arConfig.bUseMedialib)
		strSrc = '<input type="text" size="30" value="" id="lhed_img_src"><input class="lhe-br-but" type="button" value="..." onclick="window.LHED_Img_MLOpen();">';
	else if (pObj.pLEditor.arConfig.bUseFileDialogs)
		strSrc = '<input type="text" size="30" value="" id="lhed_img_src"><input class="lhe-br-but" type="button" value="..." onclick="window.LHED_Img_FDOpen();">';
	else
		strSrc = '<input type="text" size="33" value="" id="lhed_img_src">';

	for (i = 0; i < 200; i++){sText += 'text ';}

	var str = '<table width="100%">' +
	'<tr>' +
		'<td class="lhe-dialog-label lhe-label-imp">' + BX.message.ImageSrc + ':</td>' +
		'<td class="lhe-dialog-param">' + strSrc + '</td>' +
	'</tr>';
	if (!pObj.pLEditor.arConfig.bBBCode)
	{
		str +=
	'<tr>' +
		'<td class="lhe-dialog-label">' + BX.message.ImageTitle + ':</td>' +
		'<td class="lhe-dialog-param"><input type="text" size="33" value="" id="lhed_img_title"></td>' +
	'</tr>' +
	'<tr>' +
		'<td class="lhe-dialog-label">' + BX.message.ImgAlign + ':</td>' +
		'<td class="lhe-dialog-param">' +
			'<select id="lhed_img_align">' +
				'<option value="">' + BX.message.LinkTarget_def + '</option>' +
				'<option value="top">' + BX.message.ImgAlignTop + '</option>' +
				'<option value="right">' + BX.message.ImgAlignRight + '</option>' +
				'<option value="bottom">' + BX.message.ImgAlignBottom + '</option>' +
				'<option value="left">' + BX.message.ImgAlignLeft + '</option>' +
				'<option value="middle">' + BX.message.ImgAlignMiddle + '</option>' +
			'</select>' +
		'</td>' +
	'</tr>' +
	'<tr>' +
		'<td colSpan="2" class="lhe-dialog-param"><span class="lhed-img-preview-label">' + BX.message.ImageSizing + ':</span>' +
		'<div class="lhed-img-size-cont"><input type="text" size="4" value="" id="lhed_img_width"> x <input type="text" size="4" value="" id="lhed_img_height"> <input type="checkbox" id="lhed_img_save_prop" checked><label for="lhed_img_save_prop">' + BX.message.ImageSaveProp + '</label></div></td>' +
	'</tr>';
	str +=
	'<tr>' +
		'<td colSpan="2" class="lhe-dialog-param"><span class="lhed-img-preview-label">' + BX.message.ImagePreview + ':</span>' +
			'<div class="lhed-img-preview-cont"><img id="lhed_img_preview" style="display:none" />' + sText + '</div>' +
		'</td>' +
	'</tr>';
	}
	str += '</table>';

	var PreviewOnLoad = function()
	{
		var w = parseInt(this.style.width || this.getAttribute('width') || this.offsetWidth);
		var h = parseInt(this.style.height || this.getAttribute('hright') || this.offsetHeight);
		if (!w || !h)
			return;
		pObj.iRatio = w / h; // Remember proportion
		pObj.curWidth = pObj.pWidth.value = w;
		pObj.curHeight = pObj.pHeight.value = h;
	};

	var PreviewReload = function()
	{
		var newSrc = pObj.pSrc.value;
		if (!newSrc) return;
		if (pObj.prevSrc != newSrc)
		{
			pObj.prevSrc = pObj.pPreview.src = newSrc;
			pObj.pPreview.style.display = "";
			pObj.pPreview.removeAttribute("width");
			pObj.pPreview.removeAttribute("height");
		}

		if (pObj.curWidth && pObj.curHeight)
		{
			pObj.pPreview.style.width = pObj.curWidth + 'px';
			pObj.pPreview.style.height = pObj.curHeight + 'px';
		}

		if (!pObj.pLEditor.bBBCode)
		{
			SetAttr(pObj.pPreview, 'align', pObj.pAlign.value);
			SetAttr(pObj.pPreview, 'title', pObj.pTitle.value);
		}
	};

	if (pObj.pLEditor.arConfig.bUseMedialib || pObj.pLEditor.arConfig.bUseFileDialogs)
	{
		window.LHED_Img_SetUrl = function(filename, path, site)
		{
			var url, srcInput = BX("lhed_img_src"), pTitle;

			if (typeof filename == 'object') // Using medialibrary
			{
				url = filename.src;
				if (pTitle = BX("lhed_img_title"))
					pTitle.value = filename.name;
			}
			else // Using file dialog
			{
				url = (path == '/' ? '' : path) + '/'+filename;
			}

			srcInput.value = url;
			if(srcInput.onchange)
				srcInput.onchange();

			pObj.pLEditor.focus(srcInput, true);
		};
	}

	return {
		title: BX.message.ImageProps,
		innerHTML : str,
		width: 500,
		OnLoad: function()
		{
			pObj.bNew = !pObj.pSel || pObj.pSel.tagName.toUpperCase() != 'IMG';
			pObj.bSaveProp = true;
			pObj.iRatio = 1;

			pObj.pSrc = BX("lhed_img_src");
			pObj.pLEditor.focus(pObj.pSrc);

			if (!pObj.pLEditor.bBBCode)
			{
				pObj.pPreview = BX("lhed_img_preview");
				pObj.pTitle = BX("lhed_img_title");
				pObj.pAlign = BX("lhed_img_align");
				pObj.pWidth = BX("lhed_img_width");
				pObj.pHeight = BX("lhed_img_height");
				pObj.pSaveProp = BX("lhed_img_save_prop");
				pObj.bSetInStyles = false;
				pObj.pSaveProp.onclick = function()
				{
					pObj.bSaveProp = this.checked ? true : false;
					if (pObj.bSaveProp)
						pObj.pWidth.onchange();
				};
				pObj.pWidth.onchange = function()
				{
					var w = parseInt(this.value);
					if (isNaN(w)) return;
					pObj.curWidth = pObj.pWidth.value = w;
					if (pObj.bSaveProp)
					{
						var h = Math.round(w / pObj.iRatio);
						pObj.curHeight = pObj.pHeight.value = h;
					}
					PreviewReload();
				};
				pObj.pHeight.onchange = function()
				{
					var h = parseInt(this.value);
					if (isNaN(h)) return;
					pObj.curHeight = pObj.pHeight.value = h;
					if (pObj.bSaveProp)
					{
						var w = parseInt(h * pObj.iRatio);
						pObj.curWidth = pObj.pWidth.value = w;
					}
					PreviewReload();
				};
				pObj.pAlign.onchange = pObj.pTitle.onchange = PreviewReload;
				pObj.pSrc.onchange = PreviewReload;
				pObj.pPreview.onload = PreviewOnLoad;
			}
			else if (pObj.pLEditor.sEditorMode == 'code' && pObj.pLEditor.bBBCode && pObj.pLEditor.pTextarea.selectionStart != undefined)
			{
				pObj._selectionStart = pObj.pLEditor.pTextarea.selectionStart;
				pObj._selectionEnd = pObj.pLEditor.pTextarea.selectionEnd;
			}

			if (!pObj.bNew) // Select Img
			{
				var bxTag = pObj.pLEditor.GetBxTag(pObj.pSel);
				if (bxTag.tag !== 'img')
					bxTag.params = {};

				pObj.pSrc.value = bxTag.params.src || '';
				if (!pObj.pLEditor.bBBCode)
				{
					pObj.pPreview.onload = function(){pObj.pPreview.onload = PreviewOnLoad;};
					if (pObj.pSel.style.width || pObj.pSel.style.height)
						pObj.bSetInStyles = true;
					pObj.bSetInStyles = false;

					var w = parseInt(pObj.pSel.style.width || pObj.pSel.getAttribute('width') || pObj.pSel.offsetWidth);
					var h = parseInt(pObj.pSel.style.height || pObj.pSel.getAttribute('height') || pObj.pSel.offsetHeight);
					if (w && h)
					{
						pObj.iRatio = w / h; // Remember proportion
						pObj.curWidth = pObj.pWidth.value = w;
						pObj.curHeight = pObj.pHeight.value = h;
					}
					pObj.pTitle.value = bxTag.params.title || '';
					pObj.pAlign.value = bxTag.params.align || '';
					PreviewReload();
				}
			}
		},
		OnSave: function()
		{
			var src = pObj.pSrc.value, img, oTag;

			if (src.length < 1) // Need for showing error
				return;

			// BB code mode
			if (pObj.pLEditor.sEditorMode == 'code' && pObj.pLEditor.bBBCode)
			{
				if (pObj._selectionStart != undefined && pObj._selectionEnd != undefined)
				{
					pObj.pLEditor.pTextarea.selectionStart = pObj._selectionStart;
					pObj.pLEditor.pTextarea.selectionEnd = pObj._selectionEnd;
				}
				pObj.pLEditor.WrapWith("", "",  '[IMG]' + src + '[/IMG]');
			}
			else
			{
				// WYSIWYG mode
				if (pObj.pSel)
				{
					img = pObj.pSel;
					oTag = pObj.pLEditor.GetBxTag(img);
					if (oTag.tag != 'img' || !oTag.params)
						oTag = false;
				}
				else
				{
					var tmpid = Math.random().toString().substring(4);
					pObj.pLEditor.InsertHTML('<img id="' + tmpid + '" src="" />');
					img = pObj.pLEditor.pEditorDocument.getElementById(tmpid);
					img.removeAttribute("id");
				}
				SetAttr(img, "src", src);

				if (!oTag)
					oTag = {tag: 'img', params: {}};

				oTag.params.src = src;

				if (!pObj.pLEditor.bBBCode)
				{
					if (pObj.bSetInStyles)
					{
						img.style.width = pObj.pWidth.value + 'px';
						img.style.height = pObj.pHeight.value + 'px';
						SetAttr(img, "width", '');
						SetAttr(img, "height", '');
					}
					else
					{
						SetAttr(img, "width", pObj.pWidth.value);
						SetAttr(img, "height", pObj.pHeight.value);
						img.style.width = '';
						img.style.height = '';
					}

					oTag.params.align = pObj.pAlign.value;
					oTag.params.title = pObj.pTitle.value;

					SetAttr(img, "align", pObj.pAlign.value);
					SetAttr(img, "title", pObj.pTitle.value);
				}

				pObj.pLEditor.SetBxTag(img, oTag);
			}
		}
	};
}

window.LHEDailogs['Video'] = function(pObj)
{
	var strPath;
	if (pObj.pLEditor.arConfig.bUseMedialib)
		strPath = '<input type="text" size="30" value="" id="lhed_video_path"><input class="lhe-br-but" type="button" value="..." onclick="window.LHED_Video_MLOpen();">';
	else if (pObj.pLEditor.arConfig.bUseFileDialogs)
		strPath = '<input type="text" size="30" value="" id="lhed_video_path"><input class="lhe-br-but" type="button" value="..." onclick="window.LHED_VideoPath_FDOpen();">';
	else
		strPath = '<input type="text" size="33" value="" id="lhed_video_path">';

	var strPreview = pObj.pLEditor.arConfig.bUseFileDialogs ? '<input type="text" size="30" value="" id="lhed_video_prev_path"><input type="button" value="..." style="width: 20px;" onclick="window.LHED_VideoPreview_FDOpen();">' : '<input type="text" size="33" value="" id="lhed_video_prev_path">';

	var sText = '', i;
	for (i = 0; i < 200; i++){sText += 'text ';}

	var str = '<table width="100%">' +
	'<tr>' +
		'<td class="lhe-dialog-label lhe-label-imp">' + BX.message.VideoPath + ':</td>' +
		'<td class="lhe-dialog-param">' + strPath + '</td>' +
	'</tr>';
	if (!pObj.pLEditor.arConfig.bBBCode)
	{
		str +=
	'<tr>' +
		'<td class="lhe-dialog-label">' + BX.message.VideoPreviewPath + ':</td>' +
		'<td class="lhe-dialog-param">' + strPreview + '</td>' +
	'</tr>';
	}
	str +=
	'<tr>' +
		'<td class="lhe-dialog-label lhe-label-imp">' + BX.message.ImageSizing + ':</td>' +
		'<td class="lhe-dialog-param">' +
		'<div class="lhed-img-size-cont"><input type="text" size="4" value="" id="lhed_video_width"> x <input type="text" size="4" value="" id="lhed_video_height"></div></td>' +
	'</tr>';
	if (!pObj.pLEditor.arConfig.bBBCode)
	{
		str +=
	'<tr>' +
		'<td class="lhe-dialog-label"></td>' +
		'<td class="lhe-dialog-param"><input type="checkbox" id="lhed_video_autoplay"><label for="lhed_video_autoplay">' + BX.message.VideoAutoplay + '</label></td>' +
	'</tr>' +
	'<tr>' +
		'<td class="lhe-dialog-label">' + BX.message.VideoVolume + ':</td>' +
		'<td class="lhe-dialog-param">' +
			'<select id="lhed_video_volume">' +
				'<option value="10">10</option><option value="20">20</option>' +
				'<option value="30">30</option><option value="40">40</option>' +
				'<option value="50">50</option><option value="60">60</option>' +
				'<option value="70">70</option><option value="80">80</option>' +
				'<option value="90" selected="selected">90</option><option value="100">100</option>' +
			'</select> %' +
		'</td>' +
	'</tr>';
	}

	window.LHED_Video_SetPath = function(filename, path, site)
	{
		var url, srcInput = BX("lhed_video_path");
		if (typeof filename == 'object') // Using medialibrary
			url = filename.src;
		else // Using file dialog
			url = (path == '/' ? '' : path) + '/' + filename;

		srcInput.value = url;
		if(srcInput.onchange)
			srcInput.onchange();

		pObj.pLEditor.focus(srcInput, true);
	};

	return {
		title: BX.message.VideoProps,
		innerHTML : str,
		width: 500,
		OnLoad: function()
		{
			pObj.pSel = pObj.pLEditor.GetSelectionObject();
			pObj.bNew = true;
			var bxTag = {};

			if (pObj.pSel)
				bxTag = pObj.pLEditor.GetBxTag(pObj.pSel);

			if (pObj.pSel && pObj.pSel.id)
				bxTag = pObj.pLEditor.GetBxTag(pObj.pSel.id);

			if (bxTag.tag == 'video' && bxTag.params)
				pObj.bNew = false;
			else
				pObj.pSel = false;

			pObj.pPath = BX("lhed_video_path");
			pObj.pLEditor.focus(pObj.pPath);
			pObj.pWidth = BX("lhed_video_width");
			pObj.pHeight = BX("lhed_video_height");

			if (!pObj.pLEditor.bBBCode)
			{
				pObj.pPrevPath = BX("lhed_video_prev_path");
				pObj.pVolume = BX("lhed_video_volume");
				pObj.pAutoplay = BX("lhed_video_autoplay");
			}
			else if (pObj.pLEditor.sEditorMode == 'code' && pObj.pLEditor.bBBCode && pObj.pLEditor.pTextarea.selectionStart != undefined)
			{
				pObj._selectionStart = pObj.pLEditor.pTextarea.selectionStart;
				pObj._selectionEnd = pObj.pLEditor.pTextarea.selectionEnd;
			}

			if (!pObj.bNew)
			{
				pObj.arParams = bxTag.params || {};

				var path, prPath, vol, w, h, autoplay;
				if (pObj.arParams.flashvars) //FLV
				{
					path = pObj.arParams.flashvars.file;
					w = pObj.arParams.width || '';
					h = pObj.arParams.height || '';
					prPath = pObj.arParams.flashvars.image || '';
					vol = pObj.arParams.flashvars.volume || '90';
					autoplay = pObj.arParams.flashvars.autostart || false;
				}
				else
				{
					path = pObj.arParams.JSConfig.file;
					w = pObj.arParams.JSConfig.width || '';
					h = pObj.arParams.JSConfig.height || '';
					prPath = pObj.arParams.JSConfig.image || '';
					vol = pObj.arParams.JSConfig.volume || '90';
					autoplay = pObj.arParams.JSConfig.autostart || false;
				}
				pObj.pPath.value = path;
				pObj.pWidth.value = w;
				pObj.pHeight.value = h;

				if (!pObj.pLEditor.bBBCode)
				{
					if (pObj.pPrevPath)
						pObj.pPrevPath.value = prPath;
					pObj.pVolume.value = vol;
					pObj.pAutoplay.checked = autoplay ? true : false;
				}
			}
		},
		OnSave: function()
		{
			var
				path = pObj.pPath.value,
				w = parseInt(pObj.pWidth.value) || 240,
				h = parseInt(pObj.pHeight.value) || 180,
				pVid, ext,
				arVidConf = pObj.pLEditor.arConfig.videoSettings;

			if (path.length  < 1) // Need for showing error
				return;

			if (pObj.pSel)
			{
				pVid = pObj.pSel;
			}
			else
			{
				pObj.videoId = "bx_video_" + Math.round(Math.random() * 100000);

				pObj.pLEditor.InsertHTML('<img id="' + pObj.videoId + '" src="' + pObj.pLEditor.oneGif + '" class="bxed-video" />');

				pVid = pObj.pLEditor.pEditorDocument.getElementById(pObj.videoId);
			}

			if (arVidConf.maxWidth && w && parseInt(w) > parseInt(arVidConf.maxWidth))
				w = arVidConf.maxWidth;
			if (arVidConf.maxHeight && h && parseInt(h) > parseInt(arVidConf.maxHeight))
				h = arVidConf.maxHeight;

			var oVideo = {width: w, height: h};
			if (path.indexOf('http://') != -1 || path.indexOf('.') != -1)
			{
				ext = (path.indexOf('.') != -1) ? path.substr(path.lastIndexOf('.') + 1).toLowerCase() : false;
				if (ext && (ext == 'wmv' || ext == 'wma')) // WMV
				{
					oVideo.JSConfig = {file: path};
					if (!pObj.pLEditor.bBBCode)
					{
						if (pObj.pPrevPath)
							oVideo.JSConfig.image = pObj.pPrevPath.value || '';
						oVideo.JSConfig.volume = pObj.pVolume.value;
						oVideo.JSConfig.autostart = pObj.pAutoplay.checked ? true : false;
						oVideo.JSConfig.width = w;
						oVideo.JSConfig.height = h;
					}
				}
				else
				{
					oVideo.flashvars= {file: path};
					if (!pObj.pLEditor.bBBCode)
					{
						if (pObj.pPrevPath)
							oVideo.flashvars.image = pObj.pPrevPath.value || '';
						oVideo.flashvars.volume = pObj.pVolume.value;
						oVideo.flashvars.autostart = pObj.pAutoplay.checked ? true : false;
					}
				}

				pVid.title= BX.message.Video + ': ' + path;
				pVid.style.width = w + 'px';
				pVid.style.height = h + 'px';
				if (pObj.pPrevPath && pObj.pPrevPath.value.length > 0)
					pVid.style.backgroundImage = 'url(' + pObj.pPrevPath.value + ')';

				oVideo.id = pObj.videoId;
				pVid.id = pObj.pLEditor.SetBxTag(false, {tag: 'video', params: oVideo});
			}
			else
			{
				pObj.pLEditor.InsertHTML('');
			}
		}
	};
}

// Table
window.LHEDailogs['Table'] = function(pObj)
{
	return {
		title: BX.message.InsertTable,
		innerHTML : '<table>' +
			'<tr>' +
				'<td class="lhe-dialog-label lhe-label-imp"><label for="' + pObj.pLEditor.id + 'lhed_table_cols">' + BX.message.TableCols + ':</label></td>' +
				'<td class="lhe-dialog-param"><input type="text" size="4" value="3" id="' + pObj.pLEditor.id + 'lhed_table_cols"></td>' +
				'<td class="lhe-dialog-label lhe-label-imp"><label for="' + pObj.pLEditor.id + 'lhed_table_rows">' + BX.message.TableRows + ':</label></td>' +
				'<td class="lhe-dialog-param"><input type="text" size="4" value="3" id="' + pObj.pLEditor.id + 'lhed_table_rows"></td>' +
			'</tr>' +
			'<tr>' +
				'<td colSpan="4">' +
					'<span>' + BX.message.TableModel + ': </span>' +
					'<div class="lhed-model-cont" id="' + pObj.pLEditor.id + 'lhed_table_model" ><div>' +
				'</td>' +
			'</tr></table>',
		width: 350,
		OnLoad: function(oDialog)
		{
			pObj.pCols = BX(pObj.pLEditor.id + "lhed_table_cols");
			pObj.pRows = BX(pObj.pLEditor.id + "lhed_table_rows");
			pObj.pModelDiv = BX(pObj.pLEditor.id + "lhed_table_model");

			pObj.pLEditor.focus(pObj.pCols, true);

			if (pObj.pLEditor.sEditorMode == 'code' && pObj.pLEditor.bBBCode && pObj.pLEditor.pTextarea.selectionStart != undefined)
			{
				pObj._selectionStart = pObj.pLEditor.pTextarea.selectionStart;
				pObj._selectionEnd = pObj.pLEditor.pTextarea.selectionEnd;
			}

			var BuildModel = function()
			{
				BX.cleanNode(pObj.pModelDiv);
				var
					rows = parseInt(pObj.pRows.value),
					cells = parseInt(pObj.pCols.value);

				if (rows > 0 && cells > 0)
				{
					var tbl = pObj.pModelDiv.appendChild(BX.create("TABLE", {props: {className: "lhe-table-model"}}));
					var i, j, row, cell;
					for(i = 0; i < rows; i++)
					{
						row = tbl.insertRow(-1);
						for(j = 0; j < cells; j++)
							row.insertCell(-1).innerHTML = "&nbsp;";
					}
				}
			};

			pObj.pCols.onkeyup = pObj.pRows.onkeyup = BuildModel;
			BuildModel();
		},
		OnSave: function()
		{
			var
				rows = parseInt(pObj.pRows.value),
				cells = parseInt(pObj.pCols.value),
				t1 = "<", t2 = ">", res = "", cellHTML = "<br _moz_editor_bogus_node=\"on\" />";

			if (pObj.pLEditor.sEditorMode == 'code' && pObj.pLEditor.bBBCode)
			{
				t1 = "[";
				t2 = "]";
				cellHTML = " ";
			}

			if (rows > 0 && cells > 0)
			{
				res = "\n" + t1 + "TABLE" + t2 + "\n";

				var i, j;
				for(i = 0; i < rows; i++)
				{
					res += "\t" + t1 + "TR" + t2 + "\n";
					for(j = 0; j < cells; j++)
						res += "\t\t" + t1 + "TD" + t2 + cellHTML + t1 + "/TD" + t2 + "\n";
					res += "\t" + t1 + "/TR" + t2 + "\n";
				}

				res += t1 + "/TABLE" + t2 + "\n";
			}

			if (pObj.pLEditor.sEditorMode == 'code' && pObj.pLEditor.bBBCode)
			{
				if (pObj._selectionStart != undefined && pObj._selectionEnd != undefined)
				{
					pObj.pLEditor.pTextarea.selectionStart = pObj._selectionStart;
					pObj.pLEditor.pTextarea.selectionEnd = pObj._selectionEnd;
				}
				pObj.pLEditor.WrapWith("", "", res);
			}
			else if (pObj.pLEditor.sEditorMode == 'code' && !pObj.pLEditor.bBBCode)
			{
				// ?
			}
			else // WYSIWYG
			{
				pObj.pLEditor.InsertHTML(res + "</br>");
			}
		}
	};
}

// Ordered and unordered lists for BBCodes
window.LHEDailogs['List'] = function(pObj)
{
	return {
		title: pObj.arParams.bOrdered ? BX.message.OrderedList : BX.message.UnorderedList,
		innerHTML : '<table class="lhe-dialog-list-table"><tr>' +
				'<td>' + BX.message.ListItems + ':</td>' +
			'</tr><tr>' +
				'<td class="lhe-dialog-list-items"><div id="' + pObj.pLEditor.id + 'lhed_list_items"></div></td>' +
			'</tr><tr>' +
				'<td align="right"><a href="javascript:void(0);" title="' + BX.message.AddLITitle + '" id="' + pObj.pLEditor.id + 'lhed_list_more">' + BX.message.AddLI + '</a>' +
			'</tr><table>',
		width: 350,
		OnLoad: function(oDialog)
		{
			if (pObj.pLEditor.sEditorMode == 'code' && pObj.pLEditor.bBBCode && pObj.pLEditor.pTextarea.selectionStart != undefined)
			{
				pObj._selectionStart = pObj.pLEditor.pTextarea.selectionStart;
				pObj._selectionEnd = pObj.pLEditor.pTextarea.selectionEnd;
			}

			pObj.pItemsCont = BX(pObj.pLEditor.id + "lhed_list_items");
			pObj.pMore = BX(pObj.pLEditor.id + "lhed_list_more");

			BX.cleanNode(pObj.pItemsCont);
			pObj.pList = pObj.pItemsCont.appendChild(BX.create(pObj.arParams.bOrdered ? "OL" : "UL"));

			var firstItemText = "";
			if (pObj.prevTextSelection)
				firstItemText = pObj.prevTextSelection;

			var addItem = function(val, pPrev, bFocus, bCheck)
			{
				var pLi = BX.create("LI");
				var pInput = pLi.appendChild(BX.create("INPUT", {props: {type: 'text', value: val || "", size: 35}}));

				if (pPrev && pPrev.nextSibling)
					pObj.pList.insertBefore(pLi, pPrev.nextSibling);
				else
					pObj.pList.appendChild(pLi);

				pInput.onkeyup = function(e)
				{
					if (!e)
						e = window.event;

					if (e.keyCode == 13) // Enter
					{
						addItem("", this.parentNode, true, true);
						return BX.PreventDefault(e);
					}
				}

				pLi.appendChild(BX.create("IMG", {props: {src: pObj.pLEditor.oneGif, className: "lhe-dialog-list-del", title: BX.message.DelListItem}})).onclick = function()
				{
					// del list item
					var pLi = BX.findParent(this, {tagName: 'LI'});
					if (pLi)
						pLi.parentNode.removeChild(pLi);
				};

				if(bFocus !== false)
					pObj.pLEditor.focus(pInput);

				if (bCheck === true)
				{
					var arInp = pObj.pList.getElementsByTagName("INPUT"), i, l = arInp.length;
					for (i = 0; i < l; i++)
						arInp[i].onfocus = (i == l - 1) ? function(){addItem("", false, false, true);} : null;
				}
			};

			addItem(firstItemText, false, firstItemText == "");
			addItem("", false, firstItemText != "");
			addItem("", false, false, true);

			pObj.pMore.onclick = function(){addItem("", false, true, true);};
		},
		OnSave: function()
		{
			var
				res = "",
				arInputs = pObj.pList.getElementsByTagName("INPUT"),
				i, l = arInputs.length;

			if (l == 0)
				return;

			res = "\n[LIST";
			if (pObj.arParams.bOrdered)
				res += "=1";
			res += "]\n";

			var i, j;
			for (i = 0; i < l; i++)
			{
				if (arInputs[i].value != "" || i == 0)
					res += "[*]" + arInputs[i].value + "\n";
			}
			res += "[/LIST]" + "\n";

			if (pObj._selectionStart != undefined && pObj._selectionEnd != undefined)
			{
				pObj.pLEditor.pTextarea.selectionStart = pObj._selectionStart;
				pObj.pLEditor.pTextarea.selectionEnd = pObj._selectionEnd;
			}
			pObj.pLEditor.WrapWith("", "", res);
		}
	};
}


