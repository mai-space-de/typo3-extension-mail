CREATE TABLE tx_maimail_domain_model_mailqueue (
    uid int(11) NOT NULL AUTO_INCREMENT,
    pid int(11) DEFAULT 0 NOT NULL,

    sender varchar(255) DEFAULT '' NOT NULL,
    recipients text,
    subject varchar(255) DEFAULT '' NOT NULL,
    body mediumtext,
    attachments text,
    status varchar(20) DEFAULT 'queued' NOT NULL,
    priority tinyint(3) DEFAULT 0 NOT NULL,
    scheduled_at int(11) DEFAULT 0 NOT NULL,
    sent_at int(11) DEFAULT 0 NOT NULL,
    error_message text,
    retry_count int(11) DEFAULT 0 NOT NULL,

    tstamp int(11) DEFAULT 0 NOT NULL,
    crdate int(11) DEFAULT 0 NOT NULL,
    deleted tinyint(4) DEFAULT 0 NOT NULL,
    hidden tinyint(4) DEFAULT 0 NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY status (status),
    KEY scheduled_at (scheduled_at),
    KEY priority (priority)
);
