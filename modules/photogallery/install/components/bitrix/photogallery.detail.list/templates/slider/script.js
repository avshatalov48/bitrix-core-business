/********************************************************************
	BPCStretchSlider - stretched "resin" slider

	data - initial data
	active - active element in array
	count - count of items in slider (0 if unknown)
	position - position of first element in slider (1 by default)

********************************************************************/
function BPCStretchSlider(data, position, count, active)
{
	if (count <= 0 && data)
		count = data.length;
	else if (count <= 0 )
		return false;
	this.oSource = new BPCSourse(data, position, count);
	if (!this.oSource)
		return false;
	this.oSource.iForceElementCount = 10;
	this.active = this.oSource.iFirstNumber;
	this.params = {'height' : 250, 'width' : 250, 'first_created' : 0, 'last_created' : 0, 'step_size' : 100};
	this.events = {};
	for (var ii = this.oSource.iFirstNumber; ii < this.oSource.Data.length; ii++)
	{
		if (active == this.oSource.Data[ii]['id'])
			this.active = ii;
	}
	this.oSource.OnAfterItemAdd = function()
	{
		try
		{
			var element_id = arguments[0][1];
			var item_data = this.Data[element_id];
			var item_id = item_data["id"];
			var item = BX('item_' + item_id);
			if (!item)
			{
				var div = BX.create("DIV", {"props" : {"id" : "item_" + item_id}, "attrs" : {"class" : "photo-slider-item"},
					"html" : (
					'<table class="photo-slider-thumb" cellpadding="0">' +
						'<tr>' +
							'<td>' +
								'<a href="' + item_data['url'] + '">' +
									this.parentObject.CreateItem(element_id) +
								'</a>' +
							'</td>' +
						'</tr>' +
					'</table>')});

				if (element_id < this.iFirstNumber)
				{
					var pointer = BX('item_' + this.Data[this.iFirstNumber]["id"]);
					if (pointer)
						pointer.parentNode.insertBefore(div, pointer);
				}
				else
					this.parentObject.tape.appendChild(div);

				pos = BX.pos(div);
				this.parentObject.tape.__int_width += parseInt(pos['width']);
				if (element_id < this.iFirstNumber)
				{
					this.parentObject.tape.style.left = (parseInt(this.parentObject.tape.style.left) - parseInt(pos['width'])) + 'px';
					this.parentObject.prev.className = this.parentObject.prev.className.replace("-disabled", "-enabled").replace("-wait", "-enabled");
				}
				else
				{
					this.parentObject.next.className = this.parentObject.next.className.replace("-disabled", "-enabled").replace("-wait", "-enabled");
				}
			}
		}
		catch (e) { }
	}
	this.oSource.OnBeforeSendData = function()
	{
		arguments[0][1]['package_id'] = this.parentObject.pack_id;
		return arguments[0][1];
	}

	return true;
}

BPCStretchSlider.prototype = {
	/**
		CreateSlider - create slider and set carret to the active item
	*/
	CreateSlider: function()
	{
		this.checkEvent('OnBeforeSliderCreate');
		this.params['first_created'] = this.params['last_created'] = this.oSource.iFirstNumber;
		for (var item_id = this.oSource.iFirstNumber; item_id < this.oSource.Data.length; item_id++)
		{
			this.params['last_created'] = item_id;
			var res = this.oSource.checkItem(item_id);
			if (!res || res == 'wait')
				return res;
			this.MakeItem(item_id, (this.active == item_id));
		}
		this.checkEvent('OnAfterSliderCreate');
		return true;
	},

	/**
		MakeItem create the item
		item_id - item id 
		number - serial number in slider
	*/
	MakeItem: function(item_id, active_id)
	{
		this.checkEvent('OnBeforeItemMake', item_id, active_id);
		this.ShowItem(item_id, active_id);
		this.checkEvent('OnAfterItemMake', item_id, active_id);
	},

	/**
		ShowItem - show the element, this method have to be redefined in template.php
	*/
	ShowItem: function(item_id, active_id)
	{
	},

	/**
		CreateItem - create dom item
		Returns: object
	*/
	CreateItem: function(item_id)
	{
		var koeff = Math.min(this.params['width']/this.oSource.Data[item_id]['width'], this.params['height']/this.oSource.Data[item_id]['height']);
		var res = {'width' : this.oSource.Data[item_id]['width'], 'height' : this.oSource.Data[item_id]['height']};
		if (koeff < 1)
		{
			res['width'] = parseInt(this.oSource.Data[item_id]['width']*koeff);
			res['height'] = parseInt(this.oSource.Data[item_id]['height']*koeff);
		}

		var image = new Image();
		image.src = this.oSource.Data[item_id]['src'];
		return '<img id="image_' + item_id + '" border="0" ' +
			'onload="window[\'_slider_' + this.pack_id + '\'].oSource.Data[this.id.replace(\'image_\', \'\')][\'loaded\'] = true; window[\'_slider_' + this.pack_id + '\'].checkEvent(\'OnAfterItemLoad\', this);" ' +
			'style="width:' + res['width'] + 'px;height:' + res['height'] + 'px;" ' +
			'title="' + this.oSource.Data[item_id]['title'] + '" alt="' + this.oSource.Data[item_id]['title'] + '" ' +
			'src="' + this.oSource.Data[item_id]['src'] + '" />';
	},

	/**
		GoToNext - go to the next photo
		Returns: true || false || 'wait'
	*/
	GoToNext: function()
	{
		var pos_window = BX.pos(this.window);
		var tape_right_width = parseInt(this.tape.__int_width) + parseInt(this.tape.style.left) - pos_window['width'];

		var leftward = (tape_right_width > this.params['step_size'] ? this.params['step_size'] : tape_right_width);
		if (leftward > 0)
		{
			this.tape.style.left = parseInt(parseInt(this.tape.style.left) - leftward - 5) + 'px';
			this.prev.className = this.prev.className.replace("-disabled", "-enabled").replace("-wait", "-enabled");
		}

		if (this.oSource.Data.length <= this.oSource.iCountData && tape_right_width <= this.params['step_size'] * 10)
			this.oSource.getData(this.oSource.Data.length, true);

		if (tape_right_width > this.params['step_size'])
			this.next.className = this.next.className.replace("-disabled", "-enabled").replace("-wait", "-enabled");
		else if (this.oSource.busy === true || this.oSource.Data.length < this.oSource.iCountData)
			this.next.className = this.next.className.replace("-enabled", "-wait").replace("-disabled", "-wait");
		else
			this.next.className = this.next.className.replace("-enabled", "-disabled").replace("-wait", "-disabled");

		return true;
	},

	/**
		GoToPrev - go to the previous photo
		Returns: true || false || 'wait'
	*/
	GoToPrev: function()
	{
		var tape_left_width = parseInt(this.tape.style.left) * (-1);
		var rightward = (tape_left_width > this.params['step_size'] ? this.params['step_size'] : tape_left_width);

		if (rightward > 0)
		{
			this.tape.style.left = parseInt(parseInt(this.tape.style.left) + rightward) + 'px';
			var pos_window = BX.pos(this.window);
			var tape_right_width = parseInt(this.tape.__int_width) + parseInt(this.tape.style.left) - pos_window['width'];
			if (tape_right_width > 0)
				this.next.className = this.next.className.replace("-disabled", "-enabled").replace("-wait", "-enabled");
		}

		if (this.oSource.iFirstNumber > 1 && rightward <= this.params['step_size'] * 10)
			this.oSource.getData(this.oSource.iFirstNumber, false);

		if (tape_left_width > this.params['step_size'])
			this.prev.className = this.prev.className.replace("-disabled", "-enabled").replace("-wait", "-enabled");
		else if (this.oSource.busy === true || this.oSource.iFirstNumber > 1)
			this.prev.className = this.prev.className.replace("-enabled", "-wait").replace("-disabled", "-wait");
		else
			this.prev.className = this.prev.className.replace("-enabled", "-disabled").replace("-wait", "-disabled");

		return true;
	},

	checkEvent: function()
	{
		eventName = arguments[0];
		if (this.events[eventName]) { return this.events[eventName](arguments); }
		if (this[eventName]) {return this[eventName](arguments); }
		return true;
	},

	OnBeforeSliderCreate: function(image)
	{
		window['_slider_' + this.pack_id] = this;

		this.prev = BX('prev_' + this.pack_id);
		this.next = BX('next_' + this.pack_id);
		this.window = BX('slider_window_' + this.pack_id);
		this.tape = this.window.firstChild;
		this.oSource.parentObject = this;
		this.__leftward = 0;
		this.__width = 0;
		this.__active_element_founded = false;

		if (this.window.addEventListener)
			this.window.addEventListener('DOMMouseScroll', BX.proxy(this.OnMouseWheel, this), false);

		BX.bind(this.window, 'mousewheel', BX.proxy(this.OnMouseWheel, this));
	},

	OnMouseWheel: function(event)
	{
		if (!event)
			event = window.event;

		var wheelDelta = 0;

		if (event.wheelDelta)
			wheelDelta = event.wheelDelta / 120;
		else if (event.detail)
			wheelDelta = -event.detail / 3;
		BX.PreventDefault(event);
		var steps = (wheelDelta > 0 ? wheelDelta : wheelDelta * (-1));
		for (var ii = 1; ii <= steps; ii++)
		{
			if (wheelDelta < 0)
				this.GoToNext();
			else
				this.GoToPrev();
		}
	},

	OnAfterSliderCreate: function()
	{
		this.tape.style.left = (this.__leftward > 0 ? ('-' + this.__leftward + 'px') : '0px');
		this.tape.__int_width = this.__width;

		delete this.__leftward;
		delete this.__width;
		delete this.__active_element_founded;

		BX.bind(this.next, 'click', BX.proxy(this.GoToNext, this));
		BX.bind(this.prev, 'click', BX.proxy(this.GoToPrev, this));
	},

	OnAfterItemMake: function()
	{
		arguments = arguments[0];
		var item_id = arguments[1];
		var is_active = (arguments[2] === false || arguments[2] === true ? arguments[2] : (arguments[2] === 'false' ? false : (arguments[2] === 'true' ? true : null)));

		var item = BX('item_' + this.oSource.Data[item_id]['id']);
		var pos = BX.pos(item);

		this.__width += parseInt(pos['width']);
		if (!this.__active_element_founded && (is_active === false || is_active === true))
		{
			if (is_active === false)
			{
				this.__leftward += parseInt(pos['width']);
			}
			else
			{
				this.__active_element_founded = true;
			}
		}
	}
};
window.bPhotoSliderStretchLoad = true;