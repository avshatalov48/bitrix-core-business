;(function()
{
	var lsKey = {
		defaultMicrophone: 'bx-im-settings-default-microphone',
		defaultCamera: 'bx-im-settings-default-camera',
		defaultSpeaker: 'bx-im-settings-default-speaker',
		enableMicAutoParameters: 'bx-im-settings-enable-mic-auto-parameters',
		preferHd: 'bx-im-settings-camera-prefer-hd'
	};

	var HardwareManager = function()
	{
		this.initialized = false;
		this._currentDeviceList = [];
		this.updating = false;

		Object.defineProperty(this, "cameraList", {get: this._getDeviceMap.bind(this, "videoinput")});
		Object.defineProperty(this, "microphoneList", {get: function(){return this._getDeviceMap("audioinput")}});
		Object.defineProperty(this, "audioOutputList", {get: function(){return this._getDeviceMap("audiooutput")}});

		Object.defineProperty(this, "defaultMicrophone", {
			get: function() {
				var microphoneId = localStorage ? localStorage.getItem(lsKey.defaultMicrophone) : '';
				return this.microphoneList[microphoneId] ? microphoneId : '';
			},
			set: function(microphoneId) {
				if (localStorage)
				{
					localStorage.setItem(lsKey.defaultMicrophone, microphoneId)
				}
			}
		});
		Object.defineProperty(this, "defaultCamera", {
			get: function() {
				var cameraId = localStorage ? localStorage.getItem(lsKey.defaultCamera) : '';
				return this.cameraList[cameraId] ? cameraId : '';
			},
			set: function(cameraId) {
				if (localStorage)
				{
					localStorage.setItem(lsKey.defaultCamera, cameraId)
				}
			}
		});
		Object.defineProperty(this, "defaultSpeaker", {
			get: function() {
				var speakerId = localStorage ? localStorage.getItem(lsKey.defaultSpeaker) : '';
				return this.audioOutputList[speakerId] ? speakerId : '';
			},
			set: function(speakerId) {
				if (localStorage)
				{
					localStorage.setItem(lsKey.defaultSpeaker, speakerId)
				}
			}

		});
		Object.defineProperty(this,"enableMicAutoParameters", {
			get: function() {
				return localStorage ? (localStorage.getItem(lsKey.enableMicAutoParameters) !== 'N') : true;
			},
			set: function(enableMicAutoParameters) {
				if (localStorage)
				{
					localStorage.setItem(lsKey.enableMicAutoParameters, enableMicAutoParameters ? 'Y' : 'N')
				}
			}
		});
		Object.defineProperty(this, "preferHdQuality", {
			get: function() {
				return localStorage ? (localStorage.getItem(lsKey.preferHd) !== 'N') : true;
			},
			set: function(preferHdQuality) {
				if (localStorage)
				{
					localStorage.setItem(lsKey.preferHd, preferHdQuality ? 'Y' : 'N')
				}
			}
		});
	};

	HardwareManager.prototype = {
		init: function()
		{
			return new Promise(function(resolve, reject)
			{
				if(this.initialized)
				{
					return resolve();
				}

				this.checkMicrophone().then(this.enumerateDevices.bind(this)).then(function(deviceList)
				{
					this._currentDeviceList = deviceList;

					navigator.mediaDevices.addEventListener('devicechange', BX.debounce(this.onNavigatorDeviceChanged.bind(this), 500));
					this.initialized = true;
					resolve();
				}.bind(this)).catch(reject);
			}.bind(this))
		},

		checkMicrophone: function()
		{
			return new Promise(function(resolve, reject)
			{
				navigator.mediaDevices.getUserMedia({audio: true}).then(function(stream)
				{
					stream.getAudioTracks().forEach(function(track)
					{
						track.stop()
					});
					resolve();
				}).catch(reject);
			});
		},

		enumerateDevices: function()
		{
			return new Promise(function(resolve, reject)
			{
				if(!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices)
				{
					return reject("NO_WEBRTC");
				}
				navigator.mediaDevices.enumerateDevices().then(function(devices)
				{
					resolve(devices);
				}.bind(this))
			}.bind(this));
		},

		hasCamera: function()
		{
			if(!this.initialized)
			{
				throw new Error("HardwareManager is not initialized yet");
			}

			return Object.keys(this.cameraList).length > 0;
		},

		getMicrophoneList: function()
		{
			if(!this.initialized)
			{
				throw new Error("HardwareManager is not initialized yet");
			}
			return Object.values(this._currentDeviceList).filter(function(deviceInfo) {
				return deviceInfo.kind == "audioinput";
			});
		},

		getCameraList: function()
		{
			if(!this.initialized)
			{
				throw new Error("HardwareManager is not initialized yet");
			}
			return Object.values(this._currentDeviceList).filter(function(deviceInfo) {
				return deviceInfo.kind == "videoinput";
			});
		},

		getSpeakerList: function()
		{
			if(!this.initialized)
			{
				throw new Error("HardwareManager is not initialized yet");
			}
			return Object.values(this._currentDeviceList).filter(function(deviceInfo) {
				return deviceInfo.kind == "audiooutput";
			});
		},

		canSelectSpeaker: function()
		{
			return Object.keys(this.audioOutputList).length > 0 && 'setSinkId' in HTMLMediaElement.prototype;
		},

		updateDeviceList: function(e)
		{
			if(this.updating)
			{
				return;
			}
			this.updating = true;
			var removedDevices = this._currentDeviceList;
			var addedDevices = [];

			navigator.mediaDevices.enumerateDevices().then(function(devices)
			{
				devices.forEach(function(deviceInfo)
				{
					var index = removedDevices.findIndex(function(dev){
						return dev.kind == deviceInfo.kind && dev.deviceId == deviceInfo.deviceId;
					});
					if (index != -1)
					{
						// device found in previous version
						removedDevices.splice(index, 1);
					}
					else
					{
						addedDevices.push(deviceInfo);
					}
				}, this);

				BX.onCustomEvent(this, "HardwareManager::deviceChange", {
					added: addedDevices,
					removed: removedDevices
				});

				this._currentDeviceList = devices;
				this.updating = false;
			}.bind(this))
		},

		onNavigatorDeviceChanged: function(e)
		{
			if (!this.initialized)
			{
				return;
			}

			this.updateDeviceList();
		},

		_getDeviceMap: function(deviceKind)
		{
			var result = {};
			if(!this.initialized)
			{
				throw new Error("HardwareManager is not initialized yet");
			}
			for (var i = 0; i < this._currentDeviceList.length; i++)
			{
				if(this._currentDeviceList[i].kind == deviceKind)
				{
					result[this._currentDeviceList[i].deviceId] = this._currentDeviceList[i].label;
				}
			}
			return result;
		}
	};

	BX.Call.Hardware = new HardwareManager();
})();