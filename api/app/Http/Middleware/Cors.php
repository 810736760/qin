<?php
/**
 * Created by PhpStorm.
 * User: xubin
 * Date: 9/24/21
 * Time: 11:10 AM
 */

namespace App\Http\Middleware;

use Closure;

class Cors
{
    /**
     * Handle an incoming request.
     *
     * @param \Illuminate\Http\Request $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        $response = $next($request);
        // // $response->header('Access-Control-Allow-Methods', 'HEAD, GET, POST, PUT, PATCH, DELETE,OPTIONS');
        // //         // $response->header('Access-Control-Allow-Headers', $request->header(
        // //         //     'Access-Control-Request-Headers',
        // //         //     'Content-Type,XFILENAME,XFILECATEGORY,XFILESIZE'
        // //         // ));
        // $response->header('Access-Control-Allow-Origin', '*');
        // $response->header('Access-Control-Allow-Headers', 'Origin, X-Requested-With, Content-Type, Accept');
        // dump($request->all());
        dump($_SERVER);
        header("Access-Control-Allow-Origin: *");
        header('Access-Control-Allow-Credentials: true');
        header('Access-Control-Request-Method: GET,POST,PUT,OPTIONS');
        header("Access-Control-Allow-Headers:  content-type");
        // if (isset($_SERVER['HTTP_ACCESS_CONTROL_REQUEST_HEADERS'])) {
        //
        // }
        //
        // if ($_SERVER['REQUEST_METHOD'] == 'OPTIONS') {
        //     exit;
        // }
        return $response;
    }
}
