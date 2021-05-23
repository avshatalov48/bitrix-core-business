function debug_info(text)
{
	container_id = 'debug_info_forum';
	var div = document.getElementById(container_id);
	if (!div || div == null)
	{
		div = document.body.appendChild(document.createElement("DIV"));
		div.id = container_id;
//		div.className = "forum-debug";
		div.style.position = "absolute";
		div.style.width = "170px";
		div.style.padding = "5px";
		div.style.backgroundColor = "#FCF7D1";
		div.style.border = "1px solid #EACB6B";
		div.style.textAlign = "left";
		div.style.zIndex = '7900'; 
		div.style.fontSize = '11px'; 
		
		div.style.left = document.body.scrollLeft + (document.body.clientWidth - div.offsetWidth) - 5 + "px";
		div.style.top = document.body.scrollTop + 5 + "px";
	}
	if (typeof text == "object")
	{
		for (var ii in text)
		{
			div.innerHTML += ii + ': ' + text[ii] + "<br />";
		}
	}
	else
	{
		div.innerHTML += text + "<br />";
	}
	return;
}
/************************************************/

function PhotoPopupMenu()
{
	var _this = this;
	this.active = null;
	this.just_hide_item = false;
	this.events = null;
	
	this.PopupShow = function(div, pos, set_width, set_shadow, events)
	{
		this.PopupHide();
		if (!div) { return; } 
		if (typeof(pos) != "object") { pos = {}; } 

		this.active = div.id;
		
		if (set_width !== false && !div.style.width)
		{
			div.style.width = div.offsetWidth + 'px';
		}
		
		this.events = ((events && typeof events == "object") ? events : null);

		var res = jsUtils.GetWindowSize();
		
		pos['top'] = (pos['top'] ? pos['top'] : parseInt(res["scrollTop"] + res["innerHeight"]/2 - div.offsetHeight/2));
		pos['left'] = (pos['left'] ? pos['left'] : parseInt(res["scrollLeft"] + res["innerWidth"]/2 - div.offsetWidth/2));
		
		jsFloatDiv.Show(div, pos["left"], pos["top"], set_shadow, true, false);
		div.style.display = '';
		
		jsUtils.addEvent(document, "keypress", _this.OnKeyPress);
		
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
		substrate.style.zIndex = 7500;
		substrate.style.display = 'block';
	}

	this.PopupHide = function()
	{
		this.active = (this.active == null && arguments[0] ? arguments[0] : this.active);
		
		this.CheckEvent('BeforeHide');
		
		var div = document.getElementById(this.active);
		if (div)
		{
			jsFloatDiv.Close(div);
			div.style.display = 'none';
			if (!this.just_hide_item) {div.parentNode.removeChild(div); } 
		}
		var substrate = document.getElementById("photo_substrate");
		if (substrate) { substrate.style.display = 'none'; } 

		this.active = null;
		
		jsUtils.removeEvent(document, "keypress", _this.OnKeyPress);
		
		this.CheckEvent('AfterHide');
		this.events = null;
	}

	this.CheckClick = function(e)
	{
		var div = document.getElementById(_this.active);
		
		if (!div || !_this.IsVisible()) { return; }
		if (!jsUtils.IsIE() && e.target.tagName == 'OPTION') { return false; }
		
		var x = e.clientX + document.body.scrollLeft;
		var y = e.clientY + document.body.scrollTop;

		/*menu region*/
		var posLeft = parseInt(div.style.left);
		var posTop = parseInt(div.style.top);
		var posRight = posLeft + div.offsetWidth;
		var posBottom = posTop + div.offsetHeight;
		
		if (x >= posLeft && x <= posRight && y >= posTop && y <= posBottom) { return; }

		if(_this.controlDiv)
		{
			var pos = jsUtils.GetRealPos(_this.controlDiv);
			if(x >= pos['left'] && x <= pos['right'] && y >= pos['top'] && y <= pos['bottom'])
				return;
		}
		_this.PopupHide();
	}

	this.OnKeyPress = function(e)
	{
		if(!e) e = window.event
		if(!e) return;
		if(e.keyCode == 27)
			_this.PopupHide();
	},

	this.IsVisible = function()
	{
		return (document.getElementById(this.active).style.visibility != 'hidden');
	}, 
	
	this.CheckEvent = function()
	{
		if (!this.events || this.events == null)
		{
			return false;
		}
		
		eventName = arguments[0];
		
		if (this.events[eventName]) 
		{ 
			return this.events[eventName](arguments); 
		} 
		return true;
	}
}
var PhotoMenu;
if (!PhotoMenu) 
	PhotoMenu = new PhotoPopupMenu();

var jsUtilsPhoto = {
	GetElementParams : function(element)
	{
		if (!element) return false;
		if (element.style.display != 'none' && element.style.display != null)
			return {width: element.offsetWidth, height: element.offsetHeight};
		var originstyles = {position: element.style.position, visibility : element.style.visibility, display: element.style.display};
		element.style.position = 'absolute';
		element.style.visibility = 'hidden';
		element.style.display = 'block';
		var result = {width: element.offsetWidth, height: element.offsetHeight};
		element.style.display = originstyles.display;
		element.style.visibility = originstyles.visibility;
		element.style.position = originstyles.position;
		return result;
	}, 
	ClassCreate : function(parent, properties)
	{
		function oClass() { 
			this.init.apply(this, arguments); 
		}
		
		if (parent) 
		{
			var temp = function() { };
			temp.prototype = parent.prototype;
			oClass.prototype = new temp;
		}
		
		for (var property in properties)
			oClass.prototype[property] = properties[property];
		if (!oClass.prototype.init)
			oClass.prototype.init = function() {};
		
		oClass.prototype.constructor = oClass;
		
		return oClass;
	}, 
	ObjectsMerge : function(arr1, arr2)
	{
		var arr3 = {};
		for (var key in arr1)
			arr3[key] = arr1[key];
		for (var key in arr2)
			arr3[key] = arr2[key];
		return arr3;
	}
}; 

window.bPhotoMainLoad = true;