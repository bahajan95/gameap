<?php

namespace Gameap\Http\Middleware;

use Closure;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Gameap\Exceptions\GdaemonAPI\InvalidApiKeyException;
use Gameap\Models\DedicatedServer;

class GdaemonApiAuth
{
    private $repository;

    public function __construct(\Gameap\Repositories\DedicatedServersRepository $dedicatedServersRepository)
    {
        $this->repository = $dedicatedServersRepository;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $bearerToken = $request->bearerToken();
        
        if (is_null($bearerToken)) {
            throw new HttpException(401, "Bearer token not set", null, ['WWW-Authenticate' => 'Bearer']);
        }

        try {
            $dedicatedServer = DedicatedServer::where('gdaemon_api_key', '=', $bearerToken)->firstOrFail();
        } catch (ModelNotFoundException $exception) {
            throw new InvalidApiKeyException("Invalid api token");
        }

        app()->instance(DedicatedServer::class, $dedicatedServer);
        
        return $next($request);
    }
}