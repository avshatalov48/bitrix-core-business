import { Type, Dom, Event } from 'main.core';
import { BannerDispatcher } from 'ui.banner-dispatcher';
import { Popup, PopupManager } from 'main.popup';
import './style.css';

export type NotificationOptions = {
	title: ?string;
	description: ?string;
	type: number;
}

export class MailboxGridNotification
{
	#popup: Popup = null;
	#options: NotificationOptions = null;
	mailboxGridButton: ?Element = null;

	constructor(options: NotificationOptions)
	{
		if (Type.isObject(options))
		{
			this.#options = options;
		}

		this.mailboxGridButton = document.querySelector('[data-id="mailboxGridButton"]');
	}

	createNotificationBalloon(onDone: Function): BX.UI.Notification.Balloon
	{
		return PopupManager.create({
			id: 'push-mailbox-grid',
			className: 'popup-window-dark',
			background: 'rgb(8, 93, 193)',
			closeIcon: true,
			autoHide: false,
			closeByEsc: true,
			padding: 12,
			borderRadius: 20,
			contentPadding: 0,
			offsetTop: 10,
			offsetLeft: -78,
			angle: {
				offset: 205,
				position: 'top',
			},
			bindElement: this.mailboxGridButton,
			bindOptions: {
				forceBindPosition: false,
			},
			width: 372,
			minHeight: 120,
			content: this.getContent(),
			events: {
				onClose: () => {
					onDone();
				},
			},
		});
	}

	getContent(): HTMLElement
	{
		const title = this.#options?.title;
		const description = this.#options?.description;

		return Dom.create('div', {
			props: {
				className: 'mail-notification-container',
			},
			children: [
				Dom.create('div', {
					props: {
						className: 'mail-notification-container__image-wrapper',
					},
					children: [
						this.#renderImage(),
					],
				}),
				Dom.create('div', {
					props: {
						className: 'mail-notification-content',
					},
					children: [
						this.#getMessageContainer(title, description),
					],
				}),
			],
		});
	}

	#renderImage(): HTMLElement
	{
		return Dom.create('div', {
			props: {
				className: 'mail-notification-container__image',
			},
		});
	}

	#getMessageContainer(title: ?string, description: ?string): HTMLElement
	{
		const children = [];

		if (title)
		{
			children.push(Dom.create('h4', {
				props: {
					className: 'mail-notification-content__title',
				},
				html: title,
			}));
		}

		if (description)
		{
			children.push(Dom.create('span', {
				props: {
					className: 'mail-notification-content__description',
				},
				html: description,
			}));
		}

		return Dom.create('div', {
			props: {
				className: 'mail-notification-content-wrapper',
			},
			children,
		});
	}

	show(): void
	{
		if (!this.mailboxGridButton)
		{
			return;
		}

		BannerDispatcher.critical.toQueue((onDone) => {
			this.#popup = this.createNotificationBalloon(onDone);
			this.#popup.show();
			this.#popup.zIndexComponent.setZIndex(400);

			BX.userOptions.save('mail.tour', 'mailbox_grid_hint_shown', null, 'Y');

			Event.bind(this.mailboxGridButton, 'click', () => {
				this.#popup?.close();
			});
		});
	}
}
