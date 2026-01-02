import { BaseField } from './base-field';
import { Dom, Tag, Text } from 'main.core';
import type { User } from './component/types';

export class FullNameField extends BaseField
{
	render(params: User): void
	{
		const fullNameContainer = Tag.render`
			<div class="mailbox-grid_full-name-container">${this.#getFullNameLink(params.name, params.pathToProfile)}</div>
		`;

		if (params.position !== '')
		{
			Dom.append(this.#getPositionLabelContainer(Text.encode(params.position)), fullNameContainer);
		}

		this.appendToFieldNode(fullNameContainer);
	}

	#getFullNameLink(fullName: string, profileLink: string): HTMLElement
	{
		return Tag.render`
			<a class="mailbox-grid_full-name-label" href="${profileLink}">
				${Text.encode(fullName)}
			</a>
		`;
	}

	#getPositionLabelContainer(position: string): HTMLElement
	{
		return Tag.render`
			<div class="mailbox-grid_position-label">
				${Text.encode(position)}
			</div>
		`;
	}
}
