

export class SearchResult
{
	/**
	 * Constructor.
	 */
	constructor()
	{
		this.prepareSearchInput();
		this.scrollToFirstBlock();
	}

	/**
	 * Prepare the search input field by populating it with the 'q' parameter value from the URL.
	 * @return {void}
	 */
	prepareSearchInput()
	{
		const params = new URLSearchParams(window.location.search);
		const qValue = params.get('q');
		const element = document.querySelector('[name="q"]');
		if (element && qValue)
		{
			element.value = qValue;
		}
	}

	/**
	 * Finds first highlight word and scroll to it.
	 * @return {void}
	 */
	scrollToFirstBlock()
	{
		var result = document.querySelector('.landing-highlight');
		if (result)
		{
			var parent = result.parentNode;
			while (parent)
			{
				if (parent.classList.contains('block-wrapper'))
				{
					window.scrollTo({
						top: parent.offsetTop,
						behavior: 'smooth'
					});
					break;
				}
				parent = parent.parentNode;
			}
		}
	}
}