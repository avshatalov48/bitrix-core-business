;(function()
{
	BX.namespace("BX.Call");

	if(BX.Call.Util)
	{
		return;
	}

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

				BX.rest.callMethod("im.user.list.get", {"ID": usersToUpdate, "AVATAR_HR" : "Y"}).then(function(response)
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
					return resolve(self.userData[userId].avatar_hr ? self.userData[userId].avatar_hr : '');
				}
				else if(self.usersInProcess.hasOwnProperty(userId))
				{
					self.usersInProcess[userId].then(function()
					{
						return resolve(self.userData[userId].avatar_hr ? self.userData[userId].avatar_hr : '');
					});
				}
				else
				{
					self.updateUserData([userId]).then(function()
					{
						return resolve(self.userData[userId].avatar_hr ? self.userData[userId].avatar_hr : '');
					});
				}
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

		findRowCount: function(width, height, userCount)
		{
			var result = 0;
			var bestFilledArea = 0;

			for (var i = 1; i <= userCount; i++)
			{
				var candidateArea = this.getFilledArea(width, height, userCount, i);
				if(candidateArea > bestFilledArea)
				{
					result = i;
					bestFilledArea = candidateArea;
				}
			}
			return result;
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

			var expectedArea = expectedElementWidth * expectedElementHeight * userCount;

			return expectedArea;
		}
	};

})();