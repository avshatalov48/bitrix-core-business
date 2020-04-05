// Toolbarsets class
function BXToolbarSet(pColumn, pMainObj, bVertical)
{
	//ar_BXToolbarSetS.push(this);
	this.className = 'BXToolbarSet';
	pColumn.unselectable = "on";
	this.pWnd = pColumn;
	this.pMainObj = pMainObj;
	this.bVertical = bVertical;
	this.pWnd.className = 'bxedtoolbarset';
	this.arToolbarPositions = [];
	pColumn.style.display = "";
	pColumn.parentNode.style.display = "";

	if(bVertical)
	{
		pColumn.style.verticalAlign = "top";
		//pColumn.innerHTML = '<img src="' + one_gif_src + '" width="1" height="0">';
		this.pWnd = pColumn.appendChild(BX.create("TABLE", {props: {unselectable: "on",cellSpacing: 0,cellPadding: 0,border: 0}}));
		this.pWnd.insertRow(0);
		this.pParent = pColumn;
	}
}

// Check if coordinate hit in toolbarset area (+/- some inaccuracy)
// Return array:
//		"row" - row in toolbarset;
//		"col" - column in toolbarset;
//		"addrow"  - between two rows
// or false - if it's too far
BXToolbarSet.prototype =
{
	HitTest: function (px, py, ind)
	{
		var delta = 3, result, position, allNodes;

		if (!(position = CACHE_DISPATCHER['BXToolbarSet_pos_'+ind]))
			position = CACHE_DISPATCHER['BXToolbarSet_pos_'+ind] = BX.pos((this.bVertical ? this.pParent : this.pWnd));

		if(position["left"] - delta < px &&
			px < position["right"] + delta &&
			position["top"] - delta < py &&
			py < position ["bottom"] + delta)
		{
			result = {row: 0, col: 0, addrow: false};

			// find all toolbars in toolbarset
			if(this.bVertical)
				allNodes = this.pWnd.rows[0].cells;
			else
				allNodes = this.pWnd.childNodes;

			if(!allNodes || allNodes.length<=0)
			{
				result["addrow"] = true;
				return result;
			}

			var allCells, j, i, l = allNodes.length, toolbar_position;
			for(i = 0; i < l; i++)
			{
				toolbar_position = BX.pos(allNodes[i]);
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
							result["col"] = i + 1;
						}
						else
						{
							result["col"] = i;
							allCells = allNodes[i].childNodes[0].rows;
							for(j = allCells.length-1; j > 0; j--)
							{
								var celltemp = allCells[j].cells[0];
								var celltemp_position = BX.pos(celltemp);
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
								//var cell_position = GetRealPos(allCells[j]);
								var cell_position = BX.pos(allCells[j]);
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
	},

	returnToolbarsPositions: function ()
	{
		return this.arToolbarPositions;
	},

	// Add toolbar to toolbarset
	AddToolbar: function (pToolbar, row, col, bAddRow)
	{
		CACHE_DISPATCHER['pEditorFrame'] = null;

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
				{
					tTable = null;
					return this.AddToolbar(pToolbar, row, col+1, bAddRow);
				}
			}

			if(row>tTable.rows.length)
				row = tTable.rows.length;

			pRow = tTable.insertRow(row);
			pColTable = pRow.insertCell(0);

			tTable = null;
			pRow = null;
			ctmp = null;
			cols = null;
		}
		else
		{
			var allNodes = this.pWnd.childNodes;
			var pRowTable;
			if(row>allNodes.length)
				row = allNodes.length;
			if(row >= allNodes.length || bAddRow)
			{
				var t = BX.create("TABLE", {props: {className: "bxed-toolbar-inner",cellSpacing: 0, cellPadding: 0, unselectable: "on"}});
				t.insertRow(0);
				pRowTable = (row >= allNodes.length) ? (this.pWnd.appendChild(t)) : (this.pWnd.insertBefore(t, allNodes[row]));
			}
			else
			{
				pRowTable = allNodes[row];
				if(pRowTable.clientWidth + pToolbar.pWnd.clientWidth > this.pMainObj.arConfig["width"])
					return this.AddToolbar(pToolbar, row+1, col, bAddRow);
			}

			if(col > pRowTable.rows[0].cells.length)
				col = pRowTable.rows[0].cells.length;

			pColTable = pRowTable.rows[0].insertCell(col);
			rowIcons = pToolbar.pIconsTable.rows[0];
			rowIcons.cells[0].style.display = GetDisplStr(1);
			rowIcons.cells[rowIcons.cells.length-1].style.display = GetDisplStr(1);

			r = null;
			t = null;
			pRowTable = null;
			allNodes = null;
		}

		pToolbar.row = row;
		pToolbar.col = col;

		pToolbar.pWnd.style.position = "relative";
		pToolbar.pWnd.style.zIndex = "200";
		pToolbar.pWnd.style.left = null;
		pToolbar.pWnd.style.top = null;

		pToolbar.pTitleRow.style.display = "none";

		pToolbar.pWnd = pColTable.appendChild(pToolbar.pWnd);
		pToolbar.pWnd.style.position = "";
		pToolbar.pToolbarSet = this;
		pToolbar.parentCell = pColTable;

		pColTable.style.width = '10px'; // Hack

		this.__ReCalc();
		pColTable = null;
	},

	//Dell toolbar from toolbarset
	DelToolbar: function (pToolbar)
	{
		CACHE_DISPATCHER['pEditorFrame'] = null;

		pToolbar.parentCell.removeChild(pToolbar.pWnd);
		pToolbar.pToolbarSet = null;
		this.__ReCalc();
	},

	__ReCalc: function ()
	{
		var allNodes, i, j, pToolbar, cols, pDomToolbar;
		if(this.bVertical)
		{
			cols = this.pWnd.rows[0].cells;
			for(i = cols.length - 1; i >= 0; i--)
			{
				allNodes = cols[i].childNodes[0].rows;
				for(j = allNodes.length - 1; j >= 0; j--)
					if(allNodes[j].cells[0].childNodes.length <= 0)
						cols[i].childNodes[0].deleteRow(j);
				if(cols[i].childNodes[0].rows.length <= 0)
					this.pWnd.rows[0].deleteCell(i);
			}

			for(i = 0; i < cols.length; i++)
			{
				allNodes = cols[i].childNodes[0].rows;
				for(j = 0; j < allNodes.length; j++)
				{
					pToolbar = allNodes[j].cells[0].childNodes[0].pObj;

					pToolbar.row = j;
					pToolbar.col = i;
					this.arToolbarPositions[pToolbar.name] = [pToolbar.row,pToolbar.col];
				}
			}
		}
		else
		{
			allNodes = this.pWnd.childNodes;


			for(i = allNodes.length-1; i>=0; i--) // horizontal rows
			{
				var tbl = allNodes[i];
				for(j = tbl.rows[0].cells.length - 1; j >= 0; j--)
				{
					if(tbl.rows[0].cells[j].childNodes.length <= 0)
						tbl.rows[0].deleteCell(j);
				}
				//dell whole table if there are no rows....
				if(tbl.rows[0].cells.length <= 0)
					this.pWnd.removeChild(tbl);
				else
					tbl.rows[0].insertCell(-1);
			}

			for(i = 0; i < allNodes.length; i++)
			{
				for(j = 0; j < allNodes[i].rows[0].cells.length; j++)
				{
					pDomToolbar = allNodes[i].rows[0].cells[j].childNodes[0];
					if (!pDomToolbar || !pDomToolbar.pObj)
						continue;

					pToolbar = pDomToolbar.pObj;
					pToolbar.row = i;
					pToolbar.col = j;
					this.arToolbarPositions[pToolbar.name] = [pToolbar.row,pToolbar.col];
				}
			}
		}
		pToolbar = null;
		tbl = null;
		allNodes = null;
	}
};


//###################################################
//#   class BXToolbar - toolbar
//#   pWnd - pointer to TABLE of toolbar
//#   bDragging - dragging state
//#   bDocked - docked state
//###################################################
function BXToolbar(pMainObj, title, name, dx, dy)
{
	ar_BXToolbarS.push(this);
	this.pMainObj = pMainObj;
	this.className = 'BXToolbar';
	this.id = Math.random();
	this.name = name;
	this.bVertical = false;
	this.title = title;
	this.actTInd = 0;
	this.buttons = [];

	var obj = this;

	var tableToolbar = BX.create("TABLE", {props: {className: "bx-toolbar-tbl", unselectable: "on"}, style: {width: dx != null ? dx : "0%", height: dy != null ? dy : "20px"}});
	tableToolbar.pObj = this;
	tableToolbar.ondragstart = function (e){return false;};
	this.pTitleRow = tableToolbar.insertRow(0);
	var cellTitle = BX.adjust(this.pTitleRow.insertCell(0), {props: {className: "bxedtoolbartitle", noWrap: "nowrap", unselectable: "on"}});

	cellTitle.innerHTML = '<table class="bxedtoolbartitletext"><tr><td width="99%" nowrap style="padding: 0px 1px 1px 8px;">' + title + '</td><td width="0%">&nbsp;</td><td id="title_x_'+this.id+'" width="1%" style="padding: 0px 3px 0px 3px; cursor: default;"><img src="' + one_gif_src + '" class= "iconkit_c bx-toolbar-x" /></td></table>';
	cellTitle.onmousedown = function(e){obj.MouseDown(e); return false;};

	var cellrow2 = tableToolbar.insertRow(1).insertCell(0);
	cellrow2.className = "bxedtoolbar";
	cellrow2.unselectable = "on";

	var tableIcons = BX.create("TABLE", {props: {className: "bxedtoolbaricons", unselectable: "on"}});
	tableIcons.style.height = (dy != null ? dy : "22px");

	var rowIcons = tableIcons.insertRow(0);
	rowIcons.style.backgroundImage = "url(" + image_path + "/toolbarbg.gif)";
	var cellIcons = rowIcons.insertCell(0);
	cellIcons.style.width = "0%";
	cellIcons.style.cursor = "move";
	cellIcons.appendChild(pMainObj.CreateElement("DIV", {title: title, className: "iconkit_c"}, {backgroundPosition: "-317px -96px", width: "12px", height: "25px"}));

	cellIcons.unselectable = "on";
	cellIcons.onmousedown = function(e){obj.MouseDown(e);  return false;};
	cellIcons = rowIcons.insertCell(-1);
	cellIcons.unselectable = "on";
	cellIcons.style.width = "100%";
	cellIcons.style.backgroundImage = "url(" + image_path + "/toolbarbg.gif)";
	cellIcons.innerHTML = ' ';
	cellIcons = rowIcons.insertCell(-1);
	cellIcons.unselectable = "on";
	cellIcons.style.width = "0%";
	//Right part of toolbar
	cellIcons.appendChild(pMainObj.CreateElement("DIV", {title: title, className: "iconkit_c"}, {backgroundPosition: "-334px -96px", width: "5px", height: "25px"}));
	cellIcons.onmousedown = function(e){obj.MouseDown(e); return false;};

	this.pIconsTable = cellrow2.appendChild(tableIcons);
	this.pWnd = this.pMainObj.pWnd.appendChild(tableToolbar);

	var x_cell = pMainObj.pDocument.getElementById('title_x_'+this.id);
	x_cell.onmousedown = function(e){obj.Close(e)};
	x_cell = null;

	// Add button to toolbar
	BXToolbar.prototype.AddButton = function(pButton, num)
	{
		var rowIcons = this.pIconsTable.rows[0];
		var but_count = rowIcons.cells.length - 3;
		if(!num || num>but_count)
			num = but_count;

		var cellIcon = rowIcons.insertCell(num + 1);
		cellIcon.unselectable = "on";
		cellIcon.style.backgroundImage = "url(" + image_path + "/toolbarbg.gif)";
		cellIcon.style.width = "0%";
		cellIcon.appendChild(pButton.pWnd);
		cellIcon.pObj = pButton;

		cellIcon = null;
		rowIcons = null;
	};

	BXToolbar.prototype.SetDirection = function(bVertical)
	{
		if(this.bVertical == bVertical)
			return;

		var obj = this;
		this.bVertical = bVertical;
		var newr, i, buttons, ar = Array();
		if(bVertical)
		{
			buttons = this.pIconsTable.rows[0].cells;
			i=0;
			while(buttons.length > 3)
				ar[i++] = this.pIconsTable.rows[0].removeChild(buttons[1]);

				this.pIconsTable.deleteRow(0);
			var ct = this.pIconsTable.insertRow(0).insertCell(0);
			ct.appendChild(pMainObj.CreateElement("DIV", {title: title, className: "iconkit_c"}, {backgroundPosition: "-291px -100px", width: "25px", height: "12px"}));
			ct.style.width = "0%";

			ct.onmousedown = function(e){obj.MouseDown(e);  return false;};
			ct.style.height = "0%";
			ct.style.cursor = "move";
			for(i = 0, l = ar.length; i < l; i++)
			{
				var ra = this.pIconsTable.insertRow(i+1);
				ct = ra.appendChild(ar[i]);
				ct.style.backgroundImage = "url(" + image_path + "/toolbarbg_vert.gif)";

				if(ar[i].pObj.OnToolbarChangeDirection)
					ar[i].pObj.OnToolbarChangeDirection(bVertical);
			}
			ct = this.pIconsTable.insertRow(-1).insertCell(0).appendChild(pMainObj.CreateElement("IMG", {src: one_gif_src, title: title, className: "iconkit_c"}, {backgroundPosition: "-291px -113px", width: "25px", height: "5px"}));
			ct = null;
			ra = null;
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
			ct2.appendChild(pMainObj.CreateElement("DIV", {title: title, className: "iconkit_c"}, {backgroundPosition: "-317px -96px", width: "12px", height: "25px"}));
			ct2.style.width = "0%";
			ct2.style.height = "0%";
			ct2.style.cursor = "move";
			ct2.onmousedown = function(e){obj.MouseDown(e);  return false;};

			for(i=0; i<ar.length; i++)
			{
				ct2 = r.appendChild(ar[i]);
				ct2.style.width = "0%";
				ct2.style.backgroundImage = "url(" + image_path + "/toolbarbg.gif)";

				if(ct2.pObj.OnToolbarChangeDirection)
					ct2.pObj.OnToolbarChangeDirection(bVertical);
			}
			var ln = r.cells.length;
			ct2 = r.insertCell(ln)
			ct2.innerHTML = ' ';
			ct2.style.width = "100%";
			r.insertCell(-1).appendChild(pMainObj.CreateElement("DIV", {title: title, className: "iconkit_c"}, {backgroundPosition: "-334px -96px", width: "5px", height: "25px"}));

			buttons = null; r = null; ct2 = null;
		}
	};
}

BXToolbar.prototype = {
MouseDown: function (e)
{
	e = getRealMousePos(e, this.pMainObj);
	var position = BX.pos(this.pWnd);

	this.pMainObj.bDragging = true;
	this.bDragging = true;

	this.pMainObj.iLeftDragOffset = e.realX - position["left"];
	this.pMainObj.iTopDragOffset = e.realY - position["top"];

	pBXEventDispatcher.SetCursor("move");
	this.pWnd.oldBorder = this.pWnd.style.border;
	this.pWnd.style.zIndex = "1000";
	var _this = this;


	var __BXToolbarMouseMove = function(e){_this.MouseMove(getRealMousePos(e, _this.pMainObj));};
	var __BXToolbarMouseMoveF = function(e){_this.MouseMove(getRealMousePos(e, _this.pMainObj, true));};

	var __BXToolbarMouseUp = function(e)
	{
		// Clean event handlers
		removeAdvEvent(document, "mousemove", __BXToolbarMouseMove, true);
		removeAdvEvent(document, "mouseup", __BXToolbarMouseUp, true);
		removeAdvEvent(_this.pMainObj.pEditorDocument, "mousemove", __BXToolbarMouseMoveF, true);
		removeAdvEvent(_this.pMainObj.pEditorDocument, "mouseup", __BXToolbarMouseUp, true);
		if (BX.browser.IsIE())
		{
			removeAdvEvent(_this.pMainObj.pEditorDocument, "selectstart", preventselect, true);
			removeAdvEvent(document, "selectstart", preventselect, true);
		}

		if(_this.pMainObj.bDragging && _this.bDragging)
		{
			_this.pMainObj.bDragging = false;
			_this.bDragging = false;
			_this.pWnd.style.zIndex = "200";
			_this.pWnd.style.border = _this.pWnd.oldBorder;
			pBXEventDispatcher.SetCursor("auto");

			_this.SaveConfiguration();
		}

		// Resize (refresh) taskbarsets
		_this.pMainObj.arTaskbarSet[2]._SetTmpClass(true);
		_this.pMainObj.arTaskbarSet[2].Resize();
		_this.pMainObj.arTaskbarSet[3].Resize();
	};

	var preventselect = function(e){return false;};

	addAdvEvent(document, "mousemove", __BXToolbarMouseMove, true);
	addAdvEvent(this.pMainObj.pEditorDocument, "mousemove", __BXToolbarMouseMoveF, true);
	addAdvEvent(document, "mouseup", __BXToolbarMouseUp, true);
	addAdvEvent(this.pMainObj.pEditorDocument, "mouseup", __BXToolbarMouseUp, true);

	if (BX.browser.IsIE())
	{
		addAdvEvent(this.pMainObj.pEditorDocument, "selectstart", preventselect, true);
		addAdvEvent(document, "selectstart", preventselect, true);
	}

	if (e.stopPropagandation)
		e.stopPropagandation();
	else
		e.cancelBubble = true;
},

// Undock toolbar from toolbarset:
//      .... position = absolute
//      show toolbar title
UnDock: function ()
{
	if(this.pToolbarSet)
		this.pToolbarSet.DelToolbar(this);
	this.pWnd.style.zIndex = "1000";
	this.pWnd.style.position = "absolute";
	document.body.appendChild(this.pWnd);
	var rowIcons = this.pIconsTable.rows[0];
	this.pTitleRow.style.display = GetDisplStr(1);
	this.SetDirection(false);
	this.bDocked = false;
},

Close: function ()
{
	if(this.pToolbarSet)
		this.pToolbarSet.DelToolbar(this);
	this.pWnd.style.display = GetDisplStr(0);

	this.SaveConfiguration();
},

SaveConfiguration: function ()
{
	var arTlbrSet_old = copyObj(SETTINGS[this.pMainObj.name].arToolbarSettings);
	if (this.bDocked)
		this.ReCalcPositions();

	var arTlbrSet = SETTINGS[this.pMainObj.name].arToolbarSettings;
	arTlbrSet[this.name].show = !(this.pWnd.style.display == "none" && this.name != 'standart');
	if (!this.bDocked)
	{
		arTlbrSet[this.name].docked = false;
		arTlbrSet[this.name].position = {x:this.pWnd.style.left,y:this.pWnd.style.top};
	}

	if (compareObj(arTlbrSet_old, arTlbrSet))
		return;

	this.pMainObj.SaveConfig("toolbars", {tlbrset: arTlbrSet});
},

ReCalcPositions: function ()
{
	var arTlbrSet = SETTINGS[this.pMainObj.name].arToolbarSettings;
	var arToolbarSet = this.pMainObj.GetToolbarSet();
	var __arToolBarPos = arToolbarSet[this.actTInd].returnToolbarsPositions();
	arTlbrSet[this.name].docked = true;
	arTlbrSet[this.name].position = [];
	for (var k in __arToolBarPos)
		if (arTlbrSet[k] && arTlbrSet[k].docked)
			arTlbrSet[k].position = [this.actTInd,__arToolBarPos[k][0],__arToolBarPos[k][1]];
},

SetPosition: function (x,y)
{
	if (this.bDocked)
		this.UnDock();

	this.pWnd.style.top = (y || 0) + "px";
	this.pWnd.style.left = (x || 0) + "px";
},

// Mouse moving:
//	if it's dragging than check nearest toolbarset with help of BXToolToolbarSet.HitTest...
//	And dock or undock toolbar....
MouseMove: function(e)
{
	if(this.pMainObj.bDragging && this.bDragging)
	{
		// check: if hit the toolbarset
		var
			left, top,
			bDocked = false, actToolbarSet = false, arPos,
			arToolbarSet = this.pMainObj.GetToolbarSet(),
			i, tl = arToolbarSet.length;

		for(i = 0; i < tl; i++)
		{
			if(arPos = arToolbarSet[i].HitTest(e.realX, e.realY, i))
			{
				bDocked = true;
				actToolbarSet = arToolbarSet[i];
				this.actTInd = i;
				break;
			}
		}

		left = e.realX - this.pMainObj.iLeftDragOffset;
		top = e.realY - this.pMainObj.iTopDragOffset;
		if (isNaN(left) || left < 0)
			left = 0;
		if (isNaN(top) || top < 0)
			top = 0;
		left += 'px';
		top += 'px';

		if(this.bDocked && !bDocked) // toolbar go out from toolbarset
		{
			this.UnDock();
			this.pWnd.style.left = left;
			this.pWnd.style.top = top;
		}
		else if(!this.bDocked && bDocked && actToolbarSet) // toolbar in toolbarset
		{
			if(this.pToolbarSet)
				this.pToolbarSet.DelToolbar(this);
			actToolbarSet.AddToolbar(this, arPos['row'], arPos['col'], arPos['addrow']);
		}
		else if(!this.bDocked && !bDocked)
		{
			this.pWnd.style.left = left;
			this.pWnd.style.top = top;
		}
		else if(arPos["addrow"] || this.row != arPos['row'] || this.col != arPos['col'])
		{
			if(this.pToolbarSet)
				this.pToolbarSet.DelToolbar(this);
			actToolbarSet.AddToolbar(this, arPos['row'], arPos['col'], arPos['addrow']);
		}

		this.bDocked = bDocked;
	}
}
}

function BXRefreshToolbars(pMainObj)
{
	var
		arTlbrSet = SETTINGS[pMainObj.name].arToolbarSettings, sToolBarId, k, BXToolbar;

	for (k in ar_BXToolbarS)
	{
		BXToolbar = ar_BXToolbarS[k];
		sToolBarId = BXToolbar.name;

		if (BXToolbar.pMainObj.name!=pMainObj.name)
			continue;

		if (!arTlbrSet || !arTlbrSet[sToolBarId])
			continue;

		if (arTlbrSet[sToolBarId].show && BXToolbar.pWnd.style.display == 'none')
		{
			if (arTlbrSet[sToolBarId].docked)
					pMainObj.arToolbarSet[arTlbrSet[sToolBarId].position[0]].AddToolbar(BXToolbar,arTlbrSet[sToolBarId].position[1],arTlbrSet[sToolBarId].position[2]);

			BXToolbar.pWnd.style.display = GetDisplStr(1);
		}
		else if (!arTlbrSet[sToolBarId].show && BXToolbar.pWnd.style.display != 'none')
		{
			if (BXToolbar.pToolbarSet)
				BXToolbar.pToolbarSet.DelToolbar(BXToolbar);
			BXToolbar.pWnd.style.display = GetDisplStr(0);
		}
	}
}
