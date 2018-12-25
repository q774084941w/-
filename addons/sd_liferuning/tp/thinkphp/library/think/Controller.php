<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2017 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace think;

\think\Loader::import('controller/Jump', TRAIT_PATH, EXT);

use think\exception\ValidateException;
use think\db;

class Controller
{

    /**
     * @var string 返回结果
     */
    protected $result = '';
    /**
     * @var int 错误1级标志
     */
    protected $msg = 'ok';
    /**
     * @var int 错误标志
     */
    protected $code = 0;
    /**
     * @var null 返回数据
     */
    protected $data = '';
    
    use \traits\controller\Jump;

    /**
     * @var int 用户id
     */
    protected $uid = 0;
    protected $bid = 0;

    /**
     * @var \think\View 视图类实例
     */
    protected $view;
    /**
     * @var \think\Request Request实例
     */
    protected $request;
    // 验证失败是否抛出异常
    protected $failException = false;
    // 是否批量验证
    protected $batchValidate = false;

    /**
     * 前置操作方法列表
     * @var array $beforeActionList
     * @access protected
     */
    protected $beforeActionList = [];

    /**
     * 构造方法
     * @param Request $request Request对象
     * @access public
     */
    public function __construct(Request $request = null)
    {
        //获取用户uid
        $this->uid = intval($this->_getUid($request));
        $this->bid = intval($this->_getBid());
        
        
        if (is_null($request)) {
            $request = Request::instance();
        }
        $this->view    = View::instance(Config::get('template'), Config::get('view_replace_str'));
        $this->request = $request;
        $blogo = db('business')->where(['bid' => $this->bid])->find();
        if($blogo){
            $blogo['logo'] = uploadpath('business',$blogo['logo']);
        }


        $this->assign('blogo',$blogo);
        // 控制器初始化
        $this->_initialize();

        // 前置操作方法
        if ($this->beforeActionList) {
            foreach ($this->beforeActionList as $method => $options) {
                is_numeric($method) ?
                $this->beforeAction($options) :
                $this->beforeAction($method, $options);
            }
        }
    }

    // 初始化
    protected function _initialize()
    {
    }

    /**
     * 前置操作
     * @access protected
     * @param string $method  前置操作方法名
     * @param array  $options 调用参数 ['only'=>[...]] 或者['except'=>[...]]
     */
    protected function beforeAction($method, $options = [])
    {
        if (isset($options['only'])) {
            if (is_string($options['only'])) {
                $options['only'] = explode(',', $options['only']);
            }
            if (!in_array($this->request->action(), $options['only'])) {
                return;
            }
        } elseif (isset($options['except'])) {
            if (is_string($options['except'])) {
                $options['except'] = explode(',', $options['except']);
            }
            if (in_array($this->request->action(), $options['except'])) {
                return;
            }
        }

        call_user_func([$this, $method]);
    }

    /**
     * 加载模板输出
     * @access protected
     * @param string $template 模板文件名
     * @param array  $vars     模板输出变量
     * @param array  $replace  模板替换
     * @param array  $config   模板参数
     * @return mixed
     */
    protected function fetch($template = '', $vars = [], $replace = [], $config = [])
    {
        return $this->view->fetch($template, $vars, $replace, $config);
    }

    /**
     * 渲染内容输出
     * @access protected
     * @param string $content 模板内容
     * @param array  $vars    模板输出变量
     * @param array  $replace 替换内容
     * @param array  $config  模板参数
     * @return mixed
     */
    protected function display($content = '', $vars = [], $replace = [], $config = [])
    {
        return $this->view->display($content, $vars, $replace, $config);
    }

    /**
     * 模板变量赋值
     * @access protected
     * @param mixed $name  要显示的模板变量
     * @param mixed $value 变量的值
     * @return void
     */
    protected function assign($name, $value = '')
    {
        $this->view->assign($name, $value);
    }

    /**
     * 初始化模板引擎
     * @access protected
     * @param array|string $engine 引擎参数
     * @return void
     */
    protected function engine($engine)
    {
        $this->view->engine($engine);
    }

    /**
     * 设置验证失败后是否抛出异常
     * @access protected
     * @param bool $fail 是否抛出异常
     * @return $this
     */
    protected function validateFailException($fail = true)
    {
        $this->failException = $fail;
        return $this;
    }

    /**
     * 验证数据
     * @access protected
     * @param array        $data     数据
     * @param string|array $validate 验证器名或者验证规则数组
     * @param array        $message  提示信息
     * @param bool         $batch    是否批量验证
     * @param mixed        $callback 回调方法（闭包）
     * @return array|string|true
     * @throws ValidateException
     */
    protected function validate($data, $validate, $message = [], $batch = false, $callback = null)
    {
        if (is_array($validate)) {
            $v = Loader::validate();
            $v->rule($validate);
        } else {
            if (strpos($validate, '.')) {
                // 支持场景
                list($validate, $scene) = explode('.', $validate);
            }
            $v = Loader::validate($validate);
            if (!empty($scene)) {
                $v->scene($scene);
            }
        }
        // 是否批量验证
        if ($batch || $this->batchValidate) {
            $v->batch(true);
        }

        if (is_array($message)) {
            $v->message($message);
        }

        if ($callback && is_callable($callback)) {
            call_user_func_array($callback, [$v, &$data]);
        }

        if (!$v->check($data)) {
            if ($this->failException) {
                throw new ValidateException($v->getError());
            } else {
                return $v->getError();
            }
        } else {
            return true;
        }
    }
    /**
     * 输出json返回值
     * @access $result
     */
    public function jsonOut($result)
    {
        if (is_array($result) && isset($result['errorCode'])) {
            $this->outPut('', $result['errorCode']);
        } else {
            $this->outPut($result, 1);
        }
    }
    /**
     * 输出函数
     *
     * @param array $result 输出数据
     * @param int   $code   错误code
     */
    public function outPut($result = null, $code ,$msg='')
    {
        $this->setData($result);
        $this->getErrMsg($code ,$msg);
        $this->setResult();
        $ret = json_encode($this->result);
        echo $ret;
        exit;
    }
    /**
     * 错误代码
     *
     */
    public function getErrMsg($code, $msg = '')
    {
        $this->code = $code;
        Loader::import('org.errorInfo');
        $error = new \org\errorInfo();
        $this->msg = $error->getErrMsg($code) . $msg;
    }

    /**
     * 设置输出结果
     *
     */
    private function setResult()
    {
        $this->result = array(
            'code'    => $this->code,
            'msg'     => $this->msg,
            'data'    => $this->data
        );
    }
    /**
     * 设置数据
     *
     */
    private function setData($data)
    {
        $this->data = $this->_checkData($data);
    }

    /**
     * 检测字段值是否为NULL，如果为NULL将其转换为''
     *
     * @param  string | array ：需要被检测的数据
     *
     * @return string | array ：处理过后的数据
     */
    protected function _checkData($data)
    {
        if (is_array($data)) {
            foreach ($data as &$val) {
                if (is_array($val)) {
                    $val = $this->_checkData($val);
                } else {
                    $val = isset($val) ? $val : '';
                }
            }
        } else {
           
            $data = !empty($data) ? $data : (object)null;
        }
        return $data;
    }
    /**
     * 获取用户uid
     */
    public function _getUid(Request $request){
        $uid = $request->param('uid');
        return $uid;
    }
    /**
     * 商家
     */
    public function _getBid(){
        $bid = Session::get('bus_bid');
        if($bid){
            return $bid;
        }else{
            return '';
        }
        
    }

    /**
     * 验证是否POST方式提交
     *
     */
    protected function _isPOST()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'POST') {
            $this->outPut('', 101);
        }
    }

    /**
     * 验证是否GET方式提交
     *
     */
    protected function _isGET()
    {
        if ($_SERVER['REQUEST_METHOD'] != 'GET') {
            $this->outPut('', 101);
        }
    }

 
}
