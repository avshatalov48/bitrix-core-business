var jsUtilsEffect = {
	Sequences:
	{
		linear: function(x) { return x },
		sinoidal: function(x) { return (-Math.cos(x*Math.PI)/2) + 0.5; },
		reverse: function(x) {return 1 - x;},
		beat: function(x, beats) {
			beats = parseInt(beats ? beats : 5);
			var amplitude = 2 * x * beats;
			var result = amplitude - Math.floor(amplitude);
			return (Math.round((x % (1/beats)) * beats) == 0 ? result : (1 - result));
		},
		none: function(x) {return 0;},
		full: function(x) {return 1;}
	},
	DefaultOptions:
	{
		duration: 1.0,
		frames_per_second: 100,
		delay: 0.0
	},
	Base: jsUtilsPhoto.ClassCreate(null, {
		position: null,
		start: function(options) {
			options = (options ? options : {});
			if (options['events'])
			{
				var res = {};
				for (var key in options['events'])
					this[key] = options['events'][key];

				for (var key in options)
					if (key != 'events') { res[key] = options[key]; }
				options = res;
			}

			this.options = jsUtilsPhoto.ObjectsMerge(jsUtilsEffect.DefaultOptions, options);
			if (!this.options.sequences)
				this.options.sequences = jsUtilsEffect.Sequences.linear;
			this.currentFrame = 0;

			this.totalTime = this.options.duration * 1000; // total moving duration
			this.totalFrames = this.options.frames_per_second * this.options.duration; // frames count

			this.state = 'stoped';
			this.startOn = this.options.delay * 1000; // relative moving time of start
			this.finishOn = this.startOn + this.totalTime; // relative moving time of finish
			this.controller = false;
			if (this.options['set_controller'] != false)
			{
				if (this.options['controller_name'])
					this.controller = (jsUtilsEffect.Controller[this.options['controller_name']] || new jsUtilsEffect.Controllers());
				else
					this.controller = jsUtilsEffect.Controller['default'];
				this.controller.add(this);
			}
		},
		render: function(pos)
		{
			if (this.state == "stoped")
			{
				this.state = 'running';
				this.event('BeforeSetup');
				this.setup();
				this.event('AfterSetup');
			}
			if (this.state == 'running')
			{
				this.position = this.options.sequences(pos);
				this.event('BeforeUpdate');
				this.update(this.position);
				this.event('AfterUpdate');
			}
		},
		loop: function(timePos)
		{
			if (timePos >= this.startOn)
			{
				if (timePos >= this.finishOn)
				{
					this.controller.remove(this);
					this.render(1.0);
					this.event('BeforeCancel');
					this.cancel();
					this.event('AfterCancel');
					this.event('BeforeFinish');
					this.finish();
					this.event('AfterFinish');
					return;
				}
				var pos = (timePos - this.startOn) / this.totalTime,
				frame = Math.round(pos * this.totalFrames); // frame number
				if (frame != this.currentFrame)
				{
					this.render(pos);
					this.currentFrame = frame;
				}
			}
		},
		cancel: function()
		{
			this.state = 'finished';
		},
		finish: function()
		{
			this.state = 'finished';
		},
		setup: function()
		{
		},
		update: function()
		{
		},
		event: function() {
			var EventName = arguments[0];
			if (this[EventName]) this[EventName](this, arguments);
			if (this.options[EventName]) this.options[EventName](this, arguments);
		}
	}),

	Controllers: jsUtilsPhoto.ClassCreate(null, {
		init: function()
		{
			this.effects = [];
			this.interval = null;
		},
		add: function(effect)
		{
			var timestamp = new Date().getTime();
			effect.startOn += timestamp; // start moving 
			effect.finishOn += timestamp; // finish moving

			this.effects.push(effect); // effects
			if (!this.interval) // run interval
			{
				var _this = this;
				this.interval = setInterval(function(){_this.check.apply(_this);}, 10);
			}
		},
		remove: function(effect)
		{
			var result = [];
			for (var ii in this.effects)
			{
				if (this.effects[ii] != effect)
					result.push(this.effects[ii]);
			}
			this.effects = result;

			if (this.effects.length == 0)
			{
				clearInterval(this.interval);
				this.interval = null;
			}
		},
		check: function()
		{
			var timePos = new Date().getTime();
			for (var ii = 0, length = this.effects.length; ii < length; ii++)
			{
				this.effects[ii] && this.effects[ii].loop(timePos);
			}
		}
	})
};
jsUtilsEffect.Controller = {
	'default' : new jsUtilsEffect.Controllers()};

jsUtilsEffect.Scale = jsUtilsPhoto.ClassCreate(jsUtilsEffect.Base, {
	init: function(element, percent)
	{
		this.className = 'Scale';
		if (!element) { return false; };
		this.element = element;
		var options = jsUtilsPhoto.ObjectsMerge({
			scaleX: true,
			scaleXFrom: 1.0,
			scaleXTo: (percent ? percent : 1.0),
			scaleY: true,
			scaleYFrom: 1.0,
			scaleYTo: (percent ? percent : 1.0)
		}, (arguments[2] || {}));
		this.start(options);
	},
	setup: function()
	{
		this.originParams = {'top': 0, 'left' : 0, 'width' : 0, 'height' : 0, 'position' : 0};
		for (var key in this.originParams)
			this.originParams[key] = this.element.style[key];

		this.originParams['offset_top'] = this.element.offsetTop;
		this.originParams['offset_left'] = this.element.offsetLeft;

		this.scaleDelta = [(this.options.scaleXTo - this.options.scaleXFrom), (this.options.scaleYTo - this.options.scaleYFrom)];
		this.dims = [this.element.offsetWidth, this.element.offsetHeight];

	},
	update: function(position) // position - it's % from final size
	{
		var currentScale = [
			(this.options.scaleXFrom + this.scaleDelta[0] * position),
			(this.options.scaleYFrom + this.scaleDelta[1] * position)];
		this.setDimensions(this.dims[0] * currentScale[0], this.dims[1] * currentScale[1]);
	},
	setDimensions: function(width, height)
	{
		var d = { };
		if (this.options.scaleX) d.width = Math.round(width) + 'px';
		if (this.options.scaleY) d.height = Math.round(height) + 'px';

		this.event('BeforeSetDimensions', d);
		for (var ii in d)
		{
			this.element.style[ii] = d[ii];
		}
		this.event('AfterSetDimensions');
	}
});

jsUtilsEffect.Transparency = jsUtilsPhoto.ClassCreate(jsUtilsEffect.Base, {
	init: function(element)
	{
		this.element = element;
		if (!this.element) { return false; };
		var options = jsUtilsPhoto.ObjectsMerge({
			from: 0.0,
			to: 1.0
		}, (arguments[1] || { }));

		this.start(options);
	},
	update: function(position)
	{
		position = (position == 1 || position === '' ? '' : (position < 0.0001 ? 0 : position));
		this.element.style.opacity = position;
		return this.element;
	},
	BeforeSetup: function()
	{
		this.element.style.opacity = 0;
		if (this.element.style.visibility == 'hidden')
			this.element.style.visibility = 'visible';
		if (this.element.style.display == 'none')
			this.element.style.display = '';
	}
}
);

jsUtilsEffect.Untwist = jsUtilsPhoto.ClassCreate(jsUtilsEffect.Base, {
	init: function(element)
	{
		this.className = 'Untwist';
		if (!element) { return false; };
		this.element = element;
		var options = jsUtilsPhoto.ObjectsMerge({
			scaleYFrom: 0.001,
			scaleYTo: 1.0
		}, (arguments[1] || {}));
		this.start(options);
	},
	setup: function()
	{
		var params = (jsUtilsPhoto.GetElementParams(this.element) || {});
		this.dims = [params['width'], params['height']];
		this.originParams = {'bottom': 0, 'top' : 0, 'left' : 0, 'width' : 0, 'height' : 0, 'position' : 0, 'overflow' : 0};
		for (var key in this.originParams)
			this.originParams[key] = this.element.style[key];

		this.scaleDelta = this.options.scaleYTo - this.options.scaleYFrom;
	},
	update: function(position) // position - it's % from final size
	{
		this.setDimensions(this.dims[1] * (this.options.scaleYFrom + this.scaleDelta * position));
	},
	setDimensions: function(height)
	{
		this.event('BeforeSetDimensions', height);
		this.element.style.height = Math.round(height) + 'px';;
		this.event('AfterSetDimensions');
	},
	finish: function(position) {
		for (var ii in this.originParams)
		{
			this.element.style[ii] = this.originParams[ii];
		}
	},
	AfterSetup: function(effect)
	{
		var res = this.element.style.position;
		if (!res || res == 'static')
		{
			this.element.__style_position = res;
			this.element.style.position = 'relative';
		}
		var res = this.element.style.overflow;
		if (res != 'hidden')
		{
			this.element.__style_overflow = res;
			this.element.style.overflow = 'hidden';
		}
		this.element.style.top = '';
		this.element.style.height = '0px';
		this.element.style.bottom = '0px';
		this.element.style.display = '';
		this.element.style.visibility = 'visible';
	},
	AfterFinish: function(effect)
	{
		if (this.element.__style_position)
		{
			this.element.style.position = this.element.style.top = this.element.style.left = this.element.style.bottom = this.element.style.right = '';
			this.element.__style_position = null;
		}
		if (this.element.__style_overflow)
		{
			this.element.style.overflow = this.element.__style_overflow;
			this.element.__style_overflow = null;
		}
	}
}
);
bPhotoEffectsLoad = true;