import {DesktopApi} from './desktop-api';
import {DesktopManager} from './desktop';
import {Dom, Type} from 'main.core';

let locationChangedToBx = false;
const checkTimeoutList = {};
const CHECK_IMAGE_URL = 'http://127.0.0.1:20141';
const DESKTOP_PROTOCOL_VERSION = 2;

export const DesktopUtils = {

	checkRunStatus(successCallback, failureCallback)
	{
		if (!Type.isFunction(failureCallback))
		{
			failureCallback = () => {};
		}

		// this.settings.openDesktopFromPanel -> false

		if (!Type.isFunction(successCallback))
		{
			failureCallback();
			return false;
		}

		const dateCheck = Date.now();
		const Desktop = DesktopManager.getInstance();

		if (!Desktop.isDesktopActive())
		{
			failureCallback(false, dateCheck);
			return true;
		}

		if (DesktopApi.isApiAvailable())
		{
			failureCallback(false, dateCheck);
			return false;
		}

		let alreadyExecuteFailureCallback = false;

		const imageForCheck = Dom.create({
			tag: 'img',
			attrs: {
				src: `${CHECK_IMAGE_URL}/icon.png?${dateCheck}`,
				'data-id': dateCheck,
			},
			props: {
				className: 'bx-im-messenger__out-of-view',
			},
			events: {
				error: function() {
					if (alreadyExecuteFailureCallback)
					{
						return;
					}

					const checkId = this.dataset.id;
					failureCallback(false, checkId);

					clearTimeout(checkTimeoutList[checkId]);
					Dom.remove(this);
				},
				load: function() {
					const checkId = this.dataset.id;
					successCallback(true, checkId);

					clearTimeout(checkTimeoutList[checkId]);
					Dom.remove(this);
				}
			}
		});
		document.body.append(imageForCheck);

		checkTimeoutList[dateCheck] = setTimeout(() => {
			alreadyExecuteFailureCallback = true;

			failureCallback(false, dateCheck);
			Dom.remove(imageForCheck);
		}, 500);

		return true;
	},

	goToBx(url)
	{
		if (!/^bx:\/\/v(\d)\//.test(url))
		{
			url = url.replace('bx://', `bx://v${DESKTOP_PROTOCOL_VERSION}/${location.hostname}/`);
		}

		locationChangedToBx = true;
		setTimeout(() => {
			// eslint-disable-next-line bitrix-rules/no-bx
			BX.onCustomEvent('BXLinkOpened', []);
			locationChangedToBx = false;
		}, 1000);

		location.href = url;
	},

	isLocationChangedToBx()
	{
		return locationChangedToBx;
	},

	encodeParams(params)
	{
		if (!Type.isPlainObject(params))
		{
			return '';
		}

		let stringParams = '';
		let first = true;

		for (const i in params)
		{
			stringParams = stringParams + (first? '': '!!') + i + '!!' + params[i];
			first = false;
		}

		return stringParams;
	},

	decodeParams(encodedParams)
	{
		const result = {};
		if (!Type.isStringFilled(encodedParams))
		{
			return result;
		}

		const chunks = encodedParams.split('!!');
		for (let i = 0; i < chunks.length; i += 2)
		{
			result[chunks[i]] = chunks[i+1];
		}

		return result;
	},

	encodeParamsJson(params)
	{
		if (!Type.isPlainObject(params))
		{
			return '{}';
		}

		let result;
		try
		{
			result = encodeURIComponent(JSON.stringify(params));
		}
		catch (error)
		{
			console.error('DesktopUtils: could not encode params.', error);
			result = '{}';
		}

		return result;
	},

	decodeParamsJson(encodedParams)
	{
		let result = {};
		if (!Type.isStringFilled(encodedParams))
		{
			return result;
		}

		try
		{
			result = JSON.parse(decodeURIComponent(encodedParams));
		}
		catch (error)
		{
			console.error('DesktopUtils: could not decode encoded params.', error);
		}

		return result;
	}
};