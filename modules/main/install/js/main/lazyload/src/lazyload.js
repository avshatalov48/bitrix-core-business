const LazyLoad = {
	images: [],
	imageStatus: {
		hidden: -2,
		error: -1,
		"undefined": 0,
		inited: 1,
		loaded: 2
	},
	imageTypes: {
		image: 1,
		background: 2
	},

	registerImage: function(id, isImageVisibleCallback, options)
	{
		options = options || {};

		if (BX.type.isNotEmptyString(id))
		{
			this.images.push({
				id: id,
				node: null,
				src: null,
				dataSrcName: options.dataSrcName || 'src',
				type: null,
				func: BX.type.isFunction(isImageVisibleCallback) ? isImageVisibleCallback : null,
				status: this.imageStatus.undefined
			});
		}
	},

	registerImages: function(ids, isImageVisibleCallback, options)
	{
		if (BX.type.isArray(ids))
		{
			for (var i = 0, length = ids.length; i < length; i++)
			{
				this.registerImage(ids[i], isImageVisibleCallback, options);
			}
		}
	},

	showImages: function(checkOwnVisibility)
	{
		var image = null;
		var isImageVisible = false;

		checkOwnVisibility = (checkOwnVisibility !== false);
		for (var i = 0, length = this.images.length; i < length; i++)
		{
			image = this.images[i];

			if (image.status == this.imageStatus.undefined)
			{
				this.initImage(image);
			}

			if (image.status !== this.imageStatus.inited)
			{
				continue;
			}

			if (
				!image.node
				|| !image.node.parentNode
			)
			{
				image.node = null;
				image.status = this.imageStatus.error;
				continue;
			}

			isImageVisible = true;
			if (checkOwnVisibility && image.func)
			{
				isImageVisible = image.func(image);
			}

			if (
				isImageVisible === true
				&& this.isElementVisibleOnScreen(image.node)
			)
			{
				if (image.type == this.imageTypes.image)
				{
					image.node.src = image.src;
				}
				else
				{
					image.node.style.backgroundImage = "url('" + image.src + "')";
				}

				image.node.dataset[image.dataSrcName] = "";
				image.status = this.imageStatus.loaded;
			}
		}
	},

	initImage: function(image)
	{
		image.status = this.imageStatus.error;
		var node = BX(image.id);
		if (node)
		{
			var src = node.dataset[image.dataSrcName];
			if (BX.type.isNotEmptyString(src))
			{
				image.node = node;
				image.src = src;
				image.status = this.imageStatus.inited;
				image.type = (image.node.tagName.toLowerCase() == "img"
						? this.imageTypes.image
						: this.imageTypes.background
				);
			}
		}
	},

	isElementVisibleOnScreen: function (element)
	{
		var coords = this.getElementCoords(element);

		var windowTop = window.pageYOffset || document.documentElement.scrollTop;
		var windowBottom = windowTop + document.documentElement.clientHeight;

		coords.bottom = coords.top + element.offsetHeight;

		var topVisible = coords.top > windowTop && coords.top < windowBottom;
		var bottomVisible = coords.bottom < windowBottom && coords.bottom > windowTop;

		return topVisible || bottomVisible;
	},

	isElementVisibleOn2Screens: function(element)
	{
		var coords = this.getElementCoords(element);

		var windowHeight = document.documentElement.clientHeight;
		var windowTop = window.pageYOffset || document.documentElement.scrollTop;
		var windowBottom = windowTop + windowHeight;

		coords.bottom = coords.top + element.offsetHeight;

		windowTop -= windowHeight;
		windowBottom += windowHeight;

		var topVisible = coords.top > windowTop && coords.top < windowBottom;
		var bottomVisible = coords.bottom < windowBottom && coords.bottom > windowTop;

		return topVisible || bottomVisible;
	},

	getElementCoords: function(element)
	{
		var box = element.getBoundingClientRect();

		return {
			originTop: box.top,
			originLeft: box.left,
			top: box.top + window.pageYOffset,
			left: box.left + window.pageXOffset
		};
	},

	onScroll: function()
	{
		BX.LazyLoad.showImages();
	},

	clearImages: function ()
	{
		this.images = [];
	}
};

export {LazyLoad};