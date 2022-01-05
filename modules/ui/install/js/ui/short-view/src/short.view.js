import {Dom, Tag, Type, Loc, Event} from 'main.core';
import {EventEmitter} from 'main.core.events';

type Params = {
	isShortView: 'Y' | 'N'
}

import './css/base.css';

export class ShortView extends EventEmitter
{
	constructor(params: Params)
	{
		super(params);

		this.setEventNamespace('BX.UI.ShortView');

		this.setShortView(params.isShortView);

		this.node = null;
	}

	renderTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('UI ShortView: HTMLElement not found');
		}

		Dom.append(this.render(), container);
	}

	render(): HTMLElement
	{
		const checked = (this.getShortView() === 'Y' ? 'checked' : '');

		this.node = Tag.render`
			<div class="tasks-scrum__switcher--container tasks-scrum__scope-switcher">
				<label class="tasks-scrum__switcher--label">
				<div class="tasks-scrum__switcher--label-text">
					${Loc.getMessage('UI_SHORT_VIEW_LABEL')}
				</div>
				<input type="checkbox" class="tasks-scrum__switcher--checkbox" ${checked}>
				<span class="tasks-scrum__switcher-cursor"></span>
				</label>
			</div>
		`;

		Event.bind(this.node, 'change', this.onChange.bind(this));

		return this.node;
	}

	setShortView(value: string)
	{
		this.shortView = (value === 'Y' ? 'Y' : 'N');
	}

	getShortView(): 'Y' | 'N'
	{
		return this.shortView;
	}

	onChange()
	{
		const checkboxNode = this.node.querySelector('input[type="checkbox"]');

		this.setShortView(checkboxNode.checked ? 'Y' : 'N');

		this.emit('change', this.getShortView());
	}
}