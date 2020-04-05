truncate table b_stat_adv;
truncate table b_stat_adv_searcher;
truncate table b_stat_adv_event;
truncate table b_stat_adv_guest;
truncate table b_stat_adv_page;
truncate table b_stat_adv_searcher;
truncate table b_stat_day;
truncate table b_stat_day_site;
truncate table b_stat_event;
truncate table b_stat_event_day;
truncate table b_stat_event_list;
truncate table b_stat_guest;
truncate table b_stat_hit;
truncate table b_stat_searcher_hit;
truncate table b_stat_phrase_list;
truncate table b_stat_referer;
truncate table b_stat_referer_list;
update b_stat_searcher set
	DATE_CLEANUP=null,
	TOTAL_HITS=0
	;
truncate table b_stat_searcher_day;
truncate table b_stat_session;
truncate table b_stat_adv_day;
truncate table b_stat_adv_event_day;
update b_stat_country set
	SESSIONS=0,
	HITS=0,
	NEW_GUESTS=0,
	C_EVENTS=0
	;
truncate table b_stat_country_day;
update b_stat_city set
	SESSIONS=0,
	HITS=0,
	NEW_GUESTS=0,
	C_EVENTS=0
	;
truncate table b_stat_city_day;

truncate table b_stat_path;
truncate table b_stat_path_cache;
truncate table b_stat_path_adv;

truncate table b_stat_page;
truncate table b_stat_page_adv;
