;(function()
{
	var lsKey = {
		defaultMicrophone: 'bx-im-settings-default-microphone',
		defaultCamera: 'bx-im-settings-default-camera',
		defaultSpeaker: 'bx-im-settings-default-speaker',
		enableMicAutoParameters: 'bx-im-settings-enable-mic-auto-parameters',
		preferHd: 'bx-im-settings-camera-prefer-hd'
	};

	var Events = {
		initialized: "initialized",
		deviceChanged: "deviceChange",
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
		this.eventEmitter = new BX.Event.EventEmitter(this, "HardwareManager");
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

				this.enumerateDevices().then(function(deviceList)
				{
					this._currentDeviceList = this.filterDeviceList(deviceList);

					navigator.mediaDevices.addEventListener('devicechange', BX.debounce(this.onNavigatorDeviceChanged.bind(this), 500));
					this.initialized = true;
					this.eventEmitter.emit(Events.initialized, {});
					resolve();
				}.bind(this)).catch(reject);
			}.bind(this))
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
			return 'setSinkId' in HTMLMediaElement.prototype;
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

			var shouldSkipDeviceChangedEvent = this._currentDeviceList.every(function(deviceInfo)
			{
				return deviceInfo.deviceId == "" && deviceInfo.label == "";
			});

			navigator.mediaDevices.enumerateDevices().then(function(devices)
			{
				devices = this.filterDeviceList(devices);
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

				if (!shouldSkipDeviceChangedEvent)
				{
					this.eventEmitter.emit(Events.deviceChanged, {
						added: addedDevices,
						removed: removedDevices
					});
				}

				this._currentDeviceList = devices;
				this.updating = false;
			}.bind(this))
		},

		filterDeviceList: function(browserDeviceList)
		{
			return browserDeviceList.filter(function(device)
			{
				if (device.kind == "audioinput" && device.deviceId == "default")
				{
					return false;
				}
				if (device.kind == "audioinput" && device.deviceId == "communications")
				{
					return false;
				}
				if (device.kind == "audiooutput" && device.deviceId == "default")
				{
					return false;
				}

				return true;
			});
		},

		onNavigatorDeviceChanged: function(e)
		{
			if (!this.initialized)
			{
				return;
			}

			this.updateDeviceList();
		},

		subscribe: function(eventName, listener) {
			return this.eventEmitter.subscribe(eventName, listener);
		},

		unsubscribe: function(eventName, listener)
		{
			return this.eventEmitter.unsubscribe(eventName, listener);
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
		},
	};

	var BackgroundDialog = function() {};

	BackgroundDialog.prototype.isAvailable = function()
	{
		if (BX.getClass('BX.desktop'))
		{
			return BX.desktop.getApiVersion() >= 52;
		}
		else if (BX.getClass("BX.Messenger.Lib.Utils.platform"))
		{
			return BX.Messenger.Lib.Utils.platform.getDesktopVersion() >= 52;
		}

		return false;

	};

	BackgroundDialog.prototype.open = function()
	{
		if (!this.isAvailable())
		{
			if (window.BX.Helper)
			{
				window.BX.Helper.show("redirect=detail&code=12398124");
			}

			return false;
		}

		var html =
			'<div id="bx-desktop-loader" class="bx-desktop-loader-wrap">'+
				'<div class="bx-desktop-loader">'+
					'<svg class="bx-desktop-loader-circular" viewBox="25 25 50 50">'+
						'<circle class="bx-desktop-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>'+
					'</svg>'+
				'</div>'+
			'</div>' +
			'<div id="placeholder"></div>'
		;

		var js = 'BX.Runtime.loadExtension("im.component.settings.call-background").then(function(exports) {'+
			'BX.Vue.create({'+
				'el: document.getElementById("placeholder"),'+
				'template: \'<bx-im-component-settings-call-background :isDesktop="true"/>\','+
			'});'+
		'});';

		(opener||top).BX.desktop.createWindow("callBackground", BX.delegate(function(controller)
		{
			controller.SetProperty("title", BX.message('BXD_CALL_BG_TITLE'));
			controller.SetProperty("clientSize", { Width: 970, Height: 660 });
			controller.SetProperty("minClientSize", { Width: 970, Height: 660 });
			controller.SetProperty("backgroundColor", "#2B3038");
			controller.ExecuteCommand("html.load", (opener||top).BXIM.desktop.getHtmlPage(html, js, false));

		},this));

		return true;
	};

	BX.Call.Hardware = new HardwareManager;
	BX.Call.Hardware.Events = Events;
	BX.Call.Hardware.BackgroundDialog = new BackgroundDialog;
})();