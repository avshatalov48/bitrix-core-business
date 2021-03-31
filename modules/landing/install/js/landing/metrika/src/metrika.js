import { Dom, Event } from 'main.core';

/**
 * @memberOf BX.Landing
 */
export class Metrika
{
	constructor()
	{
		this.formSelector= '.bitrix24forms';
		this.widgetBlockItemSelector = '.landing-b24-widget-button-social-item';
		this.formBlocks = [...document.querySelectorAll(this.formSelector)];
		this.formsLoaded = [];
		this.sendedLabel = [];
		this.widgetOpened = false;
		this.widgetBlockHover = false;

		if (this.isFormsExists())
		{
			this.waitForForms();
		}
		this.waitForWidget();
		this.detectAnchor();
	}

	/**
	 * Is any form exists into the page.
	 * @return {boolean}
	 */
	isFormsExists(): boolean
	{
		return this.formBlocks.length > 0;
	}

	/**
	 * Listener for address links on the page.
	 */
	detectAnchor(): void
	{
		[...document.querySelectorAll('a')].map(node => {
			const href = Dom.attr(node, 'href');
			if (href && href.indexOf(':'))
			{
				const hrefPref = href.split(':')[0];
				if (['callto', 'tel', 'mailto'].includes(hrefPref))
				{
					Event.bind(node, 'click', () => {
						this.sendLabel('', 'addressClick', hrefPref);
					});
				}
			}
		});
	}

	/**
	 * Listener for widget commands.
	 */
	waitForWidget(): void
	{
		[...document.querySelectorAll(this.widgetBlockItemSelector)].map(node => {
			Event.bind(node, 'mouseover', () => {
				this.widgetBlockHover = true;
			});
			Event.bind(node, 'mouseout', () => {
				this.widgetBlockHover = false;
			});
			Event.bind(node, 'click', (event) => {
				[...node.classList].map(className => {
					if (className.indexOf('ui-icon-service-') === 0)
					{
						const ol = className.substr('ui-icon-service-'.length);
						this.sendLabel('', 'olOpenedFromWidget', ol);
					}
				});
			});
		});

		window.addEventListener('onBitrixLiveChat', event => {
			const {widget, widgetHost} = event.detail;
			widget.subscribe({
				type: BX.LiveChatWidget.SubscriptionType.every,
				callback: event => {
					if (event.type === BX.LiveChatWidget.SubscriptionType.widgetOpen)
					{
						if (this.widgetBlockHover)
						{
							this.sendLabel(widgetHost, 'chatOpenedFromWidget');
						}
						else
						{
							this.sendLabel(widgetHost, 'chatOpened');
						}
					}
				}

			});
		});
	}

	/**
	 * Sends analytic label when form is loaded, otherwise sends fail label.
	 */
	waitForForms(): void
	{
		window.addEventListener('b24:form:show:first', event => {
			const {id, sec, address} = event.detail.object.identification;
			const disabled = event.detail.object.disabled;

			this.formsLoaded.push(id + '|' + sec);

			if (disabled)
			{
				this.sendLabel(address, 'formDisabledLoad', id+ '|' + sec);
			}
			else
			{
				this.sendLabel(address, 'formSuccessLoad', id+ '|' + sec);
			}
		});

		setTimeout(() => {
			this.formBlocks.map(node => {
				const dataAttr = Dom.attr(node, 'data-b24form');
				if (dataAttr && dataAttr.indexOf('|'))
				{
					const formData = dataAttr.split('|');
					if (!this.formsLoaded.includes(formData[0] + '|' + formData[1]))
					{
						this.sendLabel(
							null,
							'formFailLoad',
							formData[0] + '|' + formData[1]
						);
					}
				}
			});
		}, 5000);
	}

	/**
	 * Send label to the portal.
	 * @param {string} portalUrl
	 * @param {string} label
	 * @param {string} value
	 */
	sendLabel(portalUrl: ?string, label: string, value: string): void
	{
		if (this.sendedLabel.includes(label + value))
		{
			return;
		}
		this.sendedLabel.push(label + value);
		BX.ajax({url:
			(portalUrl ? portalUrl : '') + '/bitrix/images/landing/analytics/pixel.gif?action=' + label +
			(value ? '&value=' + value : '') +
			'&time=' + (new Date().getTime())
		});
	}
}