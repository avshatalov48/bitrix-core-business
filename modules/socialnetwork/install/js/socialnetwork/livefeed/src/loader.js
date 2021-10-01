export class Loader
{
	static cssClass = {
		feedMask: 'log-internal-mask',
		feedNoMask: 'log-internal-nomask',
		showLoader: 'livefeed-show-loader',
		hideLoader: 'livefeed-hide-loader',
	};

	static onAnimationEnd = (event) => {
		if (
			'animationName' in event
			&& event.animationName
			&& event.animationName === 'hideLoader'
		)
		{
			const loaderContainer = document.getElementById('feed-loader-container');
			if (!loaderContainer)
			{
				return;
			}

			loaderContainer.classList.remove(this.cssClass.showLoader);
			loaderContainer.classList.remove(this.cssClass.hideLoader);
			loaderContainer.style.display = '';
		}
	}

	static showRefreshFade()
	{
		const feedContainer = document.getElementById('log_internal_container');
		if (feedContainer)
		{
			feedContainer.classList.add(this.cssClass.feedMask);
			feedContainer.classList.remove(this.cssClass.feedNoMask);
		}

		const loaderContainer = document.getElementById('feed-loader-container');
		if (loaderContainer)
		{
			loaderContainer.style.display = 'block';
			loaderContainer.classList.remove(this.cssClass.hideLoader);

			setTimeout(() => {
				loaderContainer.classList.add(this.cssClass.showLoader);
			}, 0);
		}
	}

	static hideRefreshFade()
	{
		const feedContainer = document.getElementById('log_internal_container');
		if (feedContainer)
		{
			feedContainer.classList.remove(this.cssClass.feedMask);
			feedContainer.classList.add(this.cssClass.feedNoMask);
		}

		const loaderContainer = document.getElementById('feed-loader-container');
		if (loaderContainer)
		{
			loaderContainer.classList.remove(this.cssClass.showLoader);
			loaderContainer.classList.add(this.cssClass.hideLoader);
		}
	}
}
