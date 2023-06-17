export class MarketService
{
	#loadLink: string = '';

	openPlacement(item, context): Promise
	{
		return new Promise((resolve, reject) => {
			const formData = new FormData();
			Object.entries(item.loadConfiguration).forEach(([key, value]) => {
				formData.append(`PARAMS[params][${key}]`, value);
			});

			Object.entries(this.#getPlacementOptions(context)).forEach(([key, value]) => {
				formData.append(`PARAMS[params][PLACEMENT_OPTIONS][${key}]`, value);
			});

			const requestPrams = {
				method: 'POST',
				body: formData,
			};

			fetch(this.#loadLink, requestPrams)
				.then(response => response.text())
				.then(textResponse => resolve(textResponse))
				.catch((error) => reject(error));
		});
	}

	setLoadLink(link: string)
	{
		this.#loadLink = link;
	}

	#getPlacementOptions(context: Object): {dialogId?: string, messageId?: number}
	{
		const placementOptions = {};

		if (context.dialogId)
		{
			placementOptions.dialogId = context.dialogId;
		}

		if (context.messageId)
		{
			placementOptions.messageId = context.messageId;
		}

		return placementOptions;
	}
}