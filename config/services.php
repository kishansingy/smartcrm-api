<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'whatsapp' => [
        'api_url'      => env('WHATSAPP_API_URL', 'https://graph.facebook.com/v19.0'),
        'token'        => env('WHATSAPP_API_TOKEN'),
        'phone_id'     => env('WHATSAPP_PHONE_ID'),
        'verify_token' => env('WHATSAPP_VERIFY_TOKEN', 'smartcrm_verify'),
    ],

    'openai' => [
        'api_key' => env('OPENAI_API_KEY'),
        'model'   => env('OPENAI_MODEL', 'gpt-4o-mini'),
    ],

    'gemini' => [
        'api_key' => env('GEMINI_API_KEY'),
    ],

    'ollama' => [
        'url' => env('OLLAMA_URL', 'http://localhost:11434'),
    ],

    'calls' => [
        'default_provider' => env('CALL_DEFAULT_PROVIDER', 'retell'),
    ],

    'retell' => [
        'api_key'      => env('RETELL_API_KEY'),
        'agent_id'     => env('RETELL_AGENT_ID', 'agent_27f9e56effc1ca8aa936daf918'),
        'llm_id'       => env('RETELL_LLM_ID', 'llm_d0925279d1c7afa032ff19d1458d'),
        'from_number'  => env('RETELL_FROM_NUMBER', '+19516310929'),
        'webhook_url'  => env('RETELL_WEBHOOK_URL'),
    ],

    'exotel' => [
        'sid'          => env('EXOTEL_SID'),
        'api_key'      => env('EXOTEL_API_KEY'),
        'api_token'    => env('EXOTEL_API_TOKEN'),
        'from'         => env('EXOTEL_FROM', '04048210430'),
        'app_id'       => env('EXOTEL_APP_ID', '1272812'),
        'subdomain'    => env('EXOTEL_SUBDOMAIN', 'api.exotel.com'),
        'webhook_url'  => env('EXOTEL_WEBHOOK_URL'),
    ],

];
