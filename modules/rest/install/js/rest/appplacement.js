;(function()
{
	BX.namespace('BX.rest');

	if(!!BX.rest.Placement)
	{
		return;
	}

	BX.rest.Placement = function(param)
	{
		this.param = {
			placement: '',
			layout: '',
			node: '',
			list: [],
			extendedList: new Map(),
			ajaxUrl: location.href
		};

		this.setParameters(param);

		this.loaded = {};
	};

	BX.rest.Placement.prototype = {

		setParameters: function(param)
		{
			for (var i in param)
			{
				if (param.hasOwnProperty(i))
				{
					if (i === 'extendedList')
					{
						param['extendedList'].forEach(function(item) {
							this.param['extendedList'].set(item['id'], item);
						}.bind(this));
					}
					else
					{
						this.param[i] = param[i];
					}
				}
			}
		},

		getAppNode: function(appId)
		{
			return BX(this.param.node.replace('#ID#', appId));
		},

		showApp: function(appId)
		{
			BX.show(this.getAppNode(appId));
		},

		load: function(appId, placementOptions)
		{
			this.showApp(appId);

			if(!this.loaded[appId])
			{
				BX.ajax.post(
					this.param.ajaxUrl,
					{
						'placement_action': 'load',
						'app': appId,
						'placement_options': placementOptions,
						'sessid': BX.bitrix_sessid()
					},
					BX.delegate(function(result)
					{
						this.getAppNode(appId).innerHTML = result;
						this.appLoaded(appId);
					}, this)
				);
			}

			this.param.current = appId;
		},

		appLoaded: function(appId)
		{
			this.loaded[appId] = true;
		},

		unload: function(appId)
		{
			var appLayout = BX.rest.AppLayout.get(appId);
			if(!!appLayout)
			{
				appLayout.destroy();
			}

			this.loaded[appId] = false;
		},

		destroy: function()
		{
			for(var appId in this.loaded)
			{
				if(this.loaded.hasOwnProperty(appId))
				{
					this.unload(appId);
				}
			}
		}

	};


})();