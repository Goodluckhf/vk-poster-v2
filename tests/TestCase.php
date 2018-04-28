<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use GuzzleHttp\Psr7;
use GuzzleHttp\Psr7\Response;

abstract class TestCase extends BaseTestCase
{
    use CreatesApplication;
    
    protected function makeResponse(int $code = 200, array $headers = [], string $body = '{"ok": "ok"}') {
		$stream = Psr7\stream_for($body);
		return new Response($code, $headers, $stream);
	}
}
