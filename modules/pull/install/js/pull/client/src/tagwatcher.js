export type TagWatcherOptions = {
	restClient: any
}

export class TagWatcher
{
	queue: { [string]: boolean } = {};
	watchUpdateInterval = 1_740_000;
	watchForceUpdateInterval = 5000;

	constructor(options: TagWatcherOptions)
	{
		this.restClient = options.restClient;
	}

	extend(tag, force)
	{
		if (!tag || this.queue[tag])
		{
			return;
		}

		this.queue[tag] = true;
		if (force)
		{
			this.scheduleUpdate(true);
		}
	}

	clear(tagId)
	{
		delete this.queue[tagId];
	}

	scheduleUpdate(force)
	{
		clearTimeout(this.watchUpdateTimeout);
		this.watchUpdateTimeout = setTimeout(
			() => {
				this.update();
			},
			force ? this.watchForceUpdateInterval : this.watchUpdateInterval,
		);
	}

	update()
	{
		const watchTags = Object.keys(this.queue);
		if (watchTags.length > 0)
		{
			this.restClient.callMethod('pull.watch.extend', { tags: watchTags }, (result) => {
				if (result.error())
				{
					this.scheduleUpdate();

					return;
				}

				const updatedTags = result.data();

				for (const tagId of Object.keys(updatedTags))
				{
					if (!updatedTags[tagId])
					{
						this.clear(tagId);
					}
				}
				this.scheduleUpdate();
			});
		}
		else
		{
			this.scheduleUpdate();
		}
	}
}
