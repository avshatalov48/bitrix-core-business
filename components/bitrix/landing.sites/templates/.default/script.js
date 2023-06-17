;(function () {

	'use strict';

	BX.namespace('BX.Landing.TileGrid');

	BX.Landing.TileGrid = function (params)
	{
		this.transferPopup = '';

		if (typeof params === 'object')
		{
			this.siteId = params.siteId;
			this.siteType = params.siteType;
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

			this.setTileWidth();
			BX.bind(window, 'resize', this.setTileWidth.bind(this));

			requestAnimationFrame(function() {
			    this.wrapper.classList.add('landing-ui-show');
			}.bind(this));
		}
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
				this.inner.style.marginTop = (obj.margin * -1) + 7 + 'px';
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

		action: function(action, data, successClb, componentName)
		{
			var loaderContainer = BX.create('div',{
				attrs:{className:'landing-filter-loading-container'}
			});
			document.body.appendChild(loaderContainer);

			var loader = new BX.Loader({size: 130, color: '#bfc3c8'});
			loader.show(loaderContainer);

			BX.ajax({
				url: BX.util.add_url_param(
					window.location.href,
					{action: action}
					),
				method: 'POST',
				data: {
					data: data,
					sessid: BX.message('bitrix_sessid'),
					actionType: 'rest',
					componentName: typeof componentName !== 'undefined'
									? componentName
									: null
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
							var msg = BX.Landing.UI.Tool.ActionDialog.getInstance();
							if (
								data.error_type === 'payment' &&
								(
									data.result[0].error === 'PUBLIC_SITE_REACHED' ||
									data.result[0].error === 'TOTAL_SITE_REACHED' ||
									data.result[0].error === 'PUBLIC_PAGE_REACHED' ||
									data.result[0].error === 'PUBLIC_SITE_REACHED_FREE'
								)
							)
							{
								if (data.result[0].error === 'PUBLIC_PAGE_REACHED')
								{
									top.BX.UI.InfoHelper.show('limit_sites_number_page');
								}
								else if (data.result[0].error === 'PUBLIC_SITE_REACHED_FREE')
								{
									top.BX.UI.InfoHelper.show('limit_sites_free');
								}
								else
								{
									if (this.siteType === 'STORE')
									{
										top.BX.UI.InfoHelper.show('limit_shop_number');
									}
									else if (this.siteType === 'KNOWLEDGE')
									{
										top.BX.UI.InfoHelper.show('limit_knowledge_base_number_page');
									}
									else
									{
										top.BX.UI.InfoHelper.show('limit_sites_number');
									}
								}
							}
							else if (data.result[0].error === 'FREE_DOMAIN_IS_NOT_ALLOWED')
							{
								top.BX.UI.InfoHelper.show('limit_free_domen');
							}
							else if (data.result[0].error === 'EMAIL_NOT_CONFIRMED')
							{
								top.BX.UI.InfoHelper.show('limit_sites_confirm_email');
							}
							else if (data.result[0].error === 'PHONE_NOT_CONFIRMED' && BX.Bitrix24 && BX.Bitrix24.PhoneVerify)
							{
								BX.Bitrix24.PhoneVerify
									.getInstance()
									.setEntityType('landing_site')
									.setEntityId(this.siteId)
									.startVerify({mandatory: false})
								;
							}
							else if (
								typeof BX.Landing.PaymentAlertShow !== 'undefined' &&
								data.error_type === 'payment'
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
							if (typeof successClb === 'function')
							{
								successClb(data);
							}
							else
							{
								if (top.window !== window)
								{
									// we are in slider
									window.location.reload();
								}
								else
								{
									BX.onCustomEvent('BX.Landing.Filter:apply');
								}
							}
						}
					}
				}.bind(this)
			});
		}
	}

})();