function initDraggableOrderControl(params)
{
	var data = JSON.parse(params.data);
	if (data)
	{
		BX.loadScript('/bitrix/js/main/core/core_dragdrop.js', function(){
			(function bx_dnd_order_waiter(){
				if (!!BX.DragDrop)
					window['dnd_parameter_' + params.propertyID] = new DragNDropOrderParameterControl(data, params);
				else
					setTimeout(bx_dnd_order_waiter, 50);
			})();
		});
	}
}

function DragNDropOrderParameterControl(items, params)
{
	var rand = BX.util.getRandomString(5);

	this.params = params || {};
	this.items = this.getSortedItems(items);

	this.rootElementId = 'dnd_params_container_' + this.params.propertyID + '_' + rand;
	this.dragItemClassName = 'dnd-order-draggable-item-' + this.params.propertyID + '-' + rand;

	BX.loadCSS(this.getPath() + '/style.css?' + rand);
	this.buildNodes();
	this.initDragDrop();
}

DragNDropOrderParameterControl.prototype =
{
	getPath: function()
	{
		var path = this.params.propertyParams.JS_FILE.split('/');

		path.pop();

		return path.join('/');
	},

	getSortedItems: function(items)
	{
		if (!items)
			return [];

		var inputValue = this.params.oInput.value || this.params.propertyParams.DEFAULT || '',
			result = [],
			k;

		var values = inputValue.split(',');
		for (k in values)
		{
			if (values.hasOwnProperty(k))
			{
				values[k] = BX.util.trim(values[k]);
				if (items[values[k]])
				{
					result.push({
						value: values[k],
						message: items[values[k]]
					});
				}
			}
		}

		for (k in items)
		{
			if (items.hasOwnProperty(k) && !BX.util.in_array(k, values))
			{
				result.push({
					value: k,
					message: items[k]
				});
			}
		}

		return result;
	},

	buildNodes: function()
	{
		var baseNode = BX.create('DIV', {
			props: {className: 'dnd-order-draggable-control-container', id: this.rootElementId}
		});

		for (var k in this.items)
		{
			if (this.items.hasOwnProperty(k))
			{
				baseNode.appendChild(
					BX.create('DIV', {
						attrs: {'data-value': this.items[k].value},
						props: {
							className: 'dnd-order-draggable-control dnd-order-draggable-item ' + this.dragItemClassName
						},
						text: this.items[k].message
					})
				);
			}
		}

		this.params.oCont.appendChild(baseNode);
	},

	initDragDrop: function()
	{
		if (BX.isNodeInDom(this.params.oCont))
		{
			this.dragdrop = BX.DragDrop.create({
				dragItemClassName: this.dragItemClassName,
				dragItemControlClassName: 'dnd-order-draggable-control',
				sortable: {rootElem: BX(this.rootElementId)},
				dragEnd: BX.delegate(function(eventObj, dragElement, event){
					this.saveData();
				}, this)
			});
		}
		else
		{
			setTimeout(BX.delegate(this.initDragDrop, this), 50);
		}
	},

	saveData: function()
	{
		var items = this.params.oCont.querySelectorAll('.' + this.dragItemClassName),
			arr = [];

		for (var k in items)
		{
			if (items.hasOwnProperty(k))
			{
				arr.push(items[k].getAttribute('data-value'));
			}
		}

		this.params.oInput.value = arr.join(',');
	}
};