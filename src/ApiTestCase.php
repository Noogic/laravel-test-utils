<?php

namespace Noogic\TestUtils;

use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;
use Illuminate\Foundation\Testing\TestCase;
use Illuminate\Foundation\Testing\TestResponse;
use Tests\CreatesApplication;

abstract class ApiTestCase extends TestCase
{
    use CreatesApplication, DatabaseTransactions;

    const APPLICATION_JSON = 'application/json';

    protected $user;

    public function setUp()
    {
        parent::setUp();

        $this->user = config('test-utils.user')::first();
    }

    public function signIn($user = null)
    {
        $user = $user ?: $this->user;
        $this->be($user, 'api');
    }

    /**
     * @param string $uri
     * @param array $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function get($uri, array $headers = [])
    {
        $headers = $this->setHeaders($headers);

        return $this->getJson($uri, $headers);
    }

    /**
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function post($uri, array $data = [], array $headers = [])
    {
        $headers = $this->setHeaders($headers);

        return $this->postJson($uri, $data, $headers);
    }

    /**
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function put($uri, array $data = [], array $headers = [])
    {
        $headers = $this->setHeaders($headers);

        return $this->putJson($uri, $data, $headers);
    }

    /**
     * @param string $uri
     * @param array $data
     * @param array $headers
     * @return \Illuminate\Foundation\Testing\TestResponse
     */
    public function delete($uri, array $data = [], array $headers = [])
    {
        $headers = $this->setHeaders($headers);

        return $this->deleteJson($uri, $data, $headers);
    }

    /**
     * @param array $headers
     * @return array
     */
    protected function setHeaders(array $headers)
    {
        if(!isset($headers['Accept'])) {
            $headers['Accept'] = self::APPLICATION_JSON;
        }

        if (!isset($headers['Content-Type'])) {
            $headers['Content-Type'] = self::APPLICATION_JSON;
        }

        return $headers;
    }

    protected function dumpResponse(TestResponse $response, $server = true)
    {
        $data = json_decode($response->content(), true);

        if ($server) {
            dump($data);

            return;
        }

        print_r($data);
    }

    /**
     * Boot the testing helper traits.
     *
     * @return array
     */
    protected function setUpTraits()
    {
        $uses = array_flip(class_uses_recursive(static::class));

        if (isset($uses[RefreshDatabase::class])) {
            $this->refreshDatabase();
        }

        if (isset($uses[DatabaseMigrations::class])) {
            $this->runDatabaseMigrations();
        }

        if (config('test-utils.transactions') and isset($uses[DatabaseTransactions::class])) {
            $this->beginDatabaseTransaction();
        }

        if (isset($uses[WithoutMiddleware::class])) {
            $this->disableMiddlewareForAllTests();
        }

        if (isset($uses[WithoutEvents::class])) {
            $this->disableEventsForAllTests();
        }

        if (isset($uses[WithFaker::class])) {
            $this->setUpFaker();
        }

        return $uses;
    }
}
