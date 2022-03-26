;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");

	BX.Landing.UI.Button.PasteTable = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.editPanel = null;
		this.options = options;
	};

	BX.Landing.UI.Button.PasteTable.prototype = {
		constructor: BX.Landing.UI.Button.PasteTable,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();
			if (window.copiedTable)
			{
				var newTable = document.createElement('div');
				newTable.appendChild(window.copiedTable.cloneNode(true));
				newTable.querySelector('.landing-table-container').classList.add('landing-table-pasted');
				document.execCommand('insertHTML', null, newTable.innerHTML);
				var pastedTable = document.querySelector('.landing-table-pasted');
				pastedTable.innerHTML = '';
				pastedTable.appendChild(window.copiedTable.querySelector('.landing-table').cloneNode(true));
				pastedTable.classList.remove('landing-table-pasted');
			}
		}
	};
})();