<?php

namespace Kaylyu\Alipay\F2fpay\Notify;

use Closure;
use Exception;
use Kaylyu\Alipay\Kernel\Support\Arr;
use Kaylyu\Alipay\Kernel\Support\XML;

abstract class Handler
{
    const SUCCESS = 1000;

    /**
     * @var
     * @author kaylv <kaylv@dayuw.com>
     */
    protected $app;

    /**
     * @var array
     */
    protected $message;

    /**
     * @var string|null
     */
    protected $fail;

    /**
     * @var array
     */
    protected $attributes = [];

    /**
     * Check sign.
     * If failed, throws an exception.
     *
     * @var bool
     */
    protected $check = true;

    /**
     * Respond with sign.
     *
     * @var bool
     */
    protected $sign = true;

    /**
     * Handler constructor.
     * @param $app
     */
    public function __construct($app)
    {
        $this->app = $app;
    }

    /**
     * 处理验签入口
     * @param Closure $closure
     * @author kaylv <kaylv@dayuw.com>
     * @return string
     */
    abstract public function handle(Closure $closure);

    /**
     * 获取请求参数的KEY
     * @author kaylv <kaylv@dayuw.com>
     * @return string
     */
    abstract public function getRequestKey();

    /**
     * @param string $message
     */
    public function fail(string $message)
    {
        $this->fail = $message;
    }

    /**
     * 响应数据
     * @author kaylv <kaylv@dayuw.com>
     * @return string
     */
    public function toResponse()
    {
        return XML::buildResponse([
            'state' => [
                'code' => 1000,
                'message' => '接收成功',
            ]
        ]);
    }

    /**
     * 获取参数并验签
     * @author kaylv <kaylv@dayuw.com>
     * @return array
     * @throws Exception
     */
    public function getMessage(): array
    {
        if (!empty($this->message)) {
            return $this->message;
        }

        //获取参数
        $message = $this->getInput();

        if (empty($message)) {
            throw new Exception('Invalid Request.', 400);
        }

        if (!Arr::get($message, 'header.signed')) {
            throw new Exception('缺少Sign参数.', 400);
        }

        if ($this->check) {
            $this->validate($message);
        }

        return $this->message = $message;
    }

    /**
     * 验签
     * @param array $message
     * @author kaylv <kaylv@dayuw.com>
     * @throws Exception
     */
    protected function validate(array $message)
    {
        throw new Exception('Invalid AopClient.', 400);
    }

    /**
     * 处理入口
     * @param mixed $result
     */
    protected function strict($result)
    {
        if (true !== $result && is_null($this->fail)) {
            $this->fail(strval($result));
        }
    }

    /**
     * 获取参数
     * @author kaylv <kaylv@dayuw.com>
     * @return array
     */
    protected function getInput()
    {
        //获取请求参数
        $content = $this->app['request']->input($this->getRequestKey());

        return XML::parse($content, ['product', 'credential']) ?: [];
    }
}
