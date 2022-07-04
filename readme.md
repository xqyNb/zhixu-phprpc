### zhixu框架 - php版本rpc
---

> 安装

```sh
composer require zhixu/phprpc:dev-main
```

> 作者
  - Author : Billion  
  - Email : billionzx@qq.com

> 描述
- 高性能的rpc框架，推荐使用 PHP CLI 模式
- 需要配合 rpc serverManager Go语言服务端.

> 优势
- 相比传统的RPC，使用更方便，支持跨语言。与Go语言无缝连接，无需自己实现go的版本。
- 相比框架式RPC，您还需要自己去写对应的实现。
- 相比GRPC，众所周知：gprc安装麻烦、使用麻烦、且存在一些不兼容的情况。

> 功能
1. 轻松方便的实现跨语言和跨框架使用
2. 适合PHP CLI模式下 TCP 高性能, 同时兼容传统Apache模块或php-fpm模式
  - <font color="red">注意: 虽然TCP模式和传统模式均可使用，但2者不可相互调用。</font>




























---
