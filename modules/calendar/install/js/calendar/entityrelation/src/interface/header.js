import { Tag, Loc, Event } from 'main.core';
import { Icon, Outline } from 'ui.icon-set.api.core';
import 'ui.icon-set.main';

const HELP_DESK_CODE = 26_423_530;

export class EntityRelationsHeader
{
	render(): HTMLElement
	{
		return Tag.render`
			<div class="calendar--relation-entities-header">
				<h6 class="calendar--relation-entities-title">
					${Loc.getMessage('CALENDAR_RELATIONS_TITLE')}
				</h6>
				<span class="calendar--relation-entities-help-desk-icon">
					${this.#getHelpDeskIcon()}
				</span>
			</div>
		`;
	}

	#getHelpDeskIcon(): HTMLElement
	{
		const helpDeskIcon = new Icon({
			icon: Outline.QUESTION,
			size: 16,
			color: 'rgba(167, 167, 167, 1)',
		}).render();

		Event.bind(helpDeskIcon, 'click', () => this.#showHelpDesk());

		return helpDeskIcon;
	}

	#showHelpDesk(): void
	{
		if (!top.BX.Helper)
		{
			return;
		}

		const params = {
			redirect: 'detail',
			code: HELP_DESK_CODE,
		};

		const queryString = Object.entries(params)
			.map(([key, value]) => `${key}=${value}`)
			.join('&');

		top.BX.Helper.show(queryString);
	}
}
