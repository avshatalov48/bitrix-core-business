/**
	BPCSourse - handle source of the data

	data - initial set of the data
	id - first element number in slider (default - 0)
	count - number of elements in slider
*/
function BPCSourse(data, id, count)
{
	id = parseInt(id);
	id = (id > 0 ? id : 1);
	count = parseInt(count);
	this.Data = new Array(id); // format: "serial number" => data

	this.iCountData = (count > 0 ? count : 0); // Max element count
	this.iFirstNumber = id; // First element id in loaded area
	this.iForceElementCount = 5; // count of boundary non void elements
	this.loaded = false; // load indicator
	this.arParams = {attempt : {}};
	this.events = {};

//	try {
		for (var ii = 0; ii < data.length; ii++)
		{
			if (this.checkEvent('OnBeforeItemAdd', data[ii], this.Data.length))
			{
				this.Data.push(data[ii]);
				this.checkEvent('OnAfterItemAdd', (this.Data.length - 1));
			}
		}
		this.loaded = true;
//	} catch (e){}

	this.url = false;
	return this.loaded;
}

BPCSourse.prototype = {
	/**
	iLastNumber - serial number in array
	bDirection: true - RIGHT, false - LEFT)
	Returns: false | 'wait' | data
	*/
	getData: function(iLastNumber, bDirection)
	{
		if (!this.loaded)
			return false;
		bDirection = !!bDirection; // true = next, false = prev
		iLastNumber = parseInt(iLastNumber);

		if (iLastNumber < 1)
			return false;
		else if (this.iCountData > 0 && iLastNumber > this.iCountData)
			return false;

		if (bDirection && (iLastNumber < this.Data.length))
		{
			if ((this.Data.length - iLastNumber) < this.iForceElementCount)
				this.checkData(bDirection);
			return this.Data.slice(iLastNumber);
		}
		else if (!bDirection && this.iFirstNumber < iLastNumber)
		{
			if ((iLastNumber - this.iFirstNumber) < this.iForceElementCount)
				this.checkData(bDirection);
			return this.Data.slice(this.iFirstNumber, iLastNumber);
		}

		return this.checkData(bDirection);
	},

	/**
		checkData - Check Data. If don't find any data - request them
		bDirection - navigation (true - right, false - left)
		Returns: false | 'wait' | true
	*/
	checkData: function(bDirection)
	{
		bDirection = !!bDirection;
		if (!this.loaded)
			return false;

		if (this.busy == true)
			return 'wait';

		if ((bDirection && this.iCountData > 0 && (this.Data.length - 1) >= this.iCountData) ||
			(!bDirection && this.iFirstNumber <= 1))
			return true;

		if (this.busy != true && !this.checkSendData(bDirection) && this.busy != true && typeof this.Data[this.iFirstNumber - 1] == 'object')
		{
			this.addData(bDirection, (bDirection ?
				'{"status" : "end"}' :
				'{"start_number" : ' + (this.iFirstNumber - 1) + ', "elements" : ' +
				'{"src" : "/bitrix/components/bitrix/photogallery.detail.list/templates/slide_show/images/error.gif"}}')
			);
		}

		__this_source = this;
		setTimeout(new Function("__this_source.sendData(" + (bDirection ? "true" : "false") + ")"), 100);
		return 'wait';
	},

	/**
		checkSendData - check number of requests with the same param for prevent overrequestion the server
		bDirection - navigation (true - right, false - left)
		Returns: false | true
	*/
	checkSendData: function(bDirection)
	{
		if (this.busy == true)
			return false;
		bDirection = !!bDirection;
		var res = (bDirection ? 'next:' + this.Data.length : 'prev:' + this.Data.length);
		this.arParams['attempt'][res] = (this.arParams['attempt'][res] ? this.arParams['attempt'][res] : 0);
		if (parseInt(this.arParams['attempt'][res]) > 20)
			return false;
		this.arParams['attempt'][res]++;
		return true;
	},

	/**
		sendData - Query for data
		bDirection - navigation (true - right, false - left)
		Returns: false | 'wait' | true
	*/
	sendData: function(bDirection)
	{
		if (this.busy == true)
			return 'wait';

		if (typeof this.Data[this.iFirstNumber] != 'object')
			return false;

		this.busy = true;
		BX.showWait();

		bDirection = !!bDirection;
		var current = (bDirection ? this.Data.slice(-1) : this.Data.slice(this.iFirstNumber, this.iFirstNumber + 1));
		var url;

		if (!this.url)
			url = window.location.href;

		url = url.replace(/PAGEN\_([\d]+)\=([\d]+)/gi, '').replace(/\#(.*)/gi, '');

		var res = {
			current : {id : current[0]['id']},
			return_array : 'Y',
			direction : (bDirection ? 'next' : 'prev'),
			ELEMENT_ID : current[0]['id']
		};

		var result_events = this.checkEvent('OnBeforeSendData', res);
		if (result_events === false)
			return false;
		else if (typeof result_events == "object")
			res = result_events;
		var TID = jsAjax.InitThread();
		__this_source = this;
		eval("jsAjax.AddAction(TID, function(data){__this_source.addData(" + (bDirection ? "true" : "false") + ", data);});");

		jsAjax.Send(TID, url, res);
	},

	/**
		addData - add loaded data to array
		bDirection - navigation (true - right, false - left)
	*/
	addData: function(bDirection, data)
	{
		bDirection = !!bDirection;

		try
		{
			eval("var result=" + data + ";");
			result['start_number'] = parseInt(result['start_number']);
			if (result['start_number'] > 0)
			{
				if (result['elements'] && result['elements'].length > 0)
				{
					if (this.Data.length < result['start_number'])
					{
						var res = this.Data.length;
						for (var ii = res; ii < result['start_number']; ii++)
							this.Data[ii] = false;
					}
					for (var ii = 0; ii < result['elements'].length; ii++)
					{
						var jj = result['start_number'] + ii;
						if ((!this.Data[jj] || this.Data[jj] == null) && this.checkEvent('OnBeforeItemAdd', result['elements'][ii], jj))
						{
							this.Data[jj] = result['elements'][ii];
							this.checkEvent('OnAfterItemAdd', jj);
						}
					}
				}
				if (result['start_number'] < this.iFirstNumber)
					this.iFirstNumber = result['start_number'];
			}

			if (result['start_number'] <= 0 || !(result['elements'] && result['elements'].length > 0) ||
				result['status'] == 'end')
			{
				this.iCountData = (this.Data.length - 1);
			}
		}
		catch (e) {}
		this.checkEvent('OnAfterSendData');

		this.busy = false;
		BX.closeWait();
	},

	/**
		checkItem - check item for correct info
		item_id - item id
		bDirection - navigation (true - right, false - left)
		Returns: false | 'wait' | data
	*/
	checkItem: function(item_id, bDirection)
	{
		return true;
	},

	checkEvent: function()
	{
		eventName = arguments[0];
		if (this.events[eventName])
			return this.events[eventName](arguments);
		if (this[eventName])
			return this[eventName](arguments);
		return true;
	}
}

/********************************************************************
	BPCSlider - slider class

	data - initial data
	active - active element in array
	count - count of items in slider (0 if unknown)
	position - position of first element in slider (1 by default)

********************************************************************/
function BPCSlider(data, active, count, position)
{
	if (count <= 0)
		return false;
	this.oSource = new BPCSourse(data, position, count);
	if (!this.oSource)
		return false;
	this.windowsize = 1;
	this.oSource.iForceElementCount = this.windowsize * 3;
	this.active = this.oSource.iFirstNumber;
	this.item_params = {'width' : 800, 'height' : 600};
	this.events = {};
	for (var ii = this.oSource.iFirstNumber; ii < this.oSource.Data.length; ii++)
	{
		if (active == this.oSource.Data[ii]['id'])
			this.active = ii;
	}
}


BPCSlider.prototype = {
	/**
		ShowSlider - initializing of slider
		Returns: true || false || 'wait'
	*/
	ShowSlider: function(data)
	{
		for (var ii = 1; ii <= this.windowsize; ii++)
		{
			var item_id = this.active - 1 + ii;

			if (!this.oSource.Data[item_id])
			{
				var res = this.oSource.checkItem(item_id);
				if (!res || res == 'wait')
					return res;
			}

			if (!this.oSource.Data[item_id] || (this.oSource.Data[item_id]['loaded'] != true && !this.checkEvent('OnBeforeItemShow', item_id)))
				return 'wait';
		}

		for (var ii = 0; ii < this.windowsize; ii++)
			this.MakeItem(this.active + ii, (ii + 1));

		return true;
	},

	/**
		MakeItem create the item
		item_id - item id
	 	number - serial number in slider
	*/
	MakeItem: function(item_id, number)
	{
		this.checkEvent('OnBeforeItemShow', item_id);
		this.ShowItem(item_id, number);
	},

	/**
		ShowItem - show the element, this method have to be redefined in template.php
	*/
	ShowItem: function(item_id, number)
	{
	},

	/**
		CreateItem - create dom item
		Returns: object
	*/
	CreateItem: function(item_id)
	{
		var koeff = Math.min(this.item_params['width']/this.oSource.Data[item_id]['width'], this.item_params['height']/this.oSource.Data[item_id]['height']);
		var res = {'width' : this.oSource.Data[item_id]['width'], 'height' : this.oSource.Data[item_id]['height']};
		if (koeff < 1)
		{
			res['width'] = parseInt(this.oSource.Data[item_id]['width']*koeff);
			res['height'] = parseInt(this.oSource.Data[item_id]['height']*koeff);
		}

		var div = BX.create('DIV', {props: {className: "bx-slider-image-container", id: this.oSource.Data[item_id]['id']}, style: {overflow: 'hidden', width: res['width'] + "px", height: res['height'] + "px"}});

		var image = new Image();
		image.id = 'image_' + item_id;
		__this_slider = this;
		image.onload = function()
		{
			__this_slider.oSource.Data[this.id.replace('image_', '')]['loaded'] = true;
			__this_slider.checkEvent('OnAfterItemLoad', this);
		};
		image.style.width = res['width'] + "px";
		image.style.height = res['height'] + "px";
		image.title = image.alt = this.oSource.Data[item_id]['title'];
		div.appendChild(image);
		image.src = this.oSource.Data[item_id]['src'];
		return div;
	},

	/**
		PreloadItems - preloading the photos
		item_id - id of the item to preload
	*/
	PreloadItems: function(item_id)
	{
		item_id = parseInt(item_id);
		var images = new Array();
		var res = [(item_id - 1), (item_id + 1)];
		for (var jj in res)
		{
			var ii = res[jj];
			if (this.oSource.Data[ii] && !this.oSource.Data[ii]['loaded'])
			{
				images[ii] = new Image();
				images[ii].id = 'preload_image_' + ii;
				images[ii].onload = function(){ __this_slider.oSource.Data[this.id.replace('preload_image_', '')]['loaded'] = true; };
				images[ii].src = this.oSource.Data[ii]['src'];
			}
		}
		return true;
	},

	/**
		GoToNext - go to the next photo
		Returns: true || false || 'wait'
	*/
	GoToNext: function()
	{
		res = this.oSource.getData((this.active + this.windowsize), true);
		if (!res || res == 'wait')
			return res;
		this.active++;
		return true;
	},

	/**
		GoToLast - go to the last photo
		Returns: true || false || 'wait'
	*/
	GoToLast: function()
	{
		res = this.oSource.getData((this.oSource.iCountData - this.windowsize + 1), true);
		if (!res || res == 'wait')
			return res;
		this.active = (this.oSource.iCountData - this.windowsize + 1);
		return true;
	},

	/**
		GoToPrev - go to the previous photo
		Returns: true || false || 'wait'
	*/
	GoToPrev: function()
	{
		res = this.oSource.getData((this.active - 1), false);
		if (!res || res == 'wait')
		{
			return res;
		}
		this.active--;
		return true;
	},

	/**
		GoToFirst - set carret to the start

		Returns: true || false || 'wait'
	*/
	GoToFirst: function()
	{
		res = this.oSource.getData(1, false);
		if (!res || res == 'wait')
			return res;
		this.active = 1;
		return true;
	},

	checkEvent: function()
	{
		eventName = arguments[0];
		if (this.events[eventName]) { return this.events[eventName](arguments); }
		if (this[eventName]) {return this[eventName](arguments); }
		return true;
	}
};

/********************************************************************
	BPCPlayer - slidePlayer class
	oSlider - slider object
********************************************************************/
BPCPlayer = function(oSlider)
{
	if (!oSlider)
		return false;
	this.oSlider = oSlider;
	this.events = {};
	this.params = {period : 5, status : 'paused'};
};

BPCPlayer.prototype = {
	/**
		step - do one step (next, back, to the end, to the start)
		Returns: false || 'wait' || data
	*/
	step: function(name_step)
	{
		var res = '';
		this.stop();

		if (name_step == 'next')
		{
			res = this.oSlider.GoToNext();
			if (!res)
				res = this.oSlider.GoToFirst();
		}
		else if (name_step == 'prev')
		{
			res = this.oSlider.GoToPrev();
			if (!res)
				res = this.oSlider.GoToLast();
		}
		else if (name_step == 'last')
		{
			res = this.oSlider.GoToLast();
		}
		else
		{
			res = this.oSlider.GoToFirst();
		}

		if (res == 'wait')
		{
			this.checkEvent('OnWaitItem');
			__this_player = this;
			setTimeout(new Function("__this_player.step('" + name_step + "');"), 200);
		}
		else if (res != false)
		{
			this.checkEvent('OnShowItem');
			this.oSlider.ShowSlider();
		}
		return res;
	},

	/**
		play - plays slide show
		Returns: false || 'wait' || data
	*/
	play: function(status, bPlayAgain)
	{
		var bPlayAgain = bPlayAgain || this.params['status'] == 'paused';
		status = !!status;
		player.params['status'] = 'play';
		this.checkEvent('OnStartPlay');

		if (this.params['period'] <= 0 || this.params['status'] != 'play')
			return this.stop();
			
		var _this = this;

		// Timeout on the first step
		if (!status)
		{
			this.PlayTimeout = setTimeout(function(){_this.play(true, !!bPlayAgain);}, this.params['period'] * 1000);
		}
		else
		{
			var res = this.oSlider.GoToNext();
			if (res == false)
			{
				// If user press play in the end of the slide show - go to the start
				if (bPlayAgain)
				{
					if (this.step('next'))
						this.play();
					return;
				}
				// Stop the slide-show
				else
				{
					return this.stop();
				}
			}
			// If data not loaded yet - wait for timeout
			else if (res == 'wait')
			{
				this.checkEvent('OnWaitItem');
				setTimeout(function(){_this.play(true);}, 200);
			}
			else
			{
				this.checkEvent('OnShowItem');
				// photo showed - go to the next step
				if (this.oSlider.ShowSlider({'slideshow' : true}))
				{
					this.PlayTimeout = setTimeout(function(){_this.play(true);}, this.params['period'] * 1000);
				}
				// waiting for photo loading
				else
				{
					this.oSlider.GoToPrev();
					setTimeout(function(){_this.play(true);}, 200);
				}
			}
			return res;
		}
	},

	stop: function()
	{
		this.params['status'] = 'paused';
		if (this.PlayTimeout)
			clearTimeout(this.PlayTimeout);
		this.checkEvent('OnStopPlay');
	},

	checkEvent: function()
	{
		eventName = arguments[0];
		if (this.events[eventName]) { return this.events[eventName](arguments); }
		if (this[eventName]) { return this[eventName](arguments); }
		return true;
	},

	PlayStop: function()
	{
		if (player.params['status'] == 'paused')
			player.play();
		else
			player.stop();
	},

	checkKeyPress: function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 39)
			__this_player.step('next');
		else if(e.keyCode == 37)
			__this_player.step('prev');
	}
};

window.bPhotoPlayerLoad = true;