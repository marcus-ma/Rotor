<?php
function I($value)
{
    // 去除斜杠
    if (get_magic_quotes_gpc())
    {
        $value = stripslashes($value);
    }
    // 如果不是数字则使用反斜线引用字符串
    if (!is_numeric($value))
    {
        $value = "'" . addslashes($value) . "'";
    }
    return $value;
}

/**
 * 返回json
 *
 * @param int $status (业务状态码，默认为0)
 * @param string $msg （提示信息）
 * @param null $data  （数据集）
 */
function _json($status = 0, $msg = '',$data = null)
{
    $res = [
        'status' => $status,
        'msg' => $msg
    ];
    if($data !== null){
        $res['data'] = $data;
    }
    header("Content-Type=application/json;charset=UTF-8 ");
    echo  json_encode($res,JSON_UNESCAPED_UNICODE);
}