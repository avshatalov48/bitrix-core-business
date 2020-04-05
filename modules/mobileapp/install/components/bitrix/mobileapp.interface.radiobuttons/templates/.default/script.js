__MARadioButtonsControl = function(params) {
	this.containerId = params.containerId;
	this.rContainers = [];
};

__MARadioButtonsControl.prototype.init = function()
{
	this.rContainers = BX.findChildren(BX(this.containerId), {className: "order_status_li_container"}, true);
};

__MARadioButtonsControl.prototype._onRadioClick = function(id)
{
	var selectedId = this.getSelectedRadio(),
		divRB = BX("div_"+id),
		rb = BX(id);

	if(!rb || ! divRB || selectedId == id)
		return;

	BX.addClass(divRB, "checked");
	this._resetSelectedRadio(selectedId);
	rb.checked = BX.hasClass(divRB,"checked");
};

__MARadioButtonsControl.prototype._resetSelectedRadio = function(id)
{
	if(!id)
		return;

	var rb = false;

	for(var i in this.rContainers)
	{
		if(this.rContainers[i].id != "div_"+id)
			continue;

		if(BX.hasClass(this.rContainers[i], "checked"))
			BX.removeClass(this.rContainers[i], "checked");

		rb = BX(this.rContainers[i].id.substr(4));

		if(rb)
			rb.checked = false;

		break;
	}
};

__MARadioButtonsControl.prototype.getSelectedRadio = function(callback)
{
	var selectedId = false;

	for(var i in this.rContainers)
	{
		if(BX.hasClass(this.rContainers[i], "checked"))
		{
			selectedId = this.rContainers[i].id.substr(4);
			break;
		}
	}

	if(typeof window[callback] == "function")
		window[callback]({"selectedId" : selectedId});

	return selectedId;
};

__MARadioButtonsControl.prototype.makeFastButton = function(id)
{
	var _this = this;

	new FastButton(BX("div_"+id),
					function(e){
						// label & input generate 2 events "onclick" instead one
						if( e.target.parentNode.nodeName == "LABEL" && e.type == "click")
							return false;

						_this._onRadioClick(id);
					},
			false);
};
