CREATE INDEX tx_b_landing_search_content ON b_landing USING GIN (to_tsvector('english', search_content));
CREATE INDEX tx_b_landing_block_search_content ON b_landing_block USING GIN (to_tsvector('english', search_content));
