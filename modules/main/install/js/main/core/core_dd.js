(function(window){
BX.DD = function(params)
{
	return new BX.DD.dragdrop(params);
}

BX.DD.allowSelection = function()
{
	document.onmousedown = null;
	var b = document.body;
	b.ondrag = null;
	b.onselectstart = null;
	b.style.MozUserSelect = '';
	
	// if (jsDD.current_node)
	// {
		// jsDD.current_node.ondrag = null;
		// jsDD.current_node.onselectstart = null;
		// jsDD.current_node.style.MozUserSelect = '';
	// }
}
	
BX.DD.denySelection = function()
{
	document.onmousedown = BX.False;
	var b = document.body;
	b.ondrag = BX.False;
	b.onselectstart = BX.False;
	b.style.MozUserSelect = 'none';
	// if (jsDD.current_node) 
	// {
		// jsDD.current_node.ondrag = jsUtils.False;
		// jsDD.current_node.onselectstart = jsUtils.False;
		// jsDD.current_node.style.MozUserSelect = 'none';
	// }
}

BX.DD.dragdrop = function(params)
{


}

/*
 * BX.DD.dropFiles - for html5 drag and drop files
 *
 * example:
 *
 * BX(function() {
 *	  var dropBoxNode = BX('WebDAV23');
 *    var dropbox = new BX.DD.dropFiles(dropBoxNode);
 *    if (dropbox && dropbox.supported())
 *    {
 *        BX.addCustomEvent(dropbox, 'dropFiles', function(files) { WDUploadDroppedFiles(files);});
 *        BX.addCustomEvent(dropbox, 'dragEnter', function() {BX.addClass( dropBoxNode, 'droptarget');});
 *        BX.addCustomEvent(dropbox, 'dragLeave', function() {BX.removeClass( dropBoxNode, 'droptarget');});
 *    }
 * });
 *
 * to save files use BX.ajax.FormData
 */
BX.DD.dropFiles = function(div)
{
	if (BX.type.isElementNode(div)
		&& this.supported())
	{
		div.setAttribute('dropzone', 'copy f:*/*');
		this.DIV = div;
		this._timer = null;
		this._initEvents();

		this._cancelLeave = function()
		{
			if (this._timer != null)
			{
				clearTimeout(this._timer);
				this._timer = null;
			}
		}
		this._prepareLeave = function()
		{
			this._cancelLeave();
			this._timer = setTimeout( BX.delegate(function() {
				BX.onCustomEvent(this, 'dragLeave')
			}, this), 100);
		}

		return this;
	}
	return false;
}

BX.DD.dropFiles.prototype._initEvents = function()
{
	BX.bind(this.DIV, 'dragover', BX.proxy(this._dragOver, this));
	BX.bind(this.DIV, 'dragenter', BX.proxy(this._dragEnter, this));
	BX.bind(this.DIV, 'dragleave', BX.proxy(this._dragLeave, this));
	BX.bind(this.DIV, 'dragexit', BX.proxy(this._dragExit, this));
	BX.bind(this.DIV, 'drop', BX.proxy(this._drop, this));
}

BX.DD.dropFiles.prototype._dragEnter = function(e)
{
	BX.PreventDefault(e);
	this._cancelLeave();
	BX.onCustomEvent(this, 'dragEnter', [e]);
	return true;
}

BX.DD.dropFiles.prototype._dragExit = function(e)
{
	BX.PreventDefault(e);
	this._prepareLeave();
	return false;
}


BX.DD.dropFiles.prototype._dragLeave = function(e)
{
	BX.PreventDefault(e);
	this._prepareLeave();
	return false;
}

BX.DD.dropFiles.prototype._dragOver = function(e)
{
	BX.PreventDefault(e);
	this._cancelLeave();
	return true;
}

BX.DD.dropFiles.prototype._drop = function(e)
{
	BX.PreventDefault(e);
	var dt = e.dataTransfer;
	var files = dt.files;
	BX.onCustomEvent(this, 'dropFiles', [files, e]);
	BX.onCustomEvent(this, 'dragLeave')
	return false;
}

BX.DD.dropFiles.prototype.isEventSupported = function(event)
{
	var div = BX.create('DIV');
	var eventName = 'on'+event;
	var result = (eventName in div);
	
	if (!result && div.setAttribute && div.removeAttribute)
	{
		div.setAttribute(eventName, '');
		result = (typeof div[eventName] === 'function');
	}

	div = null;
	return result;
}

BX.DD.dropFiles.prototype.supported = function()
{
	return ( (!!window.FileReader) && this.isEventSupported('dragstart') && this.isEventSupported('drop') );
}

})(window)

