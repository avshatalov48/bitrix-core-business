BXHTMLEditor.prototype.TableOperation = function (obType, operType, arParams)
{
	try{
		this._OnChange("tableOperation", "");

		switch (obType)
		{
			case 'cell': // CELL
				this.TableOperation_cell(operType, arParams);
				break;
			case 'row': // ROW
				this.TableOperation_row(operType, arParams);
				break;
			case 'column': // COLUMN
				this.TableOperation_column(operType, arParams);
				break;
		}
		if(this.bTableBorder)
		{
			this.bTableBorder = false;
			this.ShowTableBorder(true);
		}

		this._OnChange("tableOperation", "");
	}
	catch(e){_alert('Error: TableOperation: obType = ' + obType, operType);}
};


BXHTMLEditor.prototype.TableOperation_cell = function (operType, arParams)
{
	var pElement = arParams.pElement || this.GetSelectionObject();
	switch (operType)
	{
		case 'insert_before':
		case 'insert_after':
			var oTD = BXFindParentByTagName(pElement, 'TD');
			if (!oTD) return;
			var oTR = oTD.parentNode;
			var oTable = oTR.parentNode;
			var cellInd = oTD.cellIndex;
			if (operType == 'insert_after')
				cellInd++;
			var newCell = oTR.insertCell(cellInd);
			newCell.innerHTML = '<br _moz_editor_bogus_node="on">';
			newCell.rowSpan = oTD.rowSpan;
			return;
		case 'delete':
			var arrCells = this.getSelectedCells();
			var cellLen = arrCells.length;
			if (cellLen == 0)
				break;

			var oTR, oTable = arrCells[0].parentNode.parentNode;
			for(var i = 0; i < cellLen; i++)
			{
				oTR = arrCells[i].parentNode;
				oTR.removeChild(arrCells[i]);
				if (oTR.cells.length == 0)
					oTable.removeChild(oTR);
			}

			if (oTable.rows.length == 0)
				oTable.parentNode.removeChild(oTable);
			return;
		case 'merge':
			var arrCells = this.getSelectedCells();
			var cellLen = arrCells.length;
			if (cellLen < 2)
				break;

			var zeroCellInd = arrCells[0].cellIndex;
			var zeroRowInd = arrCells[0].parentNode.rowIndex;
			var newCellContent = '';

			var arRowSpan = {};
			var arColSpan = {};
			var bVert = false;
			var bHor = false;

			for(var i = cellLen - 1; i >= 0; i--)
			{
				newCellContent += arrCells[i].innerHTML;
				oTR = arrCells[i].parentNode;
				if (i != 0)
				{
					if (oTR.rowIndex != zeroRowInd)
						var bVert = true;
					else
						var bHor = true;
				}

				arRowSpan[oTR.rowIndex] = arrCells[i].rowSpan;
				arColSpan[arrCells[i].cellIndex] = arrCells[i].colSpan;
				oTR.removeChild(arrCells[i]);
			}
			var newCell = oTR.insertCell(zeroCellInd);
			newCell.innerHTML = newCellContent;
			if (bHor)
			{
				var newCellColSpan = 0;
				for (var i in arColSpan)
					newCellColSpan += arColSpan[i];
				if (newCellColSpan > 1)
					newCell.colSpan = newCellColSpan;
			}
			if (bVert)
			{
				var newCellRowSpan = 0;
				for (var i in arRowSpan)
					newCellRowSpan += arRowSpan[i];
				if (newCellRowSpan > 1)
					newCell.rowSpan = newCellRowSpan;
			}
			return;
		case 'mergeright':
			var arrCells = this.getSelectedCells();
			if (arrCells.length != 1)
				break;
			var oTR = arrCells[0].parentNode;
			var rCell = oTR.cells[arrCells[0].cellIndex + 1];
			if (rCell)
			{
				arrCells[0].innerHTML += rCell.innerHTML;
				arrCells[0].colSpan += rCell.colSpan;
				oTR.removeChild(rCell);
			}
			return;
		case 'mergebottom':
			var arrCells = this.getSelectedCells();
			if (arrCells.length != 1)
				break;
			var oTR = arrCells[0].parentNode;
			var realInd = 0;
			for(var i = 0; i <= arrCells[0].cellIndex; i++)
				realInd += oTR.cells[i].colSpan;

			var
				newRealInd = 0,
				newTRInd = oTR.rowIndex + arrCells[0].rowSpan,
				newTR = oTR.parentNode.rows[newTRInd];

			i = 0;
			while (newRealInd < realInd && i < newTR.cells.length)
				newRealInd += newTR.cells[i++].colSpan;
			var newCell = newTR.cells[--i];
			arrCells[0].innerHTML += newCell.innerHTML;
			arrCells[0].rowSpan += newCell.rowSpan;
			newTR.removeChild(newCell);
			return;
		case 'splithorizontally':
			var arrCells = this.getSelectedCells();
			if (arrCells.length != 1)
				break;

			var oTR = arrCells[0].parentNode;
			var oTable = oTR.parentNode;
			var realInd = 0;
			for(var i = 0; i <= arrCells[0].cellIndex; i++)
				realInd += oTR.cells[i].colSpan;

			var colSpan = arrCells[0].colSpan;
			if (colSpan > 1)
			{
				arrCells[0].colSpan--;
			}
			else
			{
				for(var j = 0; j < oTable.rows.length; j++)
				{
					if (j == oTR.rowIndex)
						continue;
					var newRealInd = 0;
					var newTR = oTable.rows[j];

					i = 0;
					while (newRealInd < realInd && i < newTR.cells.length)
						newRealInd += newTR.cells[i++].colSpan;

					var newCell = newTR.cells[--i];
					newCell.colSpan += 1;
				}
			}
			var newCell = oTR.insertCell(arrCells[0].cellIndex + 1);
			newCell.innerHTML = '<br _moz_editor_bogus_node="on">';
			newCell.rowSpan = arrCells[0].rowSpan;
			return;
		case 'splitvertically':
			var arrCells = this.getSelectedCells();
			if (arrCells.length != 1)
				break;

			var oTR = arrCells[0].parentNode;
			var oTable = oTR.parentNode;

			var arTMX = this.CreateTableMatrix(oTable);
			var arInd = this.GetIndexes(arrCells[0], arTMX);
			var maxCellCount = arTMX[0].length; //max count of cell in table

			var curRowIndex = oTR.rowIndex;
			var curCellIndex = arrCells[0].cellIndex;
			var arIndLen = arInd.length;

			var curFullRowInd = arInd[0].r;
			var curFullCellInd = arInd[0].c;
			var bOneW = true;
			var bOneH = true;

			for(var i = 1; i < arIndLen; i++)
			{
				if (arInd[i].r != curFullRowInd)
					bOneH = false;
				if (arInd[i].c != curFullCellInd)
					bOneW = false;
			}

			if (!bOneH) // If cell has rowspan > 1
			{
				var _tr = oTable.rows[curRowIndex + --arrCells[0].rowSpan];
				var _arInd, rci, realCellInd = false;
				r:
				for(var c = 0; c < _tr.cells.length; c++)
				{
					_arInd = this.GetIndexes(_tr.cells[c], arTMX);
					for(var i = 0, _arIndLen = _arInd.length; i < _arIndLen; i++)
					{
						rci = _arInd[i].c;
						if (rci > curCellIndex)
							realCellInd = 0;
						else if (rci + 1 == curCellIndex)
							realCellInd = _tr.cells[c].cellIndex + 1;

						if (realCellInd !== false)
							break r;
					}
				}

				_ftd = _tr.cells[0];
				var _td = _tr.insertCell(realCellInd);
				_td.innerHTML = '<br _moz_editor_bogus_node="on">';
				if (!bOneW)
					_td.colSpan = arrCells[0].colSpan;
			}
			else // if rowSpan == 1 and we have to split this cell
			{
				var newRow = oTable.insertRow(oTR.rowIndex + 1);
				var newCell = newRow.insertCell(-1);
				newCell.innerHTML = '<br _moz_editor_bogus_node="on">';
				if (!bOneW)
					newCell.colSpan = arrCells[0].colSpan;

				var oRow, oCell;
				var cellCount = 0;
				for(var r = 0; r <= curFullRowInd; r++)
				{
					oRow = oTable.rows[r];
					for(var c = 0; c < oRow.cells.length; c++)
					{
						oCell = oRow.cells[c];
						if (r == curRowIndex && c == curCellIndex)
							continue;

						var fullRowInd = r; // oRow.rowIndex
						if (oCell.rowSpan > 1)
							fullRowInd += oCell.rowSpan - 1;

						if (fullRowInd >= curFullRowInd)
							oCell.rowSpan++;
					}
				}
			}
			return;
	}
};

BXHTMLEditor.prototype.TableOperation_row = function(operType, arParams)
{
	var pElement = arParams.pElement || this.GetSelectionObject();
	switch (operType)
	{
		case 'insertbefore':
		case 'insertafter':
			var oTD = BXFindParentByTagName(pElement, 'TD');
			if (!oTD)
				return;
			var oTR = oTD.parentNode;
			var oTable = oTR.parentNode;
			var rowInd = oTR.rowIndex;
			if (operType == 'insertafter')
				rowInd++;

			var newRow = oTable.insertRow(rowInd);
			var cellsCount = oTR.cells.length;

			for(var i = 0; i < cellsCount; i++)
			{
				var newCell = newRow.insertCell(i);
				newCell.innerHTML = '<br _moz_editor_bogus_node="on">';
				newCell.colSpan = oTR.cells[i].colSpan;
			}
			return;
		case 'mergecells':
			var oTD = BXFindParentByTagName(pElement, 'TD');
			if (!oTD)
				return;
			var oTR = oTD.parentNode;
			var cellsCount = oTR.cells.length;
			if (cellsCount < 2)
				return;
			var zeroColSpan = oTR.cells[0].colSpan;
			var zeroInnerHTML = oTR.cells[0].innerHTML;
			for(var i = 1; i < cellsCount; i++)
			{
				zeroColSpan += oTR.cells[1].colSpan;
				zeroInnerHTML += oTR.cells[1].innerHTML;
				oTR.removeChild(oTR.cells[1]);
			}
			oTR.cells[0].innerHTML = zeroInnerHTML;
			oTR.cells[0].colSpan = zeroColSpan;
			return;
		case 'delete':
			var oTD = BXFindParentByTagName(pElement, 'TD');
			if (!oTD)
				return;
			var oTR = oTD.parentNode;
			var oTable = oTR.parentNode;
			oTable.removeChild(oTR);
			if (oTable.rows.length == 0)
				oTable.parentNode.removeChild(oTable);
			return;
	}
};

BXHTMLEditor.prototype.TableOperation_column = function(operType, arParams)
{
	var pElement = arParams.pElement || this.GetSelectionObject();
	switch (operType)
	{
		case 'insertleft':
			var oTD = BXFindParentByTagName(pElement, 'TD');
			if (!oTD) return;
			var oTR = oTD.parentNode;
			var oTable = oTR.parentNode;
			var cellInd = oTD.cellIndex;
			var rowInd = oTR.rowIndex;

			var arTMX = this.CreateTableMatrix(oTable);
			var arInd = this.GetIndexes(oTD, arTMX);

			var newCell = oTR.insertCell(cellInd);
			newCell.innerHTML = '<br _moz_editor_bogus_node="on">';

			var curCellIndex = oTD.cellIndex;
			var arIndLen = arInd.length;
			var curFullCellInd = arInd[0].c;

			var r, ind, i, c;
			for (var j = 0, l1 = oTable.rows.length; j < l1; j++)
			{
				r = oTable.rows[j];
				if (r.rowIndex == rowInd)
					continue;

				ind = 0;
				if (curFullCellInd != 0)
				{
					i = 0;
					for(var i=0, l2 = r.cells.length; i < l2; i++)
					{
						c = r.cells[i];
						arInd = this.GetIndexes(c, arTMX);
						if (arInd[0].c >= curFullCellInd)
						{
							ind = c.cellIndex;
							break;
						}
						ind = i + 1;
					}
				}

				var newCell = r.insertCell(ind);
				newCell.innerHTML = '<br _moz_editor_bogus_node="on">';
			}
			return;
		case 'insertright':
			var oTD = BXFindParentByTagName(pElement, 'TD');
			if (!oTD)
				return;
			var oTR = oTD.parentNode;
			var oTable = oTR.parentNode;
			var cellInd = oTD.cellIndex;
			var rowInd = oTR.rowIndex;

			var arTMX = this.CreateTableMatrix(oTable);
			var arInd = this.GetIndexes(oTD, arTMX);
			var newCell = oTR.insertCell(cellInd + 1);
			newCell.innerHTML = '<br _moz_editor_bogus_node="on">';

			var curCellIndex = oTD.cellIndex;
			var arIndLen = arInd.length;
			var curFullCellInd = arInd[0].c;

			var r, ind, i, c;
			for (var j = 0, l1 = oTable.rows.length; j < l1; j++)
			{
				r = oTable.rows[j];
				if (r.rowIndex == rowInd)
					continue;

				ind = 0;
				i = 0;
				for(var i=0, l2 = r.cells.length; i < l2; i++)
				{
					c = r.cells[i];
					arInd = this.GetIndexes(c, arTMX);
					if (arInd[0].c >= curFullCellInd + 1)
					{
						ind = c.cellIndex;
						break;
					}
					ind = i + 1;
				}
				var newCell = r.insertCell(ind);
				newCell.innerHTML = '<br _moz_editor_bogus_node="on">';
			}
			return;
		case 'mergecells':
			var oTD = BXFindParentByTagName(pElement, 'TD');
			if (!oTD)
				return;
			var oTR = oTD.parentNode;
			var oTable = oTR.parentNode;

			var arTMX = this.CreateTableMatrix(oTable);
			var arInd = this.GetIndexes(oTD, arTMX);

			var zeroCell = arTMX[0][arInd[0].c];
			var _innerHTML = zeroCell.innerHTML;
			var c;
			for (var j = 1, l = arTMX.length; j < l; j++)
			{
				c = arTMX[j][arInd[0].c];
				_innerHTML += c.innerHTML;
				c.parentNode.removeChild(c);
			}
			zeroCell.rowSpan = arTMX.length;
			zeroCell.innerHTML = _innerHTML;
			return;
		case 'delete':
			var oTD = BXFindParentByTagName(pElement, 'TD');
			if (!oTD)
				return;
			var oTR = oTD.parentNode;
			var oTable = oTR.parentNode;
			var arTMX = this.CreateTableMatrix(oTable);
			var arInd = this.GetIndexes(oTD, arTMX);

			var c, r;
			for (var j = 0, l = arTMX.length; j < l; j++)
			{
				c = arTMX[j][arInd[0].c];
				if (!c) continue;
				r = c.parentNode;
				if (!r) continue;
				_innerHTML += c.innerHTML;
				r.removeChild(c);
				if (r.cells.length == 0)
					oTable.removeChild(r);
			}

			if (oTable.rows.length == 0)
				oTable.parentNode.removeChild(oTable);
			return;
	}
};

//r- row; i-index;
BXHTMLEditor.prototype.CreateTableMatrix = function(oTable)
{
	var aRows = oTable.rows;
	// Row and Column counters.
	var r = -1;

	var arMatrix = new Array();

	for (var i = 0; i < aRows.length; i++)
	{
		r++;
		if (!arMatrix[r])
			arMatrix[r] = [];

		var c = -1;

		for (var j = 0; j < aRows[i].cells.length; j++)
		{
			var oCell = aRows[i].cells[j];

			c++;
			while (arMatrix[r][c])
				c++;

			var iColSpan = isNaN(oCell.colSpan) ? 1 : oCell.colSpan;
			var iRowSpan = isNaN(oCell.rowSpan) ? 1 : oCell.rowSpan;

			for(var rs = 0; rs < iRowSpan; rs++)
			{
				if (!arMatrix[r + rs])
					arMatrix[r + rs] = [];

				for (var cs = 0; cs < iColSpan; cs++)
					arMatrix[r + rs][c + cs] = aRows[i].cells[j];
			}

			c += iColSpan - 1;
		}
	}
	return arMatrix;
};

BXHTMLEditor.prototype.GetIndexes  = function(oCell, arMatrix)
{
	var arR, arC, arIndexes = [];
	for (var i = 0; i < arMatrix.length; i++)
		for (var j = 0, l = arMatrix[i].length; j < l; j++)
			if (arMatrix[i][j] == oCell)
				arIndexes.push({'r' : i, 'c' : j});
	return arIndexes;
};

//Return array of cells, which/( of which) was selected
BXHTMLEditor.prototype.getSelectedCells = function()
{
	var arrCells = [];

	if (BX.browser.IsIE())
	{
		// IE
		var oParent, oRange = this.pEditorDocument.selection.createRange();
		var oParent = BXFindParentByTagName(this.GetSelectionObject(), 'TR');
		if (oParent)
		{
			// Loops throw all cells checking if the cell is, or part of it, is inside the selection
			// and then add it to the selected cells collection.
			for( var i = 0; i < oParent.cells.length; i++ )
			{
				var oCellRange = this.pEditorDocument.selection.createRange();
				oCellRange.moveToElementText(oParent.cells[i]);

				if (oRange.inRange(oCellRange) ||
					(oRange.compareEndPoints('StartToStart', oCellRange) >= 0 &&  oRange.compareEndPoints('StartToEnd',oCellRange) <= 0) ||
					(oRange.compareEndPoints('EndToStart', oCellRange) >= 0 &&  oRange.compareEndPoints('EndToEnd',oCellRange) <= 0 ))
					arrCells.push(oParent.cells[i]);
			}
		}
	}
	else
	{
		//Gecko, Opera
		var oSelection = this.pEditorWindow.getSelection();
		if (oSelection.rangeCount == 1 && oSelection.anchorNode.nodeType == 3 )
		{
			var c = BXFindParentByTagName(oSelection.anchorNode, 'TD');
			if (c)
				return [c];
		}
		for(var i = 0, l = oSelection.rangeCount; i < l; i++)
		{
			var oRange = oSelection.getRangeAt(i);
			if (oRange.startContainer.nodeName == 'TD' || oRange.startContainer.nodeName == 'TH')
				arrCells.push(oRange.startContainer);
			else if(oRange.startContainer.nodeName=='TR')
				arrCells.push(oRange.startContainer.childNodes[oRange.startOffset]);
		}
	}
	return arrCells;
};