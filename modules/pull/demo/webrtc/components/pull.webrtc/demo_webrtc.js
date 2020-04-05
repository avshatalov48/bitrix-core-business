/**
 * Class for demo of WebRTC
 * @constructor
 */

;(function (window)
{
	if (!window.YourCompanyPrefix)
		window.YourCompanyPrefix = {};

	if (window.YourCompanyPrefix.webrtc) return;

	var YourCompanyPrefix = window.YourCompanyPrefix;

	/* Initialize */
	YourCompanyPrefix.webrtc = function(params)
	{
		this.parent.constructor.apply(this, arguments);
		params = params || {};

		this.debug = true;

		this.callWindowBeforeUnload = null;
		this.placeholder = params.placeholder;
		this.signalingLink = params.signalingLink;

		if (this.ready())
		{
			BX.addCustomEvent("onPullEvent-ycp", BX.delegate(function(command,params)
			{
				if (command == 'call')
				{
					this.log('Incoming', params.command, params.senderId, JSON.stringify(params));
					if (params.command == 'invite')
					{
						if (this.callInit)
						{
							BX.ajax({
								url: this.signalingLink+'?CALL_SIGNALING',
								method: 'POST',
								dataType: 'json',
								timeout: 30,
								data: {'COMMAND': 'busy', 'USER_ID' : params.senderId, 'sessid': BX.bitrix_sessid()}
							});
						}
						else
						{
							this.initiator = false;
							this.callVideo = true;
							this.callInit = true;
							this.callUserId = params.senderId;
							this.callInitUserId = params.senderId;

							this.drawAnswerControls();
						}
					}
					else if (params.command == 'answer')
					{
						this.startGetUserMedia();

						this.drawDeclineControls();
					}
					else if (params.command == 'decline')
					{
						this.callDecline();
					}
					else if (params.command == 'busy')
					{
						this.callDecline(false);
					}
					else if (params.command == 'ready' && this.callInit)
					{
						this.log('Apponent '+params.senderId+' ready!');
						this.connected[params.senderId] = true;
					}
					else if (params.command == 'reconnect' && this.callActive)
					{
						clearTimeout(this.pcConnectTimeout[params.senderId]);
						clearTimeout(this.initPeerConnectionTimeout[params.senderId]);

						if (this.pc[params.senderId])
							this.pc[params.senderId].close();

						delete this.pc[params.senderId];
						delete this.pcStart[params.senderId];

						if (this.callStreamMain == this.callStreamUsers[params.senderId])
							this.callStreamMain = null;
						this.callStreamUsers[params.senderId] = null;

						this.initPeerConnection(params.senderId);
					}
					else if (params.command == 'signaling' && this.callActive)
					{
						this.signalingPeerData(params.senderId, params.peer);
					}
					else
					{
						this.log('Command "'+params.command+'" skip');
					}
				}
			}, this));

			BX.garbage(function(){
				this.callCommand('decline', true);
			}, this);
		}
	};
	BX.inheritWebrtc(YourCompanyPrefix.webrtc);

	/* WebRTC UserMedia API */

	YourCompanyPrefix.webrtc.prototype.startGetUserMedia = function(video, audio)
	{
		this.callWindowBeforeUnload = window.onbeforeunload;
		window.onbeforeunload = function(){
			return BX.message('DW_WINDOW_RELOAD')
		};

		this.parent.startGetUserMedia.apply(this, arguments);
	}

	YourCompanyPrefix.webrtc.prototype.onUserMediaSuccess = function(stream)
	{
		var result = this.parent.onUserMediaSuccess.apply(this, arguments);
		if (!result)
			return false;

		this.attachMediaStream(this.interfaceVideoSelf, this.callStreamSelf);
		this.interfaceVideoSelf.muted = true;
		BX.addClass(this.interfaceVideoSelf, 'ycp-webrtc-video-self-show');

		this.callCommand('ready');

		return true;
	};

	YourCompanyPrefix.webrtc.prototype.onUserMediaError = function(error)
	{
		var result = this.parent.onUserMediaError.apply(this, arguments);
		if (!result)
			return false;

		this.callDecline();

		return true;
	}

	/* WebRTC PeerConnection Events */

	YourCompanyPrefix.webrtc.prototype.setLocalAndSend = function(userId, desc)
	{
		var result = this.parent.setLocalAndSend.apply(this, arguments);
		if (!result)
			return false;

		BX.ajax({
			url: this.signalingLink+'?CALL_SIGNALING',
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'COMMAND': 'signaling', 'USER_ID' : userId, 'PEER': JSON.stringify( desc ), 'sessid': BX.bitrix_sessid()}
		});

		return true;
	}

	YourCompanyPrefix.webrtc.prototype.onRemoteStreamAdded = function (userId, event, setMainVideo)
	{
		if (!setMainVideo)
			return false;

		this.attachMediaStream(this.interfaceVideoMain, this.callStreamMain);
		this.interfaceVideoMain.muted = false;
		this.interfaceVideoMain.volume = 1;
		this.interfaceVideoMain.play();

		return true;
	}

	YourCompanyPrefix.webrtc.prototype.onRemoteStreamRemoved = function(userId, event)
	{
	}

	YourCompanyPrefix.webrtc.prototype.onIceCandidate = function (userId, candidates)
	{
		BX.ajax({
			url: this.signalingLink+'?CALL_SIGNALING',
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'COMMAND': 'signaling', 'USER_ID' : userId, 'PEER': JSON.stringify(candidates), 'sessid': BX.bitrix_sessid()}
		});
	}

	YourCompanyPrefix.webrtc.prototype.peerConnectionError = function (userId, event)
	{
		this.callDecline();
	}

	YourCompanyPrefix.webrtc.prototype.peerConnectionReconnect = function (userId)
	{
		var result = this.parent.peerConnectionReconnect.apply(this, arguments);
		if (!result)
			return false;

		BX.ajax({
			url: this.signalingLink+'?CALL_RECONNECT',
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			data: {'COMMAND': 'reconnect', 'USER_ID' : userId, 'sessid': BX.bitrix_sessid()},
			onsuccess: BX.delegate(function(){
				this.initPeerConnection(userId, true);
			}, this)
		});

		return true;
	}

	YourCompanyPrefix.webrtc.prototype.deleteEvents = function ()
	{
		if (!this.interfaceVideoMain)
			return false;

		window.onbeforeunload = this.callWindowBeforeUnload;

		this.interfaceVideoSelf.pause();
		BX.removeClass(this.interfaceVideoSelf, 'ycp-webrtc-video-self-show');

		this.interfaceVideoMain.src = '';
		this.interfaceVideoMain.muted = true;
		this.interfaceVideoMain.volume = 0;
		this.interfaceVideoMain.pause();

		this.parent.deleteEvents.apply(this, arguments);

		return true;
	}

	/* WebRTC Signaling API  */

	YourCompanyPrefix.webrtc.prototype.callInvite = function ()
	{
		var callUserId = this.interfaceUserId.value? parseInt(this.interfaceUserId.value): 0;
		if (callUserId <= 0 || callUserId == BX.message('USER_ID'))
		{
			return false;
		}

		this.initiator = true;
		this.callVideo = true;

		this.callInit = true;
		this.callActive = true;

		this.callUserId = callUserId;
		this.callInitUserId = BX.message('USER_ID');
		this.callCommand('invite');

		this.drawWaitControls();
	}

	YourCompanyPrefix.webrtc.prototype.callAnswer = function ()
	{
		this.callActive = true;
		this.startGetUserMedia();

		this.callCommand('answer');

		this.drawDeclineControls();
	}

	YourCompanyPrefix.webrtc.prototype.callDecline = function (send)
	{
		send = send === false? false: true;
		if (send)
			this.callCommand('decline');

		this.deleteEvents();

		this.drawInviteControls();
	}

	YourCompanyPrefix.webrtc.prototype.callCommand = function(command, async)
	{
		if (!this.signalingReady())
			return false;

		BX.ajax({
			url: this.signalingLink+'?CALL_COMMAND',
			method: 'POST',
			dataType: 'json',
			timeout: 30,
			async: async != false,
			data: {'COMMAND': command, 'USER_ID': this.callUserId, 'sessid': BX.bitrix_sessid()}
		});
	};

	/* Interface API */

	YourCompanyPrefix.webrtc.prototype.drawInterface = function ()
	{
		this.interface =  BX.create("div", { props: { className: "ycp-webrtc" }, children: [
			this.interfaceVideoSelf = BX.create("video", { props: { className: "ycp-webrtc-video-self" }, attrs : { autoplay : true }}),
			this.interfaceVideoMain = BX.create("video", { props: { className: "ycp-webrtc-video" }, attrs : { autoplay : true }}),
			this.interfaceVideoControls = BX.create("div", { props: { className: "ycp-webrtc-controls" }, html: BX.message('DW_NO_WEBRTC')})
		]});
		this.placeholder.innerHTML = '';
		this.placeholder.appendChild(this.interface);

		if (this.ready())
		{
			this.drawInviteControls();
		}

		return true;
	}

	YourCompanyPrefix.webrtc.prototype.drawWaitControls = function ()
	{
		this.interfaceVideoControls.innerHTML = '';
		BX.adjust(this.interfaceVideoControls, {children: [
			BX.create("span", { props: { className: "ycp-webrtc-controls-btn ycp-webrtc-controls-btn-red"}, html: BX.message('DW_VIDEO_WAIT'), events: {
				click: BX.delegate(this.callDecline, this)
			}})
		]});
	}

	YourCompanyPrefix.webrtc.prototype.drawAnswerControls = function ()
	{
		this.interfaceVideoControls.innerHTML = '';
		BX.adjust(this.interfaceVideoControls, {children: [
			BX.create("span", { props: { className: "ycp-webrtc-controls-btn"}, html: BX.message('DW_VIDEO_ANSWER'), events: {
				click: BX.delegate(this.callAnswer, this)
			}}),
			BX.create("span", { props: { className: "ycp-webrtc-controls-btn ycp-webrtc-controls-btn-red"}, html: BX.message('DW_VIDEO_DECLINE'), events: {
				click: BX.delegate(this.callDecline, this)
			}})
		]});
	}

	YourCompanyPrefix.webrtc.prototype.drawDeclineControls = function ()
	{
		this.interfaceVideoControls.innerHTML = '';
		BX.adjust(this.interfaceVideoControls, {children: [
			BX.create("span", { props: { className: "ycp-webrtc-controls-btn ycp-webrtc-controls-btn-red"}, html: BX.message('DW_VIDEO_DECLINE'), events: {
				click: BX.delegate(this.callDecline, this)
			}})
		]});
	}

	YourCompanyPrefix.webrtc.prototype.drawInviteControls = function ()
	{
		this.interfaceVideoControls.innerHTML = '';
		BX.adjust(this.interfaceVideoControls, {children: [
			this.interfaceUserId = BX.create("input", { props: { className: "ycp-webrtc-controls-input" }, attrs : { placeholder: BX.message('DW_PUT_USER_ID'), type: 'input' }}),
			BX.create("span", { props: { className: "ycp-webrtc-controls-btn"}, html: BX.message('DW_VIDEO_CALL'), events: {
				click: BX.delegate(this.callInvite, this)
			}})
		]});
	}

})(window);