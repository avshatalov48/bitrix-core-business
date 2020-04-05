;(function () {

'use strict';

BX.namespace('BX.UI');

BX.UI.ActionPanel.Item = function(options)
{
	this.id = options.id;
	this.text = options.text;
	this.icon = options.icon;
	this.onclick = options.onclick;
	this.href = options.href;
	this.items = options.items;
	this.actionPanel = options.actionPanel;
	this.options = options;
	this.layout = {
		container: null,
		icon: null,
		text: null
	};
};

BX.UI.ActionPanel.Item.prototype =
{
	render: function()
	{
		var selectorType;

		this.href ? selectorType = "a" : selectorType = "div";

		this.layout.container = BX.create(selectorType, {
			attrs: {
				className: "ui-action-panel-item"
			},
			children: [
				this.icon ? '<span class="ui-action-panel-item-icon"><img src="' + this.icon + '" title=" "></span>' : null,
				this.text ? '<span class="ui-action-panel-item-title">' + this.text + '</span>' : null
			],
			events: {
				click: this.handleClick.bind(this)
			}
		});

		this.href ? this.layout.container.setAttribute('href', this.href) : null;
		this.href ? this.layout.container.setAttribute('title', this.text) : null;

		BX.bind(window, "resize", BX.throttle(function()
		{
			if(this.layout.container.offsetTop > 8)
			{
				this.actionPanel.addHiddenItem(this);
				!this.actionPanel.layout.more ? this.actionPanel.appendMoreBlock() : null;

				return
			}

			if(this.layout.container.offsetTop <= 8)
			{
				this.actionPanel.removeHiddenItem(this);
				this.actionPanel.layout.more ? this.actionPanel.removeMoreBlock() : null;
			}

		}.bind(this), 20));

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

	handleClick: function (event)
	{
		if (this.items)
		{
			this.openSubMenu();
		}
		else
		{
			if(BX.type.isString(this.onclick))
			{
				eval(this.onclick);
			}
			else if (BX.type.isFunction(this.onclick))
			{
				this.onclick.call(this, event, this);
			}
		}
	},

	openSubMenu: function()
	{
		if(!this.items)
		{
			return;
		}

		var bindElement = this.layout.container;
		var popupMenu = BX.PopupMenu.create("ui-action-panel-item-popup-menu", bindElement, this.items, {
			className: "ui-action-panel-item-popup-menu",
			angle: true,
			offsetLeft: bindElement.offsetWidth / 2,
			closeByEsc: true,
			events: {
				onPopupClose: function() {
					popupMenu.destroy();
					BX.removeClass(bindElement, "ui-action-panel-item-active");
				}
			}
		});

		popupMenu.layout.menuContainer.setAttribute("data-tile-grid", "tile-grid-stop-close");
		popupMenu.show();
		BX.addClass(this.layout.container, "ui-action-panel-item-active");
	}
}

})();
