
function BXColorPicker(oPar/*, pLEditor*/)
{
	if (!oPar.name)
		oPar.name = oPar.id;
	if (!oPar.title)
		oPar.title = oPar.name;
	this.disabled = false;
	this.bCreated = false;
	this.bOpened = false;
	this.zIndex = oPar.zIndex ? oPar.zIndex : 1000;
	this.fid = oPar.id.toLowerCase(); // + '_' + pLEditor.id;

	//this.pLEditor = pLEditor;
	this.oPar = oPar;

	this.oneGifSrc = '/bitrix/images/1.gif';

	this.BeforeCreate();
}

BXColorPicker.prototype.BeforeCreate = function()
{
	var _this = this;
	this.pWnd = BX.create("IMG", {
		props: {
			src: this.oneGifSrc,
			title: this.oPar.title,
			className: "bx-colpic-button bx-colpic-button-normal",
			id: "bx_btn_" + this.oPar.id.toLowerCase()
		}
	});

	this.pWnd.onmouseover = function(e){_this.OnMouseOver(e, this)};
	this.pWnd.onmouseout = function(e){_this.OnMouseOut(e, this)};
	this.pWnd.onclick = function(e){_this.OnClick(e, this)};
	this.pCont = BX.create("DIV", {props: {className: 'bx-colpic-button-cont'}});
	this.pCont.appendChild(this.pWnd);
}

BXColorPicker.prototype.Create = function ()
{
	var _this = this;
	window['bx_colpic_keypress_' + this.fid] = function(e){_this.OnKeyPress(e);};
	window['bx_colpic_click_' + this.fid] = function(e){_this.OnDocumentClick(e);};

	this.pColCont = document.body.appendChild(BX.create("DIV", {props: {className: "bx-colpic-cont"}, style: {zIndex: this.zIndex}}));

	var arColors = [
	'#FF0000', '#FFFF00', '#00FF00', '#00FFFF', '#0000FF', '#FF00FF', '#FFFFFF', '#EBEBEB', '#E1E1E1', '#D7D7D7', '#CCCCCC', '#C2C2C2', '#B7B7B7', '#ACACAC', '#A0A0A0', '#959595',
	'#EE1D24', '#FFF100', '#00A650', '#00AEEF', '#2F3192', '#ED008C', '#898989', '#7D7D7D', '#707070', '#626262', '#555555', '#464646', '#363636', '#262626', '#111111', '#000000',
	'#F7977A', '#FBAD82', '#FDC68C', '#FFF799', '#C6DF9C', '#A4D49D', '#81CA9D', '#7BCDC9', '#6CCFF7', '#7CA6D8', '#8293CA', '#8881BE', '#A286BD', '#BC8CBF', '#F49BC1', '#F5999D',
	'#F16C4D', '#F68E54', '#FBAF5A', '#FFF467', '#ACD372', '#7DC473', '#39B778', '#16BCB4', '#00BFF3', '#438CCB', '#5573B7', '#5E5CA7', '#855FA8', '#A763A9', '#EF6EA8', '#F16D7E',
	'#EE1D24', '#F16522', '#F7941D', '#FFF100', '#8FC63D', '#37B44A', '#00A650', '#00A99E', '#00AEEF', '#0072BC', '#0054A5', '#2F3192', '#652C91', '#91278F', '#ED008C', '#EE105A',
	'#9D0A0F', '#A1410D', '#A36209', '#ABA000', '#588528', '#197B30', '#007236', '#00736A', '#0076A4', '#004A80', '#003370', '#1D1363', '#450E61', '#62055F', '#9E005C', '#9D0039',
	'#790000', '#7B3000', '#7C4900', '#827A00', '#3E6617', '#045F20', '#005824', '#005951', '#005B7E', '#003562', '#002056', '#0C004B', '#30004A', '#4B0048', '#7A0045', '#7A0026'
	];

	var
		row, cell, colorCell,
		tbl = BX.create("TABLE", {props:{className: 'bx-colpic-tbl adm-workarea'}}),
		i, l = arColors.length;

	row = tbl.insertRow(-1);
	cell = row.insertCell(-1);
	cell.colSpan = 8;
	cell.className = "bx-color-inp-cell-default-button";
	var defBut = cell.appendChild(BX.create("INPUT", {
			style:{width:"100%"},
			attrs:{
				type:"submit",
				value: window.jsColorPickerMess.DefaultColor
			}
		}));
	defBut.onmouseover = function()
	{
		colorCellInner.style.backgroundColor = 'transparent';
	};
	defBut.onclick = function(e){_this.Select("#FFFFFF");};

	colorCell = row.insertCell(-1);
	colorCell.colSpan = 8;

	var colorCellInner = BX.create("SPAN", {props: {className: 'bx-inner-color-cell'}});
	colorCell.appendChild(colorCellInner);
	colorCell.className = 'bx-color-inp-cell';
	colorCellInner.style.backgroundColor = arColors[38];
	for(i = 0; i < l; i++)
	{
		if (Math.round(i / 16) == i / 16) // new row
		{
			row = tbl.insertRow(-1);
			row.className = "bx-color-row";
		}


		cell = row.insertCell(-1);
		cell.className = 'bx-colpic-col-cell';
		colorBox = BX.create("SPAN", {props: {className: 'bx-inner-color-box'}});
		cell.appendChild(colorBox);
		colorBox.style.backgroundColor = arColors[i];
		colorBox.id = 'bx_color_id__' + i;

		colorBox.onmouseover = function (e)
		{
			colorCellInner.style.backgroundColor = arColors[this.id.substring('bx_color_id__'.length)];
		};
		colorBox.onclick = function (e)
		{
			var k = this.id.substring('bx_color_id__'.length);
			_this.Select(arColors[k]);
		};
	}

	this.pColCont.appendChild(tbl);
	this.bCreated = true;
};


BXColorPicker.prototype.OnClick = function (e, pEl)
{
	if(this.disabled)
		return false;

	if (!this.bCreated)
		this.Create();
	if (this.bOpened)
		return this.Close();

	this.Open();
};

BXColorPicker.prototype.Open = function (node)
{

	var element = (typeof node != "undefined")? node: this.pWnd;
	var
		pos = BX.align(element, 240, 130),
		_this = this;

	//this.pLEditor.oPrevRange = this.pLEditor.GetSelectionRange();
	BX.bind(window, "keypress", window['bx_colpic_keypress_' + this.fid]);
	BX.defer(function(){BX.bind(window, "click", window['bx_colpic_click_' + _this.fid]);})();
	//pOverlay.onclick = function(){_this.Close()};

	this.pColCont.style.display = 'block';
	this.pColCont.style.top = pos.top + 'px';
	this.pColCont.style.left = pos.left + 'px';

	this.bOpened = true;
};

BXColorPicker.prototype.Close = function ()
{
	this.pColCont.style.display = 'none';
	//this.pLEditor.oTransOverlay.Hide();
	BX.unbind(window, "keypress", window['bx_colpic_keypress_' + this.fid]);
	BX.unbind(window, "click", window['bx_colpic_click_' + this.fid]);

	this.bOpened = false;
}

BXColorPicker.prototype.OnMouseOver = function (e, pEl)
{
	if(this.disabled)
		return;
	pEl.className = 'bx-colpic-button bx-colpic-button-over';
}

BXColorPicker.prototype.OnMouseOut = function (e, pEl)
{
	if(this.disabled)
		return;
	pEl.className = 'bx-colpic-button bx-colpic-button-normal';
}

BXColorPicker.prototype.OnKeyPress = function(e)
{
	if(!e)
		e = window.event;
	if(e.keyCode == 27)
		this.Close();
};

BXColorPicker.prototype.OnDocumentClick = function (e)
{
	if(!e)
		e = window.event;

	var target = e.target || e.srcElement;
	if (target && !BX.findParent(target, {className: 'bx-colpic-cont'}))
		this.Close();
};

BXColorPicker.prototype.Select = function (color)
{
	//this.pLEditor.SelectRange(this.pLEditor.oPrevRange);
	if (this.oPar.OnSelect && typeof this.oPar.OnSelect == 'function')
		this.oPar.OnSelect(color, this);
	this.Close();
};
