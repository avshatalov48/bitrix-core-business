function initDraggableAddControl(params)
{
	var data = JSON.parse(params.data);
	if (data)
	{
		BX.loadScript('/bitrix/js/main/core/core_dragdrop.js', function(){
			(function bx_dnd_add_waiter(){
				if (!!BX.DragDrop)
					window['dnd_parameter_' + params.propertyID] = new DragNDropAddParameterControl(data, params);
				else
					setTimeout(bx_dnd_add_waiter, 50);
			})();
		});
	}
}

function DragNDropAddParameterControl(items, params)
{
	var rand = BX.util.getRandomString(5);

	this.params = params || {};
	this.useBigData = this.params.propertyParams.BIG_DATA && this.params.propertyParams.BIG_DATA === 'Y';
	this.message = JSON.parse(params.propertyParams.JS_MESSAGES) || {};
	this.nodes = {countParamInput: this.getCountParamInput()};
	this.activeDragNode = false;
	this.temporarySortNode = false;
	this.itemRemoved = false;
	this.ids = {
		to: 'to_dnd_params_container_' + this.params.propertyID + '_' + rand,
		from: 'from_dnd_params_container_' + this.params.propertyID + '_' + rand,
		label: 'label_' + this.params.propertyID + '_' + rand
	};
	this.baseItems = this.getBaseItems(items);
	this.sortedItems = this.getSortedItems(items);
	this.variantCounts = this.getVariantsCountMap(items);

	this.dragItemClassName = 'dnd-add-draggable-item-' + this.params.propertyID + '-' + rand;

	this.lastEntered = null;
	this.timeOut = null;

	BX.loadCSS(this.getPath() + '/style.css?' + rand);
	this.buildNodes();
	this.initDragDrop();
	this.saveData();
}

DragNDropAddParameterControl.prototype =
{
	getPath: function()
	{
		var path = this.params.propertyParams.JS_FILE.split('/');

		path.pop();

		return path.join('/');
	},

	getBaseItems: function(items)
	{
		if (!items)
			return [];

		var result = [], k;

		for (k in items)
		{
			if (items.hasOwnProperty(k))
			{
				result.push({
					variant: items[k].VARIANT,
					bigData: false,
					message: items[k].CODE
				});
			}
		}

		return result;
	},

	getSortedItems: function(items)
	{
		if (!items)
			return [];

		var inputValue = this.params.oInput.value || '',
			result = [],
			k, values;

		try
		{
			values = JSON.parse(inputValue.replace(/'/g, '"'));
		}
		catch (e)
		{
			values = [];
		}

		for (k in values)
		{
			if (values.hasOwnProperty(k))
			{
				if (
					items[values[k].VARIANT]
					&& (
						!this.useBigData && !values[k].BIG_DATA
						|| this.useBigData
					)
				)
				{
					result.push({
						variant: values[k].VARIANT,
						bigData: values[k].BIG_DATA,
						message: items[values[k].VARIANT].CODE
					});
				}
			}
		}

		return result;
	},

	buildNodes: function()
	{
		var propertyTr = BX.findParent(this.params.oCont, {className: 'bxcompprop-prop-tr'}),
			propertyTds = BX.findChildren(propertyTr, {tagName: 'td'}),
			newTr = BX.create('TR', {props: {className: 'bxcompprop-prop-tr'}});

		if (propertyTds.length)
		{
			propertyTds[0].setAttribute('colspan', 2);
			propertyTds[0].setAttribute('style', 'text-align: center !important');
			propertyTds[1].setAttribute('colspan', 2);
			newTr.appendChild(propertyTds[0]);
			propertyTr.parentNode.insertBefore(newTr, propertyTr);
		}

		this.nodes.rootTo = this.getToNode();
		this.nodes.rootFrom = this.getFromNode();
		this.nodes.summaryInfo = BX.create('DIV', {props: {className: 'catalog-preset-summary'}});
		this.nodes.bigDataControl = this.useBigData
			? BX.create('DIV', {
				props: {className: 'catalog-preset-bigdata-control'},
				children: [
					BX.create('LABEL', {
						attrs: {for: this.ids.label},
						children: [
							BX.create('INPUT', {
								props: {id: this.ids.label, type: 'checkbox'},
								events: {change: BX.proxy(this.toggleBigData, this)}
							}),
							BX.create('SPAN', {text: ' BigData'})
						]
					})
				]
			})
			: null;
		this.nodes.summary = BX.create('TABLE', {
			attrs: {width: '100%'},
			children: [
				BX.create('TR', {
					children: [
						BX.create('TD', {
							style: {verticalAlign: 'bottom'},
							children: [this.nodes.summaryInfo]
						}),
						BX.create('TD', {
							style: {verticalAlign: 'bottom'},
							children: [this.nodes.bigDataControl]
						})
					]
				})
			]
		});

		this.params.oCont.appendChild(
			BX.create('DIV', {
				props: {className: 'dnd-add-common-container'},
				children: [
					this.nodes.summary,
					this.nodes.rootTo,
					this.nodes.rootFrom,
					BX.create('DIV', {props: {className: 'catalog-preset-clear'}})
				]
			})
		);
	},

	getToNode: function()
	{
		var toNode = BX.create('DIV', {props: {id: this.ids.to, className: 'catalog-preset-left'}});

		for (var k in this.sortedItems)
		{
			if (this.sortedItems.hasOwnProperty(k))
			{
				toNode.appendChild(
					BX.create('DIV', {
						attrs: {
							'data-value': this.sortedItems[k].variant.toString(),
							'data-bigdata': this.sortedItems[k].bigData ? 'true' : 'false'
						},
						props: {
							type: 'button',
							className: this.dragItemClassName + ' dnd-add-draggable-control catalog-preset-shem catalog-preset-shem-'
							+ this.sortedItems[k].message,
							title: this.message.variant + ' ' + this.sortedItems[k].message
						},
						children: [
							BX.create('DIV', {props: {className: 'catalog-preset-shem-bigdata'}}),
							BX.create('DIV', {
								props: {className: 'dnd-add-draggable-control-remove', title: this.message.delete},
								events: {click: BX.proxy(this.removeItem, this)}
							})
						],
						events: {
							dragstart: BX.delegate(function(){
								this.itemFromSortedList = BX.proxy_context;
							}, this),
							dragend: BX.delegate(function(){
								this.itemFromSortedList = false;
								this.disableActiveDropZone();
							}, this)
						}
					})
				);
			}
		}

		return toNode;
	},

	getFromNode: function()
	{
		var fromNode = BX.create('DIV', {
			props: {
				id: this.ids.from,
				className: 'catalog-preset-right'
			},
			children: [
				BX.create('DIV', {
					props: {className: 'catalog-preset-center-arrow'},
					children:[
						BX.create('DIV', {
							props: {className: 'catalog-preset-center-arrow-btn'},
							events: {click: BX.proxy(this.arrowClick, this)}
						})
					]
				})
			]
		});

		for (var k in this.baseItems)
		{
			if (this.baseItems.hasOwnProperty(k))
			{
				fromNode.appendChild(
					BX.create('DIV', {
						attrs: {
							'data-value': this.baseItems[k].variant.toString(),
							'data-bigdata': 'false',
							draggable: 'true'
						},
						props: {
							type: 'button',
							className: 'catalog-preset-shem catalog-preset-shem-' + this.baseItems[k].message
							+ (k == 0 ? ' catalog-preset-selected' : ''),
							title: this.message.variant + ' ' + this.baseItems[k].message
						},
						children: [BX.create('DIV', {props: {className: 'catalog-preset-shem-bigdata'}})],
						events: {
							click: BX.proxy(this.selectItem, this),
							dragstart: BX.proxy(function(event){
								event.dataTransfer.setData('text', ''); /*for FF*/
								this.activeDragNode = BX.proxy_context.cloneNode(true);
								this.temporarySortNode = false;
								this.selectItem(event);
								BX.addClass(this.activeDragNode, 'dnd-add-dragged-item');
							}, this),
							drag: BX.proxy(function(event){
								BX.PreventDefault(event);

								this.dragdrop._ondrag(event);

								if (!BX.browser.IsFirefox())
								{
									if (this.temporarySortNode && !this.dragdrop.sortableInterval)
									{
										this.dragdrop.ondragStart(event, this.temporarySortNode);
									}

									if (!this.temporarySortNode && this.dragdrop.sortableInterval)
									{
										this.dragdrop.ondragEnd(event);
										this.dragdrop.sortableInterval = false;
									}
								}
							}, this),
							dragend: BX.proxy(function(event){
								BX.PreventDefault(event);

								BX.removeClass(this.temporarySortNode, 'draggable-active');
								this.disableActiveDropZone();

								if (this.dragdrop.sortableInterval)
								{
									this.dragdrop.ondragEnd(event, this.temporarySortNode);
									this.dragdrop.sortableInterval = false;
								}

								this.activeDragNode = false;
								this.temporarySortNode = false;
							}, this)
						}
					})
				);
			}
		}

		return fromNode;
	},

	selectItem: function(event)
	{
		var target = BX.getEventTarget(event),
			presets = this.nodes.rootFrom.querySelectorAll('.catalog-preset-shem'),
			i, value;

		if (target && !BX.hasClass(target, 'catalog-preset-shem'))
		{
			target = BX.findParent(target, {className: 'catalog-preset-shem'}, this.nodes.rootFrom);
		}

		if (!target)
			return;

		value = target.getAttribute('data-value');

		for (i in presets)
		{
			if (presets.hasOwnProperty(i))
			{
				if (presets[i].getAttribute('data-value') === value)
				{
					BX.addClass(presets[i], 'catalog-preset-selected');
				}
				else
				{
					BX.removeClass(presets[i], 'catalog-preset-selected');
				}
			}
		}
	},

	removeItem: function(event)
	{
		var target = BX.getEventTarget(event),
			preset;

		if (!target)
			return;

		preset = BX.findParent(target, {className: 'dnd-add-draggable-control'});
		if (preset)
		{
			this.nodes.rootTo.removeChild(preset);
			this.dragdrop.removeSortableItem(preset);
		}

		this.saveData();
		BX.PreventDefault(event);
	},

	initDragDrop: function()
	{
		if (BX.isNodeInDom(this.params.oCont))
		{
			this.dragdrop = BX.DragDrop.create({
				dragItemClassName: this.dragItemClassName,
				dragItemControlClassName: 'dnd-add-draggable-control',
				sortable: {rootElem: this.nodes.rootTo},
				dragEnd: BX.delegate(function(){
					this.saveData();
				}, this)
			});

			BX.bind(this.nodes.rootTo, 'dragenter', BX.delegate(this.onDragEnter, this));
			BX.bind(this.nodes.rootTo, 'dragover', BX.delegate(this.onDragOver, this));
			BX.bind(this.nodes.rootTo, 'dragleave', BX.delegate(this.onDragLeave, this));
		}
		else
		{
			setTimeout(BX.delegate(this.initDragDrop, this), 50);
		}
	},

	toggleBigData: function(event)
	{
		var target = BX.getEventTarget(event),
			nodes, i;

		if (!target)
			return;

		nodes = this.nodes.rootFrom.querySelectorAll('[data-bigdata]');
		i = nodes.length;

		while (i--)
		{
			nodes[i].setAttribute('data-bigdata', !!target.checked ? 'true' : 'false');
		}
	},

	onDragEnter: function(event)
	{
		BX.eventReturnFalse(event);

		this.lastEntered = event.target;
	},

	onDragOver: function(event)
	{
		BX.eventReturnFalse(event);

		this.enableActiveDropZone();

		if (this.activeDragNode && !this.temporarySortNode)
		{
			this.temporarySortNode = this.getTemporaryNodeClone(this.activeDragNode);
			BX.addClass(this.temporarySortNode, 'draggable-active');
			this.nodes.rootTo.appendChild(this.temporarySortNode);
			this.dragdrop.addDragItem([this.temporarySortNode]);
			this.dragdrop.addSortableItem(this.temporarySortNode);

			this.saveData();
		}

		if (this.itemFromSortedList && this.itemRemoved)
		{
			this.nodes.rootTo.appendChild(this.itemFromSortedList);
			this.dragdrop.addDragItem([this.itemFromSortedList]);
			this.dragdrop.addSortableItem(this.itemFromSortedList);
			this.temporarySortNode = false;
			this.itemRemoved = false;

			this.saveData();
		}
	},

	onDragLeave: function(event)
	{
		BX.eventReturnFalse(event);

		if (this.lastEntered !== event.target)
		{
			return;
		}

		var elementTo = document.elementFromPoint(event.pageX, event.pageY);
		if (!elementTo || !this.nodes.rootTo.contains(elementTo))
		{
			this.disableActiveDropZone();

			if (this.temporarySortNode)
			{
				this.nodes.rootTo.removeChild(this.temporarySortNode);
				this.dragdrop.removeSortableItem(this.temporarySortNode);
				this.dragdrop.isSortableActive = false;
				this.temporarySortNode = false;

				this.saveData();
			}

			if (this.itemFromSortedList && !this.itemRemoved)
			{
				this.nodes.rootTo.removeChild(this.itemFromSortedList);
				this.dragdrop.removeSortableItem(this.itemFromSortedList);
				this.dragdrop.isSortableActive = false;
				this.temporarySortNode = false;
				this.itemRemoved = true;

				this.saveData();
			}
		}
	},

	getTemporaryNodeClone: function(dragNode)
	{
		var node = dragNode.cloneNode(true);

		BX.removeClass(node, 'dnd-add-dragged-item catalog-preset-selected');
		BX.addClass(node, 'dnd-add-draggable-control ' + this.dragItemClassName);

		BX.unbindAll(node);
		BX.bind(node, 'dragstart', BX.delegate(function(){this.itemFromSortedList = BX.proxy_context;}, this));
		BX.bind(node, 'dragend', BX.delegate(function(){this.itemFromSortedList = false;}, this));

		node.appendChild(
			BX.create('DIV', {
				props: {className: 'dnd-add-draggable-control-remove', title: this.message.delete},
				events: {click: BX.delegate(this.removeItem, this)}
			})
		);

		return node;
	},

	enableActiveDropZone: function()
	{
		BX.addClass(this.nodes.rootTo, 'drop-zone-active');
	},

	disableActiveDropZone: function()
	{
		BX.removeClass(this.nodes.rootTo, 'drop-zone-active');
	},

	saveData: function()
	{
		var items = this.nodes.rootTo.querySelectorAll('.' + this.dragItemClassName),
			arr = [];

		for (var k in items)
		{
			if (items.hasOwnProperty(k))
			{
				arr.push({
					VARIANT: items[k].getAttribute('data-value'),
					BIG_DATA: items[k].getAttribute('data-bigdata') === 'true'
				});
			}
		}

		this.params.oInput.value = JSON.stringify(arr).replace(/"/g, "'");

		if (this.timeOut)
		{
			this.timeOut = clearTimeout(this.timeOut);
		}

		this.timeOut = setTimeout(BX.proxy(function(){this.setElementCount(arr)}, this), 20);
	},

	getCountParamInput: function()
	{
		var contentNode = BX.findParent(this.params.oCont, {className: 'bxcompprop-content'}),
			elementCountInput = null,
			inputName = this.params.propertyParams.COUNT_PARAM_NAME || '';

		if (contentNode && inputName)
		{
			elementCountInput = contentNode.querySelector('[data-bx-property-id="' + inputName + '"]');
		}

		return elementCountInput;
	},

	setElementCount: function(rows)
	{
		var count, bigDataCount, text;

		count = this.getElementCount(rows, false);
		bigDataCount = this.getElementCount(rows, true);

		if (this.nodes.countParamInput)
		{
			this.nodes.countParamInput.value = count;
		}

		text = this.message.quantity + ' - ' + count + '<br />';
		text += (bigDataCount ? this.message.quantityBigData + ' - ' + bigDataCount : '');

		this.nodes.summaryInfo.innerHTML = text;
	},

	getElementCount: function(rows, bigData)
	{
		var count = 0;

		for (var i in rows)
		{
			if (rows.hasOwnProperty(i))
			{
				if (bigData && rows[i].BIG_DATA || !bigData && !rows[i].BIG_DATA)
				{
					count += parseInt(this.variantCounts[rows[i].VARIANT]);
				}
			}
		}

		return count;
	},

	getVariantsCountMap: function(items)
	{
		var map = [];

		for (var i in items)
		{
			if (items.hasOwnProperty(i))
			{
				map.push(items[i].COUNT);
			}
		}

		return map;
	},

	arrowClick: function()
	{
		var node = this.nodes.rootFrom.querySelector('.catalog-preset-selected')
				|| this.nodes.rootFrom.querySelector('.catalog-preset-shem'),
			cloneNode;

		if (node)
		{
			cloneNode = this.getTemporaryNodeClone(node);

			this.nodes.rootTo.appendChild(cloneNode);
			this.dragdrop.addDragItem([cloneNode]);
			this.dragdrop.addSortableItem(cloneNode);

			this.saveData();
		}
	}
};