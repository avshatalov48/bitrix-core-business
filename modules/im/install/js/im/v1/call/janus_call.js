;(function()
{
	BX.namespace('BX.Call');

	var janus = null;

	var ajaxActions = {
		invite: 'im.bitrix.im.controller.call.invite',
		cancel: 'im.bitrix.im.controller.call.cancel',
		answer: 'im.bitrix.im.controller.call.answer',
		decline: 'im.bitrix.im.controller.call.decline',
		hangup: 'im.bitrix.im.controller.call.hangup',
		ping: 'im.bitrix.im.controller.call.ping'
	};

	BX.Call.JanusCall = function(params)
	{
		this.superclass.constructor.apply(this, arguments);

		this.roomId = 1234;

		this.server = "https://janus.perevozov.bx:8089/janus";
		this.apiSecret = '';

		this.isJanusInited = false;
		this.isJanusConnected = false;

		this.publishers = {
			main: null,
			screen: null
		};

		this.signaling = new BX.Call.JanusCall.Signaling({
			call: this
		});

		this.peers = {};
		this.initPeers();
	};

	BX.extend(BX.Call.JanusCall, BX.Call.AbstractCall);

	BX.Call.JanusCall.prototype.initPeers = function()
	{
		var self = this;
		for (var i = 0; i < this.users.length; i++)
		{
			var userId = this.users[i];
			if(userId == this.userId)
				continue;

			this.peers[userId] = new BX.Call.JanusCall.Peer({
				userId: userId,
				roomId: this.roomId,
				onStreamReceived: function(e)
				{
					console.log("onStreamReceived: ", e);
					self.runCallback("onStreamReceived", e);
				},
				onStreamRemoved: function(e)
				{
					console.log("onStreamRemoved: ", e);
					self.runCallback("onStreamRemoved", e);
				},
				onStateChanged: this.__onPeerStateChanged.bind(this)
			})
		}
	};

	BX.Call.JanusCall.prototype.inviteUsers = function(userIds)
	{
		var self = this;
		var users = BX.type.isArray(userIds) ? userIds : this.users;

		this.initJanus().then(this.connect.bind(this)).then(this.publishStream.bind(this)).then(function()
		{
			self.signaling.inviteUsers({
				userIds: users
			})
		}).catch(function(e)
		{
			console.error("xxx: ", e);
		})
	};

	BX.Call.JanusCall.prototype.answer = function()
	{
		var self = this;
		this.initJanus().then(this.connect.bind(this)).then(this.publishStream.bind(this)).then(function()
		{
			self.signaling.sendAnswer();
		})
	};

	BX.Call.JanusCall.prototype.decline = function()
	{
		this.signaling.sendDecline();
		this.destroy();
	};

	BX.Call.JanusCall.prototype.hangup = function()
	{
		for(var publisherTag in this.publishers)
		{
			if(this.publishers[publisherTag])
			{
				this.publishers[publisherTag].destroy();
			}
		}

		this.signaling.sendHangup();
	};

	BX.Call.JanusCall.prototype.publishStream = function(tag)
	{
		var result = new BX.Promise();
		if(!tag)
		{
			tag = "main";
		}

		if(!this.isJanusConnected)
		{
			return result.reject(new Error("Janus is not connected"));
		}

		if(!this.localStreams[tag])
		{
			return result.reject(new Error("Stream " + tag + " is not set"));
		}

		if(this.publishers[tag])
		{
			throw new Error("Publisher for tag " + tag + " already exists");
		}

		this.publishers[tag] = new BX.Call.JanusCall.Publisher({
			userId: this.userId,
			tag: tag,
			stream: this.localStreams[tag],
			roomId: this.roomId,
			onPublished: function(e)
			{
				if(result.state === null)
				{
					result.resolve();
				}
			},
			onPublishersUpdated: this._onPublishersUpdated.bind(this),
			onDestroyed: this._onPublisherDestroyed.bind(this),
			onError: function(e){console.error("Janus error: ", e)}
		});

		return result;
	};

	BX.Call.JanusCall.prototype.initJanus = function()
	{
		var self = this;
		var result = new BX.Promise();

		if(this.isJanusInited)
		{
			result.resolve();
			return result;
		}

		Janus.init({
			debug: "all",
			callback: function()
			{
				self.isJanusInited = true;
				result.resolve();
			}
		});

		return result;
	};

	BX.Call.JanusCall.prototype.connect = function()
	{
		var self = this;
		var result = new BX.Promise();
		if(this.isJanusConnected)
		{
			return result.resolve();
		}

		if(!this.isJanusInited)
		{
			return result.reject(new Error("Janus is not initialized"));
		}

		janus = new Janus({
			server: this.server,
			apisecret: this.apiSecret,
			success: function()
			{
				self.isJanusConnected = true;
				result.resolve();
			},
			error: function(e)
			{
				result.reject(e);
			},
			destroyed: function()
			{
				self.destroy();
			}

		});

		return result;
	};

	BX.Call.JanusCall.prototype._onPublishersUpdated = function(publishers)
	{
		console.log("_onPublishersUpdated: ", publishers);

		if(!BX.type.isArray(publishers))
		{
			return;
		}

		for(var i = 0; i < publishers.length; i++)
		{
			var publisher = publishers[i];
			if(!BX.type.isNotEmptyString(publisher.display) || publisher.display.substr(0, 4) !== 'user')
			{
				continue;
			}
			var userId = parseInt(publisher.display.substr(4));

			if(!this.peers[userId])
			{
				// not sure what to do if new user connects out of the sudden, skip for now
				continue;
			}

			this.peers[userId].attachFeed("main", publisher.id);
		}
	};

	BX.Call.JanusCall.prototype._onPublisherDestroyed = function(e)
	{
		console.log("_onPublisherDestroyed", e);
		//var publisher = e.target;
	};

	BX.Call.JanusCall.prototype.__onPeerStateChanged = function(e)
	{
		this.runCallback("onUserStateChanged", e);

		if(e.state == BX.Call.UserState.Failed)
		{
			if(!this.isAnyoneParticipating())
			{
				this.destroy();
			}
		}
	};

	BX.Call.JanusCall.prototype.isAnyoneParticipating = function()
	{
		for (var userId in this.peers)
		{
			if(this.peers[userId].isParticipating())
			{
				return true;
			}
		}

		return false;
	};

	BX.Call.JanusCall.prototype.destroy = function()
	{
		for(var userId in this.peers)
		{
			if(this.peers[userId])
			{
				this.peers[userId].destroy();
				this.peers[userId] = null;
			}
		}

		for(var tag in this.publishers)
		{
			if(this.publishers[tag])
			{
				this.publishers[tag].destroy();
				this.publishers[tag] = null;
			}
		}
	};

	BX.Call.JanusCall.Publisher = function(config)
	{
		this.mediaState = {
			video: null,
			audio: null
		};

		this.webrtcState = null;

		this.callbacks = {
			onAttached: (BX.type.isFunction(config.onAttached) ? config.onAttached : BX.DoNothing),
			onDetached: (BX.type.isFunction(config.onDetached) ? config.onDetached : BX.DoNothing),
			onRoomJoined: (BX.type.isFunction(config.onRoomJoined) ? config.onRoomJoined : BX.DoNothing),
			onRoomLeft: (BX.type.isFunction(config.onRoomLeft) ? config.onRoomLeft : BX.DoNothing),
			onPublished: (BX.type.isFunction(config.onPublished) ? config.onPublished : BX.DoNothing),
			onError: (BX.type.isFunction(config.onError) ? config.onError : BX.DoNothing),
			onPublishersUpdated: (BX.type.isFunction(config.onPublishersUpdated) ? config.onPublishersUpdated : BX.DoNothing),
			onDestroyed: (BX.type.isFunction(config.onDestroyed) ? config.onDestroyed : BX.DoNothing),
			onPublisherLeft: (BX.type.isFunction(config.onPublisherLeft) ? config.onPublisherLeft : BX.DoNothing),
			onWebrtcState: (BX.type.isFunction(config.onWebrtcState) ? config.onWebrtcState : BX.DoNothing)
		};

		this.userId = config.userId;
		this.tag = config.tag;

		this.pluginHandle = null;
		this.localStream = config.stream;
		this.roomId = config.roomId;

		this.init();
	};

	BX.Call.JanusCall.Publisher.prototype.init = function()
	{
		this.attach();
	};

	BX.Call.JanusCall.Publisher.prototype.attach = function()
	{
		var self = this;
		janus.attach({
			plugin: 'janus.plugin.videoroom',
			success: this.onAttached.bind(this),
			error: this.onError.bind(this),
			consentDialog: BX.DoNothing,
			webrtcState: function(state) {
				console.log("Publisher webrtcState:", state);
				self.webrtcState = state;
			},
			mediaState: function (media, state) {
				console.log("Publisher mediaState:", media, state);
				if(self.mediaState.hasOwnProperty(media))
				{
					self.mediaState[media] = state;
				}

				if(state)
				{
					self.callbacks.onPublished({
						target: self,
						media: media,
						state: state
					});
				}
			},
			onmessage: this.onMessage.bind(this),
			onlocalstream: BX.DoNothing,
			onremotestream: BX.DoNothing,
			oncleanup: BX.DoNothing,
			ondetached: function() {console.log('Publisher detached')}
		})
	};

	BX.Call.JanusCall.Publisher.prototype.onAttached = function(pluginHandle)
	{
		this.pluginHandle = pluginHandle;
		console.log("Plugin attached! (" + pluginHandle.getPlugin() + ", id=" + pluginHandle.getId() + ")");
		console.log("  -- This is a publisher/manager");

		return this.joinRoom();
	};

	BX.Call.JanusCall.Publisher.prototype.onError = function(error)
	{
		console.error("Janus publisher error: ", error);
		this.callbacks.onError({
			target: this,
			errorCode: 0,
			error: error
		});
	};

	BX.Call.JanusCall.Publisher.prototype.joinRoom = function()
	{
		var self = this;
		var result = new BX.Promise();

		var request = {
			request: "join",
			room: this.roomId,
			ptype: "publisher",
			display: 'user' + self.userId
		};
		this.pluginHandle.send({
			message: request,
			success: function(data) {result.resolve(data)},
			error: function(error) {result.reject(error)}
		});

		return result;
	};

	BX.Call.JanusCall.Publisher.prototype.publishStream = function()
	{
		var self = this;
		this.pluginHandle.createOffer({
			media: { 	// Publishers are sendonly
				audioRecv: false,
				videoRecv: false,
				audioSend: true,
				videoSend: true
			},
			stream: self.localStream,
			success: function(jsep)
			{
				console.debug("Got publisher SDP!");
				console.debug(jsep);
				var request = {
					request: "configure",
					audio: true,
					video: true
				};
				self.pluginHandle.send({message: request, jsep: jsep});
			},
			error: function(error)
			{
				console.error("WebRTC error:", error);
			}
		});
	};

	BX.Call.JanusCall.Publisher.prototype.unpublishStream = function()
	{
		var message = {
			request: "unpublish"
		};
		this.pluginHandle.send({message: message});
	};

	BX.Call.JanusCall.Publisher.prototype.changeStream = function(stream)
	{
		var self = this;
		var result = new BX.Promise();

		// straightforward hangup works slightly faster :)
		//self.unpublishStream();
		self.pluginHandle.hangup();
		setTimeout(function()
		{
			self.localStream = stream;
			self.publishStream();
			result.resolve();
		}, 1000);
		return result;
	};

	BX.Call.JanusCall.Publisher.prototype.onMessage = function(msg, jsep)
	{
		console.log('Received message:', msg);
		var event = msg["videoroom"];

		if(event === "event")
		{
			if (msg["leaving"] !== undefined && msg["leaving"] !== null)
			{
				event = "leving";
			}
			else if (msg["unpublished"] !== undefined && msg["unpublished"] !== null)
			{
				event = "unpublished";
			}
			else if (msg["error"] !== undefined && msg["error"] !== null)
			{
				event = "error";
			}
		}

		var handlers = {
			joined: this.onMessageJoinedRoom.bind(this),
			destroyed: this.onMessageRoomDestroyed.bind(this),
			leaving: this.onMessagePublisherLeaving.bind(this),
			unpublished: this.onMessagePublisherLeft.bind(this),
			error: this.onMessageError.bind(this)
		};

		if(handlers[event])
		{
			handlers[event].call(this, msg);
		}

		if (msg["publishers"] !== undefined && msg["publishers"] !== null)
		{
			this.callbacks.onPublishersUpdated(msg["publishers"]);
		}

		if (jsep !== undefined && jsep !== null)
		{
			Janus.debug("Handling SDP as well...");
			Janus.debug(jsep);
			this.pluginHandle.handleRemoteJsep({jsep: jsep});
		}
	};

	BX.Call.JanusCall.Publisher.prototype.onMessageJoinedRoom = function(msg)
	{
		myid = msg["id"];
		console.log("Successfully joined room " + msg["room"] + " with ID " + myid);

		if(this.localStream)
		{
			this.publishStream();
		}
	};

	BX.Call.JanusCall.Publisher.prototype.onMessageRoomDestroyed = function(msg)
	{
		console.log("The room has been destroyed!");
		this.destroy();
	};

	BX.Call.JanusCall.Publisher.prototype.onMessagePublisherLeaving = function(msg)
	{
		// One of the publishers has gone away?
		this.callbacks.onPublisherLeft(msg["leaving"]);
	};

	BX.Call.JanusCall.Publisher.prototype.onMessagePublisherLeft = function(msg)
	{
		// One of the publishers has unpublished?
		var unpublished = msg["unpublished"];
		Janus.log("Publisher left: " + unpublished);
		if (unpublished === 'ok')
		{
			// That's us
			this.pluginHandle.hangup();
		}
		else
		{
			// That's someone else
			this.callbacks.onPublisherLeft(msg["unpublished"]);
		}
	};

	BX.Call.JanusCall.Publisher.prototype.onMessageError = function(msg)
	{
		this.callbacks.onError({
			target: this,
			errorCode: msg["error_code"],
			error: msg["error"]
		});
	};

	BX.Call.JanusCall.Publisher.prototype.destroy = function()
	{
		if(this.pluginHandle)
		{
			this.pluginHandle.hangup();
			this.pluginHandle.detach();
		}

		this.pluginHandle = null;
		this.localStream = null;
		this.callbacks.onDestroyed();
	};

	BX.Call.JanusCall.Peer = function(config)
	{
		this.userId = config.userId;
		this.roomId = config.roomId;
		this.signalingConnected = false;
		this.ready = false;
		this.declined = false;

		this.calculatedState = BX.Call.UserState.Idle;

		this.feeds = {
			main: null,
			screen: null
		};

		this.callbacks = {
			onStreamReceived: (BX.type.isFunction(config.onStreamReceived) ? config.onStreamReceived : BX.DoNothing),
			onStreamRemoved: (BX.type.isFunction(config.onStreamRemoved) ? config.onStreamRemoved : BX.DoNothing),
			onStateChanged: (BX.type.isFunction(config.onStateChanged) ? config.onStateChanged : BX.DoNothing)
		};
	};

	BX.Call.JanusCall.Peer.prototype.attachFeed = function(tag, feedId)
	{
		if(this.feeds[tag])
		{
			throw new Error("Already attached to the feed " + tag);
		}

		this.feeds[tag] = new BX.Call.JanusCall.Feed({
			roomId: this.roomId,
			userId: this.userId,
			tag: tag,
			feedId: feedId,
			onStreamReceived: this._onStreamReceived.bind(this),
			onStreamRemoved: this._onStreamRemoved.bind(this)
		})
	};

	BX.Call.JanusCall.Peer.prototype.updateCalculatedState = function()
	{
		var calculatedState = this.calculateState();

		if(this.calculatedState != calculatedState)
		{
			this.callbacks.onStateChanged({
				userId: this.userId,
				state: calculatedState,
				previousState: this.calculatedState
			});
			this.calculatedState = calculatedState;
		}
	};

	BX.Call.JanusCall.Peer.prototype.calculateState = function()
	{
		if(!this.signalingConnected)
			return BX.Call.UserState.Failed;

		if(!this.ready)
			return BX.Call.UserState.Calling;

		if(this.declined)
			return BX.Call.UserState.Declined;

		var feeds = [];
		for (var tag in this.feeds)
		{
			if(this.feeds[tag])
			{
				feeds.push(this.feeds[tag]);
			}
		}

		if(feeds.length === 0)
			return BX.Call.UserState.Ready;

		for (var i = 0; i < feeds.length; i++)
		{
			if(feeds[i].webrtcState === '???')
			{
				return BX.Call.UserState.Connected;
			}
		}

		return BX.Call.UserState.Connecting;
	};

	BX.Call.JanusCall.Peer.prototype.isParticipating = function()
	{
		if(this.failed)
			return false;

		if(this.declined)
			return false;

		for(var connectionTag in this.feeds)
		{
			if(this.feeds[connectionTag])
			{
				var webrtcState = this.feeds[connectionTag].webrtcState;
				if(webrtcState == '???')
				{
					return true;
				}
			}
		}

		return false;
	};

	BX.Call.JanusCall.Peer.prototype._onStreamReceived = function(e)
	{
		this.callbacks.onStreamReceived({
			userId: this.userId,
			connectionTag: e.tag,
			stream: e.stream
		});
	};

	BX.Call.JanusCall.Peer.prototype._onStreamRemoved = function(e)
	{
		this.callbacks.onStreamRemoved({
			userId: this.userId,
			connectionTag: e.tag
		});
	};

	BX.Call.JanusCall.Peer.prototype.destroy = function()
	{
		for(var tag in this.feeds)
		{
			if(this.feeds[tag])
			{
				this.feeds[tag].destroy();
				this.feeds[tag] = null;
			}
		}
	};

	BX.Call.JanusCall.Feed = function(config)
	{
		this.userId = config.userId;
		this.tag = config.tag;
		this.feedId = config.feedId;
		this.roomId = config.roomId;

		this.webrtcState = null;

		this.pluginHandle = null;
		this.stream = null;

		this.callbacks = {
			onStreamReceived: (BX.type.isFunction(config.onStreamReceived) ? config.onStreamReceived : BX.DoNothing),
			onStreamRemoved: (BX.type.isFunction(config.onStreamRemoved) ? config.onStreamRemoved : BX.DoNothing)
		};

		this.attach();
	};

	BX.Call.JanusCall.Feed.prototype.attach = function()
	{
		var self = this;
		janus.attach({
			plugin: "janus.plugin.videoroom",
			success: this.onAttached.bind(this),
			error: this.onError.bind(this),
			webrtcState: function(state) {
				self.webrtcState = state;
			},

			onmessage: this.onMessage.bind(this),
			onlocalstream: BX.DoNothing,
			onremotestream: this.onRemoteStream.bind(this),
			oncleanup: BX.DoNothing
		});
	};

	BX.Call.JanusCall.Feed.prototype.onAttached = function(pluginHandle)
	{
		console.log("feed attached");
		this.pluginHandle = pluginHandle;

		this.joinRoom();
	};

	BX.Call.JanusCall.Feed.prototype.onMessage = function(msg, jsep)
	{
		var self = this;
		Janus.debug(" ::: Got a message (listener) :::");
		Janus.debug(JSON.stringify(msg));
		var event = msg["videoroom"];
		Janus.debug("Event: " + event);
		if (event != undefined && event != null)
		{
			if (event === "attached")
			{
				console.log("Successfully attached to feed ", msg);
			}
			else if (msg["error"] !== undefined && msg["error"] !== null)
			{
				console.log('Receiver error: ', msg["error"]);
			}
			else
			{
				console.log('Empty message from media gateway');
			}
		}
		if (jsep !== undefined && jsep !== null)
		{
			Janus.debug("Handling SDP as well...");
			Janus.debug(jsep);
			// Answer and attach
			this.pluginHandle.createAnswer({
				jsep: jsep,
				media: {audioRecv: true, videoRecv: true, audioSend: false, videoSend: false},	// We want recvonly audio/video
				success: function (jsep)
				{
					Janus.debug("Got SDP!");
					Janus.debug(jsep);
					var body = {
						request: "start",
						room: this.roomId
					};
					self.pluginHandle.send({"message": body, "jsep": jsep});
				},
				error: function (error)
				{
					console.log("WebRTC error:", error);
				}
			});
		}
	};

	BX.Call.JanusCall.Feed.prototype.onRemoteStream = function(stream)
	{
		this.stream = stream;
		console.log('remote stream received');
		var event = {
			target: this,
			stream: stream
		};

		this.callbacks.onStreamReceived(event);
	};

	BX.Call.JanusCall.Feed.prototype.onError = function(error)
	{
		console.log('Receiver error: ', error);
	};

	BX.Call.JanusCall.Feed.prototype.joinRoom = function()
	{
		console.log('feed: ', this.feedId);
		var request = {
			request: "join",
			room: this.roomId,
			ptype: "listener",
			feed: this.feedId
		};

		this.pluginHandle.send({message: request});
	};

	BX.Call.JanusCall.Feed.prototype.destroy = function()
	{
		this.stream = null;
		if(this.pluginHandle)
		{
			this.pluginHandle.hangup();
			this.pluginHandle.detach();
		}
	};

	BX.Call.JanusCall.Signaling = function(params)
	{
		this.call = params.call;
	};

	BX.Call.JanusCall.Signaling.prototype.inviteUsers = function(data)
	{
		return this.__runAjaxAction(ajaxActions.invite, data);
	};

	BX.Call.JanusCall.Signaling.prototype.sendAnswer = function(data)
	{
		return this.__runAjaxAction(ajaxActions.answer, data);
	};

	BX.Call.JanusCall.Signaling.prototype.sendHangup = function(data)
	{
		return this.__runAjaxAction(ajaxActions.hangup, data);
	};

	BX.Call.JanusCall.Signaling.prototype.__runAjaxAction = function(signalName, data)
	{
		if(!BX.type.isPlainObject(data))
		{
			data = {};
		}

		data.callId = this.call.id;
		data.callInstanceId = this.call.instanceId;
		data.requestId = BX.CallEngine.getUuidv4();
		return BX.ajax.runAction(signalName, {data: data});
	};

})();