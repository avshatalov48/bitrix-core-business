<?php
/** @var CMain $APPLICATION */
IncludeModuleLangFile(__FILE__);
if(
	CModule::IncludeModule('statistic')
	&& $APPLICATION->GetGroupRight("statistic") != "D"
)
{
	$aMenu = Array(
		array(
			"parent_menu" => "global_menu_statistics",
			"sort" => 100,
			"text" => GetMessage("STATM_STATISTIC"),
			"title"=>GetMessage("STATM_STATISTIC_ALT"),
			"url" => "stat_list.php?lang=".LANGUAGE_ID,
			"icon" => "statistic_icon_summary",
			"page_icon" => "statistic_page_summary",
		),
		array(
			"parent_menu" => "global_menu_statistics",
			"sort" => 200,
			"text" => GetMessage("STATM_TRAFFIC"),
			"title"=>GetMessage("STATM_TRAF_TITLE"),
			"icon" => "statistic_icon_traffic",
			"page_icon" => "statistic_page_traffic",
			"items_id" => "menu_statistic1",
			"items" => array(
				array(
					"text" => GetMessage("STATM_DYN"),
					"title"=>GetMessage("STATM_TRAFFIC_ALT"),
					"items_id" => "menu_statistic_traf1",
					"items" =>
						Array(
							array(
								"text" => GetMessage("STATM_DYN_D"),
								"title"=>"",
								"url" => "traffic.php?lang=".LANGUAGE_ID."&amp;set_default=Y&amp;graph_type=date",
								"more_url"=>array(
									"traffic.php?graph_type=date",
									"traffic.php?find_graph_type=date",
								),
							),
							array(
								"text" => GetMessage("STATM_DYN_H"),
								"title"=>"",
								"url" => "traffic.php?lang=".LANGUAGE_ID."&amp;set_default=Y&amp;graph_type=hour",
								"more_url"=>array("traffic.php?graph_type=hour"),
							),
							array(
								"text" => GetMessage("STATM_DYN_W"),
								"title"=>"",
								"url" => "traffic.php?lang=".LANGUAGE_ID."&amp;set_default=Y&amp;graph_type=weekday",
								"more_url"=>array("traffic.php?graph_type=weekday"),
							),
							array(
								"text" => GetMessage("STATM_DYN_M"),
								"title"=>"",
								"url" => "traffic.php?lang=".LANGUAGE_ID."&amp;set_default=Y&amp;graph_type=month",
								"more_url"=>array("traffic.php?graph_type=month"),
							),

						)
				),
				array(
					"text" => GetMessage("STATM_VISITS_SECTION"),
					"title"=>GetMessage("STATM_VISITS_SECTION_ALT"),
					"url" => "visit_section_list.php?lang=".LANGUAGE_ID.'&amp;set_default=Y&amp;find_diagram_type=COUNTER',
					"more_url"=>array("visit_section_list.php?find_diagram_type=COUNTER"),
				),
				array(
					"text" => GetMessage("STATM_DYN_ENT"),
					"title"=> "",
					"url" => "visit_section_list.php?lang=".LANGUAGE_ID."&amp;set_default=Y&amp;find_diagram_type=ENTER_COUNTER",
					"more_url"=>array("visit_section_list.php?find_diagram_type=ENTER_COUNTER"),
				),
				array(
					"text" => GetMessage("STATM_DYN_EX"),
					"title"=> "",
					"url" => "visit_section_list.php?lang=".LANGUAGE_ID."&amp;set_default=Y&amp;find_diagram_type=EXIT_COUNTER",
					"more_url"=>array("visit_section_list.php?find_diagram_type=EXIT_COUNTER"),
				),
				array(
					"text" => GetMessage("STATM_SITE_PATH"),
					"title"=>GetMessage("STATM_SITE_PATH_ALT"),
					"items_id" => "menu_statistic_path",
					"items" =>
						Array(
							array(
								"text" => GetMessage("STATM_OTR"),
								"title"=> "",
								"url" => "path_list.php?lang=".LANGUAGE_ID.'&amp;set_default=Y&amp;find_diagram_type=COUNTER',
								"more_url"=>array("path_list.php?find_diagram_type=COUNTER"),
							),
							array(
								"text" => GetMessage("STATM_FULL"),
								"title"=>"",
								"url" => "path_list.php?lang=".LANGUAGE_ID.'&amp;set_default=Y&amp;find_diagram_type=COUNTER_FULL_PATH',
								"more_url"=>array("path_list.php?find_diagram_type=COUNTER_FULL_PATH"),
							)
						)
				),

				array(
					"text" => GetMessage("STATM_ATTENTIVENESS"),
					"title"=>GetMessage("STATM_ATTENTIVENESS_ALT"),
					"items_id" => "menu_statistic_attent",
					"items" =>
						Array(
							array(
								"text" => GetMessage("STATM_SESSION_DURATION"),
								"title"=> "",
								"url" => "attentiveness_list.php?lang=".LANGUAGE_ID.'&amp;find_diagram_type=DURATION&amp;set_default=Y',
								"more_url"=>array("attentiveness_list.php?find_diagram_type=DURATION"),
							),
							array(
								"text" => GetMessage("STATM_ACTIVITY"),
								"title"=>"",
								"url" => "attentiveness_list.php?lang=".LANGUAGE_ID.'&amp;find_diagram_type=ACTIVITY&amp;set_default=Y',
								"more_url"=>array("attentiveness_list.php?find_diagram_type=ACTIVITY"),
							)
						)
				),
				array(
					"text" => GetMessage("STATM_COUNTRY_GEOGRAPHY"),
					"title"=>GetMessage("STATM_COUNTRY_GEOGRAPHY_ALT"),
					"items_id" => "menu_statistic_country",
					"items" =>
						Array(
							array(
								"text" => GetMessage("STATM_SESS"),
								"title"=> "",
								"url" => "country_list.php?lang=".LANGUAGE_ID.'&amp;set_default=Y&amp;find_data_type=SESSIONS',
								"more_url"=>array("country_list.php?find_data_type=SESSIONS"),
							),
							array(
								"text" => GetMessage("STATM_NEWG"),
								"title"=>"",
								"url" => "country_list.php?lang=".LANGUAGE_ID.'&amp;set_default=Y&amp;find_data_type=NEW_GUESTS',
								"more_url"=>array("country_list.php?find_data_type=NEW_GUESTS"),
							),
							array(
								"text" => GetMessage("STATM_HITS"),
								"title"=>"",
								"url" => "country_list.php?lang=".LANGUAGE_ID.'&amp;set_default=Y&amp;find_data_type=HITS',
								"more_url"=>array("country_list.php?find_data_type=HITS"),
							),
							array(
								"text" => GetMessage("STATM_EVENTS"),
								"title"=>"",
								"url" => "country_list.php?lang=".LANGUAGE_ID.'&amp;set_default=Y&amp;find_data_type=C_EVENTS',
								"more_url"=>array("country_list.php?find_data_type=C_EVENTS"),
							)
						)
				),
				array(
					"text" => GetMessage("STATM_CITY_GEOGRAPHY"),
					"title"=>GetMessage("STATM_CITY_GEOGRAPHY_ALT"),
					"items_id" => "menu_statistic_city",
					"items" =>
						Array(
							array(
								"text" => GetMessage("STATM_SESS"),
								"title"=> "",
								"url" => "city_list.php?lang=".LANGUAGE_ID.'&amp;set_default=Y&amp;find_data_type=SESSIONS',
								"more_url"=>array("city_list.php?find_data_type=SESSIONS"),
							),
							array(
								"text" => GetMessage("STATM_NEWG"),
								"title"=>"",
								"url" => "city_list.php?lang=".LANGUAGE_ID.'&amp;set_default=Y&amp;find_data_type=NEW_GUESTS',
								"more_url"=>array("city_list.php?find_data_type=NEW_GUESTS"),
							),
							array(
								"text" => GetMessage("STATM_HITS"),
								"title"=>"",
								"url" => "city_list.php?lang=".LANGUAGE_ID.'&amp;set_default=Y&amp;find_data_type=HITS',
								"more_url"=>array("city_list.php?find_data_type=HITS"),
							),
							array(
								"text" => GetMessage("STATM_EVENTS"),
								"title"=>"",
								"url" => "city_list.php?lang=".LANGUAGE_ID.'&amp;set_default=Y&amp;find_data_type=C_EVENTS',
								"more_url"=>array("city_list.php?find_data_type=C_EVENTS"),
							)
						)
				),
			)
		),
		array(
			"parent_menu" => "global_menu_statistics",
			"sort" => 700,
			"text" => GetMessage("STATM_ADV"),
			"title"=>GetMessage("STATM_ADV_ALT"),
			"icon" => "statistic_icon_advert",
			"page_icon" => "statistic_page_advert",
			"items_id" => "menu_statistic2",
			"items" => array(
				Array(
					"text" => GetMessage("STATM_ADVLIST"),
					"url" => "adv_list.php?lang=".LANGUAGE_ID,
					"more_url" => array(
						"adv_edit.php",
						"adv_dynamic_list.php",
						"adv_graph_list.php",
						"adv_detail.php",
					),
					"title" => GetMessage("STATM_ADV_ALT")
				),
				Array(
					"text" => GetMessage("STATM_ADV_ANALYSIS"),
					"url" => "adv_analysis.php?lang=".LANGUAGE_ID."&amp;set_default=Y",
					"more_url" => array("adv_analysis.php"),
					"title" => GetMessage("STATM_ADV_ANALYSIS_ALT")
				)
			)
		),
		array(
			"parent_menu" => "global_menu_statistics",
			"sort" => 800,
			"text" => GetMessage("STATM_EVENTS"),
			"title"=> GetMessage("STATM_EVENTS"),
			"icon" => "statistic_icon_events",
			"page_icon" => "statistic_page_events",
			"items_id" => "menu_statistic3",
			"items" => array(
				Array(
					"text" => GetMessage("STATM_EVENT_TYPE"),
					"title" => GetMessage("STATM_EVENT_TYPE_ALT"),
					"items_id" => "menu_statistic_event_type",
					"items" => array(
						Array(
							"text" => GetMessage("STATM_LIST"),
							"url" => "event_type_list.php?lang=".LANGUAGE_ID,
							"more_url" => array(
								"event_type_list.php",
								"event_type_edit.php",
								"event_dynamic_list.php",
							),
							"title" => GetMessage("STATM_EVENT_TYPE_ALT"),
						),
						Array(
							"text" => GetMessage("STATM_DIAGRAM"),
							"url" => "event_diagram_list.php?lang=".LANGUAGE_ID."&amp;set_default=Y",
							"more_url" => array("event_diagram_list.php"),
							"title" => GetMessage("STATM_DIAGRAM_ALT")
						),
						Array(
							"text" => GetMessage("STATM_GRAPH_FULL"),
							"url" => "event_graph_list.php?lang=".LANGUAGE_ID."&amp;set_default=Y",
							"more_url" => array("event_graph_list.php"),
							"title" => GetMessage("STATM_GRAPH_FULL_ALT")
						),
					),
				),
				Array(
					"text" => GetMessage("STATM_EVENTS"),
					"url" => "event_list.php?lang=".LANGUAGE_ID,
					"title" => GetMessage("STATM_EVENTS_ALT"),
					"more_url" => array("event_list.php")
				),
				Array(
					"text" => GetMessage("STATM_EVENTS_LOADING"),
					"url" => "event_edit.php?lang=".LANGUAGE_ID,
					"title" => GetMessage("STATM_EVENTS_LOADING_ALT")
				)
			)
		),
		array(
			"parent_menu" => "global_menu_statistics",
			"sort" => 900,
			"text" => GetMessage("STATM_SEARCHERS"),
			"title"=> GetMessage("STATM_SEARCHERS"),
			"icon" => "statistic_icon_searchers",
			"page_icon" => "statistic_page_searchers",
			"items_id" => "menu_statistic4",
			"items" => array(
				Array(
					"text" => GetMessage("STATM_SEARCHERS_ENTRY"),
					"title" => GetMessage("STATM_SEARCHERS_ENTRY"),
					"items_id" => "menu_statistic_phras",
					"items" => array(
						Array(
							"text" => GetMessage("STATM_SEARCHERS_ENTRY_LIST"),
							"url" => "phrase_list.php?lang=".LANGUAGE_ID.'&amp;group_by=none&amp;set_filter=Y&amp;menu_item_id=1',
							"title" => "",
							"more_url" => Array("phrase_list.php?group_by=none&menu_item_id=1"),
						),
						Array(
							"text" => GetMessage("STATM_BY_SEARCHERS"),
							"url" => "phrase_list.php?lang=".LANGUAGE_ID.'&amp;group_by=S&amp;set_filter=Y',
							"title" => "",
							"more_url" => Array("phrase_list.php?group_by=S"),
						),
					),
				),
				Array(
					"text" => GetMessage("STATM_PHRASES_LIST"),
					"title" => GetMessage("STATM_PHRASES_ALT"),
					"url" => "phrase_list.php?lang=".LANGUAGE_ID.'&amp;group_by=P&amp;set_filter=Y',
					"more_url"=>array("phrase_list.php?group_by=P"),
				),
				Array(
					"text" => GetMessage("STATM_SEARCHERS_LIST"),
					"title" => GetMessage("STATM_SEARCHERS_ALT"),
					"items_id" => "menu_serchers_list",
					"items" => array(
						array(
							"text" => GetMessage("STATM_LIST"),
							"title" => GetMessage("STATM_SEARCHERS_ALT"),
							"url" => "searcher_list.php?lang=".LANGUAGE_ID,
							"more_url" => array(
								"searcher_edit.php",
								"searcher_dynamic_list.php",
							),
						),
						array(
							"text" => GetMessage("STATM_DIAGRAM"),
							"title" => GetMessage("STATM_SEARCHER_DIAGRAM_ALT"),
							"url" => "searcher_diagram_list.php?lang=".LANGUAGE_ID."&amp;set_default=Y",
							"more_url" => array("searcher_diagram_list.php"),
						),
						array(
							"text" => GetMessage("STATM_GRAPH"),
							"title" => GetMessage("STATM_GRAPH_ALT"),
							"url" => "searcher_graph_list.php?lang=".LANGUAGE_ID."&amp;set_default=Y",
							"more_url" => array("searcher_graph_list.php"),
						),
						Array(
							"text" => GetMessage("STATM_SEARCHERS_HITS"),
							"url" => "hit_searcher_list.php?lang=".LANGUAGE_ID."&amp;set_default=Y",
							"title" => GetMessage("STATM_SEARCHERS_HITS_ALT"),
							"more_url" => array("hit_searcher_list.php"),
						),
					)
				),
				Array(
					"text" => GetMessage("STATM_AUTODETECT"),
					"url" => "autodetect_list.php?lang=".LANGUAGE_ID,
					"more_url" => array("autodetect_list.php"),
					"title" => GetMessage("STATM_AUTODETECT_ALT")
				),
			)
		),
		Array(
			"parent_menu" => "global_menu_statistics",
			"sort" => 950,
			"text" => GetMessage("STATM_REFERERS"),
			"title" => GetMessage("STATM_REFERERS_ALT"),
			"icon" => "statistic_icon_sites",
			"page_icon" => "statistic_page_sites",
			"items_id" => "menu_statistic_refs",
			"items" => array(
				Array(
					"text" => GetMessage("STATM_SITES"),
					"url" => "referer_list.php?lang=".LANGUAGE_ID.'&amp;group_by=S',
					"more_url"=>array("referer_list.php?group_by=S"),
					"title" => "",
				),
				Array(
					"text" => GetMessage("STATM_PAGES"),
					"url" => "referer_list.php?lang=".LANGUAGE_ID.'&amp;group_by=U',
					"more_url"=>array("referer_list.php?group_by=U"),
					"title" => "",
				),
				Array(
					"text" => GetMessage("STATM_SEARCH_ENT"),
					"url" => "referer_list.php?lang=".LANGUAGE_ID.'&amp;group_by=none',
					"title" => GetMessage("STATM_REFENT_TITLE"),
					"more_url"=>array("referer_list.php?group_by=none", "referer_list.php"),
				),
			)
		),
		array(
			"parent_menu" => "global_menu_statistics",
			"sort" => 1000,
			"text" => GetMessage("STATM_GUESTS"),
			"title"=>GetMessage("STATM_GUESTS_ALT"),
			"icon" => "statistic_icon_visitors",
			"page_icon" => "statistic_page_visitors",
			"items_id" => "menu_statistic10",
			"items" => array(
				Array(
					"text" => GetMessage("STATM_GUEST_LIST"),
					"url" => "guest_list.php?lang=".LANGUAGE_ID,
					"more_url" => array("guest_detail.php"),
					"title" => GetMessage("STATM_GUESTS_ALT")
				),
				Array(
					"text" => GetMessage("STATM_SESSIONS"),
					"url" => "session_list.php?lang=".LANGUAGE_ID,
					"more_url" => array("session_detail.php"),
					"title" => GetMessage("STATM_SESSIONS_ALT")
				),
				Array(
					"text" => GetMessage("STATM_HITS"),
					"url" => "hit_list.php?lang=".LANGUAGE_ID,
					"more_url" => array("hit_detail.php"),
					"title" => GetMessage("STATM_HITS_ALT")
				),
				Array(
					"text" => GetMessage("STATM_STOPLIST"),
					"title"=>GetMessage("STATM_STOPLIST"),
					"url" => "stoplist_list.php?lang=".LANGUAGE_ID,
					"more_url" => Array("stoplist_edit.php"),
				),
			)
		),
		Array(
			"parent_menu" => "global_menu_statistics",
			"sort" => 1100,
			"text" => GetMessage("STATM_ONLINE"),
			"title"=>GetMessage("STATM_ONLINE_ALT"),
			"url" => "users_online.php?lang=".LANGUAGE_ID,
			"icon" => "statistic_icon_online",
			"page_icon" => "statistic_page_online",
		),
	);
	return $aMenu;
}
return false;
