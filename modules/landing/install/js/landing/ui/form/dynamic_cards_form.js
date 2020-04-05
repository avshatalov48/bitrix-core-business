;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Form");

	BX.Landing.UI.Form.DynamicCardsForm = function(data)
	{
		BX.Landing.UI.Form.BaseForm.apply(this, arguments);
		this.type = data.type;
		this.code = data.code;
		this.presets = data.presets;
		this.sync = data.sync;
		this.forms = data.forms;
		this.id = this.code.replace(".", "") + "-" + BX.Landing.Utils.random();
		this.onSourceChangeHandler = data.onSourceChange;
		this.dynamicParams = data.dynamicParams;
		this.settingFieldsSelectors = [
			"source",
			"pagesCount",
			"detailPage",
			"useSef"
		];

		this.sourceField = this.createSourceField();
		this.pagesField = this.createPagesField();

		this.addField(this.sourceField);
		this.addField(this.pagesField);

		this.detailPageGroup = this.createFieldsGroup([
			this.createLinkField()
		]);

		this.addCard(
			this.detailPageGroup
		);
	};

	BX.Landing.UI.Form.DynamicCardsForm.prototype = {
		constructor: BX.Landing.UI.Form.DynamicCardsForm,
		__proto__: BX.Landing.UI.Form.BaseForm.prototype,

		getSources: function()
		{
			return BX.Landing.Main.getInstance().options.sources;
		},

		getSourceItems: function()
		{
			return this.getSources()
				.map(function(item) {
					return {
						name: item.name,
						value: item.id,
						url: item.url ? item.url.filter : '',
						filter: item.filter,
						sort: {
							items: item.sort.map(function(sortItem) {
								return {name: sortItem.name, value: sortItem.id}
							})
						},
						settings: item.settings
					}
				});
		},

		createSourceField: function()
		{
			var sourceItems = this.getSourceItems();
			var value = {
				source: sourceItems[0].value,
				filter: sourceItems[0].filter,
			};

			if (
				BX.type.isPlainObject(this.dynamicParams)
				&& BX.type.isPlainObject(this.dynamicParams.settings)
				&& BX.type.isPlainObject(this.dynamicParams.settings.source)
			)
			{
				value.source = this.dynamicParams.settings.source.source;
				value.filter = this.dynamicParams.settings.source.filter;
				value.sort = this.dynamicParams.settings.source.sort;
			}

			return new BX.Landing.UI.Field.SourceField({
				selector: "source",
				title: BX.Landing.Loc.getMessage("LANDING_CARDS__SOURCE_FIELD_TITLE"),
				items: sourceItems,
				value: value,
				onValueChange: function(field)
				{
					var value = field.getValue();
					var source = this.getSources().find(function(item) {
						return item.id === value.source;
					});

					setTimeout(function() {
						if (!this.sourceField.isDetailPageAllowed())
						{
							BX.style(this.detailPageGroup.layout, 'display', 'none');
						}
						else
						{
							BX.style(this.detailPageGroup.layout, 'display', null);
						}
						this.onSourceChangeHandler(source);
					}.bind(this), 0);
				}.bind(this)
			});
		},

		createPagesField: function()
		{
			return new BX.Landing.UI.Field.Pages({
				selector: "pagesCount",
				title: BX.Landing.Loc.getMessage("LANDING_CARDS__PAGES_FIELD_TITLE"),
				value: this.dynamicParams.settings.pagesCount
			});
		},

		createLinkField: function()
		{
			var content = {
				text: "",
				href: ""
			};

			if (
				BX.type.isPlainObject(this.dynamicParams)
				&& BX.type.isPlainObject(this.dynamicParams.settings)
				&& BX.type.isPlainObject(this.dynamicParams.settings.detailPage)
			)
			{
				content = this.dynamicParams.settings.detailPage;
			}

			return new BX.Landing.UI.Field.Link({
				selector: "detailPage",
				title: BX.Landing.Loc.getMessage("LANDING_CARDS__DETAIL_PAGE_FIELD_TITLE"),
				textOnly: true,
				disableCustomURL: true,
				disableBlocks: true,
				disallowType: true,
				allowedTypes: [
					BX.Landing.UI.Field.LinkURL.TYPE_PAGE
				],
				detailPageMode: true,
				sourceField: this.fields.find((function(field) {
					return field.selector === 'source';
				})),
				options: {
					siteId: BX.Landing.Main.getInstance().options.site_id,
					landingId: BX.Landing.Main.getInstance().id,
					filter: {
						'=TYPE': BX.Landing.Main.getInstance().options.params.type
					}
				},
				content: content
			});
		},

		createUseSefField: function()
		{
			return new BX.Landing.UI.Field.Checkbox({
				selector: "useSef",
				multiple: false,
				items: [
					{
						name: BX.Landing.Loc.getMessage("LANDING_CARDS__DETAIL_PAGE_USE_SEF"),
						value: true,
						checked: true
					}
				]
			});
		},

		createFieldsGroup: function(items)
		{
			return new BX.Landing.UI.Card.DynamicFieldsGroup({
				items: items
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
			var isDetailPageAllowed = this.sourceField.isDetailPageAllowed();

			return this.fields.reduce(function(acc, field) {
				if (field.selector === 'detailPage' && !isDetailPageAllowed)
				{
					return acc;
				}

				var value = field.getValue();

				if (this.settingFieldsSelectors.includes(field.selector))
				{
					if (field.selector === 'source')
					{
						acc.source = value.source;
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