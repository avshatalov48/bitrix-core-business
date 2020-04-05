/**
 * Class BX.Scale.InfoTable.LoadBar
 * Info Table's LoadBar control
 */
;(function(window) {

	if (BX.Scale.InfoTable.LoadBar) return;

	/**
	 * Class BX.Scale.InfoTable.LoadBar
	 * @constructor
	 */
	BX.Scale.InfoTable.LoadBar = function (id, value)
	{
		this.id = id;
		this.domObj = null;
		this.value = parseInt(value) || 0;
	};

	/**
	 * Returns DOM object contains server data
	 * @returns {object}
	 */

	BX.Scale.InfoTable.LoadBar.prototype.getDomObj = function()
	{
		if(!this.domObj)
		{
			this.domObj = BX.create('span',{props:{className:"adm-table-state"}});
			this.domObj.appendChild(BX.create('span',{props:{className:"adm-table-state-white"}}));
			this.domObj.id = this.id;

			if(parseInt(this.value) > 0)
				this.setValue(this.value);
		}

		return this.domObj;
	};

	/**
	 * Sets the bar value
	 * @param value
	 * @returns {boolean}
	 */
	BX.Scale.InfoTable.LoadBar.prototype.setValue = function(value)
	{
		if(!this.domObj)
			return false;

		value = parseInt(value);

		if(value < 0)
			this.value = 0;
		else if(value > 100)
			this.value = 100;
		else
			this.value = value;

		this.domObj.children[0].style.left = value+"%";

		return value;
	};

})(window);