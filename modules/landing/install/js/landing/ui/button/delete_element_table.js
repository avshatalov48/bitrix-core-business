;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");

	BX.Landing.UI.Button.DeleteElementTable = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.editPanel = null;
		this.options = options;
		this.id = id;
	};

	BX.Landing.UI.Button.DeleteElementTable.prototype = {
		constructor: BX.Landing.UI.Button.DeleteElementTable,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();
			if (this.id === 'deleteRow')
			{
				var row = this.options.setTd[0].parentNode;
				row.remove();
			}
			if (this.id === 'deleteCol')
			{
				this.options.setTd.forEach(function(td){
					td.remove();
				})
			}
			BX.Event.EventEmitter.emit('BX.Landing.TableEditor:onDeleteElementTable');
			BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
			BX.Landing.Block.Node.Text.currentNode.onChange(true);
		}
	};
})();