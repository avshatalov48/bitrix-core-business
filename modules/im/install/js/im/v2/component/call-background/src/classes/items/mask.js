import {Loc} from 'main.core';

import type {MaskRestResult} from '../../types/rest';

export class Mask
{
	id: string = '';
	active: boolean = true;
	mask: string = '';
	background: string = '';
	preview: string = '';
	title: string = '';

	isLoading: boolean = false;

	constructor(params)
	{
		Object.assign(this, params);
	}

	isEmpty()
	{
		return this.id === '';
	}

	static createEmpty()
	{
		return new Mask({
			active: true,
			id: '',
			mask: '',
			preview: '',
			background: '',
			title: Loc.getMessage('BX_IM_CALL_BG_NO_MASK_TITLE')
		});
	}

	static createFromRest(rawMask: MaskRestResult)
	{
		const {active, id, mask, background, preview, title} = rawMask;

		return new Mask({
			active,
			id,
			mask,
			preview,
			background,
			title
		});
	}
}