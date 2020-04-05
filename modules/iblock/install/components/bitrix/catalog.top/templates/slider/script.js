(function (window) {

if (!!window.JCCatalogTopBannerList)
	return;

window.JCCatalogTopBannerList = function (arParams)
{
	this.params = null;
	this.prevIndex = -1;
	this.currentIndex = 0;
	this.size = 0;
	this.rotate = false;
	this.rotateTimer = 30000;
	this.rotatePause = false;
	this.errorCode = 0;

	this.slider = {
		cont: null,
		row: null,
		items: null,
		arrows: null,
		left: null,
		right: null,
		pagination: null,
		pages: null
	};

	if (!arParams || 'object' != typeof(arParams))
	{
		this.errorCode = -1;
	}
	if (0 === this.errorCode)
	{
		this.params = arParams;
	}
	if (!!this.params.rotate)
		this.rotate = this.params.rotate;
	if (!!this.params.rotateTimer)
	{
		this.params.rotateTimer = parseInt(this.params.rotateTimer);
		if (!isNaN(this.params.rotateTimer) && 0 <= this.params.rotateTimer)
			this.rotateTimer = this.params.rotateTimer;
	}

	if (0 === this.errorCode)
	{
		BX.ready(BX.delegate(this.Init,this));
	}
};

window.JCCatalogTopBannerList.prototype.Init = function()
{
	if (0 > this.errorCode)
		return;

	var i = 0;
	if (!!this.params.cont)
	{
		this.slider.cont = BX(this.params.cont);
	}
	if (!!this.params.items && BX.type.isArray(this.params.items))
	{
		this.slider.items = [];
		for (i = 0; i < this.params.items.length; i++)
		{
			this.slider.items[this.slider.items.length] = BX(this.params.items[i]);
			this.slider.items[this.slider.items.length-1].style.opacity = 0;
			if (!this.slider.row)
				this.slider.row = this.slider.items[this.slider.items.length-1].parentNode;
		}
		this.slider.items[0].style.opacity = 1;
		this.size = this.slider.items.length;
	}

	if (!!this.params.arrows)
	{
		if (BX.type.isDomNode(this.params.arrows))
			this.slider.arrows = this.params.arrows;
		else if ('object' == typeof(this.params.arrows))
			this.slider.arrows = this.slider.cont.appendChild(BX.create(
				'DIV',
				{
					props: {
						id: this.params.arrows.id,
						className: this.params.arrows.className
					}
				}
			));
		else if (BX.type.isNotEmptyString(this.params.arrows))
			this.slider.arrows = BX(this.params.arrows);
	}
	if (!this.slider.arrows)
	{
		this.slider.arrows = this.slider.cont;
	}
	if (!!this.params.left)
	{
		if (BX.type.isDomNode(this.params.left))
			this.slider.left = this.params.left;
		else if ('object' == typeof(this.params.left))
			this.slider.left = this.slider.arrows.appendChild(BX.create(
				'DIV',
				{
					props: {
						id: this.params.left.id,
						className: this.params.left.className
					}
				}
			));
		else if (BX.type.isNotEmptyString(this.params.left))
			this.slider.left = BX(this.params.left);
	}
	if (!!this.params.right)
	{
		if (BX.type.isDomNode(this.params.right))
			this.slider.right = this.params.right;
		else if ('object' == typeof(this.params.right))
			this.slider.right = this.slider.arrows.appendChild(BX.create(
				'DIV',
				{
					props: {
						id: this.params.right.id,
						className: this.params.right.className
					}
				}
			));
		else if (BX.type.isNotEmptyString(this.params.right))
			this.slider.right = BX(this.params.right);
	}
	if (!!this.params.pagination)
	{
		if (BX.type.isDomNode(this.params.pagination))
			this.slider.pagination = this.params.pagination;
		else if ('object' == typeof(this.params.pagination))
			this.slider.pagination = this.slider.cont.appendChild(BX.create(
				'UL',
				{
					props: {
						id: this.params.pagination.id,
						className: this.params.pagination.className
					}
				}
			));
		else if (BX.type.isNotEmptyString(this.params.pagination))
			this.slider.pagination = BX(this.params.pagination);
	}
	if (!!this.slider.pagination)
	{
		this.slider.pages = [];
		for (i = 0; i < this.slider.items.length; i++)
		{
			this.slider.pages[this.slider.pages.length] = this.slider.pagination.appendChild(BX.create(
				'LI',
				{
					props: {
						className: (0 === i ? 'active' : '')
					},
					attrs: {
						'data-pagevalue': i.toString()
					},
					events: {
						'click': BX.delegate(this.RowMove, this)
					},
					html: '<span></span>'
				}
			));
		}
	}

	if (0 === this.errorCode)
	{
		if (this.rotate && !!this.slider.cont && 0 < this.rotateTimer)
		{
			BX.bind(this.slider.cont, 'mouseover', BX.delegate(this.RotateStop, this));
			BX.bind(this.slider.cont, 'mouseout', BX.delegate(this.RotateStart, this));
			setTimeout(BX.delegate(this.RowRotate, this), this.rotateTimer);
		}
		if (!!this.slider.left)
		{
			BX.bind(this.slider.left, 'click', BX.delegate(this.RowLeft, this));
		}
		if (!!this.slider.right)
		{
			BX.bind(this.slider.right, 'click', BX.delegate(this.RowRight, this));
		}
	}
};

window.JCCatalogTopBannerList.prototype.RowStart = function()
{
	if (0 > this.errorCode)
		return;
	BX.removeClass(this.slider.items[this.prevIndex], 'active');
	BX.removeClass(this.slider.pages[this.prevIndex], 'active');
};

window.JCCatalogTopBannerList.prototype.RowAnimate = function(state)
{
	if (0 > this.errorCode)
		return;
	this.slider.items[this.prevIndex].style.opacity = (100 - state.opacity)/100;
	this.slider.items[this.currentIndex].style.opacity = state.opacity/100;
};

window.JCCatalogTopBannerList.prototype.RowComplete = function()
{
	if (0 > this.errorCode)
		return;
	BX.addClass(this.slider.items[this.currentIndex], 'active');
	BX.addClass(this.slider.pages[this.currentIndex], 'active');
};

window.JCCatalogTopBannerList.prototype.RowLeft = function()
{
	if (0 > this.errorCode)
		return;
	this.prevIndex = this.currentIndex;
	this.currentIndex = (0 === this.currentIndex ? this.size : this.currentIndex)-1;
	new BX.easing({
		duration : 800,
		start : { left : -this.prevIndex*100 },
		finish : { left : -this.currentIndex*100 },
		transition : BX.easing.transitions.quart,
		step : BX.delegate(function(state){
			this.slider.row.style.left = state.left+'%';
		}, this)
	}).animate();
	this.RowStart();
	new BX.easing({
		duration : 1200,
		start : { opacity : 0 },
		finish : { opacity : 100 },
		transition : BX.easing.transitions.quart,
		step : BX.delegate(function(state) {this.RowAnimate(state); }, this),
		complete: BX.delegate(this.RowComplete, this)
	}).animate();
};

window.JCCatalogTopBannerList.prototype.RowRight = function()
{
	if (0 > this.errorCode)
		return;
	this.prevIndex = this.currentIndex;
	this.currentIndex++;
	if (this.currentIndex == this.size)
		this.currentIndex = 0;
	new BX.easing({
		duration : 800,
		start : { left : -this.prevIndex*100 },
		finish : { left : -this.currentIndex*100 },
		transition : BX.easing.transitions.quart,
		step : BX.delegate(function(state){
			this.slider.row.style.left = state.left+'%';
		}, this)
	}).animate();
	this.RowStart();
	new BX.easing({
		duration : 1200,
		start : { opacity : 0 },
		finish : { opacity : 100 },
		transition : BX.easing.transitions.quart,
		step : BX.delegate(function(state) {this.RowAnimate(state); }, this),
		complete: BX.delegate(this.RowComplete, this)
	}).animate();
};

window.JCCatalogTopBannerList.prototype.RowMove = function()
{
	if (0 > this.errorCode)
		return;
	var target = BX.proxy_context;
	if (!!target && target.hasAttribute('data-pagevalue'))
	{
		var pageValue = parseInt(target.getAttribute('data-pagevalue'));
		if (!isNaN(pageValue) && pageValue < this.size)
		{
			this.prevIndex = this.currentIndex;
			this.currentIndex = pageValue;
			this.slider.row.style.left = -this.currentIndex*100+'%';
			this.slider.items[this.prevIndex].style.opacity = 0;
			this.RowStart();
			new BX.easing({
				duration : 800,
				start : { opacity : 0 },
				finish : { opacity : 100 },
				transition : BX.easing.transitions.quart,
				step : BX.delegate(function(state) { this.RowAnimate(state); }, this),
				complete: BX.delegate(this.RowComplete, this)
			}).animate();
		}
	}
};

window.JCCatalogTopBannerList.prototype.RowRotate = function()
{
	if (0 > this.errorCode)
		return;
	if (!this.rotatePause)
	{
		this.RowRight();
	}
	setTimeout(BX.delegate(this.RowRotate, this), this.rotateTimer);
};

window.JCCatalogTopBannerList.prototype.RotateStart = function()
{
	if (0 > this.errorCode)
		return;
	this.rotatePause = false;
};

window.JCCatalogTopBannerList.prototype.RotateStop = function()
{
	if (0 > this.errorCode)
		return;
	this.rotatePause = true;
};
})(window);