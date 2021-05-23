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

			return this;
		},

		initializePickUpMap: function(selected)
		{
			if (!ymaps)
				return;

			this.pickUpMap = new ymaps.Map('pickUpMap', {
				center: !!selected
					? [selected.GPS_N, selected.GPS_S]
					: [this.pickUpOptions.defaultMapPosition.lat, this.pickUpOptions.defaultMapPosition.lon],
				zoom: this.pickUpOptions.defaultMapPosition.zoom
			});

			this.pickUpMap.behaviors.disable('scrollZoom');

			this.pickUpMap.events.add('click', BX.delegate(function(){
				if (this.pickUpMap.balloon.isOpen())
				{
					this.pickUpMap.balloon.close();
				}
			}, this));
		},

		pickUpMapFocusWaiter: function()
		{
			if (this.pickUpMap && this.pickUpMap.geoObjects)
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
			var bounds, diff0, diff1;

			bounds = this.pickUpMap.geoObjects.getBounds();
			if (bounds && bounds.length)
			{
				diff0 = bounds[1][0] - bounds[0][0];
				diff1 = bounds[1][1] - bounds[0][1];

				bounds[0][0] -= diff0/10;
				bounds[0][1] -= diff1/10;
				bounds[1][0] += diff0/10;
				bounds[1][1] += diff1/10;

				this.pickUpMap.setBounds(bounds, {checkZoomRange: true});
			}
		},

		showNearestPickups: function(successCb, failCb)
		{
			if (!ymaps)
				return;

			var provider = this.pickUpOptions.secureGeoLocation && BX.browser.IsChrome() && !this.context.isHttps
				? 'yandex'
				: 'auto';
			var maxTime = this.pickUpOptions.geoLocationMaxTime || 5000;

			ymaps.geolocation.get({
				provider: provider,
				timeOut: maxTime
			}).then(
				BX.delegate(function(result){
					if (!this.maxWaitTimeExpired)
					{
						this.maxWaitTimeExpired = true;

						result.geoObjects.options.set('preset', 'islands#darkGreenCircleDotIcon');
						this.pickUpMap.geoObjects.add(result.geoObjects);

						successCb(result);
					}
				}, this),
				BX.delegate(function() {
					if (!this.maxWaitTimeExpired)
					{
						this.maxWaitTimeExpired = true;

						failCb();
					}
				}, this)
			);
		},

		buildBalloons: function(activeStores)
		{
			if (!ymaps)
				return;

			var that = this;

			this.pickUpPointsJSON = [];

			for (var i = 0; i < activeStores.length; i++)
			{
				var storeInfoHtml = this.getStoreInfoHtml(activeStores[i]);

				this.pickUpPointsJSON.push({
					type: 'Feature',
					geometry: {type: 'Point', coordinates: [activeStores[i].GPS_N, activeStores[i].GPS_S]},
					properties: {storeId: activeStores[i].ID}
				});

				var geoObj = new ymaps.Placemark([activeStores[i].GPS_N, activeStores[i].GPS_S], {
					hintContent: BX.util.htmlspecialchars(activeStores[i].TITLE) + '<br />' + BX.util.htmlspecialchars(activeStores[i].ADDRESS),
					storeTitle: activeStores[i].TITLE,
					storeBody: storeInfoHtml,
					id: activeStores[i].ID,
					text: this.context.params.MESS_SELECT_PICKUP
				}, {
					balloonContentLayout: ymaps.templateLayoutFactory.createClass(
						'<h3>{{ properties.storeTitle }}</h3>' +
						'{{ properties.storeBody|raw }}' +
						'<br /><a class="btn btn-sm btn-primary" data-store="{{ properties.id }}">{{ properties.text }}</a>',
						{
							build: function() {
								this.constructor.superclass.build.call(this);

								var button = document.querySelector('a[data-store]');
								if (button)
									BX.bind(button, 'click', this.selectStoreByClick);
							},
							clear: function() {
								var button = document.querySelector('a[data-store]');
								if (button)
									BX.unbind(button, 'click', this.selectStoreByClick);

								this.constructor.superclass.clear.call(this);
							},
							selectStoreByClick: function(e) {
								var target = e.target || e.srcElement;

								if (that.pickUpMap.container.isFullscreen())
								{
									that.pickUpMap.container.exitFullscreen();
								}

								that.context.selectStore(target.getAttribute('data-store'));
								that.context.clickNextAction(e);
								that.pickUpMap.balloon.close();
							}
						}
					)
				});

				if (BX('BUYER_STORE').value === activeStores[i].ID)
				{
					geoObj.options.set('preset', 'islands#redDotIcon');
				}

				this.pickUpMap.geoObjects.add(geoObj);
			}
		},

		selectBalloon: function(storeItemId)
		{
			if (this.pickUpMap && this.pickUpMap.geoObjects)
			{
				this.pickUpMap.geoObjects.each(BX.delegate(function(placeMark){
					if (placeMark.properties.get('id'))
					{
						placeMark.options.unset('preset');
					}

					if (placeMark.properties.get('id') === storeItemId)
					{
						placeMark.options.set({preset: 'islands#redDotIcon'});
						this.pickUpMap.panTo([placeMark.geometry.getCoordinates()])
					}
				}, this));
			}
		},

		pickUpFinalAction: function()
		{
			if (this.pickUpMap && this.pickUpMap.geoObjects)
			{
				var buyerStoreInput = BX('BUYER_STORE');

				this.pickUpMap.geoObjects.each(function(geoObject){
					if (geoObject.properties.get('id') === buyerStoreInput.value)
					{
						geoObject.options.set({preset: 'islands#redDotIcon'});
					}
					else if (parseInt(geoObject.properties.get('id')) > 0)
					{
						geoObject.options.unset('preset');
					}
				});
			}
		},

		initializePropsMap: function(propsMapData)
		{
			if (!ymaps)
				return;

			this.propsMap = new ymaps.Map('propsMap', {
				center: [propsMapData.lat, propsMapData.lon],
				zoom: propsMapData.zoom
			});

			this.propsMap.behaviors.disable('scrollZoom');

			this.propsMap.events.add('click', BX.delegate(function(e){
				var coordinates = e.get('coords'), placeMark;

				if (this.propsMap.geoObjects.getLength() === 0)
				{
					placeMark = new ymaps.Placemark([coordinates[0], coordinates[1]], {}, {
						draggable:true,
						preset: 'islands#redDotIcon'
					});
					placeMark.events.add(['parentchange', 'geometrychange'], function() {
						var orderDesc = BX('orderDescription'),
							coordinates = placeMark.geometry.getCoordinates(),
							ind, before, after, string;

						if (orderDesc)
						{
							ind = orderDesc.value.indexOf(BX.message('SOA_MAP_COORDS') + ':');
							if (ind === -1)
							{
								orderDesc.value = BX.message('SOA_MAP_COORDS') + ': ' + coordinates[0] + ', '
									+ coordinates[1] + '\r\n' + orderDesc.value;
							}
							else
							{
								string = BX.message('SOA_MAP_COORDS') + ': ' + coordinates[0] + ', ' + coordinates[1];
								before = orderDesc.value.substring(0, ind);
								after = orderDesc.value.substring(ind + string.length);
								orderDesc.value = before + string + after;
							}
						}
					});

					this.propsMap.geoObjects.add(placeMark);
				}
				else
				{
					this.propsMap.geoObjects.get(0).geometry.setCoordinates([coordinates[0], coordinates[1]]);
				}
			}, this));
		},

		canUseRecommendList: function()
		{
			return (this.pickUpPointsJSON && this.pickUpPointsJSON.length);
		},

		getRecommendedStoreIds: function(geoLocation)
		{
			if (!geoLocation)
				return [];

			var storeIds = [];
			var length = this.pickUpPointsJSON.length < this.pickUpOptions.nearestPickUpsToShow
					? this.pickUpPointsJSON.length
					: this.pickUpOptions.nearestPickUpsToShow;

			this.storeQueryResult = {};

			for (var i = 0; i < length; i++)
			{
				var pointsGeoQuery = ymaps.geoQuery({
					type: 'FeatureCollection',
					features: this.pickUpPointsJSON
				});
				var res = pointsGeoQuery.getClosestTo(geoLocation);
				var storeId = res.properties.get('storeId');

				this.storeQueryResult[storeId] = res;
				storeIds.push(storeId);
				this.pickUpPointsJSON.splice(pointsGeoQuery.indexOf(res), 1);
			}

			return storeIds;
		},

		getDistance: function(geoLocation, storeId)
		{
			if (!geoLocation || !storeId)
				return false;

			var storeGeoQuery = this.storeQueryResult[storeId];
			var distance = ymaps.coordSystem.geo.getDistance(geoLocation.geometry.getCoordinates(), storeGeoQuery.geometry.getCoordinates());
			distance = Math.round(distance / 100) / 10;

			return distance;
		},

		propsMapFocusWaiter: function(){},

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