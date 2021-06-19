;(function()
{
	BX.namespace("BX.Call");

	if(BX.Call.Util)
	{
		return;
	}

	var blankAvatar = '/bitrix/js/im/images/blank.gif';

	BX.Call.Util = {
		userData: {},
		usersInProcess: {},

		updateUserData: function(callId, users)
		{
			var usersToUpdate = [];
			var self = this;
			for (var i = 0; i < users.length; i++)
			{
				if(this.userData.hasOwnProperty(users[i]))
				{
					continue;
				}

				usersToUpdate.push(users[i]);
			}

			var result =  new Promise(function(resolve, reject)
			{
				if(usersToUpdate.length === 0)
				{
					return resolve();
				}

				BX.CallEngine.getRestClient().callMethod("im.call.getUsers", {callId: callId, userIds: usersToUpdate}).then(function(response)
				{
					var result = BX.type.isPlainObject(response.answer.result) ? response.answer.result : {};
					users.forEach(function(userId)
					{
						if(result[userId])
						{
							self.userData[userId] = result[userId];
						}
						delete self.usersInProcess[userId];
					});
					resolve();

				}).catch(function(error)
				{
					reject(error.answer);
				});
			});

			for (var i = 0; i < usersToUpdate.length; i++)
			{
				this.usersInProcess[usersToUpdate[i]] = result;
			}
			return result;
		},

		setUserData: function(userData)
		{
			for (var userId in userData)
			{
				this.userData[userId] = userData[userId];
			}
		},

		getDateForLog: function()
		{
			var d = new Date();

			return d.getFullYear() + "-" + this.lpad(d.getMonth() + 1, 2, '0') + "-" + this.lpad(d.getDate(), 2, '0') + " " + this.lpad(d.getHours(), 2, '0') + ":" + this.lpad(d.getMinutes(), 2, '0') + ":" + this.lpad(d.getSeconds(), 2, '0') + "." + d.getMilliseconds();
		},

		getTimeForLog: function()
		{
			var d = new Date();

			return this.lpad(d.getHours(), 2, '0') + ":" + this.lpad(d.getMinutes(), 2, '0') + ":" + this.lpad(d.getSeconds(), 2, '0') + "." + d.getMilliseconds();
		},

		lpad: function(str, length, chr)
		{
			str = str.toString();
			chr = chr || ' ';

			if(str.length > length)
			{
				return str;
			}

			var result = '';
			for(var i = 0; i < length - str.length; i++)
			{
				result += chr;
			}

			return result + str;
		},

		getUser: function(callId, userId)
		{
			var self = this;
			return new Promise(function(resolve, reject)
			{
				if(self.userData.hasOwnProperty(userId))
				{
					return resolve(self.userData[userId]);
				}
				else if(self.usersInProcess.hasOwnProperty(userId))
				{
					self.usersInProcess[userId].then(function()
					{
						return resolve(self.userData[userId]);
					});
				}
				else
				{
					self.updateUserData(callId, [userId]).then(function()
					{
						return resolve(self.userData[userId]);
					});
				}
			});
		},

		getUsers: function(callId, users)
		{
			var self = this;
			return new Promise(function(resolve, reject)
			{
				self.updateUserData(callId, users).then(function()
				{
					var result = {};
					users.forEach(function(userId)
					{
						result[userId] = self.userData[userId] || {};
					});
					return resolve(result);
				});
			});
		},

		getUserName: function(callId, userId)
		{
			var self = this;
			return new Promise(function(resolve, reject)
			{
				if(self.userData.hasOwnProperty(userId))
				{
					return resolve(self.userData[userId].name ? self.userData[userId].name : '');
				}
				else if(self.usersInProcess.hasOwnProperty(userId))
				{
					self.usersInProcess[userId].then(function()
					{
						return resolve(self.userData[userId].name ? self.userData[userId].name : '');
					});
				}
				else
				{
					self.updateUserData(callId,[userId]).then(function()
					{
						return resolve(self.userData[userId].name ? self.userData[userId].name : '');
					});
				}
			});
		},

		getUserAvatar: function(callId, userId)
		{
			var self = this;
			return new Promise(function(resolve, reject)
			{
				if(self.userData.hasOwnProperty(userId))
				{
					return resolve(self.userData[userId].avatar_hr && !isBlank(self.userData[userId].avatar_hr) ? self.userData[userId].avatar_hr : '');
				}
				else if(self.usersInProcess.hasOwnProperty(userId))
				{
					self.usersInProcess[userId].then(function()
					{
						return resolve(self.userData[userId].avatar_hr && !isBlank(self.userData[userId].avatar_hr) ? self.userData[userId].avatar_hr : '');
					});
				}
				else
				{
					self.updateUserData(callId,[userId]).then(function()
					{
						return resolve(self.userData[userId].avatar_hr && !isBlank(self.userData[userId].avatar_hr) ? self.userData[userId].avatar_hr : '');
					});
				}
			});
		},

		getUserAvatars: function(callId, users)
		{
			var self = this;
			return new Promise(function(resolve, reject)
			{
				self.updateUserData(callId, users).then(function()
				{
					var result = {};
					users.forEach(function(userId)
					{
						result[userId] = self.userData[userId].avatar_hr && !isBlank(self.userData[userId].avatar_hr) ? self.userData[userId].avatar_hr : ''
					});
					return resolve(result);
				});
			});
		},

		isAvatarBlank: function(url)
		{

			var result =  isBlank(url);
			return result;
		},

		getCustomMessage: function(message, userData)
		{
			var messageText;
			if(!BX.type.isPlainObject(userData))
			{
				userData = {};
			}

			if(userData.gender && BX.message.hasOwnProperty(message + '_' + userData.gender))
			{
				messageText = BX.message(message + '_' + userData.gender);
			}
			else
			{
				messageText = BX.message(message);
			}

			userData = this.convertKeysToUpper(userData);

			return messageText.replace(/#.+?#/gm, function(match)
			{
				var placeHolder = match.substr(1, match.length - 2);
				return userData.hasOwnProperty(placeHolder) ? userData[placeHolder] : match;
			});
		},

		convertKeysToUpper: function(obj)
		{
			var result = BX.util.objectClone(obj);

			for(var k in result)
			{
				var u = k.toUpperCase();

				if(u != k)
				{
					result[u] = result[k];
					delete result[k];
				}
			}
			return result;
		},

		appendChildren: function(parent, children)
		{
			children.forEach(function(child)
			{
				parent.appendChild(child);
			})
		},

		containsVideoTrack: function(stream)
		{
			if(!(stream instanceof MediaStream))
				return false;

			return stream.getVideoTracks().length > 0;
		},

		hasHdVideo: function(stream)
		{
			if(!(stream instanceof MediaStream) || stream.getVideoTracks().length === 0)
				return false;

			var videoTrack = stream.getVideoTracks()[0];
			var trackSettings = videoTrack.getSettings();

			return trackSettings.width >= 1280;
		},

		findBestElementSize: function(width, height, userCount, minWidth, minHeight)
		{
			minWidth = minWidth || 0;
			minHeight = minHeight || 0;
			var bestFilledArea = 0;

			for (var i = 1; i <= userCount; i++)
			{
				var area = this.getFilledArea(width, height, userCount, i);
				if(area.area > bestFilledArea && area.elementWidth > minWidth && area.elementHeight > minHeight)
				{
					bestFilledArea = area.area;
					var bestWidth = area.elementWidth;
					var bestHeight = area.elementHeight;
				}
				if(area.area < bestFilledArea)
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
		},

		getFilledArea: function(width, height, userCount, rowCount)
		{
			var columnCount = Math.ceil(userCount / rowCount);
			var maxElementWidth = Math.floor(width / columnCount);
			var maxElementHeight = Math.floor(height / rowCount);

			var ratio = maxElementHeight / maxElementWidth;
			var neededRatio = 9 / 16;

			var expectedElementHeight;
			var expectedElementWidth;

			if(ratio < neededRatio)
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
		},

		isWebRTCSupported: function()
		{
			return (typeof webkitRTCPeerConnection != 'undefined' || typeof mozRTCPeerConnection != 'undefined' || typeof RTCPeerConnection != 'undefined');
		},

		isCallServerAllowed: function()
		{
			return BX.message('call_server_enabled') === 'Y'
		},

		getUserLimit: function()
		{
			if (this.isCallServerAllowed())
			{
				return parseInt(BX.message('call_server_max_users'));
			}

			return parseInt(BX.message('turn_server_max_users'));
		},

		getLogMessage: function()
		{
			var text = BX.Call.Util.getDateForLog();

			for (var i = 0; i < arguments.length; i++)
			{
				if(arguments[i] instanceof Error)
				{
					text = arguments[i].message + "\n" + arguments[i].stack
				}
				else
				{
					try
					{
						text = text+' | '+(typeof(arguments[i]) == 'object'? JSON.stringify(arguments[i]): arguments[i]);
					}
					catch (e)
					{
						text = text+' | (circular structure)';
					}
				}
			}

			return text;
		},

		getUuidv4: function()
		{
			return 'xxxxxxxx-xxxx-4xxx-yxxx-xxxxxxxxxxxx'.replace(/[xy]/g, function(c) {
				var r = Math.random() * 16 | 0, v = c == 'x' ? r : (r & 0x3 | 0x8);
				return v.toString(16);
			});
		},

		alterSDP: function(sdp, options)
		{
			if(!options)
			{
				return;
			}

			var sdpLines = sdp.split("\n");
			var codecRtpMaps = [];

			var videoLineIndex = false;
			for (var i = 0; i < sdpLines.length; i++)
			{
				var line = sdpLines[i];

				videoLineIndex = videoLineIndex || (line.match(/m=video/) !== null ? i : false);
				if(!videoLineIndex)
				{
					continue;
				}

				var match = /a=rtpmap:(\d+)\s(.+)/.exec(line);
				if (match)
				{
					codecRtpMaps.push({
						rtpmap: match[1],
						codec: match[2]
					})
				}
			}

			if(!videoLineIndex)
			{
				return;
			}

			sdpLines[videoLineIndex] = sortVideoLine(sdpLines[videoLineIndex], codecRtpMaps, options);
			return sdpLines.join("\n");
		},

		sendTelemetryEvent: function(options)
		{
			var url = (document.location.protocol == "https:" ? "https://" : "http://") + "bitrix.info/bx_stat";
			var req =  new XMLHttpRequest();
			req.open("POST", url, true);
			req.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
			req.withCredentials = true;
			options.op = "call";
			options.d = document.location.host;
			var query = BX.util.buildQueryString(options);
			req.send(query);
		},

		isDesktop: function()
		{
			return typeof(BXDesktopSystem) != "undefined" || typeof(BXDesktopWindow) != "undefined";
		},

		getBrowserForStatistics: function ()
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
		},
	};

	function sortVideoLine(videoLine, rtpMaps, options)
	{
		debugger;
		var parsedVideoLine = videoLine.split(" ");
		var codecsSlice = parsedVideoLine.slice(3);

		var rtpMapToCodec = {};
		rtpMaps.forEach(function(rtpMap)
		{
			rtpMapToCodec[rtpMap.rtpmap] = rtpMap.codec;
		});

		codecsSlice.sort(function(a, b)
		{
			a = a.trim();
			b = b.trim();

			var codecA = rtpMapToCodec[a];
			var codecB = rtpMapToCodec[b];

			if(codecA.substr(0, 4) === "H264" && codecB.substr(0, 4) !== "H264")
			{
				return -1;
			}
			else if (codecA.substr(0, 4) !== "H264" && codecB.substr(0, 4) === "H264")
			{
				return 1;
			}
			else
			{
				return a - b;
			}
		});

		var result = parsedVideoLine.slice(0, 3).concat(codecsSlice);

		return result.join(" ")
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

		mediaStream.getTracks().forEach(function(track)
		{
			track.stop()
		});
	}
})();