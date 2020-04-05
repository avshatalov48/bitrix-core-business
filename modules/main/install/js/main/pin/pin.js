BX.namespace('BX.Main');

if (typeof(BX.Main.pin) === 'undefined')
{

	BX.Main.pin = function(container)
	{
		this.container = container;
		this.defaultOptions = {
			pinTop: {
				dataKey: 'pin-top', 
				defaultValue: false
			}, 
			pinBottom: {
				dataKey: 'pin-bottom', 
				defaultValue: false
			}, 
			pinClass: {
				dataKey: 'pin-class', 
				defaultValue: 'bx-pinned'
			},
			pinTopClass: {
				dataKey: 'pin-top-class', 
				defaultValue: 'bx-pinned-top'
			}, 
			pinBottomClass: {
				dataKey: 'pin-bottom-class', 
				defaultValue: 'bx-pinned-bottom'
			},
			pinUseControll: {
				dataKey: 'pin-use-controll', 
				defaultValue: false
			},
			pinControllClass: {
				dataKey: 'pin-controll-class', 
				defaultValue: 'bx-pin-controll'
			},
			pinControllPinClass: {
				dataKey: 'pin-controll-pin-class', 
				defaultValue: 'bx-pin-controll-pin'
			},
			pinControllUnpinClass: {
				dataKey: 'pin-controll-unpin-class', 
				defaultValue: 'bx-pin-controll-unpin'
			},
			offsetTop: {
				dataKey: 'pin-offset-top',
				defaultValue: 0
			},
			offsetBottom: {
				dataKey: 'pin-offset-bottom',
				defaultValue: 0
			},
			idPrefix: {
				dataKey: 'pin-id-prefix',
				defaultValue: 'bxpin'
			},
			useOuterWidth: {
				dataKey: 'pin-use-outer-width',
				defaultValue: ""
			},
			useInnerWidth: {
				dataKey: 'pin-use-inner-width',
				defaultValue: ""
			},
			niceAttachment: {
				dataKey: 'pin-use-nice-attachment',
				defaultValue: true
			}
		};
		this.settings = null;
		this.controll = null;
		this.cache = {};
		this.init();
	};


	BX.Main.pin.prototype = 
	{
		init: function()
		{
			this.prepareSettings();
			this.makeControll();
			this.bindOnClickControll();
			this.adjustPosition();
			this.bindOnScrollWindow();
			this.bindOnResizeWindow();
		},

		prepareSettings: function()
		{
			var optionsKeys = Object.keys(this.defaultOptions);
			var result = {};
			var dataValue = null;
			var self = this;

			optionsKeys.forEach(function(current) {
				dataValue = BX.data(self.container, self.defaultOptions[current].dataKey);
				dataValue = (dataValue === 'true') ? true : dataValue;
				dataValue = (dataValue === 'false') ? false : dataValue;
				result[current] = dataValue ? dataValue : self.defaultOptions[current].defaultValue;
			});

			this.settings = result;
		},

		makeControll: function()
		{
			var controll = null;
			var className = '';

			if (this.settings.pinUseControll)
			{
				className = [
					this.settings.pinControllClass,
					this.getUserPinState() ? this.settings.pinControllPinClass : this.settings.pinControllUnpinClass
				].join(' ');

				controll = BX.create('span', {props: {className: className}});

				this.container.appendChild(controll);
				this.controll = controll;
			}
		},

		bindOnClickControll: function()
		{
			BX.bind(this.controll, 'click', BX.delegate(this._onClickControll, this));
		},

		bindOnScrollWindow: function()
		{
			BX.bind(window, 'scroll', BX.delegate(this._onScroll, this));
		},

		bindOnResizeWindow: function()
		{
			BX.bind(window, 'resize', BX.delegate(this._onResize, this));
		},

		getScrollTopPosition: function()
		{
			return BX.scrollTop(window);
		},

		getParentRect: function()
		{	
			return BX.pos(this.container.parentNode);
		},

		getContainerRect: function()
		{
			var result = this.getCache('getContainerRect');

			if (!result)
			{
				result = BX.pos(this.container);
				this.setCache('getContainerRect', result);
			}

			return result;
		},

		getContainerWidth: function()
		{
			var result = this.getCache('getContainerWidth');

			if (!result)
			{
				result = BX.width(this.container);
				this.setCache('getContainerWidth', result);
			}

			return result;
		},

		setContainerWidth: function(width)
		{
			this.setCache('getContainerWidth', width);
			BX.width(this.container, width + 'px');
		},

		getCache: function(key)
		{
			if (!key)
			{
				return;
			}

			return this.cache[key] ? this.cache[key] : null;
		},

		setCache: function(key, value)
		{
			return this.cache[key] = value;
		},

		getStyleProperty: function(property, selector)
		{
			var sheets = document.styleSheets;
			var result = this.getCache('getStyleProperty');

			if (!result)
			{
			    for (var i = 0, l = sheets.length; i < l; i++) {
			        var sheet = sheets[i];
			        if( !sheet.cssRules ) { continue; }
			        for (var j = 0, k = sheet.cssRules.length; j < k; j++) {
			            var rule = sheet.cssRules[j];
			            if (rule.selectorText && rule.selectorText.split(',').indexOf(selector) !== -1) {
			                result = rule.style[property].replace('px', '');
			            }
			        }
			    }

			    this.setCache('getStyleProperty', result);
			}

		    return result;
		},

		pin: function(className, offset)
		{
			var resultVal = null;
			var currentOffset;
			var propName = className == this.settings.pinTopClass ? 'top' : 'bottom';
			var currentWidth = this.getParentWidth();
			var containerHeight;

			if (offset)
			{
				currentOffset = this.getStyleProperty(propName, '.' + className);
				currentOffset = propName == 'top' ? 
					parseFloat(currentOffset) + parseFloat(offset) : 
					parseFloat(currentOffset) - parseFloat(offset);
			}

			BX.addClass(this.container, className);
			BX.style(this.container, propName, currentOffset + 'px');
			BX.style(this.container, 'width', currentWidth + 'px');

			if (this.settings.niceAttachment) 
			{
				containerHeight = BX.height(this.container);
				this.fake = BX.create('div', {style: {opacity: 0, height: containerHeight + 'px'}});
				this.container.parentNode.appendChild(this.fake);
				this.container.parentNode.insertBefore(this.fake, this.container);
			}
		},

		unpin: function()
		{
			BX.remove(this.fake);
			BX.removeClass(this.container, this.settings.pinTopClass);
			BX.removeClass(this.container, this.settings.pinBottomClass);
			this.container.removeAttribute('style');
		},

		adjustPosition: function()
		{
			var containerRect, scrollTop;

			if (this.settings.pinUseControll && !this.getUserPinState()) 
			{
				return;
			}

			containerRect = this.getContainerRect();
			scrollTop = this.getScrollTopPosition();

			if (this.settings.pinTop && (scrollTop >= (containerRect.top - this.settings.offsetTop)))
			{
				if (!this.isPinned())
				{
					this.pin(this.settings.pinTopClass, this.settings.offsetTop);
				}
			}

			if (this.settings.pinTop && (scrollTop <= (containerRect.top - this.settings.offsetTop)))
			{
				if (this.isPinned())
				{
					this.unpin();
				}
			}


			if (this.settings.pinBottom && ((scrollTop + BX.height(window)) >= (containerRect.bottom - this.settings.offsetBottom)))
			{
				if (!this.isPinned())
				{
					this.pin(this.settings.pinBottomClass, this.settings.offsetBottom);
				}
			}

			if (this.settings.pinBottom && ((scrollTop + BX.height(window)) <= (containerRect.bottom - this.settings.offsetBottom)))
			{
				if (this.isPinned())
				{
					this.unpin();
				}
			}
		},

		isPinned: function()
		{
			return (
				BX.hasClass(this.container, this.settings.pinTopClass) || 
				BX.hasClass(this.container, this.settings.pinBottomClass)
			);
		},

		getId: function()
		{
			var result = null;

			if (BX.type.isNotEmptyString(this.container.id))
			{
				result = this.settings.idPrefix + this.container.id;
			}

			return result;
		},

		setUserPinState: function(value)
		{	
			var key;

			if (!window.localStorage)
			{

			}

			key = this.getId();

			window.localStorage.setItem(key, JSON.stringify(value));
		},

		getUserPinState: function()
		{
			var key;

			if (!window.localStorage)
			{
				return;
			}

			key = this.getId();

			return JSON.parse(window.localStorage.getItem(key));
		},

		getParentWidth: function()
		{
			var width, paddingLeft, paddingRight, outerWidthContainer, innerWidthContainer;

			if (BX.type.isNotEmptyString(this.settings.useOuterWidth) && 
				!BX.type.isNotEmptyString(this.settings.useInnerWidth))
			{
				outerWidthContainer = BX.findParent(this.container, {class: this.settings.useOuterWidth});
				width = BX.width(outerWidthContainer);
			}
			else
			{
				innerWidthContainer = BX.findParent(this.container, {class: this.settings.useInnerWidth});
				width = parseFloat(BX.width(innerWidthContainer));
				paddingLeft = parseFloat(BX.style(innerWidthContainer, 'padding-left'));
				paddingRight = parseFloat(BX.style(innerWidthContainer, 'padding-right'));
				width = (width - (paddingLeft + paddingRight));
			}

			if (!BX.type.isNotEmptyString(this.settings.useOuterWidth) && 
				!BX.type.isNotEmptyString(this.settings.useInnerWidth))
			{
				width = BX.width(this.container.parentNode);
				paddingLeft = parseFloat(BX.style(this.container.parentNode, 'padding-left'));
				paddingRight = parseFloat(BX.style(this.container.parentNode, 'padding-right'));
				width = (width - (paddingLeft + paddingRight));
			}

			return width;
		},

		_onClickControll: function(event)
		{
			if (BX.hasClass(this.controll, this.settings.pinControllPinClass))
			{
				BX.removeClass(this.controll, this.settings.pinControllPinClass);
				BX.addClass(this.controll, this.settings.pinControllUnpinClass);
				this.setUserPinState(false);
				this.unpin();
			}
			else
			{
				BX.addClass(this.controll, this.settings.pinControllPinClass);
				BX.removeClass(this.controll, this.settings.pinControllUnpinClass);
				this.setUserPinState(true);
			}
		},

		_onScroll: function(event)
		{
			this.adjustPosition();
		},

		_onResize: function(event)
		{
			var width;

			if (this.isPinned())
			{
				width = this.getParentWidth();
				this.setContainerWidth(width ? width : this.getContainerWidth());
			}			
		}

	};


	BX(function() {
		var pinContainers = BX.findChild(document, {'class': 'bx-pin'}, true, true);
		pinContainers.map(function(current) {
			new BX.Main.pin(current);
		});		
	});


}