var ProfitCalc = function(params)
	{

		this.wrapper = params.wrapper;
		this.topBlockWrapper = params.topBlockWrapper;
		this.cellParams = params.cellParams;
		this.topBlockParams = params.topBlockParams;
		this.titleText = params.calcTitleText;
		this.btnText = params.calcBtnText;
		this.valueNodeList = [];
		this.objList = [];
		this.cellOrderObjList = [];
		this.isEditableOn = false;
		this.toggleBtn = null;
		this.isError = false;
		this.errorClassName = 'adm-profit-error';

		this.init();

	};

ProfitCalc.prototype =
{

	init : function()
	{
		this.createObjList();
		this.createObjNodeList();
		this.createCalcNode();
		this.firstCounting();
		this.painting();
		this.setFixSize();
	},
	createObjNodeList : function()
	{
		this.budget_1 = {
			title : this.cellParams.budget.title,
			isEditable : true,
			node : null,
			getMainObj : BX.proxy(function(){return this.budget},this),
			field : null
		};

		this.clickPrice_2 = {
			title : this.cellParams.clickPrice.title,
			isEditable : true,
			node : null,
			getMainObj : BX.proxy(function(){return this.clickPrice},this),
			field : null
		};

		this.amountClicks_3 = {
			title : this.cellParams.amountClicks.title,
			addClass : 'adm-profit-calc-cel-yellow',
			node : null,
			getMainObj : BX.proxy(function(){return this.amountClicks},this)
		};
		this.amountClicks_4 = {
			title : this.cellParams.amountClicks.title,
			node : null,
			getMainObj : BX.proxy(function(){return this.amountClicks},this)
		};
		this.amountOrders_5 = {
			title : this.cellParams.amountOrders.title,
			node : null,
			getMainObj : BX.proxy(function(){return this.amountOrders},this)
		};
		this.conversion_6 = {
			title : this.cellParams.conversion.title,
			isEditable : true,
			addClass : 'adm-profit-calc-cel-yellow',
			node : null,
			getMainObj : BX.proxy(function(){return this.conversion}, this),
			field : null
		};

		this.grossProfit_7 = {
			title : this.cellParams.grossProfit.title,
			node : null,
			getMainObj : BX.proxy(function(){return this.grossProfit},this)
		};
		this.amountOrders_8 = {
			title : this.cellParams.amountOrders.title,
			node : null,
			getMainObj : BX.proxy(function(){return this.amountOrders},this)
		};
		this.averageBill_9 = {
			title : this.cellParams.averageBill.title,
			isEditable : true,
			addClass : 'adm-profit-calc-cel-red',
			node : null,
			getMainObj : BX.proxy(function(){return this.averageBill},this),
			field : null
		};

		this.budget_10 = {
			title : this.cellParams.budget.title,
			node : null,
			getMainObj : BX.proxy(function(){return this.budget},this)
		};
		this.amountOrders_11 = {
			title : this.cellParams.amountOrders.title,
			node : null,
			getMainObj : BX.proxy(function(){return this.amountOrders},this)
		};
		this.orderPrice_12 = {
			title : this.cellParams.orderPrice.title,
			addClass : 'adm-profit-calc-cel-red',
			node : null,
			getMainObj : BX.proxy(function(){return this.orderPrice},this)
		};
		this.budget_13 = {
			title : this.cellParams.budget.title,
			node : null,
			getMainObj :  BX.proxy(function(){return this.budget},this)
		};
		this.markup_14 = {
			title : this.cellParams.markup.title,
			isEditable : true,
			node : null,
			getMainObj : BX.proxy(function(){return this.markup},this),
			field : null
		};

		this.roi_15 = {
			title : this.cellParams.roi.title,
			addClass : 'adm-profit-calc-cel-violet',
			node : null,
			getMainObj : BX.proxy(function(){return this.roi},this)
		};
		this.other_16 = {
			title : this.cellParams.other.title,
			isEditable : true,
			node : null,
			getMainObj : BX.proxy(function(){return this.other},this),
			field : null
			//addClass: 'adm-profit-calc-footer'
		};

		this.cost_17 = {
			title : this.cellParams.cost.title,
			isEditable : true,
			node : null,
			getMainObj : BX.proxy(function(){return this.cost},this),
			field : null
		};

		this.cellOrderObjList = [
			this.budget_1,       this.clickPrice_2,    this.amountClicks_3,
			this.amountClicks_4, this.amountOrders_5,  this.conversion_6,
			this.grossProfit_7,  this.amountOrders_8,  this.averageBill_9,
			this.budget_10,      this.amountOrders_11, this.orderPrice_12,
			this.budget_13,      this.markup_14,       this.roi_15,
			this.other_16,       this.cost_17
		];

		this.topGrossProfit = {
			node : this.topBlockParams.topGrossProfit,
			getMainObj : BX.proxy(function(){return this.grossProfit},this)
		};
		this.topBudget = {
			node : this.topBlockParams.topBudget,
			getMainObj : BX.proxy(function(){return this.budget},this)
		};
		this.topOther = {
			node : this.topBlockParams.topOther,
			getMainObj: BX.proxy(function(){return this.other},this)
		};
		this.topTotal = {
			node : this.topBlockParams.topTotal,
			getMainObj : BX.proxy(function(){return this.total},this)
		};
		this.topConversion = {
			node : this.topBlockParams.topConversion,
			getMainObj : BX.proxy(function(){return this.conversion},this)
		};

		for(var i = this.cellOrderObjList.length-1; i>=0; i--)
		{
			this.valueNodeList.push(this.cellOrderObjList[i])
		}

		this.valueNodeList.push(this.topGrossProfit);
		this.valueNodeList.push(this.topBudget);
		this.valueNodeList.push(this.topOther);
		this.valueNodeList.push(this.topTotal);
		this.valueNodeList.push(this.topConversion);

	},
	createObjList : function()
	{
		this.budget = {
			firstValue : this.cellParams.budget.value,
			value : this.cellParams.budget.value,
			getValue : BX.proxy(function(){return this.budget_1.field.value},this),
			directDependence : BX.proxy(function(){return this.amountClicks},this),
			isEditable : false
		};
		this.objList.push(this.budget);

		this.clickPrice = {
			firstValue : this.cellParams.clickPrice.value,
			value : this.cellParams.clickPrice.value,
			getValue :  BX.proxy(function(){return this.clickPrice_2.field.value},this),
			directDependence : BX.proxy(function(){return this.amountClicks},this),
			isEditable : false
		};
		this.objList.push(this.clickPrice);

		this.amountClicks = {
			firstValue : null,
			value : null,
			round : function(num)
					{
						return Math.ceil(num);
					},
			formula : BX.proxy(function()
					{
						return this.clickPrice.value
							? this.budget.value / this.clickPrice.value
							: this.budget.value;
					},this),
			isEditable : false
		};
		this.objList.push(this.amountClicks);

		this.amountOrders = {
			firstValue : this.cellParams.amountOrders.value,
			value : this.cellParams.amountOrders.value,
			round : function(num)
					{
						return Math.ceil(num);
					},
			formula : BX.proxy(function()
					{
						return this.amountClicks.value
							? this.conversion.value * this.amountClicks.value / 100
							: this.amountClicks.value;
					},this),
			isEditable : false
		};
		this.objList.push(this.amountOrders);

		this.conversion = {
			firstValue : null,
			value : null,
			round : BX.proxy(function(num)
					{
						return this.round100(num);
					},this),
			formula : BX.proxy(function()
			{
						return this.amountClicks.value
							? this.amountOrders.value / this.amountClicks.value * 100
							: this.amountOrders.value;
					},this),
			getValue :  BX.proxy(function(){return this.conversion_6.field.value},this),
			directDependence : BX.proxy(function(){return this.amountOrders},this),
			isEditable : false
		};
		this.objList.push(this.conversion);

		this.grossProfit = {
			firstValue : this.cellParams.grossProfit.value,
			value : this.cellParams.grossProfit.value,
			round : function(num)
					{
						return Math.ceil(num);
					},
			formula : BX.proxy(function()
					{
						var profit;
						if(this.markup.isActive)
						{
							profit = this.cost.value + (this.cost.value/100 * this.markup.value);
							this.markup.isActive = false;
							this.averageBill.value =
									this.amountOrders.value
									? profit / this.amountOrders.value
									: profit;
							this.averageBill.isEditable = true;
						}
						else
						{
							profit = this.averageBill.value * this.amountOrders.value;
						}
						return profit;

					},this),
			isEditable : false
		};
		this.objList.push(this.grossProfit);

		this.averageBill = {
			firstValue : null,
			value : null,
			round : BX.proxy(function(num)
					{
						return this.round100(num);
					},this),
			formula : BX.proxy(function(){
						return this.amountOrders.value
							? this.grossProfit.value / this.amountOrders.value
							: this.grossProfit.value;
					},this),
			getValue :  BX.proxy(function(){return this.averageBill_9.field.value},this),
			directDependence : BX.proxy(function(){return this.grossProfit},this),
			isEditable : false
		};
		this.objList.push(this.averageBill);

		this.orderPrice = {
			firstValue : null,
			value : null,
			round : BX.proxy(function(num)
							{
								return this.round100(num);
							},this),
			formula : BX.proxy(function(){
						return this.amountOrders.value
							? this.budget.value / this.amountOrders.value
							: this.budget.value;
					},this),
			isEditable : false
		};
		this.objList.push(this.orderPrice);

		this.markup = {
			firstValue : this.cellParams.markup.value,
			value : this.cellParams.markup.value,
			prevValue : this.cellParams.markup.value,
			getValue :  BX.proxy(function(){return this.markup_14.field.value},this),
			directDependence : BX.proxy(function(){return this.grossProfit},this),
			isActive : false,
			isEditable : false
		};
		this.objList.push(this.markup);

		this.cost = {
			firstValue : null,
			value : null,
			round :function(num)
					{
						return Math.round(num);
					},
			formula : BX.proxy(function()
					{
						return this.grossProfit.value - (this.grossProfit.value/(100+this.markup.value)*this.markup.value)
					},this),
			getValue :  BX.proxy(function(){return this.cost_17.field.value},this),
			isEditable : false
		};
		this.objList.push(this.cost);

		this.roi = {
			firstValue : null,
			value : null,
			round : BX.proxy(function(num)
							{
								return this.round100(num);
							},this),
			formula : BX.proxy(function(){
						return this.budget.value || this.other.value
							? (this.grossProfit.value - this.cost.value) / (this.budget.value + this.other.value) * 100
							: this.grossProfit.value - this.cost.value;
					},this),
			isEditable : false
		};
		this.objList.push(this.roi);

		this.other = {
			firstValue : this.cellParams.other.value,
			value : this.cellParams.other.value,
			getValue :  BX.proxy(function(){return this.other_16.field.value},this),
			isEditable : false
		};
		this.objList.push(this.other);

		this.total = {
			firstValue : null,
			value : null,
			round : function(num)
					{
						return Math.ceil(num);
					},
			formula : BX.proxy(function(){
				return this.grossProfit.value - (this.budget.value + this.other.value);
			},this),
			isEditable : false
		};
		this.objList.push(this.total);
	},
	showFields : function()
	{
		for(var i=this.cellOrderObjList.length-1; i>=0; i--)
		{
			if(this.cellOrderObjList[i].isEditable)
			{
				this.cellOrderObjList[i].field.style.fontSize = BX.style(this.cellOrderObjList[i].node, 'font-size');
				this.cellOrderObjList[i].field.style.display = 'block';
				(function(field)
				{
					setTimeout(function()
					{
						field.style.opacity = 1;

					}, 100)
				})(this.cellOrderObjList[i].field)
			}
		}
	},
	hideFields : function()
	{
		for(var i=this.cellOrderObjList.length-1; i>=0; i--)
		{
			if(this.cellOrderObjList[i].isEditable)
			{
				this.cellOrderObjList[i].field.style.fontSize = BX.style(this.cellOrderObjList[i].node, 'font-size');
				this.cellOrderObjList[i].field.style.display = 'none';
				this.cellOrderObjList[i].field.style.opacity = 0;
			}
		}
	},
	editToggle : function()
	{
		if(!this.isEditableOn)
		{
			this.isEditableOn = true;
			BX.addClass(this.toggleBtn, 'adm-profit-calc-toggle-active');
			BX.addClass(this.wrapper, 'adm-profit-block-part-active');
			this.showFields();
		}
		else
		{
			this.isEditableOn = false;
			BX.removeClass(this.toggleBtn, 'adm-profit-calc-toggle-active');
			BX.removeClass(this.wrapper, 'adm-profit-block-part-active');
			this.hideFields();
			this.returnValue();
		}
	},
	toggleButton : function()
	{
		this.toggleBtn = BX.create('div',{props:{className:'adm-profit-calc-toggle'},
									children : [
											BX.create('span',{props:{className:'adm-profit-calc-toggle-text'},
																text : this.btnText
											}),
											BX.create('span',{props:{className:'adm-profit-calc-toggle-btn'}})
									]
		});

		BX.bind(this.toggleBtn, 'click', BX.proxy(this.editToggle, this));

		return this.toggleBtn;
	},
	createCalcNode : function()
	{
		var calcChildren = [];
		var counter = 1;
		var row;
		var title = BX.create('div',{props:{className:'adm-profit-title-wrap'},
									children : [
											BX.create('div',{props:{className:'adm-profit-title adm-profit-title-calc'},
															text:this.titleText
											}),
										this.toggleButton()
									]
				});


		for(var i =0; i<this.cellOrderObjList.length; i++)
		{
			if(counter == 1)
			{
				row = BX.create('div',{props:{className:'adm-profit-calc-row'}});
				calcChildren.push(row);
			}

			row.appendChild(this.createCell(this.cellOrderObjList[i]));

			counter = counter == 3 ? 1 : ++counter;
		}

		var calc = BX.create('div',{props:{className:'adm-profit-calc-block'},
									children : calcChildren
		});

		this.wrapper.appendChild(title);
		this.wrapper.appendChild(calc);

	},
	setFixSize : function()
	{
		var objList = [];
		for(var i= this.cellOrderObjList.length-1; i>=0; i--){
			objList.push({node : this.cellOrderObjList[i].node, maxFontSize : 23, smallestValue : true})
		}

		objList.push({node : this.topGrossProfit.node, maxFontSize : 45});
		objList.push({node : this.topBudget.node, maxFontSize : 29});
		objList.push({node : this.topOther.node, maxFontSize : 29});
		objList.push({node : this.topTotal.node, maxFontSize : 45});
		objList.push({node : this.topConversion.node, maxFontSize : 19});

		BX.FixFontSize.init({
				objList : objList,
				onresize : true
			});


	},
	createField : function()
	{
		var handler;

		if(!BX.browser.IsIE8())
		{
			handler = BX.debounce(this.reCounting, 500, this);
		}else {
			handler = BX.proxy(this.reCounting, this);
		}

		return BX.create('input',{props:{type : 'text', className : 'adm-profit-calc-inp'},
									events:{keyup  : handler}});
	},
	createCell : function(cellObj)
	{
		var inp,
			cellInner,
			cellInnerClassName,
			child,
			children = [];

		var className = cellObj.addClass ? 'adm-profit-calc-cel '+ cellObj.addClass : 'adm-profit-calc-cel';

		cellInnerClassName = cellObj.isEditable ? 'adm-profit-calc-cont adm-profit-calc-cont-inp' : 'adm-profit-calc-cont'

		var title = BX.create('div',{props:{className:'adm-profit-calc-cel-header'},
									text : cellObj.title
					});

		cellInner = BX.create('div',{props:{className : cellInnerClassName}});

		children.push(title);

		if(cellObj == this.other_16 || cellObj == this.cost_17)
			className = className + ' adm-profit-calc-footer';

		if(cellObj.isEditable)
		{
			inp = this.createField();
			children.push(cellInner, inp);
			cellObj.field = inp;
		}
		else
		{
			children.push(cellInner);
		}

		cellObj.node = cellInner;

		return BX.create('div', {
			props : {className : className},
			children : children
		})
	},
	round100 : function(num)
	{
		return (Math.ceil(num *100) / 100);
	},
	firstCounting : function()
	{
		for(var i = 0; i < this.objList.length; i++)
		{
			if(this.objList[i].firstValue === null)
			{
				this.objList[i].value = this.objList[i].firstValue =  this.objList[i].formula();
			}
		}

		this.conversion.formula = null;
		this.averageBill.formula = null;
	},
	reCounting : function(event)
	{
		event = event || window.event;
		var target = event.target || event.srcElement;

		var activeObj,
			fieldValue = this.validation(target.value);

		if(!fieldValue && fieldValue !== 0)
		{
			BX.addClass(target, this.errorClassName)
			return
		}
		else
		{
			BX.removeClass(target, this.errorClassName)
		}

		for(var i = this.cellOrderObjList.length-1; i>=0; i--)
		{
			if(this.cellOrderObjList[i].field == target)
			{
				activeObj = this.cellOrderObjList[i].getMainObj();
			}
		}

		activeObj.value = fieldValue;
		activeObj.isEditable = true;

		if(activeObj == this.markup)
			this.markup.isActive = true;

		if(activeObj.directDependence){
			activeObj.directDependence().value = activeObj.directDependence().formula();
			activeObj.directDependence().isEditable = true;
		}

		for(var b = 0; b < this.objList.length; b++)
		{
			if(this.objList[b].formula && !this.objList[b].isEditable)
			{
				this.objList[b].value = this.objList[b].formula()
			}
			else if(this.objList[b].isEditable){
				this.objList[b].isEditable = false;
			}
		}

		this.painting();
	},
	validation : function(value)
	{
		value = value.replace(',','.');

		return  parseFloat(value);
	},
	painting : function()
	{
		for(var i=this.valueNodeList.length-1; i>=0; i--)
		{
			var mainObj = this.valueNodeList[i].getMainObj();
			var value = mainObj.value;

			value = mainObj.round ? mainObj.round(value) : value;

			this.valueNodeList[i].node.innerHTML = this.addBit(value.toString());

			if(this.valueNodeList[i].field)
			{
				this.valueNodeList[i].field.value = value;

				if(this.valueNodeList[i] == this.averageBill_9 && this.grossProfit.value === 0)
				{
					this.valueNodeList[i].node.innerHTML = 0;
					this.valueNodeList[i].node.style.transition = 'none';
					this.valueNodeList[i].node.style.color = '#000';
					this.valueNodeList[i].field.style.opacity = 0;
				}
				else {
					this.valueNodeList[i].field.style.opacity = 1;
					this.valueNodeList[i].node.style.color = '';
					this.valueNodeList[i].node.style.transition = '';
				}
			}
		}
	},
	addBit :function(str)
	{
		var dotIndex = str.indexOf('.');
		var counter = 0;

		var i = dotIndex !=-1 ? dotIndex-1 : str.length-1;

		for(i; i>=0; i--)
		{
			counter++;

			if(counter == 3)
			{
				str = str.slice(0, i) + ' ' + str.slice(i);
				counter = 0;
			}
		}
		return str;
	},
	returnValue : function()
	{
		for(var i=this.valueNodeList.length-1; i>=0; i--)
		{
			var mainObj = this.valueNodeList[i].getMainObj();
			var value = mainObj.firstValue;

			value = mainObj.round ? mainObj.round(value) : value;

			if(this.valueNodeList[i].field)
			{
				this.valueNodeList[i].field.value = value;
				BX.removeClass(this.valueNodeList[i].field, this.errorClassName)
			}

			this.valueNodeList[i].node.innerHTML = this.addBit(value.toString());
		}

		for(var b = this.objList.length-1; b>=0; b--)
		{
			this.objList[b].value = this.objList[b].firstValue;
		}
	}
};

ProfitCalc.create = function(params)
	{
		return new ProfitCalc(params);
	};




