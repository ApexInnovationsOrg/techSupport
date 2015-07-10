<?php

return [

    /*
     * Default options.
     * - 'apiToken' and 'room' are required
     * - All options apart from 'apiToken' can be overridden per message
     */

    'apiToken' => env('HIPCHAT_API'), // required

    'room' => 'Apex IT Maintenance', // room name (not ID), required

    'color' => 'random', // default notification color, optional

    'from' => 'Tech Support', // default sender name, optional

    'queue' => false, // use laravel queue, optional, default true

    'notify' => true, // Hipchat app will notify room of the message

    'format' => 'auto', // message format - 'auto', 'html' or 'text', optional
];