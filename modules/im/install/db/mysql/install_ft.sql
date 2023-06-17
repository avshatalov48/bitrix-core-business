CREATE fulltext index `IXF_IM_CHAT_INDEX_1` on `b_im_chat_index` (`SEARCH_CONTENT`);
CREATE fulltext index `IXF_IM_MESSAGE_INDEX_1` on `b_im_message_index` (`SEARCH_CONTENT`);
CREATE fulltext index `IXF_IM_CHAT_INDEX_2` on `b_im_chat_index` (`SEARCH_TITLE`);
CREATE fulltext index `IXF_IM_LINK_URL_1` on `b_im_link_url_index` (`SEARCH_CONTENT`);
CREATE fulltext index `IXF_B_IM_LINK_CALENDAR_INDEX_1` on `b_im_link_calendar_index` (`SEARCH_CONTENT`);