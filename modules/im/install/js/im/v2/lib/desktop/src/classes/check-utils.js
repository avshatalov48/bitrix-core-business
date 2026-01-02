import { Dom, Extension } from 'main.core';

const IMAGE_DESKTOP_RUN = 'icon.png';
const IMAGE_DESKTOP_TWO_WINDOW_MODE = 'internal.png';

const IMAGE_CHECK_URL = 'http://127.0.0.1:20141';
const IMAGE_CHECK_TIMEOUT = 500;
const IMAGE_CLASS = 'bx-im-messenger__out-of-view';

const checkTimeoutList = {};

export const CheckUtils = {
	testImageLoad(image = IMAGE_DESKTOP_RUN): Promise<boolean>
	{
		let resolvePromise = null;

		const loadCheckPromise = new Promise((resolve) => {
			resolvePromise = resolve;
		});

		const dateCheck = Date.now();
		let isPromiseResolvedToFalse = false;

		const imageForCheck = Dom.create({
			tag: 'img',
			attrs: {
				src: `${IMAGE_CHECK_URL}/${image}?${dateCheck}`,
				'data-id': dateCheck,
			},
			props: {
				className: IMAGE_CLASS,
			},
			events: {
				error() {
					if (isPromiseResolvedToFalse)
					{
						return;
					}

					const checkId = this.dataset.id;
					resolvePromise(false);

					clearTimeout(checkTimeoutList[checkId]);
					Dom.remove(this);
				},
				load() {
					const checkId = this.dataset.id;
					resolvePromise(true);

					clearTimeout(checkTimeoutList[checkId]);
					Dom.remove(this);
				},
			},
		});

		document.body.append(imageForCheck);

		checkTimeoutList[dateCheck] = setTimeout(() => {
			isPromiseResolvedToFalse = true;

			resolvePromise(false);
			Dom.remove(imageForCheck);
		}, IMAGE_CHECK_TIMEOUT);

		return loadCheckPromise;
	},

	testInternetConnection(): Promise
	{
		const currentTimestamp = Date.now();

		const settings = Extension.getSettings('im.v2.lib.desktop');
		const internetCheckUrl = settings.get('internetCheckUrl');

		return new Promise((resolve) => {
			fetch(`${internetCheckUrl}.${currentTimestamp}`)
				.then((response: Response) => {
					if (response.status === 200)
					{
						resolve(true);

						return;
					}

					resolve(false);
				})
				.catch(() => {
					resolve(false);
				});
		});
	},

	IMAGE_DESKTOP_RUN,
	IMAGE_DESKTOP_TWO_WINDOW_MODE,
};
