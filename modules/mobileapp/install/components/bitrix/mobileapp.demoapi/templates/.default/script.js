var BXMPage = BXMobileApp.UI.Page;
var BXMobileDemoApi = {
	alert: {
		openSimpleAlert: function ()
		{
			app.alert({
				title: "Alert",
				button: "OK",
				text: BXMmessage["MBD_ALERT_TEXT"]
			});
		},
		showConfirm: function ()
		{
			app.confirm({
				title: BXMmessage["MBD_CHOOSE"],
				text: BXMmessage["MBD_CONFIRM_TEXT"],
				buttons: [BXMmessage["MBD_ONE_BUTTON"], BXMmessage["MBD_TWO_BUTTON"], BXMmessage["MBD_THREE_BUTTON"]]
			});
		}
	},
	buttons: {
		openButtonRightText: function ()
		{
			var buttonParams = {
				callback: function ()
				{
					app.alert({
						title: BXMmessage["MBD_ITWORKS"],
						button:  BXMmessage["MBD_ALERT_BUTTON"],
						text: BXMmessage["MBD_BUTTON_WITH_TEXT_PRESSED"]
					});

				},
				name: BXMmessage["MBD_BUTTON_TEXT_TITLE"],
				type: "text"
			};

			app.addButtons({button1: buttonParams});
		},
		openButtonPlus: function ()
		{
			var buttonParamsPlus = {
				callback: function ()
				{
					app.alert({
						title: BXMmessage["MBD_ITWORKS"],
						button: BXMmessage["MBD_ALERT_BUTTON"],
						text:  BXMmessage["MBD_BUTTON_WITH_PLUS_PRESSED"]
					});
				},
				type: "plus"
			};

			app.addButtons({button1: buttonParamsPlus});
		}
	},
	lists: {
		openListMarkModeSingle: function ()
		{
			var params = {
				url: dataPath+"/data/list.php?listType=simple",
				table_settings: {
					markmode: true,
					multiple: false,
					searchField: true,
					showtitle: true,
					name: BXMmessage["MBD_TABLE_HEADER"],
					footer: BXMmessage["MBD_TABLE_FOOTER"],

					callback: function (data)
					{
						app.alert({title: BXMmessage["MBD_BUTTON_MARKMODE_RESULT"], text: JSON.stringify(data)});
					}
				}
			};
			var table = new BXMobileApp.UI.Table(params, "table");
			table.show();
		},

		openListMarkModeMultiple: function ()
		{
			var params = {
				url: dataPath+"/data/list.php?listType=simple",
				table_settings: {
					modal: false,
					markmode: true,
					multiple: true,
					searchField: true,
					return_full_mode:true,
					showtitle: true,
					okname:"OK",
					name: BXMmessage["MBD_TABLE_HEADER"],
					footer: BXMmessage["MBD_TABLE_FOOTER"],
					callback: function (data)
					{
						app.alert({title: BXMmessage["MBD_BUTTON_MARKMODE_RESULT"], text: JSON.stringify(data)});
					}

				}
			};
			var markmode_table = new BXMobileApp.UI.Table(params, "table");
			markmode_table.show();
		},

		openListSelected: function ()
		{
			var params = {
				url: dataPath+"/data/list.php?listType=simple",
				table_settings: {
					modal: false,
					markmode: true,
					multiple: true,
					showtitle: true,
					okname:"OK",
					selected: ({elements: [1, 2]}),
					callback: function (data)
					{
						app.alert({title: BXMmessage["MBD_BUTTON_MARKMODE_RESULT"], text: JSON.stringify(data)});
					},
					name: BXMmessage["MBD_TABLE_HEADER"]
				}
			};
			var selected_markmode_table = new BXMobileApp.UI.Table(params, "table");
			selected_markmode_table.show();
		},

		openListSection: function ()
		{
			var params = {
				url: dataPath+"/data/list.php?listType=sections",
				table_settings: {
					use_sections: true,
					showtitle: true,
					name: BXMmessage["MBD_TABLE_HEADER"],
					footer: BXMmessage["MBD_TABLE_FOOTER"]

				}
			};
			var section_table = new BXMobileApp.UI.Table(params, "table");
			section_table.show();
		},
		openListSectionWithAlphabet: function ()
		{
			var params = {
				url: dataPath+"/data/list.php?listType=simple_alphabet",
				table_settings: {
					alphabet_index: true,
					showtitle: true,
					name: BXMmessage["MBD_TABLE_HEADER"]
				}
			};
			var alphabet_index_table = new BXMobileApp.UI.Table(params, "table");
			alphabet_index_table.show();
		},
		openModalList: function ()
		{
			app.openBXTable({
				url: dataPath+"/data/list.php?listType=simple",
				TABLE_SETTINGS: {
					markmode: true,
					multiple: true,
					modal: true,
					okname:"OK",
					showtitle: true,
					name: BXMmessage["MBD_TABLE_HEADER"],
					cancelname: BXMmessage["MBD_TABLE_CANCEL"],
					callback: function (data)
					{
						app.alert({title: BXMmessage["MBD_BUTTON_MARKMODE_RESULT"], text: JSON.stringify(data)});
					}
				}
			});
		},

		openListNestedTable: function ()
		{
			var url = dataPath+"/data/list.php?listType=recursive";
			app.openBXTable({
				url: url,
				TABLE_SETTINGS: {
					showtitle: true,
					name: BXMmessage["MBD_NAV_BAR_TITLE_TEXT"],
					cache: false,
					callback: function (data)
					{
						app.alert({title: BXMmessage["MBD_NAV_BAR_TITLE_TEXT"], text: JSON.stringify(data)});
					}
				}
			});
		}
	},
	page: {
		showModal: function ()
		{
			app.showModalDialog({
				title: "bitrix.ru",
				url: "index.php"
			});
		},
		showNew: function ()
		{
			app.loadPageBlank({
				url: "index2.php",
				title: "There Once Was a Dog"
			});
		}
	},
	loadingIndicator: {
		showPopup: function ()
		{
			BXMPage.PopupLoader.show();
			app.addButtons({
				button1: {
					callback: function ()
					{
						BXMPage.PopupLoader.hide();
						app.removeButtons({
							position: 'right'
						});
					},
					name: BXMmessage["MBD_HIDE"],
					type: "right_text"
				}
			});
		},
		showFullScreen: function ()
		{
			BXMPage.LoadingScreen.show();
			app.addButtons({
				button1: {
					callback: function ()
					{
						BXMPage.LoadingScreen.hide();
						app.removeButtons({
							position: 'right'
						});
					},
					name: BXMmessage["MBD_HIDE"],
					type: "right_text"
				}
			});
		}
	},
	pickers: {
		showSinglePicker: function ()
		{
			var items = [
				BXMmessage["MBD_PICKER_VERY_GOOD"],
				BXMmessage["MBD_PICKER_GOOD"],
				BXMmessage["MBD_PICKER_NORMAL"],
				BXMmessage["MBD_PICKER_BAD"],
				BXMmessage["MBD_PICKER_VERY_BAD"]
			];
			BXMobileApp.UI.SelectPicker.show({
				callback: function (data)
				{
					app.alert({title: BXMmessage["MBD_BUTTON_MARKMODE_RESULT"], text: JSON.stringify(data)});
				},

				values: items
			});
		},
		showMultiPicker: function ()
		{
			var items = [
				BXMmessage["MBD_PICKER_VERY_GOOD"],
				BXMmessage["MBD_PICKER_GOOD"],
				BXMmessage["MBD_PICKER_NORMAL"],
				BXMmessage["MBD_PICKER_BAD"],
				BXMmessage["MBD_PICKER_VERY_BAD"]
			];

			BXMobileApp.UI.SelectPicker.show({
				multiselect: true,
				callback: function (data)
				{
					app.alert({title: BXMmessage["MBD_BUTTON_MARKMODE_RESULT"], text: JSON.stringify(data)});
				},
				values: items,
				default_value: items [1]

			});
		},

		showTimePicker: function ()
		{
			BXMobileApp.UI.DatePicker.show({
				type: "time",
				start_date: "09:12",
				format: "h:mm",
				callback: function (d)
				{
					app.alert({title: "time", text: JSON.stringify(d)});
				}
			})
		},
		showDatePicker: function ()
		{
			BXMobileApp.UI.DatePicker.show({
				type: "date",
				start_date: "29.06.1998",
				format: "dd.MM.yyyy",
				callback: function (d)
				{
					app.alert({title: "date", text: JSON.stringify(d)});
				}
			})
		},
		showDateTimePicker: function ()
		{
			BXMobileApp.UI.DatePicker.show({
				type: "datetime",
				start_date: "01.03.2015, 09:12",
				format: "dd.MM.yyyy, h:mm",
				callback: function (d)
				{
					app.alert({title: "date", text: JSON.stringify(d)});
				}
			})
		},
		showDateTimePickerMinAndMax: function ()
		{
			BXMobileApp.UI.DatePicker.show({
				type: "datetime",
				start_date: "01.03.2015, 09:12",
				format: "dd.MM.yyyy, h:mm",
				max_date: "05.03.2015, 09:00",
				min_date: "01.03.2015, 09:00",
				callback: function (d)
				{
					app.alert({title: "datetime", text: JSON.stringify(d)});
				}
			})
		}
	},
	barcodeScanner: {
		show: function ()
		{
			BXMobileApp.UI.BarCodeScanner.open({
				callback: function (data)
				{
					if (data.text)
					{
						app.alert({
							title: "Barcode",
							text: "Format: " + JSON.stringify(data.format) + "\nBarcode: " + JSON.stringify(data.text)
						});
					}
					else
					{
						app.alert(
							{
								text: "Error:(",
								button: "OK"
							}
						);
					}
				}
			});
		}
	},
	photo: {
		showSingle: function ()
		{
			BXMobileApp.UI.Photo.show(
				{
					"photos": [
						{
							"url": dataPath+"/img/locked-icon.png"
						}
					]
				}
			)
		},
		showGallery: function ()
		{
			BXMobileApp.UI.Photo.show({
				"photos": [
					{
						"url": dataPath+"/img/addition-icon.png",
						"description": BXMmessage["MBD_PHOTO_DESC"]
					},
					{
						"url": dataPath+"/img/attach-2-icon.png",
						"description": BXMmessage["MBD_PHOTO_DESC"]
					},
					{
						"url": dataPath+"/img/check-icon.png"
					},
					{
						"url": dataPath+"/img/contact-icon.png",
						"description": BXMmessage["MBD_PHOTO_DESC"]
					}
				]
			})
		}
	},
	docs: {
		demoDocs: [
			'docs/text.txt',
			'docs/tables.xlsx',
			'img/1c_bitrix_mobilnoe_prilozhenie.jpg',
			'docs/sample.doc',
			'dosc/demo.docx',
			'docs/demo.pdf'
		],
		open: function (id)
		{
			app.openDocument({"url": dataPath + this.demoDocs[id]});
		}
	},
	camera: {
		open: function (source)
		{
			app.takePhoto(
				{
					source: source,
					callback: function (a)
					{
						app.alert({title: "takePhoto", text: JSON.stringify(a)});

					}
				});
		}
	},
	actionSheet: {
		open: function ()
		{
			var action = new BXMobileApp.UI.ActionSheet(
				{
					title: BXMmessage["MBD_PRODUCTS_BITRIX"],
					buttons: [
						{
							title: BXMmessage["MBD_PRODUCTS_BSM"],
							callback: function ()
							{
								app.alert({
									title: BXMmessage["MBD_PRODUCTS_BSM"],
									button: "OK",
									text: BXMmessage["MBD_PRODUCTS_BSM_DESC"]
								});
							}
						},
						{
							title: BXMmessage["MBD_PRODUCTS_CP"],
							callback: function ()
							{
								app.alert({
									title: BXMmessage["MBD_PRODUCTS_CP"],
									button: "OK",
									text: BXMmessage["MBD_PRODUCTS_CP_DESC"]
								});
							}
						},
						{
							title: BXMmessage["MBD_PRODUCTS_MB"],
							callback: function ()
							{
								app.alert({
									title:  BXMmessage["MBD_PRODUCTS_MB"],
									button: "OK",
									text: BXMmessage["MBD_PRODUCTS_CP_DESC"]
								});
							}
						}
					]
				}, "test");
			action.show();
		}
	},
	textPanel: {
		show: function ()
		{
			BXMobileApp.UI.Page.TextPanel.show();
		},
		setText: function ()
		{
			BXMobileApp.UI.Page.TextPanel.setText(BXMmessage["MBD_TEXT"]);
		},
		setPlusAction: function ()
		{
			var params =
			{
				plusAction: function ()
				{
					var action = new BXMobileApp.UI.ActionSheet(
						{
							buttons: [
								{
									title: "Button 1",
									callback: function ()
									{
										app.alert({
											title: BXMmessage["MBD_ONE_BUTTON"],
											button: "OK",
											text: BXMmessage["MBD_ONE_BUTTON_PRESSED"]
										});
									}
								},
								{
									title: "Button 2",
									callback: function ()
									{
										app.alert({
											title: BXMmessage["MBD_TWO_BUTTON"],
											button: "OK",
											text: BXMmessage["MBD_TWO_BUTTON_PRESSED"]
										});
									}
								}
							]
						}, "test");

					action.show();
				}
			};
			BXMobileApp.UI.Page.TextPanel.setParams(params);
		}
	},
	topBar: {
		showTitle: function ()
		{
			BXMobileApp.UI.Page.TopBar.title.setText(BXMmessage["MBD_NAV_BAR_TITLE_TEXT"]);
			BXMobileApp.UI.Page.TopBar.title.show();
		},
		setDetail:function(){
			BXMobileApp.UI.Page.TopBar.title.setDetailText(BXMmessage["MBD_NAV_BAR_TEXT_DETAIL"]);
		},
		setIcon :function(){
			BXMobileApp.UI.Page.TopBar.title.setImage(dataPath+'/img/laboratory-icon.png');
		},
		setTitleCallback: function ()
		{
			BXMobileApp.UI.Page.TopBar.title.setCallback(function ()
			{
				app.alert({title: "setCallback", text: BXMmessage["MBD_ITWORKS"]});
			});
		},
		resetTitle: function ()
		{
			BXMobileApp.UI.Page.TopBar.title.setImage('');
			BXMobileApp.UI.Page.TopBar.title.setText('');
			BXMobileApp.UI.Page.TopBar.title.setDetailText('');
			BXMobileApp.UI.Page.TopBar.title.setCallback('');
		},
		setColor: function (color)
		{
			BXMobileApp.UI.Page.TopBar.setColors(
				{
					background:color
				}
			);

		}
	},
	refresh: {
		show: function ()
		{
			var params = {
				enabled: true,
				callback: function ()
				{
					app.alert({title: BXMmessage["MBD_PULL_TO_REFRESH"], text: BXMmessage["MBD_PULL_TO_REFRESH"]})
				},
				pullText: BXMmessage["MBD_PULL_TO_REFRESH"],
				releaseText: BXMmessage["MBD_RELEASE_TO_REFRESH"],
				loadText: BXMmessage["MBD_LOADING"]
			};
			BXMobileApp.UI.Page.Refresh.setParams(params);
		}
	},
	menu: {
		show: function ()
		{
			var menu = new BXMobileApp.UI.Menu(
			{
				items: [
					{
						name: BXMmessage["MBD_MENU_1"],
						image: dataPath+"img/upravlenie-saitom.png",
						url: "https://www.1c-bitrix.ru/products/cms/"
					},
					{
						name: BXMmessage["MBD_MENU_2"],
						action: function ()
						{
							app.alert(
								{
									title: BXMmessage["MBD_MENU_2"],
									button: "OK",
									text: BXMmessage["MBD_MENU_2_PRESSED"]
								});
						},
						icon: 'check'
					},
					{
						name: BXMmessage["MBD_MENU_3"],
						url: "https://www.bitrix24.ru/",
						arrowFlag: "true"
					}
				]
			});

			menu.show();
		}
	},
	slidingPanel:{
		show:function(){
			BXMobileApp.UI.Page.SlidingPanel.show({
				hidden_sliding_panel: true,
				buttons:
				{
					list2:
					{
						name: BXMmessage["MBD_JUST_TEXT"],
						type: "right_text",
						callback: function ()
						{

							app.alert({title: BXMmessage["MBD_BUTTON_PUSHED"], text: BXMmessage["MBD_JUST_TEXT"]});
						}
					},
					list4:
					{
						type: "basket",
						callback: function ()
						{
							app.alert({title: BXMmessage["MBD_BUTTON_PUSHED"], text: BXMmessage["MBD_JUST_ICON"]});
						}
					},
					list3:
					{
						name: BXMmessage["MBD_ICON_AND_TEXT"],
						type: "menu",
						callback: function ()
						{
							app.alert({title: BXMmessage["MBD_BUTTON_PUSHED"], text: BXMmessage["MBD_ICON_AND_TEXT"]});

						}
					}
				}
			});
		},
		hide:function(){
			BXMobileApp.UI.Page.SlidingPanel.hide();
		}
	},
	notifications: {

		textNotifyBar: function ()
		{
			(new BXMobileApp.UI.NotificationBar({message: BXMmessage["MBD_TAP_ME_TO_DISMISS"]})).show();
		},

		textAndIconNotifyBar: function ()
		{
			(new BXMobileApp.UI.NotificationBar({
				color:"#76088c", hideOnTap:true,
				align:"center",
				message: BXMmessage["MBD_TAP_ME_TO_DISMISS"]})).show();
		},

		textAndImageNotifyBar: function ()
		{
			(new BXMobileApp.UI.NotificationBar({
				message: BXMmessage["MBD_TAP_ME_TO_DISMISS"],
				imageURL: dataPath+"img/bitrixico.png",
				color: "#ffd700",
				indicatorHeight:40,
			})).show();
		},

		textAndImageAndColorTextAndBackgroundNotifyBar: function ()
		{
			(new BXMobileApp.UI.NotificationBar({
				message: BXMmessage["MBD_TAP_ME_TO_DISMISS"],
				imageURL: dataPath+"img/bitrixico.png",
				textColor: "#b2fb49",
				color: "#76088c",
				indicatorHeight:40,
			})).show();
		},

		loaderNotifyBar: function ()
		{
			(new BXMobileApp.UI.NotificationBar({
				message: BXMmessage["MBD_TAP_ME_TO_DISMISS"],
				useLoader: true,
				align:"center",
				color: "#76088c",
				autoHideTimeout:2000,
				hideOnTap:true,
			})).show();
		},

		loaderGrayNotifyBar: function ()
		{
			(new BXMobileApp.UI.NotificationBar({
				message: BXMmessage["MBD_TAP_ME_TO_DISMISS"],
				useLoader: true,
				autoHideTimeout:2000,
				hideOnTap:true,
				loaderGray: true,
				color: "#b2fb49",
				textColor: "#76088c"
			})).show();
		},

		fullAlphaNotifyBar: function ()
		{
			(new BXMobileApp.UI.NotificationBar({
				message: BXMmessage["MBD_TAP_ME_TO_DISMISS"],
				loader: true,
				autoHideTimeout:2000,
				hideOnTap:true,
				loaderGray: true,
				color: "#b2fb49",
				textColor: "#76088c",
				alpha: 1.0
			})).show();
		},

		fiftyPercentAlphaNotifyBar: function ()
		{
			(new BXMobileApp.UI.NotificationBar({
				message: BXMmessage["MBD_TAP_ME_TO_DISMISS"],
				loader: true,
				autoHideTimeout:2000,
				hideOnTap:true,
				loaderGray: true,
				color: "#ccb2fb49",
				textColor: "#76088c",
			})).show();
		},

		MultilineNotifyBar: function ()
		{
			(new BXMobileApp.UI.NotificationBar({
				message: BXMmessage["MBD_NOTIF_TEXT"],
				maxLines:10,
				contentType: 'html',
				autoHideTimeout:2000,
				hideOnTap:true,
				contentEncoding:"utf8",
				textColor:"#000000",
				color: "#ccb2fb49",
			})).show();
		},
		MultilineAndImageNotifyBar: function ()
		{
			(new BXMobileApp.UI.NotificationBar({
				message:  BXMmessage["MBD_NOTIF_TEXT"],
				maxLines: 10,
				contentType: 'html',
				indicatorHeight:60,
				autoHideTimeout:10000,
				hideOnTap:true,
				textColor:"#ffffff",
				color:"#cc000000",
				imageURL: dataPath+"/img/addition-icon.png"
			})).show();
		},


		actionNotifyBar: function ()
		{
			(new BXMobileApp.UI.NotificationBar(
				{
					message: BXMmessage["MBD_TAP_ME_TO_DISMISS"],
					imageURL: dataPath+"/img/check-icon.png",
					textColor: "#b2fb49",
					autoHideTimeout:20000,
					hideOnTap:true,
					indicatorHeight:30,
					onHideAfter:function(data){
						//do something
					},
					color:"#cc000000",
					onTap: function ()
					{
						//do something
					}
				})).show();
		},
		actionNotifyBarMulti: function ()
		{
			var red =new BXMobileApp.UI.NotificationBar(
				{
					message: BXMmessage["MBD_TAP_ME_TO_DISMISS_OR_SHOW_ANOTHER"],
					imageURL: dataPath+"/img/locked-icon.png",
					textColor: "#ffffff",
					autoHideTimeout:20000,
					hideOnTap:true,
					maxLines:10,
					groupId:"3",
					indicatorHeight:40,
					onHideAfter:function(data){
						//do something
					},
					color:"#a1fb0000",
					onTap: function ()
					{
						gray.show();
					}
				});


			var gray = new BXMobileApp.UI.NotificationBar(
				{
					message: BXMmessage["MBD_TAP_ME_TO_DISMISS_OR_SHOW_ANOTHER"],
					imageURL: dataPath+"/img/check-icon.png",
					textColor: "#b2fb49",
					autoHideTimeout:20000,
					hideOnTap:true,
					groupId:"1",
					maxLines:10,
					indicatorHeight:40,
					onHideAfter:function(data){
						//do something
					},
					color:"#cc000000",
					onTap: function ()
					{
						red.show();
					}});
			(new BXMobileApp.UI.NotificationBar(
				{
					message: BXMmessage["MBD_TAP_ME_TO_DISMISS"],
					imageURL: dataPath+"/img/contact-icon.png",
					textColor: "#b2fb49",
					autoHideTimeout:20000,
					hideOnTap:true,
					groupId:"2",
					indicatorHeight:30,
					onHideAfter:function(data){
						//do something
					},
					color:"#cc000000",
					onTap: function ()
					{
						//do something
					}
				})).show();

			red.show();

		},

	}
};