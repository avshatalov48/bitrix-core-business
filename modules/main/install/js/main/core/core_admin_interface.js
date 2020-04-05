;(function(){

if (!!BX.adminPanel)
	return;

/*************************** admin panel **************************************/

BX.adminPanel = function()
{
	this.buttons = [];
	this.panel = null;

	this.modifyFormElements = BX.adminFormTools.modifyFormElements;
	this.modifyFormElement = BX.adminFormTools.modifyFormElement;

	this._showMenu = function(e)
	{
		if (this.CONFIG.MENU)
		{
			BX.adminShowMenu(this.BUTTON, this.CONFIG.MENU, {active_class: 'adm-header-language-active'});
		}

		return BX.PreventDefault(e);
	};

	BX.ready(BX.defer(this.Init, this));
};

BX.adminPanel.isFixed = BX.False;

BX.adminPanel.prototype.Init = function()
{
	this.panel = BX('bx-panel');

	if (!!this.panel)
	{
		for (var i = 0; i<this.buttons.length; i++)
		{
			this.buttons[i].BUTTON = BX(this.buttons[i].ID);
			if (this.buttons[i].BUTTON)
			{
				if (this.buttons[i].CONFIG.MENU)
				{
					this.setButtonMenu(this.buttons[i]);
				}
			}
		}

		(BX.defer(this._recountWrapHeight, this))();
	}
};

BX.adminPanel.prototype.registerButton = function(id, config)
{
	this.buttons.push({ID: id, CONFIG: config});
};

BX.adminPanel.prototype.setButtonMenu = function(button)
{
	BX.bind(button.BUTTON, 'click', BX.delegate(this._showMenu, button))
};

BX.adminPanel.prototype.isFixed = function()
{
	return BX.hasClass(document.documentElement, 'adm-header-fixed');
};

BX.adminPanel.prototype.Fix = function(el)
{
	var bFixed = this.isFixed();

	if (bFixed)
	{
		this.panel.parentNode.style.height = 'auto';
		BX.removeClass(document.documentElement, 'adm-header-fixed');
		el.title = BX.message('JSADM_PIN_ON');
	}
	else
	{
		BX.addClass(document.documentElement, 'adm-header-fixed');
		el.title = BX.message('JSADM_PIN_OFF');
		(BX.defer(this._recountWrapHeight, this))();
	}

	BX.userOptions.save('admin_panel', 'settings', 'fix', (bFixed ? 'off':'on'));
	BX.onCustomEvent('onAdminPanelFix', [!bFixed]);
};

BX.adminPanel.prototype.addDesktop = function()
{
	(new BX.CAdminDialog({
		'content_url': '/bitrix/components/bitrix/desktop/admin_settings.php?lang='+BX.message('LANGUAGE_ID')+'&bxpublic=Y',
		'content_post': 'sessid='+BX.bitrix_sessid()+'&type=desktop&desktop_page=0&action=new&desktop_backurl=/bitrix/admin/',
		'draggable': true,
		'resizable': true,
		'buttons': [BX.CAdminDialog.btnSave, BX.CAdminDialog.btnCancel]
	})).Show();
};

BX.adminPanel.prototype.recalcDesktopSettingsDialog = function(e)
{
	if(!e)
		e = window.event;

	var col_count = this.value;
	if (e.type == 'blur' && col_count.length <= 0)
	{
		col_count = current_col_count;
		BX('SETTINGS_COLUMNS').value = col_count;
	}
	else if (e.type == 'keyup' && (parseInt(col_count) <= 0	|| parseInt(col_count) >= 10))
	{
		current_col_count = col_count = 2;
		BX('SETTINGS_COLUMNS').value = col_count;
	}
	else if (e.type == 'keyup' && col_count.length > 0)
		current_col_count = col_count;

	var tableNode = BX.findParent(this, {'tag':'tbody'});

	var arItems = BX.findChildren(tableNode, {'tag':'tr', 'class':'bx-gd-admin-settings-col'}, true);
	if (!arItems)
		arItems = [];

	for (var i = 0; i < arItems.length; i++)
	{
		if (i >= col_count)
			arItems[i].parentNode.removeChild(arItems[i]);
	}

	var col_add = col_count - i;

	for (i = 0; i < col_add; i++)
	{
		tableNode.appendChild(BX.create('tr', {
			props: {
				'className': 'bx-gd-admin-settings-col'
			},
			children: [
				BX.create('td', {
					attrs: {
						'width': '40%'
					},
					html: BX.message('langGDSettingsDialogRowTitle') + (parseInt(arItems.length) + parseInt(i) + 1)
				}),
				BX.create('td', {
					attrs: {
						'width': '60%'
					},
					children: [
						BX.create('input', {
							attrs: {
								'type': 'text',
								'size': '5',
								'maxlength': '6'
							},
							props: {
								'id': 'SETTINGS_COLUMN_WIDTH_' + (arItems.length + i),
								'name': 'SETTINGS_COLUMN_WIDTH_' + (arItems.length + i),
								'value': ''
							}
						})
					]
				})
			]
		}));
	}
};

BX.adminPanel.prototype.setTitle = function(title)
{
	document.title = BX.message('TITLE_PREFIX') + title;
	var p = BX('adm-title');
	if (p)
	{
		if (p.firstChild && p.firstChild.nodeType == 3)
		{
			p.replaceChild(document.createTextNode(title), p.firstChild);
		}
		else if (p.firstChild)
		{
			p.insertBefore(p.firstChild, document.createTextNode(title));
		}
		else
		{
			BX.adjust(p, {text: title});
		}
	}
};

BX.adminPanel.prototype._recountWrapHeight = function()
{
	if (this.isFixed())
		BX.adminPanel.panel.parentNode.style.height = BX.adminPanel.panel.offsetHeight + 'px';
	BX.onCustomEvent(this, 'onAdminPanelChange');
};

BX.adminPanel.prototype.Notify = function(str)
{
	if (!BX.isReady)
	{
		var _args = arguments;
		BX.ready(BX.defer(function() {BX.adminPanel.Notify.apply(this, _args);}));
		return;
	}

	if (null == BX.adminPanel.NOTIFY)
	{
		BX.adminPanel.NOTIFY = BX.adminPanel.panel.appendChild(BX.create('DIV', {
			props: {className: 'adm-warning-block'},
			html:
				'<span class="adm-warning-text">'+(str||'&nbsp;')+'</span><span class="adm-warning-icon"></span><span onclick="BX.adminPanel.hideNotify(this.parentNode)" class="adm-warning-close"></span>'
		}));

	}
	else
	{
		BX.adminPanel.NOTIFY.firstChild.innerHTML = str||'&nbsp;';
	}

	BX.removeClass(BX.adminPanel.NOTIFY, 'adm-warning-animate');

	(BX.defer(this._recountWrapHeight, this))();
	setTimeout(BX.proxy(this._recountWrapHeight, this), 310);
};

BX.adminPanel.hideNotify =
BX.adminPanel.prototype.hideNotify = function(element)
{
	element = BX.type.isDomNode(element)? element: this;

	if (!!element && !!element.parentNode && !!element.parentNode.parentNode)
	{
		element.style.height = element.offsetHeight + "px";
		setTimeout(function() {BX.addClass(element, 'adm-warning-animate');}, 50);
	}

	if (BX.type.isDomNode(element) && element.getAttribute('data-ajax') == "Y")
	{
		var notifyId = parseInt(element.getAttribute('data-id'));
		if (notifyId > 0)
		{
			BX.ajax({
				url: '/bitrix/admin/admin_notify.php',
				method: 'POST',
				dataType: 'json',
				data: {'ID' : notifyId, 'sessid': BX.bitrix_sessid()}
			});
		}
	}

	(BX.defer(this._recountWrapHeight, this))();
	setTimeout(BX.proxy(this._recountWrapHeight, this), 310);
};

BX.adminPanel.Redirect =
BX.adminPanel.prototype.Redirect = function(args, url, e)
{
	var bShift = false;
	if(args && args.length > 0)
		e = args[0];
	if(!e)
		e = window.event;

	try{
		if(e) bShift = e.shiftKey;
	}catch(e){}

	if(bShift)
		window.open(url);
	else
	{
		window.location.href=url;
	}
};

BX.adminPanel._reShowWait =
BX.adminPanel.prototype._reShowWait = function()
{
	BX.adminPanel.closeWait(this);
	BX.adminPanel.showWait(this);
};

BX.adminPanel.showWait =
BX.adminPanel.prototype.showWait = function(el)
{
	if (el && !BX.type.isElementNode(el))
		el = null;
	el = el || this;

	if (BX.type.isElementNode(el)
		&& (el.type == 'button' || el.type == 'submit')
		&& !!el.name
		&& BX.util.in_array(el.name.toLowerCase(), ['save', 'apply', 'cancel', 'save_and_add', 'set_filter', 'del_filter', 'template_preview'])
		)
	{
		if (el.disabled == true)
			return null;
		BX.defer(function(){el.disabled = true})();

		var bSave = (BX.hasClass(el, 'adm-btn-save') || BX.hasClass(el, 'adm-btn-save')),
			waiter_parent = BX.findParent(el, BX.is_relative),
			pos = BX.pos(el, !!waiter_parent);

		el.bxwaiter = (waiter_parent || document.body).appendChild(BX.create('DIV', {
			props: {className: 'adm-btn-load-img' + (bSave ? '-green' : '')},
			style: {
				top: parseInt((pos.bottom + pos.top)/2 - 9) + 'px',
				left: parseInt((pos.right + pos.left)/2 - 9) + 'px'
			}
		}));

		BX.addClass(el, 'adm-btn-load');

		BX.adminPanel.lastWaitElement = el;

		return el.bxwaiter;
	}
	return null;
};

BX.adminPanel.closeWait =
BX.adminPanel.prototype.closeWait = function(el)
{
	if (el && !BX.type.isElementNode(el))
		el = null;
	el = el || BX.adminPanel.lastWaitElement || this;

	if (BX.type.isElementNode(el))
	{
		if (el.bxwaiter && el.bxwaiter.parentNode)
		{
			el.bxwaiter.parentNode.removeChild(el.bxwaiter);
			el.bxwaiter = null;
		}

		el.disabled = false;
		BX.removeClass(el, 'adm-btn-load');

		if (BX.adminPanel.lastWaitElement == el)
			BX.adminPanel.lastWaitElement = null;
	}

};

/**************************** admin forms *************************************/

BX.adminFormTools = {
	modifyFormElements: function(tbl, types)
	{
		var el = BX.findFormElements(tbl);

		if (el && el.length > 0)
		{
			for (var i = 0; i < el.length; i++)
			{
				BX.adminFormTools.modifyFormElement(el[i], types)
			}
		}
	},

	modifyFormElement: function(el, types)
	{
		if (typeof types == 'undefined' || !BX.type.isArray(types))
			types = ['checkbox', 'file'];

		if (el && BX.type.isElementNode(el) && !!el.type)
		{
			if (BX.util.in_array('*', types) || BX.util.in_array(el.type, types))
			{
				switch(el.type)
				{
					case 'checkbox': return BX.adminFormTools.modifyCheckbox(el);
					case 'file': return BX.adminFormTools.modifyFile(el);

					case 'select-one':
					case 'select-multiple':
						return BX.adminFormTools.modifySelect(el);

					case 'button':
					case 'submit':
					case 'reset':
						return BX.adminFormTools.modifyButton(el);

					default: return el;
				}
			}
			else
			{
				return el;
			}
		}
		return null;
	},

	modifyCheckbox: function(el)
	{
		if ((!BX.browser.IsIE() || BX.browser.IsIE9()) && BX.type.isElementNode(el) && el.tagName.toUpperCase() == 'INPUT' && el.type.toUpperCase() == 'CHECKBOX')
		{
			if (!BX.hasClass(el, 'adm-designed-checkbox'))
			{
				if (!el.id)
					el.id = 'designed_checkbox_' + Math.random();

				var label = BX.create('LABEL', {
					props: {
						className: 'adm-designed-checkbox-label',
						htmlFor: el.id,
						title: el.title
					}
				});

				BX.addClass(label, el.className);
				BX.addClass(el, 'adm-designed-checkbox');

				if (!!el.nextSibling)
					el.parentNode.insertBefore(label, el.nextSibling);
				else
					el.parentNode.appendChild(label);
			}
		}
	},

	modifyFile: function(el)
	{
		if (!BX.hasClass(el, 'adm-designed-file') && !el.hasAttribute('data-fileinput'))
		{
			var wrap = BX.create('SPAN', {
				props: {className: 'adm-input-file'},
				html: '<span>' + (!!el.multiple ? BX.message('JSADM_FILES') : BX.message('JSADM_FILE')) + '</span>'
			});

			BX.bind(el, 'change', BX.adminFormTools._modified_file_onchange);

			BX.addClass(el, 'adm-designed-file');

			if (el.parentNode)
				el.parentNode.insertBefore(wrap, el);

			wrap.appendChild(el);

			return wrap;
		}
		else
		{
			return el;
		}
	},

	_modified_file_onchange: function()
	{
		var v = this.files || [this.value], s = '';
		if (!v || v.length <= 0)
		{
			s = (!!this.multiple ? BX.message('JSADM_FILES') : BX.message('JSADM_FILE'));
		}
		else
		{
			s = '';
			for(var i = 0; i < v.length; i++)
			{
				var n = v[i].name || v[i];
				var p = Math.max(n.lastIndexOf('/'), n.lastIndexOf('\\'));
				if (p > 0)
					n = n.substring(p+1, n.length);
				s += (s == '' ? '' : ', ') + n;
			}
		}

		this.parentNode.firstChild.innerHTML = s;
	},

	// should not be called in modifyFormElements!
	modifySelect: function(el)
	{
		if (BX.type.isElementNode(el) && el.tagName.toUpperCase() == 'SELECT')
		{
			if (el.type == 'select-one')
			{
				if (!BX.hasClass(el, 'adm-select'))
				{
					var wrap = BX.create('SPAN', {
						props: {className: 'adm-select-wrap'}
					});

					BX.addClass(el, 'adm-select');

					if (el.parentNode)
						el.parentNode.insertBefore(wrap, el);

					wrap.appendChild(el);

					return wrap;
				}
			}
		}
		return null;
	},

	modifyButton: function(el)
	{
		if (BX.type.isElementNode(el) && el.tagName.toUpperCase() == 'INPUT'
			&& (el.type == 'submit' || el.type == 'reset' || el.type == 'button')
			&& !BX.hasClass(el, 'adm-btn') && !BX.hasClass(el, 'adm-btn-save') && !BX.hasClass(el, 'adm-btn-green')
		)
		{
			var wrap = BX.create('SPAN', {props: {className: 'adm-btn-wrap ' + el.className}});

			el.className = 'adm-btn';

			if (el.parentNode)
				el.parentNode.insertBefore(wrap, el);

			wrap.appendChild(el);
			return wrap;
		}
		else
		{
			return el;
		}
	}
};

/*************************** admin menu ***************************************/

BX.adminMenu = function()
{
	this.activeSection = '';
	this.oSections = {};
	this.items = [];
	this.easing = {};

	var dest = this.dest = {item: null, fav: null};
	this.dest_cont = {item: null, fav: null};

	this.bMinimized = false;

	var currentDest = null;

	var _admin_fav_callback = function(result){
		var key = this.BXTYPEKEY;
		BX('fav_text_' + key).style.display = 'none';
		if(result)
		{
			BX('fav_text_error_' + key).style.display = 'none';
			BX('fav_text_finish_' + key).style.display = 'inline-block';
			BX('fav_icon_finish_' + key).style.display = 'inline-block';
		}
		else
		{
			BX('fav_text_finish_' + key).style.display = 'none';
			BX('fav_icon_finish_' + key).style.display = 'none';
			BX('fav_text_error_' + key).style.display = 'inline-block';
		}

		BX.adminFav.refresh(result);

		this.bxprogress = false;
		if (!this.bxover)
		{
			_ondestdragstop.apply(this);
		}
	};

	var _ondestdragfinish = this._ondestdragfinish = {
		item: BX.delegate(function(node)
		{
			if (typeof node.BXMENUITEM == 'undefined' || !this.items[node.BXMENUITEM])
				return;

			currentDest = 'item';
			this.dest[currentDest].bxprogress = true;

			var favName = this.items[node.BXMENUITEM].CONFIG.TEXT || node.innerText || node.textContent;

			//adding title before filter name
			if(this.items[node.BXMENUITEM].CONFIG.TITLE)
			{
				var title = BX("adm-title");

				if (title)
				{
					var favNameTitle = title.textContent || title.innerText;

					if(favNameTitle)
						favName = favNameTitle +": "+favName;
				}
			}

			BX.adminFav.add(
				favName,
				this.items[node.BXMENUITEM].CONFIG.URL,
				this.items[node.BXMENUITEM].CONFIG.ID,
				this.items[node.BXMENUITEM].CONFIG.MODULE_ID,
				BX.proxy(_admin_fav_callback, this.dest.item)
			);

		}, this),
		fav: BX.delegate(function(node)
		{
			if (typeof node.BXMENUITEM == 'undefined' || !this.items[node.BXMENUITEM] || !this.items[node.BXMENUITEM].CONFIG.FAV_ID)
				return;

			currentDest = 'fav';
			this.dest[currentDest].bxprogress = true;

			BX.adminFav.del(this.items[node.BXMENUITEM].CONFIG.FAV_ID, BX.proxy(_admin_fav_callback, this.dest.fav));

		}, this)

	};

	var __r = function(){jsDD.refreshDestArea(this)};
	var _ondestdragstart = BX.delegate(function(node)
	{
		if (typeof node.BXMENUITEM == 'undefined' || !this.items[node.BXMENUITEM])
			return;

		var key = !!this.items[node.BXMENUITEM].CONFIG.FAV_ID ? 'fav' : 'item';

		this.dest_cont[key].bxprogress = true;
		BX.adminFav.showDDBlock(this.dest_cont[key], BX.proxy(__r, this.dest[key]));

		BX.bind(window, 'scroll', BX.proxy(__r, this.dest[key]));
	}, this);

	var _destmsover = function() {this.bxover = true;};
	var _destmsout = function(){
		this.bxover = false;
		setTimeout(BX.delegate(function()
			{
				if (!this.bxover && !this.bxprogress)
					_ondestdragstop.apply(this);
			}, this), 100);
	};

	var _ondestdragstop = this._ondestdragstop = BX.delegate(function()
	{
		if (!currentDest || currentDest == BX.proxy_context.BXTYPEKEY)
		{
			var key = BX.proxy_context.BXTYPEKEY;
			if (currentDest == BX.proxy_context.BXTYPEKEY)
			{
				this.dest_cont.bxover = true;

				BX.bind(BX.proxy_context, 'mouseover', _destmsover);
				BX.bind(BX.proxy_context, 'mouseout', _destmsout);
				currentDest = null;
			}
			else
			{
				BX.adminFav.hideDDBlock(this.dest_cont[key], function() {
					BX('fav_text_' + key).style.display = 'inline-block';
					BX('fav_text_error_' + key).style.display = 'none';
					BX('fav_text_finish_' + key).style.display = 'none';
					BX('fav_icon_finish_' + key).style.display = 'none';
				});

				BX.unbind(window, 'scroll', BX.proxy(__r, this.dest[key]));

				BX.unbind(BX.proxy_context, 'mouseover', _destmsover);
				BX.unbind(BX.proxy_context, 'mouseout', _destmsout);

				BX.unbind(this.dest_cont[key], 'mouseout', BX.proxy(_ondestdragstop, BX.proxy_context));
			}
		}
	}, this);

	this._onitemdragstart = function()
	{
		_ondestdragstart(this.NODE);

		if (null == this.MIRROR)
		{
			this.MIRROR = document.body.appendChild(BX.create('DIV', {
				props: {
					className: BX.hasClass(this.NODE, 'adm-main-menu-item')
						? 'adm-favorites-main-menu-wrap' : 'adm-favorites-sub-menu-wrap'
				},
				html: this.NODE.outerHTML||this.NODE.innerHTML
			}));
		}

		this.MIRROR.style.display = 'block';
	};

	this._onitemdrag = function(x, y)
	{
		var wndSize = BX.GetWindowSize();

		var top = parseInt(y - this.MIRROR.offsetHeight/2),
			left = parseInt(x - this.MIRROR.offsetWidth/2);

		var leftBorder = wndSize.scrollLeft + wndSize.innerWidth-20;

		if (left + this.MIRROR.offsetWidth > leftBorder)
			left -= left + this.MIRROR.offsetWidth - leftBorder;
		if (left <= wndSize.scrollLeft)
			left = wndSize.scrollLeft;

		this.MIRROR.style.left = left + 'px';
		this.MIRROR.style.top = top + 'px';
	};

	this._onitemdragstop = function()
	{
		this.MIRROR.style.display = 'none';
	};

	BX.ready(BX.delegate(this.Init, this));
};

BX.adminMenu.prototype.Init = function()
{
	if (!!BX('bx_menu_panel', true))
	{
		for (var key in this.dest)
		{
			this.dest[key] = BX('fav_dest_' + key);
			this.dest_cont[key] = BX('fav_cont_' + key);

			this.dest[key].BXTYPEKEY = key;
			this.dest[key].onbxdestdraghover = function() {BX.addClass(this, 'adm-favorites-center-hover')};
			this.dest[key].onbxdestdraghout = function() {BX.removeClass(this, 'adm-favorites-center-hover')};
			this.dest[key].onbxdestdragfinish = this._ondestdragfinish[key];
			this.dest[key].onbxdestdragstop = BX.proxy(this._ondestdragstop, this.dest[key]);

			jsDD.registerDest(this.dest[key]);
		}

		setTimeout(BX.delegate(this.InitDeferred, this), 200);
	}
};

BX.adminMenu.prototype.InitDeferred = function()
{
	new BX.adminMenuResizer(BX('bx_menu_panel', true), !!this.bMinimized);

	for(var i=0; i<this.items.length; i++)
	{
		this._registerItem(i);
	}
};

BX.adminMenu.prototype.showFavorites = function(el)
{
	if(!!el)
		BX.fireEvent(el, 'mouseout');

	this.GlobalMenuClick('desktop');

	if (BX.adminFav.lastId > 0)
	{
		var node = BX.findChild(BX('_global_menu_desktop'), {attr: {
			'data-fav-id': BX.adminFav.lastId
		}}, true);

		if (!!node)
		{
			BX.defer(function(){
				var pos = BX.pos(node),
					wndSize = BX.GetWindowSize(),
					scrollBottom = wndSize.scrollTop + wndSize.innerHeight;

				if (pos.bottom > scrollBottom || pos.top < wndSize.scrollTop)
				{
					window.scrollTo(wndSize.scrollLeft, pos.top - parseInt(wndSize.innerHeight/2));
				}

				BX.addClass(node, 'adm-submenu-current-fav');
				(new BX.easing({
					duration : 1200,
					start:{opacity: 0},
					finish:{opacity: 100},
					transition: function(progress) {
						return Math.abs(Math.sin(3 * Math.PI * progress / 2));
					},
					step : function(state){
						node.style.background = 'rgba(242,245,220,'+(state.opacity/100)+')'
					},
					complete: function()
					{
						node.style.background = '#f2f5dc';
						var f = function(){
							this.style.background = null;
							BX.removeClass(this, 'adm-submenu-current-fav');
							BX.unbind(this, 'mouseover', f);
						};
						BX.bind(node, 'mouseover', f);
					}
				})).animate();
			})();
		}
	}
};

BX.adminMenu.prototype.itemsStretchScroll = function()
{
	BX.onCustomEvent(BX.adminMenu, 'onAdminMenuItemsStretchScroll');
};

BX.adminMenu.prototype.setMinimizedState = function(state)
{
	this.bMinimized = state;
};

BX.adminMenu.prototype.setActiveSection = function(section_id)
{
	this.activeSection = section_id;
};

BX.adminMenu.prototype.setOpenedSections = function(sSections)
{
	var aSect = sSections.split(',');
	for(var i in aSect)
	{
		this.oSections[aSect[i]] = true;
	}
};

BX.adminMenu.prototype.GlobalMenuClick = function(id)
{
	if (id == this.activeSection)
	{
		return;
	}

	if (!!this.activeSection)
	{
		BX.removeClass(BX('global_menu_' + this.activeSection, true), 'adm-main-menu-item-active');
		//BX.hide(BX('global_submenu_' + this.activeSection, true));
		BX.removeClass(BX('global_submenu_' + this.activeSection, true), "adm-global-submenu-active adm-global-submenu-animate");
	}

	this.activeSection = id;

	BX.addClass(BX('global_menu_' + this.activeSection, true), 'adm-main-menu-item-active');
	//BX.show(BX('global_submenu_' + this.activeSection, true));
	BX.addClass(BX('global_submenu_' + this.activeSection, true), "adm-global-submenu-active");

	if (BX.browser.isPropertySupported("transition"))
	{
		BX('global_submenu_' + id, true).style.opacity = 0;
		setTimeout(function() { BX.addClass(BX('global_submenu_' + id, true), "adm-global-submenu-animate"); }, 0);
	}

	BX.onCustomEvent(this, 'onMenuChange');
};

BX.adminMenu.prototype.startAnimation = function(cell, div_id, opening)
{
	if (!this.easing[div_id])
	{
		this.easing[div_id] = {
			icon : BX.findChild(cell, { className : "adm-submenu-item-arrow-icon"}, true),
			animation : null,
			opening : opening,
			childrenCont: cell.childNodes[1],
			startHeight : 0
		};
	}

	if (this.easing[div_id].animation)
		this.easing[div_id].animation.stop();

	this.easing[div_id].opening = opening;
	this.easing[div_id].startHeight = this.easing[div_id].childrenCont.offsetHeight;
	this.easing[div_id].childrenCont.style.overflowY = "hidden";
	this.easing[div_id].childrenCont.style.height = this.easing[div_id].startHeight + "px";

	BX.addClass(this.easing[div_id].childrenCont, "adm-sub-submenu-block-children-animate");

	if (BX.browser.isPropertySupported("transform"))
		BX.addClass(this.easing[div_id].icon, "adm-submenu-item-arrow-icon-animate");
};

BX.adminMenu.prototype.endAnimation = function(div_id)
{
	if (!this.easing[div_id])
		return;

	var opening = this.easing[div_id].opening;
	var arrowIcon = this.easing[div_id].icon;
	var divCont = this.easing[div_id].childrenCont;
	var rotateProperty = BX.browser.isPropertySupported("transform");

	this.easing[div_id].animation = new BX.easing({

		duration : 200,

		start : {
			rotation: 0,
			height : this.easing[div_id].startHeight,
			opacity : opening ? 0 : 100
		},

		finish : {
			rotation: opening ? 90 : -90,
			height : opening ? this.easing[div_id].childrenCont.scrollHeight : 0,
			opacity : opening ? 100 : 0
		},

		transition : BX.easing.transitions.linear,

		step : function(state) {

			if (rotateProperty !== false)
			{
				arrowIcon.style[rotateProperty] = "rotate(" + state.rotation + "deg)" +
					(rotateProperty == "WebkitTransform" ? " translate3d(0, 0, 0)" : "" );
			}

			divCont.style.height = state.height + "px";
			divCont.style.opacity = state.opacity/100;
		},

		complete : BX.proxy(function() {
			arrowIcon.style.cssText = "";
			divCont.style.cssText = "";
			BX.removeClass(arrowIcon, "adm-submenu-item-arrow-icon-animate");
			BX.removeClass(divCont, "adm-sub-submenu-block-children-animate");
			this.easing[div_id].animation = null;
		}, this)
	});

	this.easing[div_id].animation.animate();
};

BX.adminMenu.prototype.toggleSection = function(cell, div_id, level)
{
	var res = !BX.hasClass(cell, 'adm-sub-submenu-open');

	this.startAnimation(cell, div_id, res);

	if (res)
		BX.addClass(cell, 'adm-sub-submenu-open');
	else
		BX.removeClass(cell, 'adm-sub-submenu-open');

	this.endAnimation(div_id);

	if(level <= 2)
	{
		this.oSections[div_id] = res;

		var sect='';
		for(var i in this.oSections)
		{
			if(this.oSections[i] == true)
			{
				sect += (sect != ''? ',':'')+i;
			}
		}

		BX.userOptions.save('admin_menu', 'pos', 'sections', sect);
	}

	BX.onCustomEvent(this, 'onMenuChange');

	return res;
};

BX.adminMenu.prototype.toggleDynSection = function(padding, cell, module_id, div_id, level)
{
	if (cell.BXLOAD)
	{
		this.toggleSection(cell, div_id, level);
		return;
	}

	cell.BXLOAD = true;
	cell.BXLOAD_AJAX = false;

	var img = BX.create('SPAN', {
		props: {className: 'adm-submenu-loading adm-sub-submenu-block'},
		style: {marginLeft: parseInt(padding) + 'px'},
		text: BX.message('JS_CORE_LOADING')
	});

	setTimeout(BX.proxy(function() {
		if (!cell.BXLOAD_AJAX)
		{
			cell.childNodes[1].appendChild(img);
			this.toggleSection(cell, div_id, level);
		}
	}, this), 200);

	BX.ajax.get(
		'/bitrix/admin/get_menu.php',
		{
			lang: BX.message('LANGUAGE_ID'),
			admin_mnu_module_id: module_id,
			admin_mnu_menu_id: div_id
		},
		BX.proxy(function(result)
		{
			cell.BXLOAD_AJAX = true;
			result = BX.util.trim(result);
			if (result != '')
			{
				var toggleExecuted = img.parentNode ? true : false;
				cell.childNodes[1].innerHTML = result;
				if (!toggleExecuted)
					this.toggleSection(cell, div_id, level);
			}
			else
			{
				img.innerHTML = BX.message('JS_CORE_NO_DATA');
				if (!img.parentNode)
				{
					cell.childNodes[1].appendChild(img);
					this.toggleSection(cell, div_id, level);
				}
			}
			BX.onCustomEvent(this, 'onMenuChange');
		}, this)
	);

};

BX.adminMenu.prototype._item_onmouseover = function()
{
	this.bxover = true;
	setTimeout(BX.proxy(BX.adminMenu.__item_onmouseover, this), 500);
};

BX.adminMenu.prototype._item_onmouseout = function()
{
	this.bxover = false;
	setTimeout(BX.proxy(BX.adminMenu.__item_onmouseout, this), 50);
};

BX.adminMenu.prototype.__item_onmouseover = function()
{
	if (this.bxover)
	{
		var pos1 = BX.pos(this.NODE.lastChild.lastChild);

		if (pos1.right > BX('bx_menu_panel', true).offsetWidth)
		{
			var pos = BX.pos(this.NODE);

			if (!this.MSOVERMIRROR)
			{
				var bActive = BX.hasClass(this.NODE.parentNode, 'adm-submenu-item-active');
				this.MSOVERMIRROR = BX('menu_mirrors_cont').appendChild(BX.create('DIV', {
					props: {
						className: 'adm-submenu-longname' + (bActive ? ' adm-submenu-active-longname' : '')
					},
					html: this.NODE.outerHTML||this.NODE.innerHTML
				}));
			}

			BX.adjust(this.MSOVERMIRROR, {
				style: {
					top: pos.top + 'px',
					left: pos.left + 'px',
					height: pos.height + 'px',
					display: 'inline-block'
				}
			});
		}
	}
};

BX.adminMenu.prototype.__item_onmouseout = function()
{
	if (!this.bxover && !!this.MSOVERMIRROR)
	{
		this.MSOVERMIRROR.style.display = 'none';
	}
};

BX.adminMenu.prototype._registerItem = function(i)
{
	this.items[i].NODE = BX(this.items[i].ID);
	this.items[i].NODE.BXMENUITEM = i;
	if (this.items[i].NODE)
	{
		this.items[i].NODE.onbxdragstart = BX.delegate(this._onitemdragstart, this.items[i]);
		this.items[i].NODE.onbxdrag = BX.delegate(this._onitemdrag, this.items[i]);
		this.items[i].NODE.onbxdragstop = BX.delegate(this._onitemdragstop, this.items[i]);

		jsDD.registerObject(this.items[i].NODE)
	}

	var itemType = this.items[i].NODE.getAttribute('data-type');
	switch(itemType)
	{
		case 'submenu-item':
			BX.bind(this.items[i].NODE, 'mouseover', BX.proxy(this._item_onmouseover, this.items[i]));
			BX.bind(this.items[i].NODE, 'mouseout', BX.proxy(this._item_onmouseout, this.items[i]));
			BX.addCustomEvent(this, 'onAdminMenuItemsStretchScroll', BX.proxy(this._item_onmouseout, this.items[i]));
		break;
	}
};

BX.adminMenu.prototype.registerItem = function(id, config)
{
	this.items.push({ID: id, CONFIG: config});

	if (BX.isReady)
	{
		this._registerItem(this.items.length-1);
	}
};

/*************************** admin menu resizer *******************************/

BX.adminMenuResizer = function(node, startState)
{
	this.node = node;
	this.bMinimized = !!startState;

	this.min_width = 70;
	this.denySave = false;

	this.scrollLeft = 0;

	this.pos = this.pos_final = parseInt(this.node.getAttribute('data-width')) || parseInt(BX.style(this.node, 'width'));

	this.dragger = document.body.appendChild(BX.create('DIV', {
		props: {className: 'adm-resize-block' + (this.bMinimized ? ' adm-resize-block-close' : '')},
		events: {
			mouseover: function(){
				if (!this.bDrag)
				{
					var el = this;
					el.bxover = true;
					setTimeout(function(){
						if(el.bxover && !el.bDrag)
							BX.addClass(el, 'adm-resize-block-hover');
					}, 100);
				}
			},
			mouseout: function(){
				var el = this;
				el.bxover = false;
				setTimeout(function(){
					if(!el.bxover)
						BX.removeClass(el, 'adm-resize-block-hover');
				}, 50);
			}
		},
		style: {left: (this.bMinimized ? 10 : this.pos_final-5) + 'px'}
	}));

	this.minimizer = this.dragger.appendChild(BX.create('DIV', {
		props: {
			className: 'adm-resizer-btn' + (this.bMinimized ? ' adm-resizer-btn-close' : '')
		},
		style: {left: (this.bMinimized ? 10 : this.pos_final-5) + 'px'},
		events: {
			click: BX.proxy(this.Minimize, this),
			mousedown: BX.eventCancelBubble
		}
	}));

	BX.bind(this.dragger, 'dblclick', BX.proxy(this.Minimize, this));

	this.dragger.onbxdragstart = BX.delegate(this.Start, this);
	this.dragger.onbxdrag = BX.delegate(this.Drag, this);
	this.dragger.onbxdragstop = BX.delegate(this.Save, this);

	jsDD.registerObject(this.dragger);

	BX.bind(window, 'scroll', BX.delegate(this.setDraggerPos, this));
	BX.bind(window, 'resize', BX.delegate(this.setDraggerPos, this));
	this.setDraggerPos();
};

BX.adminMenuResizer.prototype.setDraggerPos = function()
{
	this.scrollLeft = BX.GetWindowScrollPos().scrollLeft;
	this.dragger.style.left = this.minimizer.style.left = ((this.bMinimized ? 10 : this.pos-5) - this.scrollLeft) + 'px';
};

BX.adminMenuResizer.prototype.Start = function()
{
	if (this.bMinimized)
		BX.removeClass(BX.firstChild(this.node), 'adm-main-menu-close');

	BX.setUnselectable(document.body);
	document.body.style.cursor = 'e-resize';

	this.dragger.bDrag = true;
	BX.removeClass(this.dragger, 'adm-resize-block-hover');
};

BX.adminMenuResizer.prototype.Drag = function(x, y)
{
	if (x >= this.min_width || this.bMinimized)
	{
		this.denySave = x < this.min_width;

		BX.removeClass(this.dragger, 'adm-resize-block-close');
		BX.removeClass(this.minimizer, 'adm-resizer-btn-close');

		if (this.bMinimized && !this.denySave)
		{
			BX.removeClass(this.node, 'adm-left-side-wrap-close');
			this.bMinimized = false;
		}

		this.pos = x;
		this.node.style.width = this.pos + 'px';
		this.setDraggerPos();
	}
	else if (!this.bMinimized)
	{
		this.Minimize();
		jsDD.stopCurrentDrag();
	}

	BX.onCustomEvent(BX.adminMenu, 'onAdminMenuResize', [this.pos]);
};

BX.adminMenuResizer.prototype.Save = function()
{
	this.dragger.bDrag = false;

	if (!this.denySave && !this.bMinimized)
	{
		this.pos_final = this.pos;
	}

	if (this.denySave)
	{
		if (this.bMinimized)
			this.Maximize();

		this.denySave = false;
	}

	if (!this.denySave)
	{
		BX.onCustomEvent(BX.adminMenu, 'onAdminMenuResize', [this.pos]);

		BX.setSelectable(document.body);
		document.body.style.cursor = '';

		// check BX.fireEvent()
		if(window.onresize)
			window.onresize();

		if (!this.bMinimized)
			BX.userOptions.save('admin_menu', 'pos', 'width', this.pos_final);

		BX.userOptions.save('admin_menu', 'pos', 'ver', this.bMinimized ? 'off' : 'on');
	}
};

BX.adminMenuResizer.prototype.Minimize = function()
{
	if (this.bMinimized)
		return this.Maximize();

	BX.addClass(this.minimizer, 'adm-resizer-btn-animate');
	BX.addClass(this.node, 'adm-left-side-wrap-close');

	var easing = new BX.easing({
		duration : 400,
		start:{width: this.pos},
		finish:{width: 15},
		transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
		step : BX.delegate(function(state){
			this.node.style.width = state.width + 'px';
			this.dragger.style.left = this.minimizer.style.left = (state.width-5-this.scrollLeft) + 'px';

			BX.onCustomEvent(BX.adminMenu, 'onAdminMenuResize', [state.width]);
		}, this),
		complete: BX.delegate(function(){
			this.pos = this.pos_final;

			BX.addClass(this.minimizer, 'adm-resizer-btn-close');
			BX.removeClass(this.minimizer, 'adm-resizer-btn-animate');

			BX.addClass(this.dragger, 'adm-resize-block-close');
			BX.addClass(BX.firstChild(this.node), 'adm-main-menu-close');

			BX.onCustomEvent(BX.adminMenu, 'onAdminMenuResize', [this.pos]);
		}, this)
	});
	easing.animate();

	this.bMinimized = true;
	this.Save();
};

BX.adminMenuResizer.prototype.Maximize = function()
{
	BX.addClass(this.minimizer, 'adm-resizer-btn-animate');
	BX.removeClass(this.node, 'adm-left-side-wrap-close');
	BX.removeClass(this.dragger, 'adm-resize-block-close');

	BX.removeClass(BX.firstChild(this.node), 'adm-main-menu-close');

	var easing = new BX.easing({
		duration : 400,
		start:{width: this.pos < this.min_width ? this.pos : 15},
		finish:{width: this.pos_final},
		transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
		step : BX.delegate(function(state){
			this.node.style.width = state.width + 'px';
			this.dragger.style.left = this.minimizer.style.left = (state.width-5-this.scrollLeft) + 'px';

			BX.onCustomEvent(BX.adminMenu, 'onAdminMenuResize', [state.width]);
		}, this),
		complete: BX.delegate(function(){
			BX.removeClass(this.minimizer, 'adm-resizer-btn-close');
			BX.removeClass(this.minimizer, 'adm-resizer-btn-animate');

			BX.onCustomEvent(BX.adminMenu, 'onAdminMenuResize', [15]);
		}, this)
	});
	easing.animate();

	this.bMinimized = false;
	this.Save();
};

/*************************** admin favorites **********************************/

BX.adminFav = {
	url: '/bitrix/admin/favorite_act.php',
	lastId: null,
	add: function(nameToSave,urlToSave,menu_id,module_id,callback)
	{
		var urlToSend = BX.adminFav.url + "?act=add&lang="+BX.message('LANGUAGE_ID'),
			data = {
				sessid: BX.bitrix_sessid(),
				name: nameToSave
			};

		if(urlToSave)
			data.addurl = urlToSave;

		if(menu_id)
			data.menu_id = menu_id;

		if (BX.type.isFunction(module_id))
		{
			callback = module_id;
			module_id = '';
		}

		if(module_id)
		{
			data.module_id = module_id;
		}

		if(!callback)
		{
			callback = function(result)
			{
				if(result)
				{
					BX.adminFav.refresh(result);
					alert(BX.message('JSADM_FAV_ADD_SUC'));
				}
				else
				{
					alert(BX.message('JSADM_FAV_ADD_ERR'));
				}
			}
		}

		return BX.ajax.post(urlToSend,data,callback);
	},

	del: function(id, callback)
	{
		var urlToSend = BX.adminFav.url + "?act=delete&id="+id,
			data = {sessid: BX.bitrix_sessid()};

		if(!callback)
		{
			callback = function(result)
			{
				if(result)
				{
					BX.adminFav.refresh(result);
					alert(BX.message('JSADM_FAV_DEL_SUC'));
				}
				else
				{
					alert(BX.message('JSADM_FAV_DEL_ERR'));
				}
			}
		}

		return BX.ajax.post(urlToSend,data,callback);
	},

	refresh: function(htmlMenu)
	{
		if(!htmlMenu)
			return;

		var menu = BX("_global_menu_desktop");
		menu.innerHTML = htmlMenu;

		BX.adminFav.setActiveItem();
	},

	setActiveItem: function()
	{
		var menu = BX("menucontainer");
		var activeItem = BX.findChild(menu, { className: "adm-submenu-item-active"}, true);

		if(!activeItem)
			return false;

		var itemNameLink = BX.findChild(activeItem, { className: "adm-submenu-item-name-link"}, true).href;
		var itemNameLinkText = BX.findChild(activeItem, { className: "adm-submenu-item-name-link-text"}, true);

		var itemText = itemNameLinkText.textContent || itemNameLinkText.innerText;
		itemText =  BX.util.trim(itemText);

		var favMenu = BX("_global_menu_desktop");

		var favMenuItems = BX.findChildren(favMenu, { className: "adm-sub-submenu-block"}, true);

		for(var idx in favMenuItems)
		{
			var favItemNameLink = BX.findChild(favMenuItems[idx], { className: "adm-submenu-item-name-link"},true).href;
			var favItemNameLinkText = BX.findChild(favMenuItems[idx], { className: "adm-submenu-item-name-link-text"}, true);
			var favItemText = favItemNameLinkText.textContent || favItemNameLinkText.innerText;
			favItemText = BX.util.trim(favItemText);

			if((favItemNameLink == itemNameLink) && itemNameLink != "javascript:void(0)")
			{
				BX.addClass(favMenuItems[idx],"adm-submenu-item-active");
				return true;
			}

			if(itemText && itemText == favItemText)
			{
				BX.addClass(favMenuItems[idx],"adm-submenu-item-active");
				return true;
			}
		}

		return false;
	},

	setLastId: function(id)
	{
		BX.adminFav.lastId = id;
	},

	titleLinkClick: function(el, fav_id, items_id)
	{
		BX.adminFav.titleLink = el;
		BX.adminFav.titleNode = el.parentNode;

		if (!el.BXFAVSET)
		{
			el.BXFAVID = fav_id;
			el.BXITEMSID = items_id;

			if (!!el.BXFAVID)
				BX.adminFav._titleLinkClickDel();
			else
				BX.adminFav._titleLinkClickAdd();

			el.BXFAVSET = true;
		}
	},

	_titleLinkClickAdd: function()
	{
		BX.adminFav.add(
			BX.adminFav.titleNode.textContent||BX.adminFav.titleNode.innerText,
			BX.adminHistory.pushSupported ? window.location.pathname+window.location.search : BX('navchain-link').getAttribute('href'),
			BX.adminFav.titleLink.BXITEMSID,
			'',
			function(result) {
				if (result)
				{
					BX.adminFav.refresh(result);

					// we should somehow get fav_id here
					BX.adminFav.titleLink.BXFAVID = BX.adminFav.lastId;

					BX.addClass(BX.adminFav.titleLink, 'adm-fav-link-active');
					BX.adminFav.titleLink.title = BX.message('JSADM_FAV_DEL');

					BX.unbind(BX.adminFav.titleLink, 'click', BX.adminFav._titleLinkClickAdd);
					BX.bind(BX.adminFav.titleLink, 'click', BX.adminFav._titleLinkClickDel);
				}
				else
				{
					alert(BX.message('JSADM_FAV_ADD_ERR'));
				}
			}
		);
	},

	_titleLinkClickDel: function()
	{
		BX.adminFav.del(
			BX.adminFav.titleLink.BXFAVID,
			function(result) {
				if (result)
				{
					BX.adminFav.refresh(result);
					BX.removeClass(BX.adminFav.titleLink, 'adm-fav-link-active');
					BX.adminFav.titleLink.removeAttribute('data-fav-id');
					BX.adminFav.titleLink.title = BX.message('JSADM_FAV_ADD');

					BX.unbind(BX.adminFav.titleLink, 'click', BX.adminFav._titleLinkClickDel);
					BX.bind(BX.adminFav.titleLink, 'click', BX.adminFav._titleLinkClickAdd);
				}
				else
				{
					alert(BX.message('JSADM_FAV_DEL_ERR'));
				}
			}
		);
	},

	onMenuChange: function()
	{
		if(BX.adminMenu.activeSection =='desktop')
			BX.userOptions.save('favorite', 'favorite_menu', 'stick', "Y");
		else
			BX.userOptions.save('favorite', 'favorite_menu', 'stick', "N");
	},

	showDDBlock: function(obj, callback)
	{
		if (!obj.BXVISIBLE)
		{
			obj.style.display = '';

			var start = {property: 0},
				finish = {property: -100},

				text1 = 'translate(',
				text2 = '%,0)',

				attr = BX.browser.isPropertySupported('transform');

			if (BX.browser.IsIE10())
			{
				start.property = 0;
				finish.property = 33;
				attr = 'right';
				text1 = '';
				text2 = '%';
			}
			// FF has rendering bugs in this case
			else if (!attr || BX.browser.IsFirefox())
			{
				start.property = -33;
				finish.property = 0;
				attr = 'right';
				text1 = '';
				text2 = '%';
			}

			var easing = new BX.easing({
				duration : 500,
				start:start,
				finish:finish,
				complete: callback,
				transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),

				step : function(state)
				{
					obj.style[attr] = text1 + state.property + text2;
				}
			});

			easing.animate();
			obj.BXVISIBLE = true;
		}
	},

	hideDDBlock: function(obj, callback)
	{
		if(obj.BXVISIBLE)
		{
			var start = {property: -100},
				finish = {property: 0},

				text1 = 'translate(',
				text2 = '%,0)',

				attr = BX.browser.isPropertySupported('transform');

			if (BX.browser.IsIE10())
			{
				start.property = 33;
				finish.property = -2;
				attr = 'right';
				text1 = '';
				text2 = '%';
			}
			// FF has rendering bugs in this case
			else if (!attr || BX.browser.IsFirefox())
			{
				start.property = 0;
				finish.property = -35;
				attr = 'right';
				text1 = '';
				text2 = '%';
			}

			var easing = new BX.easing({
				duration : 500,
				start:start,
				finish:finish,
				complete: function() {
					obj.style.display = 'none';
					if (callback)
						callback();
				},
				transition : BX.easing.makeEaseInOut(BX.easing.transitions.quart),

				step : function(state){
					obj.style[attr] = text1 + state.property + text2;
				}
			});

			easing.animate();
			obj.BXVISIBLE = false;
		}
	},

	closeHint: function(obj)
	{
		obj.parentNode.style.display = "none";
		BX.userOptions.save('favorites_menu', "hint", "hide", "Y");
	}
};

/**************************** admin grid ********************************/

BX.adminList = function(table_id, params)
{
	this.table_id = table_id;
	this.params = {
		context_ctrl: !!(params||{}).context_ctrl,
		context_menu: !!(params||{}).context_menu,
		FIX_HEADER: !!(params||{}).FIX_HEADER,
		FIX_FOOTER: !!(params||{}).FIX_FOOTER
	};

	this.TABLE = null;
	this.CHECKBOX = [];
	this.CHECKBOX_DISABLED = [];
	this.CHECKBOX_COUNTER = null;

	this.num_checked = 0;
	this.bSelectAllChecked = false;
	this._last_row = null;

	BX.ready(BX.defer(this.Init, this));
	BX.garbage(BX.proxy(this.Destroy, this));
};

BX.adminList.prototype.Init = function()
{
	this.TABLE = BX(this.table_id);

	this.LAYOUT = BX(this.table_id + '_result_div');
	this.FOOTER = BX(this.table_id + '_footer');
	this.FOOTER_EDIT = BX(this.table_id + '_footer_edit');
	this.FORM = document.forms['form_' + this.table_id];

	this.CHECKBOX_COUNTER = BX(this.table_id + '_selected_count');

	this.BUTTON_EDIT = BX('action_edit_button');
	this.BUTTON_DELETE = BX('action_delete_button');

	if(!!this.FORM)
	{
		this.ACTION_SELECTOR = this.FORM.elements.action;
		this.ACTION_BUTTON = this.FORM.elements.apply;
		this.ACTION_TARGET = this.FORM.elements.action_target;

		if(this.ACTION_SELECTOR)
		{
			BX.bind(this.ACTION_SELECTOR, 'change', BX.proxy(this.UpdateCheckboxCounter, this));
		}

		if(this.ACTION_TARGET)
		{
			BX.bind(this.ACTION_TARGET, 'click', BX.proxy(this.UpdateCheckboxCounter, this));
		}
	}

	if (!!this.TABLE && this.TABLE.tBodies[0] && this.TABLE.tBodies[0].rows.length > 0)
	{
		for (var i = 0; i < this.TABLE.tBodies[0].rows.length; i++)
		{
			if (this.TABLE.tBodies[0].rows[i].oncontextmenu)
			{
				BX.bind(this.TABLE.tBodies[0].rows[i], 'contextmenu', BX.proxy(function(e)
				{
					if(!this.params.context_menu)
						return;

					e = e||window.event;
					if(!this.params.context_ctrl && e.ctrlKey || this.params.context_ctrl && !e.ctrlKey || e.target && e.target.tagName.toUpperCase() == 'A')
						return;

					BX.adminList.ShowMenu({x: e.pageX || (e.clientX + document.body.scrollLeft), y: e.pageY || (e.clientY + document.body.scrollTop)}, BX.proxy_context.oncontextmenu(), BX.proxy_context);

					return BX.PreventDefault(e);

				}, this))
			}

			BX.bind(this.TABLE.tBodies[0].rows[i], 'click', BX.proxy(this.RowClick, this));
		}
	}

	var checkboxList = BX.findChildren(this.LAYOUT || this.TABLE, {tagName: 'INPUT', property: {type: 'checkbox'}}, true);
	if (!!checkboxList)
	{
		for (i = 0; i < checkboxList.length; i++)
		{
			BX.adminFormTools.modifyCheckbox(checkboxList[i]);
			if(checkboxList[i].name == 'ID[]')
			{
				if (!checkboxList[i].disabled)
				{
					BX.bind(checkboxList[i], 'click', BX.proxy(this._checkboxClick, this));
					BX.bind(checkboxList[i].parentNode, 'click', BX.proxy(this._checkboxCellClick, this));
					BX.bind(checkboxList[i].parentNode, 'dblclick', BX.PreventDefault);

					this.CHECKBOX.push(checkboxList[i]);
				}
				else
				{
					this.CHECKBOX_DISABLED.push(checkboxList[i]);
				}
			}
		}
	}

	var check = BX(this.table_id + '_check_all');

	if (this.TABLE && this.TABLE.tHead && !!this.params.FIX_HEADER)
	{
		if (check)
		{
			check.checked = false;
			this.bSelectAllChecked = false;

			var check_id = check.id;
			BX.addCustomEvent(this.TABLE.tHead, 'onFixedNodeChangeState', BX.delegate(function(state)
			{
				if (state)
				{
					check.setAttribute('id', '');
					setTimeout("BX('"+check_id+"').checked="+this.table_id+".bSelectAllChecked", 5);
				}
				else
				{
					check.checked = this.bSelectAllChecked;
					check.setAttribute('id', check_id);
				}
			}, this));
		}

		BX.Fix(this.TABLE.tHead, {type: 'top', limit_node: this.TABLE});
	}

	if (this.FOOTER || this.FOOTER_EDIT)
	{
		BX.adminFormTools.modifyFormElements(this.FOOTER || this.FOOTER_EDIT, ['*']);

		if (!!this.params.FIX_FOOTER)
		{
			BX.addCustomEvent(this.FOOTER || this.FOOTER_EDIT, 'onFixedNodeChangeState', function(state) {
				if (state)
					BX.addClass(this, 'adm-list-table-footer-fixed');
				else
					BX.removeClass(this, 'adm-list-table-footer-fixed');
			});
		}
	}

	if (this.FOOTER_EDIT)
	{
		if (!!this.params.FIX_FOOTER)
		{
			BX.Fix(this.FOOTER_EDIT, {type: 'bottom', limit_node: this.TABLE});
		}

		BX.bindDelegate(this.FOOTER_EDIT, 'click', {property:{type: /button|submit/}}, BX.adminPanel.showWait);
	}

	if (!!this.LAYOUT)
	{
		var pos = BX.pos(this.LAYOUT), wndScroll = BX.GetWindowSize();

		if (BX.adminPanel.isFixed() && BX.adminPanel.panel)
		{
			pos.top -= BX.adminPanel.panel.offsetHeight;
		}

		if (!!this.FOOTER_EDIT)
		{
			if (!!this.CHECKBOX_DISABLED[0])
			{
				pos = BX.pos(this.CHECKBOX_DISABLED[0].parentNode);
			}

			window.scrollTo(wndScroll.scrollLeft, pos.top - parseInt(wndScroll.innerHeight/2));
		}
		else if (pos.top < wndScroll.scrollTop)
		{

			window.scrollTo(wndScroll.scrollLeft, pos.top);
		}
	}

	this.UpdateCheckboxCounter();
};

BX.adminList.prototype.ReInit = function()
{
	BX.defer(this.Init, this)();
};

BX.adminList.prototype.GetAdminList = function(url, callback)
{
	url = BX.util.add_url_param(url, {'mode': 'list', 'table_id': BX.util.urlencode(this.table_id)});

	BX.ajax({
		method: 'GET',
		dataType: 'html',
		url: url,
		onsuccess: BX.delegate(function(result) {
			if (result.length > 0)
			{
				BX.closeWait(this.LAYOUT);
				BX.onCustomEvent(window, "onAdminListLoaded");
				this._GetAdminList(result);

				if (callback && BX.type.isFunction(callback))
					callback(url);
			}
		}, this),
		onfailure: function() {BX.debug('GetAdminList', arguments)}
	});
};

BX.adminList.prototype._GetAdminList = function(result)
{
	BX.adminPanel.closeWait();

	this.Destroy();
	this.LAYOUT.innerHTML = result;

	this.ReInit();

	BX.adminChain.addItems(this.table_id + "_navchain_div");
};

BX.adminList.prototype.PostAdminList = function(url)
{
	url = BX.util.remove_url_param(url, ['mode', 'table_id']);
	url += (url.indexOf('?') >= 0 ? '&' : '?') + 'mode=frame&table_id='+BX.util.urlencode(this.table_id);

	// i can only guess of the sacred meaning of this strange thing. but it had an error in previous version.
	try{this.FORM.action.parentNode.removeChild(this.FORM.action);}catch(e){}

	this.FORM.action = url;
	BX.submit(this.FORM);
};

BX.adminList.prototype.UpdateCheckboxCounter = function()
{
	if (!this.CHECKBOX_COUNTER)
		return;

	var bChecked = this.num_checked > 0 || this.ACTION_TARGET && this.ACTION_TARGET.checked;

	if (!bChecked)
	{
		if (!!this.FOOTER && !!this.params.FIX_FOOTER)
			BX.UnFix(this.FOOTER);

		BX.removeClass(this.CHECKBOX_COUNTER, 'adm-table-counter-visible');
		this.CHECKBOX_COUNTER.lastChild.innerHTML = '0';

		if (!!this.ACTION_BUTTON)
			this.ACTION_BUTTON.disabled = true;
	}
	else
	{
		if (!!this.FOOTER && !!this.params.FIX_FOOTER)
			BX.Fix(this.FOOTER, {type: 'bottom', limit_node: this.TABLE.tBodies[0]});

		BX.addClass(this.CHECKBOX_COUNTER, 'adm-table-counter-visible');
		this.CHECKBOX_COUNTER.lastChild.innerHTML = this.ACTION_TARGET && this.ACTION_TARGET.checked ? BX.message('JSADM_LIST_SELECTEDALL') : this.num_checked;

		if (!!this.ACTION_BUTTON)
			this.ACTION_BUTTON.disabled = this.ACTION_SELECTOR.selectedIndex <= 0;
	}
};

BX.adminList.prototype.Sort = function(url, bCheckCtrl, args)
{
	if(bCheckCtrl == true)
	{
		var e = null, bControl = false;

		if(args.length > 0)
			e = args[0];
		if(!e)
			e = window.event;
		if(e)
			bControl = e.ctrlKey;
		url += (bControl? 'desc':'asc');
	}

	this.GetAdminList(url);
};


BX.adminList.prototype.RowClick = function(e)
{
	e = e || window.event;

	if (e.button != 0)
		return true;

	if (e.ctrlKey || e.metaKey || e.shiftKey && !this._last_row)
	{
		var c = BX.proxy_context.cells[0].firstChild;
		c.checked = !c.checked;

		this.SelectRow(c, c.checked);

		this.UpdateCheckboxCounter();
		this.EnableActions();
	}

	if (e.shiftKey)
	{
		if (!this._last_row)
			this._last_row = BX.proxy_context.parentNode.rows[0];

		var tBody = this._last_row.parentNode,
			ixStart = Math.min(this._last_row.rowIndex, BX.proxy_context.rowIndex),
			ixFinish = Math.max(this._last_row.rowIndex, BX.proxy_context.rowIndex);

		for (var i = ixStart; i <= ixFinish; i++)
		{
			c = tBody.rows[i-1].cells[0].firstChild;
			if (!c.checked)
			{
				c.checked = true;
				this.SelectRow(c, c.checked);
			}
		}

		this.UpdateCheckboxCounter();
		this.EnableActions();

		return BX.PreventDefault(e);
	}
};

BX.adminList.prototype._checkboxClick = function(e)
{
	if (e.shiftKey || e.ctrlKey || e.metaKey)
		return true;

	this.SelectRow(BX.proxy_context, BX.proxy_context.checked);

	this.UpdateCheckboxCounter();
	this.EnableActions();

	return BX.eventCancelBubble(e);
};

BX.adminList.prototype._checkboxCellClick = function(e)
{
	if (e.shiftKey || e.ctrlKey || e.metaKey)
		return true;

	var c = BX.proxy_context.firstChild;
	c.checked = !c.checked;

	this.SelectRow(c, c.checked);

	this.UpdateCheckboxCounter();
	this.EnableActions();

	return BX.PreventDefault(e);
};

BX.adminList.prototype.SelectRow = function(el, bSelect)
{
	if (el.tagName.toUpperCase() != 'TR')
	{
		if (!el.BXROW)
		{
			el.BXROW = BX.findParent(el, {tag: 'TR'});
		}

		if (!!el.BXROW)
		{
			this.SelectRow(el.BXROW, bSelect);
		}
	}
	else
	{
		if (bSelect)
			BX.addClass(el, 'adm-table-row-active');
		else
			BX.removeClass(el, 'adm-table-row-active');

		this._last_row = el;
		this.num_checked += bSelect ? 1 : -1;
	}
};

BX.adminList.prototype.SelectAllRows = function(node)
{
	this.bSelectAllChecked = !!node.checked;

	for (var i = 0; i < this.CHECKBOX.length; i++)
	{
		if(this.CHECKBOX[i].checked != this.bSelectAllChecked && !this.CHECKBOX[i].disabled)
		{
			this.CHECKBOX[i].checked = this.bSelectAllChecked;
			this.SelectRow(this.CHECKBOX[i], this.bSelectAllChecked);
		}
	}

	this.UpdateCheckboxCounter();
	this.EnableActions();
};

BX.adminList.prototype.IsActionEnabled = function(action)
{
	if(action == 'edit')
		return !(this.ACTION_TARGET && this.ACTION_TARGET.checked) && (this.num_checked > 0);
	else
		return (this.ACTION_TARGET && this.ACTION_TARGET.checked) || (this.num_checked > 0);
};

BX.adminList.prototype.EnableActions = function()
{
	if (!!this.BUTTON_EDIT)
	{
		if (this.IsActionEnabled('edit'))
			BX.removeClass(this.BUTTON_EDIT, 'adm-edit-disable');
		else
			BX.addClass(this.BUTTON_EDIT, 'adm-edit-disable');
	}

	if (!!this.BUTTON_DELETE)
	{
		if (this.IsActionEnabled('delete'))
			BX.removeClass(this.BUTTON_DELETE, 'adm-edit-disable');
		else
			BX.addClass(this.BUTTON_DELETE, 'adm-edit-disable');
	}
};

BX.adminList.prototype.Destroy = function()
{
	this.CHECKBOX = [];
	this.CHECKBOX_DISABLED = [];

	if (BX.PopupMenu.currentItem && BX.PopupMenu.currentItem.popupWindow.isShown())
		BX.PopupMenu.currentItem.popupWindow.close();

	if (this.TABLE && this.TABLE.tHead)
		BX.UnFix(this.TABLE.tHead);
	if (this.FOOTER)
	BX.UnFix(this.FOOTER);
	if (this.FOOTER_EDIT)
		BX.UnFix(this.FOOTER_EDIT);

	this._last_row = null;
	this.num_checked = 0;
};

BX.adminList.prototype.ShowSettings = function(url)
{
	(new BX.CDialog({
		content_url: url,
		resizable: true,
		height: 475,
		width: 560
	})).Show();
};

BX.adminList.prototype.SaveSettings =  function(el)
{
	var sCols='', sBy='', sOrder='', sPageSize;

	var oSelect = document.list_settings.selected_columns;
	var n = oSelect.length;
	for(var i=0; i<n; i++)
		sCols += (sCols != ''? ',':'')+oSelect[i].value;

	oSelect = document.list_settings.order_field;
	if(oSelect)
		sBy = oSelect[oSelect.selectedIndex].value;

	oSelect = document.list_settings.order_direction;
	if(oSelect)
		sOrder = oSelect[oSelect.selectedIndex].value;

	oSelect = document.list_settings.nav_page_size;
	sPageSize = oSelect[oSelect.selectedIndex].value;

	var bCommon = (document.list_settings.set_default && document.list_settings.set_default.checked);

	BX.userOptions.save('list', this.table_id, 'columns', sCols, bCommon);
	BX.userOptions.save('list', this.table_id, 'by', sBy, bCommon);
	BX.userOptions.save('list', this.table_id, 'order', sOrder, bCommon);
	BX.userOptions.save('list', this.table_id, 'page_size', sPageSize, bCommon);

	var url = window.location.href;

	BX.WindowManager.Get().showWait(el);
	BX.userOptions.send(BX.delegate(function(){
		this.GetAdminList(
			url,
			function(){
				BX.WindowManager.Get().closeWait(el);
				BX.WindowManager.Get().Close();
			}
		);
	}, this));
};

BX.adminList.prototype.DeleteSettings = function(bCommon)
{
	BX.showWait();
	var url = window.location.href;
	BX.userOptions.del('list', this.table_id, bCommon, BX.delegate(function(){
		BX.closeWait();
		this.GetAdminList(
			url,
			function(){BX.WindowManager.Get().Close();}
		);
	}, this));
};

/****************************** For new grid ********************************/
BX.adminList.SendSelected = function(gridId)
{
	var gridInstance = BX.Main.gridManager.getById(gridId).instance;
	var values = gridInstance.getActionsPanel().getValues();
	var selectedRows = gridInstance.getRows().getSelectedIds();
	var data = {
		ID: selectedRows,
		action: values
	};
	gridInstance.reloadTable("POST", data);
};

BX.adminList._onpopupmenushow = function(){BX.addClass(this, 'adm-list-row-active');};
BX.adminList._onpopupmenuclose = function(){BX.removeClass(this, 'adm-list-row-active');};

BX.adminList.ShowMenu = function(el, menu, el_row)
{
	if (!!menu && menu.length > 0)
	{
		if (!!el_row)
		{
			BX.addCustomEvent(el, 'onAdminMenuShow', BX.proxy(BX.adminList._onpopupmenushow, el_row));
			BX.addCustomEvent(el, 'onAdminMenuClose', BX.proxy(BX.adminList._onpopupmenuclose, el_row));
		}

		BX.adminShowMenu(el, menu, {active_class: 'adm-list-table-popup-active'});
	}
};

BX.adminTabControl = function (name, unique_name, aTabs)
{
	this.name = name;
	this.unique_name = unique_name;
	this.aTabs = aTabs;

	this.bInited = false;
	this.bFixed = {top: true, bottom: true};

	this.bExpandTabs = false;
	this.aTabsDisabled = {};

	this.bPublicMode = false;

	this.PreInit();
};

BX.adminTabControl.prototype.PreInit = function(bSkipInit)
{
	for (var tab = 0; tab < this.aTabs.length; tab++)
	{
		this.aTabs[tab].CONTENT = BX(this.aTabs[tab]["DIV"]);

		var tbl = BX(this.aTabs[tab]["DIV"]+'_edit_table');
		if (!!tbl)
		{
			for(var k = 0; k < tbl.tBodies.length; k++)
			 {
				var n = tbl.tBodies[k].rows.length;
				for (var i = 0; i < n; i++)
				{
					if (tbl.tBodies[k].rows[i].cells.length > 1)
					{
						BX.addClass(tbl.tBodies[k].rows[i].cells[0], 'adm-detail-content-cell-l');
						BX.addClass(tbl.tBodies[k].rows[i].cells[1], 'adm-detail-content-cell-r');
					}
				}
			}

			this.aTabs[tab].EDIT_TABLE = tbl;
			this.aTabs[tab].CONTENT_BLOCK = tbl.parentNode;
			var modifyFormElements = BX.adminFormTools.modifyFormElements(tbl);
		}
	}

	if(!bSkipInit)
	{
		BX.ready(BX.defer(this.Init, this));
	}
};

BX.adminTabControl.prototype.Init = function()
{
	if (this.aTabs && this.aTabs.length > 0)
	{
		var tabs_block = this.TABS_BLOCK = BX(this.name + '_tabs');
		if (!!tabs_block)
		{
			var settings_btn = BX(this.name + '_settings_btn');

			tabs_block.appendChild(BX.create('DIV', {
				props: {
					className: 'adm-detail-pin-btn-tabs',
					title: BX.message('JSADM_PIN_OFF')
				},
				attrs: {onclick: this.name + '.ToggleFix(\'top\')'}
			}));

			BX.addCustomEvent(tabs_block, 'onFixedNodeChangeState', function(state)
			{
				if (state)
				{
					BX.addClass(tabs_block, 'adm-detail-tabs-block-fixed');
				}
				else
				{
					BX.removeClass(tabs_block, 'adm-detail-tabs-block-fixed');
				}

				if (!!settings_btn && BX.hasClass(settings_btn, 'bx-settings-btn-active'))
				{
					BX.onCustomEvent(settings_btn, 'onChangeNodePosition');
				}
			});

			if (this.bFixed['top'])
			{
				BX.Fix(tabs_block, {type: 'top', limit_node: tabs_block.parentNode});
			}
			else
			{
				BX.addClass(tabs_block, 'adm-detail-tabs-block-pin');
				tabs_block.lastChild.title = BX.message('JSADM_PIN_ON');
			}
		}
	}

	var footer = BX(this.name + '_buttons_div');
	if (!!footer)
	{
		if (footer.firstChild)
		{
			if (BX.util.trim(footer.firstChild.innerHTML).length <= 0)
			{
				if (!BX.hasClass(footer.firstChild, 'adm-detail-content-btns-empty'))
					BX.addClass(footer.firstChild, 'adm-detail-content-btns-empty');
			}
			else
			{
				footer.firstChild.insertBefore(BX.create('DIV', {
					props: {
						className: 'adm-detail-pin-btn',
						title: BX.message('JSADM_PIN_OFF')
					},
					attrs: {onclick: this.name + '.ToggleFix(\'bottom\')'}
				}), footer.firstChild.firstChild);

				BX.addCustomEvent(footer, 'onFixedNodeChangeState', function(state)
					{
						if (state)
							BX.addClass(footer, 'adm-detail-content-btns-fixed');
						else
							BX.removeClass(footer, 'adm-detail-content-btns-fixed');
					});

				if (this.bFixed['bottom'])
				{
					BX.Fix(footer, {type: 'bottom', limit_node: footer.parentNode});
				}
				else
				{
					BX.addClass(footer, 'adm-detail-content-btns-pin');
					footer.firstChild.firstChild.title = BX.message('JSADM_PIN_ON')
				}
			}

			BX.bindDelegate(footer, 'click', {property:{type: /button|submit/}}, BX.adminPanel.showWait);
		}
	}

	this.bInited = true;
};

BX.adminTabControl.prototype.setPublicMode = function(v)
{
	this.bPublicMode = !!v;
	if (this.bPublicMode)
	{
		var name = this.name;
		BX.addCustomEvent(BX.WindowManager.Get(), 'onWindowClose', function(){
			window[name] = null;
		});
	}
};

BX.adminTabControl.prototype.ToggleFix = function(type, value)
{
	if (!this.bInited)
	{
		this.bFixed[type] = typeof value == 'undefined' ? !this.bFixed[type] : !!value;
		return;
	}

	if (typeof value != 'undefined')
	{
		this.bFixed[type] = !value;
	}

	switch (type)
	{
		case 'bottom':
			var footer = BX(this.name + '_buttons_div');
			if (!!footer)
			{
				if (this.bFixed[type])
				{
					BX.addClass(footer, 'adm-detail-content-btns-pin');
					footer.firstChild.firstChild.title = BX.message('JSADM_PIN_ON');
					BX.UnFix(footer);
				}
				else
				{
					BX.removeClass(footer, 'adm-detail-content-btns-pin');
					footer.firstChild.firstChild.title = BX.message('JSADM_PIN_OFF');
					BX.Fix(footer, {type: 'bottom', limit_node: footer.parentNode});
				}

			}
		break;
		case 'top':
			if (!!this.TABS_BLOCK)
			{
				if (this.bFixed[type])
				{
					BX.addClass(this.TABS_BLOCK, 'adm-detail-tabs-block-pin');
					this.TABS_BLOCK.lastChild.title = BX.message('JSADM_PIN_ON');
					BX.UnFix(this.TABS_BLOCK);
				}
				else
				{
					BX.removeClass(this.TABS_BLOCK, 'adm-detail-tabs-block-pin');
					this.TABS_BLOCK.lastChild.title = BX.message('JSADM_PIN_OFF');
					BX.Fix(this.TABS_BLOCK, {type: 'top', limit_node: this.TABS_BLOCK.parentNode});
				}
			}
		break;
	}

	this.bFixed[type] = !this.bFixed[type];
	BX.userOptions.save('edit', 'admin_tabs', 'fix_'+type, (this.bFixed[type] ? 'on': 'off'));
};

BX.adminTabControl.prototype.SelectTab = function(tab_id)
{
	if (!this.bInited)
	{
		setTimeout("window."+this.name+".SelectTab('"+BX.util.jsencode(tab_id)+"')", 50);
	}
	else if (!this.aTabsDisabled[tab_id])
	{
		var div = BX(tab_id);
		if (div.style.display != 'none')
		{
			//already visible or expanded tab
			if(this.bExpandTabs)
			{
				//let's scroll to the expanded tab
				var pos = BX.pos(div), wndScroll = BX.GetWindowScrollPos();
				if (!!this.TABS_BLOCK && this.bFixed['top'])
				{
					pos.top -= this.TABS_BLOCK.offsetHeight;
				}
				window.scrollTo(wndScroll.scrollLeft, pos.top);
			}
		}
		else
		{
			//invisible tab - need to show it
			var oldHeight = 0;
			var newHeight = 0;
			var contentBlockPaddings = 40;
			for (var i = 0, cnt = this.aTabs.length; i < cnt; i++)
			{
				var tab = BX(this.aTabs[i]["DIV"]);
				if(tab.style.display != 'none')
				{
					oldHeight = this.aTabs[i].CONTENT_BLOCK.offsetHeight - contentBlockPaddings;
					this.ShowTab(this.aTabs[i]["DIV"], false);
					tab.style.display = 'none';
					break;
				}
			}

			this.ShowTab(tab_id, true);
			div.style.display = 'block';

			BX(this.name+'_active_tab').value = tab_id;

			var currentTab = null;
			for (i = 0, cnt = this.aTabs.length; i < cnt; i++)
			{
				if(this.aTabs[i]["DIV"] == tab_id)
				{
					this.aTabs[i]["_ACTIVE"] = true;

					if(this.aTabs[i]["ONSELECT"])
					{
						BX.evalGlobal(this.aTabs[i]["ONSELECT"]);
					}

					if (!this.bPublicMode)
					{
						currentTab = this.aTabs[i];
						var currentContentBlock = this.aTabs[i].CONTENT_BLOCK;
						newHeight = currentContentBlock.offsetHeight - contentBlockPaddings;
						if (oldHeight > 0)
						{
							currentContentBlock.style.height = oldHeight + "px";
							currentContentBlock.style.overflowY = "hidden";
							this.aTabs[i].EDIT_TABLE.style.opacity = 0;
						}
					}

					break;
				}
			}

			if (!!this.TABS_BLOCK)
			{
				if (BX.hasClass(this.TABS_BLOCK, 'adm-detail-tabs-block-fixed'))
				{
					pos = BX.pos(div);
					wndScroll = BX.GetWindowScrollPos();
					window.scrollTo(wndScroll.scrollLeft, pos.top - this.TABS_BLOCK.offsetHeight - parseInt(this.TABS_BLOCK.style.top));
				}
			}

			if (!this.bPublicMode && oldHeight > 0 && newHeight > 0 && currentTab)
			{
				var easing = new BX.easing({
					duration : 500,
					start : { height: oldHeight, opacity : 0 },
					finish : { height: newHeight, opacity : 100 },
					transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),

					step : BX.proxy(function(state){
						this.CONTENT_BLOCK.style.height = state.height + 'px';
						this.EDIT_TABLE.style.opacity = state.opacity / 100;
						BX.onCustomEvent('onAdminTabsChange');
					}, currentTab),

					complete : BX.proxy(function(){
						this.CONTENT_BLOCK.style.height = "auto";
						this.CONTENT_BLOCK.style.overflowY = "visible";
						BX.onCustomEvent('onAdminTabsChange');

					}, currentTab)

				});
				easing.animate();
			}
			else
			{
				BX.onCustomEvent('onAdminTabsChange');
			}
		}
	}
};

BX.adminTabControl.prototype.ShowTab = function(tab_id, bShow)
{
	if (bShow)
		BX.addClass(BX('tab_cont_' + tab_id), 'adm-detail-tab-active');
	else
		BX.removeClass(BX('tab_cont_' + tab_id), 'adm-detail-tab-active');
};

BX.adminTabControl.prototype.ShowDisabledTab = function(tab_id, disabled)
{
	var tab = BX('tab_cont_'+tab_id);
	if(disabled)
	{
		BX.addClass(tab, 'adm-detail-tab-disable');
	}
	else
	{
		BX.removeClass(tab, 'adm-detail-tab-disable');
	}
};

// TODO: rewrite
BX.adminTabControl.prototype.NextTab = function()
{
	var CurrentTab=BX(this.name+'_active_tab').value;
	var NextTab="";

	for(var i=0; i<this.aTabs.length; i++)
		{
			if(CurrentTab==this.aTabs[i]["DIV"])
			{
				if(i>=(this.aTabs.length-1))
					NextTab=this.aTabs[0];
				else
					NextTab=this.aTabs[i+1];
			}
		}

	if(NextTab["DIV"])
		this.SelectTab(NextTab["DIV"]);
};

BX.adminTabControl.prototype.ToggleTabs = function()
{
	this.bExpandTabs = !this.bExpandTabs;

	var a = BX(this.name+'_expand_link');
	a.title = (this.bExpandTabs? BX.message('JSADM_TABS_COLLAPSE') : BX.message('JSADM_TABS_EXPAND'));
	if (this.bExpandTabs)
	{
		BX.addClass(a, 'adm-detail-title-setting-active');
	}
	else
	{
		BX.removeClass(a, 'adm-detail-title-setting-active');
	}

	for(var i=0; i < this.aTabs.length; i++)
	{
		var tab_id = this.aTabs[i]["DIV"];
		var div = BX(tab_id);
		this.ShowTab(tab_id, false);
		div.style.display = (this.bExpandTabs && !this.aTabsDisabled[tab_id]? 'block':'none');
	}

	if(!this.bExpandTabs)
	{
		this.ShowTab(this.aTabs[0]["DIV"], true);
		div = document.getElementById(this.aTabs[0]["DIV"]);
		div.style.display = 'block';
	}

	BX.userOptions.save('edit', this.unique_name, 'expand', (this.bExpandTabs? 'on': 'off'));

	BX.onCustomEvent('OnToggleTabs');
	BX.onCustomEvent('onAdminTabsChange');
};


BX.adminTabControl.prototype.DisableTab = function(tab_id)
{
	this.aTabsDisabled[tab_id] = true;
	this.ShowDisabledTab(tab_id, true);
	if(this.bExpandTabs)
	{
		var div = BX(tab_id);
		div.style.display = 'none';
	}
};

BX.adminTabControl.prototype.EnableTab = function(tab_id)
{
	this.aTabsDisabled[tab_id] = false;
	this.ShowDisabledTab(tab_id, this.bExpandTabs);
	if(this.bExpandTabs)
	{
		var div = BX(tab_id);
		div.style.display = 'block';
	}
};

BX.adminTabControl.prototype.ShowWarnings = function(form_name, warnings)
{
	var form = document.forms[form_name];
	if(!form)
		return;

	for(var i in warnings)
	{
		var e = form.elements[warnings[i]['name']];

		if(!e)
			continue;

		var type = (e.type? e.type.toLowerCase():'');
		var bBefore = false;
		if(e.length > 1 && type != 'select-one' && type != 'select-multiple')
		{
			e = e[0];
			bBefore = true;
		}
		if(type == 'textarea' || type == 'select-multiple')
			bBefore = true;

		var td = e.parentNode;
		var img;
		if(bBefore)
		{
			img = td.insertBefore(new Image(), e);
			td.insertBefore(document.createElement("BR"), e);
		}
		else
		{
			img = td.insertBefore(new Image(), e.nextSibling);
			img.hspace = 2;
			img.vspace = 2;
			img.style.verticalAlign = 'middle';
		}
		img.src = '/bitrix/panel/main/images_old/icon_warn.gif';
		img.title = warnings[i]['title'];
	}
};

BX.adminTabControl.prototype.ShowSettings = function(url)
{
	(new BX.CDialog({
			content_url: url,
			resizable: true,
			height: 605,
			width: 560
	})).Show();
};

BX.adminTabControl.prototype.CloseSettings =  function()
{
	BX.WindowManager.Get().Close();
};

BX.adminTabControl.prototype.SaveSettings =  function(el)
{
	var sTabs='', s='';

	var oFieldsSelect;
	var oSelect = BX('selected_tabs');
	if(oSelect)
	{
		var k = oSelect.length;
		for(var i=0; i<k; i++)
		{
			s = oSelect[i].value + '--#--' + oSelect[i].text;
			oFieldsSelect = BX('selected_fields[' + oSelect[i].value + ']');
			if(oFieldsSelect)
			{
				var n = oFieldsSelect.length;
				for(var j=0; j<n; j++)
				{
					s += '--,--' + oFieldsSelect[j].value + '--#--' + jsUtils.trim(oFieldsSelect[j].text);
				}
			}
			sTabs += s + '--;--';
		}
	}

	var bCommon = (document.form_settings.set_default && document.form_settings.set_default.checked);

	var sParam = '';
	sParam += '&p[0][c]=form';
	sParam += '&p[0][n]='+BX.util.urlencode(this.name);
	if(bCommon)
		sParam += '&p[0][d]=Y';
	sParam += '&p[0][v][tabs]=' + BX.util.urlencode(sTabs);

	var options_url = '/bitrix/admin/user_options.php?lang='+BX.message('LANGUAGE_ID')+'&sessid=' + BX.bitrix_sessid();
	options_url += '&action=delete&c=form&n='+this.name+'_disabled';

	BX.WindowManager.Get().showWait(el);
	BX.ajax.post(options_url, sParam, function() {
		BX.WindowManager.Get().closeWait(el);
		BX.WindowManager.Get().Close();
		BX.reload();
	});
};

BX.adminTabControl.prototype.DeleteSettings = function(bCommon)
{
	BX.showWait();
	BX.userOptions.del('form', this.name, bCommon, function () {BX.reload()});
};

BX.adminTabControl.prototype.DisableSettings = function()
{
	var request = new JCHttpRequest;
	request.Action = function () {BX.reload()};
	var sParam = '';
	sParam += '&p[0][c]=form';
	sParam += '&p[0][n]='+encodeURIComponent(this.name+'_disabled');
	sParam += '&p[0][v][disabled]=Y';
	request.Send('/bitrix/admin/user_options.php?lang=' + phpVars.LANGUAGE_ID + sParam + '&sessid='+phpVars.bitrix_sessid);
};

BX.adminTabControl.prototype.EnableSettings = function()
{
	var request = new JCHttpRequest;
	request.Action = function () {BX.reload()};
	var sParam = '';
	sParam += '&c=form';
	sParam += '&n='+encodeURIComponent(this.name)+'_disabled';
	sParam += '&action=delete';
	request.Send('/bitrix/admin/user_options.php?lang=' + phpVars.LANGUAGE_ID + sParam + '&sessid='+phpVars.bitrix_sessid);
};

BX.adminViewTabControl = function(aTabs)
{
	this.aTabs = aTabs;
	this.bPublicMode = false;
	BX.ready(BX.delegate(this.Init, this));
};

BX.adminViewTabControl.prototype.setPublicMode = function(v)
{
	this.bPublicMode = !!v;
};

BX.adminViewTabControl.prototype.SelectTab = function(tab_id)
{
	var div = BX(tab_id);
	if(div.style.display != 'none')
		return;

	var oldHeight = 0;
	var contentBlockPaddings = 41;
	for(var i in this.aTabs)
	{
		var tab_div = BX(this.aTabs[i]["DIV"]);
		if(tab_div.style.display != 'none')
		{
			var tab = BX('view_tab_'+this.aTabs[i]["DIV"]);
			BX.removeClass(tab, 'adm-detail-subtab-active');

			var oldContentBlock = BX.findChild(tab_div, { className : "adm-detail-content-item-block-view-tab"});
			if (oldContentBlock)
				oldHeight = oldContentBlock.offsetHeight - contentBlockPaddings;

			tab_div.style.display = 'none';
			break;
		}
	}

	var active_tab = BX('view_tab_'+tab_id);
	BX.addClass(active_tab, 'adm-detail-subtab-active');
	div.style.display = 'block';

	var newHeight = 0;
	var newContentBlock = BX.findChild(div, { className : "adm-detail-content-item-block-view-tab" });
	var newContentTable = null;
	if (newContentBlock)
	{
		newHeight = newContentBlock.offsetHeight - contentBlockPaddings;
		if (oldHeight > 0)
		{
			newContentBlock.style.height = oldHeight + "px";
			newContentBlock.style.overflowY = "hidden";
			newContentTable = BX.findChild(newContentBlock, { tagName : "table" });
			if (newContentTable)
				newContentTable.style.opacity = 0;
		}
	}

	for(i in this.aTabs)
	{
		if(this.aTabs[i]["DIV"] == tab_id)
		{
			if(this.aTabs[i]["ONSELECT"])
			{
				BX.evalGlobal(this.aTabs[i]["ONSELECT"]);
			}
			break;
		}
	}

	if (oldHeight > 0 && newHeight > 0 && newContentBlock)
	{
		var easing = new BX.easing({
			duration : 500,
			start : { height: oldHeight, opacity : 0 },
			finish : { height: newHeight, opacity : 100 },
			transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),

			step : BX.proxy(function(state){
				this.style.height = state.height + 'px';
				if (newContentTable)
					newContentTable.style.opacity = state.opacity / 100;
				BX.onCustomEvent('onAdminTabsChange');
			}, newContentBlock),

			complete : BX.proxy(function(){
				this.style.height = "auto";
				this.style.overflowY = "visible";
				BX.onCustomEvent('onAdminTabsChange');

			}, newContentBlock)

		});
		easing.animate();
	}
	else
		BX.onCustomEvent('onAdminTabsChange');
};

BX.adminViewTabControl.prototype.DisableTab = function(tab_id)
{
	if (this.aTabs.length <= 0)
	{
		return;
	}

	var j = null;
	var bFound = false;

	for(var i in this.aTabs)
	{
		if(this.aTabs[i]["DIV"] == tab_id)
		{
			if (i > 0)
			{
				j = parseInt(i) - 1;
				while (j >= 0)
				{
					if (BX('view_tab_' + this.aTabs[j]["DIV"]).style.display != 'none')
					{
						this.SelectTab(this.aTabs[j]["DIV"]);
						bFound = true;
						break;
					}
					j--;
				}
			}

			if (!bFound)
			{
				j = parseInt(i) + 1;
				while (j < (this.aTabs.length - 1))
				{
					if (BX('view_tab_' + this.aTabs[j]["DIV"]).style.display != 'none')
					{
						this.SelectTab(this.aTabs[j]["DIV"]);
						bFound = true;
						break;
					}
					j++;
				}
			}

			if (!bFound)
			{
				BX(tab_id).style.display = 'none';
			}

			BX('view_tab_' + this.aTabs[i]["DIV"]).style.display = 'none';
			break;
		}
	}
};

BX.adminViewTabControl.prototype.EnableTab = function(tab_id)
{
	for(var i in this.aTabs)
	{
		if(this.aTabs[i]["DIV"] == tab_id)
		{
			BX('view_tab_' + this.aTabs[i]["DIV"]).style.display = 'inline-block';
			this.SelectTab(this.aTabs[i]["DIV"]);
			break;
		}
	}
};

BX.adminViewTabControl.prototype.ReplaceAnchor = function(tab)
{
};

BX.adminViewTabControl.prototype.RebuildTabs = function()
{

};

BX.adminViewTabControl.prototype.Init = function()
{
	if(this.aTabs.length == 0)
		return;
};

/***************************** simple history listener **********************/

BX.adminHistory = function()
{
	BX.bind(window, 'popstate', BX.proxy(this._get, this));

	this.bStart = true;

	this.pushSupported = true;
	this.state = {};
	this.startState = null;

	this.disabled = false;
};

BX.adminHistory.pushSupported = false;

BX.adminHistory.disable = BX.adminHistory.prototype.disable = function() {BX.adminHistory.disabled = true};
BX.adminHistory.enable = BX.adminHistory.prototype.enable = function() {BX.adminHistory.disabled = false};

/* callback is useless here but should be here for compatibility reasons */
BX.adminHistory.put = function(url, callback, arIgnoreParams)
{
	if (BX.adminHistory.disabled)
		return;

	var link = BX('navchain-link');
	if(link)
	{
		if (url.indexOf('&amp;') > 0)
			url = BX.util.htmlspecialcharsback(url);
		if (BX.type.isArray(arIgnoreParams))
			url = BX.util.remove_url_param(url, arIgnoreParams);

		link.href = url;
		if (url != window.location.href)
			BX.addClass(link, 'navchain-link-visible');
		else
			BX.removeClass(link, 'navchain-link-visible');
	}
};

BX.adminHistory.prototype.put = function(url, callback, arIgnoreParams)
{
	if (BX.adminHistory.disabled)
		return;

	if (url.indexOf('&amp;') > 0)
		url = BX.util.htmlspecialcharsback(url);
	if (BX.type.isArray(arIgnoreParams))
		url = BX.util.remove_url_param(url, arIgnoreParams);

	url = BX.util.remove_url_param(url, 'admin_history');

	var state = {url: url, callback: callback};

	var k = Math.random();
	this.state[k] = state;

	if (this.bStart)
	{
		this.startState = k;
		this.bStart = false;
	}
	else
	{
		history.pushState(k, '', url);
	}
};

BX.adminHistory.prototype._get = function(e)
{
	e = e || window.event || {state: null};

	var state = e.state;

	if(state === null)
	{
		state = this.startState;
	}

	if (state && this.state[state])
	{
		if (this.state[state].callback)
		{
			this.state[state].callback(this.state[state].url + (this.state[state].url.indexOf('?')>0?'&':'?') + 'admin_history=Y')
		}
		else
		{
			window.location.href = this.state[state].url;
		}
	}
};

/*************************** fixed elements *********************************/

BX.FixOffsets = {
	top: 0,
	bottom: 0,
	right: 0
};

BX.Fix = function(el, params)
{
	if (!el.BXFIXER)
	{
		if (el.tagName.toUpperCase() == 'THEAD')
			el.BXFIXER = new BX.CFixerTHead(el, params);
		else
			el.BXFIXER = new BX.CFixer(el, params);
	}

	el.BXFIXER.Start()
};

BX.UnFix = function(el)
{
	if (!!el && !!el.BXFIXER)
		el.BXFIXER.Stop()
};

BX.CFixer = function(node, params)
{
	this.node = node;
	this.params = params || {type: 'top'};

	this.pos = {};
	this.limit = -1;

	this.position_top = null;
	this.position_bottom = null;
	this.position_right = null;

	this.bStarted = false;
	this.bFixed = false;

	this.gutter = null;

	this.clingTop = null;
	this.clingBottom = null;
	this.clingRight = null;
};

BX.CFixer.prototype.Start = function()
{
	if (this.bStarted)
		return;

	this.pos = BX.pos(this.node);

	BX.bind(window, 'scroll', BX.proxy(this._scroll_listener, this));
	BX.bind(window, 'resize', BX.proxy(this._scroll_listener, this));
	BX.bind(window, 'resize', BX.proxy(this._recalc_pos, this));

	BX.addCustomEvent('onAdminFilterToggleRow', BX.proxy(this._recalc_pos, this));
	BX.addCustomEvent('onAdminFilterToggleRow', BX.proxy(this._scroll_listener, this));
	BX.addCustomEvent('onAdminPanelFix', BX.defer(this._scroll_listener, this));
	BX.addCustomEvent('onAdminPanelChange', BX.defer(this._scroll_listener, this));
	BX.addCustomEvent('onAdminTabsChange', BX.defer(this._recalc_pos, this));
	BX.addCustomEvent(BX.adminMenu, 'onAdminMenuResize', BX.proxy(this._recalc_pos, this));
	//BX.addCustomEvent(BX.adminMenu, 'onAdminMenuResize', BX.proxy(this._ReFix, this));

	this._scroll_listener();

	this.bStarted = true;
};

BX.CFixer.prototype.Stop = function()
{
	if (!this.bStarted)
		return;

	this._UnFix();

	BX.unbind(window, 'scroll', BX.proxy(this._scroll_listener, this));
	BX.unbind(window, 'resize', BX.proxy(this._scroll_listener, this));
	BX.unbind(window, 'resize', BX.proxy(this._recalc_pos, this));

	BX.removeCustomEvent('onAdminFilterToggleRow', BX.proxy(this._recalc_pos, this));
	BX.removeCustomEvent('onAdminFilterToggleRow', BX.proxy(this._scroll_listener, this));
	BX.removeCustomEvent('onAdminPanelFix', BX.defer(this._scroll_listener, this));
	BX.removeCustomEvent('onAdminTabsChange', BX.defer(this._recalc_pos, this));
	BX.removeCustomEvent('onAdminPanelChange', BX.defer(this._scroll_listener, this));
	//BX.removeCustomEvent(BX.adminMenu, 'onAdminMenuResize', BX.proxy(this._scroll_listener, this));
	BX.removeCustomEvent(BX.adminMenu, 'onAdminMenuResize', BX.proxy(this._recalc_pos, this));

	this.bStarted = false;
};

BX.CFixer.prototype._recalc_pos = function()
{
	this.pos = BX.pos(this.gutter || this.node);
	var node_pos = BX.pos(this.node);

	if (this.bFixed)
	{
		if (this.params.type == 'top' || this.params.type == 'bottom')
		{
			this.node.style.width = this.pos.width + 'px';
			this.gutter.style.height = node_pos.height + 'px';
		}
	}

	this._scroll_listener();
};

BX.CFixer.prototype._Fix = function()
{
	if (!this.bFixed)
	{
		this.pos = BX.pos(this.gutter || this.node);

		if (!this.gutter)
			this.gutter = this.node.parentNode.insertBefore(BX.create(
				this.node.tagName, {
					//style: {height: this.pos.height + 'px', width: this.pos.width + 'px'},
					style: {display: 'block', height: this.pos.height + 'px'},
					props: {className: this.node.className}
				}), this.node);

		this._w = this.node.style.width;
		this.node.style.width = this.pos.width + 'px';

		BX.addClass(this.node, 'bx-fixed-' + this.params.type);

		if (this['position_' + this.params.type] !== null)
			this.node.style[this.params.type] = this['position_' + this.params.type] + 'px';

		this.bFixed = true;

		if(this.params.type == 'top')
		{
			this.clingTop = BX.FixOffsets.top;
			BX.FixOffsets.top += this.pos.height;
		}

		BX.addCustomEvent('onAdminFixerUnfix', BX.proxy(this._cling_offset_correction, this));
	}
};

BX.CFixer.prototype._UnFix = function(bRefix)
{
	if (this.bFixed)
	{
		this.node.style.width = this._w;
		BX.removeClass(this.node, 'bx-fixed-' + this.params.type);

		this.node.style[this.params.type] = null;

		this.bFixed = false;

		if (!bRefix)
		{
			if (this.gutter && this.gutter.parentNode)
				this.gutter.parentNode.removeChild(this.gutter);

			this.gutter = null;

			this._check_scroll(this.pos.left, this.pos.top);
		}

		var clingPoint, offsetSize;

		if(this.params.type == 'top')
		{
			clingPoint = this.clingTop;
			offsetSize = this.pos.height;
			this.clingTop = null;
			BX.FixOffsets.top -= this.pos.height;
		}

		BX.removeCustomEvent('onAdminFixerUnfix', BX.proxy(this._cling_offset_correction, this));
		BX.onCustomEvent('onAdminFixerUnfix', [{type: this.params.type, clingPoint: clingPoint, offsetSize: offsetSize}]);
	}
};

BX.CFixer.prototype._ReFix = function()
{
	if (this.bFixed)
	{
		this._UnFix(true); BX.defer(this._Fix, this)();
	}
};

BX.CFixer.prototype._cling_offset_correction = function(params)
{
	if(this.params.type == params.type)
	{
		if(this.params.type == 'top' && params.clingPoint < this.clingTop )
			this.clingTop -= params.offsetSize;

		this._scroll_listener();
	}
};

BX.CFixer.prototype._scroll_listener = function()
{
	var wndScroll = BX.GetWindowScrollPos(), bFixed = this.bFixed, wndSize;

	if (!BX.isNodeInDom(this.node))
		return this.Stop();

	var pos = bFixed ? this.pos : BX.pos(this.node);

	if (this.params.limit_node)
	{
		var pos1 = BX.pos(this.params.limit_node);

		switch(this.params.type)
		{
			case 'top':
				this.limit = pos1.bottom - this.pos.height;
			break;
			case 'bottom':
				this.limit = pos1.top + this.pos.height;
			break;
			case 'right':
				this.limit = pos1.right + this.node.offsetWidth;
			break;
		}
	}

	if (!BX.isNodeHidden(this.node))
	{
		switch(this.params.type)
		{
			case 'top':
				var additive = this.clingTop !== null ? this.clingTop : BX.FixOffsets.top;
				this.position_top = BX.adminPanel.isFixed() ? BX.adminPanel.panel.offsetHeight + additive : additive;

				if (this.limit > additive && wndScroll.scrollTop + this.position_top > this.limit)
					this._UnFix();
				else if (!this.bFixed && wndScroll.scrollTop + this.position_top >= pos.top)
					this._Fix();
				else if (this.bFixed && wndScroll.scrollTop + this.position_top < pos.top)
					this._UnFix();

			break;
			case 'bottom':
				wndSize = BX.GetWindowInnerSize();
				wndScroll.scrollBottom = wndScroll.scrollTop + wndSize.innerHeight;

				if (this.limit > 0 && wndScroll.scrollBottom < this.limit)
					this._UnFix();
				else if (!this.bFixed && wndScroll.scrollBottom < pos.bottom)
					this._Fix();
				else if (this.bFixed && wndScroll.scrollBottom >= pos.bottom)
					this._UnFix();
			break;
			case 'right':
				wndSize = BX.GetWindowInnerSize();

				// 15 is a browser scrollbar fix
				wndScroll.scrollRight = wndScroll.scrollLeft + wndSize.innerWidth - 15;

				if (this.limit > 0 && wndScroll.scrollRight < this.limit)
					this._UnFix();
				else if (!this.bFixed && wndScroll.scrollRight < pos.right)
					this._Fix();
				else if (this.bFixed && wndScroll.scrollRight >= pos.right)
					this._UnFix();

			break;
		}
	}
	else if (this.bFixed)
	{
		this._UnFix();
	}

	if (this.bFixed)
	{
		this._check_scroll(wndScroll.scrollLeft, wndScroll.scrollTop);
	}
	else
	{
		this._check_scroll(this.pos.left, this.pos.top);
	}

	if (bFixed != this.bFixed)
	{
		BX.onCustomEvent(this.node, 'onFixedNodeChangeState', [this.bFixed]);
	}
};

BX.CFixer.prototype._check_scroll = function(scrollLeft, scrollTop)
{
	if (this.params.type == 'top' || this.params.type == 'bottom')
		this.node.style.left = (this.pos.left - scrollLeft) + 'px';
	else
		this.node.style.top = (this.pos.top - scrollTop) + 'px';

	if (this.bFixed && this['position_' + this.params.type] !== null)
	{
		this.node.style[this.params.type] = this['position_' + this.params.type] + 'px';
	}
};

BX.CFixerTHead = function()
{
	BX.CFixerTHead.superclass.constructor.apply(this, arguments);

	this.mirror = null;
	this.mirror_thead = null;
};
BX.extend(BX.CFixerTHead, BX.CFixer);

BX.CFixerTHead.prototype._Fix = function()
{
	if (!this.bFixed)
	{
		if (!this.mirror)
		{
			this.pos = BX.pos(this.node);

			var wndScroll = BX.GetWindowScrollPos();

			this.mirror_thead = BX.clone(this.node);

			this.mirror = document.body.appendChild(
				BX.create('DIV', {
					style: {
						left: (this.pos.left-wndScroll.scrollLeft) + 'px'
					},
					props: {className: 'bx-fixed-' + this.params.type + ' adm-list-table-fixed'},
					children:[
						BX.create('TABLE', {
							props: {className: this.node.parentNode.className},
							style: {width: this.node.parentNode.offsetWidth + 'px'},
							children: [this.mirror_thead]
						})
					]
				})
			);

			for (var i = 0; i < this.node.rows[0].cells.length; i++)
			{
				this.mirror_thead.rows[0].cells[i].appendChild(
					BX.create('SPAN', {
						style: {
							cssFloat: 'left',
							height: '1px'
						},
						html: '<img src="/bitrix/images/1.gif" style="height: 0; width: ' + (this.node.rows[0].cells[i].offsetWidth-4) + 'px;">'
					}));

				this.mirror_thead.rows[0].cells[i].style.width = this.node.rows[0].cells[i].offsetWidth + 'px';
			}
		}

		this.mirror.style.display = 'block';
		this.mirror.style.top = (this.position_top !== null ? this.position_top : 0) + 'px';
		this.bFixed = true;
	}
};

BX.CFixerTHead.prototype._UnFix = function()
{
	if (this.bFixed)
	{
		if (!!this.mirror)
		{
			this._clear_mirror();
		}

		this.bFixed = false;
	}
};

BX.CFixerTHead.prototype._recalc_pos = function()
{
	this.pos = BX.pos(this.node);

	if (this.bFixed && (this.params.type == 'top' || this.params.type == 'bottom'))
	{
		this.mirror.firstChild.style.width = this.pos.width + 'px';
	}

	this._scroll_listener();
};


BX.CFixerTHead.prototype._clear_mirror = function()
{
	if (!!this.mirror)
		this.mirror.parentNode.removeChild(this.mirror);

	this.mirror = null;
	this.mirror_thead = null;
};

BX.CFixerTHead.prototype._check_scroll = function(scrollLeft)
{
	if (!!this.mirror)
	{
		this.mirror.style.left = (this.pos.left - scrollLeft) + 'px';
		if (this.bFixed && this['position_' + this.params.type] !== null)
			this.mirror.style[this.params.type] = this['position_' + this.params.type] + 'px'
	}
};

/******************************** admin menu unification ********************/

BX.adminShowMenu = function(el, menu, params)
{
	if (el.OPENER)
		return true;

	var bindElement = el,
		pseudo_el = null;

	if (typeof el == 'object' && !BX.type.isElementNode(el) && typeof el.x != 'undefined')
	{
		pseudo_el = document.body.appendChild(BX.create('DIV', {
			style: {
				position: 'absolute',
				left: el.x + 'px',
				top: el.y + 'px',
				height: 0,
				width: 0
			}
		}));

		bindElement = pseudo_el;
	}

	params = params || {};

	bindElement.OPENER = new BX.COpener({
		DIV: bindElement,
		MENU: menu,
		TYPE: 'click',
		ACTIVE_CLASS: (typeof params.active_class != 'undefined') ? params.active_class : 'adm-btn-active',
		CLOSE_ON_CLICK: (typeof params.close_on_click != 'undefined') ? !!params.close_on_click : true
	});

	var f = function()
	{
		BX.onCustomEvent(el, 'onAdminMenuClose');

		if (!!pseudo_el)
		{
			pseudo_el.parentNode.removeChild(pseudo_el);
			pseudo_el = null;
		}

		bindElement = null;
	};

	BX.addCustomEvent(bindElement.OPENER, 'onOpenerMenuClose', f);
	BX.addCustomEvent(bindElement.OPENER, 'onOpenerMenuOpen', function() {
		BX.onCustomEvent(el, 'onAdminMenuShow');
	});

	bindElement.OPENER.Toggle();
};

/****************Admin Filter********************************/

BX.AdminFilter = function(filter_id, aRows)
{
	var _this = this;
	this.filter_id = filter_id;
	this.aRows = aRows;
	this.oVisRows = {};
	this.oOptions = {};
	this.curID = "0";
	this.form = jsUtils.FindParentObject(BX(this.filter_id), "form");
	this.popupItems = {};
	this.missingRows = 0;
	this.tableWrap = null;
	this.table = null;
	this.easing = null;
	this.startContentHeight = 0;
	this.table_id = false;
	this.url = false;
	this.currentLoadedTab = null;
	this.presetsDeleted = [];

	this.state = {
		init: false,
		requesting: false,
		clearing: false,
		folded: false,
		saving: false
	};

	//saving in session or cookie
	this.params = {
		filteredId: false,
		activeTabId: false
	};

	this.SetFoldedView = function()
	{
		BX.toggleClass(BX('adm-filter-tab-wrap-'+this.filter_id), 'adm-filter-folded');
		this.state.folded = !this.state.folded;
		BX.userOptions.save('filter', this.filter_id, 'styleFolded', this.state.folded ? "Y" : "N");
		this.SetSwitcherTitle();
	};

	this.SetSwitcherTitle = function()
	{
		var switcher = BX("adm-filter-switcher-tab");
		var wrap = BX("adm-filter-tab-wrap-"+this.filter_id);

		switcher.title = BX.hasClass(wrap,"adm-filter-folded") ? BX.message('JSADM_FLT_UNFOLD') : BX.message('JSADM_FLT_FOLD');
	};

	this.InitFilter = function(oVisRows)
	{
		var vREmpty = this.isObjectEmpty(oVisRows);

		this.SetSwitcherTitle();

		this.oVisRows = oVisRows;

		var tbl = BX(this.filter_id);

		if(!tbl)
			return;

		this.table = tbl;

		var n=tbl.rows.length;
		this.missingRows = tbl.rows.length - this.aRows.length;
		var diff = this.missingRows;

		for(var i=n-1; i>=0; i--)
		{
			var row = tbl.rows[i];
			var td = row.insertCell(-1);
			var tail = "";
			BX.admFltWrap.Row(row);

			if( i-diff >=0 )
			{
				tail = this.aRows[i-diff];
			}
			else
			{
				tail = "miss-"+i;
				this.aRows.unshift(tail);

				if(vREmpty)
					this.oVisRows[tail] = true;
			}

			row.id = this.filter_id+'_row_'+tail;

			if(this.oVisRows[tail] != true)
				row.style.display = 'none';

			td.innerHTML = '<span class="adm-filter-item-delete" onclick="this.blur(); '+this.filter_id+'.DeleteFilterRow(\''+row.id+'\');" hidefocus="true" title="'+phpVars.messFilterLess+'" style="display: none;"></span>';
		}

		for(i=0; i<n; i++)
		{
			var tr = tbl.insertRow(i*2+1);

			if(this.oVisRows[this.aRows[i]] != true)
				tr.style.display = 'none';
			tr.id = this.filter_id+'_row_'+this.aRows[i]+'_delim';

			td = tr.insertCell(-1);
			td.colSpan = 3;
			td.className = 'delimiter';
			td.innerHTML = '<div class="empty"></div>';
		}

		try{
			tbl.style.display = 'table';}
		catch(e){
			tbl.style.display = 'block';}

		this.tableWrap = tbl.parentNode;

		this.DisplayNonEmptyRows();
		this.ChangeViewDependVisible();

		BX.addCustomEvent(window, "onAdminListLoaded", BX.proxy(this.onAdminListLoaded, this));
		BX.onCustomEvent(window, "onAdminFilterInited", [{filterId: this.filter_id}]);
	};

	this.InitFirst = function()
	{
		this.oOptions["0"] = {
			FIELDS: {},
			EDITABLE: false
		};

		for(var i in this.oOptions)
			this.oOptions[i]["tab"] = new BX.admFltTab(i,this);
	};

	this.InitFilteredTab = function(tabId)
	{
		var flterId = false;

		if(this.oOptions[tabId])
			flterId = tabId;
		else
			flterId = this.GetByPresetId(tabId);

		if(flterId === false)
			return false;

		if(!this.ApplyFilter(flterId))
			return false;

		if(this.state.folded)
		{
			this.oOptions["0"]["tab"].UnSetActive();
			this.oOptions[flterId]["tab"].SetActive();
		}

		this.oOptions[flterId]["tab"].SetFiltered(true);
		return true;
	};

	this.InitOpenedTab = function(tabIdUri, tabIdSes)
	{

		var tabIds = [tabIdUri, tabIdSes];

		var openedTabObj, openedTabId;

		for(var i in tabIds)
		{
			var tabId = tabIds[i];

			if(tabId=="")
				continue;

			openedTabId = false;

			if(this.oOptions[tabId])
				openedTabId = tabId;
			else
				openedTabId = this.GetByPresetId(tabId);

			if(openedTabId === false)
				continue;

			openedTabObj = BX("adm-filter-tab-"+this.filter_id+'-'+openedTabId);

			if(openedTabObj)
				break;
		}

		if(!openedTabObj)
				return false;

		//openedTabObj.onclick();
		this.SetActiveTab(openedTabObj);
		this.ApplyFilter(openedTabId);

		if(openedTabId == tabIdUri)
			this.SaveFilterParams();

		return true;
	};

	this.GetByPresetId = function(presetId)
	{
		for(var i in  this.oOptions )
			if(this.oOptions[i] && this.oOptions[i]["PRESET_ID"] && this.oOptions[i]["PRESET_ID"] == presetId)
				return i;

		return false;
	};

	this.isObjectEmpty = function( obj )
	{
		for ( var key in obj )
			return false;

		return true;
	};

	this.ChangeViewDependVisible = function()
	{
		var countVR = this.CountVisibleRows();

		if(countVR < 1)
			this.ToggleFilterRow(this.filter_id+'_row_'+this.aRows[0], true);

		if(countVR <= 1)
			this.ToggleButtonsHideAll();

		if(countVR >= 2)
			this.ToggleButtonsShowAll();

		this.SetBottomStyle();
	};

	this.UrlAddParams = function(url, sParams)
	{
		var retUrl = url;
		var lastUrlSymb = url.substr(url.length-1);

		if(retUrl.indexOf('?') >= 0)
		{
			if(lastUrlSymb!='&')
				retUrl += '&';
		}
		else
		{
			retUrl += '?';
		}

		retUrl+=sParams;

		return retUrl;
	};

	this.OnSet = function(table_id, url, oButt)
	{
		if(!window[table_id])
		{
			return true;
		}

		if(this.state.requesting)
		{
			return false;
		}

		if(!this.table_id)
		{
			this.table_id = table_id;
		}

		if(!this.url)
		{
			this.url = url;
		}

		BX.onCustomEvent(window, 'onBeforeAdminFilterSet');

		if(this.curID != "0" && !this.state.init)
		{
			this.Save();
		}

		var filterUrl = this.UrlAddParams(url,'set_filter=Y&adm_filter_applied='+encodeURIComponent(this.curID));

		if(this.oOptions[this.curID]["PRESET_ID"])
		{
			filterUrl+=this.UrlAddParams(filterUrl,"adm_filter_preset="+encodeURIComponent(this.oOptions[this.curID]["PRESET_ID"]));
		}

		var params = this.GetParameters();

		this.state.requesting = true;

		BX.defer(function()
		{
			if(_this.state.folded)
			{
				_this.currentLoadedTab = _this.oOptions[_this.curID]["tab"].GetObj();
				_this.oOptions[_this.curID]["tab"].ShowWheel();

			}
			else
			{
				BX.adminPanel.showWait(oButt);
			}

			//wait until filter ajax-saving
			var waiter =
			{
				func: function()
				{
					if (!_this.state.saving)
					{
						window[table_id].GetAdminList(filterUrl+params);
						_this.oOptions[_this.curID]["tab"].SetFiltered(_this.state.init);
						clearInterval(intervalID);
					}
				}
			};

			var intervalID = window.setInterval(function(){ waiter.func.call(waiter) }, 200);

		})();

		return false;
	};

	this.OnClear = function(table_id, url, oButt)
	{
		if(!window[table_id])
		{
			return true;
		}

		if(this.state.requesting)
		{
			return false;
		}

		this.state.clearing = true;
		BX.onCustomEvent(window, 'onBeforeAdminFilterClear');
		var filterUrl = this.UrlAddParams(url,"del_filter=Y"+this.GetParameters());

		this.state.requesting = true;

		BX.defer(function()
		{
			if(_this.state.folded)
			{
				_this.currentLoadedTab = _this.oOptions[_this.curID]["tab"].GetObj();
				_this.oOptions[_this.curID]["tab"].ShowWheel();
			}
			else
			{
				BX.adminPanel.showWait(oButt);
			}

			if(_this.params.filteredId && _this.oOptions[_this.params.filteredId] && !_this.state.folded)
			{
				_this.oOptions[_this.params.filteredId]["tab"].UnSetFiltered();
			}

			window[table_id].GetAdminList(filterUrl);
		})();

		return false;
	};

	//when window[table_id].GetAdminList(...) executed
	this.onAdminListLoaded = function()
	{
		if (this.currentLoadedTab === false)
			return;

		BX.removeClass(this.currentLoadedTab, "adm-filter-tab-loading");

		if(this.state.clearing && this.params.filteredId !== false && this.oOptions[this.params.filteredId])
			this.oOptions[this.params.filteredId]["tab"].UnSetFiltered();

		this.currentLoadedTab = null;
		this.state.clearing = false;
		this.state.requesting = false;
	};

	this.GetFormButton = function(name)
	{
		if(!name)
			return false;

		var button = BX(this.filter_id+name);

		if(button)
			return button;

		return this.form[name];
	};

	this.ApplyFilter = function(id)
	{
		if(this.state.requesting && !this.state.init)
			return false;

		if(!this.oOptions[id])
			return false;

		if(this.curID == "0")
			this.oOptions["0"]["FIELDS"] = this.GetFilterFields(true);

		this.curID = id;

		this.StartAnimation();

		this.SetFilterFields(this.oOptions[id]["FIELDS"]);

		if(!this.state.init)
		{
			//this.SaveOpenTab(id);
			this.SaveFilterParams();
		}

		this.EndAnimation();

		if(this.state.folded && !this.state.init)
		{
			this.currentLoadedTab = this.oOptions[id]["tab"].GetObj();
			this.oOptions[id]["tab"].ShowWheel();

			//click on pressed button
			if(this.params.filteredId === id)
			{
				var clearButton = this.GetFormButton('del_filter');

				if(this.filter_id  && this.url)
				{
					this.OnClear(this.table_id, this.url, clearButton);
				}
				else
				{
					if(clearButton)
						clearButton.onclick();
				}
			}
			else // click on unpressed button
			{
				var setFilterButton = this.GetFormButton('set_filter');

				if(this.filter_id  && this.url)
				{
					this.OnSet(this.table_id, this.url, setFilterButton);
				}
				else
				{
					if(setFilterButton)
						setFilterButton.onclick();
					else
						this.form.submit();
				}
			}
		}

		return true;
	};

	this.Save = function(saveAs)
	{
		var fields = this.GetFilterFields(true);

		if((!this.oOptions[this.curID]["EDITABLE"] && !saveAs))
		{
			this.SaveInsteadPreset();
			return;
		}

		if(saveAs || this.curID == "0")
			this.ShowSaveOptsWnd(fields, false);
		else
		{
			var common = (this.oOptions[this.curID]["COMMON"] == 'Y');
			this.SaveToBase(this.oOptions[this.curID]["NAME"], common, fields, false, false);
		}
	};

	this.SaveAs = function()
	{
		this.Save(true);
	};

	this.Delete = function()
	{
		if(!this.oOptions[this.curID].EDITABLE)
			this.MarkPresetAsDeleted(this.curID);
		else
			this.DeleteFromBase(this.curID);
	};

	this.GetClearFields = function()
	{
		var fields = this.GetFilterFields();

		for(var key in fields)
			fields[key]["value"] = "";

		return fields;
	};

	this.ReplaceFilterTab = function(oldId, newId)
	{
		if(!oldId || !newId)
			return false;

		var tab = BX("adm-filter-tab-"+this.filter_id+"-"+oldId);

		if(!tab)
			return false;

		tab.id = "adm-filter-tab-"+this.filter_id+"-"+newId;

		if(this.url)
		{
			var registerUrl = BX.util.remove_url_param(this.url,["adm_filter_applied","adm_filter_preset"]);
			registerUrl += "&adm_filter_applied" + '=' + BX.util.urlencode(newId);
			BX.adminMenu.registerItem(tab.id, {URL: registerUrl});
		}

		tab.onclick = function(){ _this.SetActiveTab(this); _this.ApplyFilter(newId); };

		this.MarkPresetAsDeleted(oldId,newId);

		return true;
	};

	this.SetFilteredBG = function(id)
	{
		if(!this.params.filteredId && id !== false)
			return;

		if(id == this.params.filteredId && id !== false)
			BX.addClass(BX("adm-filter-tab-wrap-"+this.filter_id),"adm-current-filter");
		else
		{
			BX.removeClass(BX("adm-filter-tab-wrap-"+this.filter_id),"adm-current-filter");
		}
	};

	this.SetActiveTab = function(tabObj)
	{
		if(this.state.requesting && !this.state.init)
			return false;

		var tabIdBegin = "adm-filter-tab-"+this.filter_id+"-";
		var tabId = tabObj.id.substr(tabIdBegin.length,tabObj.id.length);

		if(this.params.filteredId!== false && this.params.filteredId === tabId && this.state.folded)
			return true;

		var arPrevSelTabs = BX.findChildren(tabObj.parentNode, {tag: "span"} ,false);

		for (var i=arPrevSelTabs.length-1; i>=0; i--)
			BX.removeClass(arPrevSelTabs[i] ,"adm-filter-tab-active");

		this.SetFilteredBG(tabId);
		this.oOptions[tabId]["tab"].SetActive();
		this.params.activeTabId = tabId;

		return true;
	};

	this.ShowSaveOptsWnd = function(fields, empty)
	{
		var bCreated = false;
		if(!window['filterSaveOptsDialog'+this.filter_id])
		{
			window['filterSaveOptsDialog'+this.filter_id] = new BX.CDialog({
				'content':'<form name="flt_save_opts_'+this.filter_id+'" onkeypress=" return '+this.filter_id+'.SaveOptsWndKeyPress(event);"></form>',
				'title': BX.message('JSADM_FLT_SAVE_TITLE'),
				'width': 450,
				'height': 100,
				'resizable': false
			});
			bCreated = true;
		}

		var formOpts = document['flt_save_opts_'+this.filter_id];

		formOpts.onKeyPress = this.onKeyPress;
		var fsTable= BX('filter_save_opts_'+this.filter_id).children[0];

		window['filterSaveOptsDialog'+this.filter_id].ClearButtons();

		window['filterSaveOptsDialog'+this.filter_id].SetButtons([
			{
				'id': this.filter_id+"_btn_save",
				'className':'adm-btn-save',
				'title': BX.message('JSADM_FLT_SAVE'),
				'action': function(){

					var common;
					if(formOpts.common)
						common = formOpts.common.checked;
					else
						common = false;

					_this.SaveToBase(formOpts.save_filter_name.value, common, fields, true, empty);

					if(_this.state.folded)
						_this.SetFoldedView();

					this.parentWindow.Close();
				}
			},
			BX.CDialog.prototype.btnCancel
		]);

		window['filterSaveOptsDialog'+this.filter_id].adjustSizeEx();
		window['filterSaveOptsDialog'+this.filter_id].Show();

		if(bCreated)
			formOpts.appendChild(fsTable);

		if(this.curID != "0" && !empty)
		{
			formOpts.save_filter_name.value = (this.oOptions[this.curID]["NAME"] ? this.oOptions[this.curID]["NAME"] : '');

			if(formOpts.common)
				formOpts.common.checked = (this.oOptions[this.curID]["COMMON"] == 'Y');
		}
		else
		{
			formOpts.save_filter_name.value = BX.message('JSADM_FLT_NEW_NAME');

			if(formOpts.common)
				formOpts.common.checked = false;
		}

		formOpts.save_filter_name.focus();
	};

	this.SaveOptsWndKeyPress = function(event)
	{
		if(!event)
			event = window.event;

		if(!event)
			return true;

		if(event.keyCode == 13)
		{
			BX(this.filter_id+"_btn_save").click();
			return false;
		}

		if(event.keyCode == 27)
		{
			window['filterSaveOptsDialog'+this.filter_id].Close();
			return false;
		}

		return true;
	};

	this.MarkPresetAsDeleted = function(oldId, newId)
	{
		if(!newId && !confirm(BX.message('JSADM_FLT_DEL_CONFIRM')))
			return;

		this.presetsDeleted[this.presetsDeleted.length] = oldId;

		var strOpt = '';

		for(var key in this.presetsDeleted)
				strOpt += (strOpt != ''? ',':'')+this.presetsDeleted[key];

		var bCurrentFiltered = false;

		if(this.params.filteredId == this.curID)
			bCurrentFiltered = true;

		var tabId = "0";

		if(newId)
		{
			this.oOptions[this.curID]["tab"].id = this.curID = tabId = newId;
		}
		else
		{
			this.oOptions[oldId]["tab"].DeleteHtml();
			delete this.oOptions[oldId];
		}

		var newActiveTab = this.oOptions[tabId]["tab"].GetObj();

		if(newActiveTab)
			newActiveTab.click();

		BX.userOptions.save('filter', this.filter_id, 'presetsDeleted', strOpt);

		if(bCurrentFiltered && this.table_id && !newId)
			this.OnClear(this.table_id,this.url);
	};

	this.DeleteFromBase = function(id)
	{
		this.state.saving = true;
		if(!confirm(BX.message('JSADM_FLT_DEL_CONFIRM')))
			return;

		var data = {
			'id': id,
			'action': 'del_filter',
			'sessid': phpVars.bitrix_sessid,
			'lang': BX.message("LANGUAGE_ID")
		};

		var callback = function(result)
		{
			if(result)
			{
				_this.oOptions[id]["tab"].DeleteHtml();
				delete _this.oOptions[id];

				var bCurrentFiltered = false;

				if(_this.params.filteredId == _this.curID)
					bCurrentFiltered = true;

				var defaultTab = _this.oOptions["0"]["tab"].GetObj();

				if(defaultTab)
					defaultTab.click();

				_this.state.saving = false;

				if(_this.table_id && bCurrentFiltered)
					_this.OnClear(_this.table_id,_this.url);
			}
			else
				alert(BX.message('JSADM_FLT_DEL_ERROR'));
		};

		BX.ajax.post('/bitrix/admin/filter_act.php', data, callback);

	};

	this.SaveInsteadPreset = function()
	{
		this.state.saving = true;
		var data = {
			'filter_id': this.filter_id,
			'preset_id': this.curID,
			'action': 'save_filter',
			'sessid': phpVars.bitrix_sessid,
			'name': this.oOptions[this.curID]["NAME"],
			'common': 'N',
			'fields': _this.GetFilterFields(),
			'lang': BX.message("LANGUAGE_ID")
		};

		if(this.oOptions[this.curID]["SORT_FIELD"])
			data['sort_field'] = this.oOptions[this.curID]["SORT_FIELD"];

		if(this.oOptions[this.curID]["SORT"])
			data['sort'] = this.oOptions[this.curID]["SORT"];

		var callback = function(resultId)
		{
			if(resultId)
			{
				_this.oOptions[resultId] =
				{
					NAME: _this.oOptions[_this.curID]["NAME"],
					FIELDS: _this.GetFilterFields(),
					EDITABLE: true,
					PRESET_ID: _this.curID,
					COMMON: false
				};

				_this.oOptions[resultId]["tab"] = new BX.admFltTab(resultId,_this);

				if(data['sort_field'])
					_this.oOptions[resultId]["SORT_FIELD"] = data['sort_field'];

				if(data['sort'])
					_this.oOptions[resultId]["SORT"] = data['sort'];

				_this.ReplaceFilterTab(_this.curID, resultId);
				_this.state.saving = false;
			}
			else
				alert(BX.message('JSADM_FLT_SAVE_ERROR'));
		};

		BX.ajax.post('/bitrix/admin/filter_act.php', data, callback);
	};


	this.SaveToBase = function(name, common, fields, saveAs, empty)
	{
		this.state.saving = true;
		if(name=="")
			name = BX.message('JSADM_FLT_NO_NAME');

		var data = {
			'filter_id': this.filter_id,
			'action': 'save_filter',
			'sessid': phpVars.bitrix_sessid,
			'name': name,
			'common': common ? 'Y' : 'N',
			'fields': fields,
			'lang': BX.message("LANGUAGE_ID")
		};

		if(!saveAs && this.curID != "0")
			data['id']=this.curID;

		if(!saveAs && this.oOptions[this.curID]["PRESET_ID"])
			data['preset_id']=this.oOptions[this.curID]["PRESET_ID"];

		if(this.oOptions[this.curID]["SORT_FIELD"])
			data['sort_field'] = this.oOptions[this.curID]["SORT_FIELD"];

		if(this.oOptions[this.curID]["SORT"])
			data['sort'] = this.oOptions[this.curID]["SORT"];

		var callback = function(resultId)
		{
			if(resultId)
			{
				_this.oOptions[resultId] =
				{
					NAME: name,
					COMMON: common ? "Y" : "N",
					FIELDS: fields,
					EDITABLE: true
				};

				if(data['sort_field'])
					_this.oOptions[resultId]["SORT_FIELD"] = data['sort_field'];

				if(data['sort'])
					_this.oOptions[resultId]["SORT"] = data['sort'];

				_this.oOptions[resultId]["tab"] = new BX.admFltTab(resultId,_this);

				if(saveAs || data['id'] == undefined)
					_this.oOptions[resultId]["tab"].AddHtml(_this.url, name);

				if(empty)
					_this.ClearParameters();

				_this.state.saving = false;
			}
			else
				alert(BX.message('JSADM_FLT_SAVE_ERROR'));
		};

		BX.ajax.post('/bitrix/admin/filter_act.php', data, callback);

		return data;
	};

	this.ClearParameters = function()
	{
		if(!this.form)
			return;

		var i;
		var n = this.form.elements.length;
		for(i=0; i<n; i++)
		{
			var el = this.form.elements[i];

			BX.removeClass(el,"adm-calendar-inp-setted");

			switch(el.type.toLowerCase())
			{
				case 'text':
				case 'textarea':
					el.value = '';
					break;

				case 'select-one':
					el.selectedIndex = 0;
					if(el.onchange)
						el.onchange();
					break;

				case 'select-multiple':
					var j;
					var l = el.options.length;
					for(j=0; j<l; j++)
						el.options[j].selected = false;
					break;

				default:
					break;
			}
		}
	};

	this.GetRowByElement = function(element)
	{
		return jsUtils.FindParentObject(element, "tr");
	};

	this.SetFilterFields = function(fields)
	{
		this.ClearParameters();
		var checkboxesIdx = [];
		var elName;

		for(var i=0, n = this.form.elements.length; i<n; i++)
		{
			var el = this.form.elements[i];

			if(BX.hasClass(el,"adm-calendar-inp-setted"))
				continue;

			if(el.type == 'select-multiple')
			{
				elName = el.name.substr(0, el.name.length - 2);
			}
			else if(el.type == 'checkbox' && el.name.search(/[\[\]]/))
			{
				elName = el.name.substr(0, el.name.length - 2);

				if(checkboxesIdx[elName] == undefined)
					checkboxesIdx[elName] = 0;
				else
					checkboxesIdx[elName]++;

				elName +="_cbxIdx_"+checkboxesIdx[elName];

				el.checked = false;
			}
			else
			{
				elName = el.name;
			}

			if(!fields[elName])
			{
				var row = this.GetRowByElement(el);

				if(!row)
					continue;

				if(this.IsAllRowElementsHidden(row.id, fields))
					this.ToggleFilterRow(row.id, false, false, true);

				continue;
			}

			switch(el.type.toLowerCase())
			{
				case 'select-one':
					el.value = fields[elName]["value"];

					if(el.value == "")
						el.selectedIndex = 0;

					break;

				case 'text':
				case 'textarea':
					el.value = fields[elName]["value"];
					break;

				case 'radio':
				case 'checkbox':
					el.checked = (el.value == fields[elName]["value"]);
					break;

				case 'select-multiple':
					var bWasSelected = false;
					el.value = null;
					if (el.options.length > 0)
					{
						el.options[0].selected = false;
						for(var j=0, l=el.options.length; j<l; j++)
						{
							for(var option in fields[elName]['value'])
							{
								if(el.options[j].value == fields[elName]['value'][option])
								{
									el.options[j].selected = true;
									bWasSelected = true;
								}
							}
						}

						if(!bWasSelected && el.options[0].value == '')
							el.options[0].selected = true;
					}
					break;

				default:
					break;
			}

			BX.fireEvent(el, 'change');

			if(fields[elName]['hidden'] ==  'true' && this.IsAllRowElementsHidden(this.GetRowByElement(el).id, fields))
				this.ToggleFilterRow(this.GetRowByElement(el).id, false, false, true);
			else
				this.ToggleFilterRow(this.GetRowByElement(el).id, true, false);
		}

		if(this.CountVisibleRows() < 1)
			this.ToggleFilterRow(this.filter_id+'_row_'+this.aRows[0], true, false);

		//this.SaveRowsOption();
	};

	this.IsFormElementHidden = function (el)
	{

		if(BX.browser.IsOpera())
			return !el.offsetWidth && !el.offsetHeight && !el.clientHeight && !el.clientWidth;

		return !el.offsetWidth && !el.offsetHeight;
	};

	this.IsAllRowElementsHidden = function (rowId, fields)
	{
		var bAllHidden = true;

		for(var i=0, n = this.form.elements.length; i<n; i++)
		{
			var el = this.form.elements[i];

			if(!fields[el.name])
				continue;

			if(jsUtils.FindParentObject(el, "tr").id != rowId)
				continue;

			if(fields[el.name]['hidden'] == 'false')
			{
					bAllHidden = false;
					break;
			}
		}

		return bAllHidden;
	};

	this.GetFilterFields = function(bSetVisibilityByRow)
	{
		var fields = {};
		var checkboxesIdx = [];
		var elName;

		for(var i=0, n = this.form.elements.length; i<n; i++)
		{
			var el = this.form.elements[i];

			if(!el.name)
				continue;

			if(el.type == 'select-multiple')
			{
				elName = el.name.substr(0, el.name.length - 2);
			}
			else if(el.type == 'checkbox' && el.name.search(/[\[\]]/))
			{
				elName = el.name.substr(0, el.name.length - 2);

				if(checkboxesIdx[elName] == undefined)
					checkboxesIdx[elName] = 0;
				else
					checkboxesIdx[elName]++;

				elName += "_cbxIdx_"+checkboxesIdx[elName];
			}
			else
			{
				elName = el.name;
			}

			switch(el.type.toLowerCase())
			{
				case 'select-one':
				case 'text':
				case 'textarea':
					fields[elName] = { value: el.value };
					break;

				case 'radio':
					if(el.checked)
						fields[elName] = { value: el.value };
					break;

				case 'checkbox':
					if(el.checked)
						fields[elName] = { value: el.value };
					else
						fields[elName] = { value: false };
					break;

				case 'select-multiple':
					fields[elName] = {value:[]};

					for(var j=0, l = el.options.length; j<l; j++)
						if(el.options[j].selected && el.options[j].value)
							fields[elName]['value']['sel_'+el.options[j].value] = el.options[j].value;

					//fields[elName]['hidden'] = this.IsFormElementHidden(el);

					break;
				default:
					break;
			}

			if(!fields[elName])
				continue;

			if(bSetVisibilityByRow)
				fields[elName]['hidden'] = (this.GetRowByElement(el).style.display == 'none' ? 'true' : 'false');
			else
				fields[elName]['hidden'] = this.IsFormElementHidden(el) ? 'true' : 'false';

		}

		return fields;
	};

	this.IsFilterFill = function()
	{
		if(!this.form)
			return;

		var i;
		var n = this.form.elements.length;
		for(i=0; i<n; i++)
		{
			var el = this.form.elements[i];
			if(el.disabled)
				continue;
			var tr = this.GetRowByElement(el);
			if(tr && tr.style && tr.style.display == 'none')
				continue;

			switch(el.type.toLowerCase())
			{
				case 'select-one':
					if(el.options.length > 0)
						if(el.options[0].value.length != 0 && (el.options[0].value.toUpperCase() != 'NOT_REF' || el.value.toUpperCase() == 'NOT_REF'))
							break;
				case 'text':
				case 'textarea':
					if(el.value.length > 0)
						return true;
					break;
				case 'checkbox':
					if(el.checked)
						return true;
					break;
				case 'select-multiple':
					var j;
					var l = el.options.length;
					for(j=0; j<l; j++)
						if(el.options[j].selected && el.options[j].value != '')
							return true;
					break;
				default:
					break;
			}
		}
		return false;
	};

	this.GetParameters = function()
	{
		if(!this.form)
			return;

		var i, s = "";
		var n = this.form.elements.length;
		for(i=0; i<n; i++)
		{
			var el = this.form.elements[i];

			if(el.disabled)
				continue;

			var tr = this.GetRowByElement(el);

			if(tr && tr.style && tr.style.display == 'none')
				continue;

			if(el.className == "adm-select adm-calendar-period" && el.value != '')
			{
				var selPParent = el.parentNode.parentNode;
				var inputFrom = BX.findChild(selPParent, {'className':'adm-input adm-calendar-from'},true);
				var inputTo = BX.findChild(selPParent, {'className':'adm-input adm-calendar-to'},true);

				var dateFrom = false;
				var dateTo = false;
				var today = new Date();
				var year = today.getFullYear();
				var month = today.getMonth();
				var day = today.getDate();
				var dayW = today.getDay();

				if (dayW == 0)
					dayW = 7;

				switch(el.value)
				{
					case 'exact':
						dateFrom = new Date(inputFrom.value.replace(/(\d+).(\d+).(\d+)/, '$3/$2/$1'));
						dateTo = dateFrom;
						break;

					case 'after':
						inputTo.value = "";
						break;

					case 'before':
						inputFrom.value = "";
						break;

					default:
						break;
				}

				var format = window[inputFrom.name+"_bTime"] ? BX.message('FORMAT_DATETIME') : BX.message('FORMAT_DATE');

				if(dateFrom)
					inputFrom.value = BX.formatDate(dateFrom, format);

				if(dateTo)
					inputTo.value = BX.formatDate(dateTo, format);
			}

			var val = "";
			switch(el.type.toLowerCase())
			{
				case 'select-one':
				case 'text':
				case 'textarea':
				case 'hidden':
					val = el.value;
					break;
				case 'radio':
				case 'checkbox':
					if(el.checked)
						val = el.value;
					break;
				case 'select-multiple':
					var j;
					var l = el.options.length;
					for(j=0; j<l; j++)
						if(el.options[j].selected)
							s += '&' + el.name + '=' + encodeURIComponent(el.options[j].value);
					break;
				default:
					break;
			}
			if(val != "")
				s += '&' + el.name + '=' + encodeURIComponent(val);

		}

		if(this.oOptions[this.curID]["SORT_FIELD"] && typeof this.oOptions[this.curID]["SORT_FIELD"] == 'object')
		{
			for(var idx in this.oOptions[this.curID]["SORT_FIELD"])
			{
				s += '&by=' +encodeURIComponent(idx)+'&order='+this.oOptions[this.curID]["SORT_FIELD"][idx];
				break;
			}
		}
		return s;
	};

	this.CheckActive = function()
	{
		var i;
		var n = this.form.elements.length;
		for(i=0; i<n; i++)
		{
			var el = form.elements[i];
			if(el.disabled)
				continue;
			var tr = this.GetRowByElement(el);
			if(tr && tr.style && tr.style.display == 'none')
				continue;

			switch(el.type.toLowerCase())
			{
				case 'select-one':
					if(el.options[0].value.length != 0 && (el.options[0].value.toUpperCase() != 'NOT_REF' || el.value.toUpperCase() == 'NOT_REF'))
						break;
				case 'text':
				case 'textarea':
					if(el.value.length > 0)
						return true;
					break;
				case 'checkbox':
					if(el.checked)
						return true;
					break;
				case 'select-multiple':
					var j;
					var l = el.options.length;
					for(j=0; j<l; j++)
						if(el.options[j].selected && el.options[j].value != '')
							return true;
					break;
				default:
					break;
			}
		}
		return false;
	};

	this.DisplayNonEmptyRows = function()
	{
		if(!this.form)
			return;

		var i;
		var n = this.form.elements.length;
		for(i=0; i<n; i++)
		{
			var el = this.form.elements[i];
			if(el.disabled)
				continue;

			var bVisible = false;
			switch(el.type.toLowerCase())
			{
				case 'select-one':
					if(el.value.length>0 && (el.options[0].value.length == 0 || (el.options[0].value != el.value)))
						bVisible = true;
					break;

				case 'text':
				case 'textarea':
					if(el.value.length>0)
						bVisible = true;
					break;

				case 'checkbox':
					if(el.checked)
						bVisible = true;
					break;

				case 'select-multiple':
					var j;
					var l = el.options.length;
					for(j=0; j<l; j++)
						if(el.options[j].selected && el.options[j].value != '')
						{
							bVisible = true;
							break;
						}
					break;

				default:
					break;
			}
			if(bVisible)
			{
				var tr = jsUtils.FindParentObject(el, 'tr');
				if(tr.id)
					this.ToggleFilterRow(tr.id, true, false);
			}
		}
	};

	this.CountVisibleRows = function()
	{
		var counter = 0;
		for (var i=this.aRows.length-1; i>=0; i--)
			if(this.oVisRows[this.aRows[i]])
				counter++;

		return counter;
	};

	this.SetBottomStyle = function()
	{
		var bottomSeparator = BX(this.filter_id+"_bottom_separator");
		var contentDiv = BX(this.filter_id+"_content");

		if(this.CountVisibleRows() > 1)
		{
			contentDiv.className = "adm-filter-content";
			bottomSeparator.style.display = "block";
		}
		else
		{
			contentDiv.className = "adm-filter-content adm-filter-content-first";
			bottomSeparator.style.display = "none";
		}
	};

	this.ToggleButtonShow = function(rowId)
	{
		var row = BX(rowId);

		if(!row)
			return;

		row.cells[2].children[0].style.display = 'block';
	};

	this.ToggleButtonHide = function(rowId)
	{
		var row = BX(rowId);

		if(!row)
			return;

		row.cells[2].children[0].style.display = 'none';
	};

	this.ToggleButtonsShowAll = function()
	{
		for(var key in this.aRows)
			this.ToggleButtonShow(this.filter_id+'_row_'+this.aRows[key]);
	};

	this.ToggleButtonsHideAll = function()
	{
		for(var key in this.aRows)
			this.ToggleButtonHide(this.filter_id+'_row_'+this.aRows[key]);
	};

	this.ToggleFilterRow = function(rowId, on, bSave, skipControl)
	{
		var row = BX(rowId),
			delimiter = BX(rowId+'_delim'),
			ret = 0;


		if(!row || !delimiter)
			return ret;

		var short_id = rowId.substr((this.filter_id+'_row_').length);

		if(on != true && on != false)
			on = (row.style.display == 'none');

		if(on == true)
		{
			try{
				row.style.display = 'table-row';
				delimiter.style.display = 'table-row';
			}
			catch(e){
				row.style.display = 'block';
				delimiter.style.display = 'block';
			}
			this.oVisRows[short_id] = true;

			ret = row.offsetHeight + delimiter.offsetHeight;
		}
		else
		{
			if( skipControl || this.CountVisibleRows() > 1)
			{
				ret = -(row.offsetHeight + delimiter.offsetHeight);

				row.style.display = 'none';
				delimiter.style.display = 'none';
				this.oVisRows[short_id] = false;

			}
		}

		this.SetBottomStyle();

		var countVR = this.CountVisibleRows();

		if(countVR == 1)
			this.ToggleButtonsHideAll();

		if(countVR == 2)
			this.ToggleButtonsShowAll();


		if(bSave != false)
			this.SaveRowsOption();

		return ret;
	};

	this.DeleteFilterRow = function(rowId)
	{
		this.StartAnimation();
		this.ToggleFilterRow(rowId);
		this.EndAnimation();
	};

	this.StartAnimation = function()
	{
		if(this.state.folded)
			return;

		if (this.easing)
			this.easing.stop();

		this.startContentHeight = this.tableWrap.offsetHeight;
		this.tableWrap.style.height = this.startContentHeight + "px";
		this.tableWrap.style.overflowY = "hidden";
	};

	this.EndAnimation = function()
	{
		if(this.state.folded)
			return;

		var newHeight = this.table.offsetHeight;
		if (newHeight == 0)
		{
			this.tableWrap.style.height = "auto";
			this.tableWrap.style.overflowY = "visible";
			return;
		}


		this.easing = new BX.easing({
			duration : 500,
			start : { height: this.startContentHeight, opacity : 0 },
			finish : { height: newHeight, opacity : 100 },
			transition : BX.easing.makeEaseOut(BX.easing.transitions.quart),

			step : BX.proxy(function(state){
				this.tableWrap.style.height = state.height + "px";

				BX.onCustomEvent(this, 'onAdminFilterToggleRow');
			}, this),

			complete : BX.proxy(function(){
				this.tableWrap.style.height = "auto";
				this.tableWrap.style.overflowY = "visible";
				this.easing = null;

				BX.onCustomEvent(this, 'onAdminFilterToggleRow');
			}, this)

		});
		this.easing.animate();

	};

	this.SaveFilterParams = function()
	{
		var sParams = "filter_id:"+this.filter_id+",";

		for(var name in this.params)
			sParams += name+":"+this.params[name]+",";

		// Remove the last comma from the final string
		sParams = sParams.substr(0,sParams.length-1);

		document.cookie = BX.message('COOKIE_PREFIX')+"_ADM_FLT_PARAMS=" + sParams;
	};

	this.SaveRowsOption = function()
	{
		if(this.curID != "0")
		{
			this.Save(false);
			return true;
		}


		var sRows = '';

		for(var key in this.oVisRows)
			if(this.oVisRows[key] == true)
				sRows += (sRows != ''? ',':'')+key;

		jsUserOptions.SaveOption('filter', this.filter_id, 'rows', sRows);
	};

	this.SaveOpenTab = function(id)
	{
		var data = {
			'id': id,
			'filter_id': this.filter_id,
			'action': 'open_tab_save',
			'sessid': phpVars.bitrix_sessid,
			'lang': BX.message("LANGUAGE_ID")
		};

		BX.ajax.post('/bitrix/admin/filter_act.php', data);
	};

	this.SaveFilteredId = function(id)
	{
		var data = {
			'id': id,
			'filter_id': this.filter_id,
			'action': 'filtered_tab_save',
			'sessid': phpVars.bitrix_sessid,
			'lang': BX.message("LANGUAGE_ID")
		};

		BX.ajax.post('/bitrix/admin/filter_act.php', data);
	};

	this.ToggleAllFilterRows = function(on)
	{
		var tbl = document.getElementById(this.filter_id);
		if(!tbl)
			return;

		this.StartAnimation();

		var n = tbl.rows.length;
		for(var i=n-1; i>=0; i--)
		{
			var row = tbl.rows[i];
			if(row.id && row.cells[0].className != 'delimiter')
				this.ToggleFilterRow(row.id, on, false);
		}

		if(on)
			this.ToggleButtonsShowAll();
		else
			this.ToggleButtonsHideAll();

		this.SaveRowsOption();

		this.EndAnimation();
	};

	this.SaveMenuShow = function(el)
	{
		var menuItems =[];

		if(this.curID != "0")
			menuItems.push({TEXT: BX.message('JSADM_FLT_SAVE'), ONCLICK: filter_id+".Save();"});

		menuItems.push({TEXT: BX.message('JSADM_FLT_SAVE_AS'), ONCLICK: 'setTimeout(function(){'+filter_id+'.SaveAs();},10);'});

		if(this.curID != "0") //&& this.oOptions[this.curID].EDITABLE)
			menuItems.push({TEXT: BX.message('JSADM_FLT_DELETE'), ONCLICK: filter_id+".Delete();"});

		if (!el.OPENER)
			BX.adminShowMenu(el,menuItems);
		else
			el.OPENER.SetMenu(menuItems);

	};

	this.SettMenuItemClick = function(rowId, objItem)
	{

		var menu = BX.WindowManager.Get();
		if (menu && BX.type.isFunction(menu.toggleArrow))
			menu.toggleArrow(false);

		this.StartAnimation();

		var scrollOffset = this.ToggleFilterRow(rowId);

		this.EndAnimation();
	};

	this.SettMenuShow = function(el)
	{
		var tbl = BX(this.filter_id);

		if(!tbl)
			return;

		var menuItems =[];
		var diff = this.missingRows;
		var itemsIdx = this.aRows.length-1;

		for(var i = tbl.rows.length-1; i >=0; i--)
		{
			var row = tbl.rows[i];

			if(!row.id || row.cells[0].className == 'delimiter')
				continue;

			var text ="";
			if(itemsIdx-diff >= 0)
				text = this.popupItems[this.aRows[itemsIdx]];
			else
				text = (row.cells[0].textContent || row.cells[0].innerText).replace(/:$/,"");

			menuItems.unshift({
				TEXT: text,
				ONCLICK: filter_id+".SettMenuItemClick('"+row.id+"',this);",
				CLOSE_ON_CLICK: false,
				ADJUST_ON_CLICK: false,
				CHECKED: (row.style.display != 'none')
			});

			itemsIdx--;
		}

		menuItems.push({SEPARATOR: true});
		menuItems.push({
			TEXT: BX.message('JSADM_FLT_SHOW_ALL'),
			ONCLICK: filter_id+".ToggleAllFilterRows(true);"
		});

		menuItems.push({
			TEXT: BX.message('JSADM_FLT_HIDE_ALL'),
			ONCLICK: filter_id+".ToggleAllFilterRows(false);"
		});

		if (!el.OPENER)
			BX.adminShowMenu(el,menuItems);
		else
		{
			el.OPENER.SetMenu(menuItems);
			var menu = el.OPENER.GetMenu();
			if (menu)
				menu.toggleArrow(true);
		}
	}
};

//********** admin filter tab object begin****************
BX.admFltTab = function(id, fltObj)
{
	this.id = id;
	this.filter = fltObj;
};

BX.admFltTab.prototype = {

	GetObjId: function()
	{
		return "adm-filter-tab-"+this.filter.filter_id+"-"+this.id;
	},

	GetObj: function()
	{
		var tabObjId = this.GetObjId();
		return BX(tabObjId);
	},

	SetActive: function()
	{
		BX.addClass(this.GetObj(),"adm-filter-tab-active");
	},

	UnSetActive: function()
	{
		BX.removeClass(this.GetObj(),"adm-filter-tab-active");
	},

	SetFiltered: function(init)
	{
		if(this.filter.params.filteredId !== false && this.filter.oOptions[this.filter.params.filteredId] !==undefined && !init)
			this.filter.oOptions[this.filter.params.filteredId]["tab"].UnSetFiltered();

		BX.addClass(this.GetObj(),"adm-current-filter-tab");

		if(!init)
		{
			//this.filter.SaveFilteredId(this.id);
			this.filter.params.filteredId = this.id;
			this.filter.SaveFilterParams();

		}

		this.filter.params.filteredId = this.id;
		this.filter.SetFilteredBG(this.id);
	},

	UnSetFiltered: function()
	{
		this.filter.params.filteredId = false;
		BX.removeClass(this.GetObj(),"adm-current-filter-tab");
		this.filter.SetFilteredBG(false);
		//this.filter.SaveFilteredId(false);
		this.filter.SaveFilterParams();
	},

	_RegisterDD: function(tabId, url, name)
	{
		if(!url)
			return false;

		var registerUrl = BX.util.remove_url_param(url, ["adm_filter_applied","adm_filter_preset"]);
		registerUrl += "&adm_filter_applied" + '=' + BX.util.urlencode(this.id);
		BX.adminMenu.registerItem(tabId, {URL: registerUrl, TITLE: true});
	},

	AddHtml: function(url, name)
	{
		var _this = this;
		var tabsBlock = BX("filter-tabs-"+this.filter.filter_id);
		var newTab = document.createElement('span');
		newTab.className = "adm-filter-tab";
		newTab.id = this.GetObjId();
		newTab.onclick = function(){ _this.filter.SetActiveTab(this); _this.filter.ApplyFilter(_this.id); };
		newTab.innerHTML = BX.util.htmlspecialchars(name);
		tabsBlock.insertBefore(newTab, BX("adm-filter-add-tab-"+this.filter.filter_id));
		this._RegisterDD(newTab.id, url, name);
		this.filter.SetActiveTab(newTab);
		this.filter.ApplyFilter(this.id);
	},

	DeleteHtml: function()
	{
		var delTab = this.GetObj();

		if(delTab)
			delTab.parentNode.removeChild(delTab);
	},

	ShowWheel: function()
	{
		var timeout = 250;

		setTimeout(
			BX.proxy(
				function() {
					if (this.GetObj())
						BX.addClass(this.GetObj(), "adm-filter-tab-loading");
				},
			this),
		timeout);
	}
};

//********** admin filter wrap object begin****************
BX.admFltWrap = {

	Inner: function(el, elClass, wrapType, wrapClass)
	{
		var wrap = document.createElement(wrapType);

		if(wrapClass)
			wrap.className = wrapClass;

		if(elClass)
			el.className = elClass;

		var elChildren = BX.findChildren(el);

		for(var i in elChildren)
			wrap.appendChild(elChildren[i]);

		el.appendChild(wrap);

		return wrap;
	},

	Element: function(el, elClass, wrapType, wrapClass)
	{
		var wrap = document.createElement(wrapType);

		if(wrapClass)
			wrap.className = wrapClass;

		if(elClass)
			el.className = elClass;

		el.parentNode.insertBefore(wrap, el);
		wrap.appendChild(el);

		return wrap;
	},

	Input:  function(el)
	{
		var wrap = false;
		switch (el.type)
		{
			case "select-one":
				if(el.size && el.size > 1)
					wrap = BX.admFltWrap.Element(el,"adm-select-multiple","span","adm-select-wrap-multiple");
				else
					wrap = BX.admFltWrap.Element(el,"adm-select","span","adm-select-wrap");
				break;

			case "select-multiple":
				wrap = BX.admFltWrap.Element(el,"adm-select-multiple","span","adm-select-wrap-multiple");
				break;

			case "text": // input
				wrap = BX.admFltWrap.Element(el,"adm-input","div","adm-input-wrap");
				break;

			case "checkbox":

				var label = BX.findChild(el.parentNode, {tagName: "label", htmlFor: el.id});
				if(!label)
				{
					var wraplabel = BX.admFltWrap.Element(el, "", "label", "");

					if(label && label.childNodes[0])
					{
						wraplabel.appendChild(label.childNodes[0]);
						label.parentNode.removeChild(label);
					}
				}

				BX.adminFormTools.modifyCheckbox(el);
				break;

			case 'submit':
			case 'button':
			case 'reset':
			case "hidden":
			default:
				break;
		}

		return wrap;
	},

	Cell: function(cell)
	{
		var newCell = cell.cloneNode(true);
		var wrap;
		newCell.innerHTML = "";

		while(cell.childNodes.length)
		{
			switch(cell.childNodes[0].nodeName.toLowerCase())
			{
				case 'small':
					BX.admFltWrap.Element(cell.childNodes[0], "", "span", "adm-filter-text-wrap");
					break;

				case '#text':

					cell.childNodes[0].nodeValue = jsUtils.trim(cell.childNodes[0].nodeValue);

					if(cell.childNodes[0].nodeValue == '')
					{
						cell.removeChild(cell.childNodes[0]);
						continue;
					}

					BX.admFltWrap.Element(cell.childNodes[0], "", "span", "adm-filter-text-wrap");

					break;

				case 'label':

					if(cell.childNodes[0].className == "adm-designed-checkbox-label")
						break;


					var input = BX.findChild(cell.childNodes[0],{tag: "input"});

					if(input)
						wrap = BX.admFltWrap.Input(input);

					break;

				case 'input':

					var helpIcon = false;

					var nextInput = BX.findNextSibling(cell.childNodes[0], {tagName: "INPUT"});

					if(cell.childNodes[0].type == "text" && ( !nextInput || nextInput.type != "text"))
						helpIcon = BX.findChild(cell.childNodes[0].parentNode, {className: "adm-input-help-icon"});

					wrap = BX.admFltWrap.Input(cell.childNodes[0]);

					if(helpIcon)
					{
						BX.addClass (wrap, "adm-input-help-icon-wrap");
						wrap.appendChild(helpIcon);
					}
					break;

				case 'select':
					BX.admFltWrap.Input(cell.childNodes[0]);
					break;

				case 'iframe':
					cell.childNodes[0].style.display = 'none';
					break;

				case 'span':
					if(cell.childNodes[0].style.display != 'none')
						cell.childNodes[0].style.display = 'inline-block';
					break;

				default:
					break;
			}

			newCell.appendChild(cell.childNodes[0]);
		}

		return newCell;
	},

	Row: function(row)
	{
		row.cells[0].className = "adm-filter-item-left";
		row.cells[1].className = "adm-filter-item-center";
		row.cells[2].className = 'adm-filter-item-right';
		row.cells[0].innerHTML = row.cells[0].innerHTML.replace(/<\/?[^>]+>/gi, ''); // strip_tags

		var calendarInput = ( !!BX.findChild(row.cells[1], {'className': 'adm-input adm-input-calendar'}, true));

		if(calendarInput)
		{
			var calendarBlock = BX.admFltWrap.Inner(row.cells[1], "","DIV","adm-calendar-block adm-filter-alignment");
			BX.admFltWrap.Inner(calendarBlock, "", "DIV", "adm-filter-box-sizing");
			return null;
		}

		if (row.cells[1].children[0] && !BX.hasClass(row.cells[1].children[0], 'adm-filter-alignment'))
		{
			var boxSizing = BX.create('div', {props: {className: 'adm-filter-box-sizing'}});
			var alingment = BX.create('div', {props: {className: 'adm-filter-alignment'}});

			row.cells[1].innerHTML = BX.admFltWrap.Cell(row.cells[1]).innerHTML;

			while(row.cells[1].children.length>0)
				boxSizing.appendChild(row.cells[1].children[0]);

			alingment.appendChild(boxSizing);
			row.cells[1].appendChild(alingment);
		}
		return row;
	}
};
//********** admin filter wrap object end****************

BX.adminChain = {
	_addon: null,

	addItems: function(divId)
	{
		BX.ready(function(){BX.adminChain._addItems(divId)});
	},

	_addItems: function(divId)
	{
		var main_chain = BX("main_navchain");
		if(!main_chain)
			return;

		if (!!this._addon)
		{
			this._addon.parentNode.removeChild(this._addon);
			this._addon = null;
		}

		var div = BX(divId);
		if(!div)
			return;

		this._addon = main_chain.appendChild(BX.create('span', {html: '<span class="adm-navchain-delimiter"></span>' + div.innerHTML}));
	}
};

/************************* singletons construction **************************/

BX.InitializeAdmin = function()
{
	BX.browser.addGlobalFeatures(["boxShadow", "borderRadius", "flexWrap", "boxDirection", "transition", "transform"]);
	BX.adminPanel = new BX.adminPanel();
	BX.adminMenu = new BX.adminMenu();

	if (!!(history.pushState && BX.type.isFunction(history.pushState)))
	{
		BX.adminHistory = new BX.adminHistory();
	}

	BX.ready(function() {
		var workarea = BX("adm-workarea");
		if (workarea)
			workarea.style.opacity = 1;
	});
};

BX.adminPanel.modifyFormElements = BX.adminFormTools.modifyFormElements;
BX.adminPanel.modifyFormElement = BX.adminFormTools.modifyFormElement;

})();
