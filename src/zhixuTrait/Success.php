<?php

namespace Zhixu\Phprpc\zhixuTrait;

/**
 * 用于判断成功活失败
 */
trait Success {

    private $success = false;
    private $failMessage = '';

    // 设置是否成功
    public function setSuccess(): self {
        $this->success = true;
        return $this;
    }

    // 获取是否成功
    public function getSuccess(): bool {
        return $this->success;
    }

    // 设置错误信息
    public function setFail(string $msg): self {
        $this->success = false;
        $this->failMessage = $msg;
        return $this;
    }

    // 获取错误信息
    public function getFailMessage(): string {
        return $this->failMessage;
    }

}