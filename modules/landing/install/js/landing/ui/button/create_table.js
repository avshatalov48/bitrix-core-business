;(function() {
	"use strict";

	BX.namespace("BX.Landing.UI.Button");

	BX.Landing.UI.Button.CreateTable = function(id, options)
	{
		BX.Landing.UI.Button.EditorAction.apply(this, arguments);
		this.editPanel = null;
		this.options = options;
		this.id = id;
	};

	BX.Landing.UI.Button.CreateTable.prototype = {
		constructor: BX.Landing.UI.Button.CreateTable,
		__proto__: BX.Landing.UI.Button.EditorAction.prototype,

		onClick: function(event)
		{
			event.preventDefault();
			event.stopPropagation();
			var defaultColorTextRgb = 'rgb(51, 51, 51)';
			var defaultColorTextHex = '#333333';
			var divTableContainer = this.contextDocument.createElement('div');
			divTableContainer.classList.add('landing-table-container');
			var tableElement = this.contextDocument.createElement('table');
			tableElement.classList.add('landing-table', 'landing-table-style-1');
			tableElement.setAttribute('text-color', defaultColorTextHex);
			var trElementFirst = this.contextDocument.createElement('tr');
			trElementFirst.classList.add('landing-table-tr');
			var trElement = this.contextDocument.createElement('tr');
			trElement.classList.add('landing-table-tr');
			var thElementDnd = this.contextDocument.createElement('th');
			thElementDnd.classList.add('landing-table-th', 'landing-table-row-dnd');
			var divAddRowHere = this.contextDocument.createElement('div');
			divAddRowHere.classList.add('landing-table-row-add');
			var divLineX = this.contextDocument.createElement('div');
			divLineX.classList.add('landing-table-row-add-line');
			var divRowDnd = this.contextDocument.createElement('div');
			divRowDnd.classList.add('landing-table-div-row-dnd');
			divAddRowHere.appendChild(divLineX);
			var tdElement = this.contextDocument.createElement('td');
			tdElement.classList.add('landing-table-th', 'landing-table-td');
			var width = this.getCellWidth();
			tdElement.style.width = width + 'px';
			tdElement.style.color = defaultColorTextRgb;
			var thDndElement = this.contextDocument.createElement('th');
			thDndElement.classList.add('landing-table-th', 'landing-table-col-dnd');
			thDndElement.style.width = width + 'px';
			var divColumnDnd = this.contextDocument.createElement('div');
			divColumnDnd.classList.add('landing-table-div-col-dnd');
			var divColumnResize = this.contextDocument.createElement('div');
			divColumnResize.classList.add('landing-table-col-resize');
			var divAddColHere = this.contextDocument.createElement('div');
			divAddColHere.classList.add('landing-table-col-add');
			var divLineY = this.contextDocument.createElement('div');
			divLineY.classList.add('landing-table-col-add-line');
			divAddColHere.appendChild(divLineY);
			var thElementFirst = this.contextDocument.createElement('th');
			thElementFirst.classList.add('landing-table-th', 'landing-table-th-select-all');
			thElementFirst.style.width = '16px';
			var divTechIconElement = this.contextDocument.createElement('div');
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
			var node = this.contextDocument.createElement('div');
			divTableContainer.id = 'new-table';
			node.appendChild(divTableContainer);
			this.contextDocument.execCommand('insertHTML', null, node.innerHTML);
			var newTable = this.contextDocument.getElementById('new-table');
			var thTech = newTable.querySelector('.landing-table-th-select-all');
			if (thTech.firstChild)
			{
				thTech.firstChild.remove();
			}
			thTech.appendChild(divTechIconElement.cloneNode(true));
			var setThDnd = newTable.querySelectorAll('.landing-table-col-dnd');
			setThDnd.forEach(function(thDnd) {
				if (thDnd.firstChild)
				{
					thDnd.firstChild.remove();
				}
				thDnd.appendChild(divColumnDnd.cloneNode(true));
				thDnd.appendChild(divColumnResize.cloneNode(true));
				thDnd.appendChild(divAddColHere.cloneNode(true));
			})
			var setTrDnd = newTable.querySelectorAll('.landing-table-row-dnd');
			setTrDnd.forEach(function(trDnd) {
				if (trDnd.firstChild)
				{
					trDnd.firstChild.remove();
				}
				trDnd.appendChild(divAddRowHere.cloneNode(true));
				trDnd.appendChild(divRowDnd.cloneNode(true));
			})
			newTable.removeAttribute('id');
			BX.Landing.Block.Node.Text.currentNode.onChange(true);
		},

		getCellWidth: function()
		{
			var STANDART_CELL_WIDTH = 250;
			var BRAKEPOINT_DINAMIC_CELL = 1000;
			var TECHNIC_WIDTH = 57;
			var DEFAULT_AMOUNT_CELL = 4;
			var cellWidth = STANDART_CELL_WIDTH;
			var textNodeWidth = BX.Landing.Block.Node.Text.currentNode.node.getBoundingClientRect().width;
			if (textNodeWidth < BRAKEPOINT_DINAMIC_CELL)
			{
				cellWidth = Math.floor((textNodeWidth - TECHNIC_WIDTH) / DEFAULT_AMOUNT_CELL);
			}
			return cellWidth;
		}
	};
})();