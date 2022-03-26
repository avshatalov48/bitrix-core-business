import './css/style.css';
import { Draggable } from 'ui.draganddrop.draggable';
import { Event } from 'main.core';

export default class TableEditor
{
	constructor(node: HTMLElement)
	{
		this.node = node;
		this.tBody = this.node.getElementsByTagName('tbody')[0];
		this.table = node.querySelector('.landing-table');
		this.addTitles(this.node);
		this.enableEditCells(this.table);
		this.dragAndDropRows(this);
		this.dragAndDropCols(this);
		this.resizeColumn(this);
		this.buildLines(this);
		this.addRow(this);
		this.addCol(this);
		this.onUnselect(this);
		this.unselect(this);
		this.selectAll(this);
		this.selectRow(this);
		this.selectCol(this);
		this.onCopyTable(this);
		this.onDeleteElementTable(this);
		this.onShowPopupMenu(this);
	}

	addTitles(tableNode)
	{
		if (!tableNode.hasAttribute('title-added'))
		{
			tableNode.title = '';
			tableNode.querySelector('.landing-table-th-select-all').title = BX.Landing.Utils.escapeText(
				BX.Landing.Loc.getMessage("LANDING_TABLE_SELECT_TABLE")
			);
			tableNode.querySelectorAll('.landing-table-div-col-dnd').forEach(function(element) {
				element.title = BX.Landing.Utils.escapeText(
					BX.Landing.Loc.getMessage("LANDING_TABLE_DND_COLS")
				);
			})
			tableNode.querySelectorAll('.landing-table-col-resize').forEach(function(element) {
				element.title = BX.Landing.Utils.escapeText(
					BX.Landing.Loc.getMessage("LANDING_TABLE_RESIZE_COLS")
				);
			})
			tableNode.querySelectorAll('.landing-table-col-add').forEach(function(element) {
				element.title = BX.Landing.Utils.escapeText(
					BX.Landing.Loc.getMessage("LANDING_TABLE_BUTTON_ADD_COL")
				);
			})
			tableNode.querySelectorAll('.landing-table-row-dnd').forEach(function(element) {
				element.title = BX.Landing.Utils.escapeText(
					BX.Landing.Loc.getMessage("LANDING_TABLE_DND_ROWS")
				);
			})
			tableNode.querySelectorAll('.landing-table-row-add').forEach(function(element) {
				element.title = BX.Landing.Utils.escapeText(
					BX.Landing.Loc.getMessage("LANDING_TABLE_BUTTON_ADD_ROW")
				);
			})
			tableNode.querySelectorAll('.landing-table-td').forEach(function(element) {
				element.title = BX.Landing.Utils.escapeText(
					BX.Landing.Loc.getMessage("LANDING_TABLE_BUTTON_CHANGE_TEXT")
				);
			})
			tableNode.setAttribute('title-added', 'true');
		}
	}

	unselect(tableEditor, isSelectAll = false)
	{
		if (!isSelectAll)
		{
			tableEditor.table.classList.remove('table-selected-all');
			this.removeClasses(tableEditor.table, 'landing-table-th-select-all-selected');
			this.removeClasses(tableEditor.table, 'landing-table-cell-selected');
		}
		this.removeClasses(tableEditor.table, 'landing-table-row-selected');
		this.removeClasses(tableEditor.table, 'landing-table-th-selected');
		this.removeClasses(tableEditor.table, 'landing-table-th-selected-cell');
		this.removeClasses(tableEditor.table, 'landing-table-th-selected-top');
		this.removeClasses(tableEditor.table, 'landing-table-th-selected-x');
		this.removeClasses(tableEditor.table, 'landing-table-tr-selected-left');
		this.removeClasses(tableEditor.table, 'landing-table-tr-selected-y');
		this.removeClasses(tableEditor.table, 'landing-table-col-selected');
		this.removeClasses(tableEditor.table, 'landing-table-tr-selected');
		this.removeClasses(tableEditor.table, 'table-selected-all-right');
		this.removeClasses(tableEditor.table, 'table-selected-all-bottom');
	}

	onUnselect(tableEditor)
	{
		Event.bind(tableEditor.table, 'click', () => {
			const classList = ['landing-table-th-select-all', 'landing-table-row-dnd', 'landing-table-row-add'];
			let isContains = [...event.target.classList].some(className => classList.includes(className));
			if (!isContains)
			{
				const classListChild = ['landing-table-col-dnd'];
				isContains = [...event.target.parentElement.classList].some(className => classListChild.includes(className));
				if (!isContains)
				{
					tableEditor.unselect(tableEditor);
				}
			}
		});
	}

	selectAll(tableEditor)
	{
		let thTech = tableEditor.table.querySelector('.landing-table-th-select-all');
		Event.bind(thTech, 'click', () => {
			let isSelectedTable = false;
			if (tableEditor.table.classList.contains('table-selected-all'))
			{
				isSelectedTable = true;
			}
			tableEditor.unselect(tableEditor, true);
			let setRows = tableEditor.table.querySelectorAll('.landing-table-tr');
			let count = 0;
			setRows.forEach(function(row){
				let setTh = row.childNodes;
				let index = 0;
				let lastThIndex = 0;
				row.childNodes.forEach(function(cell) {
					if (cell.nodeType === 1)
					{
						lastThIndex = index;
					}
					index++;
				})
				if (count > 0)
				{
					let lastTh = setTh[lastThIndex];
					if (isSelectedTable)
					{
						lastTh.classList.remove('table-selected-all-right');
					}
					else
					{
						lastTh.classList.add('table-selected-all-right');
					}
				}
				count++;
				if (count === setRows.length)
				{
					setTh.forEach(function(th) {
						if (th.nodeType === 1)
						{
							if (isSelectedTable)
							{
								th.classList.remove('table-selected-all-bottom');
							}
							else
							{
								th.classList.add('table-selected-all-bottom');
							}
						}
					})
				}
			})
			thTech.classList.toggle('landing-table-th-select-all-selected');
			tableEditor.table.classList.toggle('table-selected-all');
			tableEditor.table.querySelectorAll('.landing-table-col-dnd').forEach(function(thDnd){
				thDnd.classList.toggle('landing-table-cell-selected');
			})
			tableEditor.table.querySelectorAll('.landing-table-row-dnd').forEach(function(trDnd){
				trDnd.classList.toggle('landing-table-cell-selected');
			})
		});
	}

	selectRow(tableEditor, neededPosition = null)
	{
		let setTrDnd = tableEditor.table.querySelectorAll('.landing-table-row-dnd');
		if (neededPosition !== null)
		{
			var newSetTrDnd = [];
			newSetTrDnd[0] = setTrDnd[neededPosition];
			setTrDnd = newSetTrDnd;
		}
		setTrDnd.forEach(function(trDnd){
			Event.bind(trDnd, 'click', () => {
				if (!event.srcElement.classList.contains('landing-table-row-add'))
				{
					tableEditor.unselect(tableEditor);
					let setTh = trDnd.parentElement.childNodes;
					let count = 0;
					setTh.forEach(function(th){
						if (th.nodeType === 1)
						{
							if (count === 1)
							{
								th.classList.add('landing-table-tr-selected-left');
							}
							if (count >= 1)
							{
								th.classList.add('landing-table-tr-selected-y');
							}
							count++;
						}
					})
					trDnd.parentElement.classList.add('landing-table-row-selected');
					tableEditor.tBody.classList.add('landing-table-tr-selected');
				}
			});
		});
	}

	selectCol(tableEditor, neededPosition = null)
	{
		let setThDnd = tableEditor.table.querySelectorAll('.landing-table-col-dnd');
		if (neededPosition !== null)
		{
			var newSetTrDnd = [];
			newSetTrDnd[0] = setThDnd[neededPosition];
			setThDnd = newSetTrDnd;
		}
		setThDnd.forEach(function(thDnd){
			Event.bind(thDnd, 'click', () => {
				if (!event.srcElement.classList.contains('landing-table-col-add')
					&& !event.srcElement.classList.contains('landing-table-col-resize'))
				{
					tableEditor.unselect(tableEditor);
					let cellIndex = thDnd.cellIndex;
					let count = 0;
					tableEditor.tBody.childNodes.forEach(function(tr) {
						if (tr.nodeType === 1)
						{
							let countNode = 0;
							let nodeCount = 0;
							let needNodePosition = 0;
							tr.childNodes.forEach(function(trChild) {
								if (trChild.nodeType === 1)
								{
									if (cellIndex === nodeCount)
									{
										needNodePosition = countNode;
									}
									nodeCount++
								}
								countNode++;
							})
							if (count === 0)
							{
								tr.classList.add('landing-table-col-selected');
								tr.childNodes[needNodePosition].classList.add('landing-table-th-selected-cell');
							}
							if (count === 1)
							{
								tr.childNodes[needNodePosition].classList.add('landing-table-th-selected-top');
							}
							if (count >= 1)
							{
								tr.childNodes[needNodePosition].classList.add('landing-table-th-selected-x');
							}
							count++;
							tr.childNodes[needNodePosition].classList.add('landing-table-th-selected');
						}
					})
				}
			});
		})
	}

	buildLines(tableEditor)
	{
		if (tableEditor.node)
		{
			let width = tableEditor.node.querySelector('.landing-table').getBoundingClientRect().width;
			let height = tableEditor.node.querySelector('.landing-table').getBoundingClientRect().height;
			const offset = 5;
			let linesX = document.querySelectorAll('.landing-table-row-add-line');
			linesX.forEach(function(lineX) {
				lineX.style.width = width + offset + "px";
			});
			let linesY = document.querySelectorAll('.landing-table-col-add-line');
			linesY.forEach(function(lineY) {
				lineY.style.height = height + offset + "px";
			});
		}
	}

	getButtonsAddRow(node)
	{
		return node.querySelectorAll('.landing-table-row-add');
	}

	addRow(tableEditor, neededPosition = null)
	{
		let buttons = tableEditor.getButtonsAddRow(tableEditor.node);
		if (neededPosition !== null)
		{
			let button = buttons[neededPosition];
			buttons = [];
			buttons[0] = button;
		}
		else
		{
			buttons = Array.prototype.slice.call(buttons,0);
		}
		buttons = Array.prototype.slice.call(buttons,0);
		buttons.forEach(function(button){
			Event.bind(button, 'click', () => {
				let selectedCell = tableEditor.table.querySelector('.landing-table-th-selected-cell');
				let selectedCellPos = 0;
				let nodeCount = 0;
				if (selectedCell)
				{
					selectedCell.parentNode.childNodes.forEach(function(node) {
						if (selectedCellPos === 0 && node === selectedCell)
						{
							selectedCellPos = nodeCount;
						}
						if (node.nodeType === 1)
						{
							nodeCount++;
						}
					})
				}
				let trDnd = document.createElement('th');
				trDnd.classList.add('landing-table-th', 'landing-table-row-dnd');
				if (tableEditor.table.classList.contains('table-selected-all'))
				{
					trDnd.classList.add('landing-table-cell-selected');
				}
				let row = button.parentNode.parentNode;
				let neededPosition = [...row.parentNode.children].indexOf(button.parentNode.parentNode)
				let count = 0;
				let lastElementPosition = 0;
				tableEditor.tBody.childNodes.forEach(function(element) {
					if (element.nodeType === 1)
					{
						lastElementPosition = count;
					}
					count++;
				})
				let tr = tableEditor.tBody.childNodes[lastElementPosition];
				let newTd = document.createElement('td');
				newTd.classList.add('landing-table-th', 'landing-table-td');
				newTd.style.width = '50px';
				let table = tableEditor.node.querySelector('.landing-table');
				if (table.hasAttribute('bg-color'))
				{
					newTd.style.backgroundColor = table.getAttribute('bg-color');
				}
				if (table.hasAttribute('text-color'))
				{
					newTd.style.color = table.getAttribute('text-color');
				}
				let newTr = document.createElement('tr');
				newTr.classList.add('landing-table-tr');
				trDnd.title = BX.Landing.Utils.escapeText(BX.Landing.Loc.getMessage("LANDING_TABLE_DND_ROWS"));
				trDnd.style.width = '16px';
				let divAddRow = document.createElement('div');
				divAddRow.classList.add('landing-table-row-add');
				divAddRow.title = BX.Landing.Utils.escapeText(BX.Landing.Loc.getMessage("LANDING_TABLE_BUTTON_ADD_COL"));
				let divLineX = document.createElement('div');
				divLineX.classList.add('landing-table-row-add-line');
				let divRowDnd = document.createElement('div');
				divRowDnd.classList.add('landing-table-div-row-dnd');
				divAddRow.appendChild(divLineX);
				trDnd.appendChild(divAddRow);
				trDnd.appendChild(divRowDnd);
				if (tr)
				{
					const count = tr.children.length;
					let setTd = [];
					button.parentNode.parentNode.childNodes.forEach(function(item) {
						if (item.nodeType == 1)
						{
							setTd.push(item);
						}
					})
					for (let i = 0; i < count; i++)
					{
						let newTdCloned = newTd.cloneNode(true);
						if (i === selectedCellPos)
						{
							newTdCloned.classList.add('landing-table-th-selected', 'landing-table-th-selected-x');
						}
						if (i === 0)
						{
							newTr.appendChild(trDnd);
						}
						else
						{
							newTdCloned.style.width = setTd[i].style.width;
							newTdCloned.style.height = setTd[i].style.height;
							newTr.appendChild(newTdCloned);
						}
					}
				}
				button.parentNode.parentNode.parentNode.insertBefore(newTr, button.parentNode.parentNode.nextSibling);
				tableEditor.buildLines(tableEditor);
				tableEditor.enableEditCells(tableEditor.node);
				BX.Landing.Block.Node.Text.currentNode.onChange(true);
				tableEditor.selectRow(tableEditor, neededPosition);
				tableEditor.addRow(tableEditor, neededPosition);
				tableEditor.unselect(tableEditor);
				BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
			});
		})
	}

	getButtonsAddCol(node)
	{
		return node.querySelectorAll('.landing-table-col-add');
	}

	addCol(tableEditor, neededPosition = null)
	{
		let buttons = tableEditor.getButtonsAddCol(tableEditor.node);
		if (neededPosition !== null)
		{
			// todo: is ok?
			let button = buttons[neededPosition];
			buttons = [];
			buttons[0] = button;
		}
		else
		{
			buttons = Array.prototype.slice.call(buttons,0);
		}
		buttons.forEach(function(button){
			Event.bind(button, 'click', () => {
				let selectedRow = tableEditor.table.querySelector('.landing-table-row-selected');
				let selectedRowPos = 0;
				let countNode = 0;
				if (selectedRow)
				{
					selectedRow.parentNode.childNodes.forEach(function(node) {
						if (node === selectedRow && selectedRowPos === 0)
						{
							selectedRowPos = countNode;
						}
						if (node.nodeType === 1)
						{
							countNode++;
						}
					})
				}
				let newThFirst;
				let newThFirstCloned;
				newThFirst = document.createElement('th');
				newThFirst.classList.add('landing-table-th', 'landing-table-col-dnd');
				newThFirst.style.width = '50px';
				if (tableEditor.table.classList.contains('table-selected-all'))
				{
					newThFirst.classList.add('landing-table-cell-selected');
				}
				let row = button.parentNode.parentNode;
				let neededPosition = [...row.children].indexOf(button.parentNode)
				if (tableEditor.tBody.childNodes.length > 0)
				{
					let count = 0;
					tableEditor.tBody.childNodes.forEach(function(element) {
						if (element.nodeType === 1)
						{
							newThFirstCloned = newThFirst.cloneNode(true);
							let divColumnDnd = document.createElement('div');
							divColumnDnd.classList.add('landing-table-div-col-dnd');
							divColumnDnd.title = BX.Landing.Utils.escapeText(
								BX.Landing.Loc.getMessage("LANDING_TABLE_DND_COLS")
							);
							let divColumnResize = document.createElement('div');
							divColumnResize.classList.add('landing-table-col-resize');
							divColumnResize.title = BX.Landing.Utils.escapeText(
								BX.Landing.Loc.getMessage("LANDING_TABLE_RESIZE_COLS")
							);
							let divAddColHere = document.createElement('div');
							divAddColHere.classList.add('landing-table-col-add');
							divAddColHere.title = BX.Landing.Utils.escapeText(
								BX.Landing.Loc.getMessage("LANDING_TABLE_BUTTON_ADD_COL")
							);
							let divLineY = document.createElement('div');
							divLineY.classList.add('landing-table-col-add-line');
							divAddColHere.appendChild(divLineY);
							newThFirstCloned.appendChild(divColumnDnd);
							newThFirstCloned.appendChild(divColumnResize);
							newThFirstCloned.appendChild(divAddColHere);
							let newTd = document.createElement('td');
							newTd.classList.add('landing-table-th', 'landing-table-td');
							newTd.style.width = '50px';
							let table = tableEditor.node.querySelector('.landing-table');
							if (table.hasAttribute('bg-color'))
							{
								newTd.style.backgroundColor = table.getAttribute('bg-color');
							}
							if (table.hasAttribute('text-color'))
							{
								newTd.style.color = table.getAttribute('text-color');
							}
							if (selectedRowPos > 0 && selectedRowPos === count)
							{
								newTd.classList.add('landing-table-tr-selected-y');
							}
							let countChild = 0;
							let countNodes = 0;
							let newNeededPosition = 0;
							element.childNodes.forEach(function(node) {
								if (node.nodeType === 1)
								{
									if (countNodes === neededPosition)
									{
										newNeededPosition = countChild;
									}
									countNodes++;
								}
								countChild++;
							})
							if (count === 0)
							{
								element.childNodes[newNeededPosition].parentNode.insertBefore(
									newThFirstCloned,
									element.childNodes[newNeededPosition].nextSibling
								);
							}
							else
							{
								element.childNodes[newNeededPosition].parentNode.insertBefore(
									newTd,
									element.childNodes[newNeededPosition].nextSibling
								);
							}
							count++;
						}
					})
				}
				tableEditor.buildLines(tableEditor);
				tableEditor.enableEditCells(tableEditor.node);
				BX.Landing.Block.Node.Text.currentNode.onChange(true);
				tableEditor.selectCol(tableEditor, neededPosition);
				tableEditor.addCol(tableEditor, neededPosition);
				tableEditor.unselect(tableEditor);
				BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
			});
		})
	}

	dragAndDropRows(tableEditor)
	{
		this.draggableRows = new Draggable({
			container: tableEditor.tBody,
			draggable: '.landing-table-tr',
			dragElement: '.landing-table-row-dnd',
			type: Draggable.HEADLESS,
		});

		let rows = [];
		let setRowPositionsY;
		let setRowHeights;
		let currentPositionRow;
		let newPositionRow = 0;
		let draggableRowOffsetY;
		let tablePositionLeft;
		let tablePositionTop;
		let currentPositionRowX;
		let currentPositionRowY;
		let cloneRow;
		let originalSource;

		this.draggableRows
			.subscribe('start', (event) => {
				originalSource = this.draggableRows.dragStartEvent.data.originalSource;
				tablePositionLeft = tableEditor.tBody.getBoundingClientRect().left;
				tablePositionTop = tableEditor.tBody.getBoundingClientRect().top;
				setRowPositionsY = [];
				setRowHeights = [];
				draggableRowOffsetY = 0;
				currentPositionRow = event.getData().sourceIndex;
				rows = tableEditor.tBody.querySelectorAll('.landing-table-tr');
				rows.forEach(function(row) {
					setRowPositionsY.push(row.getBoundingClientRect().y);
					setRowHeights.push(row.getBoundingClientRect().height);
				});
				currentPositionRowX = rows[currentPositionRow].getBoundingClientRect().x;
				currentPositionRowY = rows[currentPositionRow].getBoundingClientRect().y;
				cloneRow = document.createElement('tr');
				cloneRow.classList.add('landing-table-tr-draggable');
				rows[currentPositionRow].childNodes.forEach(function(node) {
					cloneRow.append(node.cloneNode(true));
				})
				if (rows[currentPositionRow].classList.contains('landing-table-row-selected'))
				{
					cloneRow.classList.add('landing-table-row-selected');
				}
				let indexFirstNode;
				let count = 0;
				while (!indexFirstNode)
				{
					if (rows[currentPositionRow].childNodes[count].nodeType === 1)
					{
						indexFirstNode = count;
					}
					count++;
				}
				cloneRow.childNodes[indexFirstNode].style.borderRadius = getComputedStyle(rows[currentPositionRow].childNodes[indexFirstNode]).borderRadius;
			})
			.subscribe('move', (event) => {
				if (!originalSource.classList.contains('landing-table-row-add'))
				{
					tableEditor.tBody.classList.add("landing-table-draggable");
					rows[currentPositionRow].classList.add('landing-table-tr-taken');
					draggableRowOffsetY = event.getData().offsetY;
					tableEditor.tBody.append(cloneRow);
					cloneRow.style.position = `absolute`;
					cloneRow.style.top = currentPositionRowY - tablePositionTop + draggableRowOffsetY - 0.5 + 'px';
					cloneRow.style.left = currentPositionRowX - tablePositionLeft - 0.5 + 'px';
					if (draggableRowOffsetY > 0)
					{
						cloneRow.style.transform = 'rotate(-1deg)';
					}
					else
					{
						cloneRow.style.transform = 'rotate(1deg)';
					}
				}
			})
			.subscribe('end', () => {
				cloneRow.remove();
				rows[currentPositionRow].classList.remove('landing-table-tr-taken');
				rows[currentPositionRow].style = '';
				let newDraggableRowPositionY = currentPositionRowY + draggableRowOffsetY;
				let newDraggableRowPositionBottomY = newDraggableRowPositionY + rows[currentPositionRow].getBoundingClientRect().height;
				if (draggableRowOffsetY < 0)
				{
					for (let i = 0; i < setRowPositionsY.length; i++) {
						let transitivePositionY = setRowPositionsY[i];
						if (i === currentPositionRow)
						{
							transitivePositionY = setRowPositionsY[i] - (setRowHeights[i - 1] / 2);
						}
						if (newDraggableRowPositionY >= transitivePositionY)
						{
							newPositionRow = i;
						}
					}
				}
				if (draggableRowOffsetY === 0)
				{
					newPositionRow = currentPositionRow;
				}
				if (draggableRowOffsetY > 0)
				{
					for (let i = 0; i < setRowPositionsY.length; i++) {
						let transitivePositionY = setRowPositionsY[i] + (setRowHeights[i] / 2);
						if (i === currentPositionRow)
						{
							transitivePositionY = setRowPositionsY[i];
						}
						if (newDraggableRowPositionBottomY >= transitivePositionY)
						{
							newPositionRow = i;
						}
					}
				}
				//draggable row can only be in the 1 position, 0 position for technical row
				if (newPositionRow === 0)
				{
					newPositionRow++;
				}
				//need to move
				if (currentPositionRow !== newPositionRow)
				{
					let referenceNode = null;
					let referenceNodeNext = null;
					if (rows[newPositionRow])
					{
						referenceNode = rows[newPositionRow];
						referenceNodeNext = referenceNode.nextSibling;
						while(referenceNodeNext && referenceNodeNext.nodeType !== 1) {
							referenceNodeNext = referenceNodeNext.nextSibling;
						}
					}
					if (currentPositionRow > newPositionRow)
					{
						tableEditor.tBody.insertBefore(rows[currentPositionRow], referenceNode);
					}
					if (currentPositionRow < newPositionRow)
					{
						tableEditor.tBody.insertBefore(rows[currentPositionRow], referenceNodeNext);
					}
				}
				tableEditor.tBody.classList.remove("landing-table-draggable");
				BX.Landing.Block.Node.Text.currentNode.onChange(true);
			})
	}

	dragAndDropCols(tableEditor)
	{
		this.draggableCols = new Draggable({
			container: tableEditor.tBody,
			draggable: '.landing-table-div-col-dnd',
			type: Draggable.HEADLESS,
		});

		let currentPositionCol;
		let newPositionCol = 0;
		let draggableColOffsetX;
		let draggableColOffsetY;
		let setColCells = [];
		let setColPositionsX;
		let setColWidths;
		let setRows;
		let tablePositionLeft;
		let currentPositionColX;
		let setColCellsStyles;
		let draggableCol;

		this.draggableCols
			.subscribe('start', (event) => {
				tablePositionLeft = tableEditor.tBody.getBoundingClientRect().left;
				setColPositionsX = [];
				setColWidths = [];
				setColCellsStyles = [];
				draggableColOffsetX = 0;
				draggableColOffsetY = 0;
				currentPositionCol = event.getData().originalSource.parentNode.cellIndex;
				if (currentPositionCol)
				{
					setColCells = [...tableEditor.tBody.querySelectorAll('.landing-table-tr')].map((row) => {
						return row.children[currentPositionCol];
					});
					setRows = tableEditor.tBody.querySelectorAll('.landing-table-tr');
					let thListOfFirstRow = setRows[0].childNodes;
					thListOfFirstRow.forEach(function(thOfFirstRow) {
						if (thOfFirstRow.nodeType === 1)
						{
							setColPositionsX.push(thOfFirstRow.getBoundingClientRect().x);
							setColWidths.push(thOfFirstRow.getBoundingClientRect().width);
						}
					})
				}
				currentPositionColX = setColCells[0].getBoundingClientRect().x;
				draggableCol = document.createElement('div');
				setColCells.forEach(function(cell) {
					setColCellsStyles.push(cell.getAttribute('style'));
					draggableCol.append(cell.cloneNode(true));
					draggableCol.lastChild.style.borderRadius = getComputedStyle(cell).borderRadius;
					draggableCol.lastChild.style.height = cell.getBoundingClientRect().height + 'px';
					draggableCol.lastChild.style.width = cell.getBoundingClientRect().width + 'px';
				})
				draggableCol.hidden = true;
				draggableCol.classList.add('landing-table-col-draggable');
				tableEditor.tBody.append(draggableCol);
			})
			.subscribe('move', (event) => {
				tableEditor.tBody.classList.add("landing-table-draggable");
				setColCells.forEach((cell) => {
					cell.classList.add('landing-table-col-taken');
				});
				draggableColOffsetX = event.getData().offsetX;
				draggableColOffsetY = event.getData().offsetY;
				draggableCol.hidden = false;
				draggableCol.style.position = `absolute`;
				draggableCol.style.left = currentPositionColX - tablePositionLeft + draggableColOffsetX + 'px';
				draggableCol.style.top = 0 + 'px';
				if (draggableColOffsetX < 0)
				{
					draggableCol.style.transform = 'rotate(-1deg)';
				}
				if (draggableColOffsetX > 0)
				{
					draggableCol.style.transform = 'rotate(1deg)';
				}
			})
			.subscribe('end', () => {
				draggableCol.remove();
				setColCells.forEach((cell) => {
					cell.hidden = false;
				});
				if (currentPositionCol)
				{
					let newDraggableColPositionX = setColPositionsX[currentPositionCol] + draggableColOffsetX;
					let newDraggableColPositionRightX = setColPositionsX[currentPositionCol] + draggableColOffsetX + setColCells[0].getBoundingClientRect().width;
					let i = 0;
					setColCells.forEach((cell) => {
						cell.style = setColCellsStyles[i];
						cell.classList.remove('landing-table-col-taken');
						i++;
					});
					if (draggableColOffsetX < 0)
					{
						for (let i = 0; i < setColPositionsX.length; i++) {
							let transitivePositionX = setColPositionsX[i];
							if (i > 0)
							{
								transitivePositionX = setColPositionsX[i] - (setColWidths[i - 1] / 2);
							}
							if (newDraggableColPositionX > transitivePositionX)
							{
								newPositionCol = i;
							}
						}
					}
					if (draggableColOffsetX === 0)
					{
						newPositionCol = currentPositionCol;
					}
					if (draggableColOffsetX > 0)
					{
						for (let i = 0; i < setColPositionsX.length; i++) {
							let transitivePositionX = setColPositionsX[i] + (setColWidths[i] / 2);
							if (i === currentPositionCol)
							{
								transitivePositionX = setColPositionsX[i];
							}
							if (newDraggableColPositionRightX > transitivePositionX)
							{
								newPositionCol = i;
							}
						}
					}
					//draggable col can only be in the 1 position, 0 position for technical
					if (newPositionCol === 0)
					{
						newPositionCol++;
					}
					if (currentPositionCol !== newPositionCol)
					{
						setRows.forEach(function(row) {
							let childCells = [];
							row.childNodes.forEach(function(th) {
								if (th.nodeType === 1)
								{
									childCells.push(th);
								}
							})
							let referenceNode = null;
							let referenceNodeNext = null;
							if (childCells[newPositionCol])
							{
								referenceNode = childCells[newPositionCol];
								referenceNodeNext = referenceNode.nextSibling;
								while(referenceNodeNext && referenceNodeNext.nodeType !== 1) {
									referenceNodeNext = referenceNodeNext.nextSibling;
								}
							}
							if (currentPositionCol > newPositionCol)
							{
								row.insertBefore(childCells[currentPositionCol], referenceNode);
							}
							if (currentPositionCol < newPositionCol)
							{
								row.insertBefore(childCells[currentPositionCol], referenceNodeNext);
							}
						})
					}
					tableEditor.tBody.classList.remove("landing-table-draggable");
					BX.Landing.Block.Node.Text.currentNode.onChange(true);
				}
			});
	}

	resizeColumn(tableEditor)
	{
		let tbody = this.tBody;
		this.resizeElement = new Draggable({
			container: tbody,
			draggable: '.landing-table-col-resize',
			type: Draggable.HEADLESS,
		});

		let thWidth;
		let setTh;

		this.resizeElement
			.subscribe('start', (event) => {
				setTh = [];
				const th = event.getData().draggable.parentNode;
				thWidth = th.getBoundingClientRect().width;
				const currentPosition = th.cellIndex;
				const setTr = tbody.querySelectorAll('.landing-table-tr');
				setTr.forEach(function(tr) {
					setTh.push(tr.children[currentPosition]);
				})
			})
			.subscribe('move', (event) => {
				const offsetX = event.getData().offsetX;
				const thNewWidth = thWidth + offsetX;
				setTh.forEach(function(th) {
					BX.Dom.style(th, 'width', `${thNewWidth}px`);
				})
			})
			.subscribe('end', () => {
				let tBodyWidth = tbody.getBoundingClientRect().width;
				let tableContainerWidth = tbody.parentElement.parentElement.getBoundingClientRect().width;
				if (tableContainerWidth > tBodyWidth)
				{
					tbody.parentElement.parentElement.classList.add('landing-table-scroll-hidden');
				}
				else
				{
					tbody.parentElement.parentElement.classList.remove('landing-table-scroll-hidden');
				}
				tableEditor.buildLines(tableEditor);
				BX.Landing.Block.Node.Text.currentNode.onChange(true);
			})
	}

	enableEditCells(table)
	{
		let thContentList = table.querySelectorAll('.landing-table-td');
		thContentList.forEach(function(td) {
			td.setAttribute('contenteditable', 'true');
		})
	}

	removeClasses(element, className)
	{
		let setElements = element.querySelectorAll('.' + className)
		setElements.forEach(function(element){
			element.classList.remove(className);
		})
	}

	onCopyTable(tableEditor)
	{
		BX.Event.EventEmitter.subscribe('BX.Landing.TableEditor:onCopyTable', ()=> {
			tableEditor.unselect(tableEditor);
			BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
		});
	}

	onShowPopupMenu(tableEditor)
	{
		BX.Event.EventEmitter.subscribe('BX.Landing.PopupMenuWindow:onShow', ()=> {
			tableEditor.unselect(tableEditor);
			BX.Landing.UI.Panel.EditorPanel.getInstance().hide();
		});
	}

	onDeleteElementTable(tableEditor)
	{
		BX.Event.EventEmitter.subscribe('BX.Landing.TableEditor:onDeleteElementTable', ()=> {
			tableEditor.buildLines(tableEditor);
		});
	}
}