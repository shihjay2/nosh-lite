<?php

namespace App\Http\Middleware;

use Fideloper\Proxy\TrustProxies as Middleware;
use Illuminate\Http\Request;

class TrustProxies extends Middleware
{
    /**
     * The trusted proxies for this application.
     *
     * @var array|string
     */
    protected $proxies;

    /**
     * The headers that should be used to detect proxies.
     *
     * @var int
     */
    protected $headers = Request::HEADER_X_FORWARDED_ALL;

    public function handle(Request $request, \Closure $next) {
        if (env('TRUSTED_PROXIES') == null) {
            return $next($request);
        }

        return parent::handle($request, $next);
    }

    public function __construct()
    {
        if (env('TRUSTED_PROXIES') == '*') {
            $this->proxies = '*';
        } else {
            $this->proxies = explode(',', env('TRUSTED_PROXIES'));
        }
    }
}
