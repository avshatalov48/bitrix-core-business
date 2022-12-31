;(function ()
{
	const lsKey = {
		defaultMicrophone: 'bx-im-settings-default-microphone',
		defaultCamera: 'bx-im-settings-default-camera',
		defaultSpeaker: 'bx-im-settings-default-speaker',
		enableMicAutoParameters: 'bx-im-settings-enable-mic-auto-parameters',
		preferHd: 'bx-im-settings-camera-prefer-hd',
		enableMirroring: 'bx-im-settings-camera-enable-mirroring'
	};

	const Events = {
		initialized: "initialized",
		deviceChanged: "deviceChange",
		onChangeMirroringVideo: "onChangeMirroringVideo",
	};

	class HardwareManager
	{
		constructor()
		{
			this.initialized = false;
			this._currentDeviceList = [];
			this.updating = false;

			this.eventEmitter = new BX.Event.EventEmitter(this, "HardwareManager");
		}

		init()
		{
			if (this.initialized)
			{
				return Promise.resolve();
			}

			if (this.initPromise)
			{
				return this.initPromise;
			}

			this.initPromise = new Promise((resolve, reject) =>
			{
				this.enumerateDevices()
					.then((deviceList) =>
					{
						this._currentDeviceList = this.filterDeviceList(deviceList);

						navigator.mediaDevices.addEventListener('devicechange', BX.debounce(this.onNavigatorDeviceChanged.bind(this), 500));
						this.initialized = true;
						this.initPromise = null;
						this.eventEmitter.emit(Events.initialized, {});
						resolve();
					})
					.catch((e) =>
					{
						this.initPromise = null;
						reject(e)
					});
			})

			return this.initPromise;
		}

		enumerateDevices()
		{
			return new Promise((resolve, reject) =>
			{
				if (!navigator.mediaDevices || !navigator.mediaDevices.enumerateDevices)
				{
					return reject("NO_WEBRTC");
				}
				navigator.mediaDevices.enumerateDevices().then(devices => resolve(devices))
			});
		}

		get cameraList()
		{
			return this._getDeviceMap("videoinput");
		}

		get microphoneList()
		{
			return this._getDeviceMap("audioinput");
		}

		get audioOutputList()
		{
			return this._getDeviceMap("audiooutput");
		}

		get defaultMicrophone()
		{
			const microphoneId = localStorage ? localStorage.getItem(lsKey.defaultMicrophone) : '';
			return this.microphoneList[microphoneId] ? microphoneId : '';
		}

		set defaultMicrophone(microphoneId)
		{
			if (localStorage)
			{
				localStorage.setItem(lsKey.defaultMicrophone, microphoneId)
			}
		}

		get defaultCamera()
		{
			const cameraId = localStorage ? localStorage.getItem(lsKey.defaultCamera) : '';
			return this.cameraList[cameraId] ? cameraId : '';
		}

		set defaultCamera(cameraId)
		{
			if (localStorage)
			{
				localStorage.setItem(lsKey.defaultCamera, cameraId)
			}
		}

		get defaultSpeaker()
		{
			const speakerId = localStorage ? localStorage.getItem(lsKey.defaultSpeaker) : '';
			return this.audioOutputList[speakerId] ? speakerId : '';
		}

		set defaultSpeaker(speakerId)
		{
			if (localStorage)
			{
				localStorage.setItem(lsKey.defaultSpeaker, speakerId)
			}
		}

		get enableMicAutoParameters()
		{
			return localStorage ? (localStorage.getItem(lsKey.enableMicAutoParameters) !== 'N') : true;
		}

		set enableMicAutoParameters(enableMicAutoParameters)
		{
			if (localStorage)
			{
				localStorage.setItem(lsKey.enableMicAutoParameters, enableMicAutoParameters ? 'Y' : 'N')
			}
		}

		get preferHdQuality()
		{
			return localStorage ? (localStorage.getItem(lsKey.preferHd) !== 'N') : true;
		}

		set preferHdQuality(preferHdQuality)
		{
			if (localStorage)
			{
				localStorage.setItem(lsKey.preferHd, preferHdQuality ? 'Y' : 'N')
			}
		}

		get enableMirroring()
		{
			return localStorage ? (localStorage.getItem(lsKey.enableMirroring) !== 'N') : true;
		}

		set enableMirroring(enableMirroring)
		{
			if (this.enableMirroring !== enableMirroring)
			{
				this.eventEmitter.emit(Events.onChangeMirroringVideo, {enableMirroring: enableMirroring});

				if (BX.desktop)
				{
					BX.desktop.onCustomEvent(Events.onChangeMirroringVideo, [enableMirroring]);
				}
				if (localStorage)
				{
					localStorage.setItem(lsKey.enableMirroring, enableMirroring ? 'Y' : 'N');
				}
			}
		}

		hasCamera()
		{
			if (!this.initialized)
			{
				throw new Error("HardwareManager is not initialized yet");
			}

			return Object.keys(this.cameraList).length > 0;
		}

		getMicrophoneList()
		{
			if (!this.initialized)
			{
				throw new Error("HardwareManager is not initialized yet");
			}

			return Object.values(this._currentDeviceList).filter(deviceInfo => deviceInfo.kind == "audioinput")
		}

		getCameraList()
		{
			if (!this.initialized)
			{
				throw new Error("HardwareManager is not initialized yet");
			}

			return Object.values(this._currentDeviceList).filter(deviceInfo => deviceInfo.kind == "videoinput")
		}

		getSpeakerList()
		{
			if (!this.initialized)
			{
				throw new Error("HardwareManager is not initialized yet");
			}

			return Object.values(this._currentDeviceList).filter(deviceInfo => deviceInfo.kind == "audiooutput")
		}

		canSelectSpeaker()
		{
			return 'setSinkId' in HTMLMediaElement.prototype;
		}

		updateDeviceList(e)
		{
			if (this.updating)
			{
				return;
			}
			this.updating = true;
			let removedDevices = this._currentDeviceList;
			let addedDevices = [];

			const shouldSkipDeviceChangedEvent = this._currentDeviceList.every(deviceInfo => deviceInfo.deviceId == "" && deviceInfo.label == "");

			navigator.mediaDevices.enumerateDevices().then(devices =>
			{
				devices = this.filterDeviceList(devices);
				devices.forEach(deviceInfo =>
				{
					const index = removedDevices.findIndex(dev => dev.kind === deviceInfo.kind && dev.deviceId === deviceInfo.deviceId)
					if (index != -1)
					{
						// device found in previous version
						removedDevices.splice(index, 1);
					}
					else
					{
						addedDevices.push(deviceInfo);
					}
				});

				if (!shouldSkipDeviceChangedEvent)
				{
					this.eventEmitter.emit(Events.deviceChanged, {
						added: addedDevices,
						removed: removedDevices
					});
				}

				this._currentDeviceList = devices;
				this.updating = false;
			})
		}

		filterDeviceList(browserDeviceList)
		{
			return browserDeviceList.filter(device =>
			{
				switch (device.kind)
				{
					case "audioinput":
						return device.deviceId !== "default" && device.deviceId !== "communications";
					case "audiooutput":
						return device.deviceId !== "default";
					default:
						return true;
				}
			})
		}

		onNavigatorDeviceChanged(e)
		{
			if (!this.initialized)
			{
				return;
			}

			this.updateDeviceList();
		}

		subscribe(eventName, listener)
		{
			return this.eventEmitter.subscribe(eventName, listener);
		}

		unsubscribe(eventName, listener)
		{
			return this.eventEmitter.unsubscribe(eventName, listener);
		}

		_getDeviceMap(deviceKind)
		{
			let result = {};
			if (!this.initialized)
			{
				throw new Error("HardwareManager is not initialized yet");
			}
			for (let i = 0; i < this._currentDeviceList.length; i++)
			{
				if (this._currentDeviceList[i].kind == deviceKind)
				{
					result[this._currentDeviceList[i].deviceId] = this._currentDeviceList[i].label;
				}
			}

			return result;
		}
	}

	class BackgroundDialog
	{
		isAvailable()
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
		}

		isMaskAvailable()
		{
			if (BX.getClass('BX.desktop'))
			{
				return BX.desktop.isFeatureEnabled('mask');
			}
			else if (BX.getClass("BX.Messenger.Lib.Utils.platform"))
			{
				return BX.Messenger.Lib.Utils.platform.isDesktopFeatureEnabled('mask');
			}

			return false;
		}

		open(options)
		{
			options = BX.type.isPlainObject(options) ? options : {};
			const tab = BX.type.isStringFilled(options.tab) ? options.tab : 'background'; // mask, background

			if (!this.isAvailable())
			{
				if (window.BX.Helper)
				{
					window.BX.Helper.show("redirect=detail&code=12398124");
				}

					return false;
				}

				const html =
					`<div id="bx-desktop-loader" class="bx-desktop-loader-wrap">
						<div class="bx-desktop-loader">
							<svg class="bx-desktop-loader-circular" viewBox="25 25 50 50">
								<circle class="bx-desktop-loader-path" cx="50" cy="50" r="20" fill="none" stroke-miterlimit="10"/>
							</svg>
						</div>
					</div>
					<div id="placeholder"></div>`
				;

			const js = `BX.Runtime.loadExtension("im.v2.component.call-background").then(function(exports) {
				BX.Vue3.BitrixVue.createApp({
					components: {CallBackground: exports.CallBackground},
					template: '<CallBackground tab="${tab}"/>',
				}).mount("#placeholder");
			});`;

				(opener || top).BX.desktop.createWindow("callBackground", BX.delegate(function (controller)
				{
					const title = this.isMaskAvailable()? BX.message('BXD_CALL_BG_MASK_TITLE'): BX.message('BXD_CALL_BG_TITLE');
					controller.SetProperty("title", title);
					controller.SetProperty("clientSize", { Width: 943, Height: 670});
					controller.SetProperty("minClientSize", {Width: 943, Height: 670 });
					controller.SetProperty("backgroundColor", "#2B3038");
					controller.ExecuteCommand("center");
					controller.ExecuteCommand("html.load", (opener||top).BXIM.desktop.getHtmlPage(html, js, false));
				},this));

				return true;
		}
	}

	BX.Call.Hardware = new HardwareManager;
	BX.Call.Hardware.Events = Events;
	BX.Call.Hardware.BackgroundDialog = new BackgroundDialog;
})();