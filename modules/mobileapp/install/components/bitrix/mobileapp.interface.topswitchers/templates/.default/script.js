__MATopSwitchersControl = function(params) {
	this.itemSelectedId = params.itemSelectedId;
	this.callbackFunc = params.callbackFunc;
};

__MATopSwitchersControl.prototype.onItemClick = function(id)
{
	this.setItemSelected(id);

	if(typeof window[this.callbackFunc] == "function")
		window[this.callbackFunc]({"selectedId" : id});
};

__MATopSwitchersControl.prototype.setItemSelected = function(id)
{
	if(this.itemSelectedId == id)
		return;

	BX.removeClass(BX("top_sw_"+this.itemSelectedId),'current');
	BX.addClass(BX("top_sw_"+id),'current');
	this.itemSelectedId = id;
};

__MATopSwitchersControl.prototype.setFastButton = function(id)
{
	var _this = this;
	new FastButton(BX("top_sw_"+id), function(){ _this.onItemClick(id); }, false);
};
