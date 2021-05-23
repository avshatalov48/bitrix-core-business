;(function(){
	BX.namespace('BX.rest');

	if(!!BX.rest.PlacementCarousel)
	{
		return;
	}

	BX.rest.PlacementCarousel = function(param)
	{
		BX.rest.PlacementCarousel.superclass.constructor.apply(this, arguments);

		if(this.param.current)
		{
			this.loaded[this.param.current] = true;
		}
	};

	BX.extend(BX.rest.PlacementCarousel, BX.rest.Placement);


	BX.rest.PlacementCarousel.prototype.showApp = function(appId)
	{
		BX.hide(this.getAppNode(this.param.current));

		BX.rest.PlacementCarousel.superclass.showApp.apply(this, arguments);

		BX.userOptions.save('rest', 'placement_last', this.param.placement, appId);
	};

	BX.rest.PlacementCarousel.prototype.load = function(appId)
	{
		BX.rest.PlacementCarousel.superclass.load.apply(this, arguments);
	};

	BX.rest.PlacementCarousel.prototype.previous = function()
	{
		var p = 0;
		for(var i = 0; i < this.param.list.length; i++)
		{
			if(this.param.list[i] == this.param.current)
			{
				if(i > 0)
				{
					p = this.param.list[i - 1];
				}
				else
				{
					p = this.param.list[this.param.list.length - 1];
				}

				break;
			}
		}

		this.load(p);
	};

	BX.rest.PlacementCarousel.prototype.next = function()
	{
		var p = 0;
		for(var i = 0; i < this.param.list.length; i++)
		{
			if(this.param.list[i] == this.param.current)
			{
				if(i < this.param.list.length - 1)
				{
					p = this.param.list[i + 1];
				}
				else
				{
					p = this.param.list[0];
				}

				break;
			}
		}

		this.load(p);
	};


})();