import {ImModelSidebarFileItem, ImModelFile} from 'im.v2.model';
import {Avatar, AvatarSize} from 'im.v2.component.elements';
import {SocialVideo} from 'ui.vue3.components.socialvideo';
import {Utils} from 'im.v2.lib.utils';
import 'ui.viewer';
import '../../../css/file/media-detail-item.css';

// @vue/component
export const MediaDetailItem = {
	name: 'MediaDetailItem',
	components: {SocialVideo, Avatar},
	props: {
		fileItem: {
			type: Object,
			required: true
		}
	},
	emits: ['contextMenuClick'],
	data() {
		return {
			showContextButton: false,
			videoDuration: 0,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		sidebarFileItem(): ImModelSidebarFileItem
		{
			return this.fileItem;
		},
		file(): ImModelFile
		{
			return this.$store.getters['files/get'](this.sidebarFileItem.fileId, true);
		},
		previewPicture(): Object
		{
			if (!this.hasPreview)
			{
				return {};
			}

			return {
				backgroundImage: `url('${this.file.urlPreview}')`,
			};
		},
		hasPreview(): boolean
		{
			return this.file.urlPreview !== '';
		},
		isImage(): boolean
		{
			return this.file.type === 'image';
		},
		isVideo(): boolean
		{
			return this.file.type === 'video';
		},
		viewerAttributes(): Object
		{
			return Utils.file.getViewerDataAttributes(this.file.viewerAttrs);
		},
		videoDurationText(): string
		{
			if (this.videoDuration === 0)
			{
				return '--:--';
			}

			return this.formatTime(this.videoDuration);
		}
	},
	methods:
	{
		formatTime(rawSeconds: number): string
		{
			rawSeconds = Math.floor(rawSeconds);
			const durationHours = Math.floor(rawSeconds/60/60);
			if (durationHours > 0)
			{
				rawSeconds -= durationHours*60*60;
			}

			const durationMinutes = Math.floor(rawSeconds/60);
			if (durationMinutes > 0)
			{
				rawSeconds -= durationMinutes*60;
			}

			const hours = durationHours > 0 ? `${durationHours}:`: '';
			const minutes = hours > 0 ? `${durationMinutes.toString().padStart(2, '0')}:`: `${durationMinutes}:`;
			const seconds = rawSeconds.toString().padStart(2, '0');

			return hours + minutes + seconds;
		},
		handleVideoEvent()
		{
			if (!this.$refs['video'])
			{
				return;
			}

			this.videoDuration = this.$refs['video'].duration;
		},
		onContextMenuClick(event)
		{
			this.$emit('contextMenuClick', {
				sidebarFile: this.sidebarFileItem,
				file: this.file,
				messageId: this.sidebarFileItem.messageId,
			}, event.currentTarget);
		}
	},
	template: `
		<div 
			class="bx-im-sidebar-file-media-detail-item__container bx-im-sidebar-file-media-detail-item__scope"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-sidebar-file-media-detail-item__header-container">
				<div class="bx-im-sidebar-file-media-detail-item__avatar-container">
					<Avatar :dialogId="sidebarFileItem.authorId" :withStatus="false" :size="AvatarSize.S"></Avatar>
				</div>
				<button
					v-if="showContextButton"
					class="bx-im-sidebar-file-media-detail-item__context-menu bx-im-messenger__context-menu-icon"
					@click="onContextMenuClick"
				></button>
			</div>
			<div 
				v-if="isImage"
				class="bx-im-sidebar-file-media-detail-item__content --image" 
				:style="previewPicture"
				v-bind="viewerAttributes"
			>
			</div>
			<div
				v-if="isVideo"
				class="bx-im-sidebar-file-media-detail-item__content --video"
				:style="previewPicture"
				v-bind="viewerAttributes"
			>
				<video 
					v-show="!hasPreview"
					ref="video"
					class="bx-im-sidebar-file-media-detail-item__video" 
					preload="metadata" :src="file.urlDownload"
					@durationchange="handleVideoEvent"
					@loadeddata="handleVideoEvent"
					@loadedmetadata="handleVideoEvent"
				></video>
			</div>
			<div v-if="isVideo" class="bx-im-sidebar-file-media-detail-item__video-controls">
				<span class="bx-im-sidebar-file-media-detail-item__video-controls-icon"></span>
				<span class="bx-im-sidebar-file-media-detail-item__video-controls-time">{{ videoDurationText }}</span>
			</div>
		</div>
	`
};