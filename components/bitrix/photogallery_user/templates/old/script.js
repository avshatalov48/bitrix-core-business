function PhotoPopupMenu()
{
	var _this = this;
	this.active = null;
	
	this.PopupShow = function(div, pos)
	{
		this.PopupHide();
		if(!div)
			return false;
		if (typeof(pos) != "object")
			pos = {};
			
		this.active = div.id;
	    div.ondrag = jsUtils.False;
		
		jsUtils.addEvent(document, "keypress", _this.OnKeyPress);
		
		div.style.width = div.offsetWidth + 'px';
		div.style.visibility = 'visible';
		
		pos['top'] = div.offsetHeight;
		pos['left'] = div.offsetWidth;
		
		div.style._display = div.style.display; 
		div.style.display = 'none';
		var res = jsUtils.GetWindowSize();
		div.style.display = div.style._display; 
		
		pos['top'] = parseInt(res["scrollTop"] + res["innerHeight"]/2 - pos['top']/2);
		pos['left'] = parseInt(res["scrollLeft"] + res["innerWidth"]/2 - pos['left']/2);
		
		jsFloatDiv.Show(div, pos["left"], pos["top"], true, true, false);

		var substrate = document.getElementById("photo_substrate");
		if (!substrate)
		{
			substrate = document.createElement("DIV");
			substrate.id = 	"photo_substrate";
			substrate.style.position = "absolute";
			substrate.style.display = "none";
			substrate.style.background = "#052635";
			substrate.style.opacity = "0.5";
			substrate.style.top = "0";
			substrate.style.left = "0";
			if (substrate.style.MozOpacity)
				substrate.style.MozOpacity = '0.5';
			else if (substrate.style.KhtmlOpacity)
				substrate.style.KhtmlOpacity = '0.5';
			if (jsUtils.IsIE())
		 		substrate.style.filter += "progid:DXImageTransform.Microsoft.Alpha(opacity=50)";
			document.body.appendChild(substrate);
		}
		
		substrate.style.width = res["scrollWidth"] + "px";
		substrate.style.height = res["scrollHeight"] + "px";
		substrate.style.zIndex = 99;
		substrate.style.display = 'block';
		
		return false;
	}

	this.PopupHide = function()
	{
		if (!_this.active || _this.active.length <= 0)
			return false;
		var div = document.getElementById(_this.active);
		if(div)
		{
			jsFloatDiv.Close(div);
			div.parentNode.removeChild(div);
		}
		var substrate = document.getElementById("photo_substrate");
		if (substrate) { substrate.style.display = 'none'; } 

		this.active = null;
//		jsUtils.removeEvent(document, "click", _this.CheckClick);
		jsUtils.removeEvent(document, "keypress", _this.OnKeyPress);
		return false;
	}

	this.CheckClick = function(e)
	{
		var div = document.getElementById(_this.active);
		
		if(!div)
		{
			return false;
		}

		if (div.style.visibility != 'visible')
			return false;
			
		if (!jsUtils.IsIE() && e.target.tagName == 'OPTION')
			return false;
			
		var x = e.clientX + document.body.scrollLeft;
		var y = e.clientY + document.body.scrollTop;

		/*menu region*/
		var posLeft = parseInt(div.style.left);
		var posTop = parseInt(div.style.top);
		var posRight = posLeft + div.offsetWidth;
		var posBottom = posTop + div.offsetHeight;
		if(x >= posLeft && x <= posRight && y >= posTop && y <= posBottom)
			return false;

		if(_this.controlDiv)
		{
			var pos = jsUtils.GetRealPos(_this.controlDiv);
			if(x >= pos['left'] && x <= pos['right'] && y >= pos['top'] && y <= pos['bottom'])
				return true;
		}
		_this.PopupHide();
		
		return false;
	}

	this.OnKeyPress = function(e)
	{
		if(!e) e = window.event
		if(!e) return false;
		if(e.keyCode == 27)
			_this.PopupHide();
		return;
	},

	this.IsVisible = function()
	{
		return (document.getElementById(this.active).style.visibility != 'hidden');
	}
}
var PhotoMenu = new PhotoPopupMenu();