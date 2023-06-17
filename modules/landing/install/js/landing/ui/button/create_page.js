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

			const range = this.contextDocument.getSelection().getRangeAt(0);
			const title = range.toString();

			const createPagePanel = BX.Landing.UI.Panel.CreatePage.getInstance();
			createPagePanel.setContextDocument(this.contextDocument);
			createPagePanel.show({title: title});
		}
	};
})();