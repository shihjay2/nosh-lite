<?php

namespace App\Http\Middleware;

use Artisan;
use Closure;
use DB;
use Symfony\Component\Process\Process;
use Response;
use Schema;
use Session;
use URL;

class CheckInstall
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        // Check Database if empty
        $connect = mysqli_connect(env('DB_HOST'), env('DB_USERNAME'), env('DB_PASSWORD'));
        if ($connect) {
            if (!mysqli_select_db($connect, env('DB_DATABASE'))) {
                // return 'No db exist of that name!';
                return redirect()->route('load_data');
            } else {
                if (!Schema::hasTable('demographics')) {
                    return redirect()->route('load_data');
                }
            }
        }
        if (!Session::has('pid')) {
            $dirs = array_filter(glob(env('DOCUMENTS_DIR') . '/*'), 'is_dir');
            $pid = str_replace(env('DOCUMENTS_DIR') . '/', '', $dirs[0]);
            Session::put('pid', $pid);
        }
        return $next($request);
    }
}
