import Button from './button';
import { Dom } from 'main.core';

/**
 * @deprecated use BX.UI.Button
 */
export default class CustomButton extends Button
{
	constructor(params)
	{
		super(params);

		this.buttonNode = Dom.create(
			'span',
			{
				props: {
					className: (this.className.length > 0 ? this.className : ''),
					id: this.id
				},
				events: this.contextEvents,
				text: this.text
			}
		);
	}
}