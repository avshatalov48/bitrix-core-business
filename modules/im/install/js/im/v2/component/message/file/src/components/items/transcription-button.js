import { BIcon, Outline as OutlineIcons } from 'ui.icon-set.api.vue';

import { Spinner, SpinnerSize } from 'im.v2.component.elements.loader';
import { Color, TranscriptionStatus } from 'im.v2.const';
import { MessageService } from 'im.v2.provider.service.message';
import { Feature, FeatureManager } from 'im.v2.lib.feature';
import { Analytics } from 'im.v2.lib.analytics';

import '../../css/items/transcription.css';

import type { ImModelTranscription } from 'im.v2.model';

// @vue/component
export const TranscriptionButtonItem = {
	name: 'TranscriptionButtonItem',
	components: { Spinner, BIcon },
	props: {
		file: {
			type: Object,
			required: true,
		},
		isOpened: {
			type: Boolean,
			required: true,
		},
	},
	emits: ['transcriptionToggle'],
	computed:
	{
		SpinnerSize: () => SpinnerSize,
		OutlineIcons: () => OutlineIcons,
		Color: () => Color,
		fileId(): boolean
		{
			return this.file.id;
		},
		chatId(): boolean
		{
			return this.file.chatId;
		},
		transcription(): ?ImModelTranscription
		{
			return this.$store.getters['files/getTranscription'](this.fileId);
		},
		status(): TranscriptionStatus | null
		{
			return this.transcription ? this.transcription.status : null;
		},
		isPending(): boolean
		{
			return this.status === TranscriptionStatus.PENDING;
		},
		isSuccess(): boolean
		{
			return this.status === TranscriptionStatus.SUCCESS;
		},
		buttonIcon(): string
		{
			return this.isOpened ? OutlineIcons.CHEVRON_TOP_M : OutlineIcons.TRANSCRIPTION;
		},
		withTranscription(): boolean
		{
			return this.file.isTranscribable
				&& FeatureManager.isFeatureAvailable(Feature.aiFileTranscriptionAvailable)
				&& FeatureManager.isFeatureAvailable(Feature.copilotAvailable);
		},
	},
	watch:
	{
		status(newValue: TranscriptionStatus | null, oldValue: TranscriptionStatus | null)
		{
			if (oldValue === TranscriptionStatus.PENDING)
			{
				Analytics.getInstance().audioMessage.onViewTranscription(this.chatId, newValue);
				this.open();
			}
		},
	},
	methods:
	{
		async onButtonClick(): Promise<void>
		{
			if (this.isPending)
			{
				return;
			}

			if (this.isOpened)
			{
				this.close();

				return;
			}

			if (this.isSuccess)
			{
				Analytics.getInstance().audioMessage.onViewTranscription(this.chatId, TranscriptionStatus.SUCCESS);
				this.open();

				return;
			}

			const result = await this.transcribe();
			if (result)
			{
				this.open();
			}
		},
		async transcribe(): Promise<void>
		{
			const messageService = new MessageService({ chatId: this.chatId });

			return messageService.transcribe(this.fileId);
		},
		open(): void
		{
			this.$emit('transcriptionToggle', true);
		},
		close(): void
		{
			this.$emit('transcriptionToggle', false);
		},
	},
	template: `
		<div
			v-if="withTranscription"
			class="bx-im-audio-player__transcription-button-container"
		>
			<button @click="onButtonClick">
				<Spinner v-if="isPending" :size="SpinnerSize.XXS" />
				<BIcon
					v-else
					:name="buttonIcon"
					:color="Color.accentBlue"
					:size="20"
				/>
			</button>
		</div>
	`,
};
