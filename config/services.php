'gemini' => [
    'key' => env('GEMINI_API_KEY'),
    'model' => env('GEMINI_MODEL', 'gemini-flash-latest'),
    'base_url' => env(
        'GEMINI_BASE_URL',
        'https://generativelanguage.googleapis.com/v1beta'
    ),
    'timeout' => (int) env('GEMINI_TIMEOUT', 120),
    'retry_times' => (int) env('GEMINI_RETRY_TIMES', 2),
    'retry_sleep_ms' => (int) env('GEMINI_RETRY_SLEEP_MS', 1500),
],

'campuscare' => [
    'url' => env('CAMPUSCARE_URL', 'http://localhost:8001'),
],