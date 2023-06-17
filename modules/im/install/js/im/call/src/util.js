import {Type} from 'main.core'
import {CallEngine} from './engine/engine';

const blankAvatar = '/bitrix/js/im/images/blank.gif';

let userData = {}
let usersInProcess = {}

function updateUserData(callId, users)
{
	let usersToUpdate = [];
	for (let i = 0; i < users.length; i++)
	{
		if (userData.hasOwnProperty(users[i]))
		{
			continue;
		}

		usersToUpdate.push(users[i]);
	}

	let result = new Promise((resolve, reject) =>
	{
		if (usersToUpdate.length === 0)
		{
			return resolve();
		}

		CallEngine.getRestClient().callMethod("im.call.getUsers", {
			callId: callId,
			userIds: usersToUpdate
		}).then((response) =>
		{
			const result = Type.isPlainObject(response.answer.result) ? response.answer.result : {};
			users.forEach((userId) =>
			{
				if (result[userId])
				{
					userData[userId] = result[userId];
				}
				delete usersInProcess[userId];
			});
			resolve();

		}).catch(function (error)
		{
			reject(error.answer);
		});
	});

	for (let i = 0; i < usersToUpdate.length; i++)
	{
		usersInProcess[usersToUpdate[i]] = result;
	}
	return result;
}

function setUserData(userData)
{
	for (let userId in userData)
	{
		userData[userId] = userData[userId];
	}
}

const getDateForLog = () =>
{
	const d = new Date();

	return d.getFullYear() + "-" + lpad(d.getMonth() + 1, 2, '0') + "-" + lpad(d.getDate(), 2, '0') + " " + lpad(d.getHours(), 2, '0') + ":" + lpad(d.getMinutes(), 2, '0') + ":" + lpad(d.getSeconds(), 2, '0') + "." + d.getMilliseconds();
}

const getTimeForLog = () =>
{
	const d = new Date();

	return lpad(d.getHours(), 2, '0') + ":" + lpad(d.getMinutes(), 2, '0') + ":" + lpad(d.getSeconds(), 2, '0') + "." + d.getMilliseconds();
}

function lpad(str, length, chr)
{
	str = str.toString();
	chr = chr || ' ';

	if (str.length > length)
	{
		return str;
	}

	let result = '';
	for (let i = 0; i < length - str.length; i++)
	{
		result += chr;
	}

	return result + str;
}

function getUser(callId, userId)
{
	return new Promise((resolve, reject) =>
	{
		if (userData.hasOwnProperty(userId))
		{
			return resolve(userData[userId]);
		}
		else if (usersInProcess.hasOwnProperty(userId))
		{
			usersInProcess[userId].then(() =>
			{
				return resolve(userData[userId]);
			});
		}
		else
		{
			updateUserData(callId, [userId]).then(() =>
			{
				return resolve(userData[userId]);
			});
		}
	});
}

function getUserCached(userId)
{
	return userData.hasOwnProperty(userId) ? userData[userId] : null;
}

function getUsers(callId, users)
{
	return new Promise((resolve, reject) =>
	{
		updateUserData(callId, users).then(() =>
		{
			let result = {};
			users.forEach(userId => result[userId] = userData[userId] || {});
			return resolve(result);
		});
	});
}

function getUserName(callId, userId)
{
	return new Promise((resolve, reject) =>
	{
		if (userData.hasOwnProperty(userId))
		{
			return resolve(userData[userId].name ? userData[userId].name : '');
		}
		else if (usersInProcess.hasOwnProperty(userId))
		{
			usersInProcess[userId].then(() =>
			{
				return resolve(userData[userId].name ? userData[userId].name : '');
			});
		}
		else
		{
			updateUserData(callId, [userId]).then(() =>
			{
				return resolve(userData[userId].name ? userData[userId].name : '');
			});
		}
	});
}

function getUserAvatar(callId, userId)
{
	return new Promise((resolve, reject) =>
	{
		if (userData.hasOwnProperty(userId))
		{
			return resolve(userData[userId].avatar_hr && !isBlank(userData[userId].avatar_hr) ? userData[userId].avatar_hr : '');
		}
		else if (usersInProcess.hasOwnProperty(userId))
		{
			usersInProcess[userId].then(() =>
			{
				return resolve(userData[userId].avatar_hr && !isBlank(userData[userId].avatar_hr) ? userData[userId].avatar_hr : '');
			});
		}
		else
		{
			updateUserData(callId, [userId]).then(() =>
			{
				return resolve(userData[userId].avatar_hr && !isBlank(userData[userId].avatar_hr) ? userData[userId].avatar_hr : '');
			});
		}
	});
}

function getUserAvatars(callId, users)
{
	return new Promise((resolve, reject) =>
	{
		updateUserData(callId, users).then(() =>
		{
			let result = {};
			users.forEach((userId) =>
			{
				result[userId] = userData[userId].avatar_hr && !isBlank(userData[userId].avatar_hr) ? userData[userId].avatar_hr : ''
			});
			return resolve(result);
		});
	});
}

function isAvatarBlank(url)
{
	return isBlank(url);
}

function getCustomMessage(message, userData)
{
	let messageText;
	if (!Type.isPlainObject(userData))
	{
		userData = {};
	}

	if (userData.gender && BX.message.hasOwnProperty(message + '_' + userData.gender))
	{
		messageText = BX.message(message + '_' + userData.gender);
	}
	else
	{
		messageText = BX.message(message);
	}

	userData = convertKeysToUpper(userData);

	return messageText.replace(/#.+?#/gm, function (match)
	{
		const placeHolder = match.substr(1, match.length - 2);
		return userData.hasOwnProperty(placeHolder) ? userData[placeHolder] : match;
	});
}

function convertKeysToUpper(obj)
{
	var result = BX.util.objectClone(obj);

	for (let k in result)
	{
		const u = k.toUpperCase();

		if (u != k)
		{
			result[u] = result[k];
			delete result[k];
		}
	}
	return result;
}

function appendChildren(parent, children)
{
	children.forEach(child => parent.appendChild(child))
}

function containsVideoTrack(stream: MediaStream)
{
	if (!(stream instanceof MediaStream))
	{
		return false;
	}

	return stream.getVideoTracks().length > 0;
}

function hasHdVideo(stream: MediaStream)
{
	if (!(stream instanceof MediaStream) || stream.getVideoTracks().length === 0)
	{
		return false;
	}

	var videoTrack = stream.getVideoTracks()[0];
	var trackSettings = videoTrack.getSettings();

	return trackSettings.width >= 1280;
}

function findBestElementSize(width, height, userCount, minWidth, minHeight)
{
	minWidth = minWidth || 0;
	minHeight = minHeight || 0;
	let bestFilledArea = 0;

	for (let i = 1; i <= userCount; i++)
	{
		const area = getFilledArea(width, height, userCount, i);
		if (area.area > bestFilledArea && area.elementWidth > minWidth && area.elementHeight > minHeight)
		{
			bestFilledArea = area.area;
			var bestWidth = area.elementWidth;
			var bestHeight = area.elementHeight;
		}
		if (area.area < bestFilledArea)
		{
			break;
		}
	}
	if (bestFilledArea === 0)
	{
		bestWidth = minWidth;
		bestHeight = minHeight
	}
	return {width: bestWidth, height: bestHeight};
}

function getFilledArea(width, height, userCount, rowCount)
{
	const columnCount = Math.ceil(userCount / rowCount);
	const maxElementWidth = Math.floor(width / columnCount);
	const maxElementHeight = Math.floor(height / rowCount);

	const ratio = maxElementHeight / maxElementWidth;
	const neededRatio = 9 / 16;

	let expectedElementHeight;
	let expectedElementWidth;

	if (ratio < neededRatio)
	{
		expectedElementHeight = maxElementHeight;
		expectedElementWidth = Math.floor(maxElementWidth * (ratio / neededRatio));
	}
	else
	{
		expectedElementWidth = maxElementWidth;
		expectedElementHeight = Math.floor(maxElementHeight * (neededRatio / ratio));
	}

	//console.log(expectedElementWidth + 'x' + expectedElementHeight)
	var area = expectedElementWidth * expectedElementHeight * userCount;

	return {area: area, elementWidth: expectedElementWidth, elementHeight: expectedElementHeight};
}

const isWebRTCSupported = () =>
{
	return (typeof webkitRTCPeerConnection != 'undefined' || typeof mozRTCPeerConnection != 'undefined' || typeof RTCPeerConnection != 'undefined');
}

const isCallServerAllowed = () =>
{
	return BX.message('call_server_enabled') === 'Y'
}

const isFeedbackAllowed = () =>
{
	return BX.message('call_allow_feedback') === 'Y'
}

const shouldCollectStats = () =>
{
	return BX.message('call_collect_stats') === 'Y'
}

const shouldShowDocumentButton = () =>
{
	return BX.message('call_docs_status') !== 'N' || BX.message('call_resumes_status') !== 'N';
}

const getDocumentsArticleCode = () =>
{
	if (!BX.message('call_docs_status').startsWith('L'))
	{
		return false;
	}

	return BX.message('call_docs_status').substr(2);
}

const getResumesArticleCode = () =>
{
	if (!BX.message('call_resumes_status').startsWith('L'))
	{
		return false;
	}

	return BX.message('call_resumes_status').substr(2);
}

const getUserLimit = () =>
{
	if (isCallServerAllowed())
	{
		return parseInt(BX.message('call_server_max_users'));
	}

	return parseInt(BX.message('turn_server_max_users'));
}

function getLogMessage ()
{
	let text = getDateForLog();

	for (let i = 0; i < arguments.length; i++)
	{
		if (arguments[i] instanceof Error)
		{
			text = arguments[i].message + "\n" + arguments[i].stack
		}
		else
		{
			try
			{
				text = text + ' | ' + (typeof (arguments[i]) == 'object' ? JSON.stringify(arguments[i]) : arguments[i]);
			} catch (e)
			{
				text = text + ' | (circular structure)';
			}
		}
	}

	return text;
}

const getUuidv4 = () =>
{
	return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, (c) =>
	{
		const r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
		return v.toString(16);
	});
}

function reportConnectionResult(callId, connectionResult)
{
	BX.ajax.runAction("im.call.reportConnection", {
		data: {
			callId: callId,
			connectionResult: connectionResult
		}
	})
}

function sendTelemetryEvent(options)
{
	const url = (document.location.protocol == "https:" ? "https://" : "http://") + "bitrix.info/bx_stat";
	const req = new XMLHttpRequest();
	req.open("POST", url, true);
	req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
	req.withCredentials = true;
	options.op = "call";
	options.d = document.location.host;
	const query = BX.util.buildQueryString(options);
	req.send(query);
}

const isDesktop = () =>
{
	return typeof (BXDesktopSystem) != "undefined" || typeof (BXDesktopWindow) != "undefined";
}

const getBrowserForStatistics = () =>
{
	if (BX.browser.IsOpera())
	{
		return 'opera';
	}
	if (BX.browser.IsChrome())
	{
		return 'chrome';
	}
	if (BX.browser.IsFirefox())
	{
		return 'firefox';
	}
	if (BX.browser.IsSafari())
	{
		return 'safari';
	}
	return 'other';
}

function isBlank(url)
{
	return typeof (url) !== "string" || url == "" || url.endsWith(blankAvatar);
}

function stopMediaStream(mediaStream)
{
	if (!mediaStream instanceof MediaStream)
	{
		return;
	}

	mediaStream.getTracks().forEach(function (track)
	{
		track.stop()
	});
}

export default {
	updateUserData,
	setUserData,
	getDateForLog,
	getTimeForLog,
	lpad,
	getUser,
	getUserCached,
	getUsers,
	getUserName,
	getUserAvatar,
	getUserAvatars,
	isAvatarBlank,
	getCustomMessage,
	convertKeysToUpper,
	appendChildren,
	containsVideoTrack,
	hasHdVideo,
	findBestElementSize,
	getFilledArea,
	isWebRTCSupported,
	isCallServerAllowed,
	isFeedbackAllowed,
	shouldCollectStats,
	shouldShowDocumentButton,
	getDocumentsArticleCode,
	getResumesArticleCode,
	getUserLimit,
	getLogMessage,
	getUuidv4,
	reportConnectionResult,
	sendTelemetryEvent,
	isDesktop,
	getBrowserForStatistics,
	isBlank,
	stopMediaStream,
}