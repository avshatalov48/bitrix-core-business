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
	BX.Landing.UI.Button.CreateLink = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.editPanel = null;
	};


	BX.Landing.UI.Button.CreateLink.prototype = {
		constructor: BX.Landing.UI.Button.CreateLink,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();

			this.textNode = BX.Landing.Block.Node.Text.currentNode;
			this.textField = BX.Landing.UI.Field.BaseField.currentField;

			if (!!this.textField && this.textField.isEditable())
			{
				this.textNode = this.textField;
			}

			BX.Landing.UI.Panel.Link.getInstance().show(this.textNode);
		}
	};
})();