;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Field");

	var bind = BX.Landing.Utils.bind;
	var fireCustomEvent = BX.Landing.Utils.fireCustomEvent;
	var getQueryParam = BX.Landing.Utils.getQueryParams;
	var remove = BX.Landing.Utils.remove;
	var create = BX.Landing.Utils.create;

	/**
	 * Implements interface for works with text field
	 *
	 * @extends {BX.Landing.UI.Field.BaseField}
	 *
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Field.EmbedBg = function(data)
	{
		data.description = "<span class='landing-ui-anchor-preview'>"+BX.Landing.Loc.getMessage('LANDING_EMBED_BG_FIELD_DESCRIPTION')+"</span>";
		BX.Landing.UI.Field.Embed.apply(this, arguments);

		BX.Dom.addClass(this.error, 'landing-ui-error');
	};


	BX.Landing.UI.Field.EmbedBg.prototype = {
		constructor: BX.Landing.UI.Field.EmbedBg,
		__proto__: BX.Landing.UI.Field.Embed.prototype,

		isEmbedUrl: function(value)
		{
			console.log("isEmbedUrl bg");
			return BX.Landing.Utils.Matchers.youtube.test(value)
				|| BX.Landing.Utils.Matchers.vk.test(value)
			;
		},
	}
})();