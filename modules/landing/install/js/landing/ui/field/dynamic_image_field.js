;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	BX.Landing.UI.Field.DynamicImage = function(options)
	{
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		BX.addClass(this.layout, "landing-ui-field-dynamic-image");

		BX.remove(this.input);
		this.reference = options.reference;
		this.linkField = options.linkField;

		this.dropdownField = new BX.Landing.UI.Field.Dropdown({
			title: options.title,
			items: options.dropdownItems,
			content: BX.type.isPlainObject(options.value) ? options.value.id : undefined
		});

		this.checkboxField = new BX.Landing.UI.Field.Checkbox({
			title: BX.Landing.Loc.getMessage('LANDING_BLOCK__DYNAMIC_LINK_ACTION_FIElD_LABEL'),
			items: [
				{
					name: BX.Landing.Loc.getMessage('LANDING_BLOCK__DYNAMIC_IMAGE_MAKE_LINK_TO_DETAIL'),
					value: true,
					checked: (function() {
						if (BX.type.isPlainObject(options.value))
						{
							return [true, "true"].includes(options.value.link);
						}

						return false;
					})()
				}
			],
			multiple: false
		});

		BX.remove(this.dropdownField.header);
		BX.remove(this.checkboxField.header);

		this.grid = this.createGrid();
		this.dropdownContainer = this.grid.children[0];
		this.checkboxContainer = this.grid.children[1];
		this.layout.appendChild(this.grid);

		this.dropdownContainer.appendChild(this.dropdownField.layout);

		if (!options.hideCheckbox)
		{
			this.checkboxContainer.appendChild(this.checkboxField.layout);
		}

		if (BX.type.isPlainObject(options.value))
		{
			this.setValue(options.value);
		}
	};

	BX.Landing.UI.Field.DynamicImage.prototype = {
		constructor: BX.Landing.UI.Field.DynamicImage,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		createGrid: function()
		{
			return BX.create({
				tag: "div",
				props: {className: "landing-ui-field-dynamic-image-grid"},
				children: [
					BX.create({
						tag: "div",
						props: {className: "landing-ui-field-dynamic-image-grid-reference"}
					}),
					BX.create({
						tag: "div",
						props: {className: "landing-ui-field-dynamic-image-grid-link"}
					})
				]
			})
		},

		getValue: function()
		{
			return {
				id: this.dropdownField.getValue(),
				link: this.checkboxField.getValue()
			};
		},

		setValue: function(value)
		{
			if (BX.type.isPlainObject(value))
			{
				this.dropdownField.setValue(value.id);
				this.checkboxField.setValue(value.link);
			}
		}
	};
})();