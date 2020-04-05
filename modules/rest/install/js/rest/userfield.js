;(function(){
	'use strict';

	BX.namespace('BX.rest');

	if(typeof(BX.rest.UserField) !== 'undefined')
	{
		return;
	}

	BX.rest.UserField = function(id, param)
	{
		this.id = id;

		this.param = {
			callback: param.callback,
			value: param.value
		};

		this.inited = false;

		this.init();
	};

	BX.rest.UserField.prototype.init = function()
	{
		if(this.inited)
		{
			return;
		}

		var appLayout = BX.rest.AppLayout.get(this.id);
		appLayout.allowInterface(['resizeWindow']);

		var placementInterface = appLayout.messageInterface;
		placementInterface.setValue = BX.proxy(this.setValue, this);
		placementInterface.getValue = BX.proxy(this.getValue, this);
		placementInterface.resizeWindow = function(params, cb)
		{
			var f = BX(this.params.layoutName);
			params.height = parseInt(params.height);

			if(!!params.height)
			{
				f.style.height = params.height + 'px';
			}

			var p = BX.pos(f);
			cb({width: p.width, height: p.height});
		};
	};

	BX.rest.UserField.prototype.setValue = function(value, callback)
	{
		this.param.value = value;

		if(BX.type.isFunction(this.param.callback))
		{
			this.param.callback.apply(this, [value]);
		}

		callback();
	};

	BX.rest.UserField.prototype.getValue = function(params, cb)
	{
		cb(this.param.value);
	};


})();