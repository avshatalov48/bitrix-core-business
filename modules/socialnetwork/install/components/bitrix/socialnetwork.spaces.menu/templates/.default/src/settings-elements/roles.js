import { Event, Loc, Tag } from 'main.core';
import { EventEmitter } from 'main.core.events';

type Params = {
	canEdit: boolean,
}

export class Roles extends EventEmitter
{
	#canEdit: boolean;

	constructor(params: Params)
	{
		super();

		this.setEventNamespace('BX.Socialnetwork.Spaces.Settings.Roles');

		this.#canEdit = params.canEdit === true;
	}

	render(): HTMLElement
	{
		const rolesId = 'spaces-settings-roles';

		const disabled = this.#canEdit ? '' : '--disabled';

		const node = Tag.render`
			<div
				data-id="${rolesId}"
				class="sn-spaces__popup-item --mini ${disabled}"
			>
				<div class="sn-spaces__popup-icon-round">
					<div
						class="ui-icon-set --crown-1"
						style="--ui-icon-set__icon-size: 22px;"
					></div>
				</div>
				<div class="sn-spaces__popup-icon-round-name">
					${Loc.getMessage('SN_SPACES_MENU_ROLES')}
				</div>
			</div>
		`;

		Event.bind(node, 'click', () => {
			if (this.#canEdit)
			{
				this.emit('click');
			}
		});

		return node;
	}
}
