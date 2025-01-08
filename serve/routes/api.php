<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CaptchaController;
use App\Http\Controllers\Api\CardController;
use App\Http\Controllers\Api\CommissionController;
use App\Http\Controllers\Api\ConfigController;
use App\Http\Controllers\Api\DomainController;
use App\Http\Controllers\Api\IndexController;
use App\Http\Controllers\Api\JumpController;
use App\Http\Controllers\Api\LinkController;
use App\Http\Controllers\Api\MaterialCateController;
use App\Http\Controllers\Api\MaterialController;
use App\Http\Controllers\Api\MiniProgramController;
use App\Http\Controllers\Api\NoticeController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UploadController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\VersionController;
use App\Http\Controllers\Api\VipPackageController;
use App\Http\Controllers\Api\WithdrawController;
use App\Http\Controllers\UpgradeController;
use App\Http\Middleware\ApiAuth;
use App\PayChannels\WeChatPayNative;
use Illuminate\Routing\Router;
use Illuminate\Support\Facades\Route;

Route::get('/config', [IndexController::class, 'config']); // 公开配置
Route::post('/login', [AuthController::class, 'login']); // 登录
Route::post('/register', [AuthController::class, 'register']); // 注册
Route::post('/reset-password', [AuthController::class, 'resetPassword']); // 重置密码
Route::get('/captcha/image', [CaptchaController::class, 'image']); // 登录
Route::post('/captcha/sms', [CaptchaController::class, 'sms']); // 获取短信验证码
Route::post('/wechat/payment_notify', [WeChatPayNative::class, 'notify']); // 微信支付回调

// 获取链接重定向目标
Route::get('/link-target/{code}', [JumpController::class, 'target']); // 获取链接跳转地址
Route::get('/link-show-qr/{code}', [JumpController::class, 'getShowQr']); // 显示的二维码信息

// 登录用户都访问
Route::middleware(ApiAuth::class)->group(function (Router $router) {
    $router->post('/change-password', [AuthController::class, 'changePassword']); // 修改密码
    $router->get('/userinfo', [AuthController::class, 'userInfo']); // 用户信息

    $router->apiResource('materials-cate', MaterialCateController::class)->except('show'); // 素分类
    $router->apiResource('material-upload', UploadController::class)->only(['index', 'store']); // 素材库列表管理(分类)
    $router->post('material-upload-del', [UploadController::class, 'destroy']); // 批量删除
    $router->apiResource('/materials', MaterialController::class)->except('show'); // 素材库(创建链接)

    $router->get('/home', [IndexController::class, 'index']); // 首页
    // 显示最新公告
    $router->get('notice', [NoticeController::class, 'get_notice']);

    $router->get('/domains', [DomainController::class, 'index']); // 域名

    // 小程序管理
    $router->apiResource('/min-program', MiniProgramController::class)->except('show');
    $router->get('/min-programs', [MiniProgramController::class, 'home']); // 创建链接下拉数据源

    // 链接
    $router->apiResource('/links', LinkController::class);
    $router->get('link-list', [LinkController::class, 'link_list']); // 获取下拉数据源

    // 佣金
    $router->get('/commission-logs', [CommissionController::class, 'logs']); // 佣金记录
    $router->post('/apply-withdraw', [CommissionController::class, 'applyWithdraw']); // 申请提现

    $router->get('/agent-invite', [UserController::class, 'invite']); // 邀请记录

    // 购买会员
    $router->post('/buy-vip', [AuthController::class, 'buyVip']);
    // 卡密兑换
    $router->post('/consume-card', [CardController::class, 'consume']);
});

// 仅管理员可访问
Route::middleware(ApiAuth::class.':admin')->group(function (Router $router) {
    // 公告
    $router->apiResource('notices', NoticeController::class)->except('show');
    // 添加/编辑公告
    $router->put('notice', [NoticeController::class, 'set_notice']);

    // 域名
    $router->apiResource('domains', DomainController::class)->except(['index', 'show']);
    // VIP套餐
    $router->apiResource('vip-packages', VipPackageController::class)->except('show');

    // 设置
    $router->get('config/{type}', [ConfigController::class, 'getForm']);
    $router->put('config/{type}', [ConfigController::class, 'saveForm']);
    // 用户
    $router->apiResource('users', UserController::class)->only(['index', 'store', 'update']);
    $router->get('agent-tree', [UserController::class, 'agent_tree']);

    // 提现
    $router->get('withdraws', [WithdrawController::class, 'index']); // 申请记录
    $router->post('withdraws-reject/{id}', [WithdrawController::class, 'reject']); // 拒绝
    $router->post('withdraws-confirm/{id}', [WithdrawController::class, 'confirm']); // 确认打款

    // 卡密
    $router->apiResource('cards', CardController::class)->only(['index', 'store', 'update']);

    $router->get('payments', [PaymentController::class, 'index']);

    $router->get('/commission-logs/{user_id}', [CommissionController::class, 'agent_logs']); // 代理商佣金记录

    $router->apiResource('version', VersionController::class);

});
Route::get('news_version', [VersionController::class, 'version']);
Route::get('upgrade', [UpgradeController::class, 'index']);
