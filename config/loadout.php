<?php

return [
    'admin_email' => env('LOADOUT_ADMIN_EMAIL', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
    'moderation_link_expiration_days' => env('LOADOUT_MODERATION_LINK_EXPIRATION_DAYS', 7),
];
