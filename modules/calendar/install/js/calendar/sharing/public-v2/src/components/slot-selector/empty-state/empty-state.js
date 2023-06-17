import {Loc, Tag} from "main.core";
import Base from '../base';

type EmptyStateOptions = {
	isHiddenOnStart: boolean,
};

export default class EmptyState extends Base
{
	#layout;

	constructor(options)
	{
		super({isHiddenOnStart: options.isHiddenOnStart});
		this.#layout = {
			content: null,
		}

		this.#bindEvents();
	}

	#bindEvents()
	{

	}

	getType()
	{
		return 'empty-state';
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
				<div class="calendar-pub__slots-empty">
					<div class="calendar-pub__slots-empty_title">${Loc.getMessage('CALENDAR_SHARING_SLOTS_EMPTY')}</div>
					<div class="calendar-pub__slots-empty_info">${Loc.getMessage('CALENDAR_SHARING_SLOTS_EMPTY_INFO')}</div>
				</div>
			`;
		}

		return this.#layout.content;
	}
}