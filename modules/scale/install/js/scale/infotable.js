/**
 * Class BX.Scale.InfoTable
 * Server's InfoTable control
 */
;(function(window) {

	if (BX.Scale.InfoTable) return;

	/**
	 * Class BX.Scale.InfoTable
	 * @constructor
	 */
	BX.Scale.InfoTable = function (hostname, categories)
	{
		this.hostname = hostname;
		this.maxParamsCount = 0;
		this.structure = categories;
		this.loadBars = {};
		this.tableObj = null;
		this.toggleButton = null;

		for(var category in categories)
		{
			var paramsCount = 0;
			for(var param in categories[category].PARAMS)
			{
				paramsCount++;
			}

			if(this.maxParamsCount < paramsCount)
				this.maxParamsCount = paramsCount;
		}
	};

	/**
	 * Returns DOM object contains InfoTable
	 * @returns {object}
	 */
	BX.Scale.InfoTable.prototype.getDomObj = function()
	{
		if(!this.domObj)
		{
			this.domObj = BX.create('div',{props:{className:"adm-scale-table-wrap"}});
			var table = BX.create('table',{props:{className:"adm-scale-info-table"}}),
				tr = BX.create('tr'),
				paramsTable = [],
				col = 0,
				row,
				param;

			BX.addClass(tr, "no_toggle");

			for(var cat in this.structure)
			{
				tr.appendChild(BX.create('td', {props:{className:"adm-scale-info-table-header"}, html: this.structure[cat].NAME}));
				row = 0;

				for(param in this.structure[cat].PARAMS)
				{
					if(!paramsTable[row])
						paramsTable[row] = [];

					paramsTable[row][col] = this.structure[cat].PARAMS[param];
					this.structure[cat].PARAMS[param].id = this.hostname+"_infotable_"+cat+"_"+param;
					row++;
				}

				while(row < this.maxParamsCount)
				{
					if(!paramsTable[row])
						paramsTable[row] = [];

					paramsTable[row][col] = null;
					row++;
				}

				col++;
			}

			table.appendChild(tr);

			for( row=0; row<this.maxParamsCount; row++)
			{
				tr = BX.create('tr');

				if(row == 0)
					BX.addClass(tr, "no_toggle");

				for(col in paramsTable[row])
				{
					var value = "0",
						td = BX.create('td');

					if(paramsTable[row] && paramsTable[row][col])
					{
						td.appendChild(BX.create('text',{html: paramsTable[row][col].NAME}));
						td.appendChild(BX.create('br'));

						if(paramsTable[row][col].VALUE)
							value = paramsTable[row][col].VALUE;

						param = paramsTable[row][col];
						var	itemObj = null;

						if(param.TYPE && param.TYPE == "ARRAY")
						{
							itemObj = BX.create('DIV');

							for(var i in param.ITEMS)
							{
								var tmpObj = this.getItemObj(param.id+"_"+i, param.ITEMS[i].TYPE, value[i]);
								itemObj.appendChild(tmpObj);
							}
						}
						else
						{
							itemObj = this.getItemObj(param.id, param.TYPE, value);
						}

						td.appendChild(itemObj);
					}

					tr.appendChild(td);
				}

				table.appendChild(tr);
			}

			this.domObj.appendChild(table);
			this.tableObj = table;
			this.toggleButton = BX.create('span',{props:{className:"adm-scale-table-btn"}});
			BX.addClass(this.toggleButton,"toggled");
			BX.addClass(this.tableObj,"toggled");
			BX.bind(this.toggleButton, 'click', BX.proxy(this.toggle, this));
			this.domObj.appendChild(this.toggleButton);
		}

		return this.domObj;
	};
	BX.Scale.InfoTable.prototype.toggle = function(e)
	{
		if(!this.tableObj)
			return;
		
		BX.toggleClass(this.toggleButton,"toggled");
		BX.toggleClass(this.tableObj,"toggled");
	};

	BX.Scale.InfoTable.prototype.getItemObj = function(id, type, value)
	{
		var result = null;
		if(type == "LOADBAR")
		{
			this.loadBars[id] = new BX.Scale.InfoTable.LoadBar(id, value);
			result = this.loadBars[id].getDomObj();
		}
		else
		{
			result = BX.create('strong',{props:{id: id}, html: value});
		}

		return result;
	}

	/**
	 * Sets values
	 * @param values
	 * @returns {boolean}
	 */
	BX.Scale.InfoTable.prototype.setValues = function(values)
	{
		for(var category in values)
		{
			for(var param in values[category])
			{
				var id = this.hostname+"_infotable_"+category+"_"+param,
					monParam = this.structure[category].PARAMS[param],
					value = values[category][param];

				if(monParam.TYPE && monParam.TYPE == "ARRAY")
				{
					for(var i in monParam.ITEMS)
					{
						this.setItemValue(id+"_"+i, monParam.ITEMS[i].TYPE, value[i]);
					}
				}
				else
				{
					this.setItemValue(id, monParam.TYPE, value);
				}
			}
		}
	};

	BX.Scale.InfoTable.prototype.setItemValue = function(id, type, value)
	{
		if(type == "LOADBAR" && this.loadBars[id])
			this.loadBars[id].setValue(value);
		else
			BX(id).innerHTML = value;
	};

	BX.Scale.InfoTable.prototype.getStructure = function()
	{
		var result = {};

		for(var cat in this.structure)
		{
			result[cat] = [];

			for(var param in this.structure[cat].PARAMS)
				result[cat].push(param);

			if(result[cat].length <=0 )
				delete result[cat];
		}

		return result;
	};

	})(window);