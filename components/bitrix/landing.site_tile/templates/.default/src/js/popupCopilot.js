import { Tag, Loc, Dom, Event } from 'main.core';
import { Popup } from 'main.popup';
import { Button } from 'ui.buttons';

export default class PopupCopilot
{
	constructor(options)
	{
		this.id = options.id;
		this.videoSrc = options.videoSrc;

		this.container = null;
		this.content = null;

		this.popup = this.getPopup();
	}

	getContent(): HTMLElement
	{
		if (!this.content)
		{
			this.content = Tag.render`
				<div class="landing-site_title-popup-content">
					<div class="landing-site_title-popup-main">
						<div class="landing-site_title-popup-title">
							${Loc.getMessage('LANDING_SITE_TILE_POPUP_COPILOT_TITLE')}
						</div>
						<div class="landing-site_title-popup-list">
							<div class="landing-site_title-popup-list-item --about">
								<div class="landing-site_title-popup-list-icon"></div>
								<div class="landing-site_title-popup-list-text">
									${Loc.getMessage('LANDING_SITE_TILE_POPUP_COPILOT_LIST_TEXT_1')}
								</div>
							</div>
							<div class="landing-site_title-popup-list-item --ai">
								<div class="landing-site_title-popup-list-icon"></div>
								<div class="landing-site_title-popup-list-text">
									${Loc.getMessage('LANDING_SITE_TILE_POPUP_COPILOT_LIST_TEXT_2')}
								</div>
							</div>
							<div class="landing-site_title-popup-list-item --rocket">
								<div class="landing-site_title-popup-list-icon"></div>
								<div class="landing-site_title-popup-list-text">
									${Loc.getMessage('LANDING_SITE_TILE_POPUP_COPILOT_LIST_TEXT_3')}
								</div>
							</div>
						</div>
						<div class="landing-site_title-popup-desc">
							${Loc.getMessage('LANDING_SITE_TILE_POPUP_COPILOT_DESCRIPTION')}
						</div>
					</div>
					${this.renderVideo()}
				</div>
			`;
		}

		return this.content;
	}

	getPopup(): Popup
	{
		if (!this.container)
		{
			this.container = new Popup({
				darkMode: true,
				bindElement: window,
				content: this.getContent(),
				width: 670,
				closeIcon: { top: '12px', right: '13px' },
				padding: 0,
				className: 'landing-site_title-popup',
				borderRadius: '24px',
				background: '#853af5',
				cacheable: true,
				animation: 'fading-slide',
				overlay: true,
				events: {
					onShow: () => {
						const button = this.container.buttonsContainer.children[0];
						const icon = document.querySelector('.landing-site_title-popup-list-icon');
						const video = document.querySelector('.landing-site_title-popup-video');

						Dom.addClass(this.container.popupContainer, '--animation-first-step');

						Event.bind(button, 'animationend', (event: AnimationEvent) => {
							if (event.animationName === 'landing-site_title-popup-btn-animation')
							{
								Dom.addClass(this.container.popupContainer, '--animation-second-step');
							}
						});
						Event.bind(icon, 'animationend', (event: AnimationEvent) => {
							if (event.animationName === 'landing-site_title-popup-list-icon')
							{
								Dom.addClass(this.container.popupContainer, '--animation-third-step');
							}
						});
						Event.bind(video, 'animationend', (event: AnimationEvent) => {
							if (event.animationName === 'landing-site_title-popup-video')
							{
								this.videoElement.play();
							}
						});
					},
				},
				buttons: [
					new Button({
						text: Loc.getMessage('LANDING_SITE_TILE_POPUP_COPILOT_BUTTON'),
						color: Button.Color.SUCCESS,
						size: Button.Size.EXTRA_LARGE,
						useAirDesign: true,
						style: Button.AirStyle.FILLED_SUCCESS,
						onclick: (button: Button) => {
							button.setWaiting();

							BX.ajax.runAction('bitrix24.license.demolicense.activate')
								.then(() => {
									window.location.href = '/sites/ai/';
								})
								.catch((err) => {
									console.error(err);
									window.location.href = '/sites/ai/';
								});
						},
					}),
				],
			});
		}

		return this.container;
	}

	renderVideo(): HTMLElement
	{
		this.videoElement = Tag.render`
			<video
				src="${this.videoSrc}"
				autoplay
				preload
				loop
				muted
				class="landing-site_title-popup-video"
			></video>
		`;

		Event.bind(this.videoElement, 'canplay', () => {
			this.videoElement.muted = true;
		});

		return this.videoElement;
	}

	showPopup(timeout = 0): void
	{
		if (!this.popup)
		{
			this.popup = this.getPopup();
		}
		setTimeout(() => {
			this.popup.show();
		}, timeout);
	}
}
