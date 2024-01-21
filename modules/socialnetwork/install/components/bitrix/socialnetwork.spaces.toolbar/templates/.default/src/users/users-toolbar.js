import { Dom, Event, Tag, Type, Loc, Cache } from 'main.core';
import { UsersRouter } from './users-router';

type Params = {
	pathToInvite: string,
}

export class UsersToolbar
{
	#cache = new Cache.MemoryCache();

	#router: UsersRouter;

	constructor(params: Params)
	{
		this.#setParams(params);

		this.#initServices(params);
	}

	renderInviteBtnTo(container: HTMLElement)
	{
		if (!Type.isDomNode(container))
		{
			throw new Error('BX.Socialnetwork.Spaces.UsersToolbar: HTMLElement for add btn not found');
		}

		Dom.append(this.#renderInviteBtn(), container);
	}

	#setParams(params: Params)
	{
		this.#cache.set('params', params);
	}

	#getParam(param: string): any
	{
		return this.#cache.get('params')[param];
	}

	#initServices(params: Params)
	{
		this.#router = new UsersRouter({
			pathToInvite: params.pathToInvite,
		});
	}

	#renderInviteBtn(): HTMLElement
	{
		const node = Tag.render`
			<div
				data-id="spaces-users-invite-btn"
				class="ui-btn ui-btn-success ui-btn-round ui-btn-no-caps"
			>
				${Loc.getMessage('SN_SPACES_USERS_INVITE_BTN')}
			</div>
		`;

		Event.bind(node, 'click', this.#inviteClick.bind(this));

		return node;
	}

	#inviteClick()
	{
		this.#router.openInvite();
	}
}
