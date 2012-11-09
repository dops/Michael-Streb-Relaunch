<?php

/**
 * This file defines all messages for the accounting application. All messages are stored in constants. The constant names depend on the type of the message. It always starts with
 * "MESSAGE_", and is followed by a number. Depending on the number the message is a success, error or info message.
 *
 * 1000 - 1999 = success
 * 2000 - 2999 = error
 * 3000 - 3999 = info
 *
 * Be aware keep the order, to not define a message name twice!
 */

// References
define('MESSAGE_1000', 'Die Referenz wurde gespeichert.');

// References
define('MESSAGE_2000', 'Die hochgeladene Grafik hat einen ungültigen Typ. Nur die folgenden Typen sind erlaubt: ::ALLOWED_TYPES::');