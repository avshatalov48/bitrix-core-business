;(function (w) {

	w.BX = w.BX || {};
	if (w.BX.HeatMap)
	{
		return;
	}

	w.BX.HeatMapItem = function (params) {
		this.caller = params.caller;
		this.document = params.document;
		this.value = params.value || 0;
		this.color = params.color || '#000';
		this.textColor = params.textColor || '#000';
		this.text = params.text || this.value;
		this.baloon = params.baloon || '';
		this.anchorNode = params.anchorNode || '';

		this.id = params.id || (Math.floor(Math.random() * (99999999 - 1000000 + 1)) + 1000000);

		this.valuePercent = 0;
		this.currentSize = 50;
		this.position = {left: 0, top: 0, isActual: false};
		this.node = null;
	};
	w.BX.HeatMapItem.prototype = {
		initNodes: function ()
		{
			if (this.node)
			{
				return;
			}

			this.node = this.caller.document.createElement('div');
			BX.addClass(this.node, 'bx-heat-map-item');

			var cont = this.caller.document.createElement('span');
			this.node.appendChild(cont);
			this.nodeValue = this.caller.document.createElement('span');
			cont.appendChild(this.nodeValue);
			this.nodePercent = this.caller.document.createElement('span');
			cont.appendChild(this.nodePercent);

			this.caller.document.body.appendChild(this.node);
		},
		getRealAnchorNode: function ()
		{
			return (this.anchorNode.children.length > 0 ? this.anchorNode.children[0] : this.anchorNode);
		},
		resize: function (size)
		{
			this.position.isActual = this.currentSize == size;
			this.currentSize = size;
			this.calcPosition();
		},
		calcPosition: function ()
		{
			if (this.position.isActual)
			{
				return;
			}

			var anchorPos = BX.pos(this.getRealAnchorNode());

			this.position.top = anchorPos.top + Math.round(anchorPos.height/2);
			this.position.top -= Math.round(this.currentSize / 2);

			this.position.left = anchorPos.left + Math.round(anchorPos.width/2);
			this.position.left -= Math.round(this.currentSize / 2);
		},
		remove: function ()
		{
			BX.remove(this.node);
		},
		draw: function ()
		{
			this.initNodes();
			this.calcPosition();

			var percents = String(this.valuePercent);
			if (percents.substring(percents.length-2, percents.length) == '.0')
			{
				percents = percents.substring(0, percents.length-2);
			}
			this.nodeValue.innerText = percents;
			this.nodePercent.innerText = '%';

			this.node.style.width = this.currentSize + 'px';
			this.node.style.height = this.currentSize + 'px';
			this.node.style.fontSize = Math.round(this.currentSize/3) + 'px';

			this.node.style.top = this.position.top + 'px';
			this.node.style.left = this.position.left + 'px';
		}
	};

	w.BX.HeatMap = function (params) {
		this.document = params.document || document;
		this.color = params.color || [0, 191, 255, 0.5];
		this.maxSize = params.maxSize || 90;
		this.minSize = params.minSize || 30;
		this.valueSum = 0;

		this.items = [];
		(params.items || []).forEach(this.addItem, this);
	};
	//(function(){}()).apply(w.BX.HeatMap.prototype);
	w.BX.HeatMap.prototype = {
		isItemsInited: false,
		draw: function ()
		{
			if (this.document == document)
			{
				BX.loadCSS('/bitrix/js/sender/heatmap/style.css', this.document);
			}
			else
			{
				var cssNode = this.document.createElement('LINK');
				cssNode.type = 'text/css';
				cssNode.rel = 'stylesheet';
				cssNode.href = '/bitrix/js/sender/heatmap/style.css?' + (1 * new Date());
				this.document.head.appendChild(cssNode);
			}

			this.resizeItems();

			this.items.forEach(function (item) {
				item.draw();
			}, this);

			if (!this.isItemsInited)
			{
				this.items.forEach(function (item) {
					var _this = this;
					BX.bind(item.node, 'mouseenter', function () {
						_this.highLightItem(item, true);
					});
					BX.bind(item.node, 'mouseleave', function () {
						_this.highLightItem(item, false);
					});
				}, this);
			}

			this.isItemsInited = true;
		},
		resizeItems: function ()
		{
			this.valueSum = 0;
			this.items.forEach(function (item) {
				this.valueSum += parseFloat(item.value);
			}, this);

			this.items.forEach(function (item) {
				var value = parseFloat(item.value);
				var delta = this.maxSize - this.minSize;
				var size = this.minSize + Math.ceil(value * delta / this.valueSum);
				item.valuePercent = (value * 100 / this.valueSum).toFixed(1);
				item.resize(size);
			}, this);
		},
		highLightItem: function (item, isHiglight)
		{
			isHiglight = isHiglight || false;
			if (!this.shadowNode)
			{
				this.shadowNode = this.document.createElement('div');
				BX.addClass(this.shadowNode, 'bx-heat-map-shadow');
				this.document.body.appendChild(this.shadowNode);
			}

			if (isHiglight)
			{
				BX.addClass(this.shadowNode, 'bx-heat-map-shadow-show');
				BX.addClass(item.getRealAnchorNode(), 'bx-heat-map-item-highlight');
			}
			else
			{
				BX.removeClass(this.shadowNode, 'bx-heat-map-shadow-show');
				BX.removeClass(item.getRealAnchorNode(), 'bx-heat-map-item-highlight');
			}
		},
		addItem: function (params)
		{
			params.caller = this;
			params.document = this.document;
			var item = new BX.HeatMapItem(params);
			this.items.push(item);

			return item.id;
		},
		getItemById: function (id)
		{
			var filtered = this.items.filter(function (item) {
				return item.id == id;
			}, this);
			return (filtered.length > 0 ? filtered[0] : null);
		},
		removeItem: function (id)
		{
			var item = this.getItemById(id);
			var index;
			this.items.forEach(function (item, ind) {
				if (item.id == id)
				{
					index = ind;
				}
			}, this);

			BX.util.deleteFromArray(this.items, index);
			item.remove();

			return item;
		}
	};

})(window);