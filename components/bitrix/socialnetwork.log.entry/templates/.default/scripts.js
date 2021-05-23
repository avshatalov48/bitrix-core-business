window["__logCommentsListRedefine"] = function(ENTITY_XML_ID, node_quote_id, author_id, logId)
{
	if (window["UC"] && !!window["UC"][ENTITY_XML_ID])
	{
		BX.addCustomEvent(window["UC"][ENTITY_XML_ID], "OnUCListWasBuilt", function(obj, data, container){
			if (BX(container) && container.hasChildNodes)
			{
				var node = container.firstChild, id;
				do {
					if (BX(node) && node["getAttribute"])
					{
						id = node.getAttribute("id").replace('record-' + ENTITY_XML_ID + '-', '').replace('-cover', '');
						BX.onCustomEvent(window, "OnUCAddEntitiesCorrespondence", [ENTITY_XML_ID + '-' + id, [logId, top["arLogCom" + logId + id]]]);
					}
				} while ((node = node.nextSibling))
			}
		});
	}
	if (!!window.mplCheckForQuote)
		BX.bind(BX(node_quote_id), "mouseup", function(e){ mplCheckForQuote(e, this, ENTITY_XML_ID, author_id) });

};

window["__logBuildRating"] = function(comm, commFormat, anchor_id) {
	var ratingNode = '';
		anchor_id = (!!anchor_id ? anchor_id : (Math.floor(Math.random()*100000) + 1));
	if ( BX.message("sonetLShowRating") == 'Y' &&
		!!comm["RATING_TYPE_ID"] > 0 && comm["RATING_ENTITY_ID"] > 0 &&
		(BX.message("sonetLRatingType") == "like" && !!window["RatingLike"] || BX.message("sonetLRatingType") == "standart_text" && !!window["Rating"]))
	{
		if (BX.message("sonetLRatingType") == "like")
		{
			var
				you_like_class = (comm["RATING_USER_VOTE_VALUE"] > 0) ? " bx-you-like" : "",
				you_like_text = (comm["RATING_USER_VOTE_VALUE"] > 0) ? BX.message("sonetLTextLikeN") : BX.message("sonetLTextLikeY"),
				vote_text = null;
			if (!!commFormat["ALLOW_VOTE"] &&
				!!commFormat["ALLOW_VOTE"]["RESULT"])
				vote_text = BX.create('span', {
					props: {
						'className': 'bx-ilike-text'
					},
					html: you_like_text
				});

			ratingNode = BX.create('span', {
				attrs : {
					id : 'sonet-rating-' + comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id
				},
				props: {
					'className': 'sonet-log-comment-like rating_vote_text'
				},
				children: [
					BX.create('span', {
						props: {
							'className': 'ilike-light'
						},
						children: [
							BX.create('span', {
								props: {
									'id': 'bx-ilike-button-' + comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id,
									'className': 'bx-ilike-button'
								},
								children: [
									BX.create('span', {
										props: {
											'className': 'bx-ilike-right-wrap' + you_like_class
										},
										children: [
											BX.create('span', {
												props: {
													'className': 'bx-ilike-right'
												},
												html: comm["RATING_TOTAL_POSITIVE_VOTES"]
											})
										]
									}),
									BX.create('span', {
										props: {
											'className': 'bx-ilike-left-wrap'
										},
										children: [
											vote_text
										]
									})
								]
							}),
							BX.create('span', {
								props: {
									'id': 'bx-ilike-popup-cont-' + comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id,
									'className': 'bx-ilike-wrap-block'
								},
								style: {
									'display': 'none'
								},
								children: [
									BX.create('span', {
										props: {
											'className': 'bx-ilike-popup'
										},
										children: [
											BX.create('span', {
												props: {
													'className': 'bx-ilike-wait'
												}
											})
										]
									})
								]
							})
						]
					})
				]
			});
		}
		else if (BX.message("sonetLRatingType") == "standart_text")
		{
			ratingNode = BX.create('span', {
				attrs : {
					id : 'sonet-rating-' + comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id
				},
				props: {
					'className': 'sonet-log-comment-like rating_vote_text'
				},
				children: [
					BX.create('span', {
						props: {
							'className': 'bx-rating' + (!commFormat["ALLOW_VOTE"]['RESULT'] ? ' bx-rating-disabled' : '') + (comm["RATING_USER_VOTE_VALUE"] != 0 ? ' bx-rating-active' : ''),
							'id': 'bx-rating-' + comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id,
							'title': (!commFormat["ALLOW_VOTE"]['RESULT'] ? commFormat["ERROR_MSG"] : '')
						},
						children: [
							BX.create('span', {
								props: {
									'className': 'bx-rating-absolute'
								},
								children: [
									BX.create('span', {
										props: {
											'className': 'bx-rating-question'
										},
										html: (!commFormat["ALLOW_VOTE"]['RESULT'] ? BX.message("sonetLTextDenied") : BX.message("sonetLTextAvailable"))
									}),
									BX.create('span', {
										props: {
											'className': 'bx-rating-yes ' +  (comm["RATING_USER_VOTE_VALUE"] > 0 ? '  bx-rating-yes-active' : ''),
											'title': (comm["RATING_USER_VOTE_VALUE"] > 0 ? BX.message("sonetLTextCancel") : BX.message("sonetLTextPlus"))
										},
										children: [
											BX.create('a', {
												props: {
													'className': 'bx-rating-yes-count',
													'href': '#like'
												},
												html: ""+parseInt(comm["RATING_TOTAL_POSITIVE_VOTES"])
											}),
											BX.create('a', {
												props: {
													'className': 'bx-rating-yes-text',
													'href': '#like'
												},
												html: BX.message("sonetLTextRatingY")
											})
										]
									}),
									BX.create('span', {
										props: {
											'className': 'bx-rating-separator'
										},
										html: '/'
									}),
									BX.create('span', {
										props: {
											'className': 'bx-rating-no ' +  (comm["RATING_USER_VOTE_VALUE"] < 0 ? '  bx-rating-no-active' : ''),
											'title': (comm["RATING_USER_VOTE_VALUE"] < 0 ? BX.message("sonetLTextCancel") : BX.message("sonetLTextMinus"))
										},
										children: [
											BX.create('a', {
												props: {
													'className': 'bx-rating-no-count',
													'href': '#dislike'
												},
												html: ""+parseInt(comm["RATING_TOTAL_NEGATIVE_VOTES"])
											}),
											BX.create('a', {
												props: {
													'className': 'bx-rating-no-text',
													'href': '#dislike'
												},
												html: BX.message("sonetLTextRatingN")
											})
										]
									})
								]
							})
						]
					}),
					BX.create('span', {
						props: {
							'id': 'bx-rating-popup-cont-' + comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id + '-plus'
						},
						style: {
							'display': 'none'
						},
						children: [
							BX.create('span', {
								props: {
									'className': 'bx-ilike-popup  bx-rating-popup'
								},
								children: [
									BX.create('span', {
										props: {
											'className': 'bx-ilike-wait'
										}
									})
								]
							})
						]
					}),
					BX.create('span', {
						props: {
							'id': 'bx-rating-popup-cont-' + comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id + '-minus'
						},
						style: {
							'display': 'none'
						},
						children: [
							BX.create('span', {
								props: {
									'className': 'bx-ilike-popup  bx-rating-popup'
								},
								children: [
									BX.create('span', {
										props: {
											'className': 'bx-ilike-wait'
										}
									})
								]
							})
						]
					})
				]
			});
		}
	}

	if (!!ratingNode)
	{
		ratingNode = BX.create('span', { children : [ ratingNode ] } );
		ratingNode = ratingNode.innerHTML +
			'<script>window["#OBJ#"].Set("#ID#", "#RATING_TYPE_ID#", #RATING_ENTITY_ID#, "#ALLOW_VOTE#", BX.message("sonetLCurrentUserID"), #TEMPLATE#, "light", BX.message("sonetLPathToUser"));</script>'.
			replace("#OBJ#", (BX.message("sonetLRatingType") == "like" ? "RatingLike" : "Rating")).
			replace("#ID#", comm["RATING_TYPE_ID"] + '-' + comm["RATING_ENTITY_ID"] + '-' + anchor_id).
			replace("#RATING_TYPE_ID#", comm["RATING_TYPE_ID"]).
			replace("#RATING_ENTITY_ID#", comm["RATING_ENTITY_ID"]).
			replace("#ALLOW_VOTE#", (!!commFormat["ALLOW_VOTE"] && !!commFormat["ALLOW_VOTE"]['RESULT'] ? 'Y' : 'N')).
			replace("#TEMPLATE#", (BX.message("sonetLRatingType") == "like" ?
				'{LIKE_Y:BX.message("sonetLTextLikeN"),LIKE_N:BX.message("sonetLTextLikeY"),LIKE_D:BX.message("sonetLTextLikeD")}' :
				'{PLUS:BX.message("sonetLTextPlus"),MINUS:BX.message("sonetLTextMinus"),CANCEL:BX.message("sonetLTextCancel")}'));

	}
	return ratingNode;
};
window["__logShowCommentForm"] = function(xmlId)
{
	if (!!window["UC"][xmlId])
		window["UC"][xmlId].reply();
};

function __logShowHiddenDestination(log_id, created_by_id, bindElement)
{
	BX.ajax.runAction('socialnetwork.api.livefeed.logentry.getHiddenDestinations', {
		data: {
			params: {
				logId: log_id,
				createdById: created_by_id,
				pathToUser: BX.message('sonetLPathToUser'),
				pathToWorkgroup: BX.message('sonetLPathToGroup'),
				pathToDepartment: BX.message('sonetLPathToDepartment'),
				nameTemplate: BX.message('sonetLNameTemplate'),
				showLogin: BX.message('sonetLShowLogin'),
				destinationLimit: BX.message('sonetLDestinationLimit')
			}
		}
	}).then(function(response)
	{
		var destinationList = response.data.destinationList;
		if (!BX.type.isNotEmptyObject(destinationList))
		{
			return;
		}

		if (BX(bindElement))
		{
			var containerNode = bindElement.parentNode;
			containerNode.removeChild(bindElement);

			var url = '';

			for (var key in destinationList)
			{
				if(!destinationList.hasOwnProperty(key))
				{
					continue;
				}

				if (BX.type.isNotEmptyString(destinationList[key]['TITLE']))
				{
					containerNode.appendChild(BX.create('SPAN', {
						html: ', '
					}));

					if (BX.type.isNotEmptyString(destinationList[key]['CRM_PREFIX']))
					{
						containerNode.appendChild(BX.create('SPAN', {
							props: {
								className: 'feed-add-post-destination-prefix'
							},
							html: destinationList[key]['CRM_PREFIX'] + ':&nbsp;'
						}));
					}

					if (BX.type.isNotEmptyString(destinationList[key]['URL']))
					{
						containerNode.appendChild(BX.create('A', {
							props: {
								className: 'feed-add-post-destination-new' + (BX.type.isNotEmptyString(destinationList[key]['IS_EXTRANET']) && destinationList[key]['IS_EXTRANET'] == 'Y' ? ' feed-post-user-name-extranet' : ''),
								href: destinationList[key]['URL']
							},
							html: destinationList[key]['TITLE']
						}));
					}
					else
					{
						containerNode.appendChild(BX.create('SPAN', {
							props: {
								className: 'feed-add-post-destination-new' + (BX.type.isNotEmptyString(destinationList[key]['IS_EXTRANET']) && destinationList[key]['IS_EXTRANET'] == 'Y' ? ' feed-post-user-name-extranet' : '')
							},
							html: destinationList[key]['TITLE']
						}));
					}
				}
			}

			if (
				typeof response.data['hiddenDestinationsCount'] != 'undefined'
				&& parseInt(response.data['hiddenDestinationsCount']) > 0
			)
			{
				response.data['hiddenDestinationsCount'] = parseInt(response.data['hiddenDestinationsCount']);
				var suffix = (
					(response.data['hiddenDestinationsCount'] % 100) > 10
					&& (response.data['hiddenDestinationsCount'] % 100) < 20
						? 5
						: response.data['hiddenDestinationsCount'] % 10
				);

				containerNode.appendChild(BX.create('SPAN', {
					html: '&nbsp;' + BX.message('sonetLDestinationHidden' + suffix).replace("#COUNT#", response.data['hiddenDestinationsCount'])
				}));
			}
		}

	}, function(response) {

	});
}

function __logSetFollow(log_id)
{
	return BX.Livefeed.FeedInstance.changeFollow({
		logId: log_id
	});
}

function __logRefreshEntry(params) // crm.livefeed.activity
{
	var entryNode = (params.node !== undefined ? BX(params.node) : false);
	var logId = (params.logId !== undefined ? parseInt(params.logId) : 0);

	if (
		!entryNode
		|| logId <= 0
		|| BX.message('sonetLEPath') === undefined
	)
	{
		return;
	}

	BX.ajax({
		url: BX.message('sonetLEPath').replace("#log_id#", logId),
		method: 'POST',
		dataType: 'json',
		data: {
			"log_id": logId,
			"action": "get_entry"
		},
		onsuccess: function(data) {
			if (data["ENTRY_HTML"] !== undefined)
			{
				BX.cleanNode(entryNode);
				entryNode.innerHTML = data["ENTRY_HTML"];
				var ob = BX.processHTML(entryNode.innerHTML, true);
				var scripts = ob.SCRIPT;
				BX.ajax.processScripts(scripts, true);
			}
		},
		onfailure: function(data) {}
	});
	return false;
}

window.__logEditComment = function(entityXmlId, key, postId)
{
	BX.ajax.runAction('socialnetwork.api.livefeed.comment.getsource', {
		data: {
			params: {
				postId: postId,
				commentId: key
			}
		}
	}).then(function(response) {
		var responseData = response.data;

			var eventData = {
				messageBBCode : responseData.message,
				messageFields : {
					arFiles : (
						BX.type.isNotEmptyObject(responseData.UF.UF_SONET_COM_FILE)
							? responseData.UF.UF_SONET_COM_FILE.VALUE
							: []
					)
				}
			};

			if (
				BX.type.isNotEmptyObject(responseData.UF.UF_SONET_COM_DOC)
				&& BX.type.isNotEmptyString(responseData.UF.UF_SONET_COM_DOC.USER_TYPE_ID)
			)
			{
				if (responseData.UF.UF_SONET_COM_DOC.USER_TYPE_ID == 'webdav_element')
				{
					eventData.messageFields.arDocs = responseData.UF.UF_SONET_COM_DOC.VALUE;
				}
				else if (responseData.UF.UF_SONET_COM_DOC.USER_TYPE_ID == 'disk_file')
				{
					eventData.messageFields.arDFiles = responseData.UF.UF_SONET_COM_DOC.VALUE;
				}
			}

			window.UC[window.SLEC.formKey].entitiesCorrespondence[entityXmlId + '-' + responseData.sourceId] = [ postId, responseData.id ];
			BX.onCustomEvent(window, 'OnUCAfterRecordEdit', [ entityXmlId, responseData.sourceId, eventData, 'EDIT' ]);
	}, function() {});
};

(function(){
	BX.SocialnetworkLogEntry = {
	};

	BX.SocialnetworkLogEntry.registerViewAreaList = function(params)
	{
		if (
			typeof params == 'undefined'
			|| typeof params.containerId == 'undefined'
			|| typeof params.className == 'undefined'
		)
		{
			return;
		}

		if (BX(params.containerId))
		{
			var
				viewAreaList = BX.findChildren(BX(params.containerId), {'tag':'div', 'className': params.className}, true),
				fullContentArea = null;

			for (var i = 0, length = viewAreaList.length; i < length; i++)
			{
				if (viewAreaList[i].id.length > 0)
				{
					fullContentArea = null;
					if (BX.type.isNotEmptyString(params.fullContentClassName))
					{
						fullContentArea = BX.findChild(viewAreaList[i], {
							className: params.fullContentClassName
						});
					}

					BX.UserContentView.registerViewArea(viewAreaList[i].id, (fullContentArea ? fullContentArea : null));
				}
			}
		}
	};
})();

