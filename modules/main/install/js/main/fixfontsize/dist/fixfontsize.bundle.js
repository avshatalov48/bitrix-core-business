(function() {
	BX.FixFontSize = function(params)
	{
		var widthNode, computedStyles, width;

		this.node = null;
		this.prevWindowSize = 0;
		this.prevWrapperSize = 0;
		this.mainWrapper = null;
		this.textWrapper = null;
		this.objList = params.objList;
		this.minFontSizeList = [];
		this.minFontSize = 0;

		if (params.onresize)
		{
			this.prevWindowSize = window.innerWidth || document.documentElement.clientWidth;
			BX.bind(window, 'resize', BX.throttle(this.onResize, 350, this));
		}

		if (params.onAdaptiveResize)
		{
			widthNode = this.objList[0].scaleBy || this.objList[0].node;
			computedStyles = getComputedStyle(widthNode);
			this.prevWrapperSize = parseInt(computedStyles["width"]) - parseInt(computedStyles["paddingLeft"]) - parseInt(computedStyles["paddingRight"]);
			BX.bind(window, 'resize', BX.throttle(this.onAdaptiveResize, 350, this));
		}

		this.createTestNodes();
		this.decrease();
	};

	BX.FixFontSize.prototype =
		{
			createTestNodes: function()
			{
				this.textWrapper = BX.create('div',{
					style : {
						display : 'inline-block',
						whiteSpace : 'nowrap'
					}
				});

				this.mainWrapper = BX.create('div',{
					style : {
						height : 0,
						overflow : 'hidden'
					},
					children : [this.textWrapper]
				});

			},
			insertTestNodes: function()
			{
				document.body.appendChild(this.mainWrapper);
			},
			removeTestNodes: function()
			{
				document.body.removeChild(this.mainWrapper);
			},
			decrease: function()
			{
				var width,
					fontSize,
					widthNode,
					computedStyles;

				this.insertTestNodes();

				for(var i=this.objList.length-1; i>=0; i--)
				{
					widthNode = this.objList[i].scaleBy || this.objList[i].node;
					computedStyles = getComputedStyle(widthNode);
					width  = parseInt(computedStyles["width"]) - parseInt(computedStyles["paddingLeft"]) - parseInt(computedStyles["paddingRight"]);
					fontSize = parseInt(getComputedStyle(this.objList[i].node)["font-size"]);

					this.textWrapperSetStyle(this.objList[i].node);

					if(this.textWrapperInsertText(this.objList[i].node))
					{
						while(this.textWrapper.offsetWidth > width && fontSize > 0)
						{
							this.textWrapper.style.fontSize = --fontSize + 'px';
						}

						if(this.objList[i].smallestValue)
						{
							this.minFontSize = this.minFontSize ? Math.min(this.minFontSize, fontSize) : fontSize;

							this.minFontSizeList.push(this.objList[i].node)
						}
						else
						{
							this.objList[i].node.style.fontSize = fontSize + 'px';
						}
					}
				}

				if(this.minFontSizeList.length > 0)
					this.setMinFont();

				this.removeTestNodes();

			},
			increase: function()
			{
				this.insertTestNodes();
				var width,
					fontSize,
					widthNode,
					computedStyles;

				this.insertTestNodes();

				for(var i=this.objList.length-1; i>=0; i--)
				{
					widthNode = this.objList[i].scaleBy || this.objList[i].node;
					computedStyles = getComputedStyle(widthNode);
					width  = parseInt(computedStyles["width"]) - parseInt(computedStyles["paddingLeft"]) - parseInt(computedStyles["paddingRight"]);
					fontSize = parseInt(getComputedStyle(this.objList[i].node)["font-size"]);

					this.textWrapperSetStyle(this.objList[i].node);

					if(this.textWrapperInsertText(this.objList[i].node))
					{
						while(this.textWrapper.offsetWidth < width && fontSize < this.objList[i].maxFontSize)
						{
							this.textWrapper.style.fontSize = ++fontSize + 'px';
						}

						fontSize--;

						if(this.objList[i].smallestValue)
						{
							this.minFontSize = this.minFontSize ? Math.min(this.minFontSize, fontSize) : fontSize;

							this.minFontSizeList.push(this.objList[i].node)
						}
						else
						{
							this.objList[i].node.style.fontSize = fontSize + 'px';
						}
					}
				}

				if(this.minFontSizeList.length > 0)
					this.setMinFont();

				this.removeTestNodes();
			},
			setMinFont : function()
			{
				for(var i = this.minFontSizeList.length-1; i>=0; i--)
				{
					this.minFontSizeList[i].style.fontSize = this.minFontSize + 'px';
				}

				this.minFontSize = 0;
			},
			onResize : function()
			{
				var width = window.innerWidth || document.documentElement.clientWidth;

				if(this.prevWindowSize > width)
					this.decrease();

				else if (this.prevWindowSize < width)
					this.increase();

				this.prevWindowSize = width;
			},
			onAdaptiveResize : function()
			{
				var widthNode = this.objList[0].scaleBy || this.objList[0].node,
					computedStyles = getComputedStyle(widthNode),
					width = parseInt(computedStyles["width"]) - parseInt(computedStyles["paddingLeft"]) - parseInt(computedStyles["paddingRight"]);

				if (this.prevWrapperSize > width)
					this.decrease();
				else if (this.prevWrapperSize < width)
					this.increase();

				this.prevWrapperSize = width;
			},
			textWrapperInsertText : function(node)
			{
				if(node.textContent){
					this.textWrapper.textContent = node.textContent;
					return true;
				}
				else if(node.innerText)
				{
					this.textWrapper.innerText = node.innerText;
					return true;
				}
				else {
					return false;
				}
			},
			textWrapperSetStyle : function(node)
			{
				this.textWrapper.style.fontFamily = getComputedStyle(node)["font-family"];
				this.textWrapper.style.fontSize = getComputedStyle(node)["font-size"];
				this.textWrapper.style.fontStyle = getComputedStyle(node)["font-style"];
				this.textWrapper.style.fontWeight = getComputedStyle(node)["font-weight"];
				this.textWrapper.style.lineHeight = getComputedStyle(node)["line-height"];
			}
		};

	BX.FixFontSize.init = function(params)
	{
		return new BX.FixFontSize(params);
	};
})();