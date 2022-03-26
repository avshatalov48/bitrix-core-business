;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");

	BX.Landing.UI.Button.AlignTable = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.editPanel = null;
		this.options = options;
		this.id = id;
	};

	BX.Landing.UI.Button.AlignTable.prototype = {
		constructor: BX.Landing.UI.Button.AlignTable,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();
			var id = this.id;
			var activeClass = 'landing-ui-active';
			this.options.alignButtons.forEach(function(alignButton) {
				if (alignButton.id === id)
				{
					alignButton.layout.classList.add(activeClass);
				}
				else
				{
					alignButton.layout.classList.remove(activeClass);
				}
			})
			if (this.options.table)
			{
				var addedClass = null;
				var setAlignClasses = ["text-left", "text-center", "text-right", "text-justify"];
				switch (this.id) {
					case 'alignLeft':
						addedClass = 'text-left';
						break;
					case 'alignCenter':
						addedClass = 'text-center';
						break;
					case 'alignRight':
						addedClass = 'text-right';
						break;
					case 'alignJustify':
						addedClass = 'text-justify';
						break;
				}
				this.options.setTd.forEach(function(td) {
					if (td.nodeType === 1)
					{
						setAlignClasses.forEach(function(alignClass) {
							if (alignClass === addedClass)
							{
								td.classList.add(alignClass);
							}
							else
							{
								td.classList.remove(alignClass);
							}
						})
					}
				})
			}
			BX.Landing.Block.Node.Text.currentNode.onChange(true);
		}
	};
})();