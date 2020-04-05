import Button from './button';
import { Dom } from 'main.core';

/**
 * @deprecated use BX.UI.Button
 */
export default class ButtonLink extends Button
{
	constructor(params)
	{
		super(params);

		this.buttonNode = Dom.create(
			'span',
			{
				props: {
					className:
						'popup-window-button popup-window-button-link' +
						(this.className.length > 0 ? ' ' + this.className : '')
					,
					id: this.id
				},
				text: this.text,
				events: this.contextEvents
			}
		);
	}
}