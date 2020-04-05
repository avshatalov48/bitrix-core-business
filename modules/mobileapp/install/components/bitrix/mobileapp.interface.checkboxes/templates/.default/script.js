__MACheckBoxControl = function(params) {
	this.containerId = params.containerId;
	this.resultCallback = params.resultCallback;
	this.ownIds = params.ownIds;
};

__MACheckBoxControl.prototype.getContainers = function()
{
	return BX.findChildren(BX(this.containerId), {className: "order_acceptpay_li_container"}, true);
};

__MACheckBoxControl.prototype.onCheckBoxClick = function(id)
{
	var cb = BX(id),
		divCb = BX("div_"+id);

	if(!cb || !divCb)
		return false;

	BX.toggleClass(BX(divCb),'checked');
	cb.checked = BX.hasClass(divCb,'checked');
};

__MACheckBoxControl.prototype.makeFastButton = function(id)
{
	var _this = this;

	new FastButton(BX("div_"+id),
					function(e){

						// label & input generate 2 events "onclick" instead one
						if( e.target.parentNode.nodeName == "LABEL" && e.type == "click")
							return false;
						_this.onCheckBoxClick(id);
					},
			false);
};

__MACheckBoxControl.prototype.getChecked = function()
{
	var cbContainers = this.getContainers();
	var arChecked = [];

	for(var i in cbContainers)
	{
		if(BX.hasClass(cbContainers[i],"checked"))
		{
			var input = BX(cbContainers[i].id.substr(4));

			if(input)
				arChecked.push(input.value);
		}
	}

	if(typeof window[this.resultCallback] == 'function')
		window[this.resultCallback]({"arChecked" : arChecked});

	return arChecked;
};
