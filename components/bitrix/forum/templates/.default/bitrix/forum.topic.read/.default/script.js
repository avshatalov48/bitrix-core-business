function forumActionComment(link, action)
{
	if (! BX.util.in_array(action, ['DEL', 'MODERATE'])) return false;
	if (action == 'DEL' && (!confirm(oText['cdm']))) return false;
	var href = link.getAttribute('href');
	href = href.replace(/.AJAX_CALL=Y/g,'').replace(/.sessid=[^&]*/g, '')
	href += ((href.indexOf('?') > -1) ? '&' : '?') + 'AJAX_CALL=Y&sessid=' + BX.bitrix_sessid();
	
	if (linkParent = BX.findParent(link, {'className': 'forum-action-links'}))
		BX.hide(linkParent);

	var note = BX.create('a', {attrs: { className : 'forum-action-note'}});
	note.innerHTML = oText['wait'];
	linkParent.parentNode.appendChild(note);

	var replyActionDone = function(l)
	{
		BX.remove(note);
		BX.show(l);
	}

	function _moveChildren(src, dst)
	{
		if (!BX.type.isDomNode(src) || !BX.type.isDomNode(dst)) return false;
		while (src.childNodes.length > 0)
			dst.appendChild(src.childNodes[0]);
		return true;
	}

	BX.ajax.loadJSON(href, function(res)
	{
		if (res.status == true)
		{
			var tbl = BX.findParent(link, {'tag' : 'table'});
			if (tbl)
			{
				var linkParent = BX.findChild(tbl, {'className': 'forum-action-links'}, true);
				if (action == 'DEL')
				{
					var footer = BX.findChild(tbl, {tagName: 'tfoot'});
					if (!!footer) // move footer with actions
					{
						lastMessage = tbl.previousSibling;
						while (!!lastMessage && lastMessage.nodeType!=1)
							lastMessage=lastMessage.previousSibling;
					}
					var tmpDIV = BX.create('div', {style: {'overflow':'hidden'}});
					tbl.parentNode.insertBefore(tmpDIV, tbl);
					tmpDIV.appendChild(tbl);
					if (!!footer && !!lastMessage)
						lastMessage.appendChild(footer);

					BX.fx.hide(tmpDIV, 'scroll', {time: 0.35, callback_complete: function() {
						BX.remove(tmpDIV);
						var posts = BX.findChild(document, {'class': 'forum-post-table'}, true, true);
						if (!posts || posts.length < 1)
							window.location = BX.message("topic_read_url");
						replyActionDone(linkParent);
					}});
				} else { // MODERATE
					var bHidden = BX.hasClass(tbl, 'forum-post-hidden');
					var label = (bHidden ? oText['hide'] : oText['show']);
					var tbldiv = BX.findChild(tbl, { className : 'forum-cell-post'}, true);
					var tmpDIV = BX.create('div');
					_moveChildren(tbldiv, tmpDIV);
					tbldiv.appendChild(tmpDIV);
					BX.fx.hide(tmpDIV, 'fade', {time: 0.1, callback_complete: function() {
						BX.toggleClass(tbl, 'forum-post-hidden');
						link.innerHTML = label;
						href = href.replace(new RegExp('ACTION='+(bHidden ? 'SHOW' : 'HIDE')), ('ACTION='+(bHidden ? 'HIDE' : 'SHOW')));
						link.setAttribute('href', href);
						BX.fx.show(tmpDIV, 'fade', {time: 0.1, callback_complete: function() {
							_moveChildren(tmpDIV, tbldiv);
							tbldiv.removeChild(tmpDIV);
						}});
						replyActionDone(linkParent);
					}});
				}
			}
		} else {
			BX.addClass(note, 'error');
			note.innerHTML = '<font class="errortext">'+res.message+'</font>';
		}
	});
	return false;
}
var __forum_messages_selected = false;
function SelectPosts(iIndex)
{
	__forum_messages_selected = !__forum_messages_selected; 
	form = document.forms['MESSAGES_' + iIndex];
	if (typeof(form) != "object" || form == null)
		return false;

	var items = form.getElementsByTagName('input');
	if (items && typeof items == "object" )
	{
		if (!items.length || (typeof(items.length) == 'undefined'))
		{
			items = [items];
		}
		
		for (ii = 0; ii < items.length; ii++)
		{
			if (!(items[ii].type == "checkbox" && items[ii].name == 'message_id[]'))
				continue;
			items[ii].checked = __forum_messages_selected;
			var table = items[ii].parentNode.parentNode.parentNode.parentNode.parentNode.parentNode; 
			if (items[ii].checked)
				table.className += ' forum-post-selected';
			else
				table.className = table.className.replace(/\s*forum-post-selected/gi, '');
		}
	}
}
function Validate(form)
{
	if (typeof(form) != "object" || form == null)
		return false;
	var oError = [];
	if (form.type.value == 'messages')
	{
		var items = form.getElementsByTagName('input');
		if (items && typeof items == "object" )
		{
			if (!items.length || (typeof(items.length) == 'undefined'))
			{
				items = [items];
			}
			var bEmptyData = true;
			for (ii = 0; ii < items.length; ii++)
			{
				if (!(items[ii].type == "checkbox" && items[ii].name == 'message_id[]'))
					continue;
				if (items[ii].checked)
				{
					bEmptyData = false;
					break;
				}
			}
			if (bEmptyData)
				oError.push(oText['no_data']);
		}
	}
	if (form['ACTION'].value == '')
		oError.push(oText['no_action']);
	if (oError.length > 0)
	{
		alert(oError.join('\n'));
		return false;
	}
	if (form['ACTION'].value == 'DEL_TOPIC')
		return confirm(oText['cdt']);
	else if (form['ACTION'].value == 'DEL')
		return confirm(oText['cdms']);
	return true;
}

function fReplyForm()
{
	var oLHE = (window["BXHtmlEditor"] ? window["BXHtmlEditor"].Get('POST_MESSAGE') : false);
	if (oLHE)
		setTimeout(function() { oLHE.Focus(); }, 100);
}

BX(function() {
	if (BX.browser.IsIE())
	{
		var posts = BX.findChildren(document, {'className':'forum-post-table'}, true);
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