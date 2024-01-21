(function() {
	'use strict';

	BX.namespace('BX.Landing.UI.Button');

	BX.Landing.UI.Button.CreateTable = function(id, options, textNode)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.editPanel = null;
		this.options = options;
		this.id = id;
		this.textNode = textNode;
	};

	BX.Landing.UI.Button.CreateTable.prototype = {
		constructor: BX.Landing.UI.Button.CreateTable,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick(event)
		{
			event.preventDefault();
			event.stopPropagation();
			const defaultColorTextRgb = 'rgb(51, 51, 51)';
			const defaultColorTextHex = '#333333';
			const divTableContainer = this.contextDocument.createElement('div');
			divTableContainer.classList.add('landing-table-container');
			const tableElement = this.contextDocument.createElement('table');
			tableElement.classList.add('landing-table', 'landing-table-style-1');
			tableElement.setAttribute('text-color', defaultColorTextHex);
			const trElementFirst = this.contextDocument.createElement('tr');
			trElementFirst.classList.add('landing-table-tr');
			const trElement = this.contextDocument.createElement('tr');
			trElement.classList.add('landing-table-tr');
			const thElementDnd = this.contextDocument.createElement('th');
			thElementDnd.classList.add('landing-table-th', 'landing-table-row-dnd');
			const divAddRowHere = this.contextDocument.createElement('div');
			divAddRowHere.classList.add('landing-table-row-add');
			const divLineX = this.contextDocument.createElement('div');
			divLineX.classList.add('landing-table-row-add-line');
			const divRowDnd = this.contextDocument.createElement('div');
			divRowDnd.classList.add('landing-table-div-row-dnd');
			divAddRowHere.appendChild(divLineX);
			const tdElement = this.contextDocument.createElement('td');
			tdElement.classList.add('landing-table-th', 'landing-table-td');
			const width = this.getCellWidth();
			tdElement.style.width = `${width}px`;
			tdElement.style.color = defaultColorTextRgb;
			const thDndElement = this.contextDocument.createElement('th');
			thDndElement.classList.add('landing-table-th', 'landing-table-col-dnd');
			thDndElement.style.width = `${width}px`;
			const divColumnDnd = this.contextDocument.createElement('div');
			divColumnDnd.classList.add('landing-table-div-col-dnd');
			const divColumnResize = this.contextDocument.createElement('div');
			divColumnResize.classList.add('landing-table-col-resize');
			const divAddColHere = this.contextDocument.createElement('div');
			divAddColHere.classList.add('landing-table-col-add');
			const divLineY = this.contextDocument.createElement('div');
			divLineY.classList.add('landing-table-col-add-line');
			divAddColHere.appendChild(divLineY);
			const thElementFirst = this.contextDocument.createElement('th');
			thElementFirst.classList.add('landing-table-th', 'landing-table-th-select-all');
			thElementFirst.style.width = '16px';
			const divTechIconElement = this.contextDocument.createElement('div');
			divTechIconElement.classList.add('th-tech-icon');
			trElementFirst.appendChild(thElementFirst.cloneNode(true));
			for (var i = 0; i <= 3; i++)
			{
				trElementFirst.appendChild(thDndElement.cloneNode(true));
			}
			trElement.appendChild(thElementDnd.cloneNode(true));
			for (var i = 0; i <= 3; i++)
			{
				trElement.appendChild(tdElement.cloneNode(true));
			}
			tableElement.appendChild(trElementFirst.cloneNode(true));
			for (var i = 0; i <= 3; i++)
			{
				tableElement.appendChild(trElement.cloneNode(true));
			}
			divTableContainer.appendChild(tableElement);
			const node = this.contextDocument.createElement('div');
			divTableContainer.id = 'new-table';
			node.appendChild(divTableContainer);
			this.contextDocument.execCommand('insertHTML', null, node.innerHTML);
			const newTable = this.contextDocument.getElementById('new-table');
			const thTech = newTable.querySelector('.landing-table-th-select-all');
			if (thTech.firstChild)
			{
				thTech.firstChild.remove();
			}
			thTech.appendChild(divTechIconElement.cloneNode(true));
			const setThDnd = newTable.querySelectorAll('.landing-table-col-dnd');
			setThDnd.forEach((thDnd) => {
				if (thDnd.firstChild)
				{
					thDnd.firstChild.remove();
				}
				thDnd.appendChild(divColumnDnd.cloneNode(true));
				thDnd.appendChild(divColumnResize.cloneNode(true));
				thDnd.appendChild(divAddColHere.cloneNode(true));
			});
			const setTrDnd = newTable.querySelectorAll('.landing-table-row-dnd');
			setTrDnd.forEach((trDnd) => {
				if (trDnd.firstChild)
				{
					trDnd.firstChild.remove();
				}
				trDnd.appendChild(divAddRowHere.cloneNode(true));
				trDnd.appendChild(divRowDnd.cloneNode(true));
			});
			newTable.removeAttribute('id');
			if (this.textNode)
			{
				this.textNode.onChange(true);
			}
		},

		getCellWidth()
		{
			const STANDART_CELL_WIDTH = 250;
			const BRAKEPOINT_DINAMIC_CELL = 1000;
			const TECHNIC_WIDTH = 57;
			const DEFAULT_AMOUNT_CELL = 4;

			let cellWidth = STANDART_CELL_WIDTH;
			if (BX.Landing.Node.Text.currentNode)
			{
				const textNodeWidth = BX.Landing.Node.Text.currentNode.node.getBoundingClientRect().width;
				if (textNodeWidth < BRAKEPOINT_DINAMIC_CELL)
				{
					cellWidth = Math.floor((textNodeWidth - TECHNIC_WIDTH) / DEFAULT_AMOUNT_CELL);
				}
			}

			return cellWidth;
		},
	};
})();
