import { EventEmitter, BaseEvent } from 'main.core.events';
import { AccessRights } from 'socialnetwork.collab.access-rights';

import { CreateChatExternalSection } from 'im.v2.component.content.chat-forms.elements';

import type { JsonObject } from 'main.core';

export type AccessRightsFormResult = {
	moderators: number[],
	ownerId: number,
	options: JsonObject,
	permissions: JsonObject
};

// @vue/component
export const RightsSection = {
	name: 'RightsSection',
	components: { CreateChatExternalSection },
	props:
	{
		collabId: {
			type: Number,
			default: 0,
		},
	},
	emits: ['change'],
	data(): JsonObject
	{
		return {
			formResult: null,
		};
	},
	methods:
	{
		async onClick()
		{
			const sliderParams = {};
			if (this.collabId > 0)
			{
				sliderParams.collabId = this.collabId;
			}

			if (this.formResult)
			{
				sliderParams.formData = this.formResult;
			}
			this.form = await AccessRights.openForm(sliderParams);
			this.bindEvents();
		},
		bindEvents()
		{
			EventEmitter.subscribe(this.form, 'save', this.onSave);
			EventEmitter.subscribe(this.form, 'cancel', this.onCancel);
		},
		unbindEvents()
		{
			EventEmitter.unsubscribe(this.form, 'save', this.onSave);
			EventEmitter.unsubscribe(this.form, 'cancel', this.onCancel);
		},
		onSave(event: BaseEvent<AccessRightsFormResult>)
		{
			this.formResult = event.getData();
			this.$emit('change', this.formResult);
			this.unbindEvents();
		},
		onCancel()
		{
			this.unbindEvents();
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		},
	},
	template: `
		<CreateChatExternalSection
			:title="loc('IM_CREATE_CHAT_RIGHTS_SECTION')"
			@click="onClick"
			name="rights"
		/>
	`,
};
