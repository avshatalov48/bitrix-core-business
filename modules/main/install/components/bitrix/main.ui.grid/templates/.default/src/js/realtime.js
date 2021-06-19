import {EventEmitter} from 'main.core.events';
import {Dom, Reflection, Type, Event, Tag, Text} from 'main.core';

type RealtimeOptions = {
	grid: BX.Main.grid,
};

type AddRowOptions = {
	id: number | string,
	actions?: Array<{[key: string]: any}>,
	columns?: {[key: string]: any},
	cellActions?: {[key: string]: any},
	append?: true,
	prepend?: true,
	insertBefore?: number | string,
	insertAfter?: number | string,
	animation?: boolean,
	counters?: {
		[colId: string]: {
			type: $Values<BX.Grid.Counters.Type>,
			color?: $Values<BX.Grid.Counters.Color>,
			value: string | number,
		},
	},
};

type ShowStubOptions = {
	content?: HTMLElement | string | {title?: string, description?: string},
};

/**
 * @memberOf BX.Grid
 */
export class Realtime extends EventEmitter
{
	constructor(options: RealtimeOptions)
	{
		super();
		this.setEventNamespace('BX.Grid.Realtime');
		this.options = {...options};
	}

	addRow(options: AddRowOptions)
	{
		const {grid} = this.options;
		const row = grid.getTemplateRow();
		row.makeCountable();
		grid.hideEmptyStub();

		if (Type.isNumber(options.id) || Type.isStringFilled(options.id))
		{
			row.setId(options.id);
		}
		else
		{
			throw new ReferenceError('id is not number or string');
		}

		if (Type.isArrayFilled(options.actions))
		{
			row.setActions(options.actions);
		}

		if (Type.isPlainObject(options.columns))
		{
			row.setCellsContent(options.columns);
		}

		if (Type.isPlainObject(options.cellActions))
		{
			row.setCellActions(options.cellActions);
		}

		if (Type.isPlainObject(options.counters))
		{
			const preparedCounters = Object.entries(options.counters).reduce((acc, [columnId, counter]) => {
				if (Type.isPlainObject(counter))
				{
					acc[columnId] = {
						...counter,
						animation: Text.toBoolean(counter.animation),
					};
				}

				return acc;
			}, {});

			row.setCounters(preparedCounters);
		}

		if (options.prepend === true)
		{
			row.prependTo(grid.getBody());
		}
		else if (options.append === true)
		{
			row.appendTo(grid.getBody());
		}
		else if (Type.isNumber(options.insertBefore) || Type.isStringFilled(options.insertBefore))
		{
			const targetRow = grid.getRows().getById(options.insertBefore);
			if (targetRow)
			{
				BX.Dom.insertBefore(row.getNode(), targetRow.getNode());
			}
		}
		else if (Type.isNumber(options.insertAfter) || Type.isStringFilled(options.insertAfter))
		{
			const targetRow = grid.getRows().getById(options.insertAfter);
			if (targetRow)
			{
				BX.Dom.insertAfter(row.getNode(), targetRow.getNode());
			}
		}
		else
		{
			throw new ReferenceError('prepend, append, insertBefore or insertAfter not filled');
		}

		row.show();

		if (options.animation !== false)
		{
			row.enableAbsolutePosition();

			const movedElements = grid.getRows().getSourceBodyChild().filter((currentRow) => {
				return currentRow.rowIndex > row.getIndex();
			});

			const fakeRowNode = document.createElement('tr');
			Dom.style(fakeRowNode, {
				height: '0px',
				transition: '200ms height linear',
			});
			Dom.append(fakeRowNode, grid.getBody());

			const offset = row.getHeight();
			Dom.style(fakeRowNode, 'height', `${offset}px`);
			movedElements.forEach((element) => {
				Dom.style(element, {
					transition: '200ms transform linear',
					transform: `translateY(${offset}px) translateZ(0)`,
				});
			});

			Dom.addClass(row.getNode(), 'main-ui-grid-show-new-row');

			Event.bind(row.getNode(), 'animationend', (event: AnimationEvent) => {
				if (event.animationName === 'showNewRow')
				{
					movedElements.forEach((element) => {
						Dom.style(element, {
							transition: null,
							transform: null,
						});
					});
					Dom.remove(fakeRowNode);
					row.disableAbsolutePosition();

					Dom.removeClass(row.getNode(), 'main-ui-grid-show-new-row');
				}
			});
		}

		grid.getRows().reset();
		grid.bindOnRowEvents();
		grid.updateCounterDisplayed();
		grid.updateCounterSelected();

		if (grid.getParam('ALLOW_ROWS_SORT'))
		{
			grid.rowsSortable.reinit();
		}

		if (grid.getParam('ALLOW_COLUMNS_SORT'))
		{
			grid.colsSortable.reinit();
		}
	}

	showStub(options: ShowStubOptions = {})
	{
		const tr = document.createElement('tr');
		Dom.addClass(tr, 'main-grid-row main-grid-row-empty main-grid-row-body');

		const td = document.createElement('td');
		Dom.addClass(td, 'main-grid-cell main-grid-cell-center');
		const colspan = this.options.grid.getRows().getHeadFirstChild().getCells().length;
		Dom.attr(td, 'colspan', colspan);

		const content = (() => {
			if (Type.isPlainObject(options.content))
			{
				const result = [];
				if (Type.isStringFilled(options.content.title))
				{
					result.push(
						Tag.render`
							<div class="main-grid-empty-block-title">
								${options.content.title}
							</div>
						`,
					);
				}

				if (Type.isStringFilled(options.content.description))
				{
					result.push(
						Tag.render`
							<div class="main-grid-empty-block-description">
								${options.content.description}
							</div>
						`,
					);
				}

				return result;
			}

			if (
				Type.isStringFilled(options.content)
				|| Type.isDomNode(options.content)
			)
			{
				return options.content;
			}

			return [
				Tag.render`<div class="main-grid-empty-image"></div>`,
				Tag.render`<div class="main-grid-empty-text">${this.options.grid.getParam('EMPTY_STUB_TEXT')}</div>`,
			];
		})();

		const container = Tag.render`
			<div class="main-grid-empty-block">
				<div class="main-grid-empty-inner">
					${content}
				</div>
			</div>
		`;

		Dom.append(container, td);
		Dom.append(td, tr);

		const oldStub = this.options.grid.getBody().querySelector('.main-grid-row-empty');
		if (oldStub)
		{
			Dom.remove(oldStub);
		}

		Dom.append(tr, this.options.grid.getBody());

		this.options.grid.getRows().getBodyChild().forEach((row) => {
			row.hide();
		});

		this.options.grid.adjustEmptyTable(this.options.grid.getRows().getSourceBodyChild());
	}
}

const namespace = Reflection.namespace('BX.Grid');
namespace.Realtime = Realtime;