import {Tag, Loc, Dom, Text} from 'main.core';
import DefaultTab from './default-tab';

export default class CameraTab extends DefaultTab
{
	static priority = 2;

	static #isCameraEnabled = null;

	getHeader(): ?String
	{
		return null;
	}

	getBody(): Element
	{
		return this.cache.remember('body', () => {
			const res = Tag.render`
				<div>
					<div class="ui-avatar-editor__camera-block-image">
						<div class="ui-avatar-editor__btn-back" data-bx-role="button-back"></div>
						<div class="ui-avatar-editor__user-loader-item">
							<div class="ui-avatar-editor__loader">
								<svg class="ui-avatar-editor__circular" viewBox="25 25 50 50">
									<circle class="ui-avatar-editor__path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"/>
								</svg>
							</div>
						</div>
						<div class="ui-avatar-editor__error">
							<span>
								${Loc.getMessage('JS_AVATAR_EDITOR_ERROR')}
							</span>
							<span data-bx-role="tab-camera-error"></span>
						</div>
						<div class="ui-avatar-editor__camera-block-image-inner">
							<video autoplay></video>
						</div>
					</div>
					<div class="ui-avatar-editor__button-layout" data-bx-role="camera-button">
						<div class="ui-avatar-editor__button">
							<span class="ui-avatar-editor__button-icon"></span>
						</div>
					</div>
				</div>
			`;
			const video = res.querySelector('VIDEO');
			video.addEventListener("playing", (event) => {
				const visibleWidth = res.clientWidth,
					visibleHeight = res.clientHeight,
					w = video.clientWidth,
					h = video.clientHeight,
					scale = Math.max(
						( w > 0 ? visibleWidth / w : 1 ),
						( h > 0 ? visibleHeight / h : 1 )
					),
					left = (w * scale - w) / 2 + (visibleWidth - w * scale) / 2,
					top = (h * scale - h) / 2 + (visibleHeight - h * scale) / 2;
				Dom.adjust(
					video.parentNode, {style : {
							width : w + 'px',
							height : h + 'px',
							transform : 'translate(' +
								Math.ceil(left) + 'px, ' +
								Math.ceil(top) + 'px) scale(' + scale + ', ' + scale + ')'
						}}
				);
			});
			res.querySelector('[data-bx-role="camera-button"]').onclick = () => {
				this.emit('onSetFile', video);
			};
			res.querySelector('[data-bx-role="button-back"]').onclick = () => {
				this.emit('onClickBack');
			};
			return res;
		});
	}

	inactivate(): DefaultTab {
		this.stopStreaming();
		return super.inactivate();
	}

	activate(): DefaultTab {
		this.startStreaming();
		return super.activate();
	}

	startStreaming()
	{
		const video = this.getBody().querySelector('VIDEO');
		video.setAttribute("active", "Y");
		navigator.mediaDevices.getUserMedia({
			audio: false,
			video: {
				width: {max: 1024, min: 640, ideal: 1024},
				height: {max: 860, min: 480, ideal: 860}
			}
		}).then(function(stream) {
			if (video.hasAttribute("active"))
			{
				video.srcObject = stream;
			}
			else
			{
				stream.getTracks()[0].stop();
			}
		})
		.catch((error) => {
			this.getBody().querySelector('[data-bx-role="tab-camera-error"]')
				.innerHTML = Text.encode(error);
		});
	}

	stopStreaming()
	{
		const video = this.getBody().querySelector('VIDEO');
		video.removeAttribute("active");
		video.pause();
		video.src = "";
		if (video.srcObject)
		{
			video.srcObject.getTracks()[0].stop();
		}
	}

	static check(): void
	{
		if (this.#isCameraEnabled === null)
		{
			if (navigator.mediaDevices && navigator.mediaDevices.enumerateDevices)
			{
				navigator.mediaDevices
					.enumerateDevices()
					.then((devices) =>
					{
						this.#isCameraEnabled = Array
							.from(devices)
							.filter(function(deviceInfo) {
								return deviceInfo.kind === 'videoinput';
							}).length > 0;
					})
					.catch(() => {
						this.#isCameraEnabled = false;
					});
			}
			else
			{
				this.#isCameraEnabled = false;
			}
		}
	}

	static isAvailable(): boolean
	{
		this.check();
		return this.#isCameraEnabled;
	}

	static get code()
	{
		return 'camera';
	}
}
