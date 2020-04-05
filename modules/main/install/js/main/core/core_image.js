;(function(){

if (window.BX.CImageView)
	return;

/******* image viewer ********/

// show single image - not recommended
BX.viewImage = function(img_src, w, h, title, params)
{
	params = params || {};
	params.cycle = false;
	params.list = [{image:img_src, height: h, width: w, title: title}];

	var obView = new BX.CImageView(params);
	obView.show();

	return obView;
}

// bind image viewer on concrete node
BX.viewImageBind = function(div, params, isTarget, groupBy)
{
	var obImageView = new BX.CImageView(params);

	if(!isTarget)
		isTarget = {tag:'IMG'};

	BX.ready(function(){
		_viewImageBind(div, isTarget, groupBy, obImageView);
	});

	return obImageView;
};

function _viewImageBind(div, isTarget, groupBy, obImageView)
{
	var div = BX(div);
	if (!!div)
	{
		BX.bindDelegate(div, 'click', isTarget, function(e)
		{
			var parent = div;
			if (!!groupBy)
			{
				parent = BX.findParent(this, groupBy, div)||parent;
			}

			var imgNodeList = BX.findChildren(parent, isTarget, true),
				imgList = [],
				currentImage = false;

			for(var i=0; i<imgNodeList.length; i++)
			{
				var imgData = {
					thumb: imgNodeList[i].src,
					image: imgNodeList[i].getAttribute('data-bx-image'),

					width: imgNodeList[i].getAttribute('data-bx-width'),
					height: imgNodeList[i].getAttribute('data-bx-height'),

					full: imgNodeList[i].getAttribute('data-bx-full'),
					full_width: imgNodeList[i].getAttribute('data-bx-full-width'),
					full_height: imgNodeList[i].getAttribute('data-bx-full-height'),
					full_size: imgNodeList[i].getAttribute('data-bx-full-size'),

					title: imgNodeList[i].getAttribute('data-bx-title')||imgNodeList[i].alt||imgNodeList[i].title
				};

				var bHasLink = imgNodeList[i].parentNode.tagName.toUpperCase() == 'A' && !!imgNodeList[i].parentNode.href;

				if(bHasLink)
				{
					imgData.image = imgData.image || imgNodeList[i].parentNode.href;
					imgData.title = imgData.title || imgNodeList[i].parentNode.title;

					imgData.width = imgNodeList[i].parentNode.getAttribute('data-bx-width');
					imgData.height = imgNodeList[i].parentNode.getAttribute('data-bx-height');

					imgData.full = imgData.full || imgNodeList[i].parentNode.getAttribute('data-bx-full');
					if(!!imgData.full)
					{
						imgData.full_width = imgData.full_width || imgNodeList[i].parentNode.getAttribute('data-bx-full-width');
						imgData.full_height = imgData.full_height || imgNodeList[i].parentNode.getAttribute('data-bx-full-height');
						imgData.full_size = imgData.full_size || imgNodeList[i].parentNode.getAttribute('data-bx-full-size');
					}

					imgData.title = imgData.title||imgNodeList[i].parentNode.getAttribute('data-bx-title')||imgNodeList[i].parentNode.alt||imgNodeList[i].parentNode.title;
				}

				imgData.image = imgData.image || imgData.thumb;

				if(imgNodeList[i] == this)
					currentImage = imgData.image

				if (!!imgData.image)
					imgList.push(imgData);
			}

			var bLink = this.parentNode.tagName.toUpperCase() == 'A' && !!this.parentNode.href,
				bExtLink = bLink && !!currentImage && this.parentNode.href != currentImage;

			if(!bExtLink)
			{
				obImageView.setList(imgList);
				obImageView.show(this.getAttribute('data-bx-image')||this.src);

				if(bLink)
					return BX.PreventDefault(e);
			}
		});
	}
};

/******* image viewer main class ********/
/*
params: {
	list: [], // starting list of images
	cycle: true, // whether to cycle images list - go to first after last
	resize: 'WH', //'W' - resize image to fit width, 'H' - resize image to fit height, 'WH' - W&H , ''||false => show original image size without resizing. RECOMMENDATION: set lockScroll: true for resize: W or resize: false;
	resizeToggle: false,
	showTitle: true, // whether to show image title
	preload: 1, // number of list images to be preloaded (in both sides. default - 1 next and 1 previous)
	minMargin: 20, // - minimum space between viewer and screen edge.
	minPadding: 12, // - minimum space between viewer and image edge.
	lockScroll: false, // whether to lock page scroll.
	keyMap: {} // map for hotkeys. set to false to disable hotkeys. use BX.CImageView.defaultSettings.keyMap as default
}

elements: [{
	thumb: '/images/image.jpg',
	image: '/images/thumb.jpg',
	title: 'This is my image!',
	height: int
	width: int
}]
*/
BX.CImageView = function(params)
{
	this.params = BX.clone(BX.CImageView.defaultSettings);
	for(var i in params)
	{
		this.params[i] = params[i];
	}

	this.DIV = null;
	this.OVERLAY = null;
	this.IMAGE_WRAP = null;
	this.IMAGE = BX.create('IMG', {
		props: {
			className: 'bx-images-viewer-image'
		},
		events: {
			load: BX.proxy(this.adjustPos, this),
			click: BX.proxy(this.next, this)
		}
	});

	this.list = this.params.list;
	this.list_preload = [];

	this._current = 0;

	this.bVisible = false;
};

BX.CImageView.defaultSettings = {
	list: [],
	cycle: true, // whether to cycle images list - go to first after last
	resize: 'WH', //'W' - resize image to fit width, 'H' - resize image to fit height, 'WH' - W&H , ''||false => show original image size without resizing
	resizeToggle: false,
	showTitle: true, // whether to show image title
	preload: 1, // number of list images to be preloaded
	minMargin: 20, //minimal margin
	minPadding: 12, // minimal padding
	lockScroll: false,
	keyMap: {
		27: 'close', // esc
		33: 'prev', // pgup
		37: 'prev', // left
		38: 'prev', // up
		34: 'next', // pgdn
		39: 'next', // right
		40: 'next', // down
		32: 'next' // space
	}
};

BX.CImageView.prototype._create = function()
{
	if (!this.DIV)
	{
		var specTag = BX.browser.IsIE() && !BX.browser.IsDoctype() ? 'A' : 'SPAN',
			specHref = specTag == 'A' ? 'javascript:void(0)' : null;

		this.OVERLAY = document.body.appendChild(BX.create('DIV', {
			props: {className: 'bx-images-viewer-overlay'},
			events: {click: BX.proxy(this._hide, this)}
		}));

		this.DIV = this.OVERLAY.appendChild(BX.create('DIV', {
			props: {className: 'bx-images-viewer-wrap-outer'},
			events: {
				click: BX.eventCancelBubble
			},
			children: [
				(this.PREV_LINK = BX.create(specTag, {
					props: {
						className: 'bx-images-viewer-prev-outer',
						href: specHref
					},
					events: {
						click: BX.proxy(this.prev, this)
					},
					html: '<span class="bx-images-viewer-prev"></span>'
				})),
				(this.NEXT_LINK = BX.create(specTag, {
					props: {
						className: 'bx-images-viewer-next-outer',
						href: specHref
					},
					events: {
						click: BX.proxy(this.next, this)
					},
					html: '<span class="bx-images-viewer-next"></span>'
				})),
				(this.IMAGE_TITLE = BX.create('DIV', {
					style: {bottom: '0'},
					props: {className: 'bx-images-viewer-title'}
				})),
				BX.create('DIV', {
					props: {className: 'bx-images-viewer-wrap-inner'},
					style: {padding: this.params.minPadding + 'px'},
					children: [
						(this.IMAGE_WRAP = BX.create('DIV', {
							props: {className: 'bx-images-viewer-wrap'},
							children: [
								this.IMAGE
							]
						}))
					]
				}),
				BX.create(specTag, {
					props: {
						className: 'bx-images-viewer-close',
						href: specHref
					},
					events: {click: BX.proxy(this._hide, this)},
					html: '<span class="bx-images-viewer-close-inner"></span>'
				})
			]
		}));

		if (!!this.params.resizeToggle)
		{
			this.IMAGE_WRAP.appendChild(BX.create('SPAN', {
				props: {className: 'bx-images-viewer-size-toggle'},
				style: {
					right: this.params.minPadding + 'px',
					bottom: this.params.minPadding + 'px'
				},
				events: {
					click: BX.proxy(this._toggle_resize, this)
				}
			}))
		}
	}
};

BX.CImageView.prototype._keypress = function(e)
{
	var key = (e||window.event).keyCode || (e||window.event).charCode;
	if (!!this.params.keyMap && !!this.params.keyMap[key] && !!this[this.params.keyMap[key]])
	{
		this[this.params.keyMap[key]].apply(this);
		return BX.PreventDefault(e);
	}
};

BX.CImageView.prototype._toggle_resize = function()
{
	var tmp = this.params.resize;
	this.params.resize = this.params.resizeToggle;
	this.params.resizeToggle = tmp;

	if (this.params.resize != 'WH')
	{
		this.params.lockScroll = true;
		this._lock_scroll();
	}
	else
	{
		this.params.lockScroll = false;
		this._unlock_scroll();
	}

	this.adjustSize();
	this.adjustPos();
};

BX.CImageView.prototype.adjustPos = function()
{
	if (this.list[this._current].height > 0 && this.list[this._current].width > 0)
	{
		this._adjustPosByImg();
	}
	else
	{
		if (!this.IMAGE_WRAP.style.height)
			this.IMAGE_WRAP.style.height = "100px";
		if (!this.IMAGE_WRAP.style.width)
			this.IMAGE_WRAP.style.width = "100px";

		setTimeout(BX.proxy(this._adjustPosByImg, this), 250);
	}
};

BX.CImageView.prototype._adjustPosByImg = function()
{
	if (this.bVisible)
	{
		var wndSize = BX.GetWindowSize(),
			top = parseInt((wndSize.innerHeight - parseInt(this.IMAGE_WRAP.style.height) - 2 * this.params.minPadding)/2),
			left = parseInt((wndSize.innerWidth - parseInt(this.IMAGE_WRAP.style.width) - 2 * this.params.minPadding)/2);

		if (!this.params.lockScroll && wndSize.innerWidth < wndSize.scrollHeight)
			left -= 20;

		if (top < this.params.minMargin)
			top = this.params.minMargin;
		if (left < this.params.minMargin + Math.min(70, this.PREV_LINK.offsetWidth))
			left = this.params.minMargin + Math.min(70, this.PREV_LINK.offsetWidth);

		if (this.params.showTitle && !!this.list[this._current].title)
		{
			top -= 20;
		}

		this.DIV.style.top = top + 'px';
		this.DIV.style.left = left + 'px';
	}
};

BX.CImageView.prototype.adjustSize = function()
{
	var wndSize = BX.GetWindowSize(), img = this.list[this._current];

	if (!!img.height && !!img.width)
	{
		if (!this.params.lockScroll && wndSize.innerWidth < wndSize.scrollHeight)
			wndSize.innerWidth -= 20;

		wndSize.innerWidth -= this.params.minMargin * 2 + this.params.minPadding * 2 + Math.min(140, this.PREV_LINK.offsetWidth + this.NEXT_LINK.offsetWidth);
		wndSize.innerHeight -= this.params.minMargin * 2 + this.params.minPadding * 2;

		if (this.params.showTitle && !!this.list[this._current].title)
		{
			wndSize.innerHeight -= 40;
		}

		var height = img.height,
			width = img.width,
			ratio = [1];

		if (this.params.resize)
		{
			if(this.params.resize.indexOf('W') >= 0)
				ratio.push(wndSize.innerWidth/width);
			if (this.params.resize.indexOf('H') >= 0)
				ratio.push(wndSize.innerHeight/height);
		}

		ratio = Math.min.apply(window, ratio);

		height *= ratio;
		width *= ratio;

		this.IMAGE_WRAP.style.height = parseInt(height) + 'px';
		this.IMAGE_WRAP.style.width = parseInt(width) + 'px';

		if (BX.browser.IsIE())
		{
			var h = parseInt(this.IMAGE_WRAP.style.height) + this.params.minPadding * 2;

			this.PREV_LINK.style.height = this.NEXT_LINK.style.height = h + 'px';
			this.PREV_LINK.firstChild.style.top = this.NEXT_LINK.firstChild.style.top = parseInt(h/2-20) + 'px';
		}
	}
};

BX.CImageView.prototype._lock_scroll = function()
{
	if (this.params.lockScroll)
		BX.addClass(document.body, 'bx-images-viewer-lock-scroll');
};

BX.CImageView.prototype._unlock_scroll = function()
{
	if (this.params.lockScroll)
		BX.removeClass(document.body, 'bx-images-viewer-lock-scroll');
};

BX.CImageView.prototype._unhide = function()
{
	this.bVisible = true;

	this.DIV.style.display = 'block';
	this.OVERLAY.style.display = 'block';

	this.PREV_LINK.style.display = (this.list.length > 1 && (this.params.cycle || this._current > 0)) ? 'block' : 'none';
	this.NEXT_LINK.style.display = (this.list.length > 1 && (this.params.cycle || this._current < this.list.length-1)) ? 'block' : 'none';

	this.adjustPos();

	BX.unbind(document, 'keydown', BX.proxy(this._keypress, this));
	BX.unbind(window, 'resize', BX.proxy(this.adjustSize, this));
	BX.unbind(window, 'resize', BX.proxy(this.adjustPos, this));
	BX.bind(document, 'keydown', BX.proxy(this._keypress, this));
	BX.bind(window, 'resize', BX.proxy(this.adjustSize, this));
	BX.bind(window, 'resize', BX.proxy(this.adjustPos, this));

	this._lock_scroll();
};

BX.CImageView.prototype._hide = function()
{
	this.bVisible = false;

	this.DIV.style.display = 'none';
	this.OVERLAY.style.display = 'none';

	BX.unbind(document, 'keydown', BX.proxy(this._keypress, this));
	BX.unbind(window, 'resize', BX.proxy(this.adjustSize, this));
	BX.unbind(window, 'resize', BX.proxy(this.adjustPos, this));

	this._unlock_scroll();

	BX.onCustomEvent(this, 'onImageViewClose', [this.list[this._current]]);
};

BX.CImageView.prototype.add = function(data)
{
	this.list.push(data);
};

BX.CImageView.prototype.setList = function(list)
{
	this.list = [];
	this.list_preload = [];

	if (!!list && BX.type.isArray(list))
	{
		for(var i=0; i<list.length; i++)
		{
			this.add(list[i]);
		}
	}

	if (this.bVisible)
	{
		if (this.list.length > 0)
			this.show();
		else
			this.close();
	}
};

BX.CImageView.prototype.show = function(img)
{
	var _current = this._current;

	if (BX.type.isElementNode(img))
		img = img.getAttribute('src');

	if(typeof(img) == 'object' && (!!img.image || !!img.thumb))
		img = (img.image||img.thumb);

	if (BX.type.isString(img))
	{
		for(var i=0; i < this.list.length; i++)
		{
			if(this.list[i].image == img || this.list[i].thumb == img)
			{
				_current = i;
				break;
			}
		}
	}

	img = this.list[_current];

	if (!img)
		return;

	this._current = _current;

	this._create();

	this.IMAGE.style.opacity = 0;

	this.adjustSize();

	if (!this.list_preload[this._current])
	{
		BX.addClass(this.IMAGE_WRAP, 'bx-images-viewer-wrap-loading');
		this.list_preload[this._current] = new Image();
		this.list_preload[this._current].bxloaded = false;
		this.list_preload[this._current].onload = this._get_image_onload();
		this.list_preload[this._current].src = img.image;

		BX.defer(function(){
			if((this.list_preload[this._current].width > 0 || this.list_preload[this._current].height > 0))
			{
				this.IMAGE.onload = null;
				this.IMAGE.style.opacity = 1;
			}
		}, this)();
	}
	else if (this.list_preload[this._current].bxloaded)
	{
		setTimeout(BX.delegate(this.list_preload[this._current].onload, this.list_preload[this._current]), 350);
	}

	setTimeout(BX.proxy(this._check_title, this), 300);
	this._unhide();

	BX.onCustomEvent(this, 'onImageViewShow', [img]);
};

BX.CImageView.prototype._check_title = function()
{
	this.IMAGE_TITLE.innerHTML = '';

	if (this.params.showTitle && this.list[this._current].title || this.list[this._current].full)
	{
		var height = 0,
			bottom = 0,
			params = {
				style: {
					opacity: '1'
				},
				children: []
			};

		if(this.params.showTitle && this.list[this._current].title)
		{
			params.children.push(BX.create('DIV', {props: {className: 'bx-images-viewer-title-item'}, text: this.list[this._current].title}));
			height += 35;
			bottom += 35;
		}

		if(this.list[this._current].full)
		{
			var p = [];
			if(this.list[this._current].full_height && this.list[this._current].full_width)
			{
				p.push(this.list[this._current].full_width+'x'+this.list[this._current].full_height);
			}

			if(this.list[this._current].full_size)
			{
				p.push(this.list[this._current].full_size);
			}

			html = '<a href="'+this.list[this._current].full+'" class="bx-images-viewer-full-link" target="_blank">' + BX.message('JS_CORE_IMAGE_FULL') + (p.length > 0 ? (' ('+p.join(', ')+')') : '') + '</a>';
			params.children.push(BX.create('DIV', {props: {className: 'bx-images-viewer-title-item bx-images-viewer-full'}, html: html}));
			height += 35;
			bottom += 35;
		}

		params.style.height = height + 'px';
		params.style.bottom = -bottom + 'px';
		BX.adjust(this.IMAGE_TITLE, params);
	}
	else
	{
		this.IMAGE_TITLE.style.opacity = '0';
		this.IMAGE_TITLE.style.bottom = '0';
		this.IMAGE_TITLE.style.height = '0';
	}
}

BX.CImageView.prototype._get_image_onload = function(_current)
{
	_current = typeof _current == 'undefined' ? this._current : _current;
	return BX.delegate(function(){
		BX.proxy_context.bxloaded = true;
		if (_current == this._current)
		{
			var img = this.list[this._current];

			BX.removeClass(this.IMAGE_WRAP, 'bx-images-viewer-wrap-loading');

			this.IMAGE.onload = function(){
				this.onload = null;
				this.style.opacity = 1;
			};

			this.IMAGE.src = BX.proxy_context.src;

			if (BX.proxy_context.width)
				img.width = BX.proxy_context.width;
			if (BX.proxy_context.height)
				img.height = BX.proxy_context.height;

			this.adjustSize();
			this.adjustPos();

			BX.defer(this._preload, this)();
		}
		BX.onCustomEvent(this, 'onImageViewLoad', [this.list[_current], _current == this._current]);
	}, this);
}

BX.CImageView.prototype._preload = function()
{
	if (this.params.preload > 0)
	{
		var finish = Math.max(this._current-this.params.preload, this.params.cycle ? -1000 : 0),
			start = Math.min(this._current+this.params.preload, this.params.cycle ? this.list.length + 1000 : this.list.length-1);

		if (finish < start)
		{
			for (var i=start; i>=finish; i--)
			{
				var ix = i;
				if (ix < 0)
					ix += this.list.length;
				else if (ix >= this.list.length)
					ix -= this.list.length;

				if (!this.list_preload[ix])
				{
					this.list_preload[ix] = new Image();
					this.list_preload[ix].src = this.list[ix].image;
					this.list_preload[ix].onload = this._get_image_onload(ix);
				}
			}
		}

	}
};

BX.CImageView.prototype.next = function()
{
	if (this.list.length > 1)
	{
		this._current++;
		if(this._current >= this.list.length)
		{
			if(!!this.params.cycle)
				this._current = 0;
			else
				this._current--;

			BX.onCustomEvent(this, 'onImageViewFinishList', [this.list[this._current], 1]);
		}

		this.show();
	}
};

BX.CImageView.prototype.prev = function()
{
	if (this.list.length > 1)
	{
		this._current--;
		if(this._current < 0)
		{
			if(!!this.params.cycle)
				this._current = this.list.length-1;
			else
				this._current++;

			BX.onCustomEvent(this, 'onImageViewFinishList', [this.list[this._current], -1]);
		}

		this.show();
	}
};

BX.CImageView.prototype.close = function()
{
	this._hide();
};

})(window);
