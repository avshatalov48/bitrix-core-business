import { ExpandAnimation } from 'im.v2.component.animation';
import { TranscriptionStatus } from 'im.v2.const';

import '../../css/items/transcription.css';

import type { ImModelMessage, ImModelTranscription } from 'im.v2.model';

// @vue/component
export const TranscriptionItem = {
	name: 'TranscriptionItem',
	components: { ExpandAnimation },
	props: {
		file: {
			type: Object,
			required: true,
		},
		messageId: {
			type: [String, Number],
			required: true,
		},
		isOpened: {
			type: Boolean,
			required: true,
		},
	},
	computed:
	{
		transcription(): ?ImModelTranscription
		{
			return this.$store.getters['files/getTranscription'](this.file.id);
		},
		text(): string
		{
			return this.isError
				? this.loc('IM_MESSAGE_FILE_AUDIO_TRANSCRIPTION_ERROR')
				: this.transcription.transcriptText;
		},
		isError(): boolean
		{
			return this.transcription && this.transcription.status === TranscriptionStatus.ERROR;
		},
		showDivider(): boolean
		{
			return this.isOpened && Boolean(this.message.text);
		},
		message(): ImModelMessage
		{
			return this.$store.getters['messages/getById'](this.messageId);
		},
	},
	methods:
	{
		loc(code: string): string
		{
			return this.$Bitrix.Loc.getMessage(code);
		},
	},
	template: `
		<div class="bx-im-audio-player__transcription-container">
			<ExpandAnimation>
				<div
					v-if="isOpened"
					:class="{'--error': isError}"
				>
					<div class="bx-im-audio-player__transcription-content">
						{{ text }}
					</div>
				</div>
			</ExpandAnimation>
			<div v-if="showDivider" class="bx-im-audio-player__transcription-divider"></div>
		</div>
	`,
};
