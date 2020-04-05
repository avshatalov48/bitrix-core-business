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
					attribute: options.attribute
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
					time: options.time,
					format: options.format
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
					attribute: options.attribute
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
					attribute: options.attribute
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
					uploadParams: this.uploadParams
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
					attribute: options.attribute
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
					options: this.linkOptions
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
					textOnly: true
				});

				return new BX.Landing.UI.Field.LinkURL(options);
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
					type: options.type === "range-slider" ? "multiple" : null
				});
			}

			if (options.type === "palette")
			{
				return new BX.Landing.UI.Field.Color({
					title: options.name,
					selector: this.selector,
					items: options.items,
					content: options.value,
					onChange: this.onChangeHandler,
					onValueChange: this.onValueChangeHandler,
					attribute: options.attribute,
					property: options.property
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
					property: options.property
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
					property: options.property
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
					property: options.property,
					mode: options.mode
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
					property: options.property,
					compact: options.compact
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
					property: options.property,
					compact: options.compact
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
					property: options.property
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
					property: options.property
				});
			}
		}
	};

})();