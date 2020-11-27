<?php

use Illuminate\Auth\AuthenticationException;
use Illuminate\Http\Request;
use Laravel\Passport\Exceptions\MissingScopeException;
use PHPUnit\Framework\TestCase;
use Laravel\Passport\Http\Middleware\CheckClientCredentialsForAnyScope;

class CheckClientCredentialsForAnyScopeTest extends TestCase
{
    protected function tearDown(): void
    {
        Mockery::close();
    }

    public function test_request_is_passed_along_if_token_is_valid()
    {
        $resourceServer = Mockery::mock('League\OAuth2\Server\ResourceServer');
        $resourceServer->shouldReceive('validateAuthenticatedRequest')->andReturn($psr = Mockery::mock());
        $psr->shouldReceive('getAttribute')->with('oauth_user_id')->andReturn(1);
        $psr->shouldReceive('getAttribute')->with('oauth_client_id')->andReturn(1);
        $psr->shouldReceive('getAttribute')->with('oauth_access_token_id')->andReturn('token');
        $psr->shouldReceive('getAttribute')->with('oauth_scopes')->andReturn(['*']);

        $middleware = new CheckClientCredentialsForAnyScope($resourceServer);

        $request = Request::create('/');
        $request->headers->set('Authorization', 'Bearer token');

        $response = $middleware->handle($request, function () {
            return 'response';
        });

        $this->assertEquals('response', $response);
    }

    public function test_request_is_passed_along_if_token_has_any_required_scope()
    {
        $resourceServer = Mockery::mock('League\OAuth2\Server\ResourceServer');
        $resourceServer->shouldReceive('validateAuthenticatedRequest')->andReturn($psr = Mockery::mock());
        $psr->shouldReceive('getAttribute')->with('oauth_user_id')->andReturn(1);
        $psr->shouldReceive('getAttribute')->with('oauth_client_id')->andReturn(1);
        $psr->shouldReceive('getAttribute')->with('oauth_access_token_id')->andReturn('token');
        $psr->shouldReceive('getAttribute')->with('oauth_scopes')->andReturn(['foo', 'bar', 'baz']);

        $middleware = new CheckClientCredentialsForAnyScope($resourceServer);

        $request = Request::create('/');
        $request->headers->set('Authorization', 'Bearer token');

        $response = $middleware->handle($request, function () {
            return 'response';
        }, 'notfoo', 'bar', 'notbaz');

        $this->assertEquals('response', $response);
    }

    public function test_exception_is_thrown_when_oauth_throws_exception()
    {
        $this->expectException(AuthenticationException::class);
        $resourceServer = Mockery::mock('League\OAuth2\Server\ResourceServer');
        $resourceServer->shouldReceive('validateAuthenticatedRequest')->andThrow(
            new League\OAuth2\Server\Exception\OAuthServerException('message', 500, 'error type')
        );

        $middleware = new CheckClientCredentialsForAnyScope($resourceServer);

        $request = Request::create('/');
        $request->headers->set('Authorization', 'Bearer token');

        $middleware->handle($request, function () {
            return 'response';
        });
    }

    public function test_exception_is_thrown_if_token_does_not_have_required_scope()
    {
        $this->expectException(MissingScopeException::class);
        $resourceServer = Mockery::mock('League\OAuth2\Server\ResourceServer');
        $resourceServer->shouldReceive('validateAuthenticatedRequest')->andReturn($psr = Mockery::mock());
        $psr->shouldReceive('getAttribute')->with('oauth_user_id')->andReturn(1);
        $psr->shouldReceive('getAttribute')->with('oauth_client_id')->andReturn(1);
        $psr->shouldReceive('getAttribute')->with('oauth_access_token_id')->andReturn('token');
        $psr->shouldReceive('getAttribute')->with('oauth_scopes')->andReturn(['foo', 'bar']);

        $middleware = new CheckClientCredentialsForAnyScope($resourceServer);

        $request = Request::create('/');
        $request->headers->set('Authorization', 'Bearer token');

        $response = $middleware->handle($request, function () {
            return 'response';
        }, 'baz', 'notbar');
    }
}
