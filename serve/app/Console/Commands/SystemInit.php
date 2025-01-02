<?php

namespace App\Console\Commands;

use App\Enums\UserType;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SystemInit extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:system-init';

    /**
     * The console command desc.
     *
     * @var string
     */
    protected $desc = '系统初始数据';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (DB::table('sys_configs')->count() > 0) {
            echo '重复操作，已存在数据！！';

            return Command::FAILURE;
        }
        // 初始配置
        DB::table('sys_configs')->insert($this->config_data());
        DB::table('users')->insert([
            'username' => 'admin',
            'password' => bcrypt('admin123'),
            'type' => UserType::Admin,
            'referral_code' => '100000',
            'status' => true,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        // 套餐
        DB::unprepared(file_get_contents(database_path('packages.sql')));

        return Command::SUCCESS;
    }

    private function config_data(): array
    {
        return [
            [
                'slug' => 'web_site_title',
                'value' => '蚂蚁快链-智慧引流工具',
                'desc' => '网站名称',
            ],
            [
                'slug' => 'web_site_logo',
                'value' => '/image/toplogo.png',
                'desc' => '网站logo浅色',
            ],
            [
                'slug' => 'web_site_bottom_logo',
                'value' => '/image/toplogo.png',
                'desc' => '网站logo深色',
            ],
            [
                'slug' => 'web_site_customer_service',
                'value' => '/image/web-qr.png',
                'desc' => '客服二维码',
            ],
            [
                'slug' => 'is_give_vip',
                'value' => 1,
                'desc' => '开启注册赠送会员',
            ], [
                'slug' => 'give_vip_days',
                'value' => '7',
                'desc' => '注册赠送会员有效期/天',
            ], [
                'slug' => 'give_vip_id',
                'value' => '',
                'desc' => '赠送套餐',
            ], [
                'slug' => 'recommend_commission',
                'value' => '',
                'desc' => '邀请注册返现',
            ], [
                'slug' => 'member_pay_commission_1',
                'value' => '',
                'desc' => '一级分销消费返现',
            ], [
                'slug' => 'member_pay_commission_2',
                'value' => '',
                'desc' => '二级分销消费返现',
            ], [
                'slug' => 'send_code_mode',
                'value' => '',
                'desc' => '发送验证码类型',
            ],
        ];
    }
}
