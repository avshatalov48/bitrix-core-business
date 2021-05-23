window.RestMapketplaceSearch = (function(){
	var S = function(params)
	{
		this.params = {
			CONTAINER_ID: params.CONTAINER_ID,
			INPUT_ID: params.INPUT_ID,
			MIN_QUERY_LEN: params.MIN_QUERY_LEN
		};

		this.CONTAINER = null;
		this.INPUT = null;

		this.timer = null;

		BX.ready(BX.proxy(this.init, this));
	};

	S.prototype = {
		onChange: function()
		{
			if(this.INPUT.value != this.oldValue && this.INPUT.value != this.startText)
			{
				this.oldValue = this.INPUT.value;

				if(this.INPUT.value.length > this.params.MIN_QUERY_LEN)
				{
					if(this.timer !== null)
					{
						clearTimeout(this.timer);
					}

					this.timer = setTimeout(BX.proxy(this.query, this), 500);
				}
				else if(this.INPUT.value.length == 0)
				{
					this.RESULT.innerHTML = "";
				}
			}
		},

		query: function()
		{
			BX.ajax.get(
				this.params.POST_URL,
				{
					dynamic: 1,
					q: this.INPUT.value
				},
				BX.proxy(this.showResult, this)
			);

			this.timer = null;
		},

		showResult: function(result)
		{
			this.CONTAINER.innerHTML = result;

			if(this.INPUT.value.length == 0)
				this.CONTAINER.style.display = "none";
			else
				if(result)
				{
					this.CONTAINER.style.display = "block";
					this.CONTAINER.innerHTML = result;
				}
				else
					this.CONTAINER.style.display = "none";
		},

		onFocusLost: function()
		{
			setTimeout(BX.delegate(function()
			{
				this.RESULT.style.display = 'none';
			}, this), 250);
		},

		onFocusGain: function()
		{
			if(this.RESULT.innerHTML.length)
			{
				this.RESULT.style.display = 'block';
			}
		},

		init: function()
		{
			this.CONTAINER = BX(this.params.CONTAINER_ID);
			this.INPUT = BX(this.params.INPUT_ID);

			this.RESULT = this.CONTAINER;
			this.startText = this.oldValue = this.INPUT.value;

			this.params.POST_URL = this.INPUT.form.action;

			BX.bind(this.INPUT, 'focus', BX.delegate(function()
			{
				this.onFocusGain()
			}, this));
			BX.bind(this.INPUT, 'blur', BX.delegate(function()
			{
				this.onFocusLost()
			}, this));

			BX.bind(this.INPUT, 'bxchange', BX.delegate(function()
			{
				this.onChange()
			}, this));
		}
	};

	return S;
})();