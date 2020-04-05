import {CustomField} from "./custom.field";
import {Type} from "main.core";

export class SelectorField extends CustomField
{
	constructor(options)
	{
		super(options);

		options = {...{
			selectorId: "",
		}, ...options};

		this.selectorId = options.selectorId;

		this.selector = null;

		this.value = {};

		this.init();
	}

	init()
	{
		/* eslint-disable */
		this.selector = BX.UI.TileSelector.getById(this.selectorId);
		if (this.selector)
		{
			BX.addCustomEvent(this.selector, this.selector.events.search, this.onSearch.bind(this));
			BX.addCustomEvent(this.selector, this.selector.events.input, this.onInput.bind(this));
			BX.addCustomEvent(this.selector, this.selector.events.buttonSelect, this.buttonSelect.bind(this));
			BX.addCustomEvent(this.selector, this.selector.events.tileRemove, this.removeTile.bind(this));
			BX.addCustomEvent(this.selector, this.selector.events.tileClick, this.clickTile.bind(this));

			this.selector.getTiles().map((tile) => {
				this.setValue(tile.id, tile);
			});
		}
		/* eslint-enable */
	}

	setValue(key, value)
	{
		this.value[key] = value;
	}

	deleteValue(key)
	{
		delete this.value[key];
	}

	onSearch(inputValue)
	{
		if (inputValue)
		{
			this.selector.addTile(inputValue, [], inputValue);
			const tile = this.selector.getTile(inputValue);
			this.setValue(tile.id, tile);
		}
	}

	onInput(inputValue)
	{
		//todo later ajax search.tags.input
	}

	buttonSelect()
	{
		//todo if need
	}

	removeTile(tile)
	{
		this.deleteValue(tile.id);
	}

	clickTile(tile)
	{
		//todo if need
	}
}