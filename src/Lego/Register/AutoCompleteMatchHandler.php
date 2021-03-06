<?php

namespace Lego\Register;

use Illuminate\Support\Facades\Request;
use Lego\Foundation\Exceptions\InvalidRegisterData;
use Lego\Operator\SuggestResult;

class AutoCompleteMatchHandler extends Data
{
    const KEYWORD_KEY = '__lego_auto_complete';

    /**
     * 校验注册的数据是否合法, 不合法时抛出异常.
     *
     * @param $data
     */
    protected function validate($data)
    {
        InvalidRegisterData::assert($data instanceof \Closure, '$data should be Closure.');
    }

    /**
     * @var HighPriorityResponse
     */
    protected $response;

    public function afterRegistered()
    {
        $this->response = lego_register(
            HighPriorityResponse::class,
            function () {
                $items = call_user_func_array($this->data(), [Request::get(self::KEYWORD_KEY), Request::all()]);

                return self::result($items);
            },
            $this->tag
        );
    }

    public function remote()
    {
        return $this->response->url();
    }

    /**
     * 自动补全结果的构建函数.
     *
     * @param array $items [ ['id' => 1, 'text' => 'Some Text', ...], ... ]
     *
     * @return array
     */
    private static function result($items)
    {
        if ($items instanceof SuggestResult) {
            return $items->toArray();
        }

        $sr = new SuggestResult($items);

        return $sr->toArray();
    }
}
