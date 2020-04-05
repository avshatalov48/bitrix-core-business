(function (window)
{

	BX.namespace('BX.Sender.Template');
	if (BX.Sender.Template.Selector)
	{
		return;
	}

	var Helper = BX.Sender.Helper;

	/**
	 * Selector.
	 *
	 */
	function Selector()
	{
	}
	Selector.prototype.attributes = {
		'button': 'data-bx-sender-tmpl-type-btn',
		'close': 'data-bx-sender-tmpl-close',
		'item': 'data-bx-sender-tmpl-item',
		'items': 'data-bx-sender-tmpl-type-items'
	};
	Selector.prototype.events = {
		'selectorClose': 'close',
		'categorySelect': 'category-select',
		'templateSelect': 'template-select',
		'templatePreview': 'template-preview'
	};
	Selector.prototype.init = function (params)
	{
		if (!params.containerId)
		{
			return;
		}

		this.container = BX(params.containerId);
		if (!this.container)
		{
			return;
		}

		this.mess = params.mess;
		this.actionUri = params.actionUri;

		this.ajaxAction = new BX.AjaxAction(this.actionUri);
		this.previewer = new Previewer({'selector': this});

		this.initGrid(params.grid);
		this.initControls();
	};
	Selector.prototype.onGridSelect = function (item) // second parameter button
	{
		this.fireEvent(
			this.events.templateSelect,
			[{
				type: item.data.templateType,
				code: item.data.templateId,
				name: item.name,
				messageFields: item.data.messageFields,
				segments: item.data.segments,
				dispatch: item.data.dispatch
			}]);
	};
	Selector.prototype.initGrid = function (grid)
	{
		this.grid = new BX.Sender.TileGrid({
			templates: {
				'row': Helper.getNode('tpl-row', this.container),
				'item': Helper.getNode('tpl-item', this.container),
				'button': Helper.getNode('tpl-button', this.container)
			},
			/*
			buttons: [
				{
					'name': this.mess.dlgBtnSelect,
					'handler': this.onGridSelect.bind(this)
				},
				{
					'name': this.mess.dlgBtnDemo,
					'handler': BX.proxy(function (item)
					{
						var ids = item.id.split('|');
						this.fireEvent(this.events.templatePreview, [{type: ids[0], code: ids[1], name: item.name}]);
					}, this)
				}
			],
			*/
			rows: grid.rows,
			items: grid.items,
			type: grid.type,
			mess: this.mess,
			container: Helper.getNode('draw-place', this.container)
		});

		BX.addCustomEvent(this.grid, this.grid.events.itemClick, this.onGridSelect.bind(this));

		this.grid.draw();
	};

	Selector.prototype.initControls = function ()
	{
		this.closeNode = this.container.querySelector('[' + this.attributes.close + ']');
		BX.bind(this.closeNode, 'click', this.close.bind(this));

		BX.addCustomEvent(this, this.events.templatePreview, this.previewTemplate.bind(this));
	};
	Selector.prototype.fireEvent = function (name, params)
	{
		BX.onCustomEvent(this, name, params);
		BX.onCustomEvent(window, 'sender-template-selector-' + name, params);
	};
	Selector.prototype.close = function ()
	{
		this.fireEvent(this.events.selectorClose, [this]);
	};
	Selector.prototype.getTemplateRequestingUri = function (template)
	{
		return this.ajaxAction.getRequestingUri('getTemplate', {
			'lang': template.lang,
			'template_type': template.type,
			'template_id': template.code
		});
	};
	Selector.prototype.getTemplate = function (template, handler)
	{
		this.ajaxAction.requestHtml({
			action: 'getTemplate',
			onsuccess: handler,
			data: {
				'lang': template.lang,
				'template_type': template.type,
				'template_id': template.code
			}
		});
	};
	Selector.prototype.previewTemplate = function (template)
	{
		//BX.util.popup(url, 800, 800);
		this.previewer.load({
			'uri': this.getTemplateRequestingUri(template),
			'title': template.name,
			'template': template
		});
	};

	/**
	 * Previewer.
	 *
	 * @param params Parameters.
	 */
	function Previewer(params)
	{
		this.selector = params.selector;

		this.popup = null;
		this.iframe = null;
		this.currentTemplate = null;
	}
	Previewer.prototype.getPopup = function ()
	{
		if (this.popup)
		{
			return this.popup;
		}

		var popup = BX.PopupWindowManager.create(
			'sender-template-selector-preview',
			null,
			{
				titleBar: this.selector.mess.dlgPreviewTitle.replace('%name%', ''),
				autoHide: true,
				lightShadow: true,
				closeIcon: true,
				closeByEsc: true,
				contentNoPaddings: true,
				overlay: {backgroundColor: 'black', opacity: 500}
			}
		);
		popup.setButtons([
			new BX.PopupWindowButton({
				text: this.selector.mess.dlgBtnSelect,
				className: "popup-window-button-accept",
				events: {click: this.onSelect.bind(this)}
			}),
			new BX.PopupWindowButton({
				text: this.selector.mess.dlgBtnCancel,
				events: {click: this.onCancel.bind(this)}
			})
		]);

		var textTemplate = BX('sender-template-selector-preview');
		popup.setContent(textTemplate.innerHTML);

		this.popup = popup;
		return this.popup;
	};
	Previewer.prototype.getFrame = function ()
	{
		if (this.iframe)
		{
			return this.iframe;
		}

		this.iframe = this.getPopup().contentContainer.querySelector('iframe');
		BX.bind(this.iframe, 'load', this.onFrameLoad.bind(this));

		return this.iframe;
	};
	Previewer.prototype.onFrameLoad = function ()
	{
		this.getPopup().show();
	};
	Previewer.prototype.onSelect = function ()
	{
		this.getPopup().close();
		this.selector.fireEvent(this.selector.events.templateSelect, [this.currentTemplate]);
	};
	Previewer.prototype.onCancel = function ()
	{
		this.getPopup().close();
	};
	Previewer.prototype.load = function (params)
	{
		this.getPopup().setTitleBar(this.selector.mess.dlgPreviewTitle.replace('%name%', params.title));
		this.getFrame().src = params.uri;
		this.currentTemplate = params.template;

		this.getPopup().show();
	};


	BX.Sender.Template.Selector = new Selector();

})(window);