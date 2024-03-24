CREATE INDEX tx_b_im_chat_index_search_content ON b_im_chat_index USING GIN (to_tsvector('english', search_content));
CREATE INDEX tx_b_im_chat_index_search_title ON b_im_chat_index USING GIN (to_tsvector('english', search_title));
CREATE INDEX tx_b_im_message_index_search_content ON b_im_message_index USING GIN (to_tsvector('english', search_content));
CREATE INDEX tx_b_im_link_url_index_search_content ON b_im_link_url_index USING GIN (to_tsvector('english', search_content));
CREATE INDEX tx_b_im_link_calendar_index_search_content ON b_im_link_calendar_index USING GIN (to_tsvector('english', search_content));
