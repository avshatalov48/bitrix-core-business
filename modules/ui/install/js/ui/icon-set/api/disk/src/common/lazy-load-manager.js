export class LazyLoadManager
{
	static #instance: ?LazyLoadManager;
	#observer: IntersectionObserver;
	#pendingElements: WeakMap = new WeakMap();

	constructor() {
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-io-without-polyfill
		this.#observer = new IntersectionObserver((entries) => {
			entries.forEach((entry) => {
				if (entry.isIntersecting)
				{
					const data = this.#pendingElements.get(entry.target);
					if (data)
					{
						data.callback();
						this.#observer.unobserve(entry.target);
						this.#pendingElements.delete(entry.target);
					}
				}
			});
		}, {
			root: null,
			rootMargin: '50px',
			threshold: 0.1,
		});
	}

	static getInstance(): LazyLoadManager
	{
		if (!LazyLoadManager.#instance)
		{
			LazyLoadManager.#instance = new LazyLoadManager();
		}

		return LazyLoadManager.#instance;
	}

	observe(element: HTMLElement, callback: () => void): void
	{
		this.#pendingElements.set(element, { callback });
		this.#observer.observe(element);
	}
}
