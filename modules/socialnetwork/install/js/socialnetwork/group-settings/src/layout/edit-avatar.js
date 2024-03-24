import { Event, Tag } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { Editor as AvatarEditor } from 'ui.avatar-editor';
import { Logo, LogoData } from 'socialnetwork.logo';
import 'ui.icon-set.actions';

type Params = {
	logo: LogoData,
	canEdit: boolean,
	onChange: void,
}

export class EditAvatar
{
	#params: Params;
	#layout: {
		avatar: HTMLElement,
		avatarEdit: HTMLElement,
	};
	#avatarEditor: AvatarEditor;

	constructor(params: Params)
	{
		this.#layout = {};
		this.#params = params;
	}

	render(): HTMLElement
	{
		const logo = new Logo(this.#params.logo);

		const avatarNode = Tag.render`
			<div class="sn-group-settings__space-avatar sn-spaces__space-logo ${logo.getClass() ?? ''}">
				${this.#renderAvatarEdit()}
				${logo.render()}
			</div>
		`;

		this.#layout.avatar?.replaceWith(avatarNode);
		this.#layout.avatar = avatarNode;

		return this.#layout.avatar;
	}

	#renderAvatarEdit(): HTMLElement|string
	{
		if (this.#params.canEdit !== true)
		{
			return '';
		}

		this.#layout.avatarEdit = Tag.render`
			<div class="sn-group-settings__space-avatar-edit">
				<div class="ui-icon-set --pencil-40"></div>
			</div>
		`;

		Event.bind(this.#layout.avatarEdit, 'click', this.#chooseSpaceImage.bind(this));

		return this.#layout.avatarEdit;
	}

	#chooseSpaceImage()
	{
		this.#getAvatarEditor().show('file');
	}

	#getAvatarEditor(): AvatarEditor
	{
		if (!this.#avatarEditor)
		{
			this.#avatarEditor = new AvatarEditor({
				enableCamera: false,
			});
			this.#avatarEditor.subscribe('onApply', (event: BaseEvent) => {
				const [file] = event.getCompatData();
				const avatar = URL.createObjectURL(file);
				this.setAvatar(avatar);
				this.#params.onChange(file);
			});
		}

		return this.#avatarEditor;
	}

	setAvatar(avatar: string)
	{
		this.#params.logo = {
			id: avatar,
			type: 'image',
		};
		this.render();
	}
}