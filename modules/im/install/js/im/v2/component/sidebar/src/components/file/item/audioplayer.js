import {BitrixVue} from 'ui.vue3';
import {AudioPlayer, AudioPlayerState} from 'ui.vue3.components.audioplayer';
import {Utils} from 'im.v2.lib.utils';
import {Avatar, AvatarSize} from 'im.v2.component.elements';
import '../../../css/file/audioplayer.css';

// @vue/component
export const SidebarAudioPlayer = BitrixVue.cloneComponent(AudioPlayer, {
	name: 'SidebarAudioPlayer',
	components: {Avatar},
	props: {
		file: {
			type: Object,
			required: true
		},
		authorId: {
			type: Number,
			required: true
		},
		timelineType: {
			type: Number,
			required: true
		},
	},
	data() {
		return {
			...this.parentData(),
			showContextButton: false,
		};
	},
	computed:
	{
		AvatarSize: () => AvatarSize,
		fileSize(): string
		{
			return Utils.file.formatFileSize(this.file.size);
		},
		fileAuthorDialogId(): string
		{
			return this.authorId.toString();
		},
		progressPosition()
		{
			if (!this.loaded || this.state === AudioPlayerState.none)
			{
				return {width: '100%'};
			}

			return {width: `${this.progressInPixel}px`};
		},
		activeTimelineStyles()
		{
			const TIMELINE_VERTICAL_SHIFT = 44;
			const ACTIVE_TIMELINE_VERTICAL_SHIFT = 19;

			const shift = this.timelineType * TIMELINE_VERTICAL_SHIFT + ACTIVE_TIMELINE_VERTICAL_SHIFT;

			return {
				...this.progressPosition,
				'background-position-y': `-${shift}px`
			};
		},
		timelineStyles()
		{
			const TIMELINE_VERTICAL_SHIFT = 44;

			const shift = this.timelineType * TIMELINE_VERTICAL_SHIFT;

			return {
				'background-position-y': `-${shift}px`
			};
		},
	},
	template: `
		<div 
			class="bx-im-sidebar-audio-player__container bx-im-sidebar-audio-player__scope" 
			ref="body"
			@mouseover="showContextButton = true"
			@mouseleave="showContextButton = false"
		>
			<div class="bx-im-sidebar-audio-player__control-container">
				<button :class="['bx-im-sidebar-audio-player__control-button', {
					'bx-im-sidebar-audio-player__control-loader': loading,
					'bx-im-sidebar-audio-player__control-play': !loading && state !== State.play,
					'bx-im-sidebar-audio-player__control-pause': !loading && state === State.play,
				}]" @click="clickToButton"></button>
				<div class="bx-im-sidebar-audio-player__author-avatar-container">
					<Avatar :dialogId="fileAuthorDialogId" :withStatus="false" :size="AvatarSize.XS"></Avatar>
				</div>
			</div>
			<div class="bx-im-sidebar-audio-player__timeline-container">
				<div class="bx-im-sidebar-audio-player__track-container" @click="setPosition" ref="track">
					<div class="bx-im-sidebar-audio-player__track-mask" :style="timelineStyles"></div>
					<div class="bx-im-sidebar-audio-player__track-mask --active" :style="activeTimelineStyles"></div>
					<div class="bx-im-sidebar-audio-player__track-seek" :style="seekPosition"></div>
					<div class="bx-im-sidebar-audio-player__track-event" @mousemove="seeking"></div>
				</div>
				<div class="bx-im-sidebar-audio-player__timer-container">
					{{fileSize}}, {{labelTime}}
				</div>
			</div>
			<button
				v-if="showContextButton"
				class="bx-im-messenger__context-menu-icon bx-im-sidebar-audio-player__context-menu-button"
				@click="$emit('contextMenuClick', $event)"
			></button>
			<audio 
				v-if="src" 
				:src="src" 
				class="bx-im-sidebar-audio-player__audio-source" 
				ref="source" 
				:preload="preload"
				@abort="audioEventRouter('abort', $event)"
				@error="audioEventRouter('error', $event)"
				@suspend="audioEventRouter('suspend', $event)"
				@canplay="audioEventRouter('canplay', $event)"
				@canplaythrough="audioEventRouter('canplaythrough', $event)"
				@durationchange="audioEventRouter('durationchange', $event)"
				@loadeddata="audioEventRouter('loadeddata', $event)"
				@loadedmetadata="audioEventRouter('loadedmetadata', $event)"
				@timeupdate="audioEventRouter('timeupdate', $event)"
				@play="audioEventRouter('play', $event)"
				@playing="audioEventRouter('playing', $event)"
				@pause="audioEventRouter('pause', $event)"
			></audio>
		</div>
	`
});