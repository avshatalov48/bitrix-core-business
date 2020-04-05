;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var addClass = BX.Landing.Utils.addClass;
	var clone = BX.Landing.Utils.clone;
	var append = BX.Landing.Utils.append;
	var create = BX.Landing.Utils.create;

	var baseItems = [
		{name: "", value: "top-left"},
		{name: "", value: "top-center"},
		{name: "", value: "top-right"},
		{name: "", value: "middle-left"},
		{name: "", value: "middle-center"},
		{name: "", value: "middle-right"},
		{name: "", value: "bottom-left"},
		{name: "", value: "bottom-center"},
		{name: "", value: "bottom-right"}
	];

	/**
	 * @extends {BX.Landing.UI.Field.ButtonGroup}
	 * @param data
	 * @constructor
	 */
	BX.Landing.UI.Field.Position = function(data)
	{
		this.dataItems = data.items ? clone(data.items) : {};
		this.mode = data.mode ? data.mode : "ball";

		if (Object.keys(this.dataItems).length)
		{
			data.items = baseItems.map(function(item) {
				if (item.value in this.dataItems)
				{
					if ("content" in this.dataItems[item.value])
					{
						item.name = this.dataItems[item.value].content;
					}

					if ("value" in this.dataItems[item.value])
					{
						item.value = this.dataItems[item.value].value;
					}
				}

				return item;
			}, this);
		}
		else
		{
			data.items = baseItems;
		}

		BX.Landing.UI.Field.ButtonGroup.apply(this, arguments);
		addClass(this.layout, "landing-ui-field-position");
		addClass(this.layout, "landing-ui-field-position-mode-" + this.mode);

		this.wrapper = create("div", {
			props: {className: "landing-ui-field-position-wrapper"}
		});

		this.container = create("div", {
			props: {className: "landing-ui-field-position-container"},
			children: this.buttons.map(function(button) {
				return button.layout
			})
		});


		append(this.container, this.wrapper);

		var stripes = create("div", {
			props: {className: "landing-ui-field-position-stripes"},
			children: [
				create("span", {props: {className: "landing-ui-field-position-stripes-item"}}),
				create("span", {props: {className: "landing-ui-field-position-stripes-item"}}),
				create("span", {props: {className: "landing-ui-field-position-stripes-item"}}),
				create("span", {props: {className: "landing-ui-field-position-stripes-item"}}),
				create("span", {props: {className: "landing-ui-field-position-stripes-item"}})
			]
		});

		append(stripes, this.wrapper);
		append(this.wrapper, this.input);

		this.setValue(data.value, true);
		this.value = this.getValue();
	};


	BX.Landing.UI.Field.Position.prototype = {
		constructor: BX.Landing.UI.Field.Position,
		__proto__: BX.Landing.UI.Field.ButtonGroup.prototype
	}
})();