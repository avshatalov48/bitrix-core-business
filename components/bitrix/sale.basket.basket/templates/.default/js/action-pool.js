;(function(){
	'use strict';

	BX.namespace('BX.Sale.BasketActionPool');

	BX.Sale.BasketActionPool = function(component)
	{
		this.component = component;

		this.requestProcessing = false;
		this.updateTimer = null;

		this.isBasketRefreshed = this.component.params.DEFERRED_REFRESH !== 'Y';
		this.needFullRecalculation = this.component.params.DEFERRED_REFRESH === 'Y';

		this.pool = {};
		this.lastActualPool = {};

		this.approvedAction = ['QUANTITY', 'DELETE', 'RESTORE', 'DELAY', 'OFFER', 'MERGE_OFFER'];

		this.switchTimer();
	};

	BX.Sale.BasketActionPool.prototype.setRefreshStatus = function(status)
	{
		this.isBasketRefreshed = !!status;
	};

	BX.Sale.BasketActionPool.prototype.getRefreshStatus = function()
	{
		return this.isBasketRefreshed;
	};

	BX.Sale.BasketActionPool.prototype.isItemInPool = function(itemId)
	{
		return !!this.pool[itemId];
	};

	BX.Sale.BasketActionPool.prototype.clearLastActualQuantityPool = function(itemId)
	{
		this.lastActualPool[itemId] && delete this.lastActualPool[itemId].QUANTITY;
	};

	BX.Sale.BasketActionPool.prototype.checkItemPoolBefore = function(itemId)
	{
		if (!itemId)
			return;

		this.pool[itemId] = this.pool[itemId] || {};
	};

	BX.Sale.BasketActionPool.prototype.checkItemPoolAfter = function(itemId)
	{
		if (!itemId || !this.pool[itemId])
			return;

		if (Object.keys(this.pool[itemId]).length === 0)
		{
			delete this.pool[itemId];
		}
	};

	BX.Sale.BasketActionPool.prototype.addCoupon = function(coupon)
	{
		this.pool.COUPON = coupon;

		this.switchTimer();
	};

	BX.Sale.BasketActionPool.prototype.removeCoupon = function(coupon)
	{
		this.checkItemPoolBefore('REMOVE_COUPON');

		this.pool.REMOVE_COUPON[coupon] = coupon;

		this.switchTimer();
	};

	BX.Sale.BasketActionPool.prototype.changeQuantity = function(itemId, quantity, oldQuantity)
	{
		this.checkItemPoolBefore(itemId);

		if (
			(this.lastActualPool[itemId] && this.lastActualPool[itemId].QUANTITY !== quantity)
			|| (!this.lastActualPool[itemId] && quantity !== oldQuantity)
		)
		{
			this.pool[itemId].QUANTITY = quantity;
		}
		else
		{
			this.pool[itemId] && delete this.pool[itemId].QUANTITY;
		}

		this.checkItemPoolAfter(itemId);
		this.switchTimer();
	};

	BX.Sale.BasketActionPool.prototype.deleteItem = function(itemId)
	{
		this.checkItemPoolBefore(itemId);

		if (this.pool[itemId].RESTORE)
		{
			delete this.pool[itemId].RESTORE;
		}
		else
		{
			this.pool[itemId].DELETE = 'Y';
		}

		this.checkItemPoolAfter(itemId);
		this.switchTimer();
	};

	BX.Sale.BasketActionPool.prototype.restoreItem = function(itemId, itemData)
	{
		this.checkItemPoolBefore(itemId);

		if (this.pool[itemId].DELETE === 'Y')
		{
			delete this.pool[itemId].DELETE;
		}
		else
		{
			this.pool[itemId].RESTORE = itemData;
		}

		this.checkItemPoolAfter(itemId);
		this.switchTimer();
	};

	BX.Sale.BasketActionPool.prototype.addDelayed = function(itemId)
	{
		this.checkItemPoolBefore(itemId);

		this.pool[itemId].DELAY = 'Y';

		this.checkItemPoolAfter(itemId);
		this.switchTimer();
	};

	BX.Sale.BasketActionPool.prototype.removeDelayed = function(itemId)
	{
		this.checkItemPoolBefore(itemId);

		this.pool[itemId].DELAY = 'N';

		this.checkItemPoolAfter(itemId);
		this.switchTimer();
	};

	BX.Sale.BasketActionPool.prototype.changeSku = function(itemId, props, oldProps)
	{
		if (JSON.stringify(props) !== JSON.stringify(oldProps))
		{
			this.checkItemPoolBefore(itemId);
			this.pool[itemId].OFFER = props;
		}
		else
		{
			this.pool[itemId] && delete this.pool[itemId].OFFER;
			this.checkItemPoolAfter(itemId);
		}

		this.switchTimer();
	};

	BX.Sale.BasketActionPool.prototype.mergeSku = function(itemId)
	{
		this.checkItemPoolBefore(itemId);
		this.pool[itemId].MERGE_OFFER = 'Y';

		this.switchTimer();
	};

	BX.Sale.BasketActionPool.prototype.switchTimer = function()
	{
		clearTimeout(this.updateTimer);

		if (this.isProcessing())
		{
			return;
		}

		if (this.isPoolEmpty())
		{
			this.component.editPostponedBasketItems();
			this.component.fireCustomEvents();
		}

		if (!this.isPoolEmpty())
		{
			this.updateTimer = setTimeout(BX.proxy(this.trySendPool, this), 300);
		}
		else if (!this.getRefreshStatus())
		{
			this.trySendPool();
		}
	};

	BX.Sale.BasketActionPool.prototype.trySendPool = function()
	{
		if (this.isPoolEmpty() && this.getRefreshStatus())
		{
			return;
		}

		this.doProcessing(true);

		if (!this.isPoolEmpty())
		{
			this.component.sendRequest('recalculateAjax', {
				basket: this.getPoolData()
			});

			this.lastActualPool = this.pool;
			this.pool = {};
		}
		else if (!this.getRefreshStatus())
		{
			this.component.sendRequest('refreshAjax', {
				fullRecalculation: this.needFullRecalculation ? 'Y' : 'N'
			});
			this.needFullRecalculation = false;
		}
	};

	BX.Sale.BasketActionPool.prototype.getPoolData = function()
	{
		var poolData = {},
			currentPool = this.pool;

		if (currentPool.COUPON)
		{
			poolData.coupon = currentPool.COUPON;
			delete currentPool.COUPON;
		}

		if (currentPool.REMOVE_COUPON)
		{
			poolData.delete_coupon = currentPool.REMOVE_COUPON;
			delete currentPool.REMOVE_COUPON;
		}

		for (var id in currentPool)
		{
			if (currentPool.hasOwnProperty(id))
			{
				for (var action in currentPool[id])
				{
					if (currentPool[id].hasOwnProperty(action) && BX.util.in_array(action, this.approvedAction))
					{
						poolData[action + '_' + id] = currentPool[id][action];
					}
				}
			}
		}

		return poolData;
	};

	BX.Sale.BasketActionPool.prototype.isPoolEmpty = function()
	{
		return Object.keys(this.pool).length === 0;
	};

	BX.Sale.BasketActionPool.prototype.doProcessing = function(state)
	{
		this.requestProcessing = state === true;

		if (this.requestProcessing)
		{
			this.component.startLoader();
		}
		else
		{
			this.component.endLoader();
		}
	};

	BX.Sale.BasketActionPool.prototype.isProcessing = function()
	{
		return this.requestProcessing === true;
	};
})();