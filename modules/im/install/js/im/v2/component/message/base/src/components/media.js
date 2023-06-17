import {File, Image, Audio, Video} from 'im.v2.component.elements';
import {FileType, MessageType} from 'im.v2.const';

import type {ImModelFile, ImModelMessage} from 'im.v2.model';

// @vue/component
export const Media = {
	name: 'MediaComponent',
	components: {File, Image, Audio, Video},
	props:
	{
		item: {
			type: Object,
			required: true
		}
	},
	data()
	{
		return {};
	},
	computed:
	{
		FileType: () => FileType,
		message(): ImModelMessage
		{
			return this.item;
		},
		messageFiles(): ImModelFile[]
		{
			const files = [];

			if (this.message.files.length === 0)
			{
				return files;
			}

			this.message.files.forEach((fileId: number) => {
				const file: ImModelFile = this.$store.getters['files/get'](fileId, true);
				files.push(file);
			});

			return files;
		},
		messageType(): $Values<typeof MessageType>
		{
			return this.$store.getters['messages/getMessageType'](this.message.id);
		}
	},
	template: `
		<div v-for="file in messageFiles" :key="file.id" class="bx-im-message-base__media-wrap">
			<Image v-if="file.type === FileType.image" :item="file" />
			<Audio v-else-if="file.type === FileType.audio" :item="file" :messageType="messageType" />
			<Video v-else-if="file.type === FileType.video" :item="file" />
			<File v-else :item="file" />
		</div>
	`
};