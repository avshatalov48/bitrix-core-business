;(function(window){
	if (window.BX["CanvasEditor"])
		return false;
	var BX = window.BX, E = {},
	FormToArray = function(form, data) {
		data = (data || {});
		var i, ii,
			_data = [],
			n = form.elements.length,
			files = 0, length = 0;
		if(!!form)
		{
			for(i=0; i<n; i++)
			{
				var el = form.elements[i];
				if (el.disabled)
					continue;
				switch(el.type.toLowerCase())
				{
					case 'text':
					case 'textarea':
					case 'password':
					case 'hidden':
					case 'select-one':
						_data.push({name: el.name, value: el.value});
						length += (el.name.length + el.value.length);
						break;
					case 'file':
						if (!!el.files)
						{
							for(ii=0;ii<el.files.length;ii++)
							{
								files++;
								_data.push({name: el.name, value: el.files[ii]});
								length += el.files[ii].size;
							}
						}
						break;
					case 'radio':
					case 'checkbox':
						if(el.checked)
						{
							_data.push({name: el.name, value: el.value});
							length += (el.name.length + el.value.length);
						}
						break;
					case 'select-multiple':
						for (var j = 0; j < el.options.length; j++) {
							if (el.options[j].selected)
							{
								_data.push({name : el.name, value : el.options[j].value});
								length += (el.name.length + el.options[j].length);
							}
						}
						break;
					default:
						break;
				}
			}

			i = 0; length = 0;
			var current = data;

			while(i < _data.length)
			{
				var p = _data[i].name.indexOf('[');
				if (p == -1) {
					current[_data[i].name] = _data[i].value;
					current = data;
					i++;
				}
				else
				{
					var name = _data[i].name.substring(0, p);
					var rest = _data[i].name.substring(p+1);
					if(!current[name])
						current[name] = [];

					var pp = rest.indexOf(']');
					if(pp == -1)
					{
						current = data;
						i++;
					}
					else if(pp == 0)
					{
						//No index specified - so take the next integer
						current = current[name];
						_data[i].name = '' + current.length;
					}
					else
					{
						//Now index name becomes and name and we go deeper into the array
						current = current[name];
						_data[i].name = rest.substring(0, pp) + rest.substring(pp+1);
					}
				}
			}
		}
		return {data : data, filesCount : files, roughSize : length};
	};

	BX.CanvasEditor = function (id)
	{
		this.previewSize = {width : 1024, height : 860, minWidth : 500, minHeight : 335};
		this.dialogName = "BX.CanvasEditor";
		this.id = id + 'Editor';
		this.canvas = new BX.Canvas();
		BX.addCustomEvent(this.canvas, "onChangeSize", BX.delegate(function(params, changeCoeff) {
			var props = params.props,
				style = { width : parseInt(params.style.width.replace('px', '')), height : parseInt(params.style.height.replace('px', '')) },
				cover = BX.pos(this.canvas.getCanvas().parentNode.parentNode),
				cover1 = { style : params.style }, ratio, coeff = 0, cnv = this.canvas.getCanvas();

			if (changeCoeff == true)
			{
				if (style.height > cover.height || style.width > cover.width)
				{
					ratio = BX.UploaderUtils.scaleImage(props, cover, "inscribed");
					coeff = ratio.coeff;
					if (!cnv.hasAttribute("bx-bxu-html-compression-ratio-real"))
						cnv.setAttribute("bx-bxu-html-compression-ratio-real", cnv.getAttribute("bx-bxu-html-compression-ratio"));
					cnv.setAttribute("bx-bxu-html-compression-ratio", coeff);
				}
				else if (cnv.hasAttribute("bx-bxu-html-compression-ratio-real"))
				{
					coeff = cnv.getAttribute("bx-bxu-html-compression-ratio-real");
					cnv.setAttribute("bx-bxu-html-compression-ratio", coeff);
					cnv.removeAttribute("bx-bxu-html-compression-ratio-real");
				}
				if (coeff > 0)
					return this.canvas.setProps(props, false);
			}
			var top = Math.ceil((cover.height - style.height) / 2),
				left = Math.ceil((cover.width - style.width) / 2),
				right = (cover.width - left - style.width),
				cover2 = { style : {
					paddingTop : top + 'px',
					paddingBottom : (cover.height - top - style.height) + 'px',
					paddingLeft : left + 'px',
					paddingRight : right + 'px'
				} };
			BX.adjust(this.canvas.getCanvas().parentNode, cover1);
			BX.adjust(this.canvas.getCanvas().parentNode.parentNode, cover2);
		}, this));

		BX.addCustomEvent(this.canvas, "onChangeCanvas", BX.delegate(function(ratio) {
			this.canvas.getCanvas().removeAttribute("bx-bxu-html-compression-ratio-real");
			var props = ratio.destin, padding = {},
				width = Math.max(props.width, this.previewSize.minWidth),
				height = Math.max(props.height, this.previewSize.minHeight);
			padding.left = Math.ceil((width - props.width) / 2);
			padding.right = width - padding.left - props.width;
			padding.top = Math.ceil((height - props.height) / 2);
			padding.bottom = height - padding.top - props.height;
			BX.adjust(this.canvas.getCanvas().parentNode.parentNode, { style : {
					padding : '' + padding.top + 'px ' + padding.right + 'px ' + padding.bottom + 'px ' + padding.left + 'px'
				}
			} );
		}, this));

		this.popup = null;
		return this;
	};
	BX.CanvasEditor.prototype = {
		onApply : function()
		{
			this.canvas.apply();
			BX.onCustomEvent(this, "onApplyCanvas", [this.canvas.orig, FormToArray(BX(this.id + 'params'))]);
		},
		onCancel : function()
		{
			BX.onCustomEvent(this, "onCancelCanvas", [this.canvas.orig]);
		},
		onDelete : function()
		{
			BX.onCustomEvent(this, "onDeleteCanvas", [this.canvas.orig]);
		},
		showEditor : function(canvas, params) {
			params = (!!params && typeof params == "object" ? params : {template : params});
			BX.onCustomEvent(this, "onBeforeShow");
			var res = BX.GetWindowInnerSize();
			if (res.innerWidth < this.previewSize.width)
				this.previewSize.width = res.innerWidth;
			if (res.innerHeight * 0.8 < (this.previewSize.height))
				this.previewSize.height = res.innerHeight * 0.8;
			if (this.popup === null)
			{
				var ratio = BX.UploaderUtils.scaleImage(canvas, this.previewSize, "inscribed");
				var editorNode = BX.create("DIV", {
					attrs : {
						id : this.id + 'Proper',
						className : "bxu-edit-popup"
					},
					style : { display : "none" },
					html : [
						'<div class="bxu-edit-top"> \
							<span class="bxu-edit-name" id="', this.id, 'title">', BX.util.htmlspecialchars(params["title"]), '</span> \
							<span class="bxu-edit-cls" id="', this.id, 'close"></span> \
						</div> \
						<div class="bxu-edit-img-wrap">\
							<div class="bxu-edit-img-wrap-in" style="position:relative; margin:0;padding:0;border:none;"> \
								<div style="position:relative; margin:0;padding:0;border:none;"> \
									<canvas id="', this.id, 'canvas" width="', ratio.destin.width, '" height="', ratio.destin.height, '"><canvas> \
								</div> \
							</div> \
						</div> \
						<div class="bxu-edit-btn-block"> \
							<div class="bxu-edit-btn-wrap"> \
								<span class="bxu-edit-btn bxu-edit-btn-turn-l" id="', this.id, 'turn-l', '" title="', BX.message("CANVAS_TURN_L"),'"> \
									<span class="bxu-edit-btn-icon"></span> \
								</span> \
								<span class="bxu-edit-btn bxu-edit-btn-turn-r" id="', this.id, 'turn-r', '"title="', BX.message("CANVAS_TURN_R"),'"> \
									<span class="bxu-edit-btn-icon"></span> \
								</span> \
								<span class="bxu-edit-btn bxu-edit-btn-flip-v" id="', this.id, 'flip-v', '" title="', BX.message("CANVAS_FLIP_V"),'"> \
									<span class="bxu-edit-btn-icon"></span> \
								</span> \
								<span class="bxu-edit-btn bxu-edit-btn-flip-h" id="', this.id, 'flip-h', '" title="', BX.message("CANVAS_FLIP_H"),'"> \
									<span class="bxu-edit-btn-icon"></span> \
								</span> \
								<span class="bxu-edit-btn bxu-edit-btn-crop" id="', this.id, 'crop', '" title="', BX.message("CANVAS_CROP"),'"> \
									<span class="bxu-edit-btn-icon"></span> \
								</span> \
								<span class="bxu-edit-btn bxu-edit-btn-grayscale" id="', this.id, 'grayscale', '" title="', BX.message("CANVAS_GRAYSCALE"),'"> \
									<span class="bxu-edit-btn-icon"></span> \
								</span> \
								<span class="bxu-edit-btn bxu-edit-btn-sign" id="', this.id, 'sign', '" title="', BX.message("CANVAS_SIGN"),'"> \
									<span class="bxu-edit-btn-icon"></span> \
								</span> \
							</div> \
							<div class="bxu-edit-inp-wrap"> \
								<form onsubmit="return false;" id="', this.id , 'params">', (params["template"] || ''), '</form> \
							</div> \
						</div>'].join("")
				});
				var func = BX.delegate(function() {
					BX(this.id + 'canvas').parentNode.replaceChild(this.canvas.cnv, BX(this.id + 'canvas'));
					BX.bind(BX(this.id + 'turn-l'), "click", BX.proxy(function(){ this.rotate(false); }, this.canvas));
					BX.bind(BX(this.id + 'turn-r'), "click", BX.proxy(function(){ this.rotate(true); }, this.canvas));
					BX.bind(BX(this.id + 'flip-v'), "click", BX.proxy(function(){ this.flip(false); }, this.canvas));
					BX.bind(BX(this.id + 'flip-h'), "click", BX.proxy(function(){ this.flip(true); }, this.canvas));
					BX.bind(BX(this.id + 'crop'), "click", BX.proxy(function(){ this.crop(BX.proxy_context); }, this.canvas));
					BX.bind(BX(this.id + 'grayscale'), "click", BX.proxy(function(){ this.blackAndWhite(); }, this.canvas));
					BX.bind(BX(this.id + 'sign'), "click", BX.proxy(function(){ this.poster(BX.proxy_context); }, this.canvas));
					BX.bind(BX(this.id + 'close'), "click", BX.proxy(this.popup.close, this.popup));
					BX.bind(BX(this.id + 'params'), "submit", BX.proxy(this.onApply, this));

					BX.removeCustomEvent(this.popup, "onPopupShow", func);
				}, this), func3 = BX.delegate(this.onCancel, this);

				this.popup = BX.PopupWindowManager.create(
					'popup' + this.id,
					null,
					{
						className : "bxu-popup",
						autoHide : false,
						lightShadow : true,
						closeIcon : false,
						closeByEsc : true,
						zIndex : 1,
						content : editorNode,
						overlay : {},
						events : {
							onPopupClose : BX.delegate(
								function(){
									BX.onCustomEvent(this, "onClose", [this]);
									this.canvas.reset();
								}, this
							)
						},
						buttons : [
							new BX.PopupWindowButton( {text : BX.message('CANVAS_OK'), className : "", events : { click : BX.delegate(function(){
								this.onApply();
								BX.removeCustomEvent(this.popup, "onPopupClose", func3);
								this.popup.close();
							}, this) } } )
							, new BX.PopupWindowButton( {text : BX.message('CANVAS_CANCEL'), className : "", events : { click : BX.delegate(function(){
								this.popup.close();
							}, this) } } )
							//, new BX.PopupWindowButton( {text : BX.message('CANVAS_DELETE'), className : "", events : { click : BX.delegate(this.onDelete, this) } } )
						]
					}
				);
				BX.addCustomEvent(this.popup, "onPopupShow", func);
				BX.addCustomEvent(this.popup, "onPopupClose", func3);

			}
			var func2 = BX.delegate(function(){
				if (canvas != null)
					this.copyCanvas(canvas);
				BX(this.id + 'params').innerHTML = (params["template"] || '');
				if (!!params["template"])
				{
					BX.defer_proxy(function(){
						var form = BX(this.id + 'params');
						if (!!form)
						{
							for (var ii = 0; ii < form.elements.length; ii++)
							{
								if (form.elements[ii].type.toUpperCase() == "TEXT" || form.elements[ii].tagName.toUpperCase() == "TEXTAREA")
								{
									BX.focus(form.elements[ii]);
									break;
								}
							}
						}
					}, this)();
				}
				BX(this.id + 'title').innerHTML = BX.util.htmlspecialchars(params["title"] || '');
				BX.removeCustomEvent(this.popup, "onAfterPopupShow", func2);
			}, this);
			BX.addCustomEvent(this.popup, "onAfterPopupShow", func2);

			this.popup.show();
			this.popup.adjustPosition();
			BX.onCustomEvent(this, "onAfterShow");

			return true;
		},
		copyCanvas : function(canvas)
		{
			this.canvas.copy(canvas, this.previewSize);
		}
	};
	BX.CanvasEditor.show = function(canvas, html, params)
	{
		var id = "";
		if (typeof params == "string")
			id = params;
		else if (typeof params == "object")
			id = params["id"];
		id = (typeof id === "string" && id.length > 0 ? id : "default");
		if(!E[id])
			E[id] = new BX.CanvasEditor(id);
		E[id].showEditor(canvas, html, params);
		return E[id];
	};
	BX.CanvasEditor.replaceCanvas = function(canvas, id)
	{
		id = (typeof id === "string" && id.length > 0 ? id : "default");
		if(!!E[id])
			E[id].copyCanvas(canvas);
	};

	BX.Canvas = function()
	{
		this.cnv = null;
		this.ctx = null;
		this.init();
		return this;
	};
	BX.Canvas.prototype = {
		getVisFromReal : function(prop)
		{
			var c = this.cnv.getAttribute("bx-bxu-html-compression-ratio");
			return ((0 < c && c < 1) ? parseInt(c * prop) : prop);
		},
		getRealFromVis : function(prop)
		{
			var c = this.cnv.getAttribute("bx-bxu-html-compression-ratio");
			return ((0 < c && c < 1) ? parseInt(prop / c) : prop);
		},
		setStyles : function(props)
		{
			var p = {width : this.getRealFromVis(props.width), height : this.getRealFromVis(props.height)},
				styles = {width : props.width + 'px', height : props.height + 'px'};
			BX.adjust(this.cnv, {props : p, style : styles });
			BX.onCustomEvent(this, "onChangeSize", [{props : p, style : styles }])
		},
		setProps : function(props, recalcCoeff)
		{
			var p = ({width : props.width, height : props.height}),
				styles = {width : this.getVisFromReal(props.width) + 'px', height : this.getVisFromReal(props.height) + 'px'};
			BX.adjust(this.cnv, {props : p, style : styles });
			BX.onCustomEvent(this, "onChangeSize", [{props : p, style : styles }, recalcCoeff]);
		},
		getCanvas : function()
		{
			return this.cnv;
		},
		init : function()
		{
			this.cnv = BX.create('CANVAS');
			BX.adjust(this.cnv, { width : 100, height : 100 } );
			this.ctx = this.cnv.getContext("2d");
			this.initDnD();
			return this.cnv;
		},
		reset : function()
		{
			this.ctx.clearRect(0, 0, this.cnv.width, this.cnv.height);
			BX.adjust(this.cnv, { width : 100, height : 100 } );
		},
		apply : function()
		{
			this.orig.width = this.cnv.width;
			this.orig.height = this.cnv.height;
			this.orig.ctx = this.orig.getContext("2d");
			this.orig.ctx.drawImage(this.cnv, 0, 0);
		},
		copy : function(canvas, params)
		{
			this.orig = canvas;

			params = (params || {});
			params.width = (params.width > 0 ? params.width : 0);
			params.height = (params.height > 0 ? params.height : 0);

			var ratio = BX.UploaderUtils.scaleImage(canvas, params, "inscribed");
			this.cnv.setAttribute("bx-bxu-html-compression-ratio", ratio.coeff);

			this.setProps(canvas);
			BX.onCustomEvent(this, "onChangeCanvas", [ratio]);

			this.ctx = this.cnv.getContext("2d");
			this.ctx.drawImage(canvas, 0, 0);
			return this.cnv;
		},
		poster : function(node)
		{
			if (!!this.posterPopup)
				this.posterPopup.close();
			var res = BX.pos(node);

			this.posterPopup = new BX.PopupWindow('bx-poster-popup-' + node.id, node, {
				lightShadow : true,
				offsetTop: -3,
				className : "bxu-poster-popup",
				offsetLeft: Math.ceil(res.width / 2),
				autoHide: true,
				closeByEsc: true,
				bindOptions: {position: "top"},
				overlay : false,
				events : {
					onPopupClose : function() { this.destroy() },
					onPopupDestroy : BX.proxy(function() { this.posterPopup = null; }, this)
				},
				buttons : [
					new BX.PopupWindowButton( {text : BX.message('CANVAS_OK'), events : { click : BX.delegate(function() {
						var msg = BX('posterPopupText' + node.id);
						if (!!msg && msg.value.length > 0)
							this.posterApply(msg.value);
						this.posterPopup.close();
					}, this) } } )
					, new BX.PopupWindowButton( {text : BX.message('CANVAS_CANCEL'), events : { click : BX.delegate(function(){this.posterPopup.close();}, this) } } )
				],
				content : ['<div class="bxu-poster-popup-dt">', BX.message("CANVAS_POSTER_SIGN"), '</div> \
					<input type="text" id="posterPopupText', node.id,'" maxlength="255" value="" />'].join("")
			});
			this.posterPopup.show();
			this.posterPopup.setAngle({position:'bottom'});
			this.posterPopup.bindOptions.forceBindPosition = true;
			this.posterPopup.adjustPosition();
			BX.focus(BX('posterPopupText' + node.id));
			this.posterPopup.bindOptions.forceBindPosition = false;
		},
		posterApply : function(msg)
		{
			if (msg)
			{
				var size = Math.min(this.cnv.width, this.cnv.height) / 10;
				this.ctx.fillStyle = "black";
				this.ctx.fillRect(0, 0, this.cnv.width, size);
				this.ctx.fillRect(0, this.cnv.height - 2 * size, this.cnv.width, 2 * size);
				this.ctx.fillRect(0, 0, size, this.cnv.height);
				this.ctx.fillRect(this.cnv.width - size, 0, size, this.cnv.height);
				this.ctx.strokeStyle = "white";
				var border = 5;
				this.ctx.strokeRect(size - border, size - border,
					this.cnv.width - (size * 2) + 2 * border,
					this.cnv.height - (size * 3) + 2 * border);
				this.ctx.fillStyle = "white";
				this.ctx.textAlign = "center";
				this.ctx.textBaseline = "middle";
				this.ctx.font = size + "px marketing";
				this.ctx.fillText(msg, this.cnv.width / 2, this.cnv.height - size, this.cnv.width);
			}
		},
		blackAndWhite : function()
		{
			var frame = this.ctx.getImageData(0, 0, this.cnv.width, this.cnv.height), v, i;
			for (i = 0; i < frame.data.length; i += 4)
			{
				v = (frame.data[i] + frame.data[i + 1] + frame.data[i + 2]) / 3;
				frame.data[i] = v;
				frame.data[i + 1] = v;
				frame.data[i + 2] = v;
			}
			this.ctx.putImageData(frame, 0, 0);
		},
		flip : function(verticaly)
		{
			this.ctx.save();
			if (verticaly)
			{
				this.ctx.scale(1, -1);
				this.ctx.translate(0, - this.cnv.height);
			}
			else
			{
				this.ctx.scale(-1, 1);
				this.ctx.translate(-this.cnv.width, 0);
			}
			this.ctx.drawImage(this.cnv, 0, 0);
			this.ctx.restore();
		},
		rotate : function(clockwise)
		{
			this.turn(this.cnv, this.ctx, clockwise);
		},
		turn : function(cnv, ctx, clockwise)
		{
			var tmpCanvas = BX.create('CANVAS', {props : { width : cnv.width, height : cnv.height }, style : { display : "none" } } );
			tmpCanvas.getContext("2d").drawImage(cnv, 0, 0);

			this.setProps({width : tmpCanvas.height, height : tmpCanvas.width}, true);

			ctx.save();

			if (clockwise)
				ctx.translate(this.cnv.width, 0);
			else
				ctx.translate(0, this.cnv.height);
			var rad = Math.PI / 2 * (clockwise ? 1 : -1);
			ctx.rotate(rad);
			ctx.drawImage(tmpCanvas, 0, 0);
			ctx.restore();
			tmpCanvas = null;
		},
		resize : function(ratio)
		{
			var tmpCanvas = BX.create('CANVAS', {props : { width : this.cnv.width, height : this.cnv.height }, style : { display : "none" } } );
			tmpCanvas.getContext("2d").drawImage(this.cnv, 0, 0);

			this.cnv.width *= ratio;
			this.cnv.height *= ratio;

			this.ctx.save();
			this.ctx.scale(ratio, ratio);
			this.ctx.drawImage(tmpCanvas, 0, 0);
			this.ctx.restore();

			tmpCanvas = null;
		},
		_imageDropped : function(url, x, y, drawBorder)
		{
			var img = BX.create("IMG", {
					style : { display : "none" },
					events : {
						load : BX.delegate(function()
						{
							var ratioX = (img.width > this.cnv.width / 3 ? (this.cnv.width / 3) / img.width : 1),
								ratioY = (img.height > this.cnv.height / 3 ? (this.cnv.height / 3) / img.height : 1),
								ratio = Math.min(ratioX, ratioY);
							this.cnv.save();
							this.cnv.translate(x - img.width * ratio / 2, y - img.height * ratio / 2);
							this.cnv.scale(ratio, ratio);
							this.cnv.drawImage(img, 0, 0);
							if (drawBorder)
							{
								this.cnv.strokeStyle = "white";
								this.cnv.lineWidth = 5 / ratio;
								this.cnv.strokeRect(0, 0, img.width, img.height);
							}
							this.cnv.restore();
							document.body.removeChild(img);
							img = null;
						}, this)
					}
				});
			img.src = url;
			document.body.appendChild(img);
		},
		initDnD : function()
		{
			if (!!BX.DD && BX.type.isDomNode(this.cnv) && this.cnv.parentNode)
			{
				this.cnvDD = new BX.DD.dropFiles(this.cnv.canvas);
				if (this.cnvDD && this.cnvDD.supported() && BX.ajax.FormData.isSupported())
				{
					BX.addCustomEvent(this.cnvDD, 'dragover', BX.preventDefault);
					BX.addCustomEvent(this.cnvDD, 'drop', BX.delegate(function(e)
					{
						BX.preventDefault(e);
						BX.fixEventPageXY(e);

						var dt = e.dataTransfer,
							x = e.pageX - this.cnv.canvas.offsetLeft - this.cnv.canvas.parentNode.offsetLeft,
							y = e.pageY - this.cnv.canvas.offsetTop - this.cnv.canvas.parentNode.offsetTop,
							files = dt.files;
						if (files.length == 0)
						{
							var type = "application/x-moz-file-promise-url";
							if (dt.types.contains(type)) { this._imageDropped(dt.getData(type), x, y, false); }
						}
						else
						{
							var file = files[0], reader = new FileReader();
							reader.onload = BX.proxy(function(e) {
								this._imageDropped(e.target.result, x, y, true);
								reader = null;
							}, this);
							reader.readAsDataURL(file);
						}
					}, this));
				}
			}
		},
		cropParams : null,
		crop : function(button)
		{
			if (!!button)
				BX.addClass(button, "bxu-edit-btn-active");
			var cropBox = BX.create("DIV", {
				attrs : { id : "crop" + this.id, className : "bx-bxu-canvas-crop-rectangle" },
				style : { border : "3px dashed gray", position : "absolute", display : "none", zIndex : 1201 } } );

			this.cropParams = {
				x : 0, y : 0, width : 0, height : 0, top : 0, left : 0,
				mousedown : BX.delegate(function(e)
					{
						BX.fixEventPageXY(e);
						if (e.layerX || e.layerX == 0)
						{
							this.cropParams.left = e.layerX;
							this.cropParams.top = e.layerY;
						}
						else if(e.offsetX || e.offsetX == 0)
						{
							this.cropParams.left = e.offsetX;
							this.cropParams.top = e.offsetY;
						}
						else
						{
							this.cropParams.top = e.pageX - this.cnv.pos["left"];
							this.cropParams.left = e.pageY - this.cnv.pos["top"];
						}
						this.cropParams["~top"] = this.cropParams.top;
						this.cropParams["~left"] = this.cropParams.left;
						this.cropParams.x = e.pageX;
						this.cropParams.y = e.pageY;

						BX.bind(document, "mousemove", this.cropParams.mousemove);
					}, this),
				mousemove : BX.delegate(function(e)
					{
						BX.fixEventPageXY(e);
						var x = e.pageX, y = e.pageY;

						this.cropParams.width = (x - this.cropParams.x);
						if (this.cropParams.width < 0)
						{
							this.cropParams.left = this.cropParams["~left"] + this.cropParams.width;
							this.cropParams.width *= -1;
							if (this.cropParams.left < 0)
							{
								this.cropParams.left = 0;
								this.cropParams.width = this.cropParams["~left"];
							}
						}
						else
						{
							this.cropParams.left = this.cropParams["~left"];
							if ((this.cropParams.left + this.cropParams.width) > this.cnv.pos.width)
								this.cropParams.width = this.cnv.pos.width - this.cropParams.left;
						}

						this.cropParams.height = y - this.cropParams.y;
						if (this.cropParams.height < 0)
						{
							this.cropParams.top = this.cropParams["~top"] + this.cropParams.height;
							this.cropParams.height *= -1;
							if (this.cropParams.top < 0)
							{
								this.cropParams.top = 0;
								this.cropParams.height = this.cropParams["~top"];
							}
						}
						else
						{
							this.cropParams.top = this.cropParams["~top"];
							if ((this.cropParams.top + this.cropParams.height) > this.cnv.pos.height)
								this.cropParams.height = this.cnv.pos.height - this.cropParams.top;
						}

						BX.adjust(cropBox, {style : {
							left : (this.cropParams.left + 'px'),
							top : (this.cropParams.top + 'px'),
							width : ((this.cropParams.width - (cropBox.borderLeft + cropBox.borderRight)) + 'px'),
							height : ((this.cropParams.height - (cropBox.borderTop + cropBox.borderBottom)) + 'px'),
							display : "block"
						}});
					}, this),
				mouseup : BX.delegate(function(e)
					{
						BX.PreventDefault(e);
						if (this.cropParams.width > 0 && this.cropParams.height > 0)
						{
							var cnv = BX.create("CANVAS", {
								props : { width : this.cnv.width, height : this.cnv.height },
								style : { display : "none" } } );

							cnv.getContext("2d").drawImage(this.cnv, 0, 0);

							this.setStyles(this.cropParams);

							this.ctx.save();

							this.ctx.drawImage(cnv, -this.getRealFromVis(this.cropParams["left"]), -this.getRealFromVis(this.cropParams["top"]));
							this.ctx.restore();

							cnv = null;
						}
						this.cropParams.clear(e);
					}, this),
				clear :  BX.delegate(function(e)
					{
						if (!!button)
							BX.removeClass(button, "bxu-edit-btn-active");
						BX.unbind(this.cnv, "mousedown", this.cropParams.mousedown);
						BX.unbind(document, "mousemove", this.cropParams.mousemove);
						BX.unbind(this.cnv, "mouseup", this.cropParams.mouseup);
						BX.unbind(cropBox, "mouseup", this.cropParams.mouseup);
						BX.unbind(document, "mouseup", this.cropParams.mouseup);
						this.cnv.parentNode.style.position = this.cnv.parentNode.style._position;
						this.cnv.parentNode.removeChild(cropBox);
						cropBox = null;
						this.cropParams = null;
					}, this)
			};
			BX.bind(this.cnv, "mousedown", this.cropParams.mousedown);
			BX.bind(this.cnv, "mouseup", this.cropParams.mouseup);
			BX.bind(cropBox, "mouseup", this.cropParams.mouseup);
			BX.bind(document, "mouseup", this.cropParams.mouseup);
			this.cnv.pos = BX.pos(this.cnv);
			this.cnv.parentNode.style._position = this.cnv.parentNode.style.position;
			this.cnv.parentNode.style.position = "relative";
			this.cnv.parentNode.appendChild(cropBox);
			for (var ii = 0, i = ["top", "right", "bottom", "left"]; ii < i.length; ii++)
			{
				cropBox["b" + i[ii]] = parseInt(BX.style(cropBox, 'border-' + i[ii] + '-width'));
				cropBox["b" + i[ii]] = (!isNaN(cropBox["b" + i[ii]]) && cropBox["b" + i[ii]] > 0 ? cropBox["b" + i[ii]] : 0);
			}
			cropBox.borderLeft = cropBox["bleft"];
			cropBox.borderTop = cropBox["btop"];
			cropBox.borderRight = cropBox["bright"];
			cropBox.borderBottom = cropBox["bbottom"];
		}
	};
}(window));
