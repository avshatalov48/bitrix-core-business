import { Tag } from 'main.core';

type workGridOptions = {
	rowNumber: number,
	rowsNumber: number,
	cols: Array<number>,
	onNewRowClick: () => {},
	onRemoveRowClick: () => {},
	onIncColClick: () => {},
	onNewColClick: () => {}
};

export class LayoutUI
{

	static getWrapper(): HTMLDivElement
	{
		return Tag.render`<div class="landing-bootstrap-layout-wrapper"></div>`;
	}

	static getModeSelector(currentSize: string, onChangeView: () => {}): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-bootstrap-layout-switcher">
				${['md', 'sm', 'xs'].map(size => {
					return Tag.render`
						<span onclick="${() => onChangeView ? onChangeView(size) : {}}" class="${currentSize === size ? 'active' : ''}">${size}</span>
					`;
				})}
			</div>
		`;
	}

	static getBackground(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-bootstrap-layout-background container">
				<div class="row">
					${[...(new Array(12))].map(() => {
						return Tag.render`
							<div class="col grid-item">
								<div></div>
							</div>
						`;
					})}
				</div>
			</div>
		`;
	}

	static getHoverColMenu(): HTMLDivElement
	{
		return Tag.render`
			<div class="landing-bootstrap-layout-wrapper-col-menu">
				<div data-command="remove">x</div>
				<div data-command="decrease">&larr;</div>
			</div>
		`;
	}

	static getWorkGrid(options: workGridOptions): HTMLDivElement
	{
		let nums = 0;
		const rowNumber = parseInt(options.rowNumber);
		const {
			rowsNumber,
			cols,
			onNewRowClick,
			onRemoveRowClick,
			onIncColClick,
			onNewColClick
		} = options;

		return Tag.render`
			<div class="landing-bootstrap-layout-work container">
				<div class="row">
					${rowsNumber <= 1 ? '' :
					Tag.render`
						<div class="landing-bootstrap-layout-remove-row" onclick="${() => onRemoveRowClick ? onRemoveRowClick(rowNumber) : {}}">
							-
						</div>
					`}
					${Tag.render`
						<div class="landing-bootstrap-layout-add-row" onclick="${() => onNewRowClick ? onNewRowClick(rowNumber) : {}}">
							+
						</div>
					`}
					${cols.map((col, i) => {
						nums += col > 0 ? col : 1;
						return Tag.render`
							<div class="${col > 0 ? `col-${col}` : 'col'} grid-item" onclick="${() => onIncColClick ? onIncColClick(rowNumber, i) : {}}">
								<div data-ready="true" data-rowNumber="${rowNumber}" data-colNumber="${i}">${col}</div>
							</div>
						`;
					})}
					${nums >= 12 ? '' :
						Tag.render`
							<div class="col-1 grid-item grid-item-last" onclick="${() => onNewColClick ? onNewColClick(rowNumber) : {}}">
								<div></div>
							</div>
						`
					}
				</div>
			</div>
		`;
	}

	static getResult(matrix): HTMLDivElement
	{
		return Tag.render`
			<div class="container">
				${matrix.map(row => {
					return Tag.render`
						<div class="row ${row['classes'] ? row['classes'] : ''}">
							${row['cols'].map((col, i) => {
								const classes = col.classes ? col.classes.split(' ') : [];
								Object.keys(col.sizes).map(size => {
									const colValue = (size === 'xs') ? `col-${col.sizes[size]}` : `col-${size}-${col.sizes[size]}`;
									if (!classes.includes(colValue))
									{
										classes.push(colValue);
									}
								});
								return Tag.render`
									<div class="${classes.join(' ')}">
										${col.content ? col.content : '<div class="test-height"></div>'}
									</div>
								`;		
							})}
						</div>
					`;
				})}
			</div>
		`;
	}
}