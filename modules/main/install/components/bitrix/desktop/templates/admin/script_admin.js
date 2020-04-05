function __ShowDesktopSettingsDialog(e)
{
	if(!e)
		e = window.event;

		(new BX.CAdminDialog({
			'title': BX.message('langGDSettingsDialogTitle'),
			'content_url': '/bitrix/components/bitrix/desktop/admin_settings.php?lang='+language_id+'&bxpublic=Y', 
			'content_post': 'sessid='+bxsessid+'&type=desktop&desktop_page='+desktopPage,
			'draggable': true,
			'resizable': true,
			'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
		})).Show();
}

function __ShowDesktopAllSettingsDialog(e)
{
	if(!e)
		e = window.event;

		(new BX.CDialog({
			'title': BX.message('langGDSettingsAllDialogTitle'),
			'content_url': '/bitrix/components/bitrix/desktop/admin_settings_all.php?lang='+language_id+'&bxpublic=Y', 
			'content_post': 'sessid='+bxsessid+'&desktop_backurl='+desktopBackurl,
			'draggable': true,
			'resizable': true,
			'buttons': [BX.CDialog.btnSave, BX.CDialog.btnCancel]
		})).Show();
}

function __ShowDesktopAddDialog(e)
{
	if(!e)
		e = window.event;

		(new BX.CAdminDialog({
			'title': BX.message('langGDSettingsDialogTitle'),
			'content_url': '/bitrix/components/bitrix/desktop/admin_settings.php?lang='+language_id+'&bxpublic=Y', 
			'content_post': 'sessid='+bxsessid+'&type=desktop&desktop_page='+desktopPage+'&action=new&desktop_backurl='+desktopBackurl,
			'draggable': true,
			'resizable': true,
			'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
		})).Show();
}

var allAdminGagdgetHolders = [];
function getAdminGadgetHolder(id)
{
	return allAdminGagdgetHolders[id];
}

BX.AdminGadget = function(gadgetHolderID, allGadgets)
{
	BX.AdminGadget.superclass.constructor.apply(this, arguments);
	allAdminGagdgetHolders[this.gadgetHolderID] = this;
}

BX.extend(BX.AdminGadget, BXGadget);

BX.AdminGadget.prototype.ShowSettings = function(id, title)
{
	(new BX.CAdminDialog({
		'title': title,
		'content_url': '/bitrix/components/bitrix/desktop/admin_settings.php?lang='+language_id+'&bxpublic=Y', 
		'content_post': 'sessid='+bxsessid+'&type=gadget&gd_ajax='+this.gadgetHolderID+'&gid='+id+'&desktop_page='+desktopPage,
		'draggable': true,
		'resizable': true
	})).Show();
	
	return false;
}

gdTabControl = function(id)
{
	this.id = id;
	this.aTabs = BX.findChildren(BX(this.id), {'tag':'span', 'class':'adm-detail-subtabs'}, true);
	if (this.aTabs == 'undefined' || this.aTabs == null || this.aTabs == false || this.aTabs.length == 0)
		this.aTabs = BX.findChildren(BX(this.id), {'tag':'span', 'class':'bx-gadgets-tab-new'}, true);
	if (this.aTabs == 'undefined' || this.aTabs == null || this.aTabs == false || this.aTabs.length == 0)
		this.aTabs = BX.findChildren(BX(this.id), {'tag':'span', 'class':'bx-gadgets-tab-wrap'}, true);	
}

gdTabControl.prototype.SelectTab = function(tab)
{
	var content_div = BX(tab.id+'_content');
	if(!content_div)
		content_div = BX(tab+'_content');
	if(!content_div || content_div.style.display != 'none')
		return;
	var t = false;
	for (var i = 0, cnt = this.aTabs.length; i < cnt; i++)
	{
		t = BX(this.aTabs[i]);
		BX.removeClass(t, 'bx-gadgets-tab-active');
		BX.removeClass(t, 'bx-gadgets-tab-new-active');
		if(t.style.display != 'none')
		{
			if (BX(t.id+'_content'))
				BX(t.id+'_content').style.display = 'none';
			else if (BX(t.id.substr(9)+'_content'))
				BX(t.id.substr(9)+'_content').style.display = 'none';					
		}
	}
	content_div.style.display = 'block';
	if (BX.hasClass(tab, 'bx-gadgets-tab-new'))
		BX.addClass(tab, 'bx-gadgets-tab-new-active');
	else if (BX.hasClass(tab, 'bx-gadgets-tab'))
		BX.addClass(tab, 'bx-gadgets-tab-active');
}

gdTabControl.prototype.LoadTab = function(tab, url, tabControl)
{
	if (tabControl == 'undefined')
		tabControl = false;

	var content_div = BX(tab.id+'_content');
	if(!content_div)
		content_div = BX(tab+'_content');
	if(!content_div || content_div.style.display != 'none')
		return;

	var t = false;
	for (var i = 0, cnt = this.aTabs.length; i < cnt; i++)
	{
		t = BX(this.aTabs[i]);
		BX.removeClass(t, 'bx-gadgets-tab-active');
		BX.removeClass(t, 'bx-gadgets-tab-new-active');
		if(t.style.display != 'none')
		{
			if (BX(t.id+'_content'))
				BX(t.id+'_content').style.display = 'none';
			else if (BX(t.id.substr(9)+'_content'))
				BX(t.id.substr(9)+'_content').style.display = 'none';			
		}
	}

	content_div.style.display = 'block';
	if (BX.hasClass(tab, 'bx-gadgets-tab-new'))
		BX.addClass(tab, 'bx-gadgets-tab-new-active');
	else if (BX.hasClass(tab, 'bx-gadgets-tab'))
		BX.addClass(tab, 'bx-gadgets-tab-active');

	var node_div = BX(tab.id+'_content_node');

	if(node_div && node_div.innerHTML.length <= 0)
	{
		BX.ajax.get(url, function(result)
		{
			if (tabControl)
				tabControl.closeWait(content_div);
			node_div.innerHTML = result;
		})
		if (tabControl)
			tabControl.showWait(content_div);
	}
}

gdTabControl.prototype.showWait = function(el)
{
	if (BX.type.isElementNode(el))
	{
		var pos = BX.pos(el);

		el.bxwaiter = document.body.appendChild(BX.create('DIV', {
			props: {className: 'adm-gadget-tab-waiter-img'},
			style: {
				top: parseInt((pos.bottom + pos.top)/2 - 10) + 'px',
				left: parseInt((pos.right + pos.left)/2 - 10) + 'px'
			}
		}));

		this.lastWaitElement = el;

		return el.bxwaiter;
	}
}

gdTabControl.prototype.closeWait = function(el)
{
	el = el || this.lastWaitElement;

	if (BX.type.isElementNode(el))
	{
		if (el.bxwaiter)
		{
			el.bxwaiter.parentNode.removeChild(el.bxwaiter);
			el.bxwaiter = null;
		}

		if (this.lastWaitElement == el)
			this.lastWaitElement = null;
	}
}
