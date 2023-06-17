;(function(){

if(window.BX.VideoRecorder)
	return;
/**
 * Object to record video messages.
 * order inside:
 * start()
 * show permission popup
 * askDevicePermission(), showLayout() render popup on full screen
 * askDevicePermission() set stream to <video>
 * <video> after first playing invoke beforeRecord()
 * beforeRecord() attach analyzer and launches startTimer
 * startRecord() launches record until stop button used.
* */
BX.VideoRecorder = {
	popupPermissionsShown: false,
	popupConfirm: null,
	constraints: {audio: {}, video: {width: 1280, height: 720, facingMode: "user"}},
	analyserNode: null,
	activeFormID: null,
	activeFormType: null,
	chunks: [],
	bindedForms: [],
	recorderInterval: 5000,
	transformLimit: 0,
	transformTime: 70,
	transformTimerShown: false,
	errorCode: null,
	reader: null,
	getScreenWidth: function()
	{
		if(!document.fullscreenElement && !document.mozFullScreenElement && !document.webkitFullscreenElement)
		{
			return document.body.clientWidth;
		}
		else
		{
			return window.screen.width;
		}
	},
	getScreenHeight: function()
	{
		return document.documentElement.clientHeight;
	},
	isAvailable: function(report)
	{
		report = report === true;
		var error = BX.VideoRecorder.error = null;
		if(!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia || typeof MediaRecorder === 'undefined')
		{
			error = 'Not available or old browser';
			BX.VideoRecorder.errorCode = 'BLOG_VIDEO_RECORD_REQUIREMENTS';
		}
		else if (BX.browser.IsChrome() && location.protocol !== 'https:')
		{
			error = 'In chrome works on https only';
			BX.VideoRecorder.errorCode = 'BLOG_VIDEO_RECORD_ERROR_CHROME_HTTPS';
		}
		else if (BX.browser.IsIE())
		{
			error = 'Not available in IE';
			BX.VideoRecorder.errorCode = 'BLOG_VIDEO_RECORD_REQUIREMENTS';
		}
		if(error)
		{
			if(report)
			{
				console.log(error);
			}
			return false;
		}

		return true;
	},
	bindEvents: function(handler)
	{
		if(!BX.VideoRecorder.isAvailable())
		{
			return;
		}

		if(BX.VideoRecorder.bindedForms.indexOf(handler.params.formID) !== -1)
		{
			return;
		}

		BX.VideoRecorder.bindedForms.push(handler.params.formID);
	},
	showLayout: function()
	{
		BX.VideoRecorder.lockScroll();
		BX.VideoRecorder.recordCount = 0;
		BX.VideoRecorder.startCount = 5;
		BX.VideoRecorder.state = 'idle';
		BX.VideoRecorder.transformTimeLeft = BX.VideoRecorder.transformTime;
		BX.VideoRecorder.transformTimerShown = false;
		clearInterval(BX.VideoRecorder.recordInterval);
		BX.VideoRecorder.layout = BX.create('div', {props: {className: 'bx-videomessage-video-wrapper'}, children: [
			BX.create('div', {props: {className: 'bx-videomessage-video-close'}, events: {
				click: function()
				{
					BX.VideoRecorder.hideLayout();
				}
			}}),
			BX.VideoRecorder.videoOverlay = BX.create('div', {props: {className: 'bx-videomessage-video-overlay'}, style: {height: BX.VideoRecorder.getScreenHeight() + 'px'},  children: [
				BX.VideoRecorder.startTimer = BX.create('div', {props: {className: 'bx-videomessage-video-starttimer'}, text: '5'})
			]}),
			BX.VideoRecorder.videoContainer = BX.create('div', {props: {className: 'bx-videomessage-video-container'}, style: {height: BX.VideoRecorder.getScreenHeight() + 'px'}, children: [
				BX.VideoRecorder.videoWrap = BX.create('div', {props: {className: 'bx-videomessage-video-wrap'}, style: {height: BX.VideoRecorder.getScreenHeight() + 'px'}, children: [
					BX.VideoRecorder.waterMark = BX.create('div', {
						props: {className: 'bx-videomessage-watermark'},
						style: {display: 'none'},
						html: BX.message('BLOG_VIDEO_RECORD_LOGO')
					}),
					BX.VideoRecorder.transformTimeContainer = BX.create('div', {props: {className: 'bx-videomessage-transform-time-container'}, children: [
						BX.create('span', {props: {className: 'bx-videomessage-transform-time-close'}, html: '', events: {click: BX.VideoRecorder.hideTransformTimer}}),
						BX.create('span', {props: {className: 'bx-videomessage-transform-time-text'}, html: BX.message('BLOG_VIDEO_RECORD_TRANFORM_LIMIT_TEXT')})
					]}),
					BX.VideoRecorder.outputVideo = BX.create('video', {props: {className: 'bx-videomessage-video', width: BX.VideoRecorder.getScreenWidth(), loop: false}, events: {
						'playing': function()
						{
							if (BX.VideoRecorder.state === 'idle')
							{
								BX.VideoRecorder.resize();
								BX.VideoRecorder.beforeRecord();
							}
						},
						'ended': function()
						{
							BX.VideoRecorder.state = 'idle';
							BX.VideoRecorder.buttonStop.style.display = 'none';
							BX.VideoRecorder.buttonPlay.style.display = 'block';
						}
					}}),
					BX.VideoRecorder.buttonPlay = BX.create('span', {props: {className: 'bx-videomessage-playbutton'}, events: {
						'click': function()
						{
							if(BX.VideoRecorder.state === 'idle')
							{
								BX.VideoRecorder.outputVideo.play();
								BX.VideoRecorder.outputVideo.volume = 1;
								BX.VideoRecorder.state = 'playing';
								BX.VideoRecorder.buttonStop.style.display = 'inline-block';
								BX.VideoRecorder.buttonPlay.style.display = 'none';
							}
						}
					}}),
					BX.VideoRecorder.panel = BX.create('div', {props: {className: 'bx-videomessage-panel'}, style: {display: 'none'}, children: [
						BX.VideoRecorder.recordTimer = BX.create('span', {props: {className: 'bx-videomessage-video-timer'}, text: '00:00'}),
						BX.VideoRecorder.buttonStop = BX.create('span', {props: {className: 'webform-button webform-button-blue webform-button-rounded'}, text: BX.message('BLOG_VIDEO_RECORD_STOP_BUTTON'), events: {
							'click': function()
							{
								if(BX.VideoRecorder.state === 'recording')
								{
									BX.VideoRecorder.state = 'idle';
									BX.VideoRecorder.recorder.stop();
									BX.hide(BX.VideoRecorder.recordMark);
									BX.hide(BX.VideoRecorder.analyzerElement);
									clearInterval(BX.VideoRecorder.recordInterval);
									BX.VideoRecorder.buttonPlay.style.display = 'block';
									BX.VideoRecorder.buttonStop.style.display = 'none';
									BX.ajax.runAction('socialnetwork.api.videorecorder.onstoprecord', {
										analyticsLabel: 'videoRecorder.stop'
									});
									return false;
								}
								else if(BX.VideoRecorder.state === 'playing')
								{
									BX.VideoRecorder.outputVideo.pause();
									BX.VideoRecorder.state = 'idle';
									BX.VideoRecorder.buttonStop.style.display = 'none';
									BX.VideoRecorder.buttonPlay.style.display = 'block';
								}
							}
						}}),
						BX.VideoRecorder.buttonSave = BX.create('span', {props: {className: 'webform-button webform-button-accept webform-button-rounded webform-button-text-white'}, style:{display: 'none'}, text: BX.message('BLOG_VIDEO_RECORD_USE_BUTTON'), events: {
							'click': function()
							{
								if(BX.VideoRecorder.activeFormID)
								{
									BX.fireEvent(BX('bx-b-uploadfile-' + BX.VideoRecorder.activeFormID), 'click');
									var file = new File(BX.VideoRecorder.chunks, 'videomessage-' + BX.date.format(BX.date.convertBitrixFormat(BX.message("FORMAT_DATETIME"))) + '.webm');
									file.hasTobeInserted = true;
									BX.onCustomEvent(window, 'onAddVideoMessage', [file, BX.VideoRecorder.activeFormID]);
									BX.VideoRecorder.hideLayout();
									if(BX.VideoRecorder.activeFormType)
									{
										BX.ajax.runAction('socialnetwork.api.videorecorder.onsave', {
											analyticsLabel: 'videoRecorder.save.' + BX.VideoRecorder.activeFormType
										});
									}
								}
							}
						}}),
						BX.VideoRecorder.buttonCancel = BX.create('span', {props: {className: 'webform-button-link bx-videomessage-video-button-cancel'}, text: BX.message('BLOG_VIDEO_RECORD_CANCEL_BUTTON'), events: {
							'click': function()
							{
								BX.VideoRecorder.hideLayout();
							}
						}}),
						BX.VideoRecorder.buttonRestart = BX.create('span', {props: {className: 'webform-button webform-button-blue webform-button-rounded'}, style:{display: 'none'}, text: BX.message('BLOG_VIDEO_RECORD_RESTART_BUTTON'), events: {
							'click': function()
							{
								BX.VideoRecorder.restartRecord();
							}
						}}),
						BX.VideoRecorder.recordMark = BX.create('div',
							{
								props: {className: 'bx-videomessage-record'},
								style: {display: 'none'},
								html: '<span class="bx-videomessage-record-inner">' + BX.message('BLOG_VIDEO_RECORD_IN_PROGRESS_LABEL') + '</span>'
							}),
						BX.VideoRecorder.analyzerElement = BX.create('canvas', {props: {className: 'bx-videomessage-analyzer'}})
					]})
				]})
			]})
		]});
		BX.VideoRecorder.popupRecord = new BX.PopupWindow('bx-popup-videomessage-record', null, {
			zIndex: 300,
			autoHide: false,
			closeByEsc: false,
			overlay : true,
			events : { onPopupClose : function() { this.destroy() }, onPopupDestroy : function() { BX.VideoRecorder.popupRecord = null }},
			content : BX.VideoRecorder.layout,
			height: BX.VideoRecorder.getScreenHeight(),
			width: BX.VideoRecorder.getScreenWidth(),
			noAllPaddings: true
		});
		BX.VideoRecorder.popupRecord.show();
		BX.VideoRecorder.popupRecord.enterFullScreen();
		BX.VideoRecorder.outputVideo.volume = 0;
		BX.VideoRecorder.canvasContext = BX.VideoRecorder.analyzerElement.getContext('2d');
		BX.VideoRecorder.canvasContext.imageSmoothingEnabled = false;
		BX.VideoRecorder.canvasContext.webkitImageSmoothingEnabled = false;
	},
	beforeRecord: function()
	{
		window.addEventListener('resize', BX.VideoRecorder.resize);
		BX.VideoRecorder.startCount = 5;
		BX.addClass(BX.VideoRecorder.startTimer, 'started');
		BX.VideoRecorder.attachAnalyzer();
		BX.VideoRecorder.renderAnalyzer();
		BX.show(BX.VideoRecorder.analyzerElement, 'inline-block');
		var nextSecond = function()
		{
			clearTimeout(BX.VideoRecorder.startTimeout);
			BX.VideoRecorder.startTimer.innerText = BX.VideoRecorder.startCount;
			BX.VideoRecorder.startTimeout = setTimeout(function()
			{
				BX.VideoRecorder.startCount = BX.VideoRecorder.startCount - 1;
				if(BX.VideoRecorder.startCount === 0)
				{
					BX.removeClass(BX.VideoRecorder.startTimer, 'started');
					BX.VideoRecorder.startRecord();
				}
				else
				{
					nextSecond();
				}
			}, 1000);
		};
		nextSecond();
	},
	startRecord: function()
	{
		BX.VideoRecorder.chunks = [];
		BX.VideoRecorder.videoOverlay.style.display = 'none';
		var possibleMimeTypes = [
            'video/webm;codecs=h264',
            'video/webm;codecs=vp9',
            'video/webm;codecs=vp8',
            'video/webm',
        ];
		var mimeTypesCount = possibleMimeTypes.length;
		var isRecordStarted = false;
        var typesIterator;
        var recordOptions;
        var mimeType;
		for(typesIterator = 0; typesIterator < mimeTypesCount; typesIterator++)
        {
            mimeType = possibleMimeTypes[typesIterator];
            if(MediaRecorder.isTypeSupported(mimeType))
            {
                recordOptions = {mimeType: mimeType};
                try
                {
                    BX.VideoRecorder.recorder = new MediaRecorder(BX.VideoRecorder.stream, recordOptions);
                    BX.VideoRecorder.recorder.start(BX.VideoRecorder.recorderInterval);
                    isRecordStarted = true;
                }
                catch(e)
                {
                    isRecordStarted = false;
                }
            }
            if(isRecordStarted)
            {
                break;
            }
        }
		if(!isRecordStarted)
		{
			BX.VideoRecorder.showMessage(
				BX.message('BLOG_VIDEO_RECORD_REQUIREMENTS'),
				[],
				BX.message('BLOG_VIDEO_RECORD_REQUIREMENTS_TITLE')
			);
			BX.VideoRecorder.hideLayout();
			return;
		}
		BX.VideoRecorder.recordSize = 0;
		BX.VideoRecorder.recordLength = 0;
		BX.VideoRecorder.recorder.ondataavailable = function(e)
		{
			BX.VideoRecorder.recordSize += e.data.size;
			BX.VideoRecorder.recordLength += BX.VideoRecorder.recorderInterval / 1000;
			BX.VideoRecorder.chunks.push(e.data);
			if(BX.VideoRecorder.isTimeToShowTransformationAlert())
			{
				BX.VideoRecorder.startTransformationCounter();
			}
		};
		BX.VideoRecorder.recorder.onstop = function()
		{
			BX.VideoRecorder.hideTransformTimer();
			BX.VideoRecorder.recordBlob = new Blob(BX.VideoRecorder.chunks, {'type': 'video/webm'});
			BX.VideoRecorder.setSourceFromBlob(BX.VideoRecorder.recordBlob);
			BX.VideoRecorder.state = 'idle';
			BX.VideoRecorder.buttonPlay.style.display = 'block';
			BX.VideoRecorder.buttonSave.style.display = 'inline-block';
			BX.VideoRecorder.buttonCancel.style.display = 'inline-block';
			BX.VideoRecorder.buttonRestart.style.display = 'inline-block';
		};
		BX.VideoRecorder.setVideoSrc(BX.VideoRecorder.stream);
		BX.VideoRecorder.outputVideo.volume = 0;
		BX.VideoRecorder.outputVideo.play();
		BX.VideoRecorder.state = 'recording';
		BX.show(BX.VideoRecorder.recordMark, 'inline-block');
		BX.VideoRecorder.recordInterval = setInterval(function()
		{
			BX.VideoRecorder.recordCount++;
			BX.VideoRecorder.recordTimer.innerText = BX.VideoRecorder.getTimeString(BX.VideoRecorder.recordCount);
		}, 1000);
	},
	attachAnalyzer: function()
	{
		BX.VideoRecorder.audioContext = new (window.AudioContext || window.webkitAudioContext);
		BX.VideoRecorder.analyserNode = BX.VideoRecorder.audioContext.createAnalyser();
		BX.VideoRecorder.analyserNode.fftSize = 128;
		BX.VideoRecorder.analyserNode.minDecibels = -80;
		BX.VideoRecorder.analyserNode.maxDecibels = -10;
		BX.VideoRecorder.mediaStreamNode = BX.VideoRecorder.audioContext.createMediaStreamSource(BX.VideoRecorder.stream);
		BX.VideoRecorder.mediaStreamNode.connect(BX.VideoRecorder.analyserNode);
		BX.VideoRecorder.frequencyData = new Uint8Array(BX.VideoRecorder.analyserNode.frequencyBinCount);
		BX.VideoRecorder.lastFrameDate = (new Date()).getTime();
	},
	renderAnalyzer: function()
	{
		if(!BX.VideoRecorder.analyzerElement)
		{
			return;
		}

		window.requestAnimationFrame(BX.VideoRecorder.renderAnalyzer.bind(BX.VideoRecorder));

		var now = (new Date()).getTime();

		if(now - BX.VideoRecorder.lastFrameDate < 50)
		{
			return;
		}

		BX.VideoRecorder.lastFrameDate = now;

		BX.VideoRecorder.analyserNode.getByteFrequencyData(BX.VideoRecorder.frequencyData);

		var width = BX.VideoRecorder.analyzerElement.width;
		var height = BX.VideoRecorder.analyzerElement.height;
		var frequencyPoints = BX.VideoRecorder.analyserNode.frequencyBinCount;

		BX.VideoRecorder.canvasContext.clearRect(0, 0, width, height);
		BX.VideoRecorder.canvasContext.beginPath();

		var barWidth = 2;
		var barHeight;
		var x = 0;

		var middlePoint = Math.ceil(width / 2);

		BX.VideoRecorder.canvasContext.fillStyle = '#afb2b7';
		for(var i = 0; i < frequencyPoints; i++)
		{
			barHeight = Math.round(BX.VideoRecorder.frequencyData[i] * height / 256);
			//barHeight = Math.round(this.frequencyData[i] + 80);
			if(barHeight < 3)
				barHeight = 3;

			x = middlePoint + (barWidth + 2) * i;
			BX.VideoRecorder.canvasContext.fillRect(x, (height - barHeight) / 2 , barWidth, barHeight);
			x = middlePoint - (barWidth + 2) * i;
			BX.VideoRecorder.canvasContext.fillRect(x, (height - barHeight) / 2 , barWidth, barHeight);
		}
		BX.VideoRecorder.canvasContext.closePath();
	},
	getTimeString: function(seconds)
	{
		var time = '';
		var minutes = Math.floor(seconds / 60);
		if(minutes < 10)
		{
			time = '0';
		}
		time = time + minutes + ':';
		seconds = (seconds - (minutes*60));
		if(seconds < 10)
		{
			time = time + '0';
		}
		time = time + seconds;
		return time;
	},
	start: function(formId, type)
	{
		BX.VideoRecorder.transformLimit = BX.message('DISK_VIDEO_TRANSFORMATION_LIMIT') || 0;
		BX.VideoRecorder.activeFormID = formId;
		BX.VideoRecorder.activeFormType = type;
		if(!BX.VideoRecorder.isAvailable(true))
		{
			var errorCode = 'BLOG_VIDEO_RECORD_REQUIREMENTS';
			if(BX.VideoRecorder.errorCode)
			{
				errorCode = BX.VideoRecorder.errorCode;
			}
			BX.VideoRecorder.showMessage(
				BX.message(errorCode),
				[],
				BX.message('BLOG_VIDEO_RECORD_REQUIREMENTS_TITLE')
			);
		}
		else
		{
			this.askBaseDevicePermission()
				.then(function(isAccess) {
					if (isAccess === false)
					{
						throw new Error(BX.message('BLOG_VIDEO_RECORD_PERMISSIONS_ERROR'));
					}

					navigator.mediaDevices.enumerateDevices().then(function (mediaDevices) {
						var videoDevices = this.getVideoDevices(mediaDevices);

						var content = BX.message('BLOG_VIDEO_RECORD_ASK_PERMISSIONS');
						if (videoDevices.length > 1)
						{
							content = this.createContentForMessage(videoDevices);

							if (videoDevices[0].deviceId)
							{
								BX.VideoRecorder.constraints.video.deviceId = {
									exact: videoDevices[0].deviceId
								};
							}
						}

						BX.VideoRecorder.showMessage(content, [
							new BX.PopupWindowButton({
								text : BX.message('BLOG_VIDEO_RECORD_AGREE'),
								className : "popup-window-button-blue",
								events : { click : function()
									{
										BX.VideoRecorder.askDevicePermission();
										BX.VideoRecorder.showLayout();
									}}
							}),
							new BX.PopupWindowButtonLink({
								text : BX.message('BLOG_VIDEO_RECORD_CLOSE'),
								className : "popup-window-button-decline",
								events : { click : function() { this.popupWindow.close(); } }
							})
						], BX.message('BLOG_VIDEO_RECORD_PERMISSIONS_TITLE'));
					}.bind(this));
				}.bind(this))
				.catch(function(error) {
					BX.VideoRecorder.showMessage(
						BX.message('BLOG_VIDEO_RECORD_PERMISSIONS_ERROR'),
						[],
						BX.message('BLOG_VIDEO_RECORD_PERMISSIONS_ERROR_TITLE')
					);
					console.log(error);
				})
			;
		}
	},
	askDevicePermission: function()
	{
		navigator.mediaDevices.getUserMedia(BX.VideoRecorder.constraints).then(function(stream)
		{
			BX.VideoRecorder.stream = stream;
			if(BX.VideoRecorder.popupConfirm)
			{
				BX.VideoRecorder.popupConfirm.destroy();
			}
			if(!BX.VideoRecorder.popupRecord)
			{
				BX.VideoRecorder.hideLayout();
				return;
			}
			BX.VideoRecorder.setVideoSrc(BX.VideoRecorder.stream);
			BX.VideoRecorder.outputVideo.play();
		}).catch(function(error)
		{
			BX.VideoRecorder.hideLayout();
			BX.VideoRecorder.showMessage(
				BX.message('BLOG_VIDEO_RECORD_PERMISSIONS_ERROR'),
				[],
				BX.message('BLOG_VIDEO_RECORD_PERMISSIONS_ERROR_TITLE')
			);
			console.log(error);
		});
	},
	askBaseDevicePermission: function()
	{
		return navigator.mediaDevices.getUserMedia({
			audio: {},
			video: { width: 1280, height: 720, facingMode: 'user' }
		})
			.then(function() {
				return true;
			}).catch(function() {
				return false;
			})
		;
	},
	getVideoDevices: function(mediaDevices)
	{
		var devices = [];

		mediaDevices.forEach(function (mediaDevice) {
			if (mediaDevice.kind === 'videoinput')
			{
				devices.push(mediaDevice);
			}
		});

		return devices;
	},
	createContentForMessage: function(videoDevices)
	{
		var selectWrap = BX.create(
			'div',
			{
				props: {
					className: 'ui-ctl ui-ctl-after-icon ui-ctl-dropdown'
				},
				children: [
					BX.create(
						'div',
						{
							props: {
								className: 'ui-ctl-after ui-ctl-icon-angle'
							},
						}
					)
				]
			}
		);
		var selectOptions = [], count = 1;
		for (var key in videoDevices)
		{
			var videoDevice = videoDevices[key];

			selectOptions.push(
				BX.create(
					'option',
					{
						props: {
							value: videoDevice.deviceId,
							selected: selectOptions.length === 0,
						},
						text: (
							videoDevice.label
							|| BX.message('BLOG_VIDEO_RECORD_DEFAULT_CAMERA_NAME') + ` ${count++}`
						)
					}
				)
			);
		}
		var select = BX.create(
			'select',
			{
				props: {
					className: 'ui-ctl-element'
				},
				children : selectOptions,
				events: {
					change : function(event) {
						var element = event.srcElement || event.target;
						if (element.value !== '')
						{
							BX.VideoRecorder.constraints.video.deviceId = {
								exact: element.value
							};
						}
					}
				}
			}
		);
		selectWrap.appendChild(select);

		var content = BX.create(
			'div',
			{
				children: [
					BX.create(
						'div',
						{
							props: {
								className: 'ui-text-1',
							},
							children: [selectWrap]
						}
					),
					BX.create(
						'div',
						{
							text: BX.message('BLOG_VIDEO_RECORD_ASK_PERMISSIONS')
						}
					)
				]
			}
		);

		return content;
	},
	showMessage: function(content, buttons, title)
	{
		var autohide = false;
		var title = title || '';
		if (typeof(buttons) == "undefined" || typeof(buttons) == "object" && buttons.length <= 0)
		{
			buttons = [new BX.PopupWindowButton({
				text : BX.message('BLOG_VIDEO_RECORD_CLOSE'),
				className : "popup-window-button-blue",
				events : { click : function(e) { this.popupWindow.close(); BX.PreventDefault(e) } }
			})];
			autohide = true;
		}
		if(this.popupConfirm != null)
		{
			this.popupConfirm.destroy();
		}
		this.popupConfirm = new BX.PopupWindow('bx-popup-videomessage-popup', null, {
			zIndex: 200,
			autoHide: autohide,
			closeByEsc: autohide,
			buttons: buttons,
			overlay : true,
			events : { onPopupClose : function() { this.destroy() }, onPopupDestroy : BX.delegate(function() { this.popupConfirm = null }, this)},
			content : content,
			titleBar: title,
			contentColor: 'white',
			className : 'bx-popup-videomessage-popup'
		});
		this.popupConfirm.show();
	},
	hideLayout: function()
	{
		if(BX.VideoRecorder.recorder && BX.VideoRecorder.recorder.state === 'recording')
		{
			BX.VideoRecorder.recorder.stop();
		}
		if(BX.VideoRecorder.outputVideo)
		{
			BX.VideoRecorder.outputVideo.pause();
		}
		if (document.cancelFullScreen)
		{
			document.cancelFullScreen();
		}
		else if (document.mozCancelFullScreen)
		{
			document.mozCancelFullScreen();
		}
		else if (document.webkitCancelFullScreen)
		{
			document.webkitCancelFullScreen();
		}
		if(BX.VideoRecorder.popupRecord)
		{
			BX.VideoRecorder.popupRecord.destroy();
		}
		if(BX.VideoRecorder.layout)
		{
			BX.VideoRecorder.layout.remove();
			BX.VideoRecorder.layout = null;
			BX.VideoRecorder.analyzerElement = null;
		}
		if(BX.VideoRecorder.stream)
		{
			BX.VideoRecorder.stream.getVideoTracks().forEach(function(track) {
				track.stop();
			});
			BX.VideoRecorder.stream.getAudioTracks().forEach(function(track) {
				track.stop();
			});
		}
		BX.VideoRecorder.startCount = 0;
		clearInterval(BX.VideoRecorder.recordInterval);
		BX.VideoRecorder.panel.style.display = 'none';
		BX.VideoRecorder.waterMark.style.display = 'none';
		BX.VideoRecorder.buttonPlay.style.display = 'none';
		window.removeEventListener('resize', BX.VideoRecorder.resize);
		BX.VideoRecorder.unlockScroll();
		BX.VideoRecorder.hideTransformTimer();
	},
	resize: function()
	{
		if(!BX.VideoRecorder.popupRecord)
		{
			return;
		}
		var height = BX.VideoRecorder.getScreenHeight();
		var width = BX.VideoRecorder.getScreenWidth();
		var resultRelativeSize = width / height;
		var videoRelativeSize = BX.VideoRecorder.outputVideo.videoWidth / BX.VideoRecorder.outputVideo.videoHeight;
		if(resultRelativeSize > videoRelativeSize)
		{
			BX.VideoRecorder.outputVideo.width = width;
			BX.VideoRecorder.outputVideo.height = height * resultRelativeSize / videoRelativeSize;
			BX.VideoRecorder.outputVideo.style.marginTop = (height - height * resultRelativeSize / videoRelativeSize) / 2 + 'px';
			BX.VideoRecorder.outputVideo.style.marginLeft = 0;
		}
		else
		{
			BX.VideoRecorder.outputVideo.height = height;
			BX.VideoRecorder.outputVideo.width = width * videoRelativeSize / resultRelativeSize;
			BX.VideoRecorder.outputVideo.style.marginTop = 0;
			BX.VideoRecorder.outputVideo.style.marginLeft = (width - width * videoRelativeSize / resultRelativeSize) / 2 + 'px';
		}
		BX.VideoRecorder.videoOverlay.style.height = height + 'px';
		BX.VideoRecorder.videoContainer.style.height = height + 'px';
		BX.VideoRecorder.popupRecord.setHeight(height);
		BX.VideoRecorder.videoWrap.style.height = height + 'px';
		BX.VideoRecorder.videoWrap.style.width = BX.VideoRecorder.outputVideo.clientWidth + 'px';
		BX.VideoRecorder.panel.style.width = width + 'px';
		BX('popup-window-content-bx-popup-videomessage-record').style.width = width + 'px';
		BX.VideoRecorder.panel.style.display = 'block';
		BX.VideoRecorder.waterMark.style.display = 'block';
	},
	lockScroll: function()
	{
		BX.addClass(document.body, 'bx-videomessage-lock-scroll');
	},
	unlockScroll: function()
	{
		BX.removeClass(document.body, 'bx-videomessage-lock-scroll');
	},
	setVideoSrc: function(object)
	{
		BX.VideoRecorder.outputVideo.srcObject = object;
	},
	isTimeToShowTransformationAlert: function()
	{
		if(BX.VideoRecorder.transformLimit > 0 && !BX.VideoRecorder.transformTimerShown)
		{
			var derivative = BX.VideoRecorder.recordSize / BX.VideoRecorder.recordLength;
			BX.VideoRecorder.transformTimeLeft = Math.floor((BX.VideoRecorder.transformLimit - BX.VideoRecorder.recordSize) / derivative);
			if(BX.VideoRecorder.transformTimeLeft <= BX.VideoRecorder.transformTime)
			{
				if(BX.VideoRecorder.transformTimeLeft > 10)
				{
					BX.VideoRecorder.transformTimeLeft -= 10;
				}
				return true;
			}
		}
		return false;
	},
	startTransformationCounter: function()
	{
		if(!BX.VideoRecorder.transformTimerShown)
		{
			BX.VideoRecorder.transformTimerShown = true;
			BX.VideoRecorder.showTransformTimer();
			BX.VideoRecorder.transformTimer = setTimeout(function()
			{
				BX.VideoRecorder.hideTransformTimer();
			}, BX.VideoRecorder.transformTimeLeft * 1000);
		}
	},
	showTransformTimer: function()
	{
		BX.VideoRecorder.transformTimeContainer.classList.add('js-videomessage-transform-time-active');
	},
	hideTransformTimer: function()
	{
		BX.VideoRecorder.transformTimeContainer.classList.remove('js-videomessage-transform-time-active');
	},
	restartRecord: function()
	{
		BX.VideoRecorder.recordCount = 0;
		BX.VideoRecorder.recordTimer.innerText = BX.VideoRecorder.getTimeString(BX.VideoRecorder.recordCount);
		BX.VideoRecorder.startCount = 5;
		BX.VideoRecorder.state = 'idle';
		BX.VideoRecorder.transformTimeLeft = BX.VideoRecorder.transformTime;
		BX.VideoRecorder.transformTimerShown = false;
		BX.VideoRecorder.hideTransformTimer();
		BX.VideoRecorder.buttonStop.style.display = 'inline-block';
		BX.VideoRecorder.buttonPlay.style.display = 'none';
		BX.VideoRecorder.buttonSave.style.display = 'none';
		BX.VideoRecorder.buttonCancel.style.display = 'none';
		BX.VideoRecorder.buttonRestart.style.display = 'none';
		BX.VideoRecorder.videoOverlay.style.display = 'block';
		if(BX.VideoRecorder.stream)
		{
			BX.VideoRecorder.setVideoSrc(BX.VideoRecorder.stream);
			BX.VideoRecorder.outputVideo.play();
		}
		else
		{
			BX.VideoRecorder.askDevicePermission();
		}
	},
	getReader: function()
	{
		if(!BX.VideoRecorder.reader)
		{
			BX.VideoRecorder.reader = new FileReader();
			BX.VideoRecorder.reader.onload = BX.proxy(function(e)
			{
				BX.VideoRecorder.outputVideo.srcObject = null;
				BX.VideoRecorder.outputVideo.src = e.target.result;
			}, this);
		}

		return BX.VideoRecorder.reader;
	},
	setSourceFromBlob: function(blob)
	{
		BX.VideoRecorder.getReader().readAsDataURL(blob);
	}
};

BX.addCustomEvent(window, 'onInitialized', function (handler) {
	BX.VideoRecorder.bindEvents(handler);
});

})();