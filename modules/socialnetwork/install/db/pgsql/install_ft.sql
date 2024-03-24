CREATE INDEX tx_b_sonet_log_index_content ON b_sonet_log_index USING GIN (to_tsvector('english', content));
CREATE INDEX tx_b_sonet_group_search_index ON b_sonet_group USING GIN (to_tsvector('english', search_index));
