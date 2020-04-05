;(function() {
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
		init: function(parent)
		{
			this.parent = parent;
			this.table = this.parent.getTable();
			this.loader = new BX.Loader({
				target: this.getContainer()
			});
		},

		adjustLoaderOffset: function()
		{
			this.windowHeight = BX.height(window);
			this.tbodyPos = BX.pos(this.table.tBodies[0]);
			this.headerPos = BX.pos(this.table.tHead);

			var scrollY = window.scrollY;

			if (this.parent.isIE())
			{
				scrollY = document.documentElement.scrollTop;
			}

			var bottomPos = (scrollY + this.windowHeight) - this.tbodyPos.top;
			var posTop = scrollY - this.tbodyPos.top;

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

			requestAnimationFrame(function() {
				if (posTop !== this.lastPosTop)
				{
					this.getContainer().style.transform = 'translate3d(0px, ' + posTop + 'px, 0)';
				}

				if (bottomPos !== this.lastBottomPos)
				{
					this.getContainer().style.height = bottomPos + 'px';
				}

				this.lastPosTop = posTop;
				this.lastBottomPos = bottomPos;
			}.bind(this));
		},

		getContainer: function()
		{
			if (!this.container)
			{
				this.container = BX.Grid.Utils.getByClass(this.parent.getContainer(), this.parent.settings.get('classLoader'), true);
			}

			return this.container;
		},

		show: function()
		{
			if (!this.loader.isShown())
			{
				this.adjustLoaderOffset();
				this.getContainer().style.display = "block";
				this.getContainer().style.opacity = "1";
				this.getContainer().style.visibility = "visible";
				this.loader.show();
			}
		},

		hide: function()
		{
			if (this.loader.isShown())
			{
				this.adjustLoaderOffset();
				this.loader.hide().then(function() {
					this.getContainer().style.display = "none";
				}.bind(this));
			}
		}
	};
})();