;(function () {

	"use strict";

	BX.namespace('BX.Landing.TileGrid');

	BX.Landing.TileGrid = function (params)
	{
		if (typeof params === "object")
		{
			this.wrapper = params.wrapper;
			this.inner = params.inner;
			this.tiles = params.tiles;
			this.minTileWidth = 0;
			this.maxTileWidth = 0;
			this.tileRowLength = 0;

			// You can set min. max. width or amount of tiles in one row
			if(params.sizeSettings)
			{
				this.minTileWidth = params.sizeSettings.minWidth;
				this.maxTileWidth = params.sizeSettings.maxWidth;
			}
			else if (params.tileRowLength)
			{
				this.tileRowLength = params.tileRowLength;
			}
			else
			{
				this.minTileWidth = 250;
				this.maxTileWidth = 350;
			}

			this.tileRatio = params.tileRatio || 1.48;
			this.maxTileHeight = this.maxTileWidth / this.tileRatio;
		}

		this.setTileWidth();
		BX.bind(window, 'resize', this.setTileWidth.bind(this));

		requestAnimationFrame(function() {
		    this.wrapper.classList.add("landing-ui-show");
		}.bind(this));
	};

	BX.Landing.TileGrid.prototype =
	{
		setTileWidth : function ()
		{
			var obj =  this.getTileCalculating();

			var width = obj.width;
			var height = obj.height;

			if(this.minTileWidth)
			{
				width = width <= this.maxTileWidth ? obj.width : this.maxTileWidth;
				height = height <= this.maxTileHeight ? obj.height : this.maxTileHeight;
			}

			requestAnimationFrame(function() {
				for(var i=0; i<this.tiles.length; i++)
				{
					this.tiles[i].style.width = width + 'px';
					this.tiles[i].style.height = height + 'px';
					this.tiles[i].style.marginLeft = obj.margin + 'px';
					this.tiles[i].style.marginTop = obj.margin + 'px';
				}
				this.inner.style.marginLeft = (obj.margin * -1) + 'px';
				this.inner.style.marginTop = (obj.margin * -1) + 'px';
			}.bind(this));
		},

		getTileCalculating : function()
		{
			var wrapperWidth = this.wrapper.clientWidth - 12; // margin-right for wrapper
			var wholeMarginSize =  wrapperWidth / 100 * 6; // 6% of whole width for margins
			var width = 0,
				tileAmountInRow = 0;

			if(this.tileRowLength)
			{
				tileAmountInRow = this.tileRowLength;
				width = (wrapperWidth - wholeMarginSize) / this.tileRowLength;
			}
			else {
				width = this.minTileWidth;
				tileAmountInRow = (wrapperWidth - wholeMarginSize) / width;

				// if tiles in one line can fit more than tiles amount
				if(tileAmountInRow > this.tiles.length)
				{
					width = (wrapperWidth - wholeMarginSize) / this.tiles.length;
					width = width > this.maxTileWidth ? this.maxTileWidth : width;
				}
				// if there is an hole (width doesn't fit) in the end tile row, increase tile width
				else if((tileAmountInRow - Math.floor(tileAmountInRow)) > 0)
				{
					tileAmountInRow = Math.floor(tileAmountInRow);
					width = (wrapperWidth - wholeMarginSize) / tileAmountInRow;
				}
			}
			return {
				width: width,
				margin: wholeMarginSize / (tileAmountInRow-1),
				height: width / this.tileRatio
			};
		},

		action: function(action, data)
		{
			var loaderContainer = BX.create('div',{
				attrs:{className:'landing-filter-loading-container'}
			});
			document.body.appendChild(loaderContainer);

			var loader = new BX.Loader({size: 130, color: "#bfc3c8"});
			loader.show(loaderContainer);

			BX.ajax({
				url: '/bitrix/tools/landing/ajax.php?action=' + action,
				method: 'POST',
				data: {
					data: data,
					sessid: BX.message('bitrix_sessid')
				},
				dataType: 'json',
				onsuccess: function(data)
				{
					loader.hide();
					loaderContainer.classList.add('landing-filter-loading-hide');

					if (
						typeof data.type !== 'undefined' &&
						typeof data.result !== 'undefined'
					)
					{
						if (data.type === 'error')
						{
							var errorCode = data.result[0].error;
							var msg = BX.Landing.UI.Tool.ActionDialog.getInstance();
							if (
								(
									errorCode == 'PUBLIC_SITE_REACHED' ||
									errorCode == 'PUBLIC_PAGE_REACHED'
								) &&
								typeof BX.Landing.PaymentAlertShow !== 'undefined'
							)
							{
								BX.Landing.PaymentAlertShow({
									message: data.result[0].error_description
								});
							}
							else
							{
								msg.show({
									content: data.result[0].error_description,
									confirm: 'OK',
									type: 'alert'
								});
							}
						}
						else
						{
							BX.onCustomEvent('BX.Main.Filter:apply');
						}
					}
				}
			});
		}

	}

})();