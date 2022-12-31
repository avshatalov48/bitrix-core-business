import {Logger} from 'im.v2.lib.logger';

const VIDEO_CONSTRAINT_WIDTH = 1280;
const VIDEO_CONSTRAINT_HEIGHT = 720;

// @vue/component
export const VideoPreview = {
	data()
	{
		return {
			noVideo: false
		};
	},
	computed:
	{
		videoClasses()
		{
			return {'--flipped': BX.Call.Hardware.enableMirroring};
		}
	},
	created()
	{
		this.initHardware().then(() => {
			this.getDefaultDevices();
		}).catch(error => {
			console.error('VideoPreview: error initing hardware', error);
		});
	},
	beforeUnmount()
	{
		this.videoStream.getTracks().forEach(tr => tr.stop());
		this.videoStream = null;
	},
	methods:
	{
		getDefaultDevices()
		{
			const constraints = {audio: false, video: true};
			constraints.video = {};
			constraints.video.width = {ideal: VIDEO_CONSTRAINT_WIDTH};
			constraints.video.height = {ideal: VIDEO_CONSTRAINT_HEIGHT};

			if (BX.Call.Hardware.defaultCamera)
			{
				this.selectedCamera = BX.Call.Hardware.defaultCamera;
				constraints.video = {...constraints.video, ...{deviceId: {exact: this.selectedCamera}}};
			}
			else if (Object.keys(BX.Call.Hardware.cameraList).length === 0)
			{
				console.error('VideoPreview: no camera');
				return;
			}

			navigator.mediaDevices.getUserMedia(constraints).then((stream: MediaStream) => {
				this.videoStream = stream;
				if (stream.getVideoTracks().length === 0)
				{
					this.noVideo = true;
					console.error('VideoPreview: no video tracks');
					return;
				}

				if (!this.selectedCamera)
				{
					this.selectedCamera = stream.getVideoTracks()[0].getSettings().deviceId;
				}
				this.playLocalVideo();
			});
		},
		playLocalVideo()
		{
			Logger.warn('VideoPreview: playing local video');
			this.$refs['video'].volume = 0;
			this.$refs['video'].srcObject = this.videoStream;
			this.$refs['video'].play();
		},
		initHardware(): Promise
		{
			return BX.Call.Hardware.init();
		},
		loc(phraseCode: string): string
		{
			return this.$Bitrix.Loc.getMessage(phraseCode);
		}
	},
	template: `
		<div class="bx-im-call-background__video">
			<div v-if="noVideo" class="bx-im-call-background__no-cam_container">
				<div class="bx-im-call-background__no-cam_icon"></div>
				<div class="bx-im-call-background__no-cam_title">{{ loc('BX_IM_CALL_BG_NO_CAM') }}</div>
			</div>
			<video v-else :class="videoClasses" ref="video" muted autoplay playsinline></video>
		</div>
	`
};