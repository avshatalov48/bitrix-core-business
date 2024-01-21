import { Loc, Tag } from 'main.core';
import Base from '../base';

type AccessDeniedOptions = {
	isHiddenOnStart: boolean,
};

export default class AccessDenied extends Base
{
	#layout;

	constructor(options: AccessDeniedOptions)
	{
		super({ isHiddenOnStart: options.isHiddenOnStart });
		this.#layout = {
			content: null,
		};

		this.#bindEvents();
	}

	#bindEvents()
	{}

	getType()
	{
		return 'access-denied';
	}

	getContent()
	{
		return this.#getNodeEmptyState();
	}

	#getNodeEmptyState(): HTMLElement
	{
		if (!this.#layout.content)
		{
			this.#layout.content = Tag.render`
				<div class="calendar-pub__slots-empty --icon-cross">
					<div class="calendar-pub__slots-empty_title">${Loc.getMessage('CALENDAR_SHARING_SLOTS_ACCESS_DENIED')}</div>
					<div class="calendar-pub__slots-empty_info">${Loc.getMessage('CALENDAR_SHARING_SLOTS_ACCESS_DENIED_INFO')}</div>
				</div>
			`;
		}

		return this.#layout.content;
	}
}
