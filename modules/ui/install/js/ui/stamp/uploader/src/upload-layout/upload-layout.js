import {Cache, Tag} from 'main.core';

import './css/style.css';

type UploadLayoutOptions = {
	children: Array<{getLayout: () => HTMLElement}>,
}

export default class UploadLayout
{
	cache = new Cache.MemoryCache();

	constructor(options: UploadLayoutOptions)
	{
		this.setOptions(options);
	}

	setOptions(options: UploadLayoutOptions)
	{
		this.cache.set('options', {...options});
	}

	getOptions(): UploadLayoutOptions
	{
		return this.cache.get('options', {});
	}

	getLayout(): HTMLDivElement
	{
		return this.cache.remember('layout', () => {
			return Tag.render`
				<div class="ui-stamp-uploader-upload-layout">
					${this.getOptions().children.map((item) => item.getLayout())}
				</div>
			`;
		});
	}
}