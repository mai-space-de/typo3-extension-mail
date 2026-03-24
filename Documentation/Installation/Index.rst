.. _installation:

============
Installation
============

Via Composer (recommended)
===========================

.. code-block:: bash

   composer require maispace/mai-mail

Then activate the extension in the TYPO3 Extension Manager and run database
compare to create the ``tx_maimail_domain_model_mailqueue`` table.

Manual installation
===================

1. Download and extract the extension into ``typo3conf/ext/mai_mail/``.
2. Activate the extension in the Extension Manager.
3. Run **Admin Tools → Maintenance → Analyze Database Structure** to create the queue table.

Requirements
============

* TYPO3 CMS 12.4 or higher
* PHP 8.2 or higher
* PHP ``imap`` extension (optional, only required for the Inbox viewer)
