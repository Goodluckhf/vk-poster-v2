<?php
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Response;

class TestCase extends Illuminate\Foundation\Testing\TestCase
{
    /**
     * The base URL to use while testing the application.
     *
     * @var string
     */
    protected $baseUrl = 'http://localhost';

    /**
     * Creates the application.
     *
     * @return \Illuminate\Foundation\Application
     */
    public function createApplication()
    {
        $app = require __DIR__.'/../bootstrap/app.php';

        $app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

        return $app;
    }
    
    protected function resetSqlite() {
        file_put_contents( $this->app['db']->connection('sqlite')->getDatabaseName(), '');
    }
    
    protected function makeResponse(int $code = 200, array $headers = [], string $body = '{"ok": "ok"}') {
		$stream = Psr7\stream_for($body);
		return new Response($code, $headers, $stream);
	}
}
