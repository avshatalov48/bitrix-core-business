;(function () {

'use strict';

BX.namespace('BX.UI');

BX.UI.ActionPanel.Item = function(options)
{
	this.id = options.id;
	this.type = options.type;
	this.text = options.text;
	this.html = options.text;
	this.icon = options.icon;
	this.title = options.title;
	this.iconOnly = options.iconOnly;
	this.additionalClassForPanel = options.additionalClassForPanel;
	this.submenuOptions = {};
	if (options.submenuOptions && BX.type.isString(options.submenuOptions))
	{
		try
		{
			this.submenuOptions = JSON.parse(options.submenuOptions);
		}
		catch (e)
		{
		}
	}
	this.buttonIconClass = options.buttonIconClass;
	this.onclick = options.onclick;
	this.href = options.href;
	this.items = options.items;
	this.actionPanel = options.actionPanel;
	this.options = options;
	this.attributes = BX.prop.getObject(options, 'attributes');
	this.dataset = options.dataset;
	this.disabled = options.disabled;
	this.layout = {
		container: null,
		icon: null,
		text: null
	};
};

BX.UI.ActionPanel.Item.prototype =
{
	changeIconClass: function(iconClass)
	{
		BX.removeClass(this.layout.container, this.buttonIconClass);
		BX.addClass(this.layout.container, iconClass);

		this.buttonIconClass = iconClass;
	},

	render: function()
	{
		var selectorType;

		this.href ? selectorType = "a" : selectorType = "div";

		var className = "ui-action-panel-item " + (this.additionalClassForPanel ? this.additionalClassForPanel+' ':'') + (this.disabled ? 'ui-action-panel-item-is-disabled' : '');
		if (this.buttonIconClass)
		{
			className = 'ui-btn ui-btn-lg ui-btn-link ' + this.buttonIconClass;
		}

		if (this.onclick && BX.type.isArray(this.items) && this.items.length > 0)
		{
			this.layout.container = BX.create('div', {
				props: {
					className: 'ui-btn-split ui-btn-link' + (this.buttonIconClass || '')
				},
				children: [
					BX.create('button', {
						props: {
							className: 'ui-btn-main',
						},
						events: {
							click: this.handleMainClick.bind(this)
						},
						text: this.text
					}),
					BX.create('button', {
						props: {
							className: 'ui-btn-menu',
						},
						events: {
							click: this.handleMenuClick.bind(this)
						}
					})
				],
				attrs: this.attributes,
				dataset: {
					role: 'action-panel-item'
				}
			});
		}
		else
		{
			this.layout.container = BX.create(selectorType, {
				props: {
					className: className
				},
				children: [
					this.icon ? '<span class="ui-action-panel-item-icon"><img src="' + this.icon + '"></span>' : null,
					(this.text && !this.buttonIconClass && this.iconOnly !== true ) ? '<span class="ui-action-panel-item-title">' + this.text + '</span>' : (this.iconOnly !== true ? this.text : null)
				],
				attrs: this.attributes,
				dataset: {
					role: 'action-panel-item'
				},
				events: {
					click: this.handleClick.bind(this)
				}
			});
		}

		this.href ? this.layout.container.setAttribute('href', this.href) : null;
		(this.href || this.title) ? this.layout.container.setAttribute('title', (this.title ? this.title :this.text)) : null;

		if (BX.type.isString(this.onclick))
		{
			this.layout.container.setAttribute('onclick', this.onclick);
		}

		if (this.dataset)
		{
			BX.adjust(this.layout.container, {'dataset': this.dataset});
		}

		if (this.options.hide)
		{
			this.hide();
		}

		return this.layout.container;
	},

	show: function ()
	{
		BX.show(this.layout.container, 'block');
	},

	showAsInlineBlock: function ()
	{
		BX.style(this.layout.container,'display','inline-block');
	},

	hide: function ()
	{
		BX.hide(this.layout.container, 'none');
	},

	destroy: function ()
	{
		BX.remove(this.layout.container);
	},

	isVisible: function()
	{
		if (this.layout.container.offsetTop > 8)
		{
			return false;
		}

		return true;
	},

	isNotFit: function()
	{
		return this.layout.container.offsetHeight > 0 && !this.isVisible();
	},

	handleMenuClick: function (event)
	{
		if (this.isDisabled())
		{
			event.preventDefault();

			return;
		}

		return this.openSubMenu();
	},

	handleMainClick: function (event)
	{
		if (this.isDisabled())
		{
			event.preventDefault();

			return;
		}

		if (BX.type.isFunction(this.onclick))
		{
			this.onclick.call(this, event, this);
		}
	},

	handleClick: function (event)
	{
		if (this.isDisabled())
		{
			event.preventDefault();

			return;
		}
		if (BX.type.isArray(this.items) && this.items.length > 0)
		{
			this.openSubMenu();
		}
		else
		{
			if (BX.type.isFunction(this.onclick))
			{
				this.onclick.call(this, event, this);
			}
		}
	},

	isDisabled: function()
	{
		return this.disabled;
	},

	disable: function()
	{
		this.disabled = true;
		if (this.layout && this.layout.container)
		{
			BX.data(this.layout.container, 'slider-ignore-autobinding', true);
			this.layout.container.classList.add('ui-action-panel-item-is-disabled');
		}
	},

	enable: function()
	{
		this.disabled = false;
		if (this.layout && this.layout.container)
		{
			BX.data(this.layout.container, 'slider-ignore-autobinding', false);
			this.layout.container.classList.remove('ui-action-panel-item-is-disabled');
		}
	},

	openSubMenu: function()
	{
		if (!BX.type.isArray(this.items) || this.items.length === 0)
		{
			return;
		}

		var bindElement = this.layout.container;
		var popupMenuOptions = {
			className: "ui-action-panel-item-popup-menu",
			angle: true,
			zIndex: this.actionPanel.zIndex? this.actionPanel.zIndex + 1 : null,
			offsetLeft: bindElement.offsetWidth / 2,
			closeByEsc: true,
			events: {
				onPopupClose: function() {
					popupMenu.destroy();
					BX.removeClass(bindElement, "ui-action-panel-item-active");
				}
			}
		};
		popupMenuOptions = BX.mergeEx(popupMenuOptions, this.submenuOptions);
		var popupMenu = BX.PopupMenu.create("ui-action-panel-item-popup-menu", bindElement, this.items, popupMenuOptions);

		popupMenu.layout.menuContainer.setAttribute("data-tile-grid", "tile-grid-stop-close");
		popupMenu.show();
		BX.addClass(this.layout.container, "ui-action-panel-item-active");
	}
}

})();
