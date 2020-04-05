;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	/**
	 * Implements interface for works with multi select field
	 * @extends {BX.Landing.UI.Field.BaseField}
	 * @param options
	 * @constructor
	 */
	BX.Landing.UI.Field.BlockSource = function(options)
	{
		BX.Landing.UI.Field.BaseField.apply(this, arguments);
		BX.Landing.Utils.addClass(this.layout, "landing-ui-field-block-source");

		this.onSourceChangeHandler = options.onSourceChange;
		this.value = options.value;

		this.sourceField = this.createLinkField();

		this.layout.innerHTML = "";
		BX.Landing.Utils.append(this.sourceField.layout, this.layout);
	};


	BX.Landing.UI.Field.BlockSource.prototype = {
		constructor: BX.Landing.UI.Field.BlockSource,
		__proto__: BX.Landing.UI.Field.BaseField.prototype,

		getSources: function()
		{
			return BX.Landing.Main.getInstance().options.sources;
		},

		createLinkField: function()
		{
			return new BX.Landing.UI.Field.LinkURL({
				selector: "source",
				title: BX.Landing.Loc.getMessage("LANDING_BLOCK__SOURCE_TITLE"),
				textOnly: true,
				disableCustomURL: true,
				disablePages: true,
				disallowType: true,
				allowedTypes: [
					BX.Landing.UI.Field.LinkURL.TYPE_BLOCK
				],
				customPlaceholder: BX.Landing.Loc.getMessage('LANDING_BLOCK__BLOCK_SOURCE_PLACEHOLDER'),
				options: {
					siteId: BX.Landing.Main.getInstance().options.site_id,
					landingId: BX.Landing.Main.getInstance().id,
					filter: {
						"=TYPE": BX.Landing.Main.getInstance().options.params.type
					}
				},
				content: this.value,
				onValueChange: function(value) {
					var currentSource = null;

					if (
						BX.type.isPlainObject(value)
						&& BX.type.isPlainObject(value.source)
					)
					{
						currentSource = Object.keys(value.source)[0];
					}

					var source = this.getSources().find(function(item) {
						return item.id === currentSource;
					});

					setTimeout(function() {
						this.onSourceChangeHandler(source);
					}.bind(this), 0);
				}.bind(this)
			});
		},

		getValue: function()
		{
			return this.sourceField.getValue();
		}
	};
})();