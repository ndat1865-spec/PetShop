<?php

use Illuminate\Support\Env;

// Apache on Windows shares the process environment across request threads.
// Keep dotenv values in request-local $_ENV/$_SERVER so one request ending
// cannot remove another request's APP_KEY. CLI workers keep their usual setup.
Env::disablePutenv();

return require __DIR__.'/app.php';
