(function() {

	"use strict";

	BX.namespace("BX.Landing.Component");

	BX.Landing.Component.Demo = function(options)
	{
		this.wrapper = options.wrapper;
		this.inner = options.inner;
		this.tiles = options.tiles;
		this.isShow = false;
		this.startHeight = 0;
		this.innerBlock = null;

		this.handleMouseEnter = this.handleMouseEnter.bind(this);
		this.handleMouseLeave = this.handleMouseLeave.bind(this);

		this.createTileList();
		this.bindTitle();
	};

	BX.Landing.Component.Demo.prototype =
	{
		createTileList : function ()
		{
			new BX.Landing.TileGrid({
				wrapper: this.wrapper,
				inner: this.inner,
				tiles: this.tiles,
				sizeSettings : {
					minWidth : 250,
					maxWidth: 281
				}
			});
		},

		handleMouseEnter: function(tile)
		{
			this.showTitle(tile);
		},

		handleMouseLeave: function(tile)
		{
			this.hideTitle(tile);
		},

		bindTitle : function ()
		{
			this.tiles.forEach(function(tile) {
				var openBtn = tile.querySelector('.landing-item-desc-open');

				if(openBtn)
				{
					BX.bind(openBtn, 'click',
						function (event)
						{
							event.preventDefault();
							this.handleMouseEnter(tile);
						}.bind(this));

					BX.bind(tile, 'mouseleave', function (event)
					{
						this.handleMouseLeave(tile)
					}.bind(this));
				}

			}, this);
		},

		showTitle : function(tile)
		{
			this.innerBlock = tile.querySelector('.landing-item-desc-inner');

			var descHeightBlock = tile.querySelector('.landing-item-desc-height');
			var offset = this.innerBlock.offsetTop;

			tile.classList.add('landing-tile-title-show');
			this.innerBlock.style.paddingTop = offset + 'px';
			this.innerBlock.style.marginBottom = offset + 'px';
			this.startHeight = BX.style(this.innerBlock, 'height');
			this.innerBlock.style.height = descHeightBlock.offsetHeight + 'px';

			this.isShow = true;
		},

		hideTitle : function(tile)
		{
			if(this.isShow)
			{
				this.innerBlock.style.height = this.startHeight;

				setTimeout(function ()
				{
					tile.classList.remove('landing-tile-title-show');
					this.innerBlock.style.paddingTop = 0;
					this.innerBlock.style.marginBottom = 0;
				}.bind(this), 230);

				this.isShow = false;
			}
		}
	};

})();
