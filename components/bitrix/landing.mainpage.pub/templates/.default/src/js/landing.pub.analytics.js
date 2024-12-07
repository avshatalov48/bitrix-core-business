

export class Analytics
{
	/**
	 * Constructor.
	 */
	constructor()
	{
		document.addEventListener('click', this.onClick.bind(this));
	}

	/**
	 * Click callback.
	 *
	 * @return {void}
	 */
	onClick(event)
	{
		let parent = null;
		let currentElement = event.target;

		if (currentElement.tagName.toLowerCase() !== 'a')
		{
			return;
		}

		while (currentElement)
		{
			if (currentElement.classList)
			{
				if (currentElement.classList.contains('workarea-content-paddings'))
				{
					break;
				}

				if (currentElement.classList.contains('block-wrapper'))
				{
					parent = currentElement;
					break;
				}
			}
			currentElement = currentElement.parentElement;
		}

		if (parent && parent.classList)
		{
			let code = '';
			for (const className of parent.classList)
			{
				if (className !== 'block-wrapper')
				{
					code = className;
					break;
				}
			}
			code = code.replace('block-', 'widget-id_');

			BX.UI.Analytics.sendData({
				tool: 'landing',
				category: 'vibe',
				event: 'click_on_button',
				p2: code,
			});
		}
	}
}