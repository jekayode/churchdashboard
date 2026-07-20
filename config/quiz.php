<?php

declare(strict_types=1);

return [
    /*
     * Display names go up on the projector in front of the whole church, and
     * guests type them with no account behind them. The filter is deliberately
     * blunt: a name it wrongly rejects costs someone one retype, whereas one it
     * wrongly allows is on the wall in front of everyone.
     */
    'display_name' => [
        'min' => 2,
        'max' => 24,

        // Matched against the name with spacing, punctuation and the usual
        // letter-for-digit substitutions stripped out, so "f u c k" and "sh1t"
        // are caught alongside the plain spellings.
        'blocked' => [
            'fuck', 'shit', 'cunt', 'bitch', 'dick', 'cock', 'pussy', 'wank',
            'bastard', 'arse', 'ass', 'nigger', 'nigga', 'faggot', 'slut',
            'whore', 'rape', 'penis', 'vagina', 'porn', 'sex', 'anal',
            // Names that would let someone impersonate the platform or the church.
            'admin', 'pastor', 'jesus', 'god', 'satan', 'devil',
        ],
    ],

    /*
     * ~100 people in a service, all answering inside the same couple of seconds.
     * That is roughly 50 writes a second, which is unremarkable for the database
     * but worth a ceiling so a scripted client cannot turn it into a flood.
     */
    'rate_limit' => [
        'answers_per_minute' => 120,
        'joins_per_minute' => 20,
    ],
];
