function initPositionControl(params)
{
	var data = JSON.parse(params.data);
	if (data)
	{
		window['pos_parameter_' + params.propertyID] = new PositionParameterControl(data, params);
	}
}

function PositionParameterControl(data, params)
{
	var rand = BX.util.getRandomString(5);

	this.params = params || {};
	this.positions = data.positions || {};
	this.parentClassName = data.className ? ' ' + data.className : '';
	this.selected = this.params.oInput.value || this.params.propertyParams.DEFAULT;
	this.id = 'pos_params_container_' + this.params.propertyID + '_' + rand;

	BX.loadCSS(this.getPath() + '/style.css?' + rand);
	this.buildNodes();
	this.saveData();
}

PositionParameterControl.prototype =
{
	getPath: function()
	{
		var path = this.params.propertyParams.JS_FILE.split('/');

		path.pop();

		return path.join('/');
	},

	buildNodes: function()
	{
		var nodes = [];

		for (var i in this.positions)
		{
			if (this.positions.hasOwnProperty(i))
			{
				nodes.push(
					BX.create('DIV', {
						attrs: {'data-value': this.positions[i]},
						props: {
							className: 'bx-pos-parameter bx-pos-parameter-' + this.positions[i]
							+ (this.positions[i] == this.selected ? ' selected' : '')
						},
						events: {click: BX.proxy(this.selectPosition, this)}
					})
				);
			}
		}

		this.params.oCont.appendChild(
			BX.create('DIV', {
				props: {className: 'bx-pos-parameter-container' + this.parentClassName},
				children: [
					BX.create('DIV', {children: nodes, props: {className: 'bx-pos-parameter-block'}}),
					BX.create('DIV', {props: {className: 'bx-pos-parameter-decore'}}),
					BX.create('DIV', {props: {className: 'bx-pos-parameter-decore'}}),
					BX.create('DIV', {props: {className: 'bx-pos-parameter-decore'}}),
					BX.create('DIV', {props: {className: 'bx-pos-parameter-decore'}}),
					BX.create('DIV', {props: {className: 'bx-pos-parameter-decore'}})
				]
			})
		);
	},

	selectPosition: function(event)
	{
		var target = BX.getEventTarget(event),
			items = this.params.oCont.querySelectorAll('.bx-pos-parameter'),
			value = target.getAttribute('data-value');

		if (this.selected == value)
			return;

		this.selected = value;

		for (var k in items)
		{
			if (items.hasOwnProperty(k))
			{
				if (items[k].getAttribute('data-value') == this.selected)
				{
					BX.addClass(items[k], 'selected');
				}
				else
				{
					BX.removeClass(items[k], 'selected');
				}
			}
		}

		this.saveData();
	},

	saveData: function()
	{
		this.params.oInput.value = this.selected;
	}
};