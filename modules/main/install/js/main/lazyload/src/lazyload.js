import {Type} from 'main.core';
import 'main.polyfill.intersectionobserver';

const LazyLoad = {
	observer: null,
	images: {},
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

	initObserver: function()
	{
		this.observer = new IntersectionObserver(this.onIntersection.bind(this), {
			rootMargin: '20% 0% 20% 0%',
			threshold: 0.10
		});
	},

	onIntersection: function(entries)
	{
		entries.forEach(function (entry) {
			if (entry.isIntersecting)
			{
				this.showImage(entry.target);
			}
		}.bind(this));
	},

	registerImage: function(id, isImageVisibleCallback, options)
	{
		if (this.observer === null)
		{
			this.initObserver();
		}

		options = options || {};

		if (!Type.isStringFilled(id))
		{
			return;
		}

		if (Type.isObject(this.images[id]))
		{
			return;
		}

		const element = document.getElementById(id);
		if (!Type.isDomNode(element))
		{
			return;
		}

		this.observer.observe(element);

		this.images[id] = {
			id: id,
			node: null,
			src: null,
			dataSrcName: options.dataSrcName || 'src',
			type: null,
			func: Type.isFunction(isImageVisibleCallback) ? isImageVisibleCallback : null,
			status: this.imageStatus.undefined
		};
	},

	registerImages: function(ids, isImageVisibleCallback, options)
	{
		if (Type.isArray(ids))
		{
			for (let i = 0, length = ids.length; i < length; i++)
			{
				this.registerImage(ids[i], isImageVisibleCallback, options);
			}
		}
	},

	showImage: function(imageNode)
	{
		const imageNodeId = imageNode.id;
		if (!Type.isStringFilled(imageNodeId))
		{
			return;
		}

		let image = this.images[imageNodeId];
		if (!Type.isPlainObject(image))
		{
			return;
		}

		if (image.status == this.imageStatus.undefined)
		{
			this.initImage(image);
		}

		if (image.status !== this.imageStatus.inited)
		{
			return;
		}

		if (
			!image.node
			|| !image.node.parentNode
		)
		{
			image.node = null;
			image.status = this.imageStatus.error;
			return;
		}

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
	},

	showImages: function(checkOwnVisibility)
	{
		checkOwnVisibility = (checkOwnVisibility !== false);

		for (let id in this.images)
		{
			if (!this.images.hasOwnProperty(id))
			{
				continue;
			}

			let image = this.images[id];

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

			let isImageVisible = true;
			if (
				checkOwnVisibility
				&& Type.isFunction(image.func)
			)
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
		const node = document.getElementById(image.id);

		if (!Type.isDomNode(node))
		{
			return;
		}

		const src = node.dataset[image.dataSrcName];
		if (Type.isStringFilled(src))
		{
			image.node = node;
			image.src = src;
			image.status = this.imageStatus.inited;
			image.type = (image.node.tagName.toLowerCase() == "img"
					? this.imageTypes.image
					: this.imageTypes.background
			);
		}
	},

	isElementVisibleOnScreen: function (element)
	{
		const coords = this.getElementCoords(element);
		const windowTop = window.pageYOffset || document.documentElement.scrollTop;
		const windowBottom = windowTop + document.documentElement.clientHeight;

		coords.bottom = coords.top + element.offsetHeight;

		return (
			(coords.top > windowTop && coords.top < windowBottom) // topVisible
			|| (coords.bottom < windowBottom && coords.bottom > windowTop) // bottomVisible
		);
	},

	isElementVisibleOn2Screens: function(element)
	{
		const windowHeight = document.documentElement.clientHeight;


		var windowTop = window.pageYOffset || document.documentElement.scrollTop;
		var windowBottom = windowTop + windowHeight;
		var coords = this.getElementCoords(element);

		coords.bottom = coords.top + element.offsetHeight;

		windowTop -= windowHeight;
		windowBottom += windowHeight;

		return (
			(coords.top > windowTop && coords.top < windowBottom) // topVisible
			|| (coords.bottom < windowBottom && coords.bottom > windowTop) // bottomVisible
		);
	},

	getElementCoords: function(element)
	{
		const box = element.getBoundingClientRect();

		return {
			originTop: box.top,
			originLeft: box.left,
			top: box.top + window.pageYOffset,
			left: box.left + window.pageXOffset
		};
	},

	onScroll: function()
	{
	},

	clearImages: function ()
	{
		this.images = [];
	}
};

export {LazyLoad};