;(function () {

	'use strict';

	BX.namespace('BX.Landing.TileGrid');

	BX.Landing.TileGrid = function (params)
	{
		this.transferPopup = '';

		if (typeof params === 'object')
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
		    this.wrapper.classList.add('landing-ui-show');
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
		},

		/**
		 * Transfer the site to the another type.
		 * @param id Site id.
		 * @param params Some params.
		 */
		transfer: function(id, params)
		{
			if (!BX.type.isPlainObject(params))
			{
				params = {};
			}

			// base popup create
			if (!this.transferPopup)
			{
				this.transferPopup = new BX.PopupWindow('landing-domain-popup', null, {
					titleBar: BX.message('LANDING_ACTION_DIALOG_CONTENT'),
					content : BX('landing_domain_popup'),
					contentBackground: '#eef2f4',
					overlay: true,
					closeByEsc: true,
					zIndexAbsolute: 1050,
					buttons: [
						new BX.PopupWindowButton({
							id: 'landing-popup-window-button-accept',
							text : BX.message('BLOCK_CONTINUE'),
							className: 'popup-window-button-accept',
							events: {
								click : function()
								{
									this.action(
										'Site::update',
										{
											id: id,
											fields: {
												DOMAIN_ID: BX('new_domain_name').value,
												TYPE: params.type
														? params.type.toUpperCase()
														: null
											}
										},
										function()
										{
											this.transferPopup.close();
											BX.onCustomEvent('BX.Landing.Filter:apply');
										}.bind(this)
									);
								}.bind(this)
							}
						}),
						new BX.PopupWindowButton({
							text : BX.message('BLOCK_CANCEL'),
							className: 'popup-window-button-link',
							events: {
								click : function()
								{
									this.transferPopup.close();
								}.bind(this)
							}
						})
					],
				});
			}

			// set dynamic parts popup
			BX('new_domain_name').value = params.domainName;
			BX('new_domain_name_title').textContent = params.domainName;
			BX('new_domain_name_id').value = params.domainId;
			BX('landing_domain_address_allow').style.display = 'none';
			BX('landing_domain_address_disallow').style.display = 'none';

			// custom or b24 domain
			if (params.domainB24Name)
			{
				BX('new_domain_name_own').value = '';
				BX('new_domain_name_b24').value = params.domainB24Name;
			}
			else
			{
				BX('new_domain_name_b24').value = '';
				BX('new_domain_name_own').value = params.domainName;
			}

			// show
			this.transferPopup.show();

			// check domain available
			this.action(
				'Domain::check',
				{
					domain: params.domainName,
					filter: {
						'!ID': params.domainId
					}
				},
				function(data)
				{
					if (
						data.result &&
						data.result.available === true
					)
					{
						BX('landing_domain_address_allow').style.display = 'block';
					}
					else
					{
						BX('landing_domain_address_disallow').style.display = 'block';
					}
				}.bind(this)
			);
		}

	}

})();