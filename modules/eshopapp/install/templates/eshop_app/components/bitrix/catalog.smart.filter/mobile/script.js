function JCSmartFilter(ajaxURL)
{
	this.ajaxURL = ajaxURL;
	this.form = null;
	this.timer = null;
}

JCSmartFilter.prototype.keyup = function(input)
{
	if(this.timer)
		clearTimeout(this.timer);
	this.timer = setTimeout(BX.delegate(function(){
		this.reload(input);
	}, this), 1000);
}

JCSmartFilter.prototype.click = function(checkbox)
{
	if(this.timer)
		clearTimeout(this.timer);
	this.timer = setTimeout(BX.delegate(function(){
		this.reload(checkbox);
	}, this), 1000);
}

JCSmartFilter.prototype.reload = function(input)
{
	this.position = BX.pos(input, true);
	this.form = BX.findParent(input, {'tag':'form'});
	if(this.form)
	{
		var values = new Array;
		values[0] = {name: 'ajax', value: 'y'};
		this.gatherInputsValues(values, BX.findChildren(this.form, {'tag':'input'}, true));
		BX.ajax.loadJSON(
			this.ajaxURL,
			this.values2post(values),
			BX.delegate(this.postHandler, this)
		);
	}
}

JCSmartFilter.prototype.postHandler = function (result)
{
	if(result.ITEMS)
	{
		for(var PID in result.ITEMS)
		{
			var arItem = result.ITEMS[PID];
			if(arItem.PROPERTY_TYPE == 'N' || arItem.PRICE)
			{
			}
			else if(arItem.VALUES)
			{
				for(var i in arItem.VALUES)
				{
					var ar = arItem.VALUES[i];
					var control = BX(ar.CONTROL_ID);

					if(control)
					{
						if (ar.CHECKED)
						{
							control.parentNode.className = 'checked';
						}
						else if (ar.DISABLED)
						{
							control.parentNode.className = 'disable';
						}
						else
						{
							control.parentNode.className = '';
						}
				//		control.parentNode.className = ar.DISABLED? 'lvl2 lvl2_disabled': 'lvl2';
					}
				}
			}
		}
		var modef = BX('modef');
		var modef_num = BX('modef_num');
		if(modef && modef_num)
		{
			modef_num.innerHTML = result.ELEMENT_COUNT;
			var hrefFILTER = BX.findChildren(modef, {tag: 'A'}, true);
			if(result.FILTER_URL && hrefFILTER)
				hrefFILTER[0].href = BX.util.htmlspecialcharsback(result.FILTER_URL);
			/*if(result.FILTER_AJAX_URL)
				BX.bind(hrefFILTER[0], 'click', function(e){
					var url = BX.util.htmlspecialcharsback(result.FILTER_AJAX_URL);
					BX.ajax.insertToNode(url, result.COMPONENT_CONTAINER_ID);
					return BX.PreventDefault(e);
				});     */
			if(modef.style.display == 'none')
				modef.style.display = 'block';
		//	modef.style.top = this.position.top + 'px';
		}
	}
}

JCSmartFilter.prototype.gatherInputsValues = function (values, elements)
{
	if(elements)
	{
		for(var i = 0; i < elements.length; i++)
		{
			var el = elements[i];
			if (el.disabled || !el.type)
				continue;

			switch(el.type.toLowerCase())
			{
				case 'text':
				case 'textarea':
				case 'password':
				case 'hidden':
				case 'select-one':
					if(el.value.length)
						values[values.length] = {name : el.name, value : el.value};
					break;
				case 'radio':
				case 'checkbox':
					if(el.checked)
						values[values.length] = {name : el.name, value : el.value};
					break;
				case 'select-multiple':
					for (var j = 0; j < el.options.length; j++)
					{
						if (el.options[j].selected)
							values[values.length] = {name : el.name, value : el.options[j].value};
					}
					break;
				default:
					break;
			}
		}
	}
}

JCSmartFilter.prototype.values2post = function (values)
{
	var post = new Array;
	var current = post;
	var i = 0;
	while(i < values.length)
	{
		var p = values[i].name.indexOf('[');
		if(p == -1)
		{
			current[values[i].name] = values[i].value;
			current = post;
			i++;
		}
		else
		{
			var name = values[i].name.substring(0, p);
			var rest = values[i].name.substring(p+1);
			if(!current[name])
				current[name] = new Array;

			var pp = rest.indexOf(']');
			if(pp == -1)
			{
				//Error - not balanced brackets
				current = post;
				i++;
			}
			else if(pp == 0)
			{
				//No index specified - so take the next integer
				current = current[name];
				values[i].name = '' + current.length;
			}
			else
			{
				//Now index name becomes and name and we go deeper into the array
				current = current[name];
				values[i].name = rest.substring(0, pp) + rest.substring(pp+1);
			}
		}
	}
	return post;
}

function touchTrackBar(Track, Tracker, Left, Right, Settings)
{
	switch(typeof Track){
		case 'string': this.Track = document.getElementById(Track); break;
		case 'object': this.Track = Track; break;
	}
	switch(typeof Tracker){
		case 'string': this.Tracker = document.getElementById(Tracker); break;
		case 'object': this.Tracker = Tracker; break;
	}
	switch(typeof Left){
		case 'string': this.Left = document.getElementById(Left); break;
		case 'object': this.Left = Left; break;
	}
	switch(typeof Right){
		case 'string': this.Right = document.getElementById(Right); break;
		case 'object': this.Right = Right; break;
	}
	if (!Track || !Tracker)
		return false;

	this.MinPrice = Settings.MinPrice || 0;
	this.MaxPrice = Settings.MaxPrice || 1000;
	this.CurMinPrice = Settings.CurMinPrice || 0;
	this.CurMaxPrice = Settings.CurMaxPrice || 1000;
	this.MinInputId = Settings.MinInputId || 0;
	this.MaxInputId = Settings.MaxInputId || 1000;
}

touchTrackBar.prototype.touchmoveleft =  function (event)
{
	event.preventDefault();

	drag_width = this.Track.clientWidth;
	xbox = this.Left.offsetWidth  / 2;
	var koef = ((this.MaxPrice - this.MinPrice)/drag_width).toFixed(4);
	var percent_koef = drag_width/100;

	if (event.targetTouches[0].pageX  < this.Right.offsetLeft)
	{
		this.Left.style.left = event.targetTouches[0].pageX/percent_koef + "%";
		this.Tracker.style.left = event.targetTouches[0].pageX/percent_koef + "%";
		this.Tracker.style.width =  (this.Right.offsetLeft + xbox - event.targetTouches[0].pageX)/percent_koef + "%";

		var curMinPrice = Math.round(this.MinPrice+(event.targetTouches[0].pageX*koef));
		this.MinInputId.value = curMinPrice;
		this.CurMinPrice.innerHTML = curMinPrice;
		smartFilter.keyup(this.MinInputId);
	}
}

touchTrackBar.prototype.touchmoveright =  function (event)
{
	event.preventDefault();

	drag_width = this.Track.clientWidth;
	xbox = this.Left.offsetWidth  / 2;
	var koef = ((this.MaxPrice - this.MinPrice)/drag_width).toFixed(4);
	var percent_koef = drag_width/100;

	if (event.targetTouches[0].pageX <= drag_width && event.targetTouches[0].pageX > (this.Left.offsetLeft + this.Left.clientWidth))
	{
		this.Tracker.style.width = (event.targetTouches[0].pageX - this.Left.offsetLeft - xbox)/percent_koef + "%";
		this.Right.style.left =  event.targetTouches[0].pageX/percent_koef + '%';
		var curMaxPrice = Math.round(this.MinPrice+(event.targetTouches[0].pageX*koef));
		this.MaxInputId.value = curMaxPrice;
		this.CurMaxPrice.innerHTML = curMaxPrice;
		smartFilter.keyup(this.MaxInputId);
	}
}
touchTrackBar.prototype.startPosition =  function ()
{
	var drag_width = this.Tracker.clientWidth;
	var curMinPrice = this.MinInputId.value || 0;
	var curMaxPrice = this.MaxInputId.value || 0;
	if (curMinPrice || curMaxPrice)
	{
		if (!curMinPrice)
			curMinPrice = this.MinPrice;
		if (!curMaxPrice)
			curMaxPrice = this.MaxPrice;

		var koef = ((this.MaxPrice - this.MinPrice)/drag_width).toFixed(4);
		var percent_koef = drag_width/100;
		if (curMinPrice)
			var curLeft = Math.round((curMinPrice - this.MinPrice)/koef);
		if (curMaxPrice)
			var curWidth = Math.round((curMaxPrice - this.MinPrice)/koef);
		this.Left.style.left = curLeft/percent_koef + "%";
		this.Tracker.style.left = curLeft/percent_koef + "%";
		this.Tracker.style.width = (curWidth - curLeft)/percent_koef + "%";
		this.Right.style.left = curWidth/percent_koef  + "%";
	}
}