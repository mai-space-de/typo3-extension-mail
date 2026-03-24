CREATE TABLE tx_maimail_domain_model_mailqueue (
    uid INT NOT NULL AUTO_INCREMENT,
    pid INT DEFAULT 0 NOT NULL,

    sender varchar(255) DEFAULT '' NOT NULL,
    recipients text,
    subject varchar(255) DEFAULT '' NOT NULL,
    body mediumtext,
    attachments text,
    status varchar(20) DEFAULT 'queued' NOT NULL,
    priority TINYINT DEFAULT 0 NOT NULL,
    scheduled_at INT DEFAULT 0 NOT NULL,
    sent_at INT DEFAULT 0 NOT NULL,
    error_message text,
    retry_count INT DEFAULT 0 NOT NULL,

    tstamp INT DEFAULT 0 NOT NULL,
    crdate INT DEFAULT 0 NOT NULL,
    deleted TINYINT DEFAULT 0 NOT NULL,
    hidden TINYINT DEFAULT 0 NOT NULL,

    PRIMARY KEY (uid),
    KEY parent (pid),
    KEY status (status),
    KEY scheduled_at (scheduled_at),
    KEY priority (priority)
);
