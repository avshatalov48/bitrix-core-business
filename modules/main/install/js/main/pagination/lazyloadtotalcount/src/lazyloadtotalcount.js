import { Tag, Loc, Dom } from 'main.core';
import { EventEmitter } from 'main.core.events';
import './style.css';

export class Lazyloadtotalcount
{
	register(containerNode)
	{
		const container = containerNode ?? document.body;
		container.querySelectorAll('lazy-load-total-count:not([data-registered])').forEach((node) => {
			const gridId = node.getAttribute('grid-id');
			Dom.append(this.getCounterLabel(), node);
			Dom.append(this.getCounterContainer(gridId), node);

			EventEmitter.subscribe('Grid::updated', (event) => {
				const grid = event.compatData[0];
				if (grid.getId() === gridId)
				{
					this.register(grid.getContainer());
				}
			});

			node.dataset.registered = true;
		});
	}

	getCounterLabel(): HTMLElement
	{
		return Tag.render`
			<span class="main-pagination-lazyload-count_label">
				${Loc.getMessage('MAIN_PAGE_NAVIGATION_TOTAL_COUNTER_AMOUNT')}: 
			</span>
		`;
	}

	getCounterContainer(gridId: string): HTMLElement
	{
		const counter = Tag.render`<span class="main-pagination-lazyload-count_container"></span>`;
		Dom.append(
			Tag.render`
				<a class="main-pagination-lazyload-count_counter" onclick="${this.handleCounterClick.bind(this, gridId, counter)}">
					${Loc.getMessage('MAIN_PAGE_NAVIGATION_TOTAL_COUNTER_SHOW_LINK')}
				</a>
			`,
			counter,
		);

		return counter;
	}

	handleCounterClick(gridId: string, counter: HTMLElement)
	{
		Dom.clean(counter);
		Dom.append(
			Tag.render`
				<svg class="main-pagination-lazyload-count_loader" viewBox="25 25 50 50">
					<circle class="main-pagination-lazyload-count_loader-path" r="20" cx="50" cy="50" stroke-width="1" stroke-miterlimit="10" fill="none"></circle>
				</svg>
			`,
			counter,
		);

		const grid = BX.Main.gridManager.getById(gridId)?.instance;

		if (grid)
		{
			grid.getData().request('', null, null, 'get_total_rows_count', (response) => {
				const res = JSON.parse(response);
				Dom.clean(counter);
				Dom.append(
					Tag.render`<span class="main-pagination-lazyload-count_count">${res.payload.totalCount}</span>`,
					counter,
				);
			});
		}
	}
}
