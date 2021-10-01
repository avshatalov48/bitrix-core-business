import {Type} from 'main.core';
import {BaseEvent, EventEmitter} from 'main.core.events';

import {Loader} from './loader';
import {PageInstance} from './feed';

class Filter
{
	constructor()
	{
		this.filterId = '';
		this.filterApi = null;
	}

	init(params)
	{
		if (!Type.isPlainObject(params))
		{
			params = {};
		}

		if (
			Type.isStringFilled(params.filterId)
			&& !Type.isUndefined(BX.Main)
			&& !Type.isUndefined(BX.Main.filterManager)
		)
		{
			const filterManager = BX.Main.filterManager.getById(params.filterId);
			this.filterId = params.filterId;

			if(filterManager)
			{
				this.filterApi = filterManager.getApi();
			}
		}

		this.initEvents();
	}

	initEvents()
	{
		EventEmitter.subscribe('BX.Livefeed.Filter:beforeApply', (event: BaseEvent) =>
		{
			Loader.showRefreshFade();
		});

		EventEmitter.subscribe('BX.Livefeed.Filter:apply', (event: BaseEvent) =>
		{
			const [ filterValues, filterPromise, filterParams ] = event.getCompatData();

			if (typeof filterParams != 'undefined')
			{
				filterParams.autoResolve = false;
			}
			PageInstance.refresh({
				useBXMainFilter: 'Y',
			}, filterPromise);
		});

		EventEmitter.subscribe('BX.Livefeed.Filter:searchInput', (event: BaseEvent) =>
		{
			const [ searchString ] = event.getCompatData();

			if (Type.isStringFilled(searchString))
			{
				Loader.showRefreshFade();
			}
			else
			{
				Loader.hideRefreshFade();
			}
		});
	}

	initEventsCrm()
	{
		EventEmitter.subscribe('BX.Livefeed.Filter:searchInput', () => {
			PageInstance.refresh();
		});
	}

	clickTag(tagValue)
	{
		if (
			!Type.isStringFilled(tagValue)
			|| !this.filterApi
		)
		{
			return false;
		}

		this.filterApi.setFields({
			TAG: tagValue
		});
		this.filterApi.apply();

		if (
			Type.isStringFilled(this.filterId)
			&& !Type.isUndefined(BX.Main)
			&& !Type.isUndefined(BX.Main.filterManager)
		)
		{
			const filterContainer = document.getElementById(`${this.filterId}_filter_container`);
			if (
				filterContainer
				&& BX.Main.filterManager.getById(this.filterId)
				&& (
					BX.Main.filterManager.getById(this.filterId).getSearch().getSquares().length > 0
					|| BX.Main.filterManager.getById(this.filterId).getSearch().getSearchString().length > 0
				)
			)
			{
				const pagetitleContainer = filterContainer.closest('.pagetitle-wrap');
				if (pagetitleContainer)
				{
					pagetitleContainer.classList.add('pagetitle-wrap-filter-opened');
				}
			}
		}

		(new BX.easing({
			duration: 500,
			start: { scroll: window.pageYOffset },
			finish: { scroll: 0 },
			transition: BX.easing.makeEaseOut(BX.easing.transitions.quart),
			step: (state) => {
				window.scrollTo(0, state.scroll);
			},
			complete: () => {}
		})).animate();

		return true;
	}
}

export {
	Filter,
};