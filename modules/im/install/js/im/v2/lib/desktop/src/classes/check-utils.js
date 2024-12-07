import { Dom } from 'main.core';

const IMAGE_DESKTOP_RUN = 'icon.png';
const IMAGE_DESKTOP_TWO_WINDOW_MODE = 'internal.png';

const IMAGE_CHECK_URL = 'http://127.0.0.1:20141';
const IMAGE_CHECK_TIMEOUT = 500;
const IMAGE_CLASS = 'bx-im-messenger__out-of-view';

const INTERNET_CHECK_URL = '//www.bitrixsoft.com/200.ok';

const checkTimeoutList = {};

export const CheckUtils = {
	testImageLoad(successCallback, failureCallback, image = IMAGE_DESKTOP_RUN)
	{
		const dateCheck = Date.now();
		let failureCallbackCalled = false;

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
					if (failureCallbackCalled)
					{
						return;
					}

					const checkId = this.dataset.id;
					failureCallback(false, checkId);

					clearTimeout(checkTimeoutList[checkId]);
					Dom.remove(this);
				},
				load() {
					const checkId = this.dataset.id;
					successCallback(true, checkId);

					clearTimeout(checkTimeoutList[checkId]);
					Dom.remove(this);
				},
			},
		});

		document.body.append(imageForCheck);

		checkTimeoutList[dateCheck] = setTimeout(() => {
			failureCallbackCalled = true;

			failureCallback(false, dateCheck);
			Dom.remove(imageForCheck);
		}, IMAGE_CHECK_TIMEOUT);
	},

	testInternetConnection(): Promise
	{
		const currentTimestamp = Date.now();

		return new Promise((resolve) => {
			fetch(`${INTERNET_CHECK_URL}.${currentTimestamp}`)
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
