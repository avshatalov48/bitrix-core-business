CREATE INDEX tx_b_sender_mailing_chain_search_content ON b_sender_mailing_chain USING GIN (to_tsvector('english', search_content));
