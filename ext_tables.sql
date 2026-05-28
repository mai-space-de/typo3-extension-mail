CREATE TABLE tx_maimail_queue (
    subject varchar(255) DEFAULT '' NOT NULL,
    recipient varchar(255) DEFAULT '' NOT NULL,
    body mediumtext,
    headers text,
    status varchar(20) DEFAULT 'queued' NOT NULL,
    retry_count int(11) unsigned DEFAULT '0' NOT NULL,
    error_message text,
    scheduled_at int(11) unsigned DEFAULT '0' NOT NULL,
    sent_at int(11) unsigned DEFAULT '0' NOT NULL
);

CREATE TABLE tx_maimail_log (
    subject varchar(255) DEFAULT '' NOT NULL,
    recipient varchar(255) DEFAULT '' NOT NULL,
    status varchar(20) DEFAULT 'sent' NOT NULL,
    sent_at int(11) unsigned DEFAULT '0' NOT NULL,
    error_message text
);
