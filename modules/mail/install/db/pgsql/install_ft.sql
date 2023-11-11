CREATE INDEX tx_b_mail_message_search_content ON b_mail_message USING GIN (to_tsvector('english', search_content));
