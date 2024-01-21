let updateRequestStarted = false;
let csrfToken = '';
let sessionTime = 0;
let setIntervalId = null;
const browserInstances = [];

const BASE_ENDPOINT = '/bitrix/services/main/ajax.php?action=';
const UPDATE_STATE_ACTION = 'im.v2.UpdateState.getStateData';

const requestDebounced = debounce(requestUpdate, 5000);

// eslint-disable-next-line no-undef
onconnect = (event: MessageEvent) => {
	const port: MessagePort = event.ports[0];
	browserInstances.push(port);

	port.onmessage = onMessage;
	port.onmessageerror = onError;
};

function startUpdateInterval(data: { csrfToken: string, sessionTime: number, force: boolean })
{
	updateRequestStarted = true;
	setCsrfToken(data.csrfToken);
	setSessionTime(data.sessionTime);

	if (data.force)
	{
		requestDebounced();
	}

	setIntervalId = setInterval(async () => {
		await requestUpdate();
	}, sessionTime);
}

async function requestUpdate(): Promise<?Response>
{
	try
	{
		const response = await fetch(getUpdateUrl(), getRequestOptions());

		const handledResponse = await handleResponse(response);
		if (handledResponse)
		{
			browserInstances.forEach((instance) => {
				instance.postMessage({ response: handledResponse.data, csrfToken: getCsrfToken() });
			});
		}
	}
	catch (error)
	{
		console.error('updateState: request error', error);
	}

	return null;
}

async function handleResponse(response: Response): ?Response
{
	try
	{
		const responseJson = await response.json();
		if (responseJson.errors && responseJson.errors.length > 0)
		{
			const csrfTokenFromError = getCsrfTokenFromError(responseJson);
			setCsrfToken(csrfTokenFromError || getCsrfToken());
		}

		return responseJson;
	}
	catch (jsonError)
	{
		console.error('updateState: json error', jsonError);
	}

	return null;
}

function getCsrfTokenFromError(responseJson: Response): ?string
{
	let newCsrfToken = null;
	responseJson.errors.forEach((error) => {
		if (!error.customData?.csrf || error.customData?.csrf.length === 0)
		{
			return;
		}

		newCsrfToken = error.customData.csrf;
	});

	return newCsrfToken;
}

function getUpdateUrl(): string
{
	return `${BASE_ENDPOINT}${UPDATE_STATE_ACTION}`;
}

function getRequestOptions(): Object
{
	return {
		method: 'POST',
		credentials: 'same-origin',
		headers: {
			'X-Bitrix-Csrf-Token': getCsrfToken(),
		},
	};
}

function setCsrfToken(token: string)
{
	csrfToken = token;
}

function getCsrfToken(): string
{
	return csrfToken;
}

function setSessionTime(time: number)
{
	sessionTime = time;
}

function debounce(func: Function, wait: number = 0): Function
{
	let timeoutId = null;

	return function debounced(...args)
	{
		// eslint-disable-next-line @bitrix24/bitrix24-rules/no-typeof
		if (typeof timeoutId !== 'undefined' && timeoutId !== null)
		{
			clearTimeout(timeoutId);
		}

		timeoutId = setTimeout(() => {
			func(...args);
		}, wait);
	};
}

function onMessage({ data })
{
	if (updateRequestStarted)
	{
		clearInterval(setIntervalId);
	}

	startUpdateInterval(data);
}

function onError(error: MessageEvent)
{
	console.error('shared worker: messageerror', error);
}
