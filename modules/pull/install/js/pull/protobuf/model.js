/*eslint-disable block-scoped-var, no-redeclare, no-control-regex, no-prototype-builtins*/
(function($protobuf) {
    "use strict";

    // Common aliases
    var $Reader = $protobuf.Reader, $Writer = $protobuf.Writer, $util = $protobuf.util;
    
    // Exported root namespace
    var $root = $protobuf.roots["push-server"] || ($protobuf.roots["push-server"] = {});
    
    $root.RequestBatch = (function() {
    
        /**
         * Properties of a RequestBatch.
         * @exports IRequestBatch
         * @interface IRequestBatch
         * @property {Array.<Request>|null} [requests] RequestBatch requests
         */
    
        /**
         * Constructs a new RequestBatch.
         * @exports RequestBatch
         * @classdesc Represents a RequestBatch.
         * @implements IRequestBatch
         * @constructor
         * @param {IRequestBatch=} [properties] Properties to set
         */
        function RequestBatch(properties) {
            this.requests = [];
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * RequestBatch requests.
         * @member {Array.<Request>} requests
         * @memberof RequestBatch
         * @instance
         */
        RequestBatch.prototype.requests = $util.emptyArray;
    
        /**
         * Creates a new RequestBatch instance using the specified properties.
         * @function create
         * @memberof RequestBatch
         * @static
         * @param {IRequestBatch=} [properties] Properties to set
         * @returns {RequestBatch} RequestBatch instance
         */
        RequestBatch.create = function create(properties) {
            return new RequestBatch(properties);
        };
    
        /**
         * Encodes the specified RequestBatch message. Does not implicitly {@link RequestBatch.verify|verify} messages.
         * @function encode
         * @memberof RequestBatch
         * @static
         * @param {RequestBatch} message RequestBatch message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        RequestBatch.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.requests != null && message.requests.length)
                for (var i = 0; i < message.requests.length; ++i)
                    $root.Request.encode(message.requests[i], writer.uint32(/* id 1, wireType 2 =*/10).fork()).ldelim();
            return writer;
        };
    
        /**
         * Decodes a RequestBatch message from the specified reader or buffer.
         * @function decode
         * @memberof RequestBatch
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {RequestBatch} RequestBatch
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        RequestBatch.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.RequestBatch();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    if (!(message.requests && message.requests.length))
                        message.requests = [];
                    message.requests.push($root.Request.decode(reader, reader.uint32()));
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return RequestBatch;
    })();
    
    $root.Request = (function() {
    
        /**
         * Properties of a Request.
         * @exports IRequest
         * @interface IRequest
         * @property {IncomingMessagesRequest|null} [incomingMessages] Request incomingMessages
         * @property {ChannelStatsRequest|null} [channelStats] Request channelStats
         * @property {ServerStatsRequest|null} [serverStats] Request serverStats
         */
    
        /**
         * Constructs a new Request.
         * @exports Request
         * @classdesc Represents a Request.
         * @implements IRequest
         * @constructor
         * @param {IRequest=} [properties] Properties to set
         */
        function Request(properties) {
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * Request incomingMessages.
         * @member {IncomingMessagesRequest|null|undefined} incomingMessages
         * @memberof Request
         * @instance
         */
        Request.prototype.incomingMessages = null;
    
        /**
         * Request channelStats.
         * @member {ChannelStatsRequest|null|undefined} channelStats
         * @memberof Request
         * @instance
         */
        Request.prototype.channelStats = null;
    
        /**
         * Request serverStats.
         * @member {ServerStatsRequest|null|undefined} serverStats
         * @memberof Request
         * @instance
         */
        Request.prototype.serverStats = null;
    
        // OneOf field names bound to virtual getters and setters
        var $oneOfFields;
    
        /**
         * Request command.
         * @member {"incomingMessages"|"channelStats"|"serverStats"|undefined} command
         * @memberof Request
         * @instance
         */
        Object.defineProperty(Request.prototype, "command", {
            get: $util.oneOfGetter($oneOfFields = ["incomingMessages", "channelStats", "serverStats"]),
            set: $util.oneOfSetter($oneOfFields)
        });
    
        /**
         * Creates a new Request instance using the specified properties.
         * @function create
         * @memberof Request
         * @static
         * @param {IRequest=} [properties] Properties to set
         * @returns {Request} Request instance
         */
        Request.create = function create(properties) {
            return new Request(properties);
        };
    
        /**
         * Encodes the specified Request message. Does not implicitly {@link Request.verify|verify} messages.
         * @function encode
         * @memberof Request
         * @static
         * @param {Request} message Request message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        Request.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.incomingMessages != null && message.hasOwnProperty("incomingMessages"))
                $root.IncomingMessagesRequest.encode(message.incomingMessages, writer.uint32(/* id 1, wireType 2 =*/10).fork()).ldelim();
            if (message.channelStats != null && message.hasOwnProperty("channelStats"))
                $root.ChannelStatsRequest.encode(message.channelStats, writer.uint32(/* id 2, wireType 2 =*/18).fork()).ldelim();
            if (message.serverStats != null && message.hasOwnProperty("serverStats"))
                $root.ServerStatsRequest.encode(message.serverStats, writer.uint32(/* id 3, wireType 2 =*/26).fork()).ldelim();
            return writer;
        };
    
        /**
         * Decodes a Request message from the specified reader or buffer.
         * @function decode
         * @memberof Request
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {Request} Request
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        Request.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.Request();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    message.incomingMessages = $root.IncomingMessagesRequest.decode(reader, reader.uint32());
                    break;
                case 2:
                    message.channelStats = $root.ChannelStatsRequest.decode(reader, reader.uint32());
                    break;
                case 3:
                    message.serverStats = $root.ServerStatsRequest.decode(reader, reader.uint32());
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return Request;
    })();
    
    $root.IncomingMessagesRequest = (function() {
    
        /**
         * Properties of an IncomingMessagesRequest.
         * @exports IIncomingMessagesRequest
         * @interface IIncomingMessagesRequest
         * @property {Array.<IncomingMessage>|null} [messages] IncomingMessagesRequest messages
         */
    
        /**
         * Constructs a new IncomingMessagesRequest.
         * @exports IncomingMessagesRequest
         * @classdesc Represents an IncomingMessagesRequest.
         * @implements IIncomingMessagesRequest
         * @constructor
         * @param {IIncomingMessagesRequest=} [properties] Properties to set
         */
        function IncomingMessagesRequest(properties) {
            this.messages = [];
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * IncomingMessagesRequest messages.
         * @member {Array.<IncomingMessage>} messages
         * @memberof IncomingMessagesRequest
         * @instance
         */
        IncomingMessagesRequest.prototype.messages = $util.emptyArray;
    
        /**
         * Creates a new IncomingMessagesRequest instance using the specified properties.
         * @function create
         * @memberof IncomingMessagesRequest
         * @static
         * @param {IIncomingMessagesRequest=} [properties] Properties to set
         * @returns {IncomingMessagesRequest} IncomingMessagesRequest instance
         */
        IncomingMessagesRequest.create = function create(properties) {
            return new IncomingMessagesRequest(properties);
        };
    
        /**
         * Encodes the specified IncomingMessagesRequest message. Does not implicitly {@link IncomingMessagesRequest.verify|verify} messages.
         * @function encode
         * @memberof IncomingMessagesRequest
         * @static
         * @param {IncomingMessagesRequest} message IncomingMessagesRequest message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        IncomingMessagesRequest.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.messages != null && message.messages.length)
                for (var i = 0; i < message.messages.length; ++i)
                    $root.IncomingMessage.encode(message.messages[i], writer.uint32(/* id 1, wireType 2 =*/10).fork()).ldelim();
            return writer;
        };
    
        /**
         * Decodes an IncomingMessagesRequest message from the specified reader or buffer.
         * @function decode
         * @memberof IncomingMessagesRequest
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {IncomingMessagesRequest} IncomingMessagesRequest
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        IncomingMessagesRequest.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.IncomingMessagesRequest();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    if (!(message.messages && message.messages.length))
                        message.messages = [];
                    message.messages.push($root.IncomingMessage.decode(reader, reader.uint32()));
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return IncomingMessagesRequest;
    })();
    
    $root.IncomingMessage = (function() {
    
        /**
         * Properties of an IncomingMessage.
         * @exports IIncomingMessage
         * @interface IIncomingMessage
         * @property {Array.<Receiver>|null} [receivers] IncomingMessage receivers
         * @property {Sender|null} [sender] IncomingMessage sender
         * @property {string|null} [body] IncomingMessage body
         * @property {number|null} [expiry] IncomingMessage expiry
         * @property {string|null} [type] IncomingMessage type
         */
    
        /**
         * Constructs a new IncomingMessage.
         * @exports IncomingMessage
         * @classdesc Represents an IncomingMessage.
         * @implements IIncomingMessage
         * @constructor
         * @param {IIncomingMessage=} [properties] Properties to set
         */
        function IncomingMessage(properties) {
            this.receivers = [];
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * IncomingMessage receivers.
         * @member {Array.<Receiver>} receivers
         * @memberof IncomingMessage
         * @instance
         */
        IncomingMessage.prototype.receivers = $util.emptyArray;
    
        /**
         * IncomingMessage sender.
         * @member {Sender|null|undefined} sender
         * @memberof IncomingMessage
         * @instance
         */
        IncomingMessage.prototype.sender = null;
    
        /**
         * IncomingMessage body.
         * @member {string} body
         * @memberof IncomingMessage
         * @instance
         */
        IncomingMessage.prototype.body = "";
    
        /**
         * IncomingMessage expiry.
         * @member {number} expiry
         * @memberof IncomingMessage
         * @instance
         */
        IncomingMessage.prototype.expiry = 0;
    
        /**
         * IncomingMessage type.
         * @member {string} type
         * @memberof IncomingMessage
         * @instance
         */
        IncomingMessage.prototype.type = "";
    
        /**
         * Creates a new IncomingMessage instance using the specified properties.
         * @function create
         * @memberof IncomingMessage
         * @static
         * @param {IIncomingMessage=} [properties] Properties to set
         * @returns {IncomingMessage} IncomingMessage instance
         */
        IncomingMessage.create = function create(properties) {
            return new IncomingMessage(properties);
        };
    
        /**
         * Encodes the specified IncomingMessage message. Does not implicitly {@link IncomingMessage.verify|verify} messages.
         * @function encode
         * @memberof IncomingMessage
         * @static
         * @param {IncomingMessage} message IncomingMessage message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        IncomingMessage.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.receivers != null && message.receivers.length)
                for (var i = 0; i < message.receivers.length; ++i)
                    $root.Receiver.encode(message.receivers[i], writer.uint32(/* id 1, wireType 2 =*/10).fork()).ldelim();
            if (message.sender != null && message.hasOwnProperty("sender"))
                $root.Sender.encode(message.sender, writer.uint32(/* id 2, wireType 2 =*/18).fork()).ldelim();
            if (message.body != null && message.hasOwnProperty("body"))
                writer.uint32(/* id 3, wireType 2 =*/26).string(message.body);
            if (message.expiry != null && message.hasOwnProperty("expiry"))
                writer.uint32(/* id 4, wireType 0 =*/32).uint32(message.expiry);
            if (message.type != null && message.hasOwnProperty("type"))
                writer.uint32(/* id 5, wireType 2 =*/42).string(message.type);
            return writer;
        };
    
        /**
         * Decodes an IncomingMessage message from the specified reader or buffer.
         * @function decode
         * @memberof IncomingMessage
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {IncomingMessage} IncomingMessage
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        IncomingMessage.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.IncomingMessage();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    if (!(message.receivers && message.receivers.length))
                        message.receivers = [];
                    message.receivers.push($root.Receiver.decode(reader, reader.uint32()));
                    break;
                case 2:
                    message.sender = $root.Sender.decode(reader, reader.uint32());
                    break;
                case 3:
                    message.body = reader.string();
                    break;
                case 4:
                    message.expiry = reader.uint32();
                    break;
                case 5:
                    message.type = reader.string();
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return IncomingMessage;
    })();
    
    $root.ChannelStatsRequest = (function() {
    
        /**
         * Properties of a ChannelStatsRequest.
         * @exports IChannelStatsRequest
         * @interface IChannelStatsRequest
         * @property {Array.<ChannelId>|null} [channels] ChannelStatsRequest channels
         */
    
        /**
         * Constructs a new ChannelStatsRequest.
         * @exports ChannelStatsRequest
         * @classdesc Represents a ChannelStatsRequest.
         * @implements IChannelStatsRequest
         * @constructor
         * @param {IChannelStatsRequest=} [properties] Properties to set
         */
        function ChannelStatsRequest(properties) {
            this.channels = [];
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * ChannelStatsRequest channels.
         * @member {Array.<ChannelId>} channels
         * @memberof ChannelStatsRequest
         * @instance
         */
        ChannelStatsRequest.prototype.channels = $util.emptyArray;
    
        /**
         * Creates a new ChannelStatsRequest instance using the specified properties.
         * @function create
         * @memberof ChannelStatsRequest
         * @static
         * @param {IChannelStatsRequest=} [properties] Properties to set
         * @returns {ChannelStatsRequest} ChannelStatsRequest instance
         */
        ChannelStatsRequest.create = function create(properties) {
            return new ChannelStatsRequest(properties);
        };
    
        /**
         * Encodes the specified ChannelStatsRequest message. Does not implicitly {@link ChannelStatsRequest.verify|verify} messages.
         * @function encode
         * @memberof ChannelStatsRequest
         * @static
         * @param {ChannelStatsRequest} message ChannelStatsRequest message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        ChannelStatsRequest.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.channels != null && message.channels.length)
                for (var i = 0; i < message.channels.length; ++i)
                    $root.ChannelId.encode(message.channels[i], writer.uint32(/* id 1, wireType 2 =*/10).fork()).ldelim();
            return writer;
        };
    
        /**
         * Decodes a ChannelStatsRequest message from the specified reader or buffer.
         * @function decode
         * @memberof ChannelStatsRequest
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {ChannelStatsRequest} ChannelStatsRequest
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        ChannelStatsRequest.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.ChannelStatsRequest();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    if (!(message.channels && message.channels.length))
                        message.channels = [];
                    message.channels.push($root.ChannelId.decode(reader, reader.uint32()));
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return ChannelStatsRequest;
    })();
    
    $root.ChannelId = (function() {
    
        /**
         * Properties of a ChannelId.
         * @exports IChannelId
         * @interface IChannelId
         * @property {Uint8Array|null} [id] ChannelId id
         * @property {boolean|null} [isPrivate] ChannelId isPrivate
         * @property {Uint8Array|null} [signature] ChannelId signature
         */
    
        /**
         * Constructs a new ChannelId.
         * @exports ChannelId
         * @classdesc Represents a ChannelId.
         * @implements IChannelId
         * @constructor
         * @param {IChannelId=} [properties] Properties to set
         */
        function ChannelId(properties) {
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * ChannelId id.
         * @member {Uint8Array} id
         * @memberof ChannelId
         * @instance
         */
        ChannelId.prototype.id = $util.newBuffer([]);
    
        /**
         * ChannelId isPrivate.
         * @member {boolean} isPrivate
         * @memberof ChannelId
         * @instance
         */
        ChannelId.prototype.isPrivate = false;
    
        /**
         * ChannelId signature.
         * @member {Uint8Array} signature
         * @memberof ChannelId
         * @instance
         */
        ChannelId.prototype.signature = $util.newBuffer([]);
    
        /**
         * Creates a new ChannelId instance using the specified properties.
         * @function create
         * @memberof ChannelId
         * @static
         * @param {IChannelId=} [properties] Properties to set
         * @returns {ChannelId} ChannelId instance
         */
        ChannelId.create = function create(properties) {
            return new ChannelId(properties);
        };
    
        /**
         * Encodes the specified ChannelId message. Does not implicitly {@link ChannelId.verify|verify} messages.
         * @function encode
         * @memberof ChannelId
         * @static
         * @param {ChannelId} message ChannelId message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        ChannelId.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.id != null && message.hasOwnProperty("id"))
                writer.uint32(/* id 1, wireType 2 =*/10).bytes(message.id);
            if (message.isPrivate != null && message.hasOwnProperty("isPrivate"))
                writer.uint32(/* id 2, wireType 0 =*/16).bool(message.isPrivate);
            if (message.signature != null && message.hasOwnProperty("signature"))
                writer.uint32(/* id 3, wireType 2 =*/26).bytes(message.signature);
            return writer;
        };
    
        /**
         * Decodes a ChannelId message from the specified reader or buffer.
         * @function decode
         * @memberof ChannelId
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {ChannelId} ChannelId
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        ChannelId.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.ChannelId();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    message.id = reader.bytes();
                    break;
                case 2:
                    message.isPrivate = reader.bool();
                    break;
                case 3:
                    message.signature = reader.bytes();
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return ChannelId;
    })();
    
    $root.ServerStatsRequest = (function() {
    
        /**
         * Properties of a ServerStatsRequest.
         * @exports IServerStatsRequest
         * @interface IServerStatsRequest
         */
    
        /**
         * Constructs a new ServerStatsRequest.
         * @exports ServerStatsRequest
         * @classdesc Represents a ServerStatsRequest.
         * @implements IServerStatsRequest
         * @constructor
         * @param {IServerStatsRequest=} [properties] Properties to set
         */
        function ServerStatsRequest(properties) {
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * Creates a new ServerStatsRequest instance using the specified properties.
         * @function create
         * @memberof ServerStatsRequest
         * @static
         * @param {IServerStatsRequest=} [properties] Properties to set
         * @returns {ServerStatsRequest} ServerStatsRequest instance
         */
        ServerStatsRequest.create = function create(properties) {
            return new ServerStatsRequest(properties);
        };
    
        /**
         * Encodes the specified ServerStatsRequest message. Does not implicitly {@link ServerStatsRequest.verify|verify} messages.
         * @function encode
         * @memberof ServerStatsRequest
         * @static
         * @param {ServerStatsRequest} message ServerStatsRequest message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        ServerStatsRequest.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            return writer;
        };
    
        /**
         * Decodes a ServerStatsRequest message from the specified reader or buffer.
         * @function decode
         * @memberof ServerStatsRequest
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {ServerStatsRequest} ServerStatsRequest
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        ServerStatsRequest.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.ServerStatsRequest();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return ServerStatsRequest;
    })();
    
    $root.Sender = (function() {
    
        /**
         * Properties of a Sender.
         * @exports ISender
         * @interface ISender
         * @property {SenderType|null} [type] Sender type
         * @property {Uint8Array|null} [id] Sender id
         */
    
        /**
         * Constructs a new Sender.
         * @exports Sender
         * @classdesc Represents a Sender.
         * @implements ISender
         * @constructor
         * @param {ISender=} [properties] Properties to set
         */
        function Sender(properties) {
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * Sender type.
         * @member {SenderType} type
         * @memberof Sender
         * @instance
         */
        Sender.prototype.type = 0;
    
        /**
         * Sender id.
         * @member {Uint8Array} id
         * @memberof Sender
         * @instance
         */
        Sender.prototype.id = $util.newBuffer([]);
    
        /**
         * Creates a new Sender instance using the specified properties.
         * @function create
         * @memberof Sender
         * @static
         * @param {ISender=} [properties] Properties to set
         * @returns {Sender} Sender instance
         */
        Sender.create = function create(properties) {
            return new Sender(properties);
        };
    
        /**
         * Encodes the specified Sender message. Does not implicitly {@link Sender.verify|verify} messages.
         * @function encode
         * @memberof Sender
         * @static
         * @param {Sender} message Sender message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        Sender.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.type != null && message.hasOwnProperty("type"))
                writer.uint32(/* id 1, wireType 0 =*/8).int32(message.type);
            if (message.id != null && message.hasOwnProperty("id"))
                writer.uint32(/* id 2, wireType 2 =*/18).bytes(message.id);
            return writer;
        };
    
        /**
         * Decodes a Sender message from the specified reader or buffer.
         * @function decode
         * @memberof Sender
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {Sender} Sender
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        Sender.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.Sender();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    message.type = reader.int32();
                    break;
                case 2:
                    message.id = reader.bytes();
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return Sender;
    })();
    
    /**
     * SenderType enum.
     * @exports SenderType
     * @enum {string}
     * @property {number} UNKNOWN=0 UNKNOWN value
     * @property {number} CLIENT=1 CLIENT value
     * @property {number} BACKEND=2 BACKEND value
     */
    $root.SenderType = (function() {
        var valuesById = {}, values = Object.create(valuesById);
        values[valuesById[0] = "UNKNOWN"] = 0;
        values[valuesById[1] = "CLIENT"] = 1;
        values[valuesById[2] = "BACKEND"] = 2;
        return values;
    })();
    
    $root.Receiver = (function() {
    
        /**
         * Properties of a Receiver.
         * @exports IReceiver
         * @interface IReceiver
         * @property {Uint8Array|null} [id] Receiver id
         * @property {boolean|null} [isPrivate] Receiver isPrivate
         * @property {Uint8Array|null} [signature] Receiver signature
         */
    
        /**
         * Constructs a new Receiver.
         * @exports Receiver
         * @classdesc Represents a Receiver.
         * @implements IReceiver
         * @constructor
         * @param {IReceiver=} [properties] Properties to set
         */
        function Receiver(properties) {
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * Receiver id.
         * @member {Uint8Array} id
         * @memberof Receiver
         * @instance
         */
        Receiver.prototype.id = $util.newBuffer([]);
    
        /**
         * Receiver isPrivate.
         * @member {boolean} isPrivate
         * @memberof Receiver
         * @instance
         */
        Receiver.prototype.isPrivate = false;
    
        /**
         * Receiver signature.
         * @member {Uint8Array} signature
         * @memberof Receiver
         * @instance
         */
        Receiver.prototype.signature = $util.newBuffer([]);
    
        /**
         * Creates a new Receiver instance using the specified properties.
         * @function create
         * @memberof Receiver
         * @static
         * @param {IReceiver=} [properties] Properties to set
         * @returns {Receiver} Receiver instance
         */
        Receiver.create = function create(properties) {
            return new Receiver(properties);
        };
    
        /**
         * Encodes the specified Receiver message. Does not implicitly {@link Receiver.verify|verify} messages.
         * @function encode
         * @memberof Receiver
         * @static
         * @param {Receiver} message Receiver message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        Receiver.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.id != null && message.hasOwnProperty("id"))
                writer.uint32(/* id 1, wireType 2 =*/10).bytes(message.id);
            if (message.isPrivate != null && message.hasOwnProperty("isPrivate"))
                writer.uint32(/* id 2, wireType 0 =*/16).bool(message.isPrivate);
            if (message.signature != null && message.hasOwnProperty("signature"))
                writer.uint32(/* id 3, wireType 2 =*/26).bytes(message.signature);
            return writer;
        };
    
        /**
         * Decodes a Receiver message from the specified reader or buffer.
         * @function decode
         * @memberof Receiver
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {Receiver} Receiver
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        Receiver.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.Receiver();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    message.id = reader.bytes();
                    break;
                case 2:
                    message.isPrivate = reader.bool();
                    break;
                case 3:
                    message.signature = reader.bytes();
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return Receiver;
    })();
    
    $root.ResponseBatch = (function() {
    
        /**
         * Properties of a ResponseBatch.
         * @exports IResponseBatch
         * @interface IResponseBatch
         * @property {Array.<Response>|null} [responses] ResponseBatch responses
         */
    
        /**
         * Constructs a new ResponseBatch.
         * @exports ResponseBatch
         * @classdesc Represents a ResponseBatch.
         * @implements IResponseBatch
         * @constructor
         * @param {IResponseBatch=} [properties] Properties to set
         */
        function ResponseBatch(properties) {
            this.responses = [];
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * ResponseBatch responses.
         * @member {Array.<Response>} responses
         * @memberof ResponseBatch
         * @instance
         */
        ResponseBatch.prototype.responses = $util.emptyArray;
    
        /**
         * Creates a new ResponseBatch instance using the specified properties.
         * @function create
         * @memberof ResponseBatch
         * @static
         * @param {IResponseBatch=} [properties] Properties to set
         * @returns {ResponseBatch} ResponseBatch instance
         */
        ResponseBatch.create = function create(properties) {
            return new ResponseBatch(properties);
        };
    
        /**
         * Encodes the specified ResponseBatch message. Does not implicitly {@link ResponseBatch.verify|verify} messages.
         * @function encode
         * @memberof ResponseBatch
         * @static
         * @param {ResponseBatch} message ResponseBatch message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        ResponseBatch.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.responses != null && message.responses.length)
                for (var i = 0; i < message.responses.length; ++i)
                    $root.Response.encode(message.responses[i], writer.uint32(/* id 1, wireType 2 =*/10).fork()).ldelim();
            return writer;
        };
    
        /**
         * Decodes a ResponseBatch message from the specified reader or buffer.
         * @function decode
         * @memberof ResponseBatch
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {ResponseBatch} ResponseBatch
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        ResponseBatch.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.ResponseBatch();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    if (!(message.responses && message.responses.length))
                        message.responses = [];
                    message.responses.push($root.Response.decode(reader, reader.uint32()));
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return ResponseBatch;
    })();
    
    $root.Response = (function() {
    
        /**
         * Properties of a Response.
         * @exports IResponse
         * @interface IResponse
         * @property {OutgoingMessagesResponse|null} [outgoingMessages] Response outgoingMessages
         * @property {ChannelStatsResponse|null} [channelStats] Response channelStats
         * @property {JsonResponse|null} [serverStats] Response serverStats
         */
    
        /**
         * Constructs a new Response.
         * @exports Response
         * @classdesc Represents a Response.
         * @implements IResponse
         * @constructor
         * @param {IResponse=} [properties] Properties to set
         */
        function Response(properties) {
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * Response outgoingMessages.
         * @member {OutgoingMessagesResponse|null|undefined} outgoingMessages
         * @memberof Response
         * @instance
         */
        Response.prototype.outgoingMessages = null;
    
        /**
         * Response channelStats.
         * @member {ChannelStatsResponse|null|undefined} channelStats
         * @memberof Response
         * @instance
         */
        Response.prototype.channelStats = null;
    
        /**
         * Response serverStats.
         * @member {JsonResponse|null|undefined} serverStats
         * @memberof Response
         * @instance
         */
        Response.prototype.serverStats = null;
    
        // OneOf field names bound to virtual getters and setters
        var $oneOfFields;
    
        /**
         * Response command.
         * @member {"outgoingMessages"|"channelStats"|"serverStats"|undefined} command
         * @memberof Response
         * @instance
         */
        Object.defineProperty(Response.prototype, "command", {
            get: $util.oneOfGetter($oneOfFields = ["outgoingMessages", "channelStats", "serverStats"]),
            set: $util.oneOfSetter($oneOfFields)
        });
    
        /**
         * Creates a new Response instance using the specified properties.
         * @function create
         * @memberof Response
         * @static
         * @param {IResponse=} [properties] Properties to set
         * @returns {Response} Response instance
         */
        Response.create = function create(properties) {
            return new Response(properties);
        };
    
        /**
         * Encodes the specified Response message. Does not implicitly {@link Response.verify|verify} messages.
         * @function encode
         * @memberof Response
         * @static
         * @param {Response} message Response message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        Response.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.outgoingMessages != null && message.hasOwnProperty("outgoingMessages"))
                $root.OutgoingMessagesResponse.encode(message.outgoingMessages, writer.uint32(/* id 1, wireType 2 =*/10).fork()).ldelim();
            if (message.channelStats != null && message.hasOwnProperty("channelStats"))
                $root.ChannelStatsResponse.encode(message.channelStats, writer.uint32(/* id 2, wireType 2 =*/18).fork()).ldelim();
            if (message.serverStats != null && message.hasOwnProperty("serverStats"))
                $root.JsonResponse.encode(message.serverStats, writer.uint32(/* id 3, wireType 2 =*/26).fork()).ldelim();
            return writer;
        };
    
        /**
         * Decodes a Response message from the specified reader or buffer.
         * @function decode
         * @memberof Response
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {Response} Response
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        Response.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.Response();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    message.outgoingMessages = $root.OutgoingMessagesResponse.decode(reader, reader.uint32());
                    break;
                case 2:
                    message.channelStats = $root.ChannelStatsResponse.decode(reader, reader.uint32());
                    break;
                case 3:
                    message.serverStats = $root.JsonResponse.decode(reader, reader.uint32());
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return Response;
    })();
    
    $root.OutgoingMessagesResponse = (function() {
    
        /**
         * Properties of an OutgoingMessagesResponse.
         * @exports IOutgoingMessagesResponse
         * @interface IOutgoingMessagesResponse
         * @property {Array.<OutgoingMessage>|null} [messages] OutgoingMessagesResponse messages
         */
    
        /**
         * Constructs a new OutgoingMessagesResponse.
         * @exports OutgoingMessagesResponse
         * @classdesc Represents an OutgoingMessagesResponse.
         * @implements IOutgoingMessagesResponse
         * @constructor
         * @param {IOutgoingMessagesResponse=} [properties] Properties to set
         */
        function OutgoingMessagesResponse(properties) {
            this.messages = [];
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * OutgoingMessagesResponse messages.
         * @member {Array.<OutgoingMessage>} messages
         * @memberof OutgoingMessagesResponse
         * @instance
         */
        OutgoingMessagesResponse.prototype.messages = $util.emptyArray;
    
        /**
         * Creates a new OutgoingMessagesResponse instance using the specified properties.
         * @function create
         * @memberof OutgoingMessagesResponse
         * @static
         * @param {IOutgoingMessagesResponse=} [properties] Properties to set
         * @returns {OutgoingMessagesResponse} OutgoingMessagesResponse instance
         */
        OutgoingMessagesResponse.create = function create(properties) {
            return new OutgoingMessagesResponse(properties);
        };
    
        /**
         * Encodes the specified OutgoingMessagesResponse message. Does not implicitly {@link OutgoingMessagesResponse.verify|verify} messages.
         * @function encode
         * @memberof OutgoingMessagesResponse
         * @static
         * @param {OutgoingMessagesResponse} message OutgoingMessagesResponse message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        OutgoingMessagesResponse.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.messages != null && message.messages.length)
                for (var i = 0; i < message.messages.length; ++i)
                    $root.OutgoingMessage.encode(message.messages[i], writer.uint32(/* id 1, wireType 2 =*/10).fork()).ldelim();
            return writer;
        };
    
        /**
         * Decodes an OutgoingMessagesResponse message from the specified reader or buffer.
         * @function decode
         * @memberof OutgoingMessagesResponse
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {OutgoingMessagesResponse} OutgoingMessagesResponse
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        OutgoingMessagesResponse.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.OutgoingMessagesResponse();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    if (!(message.messages && message.messages.length))
                        message.messages = [];
                    message.messages.push($root.OutgoingMessage.decode(reader, reader.uint32()));
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return OutgoingMessagesResponse;
    })();
    
    $root.OutgoingMessage = (function() {
    
        /**
         * Properties of an OutgoingMessage.
         * @exports IOutgoingMessage
         * @interface IOutgoingMessage
         * @property {Uint8Array|null} [id] OutgoingMessage id
         * @property {string|null} [body] OutgoingMessage body
         * @property {number|null} [expiry] OutgoingMessage expiry
         * @property {number|null} [created] OutgoingMessage created
         * @property {Sender|null} [sender] OutgoingMessage sender
         */
    
        /**
         * Constructs a new OutgoingMessage.
         * @exports OutgoingMessage
         * @classdesc Represents an OutgoingMessage.
         * @implements IOutgoingMessage
         * @constructor
         * @param {IOutgoingMessage=} [properties] Properties to set
         */
        function OutgoingMessage(properties) {
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * OutgoingMessage id.
         * @member {Uint8Array} id
         * @memberof OutgoingMessage
         * @instance
         */
        OutgoingMessage.prototype.id = $util.newBuffer([]);
    
        /**
         * OutgoingMessage body.
         * @member {string} body
         * @memberof OutgoingMessage
         * @instance
         */
        OutgoingMessage.prototype.body = "";
    
        /**
         * OutgoingMessage expiry.
         * @member {number} expiry
         * @memberof OutgoingMessage
         * @instance
         */
        OutgoingMessage.prototype.expiry = 0;
    
        /**
         * OutgoingMessage created.
         * @member {number} created
         * @memberof OutgoingMessage
         * @instance
         */
        OutgoingMessage.prototype.created = 0;
    
        /**
         * OutgoingMessage sender.
         * @member {Sender|null|undefined} sender
         * @memberof OutgoingMessage
         * @instance
         */
        OutgoingMessage.prototype.sender = null;
    
        /**
         * Creates a new OutgoingMessage instance using the specified properties.
         * @function create
         * @memberof OutgoingMessage
         * @static
         * @param {IOutgoingMessage=} [properties] Properties to set
         * @returns {OutgoingMessage} OutgoingMessage instance
         */
        OutgoingMessage.create = function create(properties) {
            return new OutgoingMessage(properties);
        };
    
        /**
         * Encodes the specified OutgoingMessage message. Does not implicitly {@link OutgoingMessage.verify|verify} messages.
         * @function encode
         * @memberof OutgoingMessage
         * @static
         * @param {OutgoingMessage} message OutgoingMessage message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        OutgoingMessage.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.id != null && message.hasOwnProperty("id"))
                writer.uint32(/* id 1, wireType 2 =*/10).bytes(message.id);
            if (message.body != null && message.hasOwnProperty("body"))
                writer.uint32(/* id 2, wireType 2 =*/18).string(message.body);
            if (message.expiry != null && message.hasOwnProperty("expiry"))
                writer.uint32(/* id 3, wireType 0 =*/24).uint32(message.expiry);
            if (message.created != null && message.hasOwnProperty("created"))
                writer.uint32(/* id 4, wireType 5 =*/37).fixed32(message.created);
            if (message.sender != null && message.hasOwnProperty("sender"))
                $root.Sender.encode(message.sender, writer.uint32(/* id 5, wireType 2 =*/42).fork()).ldelim();
            return writer;
        };
    
        /**
         * Decodes an OutgoingMessage message from the specified reader or buffer.
         * @function decode
         * @memberof OutgoingMessage
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {OutgoingMessage} OutgoingMessage
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        OutgoingMessage.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.OutgoingMessage();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    message.id = reader.bytes();
                    break;
                case 2:
                    message.body = reader.string();
                    break;
                case 3:
                    message.expiry = reader.uint32();
                    break;
                case 4:
                    message.created = reader.fixed32();
                    break;
                case 5:
                    message.sender = $root.Sender.decode(reader, reader.uint32());
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return OutgoingMessage;
    })();
    
    $root.ChannelStatsResponse = (function() {
    
        /**
         * Properties of a ChannelStatsResponse.
         * @exports IChannelStatsResponse
         * @interface IChannelStatsResponse
         * @property {Array.<ChannelStats>|null} [channels] ChannelStatsResponse channels
         */
    
        /**
         * Constructs a new ChannelStatsResponse.
         * @exports ChannelStatsResponse
         * @classdesc Represents a ChannelStatsResponse.
         * @implements IChannelStatsResponse
         * @constructor
         * @param {IChannelStatsResponse=} [properties] Properties to set
         */
        function ChannelStatsResponse(properties) {
            this.channels = [];
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * ChannelStatsResponse channels.
         * @member {Array.<ChannelStats>} channels
         * @memberof ChannelStatsResponse
         * @instance
         */
        ChannelStatsResponse.prototype.channels = $util.emptyArray;
    
        /**
         * Creates a new ChannelStatsResponse instance using the specified properties.
         * @function create
         * @memberof ChannelStatsResponse
         * @static
         * @param {IChannelStatsResponse=} [properties] Properties to set
         * @returns {ChannelStatsResponse} ChannelStatsResponse instance
         */
        ChannelStatsResponse.create = function create(properties) {
            return new ChannelStatsResponse(properties);
        };
    
        /**
         * Encodes the specified ChannelStatsResponse message. Does not implicitly {@link ChannelStatsResponse.verify|verify} messages.
         * @function encode
         * @memberof ChannelStatsResponse
         * @static
         * @param {ChannelStatsResponse} message ChannelStatsResponse message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        ChannelStatsResponse.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.channels != null && message.channels.length)
                for (var i = 0; i < message.channels.length; ++i)
                    $root.ChannelStats.encode(message.channels[i], writer.uint32(/* id 1, wireType 2 =*/10).fork()).ldelim();
            return writer;
        };
    
        /**
         * Decodes a ChannelStatsResponse message from the specified reader or buffer.
         * @function decode
         * @memberof ChannelStatsResponse
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {ChannelStatsResponse} ChannelStatsResponse
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        ChannelStatsResponse.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.ChannelStatsResponse();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    if (!(message.channels && message.channels.length))
                        message.channels = [];
                    message.channels.push($root.ChannelStats.decode(reader, reader.uint32()));
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return ChannelStatsResponse;
    })();
    
    $root.ChannelStats = (function() {
    
        /**
         * Properties of a ChannelStats.
         * @exports IChannelStats
         * @interface IChannelStats
         * @property {Uint8Array|null} [id] ChannelStats id
         * @property {boolean|null} [isPrivate] ChannelStats isPrivate
         * @property {boolean|null} [isOnline] ChannelStats isOnline
         */
    
        /**
         * Constructs a new ChannelStats.
         * @exports ChannelStats
         * @classdesc Represents a ChannelStats.
         * @implements IChannelStats
         * @constructor
         * @param {IChannelStats=} [properties] Properties to set
         */
        function ChannelStats(properties) {
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * ChannelStats id.
         * @member {Uint8Array} id
         * @memberof ChannelStats
         * @instance
         */
        ChannelStats.prototype.id = $util.newBuffer([]);
    
        /**
         * ChannelStats isPrivate.
         * @member {boolean} isPrivate
         * @memberof ChannelStats
         * @instance
         */
        ChannelStats.prototype.isPrivate = false;
    
        /**
         * ChannelStats isOnline.
         * @member {boolean} isOnline
         * @memberof ChannelStats
         * @instance
         */
        ChannelStats.prototype.isOnline = false;
    
        /**
         * Creates a new ChannelStats instance using the specified properties.
         * @function create
         * @memberof ChannelStats
         * @static
         * @param {IChannelStats=} [properties] Properties to set
         * @returns {ChannelStats} ChannelStats instance
         */
        ChannelStats.create = function create(properties) {
            return new ChannelStats(properties);
        };
    
        /**
         * Encodes the specified ChannelStats message. Does not implicitly {@link ChannelStats.verify|verify} messages.
         * @function encode
         * @memberof ChannelStats
         * @static
         * @param {ChannelStats} message ChannelStats message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        ChannelStats.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.id != null && message.hasOwnProperty("id"))
                writer.uint32(/* id 1, wireType 2 =*/10).bytes(message.id);
            if (message.isPrivate != null && message.hasOwnProperty("isPrivate"))
                writer.uint32(/* id 2, wireType 0 =*/16).bool(message.isPrivate);
            if (message.isOnline != null && message.hasOwnProperty("isOnline"))
                writer.uint32(/* id 3, wireType 0 =*/24).bool(message.isOnline);
            return writer;
        };
    
        /**
         * Decodes a ChannelStats message from the specified reader or buffer.
         * @function decode
         * @memberof ChannelStats
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {ChannelStats} ChannelStats
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        ChannelStats.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.ChannelStats();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    message.id = reader.bytes();
                    break;
                case 2:
                    message.isPrivate = reader.bool();
                    break;
                case 3:
                    message.isOnline = reader.bool();
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return ChannelStats;
    })();
    
    $root.JsonResponse = (function() {
    
        /**
         * Properties of a JsonResponse.
         * @exports IJsonResponse
         * @interface IJsonResponse
         * @property {string|null} [json] JsonResponse json
         */
    
        /**
         * Constructs a new JsonResponse.
         * @exports JsonResponse
         * @classdesc Represents a JsonResponse.
         * @implements IJsonResponse
         * @constructor
         * @param {IJsonResponse=} [properties] Properties to set
         */
        function JsonResponse(properties) {
            if (properties)
                for (var keys = Object.keys(properties), i = 0; i < keys.length; ++i)
                    if (properties[keys[i]] != null)
                        this[keys[i]] = properties[keys[i]];
        }
    
        /**
         * JsonResponse json.
         * @member {string} json
         * @memberof JsonResponse
         * @instance
         */
        JsonResponse.prototype.json = "";
    
        /**
         * Creates a new JsonResponse instance using the specified properties.
         * @function create
         * @memberof JsonResponse
         * @static
         * @param {IJsonResponse=} [properties] Properties to set
         * @returns {JsonResponse} JsonResponse instance
         */
        JsonResponse.create = function create(properties) {
            return new JsonResponse(properties);
        };
    
        /**
         * Encodes the specified JsonResponse message. Does not implicitly {@link JsonResponse.verify|verify} messages.
         * @function encode
         * @memberof JsonResponse
         * @static
         * @param {JsonResponse} message JsonResponse message or plain object to encode
         * @param {$protobuf.Writer} [writer] Writer to encode to
         * @returns {$protobuf.Writer} Writer
         */
        JsonResponse.encode = function encode(message, writer) {
            if (!writer)
                writer = $Writer.create();
            if (message.json != null && message.hasOwnProperty("json"))
                writer.uint32(/* id 1, wireType 2 =*/10).string(message.json);
            return writer;
        };
    
        /**
         * Decodes a JsonResponse message from the specified reader or buffer.
         * @function decode
         * @memberof JsonResponse
         * @static
         * @param {$protobuf.Reader|Uint8Array} reader Reader or buffer to decode from
         * @param {number} [length] Message length if known beforehand
         * @returns {JsonResponse} JsonResponse
         * @throws {Error} If the payload is not a reader or valid buffer
         * @throws {$protobuf.util.ProtocolError} If required fields are missing
         */
        JsonResponse.decode = function decode(reader, length) {
            if (!(reader instanceof $Reader))
                reader = $Reader.create(reader);
            var end = length === undefined ? reader.len : reader.pos + length, message = new $root.JsonResponse();
            while (reader.pos < end) {
                var tag = reader.uint32();
                switch (tag >>> 3) {
                case 1:
                    message.json = reader.string();
                    break;
                default:
                    reader.skipType(tag & 7);
                    break;
                }
            }
            return message;
        };
    
        return JsonResponse;
    })();

    return $root;
})(protobuf);
