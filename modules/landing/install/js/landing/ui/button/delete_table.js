;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");

	BX.Landing.UI.Button.DeleteTable = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.editPanel = null;
		this.options = options;
	};

	BX.Landing.UI.Button.DeleteTable.prototype = {
		constructor: BX.Landing.UI.Button.DeleteTable,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();
			this.options.table.parentElement.remove();
			BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
			BX.Landing.Block.Node.Text.currentNode.onChange(true);
		}
	};
})();