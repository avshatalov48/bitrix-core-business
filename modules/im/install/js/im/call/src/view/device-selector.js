import {Dom} from 'main.core'
import {EventEmitter} from 'main.core.events'
import {Popup} from 'main.popup'
import {Hardware} from '../hardware';
import {BackgroundDialog} from '../dialogs/background_dialog';
import 'ui.switcher';

const DeviceSelectorEvents = {
	onMicrophoneSelect: "onMicrophoneSelect",
	onMicrophoneSwitch: "onMicrophoneSwitch",
	onCameraSelect: "onCameraSelect",
	onCameraSwitch: "onCameraSwitch",
	onSpeakerSelect: "onSpeakerSelect",
	onSpeakerSwitch: "onSpeakerSwitch",
	onChangeHdVideo: "onChangeHdVideo",
	onChangeMicAutoParams: "onChangeMicAutoParams",
	onChangeFaceImprove: "onChangeFaceImprove",
	onAdvancedSettingsClick: "onOpenAdvancedSettingsClick",
	onShow: "onShow",
	onDestroy: "onDestroy",
};

/**
 * @param config
 * @param {Node} config.parentElement
 * @param {boolean} config.cameraEnabled
 * @param {boolean} config.microphoneEnabled
 * @param {boolean} config.speakerEnabled
 * @param {boolean} config.allowHdVideo
 * @param {boolean} config.faceImproveEnabled
 * @param {object} config.events

 * @returns {DeviceSelector}
 */

/**
 * @param config
 * @param {Node} config.parentElement
 * @param {number} config.zIndex
 * @param {boolean} config.cameraEnabled
 * @param {boolean} config.microphoneEnabled
 * @param {boolean} config.speakerEnabled
 * @param {boolean} config.allowHdVideo
 * @param {boolean} config.faceImproveEnabled
 * @constructor
 */
export class DeviceSelector
{
	static Events = DeviceSelectorEvents;

	constructor(config)
	{
		this.viewElement = config.viewElement || null;
		this.parentElement = config.parentElement;
		this.zIndex = config.zIndex;

		this.cameraEnabled = BX.prop.getBoolean(config, "cameraEnabled", false);
		this.cameraId = BX.prop.getString(config, "cameraId", false);
		this.microphoneEnabled = BX.prop.getBoolean(config, "microphoneEnabled", false);
		this.microphoneId = BX.prop.getString(config, "microphoneId", false);
		this.speakerEnabled = BX.prop.getBoolean(config, "speakerEnabled", false);
		this.speakerId = BX.prop.getString(config, "speakerId", false);
		this.allowHdVideo = BX.prop.getBoolean(config, "allowHdVideo", false);
		this.faceImproveEnabled = BX.prop.getBoolean(config, "faceImproveEnabled", false);
		this.allowFaceImprove = BX.prop.getBoolean(config, "allowFaceImprove", false);
		this.allowBackground = BX.prop.getBoolean(config, "allowBackground", true);
		this.allowMask = BX.prop.getBoolean(config, "allowMask", true);
		this.allowAdvancedSettings = BX.prop.getBoolean(config, "allowAdvancedSettings", false);
		this.showCameraBlock = BX.prop.getBoolean(config, "showCameraBlock", true);

		this.popup = null;
		this.eventEmitter = new BX.Event.EventEmitter(this, "DeviceSelector");
		this.elements = {
			root: null,
			micContainer: null,
			cameraContainer: null,
			speakerContainer: null,
		};

		const eventListeners = BX.prop.getObject(config, "events", {});
		Object.values(DeviceSelectorEvents).forEach((eventName) =>
		{
			if (eventListeners[eventName])
			{
				this.eventEmitter.subscribe(eventName, eventListeners[eventName]);
			}
		})
	};

	// static create(config)
	// {
	// 	return new DeviceSelector(config);
	// };

	show()
	{
		if (this.popup)
		{
			this.popup.show();
			return;
		}
		this.popup = new Popup({
			id: 'call-view-device-selector',
			bindElement: this.parentElement,
			targetContainer: this.viewElement,
			autoHide: true,
			zIndex: this.zIndex,
			closeByEsc: true,
			offsetTop: 0,
			offsetLeft: 0,
			bindOptions: {
				position: 'top'
			},
			angle: {position: "bottom"},
			overlay: {
				backgroundColor: 'white',
				opacity: 0
			},
			content: this.render(),
			events: {
				onPopupClose: () => this.popup.destroy(),
				onPopupDestroy: () => this.destroy(),
			}
		});
		this.popup.show();

		this.eventEmitter.emit(DeviceSelectorEvents.onShow, {});
	};

	render()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		return Dom.create("div", {
			props: {className: "bx-call-view-device-selector"},
			children: [
				Dom.create("div", {
					props: {className: "bx-call-view-device-selector-top"},
					children: [
						DeviceMenu.create({
							deviceLabel: BX.message("IM_M_CALL_BTN_MIC"),
							deviceList: Hardware.getMicrophoneList(),
							selectedDevice: this.microphoneId,
							deviceEnabled: this.microphoneEnabled,
							icons: ["microphone", "microphone-off"],
							events: {
								onSwitch: this.onMicrophoneSwitch.bind(this),
								onSelect: this.onMicrophoneSelect.bind(this)
							}
						}).render(),
						this.showCameraBlock ?
							DeviceMenu.create({
								deviceLabel: BX.message("IM_M_CALL_BTN_CAMERA"),
								deviceList: Hardware.getCameraList(),
								selectedDevice: this.cameraId,
								deviceEnabled: this.cameraEnabled,
								icons: ["camera", "camera-off"],
								events: {
									onSwitch: this.onCameraSwitch.bind(this),
									onSelect: this.onCameraSelect.bind(this)
								}
							}).render()
							: null,
						Hardware.canSelectSpeaker() ?
							DeviceMenu.create({
								deviceLabel: BX.message("IM_M_CALL_BTN_SPEAKER"),
								deviceList: Hardware.getSpeakerList(),
								selectedDevice: this.speakerId,
								deviceEnabled: this.speakerEnabled,
								icons: ["speaker", "speaker-off"],
								events: {
									onSwitch: this.onSpeakerSwitch.bind(this),
									onSelect: this.onSpeakerSelect.bind(this)
								}
							}).render()
							: null,
					]
				}),
				Dom.create("div", {
					props: {className: "bx-call-view-device-selector-bottom"},
					children: [
						Dom.create("div", {
							props: {className: "bx-call-view-device-selector-bottom-item"},
							children: [
								Dom.create("input", {
									props: {
										id: "device-selector-hd-video",
										className: "bx-call-view-device-selector-bottom-item-checkbox"
									},
									attrs: {
										type: "checkbox",
										checked: this.allowHdVideo
									},
									events: {
										change: this.onAllowHdVideoChange.bind(this)
									}
								}),
								Dom.create("label", {
									props: {className: "bx-call-view-device-selector-bottom-item-label"},
									attrs: {for: "device-selector-hd-video"},
									text: BX.message("IM_M_CALL_HD_VIDEO")
								}),
							]
						}),
						this.allowFaceImprove ?
							Dom.create("div", {
								props: {className: "bx-call-view-device-selector-bottom-item"},
								children: [
									Dom.create("input", {
										props: {
											id: "device-selector-mic-auto-params",
											className: "bx-call-view-device-selector-bottom-item-checkbox"
										},
										attrs: {
											type: "checkbox",
											checked: this.faceImproveEnabled
										},
										events: {
											change: this.onFaceImproveChange.bind(this)
										}
									}),
									Dom.create("label", {
										props: {className: "bx-call-view-device-selector-bottom-item-label"},
										attrs: {for: "device-selector-mic-auto-params"},
										text: BX.message("IM_SETTINGS_HARDWARE_CAMERA_FACE_IMPROVE")
									}),
								]
							})
							: null,
						this.allowBackground ?
							Dom.create("div", {
								props: {className: "bx-call-view-device-selector-bottom-item"},
								children: [
									Dom.create("span", {
										props: {className: "bx-call-view-device-selector-bottom-item-action"},
										text: this.allowMask ? BX.message("IM_M_CALL_BG_MASK_CHANGE") : BX.message("IM_M_CALL_BACKGROUND_CHANGE"),
										events: {
											click: () =>
											{
												BackgroundDialog.open();
												this.popup.close();
											}
										}
									}),
								]
							})
							: null,
						this.allowAdvancedSettings ?
							Dom.create("div", {
								props: {className: "bx-call-view-device-selector-bottom-item"},
								children: [
									Dom.create("span", {
										props: {className: "bx-call-view-device-selector-bottom-item-action"},
										text: BX.message("IM_M_CALL_ADVANCED_SETTINGS"),
										events: {
											click: (e) =>
											{
												// to prevent BX.IM.autoHide
												e.stopPropagation();
												this.eventEmitter.emit(DeviceSelectorEvents.onAdvancedSettingsClick);
												this.popup.close();
											}
										}
									}),
								]
							})
							: null,
					]
				}),
			]
		})
	};

	onMicrophoneSwitch()
	{
		this.microphoneEnabled = !this.microphoneEnabled;
		this.eventEmitter.emit(DeviceSelectorEvents.onMicrophoneSwitch, {
			microphoneEnabled: this.microphoneEnabled
		})
	};

	onMicrophoneSelect(e)
	{
		this.eventEmitter.emit(DeviceSelectorEvents.onMicrophoneSelect, {
			deviceId: e.data.deviceId
		})
	};

	onCameraSwitch()
	{
		this.cameraEnabled = !this.cameraEnabled;
		this.eventEmitter.emit(DeviceSelectorEvents.onCameraSwitch, {
			cameraEnabled: this.cameraEnabled
		})
	};

	onCameraSelect(e)
	{
		this.eventEmitter.emit(DeviceSelectorEvents.onCameraSelect, {
			deviceId: e.data.deviceId
		});
	};

	onSpeakerSwitch()
	{
		this.speakerEnabled = !this.speakerEnabled;
		this.eventEmitter.emit(DeviceSelectorEvents.onSpeakerSwitch, {
			speakerEnabled: this.speakerEnabled
		})
	};

	onSpeakerSelect(e)
	{
		this.eventEmitter.emit(DeviceSelectorEvents.onSpeakerSelect, {
			deviceId: e.data.deviceId
		});
	};

	onAllowHdVideoChange(e)
	{
		this.allowHdVideo = e.currentTarget.checked;
		this.eventEmitter.emit(DeviceSelectorEvents.onChangeHdVideo, {
			allowHdVideo: this.allowHdVideo
		})
	};

	onAllowMirroringVideoChange(e)
	{
		Hardware.enableMirroring = e.target.checked;
	};

	onFaceImproveChange(e)
	{
		this.faceImproveEnabled = e.currentTarget.checked;
		this.eventEmitter.emit(DeviceSelectorEvents.onChangeFaceImprove, {
			faceImproveEnabled: this.faceImproveEnabled
		})
	};

	destroy()
	{
		this.popup = null;
		this.eventEmitter.emit(DeviceSelectorEvents.onDestroy, {});
	};
}

const DeviceMenuEvents = {
	onSelect: "onSelect",
	onSwitch: "onSwitch"
};

class DeviceMenu
{
	constructor(config)
	{
		config = BX.type.isObject(config) ? config : {};

		this.deviceList = BX.prop.getArray(config, "deviceList", []);
		this.selectedDevice = BX.prop.getString(config, "selectedDevice", "");
		this.deviceEnabled = BX.prop.getBoolean(config, "deviceEnabled", false);
		this.deviceLabel = BX.prop.getString(config, "deviceLabel", "");
		this.icons = BX.prop.getArray(config, "icons", []);
		this.eventEmitter = new EventEmitter(this, 'DeviceMenu');
		this.elements = {
			root: null,
			switchIcon: null,
			menuInner: null,
			menuItems: {}  // deviceId => {root: element, icon: element}
		};

		var events = BX.prop.getObject(config, "events", {});
		for (var eventName in events)
		{
			if (!events.hasOwnProperty(eventName))
			{
				continue;
			}

			this.eventEmitter.subscribe(eventName, events[eventName]);
		}
	};

	static create(config)
	{
		return new DeviceMenu(config);
	};

	render()
	{
		if (this.elements.root)
		{
			return this.elements.root;
		}

		this.elements.root = Dom.create("div", {
			props: {className: "bx-call-view-device-selector-menu-container"},
			children: [
				Dom.create("div", {
					props: {className: "bx-call-view-device-selector-switch-wrapper"},
					children: [
						this.elements.switchIcon = Dom.create("div", {
							props: {className: "bx-call-view-device-selector-device-icon " + this.getDeviceIconClass()}
						}),
						Dom.create("span", {
							props: {className: "bx-call-view-device-selector-device-text"},
							text: this.deviceLabel

						}),
						Dom.create("div", {
							props: {className: "bx-call-view-device-selector-device-switch"},
							children: [
								(new BX.UI.Switcher({
									size: 'small',
									checked: this.deviceEnabled,
									handlers: {
										toggled: this.onSwitchToggled.bind(this)
									}
								})).getNode()
							]
						}),
					]
				}),
				this.elements.menuInner = Dom.create("div", {
					props: {className: "bx-call-view-device-selector-menu-inner" + (this.deviceEnabled ? "" : " inactive")},
					children: this.deviceList.map(this.renderDevice.bind(this))
				})
			]
		});
		return this.elements.root;
	};

	renderDevice(deviceInfo)
	{
		var iconClass = this.selectedDevice === deviceInfo.deviceId ? "selected" : "";
		var deviceElements = {};
		deviceElements.root = Dom.create("div", {
			props: {className: "bx-call-view-device-selector-menu-item"},
			dataset: {
				deviceId: deviceInfo.deviceId
			},
			children: [
				deviceElements.icon = Dom.create("div", {
					props: {className: "bx-call-view-device-selector-menu-item-icon " + iconClass},
				}),
				Dom.create("div", {
					props: {className: "bx-call-view-device-selector-menu-item-text"},
					text: deviceInfo.label || "(" + BX.message("IM_M_CALL_DEVICE_NO_NAME") + ")",
				}),
			],
			events: {
				click: this.onMenuItemClick.bind(this)
			}
		});
		this.elements.menuItems[deviceInfo.deviceId] = deviceElements;
		return deviceElements.root;
	};

	getDeviceIconClass()
	{
		var result = "";
		if (this.deviceEnabled && this.icons.length > 0)
		{
			result = this.icons[0];
		}
		else if (!this.deviceEnabled && this.icons.length > 1)
		{
			result = this.icons[1];
		}
		return result;
	};

	onSwitchToggled()
	{
		this.deviceEnabled = !this.deviceEnabled;
		this.elements.switchIcon.className = "bx-call-view-device-selector-device-icon " + this.getDeviceIconClass();
		if (this.deviceEnabled)
		{
			this.elements.menuInner.classList.remove("inactive");
		}
		else
		{
			this.elements.menuInner.classList.add("inactive");
		}

		this.eventEmitter.emit(DeviceMenuEvents.onSwitch, {
			deviceEnabled: this.deviceEnabled
		})
	};

	onMenuItemClick(e)
	{
		var currentDevice = this.selectedDevice;
		var selectedDevice = e.currentTarget.dataset.deviceId;
		if (currentDevice == selectedDevice)
		{
			return;
		}
		this.selectedDevice = selectedDevice;
		if (this.elements.menuItems[currentDevice])
		{
			this.elements.menuItems[currentDevice]['icon'].classList.remove('selected');
		}
		if (this.elements.menuItems[this.selectedDevice])
		{
			this.elements.menuItems[this.selectedDevice]['icon'].classList.add('selected');
		}

		this.eventEmitter.emit(DeviceMenuEvents.onSelect, {
			deviceId: this.selectedDevice
		})
	};
}