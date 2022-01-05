

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

		if (target.nodeName === 'A')
		{
			if (target.getAttribute('data-viewer-type'))
			{
				return;
			}

			let href = target.getAttribute('href');
			if (href.indexOf('/bitrix/services/main/ajax.php?action=landing.api.diskFile.download') === 0)
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
}