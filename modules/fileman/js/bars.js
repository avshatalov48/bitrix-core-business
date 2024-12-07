/*
класс обработки тулбарсетов

pWnd - указатель на ячейку таблицы <td> в котором находится тулбарсет
bVertical - тулбарсет для вертикальных или горизонтальных тулбаров

*/
function BXToolbarSet(pColumn, pMainObj, bVertical)
{
	this.className = 'BXToolbarSet';
	pColumn.unselectable = "on";
	this.pWnd = pColumn;
	this.pMainObj = pMainObj;
	this.bVertical = bVertical;
	this.pWnd.className = 'bxedtoolbarset';
	if(bVertical)
	{
		pColumn.style.verticalAlign = "top";
		pColumn.innerHTML = '<img src="/bitrix/images/1.gif" width="1" height="0">';
		this.pWnd = pColumn.appendChild(this.pMainObj.pDocument.createElement("TABLE"));
		this.pWnd.unselectable = "on";
		this.pWnd.cellSpacing = 0;
		this.pWnd.cellPadding = 0;
		this.pWnd.border = 0;
		this.pWnd.insertRow(0);
		this.pParent = pColumn;
	}

	/*
	проверяет - попадает ли координата в область тулбарсета (плюс погрешность рядышком)
	возвращает
		массив:
			"row" => строка,
			"col" => столбец в который ближе всего попадает координата,
			"addrow" => попала между двумя строками тулбаров
	 или false если она слишком далеко
	*/
	BXToolbarSet.prototype.HitTest = function (px, py)
	{
		var delta = 5;

		var position = GetRealPos((this.bVertical ? this.pParent : this.pWnd));
		if(
			position["left"] - delta < px && px < position["right"] + delta
			&& position["top"] - delta < py && py < position ["bottom"] + delta
		)
		{
			//window.status = 'L:' + position['left'] + '; T: '+position['top'] + '; R: ' + position['right'] + '; B: ' + position['bottom'] + '; px = ' + px + '; py = ' + py;
			var result = Array();
			result["row"] = 0;
			result["col"] = 0;
			result["addrow"] = false;

			// найдем все имеющиеся внутри тулбары
			var allNodes;
			if(this.bVertical)
				allNodes = this.pWnd.rows[0].cells;
			else
				allNodes = this.pWnd.childNodes;

			if(!allNodes || allNodes.length<=0)
			{
				result["addrow"] = true;
				return result;
			}

			var allCells, j;
			for(var i=0; i < allNodes.length; i++)
			{
				var toolbar_position = GetRealPos(allNodes[i]);
				if(this.bVertical)
				{
					if(toolbar_position["left"] - delta < px && px < toolbar_position["right"] + delta)
					{
						if(toolbar_position["left"] + delta > px)
						{
							result["addrow"] = true;
							result["col"] = i;
						}
						else if(toolbar_position["right"] - delta < px)
						{
							result["addrow"] = true;
							result["col"] = i+1;
						}
						else
						{
							result["col"] = i;
							allCells = allNodes[i].childNodes[0].rows;
							for(j = allCells.length-1; j > 0; j--)
							{
								var celltemp = allCells[j].cells[0];
								var celltemp_position = GetRealPos(celltemp);
								if(celltemp_position["top"] - delta < py)
								{
									result["row"] = j;
									break;
								}
							}
						}
						return result;
					}
				}
				else
				{
					// если точка находится внутри таблицы по высоте
					if(toolbar_position["top"] - delta < py && py < toolbar_position["bottom"] + delta)
					{
						if(toolbar_position["top"] + delta > py)
						{
							result["addrow"] = true;
							result["row"] = i;
						}
						else if(toolbar_position["bottom"] - delta < py)
						{
							result["addrow"] = true;
							result["row"] = i + 1;
						}
						else
						{
							result["row"] = i;
							allCells = allNodes[i].rows[0].cells;
							for(j = allCells.length-1; j > 0; j--)
							{
								var cell_position = GetRealPos(allCells[j]);
								if(cell_position["left"] - delta < px)
								{
									result["col"] = j;
									return result;
								}
							}
						}
						return result;
					}
				}
			}
		}
		return false;
	}

	BXToolbarSet.prototype.__AddRow = function (id)
	{
		var t = this.pMainObj.pDocument.createElement("TABLE");
		t.id = id;
		t.cellSpacing = 0;
		t.cellPadding = 0;
		t.border = 0;
		t.unselectable = "on";
		var r = t.insertRow(0);
		return t;
	}

	/*
	Добавляет тулбар в тулбарсет в заданную позицию
	Параметры:
		pToolbar - ссылка на объект типа BXToolbar,
		row, col - строка, столбец в который добавить тулбар,
		bAddRow - true: всегда добавлять новую строку на месте col, false - пытаться добавлять в строку row
	Устанавливает флажок BXToolbar.bDocked, делает тулбар relative с родителем в нужном месте, убирает шапку
	и добавляет справа и слева обрамляющие области.
	Чтобы отклеить тулбар метод BXToolbar.UnDock
	*/
	BXToolbarSet.prototype.AddToolbar = function (pToolbar, row, col, bAddRow)
	{
		pToolbar.bDocked = true;
		var pColTable = null;
		var rowIcons;
		pToolbar.SetDirection(this.bVertical);
		if(this.bVertical)
		{
			var cols = this.pWnd.rows[0].cells;
			var pRow, tTable;
			if(col>cols.length)
				col = cols.length;
			if(col >= cols.length || bAddRow)
			{
				var ctmp = this.pWnd.rows[0].insertCell(col);
				ctmp.style.verticalAlign = "top";
				tTable = ctmp.appendChild(this.pMainObj.pDocument.createElement("TABLE"));
				tTable.cellSpacing = 0;
				tTable.cellPadding = 0;
				tTable.border = 0;
				tTable.unselectable = "on";
			}
			else
			{
				tTable = cols[col].childNodes[0];
				if(tTable.clientHeight + pToolbar.pWnd.clientHeight > this.pMainObj.arConfig["height"])
					return this.AddToolbar(pToolbar, row, col+1, bAddRow);
			}

			if(row>tTable.rows.length)
				row = tTable.rows.length;

			pRow = tTable.insertRow(row);
			pColTable = pRow.insertCell(0);
		}
		else
		{
			var allNodes = this.pWnd.childNodes;
			var pRowTable;
			if(row>allNodes.length)
				row = allNodes.length;
			if(row >= allNodes.length || bAddRow)
			{
				var t = this.pMainObj.pDocument.createElement("TABLE"); t.cellSpacing = 0; t.cellPadding = 0; t.border = 0; t.unselectable = "on";
				var r = t.insertRow(0);
				if(row >= allNodes.length)
					pRowTable = this.pWnd.appendChild(t);
				else
					pRowTable = this.pWnd.insertBefore(t, allNodes[row]);
			}
			else
			{
				pRowTable = allNodes[row];
				if(pRowTable.clientWidth + pToolbar.pWnd.clientWidth > this.pMainObj.arConfig["width"])
					return this.AddToolbar(pToolbar, row+1, col, bAddRow);
			}

			if(col>pRowTable.rows[0].cells.length)
				col = pRowTable.rows[0].cells.length;


			pColTable = pRowTable.rows[0].insertCell(col);

			rowIcons = pToolbar.pIconsTable.rows[0];
			rowIcons.cells[0].style.display = GetDisplStr(1);
			rowIcons.cells[rowIcons.cells.length-1].style.display = GetDisplStr(1);
		}

		pToolbar.row = row;
		pToolbar.col = col;

		pToolbar.pWnd.style.position = "relative";
		pToolbar.pWnd.style.zIndex = "1";
		pToolbar.pWnd.style.left = null;
		pToolbar.pWnd.style.top = null;

		pToolbar.pTitleRow.style.display = "none";

//alert(pToolbar.pWnd.outerHTML);
//return;
		pToolbar.pWnd = pColTable.appendChild(pToolbar.pWnd);
		pToolbar.pToolbarSet = this;
		pToolbar.parentCell = pColTable;
		this.__ReCalc();
	}

	/*
	Удаляет тулбар pToolbar из тулбарсета:
		удаляет его из ячейки таблицы тулбарсета, если таблица тулбарсета пустая, удаляет и ее.
		обнуляет BXToolbar.pToolbarSet
	*/
	BXToolbarSet.prototype.DelToolbar = function (pToolbar)
	{
		pToolbar.parentCell.removeChild(pToolbar.pWnd);
		pToolbar.pToolbarSet = null;
		this.__ReCalc();
	}

	BXToolbarSet.prototype.__ReCalc = function ()
	{
		var allNodes;
		var i, j, pToolbar;
		if(this.bVertical)
		{
			var cols = this.pWnd.rows[0].cells;
			for(i=cols.length-1; i>=0; i--)
			{
				allNodes = cols[i].childNodes[0].rows;
				for(j=allNodes.length-1; j>=0; j--)
					if(allNodes[j].cells[0].childNodes.length<=0)
						cols[i].childNodes[0].deleteRow(j);
				if(cols[i].childNodes[0].rows.length<=0)
					this.pWnd.rows[0].deleteCell(i);
			}

			for(i=0; i<cols.length; i++)
			{
				allNodes = cols[i].childNodes[0].rows;
				for(j=0; j<allNodes.length; j++)
				{
					pToolbar = allNodes[j].cells[0].childNodes[0].pObj;
					pToolbar.row = j;
					pToolbar.col = i;
				}
			}
		}
		else
		{
			allNodes = this.pWnd.childNodes;
			for(i=allNodes.length-1; i>=0; i--) // горизонтальные таблицы (строки)
			{
				var tbl = allNodes[i];
				for(j=tbl.rows[0].cells.length-1; j>=0; j--)
					if(tbl.rows[0].cells[j].childNodes.length<=0)
						tbl.rows[0].deleteCell(j);

				//если в строке нет ячеек, удаляем всю таблицу
				if(tbl.rows[0].cells.length<=0)
					this.pWnd.removeChild(tbl);
			}

			for(i=0; i<allNodes.length; i++)
				for(j=0; j<allNodes[i].rows[0].cells.length; j++)
				{
					pToolbar = allNodes[i].rows[0].cells[j].childNodes[0].pObj;
					pToolbar.row = i;
					pToolbar.col = j;
				}
		}
	}
}


BXToolbar.prototype.fun = function(e)
{
//		var a=xc("s");

	return;
	if(window.event)
		e = window.event;
	rpos = GetRealPos(this);
	if(Math.abs(e.clientX - rpos["left"])<3)
		this.style.cursor = 'E-resize';
	else if(Math.abs(e.clientX - rpos["right"])<3)
		this.style.cursor = 'E-resize';
	else if(Math.abs(e.clientY - rpos["top"])<3)
		this.style.cursor = 'N-resize';
	else if(Math.abs(e.clientY - rpos["bottom"])<3)
		this.style.cursor = 'N-resize';
	else
		this.style.cursor = 'default';

	if(this.drag && this.style.cursor == 'n-resize')
	{
		//alert(e.clientY - rpos["top"]);
		this.style.height = e.clientY - rpos["top"]+3;
		//alert(this.innerHTML);
	}

}


/////////////////////////////////////////////////////////////////////////////////////
// класс BXToolbar - тулбар
//   pWnd - указатель на TABLE тулбара
//   bDragging - состояние перетягивания
//   bDocked - состояние - в окне (false) или пристыкован (true)
//	 pTitleRow - указатель на строку <tr> в которой находится заголовок тулбара
//	 pIconsTable - указатель на таблицу <table> в ячейках которой находятся кнопки тулбара
//	 pToolbarSet - указатель на объект BXToolbarSet, к которому приклеен тулбар
//	 	bVertical - вертикальный или горизонтальный тулбар
//	 	parentCell - указатель на конкретную ячейку таблицы из тулбарсета
//	 	row - номер строки в тулбарсете
//	 	col - номер столбца в тулбарсете
/////////////////////////////////////////////////////////////////////////////////////
function BXToolbar(pMainObj, title, dx, dy)
{
	this.pMainObj = pMainObj;
	this.className = 'BXToolbar';
	this.id = Math.random();
	this.bVertical = false;
	this.title = title;
	this.arButtons = Array();

	var obj = this;
	var tableToolbar = pMainObj.pDocument.createElement("TABLE");
	tableToolbar.unselectable = "on";

	tableToolbar.pObj = this;
	tableToolbar.ondragstart = function (e){return false;};
	//tableToolbar.onmousedown = function (e){return false;};
	tableToolbar.cellSpacing = 0;
	tableToolbar.cellPadding = 0;
	//!!tableToolbar.className = "bxedtoolbar";
	tableToolbar.style.width = (dx != null ? dx : "0%");
	tableToolbar.style.height = (dy != null ? dy : "20px");

	var rowTitle = tableToolbar.insertRow(0);
	var cellTitle = rowTitle.insertCell(0);
	cellTitle.noWrap = "nowrap";
	cellTitle.className = "bxedtoolbartitle";
	cellTitle.unselectable = "on";
	cellTitle.style.cursor = "move";
	cellTitle.innerHTML = '<table cellpadding=0 cellspacing=0 border=0 width="100%" class="bxedtoolbartitletext"><tr><td width="99%" nowrap>'+title+'</td><td width="0%">&nbsp;</td><td width="1%">x</td></table>';
	cellTitle.onmousedown = function(e){obj.MouseDown(e); return false;};
	this.pTitleRow = rowTitle;

	var row2 = tableToolbar.insertRow(1);
	var cellrow2 = row2.insertCell(0);
	cellrow2.className = "bxedtoolbar";
	cellrow2.unselectable = "on";

	var tableIcons = pMainObj.CreateElement("TABLE");
	tableIcons.pObj = this;
	tableIcons.cellSpacing = 0;
	tableIcons.cellPadding = 0;
	tableIcons.className = "bxedtoolbaricons";
	tableIcons.style.width = "100%";
	tableIcons.style.height = (dy != null ? dy : "22px");
	tableIcons.unselectable = "on";
	var rowIcons = tableIcons.insertRow(0);
	//rowIcons.style.backgroundImage = "url(/icons/toolbarbg.gif)";

	var cellIcons = rowIcons.insertCell(0);
	cellIcons.style.width = "0%";
	cellIcons.style.cursor = "move";
	cellIcons.appendChild(pMainObj.CreateElement("IMG", {"src": "/bitrix/images/fileman/htmledit2/toolbarleft.gif", "title": title, "alt": title}));
	cellIcons.unselectable = "on";
	cellIcons.onmousedown = function(e){obj.MouseDown(e);  return false;};

	cellIcons = rowIcons.insertCell(-1);
	cellIcons.unselectable = "on";
	cellIcons.style.width = "100%";
	//cellIcons.style.backgroundImage = "url(/icons/toolbarbg.gif)";
	cellIcons.innerHTML = ' ';
	cellIcons = rowIcons.insertCell(-1);
	cellIcons.unselectable = "on";
	cellIcons.style.width = "0%";
	cellIcons.appendChild(pMainObj.CreateElement("IMG", {"src": "/bitrix/images/fileman/htmledit2/toolbarright.gif", "title": title, "alt": title}));
	cellIcons.onmousedown = function(e){obj.MouseDown(e); return false;};
	//this.SetDirection(this.bVertical);

	this.pIconsTable = cellrow2.appendChild(tableIcons);
	this.pWnd = this.pMainObj.pWnd.appendChild(tableToolbar);

	//pBXEventDispatcher.AddHandler('mousedown', function(e){obj.MouseDown(e);});
	pBXEventDispatcher.AddHandler('mouseup', function(e){obj.MouseUp(e);});
	pBXEventDispatcher.AddHandler('mousemove', function(e){obj.MouseMove(e);});

	/*
	f = function (e)
		{
			if(window.event)
				e = window.event;
			if(this.style.cursor == 'n-resize')
				this.drag = true;
		}
	this.pMainObj.AddEventHandler("mousedown", f);

	f = function (e)
		{
			if(window.event)
				e = window.event;
			if(this.style.cursor == 'n-resize')
				this.drag = false;
		}
	this.pMainObj.AddEventHandler("mouseup", f);
	*/

	//this.pMainObj.TranslateEvent(this.pTable);

	///////////////////////////////////////////////////
	// методы
	///////////////////////////////////////////////////

	BXToolbar.prototype.SetDirection = function(bVertical)
	{
		if(this.bVertical == bVertical)
			return;

	/*
	BXTButton.prototype.onToolbarChangeDirection = function (bVertical)
	{
		if(this.id=='separator')
		{
			if(bVertical)
			{
				this.pWnd.style.width = '22px';
				this.pWnd.style.height = '2px';
			}
			else
			{
				this.pWnd.style.width = '2px';
				this.pWnd.style.height = '22px';
			}
		}
	}
	*/

		var obj = this;
		this.bVertical = bVertical;
		var newr, i, buttons, ar = Array();
		if(bVertical)
		{
			buttons = this.pIconsTable.rows[0].cells;
			i=0;
			while(buttons.length>3)
				ar[i++] = this.pIconsTable.rows[0].removeChild(buttons[1]);

			this.pIconsTable.deleteRow(0);
			var ct = this.pIconsTable.insertRow(0).insertCell(0);
			ct.appendChild(pMainObj.CreateElement("IMG", {"src": "/bitrix/images/fileman/htmledit2/toolbartop.gif", "title": title, "alt": title}));
			ct.style.width = "0%";
			ct.onmousedown = function(e){obj.MouseDown(e);  return false;};
			ct.style.height = "0%";
			ct.style.cursor = "move";
			for(i=0; i<ar.length; i++)
			{
				var ra = this.pIconsTable.insertRow(i+1);
				ct = ra.appendChild(ar[i]);
				ct.style.backgroundImage = "url(/bitrix/images/fileman/htmledit2/toolbarbg_vert.gif)";
				if(ct.pObj.onToolbarChangeDirection)
					ct.pObj.onToolbarChangeDirection(bVertical);
			}
			ct = this.pIconsTable.insertRow(-1).insertCell(0).appendChild(pMainObj.CreateElement("IMG", {"src": "/bitrix/images/fileman/htmledit2/toolbarbottom.gif", "title": title, "alt": title}));
		}
		else
		{
			buttons = this.pIconsTable.rows;
			for(i=1; i<buttons.length-1; i++)
				ar[i-1] = buttons[i].removeChild(buttons[i].cells[0]);

			while(this.pIconsTable.rows.length>0)
				this.pIconsTable.deleteRow(0);

			var r = this.pIconsTable.insertRow(0)
			var ct2 = r.insertCell(0);
			ct2.appendChild(pMainObj.CreateElement("IMG", {"src": "/bitrix/images/fileman/htmledit2/toolbarleft.gif", "title": title, "alt": title}));
			ct2.style.width = "0%";
			ct2.style.height = "0%";
			ct2.style.cursor = "move";
			ct2.onmousedown = function(e){obj.MouseDown(e);  return false;};
			for(i=0; i<ar.length; i++)
			{
				ct2 = r.appendChild(ar[i]);
				ct2.style.width = "0%";
				ct2.style.backgroundImage = "url(/bitrix/images/fileman/htmledit2/toolbarbg.gif)";
				if(ct2.pObj.onToolbarChangeDirection)
					ct2.pObj.onToolbarChangeDirection(bVertical);
			}
			var ln = r.cells.length;
			ct2 = r.insertCell(ln)
			ct2.innerHTML = ' ';
			ct2.style.width = "100%";
			r.insertCell(-1).appendChild(pMainObj.CreateElement("IMG", {"src": "/bitrix/images/fileman/htmledit2/toolbarright.gif", "title": title, "alt": title}));
		}
	}

	/*
	добавляет кнопку в тулбар
	*/
	BXToolbar.prototype.AddButton = function (pButton, num)
	{
		this.arButtons[this.arButtons.length] = pButton;
		var rowIcons = this.pIconsTable.rows[0];
		var but_count = rowIcons.cells.length - 3;
		if(!num || num>but_count)
			num = but_count;

		var cellIcon = rowIcons.insertCell(num + 1);
		cellIcon.unselectable = "on";
		//cellIcon.style.backgroundImage = "url(/icons/toolbarbg.gif)";
		cellIcon.style.width = "0%";
		//pButton.Show(cellIcon);
		cellIcon.appendChild(pButton.pWnd);
		cellIcon.pObj = pButton; // opera bug!
	}

	BXToolbar.prototype.OnClick = function (px, py)
	{

	}
}

/*
при нажатии на левую кнопку мыши:
	проверяем - находимся ли мы над областью этого тулбара,
	если да, то начинаем drag
*/
BXToolbar.prototype.MouseDown = function (e)
{
	if(window.event)
		e = window.event;

	if(e.pageX || e.pageY)
	{
		e.realX = e.pageX;
		e.realY = e.pageY;
	}
	else if(e.clientX || e.clientY)
	{
		e.realX = e.clientX + document.body.scrollLeft;
		e.realY = e.clientY + document.body.scrollTop;
	}

	var position = GetRealPos(this.pWnd);
	this.pMainObj.bDragging = true;
	this.bDragging = true;

	this.pMainObj.iLeftDragOffset = e.realX - position["left"];
	this.pMainObj.iTopDragOffset = e.realY - position["top"];

	pBXEventDispatcher.SetCursor("move");
	this.pWnd.oldBorder = this.pWnd.style.border;
	this.pWnd.style.zIndex = "1000";
}

/*
при отпускании кнопки мыши:
	если было перетягивание этого тулбара, то прекращаем это
*/
BXToolbar.prototype.MouseUp = function (e)
{
	if(this.pMainObj.bDragging && this.bDragging)
	{
		this.pMainObj.bDragging = false;
		this.bDragging = false;
		this.pWnd.style.zIndex = "1";
		this.pWnd.style.border = this.pWnd.oldBorder;
		pBXEventDispatcher.SetCursor("auto");
	}
}

/*
Отклеивает тулбар от тулбарсета
	удаляет его из тулбарсета при помощи BXToolbarSet.DelToolbar
	делает ему position = "absolute";
	прячет рамки и показывает заголовок тулбара
*/
BXToolbar.prototype.UnDock = function ()
{
	if(this.pToolbarSet)
		this.pToolbarSet.DelToolbar(this);
	this.pWnd.style.zIndex = "1000";
	this.pWnd.style.position = "absolute";
	this.pMainObj.pWnd.appendChild(this.pWnd);
	var rowIcons = this.pIconsTable.rows[0];
	rowIcons.cells[0].style.display = "none";
	//rowIcons.cells[rowIcons.cells.length-2].style.display = "none";
	rowIcons.cells[rowIcons.cells.length-1].style.display = "none";
	this.pTitleRow.style.display = GetDisplStr(1);
	this.SetDirection(false);
}

/*
При движении мыши
	если состояние перетягивания, то проверяем при помощи BXToolToolbarSet.HitTest в
	какой тулбарсет попадаем и в зависимости от этого либо прикрепляем к тулбарсету,
	либо отлепляем, либо перемещаем внутри его и либо перемещаем "отклееным"
*/
BXToolbar.prototype.MouseMove = function (e)
{
//	window.status = e.realY;

	if(this.pMainObj.bDragging && this.bDragging)
	{
		//var position = GetRealPos(this.pWnd);
		// проверяем: попадаем ли в тулбарсет
		var bDocked = false;
		var actToolbarSet = false;
		var arToolbarSet = this.pMainObj.GetToolbarSet();
		for(var i=0; i<arToolbarSet.length; i++)
		{
			var arPos = arToolbarSet[i].HitTest(e.realX, e.realY)
			if(arPos)
			{
				bDocked = true;
				actToolbarSet = arToolbarSet[i];
				break;
			}
		}

		if(this.bDocked && !bDocked) // тулбар вышел из тулбарсета
		{
			this.UnDock();
			this.pWnd.style.left = e.realX - this.pMainObj.iLeftDragOffset;
			this.pWnd.style.top = e.realY - this.pMainObj.iTopDragOffset;
		}
		else if(!this.bDocked && bDocked && actToolbarSet) // тулбар попадает в тулбарсет
		{
			if(this.pToolbarSet)
				this.pToolbarSet.DelToolbar(this);
			actToolbarSet.AddToolbar(this, arPos['row'], arPos['col'], arPos['addrow']);
		}
		else if(!this.bDocked && !bDocked)
		{
			this.pWnd.style.left = e.realX - this.pMainObj.iLeftDragOffset;
			this.pWnd.style.top = e.realY - this.pMainObj.iTopDragOffset;
		}
		else if((arPos["addrow"] /*&& this.row!=0 && arPos['row']!=0*/) || this.row!=arPos['row'] || this.col!=arPos['col'])
		{
			//window.status = this.row + '!=' + arPos['row'] + '||' + this.col + '!=' + arPos['col'];
			if(this.pToolbarSet)
				this.pToolbarSet.DelToolbar(this);
			actToolbarSet.AddToolbar(this, arPos['row'], arPos['col'], arPos['addrow']);
			//window.status = Math.random() + '; ' + this.row + '!=' + arPos['row'] + ' x ' + this.col + '!=' + arPos['col'];
		}
		/*
		else
			window.status = Math.random();
			*/
		this.bDocked = bDocked;

 //		window.status = 'mousemove: ' + e.realX + 'x' + e.realY;
	}
	//alert(this.className);
}



function BXTaskbarSet(pColumn, pMainObj, iNum)
{
	var obj = this;
	var bVertical = (iNum == 1 || iNum==2);
	this.__Size = ["100%", "100%"];

	this.className = 'BXTaskbarSet';
	pColumn.unselectable = "on";
	this.pParentWnd = pColumn;
	this.pMainObj = pMainObj;
	this.bVertical = bVertical;
	this.pParentWnd.className = 'bxedtaskbarset';
	this.iNum = iNum;
	if(bVertical)
		pColumn.style.verticalAlign = "top";

	pColumn.innerHTML = '<img src="/bitrix/images/1.gif" width="1" height="1" border="0">';

	pColumn.style.paddingBottom = "1px";
	this.pWnd = pColumn.appendChild(this.pMainObj.CreateElement("TABLE"));
	this.pWnd.unselectable = "on";
	this.pWnd.cellSpacing = 0;
	this.pWnd.cellPadding = 0;
	this.pWnd.border = 0;
	//this.pWnd.style.border = "1px #DF0000 solid";
	this.pWnd.style.width = this.__Size[0];
	this.pWnd.style.height = this.__Size[1];

	var r = this.pWnd.insertRow(-1);
	switch(this.iNum)
	{
		case 0: //верхний
			this.pMainCell = r.insertCell(-1);
			this.pMoveColumn = this.pWnd.insertRow(-1).insertCell(-1);
			break;
		case 1: //левый
			this.pMainCell = r.insertCell(-1);
			this.pMoveColumn = r.insertCell(-1);
			break;
		case 2: //правый
			this.pMoveColumn = r.insertCell(-1);
			this.pMainCell = r.insertCell(-1);
			break;
		case 3: //нижний
			this.pMoveColumn = r.insertCell(-1);
			this.pMainCell = this.pWnd.insertRow(-1).insertCell(-1);
			break;
	}

	this.pTaskbarsTable = this.pMainCell.appendChild(this.pMainObj.CreateElement("TABLE", {"unselectable": "on", "cellPadding": "0", "cellSpacing": "0", "border": "0"}, {"height": "100%", "width": "100%"}));
	this.pDataColumn = this.pTaskbarsTable.insertRow(-1).insertCell(-1);
	this.pBottomColumn = this.pTaskbarsTable.insertRow(-1).insertCell(-1);

	this.arTaskbars = Array();

	this.pMainCell.style.height = "100%";
	this.pMainCell.style.width = "100%";
	this.pDataColumn.style.height = "100%";
	this.pDataColumn.style.width = "100%";

	switch(this.iNum)
	{
		case 0: //верхний
		case 3: //нижний
			this.pMoveColumn.style.cursor = "N-resize";
			this.pMoveColumn.style.height = "6px";
			this.pMoveImg = this.pMoveColumn.appendChild(this.pMainObj.CreateElement("IMG", {"border": "0", "width": "48", "height": "6"}));
			break;
		case 1: //левый
		case 2: //правый
			this.pMoveColumn.style.cursor = "W-resize";
			this.pMoveColumn.style.width = "6px";
			this.pMoveImg = this.pMoveColumn.appendChild(this.pMainObj.CreateElement("IMG", {"border": "0", "width": "6", "height": "48"}));
			break;
	}

	this.pMoveImg.style.cursor = "default";
	this.pMoveColumn.align = 'center';
	this.pMoveColumn.vAlign = 'middle';

	//this.pBottomColumn.style.display = 'none';
	//this.pBottomColumn.style.paddingBottom = "1px";
	this.pMoveColumn.style.display = "none";
	this.pMoveColumn.style.width = "0%";
	this.pMoveColumn.style.height = "0%";
	//this.pMoveColumn.innerHTML='<img src="/bitrix/images/1.gif" alt="" border=0 width="6" height="4">';
	//this.pMoveColumn.style.border = "1px solid #333333";

	addCustomElementEvent(this.pMoveImg, "mousedown", function (){this.ShowToggle();}, this);

	this.pMoveColumn.ondragstart = function (e){return false;};
	this.pMoveColumn.onmousedown = function(e){obj.MouseDown(e); return false;};
	pBXEventDispatcher.AddHandler('mouseup', function(e){obj.MouseUp(e);});
	pBXEventDispatcher.AddHandler('mousemove', function(e){obj.MouseMove(e);});

	this.Show();
}

BXTaskbarSet.prototype.MouseDown = function (e)
{
	//this.pMoveColumn.style.border="1px dashed #000000";
	if(!this.bShowing)
		return;

	if(window.event)
		e = window.event;

	if(e.pageX || e.pageY)
	{
		e.realX = e.pageX;
		e.realY = e.pageY;
	}
	else if(e.clientX || e.clientY)
	{
		e.realX = e.clientX + document.body.scrollLeft;
		e.realY = e.clientY + document.body.scrollTop;
	}

	var position = GetRealPos(this.pWnd);
	this.pMainObj.bDragging = true;
	this.bDragging = true;

	switch (this.iNum)
	{
		case 0:
			this.pMainObj.iDragOffset = position["bottom"] - e.realY - position["top"];
			pBXEventDispatcher.SetCursor("N-resize");
			break;
		case 1:
			this.pMainObj.iDragOffset = position["right"] - e.realX - position["left"] ;
			pBXEventDispatcher.SetCursor("W-resize");
			break;
		case 2:
			this.pMainObj.iDragOffset = e.realX - position["left"] + position["right"];
			pBXEventDispatcher.SetCursor("W-resize");
			break;
		case 3:
			this.pMainObj.iDragOffset = e.realY - position["top"] + position["bottom"];
			pBXEventDispatcher.SetCursor("N-resize");
			break;
	}
}

BXTaskbarSet.prototype.MouseMove = function (e)
{
	if(this.pMainObj.bDragging && this.bDragging)
	{
		var v;
		switch (this.iNum)
		{
			case 0:
				v = this.pMainObj.iDragOffset + e.realY;
				this.pWnd.style.height = (v<=0? 0 : v);
				break;
			case 1:
				v = this.pMainObj.iDragOffset + e.realX;
				this.pWnd.style.width = (v<=0? 0 : v);
				//this.pMoveColumn.style.width = "1000px";
				break;
			case 2:
				v = this.pMainObj.iDragOffset - e.realX;
				this.pWnd.style.width = (v<=0? 0 : v);
				break;
			case 3:
				v = this.pMainObj.iDragOffset - e.realY;
				this.pWnd.style.height = (v<=0? 0 : v);
				break;
		}
	}
}

BXTaskbarSet.prototype.MouseUp = function (e)
{
	if(this.pMainObj.bDragging && this.bDragging)
	{
		//this.pMoveColumn.style.border="0px solid #000000"
		this.pMainObj.bDragging = false;
		this.bDragging = false;
		pBXEventDispatcher.SetCursor("auto");
	}
}

BXTaskbarSet.prototype.Show = function ()
{
	this.pDataColumn.style.display = GetDisplStr(1);
	this.pBottomColumn.style.display = GetDisplStr(1);
	this.pMoveImg.alt = this.pMoveImg.title = BX_MESS.Hide;
	switch(this.iNum)
	{
	case 0:
		this.pMoveImg.src="/bitrix/images/fileman/htmledit2/splitterh-r.gif";
		this.pMoveColumn.style.backgroundImage = "url(/bitrix/images/fileman/htmledit2/splitterhbg-r.gif)";
		break;
	case 1:
		this.pMoveImg.src="/bitrix/images/fileman/htmledit2/splitterv-r.gif";
		this.pMoveColumn.style.backgroundImage = "url(/bitrix/images/fileman/htmledit2/splittervbg-r.gif)";
		break;
	case 2:
		this.pMoveImg.src="/bitrix/images/fileman/htmledit2/splitterv.gif";
		this.pMoveColumn.style.backgroundImage = "url(/bitrix/images/fileman/htmledit2/splittervbg.gif)";
		break;
	case 3:
		this.pMoveImg.src="/bitrix/images/fileman/htmledit2/splitterh.gif";
		this.pMoveColumn.style.backgroundImage = "url(/bitrix/images/fileman/htmledit2/splitterhbg.gif)";
		break;
	}

	switch(this.iNum)
	{
	case 0:
	case 3:
		this.pWnd.style.height = this.__Size[1];
		break;
	case 1:
	case 2:
		this.pWnd.style.width = this.__Size[0];
		break;
	}
	this.bShowing = true;
}

BXTaskbarSet.prototype.Hide = function ()
{
	this.pDataColumn.style.display = GetDisplStr(0);
	this.pBottomColumn.style.display = GetDisplStr(0);
	this.pMoveImg.alt = this.pMoveImg.title = BX_MESS.Restore;
	switch(this.iNum)
	{
	case 0:
		this.pMoveImg.src="/bitrix/images/fileman/htmledit2/splitterh.gif";
		this.pMoveColumn.style.backgroundImage = "url(/bitrix/images/fileman/htmledit2/splitterhbg.gif)";
		break;
	case 1:
		this.pMoveImg.src="/bitrix/images/fileman/htmledit2/splitterv.gif";
		this.pMoveColumn.style.backgroundImage = "url(/bitrix/images/fileman/htmledit2/splittervbg.gif)";
		break;
	case 2:
		this.pMoveImg.src="/bitrix/images/fileman/htmledit2/splitterv-r.gif";
		this.pMoveColumn.style.backgroundImage = "url(/bitrix/images/fileman/htmledit2/splittervbg-r.gif)";
		break;
	case 3:
		this.pMoveImg.src="/bitrix/images/fileman/htmledit2/splitterh-r.gif";
		this.pMoveColumn.style.backgroundImage = "url(/bitrix/images/fileman/htmledit2/splitterhbg-r.gif)";
		break;
	}

	switch(this.iNum)
	{
	case 0:
	case 3:
		this.__Size[1] = this.pWnd.offsetHeight;
		this.pWnd.style.height = "0%";
		break;
	case 1:
	case 2:
		this.__Size[0] = this.pWnd.offsetWidth;
		this.pWnd.style.width = "0%";
		break;
	}
	this.bShowing = false;
}

BXTaskbarSet.prototype.ShowToggle = function()
{
	if(this.bShowing)
		this.Hide();
	else
		this.Show();
}

BXTaskbarSet.prototype.OnResize = function (e)
{

}

BXTaskbarSet.prototype.AddTaskbar = function (pTaskbar)
{
	pTaskbar.bDocked = true;
	pTaskbar.pWnd.style.position = "relative";
	pTaskbar.pWnd.style.zIndex = "0";
	pTaskbar.pWnd.style.left = null;
	pTaskbar.pWnd.style.top = null;
	//pTaskbar.pWnd.style.height = null;
	//pTaskbar.pWnd.style.width = null;
	pTaskbar.oldWidth = pTaskbar.pWnd.style.width;
	pTaskbar.oldHeight = pTaskbar.pWnd.style.height;
	pTaskbar.pTaskbarSet = this;
	pTaskbar.parentCell = this.pWnd;
	this.arTaskbars[this.arTaskbars.length] = pTaskbar;
	this.DrawTabs();
	pTaskbar.SetActive();

	if(this.bVertical)
	{
		//pTaskbar.pWnd.style.width = "100%";
		pTaskbar.pWnd.style.height = "100%";
	}
	else
	{
		if(this.arTaskbars.length==1)
			this.pWnd.style.height = "150px";
		//else
		pTaskbar.pWnd.style.height = "100%";
		pTaskbar.pWnd.style.width = "100%";
	}

	this.pDataColumn.appendChild(pTaskbar.pWnd);
}

BXTaskbarSet.prototype.DelTaskbar = function (pTaskbar)
{
	pTaskbar.pWnd.parentNode.removeChild(pTaskbar.pWnd);
	pTaskbar.pTaskbarSet = null;
	for(var i=0; i<this.arTaskbars.length; i++)
	{
		if(pTaskbar.id==this.arTaskbars[i].id)
		{
			var arNewTemp = Array();
			for(var j=0; j<this.arTaskbars.length; j++)
				if(i!=j)
					arNewTemp[arNewTemp.length] = this.arTaskbars[j];
			this.arTaskbars = arNewTemp;
			this.DrawTabs();
			if(arNewTemp.length>0)
				this.ActivateTaskbar(arNewTemp[0].id);
			break;
		}
	}
}


BXTaskbarSet.prototype.HitTest = function (px, py)
{
	var delta = 5;

	var position = GetRealPos((/*this.bVertical ? this.pParent : */this.pWnd));
	if(
		position["left"] - delta < px && px < position["right"] + delta
		&& position["top"] - delta < py && py < position ["bottom"] + delta
	)
	{
		return true;
	}

	if(this.iNum==0)
	{
		//window.status = position["left"]+' - '+delta+' < '+px+' && '+px+' < '+position["right"]+' + '+delta+' && '+position["top"]+' - '+delta+' < '+py+' && '+py+' < '+position["bottom"]+' + '+delta+';';
	}

	return false;
}

BXTaskbarSet.prototype.DrawTabs = function ()
{
	if(this.arTaskbars.length<=0)
	{
		this.pMoveColumn.style.display = "none";
		this.pWnd.style.width = "0%";
		this.pWnd.style.height = "0%";
	}
	else
		this.pMoveColumn.style.display = (BXIsIE()?"block":"table-cell");

	if(this.arTaskbars.length<=1)
	{
		this.pBottomColumn.style.display = 'none';
	}
	else
	{
		this.pBottomColumn.style.display = (BXIsIE()?"block":"table-cell");
		//var w = this.pWnd.clientWidth/this.arTaskbars.length;

		while(this.pBottomColumn.childNodes.length>0)
			this.pBottomColumn.removeChild(this.pBottomColumn.childNodes[0]);

		var pIconTable = this.pMainObj.pDocument.createElement("TABLE");
		pIconTable.unselectable = "on";
		pIconTable.cellSpacing = 0;
		pIconTable.cellPadding = 0;
		pIconTable.width = "100%";
		pIconTable.border = 0;
		var r = pIconTable.insertRow(0), c;
		//var obj;
		for(var k=0; k<this.arTaskbars.length; k++)
		{
			c = r.insertCell(-1);
			//c.className = 'bxedtaskbaricontable';
			c.style.width = "0%";
			c.tid = this.arTaskbars[k].id;
			c.innerHTML = '<span unselectable="on" style="overflow:hidden; cursor:default; ">'+this.arTaskbars[k].title+'</span>';
			c.pObj = this.arTaskbars[k];
			c.onclick = function (e){this.pObj.SetActive();};
		}
		c = r.insertCell(-1);
		c.width = "100%";
		c.unselectable = "on";
		c.className = 'bxedtaskbaricontable';
		c.innerHTML = '&nbsp;';
		this.pBottomColumn.appendChild(pIconTable);
	}
}

BXTaskbarSet.prototype.ActivateTaskbar = function (id)
{
	while(this.pDataColumn.childNodes.length>0)
		this.pDataColumn.removeChild(this.pDataColumn.childNodes[0]);

	for(var i=0; i<this.arTaskbars.length; i++)
	{
		if(this.arTaskbars[i].id==id)
		{
			this.pDataColumn.appendChild(this.arTaskbars[i].pWnd);
			break;
		}
	}
	if(this.pBottomColumn.childNodes[0])
	{
		var tsb_cells = this.pBottomColumn.childNodes[0].rows[0].cells;
		for(i=0; i<tsb_cells.length-1; i++)
		{
			if(tsb_cells[i].tid!=id && tsb_cells[i].className!='bxedtaskbaricontable')
				tsb_cells[i].className = 'bxedtaskbaricontable';
			else if(tsb_cells[i].tid==id && tsb_cells[i].className!='bxedtaskbaricontableact')
				tsb_cells[i].className = 'bxedtaskbaricontableact';
		}
	}
}


/////////////////////////////////////////////////////////////
function BXTaskbar()
{

}

BXTaskbar.prototype.Create = function(pMainObj, title, dx, dy)
{
	this.pMainObj = pMainObj;
	this.className = 'BXTaskbar';
	this.id = Math.random();
	this.bVertical = false;
	this.title = title;

	var obj = this;
	var tableTaskbar = pMainObj.pDocument.createElement("TABLE");
	tableTaskbar.unselectable = "on";
	tableTaskbar.pObj = this;
	tableTaskbar.ondragstart = function (e){return false;};
	tableTaskbar.cellSpacing = 0;
	tableTaskbar.cellPadding = 0;
	//!!tableToolbar.className = "bxedtoolbar";
	tableTaskbar.style.width = (dx != null ? dx : "100%");
	tableTaskbar.style.height = (dy != null ? dy : "200px");
	tableTaskbar.style.position = "absolute";
	tableTaskbar.style.zIndex = "1000";

	var rowTitle = tableTaskbar.insertRow(0);
	var cellTitle = rowTitle.insertCell(0);
	cellTitle.noWrap = "nowrap";
	cellTitle.className = "bxedtaskbartitle";
	cellTitle.unselectable = "on";
	cellTitle.style.cursor = "move";
	cellTitle.style.height = "0%";
	//cellTitle.style.width = "200px";
	//cellTitle.innerHTML = '<table cellpadding=0 cellspacing=0 border=0 width="100%" class="bxedtaskbartitletext"><tr><td width="99%" id = "text" nowrap unselectable="on">'+title+'</td><td width="0%" id="sep">&nbsp;</td><td width="1%" id="button"><img src="/bitrix/images/fileman/htmledit2/taskbarx.gif" width="17" height="17"/></td></table>';
	var hdrow = cellTitle.appendChild(pMainObj.CreateElement("TABLE", {"cellPadding":"0", "cellSpacing":"0", "border":"0", "width":"100%", "className":"bxedtaskbartitletext"})).insertRow(-1);

	var c = hdrow.insertCell(-1);
	c.style.width = "180px";
	c.id = "text";
	c.noWrap=true;
	c.unselectable="on";
	c.innerHTML = title;

	c = hdrow.insertCell(-1);
	c.style.width = "0%";
	c.id = "sep";
	c.innerHTML = "&nbsp;";

	c = hdrow.insertCell(-1);
	c.style.width = "1%";
	c.id = "button";
	c.innerHTML = '<img src="/bitrix/images/fileman/htmledit2/taskbarx.gif" width="17" height="17"/>';
	addCustomElementEvent(c, "mousedown", function (){this.Close();}, this);

	cellTitle.onmousedown = function(e){obj.MouseDown(e); return false;};
	this.pTitleRow = rowTitle;

	var row2 = tableTaskbar.insertRow(1);
	var cellrow2 = row2.insertCell(0);
	cellrow2.className = "bxedtaskbar";
	cellrow2.unselectable = "on";
	cellrow2.style.height = "100%";
	this.pDataCell = cellrow2;
	this.pWnd = this.pMainObj.pWnd.appendChild(tableTaskbar);

	pBXEventDispatcher.AddHandler('mouseup', function(e){obj.MouseUp(e);});
	pBXEventDispatcher.AddHandler('mousemove', function(e){obj.MouseMove(e);});

	this.pDataCell.className = "bxedtaskbarinner";
	//this.pDataCell.innerHTML = "XX";

	if(this.OnTaskbarCreate)
		this.OnTaskbarCreate();
}

BXTaskbar.prototype.SetActive = function ()
{
	if(this.pTaskbarSet)
	{
		this.pTaskbarSet.ActivateTaskbar(this.id);
	}
}

BXTaskbar.prototype.Close = function()
{
	if(this.pTaskbarSet)
		this.pTaskbarSet.DelTaskbar(this);
}

BXTaskbar.prototype.MouseDown = function (e)
{
	return false;
	if(window.event)
		e = window.event;

	if(e.pageX || e.pageY)
	{
		e.realX = e.pageX;
		e.realY = e.pageY;
	}
	else if(e.clientX || e.clientY)
	{
		e.realX = e.clientX + document.body.scrollLeft;
		e.realY = e.clientY + document.body.scrollTop;
	}

	var position = GetRealPos(this.pWnd);
	this.pMainObj.bDragging = true;
	this.bDragging = true;

	this.pMainObj.iLeftDragOffset = e.realX - position["left"];
	this.pMainObj.iTopDragOffset = e.realY - position["top"];

	pBXEventDispatcher.SetCursor("move");
	this.pWnd.oldBorder = this.pWnd.style.border;
	this.pWnd.style.border = "1px solid #000000";
	this.pWnd.style.zIndex = 1000;
	//alert(this.pWnd.style.zIndex);

	if(!this.bDocked)
	{
		this.pWnd.parentNode.removeChild(this.pWnd);
		this.pMainObj.pWnd.appendChild(this.pWnd);
	}
}

BXTaskbar.prototype.MouseMove = function (e)
{
	if(this.pMainObj.bDragging && this.bDragging)
	{
		var bDocked = false;
		var actTaskbarSet = false;
		var arTaskbarSet = this.pMainObj.GetTaskbarSet();
		for(var i=0; i<arTaskbarSet.length; i++)
		{
			if(arTaskbarSet[i].HitTest(e.realX, e.realY))
			{
				bDocked = true;
				actTaskbarSet = arTaskbarSet[i];
				break;
			}
		}

		if(this.bDocked && !bDocked) // тулбар вышел из тулбарсета
		{
			this.UnDock();
			this.pWnd.style.left = e.realX - this.pMainObj.iLeftDragOffset;
			this.pWnd.style.top = e.realY - this.pMainObj.iTopDragOffset;
		}
		else if(!this.bDocked && bDocked && actTaskbarSet) // тулбар попадает в тулбарсет
		{
			if(this.pTaskbarSet)
				this.pTaskbarSet.DelTaskbar(this);

			actTaskbarSet.AddTaskbar(this);
		}
		else if(!this.bDocked && !bDocked)
		{
			this.pWnd.style.left = e.realX - this.pMainObj.iLeftDragOffset;
			this.pWnd.style.top = e.realY - this.pMainObj.iTopDragOffset;
		}
		this.bDocked = bDocked;
	}
}

BXTaskbar.prototype.MouseUp = function (e)
{
	if(this.pMainObj.bDragging && this.bDragging)
	{
		this.pMainObj.bDragging = false;
		this.bDragging = false;
		this.pWnd.style.zIndex = "1";
	//alert(this.pWnd.style.zIndex);
		this.pWnd.style.border = this.pWnd.oldBorder;
		pBXEventDispatcher.SetCursor("auto");
	}
}

BXTaskbar.prototype.SetContent = function (sContent)
{
	this.pDataCell.innerHTML = sContent;
}


BXTaskbar.prototype.UnDock = function ()
{
	if(this.pTaskbarSet)
		this.pTaskbarSet.DelTaskbar(this);

	this.pWnd.style.zIndex = "1000";
	this.pWnd.style.position = "absolute";
	this.pWnd.style.width = this.oldWidth;
	this.pWnd.style.height = this.oldHeight;

	this.pMainObj.pWnd.appendChild(this.pWnd);
}


BXTaskbar.prototype.CreateScrollableArea = function (pParent)
{
	var res = this.pMainObj.pDocument.createElement("DIV");
	res.style.position = "relative";
	res.style.left = "0px";
	res.style.right = "0px";
	res.style.width = "100%";
	res.style.height = "100%";
	pParent = pParent.appendChild(res);

	res = this.pMainObj.pDocument.createElement("DIV");
	res.style.position = "absolute";
	res.style.left = "0px";
	res.style.right = "0px";
	res.style.width = "100%";
	res.style.height = "100%";

	if(!BXIsIE())
		res.style.overflow = "-moz-scrollbars-vertical";

	res.style.overflowY = "scroll";
	res.style.overflowX = "auto";

	res.style.scrollbar3dLightColor = "#C0C0C0";
	res.style.scrollbarArrowColor = "#252525";
	res.style.scrollbarBaseColor = "#C0C0C0";
	res.style.scrollbarDarkShadowColor = "#252525";
	res.style.scrollbarFaceColor = "#D4D4D4";
	res.style.scrollbarHighlightColor = "#EFEFEF";
	res.style.scrollbarShadowColor = "#EFEFEF";
	res.style.scrollbarTrackColor = "#DFDFDF";


	pParent = pParent.appendChild(res);

	return pParent;
}

////////////////////////////////
// Custom taskbars
///////////////////////////////

/***  Properties taskbar ***/
function BXPropertiesTaskbar()
{
	var obj = this;
	BXPropertiesTaskbar.prototype.OnTaskbarCreate = function ()
	{
		var obj = this;

		var table = this.pMainObj.pDocument.createElement("TABLE");
		table.style.width = "100%";
		table.style.height = "100%";
		table.cellSpacing = 0;
		table.cellPadding = 0;
		this.pCellPath = table.insertRow(-1).insertCell(-1);
		this.pCellPath.style.height = "0%";
		this.pCellPath.className = "bxproptagspath";

		this.pCellProps = table.insertRow(-1).insertCell(-1);
		this.pCellProps.style.height = "100%";
		this.pCellProps.vAlign = "top";
		this.pDataCell.appendChild(table);

		this.pCellProps = this.CreateScrollableArea(this.pCellProps);

		this.pCellProps.className = "bxtaskbarprops";
		//this.pCellProps.innerHTML = 'Property tab';

		this.pMainObj.AddEventHandler("OnSelectionChange", obj.OnSelectionChange);
	}

	BXPropertiesTaskbar.prototype.OnSelectionChange = function (sReloadControl)
	{
		// obj - сам таскбар
		//alert('1');
		//try {swsw();} catch(e){}
		var oSelected = obj.pMainObj.GetSelectionObject();
		var pElement = oSelected;
		var pElementTemp, strPath = '';
		if(sReloadControl == "always" || !obj.oOldSelected || !BXElementEqual(oSelected, obj.oOldSelected))
		{
			obj.oOldSelected = oSelected;

			while(obj.pCellPath.childNodes.length>0)
				obj.pCellPath.removeChild(obj.pCellPath.childNodes[0]);

			var tPath = obj.pMainObj.pDocument.createElement("TABLE");
			tPath.className = "bxproptagspathinl";
			tPath.cellSpacing = 0;
			tPath.cellPadding = 1;
			var rPath = tPath.insertRow(-1);
			var cPath, pBut;

			var oRange;
			var cActiveTag = null;
			var fPropertyPanel = null;
			var fPropertyPanelElement = null;

			if(obj.pMainObj.pEditorDocument.body.createTextRange)
				oRange = obj.pMainObj.pEditorDocument.body.createTextRange();

			while(pElement && (pElementTemp = pElement.parentNode) != null)
			{
				if(pElementTemp.nodeType!=1 || !pElement.tagName)
				{
					pElement = pElementTemp;
					continue;
				}

				strPath = pElement.tagName.toLowerCase();
				if(pElement.getAttribute("__bxtagname"))
					strPath = pElement.getAttribute("__bxtagname").toLowerCase();

				if(strPath == 'tbody')
				{
					pElement = pElementTemp;
					continue;
				}

				cPath = rPath.insertCell(0);

				if(!fPropertyPanel && pPropertybarHandlers[strPath])
				{
					fPropertyPanel = pPropertybarHandlers[strPath];
					fPropertyPanelElement = pElement;
					cActiveTag = cPath;
				}

				cPath.innerHTML = '&lt;'+strPath+'&gt;';
				cPath.style.cursor = "default";
				cPath.pElement = pElement;
				cPath.oRange = oRange;
				cPath.pMainObj = obj.pMainObj;
				cPath.onclick = function (){
					if(this.oRange && this.oRange.moveToElementText)
					{
						this.oRange.moveToElementText(this.pElement);
						this.oRange.select();
					}
					else
					{
						this.pMainObj.pEditorWindow.getSelection().selectAllChildren(this.pElement);
					}

					this.pMainObj.OnEvent("OnSelectionChange");
				};

				cPath = rPath.insertCell(0);
				cPath.innerHTML = ' ';
				pElement = pElementTemp;
			}

			var bDefault = false;
			obj.pCellPath.appendChild(tPath);
			if(!fPropertyPanel)
			{
				fPropertyPanel = pPropertybarHandlers['default'];
				fPropertyPanelElement = oSelected;
				bDefault = true;
			}

			if(cActiveTag)
			{
				cActiveTag.style.backgroundColor = '#E4E4E4';
				cActiveTag.style.fontWeight = 'bold';
			}

			if(fPropertyPanelElement && fPropertyPanelElement.tagName && (!(obj.oOldPropertyPanelElement && BXElementEqual(fPropertyPanelElement, obj.oOldPropertyPanelElement)) || sReloadControl == "always"))
			{
				var sRealTag = fPropertyPanelElement.tagName.toLowerCase();
				if(fPropertyPanelElement.getAttribute("__bxtagname"))
					sRealTag = fPropertyPanelElement.getAttribute("__bxtagname").toLowerCase();

				obj.oOldPropertyPanelElement = fPropertyPanelElement;

				var bNew = false;
				if((sReloadControl == "always") || (bDefault && obj.bDefault != bDefault) || (!bDefault && (!obj.sOldTag || obj.sOldTag != sRealTag)))
				{
					bNew = true;
					while(obj.pCellProps.childNodes.length>0)
						obj.pCellProps.removeChild(obj.pCellProps.childNodes[0]);
				}

				obj.sOldTag = sRealTag;

				if(fPropertyPanel)
				{
					//alert(bNew);
					fPropertyPanel(bNew, obj, fPropertyPanelElement);
				}
				obj.bDefault = bDefault;
			}
		}

		return true;
	}
}

BXPropertiesTaskbar.prototype = new BXTaskbar;

/***  Components taskbar ***/
function BXComponentsTaskbar()
{
	var obj = this;
	BXComponentsTaskbar.prototype.OnTaskbarCreate = function ()
	{
		var obj = this;

		this.pDataCell = this.CreateScrollableArea(this.pDataCell);

		var table = this.pMainObj.CreateElement("TABLE", {'cellSpacing': '0', 'cellPadding': '8', 'className': "bxtaskbarcomp"});

		table.style.height = "100%";
		this.pDataCell.appendChild(table);
		this.pWnd.style.width = "200px"; //////init
		this.pWnd.style.width = "100%"; //////init
		//this.pWnd.id = 'xxxx';
		this.pCellList = table.insertRow(-1).insertCell(-1);
		this.pCellList.style.height = "0%";
		this.pModulesList = this.pMainObj.CreateCustomElement('BXList',
				{
					'width': '150',
					'height': '150',
					'field_size': '150',
					'bSetGlobalStyles': true,
					'values': [],
					'onChange': function (selected)
					{
						obj.ShowComponentList(selected["value"]);
					}
				}
			);

		this.pCellList.appendChild(this.pModulesList.pWnd);

		var emptyRow = table.insertRow(-1).insertCell(-1);
		emptyRow.style.height = "0%";
		emptyRow.innerHTML = "&nbsp;";

		this.pCellComp = table.insertRow(-1).insertCell(-1);
		this.pCellComp.style.height = "100%";
		this.pCellComp.vAlign = "top";

		//this.pCellComp = this.CreateScrollableArea(this.pCellComp);

		this.pMainObj.pComponentTaskbar = this;
		this.BuildList();
		//table.style.width = "100%";
	}

	BXComponentsTaskbar.prototype.BuildList = function ()
	{
		var arList = [];
		var arFolders = this.pMainObj.arTemplateParams["FOLDERS"];
		for(var i=0; i<arFolders.length; i++)
			arList.push({'value': arFolders[i]["ID"], 'name': arFolders[i]["NAME"]});

		this.pModulesList.SetValues(arList);
		this.pModulesList.Select(0);
		this.pModulesList.FireChangeEvent();
	}

	BXComponentsTaskbar.prototype.ShowComponentList = function (sFolder)
	{
		while(this.pCellComp.childNodes.length>0)
			this.pCellComp.removeChild(this.pCellComp.childNodes[0]);

		var arComponents = [], arAllComponents = this.pMainObj.arTemplateParams["COMPONENTS"];
		for(var i=0; i<arAllComponents.length; i++)
		{
			if(arAllComponents[i]["FOLDER"]==sFolder)
				arComponents.push(arAllComponents[i]);
		}

		var r, c, im, obj = this;
		this.bOpenedBlock = false;
		for(i=0;i<arComponents.length; i++)
		{
			if(arComponents[i]["SEPARATOR"])
			{
				this.__OpenBlock(arComponents[i]["NAME"]);
			}
			else
			{
				if(!this.bOpenedBlock)
					this.__OpenBlock('');
				r = this._tableCompList.insertRow(-1);
				c = r.insertCell(-1);
				//alert(BXSerialize(arComponents[i]));
				im = this.pMainObj.CreateElement('IMG', {'src': arComponents[i]["ICON"]});
				im.ondragstart = function (e){if(window.event)window.event.cancelBubble = true;};
				im.setAttribute("__bxtagname", "component");
				im.setAttribute("__bxcontainer", BXSerialize({"SCRIPT_NAME": arComponents[i]["PATH"], "PARAMS": {}}));

				if(BXIsIE())
					im.ondragend = function (e){
							this.id = Math.random();
							obj.pMainObj.nLastDragNDropComponent = this.id;
							obj.pMainObj.onDragDrop();
						};
				else
					im.onmousedown = function (e){
							this.id = Math.random();
							obj.pMainObj.nLastDragNDropComponent = this.id;
						};

				c.appendChild(im);
				c = r.insertCell(-1);
				c.innerHTML = arComponents[i]["NAME"];
			}
		}
		this.__CloseBlock();
		for(i=0; i<this.pCellComp.childNodes.length; i++)
		{
			if(this.pCellComp.childNodes[i].rows)
				this.pCellComp.childNodes[i].rows[0].Hide(true);
		}
	}

	BXComponentsTaskbar.prototype.__OpenBlock = function (name)
	{
		this.__CloseBlock();

		this._tableBlock = this.pCellComp.appendChild(this.pMainObj.CreateElement("table", {cellPadding: '0', border: 0, cellSpacing: 0, width: '100%', className: 'bxedcompblock'}));
		var rowTitle = this._tableBlock.insertRow(-1);
		var c = rowTitle.insertCell(-1);
		c.style.width = '0%';
		var im_l = c.appendChild(this.pMainObj.CreateElement("IMG", {'src': '/bitrix/images/fileman/htmledit2/tscomp-lt-o.gif', 'width': '20', height: '20'}));

		c = rowTitle.insertCell(-1);
		c.unselectable = "on";
		c.style.width = '100%';
		c.id = "title";
		c.innerHTML = (name && name.length>0 ? name : BX_MESS.CompTBTitle);
		c.style.backgroundImage = 'url(/bitrix/images/fileman/htmledit2/tscomp-ct-bg.gif)';

		c = rowTitle.insertCell(-1);
		c.style.width = '0%';
		var im_r = c.appendChild(this.pMainObj.CreateElement("IMG", {'src': '/bitrix/images/fileman/htmledit2/tscomp-rt-o.gif', 'width': '5', height: '20'}));


		var rowData = this._tableBlock.insertRow(-1);
		c = rowData.insertCell(-1);
		c.colSpan = "3";
		c.style.borderLeft = "solid 1px #7A7A7A";
		c.style.borderRight = "solid 1px #7A7A7A";
		this._tableCompList = c.appendChild(this.pMainObj.CreateElement("TABLE", {cellPadding: '2', border: 0, cellSpacing: 0, width: '100%', 'className': 'bxcomplist'}));

		var rowBottom = this._tableBlock.insertRow(-1);
		c = rowBottom.insertCell(-1);
		c.appendChild(this.pMainObj.CreateElement("IMG", {'src': '/bitrix/images/fileman/htmledit2/tscomp-lb.gif', 'width': '20', height: '4'}));

		c = rowBottom.insertCell(-1);
		c.style.backgroundImage = 'url(/bitrix/images/fileman/htmledit2/tscomp-cb.gif)';
		c.appendChild(this.pMainObj.CreateElement("IMG", {'src': '/bitrix/images/1.gif', 'width': '4', height: '4'}));

		c = rowBottom.insertCell(-1);
		c.appendChild(this.pMainObj.CreateElement("IMG", {'src': '/bitrix/images/fileman/htmledit2/tscomp-rb.gif', 'width': '4', height: '4'}));

		this.pCellComp.appendChild(this.pMainObj.CreateElement("IMG", {'src': '/bitrix/images/1.gif', 'width': '4', height: '4'}));

		rowTitle.rowData = rowData;
		rowTitle.rowBottom = rowBottom;
		rowTitle.im_r = im_r;
		rowTitle.im_l = im_l;
		rowTitle.Hide = this.Hide;
		rowTitle.onclick = function(){this.Hide(!this.hidden)};

		this.bOpenedBlock = true;
	}

	BXComponentsTaskbar.prototype.__CloseBlock = function ()
	{
		if(!this.bOpenedBlock)
			return;
		this.bOpenedBlock = false;
	}

	BXComponentsTaskbar.prototype.Hide = function (bHide)
	{
		if(!bHide)
		{
			this.hidden = false;
			this.rowData.style.display = GetDisplStr(1);
			this.rowBottom.style.display = GetDisplStr(1);
			this.im_r.src = '/bitrix/images/fileman/htmledit2/tscomp-rt-o.gif';
			this.im_l.src = '/bitrix/images/fileman/htmledit2/tscomp-lt-o.gif';
		}
		else
		{
			this.hidden = true;
			this.rowData.style.display = GetDisplStr(0);
			this.rowBottom.style.display = GetDisplStr(0);
			this.im_r.src = '/bitrix/images/fileman/htmledit2/tscomp-rt-c.gif';
			this.im_l.src = '/bitrix/images/fileman/htmledit2/tscomp-lt-c.gif';
		}
	}
}

BXComponentsTaskbar.prototype = new BXTaskbar;

/***  Explorer taskbar ***/
function BXExplorerTaskbar()
{

}

BXExplorerTaskbar.prototype = new BXTaskbar;

/***  Clipboard taskbar ***/



/***  Context help taskbar ***/
function BXHelpTaskbar()
{

}

BXHelpTaskbar.prototype = new BXTaskbar;



function BXCreateTaskbars(pMainObj, arParams)
{
	var tttime, lllog = '';

	tttime = (new Date().getTime());
	var pTaskbar;
	if(!pMainObj.arConfig["bWithoutPHP"])
	{
		pTaskbar = new BXComponentsTaskbar();
		pTaskbar.Create(pMainObj, BX_MESS.CompTBTitle);
		pMainObj.arTaskbarSet[2].AddTaskbar(pTaskbar);
	}
	lllog = lllog + 'Component='+(new Date().getTime() -  tttime)+'\r\n';

	/*
	var pTaskbar = new BXExplorerTaskbar();
	pTaskbar.Create(pMainObj, 'Проводник');
	pMainObj.arTaskbarSet[2].AddTaskbar(pTaskbar);
	pTaskbar1.SetActive();
	*/


	//pTaskbar1 = new BXPropertiesTaskbar();
	//pTaskbar1.Create(pMainObj, 'Свойства');
	//pMainObj.arTaskbarSet[1].AddTaskbar(pTaskbar1);

	pTaskbar = new BXPropertiesTaskbar();
	pTaskbar.Create(pMainObj, BX_MESS.CompTBProp);
	pMainObj.arTaskbarSet[3].AddTaskbar(pTaskbar);
	lllog = lllog + 'Prop='+(new Date().getTime() -  tttime)+'\r\n';

	/*
	pTaskbar = new BXHelpTaskbar();
	pTaskbar.Create(pMainObj, 'Помощь');
	pMainObj.arTaskbarSet[3].AddTaskbar(pTaskbar);

	pTaskbar = new BXHelpTaskbar();
	pTaskbar.Create(pMainObj, 'Помощь');
	pMainObj.arTaskbarSet[2].AddTaskbar(pTaskbar);

	pTaskbar1.SetActive();
	*/

	//alert(lllog);
}

pBXEventDispatcher.AddEditorHandler("OnCreate", BXCreateTaskbars);
