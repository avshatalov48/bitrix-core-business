

export class DiskFile
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
		let target = event.target;
		let href = target.getAttribute('href') || (target.getAttribute('data-pseudo-url') && JSON.parse(target.getAttribute('data-pseudo-url')).href);
		if (!href)
		{
			const parentNode = target.parentNode;
			if (parentNode.nodeName === 'A')
			{
				href = parentNode.getAttribute('href');
				target = parentNode;
			}
			else
			{
				const grandParentNode = parentNode.parentNode;
				if (grandParentNode.nodeName === 'A')
				{
					href = grandParentNode.getAttribute('href');
					target = grandParentNode;
				}
			}
		}

		if (target.getAttribute('data-viewer-type')) {
			return;
		}

		if (href && href.indexOf('/bitrix/services/main/ajax.php?action=landing.api.diskFile.download') === 0)
		{
			BX.ajax.get(href.replace('landing.api.diskFile.download', 'landing.api.diskFile.view'), function(data)
			{
				if (typeof data === 'string')
				{
					data = JSON.parse(data);
				}

				if (!data.data)
				{
					return;
				}

				Object.keys(data.data).map(key => {
					target.setAttribute(key, data.data[key]);
				});

				target.click();
			});

			event.preventDefault();
			event.stopPropagation();
			return false;
		}
	}
}