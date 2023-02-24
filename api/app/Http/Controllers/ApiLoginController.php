<?php

namespace App\Http\Controllers;

use App\Definition\ReturnCode;
use App\Helper\Tool;
use App\Models\Term;
use App\Services\User\UserService;
use Tymon\JWTAuth\Facades\JWTAuth;
use Tymon\JWTAuth\Exceptions\JWTException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class ApiLoginController extends Controller
{
    public function __construct()
    {
        // 这里额外注意了：官方文档样例中只除外了『login』
        // 这样的结果是，token 只能在有效期以内进行刷新，过期无法刷新
        // 如果把 refresh 也放进去，token 即使过期但仍在刷新期以内也可刷新
        // 不过刷新一次作废
        $this->middleware('jwt.auth', ['except' => ['login', 'refresh']]);
    }

    /**
     * @param Request $request
     */
    public function login(Request $request)
    {
        $credentials = request(['nickname', 'password']);

        if (!$token = JWTAuth::attempt($credentials)) {
            $this->responseApi(
                ReturnCode::ERROR_SIGN,
                '账号或密码错误',
                [],
                true
            );
        }


        $currentUser = JWTAuth::user()['original'];
        if ($currentUser['is_delete']) {
            $this->responseApi(
                ReturnCode::ERROR_USER_PERMISSION,
                '正在审核你的账号，请联系管理员！',
                [],
                true
            );
        }

        $this->responseApi(
            ReturnCode::SUCCEED,
            '',
            $this->respondWithToken($token)
        );
    }


    protected function respondWithToken($token): array
    {
        return [
            'access_token' => $token,
            'token_type'   => 'bearer',
            'expires_in'   => auth('api')->factory()->getTTL() * 60
        ];
    }

    public function getUserInfo()
    {
        $userInfo = UserService::getIns()->getSelfInfo();
        // base item
        $baseItem = ['id', 'nickname', 'power', 'code', 'email', 'tel', 'co'];
        $user = [];
        foreach ($baseItem as $item) {
            $user[$item] = Tool::get($userInfo, $item);
        }

        $term = Term::getIns()->getMaxId('event_date');
        if ($term == 0) {
            Term::getIns()->addTerm();
            $term = Term::getIns()->getMaxId('event_date');
        }

        $this->responseApi(
            ReturnCode::SUCCEED,
            '',
            compact('user', 'term')
        );
    }

    public function logout()
    {
        JWTAuth::parseToken()->invalidate();
        $this->responseApi(
            ReturnCode::SUCCEED
        );
    }

    public function refresh()
    {
        $data = [];
        try {
            $data = $this->respondWithToken(JWTAuth::refresh(JWTAuth::getToken()));
            $code = ReturnCode::SUCCEED;
        } catch (\Exception $e) {
            $code = ReturnCode::ERROR_LOGIN_REQUIRED;
        }
        $this->responseApi(
            $code,
            '',
            $data
        );
    }

    /**
     * 修改密码保存
     * @param Request $request
     */
    public function savePass(Request $request)
    {
        $oldpassword = $request->input('old');
        $newpassword = $request->input('new');
        $userInfo = UserService::getIns()->getSelfInfo();
        // 验证旧密码
        if (!Hash::check($oldpassword, $userInfo['password'])) {
            $this->responseApi(ReturnCode::ERROR_PARAMS, '原密码错误');
            dd();
        }

        $update = [
            'password' => bcrypt($newpassword),
        ];
        $result = Admin_Manager::where('id', $userInfo['id'])->update($update);
        $this->responseApi(
            $result ? ReturnCode::SUCCEED : ReturnCode::ERROR_PARAMS,
            '修改失败'
        );
    }
}
