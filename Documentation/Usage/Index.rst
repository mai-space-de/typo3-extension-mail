.. _usage:

=====
Usage
=====

Backend Module
==============

The **Mai Mail** backend module appears in the left navigation bar after
installation. It contains the following sub-modules:

Queue
-----

Lists all messages in the mail queue. You can:

* Filter by status (queued / sending / sent / failed / retry).
* View full details of a message.
* Send a message immediately.
* Retry a failed message.
* Delete individual messages.

Statistics
----------

Displays aggregate counts per status and the overall success rate.

Compose
-------

A form to compose and send (or queue) an email directly from the backend.
Supports HTML body, priority, and scheduled delivery.

Inbox
-----

Browse messages from a configured IMAP/POP3 account. Requires the PHP
``imap`` extension and inbox configuration (see :ref:`configuration`).

PHP API
=======

Queue a mail programmatically
------------------------------

.. code-block:: php

   use Maispace\MaiMail\Service\MailQueueService;
   use TYPO3\CMS\Core\Utility\GeneralUtility;

   $service = GeneralUtility::makeInstance(MailQueueService::class);
   $service->add(
       sender: 'noreply@example.com',
       recipients: ['user@example.com' => 'John Doe'],
       subject: 'Hello',
       body: '<p>Hello World</p>',
       attachments: [],
       priority: 0,
       scheduledAt: null
   );

Use the extended MailMessage
-----------------------------

.. code-block:: php

   use Maispace\MaiMail\Mail\MailMessage;
   use TYPO3\CMS\Core\Utility\GeneralUtility;

   $message = GeneralUtility::makeInstance(MailMessage::class);
   $message
       ->from('noreply@example.com')
       ->to('user@example.com')
       ->subject('Hello')
       ->htmlWithTextFallback('<p>Hello World</p>');

   // Send immediately
   $message->send();

   // OR queue for async delivery
   $message->queue(priority: 5);

PSR-14 Events
=============

Listen to the following events by registering an event listener in
``Configuration/Services.yaml``:

* ``Maispace\MaiMail\Event\MailQueuedEvent`` – fired when a mail is added to the queue.
* ``Maispace\MaiMail\Event\MailSentEvent`` – fired when a mail is sent successfully.
* ``Maispace\MaiMail\Event\MailFailedEvent`` – fired when sending a mail fails.

.. code-block:: yaml

   # Configuration/Services.yaml
   MyVendor\MyExtension\EventListener\MyListener:
     tags:
       - name: event.listener
         identifier: 'my-ext/mail-sent'
         event: Maispace\MaiMail\Event\MailSentEvent
