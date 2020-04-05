function BlankBackend(params)
{
	this.init = function(params)
	{
		this.initParams = params;

		this.timeout = {};

		// Post message
		this.postMessageDomain = null;
		this.postMessageOrigin = null;
		this.postMessageSource = null;

		// Process parameters from top window
		this.initFrameParameters();

		// Start listener of resize events
		this.initEvent();
	};

	this.initFrameParameters = function()
	{
		if(!this.isFrame())
		{
			return;
		}

		if(!window.location.hash)
		{
			return;
		}

		var frameParameters = {};
		try
		{
			frameParameters = JSON.parse(decodeURIComponent(window.location.hash.substring(1)));
		}
		catch (err){}

		if(frameParameters.domain)
		{
			this.postMessageDomain = frameParameters.domain;
		}
	};

	this.isFrame = function()
	{
		return window != window.top;
	};

	this.initEvent = function()
	{
		if(!this.isFrame())
		{
			return;
		}

		if(typeof window.postMessage === 'function')
		{
			BX.bind(window, 'message', BX.proxy(function(event){
				if(event && event.origin == this.postMessageDomain)
				{
					var data = {};
					try { data = JSON.parse(event.data); } catch (err){}
					if (data.action == 'init')
					{
						this.uniqueLoadId = data.uniqueLoadId;
						this.postMessageSource = event.source;
						this.postMessageOrigin = event.origin;
						this.postMessageStartShowed = data.showed;

						var initMessage = {};
						initMessage['uniqueLoadId'] = this.uniqueLoadId;
						initMessage['action'] = 'blank';
						this.sendDataToFrameHolder(initMessage);
					}
				}
			}, this));
		}
	};

	this.sendDataToFrameHolder = function(data)
	{
		var encodedData = JSON.stringify(data);
		if (!this.postMessageOrigin)
		{
			clearTimeout(this.timeout[encodedData]);
			this.timeout[encodedData] = setTimeout(BX.delegate(function(){
				this.sendDataToFrameHolder(data);
			}, this), 10);
			return true;
		}
		if(typeof window.postMessage === 'function')
		{
			if(this.postMessageSource)
			{
				this.postMessageSource.postMessage(
					encodedData,
					this.postMessageOrigin
				);
			}
		}

		var ie = 0 /*@cc_on + @_jscript_version @*/;
		if(ie)
		{
			var url = window.location.hash.substring(1);
			top.location = url.substring(0, url.indexOf('#')) + '#' + encodedData;
		}
	};

	this.init(params);
}