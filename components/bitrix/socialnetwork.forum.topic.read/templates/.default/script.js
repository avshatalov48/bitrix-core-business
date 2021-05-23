function fasessid(href)
{
	if (typeof href == "string")
		href += (href.indexOf("?") < 0 ? "?" : "&") + "sessid=" + BX.bitrix_sessid();
	else if (BX.type.isDomNode(href))
		href.href += (href.href.indexOf("?") < 0 ? "?" : "&") + "sessid=" + BX.bitrix_sessid()
	return href;
}
function forumActionComment(link, action)
{
	if (! BX.util.in_array(action, ['DEL', 'MODERATE'])) return false;
	if (action == 'DEL' && (!confirm(oText['cdm']))) return false;
	var href = link.getAttribute('href');
	href = href.replace(/.AJAX_CALL=Y/g,'').replace(/.sessid=[^&]*/g, '')
	href += ((href.indexOf('?') > -1) ? '&' : '?') + 'AJAX_CALL=Y&sessid=' + phpVars.bitrix_sessid;
	
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
						if ((!posts) || (posts.length < 1)) 
							window.location = oForum.topic_read_url;
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

function SelectPost(table, iIndex, value)
{
	
	if (table == null)
		return;
	if (iIndex != null && value != null)
		dForm = BX("MESSAGES_"+iIndex);

	if(table.className.match(/forum-post-selected/))
	{
		table.className = table.className.replace(/\s*forum-post-selected/i, '');
		if (dForm != null)
		{
			var dRow = BX('message_id_'+this.value);
			if (dRow != null)
				BX.remove(dRow);
		}
	}
	else
	{
		table.className += ' forum-post-selected';
		if (dForm != null)
		{
			var dRow = BX.create("input", {'attrs': {'name': 'message_id[]', 'value': value, 'id':'message_id_'+this.value, 'type':'hidden'}}, document); 
			dForm.appendChild(dRow);
		}
	}
}

function Validate(form)
{
	if (typeof(form) != "object" || form == null)
		return false;
	var oError = [];
	if (form.name.substr(0, 8) == 'MESSAGES')
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
				if (!(items[ii].name == 'message_id[]'))
					continue;
				bEmptyData = false;
				break;
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
	var editor = (window["BXHtmlEditor"] ? window["BXHtmlEditor"].Get('POST_MESSAGE') : false);
	if (editor)
		setTimeout(function() { editor.Focus(); }, 100);
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
