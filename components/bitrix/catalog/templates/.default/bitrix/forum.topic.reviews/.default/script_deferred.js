function replyActionComment(link, action)
{
	if (! BX.util.in_array(action, ['DEL', 'MODERATE'])) return false;
	if (action == 'DEL' && (!confirm(oText['cdm']))) return false;
	var href = link.getAttribute('href');
	href = href.replace(/.AJAX_CALL=Y/g,'').replace(/.sessid=[^&]*/g, '')
	href += ((href.indexOf('?') > -1) ? '&' : '?') + 'AJAX_CALL=Y&sessid=' + phpVars.bitrix_sessid;

	BX.hide(link.parentNode);
	var note = BX.create('a', {attrs: { className : 'reply-action-note'}});
	note.innerHTML = oText['wait'];
	link.parentNode.parentNode.appendChild(note);

	var replyActionDone = function()
	{
		BX.remove(note);
		BX.show(link.parentNode);
	}

	BX.ajax.loadJSON(href, function(res)
	{
		var tbl = BX.findParent(link, {'tag' : 'table'});
		if (res.status && !!tbl)
		{
			BX.onCustomEvent(window, 'onForumCommentAJAXAction', [action]);
			var curpage = window.curpage || top.window.location.href;

			if (action == 'DEL')
			{
				BX.fx.hide(tbl, 'scroll', {time: 0.15, callback_complete: function() {
					BX.remove(tbl);
					var reviews = BX.findChild(document, {'class': 'reviews-post-table'}, true, true);
					if ((!reviews) || (reviews.length < 1))
						if ((typeof oForum != 'undefined') && oForum.page_number > 1)
							BX.reload(curpage);
					replyActionDone();
				}});
			} else { // MODERATE
				var bHidden = BX.hasClass(tbl, 'reviews-post-hidden');
				var label = (bHidden ? oText['hide'] : oText['show']);
				var tbldiv = BX.findChild(tbl, { className : 'reviews-text'}, true);
				BX.fx.hide(tbldiv, 'fade', {time: 0.1, callback_complete: function() {
					BX.toggleClass(tbl, 'reviews-post-hidden');
					link.innerHTML = label;
					href = href.replace(new RegExp('REVIEW_ACTION='+(bHidden ? 'SHOW' : 'HIDE')), ('REVIEW_ACTION='+(bHidden ? 'HIDE' : 'SHOW')));
					link.setAttribute('href', href);
					BX.fx.show(tbldiv, 'fade', {time: 0.1});
					replyActionDone();
					BX.style(tbldiv, 'background-color', (bHidden ? '#FFFFFF' : '#E5F8E3')); // IE9
				}});
			}
		} else {
			BX.addClass(note, 'error');
			note.innerHTML = '<font class="errortext">'+res.message+'</font>';
		}
	});
	return false;
}

function fTextToNode(text)
{
	var tmpdiv = BX.create('div');
	tmpdiv.innerHTML = text;
	if (tmpdiv.childNodes.length > 0)
		return tmpdiv;
	else
		return null;
}

function PostFormAjaxStatus(status)
{
	var arNote = BX.findChild(document, { className : 'reviews-note-box'} , true, true);
	if (arNote)
		for (i in arNote)
			BX.remove(arNote[i]);

	var msgBox = BX.findChild(document, { className : 'reviews-block-container' } , true);
	if (!msgBox) return;

	if (status.length < 1) return;

	var statusDIV = fTextToNode(status);
	if (!statusDIV) return;

	var beforeDivs = ['reviews-reply-form', 'reviews-collapse'];
	var tmp = msgBox;
	while (tmp = tmp.nextSibling)
	{
		if (tmp.nodeType == 1)
		{
			var insert = false;
			for (i in beforeDivs)
			{
				if (BX.hasClass(tmp, beforeDivs[i]))
				{
					insert = true;
					break;
				}
			}
			if (insert)
			{
				tmp.parentNode.insertBefore(statusDIV, tmp);
				break;
			}
		}
	}
}

function PostFormAjaxNavigation(navString, pageNumber, pageCount)
{
	var navDIV = fTextToNode(navString);
	if (!navDIV) return;
	var navPlaceholders = BX.findChildren(document, { className : 'reviews-navigation-box' } , true);
	if (!navPlaceholders) // then add ...
	{
		// top
		var tmpDIV = BX.findChild(document, {className: 'reviews-block-container'}, true);
		if (!tmpDIV)
			return false;
		var parentNode = tmpDIV.parentNode;
		parentNode.insertBefore(BX.create('div', {props:{className:"reviews-navigation-box reviews-navigation-top"}}), tmpDIV);
		// bottom
		do {
			tmpDIV = tmpDIV.nextSibling;
		} while (tmpDIV && tmpDIV.nodeType != 1)
		var bottomPager = BX.create('div', {props:{className:"reviews-navigation-box reviews-navigation-bottom"}});
		if (!!tmpDIV)
			tmpDIV.parentNode.insertBefore( bottomPager , tmpDIV);
		else
			parentNode.appendChild(bottomPager);

		var navPlaceholders = BX.findChildren(document, { className : 'reviews-navigation-box' } , true);
	}
	for (i in navPlaceholders)
		navPlaceholders[i].innerHTML = navDIV.innerHTML;
	oForum.page_number = pageNumber;
	oForum.page_count = pageCount;
}

function SetReviewsAjaxPostTmp(text)
{
	window.forumAjaxPostTmp = text;
}

function fReplaceOrInsertNode(sourceNode, targetNode, parentTargetNode, beforeTargetNode)
{
	var parentNode = null;
	var nextNode = null;

	if (!BX.type.isDomNode(parentTargetNode)) return false;

	if (!BX.type.isDomNode(sourceNode) && !BX.type.isArray(sourceNode) && sourceNode.length > 0)
		if (! (sourceNode = fTextToNode(sourceNode))) return false;

	if (BX.type.isDomNode(targetNode)) // replace
	{
		parentTargetNode = targetNode.parentNode;
		nextNode = targetNode.nextSibling;
		parentTargetNode.removeChild(targetNode);
	}

	if (!nextNode)
		nextNode = BX.findChild(parentTargetNode, beforeTargetNode, true);

	if (nextNode)
	{
		nextNode.parentNode.insertBefore(sourceNode, nextNode);
	} else {
		parentTargetNode.appendChild(sourceNode);
	}

	return true;
}

function fRunScripts(msg)
{
	var ob = BX.processHTML(msg, true);
	scripts = ob.SCRIPT;
	BX.ajax.processScripts(scripts, true);
}

function PostFormAjaxResponse(response, postform, preorder)
{
	postform['BXFormSubmit_save'] = null;
	var result = window.forumAjaxPostTmp;
	var post = {className: /reviews-reply-form|reviews-collapse/};

	BX.onCustomEvent(window, 'onForumCommentAJAXPost', [result, postform]);
	var curpage = window.curpage || top.window.location.href;
	if (typeof result == 'undefined')
	{
		BX.reload(curpage);
		return;
	}

	var listform = BX.findChild(document, {className: 'reviews-block-inner'}, true);
	if (! listform) 
	{
		var container = BX.create('div', {
			'props': { 
				'className': 'reviews-block-container reviews-reviews-block-container'}, 
			'children': [
				BX.create('div', {
					'props': {
						'className': 'reviews-block-outer' 
					},
					'children': [
						BX.create('div', {
							'props': {
								'className': 'reviews-block-inner'
							}
						})
					]
				})
			]
		});
		fReplaceOrInsertNode(container, null, BX.findChild(document, post, true).parentNode, post);
		listform = BX.findChild(document, {className: 'reviews-block-inner'}, true);
	}

	if (result.reload)
		BX.reload(curpage);

	if (result.status)
	{
		if (!!result.allMessages)
		{
			var messagesNode = fTextToNode(result.message);
			if (! messagesNode) return;

			fReplaceOrInsertNode(messagesNode, listform, BX.findChild(document, post, true).parentNode, post);

			if (!!result.navigation && !!result.pageNumber)
				PostFormAjaxNavigation(result.navigation, result.pageNumber, result.pageCount);

			ClearForumPostForm(postform);
			fRunScripts(result.message);
		}
		else if (typeof result.message != 'undefined')
		{
			if (msgNode = fTextToNode(result.message))
			{
				if (preorder)
					listform.appendChild(msgNode);
				else
					listform.insertBefore(msgNode, listform.firstChild);
			}

			ClearForumPostForm(postform);
			fRunScripts(result.message);
		}
		else if (!!result.previewMessage)
		{
			previewDIV = BX.findChild(document, {'className': 'reviews-preview'}, true);
			previewParent = BX.findChild(document, {className : /reviews-reply-form|reviews-collapse/}, true).parentNode;
			var previewNode = fTextToNode(result.previewMessage);
			fReplaceOrInsertNode(previewNode, previewDIV, previewParent, {'className' : /reviews-reply-form|reviews-collapse/});

			PostFormAjaxStatus('');
			fRunScripts(result.previewMessage);
		}

		if ((!!result.messageID) && (message = BX('message'+result.messageID)))
			BX.scrollToNode(message);
	}
	
	var arr = postform.getElementsByTagName("input");
	for (var i=0; i < arr.length; i++)
	{
		var butt = arr[i];
		if (butt.getAttribute("type") == "submit")
			butt.disabled = false;
	}

	if (input_pageno = BX.findChild(postform, { 'attr' : { 'name' : 'pageNumber' }}, true))
		BX.remove(input_pageno);

	if (result.statusMessage)
		PostFormAjaxStatus(result.statusMessage);

	BX.onCustomEvent(window, 'onAfterForumCommentAJAXPost', [result, postform]);
}

function ClearForumPostForm(form)
{
	if (window.oLHE.sEditorMode == 'code')
		window.oLHE.SetContent('');
	else
		window.oLHE.SetEditorContent('');

	if (window.oLHE.fAutosave)
		BX.bind(window.oLHE.pEditorDocument, 'keydown',
			BX.proxy(window.oLHE.fAutosave.Init, window.oLHE.fAutosave));

	if (!BX.type.isDomNode(form)) return;

	if (previewDIV = BX.findChild(document, {'className' : 'reviews-preview'}, true))
		BX.remove(previewDIV);

	var i = 0;
	while (fileDIV = BX('upload_files_'+(i++)+'_'))
	{
		if (fileINPUT = BX.findChild(fileDIV, {'tag':'input'}))
			fileINPUT.value = '';
		BX.hide(fileDIV);
	}
	var attachLink = BX.findChild(form, {'className':"forum-upload-file-attach"}, true);
	if (attachLink)
		BX.show(attachLink);
	var attachNote = BX.findChild(form, {'className':"reviews-upload-info"}, true);
	if (attachNote)
		BX.hide(attachNote);

	captchaIMAGE = null;
	captchaHIDDEN = BX.findChild(form, {attr : {'name': 'captcha_code'}}, true);
	captchaINPUT = BX.findChild(form, {attr: {'name':'captcha_word'}}, true);
	captchaDIV = BX.findChild(form, {'className':'reviews-reply-field-captcha-image'}, true);
	if (captchaDIV)
		captchaIMAGE = BX.findChild(captchaDIV, {'tag':'img'});
	if (captchaHIDDEN && captchaINPUT && captchaIMAGE)
	{
		captchaINPUT.value = '';
		BX.ajax.getCaptcha(function(result) {
			captchaHIDDEN.value = result.captcha_sid;
			captchaIMAGE.src = '/bitrix/tools/captcha.php?captcha_code='+result.captcha_sid;
		});
	}
}

function ValidateForm(form, ajax_type, ajax_post, preorder)
{
	if (form['BXFormSubmit_save'])
		return true; // ValidateForm may be run by BX.submit one more time
	if (typeof form != "object" || typeof form.REVIEW_TEXT != "object")
		return false;
	if (typeof oForum == 'undefined')
		oForum = {};
	MessageMax = 64000;

	var errors = "";
	var MessageLength = form.REVIEW_TEXT.value.length;

	if (form.TITLE && (form.TITLE.value.length < 2))
		errors += oErrors['no_topic_name'];

	if (MessageLength < 2)
		errors += oErrors['no_message'];
	else if ((MessageMax != 0) && (MessageLength > MessageMax))
		errors += oErrors['max_len'].replace(/\#MAX_LENGTH\#/gi, MessageMax).replace(/\#LENGTH\#/gi, MessageLength);

	if (errors != "")
	{
		alert(errors);
		return false;
	}

	var btnSubmit = BX.findChild(form, {'attribute':{'name':'send_button'}}, true);
	if (btnSubmit) btnSubmit.disabled = true;
	var btnPreview = BX.findChild(form, {'attribute':{'name':'view_button'}}, true);
	if (btnPreview) btnPreview.disabled = true;

	if (ajax_type == 'Y' && window['ForumPostMessage'])
	{
		ForumPostMessage(form);
	}

	if (ajax_post == 'Y')
	{
		var postform = form;
		var reviews_preorder = (preorder == 'Y');
		if (typeof oForum != 'undefined' && typeof oForum.page_number != 'undefined')
		{
			var pageNumberInput = BX.findChild(postform, {attr : {name : 'pageNumber'}});
			if (!pageNumberInput)
			{
				pageNumberInput = BX.create("input", {props : {type : "hidden", name : 'pageNumber'}});
				pageNumberInput.value = oForum.page_number;
				postform.appendChild(pageNumberInput);
			} else {
				pageNumberInput.value = oForum.page_number;
			}
		}
		if (typeof oForum != 'undefined' && typeof oForum.page_count != 'undefined')
		{
			var pageCountInput = BX.findChild(postform, {attr : {name : 'pageCount'}});
			if (!pageCountInput)
			{
				pageCountInput = BX.create("input", {props : {type : "hidden", name : 'pageCount'}});
				pageCountInput.value = oForum.page_count;
				postform.appendChild(pageCountInput);
			} else {
				pageCountInput.value = oForum.page_count;
			}
		}

		BX.onCustomEvent(window, 'onBeforeForumCommentAJAXPost', [postform]);
		setTimeout(function() {
			BX.ajax.submit(postform, function(response) {
				PostFormAjaxResponse(response, postform, reviews_preorder);
			});
		}, 50);
		return false;
	}
	return true;
}

function ShowLastEditReason(checked, div)
{
	if (div)
	{
		if (checked)
			div.style.display = 'block';
		else
			div.style.display = 'none';
	}
}

function AttachFile(iNumber, iCount, sIndex, oObj)
{
	var element = null;
	var bFined = false;
	iNumber = parseInt(iNumber);
	iCount = parseInt(iCount);

	document.getElementById('upload_files_info_' + sIndex).style.display = 'block';
	for (var ii = iNumber; ii < (iNumber + iCount); ii++)
	{
		element = document.getElementById('upload_files_' + ii + '_' + sIndex);
		if (!element || typeof(element) == null)
			break;
		if (element.style.display == 'none')
		{
			bFined = true;
			element.style.display = 'block';
			break;
		}
	}
	var bHide = (!bFined ? true : (ii >= (iNumber + iCount - 1)));
	if (bHide == true)
		oObj.style.display = 'none';
}

var GetSelection = function()
{
	var t = '';
	if (typeof window.getSelection == 'function')
	{
		try 
		{
			var sel = window.getSelection().getRangeAt(0).cloneContents();
			var e = BX.create('div');
			e.appendChild(sel);
			t = e.innerHTML;
		} catch (e) {}
	}
	else if (document.selection && document.selection.createRange)
		t = document.selection.createRange().htmlText;
	return t;
}


function quoteMessageEx(author, mid)
{
	replyForumFormOpen();
	var selection = "";
	var message_id = 0;
	selection = GetSelection();
	
	if (document.getSelection)
	{
		selection = selection.replace(/\r\n\r\n/gi, "_newstringhere_").replace(/\r\n/gi, " ");
		selection = selection.replace(/  /gi, "").replace(/_newstringhere_/gi, "\r\n\r\n");
	}

	if (selection == "" && mid)
	{
		message_id = parseInt(mid.replace(/message_text_/gi, ""));
		if (message_id > 0)
		{
			var message = document.getElementById(mid);
			if (typeof(message) == "object" && message)
			{
				selection = message.innerHTML;
			}
		}
		else if (mid.length > 0)
		{
			selection = mid;
		}
	}

	if (selection != "")
	{
		selection = selection.replace(/[\n|\r]*\<br(\s)*(\/)*\>/gi, "\n");

		// Video
		var videoWMV = function(str, p1, offset, s)
		{
			var result = ' ';
			var rWmv = /showWMVPlayer.*?bx_wmv_player.*?file:[\s'"]*([^"']*).*?width:[\s'"]*([^"']*).*?height:[\s'"]*([^'"]*).*?/gi;
			res = rWmv.exec(p1);
			if (res)
				result = "[VIDEO WIDTH="+res[2]+" HEIGHT="+res[3]+"]"+res[1]+"[/VIDEO]";
			if (result == ' ')
			{
				var rFlv = /bxPlayerOnload[\s\S]*?[\s'"]*file[\s'"]*:[\s'"]*([^"']*)[\s\S]*?[\s'"]*height[\s'"]*:[\s'"]*([^"']*)[\s\S]*?[\s'"]*width[\s'"]*:[\s'"]*([^"']*)/gi;
				res = rFlv.exec(p1);
				if (res)
					result = "[VIDEO WIDTH="+res[3]+" HEIGHT="+res[2]+"]"+res[1]+"[/VIDEO]";
			}
			return result;
		}
		
		selection = selection.replace(/\<script[^\>]*>/gi, '\001').replace(/\<\/script[^\>]*>/gi, '\002');
		selection = selection.replace(/\001([^\002]*)\002/gi, videoWMV)
		selection = selection.replace(/\<noscript[^\>]*>/gi, '\003').replace(/\<\/noscript[^\>]*>/gi, '\004');
		selection = selection.replace(/\003([^\004]*)\004/gi, " ");
		
		// Quote & Code & Table
		selection = selection.replace(/\<table class\=[\"]*forum-quote[\"]*\>[^<]*\<thead\>[^<]*\<tr\>[^<]*\<th\>([^<]+)\<\/th\>\<\/tr\>\<\/thead\>[^<]*\<tbody\>[^<]*\<tr\>[^<]*\<td\>/gi, "\001");
		selection = selection.replace(/\<table class\=[\"]*forum-code[\"]*\>[^<]*\<thead\>[^<]*\<tr\>[^<]*\<th\>([^<]+)\<\/th\>\<\/tr\>\<\/thead\>[^<]*\<tbody\>[^<]*\<tr\>[^<]*\<td\>/gi, "\002");
		selection = selection.replace(/\<table class\=[\"]*data-table[\"]*\>[^<]*\<tbody\>/gi, "\004");
		selection = selection.replace(/\<\/td\>[^<]*\<\/tr\>(\<\/tbody\>)*\<\/table\>/gi, "\003"); 
		selection = selection.replace(/[\r|\n]{2,}([\001|\002])/gi, "\n$1");


		var ii = 0;
		while(ii++ < 50 && (selection.search(/\002([^\002\003]*)\003/gi) >= 0 || selection.search(/\001([^\001\003]*)\003/gi) >= 0))
		{
			selection = selection.replace(/\002([^\002\003]*)\003/gi, "[CODE]$1[/CODE]").replace(/\001([^\001\003]*)\003/gi, "[QUOTE]$1[/QUOTE]");
		}


		function regexReplaceTableTag(s, tag, replacement)
		{
			var re_match = new RegExp("\004([^\004\003]*)("+tag+")([^\004\003]*)\003", "i");
			var re_replace = new RegExp("((?:\004)(?:[^\004\003]*))("+tag+")((?:[^\004\003]*)(?:\003))", "i");
			var ij = 0;
			while((ij++ < 300) && (s.search(re_match) >= 0))
				s = s.replace(re_replace, "$1"+replacement+"$3");
			return s;
		}

		var ii = 0;
		while(ii++ < 10 && (selection.search(/\004([^\004\003]*)\003/gi) >= 0))
		{
			selection = regexReplaceTableTag(selection, "\<tr\>", "[TR]");
			selection = regexReplaceTableTag(selection, "\<\/tr\>", "[/TR]");
			selection = regexReplaceTableTag(selection, "\<td\>", "[TD]");
			selection = regexReplaceTableTag(selection, "\<\/td\>", "[/TD]");
			selection = selection.replace(/\004([^\004\003]*)\003/gi, "[TABLE]$1[/TD][/TR][/TABLE]");
		}

		// Smiles
		if (BX.browser.IsIE())
			selection = selection.replace(/\<img(?:(?:\s+alt\s*=\s*\"?smile([^\"\s]+)\"?)|(?:\s+\w+\s*=\s*[^\s\>]*))*\>/gi, "$1");
		else
			selection = selection.replace(/\<img.*?alt=[\"]*smile([^\"\s]+)[\"]*[^>]*\>/gi, "$1");

		// Hrefs
		selection = selection.replace(/\<a[^>]+href=[\"]([^\"]+)\"[^>]+\>([^<]+)\<\/a\>/gi, "[URL=$1]$2[/URL]");
		selection = selection.replace(/\<a[^>]+href=[\']([^\']+)\'[^>]+\>([^<]+)\<\/a\>/gi, "[URL=$1]$2[/URL]");
		selection = selection.replace(/\<[^\>]+\>/gi, " ").replace(/&lt;/gi, "<").replace(/&gt;/gi, ">").replace(/&quot;/gi, "\"");

		selection = selection.replace(/(smile(?=[:;8]))/g, "");

		selection = selection.replace(/\&shy;/gi, "");
		selection = selection.replace(/\&nbsp;/gi, " ");
		if (author != null && author)
			selection = author + oText['author'] + selection;

		if (!!window.oLHE && !!selection)
		{
			if (window.oLHE.sEditorMode == 'code' && window.oLHE.bBBCode) { // BB Codes
				window.oLHE.WrapWith("[QUOTE]", "[/QUOTE]", selection);
			} else if (window.oLHE.sEditorMode == 'html') { // WYSIWYG
				var strId = (window.oLHE.bBBCode ? '' : (" id\"=" + window.oLHE.SetBxTag(false, {tag: "quote"}) + "\""));
				window.oLHE.InsertHTML('<blockquote class="bx-quote"' + strId + ">" +
					window.oLHE.ParseContent(selection, true) + "</blockquote><br/>");
			}

			window.oLHE.SetFocus();
			BX.defer(window.oLHE.SetFocus, window.oLHE)();
		}
	}
	return false;
}
BX(function() {
	if (BX.browser.IsIE())
	{
		var posts = BX.findChildren(document, {'className':'reviews-post-table'}, true);
		if (!posts) return;
		for (i in posts)
		{
			var all = posts[i].getElementsByTagName('*'), i = all.length;
			while (i--) {
				if (all[i].scrollWidth > all[i].offsetWidth) {
					all[i].style['paddingBottom'] = '20px';
					all[i].style['overflowY'] = 'hidden';
				}
			}
		}
	}
});
