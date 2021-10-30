import { Dom, Event } from 'main.core';
import { LayoutUI } from './ui/layout';

import './layout.css';

type LayoutParamsType = {
	result: HTMLElement
};

export class Layout
{
	result: HTMLElement;
	wrapper: HTMLElement;
	container: HTMLElement;
	colMenu: HTMLElement;
	currentSize: string = 'md';
	// @todo: all attrs too
	gridMatrix = [];

	constructor(container: HTMLElement, params: LayoutParamsType)
	{
		this.container = container;
		this.colMenu = LayoutUI.getHoverColMenu();

		if (params)
		{
			this.result = params.result || null;
		}

		this.onChangeView = this.onChangeView.bind(this);
		this.onNewRowClick = this.onNewRowClick.bind(this);
		this.onRemoveRowClick = this.onRemoveRowClick.bind(this);
		this.onIncColClick = this.onIncColClick.bind(this);
		this.onNewColClick = this.onNewColClick.bind(this);

		Event.bind(this.container, 'mouseover', (e) => {
			this.onMouseOver(e);
		});
		Event.bind(this.container, 'mouseout', (e) => {
			this.onMouseOut(e);
		});
		Event.bind(this.colMenu, 'click', (e) => {
			this.onColMenuClick(e);
		});

		this.loadMatrix();
		this.drawMatrix();
	}

	getResult(): HTMLDivElement
	{
		return LayoutUI.getResult(this.gridMatrix);
	}

	getColsInRow(rowNumber: number): Array<number>
	{
		const cols = [];

		if (this.gridMatrix[rowNumber])
		{
			this.gridMatrix[rowNumber].cols.map(colItem => {
				cols.push(colItem.sizes[this.currentSize] ? colItem.sizes[this.currentSize] : 0);
			});
		}

		return cols;
	}

	getSumInRow(rowNumber: number): number
	{
		return this.getColsInRow(rowNumber).reduce((sum, x) => sum + x);
	}

	fillZero(rowNumber: number)
	{
		const cols = this.getColsInRow(rowNumber);
		if (cols[0] <= 0)
		{
			const rowLength = this.gridMatrix[rowNumber]['cols'].length;
			const newCol = parseInt(12 / rowLength);
			this.gridMatrix[rowNumber]['cols'].map(colItem => {
				colItem.sizes[this.currentSize] = newCol;
			});
		}
	}

	querySelectorAll(node, regex, attribute) {
		const output = [];
		for (let element of node.querySelectorAll(`[${attribute}]`))
		{
			if (regex.test(element.getAttribute(attribute)))
			{
				output.push(element);
			}
		}
		return output;
	}

	loadMatrix()
	{
		// @todo: all attrs too
		this.gridMatrix = [];
		if (this.result)
		{
			[...this.result.querySelectorAll('.row')].map(row => {
				const cols = [];
				[...this.querySelectorAll(row, /col[a-z-]*[\d]+/, 'class')].map(col => {
					const newCol = {sizes: {}};
					const classes = col.getAttribute('class');
					const found1 = classes.match(/col-([a-z]+)-([\d]+)/g);
					const found2 = classes.match(/col-([\d]+)/g);
					if (found1)
					{
						found1.map(f => {
							newCol.sizes[f.split('-')[1]] = parseInt(f.split('-')[2]);
						});
					}
					if (found2)
					{
						found2.map(f => {
							newCol.sizes['xs'] = parseInt(f.split('-')[1]);
						});
					}
					if (found1 || found2)
					{
						newCol['content'] = col.innerHTML;
						newCol['classes'] = col.getAttribute('class').replace(/col[a-z-]*[\d]+/g, '');
						cols.push(newCol);
					}
				});
				this.gridMatrix.push({
					classes: row.getAttribute('class') || null,
					cols
				});
			});
			console.log(this.gridMatrix);
		}
	}

	drawMatrix()
	{
		Dom.clean(this.container);
		this.container.appendChild(this.colMenu);

		const rowsNumber = this.gridMatrix.length;

		this.container.appendChild(LayoutUI.getModeSelector(this.currentSize, this.onChangeView));

		Object.keys(this.gridMatrix).map(rowNumber => {
			const wrapper = LayoutUI.getWrapper();
			const cols = this.getColsInRow(rowNumber);

			wrapper.appendChild(LayoutUI.getBackground());
			wrapper.appendChild(LayoutUI.getWorkGrid({
				rowNumber,
				rowsNumber,
				cols,
				onNewRowClick: this.onNewRowClick,
				onRemoveRowClick: this.onRemoveRowClick,
				onIncColClick: this.onIncColClick,
				onNewColClick: this.onNewColClick
			}));
			this.container.appendChild(wrapper);
		});

		if (this.result)
		{
			Dom.clean(this.result);
			this.result.appendChild(this.getResult());
		}
	}

	onChangeView(size: string)
	{
		this.currentSize = size;
		this.drawMatrix();
	}

	onRemoveRowClick(rowNumber: number)
	{
		this.gridMatrix = this.gridMatrix.filter((item, number) => number !== rowNumber);
		this.drawMatrix();
	}

	onNewRowClick(rowNumber: number)
	{
		if (this.gridMatrix[rowNumber])
		{
			const sizes = {};
			sizes[this.currentSize] = 1;
			const newRow = {
				classes: null,
				cols: [{sizes}]
			}
			this.gridMatrix.splice(rowNumber + 1, 0, newRow);
		}
		this.drawMatrix();
	}

	onIncColClick(rowNumber: number, colNumber: number)
	{
		this.fillZero(rowNumber);
		if (this.getSumInRow(rowNumber) < 12)
		{
			this.gridMatrix[rowNumber]['cols'][colNumber]['sizes'][this.currentSize]++;
		}
		this.drawMatrix();
	}

	onDecColClick(rowNumber: number, colNumber: number)
	{
		this.fillZero(rowNumber);
		if (this.gridMatrix[rowNumber])
		{
			if (this.gridMatrix[rowNumber]['cols'][colNumber]['sizes'][this.currentSize] > 1) {
				this.gridMatrix[rowNumber]['cols'][colNumber]['sizes'][this.currentSize]--;
			} else {
				this.onRemoveColClick(rowNumber, colNumber);
				return;
			}
		}
		this.drawMatrix();
	}

	onNewColClick(rowNumber: number)
	{
		this.fillZero(rowNumber);
		if (this.gridMatrix[rowNumber])
		{
			const sizes = {};
			sizes[this.currentSize] = 1;
			this.gridMatrix[rowNumber]['cols'].push({sizes});
		}
		this.drawMatrix();
	}

	onRemoveColClick(rowNumber: number, colNumber: number)
	{
		this.fillZero(rowNumber);
		if (this.gridMatrix[rowNumber])
		{
			this.gridMatrix[rowNumber]['cols'] = this.gridMatrix[rowNumber]['cols'].filter(
				(item, number) => number !== colNumber
			);
		}
		this.drawMatrix();
	}

	showColMenu()
	{
		this.colMenu.style.display = 'block';
	}

	hideColMenu()
	{
		this.colMenu.style.display = 'none';
	}

	onMouseOver(event)
	{
		let element = null;

		event.path.map(el => {
			if (!(el && el.nodeType === Node.ELEMENT_NODE))
			{
				return;
			}
			if (el.getAttribute('data-ready'))
			{
				element = el;
			}
		});

		if (element)
		{
			const clientRect = event.target.getBoundingClientRect();
			this.showColMenu();
			this.colMenu.setAttribute('data-rowNumber', element.getAttribute('data-rowNumber'))
			this.colMenu.setAttribute('data-colNumber', element.getAttribute('data-colNumber'))
			Dom.style(
				this.colMenu,
				{
					top: clientRect.top + window.scrollY + 'px',
					left: clientRect.left + clientRect.width + window.scrollX - 20 + 'px'
				}
			);
		}
	}

	onMouseOut(event)
	{
		if (!event.target.getAttribute('data-ready'))
		{
			this.hideColMenu();
		}
	}

	onColMenuClick(event)
	{
		const command = event.target.getAttribute('data-command');
		if (command === 'decrease') {
			this.onDecColClick(
				parseInt(event.target.parentNode.getAttribute('data-rowNumber')),
				parseInt(event.target.parentNode.getAttribute('data-colNumber'))
			);
		} else {
			this.onRemoveColClick(
				parseInt(event.target.parentNode.getAttribute('data-rowNumber')),
				parseInt(event.target.parentNode.getAttribute('data-colNumber'))
			);
		}
		this.hideColMenu();
	}
}