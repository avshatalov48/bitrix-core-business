;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");

	var proxy = BX.Landing.Utils.proxy;


	/**
	 * Implements interface for works with change tag name button
	 *
	 * @extends {BX.Landing.UI.Button.EditorAction}
	 *
	 * @param {string} id - Action id
	 * @param {?object} [options]
	 *
	 * @constructor
	 */
	BX.Landing.UI.Button.ChangeTag = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.layout.classList.add("landing-ui-button-editor-change-tag");
		this.onChangeHandler = options.onChange;
		this.value =  options.html;
		this.onChange = proxy(this.onChange, this);
	};

	BX.Landing.UI.Button.ChangeTag.prototype = {
		constructor: BX.Landing.UI.Button.ChangeTag,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			if (!this.menu)
			{
				this.menu = new BX.PopupMenuWindow({
					id: "change-tag-name-menu-" + BX.Text.getRandom(),
					bindElement: this.layout,
					zIndex: -678,
					items: [
						new BX.PopupMenuItem({
							id: "H1",
							text: "H1",
							onclick: this.onChange
						}),
						new BX.PopupMenuItem({
							id: "H2",
							text: "H2",
							onclick: this.onChange
						}),
						new BX.PopupMenuItem({
							id: "H3",
							text: "H3",
							onclick: this.onChange
						}),
						new BX.PopupMenuItem({
							id: "H4",
							text: "H4",
							onclick: this.onChange
						}),
						new BX.PopupMenuItem({
							id: "H5",
							text: "H5",
							onclick: this.onChange
						}),
						new BX.PopupMenuItem({
							id: "H6",
							text: "H6",
							onclick: this.onChange
						})
					]
				});

				this.activateItem(this.value);
			}

			BX.Landing.UI.Button.ChangeTag.menu = this.menu;

			this.menu.popupWindow.adjustPosition({forceTop: true});

			if (this.menu.popupWindow.isShown())
			{
				this.menu.close();
			}
			else
			{
				this.menu.show();
			}

			BX.Landing.UI.Tool.ColorPicker.hideAll();
		},


		activateItem: function(value)
		{
			if (this.menu)
			{
				var currentActive = this.menu.menuItems.find(function(item) {
					return item.layout.text.innerHTML.includes("strong");
				}, this);

				var newActive = this.menu.menuItems.find(function(item) {
					return item.id === value;
				}, this);

				if (currentActive)
				{
					currentActive.layout.text.innerHTML = currentActive.layout.text.innerText;
				}

				if (newActive)
				{
					newActive.layout.text.innerHTML = "<strong>"+newActive.layout.text.innerText+"</strong>";
					this.layout.innerHTML = "<span class=\"landing-ui-icon-editor-"+newActive.id.toLowerCase()+"\"></span>";
				}
			}
		},


		onChange: function(event, menuItem)
		{
			event.stopPropagation();
			this.activateItem(menuItem.id);
			menuItem.menuWindow.close();

			if (this.onChangeHandler)
			{
				this.onChangeHandler(menuItem.id);
			}
		}
	};
})();