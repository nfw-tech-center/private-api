<?php

return [

    /*
     * 代理配置
     */
    '_'        => [
        // 返回值类型：json，array，object
        'return_type' => 'object',
        // 中间件堆栈（应用于所有自动生成的Private API路由）
        // 'middleware'  => ['auth'],
    ],

    /*
     * API定义示例
     */
    // 'app-name' => [
    //     'app'    => '在管理中心注册的app值',
    //     'ticket' => '在管理中心注册的ticket值',
    //
    //     // APP级别的全局自动缓存，取值示例：forever, 2 minutes, 15 seconds
    //     // 'cache' => 'forever',
    //
    //     'api-name' => [
    //         'route'     => '含有route键的api定义，会自动注册路由到Laravel',
    //         'url'       => '对应的原始接口url',
    //         'has_files' => true, // 指示使用multipart/form-data请求
    //         // 请求参数类型转换（ -> 前后必须留1个空格）
    //         'casts'     => [
    //             'request_params_key1' => 'type1 -> type2',
    //             'request_params_key2' => 'type1 -> type2',
    //         ],
    //         // 请求参数默认值
    //         'defaults'  => [
    //             'request_params_key1' => '默认值',
    //             'request_params_key2' => '默认值',
    //         ],
    //     ],
    // ],

];
