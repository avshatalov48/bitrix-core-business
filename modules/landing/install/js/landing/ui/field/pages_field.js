;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	BX.Landing.UI.Field.Pages = function(options)
	{
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		BX.remove(this.input);
		this.layout.classList.add("landing-ui-field-pages");

		this.rangeField = this.createRangeField(Number(options.value));

		this.gridLeft = this.createGridLeft();
		this.gridRight = this.createGridRight();
		this.grid = this.createGrid([this.gridLeft, this.gridRight]);

		this.layout.appendChild(this.grid);
		this.gridLeft.appendChild(this.rangeField.layout);

		// this.addButton = this.createButtonField();
		// this.gridRight.appendChild(this.addButton.layout);
	};

	BX.Landing.UI.Field.Pages.prototype = {
		constructor: BX.Landing.UI.Field.Pages,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		createRangeField: function(value)
		{
			var field = new BX.Landing.UI.Field.Range({
				items: Array.from({length: 100}, function(item, index) {
					return {name: index, value: index}
				}),
				content: BX.type.isNumber(value) ? value : undefined
			});

			BX.remove(field.header);

			return field;
		},

		createGridLeft: function()
		{
			return BX.create("div", {
				props: {
					className: "landing-ui-field-pages-grid-left"
				}
			});
		},

		createGridRight: function()
		{
			return BX.create("div", {
				props: {
					className: "landing-ui-field-pages-grid-right"
				}
			});
		},

		createGrid: function(children)
		{
			return BX.create("div", {
				props: {
					className: "landing-ui-field-pages-grid"
				},
				children: children
			});
		},

		createButtonField: function()
		{
			return new BX.Landing.UI.Button.BaseButton({
				text: BX.Landing.Loc.getMessage("LANDING_CARDS__ADD_NEW_PAGE_BUTTON"),
				className: [
					"landing-ui-button-select-link",
					"landing-ui-field-pages-add-button"
				]
			});
		},

		getValue: function()
		{
			return this.rangeField.getValue();
		}
	};
})();