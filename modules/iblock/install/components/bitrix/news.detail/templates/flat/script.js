(function (window)
{
	if (!!window.JCNewsSlider)
	{
		return;
	}

	window.JCNewsSlider = function (containerId, options)
	{
		if (containerId && options)
		{
			var container = BX(containerId);
			if (container)
			{
				var sliderImages = BX.findChildByClassName(container, options.imagesContainerClassName, true);
				var sliderLeft = BX.findChildByClassName(container, options.leftArrowClassName, true);
				var sliderRight = BX.findChildByClassName(container, options.rightArrowClassName, true);
				var sliderControl = BX.findChildByClassName(container, options.controlContainerClassName, true);
				if (sliderImages && sliderLeft && sliderRight && sliderControl)
				{
					var slider = new JCNewsSlider;
					slider.Init(sliderImages, sliderControl, sliderLeft, sliderRight);
				}
			}
		}
	};

	window.JCNewsSlider.prototype.Init = function(sliderImages, sliderControl, sliderLeft, sliderRight)
	{
		this.time = 0.5;
		this.type = 'accelerated';
		//swipe
		this.threshold = 50;
		this.allowedTime = 500;
		this.startX = 0;
		this.startY = 0;
		this.startTime = 0;

		this.sliderImages = sliderImages;
		BX.bind(sliderImages, 'touchstart', BX.delegate(this.touchStart, this));
		BX.bind(sliderImages, 'touchend', BX.delegate(this.touchEnd, this));

		this.sliderControls = BX.findChildren(sliderControl, {tagName: 'LI'}, true);
		var proxy = function(j, func)
		{
			return function()
			{
				return func(j);
			}
		}
		for (var j = 0; j < this.sliderControls.length; j++)
		{
			BX.bind(this.sliderControls[j], 'click', proxy(j, BX.delegate(this.slideTo, this)));
		}

		this.sliderLeft = sliderLeft;
		BX.bind(sliderLeft, 'click', BX.delegate(this.slideLeft, this));

		this.sliderRight = sliderRight;
		BX.bind(sliderRight, 'click', BX.delegate(this.slideRight, this));
	};

	window.JCNewsSlider.prototype.slideRight = function()
	{
		if (this.animation)
			return;
		this.animation = true;

		var p = parseInt(this.sliderImages.style.left, 10);
		if (p <= -100 * (this.sliderControls.length-1))
		{
			this.animation = false;
			return;
		}

		var _this = this;
		(new BX.fx({
			start: p,
			finish: p - 100,
			time: this.time,
			type: this.type,
			callback: function(res){
				_this.sliderImages.style.left = res + '%';
			},
			callback_start: function(){
				_this.sliderImages.style.left = p + '%';
			},
			callback_complete: function(){
				_this.sliderImages.style.left = (p - 100) + '%';
				_this.adjustSliderControls(-(p - 100) / 100);
				_this.animation = false;
			}
		})).start();
	};

	window.JCNewsSlider.prototype.slideLeft = function()
	{
		if (this.animation)
			return;
		this.animation = true;

		var p = parseInt(this.sliderImages.style.left, 10);
		if (p >= 0)
		{
			this.animation = false;
			return;
		}

		var _this = this;
		(new BX.fx({
			start: p,
			finish: p + 100,
			time: this.time,
			type: this.type,
			callback: function(res){
				_this.sliderImages.style.left = res + '%';
			},
			callback_start: function(){
				_this.sliderImages.style.left = p + '%';
			},
			callback_complete: function(){
				_this.sliderImages.style.left = (p + 100) + '%';
				_this.adjustSliderControls(-(p + 100) / 100);
				_this.animation = false;
			}
		})).start();
	};

	window.JCNewsSlider.prototype.slideTo = function(j)
	{
		if (this.animation)
			return;
		this.animation = true;

		var p = parseInt(this.sliderImages.style.left, 10);
		if (p == (-j * 100))
		{
			this.animation = false;
			return;
		}

		var _this = this;
		(new BX.fx({
			start: p,
			finish: -j * 100,
			time: this.time,
			type: this.type,
			callback: function(res){
				_this.sliderImages.style.left = res + '%';
			},
			callback_start: function(){
				_this.sliderImages.style.left = p + '%';
				_this.adjustSliderControls(j);
			},
			callback_complete: function(){
				_this.sliderImages.style.left = (-j * 100) + '%';
				_this.animation = false;
			}
		})).start();
	};

	window.JCNewsSlider.prototype.adjustSliderControls = function(i)
	{
		for (var j = 0; j < this.sliderControls.length; j++)
		{
			if (i == j)
				BX.addClass(this.sliderControls[j], 'current');
			else
				BX.removeClass(this.sliderControls[j], 'current');
		}
	};

	window.JCNewsSlider.prototype.touchStart = function(e)
	{
		var touchObject = e.changedTouches[0];
		this.startX = touchObject.pageX;
		this.startY = touchObject.pageY;
		this.startTime = new Date().getTime();
		e.preventDefault();
	};

	window.JCNewsSlider.prototype.touchEnd = function(e)
	{
		var touchObject = e.changedTouches[0];
		var distance = touchObject.pageX - this.startX;
		var elapsedTime = new Date().getTime() - this.startTime;

		if (elapsedTime <= this.allowedTime)
		{
			if (distance > this.threshold)
				this.slideLeft();
			else if (distance < -this.threshold)
				this.slideRight();
		}
		e.preventDefault()
	};
}
)(window);