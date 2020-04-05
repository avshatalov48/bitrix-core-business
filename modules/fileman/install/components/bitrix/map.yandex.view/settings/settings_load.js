/* CBXYandexPoint definition */
function CBXYandexPoint(arData)
{
	/* fields */
	this.DATA = {LON:0,LAT:0,TEXT:''};
	this.PLACEMARK = null;
	this.VIEW = null;
	this.EDIT_CONTROL = null;

	/* events */
	this.onCreate = null;
	this.onDelete = null;

	/* methods */

	/* constructor */
	if (null != arData)
	{
		this.Create(arData)
	}
}

CBXYandexPoint.prototype.__updatePointPosition = function(e)
{
	var obPoint = this.PLACEMARK.geometry.getCoordinates();

	this.DATA.LAT = obPoint[0];
	this.DATA.LON = obPoint[1];
};

CBXYandexPoint.prototype.Delete = function (e)
{
	if (null != this.VIEW && null != this.VIEW.parentNode)
		this.VIEW.parentNode.removeChild(this.VIEW);

	jsYandexCE.map.geoObjects.remove(this.PLACEMARK);
	this.PLACEMARK = null;

	if (null != this.onDelete)
		this.onDelete(this);

	return BX.PreventDefault(e);
};

CBXYandexPoint.prototype.Edit = function(e)
{
	e = e || window.event;
    var this_ = this;
    jsYandexCE.map.panTo(
        this.PLACEMARK.geometry.getCoordinates(), {
            delay: 0,
            flying: false,
            callback: function () {
                if (this_.PLACEMARK._balloonVisible)
                    this_.PLACEMARK.balloon.close();
                else
                {
                    this_.PLACEMARK.balloon.open();
                    this_.EDIT_CONTROL.focus();
                }
            }
        });

	if (null != e)
		return BX.PreventDefault(e);
};

CBXYandexPoint.prototype.__updateView = function(e)
{
	e = e || window.event;

	value = BX.proxy_context.value;

	this.DATA.TEXT = value;

	var rnpos = value.indexOf("\n");
	var value_view = '';
	if (value.length > 0)
		value_view = rnpos <= 0 ? value : value.substring(0, rnpos);

	value_view = value_view.replace(/</g, '&lt;');
	value_view = value_view.replace(/>/g, '&gt;');

	this.__updateViewText(value_view ? value_view : window.jsYandexMess.noname);

	return BX.PreventDefault(e);
};

CBXYandexPoint.prototype.__point_link_hover = function() {this.style.backgroundColor = "#E3E8F7"; this.firstChild.style.display = 'block';};
CBXYandexPoint.prototype.__point_link_hout = function() {this.style.backgroundColor = "transparent"; this.firstChild.style.display = 'none';};
CBXYandexPoint.prototype.__updateViewText = function(str) {this.VIEW.firstChild.nextSibling.innerHTML = str;};
CBXYandexPoint.prototype.getData = function() {return this.DATA;};

CBXYandexPoint.prototype.Create = function(arPlacemark)
{
	this.DATA.TEXT = arPlacemark.TEXT;
	this.DATA.LAT = arPlacemark.LAT;
	this.DATA.LON = arPlacemark.LON;

	this.__createView();

	var value_view = '';
	if (this.DATA.TEXT.length > 0)
	{
		var rnpos = this.DATA.TEXT.indexOf("\n");
		value_view = rnpos <= 0 ? this.DATA.TEXT : this.DATA.TEXT.substring(0, rnpos);
		value_view = value_view.replace(/>/g, '&gt;');
		value_view = value_view.replace(/</g, '&lt;');
	}

	this.__updateViewText(value_view ? value_view : window.jsYandexMess.noname);

	this.PLACEMARK = new window.ymaps.Placemark(
		[this.DATA.LAT, this.DATA.LON],
		{
			balloonContent: this.__createEditForm()
		},
		{
			draggable: true
		}
	);

	this.PLACEMARK.events.add("dragend", BX.proxy(this.__updatePointPosition, this));
	jsYandexCE.map.geoObjects.add(this.PLACEMARK);

	if (null !== this.onCreate)
		this.onCreate(this);

	return this.PLACEMARK;
};

CBXYandexPoint.prototype.__createEditForm = function()
{
	this.EDIT_CONTROL = document.createElement('TEXTAREA');
	this.EDIT_CONTROL.value = this.DATA.TEXT;
	this.EDIT_CONTROL.style.resize = 'none';
	this.EDIT_CONTROL.rows = '4';

	this.EDIT_CONTROL.onkeyup = this.EDIT_CONTROL.onblur = BX.proxy(this.__updateView, this);

	return this.EDIT_CONTROL;
};

CBXYandexPoint.prototype.__createView = function()
{
	this.VIEW = BX('bx_yandex_points').appendChild(document.createElement('LI'));

	var obDeleteLink = this.VIEW.appendChild(document.createElement('A'));

	obDeleteLink.href = "javascript: void(0)";
	obDeleteLink.className = 'bx-yandex-delete';
	obDeleteLink.onclick = BX.delegate(this.Delete, this);
	obDeleteLink.style.display = 'none';

	var obLink = this.VIEW.appendChild(document.createElement('A'));
	obLink.className = 'bx-yandex-point';
	obLink.href = 'javascript:void(0)';
	obLink.onclick = BX.delegate(this.Edit, this);
	obLink.innerHTML = window.jsYandexMess.noname;

	this.VIEW.onmouseover = this.__point_link_hover;
	this.VIEW.onmouseout = this.__point_link_hout;
};
/* /CBXYandexPoint definition */

/* CBXYandexPoly definition */
function CBXYandexPoly(arData)
{
	/* fields */
	this.DATA = {POINTS:[],TITLE:'',STYLE:{}};
	this.POLYLINE = null;
	this.VIEW = null;
	this.EDIT_CONTROL = null;
	this.STYLE = null;

	this.START_POINT = null;
	this.END_POINT = null;

	this.bFinished = false;

	/* events */
	this.onFinish = null;
	this.onDelete = null;

	/* constructor */
	if (null != arData)
	{
		this.Create(arData)
	}
}

	/* methods */
CBXYandexPoly.prototype.Finish = function(obEvent)
{
	if (!jsYandexCE.bAddPolyMode)
		return;

	this.bFinished = true;

	jsYandexCE.map.events.remove('click', BX.proxy(this.__addPoint, this));
	jsYandexCE.map.geoObjects.remove(this.START_POINT);

	if (null == this.POLYLINE)
	{
		this.Delete();
	}
	else
	{
		this.END_POINT.properties.set('hintContent', jsYandexMess.poly_settings);
		this.END_POINT.events.remove("click", BX.delegate(this.Finish, this));
		this.END_POINT.events.add("click", BX.delegate(this.showSettingsForm, this));
	}

	if (null != this.onFinish)
	{
		this.onFinish(this);
	}
};

CBXYandexPoly.prototype.__addPoint = function(obEvent)
{
	if (!jsYandexCE.bAddPolyMode)
		return;

	// set line finish flag
	if (null == this.END_POINT)
	{
		this.END_POINT = new ymaps.Placemark(obEvent.get("coordPosition"), {
			hintContent: jsYandexMess.poly_finish
		});

		this.END_POINT.events.add("click", BX.delegate(this.Finish, this));
		jsYandexCE.map.geoObjects.add(this.END_POINT);
	}
	else
	{
		this.END_POINT.geometry.setCoordinates(obEvent.get("coordPosition"));
	}

	// initate points array
	if (this.DATA.POINTS.length <= 0)
	{
		this.DATA.POINTS = [this.START_POINT.geometry.getCoordinates(), obEvent.get("coordPosition")];
	}

	if (null !== this.POLYLINE)
	{
		this.DATA.POINTS.push(obEvent.get("coordPosition"));
		this.POLYLINE.geometry.setCoordinates(this.DATA.POINTS);

		this.__updateViewText(jsYandexMess.noname);
	}
	else
	{
		this.setStyle();
		this.prepareSettingsForm();

		this.POLYLINE = new ymaps.Polyline(
			this.DATA.POINTS,
			{},
			{
				clickable: false,
				strokeColor: this.STYLE.strokeColor,
				strokeWidth: this.STYLE.strokeWidth
			}
		);

		jsYandexCE.map.geoObjects.add(this.POLYLINE);

		this.__createView();
	}
};

CBXYandexPoly.prototype.Start = function(obPoint)
{
	this.START_POINT = new ymaps.Placemark(obPoint, {
		hintContent: jsYandexMess.poly_start_point
	});

	jsYandexCE.map.geoObjects.add(this.START_POINT);
	jsYandexCE.map.events.add('click', BX.proxy(this.__addPoint, this));
};

CBXYandexPoly.prototype.showSettingsForm = function(e)
{
	if (!this.bFinished)
		this.Finish();

	if (this.END_POINT.balloon.isOpen())
	{
		this.END_POINT.balloon.close();
	}
	else
	{
		jsYandexCE.map.geoObjects.add(this.END_POINT);
		this.END_POINT.balloon.open();
	}

	return BX.PreventDefault(e);
};

CBXYandexPoly.prototype.Delete = function (e)
{
	if (null != this.VIEW && null != this.VIEW.parentNode)
		this.VIEW.parentNode.removeChild(this.VIEW);

	if (null != this.POLYLINE)
		jsYandexCE.map.geoObjects.remove(this.POLYLINE);

	jsYandexCE.map.geoObjects.remove(this.START_POINT);

	if (null != this.END_POINT)
		jsYandexCE.map.geoObjects.remove(this.END_POINT);

	this.POLYLINE = null;

	if (!this.bFinished)
		this.Finish();

	if (null != this.onDelete)
		this.onDelete(this);

	return BX.PreventDefault(e);
};

CBXYandexPoly.prototype.__poly_link_hover = function() {this.style.backgroundColor = "#E3E8F7"; this.firstChild.style.display = 'block';}
CBXYandexPoly.prototype.__poly_link_hout = function() {this.style.backgroundColor = "#FFFFFF"; this.firstChild.style.display = 'none';}
CBXYandexPoly.prototype.__updateViewText = function(str) {this.VIEW.firstChild.nextSibling.innerHTML = str + ' (' + this.DATA.POINTS.length + ')';}
CBXYandexPoly.prototype.getData = function()
{
	var obReturn = {POINTS:[],TITLE:'',STYLE:{}};

	for (var i = 0,len = this.DATA.POINTS.length; i < len; i++)
	{
		obReturn.POINTS[i] = {LAT:this.DATA.POINTS[i][0],LON:this.DATA.POINTS[i][1]};
	}

	obReturn.TITLE = this.DATA.TITLE;
	obReturn.STYLE = {strokeColor:this.STYLE.strokeColor, strokeWidth:this.STYLE.strokeWidth};

	return obReturn;
};

CBXYandexPoly.prototype.Create = function(arPolyline)
{
	this.DATA.POINTS = [];

	for(var i = 0,cnt = arPolyline.POINTS.length; i < cnt; i++)
	{
		this.DATA.POINTS[i] = [arPolyline.POINTS[i].LAT, arPolyline.POINTS[i].LON];
	}

	if (null != arPolyline.STYLE)
	{
		if (null != arPolyline.STYLE.lineStyle)
			arPolyline.STYLE = arPolyline.STYLE.lineStyle;

		this.DATA.STYLE.strokeWidth = arPolyline.STYLE.strokeWidth;
		this.DATA.STYLE.strokeColor = arPolyline.STYLE.strokeColor;
	}

	this.setStyle();

	this.DATA.TITLE = arPolyline.TITLE;

	this.START_POINT = new ymaps.Placemark([arPolyline.POINTS[0].LAT, arPolyline.POINTS[0].LON]);
	this.END_POINT = new ymaps.Placemark([arPolyline.POINTS[arPolyline.POINTS.length-1].LAT, arPolyline.POINTS[arPolyline.POINTS.length-1].LON],
	{
		hintContent: this.DATA.TITLE ? this.DATA.TITLE : jsYandexMess.poly_settings
	});

	jsYandexCE.map.geoObjects.add(this.END_POINT);

	this.setStyle();

	this.POLYLINE = new ymaps.Polyline(
		this.DATA.POINTS, // reference!
		{}, {
			strokeColor: this.DATA.STYLE.strokeColor == null ? 'FF00007F' : this.DATA.STYLE.strokeColor,
			strokeWidth: this.DATA.STYLE.strokeWidth == null ? '3' : this.DATA.STYLE.strokeWidth,
			clickable: true
		}
	);

	this.prepareSettingsForm();

	jsYandexCE.map.geoObjects.add(this.POLYLINE);

	this.__createView();
	this.setTitle();
}

CBXYandexPoly.prototype.__createView = function()
{
	this.VIEW = BX('bx_yandex_polylines').appendChild(document.createElement('LI'));

	var obDeleteLink = this.VIEW.appendChild(document.createElement('A'));
	//obDeleteLink.style.width = '30px';
	obDeleteLink.href = "javascript: void(0)";
	obDeleteLink.className = 'bx-yandex-delete';
	obDeleteLink.onclick = BX.delegate(this.Delete, this);
	obDeleteLink.style.display = 'none';

	var obLink = this.VIEW.appendChild(document.createElement('A'));
	obLink.className = 'bx-yandex-poly';
	obLink.href = 'javascript:void(0)';
	obLink.onclick = BX.delegate(this.showSettingsForm, this);
	obLink.innerHTML = window.jsYandexMess.noname + ' (' + this.DATA.POINTS.length + ')';

	this.VIEW.onmouseover = this.__poly_link_hover;
	this.VIEW.onmouseout = this.__poly_link_hout;

	return this.VIEW;
}

CBXYandexPoly.prototype.prepareSettingsForm = function()
{
	this.EDIT_CONTROL = document.createElement('FORM');

	this.END_POINT.properties.set('balloonContent', this.EDIT_CONTROL);
	this.EDIT_CONTROL.BX_POLYLINE = this;

	var obContainer = document.createElement('DIV');
	this.EDIT_CONTROL.appendChild(obContainer);

	this.COLORPICKER = new BXColorPicker(
		{
			'id': 'CP_bx_yandex_ce',
			'name': window.jsYandexMess.poly_opt_color,
			'zIndex': BX.WindowManager.Get().zIndex + 100,
			'OnSelect': BX.delegate(function(color) {
				if (!color)
					color = 'FF0000';
				else
					color = color.substring(1);

				this.EDIT_CONTROL.elements[1].value = color;
				this.setStyle('color', color, this.EDIT_CONTROL.elements[0]);
			}, this)
		}
	);

	obContainer.innerHTML = '<b>' + window.jsYandexMess.poly_opt_header + '</b>' +
		'<br /><br /><table class="bx-yandex-poly-settings">' +
		'<tr><td><span style="font-size: 11px;">' + window.jsYandexMess.poly_opt_title + '</span></td><td><input type="text" value="" style="width: 125px;" onkeyup="this.form.BX_POLYLINE.setTitle(this.value)" /></td></tr>' +
		'<tr><td><span style="font-size: 11px;">' + window.jsYandexMess.poly_opt_color + '</span></td><td><input type="text" value="' + this.STYLE.strokeColor.substring(0,6) + '" style="width: 100px; float: left;" onkeyup="this.form.BX_POLYLINE.setStyle(\'color\', this.value, this)" /></td></tr>' +
		'<tr><td><span style="font-size: 11px;">' + window.jsYandexMess.poly_opt_width + '</span></td><td><input type="text" value="' + this.STYLE.strokeWidth + '"  style="width: 125px;"onkeyup="this.form.BX_POLYLINE.setStyle(\'width\', this.value, this)" /></td></tr>' +
		'<tr><td><span style="font-size: 11px;">' + window.jsYandexMess.poly_opt_opacity + '</span></td><td><input type="text" value="' + Math.round(parseInt('0x' + this.STYLE.strokeColor.substring(6))/2.55) + '" style="width: 125px;"onkeyup="this.form.BX_POLYLINE.setStyle(\'opacity\', this.value, this)" /></td></tr>' +
		'</table>';

	if (null != this.DATA.TITLE)
		this.EDIT_CONTROL.elements[0].value = this.DATA.TITLE;

	this.EDIT_CONTROL.elements[1].parentNode.appendChild(this.COLORPICKER.pCont);
}

CBXYandexPoly.prototype.setTitle = function(title)
{
	if (null != title)
		this.DATA.TITLE = title;
	else
		title = this.DATA.TITLE;

	this.END_POINT.properties.set('hintContent', title);

	var value_view = '';
	if (this.DATA.TITLE.length > 0)
	{
		var rnpos = this.DATA.TITLE.indexOf("\n");
		value_view = rnpos <= 0 ? this.DATA.TITLE : this.DATA.TITLE.substring(0, rnpos);
		value_view = value_view.replace(/>/g, '&gt;');
		value_view = value_view.replace(/</g, '&lt;');
	}

	this.__updateViewText(value_view ? value_view : window.jsYandexMess.noname);
}
CBXYandexPoly.prototype.setStyle = function(property, value, obInput)
{
	if (null == this.STYLE)
	{
		this.STYLE = {
			strokeColor: this.DATA.STYLE.strokeColor == null ? 'FF00007F' : this.DATA.STYLE.strokeColor,
			strokeWidth: this.DATA.STYLE.strokeWidth == null ? '3' : this.DATA.STYLE.strokeWidth
		};
	}

	var bError = false;
	if (null != property && null != value)
	{
		switch (property)
		{
			case 'color':
				if(/^[A-F0-9]{6}$/i.test(value))
				{
					this.STYLE.strokeColor = value + this.STYLE.strokeColor.substring(6);
				}
				else
				{
					bError = true;
				}
			break;
			case 'width':
				value = parseInt(value);
				if(isNaN(value))
				{
					bError = true;
				}
				else
				{
					this.STYLE.strokeWidth = value;
				}
			break;
			case 'opacity':
				value = parseInt(value);
				if(!isNaN(value) && value >= 0 && value <= 100)
				{
					value = Math.round(value * 2.55).toString(16).toUpperCase();
					this.STYLE.strokeColor = this.STYLE.strokeColor.substring(0,6)+BX.util.str_pad_left(value, 2, '0');
				}
				else
				{
					bError = true;
				}
			break;
		}

		if (null != obInput)
		{
			if (bError)
				obInput.style.backgroundColor = 'FFB0B0';
			else
				obInput.style.backgroundColor = 'white';
		}
	}

	if (!bError && null != this.POLYLINE)
	{
		this.POLYLINE.options.set('strokeColor', this.STYLE.strokeColor);
		this.POLYLINE.options.set('strokeWidth', this.STYLE.strokeWidth);
	}
}
/* /CBXYandexPoly definition */

var jsYandexCE = {
	map: null,
	arData: {},
	obForm: null,

	currentView: '',

	bPositionFixed: true,
	bAddPointMode: false,
	bAddPolyMode: false,

	onInitCompleted: null,
	bInitCompleted: false,
	bInitScriptsLoaded: false,

	__arValidKeys: ['yandex_lat', 'yandex_lon', 'yandex_scale', 'PLACEMARKS', 'LON', 'LAT', 'TEXT', 'POLYLINES', 'POINTS', 'STYLE', 'lineStyle', 'strokeColor', 'strokeWidth','TITLE'],

	__obMapTypes: {
		'HYBRID': 'yandex#hybrid',
		'SATELLITE': 'yandex#satellite',
		'PUBLIC': 'yandex#publicMap',
		'PUBLIC_HYBRID': 'yandex#publicMapHybrid',
		'MAP': 'yandex#map'
	},

	__currentPolyLine: null,
	__currentPolyLineObject: null,

	_getTypeName: function(yandex_type)
	{
		return (
			yandex_type == 'yandex#hybrid'
				? 'HYBRID'
				: (
				yandex_type == 'yandex#satellite'
					? 'SATELLITE'
					: (
					yandex_type == 'yandex#publicMap'
						? 'PUBLIC'
						: (
						yandex_type == 'yandex#publicMapHybrid'
							? 'PUBLIC_HYBRID'
							: 'MAP'
						)
					)
				)
			);
	},

	init: function(map)
	{
		if (null != map)
			jsYandexCE.map = map; //GLOBAL_arMapObjects['system_view_edit'];

		var obHead = document.getElementsByTagName('HEAD')[0];
		BX.loadCSS([
			'/bitrix/components/bitrix/map.yandex.system/templates/.default/style.css',
			'/bitrix/components/bitrix/map.yandex.view/settings/settings_iframe.css'
		]);
		BX.loadScript('/bitrix/components/bitrix/main.colorpicker/templates/.default/script.js', function() {
			top.jsYandexCE.bInitScriptsLoaded=true;
			top.jsYandexCE.checkInitCompleted();
		});

		jsYandexCE.obForm = document.forms['bx_popup_form_yandex_map'];
		jsYandexCE.obForm.onsubmit = jsYandexCE.__saveChanges;

		jsYandexCE.map.events.add("boundschange", jsYandexCE.__getPositionValues);
		jsYandexCE.map.events.add("sizechange", jsYandexCE.__getPositionValues);
		jsYandexCE.map.events.add("typechange", jsYandexCE.__getPositionValues);

		if (!jsYandexCE.arData.yandex_lat || !jsYandexCE.arData.yandex_lon || !jsYandexCE.arData.yandex_scale)
		{
			var obPos = jsYandexCE.map.getCenter();
			jsYandexCE.arData.yandex_lat = obPos[0];
			jsYandexCE.arData.yandex_lon = obPos[1];
			jsYandexCE.arData.yandex_scale = jsYandexCE.map.getZoom();
			jsYandexCE.bPositionFixed = false;
		}
		else
		{
			jsYandexCE.bPositionFixed = true;
		}

		jsYandexCE.currentView = jsYandexMess.current_view;

		jsYandexCE.setControlValue('yandex_lat', jsYandexCE.arData.yandex_lat);
		jsYandexCE.setControlValue('yandex_lon', jsYandexCE.arData.yandex_lon);
		jsYandexCE.setControlValue('yandex_scale', jsYandexCE.arData.yandex_scale);
		jsYandexCE.setControlValue('yandex_view', jsYandexMess['MAP_VIEW_' + jsYandexCE._getTypeName(jsYandexCE.map.getType())]);

		BX('bx_restore_position').onclick = jsYandexCE.restorePositionValues;
		BX('bx_yandex_position_fix').onclick = function () {jsYandexCE.setFixedFlag(this.checked)};

		jsYandexCE.setFixedFlag(BX('bx_yandex_position_fix').defaultChecked);

		BX('bx_yandex_map_controls').style.visibility = 'visible';
		BX('bx_yandex_map_address_search').style.visibility = 'visible';

		jsYandexCE.bInitCompleted = true;
		jsYandexCE.checkInitCompleted();
	},

	checkInitCompleted: function()
	{
		if (jsYandexCE.bInitCompleted && jsYandexCE.bInitScriptsLoaded)
		{
			if (jsYandexCE.onInitCompleted)
				jsYandexCE.onInitCompleted();

			return true;
		}
		else
		{
			return false;
		}
	},

	__getPositionValues: function()
	{
		if (jsYandexCE.bPositionFixed)
			return;

		var obPos = jsYandexCE.map.getCenter();
		jsYandexCE.arData.yandex_lat = obPos[0];
		jsYandexCE.arData.yandex_lon = obPos[1];
		jsYandexCE.arData.yandex_scale = jsYandexCE.map.getZoom();

		jsYandexCE.setControlValue('yandex_lat', jsYandexCE.arData.yandex_lat);
		jsYandexCE.setControlValue('yandex_lon', jsYandexCE.arData.yandex_lon);
		jsYandexCE.setControlValue('yandex_scale', jsYandexCE.arData.yandex_scale);

		jsYandexCE.currentView = jsYandexCE._getTypeName(jsYandexCE.map.getType());
		jsYandexCE.setControlValue('yandex_view', jsYandexMess['MAP_VIEW_' + jsYandexCE.currentView]);
	},

	restorePositionValues: function(e)
	{
		if (jsYandexCE.currentView && jsYandexCE.__obMapTypes[jsYandexCE.currentView])
			jsYandexCE.map.setType(jsYandexCE.__obMapTypes[jsYandexCE.currentView]);

		jsYandexCE.map.setZoom(+jsYandexCE.arData.yandex_scale);
		jsYandexCE.map.panTo([+jsYandexCE.arData.yandex_lat, +jsYandexCE.arData.yandex_lon]);

		return BX.PreventDefault(e);
	},

	setFixedFlag: function(value)
	{
		jsYandexCE.bPositionFixed = value;
		if (!value)
			jsYandexCE.__getPositionValues();
	},

	setControlValue: function(control, value)
	{
		var obControl = jsYandexCE.obForm['bx_' + control];
		if (null != obControl)
			obControl.value = value;

		var obControlOut = BX('bx_' + control + '_value');
		if (null != obControlOut)
			obControlOut.innerHTML = value;
	},

	addPoint: function()
	{
		if (!jsYandexCE.bAddPointMode)
		{
			if (jsYandexCE.bAddPolyMode)
				jsYandexCE.addPolyline();

			jsYandexCE.bAddPointMode = true;
			jsYandexCE.map.behaviors.disable("dblClickZoom");

			BX('bx_yandex_addpoint_link').style.display = 'none';
			BX('bx_yandex_addpoint_message').style.display = 'block';

			jsYandexCE.map.events.add("dblclick", jsYandexCE.__addPoint);
		}
		else
		{
			jsYandexCE.map.events.remove("dblclick", jsYandexCE.__addPoint);

			BX('bx_yandex_addpoint_message').style.display = 'none';
			BX('bx_yandex_addpoint_link').style.display = 'block';

			jsYandexCE.map.behaviors.enable("dblClickZoom");
			jsYandexCE.bAddPointMode = false;
		}
	},

	addPolyline: function()
	{
		if (jsYandexCE.bAddPolyMode)
		{
			if (null != jsYandexCE.arData.POLYLINES && jsYandexCE.arData.POLYLINES.length > 0 && !jsYandexCE.arData.POLYLINES[jsYandexCE.arData.POLYLINES.length-1].bFinished)
			{
				if (jsYandexCE.arData.POLYLINES.length > 1)
					jsYandexCE.arData.POLYLINES[jsYandexCE.arData.POLYLINES.length-1].Finish();
			}
			

			jsYandexCE.bAddPolyMode = false;
			jsYandexCE.map.behaviors.enable("dblClickZoom");
			BX('bx_yandex_addpoly_link').style.display = 'block';
			BX('bx_yandex_addpoly_message').style.display = 'none';
			BX('bx_yandex_addpoly_message1').style.display = 'none';

			jsYandexCE.map.events.remove('dblclick', jsYandexCE.__startPoly);
		}
		else
		{
			if (jsYandexCE.bAddPointMode)
				jsYandexCE.addPoint();

			jsYandexCE.bAddPolyMode = true;
			jsYandexCE.map.behaviors.disable("dblClickZoom");
			BX('bx_yandex_addpoly_link').style.display = 'none';
			BX('bx_yandex_addpoly_message').style.display = 'block';

			jsYandexCE.map.events.add('dblclick', jsYandexCE.__startPoly);
		}
	},

	addCustomPoint: function(arPointInfo)
	{
		if (null == jsYandexCE.arData.PLACEMARKS)
			jsYandexCE.arData.PLACEMARKS = [];

		var index = jsYandexCE.arData.PLACEMARKS.length;
		jsYandexCE.arData.PLACEMARKS[index] = new CBXYandexPoint({
			TEXT: arPointInfo.TEXT, LON: arPointInfo.LON, LAT: arPointInfo.LAT
		});

		jsYandexCE.arData.PLACEMARKS[index].onDelete = function () {jsYandexCE.arData.PLACEMARKS[index].DELETED = 1};

		return index;
	},

	addCustomPoly: function(arPolyInfo)
	{
		if (null == jsYandexCE.arData.POLYLINES)
			jsYandexCE.arData.POLYLINES = [];
		var index = jsYandexCE.arData.POLYLINES.length;

		jsYandexCE.arData.POLYLINES[index] = new CBXYandexPoly(arPolyInfo);
		jsYandexCE.arData.POLYLINES[index].onDelete = function () {jsYandexCE.arData.POLYLINES[index].DELETED = 1};

		return index;
	},

	__addPoint: function(obEvent)
	{
		if (!jsYandexCE.bAddPointMode)
			return;

		var pos = obEvent.get("coordPosition");
		var index = jsYandexCE.addCustomPoint({
			TEXT: '', LAT: pos[0], LON: pos[1]
		});

		jsYandexCE.arData.PLACEMARKS[index].Edit();
	},

	__startPoly: function(obEvent)
	{
		if (!jsYandexCE.bAddPolyMode)
			return;

		if (null == jsYandexCE.arData.POLYLINES)
			jsYandexCE.arData.POLYLINES = [];

		jsYandexCE.map.events.remove("dblclick", jsYandexCE.__addPoint);

		BX('bx_yandex_addpoly_message').style.display = 'none';
		BX('bx_yandex_addpoly_message1').style.display = 'block';

		var index = jsYandexCE.arData.POLYLINES.length;

		jsYandexCE.arData.POLYLINES[index] = new CBXYandexPoly();
		jsYandexCE.arData.POLYLINES[index].Start(obEvent.get("coordPosition"));

		jsYandexCE.arData.POLYLINES[index].onFinish = function() {
			BX('bx_yandex_addpoly_message').style.display = 'block';
			BX('bx_yandex_addpoly_message1').style.display = 'none';

			jsYandexCE.map.events.add('dblclick', jsYandexCE.__startPoly);
		};

		jsYandexCE.arData.POLYLINES[index].onDelete = function () {jsYandexCE.arData.POLYLINES[index].DELETED = 1};
	},

	__checkValidKey: function(key)
	{
		if (Number(key) == key)
			return true;

		for (var i = 0, len = jsYandexCE.__arValidKeys.length; i < len; i++)
		{
			if (jsYandexCE.__arValidKeys[i] == key)
				return true;
		}

		return false;
	},

	__serialize: function(obj)
	{
  		if (typeof(obj) == 'object')
  		{
    		var str = '', cnt = 0;
		    for (var i in obj)
		    {
				if (jsYandexCE.__checkValidKey(i))
				{
					++cnt;
					str += jsYandexCE.__serialize(i) + jsYandexCE.__serialize(obj[i]);
				}
		    }

    		str = "a:" + cnt + ":{" + str + "}";

    		return str;
		}
		else if (typeof(obj) == 'boolean')
		{
			return 'b:' + (obj ? 1 : 0) + ';';
		}
		else if (null == obj)
		{
			return 'N;'
		}
		else if (Number(obj) == obj && obj != '' && obj != ' ')
		{
			if (Math.floor(obj) == obj)
				return 'i:' + obj + ';';
			else
				return 'd:' + obj + ';';
		}
		else if(typeof(obj) == 'string')
		{
			obj = obj.replace(/\r\n/g, "\n");
			obj = obj.replace(/\n/g, "###RN###");

			var offset = 0;
			if (window._global_BX_UTF)
			{
				for (var q = 0, cnt = obj.length; q < cnt; q++)
				{
					if (obj.charCodeAt(q) > 2047) offset+=2;
					else if (obj.charCodeAt(q) > 127) offset++;
				}
			}

			return 's:' + (obj.length + offset) + ':"' + obj + '";';
		}
	},

	__saveChanges: function()
	{
		if (!jsYandexCE.map)
			return false;

		jsYandexCE.bAddPointMode = false;

		var arSerializeData = {
			'yandex_lat':jsYandexCE.arData.yandex_lat,
			'yandex_lon':jsYandexCE.arData.yandex_lon,
			'yandex_scale':jsYandexCE.arData.yandex_scale
		};

		if (jsYandexCE.arData['PLACEMARKS'])
		{
			arSerializeData.PLACEMARKS = [];

			for(var i = 0, len = jsYandexCE.arData.PLACEMARKS.length; i < len; i++)
			{
				if (null == jsYandexCE.arData.PLACEMARKS[i].DELETED)
					arSerializeData.PLACEMARKS[arSerializeData.PLACEMARKS.length] = jsYandexCE.arData.PLACEMARKS[i].getData();
			}
		}

		if (jsYandexCE.arData['POLYLINES'])
		{
			arSerializeData.POLYLINES = [];

			for(var i = 0, len = jsYandexCE.arData.POLYLINES.length; i < len; i++)
			{
				if (null == jsYandexCE.arData.POLYLINES[i].DELETED && null != jsYandexCE.arData.POLYLINES[i].POLYLINE)
					arSerializeData.POLYLINES[arSerializeData.POLYLINES.length] = jsYandexCE.arData.POLYLINES[i].getData();
			}
		}

		window.jsYandexCEOpener.saveData(jsYandexCE.__serialize(arSerializeData), jsYandexCE.currentView);

		//jsYandexCE.clear();

		return false;
	},

	clear: function()
	{
		jsYandexCE.bInitCompleted = false;
		jsYandexCE.bInitScriptsLoaded = false;

		jsYandexCE.bAddPointMode = false;
		jsYandexCE.bAddPolyMode = false;

		if (null != jsYandexCE.arData.PLACEMARKS && jsYandexCE.arData.PLACEMARKS.length > 0)
		{
			for (var i = 0,len = jsYandexCE.arData.PLACEMARKS.length; i<len; i++)
			{
				jsYandexCE.arData.PLACEMARKS[i].Delete();
				jsYandexCE.arData.PLACEMARKS[i] = null;
			}
			jsYandexCE.arData.PLACEMARKS = [];
		}

		if (null != jsYandexCE.arData.POLYLINES && jsYandexCE.arData.POLYLINES.length > 0)
		{
			for (var i = 0,len = jsYandexCE.arData.POLYLINES.length; i<len; i++)
			{
				jsYandexCE.arData.POLYLINES[i].Delete();
				jsYandexCE.arData.POLYLINES[i] = null;
			}
			jsYandexCE.arData.POLYLINES = [];
		}

		jsYandexCE.map = null;
	}
}

var jsYandexCESearch = {
	bInited: false,

	map: null,
	obInput: null,
	timerID: null,
	timerDelay: 1000,

	arSearchResults: [],

	obOut: null,

	__init: function(input)
	{
		if (jsYandexCESearch.bInited) return;

		jsYandexCESearch.map = jsYandexCE.map;
		jsYandexCESearch.obInput = input;

		input.form.onsubmit = function() {jsYandexCESearch.doSearch(); return false;}

		input.onfocus = jsYandexCESearch.showResults;
		input.onblur = jsYandexCESearch.hideResults;

		jsYandexCESearch.bInited = true;
	},

	setTypingStarted: function(input)
	{
		if (!jsYandexCESearch.bInited)
			jsYandexCESearch.__init(input);

		jsYandexCESearch.hideResults();

		if (null != jsYandexCESearch.timerID)
			clearTimeout(jsYandexCESearch.timerID);

		jsYandexCESearch.timerID = setTimeout(jsYandexCESearch.doSearch, jsYandexCESearch.timerDelay);
	},

	doSearch: function()
	{
		var value = BX.util.trim(jsYandexCESearch.obInput.value);
		if (value.length > 1)
		{
			ymaps.geocode(value).then(
				jsYandexCESearch.__searchResultsLoad,
				jsYandexCESearch.handleError
			);
		}
	},

	handleError: function(error)
	{
		alert(this.jsMess.mess_error + ': ' + error.message);
	},

	__generateOutput: function()
	{

		jsYandexCESearch.obOut = document.body.appendChild(document.createElement('UL'));
		jsYandexCESearch.obOut.className = 'bx-yandex-address-search-results';
		jsYandexCESearch.obOut.style.display = 'none';
	},

	__searchResultsLoad: function(res)
	{
		if (null == jsYandexCESearch.obOut)
			jsYandexCESearch.__generateOutput();

		jsYandexCESearch.obOut.innerHTML = '';
		jsYandexCESearch.clearSearchResults();

		var len = res.geoObjects.getLength();
		if (len > 0)
		{
			for (var i = 0; i < len; i++)
			{
				jsYandexCESearch.arSearchResults[i] = res.geoObjects.get(i);

				var obListElement = document.createElement('LI');

				if (i == 0)
					obListElement.className = 'bx-yandex-first';

				var obLink = document.createElement('A');
				obLink.href = "javascript:void(0)";
				var obText = obLink.appendChild(document.createElement('SPAN'));

				obText.appendChild(document.createTextNode(
					jsYandexCESearch.arSearchResults[i].properties.get('metaDataProperty').GeocoderMetaData.text
				));

				obLink.BXSearchIndex = i;
				obLink.onclick = jsYandexCESearch.__showSearchResult;

				obListElement.appendChild(obLink);
				jsYandexCESearch.obOut.appendChild(obListElement);
			}
		}
		else
		{
			jsYandexCESearch.obOut.innerHTML = '<li class="bx-yandex-notfound">' + window.jsYandexMess.nothing_found + '</li>';
		}

		jsYandexCESearch.showResults();
	},

	__showSearchResult: function(e)
	{
		if (null !== this.BXSearchIndex)
		{
			jsYandexCESearch.map.panTo(jsYandexCESearch.arSearchResults[this.BXSearchIndex].geometry.getCoordinates());
		}

		return BX.PreventDefault(e);
	},

	showResults: function()
	{
        var obPos = BX.pos(jsYandexCESearch.obInput);
        jsYandexCESearch.obOut.style.top = (obPos.bottom + 2) + 'px';
        jsYandexCESearch.obOut.style.left = obPos.left + 'px';
        jsYandexCESearch.obOut.style.zIndex = parseInt(BX.WindowManager.Get().zIndex) + 200;

        if (BX.findParent(jsYandexCESearch.obInput, {"tag" : "div", "className" : "bx-core-window bx-core-adm-dialog"}).style.display == 'block')
        {
            if (null != jsYandexCESearch.obOut)
                jsYandexCESearch.obOut.style.display = 'block';
        }
	},

	hideResults: function()
	{
		if (null != jsYandexCESearch.obOut)
		{
			setTimeout("jsYandexCESearch.obOut.style.display = 'none'", 300);
		}
	},

	clearSearchResults: function()
	{
		for (var i = 0; i < jsYandexCESearch.arSearchResults.length; i++)
		{
			delete jsYandexCESearch.arSearchResults[i];
		}

		jsYandexCESearch.arSearchResults = [];
	},

	clear: function()
	{
		if (!jsYandexCESearch.bInited)
			return;

		jsYandexCESearch.bInited = false;
		if (null != jsYandexCESearch.obOut)
		{
			jsYandexCESearch.obOut.parentNode.removeChild(jsYandexCESearch.obOut);
			jsYandexCESearch.obOut = null;
		}

		jsYandexCESearch.arSearchResults = [];
		jsYandexCESearch.map = null;
		jsYandexCESearch.obInput = null;
		jsYandexCESearch.timerID = null;
	}
}

