;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");

	BX.Landing.UI.Button.CopyTable = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.editPanel = null;
		this.options = options;
	};

	BX.Landing.UI.Button.CopyTable.prototype = {
		constructor: BX.Landing.UI.Button.CopyTable,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();
			var isCopied = false;
			if (this.options)
			{
				BX.Event.EventEmitter.emit('BX.Landing.TableEditor:onCopyTable');
				top.window.copiedTable = this.options.table.parentElement.cloneNode(true);
				if (top.window.copiedTable)
				{
					isCopied = true;
				}
			}
			if (isCopied)
			{
				BX.UI.Notification.Center.notify({
					content: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_TABLE_COPIED')
				});
			}
			else
			{
				BX.UI.Notification.Center.notify({
					content: BX.Landing.Loc.getMessage('LANDING_TITLE_OF_EDITOR_ACTION_TABLE_NOT_COPIED')
				});
			}
		}
	};
})();