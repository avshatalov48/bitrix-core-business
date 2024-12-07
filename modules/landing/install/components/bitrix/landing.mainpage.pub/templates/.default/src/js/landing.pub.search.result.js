

export class SearchResult
{
	/**
	 * Constructor.
	 */
	constructor()
	{
		this.scrollToFirstBlock();
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