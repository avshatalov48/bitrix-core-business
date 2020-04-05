;(function() {

	'use strict';

	BX.namespace('BX.UI');

	BX.UI.Pinner = function(node, options)
	{
		this.node = node;
		this.options = options || {};
		if (typeof(this.options.pinTop) === "undefined" && typeof(this.options.pinBottom) === "undefined")
		{
			this.options.pinTop = true;
			this.options.pinBottom = true;
		}
		else
		{
			this.options.pinTop = !!this.options.pinTop;
			this.options.pinBottom = !!this.options.pinBottom;
		}

		this.options.fixTop = !!this.options.fixTop;
		this.options.fixBottom = !!this.options.fixBottom;

		this.options.initialWidth = node.style.width;

		this.init();
	};
	BX.UI.Pinner.prototype = {
		node: null,
		options: {},
		timeout: 0,
		classUi: 'ui-pinner',
		classTop: 'ui-pinner-top',
		classBottom: 'ui-pinner-bottom',
		classFullWidth: 'ui-pinner-full-width',
		init: function ()
		{
			var node = this.node;
			var options = this.options;

			if (!options.anchorTop)
			{
				var anchorTop = document.createElement('div');
				node.parentNode.insertBefore(anchorTop, node);
				options.anchorTop = anchorTop;
			}
			if (!options.anchorBottom)
			{
				var anchorBottom = document.createElement('div');
				node.parentNode.insertBefore(anchorBottom, node.nextSibling);
				options.anchorBottom = anchorBottom;
			}

			BX.bind(
				window,
				'scroll',
				BX.throttle(this.onChange.bind(this), 200)
			);
			BX.bind(
				window,
				'resize',
				BX.throttle(this.onChange.bind(this), this.timeout)
			);

			this.onChange();
		},
		onChange: function ()
		{
			var node = this.node;
			var options = this.options;

			var box;
			if (this.options.fixBottom || (this.options.pinTop && this.isNodeOnScreenBottom(options.anchorBottom)))
			{
				this.applyNodeWidth();
				BX.removeClass(node, this.classTop);
				BX.addClass(node, this.classUi);
				BX.addClass(node, this.classBottom);

				box = BX.pos(node);//node.getBoundingClientRect();
				options.anchorBottom.style.height = 0;
				options.anchorBottom.style.height = box.height + 'px';
			}
			else if (this.options.fixTop || (this.options.pinTop && this.isNodeOnScreenTop(options.anchorTop)))
			{
				this.applyNodeWidth();
				BX.removeClass(node, this.classBottom);
				BX.addClass(node, this.classUi);
				BX.addClass(node, this.classTop);

				box = BX.pos(node);//node.getBoundingClientRect();
				options.anchorTop.style.height = 0;
				options.anchorTop.style.height = box.height + 'px';
			}
			else
			{
				BX.removeClass(node, this.classTop);
				BX.removeClass(node, this.classBottom);
				BX.removeClass(node, this.classUi);
				node.style.width = options.initialWidth ? options.initialWidth : null;

				options.anchorTop.style.height = 0;
				options.anchorBottom.style.height = 0;
			}
		},
		applyNodeWidth: function ()
		{
			if (this.options.fullWidth)
			{
				BX.addClass(this.node, this.classFullWidth);
				return;
			}

			var anchorNode = this.options.anchorTop || this.options.anchorBottom;
			var box = anchorNode.getBoundingClientRect();
			this.node.style.width = box.width + 'px';
		},
		isNodeOnScreenTop: function (node)
		{
			var coordinates = this.getCoordinates(node);
			return coordinates.top < coordinates.windowTop
				&&
				coordinates.bottom < coordinates.windowBottom;
		},
		isNodeOnScreenBottom: function (node)
		{
			var coordinates = this.getCoordinates(node);
			return (
					coordinates.bottom > coordinates.windowBottom
					&&
					coordinates.top > coordinates.windowTop
				)
				||
				document.documentElement.scrollHeight - node.scrollHeight < 40;
		},
		getCoordinates: function(node)
		{
			var box = node.getBoundingClientRect();
			var top = box.top + window.pageYOffset;
			var windowTop = window.pageYOffset || document.documentElement.scrollTop;

			return {
				windowTop: windowTop,
				windowBottom: windowTop + document.documentElement.clientHeight,
				top: top,
				bottom: top + node.offsetHeight,
				left: box.left + window.pageXOffset,
				width: box.width
			};
		}
	};

})();