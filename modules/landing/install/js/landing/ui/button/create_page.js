;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");


	/**
	 * @todo Refactoring
	 *
	 * Implements interface for works with create link button
 	 *
	 * @extends {BX.Landing.UI.Button.EditorAction}
	 *
	 * @inheritDoc
	 * @constructor
	 */
	BX.Landing.UI.Button.CreatePage = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.editPanel = null;
	};


	BX.Landing.UI.Button.CreatePage.prototype = {
		constructor: BX.Landing.UI.Button.CreatePage,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			var range = document.getSelection().getRangeAt(0);
			var title = range.toString();

			void BX.Landing.UI.Panel.CreatePage.getInstance().show({
				title: title
			});
		}
	};
})();