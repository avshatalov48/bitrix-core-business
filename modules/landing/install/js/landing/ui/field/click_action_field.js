;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	BX.Landing.UI.Field.ClickAction = function(options)
	{
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		BX.addClass(this.layout, "landing-ui-field-click-action");

		BX.remove(this.input);
		this.reference = options.reference;
		this.linkField = options.linkField;

		this.textField = new BX.Landing.UI.Field.Text({
			title: BX.Landing.Loc.getMessage('LANDING_BLOCK__DYNAMIC_LINK_TEXT_FIELD_LABEL'),
			textOnly: true,
			content: options.content || ''
		});

		this.actionField = new BX.Landing.UI.Field.Dropdown({
			title: BX.Landing.Loc.getMessage('LANDING_BLOCK__DYNAMIC_LINK_ACTION_FIElD_LABEL'),
			items: this.getActions(),
			onValueChange: this.onActionChange.bind(this)
		});

		this.grid = this.createGrid();
		this.textContainer = this.grid.children[0];
		this.actionContainer = this.grid.children[1];
		this.additionalContainer = this.grid.children[2];
		this.layout.appendChild(this.grid);

		this.textContainer.appendChild(this.textField.layout);
		this.actionContainer.appendChild(this.actionField.layout);

		if (BX.type.isPlainObject(options.value))
		{
			this.setValue(options.value);
		}

		this.onActionChange();
	};

	BX.Landing.UI.Field.ClickAction.prototype = {
		constructor: BX.Landing.UI.Field.ClickAction,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		createGrid: function()
		{
			return BX.create({
				tag: "div",
				props: {className: "landing-ui-field-click-action-grid"},
				children: [
					BX.create({
						tag: "div",
						props: {className: "landing-ui-field-click-action-grid-text"}
					}),
					BX.create({
						tag: "div",
						props: {className: "landing-ui-field-click-action-grid-action"}
					}),
					BX.create({
						tag: "div",
						props: {className: "landing-ui-field-click-action-layout-additional"}
					})
				]
			})
		},

		getActions: function()
		{
			return this.reference.actions.map(function(item) {
				return {name: item.name, value: item.type};
			});
		},

		onActionChange: function()
		{
			var actionType = this.actionField.getValue();

			if (actionType === "link")
			{
				this.additionalContainer.appendChild(this.linkField.layout);
				return;
			}

			this.additionalContainer.innerHTML = "";
		},

		getValue: function()
		{
			var linkValue = this.linkField.getValue();

			if (BX.type.isPlainObject(linkValue))
			{
				delete linkValue.text;
			}

			return {
				id: this.reference.id,
				text: this.textField.getValue(),
				action: this.actionField.getValue(),
				link: linkValue
			};
		},

		setValue: function(value)
		{
			if (BX.type.isPlainObject(value))
			{
				this.textField.setValue(value.text);
				this.actionField.setValue(value.action);

				if (value.link)
				{
					// @todo refactoring
					this.linkField.setValue(value.link, true);
					this.linkField.hrefInput.makeDisplayedHrefValue();
				}

				this.onActionChange();
			}
		}
	};
})();