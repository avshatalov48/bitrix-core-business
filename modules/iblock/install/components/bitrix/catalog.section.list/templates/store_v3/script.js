(function()
{
	'use strict';

	if (!!window.JCCatalogSectionListStoreComponent)
	{
		return;
	}

	/*
	mode
	offset
	variable
	maxcount
	containerid

	 */
	window.JCCatalogSectionListStoreComponent = function(params)
	{
		this.state = {
			offset: 0,
			itemNumber: 0
		};
		this.settings = {
			offset: 0,
			maxCount: 0
		};

		this.visual = {
			id: ''
		};

		this.offsetMode = params.offsetMode || 'N';
		this.visual.id = params.visual.id;

		if (this.offsetMode === 'F')
		{
			this.settings.offset = params.settings.offset;
		}
		this.settings.maxCount = params.settings.maxCount;

		this.container = null;

		BX.ready(BX.proxy(this.init, this));
	};

	window.JCCatalogSectionListStoreComponent.prototype = {
		init: function()
		{
			if (BX.type.isNotEmptyString(this.visual.id))
			{
				this.container = BX(this.visual.id);

				var ears = new BX.UI.Ears({
					container: this.container.children[0],
					smallSize: true,
					noScrollbar: true,
					className: 'catalog-sections-list-ears'
				});

				ears.init();
			}
			if (this.offsetMode === 'F')
			{
				this.setCurrentOffset(this.settings.offset);
			}
		},

		setCurrentOffset: function(offset)
		{
			this.calculateOffset(offset);
			this.applyOffset();
		},

		calculateOffset: function(offset)
		{
			this.state.offset = offset;
			this.state.itemNumber = Math.floor(this.state.offset / 5);
		},

		applyOffset: function()
		{
			if (this.container !== null)
			{
				var list = this.container.querySelector('[data-items-container="Y"]');
				if (BX.type.isElementNode(list))
				{
					var item = list.querySelector('[data-item-number="' + this.state.itemNumber + '"]');

					if (BX.type.isElementNode(item))
					{
						BX.adjust(item,
						{
							attrs : {"data-role" : "ui-ears-active"}
						})
					}
				}
			}
		}
	};
})();
