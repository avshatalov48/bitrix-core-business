import {Cache, Dom, Tag} from 'main.core';
import type Preset from '../preset/preset';

import 'ui.fonts.opensans';
import './css/preset-category.css';

type PresetCategoryOptions = {
	title: string,
	presets: Array<Preset>,
};

export default class PresetCategory
{
	constructor(options: PresetCategoryOptions)
	{
		this.options = {...options};
		this.cache = new Cache.MemoryCache();
	}

	setPresets(presets: Array<Preset>)
	{
		this.presets = presets;

		const listContainer = this.getListContainer();
		Dom.clean(listContainer);
		this.presets.forEach((preset) => {
			Dom.append(preset.getLayout(), listContainer);
		});
	}

	getListContainer(): HTMLDivElement
	{
		return this.cache.remember('listContainer', () => {
			return Tag.render`
				<div class="landing-ui-presets-category-list"></div>
			`;
		});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="landing-ui-presets-category">
					<div class="landing-ui-presets-category-title">${this.options.title}</div>
					${this.getListContainer()}
				</div>
			`;
		});
	}
}