;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Factory");

	var isFunction = BX.Landing.Utils.isFunction;
	var assign = BX.Landing.Utils.assign;


	BX.Landing.UI.Factory.FieldFactory = function(params)
	{
		this.uploadParams = params.uploadParams || {};
		this.linkOptions = params.linkOptions || {};
		this.selector = params.selector;
		this.onChangeHandler = isFunction(params.onChange) ? params.onChange : (function() {});
		this.onValueChangeHandler = isFunction(params.onValueChange) ? params.onValueChange : (function() {});
	};


	BX.Landing.UI.Factory.FieldFactory.prototype = {
		create: function(options)
		{
			if (options.type === "text")
			{
				return new BX.Landing.UI.Field.Text({
					title: options.name,
					selector: this.selector,
					content: options.value,
					placeholder: options.placeholder,
					description: options.description,
					textOnly: true,
					onInput: options.onInput,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			if (options.type === "date")
			{
				return new BX.Landing.UI.Field.Date({
					title: options.name,
					selector: this.selector,
					content: options.value,
					placeholder: options.placeholder,
					description: options.description,
					textOnly: true,
					onInput: options.onInput,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					time: options.time,
					format: options.format,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			if (options.type === "html")
			{
				return new BX.Landing.UI.Field.Html({
					title: options.name,
					selector: this.selector,
					content: options.value,
					placeholder: options.placeholder,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			if (!options.type || options.type === "list" || options.type === "dropdown")
			{
				return new BX.Landing.UI.Field.Dropdown({
					title: options.name,
					selector: this.selector,
					items: options.items,
					content: options.value,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					disabled: BX.Text.toBoolean(options.disabled),
					dependency: options.dependency,
					hint: options.hint
				});
			}

			if (options.type === "image")
			{
				return new BX.Landing.UI.Field.Image({
					title: options.name,
					selector: this.selector,
					content: options.value,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					uploadParams: this.uploadParams,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			if (options.type === "icon")
			{
				options.value = BX.Landing.Utils.isPlainObject(options.value) ? options.value : {};
				return new BX.Landing.UI.Field.Icon({
					title: options.name,
					selector: this.selector,
					content: {
						type: "icon",
						src: "",
						alt: "",
						classList: "classList" in options.value ? options.value.classList : []
					},
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			if (options.type === "link")
			{
				return new BX.Landing.UI.Field.Link({
					title: options.name,
					selector: this.selector,
					content: options.value,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					options: this.linkOptions,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			if (options.type === "url")
			{
				options = assign({}, options, {
					title: options.name,
					content: options.value,
					selector: this.selector,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					options: this.linkOptions,
					textOnly: true,
					disabled: BX.Text.toBoolean(options.disabled)
				});

				return new BX.Landing.UI.Field.LinkUrl(options);
			}

			if (options.type === "dynamic_source")
			{

				options = assign({}, options, {
					title: options.name,
					content: options.value,
					selector: this.selector,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					options: this.linkOptions,
					textOnly: true,
					currentPageOnly: options.currentPageOnly,
					allowedTypes: [
						'block'
					],
					disableCustomURL: true,
					disallowType: true,
					customPlaceholder: BX.Landing.Loc.getMessage('LANDING_BLOCK__BLOCK_SOURCE_PLACEHOLDER'),
					panelTitle: BX.Landing.Loc.getMessage('LANDING_BLOCK__BLOCK_SOURCE_PLACEHOLDER'),
					disabled: BX.Text.toBoolean(options.disabled)
				});

				return new BX.Landing.UI.Field.LinkUrl(options);
			}

			if (options.type === "slider" || options.type === "range-slider")
			{
				return new BX.Landing.UI.Field.Range({
					title: options.name,
					selector: this.selector,
					items: options.items,
					content: options.value,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					frame: window,
					type: options.type === "range-slider" ? "multiple" : null,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			if (options.type === "palette")
			{
				return new BX.Landing.UI.Field.ColorPalette({
					title: options.name,
					selector: this.selector,
					items: options.items,
					content: options.value,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					property: options.property,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			// todo: need save Backward compatibility for "pallette"?
			if (options.type === "color")
			{
				return new BX.Landing.UI.Field.ColorField({
					title: options.name,
					selector: this.selector,
					subtype: options.subtype,
					// items: options.items,
					content: options.value,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					// property: options.property,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			if (options.type === "catalog-view")
			{
				return new BX.Landing.UI.Field.DragAndDropList({
					title: options.name,
					selector: this.selector,
					items: options.items,
					value: options.value,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					property: options.property,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			if (options.type === "sortable-list")
			{
				return new BX.Landing.UI.Field.SortableList({
					title: options.name,
					selector: this.selector,
					items: options.items,
					value: options.value,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					property: options.property,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			if (options.type === "position")
			{
				return new BX.Landing.UI.Field.Position({
					title: options.name,
					selector: this.selector,
					items: options.items,
					value: options.value,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					property: options.property,
					mode: options.mode,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			if (options.type === "checkbox")
			{
				return new BX.Landing.UI.Field.Checkbox({
					title: options.name,
					selector: this.selector,
					items: options.items,
					value: options.value,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					property: options.property,
					compact: options.compact,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			if (options.type === "radio")
			{
				return new BX.Landing.UI.Field.Radio({
					title: options.name,
					selector: this.selector,
					items: options.items,
					value: options.value,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					property: options.property,
					compact: options.compact,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			if (options.type === "multiselect")
			{
				return new BX.Landing.UI.Field.MultiSelect({
					title: options.name,
					selector: this.selector,
					items: options.items,
					value: options.value,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					property: options.property,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}

			if (options.type === "filter")
			{
				return new BX.Landing.UI.Field.Filter({
					title: options.name,
					selector: this.selector,
					items: options.items,
					value: options.value,
					html: options.html,
					filterId: options.filterId,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					attrKey: options.attrKey,
					property: options.property,
					disabled: BX.Text.toBoolean(options.disabled)
				});
			}
		}
	};

})();