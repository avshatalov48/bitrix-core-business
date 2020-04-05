;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Form");

	BX.Landing.UI.Form.DynamicBlockForm = function(data)
	{
		BX.Landing.UI.Form.BaseForm.apply(this, arguments);
		this.type = data.type;
		this.forms = data.forms;
		this.code = data.code;
		this.onSourceChangeHandler = data.onSourceChange;
		this.dynamicParams = data.dynamicParams;

		this.settingFieldsSelectors = [
			"source"
		];

		this.addField(this.createSourceField());
	};

	BX.Landing.UI.Form.DynamicBlockForm.prototype = {
		constructor: BX.Landing.UI.Form.DynamicBlockForm,
		__proto__: BX.Landing.UI.Form.BaseForm.prototype,

		getSources: function()
		{
			return BX.Landing.Main.getInstance().options.sources;
		},

		getSourceById: function(id)
		{
			return this.getSources().find(function(source) {
				return String(source.id) === String(id);
			});
		},

		getSourcesFieldItems: function()
		{
			return this.getSources().map(function(source) {
				return {name: source.name, value: source.id};
			});
		},

		createSourceField: function()
		{
			var value = "";

			if (
				BX.type.isPlainObject(this.dynamicParams)
				&& BX.type.isPlainObject(this.dynamicParams.wrapper)
				&& BX.type.isPlainObject(this.dynamicParams.wrapper.settings)
				&& BX.type.isString(this.dynamicParams.wrapper.settings.source)
			)
			{
				value = this.dynamicParams.wrapper.settings.source;
			}

			var source = this.getSourceById(value);

			if (!source)
			{
				source = this.getSources()[0];
			}

			setTimeout(function() {
				this.onSourceChangeHandler.apply(this, [source]);
			}.bind(this), 0);

			return new BX.Landing.UI.Field.Dropdown({
				title: BX.Landing.Loc.getMessage('LANDING_CARDS__SOURCE_FIELD_TITLE'),
				selector: "source",
				content: value,
				items: this.getSourcesFieldItems(),
				onValueChange: function(field) {
					this.onSourceChangeHandler.apply(this, [this.getSourceById(field.getValue())]);
				}.bind(this)
			});
		},

		isReference: function(value)
		{
			var sources = this.getSources();

			if (BX.type.isArray(sources))
			{
				return sources.some(function(source) {
					if (BX.type.isArray(source.references))
					{
						return source.references.some(function(reference) {
							return reference.id === value;
						});
					}

					return false;
				});
			}

			return false;
		},

		serialize: function()
		{
			return this.fields.reduce(function(acc, field) {
				var value = field.getValue();

				if (this.settingFieldsSelectors.includes(field.selector))
				{
					if (field.selector === 'source')
					{
						acc.source = value;
					}

					acc.settings[field.selector] = value;
				}
				else if (
					value === '@hide'
					|| (BX.type.isPlainObject(value) && value.id === '@hide')
				)
				{
					acc.references[field.selector] = '@hide';

					if (BX.hasClass(field.layout, 'landing-ui-field-dynamic-dropdown'))
					{
						acc.stubs[field.selector] = '';
					}
					else if (BX.hasClass(field.layout, 'landing-ui-field-dynamic-image'))
					{
						acc.stubs[field.selector] = {
							id: -1,
							src: 'data:image/gif;base64,R0lGODlhAQABAIAAAP',
							alt: ''
						};
					}
				}
				else
				{
					if (
						this.isReference(value)
						|| (
							BX.type.isPlainObject(value)
							&& BX.type.isString(value.id)
						)
					)
					{
						if (this.isReference(value))
						{
							acc.references[field.selector] = {id: value};
						}
						else
						{
							acc.references[field.selector] = value;
						}

					}
					else
					{
						acc.stubs[field.selector] = value;
					}
				}

				return acc;
			}.bind(this), {settings: {}, references: {}, stubs: {}});
		}
	};
})();