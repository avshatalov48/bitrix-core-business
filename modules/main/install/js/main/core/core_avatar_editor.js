(function() {
	var BX = window.BX;
	var avatarEditorTabs = (function(){
		var d = function(node, tabs){
			this.node = node;
			this.tabs = {};
			this.repo = [];
			if (BX.type.isArray(tabs))
			{
				var tab, last;
				while ((tab = tabs.shift()) && tab)
				{
					if (this.add(tab))
						last = tab;
				}
				this.show(last);
			}
		};
		d.prototype = {
			add : function(tab) {
				var head, body;
				head = BX.findChild(this.node, {attribute : {"data-bx-role" : 'tab-' + tab}}, true);
				body = BX.findChild(this.node, {attribute : {"data-bx-role" : 'tab-' + tab + '-body'}}, true);
				if (head && body)
				{
					this.tabs[tab] = {
						head : head,
						body : body
					};
					BX.bind(head, "click", BX.proxy(function(){ this.show(tab) }, this));
					return true;
				}
				return false;
			},
			show : function(tab, header) {
				if (this.tabs.hasOwnProperty(tab))
				{
					var was = '';
					for(var ii in this.tabs)
					{
						if (this.tabs.hasOwnProperty(ii))
						{
							if (this.tabs[ii]["head"].hasAttribute("active"))
								was = ii;

							if (ii !== tab)
							{
								this.tabs[ii]["head"].removeAttribute("active");
								if (!header || header !== ii)
									BX.removeClass(this.tabs[ii]["head"], "main-file-input-tab-button-active");

								BX.hide(this.tabs[ii]["body"]);
							}
						}
					}
					this.tabs[tab]["head"].setAttribute("active", "Y");
					BX.addClass(this.tabs[tab]["head"], "main-file-input-tab-button-active");
					if (header && this.tabs[header])
						BX.addClass(this.tabs[header]["head"], "main-file-input-tab-button-active");

					BX.show(this.tabs[tab]["body"]);
					if (was !== tab)
					{
						BX.onCustomEvent(this, "onTabHasBeenChanged", [tab, was, this]);
						this.repo.push(header||tab);
					}
				}
			},
			showPrevious : function() {
				this.repo.pop();
				var tab = this.repo.pop();
				if (tab)
					this.show(tab);
			},
			getActive : function() {
				var tab = '';
				for(var ii in this.tabs)
				{
					if (this.tabs.hasOwnProperty(ii))
					{
						if (BX.hasClass(this.tabs[ii]["head"], "main-file-input-tab-button-active"))
						{
							tab = ii;
							break;
						}
					}
				}
				return tab;
			}
		};
		return d;
	})(),
		webRTC = null,
		avatarEditorZoom = (function(){
		var d = function(params) {
			this.scale = params.scale;
			this.knob = params.knob;
			this.minus = params.minus;
			this.plus = params.plus;
			this.moveKnob = null;
			BX.bind(this.minus, "click", BX.proxy(this.decrease, this));
			BX.bind(this.plus, "click", BX.proxy(this.increase, this));
			BX.bind(this.knob, "mousedown", BX.proxy(this.startMoving, this));
			this.move = BX.delegate(this.move, this);
			this.stopMoving = BX.delegate(this.stopMoving, this);
		};
		d.prototype = {
			step : 0.1,
			init : function() {

			},
			bind : function() {

			},
			reset : function() {
				this.move();
			},
			increase : function() {
				this.move(true);
			},
			decrease : function() {
				this.move(false);
			},
			startMoving : function() {
				BX.bind(document, "mousemove", this.move);
				BX.bind(document, "mouseup", this.stopMoving);
			},
			move : function (e) {
				var v = { x : 0, percent : 0 }, size1, size2;
				if (e === true || e === false)
				{
					var percent = parseFloat(this.knob.getAttribute("data-bx-percent"));
					if (!(percent > 0))
						percent = 0;
					percent += (e === true ? 1 : (-1)) * this.step;
					if (!this.moveKnob2)
					{
						size1 = BX.pos(this.scale);
						size2 = BX.pos(this.knob);
						this.moveKnob2 = function(percent) {
							var p = Math.min(
								Math.max(percent, 0),
								1
							);
							return { x : Math.ceil((size1["width"] - size2["width"]) * p), percent : p };
						}
					}
					v = this.moveKnob2(percent);
				}
				else if (e)
				{
					if (!this.moveKnob)
					{
						size1 = BX.pos(this.scale);
						size2 = BX.pos(this.knob);
						this.moveKnob = function(pageX) {
							var x = Math.min(
								Math.max((pageX - size1["left"]), 0),
								(size1["width"] - size2["width"])
							);
							return {x : Math.ceil(x), percent : x / Math.max((size1["width"] - size2["width"]), 1)};
						};
					}
					BX.fixEventPageXY(e);
					v = this.moveKnob(e.pageX);
				}
				BX.adjust(this.knob, { style : { left : v.x + 'px' }, attrs : { "data-bx-percent" : v.percent }});
				BX.onCustomEvent(this, "onChangeSize", [v.percent]);
			},
			stopMoving : function() {
				BX.unbind(document, "mousemove", this.move);
				BX.unbind(document, "mouseup", this.stopMoving);
			}
		};
		return d;
	})(),
		repo = {},
		canvasConstructor = (function(){
				if (!repo["canvasConstructor"])
					repo["canvasConstructor"] = new BX.UploaderFileCnvConstr();
				return repo["canvasConstructor"];
			})(),
		canvasMaster = (function(){
		var d = function(canvas) {
			if (!BX(canvas))
				throw "BX.canvasEditor: Canvas is not a DOM node.";
			this.canvas = canvas;
			this.ctx = this.canvas.getContext("2d");
			this.canvasBlock = canvas.parentNode;
			this.params = {
				scaleMultiplier : 1,
				visibleWidth : (canvas.width > 0 ? canvas.width : 1),
				visibleHeight : (canvas.height > 0 ? canvas.height : 1),
				width : canvas.width,
				height : canvas.height
			};
			this.startMoving = BX.delegate(this.startMoving, this);
			this.move = BX.delegate(this.move, this);
			this.stopMoving = BX.delegate(this.stopMoving, this);

			this.reset();
		};
		d.prototype = {
			reset : function() {
				this.canvasIsSet = false;
				this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
				this.disableToMove();
				this.canvas.style.cursor = "default";
				BX.onCustomEvent(this, "onResetCanvas", [this.canvas]);
			},
			set : function(video) {
				var w, h, k;
				if (video.clientWidth)
				{
					w = video.clientWidth;
					h = video.clientHeight;
				}
				else
				{
					var props = BX.UploaderUtils.scaleImage(video, {width : 1024, height : 1024 });
					w = props.destin.width;
					h = props.destin.height;
				}
				if (video["name"])
					this.params.name = video["name"];
				else
					delete this.params["name"];

				k = Math.ceil(Math.max(
					( w > 0 ? this.params.visibleWidth / w : 1 ),
					( h > 0 ? this.params.visibleHeight / h : 1 )
				) * 100) / 100;

				this.params.scale = (0 < k && k < 1 ? k : 1);

				this.params.width = w;
				this.params.height = h;
				delete this.params.left;
				delete this.params.top;
				delete this.params.zeroLeft;
				delete this.params.zeroTop;
				delete this.params.overWidth;
				delete this.params.overHeight;

				this.cursor = null;

				this.params.zoomScale = 1;

				BX.adjust(this.canvas, { props : { width : this.params.width, height : this.params.height } });
				this.ctx.drawImage(video, 0, 0, this.canvas.width, this.canvas.height);

				this.canvasIsSet = true;

				this.calculateOffsets({});

				this.params.firstScale = this.params.zoomScale;
				this.params.firstLeft = this.params.left;
				this.params.firstTop = this.params.top;

				BX.onCustomEvent(this, "onChangeCanvas", [this.canvas, {
					width : this.params.width,
					height : this.params.height,
					left : ( this.params.left - this.params.zeroLeft ) / this.params.scale,
					top : ( this.params.top  - this.params.zeroTop ) / this.params.scale,
					scale : this.params.zoomScale,
					maxScale : 1 + (1 - this.params.scale) / this.params.scale
				}]);
			},
			load: function(file) {
				if (!this.onLoadFile)
				{
					this.onLoadFile = BX.delegate(this.set, this);
				}
				if (!this.onLoadingFileIsFailed)
				{
					this.onLoadingFileIsFailed = BX.delegate(function() {
						this.showError(BX.message("JS_AVATAR_EDITOR_ERROR_IMAGE_DEPLOYING"), arguments);
					}, this);
				}
				this.reset();
				canvasConstructor.push(file, this.onLoadFile, this.onLoadingFileIsFailed);
			},
			showError : function(textMessage, args) {
				BX.onCustomEvent(this, "onErrorCanvas", [textMessage, args]);
			},
			scale : function(scale) {
				if (this.params.scale < 1)
				{
					this.calculateOffsets({zoomScale : 1 + (1 - this.params.scale) * scale / this.params.scale});

					BX.onCustomEvent(this, "onChangeCanvasArea", [{
						left : ( this.params.left - this.params.zeroLeft ) / this.params.scale,
						top : ( this.params.top  - this.params.zeroTop ) / this.params.scale,
						scale : this.params.zoomScale
					}]);
				}
			},
			calculateOffsets : function(params) {
				var oldZeroLeft = this.params.zeroLeft, //220
					oldZeroTop = this.params.zeroTop,
					oldZoomScale = this.params.zoomScale;

				if (params["zoomScale"])
					this.params.zoomScale = Math.ceil(params.zoomScale * 100) / 100;

				this.params.zeroLeft = (this.params.width * this.params.scale * this.params.zoomScale - this.params.width) / 2 ;
				this.params.zeroTop = (this.params.height * this.params.scale * this.params.zoomScale - this.params.height) / 2;
				this.params.overWidth = (this.params.width * this.params.scale * this.params.zoomScale - this.params.visibleWidth) / 2;
				this.params.overHeight = (this.params.height * this.params.scale * this.params.zoomScale - this.params.visibleHeight) / 2;

				if (params["zoomScale"])
				{
					var
						newLeft = this.params.zeroLeft + ((( this.params.left - oldZeroLeft - this.params.visibleWidth / 2 ) / oldZoomScale) * this.params.zoomScale) + this.params.visibleWidth / 2,
						newTop = this.params.zeroTop + (( this.params.top - this.params.visibleHeight / 2 - oldZeroTop ) / oldZoomScale) * this.params.zoomScale + this.params.visibleHeight / 2;

					this.params.left = this.params.zeroLeft - this.params.overWidth;
					if (this.params.overWidth > 0)
						this.params.left = Math.min(
								this.params.zeroLeft,
								Math.max(
									this.params.zeroLeft - this.params.overWidth * 2,
									newLeft
								)
							);

					this.params.top = this.params.zeroTop - this.params.overHeight;
					if (this.params.overHeight > 0)
						this.params.top = Math.min(
							this.params.zeroTop,
							Math.max((this.params.zeroTop - this.params.overHeight * 2),
								newTop)
						);
				}
				else
				{
					this.params.left = this.params.zeroLeft - this.params.overWidth;
					this.params.top = this.params.zeroTop - this.params.overHeight;
				}

				var cursor;
				if (this.params.overWidth > 0 || this.params.overHeight > 0)
				{
					cursor = "move";
					this.enableToMove();
				}
				else
				{
					cursor = "default";
					this.disableToMove();
				}

				this.canvas.style.cursor = cursor;

				BX.adjust(
					BX(this.canvasBlock), {style : {
						width : this.params.width + 'px',
						height : this.params.height + 'px',
						transform : 'translate(' +
						Math.ceil(this.params.left) + 'px, ' +
						Math.ceil(this.params.top) + 'px) scale(' + this.params.scale * this.params.zoomScale + ', ' + this.params.scale * this.params.zoomScale + ')'
					}}
				);
			},
			enableToMove : function() {
				BX.bind(this.canvasBlock, "mousedown", this.startMoving);
			},
			disableToMove : function() {
				BX.unbindAll(this.canvasBlock);
			},
			startMoving : function(e) {
				BX.fixEventPageXY(e);
				this.cursor = {
					pageX : e.pageX,
					pageY : e.pageY
				};
				BX.bind(document, "mousemove", this.move);
				BX.bind(document, "mouseup", this.stopMoving);
			},
			move : function (e) {
				if (this.cursor !== null)
				{
					BX.fixEventPageXY(e);
					if (this.params.overWidth > 0)
					{
						this.params.left = Math.min(
							this.params.zeroLeft,
							Math.max(
								this.params.zeroLeft - this.params.overWidth * 2,
								(this.params.left + e.pageX - this.cursor.pageX)
							)
						);
						this.cursor.pageX = e.pageX;
					}
					if (this.params.overHeight > 0)
					{
						this.params.top = Math.min(
							this.params.zeroTop,
							Math.max((this.params.zeroTop - this.params.overHeight * 2),
								this.params.top + e.pageY - this.cursor.pageY)
						);
						this.cursor.pageY= e.pageY;
					}
					BX.adjust(this.canvasBlock, { style : { transform : 'translate(' + Math.ceil(this.params.left) + 'px, ' + Math.ceil(this.params.top) + 'px) scale(' +
						this.params.scale * this.params.zoomScale + ', ' +
						this.params.scale * this.params.zoomScale +
					')'} });
					BX.onCustomEvent(this, "onChangeCanvasArea", [{
						left : ( this.params.left - this.params.zeroLeft ) / this.params.scale,
						top : ( this.params.top  - this.params.zeroTop ) / this.params.scale,
						scale : this.params.zoomScale
					}]);
				}
			},
			stopMoving : function() {
				BX.unbind(document, "mousemove", this.move);
				BX.unbind(document, "mouseup", this.stopMoving);
			},
			pack : function() {
				var result = null;
				if (this.canvasIsSet)
				{
					if (
						this.params.firstScale === this.params.zoomScale &&
						this.params.firstLeft === this.params.left &&
						this.params.firstTop === this.params.top
					)
					{
						result = BX.UploaderUtils.dataURLToBlob(this.canvas.toDataURL('image/png'));
						result.changed = false;
					}
					else
					{
						var scale = this.params.zoomScale * this.params.scale,
							left = Math.ceil((this.params.left - this.params.zeroLeft) / scale),
							top = Math.ceil((this.params.top - this.params.zeroTop) / scale),
							width = Math.ceil(this.params.visibleWidth / scale),
							height = Math.ceil(this.params.visibleHeight / scale);
						if (left > 0)
						{
							width -= left;
							left = 0;
						}
						if (top > 0)
						{
							height -= top;
							top = 0;
						}

						if (width <= 0 || height <= 0)
							throw "BX.canvasEditor: width or height is undefined.";

						BX.adjust(canvasConstructor.getCanvas(), { props : { width : width, height : height } } );
						canvasConstructor.getContext().drawImage(this.canvas, Math.abs(left), Math.abs(top), width, height, 0, 0, width, height);

						result = canvasConstructor.pack();
						result.changed = true;
					}
					if (this.params["name"])
						result.name = this.params["name"];
				}
				return result;
			}
		};
		return d;
	})(),
		canvasPreview = (function() {
			var d = function(canvas) {
				if (!BX(canvas))
					throw "Canvas is not a DOM node.";

				this.canvas = canvas;
				this.ctx = this.canvas.getContext("2d");
				this.canvasBlock = canvas.parentNode;
				this.params = {
					scaleMultiplier : 2,
					visibleWidth : (canvas.width > 0 ? canvas.width : 1),
					visibleHeight : (canvas.height > 0 ? canvas.height : 1),
					width : canvas.width,
					height : canvas.height
				};
			};
			d.prototype = {
				set : function(canvas, params) {
					var w = params.width,
						h = params.height,
						visibleSize = {
							width : this.params.visibleWidth,
							height : this.params.visibleHeight
						},
						k = Math.max(
							( w > 0 ? visibleSize.width * params.maxScale / w : 1 ),
							( h > 0 ? visibleSize.height * params.maxScale / h : 1 )
						);

					this.params.scaleMultiplier = params.maxScale;
					if (k > 1)
					{
						k = Math.max(
							( w > 0 ? visibleSize.width / w : 1 ),
							( h > 0 ? visibleSize.height / h : 1 )
						);
						this.params.scaleMultiplier = 1;
					}
					k = (0 < k && k < 1 ? k : 1);

					this.params.width = w * k;
					this.params.height = h * k;

					this.params.scale = k;

					this.position({scale : 1});

					BX.adjust(this.canvas, { props : { width : this.params.width, height : this.params.height } });
					this.ctx.drawImage(canvas, 0, 0, this.canvas.width, this.canvas.height);
				},
				position : function(params) {
					this.params.zoomScale = params.scale;

					this.params.overWidth = (this.params.width * this.params.zoomScale - this.params.visibleWidth) / 2;
					this.params.overHeight = (this.params.height * this.params.zoomScale - this.params.visibleHeight) / 2;

					this.params.zeroLeft = ( this.params.width * this.params.zoomScale / this.params.scaleMultiplier  - this.params.width) / 2;
					this.params.zeroTop = ( this.params.height * this.params.zoomScale / this.params.scaleMultiplier - this.params.height) / 2;

					this.params.left = this.params.zeroLeft - this.params.overWidth;
					if (this.params.overWidth > 0)
						this.params.left = (params.left * this.params.scale / this.params.scaleMultiplier) + this.params.zeroLeft;

					this.params.top = this.params.zeroTop - this.params.overHeight;
					if (this.params.overHeight > 0)
						this.params.top = (params.top * this.params.scale / this.params.scaleMultiplier) + this.params.zeroTop;

					BX.adjust(this.canvasBlock, {style : {
							width : this.params.width + 'px',
							height : this.params.height + 'px',
							transform : 'translate(' + Math.ceil(this.params.left) + 'px, ' + Math.ceil(this.params.top) + 'px) scale(' +
								this.params.zoomScale / this.params.scaleMultiplier + ', ' + this.params.zoomScale / this.params.scaleMultiplier + ')'
						}});
				},
				reset : function() {
					this.ctx.clearRect(0, 0, this.canvas.width, this.canvas.height);
				}
			};
			return d;
		})();
	BX.AvatarEditor = (function(){
		var video;
		var d = function(params){
			this.id = 'avatarEditor' + (new Date()).valueOf();
			this.popup = null;
			this.handlers = {
				apply : BX.delegate(this.apply, this),
				cancel : BX.delegate(this.cancel, this),
				onPopupShow : BX.delegate(this.onPopupShow, this),
				onAfterPopupShow : BX.delegate(this.onAfterPopupShow, this),
				onPopupClose : BX.delegate(this.onPopupClose, this)
			};
			params = (BX.type.isPlainObject(params) ? params : {});
			this.params = { enableCamera : params["enableCamera"] !== false };

			this.limitations = []; // TODO make control to work with file limits
		};
		d.prototype = {
			isCameraEnabled : function() {
				var res = false;
				if (this.params.enableCamera === true)
				{
					if (webRTC === null && BX["webrtc"])
						webRTC = new BX.webrtc();
					res = ((window.location.protocol.indexOf("https") === 0) && webRTC && webRTC.enabled);
				}
				return res;
			},
			getLimitText : function() {
				// TODO make text interface for limits
				return '';
			},
			getTemplate : function() {
				var bodies = [],
					headers = [];
				headers.push(
					'<span class="main-file-input-tab-button-item" data-bx-role="tab-canvas" style="display:none;">Canvas</span>'
				);
				bodies.push([
					'<div class="main-file-input-content-block main-file-input-canvas-block" data-bx-role="tab-canvas-body" style="display: none;">',
						'<div class="main-file-input-control">',
							'<div class="main-file-input-control-controller" data-bx-role="zoom-minus-button">',
								'<span class="main-file-input-control-minus"></span>',
							'</div>',
							'<div class="main-file-input-control-inner" data-bx-role="zoom-scale">',
								'<div class="main-file-input-control-slide-container main-file-input-control-slide-drag-state">',
									'<div class="main-file-input-control-slide" data-bx-role="zoom-knob"></div>',
								'</div>',
							'</div>',
							'<div class="main-file-input-control-controller" data-bx-role="zoom-plus-button">',
								'<span class="main-file-input-control-plus"></span>',
							'</div>',
						'</div>',
						'<div class="main-file-input-camera-block-image">',
							'<div class="main-file-input-user-loader-item">',
								'<div class="main-file-input-loader">',
									'<svg class="main-file-input-circular" viewBox="25 25 50 50">',
										'<circle class="main-file-input-path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"></circle>',
									'</svg>',
								'</div>',
							'</div>',
							'<div class="main-file-input-error">',
								'<span>',
									BX.message("JS_AVATAR_EDITOR_ERROR"),
								'</span>',
								'<span data-bx-role="tab-canvas-error"></span>',
							'</div>',
							'<div>', // Service block
								'<canvas data-bx-canvas="canvas" height="330" width="330"></canvas>',
							'</div>',
						'</div>',
						'<div class="main-file-input-button-layout">',
							'<div class="main-file-input-button" data-bx-role="try-again-button">',
								'<span class="main-file-input-button-icon"></span>',
								'<span class="main-file-input-button-name">', BX.message("JS_AVATAR_EDITOR_TRY_AGAIN"), '</span>',
							'</div>',
						'</div>',
					'</div>'
				].join(''));
				headers.push(
					'<span class="main-file-input-tab-button-item' +
						(window.location.protocol.indexOf("https") === 0 ? "" : " main-file-input-tab-button-active") +
						'" data-bx-role="tab-file">' + BX.message("JS_AVATAR_EDITOR_FILE") + '</span>'
				);
				bodies.push([
				'<div class="main-file-input-content-block main-file-input-upload-block" data-bx-role="tab-file-body" ',
					(window.location.protocol.indexOf("https") === 0 ? ' style="display: none;" ': ''), '>',
					'<div class="main-file-input-upload-link-container">',
						'<label for="file', this.id,'" class="main-file-input-upload-link" for="file', this.id,'">', BX.message("JS_AVATAR_EDITOR_PICK_UP_THE_FILE"),
							'<input type="file" id="file', this.id,'" data-bx-role="file-button" accept="image/*" />',
						'</label>',
						'<div class="main-file-input-upload-desc">', BX.message("JS_AVATAR_EDITOR_DROP_FILES_INTO_THIS_AREA"), '</div>',
					'</div>',
					'<div class="main-file-input-upload-info">',
						'<div class="main-file-input-upload-info-item">', this.getLimitText(), '</div>',
					'</div>',
				'</div>'
				].join(''));
				if (this.isCameraEnabled())
				{
					headers.push(
						'<span class="main-file-input-tab-button-item main-file-input-tab-button-active" data-bx-role="tab-camera">' + BX.message("JS_AVATAR_EDITOR_CAMERA") + '</span>'
					);
					bodies.push([
					'<div class="main-file-input-content-block main-file-input-camera-block" data-bx-role="tab-camera-body">',
						'<div class="main-file-input-camera-block-image">',
							'<div class="main-file-input-user-loader-item">',
								'<div class="main-file-input-loader">',
									'<svg class="main-file-input-circular" viewBox="25 25 50 50">',
										'<circle class="main-file-input-path" cx="50" cy="50" r="20" fill="none" stroke-width="1" stroke-miterlimit="10"/>',
									'</svg>',
								'</div>',
							'</div>',
							'<div class="main-file-input-error">',
								'<span>',
									BX.message("JS_AVATAR_EDITOR_ERROR"),
								'</span>',
								'<span data-bx-role="tab-camera-error"></span>',
							'</div>',
							'<div class="main-file-input-camera-block-image-inner">',
								'<video autoplay></video>',
							'</div>',
						'</div>',
						'<div class="main-file-input-button-layout" data-bx-role="camera-button">',
							'<div class="main-file-input-button">',
								'<span class="main-file-input-button-icon"></span>',
							'</div>',
						'</div>',
					'</div>'
					].join(''));
				}

				var html = [
					'<div class="main-file-input-tab-wrapper">',
						'<div class="main-file-input-tab-button-container"', (headers.length <= 2 ? ' style="display: none"' : ''), '>',
							headers.join(''),
						'</div>',
						'<div class="main-file-input-tab-container">',
							bodies.join(''),
							'<div class="main-file-input-tab-avatar-block">',
								'<div class="main-file-input-tab-avatar-inner">',
									'<div class="main-file-input-arrow-icon-container">',
										'<span class="main-file-input-arrow-icon"></span>',
									'</div>',
									'<div class="main-file-input-tab-avatar-image-container">',
										'<span class="main-file-input-tab-avatar-image-item" data-bx-role="canvas-button">',
											'<div>', // Service block
												'<canvas data-bx-canvas="preview" height="136" width="136"></canvas>',
											'</div>',
										'</span>',
									'</div>',
									'<div class="main-file-input-tab-avatar-desc-container">',
										'<span class="main-file-input-tab-avatar-desc-item">', /*"Preview",*/ '</span>',
									'</div>',
								'</div>',
							'</div>',
						'</div>',
					'</div>'
				].join('');
				return html.replace(/#id#/gi, this.id);
			},
			onTabHasBeenChanged : function(active, was, tabObject) {
				var node = BX(this.id);
				video = BX.findChild(node, {tagName : "VIDEO"}, true);
				if (tabObject.tabs[active])
					BX.removeClass(tabObject.tabs[active]["body"], "errored");

				if (BX(video))
				{
					if (active === "camera" && video.getAttribute("active") !== "Y")
					{
						if (!video.hasAttribute("data-bx-bound"))
						{
							video.setAttribute("data-bx-bound", "Y");
							var visibleWidth = video.parentNode.clientWidth,
								visibleHeight = video.parentNode.clientHeight;
							video.addEventListener("playing", function () {
								var
									w = video.clientWidth,
									h = video.clientHeight,
									scale = Math.max(
										( w > 0 ? visibleWidth / w : 1 ),
										( h > 0 ? visibleHeight / h : 1 )
									),
									left = (w * scale - w) / 2 + (visibleWidth - w * scale) / 2,
									top = (h * scale - h) / 2 + (visibleHeight - h * scale) / 2;
								BX.adjust(
									video.parentNode, {style : {
										width : w + 'px',
										height : h + 'px',
										transform : 'translate(' +
										Math.ceil(left) + 'px, ' +
										Math.ceil(top) + 'px) scale(' + scale + ', ' + scale + ')'
									}}
								);
							});
						}

						video.setAttribute("active", "Y");
						navigator.mediaDevices.getUserMedia({
							audio: false,
							video: {
								width: {max: 1024, min: 640, ideal: 1024},
								height: {max: 860, min: 480, ideal: 860}
							}
						}).then(function(stream) {
							if (video.hasAttribute("active"))
							{
								video.srcObject = stream;
							}
							else
							{
								stream.getTracks()[0].stop();
							}
						}).catch(function(error) {
							if (tabObject.tabs[active])
								BX.addClass(tabObject.tabs[active]["body"], "errored");
							var errorNode = BX.findChild(node, {attribute : {"data-bx-role" : "tab-camera-error" }}, true);
							if (errorNode)
								errorNode.innerHTML = error;
						});
					}
					else if (video.getAttribute("active") === "Y")
					{
						video.removeAttribute("active");
						video.pause();
						video.src = "";
						if (video.srcObject)
						{
							video.srcObject.getTracks()[0].stop();
						}
					}
				}
			},
			addFiles : function(files) {
				// reinit fileInput to release file handler
				if (!BX.type.isArray(files))
				{
					var result = [];
					for (var j=0; j < files.length; j++)
					{
						result.push(files[j]);
					}
					files = result;
				}
				var file;
				var loader = BX.findChild(BX(this.id), {
					tagName : "DIV", className : "main-file-input-user-loader-item"}, true);
				if ((file = files.pop()) && file && this.canvas && BX.UploaderUtils.isImage(file.name, file.type, file.size))
				{
					this.canvas.load(file);
					this.tabs.show('canvas', 'file');
					BX.hide(loader);
				}
				else
				{
					BX.show(loader);
					// TODO Show some error
				}
			},
			bindTemplate : function() {
				var node = BX(this.id);
				this.tabs = new avatarEditorTabs(node, ['canvas', 'file', 'camera']);
				BX.addCustomEvent(this.tabs, "onTabHasBeenChanged", BX.delegate(this.onTabHasBeenChanged, this));
				this.onTabHasBeenChanged(this.tabs.getActive(), null, this.tabs);
				this.canvas = new canvasMaster(BX.findChild(node, {tagName : "CANVAS", attribute : { "data-bx-canvas"  : "canvas" } }, true));
				this.preview = new canvasPreview(BX.findChild(node, {tagName : "CANVAS", attribute : { "data-bx-canvas"  : "preview" } }, true));
				BX.addCustomEvent(this.canvas, "onChangeCanvas", BX.proxy(this.preview.set, this.preview));
				BX.addCustomEvent(this.canvas, "onChangeCanvasArea", BX.proxy(this.preview.position, this.preview));
				BX.addCustomEvent(this.canvas, "onResetCanvas", BX.proxy(this.preview.reset, this.preview));
				BX.addCustomEvent(this.canvas, "onErrorCanvas", BX.proxy(function(textMessage, args){
					var tabObj = this.tabs;
					BX.addClass(tabObj.tabs['canvas']['body'], "errored");
					var n = BX.findChild(tabObj.tabs['canvas']['body'], { attribute : { "data-bx-role" : "tab-canvas-error" } }, true);
					n.innerHTML = textMessage;
				}, this));

				var n = BX.findChild(node, {attribute : { "data-bx-role"  : "canvas-button" } }, true);
				if (n)
				{
					BX.bind(n, "click", BX.proxy(function() { if (this.canvas.canvasIsSet === true){ this.tabs.show('canvas'); } }, this));
					BX.addCustomEvent(this.canvas, "onChangeCanvas", BX.proxy(function(){ BX.addClass(n, "active") }, this));
					BX.addCustomEvent(this.canvas, "onResetCanvas", BX.proxy(function(){ BX.removeClass(n, "active") }, this));
				}
				n = BX.findChild(node, {attribute : { "data-bx-role"  : "try-again-button" } }, true);
				if (n)
				{
					BX.bind(n, "click", BX.proxy(function(){
						this.canvas.reset();
						this.tabs.showPrevious();
					}, this));
				}

				var button = BX.findChild(node, {attr : {"data-bx-role" : "camera-button"}}, true);
				if (button)
				{
					button.onclick = BX.delegate(function(e) {
						if (this.canvas)
							this.canvas.set(video);
						this.tabs.show('canvas', 'camera');
						return BX.PreventDefault(e);
					}, this);
				}
				var knob = BX.findChild(node, {attr : {"data-bx-role" : "zoom-knob"}}, true),
					scale = BX.findChild(node, {attr : {"data-bx-role" : "zoom-scale"}}, true),
					plus = BX.findChild(node, {attr : {"data-bx-role" : "zoom-plus-button"}}, true),
					minus = BX.findChild(node, {attr : {"data-bx-role" : "zoom-minus-button"}}, true);
				if (knob && scale && plus && minus)
				{
					var zoomNode = BX.findChild(node, {tagName : "DIV", className : "main-file-input-control"}, true),
						zoom = new avatarEditorZoom( { scale : scale, knob : knob, plus : plus, minus : minus } );
					BX.addCustomEvent(zoom, "onChangeSize", BX.proxy(function(percent) { this.canvas.scale(percent); }, this));
					BX.addCustomEvent(this.canvas, "onChangeCanvas", BX.proxy(function(c, params){
						if (params.left >= 0 && params.top >= 0)
							BX.hide(zoomNode);
						else
							BX.show(zoomNode);
						this.reset();
					}, zoom));
				}

				var file = BX.findChild(node, {tagName : "INPUT", attr : {type: "file", "data-bx-role" : "file-button" } }, true);
				if (file)
				{
					var f = BX.delegate(function(e){
						BX.PreventDefault(e);
						var files,
							file = BX.findChild(BX(this.id), {tagName : "INPUT", attr : {type: "file", "data-bx-role" : "file-button" } }, true);
						if (e && e.target)
							files = e.target.files;
						else if (e && BX(file))
							files = file.files;
						this.addFiles(files);
						if (!BX(file))
							return;
						BX.unbindAll(file);
						var node = file.cloneNode(true, {value : ""});
						BX.adjust(node, {
							props : {
								value : ""
							},
							attrs: {
								value : ""
							}});
						node.setAttribute("new", "Y" + (new Date()).valueOf());
						file.parentNode.insertBefore(node, file);
						file.parentNode.removeChild(file);
						BX.bind(node, "change", f)
					}, this);
					BX.bind(file, "change", f);

					n = BX.findChild(node, {attribute : {"data-bx-role" : "tab-file-body"}}, true);
					if (BX.DD && BX.type.isDomNode(n) && n.parentNode)
					{
						var dropZone = new BX.DD.dropFiles(node);
						if (dropZone && dropZone.supported() && BX.ajax.FormData.isSupported()) {
							dropZone.f = {
								dropFiles : BX.delegate(function(files, e) {
									if (e && e["dataTransfer"] && e["dataTransfer"]["items"] && e["dataTransfer"]["items"].length > 0)
									{
										var dt = e["dataTransfer"], ii, entry, fileCopy = [], replace = false;
										for (ii = 0; ii < dt["items"].length; ii++)
										{
											if (dt["items"][ii]["webkitGetAsEntry"] && dt["items"][ii]["getAsFile"])
											{
												replace = true;
												entry = dt["items"][ii]["webkitGetAsEntry"]();
												if (entry && entry.isFile )
												{
													fileCopy.push(dt["items"][ii]["getAsFile"]());
												}
											}
										}
										if (replace)
											files = fileCopy;
									}
									this.addFiles(files);
								}, this),
								dragEnter : BX.proxy(function(e) {
									var isFileTransfer = false;
									if (e && e["dataTransfer"] && e.dataTransfer.types != null && e.dataTransfer.items != null)
									{
										var b = false, i;
										for (i = 0; i < e.dataTransfer.types.length; i++)
										{
											if (e.dataTransfer.types[i] == "Files")
											{
												b = true;
												break;
											}
										}
										if (b)
										{
											for (i = 0; i < e.dataTransfer.items.length; i++)
											{
												if (e.dataTransfer.items[i].type.indexOf("image/") == 0)
												{
													isFileTransfer = true;
													break;
												}
											}
										}
									}
									if (isFileTransfer)
									{
										this.tabs.show('file');
										BX.addClass(n, "dnd-over");
									}
								}, this),
								dragLeave : function() { BX.removeClass(n, "dnd-over"); }
							};
							BX.addCustomEvent(dropZone, 'dropFiles', dropZone.f.dropFiles);
							BX.addCustomEvent(dropZone, 'dragEnter', dropZone.f.dragEnter);
							BX.addCustomEvent(dropZone, 'dragLeave' , dropZone.f.dragLeave);
						}
					}
				}
				BX.onCustomEvent(this, "onEditorHasBeenShown", [this]);
			},
			show : function(activeTab) {
				if (this.popup === null)
				{
					var editorNode = BX.create("DIV", {
						attrs : {
							id : this.id
						},
						style : { display : "none" },
						html : this.getTemplate()
					});
					this.popup = BX.PopupWindowManager.create(
						'popup' + this.id,
						null,
						{
							className : "main-file-input-popup",
							autoHide : false,
							lightShadow : true,
							closeIcon : true,
							closeByEsc : true,
							titleBar : BX.message("JS_AVATAR_EDITOR_TITLE_BAR"),
							content : editorNode,
							zIndex : BX.PopupWindowManager.getMaxZIndex() + 1,
							overlay : {},
							events : {
								onPopupShow : this.handlers.onPopupShow,
								onAfterPopupShow : this.handlers.onAfterPopupShow,
								onPopupClose : this.handlers.onPopupClose
							},
							buttons : [
								new BX.PopupWindowButton( {text : BX.message("JS_AVATAR_EDITOR_SAVE_BUTTON"), className : "popup-window-button-accept", events : { click : this.handlers.apply } } ),
								new BX.PopupWindowButtonLink( {text : BX.message("JS_AVATAR_EDITOR_CANCEL_BUTTON"), className : "popup-window-button-link-cancel", events : { click : this.handlers.cancel } } )
							]
						}
					);
				}
				else
				{
					BX.onCustomEvent(this, "onEditorHasBeenShown", [this]);
				}
				if (activeTab === 'camera' || activeTab === 'file')
				{
					var f = BX.proxy(function(){ this.tabs.show(activeTab); }, this);
					if (this.popup.isShown())
						f();
					else
						BX.addCustomEvent(this, "onEditorHasBeenShown", f);
				}

				this.popup.show();
				this.popup.adjustPosition();
			},
			showFile : function(url) {
				BX.addCustomEvent(this, "onEditorHasBeenShown", BX.proxy(function() {
					this.tabs.show('file');
					this.tabs.show('canvas', 'file');
					this.canvas.load(url);
				}, this));
				this.show();
			},
			click : function() {
				this.show();
			},
			apply : function() {
				var result = this.canvas.pack();
				if (result !== null)
				{
					BX.onCustomEvent(this, "onApply", [result, this.canvas.canvas]);
					this.popup.close();
				}
				else
				{
					this.cancel();
				}
			},
			cancel : function() {
				this.popup.close();
			},
			onPopupShow : function() {
			},
			onAfterPopupShow : function() {
				try
				{
					this.bindTemplate();
				}
				catch(e)
				{
					this['bindTemplateCounter'] = (this['bindTemplateCounter'] || 0) + 1;
					if (this['bindTemplateCounter'] < 10)
					{
						setTimeout(BX.proxy(this.onAfterPopupShow, this), 500);
					}
				}
			},
			onPopupClose : function() {
				if (this.tabs)
					BX.onCustomEvent(this.tabs, "onTabHasBeenChanged", [null, null, this.tabs]);
				BX.removeCustomEvent(this.popup, "onPopupShow", this.handlers.onPopupShow);
				BX.removeCustomEvent(this.popup, "onAfterPopupShow", this.handlers.onAfterPopupShow);
				BX.removeCustomEvent(this.popup, "onPopupClose", this.handlers.onPopupClose);
				this.popup.destroy();
				this.popup = null;
			}
		};
		return d;
	})();
})();