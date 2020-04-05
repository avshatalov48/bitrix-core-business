/**
 * Class BX.Scale.LoadBar
 * Role's LoadBar control
 */
;(function(window) {

	if (BX.Scale.LoadBar) return;

	/**
	 * Class BX.Scale.LoadBar
	 * @constructor
	 */
	BX.Scale.LoadBar = function (id)
	{
		this.id = id;
		this.domObj = null;
		this.value = 0;
	};

	//Dom prototype object to copy from
	BX.Scale.LoadBar.prototype.protoObj = null;

	/**
	 * Returns and creates (if need) prototype object
	 * @returns {domNode}
	 */
	BX.Scale.LoadBar.prototype.getProtoObj = function()
	{
		if(BX.Scale.LoadBar.prototype.protoObj === null)
		{
			BX.Scale.LoadBar.prototype.protoObj = document.createElement("div");
			BX.addClass(BX.Scale.LoadBar.prototype.protoObj, "adm-scale-item-state");
			BX.addClass(BX.Scale.LoadBar.prototype.protoObj, "adm-scale-0");

			for(var i=100; i>=5; i-=5)
			{
				var span = document.createElement("span");
				BX.addClass(span, "adm-state-item");
				BX.addClass(span, "adm-state-item-"+i);
				BX.Scale.LoadBar.prototype.protoObj.appendChild(span);
			}
		}

		return BX.Scale.LoadBar.prototype.protoObj;
	};

	/**
	 * Returns DOM object contains server data
	 * @returns {object}
	 */

	BX.Scale.LoadBar.prototype.getDomObj = function()
	{
		if(!this.domObj)
		{
			this.domObj = this.getProtoObj().cloneNode(true);
			this.domObj.id = this.id;
		}

		return this.domObj;
	};

	/**
	 * Sets the bar value
	 * @param value
	 * @returns {boolean}
	 */
	BX.Scale.LoadBar.prototype.setValue = function(value)
	{
		if(!this.domObj)
			return false;

		value = parseInt(value);
		value = Math.round(value/5)*5;

		BX.removeClass(this.domObj, "adm-scale-"+this.value);

		if(value < 0)
			this.value = 0;
		else if(value > 100)
			this.value = 100;
		else
			this.value = value;

		BX.addClass(this.domObj, "adm-scale-"+this.value);

		return value;
	};

	})(window);