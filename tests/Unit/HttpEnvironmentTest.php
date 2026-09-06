<?php

namespace Tests\Unit;

use Illuminate\Contracts\Http\Kernel;
use Illuminate\Foundation\Bootstrap\LoadConfiguration;
use Illuminate\Support\Env;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use PHPUnit\Framework\TestCase;

class HttpEnvironmentTest extends TestCase
{
    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function test_http_request_keeps_its_key_when_another_request_clears_the_process_environment(): void
    {
        $directory = sys_get_temp_dir().'/pawora-http-env-'.bin2hex(random_bytes(8));
        mkdir($directory);
        $key = 'base64:'.base64_encode(str_repeat('f', 32));
        file_put_contents($directory.'/.env', "APP_ENV=testing\nAPP_KEY={$key}\n");

        try {
            // A concurrent Apache request has populated process-wide values,
            // but this request has not loaded its own environment yet.
            unset($_ENV['APP_KEY'], $_SERVER['APP_KEY'], $_ENV['APP_ENV'], $_SERVER['APP_ENV']);
            putenv('APP_KEY='.$key);
            putenv('APP_ENV=testing');
            Env::enablePutenv();

            $app = require dirname(__DIR__, 2).'/bootstrap/http.php';
            $app->useEnvironmentPath($directory);
            $app->beforeBootstrapping(LoadConfiguration::class, function () {
                // The other request ends after dotenv loads, before config reads.
                putenv('APP_KEY');
                putenv('APP_ENV');
            });
            $app->make(Kernel::class)->bootstrap();

            $encrypter = $app->make('encrypter');
            $this->assertSame('profile-session', $encrypter->decryptString($encrypter->encryptString('profile-session')));
            $this->assertSame($key, $app['config']['app.key']);
            $this->assertSame('testing', $app['config']['app.env']);
        } finally {
            putenv('APP_KEY');
            putenv('APP_ENV');
            unlink($directory.'/.env');
            rmdir($directory);
        }
    }
}
