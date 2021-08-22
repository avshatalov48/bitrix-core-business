;(function()
{
	'use strict';

	BX.namespace('BX.Fileman.Google');

/**
 * Map object
 *
 * @param node Map node
 * @param param Map parameters
 * @constructor
 */

	BX.Fileman.Google.Map = function(node, param)
	{
		this.node = node;
		this.param = param;

		this.googleMap = null;

		BX.Fileman.Google.Loader.init(BX.proxy(this.create, this));
	};

	BX.Fileman.Google.Map.prototype.create = function()
	{
		this.googleMap = new window.google.maps.Map(this.node, this.processParameters(this.param));
	};

	BX.Fileman.Google.Map.prototype.getGoogleMap = function()
	{
		return this.googleMap;
	};

	BX.Fileman.Google.Map.prototype.processParameters = function(param)
	{
		var googleParam = {
			noClear: true
		};

		if(!!param)
		{
			if(!!param.center)
			{
				googleParam.center = BX.Fileman.Google.getGoogleLatLng(param.center);
			}

			if(!!param.zoom)
			{
				googleParam.zoom = param.zoom;
			}
		}

		return googleParam;
	};

	BX.Fileman.Google.Map.prototype.addPoint = function(latLng)
	{
		return new BX.Fileman.Google.Point(this, latLng);
	};

	BX.Fileman.Google.Map.prototype.panTo = function(latLng)
	{
		BX.Fileman.Google.Loader.init(BX.delegate(function(){
			this.getGoogleMap().panTo(BX.Fileman.Google.getGoogleLatLng(latLng));
		}, this));
	};

	BX.Fileman.Google.Point = function(map, latLng, param)
	{
		this.map = map;
		this.latLng = latLng;
		this.googleLatLng = null;

		this.infoWindow = null;
		this.infoWindowContent = null;

		this.events = {
			change: null,
			click: null
		};

		this.draggable = true;

		BX.Fileman.Google.Loader.init(BX.delegate(function()
		{
			this.googleLatLng = BX.Fileman.Google.getGoogleLatLng(latLng);

			var markerParam = {
				position: this.googleLatLng,
				map: this.map.getGoogleMap(),
				draggable: this.draggable
			};

			if(!!param)
			{
				for(var i in param)
				{
					if(param.hasOwnProperty(i))
					{
						markerParam[i] = param[i];
					}
				}
			}

			this.marker = new google.maps.Marker(markerParam);

			this.marker.addListener('click', BX.proxy(this.click, this));
			this.marker.addListener('dragend', BX.proxy(this.dragend, this));
		}, this));
	};


	BX.Fileman.Google.Point.prototype.setEvent = function(event, handler)
	{
		this.events[event] = handler;
	};

	BX.Fileman.Google.Point.prototype.getMarker = function()
	{
		return this.marker;
	};

	BX.Fileman.Google.Point.prototype.setDraggable = function(value)
	{
		if(this.marker)
		{
			this.marker.setDraggable(value);
		}

		this.draggable = value;
	};

	BX.Fileman.Google.Point.prototype.setContent = function(content)
	{
		BX.Fileman.Google.Loader.init(BX.defer(function(){
			this.setContent = this._setContent;
			this.setContent(content);
		}, this));
	};

	BX.Fileman.Google.Point.prototype._setContent = function(content)
	{
		if(this.infoWindow === null)
		{
			this.infoWindowContent = BX.create('span', {
				children: [content]
			});

			this.infoWindow = new google.maps.InfoWindow({
				content: this.infoWindowContent,
				disableAutoPan: true
			});
		}
		else
		{
			setTimeout(function(){
				if(BX.type.isDomNode(content))
				{
					BX.cleanNode(this.infoWindowContent);
					this.infoWindowContent.appendChild(content);
				}
				else
					{
						BX.adjust(this.infoWindowContent, {
							text: content
						});
					}
			}.bind(this));
		}
		this.infoWindow.open({
			anchor: this.marker,
			map: this.map.getGoogleMap(),
			shouldFocus: false,
		});
	};

	BX.Fileman.Google.Point.prototype.moveTo = function(latLng)
	{
		this.latLng = latLng;

		BX.Fileman.Google.Loader.init(BX.delegate(function()
		{
			this.googleLatLng = BX.Fileman.Google.getGoogleLatLng(latLng);
			this.marker.setPosition(this.googleLatLng);
		}, this));
	};

	BX.Fileman.Google.Point.prototype.click = function(e)
	{
		if(!!this.events.click)
		{
			this.events.click.apply(this, [this.latLng]);
		}
	};

	BX.Fileman.Google.Point.prototype.dragend = function(e)
	{
		this.googleLatLng = this.marker.getPosition();
		this.latLng = BX.Fileman.Google.getLatLng(this.googleLatLng);

		this.map.panTo(this.latLng);

		this.callChangeEvent();
	};

	BX.Fileman.Google.Point.prototype.callChangeEvent = function()
	{
		if(!!this.events.change)
		{
			this.events.change.apply(this, [this.latLng]);
		}

		BX.onCustomEvent(this, 'onGoogleMapPointChanged', [this.index, this.latLng]);
	};


	BX.Fileman.Google.Point.prototype.clean = function()
	{
		this.marker.setMap(null);
		this.marker = null;
	};

	BX.Fileman.Google.Point.prototype.getPosition = function()
	{
		this.latLng = BX.Fileman.Google.getLatLng(this.googleLatLng);

		return this.latLng;
	};


})();