;(function() {
	'use strict';

	BX.namespace('BX.Landing.UI.Button');

	BX.Landing.UI.Button.DeleteTable = function(id, options, textNode)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.editPanel = null;
		this.options = options;
		this.textNode = textNode;
	};

	BX.Landing.UI.Button.DeleteTable.prototype = {
		constructor: BX.Landing.UI.Button.DeleteTable,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick(event)
		{
			event.preventDefault();
			event.stopPropagation();
			this.options.table.parentElement.remove();
			BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
			this.textNode.onChange(true);
		},
	};
})();