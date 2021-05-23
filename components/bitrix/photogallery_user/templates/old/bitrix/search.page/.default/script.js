PhotoClassResize = function() 
{
	var _this = this;
	this.oTable = false, 
	this.oImages = [], 
	this.oText = [], 
	this.iWidth = false, 
	this.iWidthSpace = 5,
	this.iCountCell = 5;
	
	this.Init = function(oTablePointer, width)
	{
		if (!oTablePointer || parseInt(width)<=0)
			return;
			
		this.oTable = oTablePointer;
		var arr = this.oTable.getElementsByTagName("th");
		for(var i=0; i <= arr.length; i++)
		{
			if (arr[i] && arr[i].id && (arr[i].id.indexOf("result_image") != -1))
				this.oImages.push(arr[i]);
		}
		
		arr = this.oTable.getElementsByTagName("td");
		for(var i=0; i <= arr.length; i++)
		{
			if (arr[i] && arr[i].id && (arr[i].id.indexOf("result_text") != -1))
				this.oText.push(arr[i]);
		}
		this.iWidth = parseInt(width);
		window.onresize=_this.Resize;
		this.Resize();
	}
	
	this.Resize = function()
	{
		var cells = _this.GetCountCell();
		if(_this.iCountCell == cells)
		{
			return;
		}
		
		_this.iCountCell = cells;
		
		var tbody = document.createElement("tbody");
		var rows = _this.oImages.length/cells;
		var width = (100/cells) + "%";
//		var width = _this.iWidth + "px";
		var counter = 0;
		var td = document.createElement("td");
		td.className = "empty";
/*		var div = document.createElement("div");
		div.className = "empty";
		td.appendChild(div);
*/		
		for(var j = 0; j < (rows+1); j++)
		{
			var row1 = document.createElement("tr");
			var row2 = document.createElement("tr");
			var row3 = document.createElement("tr");
				row3.className = "empty";
			for(var i = counter*cells; i < cells*(counter+1); i++)
			{
				if(!_this.oImages[i])
				{
					break;
				}
				
				_this.oImages[i].style.width=width;
				row1.appendChild(_this.oImages[i]);
				row2.appendChild(_this.oText[i]);
				row3.appendChild(td.cloneNode(true));
				if (i != (cells*(counter+1) - 1))
				{
					row1.appendChild(td.cloneNode(true));
					row2.appendChild(td.cloneNode(true));
					row3.appendChild(td.cloneNode(true));
				}
			}
			tbody.appendChild(row1);
			tbody.appendChild(row2);
			tbody.appendChild(row3);
			counter++;
		}
		
		tbody = _this.oTable.appendChild(tbody);
		var prev = tbody.previousSibling;
		prev.parentNode.removeChild(prev);
	}
	
	this.GetCountCell = function(returnPx)
	{
		var count = Math.floor(this.oTable.offsetWidth/(this.iWidth+this.iWidthSpace));
		if (returnPx)
			return count;
		count = (count > 0 ? count : 1);
		return count;
	}
}

bPhotoUtils = true;