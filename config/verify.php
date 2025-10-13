<?php

return [
    'email_verify_expiration' => intval(env('EMAIL_VERIFICATION_EXPIRATION', 10)),
    'phone_verify_expiration' => intval(env('PHONE_VERIFICATION_EXPIRATION', 10)),
];
