BX.namespace("BX.Catalog");
BX.Catalog.ProductSearchDialog = (function () {

	var ProductSearchDialog = function (parameters) {
		this.callback = null;
		this.event = null;
		this.tableId = parameters.tableId;
		this.multiple = parameters.multiple;
		this.itemDataNamePrefix = parameters.itemDataNamePrefix;
		if (typeof(parameters.event) !== 'undefined')
		{
			this.event = parameters.event;
		}
		else if (typeof(parameters.callback) !== 'undefined')
		{
			this.callback = parameters.callback;
		}
		this.callerName = parameters.callerName;
		this.currentUri = parameters.currentUri;
		this.popup = BX.WindowManager.Get();
		this.iblockName = parameters.iblockName;
		this.searchTimer = null;
		this.ignoreFilter = true;

		var me = this, keydownListener = function(e) {
			var dialogExists = !!BX(me.tableId+'_reload_container');
			if (dialogExists)
			{
				if (!e.altKey && !e.ctrlKey && !e.metaKey && document.activeElement.tagName !== 'INPUT'
					&& document.activeElement.tagName !== 'SELECT'
					&& document.activeElement.tagName !== 'TEXTAREA')
				{
					//alpha and digits
					if (e.keyCode >= 48 && e.keyCode <= 90
						|| e.keyCode >= 96 && e.keyCode <= 105
						|| e.keyCode == 219 || e.keyCode == 221) {
						BX(me.tableId + '_query').focus();
					}
				}
			}
			else if(document.removeEventListener)
				document.removeEventListener('keydown', keydownListener);
			else
				document.detachEvent('onkeydown', keydownListener);
		};

		if(document.addEventListener)
			document.addEventListener('keydown', keydownListener);
		else if (document.attachEvent)
			document.attachEvent('onkeydown',keydownListener);
	};

	ProductSearchDialog.prototype.getIblockId = function () {
		return BX(this.tableId + '_iblock').value;
	};

	ProductSearchDialog.prototype.getForm = function ()
	{
		return BX(this.tableId + '_form');
	};

	ProductSearchDialog.prototype.getGridForm = function ()
	{
		return BX('form_' + this.tableId);
	};

	ProductSearchDialog.prototype.sendItems = function(items)
	{
		if (BX.type.isNotEmptyString(this.event))
		{
			BX.onCustomEvent(
				this.event,
				[items]
			);
		}
		else if (BX.type.isNotEmptyString(this.callback))
		{
			window[this.callback](
				items,
				this.getIblockId()
			);
		}
	};

	ProductSearchDialog.prototype.getItemData = function(id)
	{
		let selectData = BX(this.itemDataNamePrefix + id);
		const fieldList = [
			'id',
			'type',
			'name',
			'full_quantity',
			'measureRatio',
			'measure',
			'quantity',
			'IBLOCK_ID'
		];

		if (!BX.type.isElementNode(selectData))
		{
			return null;
		}

		let result = {};

		fieldList.forEach(function(item){
			let index = 'product' + item;
			if (index in selectData.dataset)
			{
				result[item] = selectData.dataset[index];
			}
		});

		if (BX.type.isNotEmptyObject(result))
		{
			if (typeof(result.IBLOCK_ID) === 'undefined')
			{
				result.IBLOCK_ID = this.getIblockId();
			}

			let quantity = this.getItemCurrentQuantity(id);
			if (quantity !== null)
			{
				result.quantity = quantity;
			}

			return result;
		}

		return null;
	};

	ProductSearchDialog.prototype.getItemCurrentQuantity = function(id)
	{
		let quantity = BX(this.tableId + '_qty_' + id);
		if (BX.type.isElementNode(quantity))
		{
			return quantity.value;
		}

		return null;
	};

	ProductSearchDialog.prototype.selectItem = function (id) {

		let product = this.getItemData(id);

		if (product === null)
		{
			return;
		}

		if (this.multiple)
		{
			this.sendItems([product]);
		}
		else
		{
			this.sendItems(product);
		}
	};

	ProductSearchDialog.prototype.selectItemList = function()
	{
		if (!this.multiple)
		{
			return;
		}

		let items = [],
			frm = this.getGridForm();

		if (BX.type.isElementNode(frm))
		{
			let elementList = frm.querySelectorAll('input[name="ID[]"]');
			elementList.forEach(
				function(element)
				{
					if (element.checked)
					{
						let product = this.getItemData(element.value);
						if (product !== null)
						{
							items.push(product);
						}
					}
				},
				this
			);
		}

		if (items.length > 0)
		{
			this.sendItems(items);
		}
	};

	ProductSearchDialog.prototype.fShowSku = function (sku, scope) {
		let i,
			item,
			expanded = BX.hasClass(scope, 'is-expand');

		if (!BX.type.isArray(sku) || sku.length < 1)
			return false;
		for (i = 0; i < sku.length; i++)
		{
			item = BX(this.tableId+'_sku-' + sku[i]);
			if (BX.type.isElementNode(item))
			{
				if (!expanded)
					BX.show(item.parentNode.parentNode);
				else
					BX.hide(item.parentNode.parentNode);
			}
			item = null;
		}

		BX.toggleClass(scope, 'is-expand');
		return false;
	};

	ProductSearchDialog.prototype.onSubmitForm = function () {
		window[this.tableId].GetAdminList(this.buildUrl());
		return false;
	};

	ProductSearchDialog.prototype.onSearch = function (s, time) {
		var queryInput = BX(this.tableId+'_query_value'),
			oldValue = queryInput.value;
		if (oldValue === s)
			return false;
		queryInput.value = s;

		var me = this;
		if (this.searchTimer != null) clearTimeout(this.searchTimer);
		this.searchTimer = setTimeout(function () {
			if (s.length === 0 || s.length > 2) {
				me.onSubmitForm();
			}
			BX(me.tableId + '_query_clear').style.display = s.length === 0 ? 'none' : 'inline';
			BX(me.tableId + '_query_clear_separator').style.display = s.length === 0 ? 'none' : 'inline-block';

			me.searchTimer = null;
		}, time || 300);
		return true;
	};

	ProductSearchDialog.prototype.clearQuery = function () {
		var el = BX(this.tableId + '_query'), old = el.value;
		el.value = '';
		if (old.length > 2)
			this.onSearch('', 10);
		return false;
	};

	ProductSearchDialog.prototype.checkSubstring = function()
	{
		var el = BX(this.tableId + '_query_substring'),
			input = BX(this.tableId + '_query_substring_value');
		if (BX.type.isElementNode(el) && BX.type.isElementNode(input))
		{
			input.value = (el.checked ? 'Y' : 'N');
			return true;
		}
		return false;
	};

	ProductSearchDialog.prototype.search = function()
	{
		var queryInput = BX(this.tableId + '_query_value'),
			query = BX(this.tableId + '_query');
		if (BX.type.isElementNode(queryInput) && BX.type.isElementNode(query))
		{
			queryInput.value = query.value;
			this.onSubmitForm();
		}
	};

	ProductSearchDialog.prototype.onIblockChange = function (id, iblockName) {
		var me = this,
			url = this.buildUrl({action: 'change_iblock', IBLOCK_ID: id, SECTION_ID: 0});
		if (iblockName)
			me.iblockName = iblockName;
		BX.ajax.get(
			url,
			null,
			BX.proxy(function (result) {
				BX(me.tableId + '_reload_container').innerHTML = result;
			}, this)
		);
		BX(this.tableId+'_section_label').style.display='none';
		return false;
	};

	ProductSearchDialog.prototype.onSectionClick = function (sectionId, labelText) {
		BX(this.tableId+'_section_id').value = sectionId;

		this.onSubmitForm();

		var openEl = BX.findChildren(
			BX(this.tableId+'_catalog_tree_wrap'),
			{className: 'adm-submenu-item-active'},
			true
		);
		BX.removeClass(openEl[0], 'adm-submenu-item-active');
		var sectionEl = BX(this.tableId + '_section_' + sectionId);
		if (sectionEl)
		{
			BX.addClass(sectionEl.parentNode, 'adm-submenu-item-active');
			if (!BX.hasClass(sectionEl.parentNode, 'adm-sub-submenu-open')) {
				var arrow = BX.findChild(sectionEl, {className: 'adm-submenu-item-arrow'});
				if (arrow)
					arrow.click()
			}
		}

		if (!labelText && sectionEl)
		{
			var openEltext = BX.findChild(
				BX(this.tableId + '_section_' + sectionId).parentNode,
				{className: 'adm-submenu-item-name-link-text'},
				true
			);
			labelText = openEltext.innerHTML;
		}

		var labelEl = BX(this.tableId + '_section_label');
		labelEl.innerHTML = sectionId != '0' ? labelText + ' <span class="adm-s-search-tag-del" onclick="return '+this.tableId+'_helper.onSectionClick(0)"></span>' : '';
		labelEl.style.display = sectionId != '0' ? 'inline-block' : 'none';

		return false;
	};

	ProductSearchDialog.prototype.toggleSection = function (cell) {
		var res = !BX.hasClass(cell, 'adm-sub-submenu-open');
		BX[res? 'addClass':'removeClass'](cell, 'adm-sub-submenu-open');
		return res;
	};

	ProductSearchDialog.prototype.toggleDynSection = function (padding, cell, sectionId, level, active_id) {

		var target = BX.findChild(cell, {className: 'adm-sub-submenu-block-children'});

		if (target.hasChildNodes()) {
			this.toggleSection(cell, sectionId, level);
			return;
		}

		var bAjaxLoaded = false;

		var img = BX.create('SPAN', {
			props: {className: 'adm-submenu-loading adm-sub-submenu-block'},
			style: {marginLeft: parseInt(padding) + 'px'},
			text: BX.message('JS_CORE_LOADING')
		});

		setTimeout(BX.proxy(function () {
			if (!bAjaxLoaded) {
				target.appendChild(img);
				this.toggleSection(cell, sectionId, level);
			}
		}, this), 200);
		var me = this,
			url = this.currentUri+'?action=open_section&lang='+BX.message('LANGUAGE_ID')
				+'&section_id='+sectionId+'&IBLOCK_ID='+me.getIblockId()+'&caller='+this.callerName+'&level='+level+'&active_id='+(active_id? active_id : 0)
		BX.ajax.get(
			url,
			BX.proxy(function (result) {
				bAjaxLoaded = true;
				result = BX.util.trim(result);

				if (result != '') {
					var toggleExecuted = img.parentNode ? true : false;
					target.innerHTML = result;
					if (!toggleExecuted)
						this.toggleSection(cell, sectionId, level);
				}
				else {
					img.innerHTML = BX.message('JS_CORE_NO_DATA');
					if (!img.parentNode) {
						target.appendChild(img);
						this.toggleSection(cell, sectionId, level);
					}
				}
			}, this)
		);

	};

	ProductSearchDialog.prototype.openBranchByPath = function (arPath) {
		var activeId =arPath.pop();
		for (var i=0; i<arPath.length;++i)
		{
			var sectionEl = BX(this.tableId+'_section_'+arPath[i]);
			if (sectionEl)
			{

				var level = parseInt(sectionEl.getAttribute('data-level')) + 1,
					offset = sectionEl.getAttribute('data-offset'),
					targetEl = sectionEl.parentNode;
				if (BX.hasClass(targetEl, 'adm-sub-submenu-open'))
					continue;

				var childrenTargetEl = BX.findChild(targetEl, {className: 'adm-sub-submenu-block-children'});
				if (childrenTargetEl.hasChildNodes()) {
					this.toggleSection(targetEl, arPath[i], level);
				}
				else
				{
					this.toggleDynSection(offset, targetEl, arPath[i], level, activeId);
					break;
				}
			}
		}
	};

	ProductSearchDialog.prototype.setBreadcrumbs = function (data) {
		var title = this.iblockName,
			arHtml = ['<a class="adm-navchain-item adm-navchain-item-desktop" href="#" onclick="return '+this.tableId+'_helper.onSectionClick(0)">'+BX.util.htmlspecialchars(title)+'</a>'],
			arPath = [],
			breadcrumbs = BX(this.tableId+'_breadcrumbs');
		for(var i in data)
		{
			if (data.hasOwnProperty(i))
			{
				arPath.push(data[i].ID);
				arHtml.push('<a class="adm-navchain-item adm-navchain-item-desktop" href="#" onclick="return '+this.tableId+'_helper.onSectionClick('+data[i].ID+')">'+BX.util.htmlspecialchars(data[i].NAME)+'</a>');
				title = data[i].NAME;
			}
		}
		if (this.popup !== null)
		{
			this.popup.SetTitle(BX.util.htmlspecialcharsback(title));
		}
		if (BX.type.isDomNode(breadcrumbs))
		{
			breadcrumbs.innerHTML = arHtml.join('<span class="adm-navchain-delimiter"></span>');
		}
		this.openBranchByPath(arPath);
	};

	ProductSearchDialog.prototype.setIgnoreFilter = function (flag) {
		this.ignoreFilter = !!flag;
	};

	ProductSearchDialog.prototype.buildUrl = function (appendParameters) {
		let params = BX.ajax.prepareForm(this.getForm()),
			url = [],
			k,
			changeIblock = false;
		if (BX.type.isPlainObject(appendParameters))
		{
			changeIblock = (
				BX.type.isNotEmptyString(appendParameters.action)
				&& appendParameters.action === 'change_iblock'
			);
		}
		for (k in params.data) {
			if (params.data.hasOwnProperty(k) && params.data[k])
			{
				if (this.ignoreFilter && k.indexOf('filter_') === 0)
					continue;
				if (changeIblock && k === 'mode')
				{
					continue;
				}
				url.push(encodeURIComponent(k) + '=' + encodeURIComponent(params.data[k]));
			}
		}
		if (BX.type.isPlainObject(appendParameters))
		{
			for (k in appendParameters) {
				if (appendParameters.hasOwnProperty(k) && appendParameters[k])
				{
					url.push(encodeURIComponent(k) + '=' + encodeURIComponent(appendParameters[k]));
				}
			}
		}

		return this.currentUri + '?' + url.join('&', url);
	};

	return ProductSearchDialog;
})();