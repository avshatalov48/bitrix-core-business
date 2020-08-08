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

		updateUserData: function(users)
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

				BX.CallEngine.getRestClient().callMethod("im.user.list.get", {"ID": usersToUpdate, "AVATAR_HR" : "Y"}).then(function(response)
				{
					var result = response.answer.result;

					if(BX.type.isPlainObject(result))
					{
						for (var userId in result)
						{
							self.userData[userId] = result[userId];
							delete self.usersInProcess[userId];
						}
					}
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

		getDateForLog: function()
		{
			var d = new Date();

			return d.getFullYear() + "-" + this.lpad(d.getMonth(), 2, '0') + "-" + this.lpad(d.getDate(), 2, '0') + " " + this.lpad(d.getHours(), 2, '0') + ":" + this.lpad(d.getMinutes(), 2, '0') + ":" + this.lpad(d.getSeconds(), 2, '0') + "." + d.getMilliseconds();
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

		getUser: function(userId)
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
					self.updateUserData([userId]).then(function()
					{
						return resolve(self.userData[userId]);
					});
				}
			});
		},

		getUserName: function(userId)
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
					self.updateUserData([userId]).then(function()
					{
						return resolve(self.userData[userId].name ? self.userData[userId].name : '');
					});
				}
			});
		},

		getUserAvatar: function(userId)
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
					self.updateUserData([userId]).then(function()
					{
						return resolve(self.userData[userId].avatar_hr && !isBlank(self.userData[userId].avatar_hr) ? self.userData[userId].avatar_hr : '');
					});
				}
			});
		},

		getUserAvatars: function(users)
		{
			var self = this;
			return new Promise(function(resolve, reject)
			{
				self.updateUserData(users).then(function()
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

		findBestElementSize: function(width, height, userCount)
		{
			var bestFilledArea = 0;

			for (var i = 1; i <= userCount; i++)
			{
				var area = this.getFilledArea(width, height, userCount, i);
				if(area.area > bestFilledArea)
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
			return {width: bestWidth, height: bestHeight}
		},

		getFilledArea: function(width, height, userCount, rowCount)
		{
			var columnCount = Math.ceil(userCount / rowCount);
			var maxElementWidth = width / columnCount;
			var maxElementHeight = height / rowCount;

			var ratio = maxElementHeight / maxElementWidth;
			var neededRatio = 9 / 16;

			var expectedElementHeight;
			var expectedElementWidth;

			if(ratio < neededRatio)
			{
				expectedElementHeight = maxElementHeight;
				expectedElementWidth = maxElementWidth * (ratio / neededRatio);
			}
			else
			{
				expectedElementWidth = maxElementWidth;
				expectedElementHeight = maxElementHeight * (neededRatio / ratio);
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

		/**
		 * Returns bitrate cap for the required video quality level
		 * @param quality
		 * @returns {number}
		 */
		getMaxBitrate: function(quality)
		{
			switch (quality)
			{
				case BX.Call.Quality.VeryHigh:
					return 0;
				case BX.Call.Quality.High:
					return 1000;
				case BX.Call.Quality.Medium:
					return 500;
				case BX.Call.Quality.Low:
					return 250;
				case BX.Call.Quality.VeryLow:
					return 100;
			}
		},

		/**
		 * Returns max allowed video resolution for the selected quality level
		 * @param quality
		 * @param allowHd
		 * @returns {{width: number, height: number}}
		 */
		getMaxResolution: function(quality, allowHd)
		{
			switch (quality)
			{
				case BX.Call.Quality.VeryHigh:
					return allowHd ? {width: 1280, height: 720} : {width: 640, height: 360};
				case BX.Call.Quality.High:
					return {width: 640, height: 360};
				case BX.Call.Quality.Medium:
					return {width: 320, height: 180};
				case BX.Call.Quality.Low:
					return {width: 160, height: 90};
				case BX.Call.Quality.VeryLow:
					return {width: 160, height: 90};
			}
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
		}
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

})();