BX.namespace('BX.Sale.OrderAjaxComponent.Maps');

(function() {
	'use strict';

	BX.Sale.OrderAjaxComponent.Maps = {
		init: function(ctx)
		{
			this.context = ctx || {};
			this.pickUpOptions = this.context.options.pickUpMap;
			this.propsOptions = this.context.options.propertyMap;
			this.maxWaitTimeExpired = false;
			this.icons = {
				red: 'http://maps.google.com/mapfiles/ms/icons/red-dot.png',
				green: 'http://maps.google.com/mapfiles/ms/icons/green-dot.png'
			};

			return this;
		},

		initializePickUpMap: function(selected)
		{
			if (!google)
				return;

			this.markers = [];
			this.bounds = new google.maps.LatLngBounds();

			var lat = !!selected ? parseFloat(BX.util.trim(selected.GPS_N)) : this.pickUpOptions.defaultMapPosition.lat,
				lng = !!selected ? parseFloat(BX.util.trim(selected.GPS_S)) : this.pickUpOptions.defaultMapPosition.lon;

			this.pickUpMap = new google.maps.Map(BX('pickUpMap'), {
				center: {lat: lat, lng: lng},
				zoom: this.pickUpOptions.defaultMapPosition.zoom,
				scrollwheel: false
			});

			google.maps.event.addListener(this.pickUpMap, 'click', BX.proxy(this.closeAllBalloons, this));
		},

		canUseRecommendList: function()
		{
			return false;
		},

		getRecommendedStoreIds: function(geoLocation, length)
		{
			return [];
		},

		getDistance: function(from, to)
		{
			return false;
		},

		pickUpMapFocusWaiter: function()
		{
			if (this.pickUpMap && this.markers.length)
			{
				this.setPickUpMapFocus();
			}
			else
			{
				setTimeout(BX.proxy(this.pickUpMapFocusWaiter, this), 100);
			}
		},

		setPickUpMapFocus: function()
		{
			google.maps.event.trigger(this.pickUpMap, 'resize');
			this.pickUpMap.fitBounds(this.bounds);
		},

		showNearestPickups: function(successCb, failCb)
		{
		},

		buildBalloons: function(activeStores)
		{
			if (!google)
				return;

			var i, marker;
			var that = this;

			for (i = 0; i < activeStores.length; i++)
			{
				marker = new google.maps.Marker({
					position: {lat: parseFloat(activeStores[i].GPS_N), lng: parseFloat(activeStores[i].GPS_S)},
					map: this.pickUpMap,
					title: activeStores[i].TITLE
				});
				marker.info = new google.maps.InfoWindow({
					content: '<h3>' + BX.util.htmlspecialchars(activeStores[i].TITLE) + '</h3>' + this.getStoreInfoHtml(activeStores[i]) + '<br />'
					+ '<a class="btn btn-sm btn-default" data-store="' + activeStores[i].ID + '">'
					+ this.context.params.MESS_SELECT_PICKUP + '</a>'
				});
				marker.storeId = activeStores[i].ID;

				google.maps.event.addListener(marker.info, 'domready', function(){
					var button = document.querySelector('a[data-store]');
					if (button)
					{
						BX.bind(button, 'click', BX.proxy(that.selectStoreByClick, that));
					}
				});

				google.maps.event.addListener(marker, 'click', function(){
					that.closeAllBalloons();
					this.info.open(this.getMap(), this);
				});

				if (BX('BUYER_STORE').value === activeStores[i].ID)
				{
					marker.setIcon(this.icons.green);
				}
				else
				{
					marker.setIcon(this.icons.red);
				}

				this.markers.push(marker);
				this.bounds.extend(marker.getPosition());
			}
		},

		selectStoreByClick: function(e)
		{
			var target = e.target || e.srcElement;

			this.context.selectStore(target.getAttribute('data-store'));
			this.context.clickNextAction(e);
			this.closeAllBalloons();
		},

		closeAllBalloons: function()
		{
			for (var i = 0; i < this.markers.length; i++)
			{
				this.markers[i].info.close();
			}
		},

		selectBalloon: function(storeItemId)
		{
			if (!this.pickUpMap || !this.markers || !this.markers.length)
				return;


			for (var i = 0; i < this.markers.length; i++)
			{
				if (this.markers[i].storeId === storeItemId)
				{
					this.markers[i].setIcon(this.icons.green);
					this.pickUpMap.panTo(this.markers[i].getPosition())
				}
				else
				{
					this.markers[i].setIcon(this.icons.red);
				}
			}
		},

		pickUpFinalAction: function()
		{
		},

		initializePropsMap: function(propsMapData)
		{
			if (!google)
				return;

			this.propsMap = new google.maps.Map(BX('propsMap'), {
				center: {lat: propsMapData.lat, lng: propsMapData.lon},
				zoom: propsMapData.zoom,
				scrollwheel: false
			});
			this.currentPropsMapCenter = this.propsMap.getCenter();

			google.maps.event.addListener(this.propsMap, 'click', BX.delegate(function(e){
				if (!this.propsMarker)
				{
					this.propsMarker = new google.maps.Marker({
						position: e.latLng,
						map: this.propsMap,
						draggable: true
					});

					this.propsMarker.addListener('drag', BX.proxy(this.onPropsMarkerChanged, this));
					this.propsMarker.addListener('dragend', BX.proxy(this.onPropsMarkerChanged, this));
				}
				else
				{
					this.propsMarker.setPosition(e.latLng);
				}

				this.currentPropsMapCenter = e.latLng;
				this.onPropsMarkerChanged({latLng: e.latLng});
			}, this));
		},

		onPropsMarkerChanged: function(event)
		{
			var orderDesc = BX('orderDescription', true),
				lat = event.latLng.lat(),
				lng = event.latLng.lng(),
				ind, before, after, string;

			if (orderDesc)
			{
				ind = orderDesc.value.indexOf(BX.message('SOA_MAP_COORDS') + ':');
				if (ind === -1)
				{
					orderDesc.value = BX.message('SOA_MAP_COORDS') + ': ' + lat + ', ' + lng + '\r\n' + orderDesc.value;
				}
				else
				{
					string = BX.message('SOA_MAP_COORDS') + ': ' + lat + ', ' + lng;
					before = orderDesc.value.substring(0, ind);
					after = orderDesc.value.substring(ind + string.length);
					orderDesc.value = before + string + after;
				}
			}
		},

		propsMapFocusWaiter: function()
		{
			if (this.propsMap)
			{
				this.setPropsMapFocus();
			}
			else
			{
				setTimeout(BX.proxy(this.propsMapFocusWaiter, this), 100);
			}
		},

		setPropsMapFocus: function()
		{
			google.maps.event.trigger(this.propsMap, 'resize');
			this.propsMap.setCenter(this.currentPropsMapCenter);
		},

		getStoreInfoHtml: function(currentStore)
		{
			var html = '';

			if (currentStore.ADDRESS)
				html += BX.message('SOA_PICKUP_ADDRESS') + ': ' + BX.util.htmlspecialchars(currentStore.ADDRESS) + '<br />';

			if (currentStore.PHONE)
				html += BX.message('SOA_PICKUP_PHONE') + ': ' + BX.util.htmlspecialchars(currentStore.PHONE) + '<br />';

			if (currentStore.SCHEDULE)
				html += BX.message('SOA_PICKUP_WORK') + ': ' + BX.util.htmlspecialchars(currentStore.SCHEDULE) + '<br />';

			if (currentStore.DESCRIPTION)
				html += BX.message('SOA_PICKUP_DESC') + ': ' + BX.util.htmlspecialchars(currentStore.DESCRIPTION) + '<br />';

			return html;
		}
	};
})();