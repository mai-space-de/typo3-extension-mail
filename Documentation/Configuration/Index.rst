.. _configuration:

=============
Configuration
=============

Extension Configuration
=======================

Navigate to **Admin Tools → Settings → Extension Configuration → mai_mail** to
configure the following options:

.. confval:: inboxHost
   :type: string
   :default: (empty)

   Hostname of your IMAP or POP3 server (e.g. ``mail.example.com``).
   Leave empty to disable the Inbox module.

.. confval:: inboxPort
   :type: int
   :default: 993

   Port number for the mail server connection.

.. confval:: inboxProtocol
   :type: string
   :default: imap

   Protocol to use: ``imap`` or ``pop3``.

.. confval:: inboxUsername
   :type: string
   :default: (empty)

   Username / email address for the inbox account.

.. confval:: inboxPassword
   :type: string
   :default: (empty)

   Password for the inbox account. Stored encrypted in ``LocalConfiguration.php``.

.. confval:: inboxEncryption
   :type: string
   :default: ssl

   Encryption method: ``ssl`` or ``tls`` or ``none``.

Scheduler Task
==============

After installing the TYPO3 Scheduler extension, a **Send Mail Queue** task is
available. Add it via **System → Scheduler** and configure the execution
frequency (e.g. every minute).

Mail Transport
==============

The extension uses TYPO3's built-in mail transport configured in
``$GLOBALS['TYPO3_CONF_VARS']['MAIL']``. Refer to the
`TYPO3 core documentation <https://docs.typo3.org/m/typo3/reference-coreapi/main/en-us/ApiOverview/Mail/Index.html>`_
for transport options (SMTP, sendmail, etc.).
