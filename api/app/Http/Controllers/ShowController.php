<?php
/**
 * Created by PhpStorm.
 * User: xubin
 * Date: 12/24/21
 * Time: 8:03 PM
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class ShowController extends Controller
{
    public function index(Request $request)
    {
        $url = $request->url();
        if (strpos($url, 'api/middle') !== false) {
            return view('error.notFind');
        }
        return view('index');
    }
}
