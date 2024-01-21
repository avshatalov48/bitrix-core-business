(function() {
	'use strict';

	BX.namespace('BX.Landing.UI.Button');

	BX.Landing.UI.Button.AlignTable = function(id, options, textNode)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.editPanel = null;
		this.options = options;
		this.id = id;
		this.textNode = textNode;
	};

	BX.Landing.UI.Button.AlignTable.prototype = {
		constructor: BX.Landing.UI.Button.AlignTable,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick(event)
		{
			event.preventDefault();
			event.stopPropagation();
			const id = this.id;
			const activeClass = 'landing-ui-active';
			this.options.alignButtons.forEach((alignButton) => {
				if (alignButton.id === id)
				{
					alignButton.layout.classList.add(activeClass);
				}
				else
				{
					alignButton.layout.classList.remove(activeClass);
				}
			});
			if (this.options.table)
			{
				let addedClass = null;
				const setAlignClasses = ['text-left', 'text-center', 'text-right', 'text-justify'];
				switch (this.id)
				{
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
				this.options.setTd.forEach((td) => {
					if (td.nodeType === 1)
					{
						setAlignClasses.forEach((alignClass) => {
							if (alignClass === addedClass)
							{
								td.classList.add(alignClass);
							}
							else
							{
								td.classList.remove(alignClass);
							}
						});
					}
				});
			}
			this.textNode.onChange(true);
		},
	};
})();
