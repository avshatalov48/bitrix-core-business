import { Event, Tag } from 'main.core';
import { BaseEvent } from 'main.core.events';
import { GroupPrivacy } from 'socialnetwork.group-privacy';
import 'ui.icon-set.actions';

type Params = {
	privacyCode: 'open' | 'closed' | 'secret',
	canEdit: boolean,
	onChange: void,
}

export class EditPrivacy
{
	#params: Params;
	#privacyPopup: GroupPrivacy;
	#layout: {
		privacy: HTMLElement,
		privacyText: HTMLElement,
	};

	constructor(params: Params)
	{
		this.#layout = {};
		this.#params = params;
		this.#privacyPopup = this.#createPrivacyPopup();
	}

	render(): HTMLElement
	{
		const canEdit = this.#params.canEdit === true;

		this.#layout.privacyText = Tag.render`
			<div class="sn-group-settings__privacy">
				${this.#privacyPopup.getLabel()}
			</div>
		`;

		this.#layout.privacy = Tag.render`
			<div class="sn-group-settings__privacy-container ${!canEdit ? '--readonly' : ''}">
				${this.#layout.privacyText}
				<div class="ui-icon-set --chevron-down"></div>
			</div>
		`;

		if (canEdit)
		{
			Event.bind(this.#layout.privacy, 'click', this.#showPrivacy.bind(this));
		}

		return this.#layout.privacy;
	}

	#createPrivacyPopup(): GroupPrivacy
	{
		const privacyPopup = new GroupPrivacy({
			privacyCode: this.#params.privacyCode,
		});

		privacyPopup.subscribe('changePrivacy', this.#changePrivacy.bind(this));

		return privacyPopup;
	}

	#showPrivacy(event)
	{
		this.#privacyPopup.show(event.target);
	}

	#changePrivacy(baseEvent: BaseEvent)
	{
		const privacyCode: 'open' | 'closed' | 'secret' = baseEvent.getData();

		this.setPrivacy(privacyCode);

		this.#params.onChange(privacyCode);
	}

	setPrivacy(privacyCode: 'open' | 'closed' | 'secret')
	{
		this.#params.privacyCode = privacyCode;
		this.#privacyPopup.setPrivacy(this.#params.privacyCode);
		this.#layout.privacyText.innerText = this.#privacyPopup.getLabel();
	}
}