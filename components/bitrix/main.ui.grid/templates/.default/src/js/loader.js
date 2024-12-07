(function() {
	'use strict';

	BX.namespace('BX.Grid');

	BX.Grid.Loader = function(parent)
	{
		this.parent = null;
		this.container = null;
		this.windowHeight = null;
		this.tbodyPos = null;
		this.headerPos = null;
		this.lastPosTop = null;
		this.lastBottomPos = null;
		this.table = null;
		this.loader = null;
		this.adjustLoaderOffset = this.adjustLoaderOffset.bind(this);
		this.init(parent);
	};

	BX.Grid.Loader.prototype = {
		init(parent)
		{
			this.parent = parent;
			this.table = this.parent.getTable();
			this.loader = new BX.Loader({
				target: this.getContainer(),
			});
		},

		adjustLoaderOffset()
		{
			this.windowHeight = BX.height(window);
			this.tbodyPos = BX.pos(this.table.tBodies[0]);
			this.headerPos = BX.pos(this.table.tHead);

			let scrollY = window.scrollY;

			if (this.parent.isIE())
			{
				scrollY = document.documentElement.scrollTop;
			}

			let bottomPos = (scrollY + this.windowHeight) - this.tbodyPos.top;
			let posTop = scrollY - this.tbodyPos.top;

			if (bottomPos > (this.tbodyPos.bottom - this.tbodyPos.top))
			{
				bottomPos = this.tbodyPos.bottom - this.tbodyPos.top;
			}

			if (posTop < this.headerPos.height)
			{
				posTop = this.headerPos.height;
			}
			else
			{
				bottomPos -= posTop;
				bottomPos += this.headerPos.height;
			}

			requestAnimationFrame(() => {
				if (posTop !== this.lastPosTop)
				{
					this.getContainer().style.transform = `translate3d(0px, ${posTop}px, 0)`;
				}

				if (bottomPos !== this.lastBottomPos)
				{
					this.getContainer().style.height = `${bottomPos}px`;
				}

				this.lastPosTop = posTop;
				this.lastBottomPos = bottomPos;
			});
		},

		getContainer()
		{
			if (!this.container)
			{
				this.container = BX.Grid.Utils.getByClass(this.parent.getContainer(), this.parent.settings.get('classLoader'), true);
			}

			return this.container;
		},

		show()
		{
			if (!this.loader.isShown())
			{
				this.adjustLoaderOffset();
				this.getContainer().style.display = 'block';
				this.getContainer().style.opacity = '1';
				this.getContainer().style.visibility = 'visible';

				const rowsCount = this.parent.getRows().getCountDisplayed();

				if (rowsCount > 0 && rowsCount <= 2)
				{
					this.loader.setOptions({ size: 60 });
					this.loader.show();
				}
				else
				{
					this.loader.setOptions({ size: 110 });
					this.loader.show();
				}
			}
		},

		hide()
		{
			if (this.loader.isShown())
			{
				this.adjustLoaderOffset();
				this.loader.hide().then(() => {
					this.getContainer().style.display = 'none';
				});
			}
		},
	};
})();
