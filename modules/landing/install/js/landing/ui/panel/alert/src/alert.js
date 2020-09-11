import {Dom, Tag, Cache} from 'main.core';
import {Loc} from 'landing.loc';
import {BasePanel} from 'landing.ui.panel.base';

import './css/style.css';

/**
 * Implements interface for works with alert panel
 * use this panel for show error and info messages
 *
 * Implements singleton design pattern. Don't use it as constructor
 * use BX.Landing.UI.Panel.Alert.getInstance() for get instance of module
 * @memberOf BX.Landing.UI.Panel
 */
export class Alert extends BasePanel
{
	static staticCache = new Cache.MemoryCache();

	static getInstance(): Alert
	{
		return this.staticCache.remember('instance', () => {
			return new Alert();
		});
	}

	constructor(options = {})
	{
		super(options);
		this.cache = new Cache.MemoryCache();
		this.onCloseClick = this.onCloseClick.bind(this);
		this.text = this.getText();
		this.closeButton = this.getCloseButton();
		this.action = this.getAction();

		Dom.addClass(this.layout, 'landing-ui-panel-alert');

		Dom.append(this.text, this.layout);
		Dom.append(this.action, this.layout);
		Dom.append(this.layout, document.body);
	}

	getText(): HTMLDivElement
	{
		return this.cache.remember('text', () => {
			return Tag.render`<div class="landing-ui-panel-alert-text"></div>`;
		});
	}

	getCloseButton(): HTMLButtonElement
	{
		return this.cache.remember('closeButton', () => {
			const text = Loc.getMessage('LANDING_ALERT_ACTION_CLOSE');
			return Tag.render`
				<button class="ui-btn ui-btn-link" onclick="${this.onCloseClick}">${text}</button>
			`;
		});
	}

	getAction(): HTMLDivElement
	{
		return this.cache.remember('action', () => {
			return Tag.render`<div class="landing-ui-panel-alert-action">${this.getCloseButton()}</div>`;
		});
	}

	show(type, text, hideSupportLink = false): Promise<Alert>
	{
		let promise = Promise.resolve(this);

		if (this.isShown())
		{
			promise = this.hide();
		}

		return promise.then(() => {
			void super.show(this);

			if (type === 'error')
			{
				Dom.removeClass(this.layout, 'landing-ui-alert');
				Dom.addClass(this.layout, 'landing-ui-error');
			}
			else
			{
				Dom.removeClass(this.layout, 'landing-ui-error');
				Dom.addClass(this.layout, 'landing-ui-alert');
			}

			this.text.innerHTML = `${text || type} `;

			if (!hideSupportLink)
			{
				Dom.append(this.getSupportLink(), this.text);
			}

			return this;
		});
	}

	getSupportLink(): HTMLAnchorElement
	{
		return this.cache.remember('supportLink', () => {
			let url = 'https://helpdesk.bitrix24.com/ticket.php';

			switch (Loc.getMessage('LANGUAGE_ID'))
			{
				case 'ru':
				case 'by':
				case 'kz':
					url = 'https://helpdesk.bitrix24.ru/ticket.php';
					break;
				case 'de':
					url = 'https://helpdesk.bitrix24.de/ticket.php';
					break;
				case 'br':
					url = 'https://helpdesk.bitrix24.com.br/ticket.php';
					break;
				case 'es':
					url = 'https://helpdesk.bitrix24.es/ticket.php';
					break;
				default:
			}

			this.supportLink = BX.create('a', {
				props: {className: 'landing-ui-panel-alert-support-link'},
				html: BX.Landing.Loc.getMessage('LANDING_ALERT_ACTION_SUPPORT_LINK'),
				attrs: {href: url, target: '_blank'},
			});

			const text = Loc.getMessage('LANDING_ALERT_ACTION_SUPPORT_LINK');
			return Tag.render`
				<a href="${url}" target="_blank" class="landing-ui-panel-alert-support-link">${text}</a>
			`;
		});
	}

	onCloseClick()
	{
		void this.hide();
	}
}