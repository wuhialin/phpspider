<?php

/**
 * phpspider - A PHP Framework For Crawler
 *
 * @package  phpspider
 * @author   Seatle Yang <seatle@foxmail.com>
 */

class phpspider
{
    /**
     * 版本号
     * @var string
     */
    const VERSION = '1.1.0';

    /**
     * 爬虫爬取每个网页的时间间隔,0表示不延时，单位：秒
     */
    const INTERVAL = 0;

    /**
     * 爬虫爬取每个网页的超时时间，单位：秒 
     */
    const TIMEOUT = 5;

    /**
     * 爬取失败次数，不想失败重新爬取则设置为0 
     */
    const COLLECT_FAILS = 0;

    /**
     * 抽取规则的类型：xpath、jsonpath、regex 
     */
    const FIELDS_SELECTOR_TYPE = 'xpath';

    /**
     * 爬虫爬取网页所使用的浏览器类型：android，ios，pc，mobile
     */
    const AGENT_ANDROID = "Mozilla/5.0 (Linux; U; Android 6.0.1;zh_cn; Le X820 Build/FEXCNFN5801507014S) AppleWebKit/537.36 (KHTML, like Gecko)Version/4.0 Chrome/49.0.0.0 Mobile Safari/537.36 EUI Browser/5.8.015S";
    const AGENT_IOS = "Mozilla/5.0 (iPhone; CPU iPhone OS 9_3_3 like Mac OS X) AppleWebKit/601.1.46 (KHTML, like Gecko) Version/9.0 Mobile/13G34 Safari/601.1";
    const AGENT_PC = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36";
    const AGENT_MOBILE = "Mozilla/5.0 (Macintosh; Intel Mac OS X 10_11_6) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/52.0.2743.116 Safari/537.36";

    /**
     * HTTP请求的Header 
     */
    public static $headers = array();

    /**
     * HTTP请求的Cookie 
     */
    public static $cookies = array();

    /**
     * HTTP请求的Cookie，匹配domain 
     */
    public static $domain_cookies = array();

    /**
     * 试运行
     * 试运行状态下，程序持续三分钟或抓取到30条数据后停止
     */
    public static $test_run = true;

    /**
     * 配置 
     */
    public static $configs = array();

    /**
     * 要抓取的URL队列 
     md5(url) => array(
         'url'          => '',      // 要爬取的URL
         'url_type'     => '',      // 要爬取的URL类型,scan_page、list_page、content_page
         'method'       => 'get',   // 默认为"GET"请求, 也支持"POST"请求
         'headers'      => array(), // 此url的Headers, 可以为空
         'data'         => array(), // 发送请求时需添加的参数, 可以为空
         'context_data' => '',      // 此url附加的数据, 可以为空
         'proxy'        => false,   // 是否使用代理
         'proxy_auth'   => '',      // 代理验证: {$USER}:{$PASS}
         'collect_count'=> 0        // 抓取次数
         'collect_fails'=> 0        // 允许抓取失败次数
     ) 
     */
    public static $collect_queue = array();

    /**
     * 要抓取的URL数组
     * md5($url) => time()
     */
    public static $collect_urls = array();

    /**
     * 已经抓取过的URL数组
     * md5($url) => time()
     */
    public static $collected_urls = array();

    /**
     * 爬虫开始时间 
     */
    public static $spider_time_start = 0;
    
    /**
     * 提取到的字段数 
     */
    public static $fields_num = 0;

    public static $export_type = '';
    public static $export_file = '';
    public static $export_conf = '';
    public static $export_table = '';

    /**
     * 爬虫初始化时调用, 用来指定一些爬取前的操作 
     * 
     * @var mixed
     * @access public
     */
    public $on_start = null;

    /**
     * 切换IP代理后，先前请求网页用到的Cookie会被清除，这里可以再次添加 
     * 
     * @var mixed
     * @access public
     */
    public $on_change_proxy = null;

    /**
     * 判断当前网页是否被反爬虫，需要开发者实现 
     * 
     * @var mixed
     * @access public
     */
    public $is_anti_spider = null;

    /**
     * 在一个网页下载完成之后调用，主要用来对下载的网页进行处理 
     * 
     * @var mixed
     * @access public
     */
    public $on_download_page = null;

    /**
     * URL属于入口页 
     * 在爬取到入口url的内容之后, 添加新的url到待爬队列之前调用 
     * 主要用来发现新的待爬url, 并且能给新发现的url附加数据
     * 
     * @var mixed
     * @access public
     */
    public $on_scan_page = null;
    
    /**
     * URL属于列表页
     * 在爬取到列表页url的内容之后, 添加新的url到待爬队列之前调用 
     * 主要用来发现新的待爬url, 并且能给新发现的url附加数据
     * 
     * @var mixed
     * @access public
     */
    public $on_list_page = null;

    /**
     * URL属于内容页 
     * 在爬取到内容页url的内容之后, 添加新的url到待爬队列之前调用 
     * 主要用来发现新的待爬url, 并且能给新发现的url附加数据
     * 
     * @var mixed
     * @access public
     */
    public $on_content_page = null;

    /**
     * 在抽取到field内容之后调用, 对其中包含的img标签进行回调处理 
     * 
     * @var mixed
     * @access public
     */
    public $on_handle_img = null;

    /**
     * 当一个field的内容被抽取到后进行的回调, 在此回调中可以对网页中抽取的内容作进一步处理 
     * 
     * @var mixed
     * @access public
     */
    public $on_extract_field = null;

    /**
     * 在一个网页的所有field抽取完成之后, 可能需要对field进一步处理, 以发布到自己的网站 
     * 
     * @var mixed
     * @access public
     */
    public $on_extract_page = null;

    /**
     * 如果抓取的页面是一个附件文件，比如图片、视频、二进制文件、apk、ipad、exe 
     * 就不去分析他的内容提取field了，提取field只针对HTML
     * 
     * @var mixed
     * @access public
     */
    public $on_attachment_file = null;

    function __construct($configs = array())
    {
        //$files = debug_backtrace();
        //$prev_file = $files[0]['file'];
        $included_files = get_included_files();
        $content = file_get_contents($included_files[0]);
        if (!preg_match("#/\* Do NOT delete this comment \*/#", $content) || !preg_match("#/\* 不要删除这段注释 \*/#", $content))
        {
            $this->log("未知错误；请参考文档或寻求技术支持。", 'fail');
            exit;
        }

        self::$configs = $configs;
        self::$configs['name']          = isset(self::$configs['name'])          ? self::$configs['name']          : 'phpspider';
        self::$configs['proxy']         = isset(self::$configs['proxy'])         ? self::$configs['proxy']         : '';
        self::$configs['proxy_auth']    = isset(self::$configs['proxy_auth'])    ? self::$configs['proxy_auth']    : '';
        self::$configs['user_agent']    = isset(self::$configs['user_agent'])    ? self::$configs['user_agent']    : self::AGENT_PC;
        self::$configs['interval']      = isset(self::$configs['interval'])      ? self::$configs['interval']      : self::INTERVAL;
        self::$configs['timeout']       = isset(self::$configs['timeout'])       ? self::$configs['timeout']       : self::TIMEOUT;
        self::$configs['collect_fails'] = isset(self::$configs['collect_fails']) ? self::$configs['collect_fails'] : self::COLLECT_FAILS;
        self::$configs['export']        = isset(self::$configs['export'])        ? self::$configs['export']        : array();
    }

    public function add_useragent($useragent)
    {
        cls_curl::set_useragent($useragent);
    }

    /**
     * 一般在 on_start 回调函数中调用，用来添加一些HTTP请求的Header
     * 
     * @param mixed $url
     * @param mixed $options
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function add_header($key, $value)
    {
        self::$headers[$key] = $value;
    }

    /**
     * 一般在 on_start 回调函数中调用，用来得到某个域名所附带的某个Cookie
     * 
     * @param mixed $name
     * @param mixed $domain
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function get_cookie($name, $domain = '')
    {
        $cookies = empty($domain) ? self::$cookies : self::$domain_cookies[$domain];
        return isset($cookies[$name]) ? $cookies[$name] : '';
    }
    
    public function get_cookies($domain = '')
    {
        return empty($domain) ? self::$cookies : self::$domain_cookies[$domain];
    }

    /**
     * 一般在on_start回调函数中调用，用来添加一些HTTP请求的Cookie
     * 
     * @param mixed $cookies
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function add_cookie($key, $value, $domain = '/')
    {
        self::$cookies[$key] = $value;
    }

    /**
     * 一般在on_start回调函数中调用，用来添加一些HTTP请求的Cookie
     * 
     * @param mixed $cookies
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function add_cookies($cookies)
    {
        $cookies_arr = explode(";", $cookies);
        foreach ($cookies_arr as $cookie) 
        {
            $cookie_arr = explode("=", $cookie);
            self::$cookies[trim($cookie_arr[0])] = trim($cookie_arr[1]);
        }
    }

    /**
     * 一般在 on_scan_page 和 on_list_page 回调函数中调用，用来往待爬队列中添加url
     * 
     * @param mixed $url
     * @param mixed $options
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function add_url($url, $options = array())
    {
        // 投递状态
        $status = false;
        $link = array(
            'url'           => $url,            
            'url_type'      => '', 
            'method'        => isset($options['method'])        ? $options['method']        : 'get',             
            'fields'        => isset($options['fields'])        ? $options['fields']        : array(),           
            'headers'       => isset($options['headers'])       ? $options['headers']       : self::$headers,    
            'context_data'  => isset($options['context_data'])  ? $options['context_data']  : '',                
            'proxy'         => isset($options['proxy'])         ? $options['proxy']         : self::$configs['proxy'],             
            'proxy_auth'    => isset($options['proxy_auth'])    ? $options['proxy_auth']    : self::$configs['proxy_auth'],             
            'collect_count' => isset($options['collect_count']) ? $options['collect_count'] : 0,                 
            'collect_fails' => isset($options['collect_fails']) ? $options['collect_fails'] : self::$configs['collect_fails'],
        );

        if (!empty(self::$configs['list_url_regexes'])) 
        {
            foreach (self::$configs['list_url_regexes'] as $regex) 
            {
                if (preg_match("#{$regex}#i", $url) && 
                    !$this->is_collected_url($url) && 
                    !$this->is_collect_url($url))
                {
                    $this->log("发现列表网页：{$url}", 'info');
                    $link['url_type'] = 'list_page';
                    $status = $this->queue_lpush($link);
                }
            }
        }

        if (!empty(self::$configs['content_url_regexes'])) 
        {
            foreach (self::$configs['content_url_regexes'] as $regex) 
            {
                if (preg_match("#{$regex}#i", $url) && 
                    !$this->is_collected_url($url) && 
                    !$this->is_collect_url($url))
                {
                    $this->log("发现内容网页：{$url}", 'info');
                    $link['url_type'] = 'content_page';
                    $status = $this->queue_lpush($link);
                }
            }
        }

        if (!empty(self::$configs['attachment_url_regexes'])) 
        {
            foreach (self::$configs['attachment_url_regexes'] as $regex) 
            {
                if (preg_match("#{$regex}#i", $url) && 
                    !$this->is_collected_url($url) && 
                    !$this->is_collect_url($url))
                {
                    $this->log("发现网页文件：{$url}", 'info');
                    $link['url_type'] = 'attachment_file';
                    $status = $this->queue_lpush($link);
                }
            }
        }
        return $status;
    }

    public function start()
    {
        //echo "\n".self::$configs['name']."爬虫开始测试, 将持续三分钟或抓取到30条数据后停止.\n\n";
        echo "\n[".self::$configs['name']."爬虫] 开始爬行...\n\n";

        echo util::colorize("!开发文档：\nhttps://doc.phpspider.org\n\n", "warn");

        // 爬虫开始时间
        self::$spider_time_start = time();

        // csv、sql、db
        self::$export_type = isset(self::$configs['export']['type']) ? self::$configs['export']['type'] : '';
        self::$export_file = isset(self::$configs['export']['file']) ? self::$configs['export']['file'] : '';
        self::$export_table = isset(self::$configs['export']['table']) ? self::$configs['export']['table'] : '';

        // 如果设置了导出选项
        if (!empty(self::$configs['export'])) 
        {
            if (self::$export_type == 'csv') 
            {
                if (empty(self::$export_file)) 
                {
                    $this->log("设置了导出类型为CSV的导出文件不能为空", 'fail');
                    exit;
                }
            }
            elseif (self::$export_type == 'sql') 
            {
                if (empty(self::$export_file)) 
                {
                    $this->log("设置了导出类型为sql的导出文件不能为空", 'fail');
                    exit;
                }
            }
            elseif (self::$export_type == 'db') 
            {
                db::_init_mysql($GLOBALS['config']['db']);
                if (!db::table_exists(self::$export_table))
                {
                    $this->log("数据库表(".self::$export_table.")不存在", 'warn');
                    exit;
                }
            }
        }

        if (empty(self::$configs['scan_urls'])) 
        {
            $this->log("No scan url to start\n", 'fail');
            exit;
        }

        if ($this->on_start) 
        {
            call_user_func($this->on_start, $this);
        }

        foreach ( self::$configs['scan_urls'] as $url ) 
        {
            $parse_url_arr = parse_url($url);
            if (empty($parse_url_arr['host']) || !in_array($parse_url_arr['host'], self::$configs['domains'])) 
            {
                $this->log("scan_urls中的域名(\"{$parse_url_arr['host']}\")不匹配domains中的域名\n", 'fail');
                exit;
            }

            $link = array(
                'url'           => $url,                            // 要抓取的URL
                'url_type'      => 'scan_page',                     // 要抓取的URL类型
                'method'        => 'get',                           // 默认为"GET"请求, 也支持"POST"请求
                'fields'        => array(),                         // 发送请求时需添加的参数, 可以为空
                'headers'       => self::$headers,                  // 此url的Headers, 可以为空
                'context_data'  => '',                              // 此url附加的数据, 可以为空
                'proxy'         => self::$configs['proxy'],         // 代理服务器
                'proxy_auth'    => self::$configs['proxy_auth'],    // 代理验证
                'collect_count' => 0,                               // 抓取次数
                'collect_fails' => self::$configs['collect_fails'], // 允许抓取失败次数
            );
            $this->queue_lpush($link);
        }

        // 测试抓取页面
        //$this->get_contents("http://www.qiushibaike.com/article/117554075");
        //exit;

        // 抓取页面
        while(!empty(self::$collect_queue))
        { 
            // 先进先出
            $link = $this->queue_rpop();
            $this->collect_page($link);
        } 

        $this->log("爬取完成\n");

        $spider_time_run = round(microtime(true) - self::$spider_time_start, 3);
        echo "总耗时：{$spider_time_run} 秒\n";
        echo "总共爬取链接数：".count(self::$collect_urls)."\n";
        echo "成功爬取链接数：".count(self::$collected_urls)."\n";
    }

    /**
     * 爬取页面
     * 
     * @param mixed $collect_url    要抓取的链接
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function collect_page($link) 
    {
        // 爬取页面开始时间
        $time_start = microtime(true);

        $url = $link['url'];

        if ($link['url_type'] == 'attachment_file') 
        {
            if ($this->on_attachment_file) 
            {
                $pathinfo = pathinfo($url);
                $filetype = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';
                call_user_func($this->on_attachment_file, $url, $filetype, $this);
            }
            return true;
        }

        $html = $this->request_url($url, $link);
        if (!$html) 
        {
            return false;
        }

        if ($this->is_anti_spider) 
        {
            $is_anti_spider = call_user_func($this->is_anti_spider, $url, $html);
            // 如果在回调函数里面判断被反爬虫并且返回true
            if ($is_anti_spider) 
            {
                return false;
            }
        }

        // 当前正在爬取的网页页面的对象
        $page = array(
            'url'     => $url,
            'raw'     => $html,
            'request' => array(
                'url'           => $url,
                'method'        => $link['method'],
                'headers'       => $link['headers'],
                'fields'        => $link['fields'],
                'context_data'  => $link['context_data'],
                'collect_count' => $link['collect_count'],
                'collect_fails' => $link['collect_fails'],
            ),
        );

        // 在一个网页下载完成之后调用. 主要用来对下载的网页进行处理.
        if ($this->on_download_page) 
        {
            // 在一个网页下载完成之后调用. 主要用来对下载的网页进行处理
            // 比如下载了某个网页，希望向网页的body中添加html标签
            // 回调函数记得无论如何最后一定要 return $page，因为下面的 入口、列表、内容页回调都用的 $page['raw']
            $page = call_user_func($this->on_download_page, $page, $this);
        }

        // 是否从当前页面分析提取URL
        $is_collect_url = true;
        if ($link['url_type'] == 'scan_page') 
        {
            if ($this->on_scan_page) 
            {
                // 回调函数如果返回false表示不需要再从此网页中发现待爬url
                $is_collect_url = call_user_func($this->on_scan_page, $page, $page['raw'], $this);
            }
        }
        elseif ($link['url_type'] == 'list_page') 
        {
            if ($this->on_list_page) 
            {
                // 回调函数如果返回false表示不需要再从此网页中发现待爬url
                $is_collect_url = call_user_func($this->on_list_page, $page, $page['raw'], $this);
            }
        }
        elseif ($link['url_type'] == 'content_page') 
        {
            if ($this->on_content_page) 
            {
                // 回调函数如果返回false表示不需要再从此网页中发现待爬url
                $is_collect_url = call_user_func($this->on_content_page, $page, $page['raw'], $this);
            }
        }

        // 页面下载成功 或 下载失败次数超过 都存入已爬取列表队列
        $this->set_collected_url($url);

        // 爬取页面耗时时间
        $time_run = round(microtime(true) - $time_start, 3);
        $this->log("网页下载成功：{$url}\t耗时: {$time_run} 秒\n");

        $spider_time_run = round(microtime(true) - self::$spider_time_start, 3);
        $this->log("爬虫运行时间：{$spider_time_run} 秒\n");

        $this->log("等待抓取网页：".count(self::$collect_urls)." 个\n");

        $this->log("已经抓取网页：".count(self::$collected_urls)." 个\n");

        // 这个就是现阶段检查程序有木有出Bug用的
        if (count(self::$collect_queue) != count(self::$collect_urls)) 
        {
            $this->log("等待抓取网页 和 队列 数量不等，请检查程序", 'fail');
            exit;
        }

        // on_scan_page、on_list_pag、on_content_page 返回false表示不需要再从此网页中发现待爬url
        if ($is_collect_url) 
        {
            // 分析提取HTML页面中的URL
            $this->get_html_urls($html, $url);
        }

        // 如果是内容页，分析提取HTML页面中的字段
        // 列表页也可以提取数据的，source_type: urlcontext，未实现
        if ($link['url_type'] == 'content_page') 
        {
            $this->get_html_fields($html, $url, $page);
        }

        // 爬虫爬取每个网页的时间间隔，单位：秒
        if (!empty(self::$configs['interval'])) 
        {
            sleep(self::$configs['interval']);
        }
        // 默认睡眠100毫秒，太快了会被认为是ddos
        else 
        {
            usleep(100000);
        }
    }

    /**
     * 下载网页，得到网页内容
     * 
     * @param mixed $url
     * @param mixed $options
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function request_url($url, $options = array())
    {
        //$url = "http://www.qiushibaike.com/article/117568316";

        $pattern = "/\b(([\w-]+:\/\/?|www[.])[^\s()<>]+(?:\([\w\d]+\)|([^[:punct:]\s]|\/)))/";
        if(!preg_match($pattern, $url))
        {
            $this->log("你所请求的URL({$url})不是有效的HTTP地址", 'fail');
            exit;
        }

        $parse_url_arr = parse_url($url);
        $domain = $parse_url_arr['host'];

        $link = array(
            'url'           => $url,
            'url_type'      => isset($options['url_type'])      ? $options['url_type']      : '',             
            'method'        => isset($options['method'])        ? $options['method']        : 'get',             
            'fields'        => isset($options['fields'])        ? $options['fields']        : array(),           
            'headers'       => isset($options['headers'])       ? $options['headers']       : self::$headers,    
            'context_data'  => isset($options['context_data'])  ? $options['context_data']  : '',                
            'proxy'         => isset($options['proxy'])         ? $options['proxy']         : self::$configs['proxy'],             
            'proxy_auth'    => isset($options['proxy_auth'])    ? $options['proxy_auth']    : self::$configs['proxy_auth'],             
            'collect_count' => isset($options['collect_count']) ? $options['collect_count'] : 0,                 
            'collect_fails' => isset($options['collect_fails']) ? $options['collect_fails'] : self::$configs['collect_fails'],
        );

        // 如果定义了获取附件回调函数，直接拦截了
        if ($this->on_attachment_file) 
        {
            $fileinfo = $this->is_attachment_file($url);
            // 如果不是html
            if (!empty($fileinfo)) 
            {
                $this->log("发现{$fileinfo['fileext']}文件：{$url}", 'info');
                call_user_func($this->on_attachment_file, $url, $fileinfo);
                return false;
            }
        }

        // 如果有代理服务器，自动切换IP
        cls_curl::set_headers(array('Proxy-Switch-Ip: yes'));
        cls_curl::set_timeout(self::$configs['timeout']);
        cls_curl::set_useragent(self::$configs['user_agent']);
        
        // 全局Cookie + 域名下的Cookie
        $cookies = self::$cookies;
        if (isset(self::$domain_cookies[$domain]) && is_array(self::$domain_cookies[$domain])) 
        {
            // 键名为字符时，＋把最先出现的值作为最终结果返回，array_merge()则会覆盖掉前面相同键名的值
            $cookies =  array_merge($cookies, self::$domain_cookies[$domain]);
        }

        // 是否设置了cookie
        if (!empty($cookies)) 
        {
            foreach ($cookies as $key=>$value) 
            {
                $cookie_arr[] = $key."=".$value;
            }
            $cookies = implode("; ", $cookie_arr);
            cls_curl::set_cookie($cookies);
        }

        // 是否设置了代理
        if (!empty($link['proxy'])) 
        {
            cls_curl::set_proxy($link['proxy'], $link['proxy_auth']);
        }

        // 如何设置了 HTTP Headers
        if (!empty($link['headers'])) 
        {
            cls_curl::set_headers($link['headers']);
        }

        // 不能通过 curl_setopt($ch, CURLOPT_NOBODY, 1) 只获取HTTP Header
        // 因为POST数据会失效
        // 即想POST过去，返回的http又只想取header部分是不行的
        cls_curl::set_http_raw(true);

        // 如果设置了附加的数据，如json和xml，就直接发附加的数据,php端可以用 file_get_contents("php://input"); 获取
        $fields = empty($link['context_data']) ? $link['fields'] : $link['context_data'];
        $method = strtolower($link['method']);
        $html = cls_curl::$method($url, $fields);

        // 对于登录成功后302跳转的，Cookie实际上存在body而不在header，header只有一句：HTTP/1.1 100 Continue
        // 为了兼容301和301这些乱七八糟的，还是header+body一起匹配吧
        // 解析Cookie并存入 self::$cookies 方便调用
        preg_match_all("/.*?Set\-Cookie: ([^\r\n]*)/i", $html, $matches);
        $cookies = empty($matches[1]) ? array() : $matches[1];

        // 解析到Cookie
        if (!empty($cookies)) 
        {
            $cookies = implode(";", $cookies);
            $cookies = explode(";", $cookies);
            foreach ($cookies as $cookie) 
            {
                $cookie_arr = explode("=", $cookie);
                // 过滤掉domain路径
                if (trim($cookie_arr[0]) == 'path') 
                {
                    continue;
                }
                // 从URL得到的Cookie不要放入全局，放到对应的域名下即可
                //self::$cookies[trim($cookie_arr[0])] = trim($cookie_arr[1]);
                self::$domain_cookies[$domain][trim($cookie_arr[0])] = trim($cookie_arr[1]);
            }
        }

        $http_code = cls_curl::get_http_code();

        if ($http_code != 200)
        {
            // 如果是301、302跳转，抓取跳转后的网页内容
            if ($http_code == 301 || $http_code == 302) 
            {
                // 先设置为采集过的网页，不再采集它了
                $this->is_collected_url($url);
                // 获取跳转后的地址扔到队列头部去，可以立刻采集
                $info = cls_curl::get_info();
                $link['url'] = $info['redirect_url'];
                $this->queue_rpush($link);
            }
            elseif ($http_code == 404) 
            {
                // 先设置为采集过的网页，不再采集它了
                $this->is_collected_url($url);
                $this->log("网页下载失败：{$url}\n", 'fail');
                $this->log("HTTP CODE：{$http_code} 网页不存在\n", 'fail');
            }
            elseif ($http_code == 407) 
            {
                // 扔到队列头部去，继续采集
                $this->queue_rpush($link);
                $this->log("网页下载失败：{$url}\n", 'fail');
                $this->log("代理服务器验证失败，请检查代理服务器设置\n", 'fail');
            }
            elseif ($http_code == 503) 
            {
                // 采集次数加一
                $link['collect_count']++;
                // 抓取次数 小于 允许抓取失败次数
                if ( $link['collect_count'] < $link['collect_fails'] ) 
                {
                    // 扔到队列头部去，继续采集
                    $this->queue_rpush($link);
                }
                // 失败次数超过了就放入已采集队列，免得以后在其他页面遇到又采集一次
                else 
                {
                    $this->is_collected_url($url);
                }
                $this->log("网页下载失败：{$url} 失败次数：{$link['collect_count']}\n", 'fail');
                $this->log("HTTP CODE：{$http_code} 服务器过载\n", 'fail');
            }
            else 
            {
                $this->log("网页下载失败：{$url}\n", 'fail');
                $this->log("HTTP CODE：{$http_code}\n", 'fail');
            }
            return false;
        }

        // 解析HTTP数据流
        if (!empty($html)) 
        {
            // body里面可能有 \r\n\r\n，但是第一个一定是HTTP Header，去掉后剩下的就是body
            $html_arr = explode("\r\n\r\n", $html);
            unset($html_arr[0]);
            $html = implode("\r\n\r\n", $html_arr);
        }
        return $html;
    }

    /**
     * 判断是否附件文件
     * 
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function is_attachment_file($url)
    {
        $mime_types = $GLOBALS['config']['mimetype'];
        $mime_types_flip = array_flip($mime_types);

        $pathinfo = pathinfo($url);
        $fileext = isset($pathinfo['extension']) ? $pathinfo['extension'] : '';

        $fileinfo = array();
        // 存在文件后缀并且是配置里面的后缀
        if (!empty($fileext) && isset($mime_types_flip[$fileext])) 
        {
            stream_context_set_default(
                array(
                    'http' => array(
                        'method' => 'HEAD'
                    )
                )
            );
            // 代理和Cookie以后实现，方法和 file_get_contents 一样 使用 stream_context_create 设置
            $headers = get_headers($url, 1);
            if (strpos($headers[0], '302')) 
            {
                $url = $headers['Location'];
                $headers = get_headers($url, 1);
            }
            //print_r($headers);
            $fileinfo = array(
                'basename' => isset($pathinfo['basename']) ? $pathinfo['basename'] : '',
                'filename' => isset($pathinfo['filename']) ? $pathinfo['filename'] : '',
                'fileext' => isset($pathinfo['extension']) ? $pathinfo['extension'] : '',
                'filesize' => isset($headers['Content-Length']) ? $headers['Content-Length'] : 0,
                'atime' => isset($headers['Date']) ? strtotime($headers['Date']) : time(),
                'mtime' => isset($headers['Last-Modified']) ? strtotime($headers['Last-Modified']) : time(),
            );

            $mime_type = 'html';
            $content_type = isset($headers['Content-Type']) ? $headers['Content-Type'] : '';
            if (!empty($content_type)) 
            {
                $mime_type = isset($GLOBALS['config']['mimetype'][$content_type]) ? $GLOBALS['config']['mimetype'][$content_type] : $mime_type;
            }
            $mime_types_flip = array_flip($mime_types);
            // 判断一下是不是文件名被加什么后缀了，比如 http://www.xxxx.com/test.jpg?token=xxxxx
            if (!isset($mime_types_flip[$fileinfo['fileext']]))
            {
                $fileinfo['fileext'] = $mime_type;
                $fileinfo['basename'] = $fileinfo['filename'].'.'.$mime_type;
            }
        }
        return $fileinfo;
    }

    /**
     * 分析提取HTML页面中的URL
     * 
     * @param mixed $html           HTML内容
     * @param mixed $collect_url    抓取的URL，用来拼凑完整页面的URL
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function get_html_urls($html, $collect_url) 
    { 
        //--------------------------------------------------------------------------------
        // 正则匹配出页面中的URL
        //--------------------------------------------------------------------------------
        preg_match_all('/<a .*?href="(.*?)".*?>/is', $html, $matchs); 
        $urls = !empty($matchs[1]) ? $matchs[1] : array();

        //--------------------------------------------------------------------------------
        // 过滤和拼凑URL
        //--------------------------------------------------------------------------------
        // 去除重复的RUL
        $urls = array_unique($urls);
        foreach ($urls as $k=>$url) 
        {
            $val = $this->get_complete_url($url, $collect_url);
            if ($val) 
            {
                $urls[$k] = $val;
            }
            else 
            {
                unset($urls[$k]);
            }
        }

        if (empty($urls)) 
        {
            return false;
        }

        //--------------------------------------------------------------------------------
        // 把抓取到的URL放入队列
        //--------------------------------------------------------------------------------
        foreach ($urls as $url) 
        {
            $this->add_url($url);
        }
        echo "\n";
    }

    /**
     * 是否待爬取网页
     * 
     * @param mixed $url
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function is_collect_url($url)
    {
        return array_key_exists(md5($url), self::$collect_urls);
    }

    /**
     * 添加待爬取网页标记
     * 
     * @param mixed $url
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function set_collect_url($url)
    {
        self::$collect_urls[md5($url)] = time();
    }

    /**
     * 删除待爬取网页标记
     * 
     * @param mixed $url
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function del_collect_url($url)
    {
        unset(self::$collect_urls[md5($url)]);
    }

    /**
     * 是否已爬取网页
     * 
     * @param mixed $url
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function is_collected_url($url)
    {
        return array_key_exists(md5($url), self::$collected_urls);
    }

    /**
     * 添加已爬取网页标记
     * 
     * @param mixed $url
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function set_collected_url($url)
    {
        self::$collected_urls[md5($url)] = time();
    }

    /**
     * 删除已爬取网页标记
     * 
     * @param mixed $url
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function del_collected_url($url)
    {
        unset(self::$collected_urls[md5($url)]);
    }

    /**
     * 从队列左边插入
     * 
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function queue_lpush($link = array())
    {
        if (empty($link) || empty($link['url'])) 
        {
            return false;
        }
        $url = $link['url'];
        array_push(self::$collect_queue, $link);
        $this->set_collect_url($url);
        return true;
    }

    /**
     * 从队列右边插入
     * 
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function queue_rpush($link = array())
    {
        if (empty($link) || empty($link['url'])) 
        {
            return false;
        }
        $url = $link['url'];
        array_unshift(self::$collect_queue, $link);
        $this->set_collect_url($url);
        return true;
    }

    /**
     * 从队列左边取出
     * 
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function queue_lpop()
    {
        // 后进先出
        // 可以避免采集内容页有分页的时候采集失败数据拼凑不全
        // 还可以按顺序采集列表页
        $link = array_pop(self::$collect_queue); 
        // 从采集数组中排除这个URL
        $this->del_collect_url($link['url']);
        return $link;
    }

    /**
     * 从队列右边取出
     * 
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function queue_rpop()
    {
        $link = array_shift(self::$collect_queue); 
        // 从采集数组中排除这个URL
        $this->del_collect_url($link['url']);
        return $link;
    }

    /**
     * 获得完整的连接地址
     * 
     * @param mixed $url            要检查的URL
     * @param mixed $collect_url    从那个URL页面得到上面的URL
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function get_complete_url($url, $collect_url)
    {
        $collect_parse_url = parse_url($collect_url);

        // 排除JavaScript的连接
        if (strpos($url, "javascript:") !== false) 
        {
            return false;
        }

        $cur_parse_url = parse_url($url);

        if (empty($cur_parse_url['path'])) 
        {
            return false;
        }

        // 如果host不为空，判断是不是要爬取的域名
        if (!empty($cur_parse_url['host'])) 
        {
            // 排除非域名下的url以提高爬取速度
            if (!in_array($cur_parse_url['host'], self::$configs['domains'])) 
            {
                return false;
            }
        }
        else
        {
            $url = $collect_parse_url['scheme'].'://'.str_replace("//", "/", $collect_parse_url['host']."/".$url);
        }
        return $url;
    }

    /**
     * 分析提取HTML页面中的字段
     * 
     * @param mixed $html
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function get_html_fields($html, $url, $page) 
    {
        $fields = $this->get_fields(self::$configs['fields'], $html, $url, $page);

        if (!empty($fields)) 
        {
            if ($this->on_extract_page) 
            {
                $return_data = call_user_func($this->on_extract_page, $page, $fields);
                if (!isset($return_data))
                {
                    $this->log("on_extract_page函数返回为空\n", 'warn');
                }
                elseif (!is_array($return_data))
                {
                    $this->log("on_extract_page函数返回值必须是数组\n", 'warn');
                }
                else 
                {
                    $fields = $return_data;
                }
            }

            if (isset($fields) && is_array($fields)) 
            {
                $fields_num = $this->incr_fields_num();
                $this->log("结果{$fields_num}：".json_encode($fields, JSON_UNESCAPED_UNICODE)."\n");

                // 如果设置了导出选项
                if (!empty(self::$configs['export'])) 
                {
                    self::$export_type = isset(self::$configs['export']['type']) ? self::$configs['export']['type'] : '';
                    if (self::$export_type == 'csv') 
                    {
                        util::put_file(self::$export_file, util::format_csv($fields)."\n", FILE_APPEND);
                    }
                    elseif (self::$export_type == 'sql') 
                    {
                        $sql = db::insert(self::$export_table, $fields, true);
                        util::put_file(self::$export_file, $sql.";\n", FILE_APPEND);
                    }
                    elseif (self::$export_type == 'db') 
                    {
                        db::insert(self::$export_table, $fields);
                    }
                }
            }

        }
    }

    /**
     * 提取到的field数目加一
     * 
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function incr_fields_num()
    {
        self::$fields_num++;
        return self::$fields_num;
    }

    /**
     * 提取到的field数目
     * 
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function get_fields_num()
    {
        return self::$fields_num;
    }

    /**
     * 根据配置提取HTML代码块中的字段
     * 
     * @param mixed $confs
     * @param mixed $html
     * @param mixed $page
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-23 17:13
     */
    public function get_fields($confs, $html, $url, $page) 
    {
        $fields = array();
        foreach ($confs as $conf) 
        {
            // 当前field抽取到的内容是否是有多项
            $repeated = isset($conf['repeated']) && $conf['repeated'] ? true : false;
            // 当前field抽取到的内容是否必须有值
            $required = isset($conf['required']) && $conf['required'] ? true : false;

            if (empty($conf['name'])) 
            {
                $this->log("field的名字是空值, 请检查你的\"fields\"并添加field的名字\n", 'fail');
                exit;
            }

            $values = array();
            // 如果定义抽取规则
            if (!empty($conf['selector'])) 
            {
                // 如果这个field是上一个field的附带连接
                if (isset($conf['source_type']) && $conf['source_type']=='attached_url') 
                {
                    // 取出上个field的内容作为连接，内容分页是不进队列直接下载网页的
                    if (!empty($fields[$conf['attached_url']])) 
                    {
                        $collect_url = $this->get_complete_url($url, $fields[$conf['attached_url']]);
                        $this->log("发现内容分页：{$url}", 'info');
                        $html = $this->request_url($collect_url);
                        // 请求获取完分页数据后把连接删除了 
                        unset($fields[$conf['attached_url']]);
                    }
                }

                // 没有设置抽取规则的类型 或者 设置为 xpath
                if (!isset($conf['selector_type']) || $conf['selector_type']=='xpath') 
                {
                    // 返回值一定是多项的
                    $values = $this->get_fields_xpath($html, $conf['selector'], $conf['name']);
                }
                elseif ($conf['selector_type']=='regex') 
                {
                    $values = $this->get_fields_regex($html, $conf['selector'], $conf['name']);
                }

                // field不为空而且存在子配置
                if (!empty($values) && !empty($conf['children'])) 
                {
                    $child_values = array();
                    // 父项抽取到的html作为子项的提取内容
                    foreach ($values as $html) 
                    {
                        // 递归调用本方法，所以多少子项目都支持
                        $child_value = $this->get_fields($conf['children'], $url, $html, $page);
                        if (!empty($child_value)) 
                        {
                            $child_values[] = $child_value;
                        }
                    }
                    // 有子项就存子项的数组，没有就存HTML代码块
                    if (!empty($child_values)) 
                    {
                        $values = $child_values;
                    }
                }
            }

            if (empty($values)) 
            {
                // 如果值为空而且值设置为必须项，跳出foreach循环
                if ($required) 
                {
                    break;
                }
                // 避免内容分页时attached_url拼接时候string + array了
                $fields[$conf['name']] = '';
                //$fields[$conf['name']] = array();
            }
            else 
            {
                // 不重复抽取则只取第一个元素
                $fields[$conf['name']] = $repeated ? $values : $values[0];
            }
        }

        if (!empty($fields)) 
        {
            foreach ($fields as $fieldname => $data) 
            {
                $pattern = "/<img.*?src=[\'|\"](.*?(?:[\.gif|\.jpg|\.jpeg|\.png]))[\'|\"].*?[\/]?>/i"; 
                // 在抽取到field内容之后调用, 对其中包含的img标签进行回调处理
                if ($this->on_handle_img && preg_match($pattern, $data)) 
                {
                    $return = call_user_func($this->on_handle_img, $fieldname, $data);
                    if (!isset($return))
                    {
                        $this->log("on_handle_img函数返回为空\n", 'warn');
                    }
                    else 
                    {
                        // 有数据才会执行 on_handle_img 方法，所以这里不要被替换没了
                        $data = $return;
                    }
                }

                // 当一个field的内容被抽取到后进行的回调, 在此回调中可以对网页中抽取的内容作进一步处理
                if ($this->on_extract_field) 
                {
                    $return = call_user_func($this->on_extract_field, $fieldname, $data, $page);
                    if (!isset($return))
                    {
                        $this->log("on_extract_field函数返回为空\n", 'warn');
                    }
                    else 
                    {
                        // 有数据才会执行 on_extract_field 方法，所以这里不要被替换没了
                        $fields[$fieldname] = $return;
                    }
                }
            }
        }

        return $fields;
    }

    /**
     * 采用xpath分析提取字段
     * 
     * @param mixed $html
     * @param mixed $selector
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function get_fields_xpath($html, $selector, $fieldname) 
    {
        $dom = new DOMDocument();
        @$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        //libxml_use_internal_errors(true);
        //$dom->loadHTML('<?xml encoding="UTF-8">'.$html);
        //$errors = libxml_get_errors();
        //if (!empty($errors)) 
        //{
            //print_r($errors);
            //exit;
        //}

        $xpath = new DOMXpath($dom);
        $elements = @$xpath->query($selector);
        if ($elements === false)
        {
            $this->log("field(\"{$fieldname}\")中selector的xpath(\"{$selector}\")语法错误\n", 'fail');
            exit;
        }

        $array = array();
        if (!is_null($elements)) 
        {
            foreach ($elements as $element) 
            {
                $nodeName = $element->nodeName;
                $nodeType = $element->nodeType;     // 1.Element 2.Attribute 3.Text
                //$nodeAttr = $element->getAttribute('src');
                //$nodes = util::node_to_array($dom, $element);
                //echo $nodes['@src']."\n";
                // 如果是img标签，直接取src值
                if ($nodeType == 1 && in_array($nodeName, array('img'))) 
                {
                    $content = $element->getAttribute('src');
                }
                // 如果是标签属性，直接取节点值
                elseif ($nodeType == 2 || $nodeType == 3) 
                {
                    $content = $element->nodeValue;
                }
                else 
                {
                    // 保留nodeValue里的html符号，给children二次提取
                    $content = $dom->saveXml($element);
                    //$content = trim($dom->saveHtml($element));
                    $content = preg_replace(array("#^<{$nodeName}.*>#isU","#</{$nodeName}>$#isU"), array('', ''), $content);
                }
                $array[] = trim($content);
            }
        }
        return $array;
    }

    /**
     * 采用正则分析提取字段
     * 
     * @param mixed $html
     * @param mixed $selector
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function get_fields_regex($html, $selector, $fieldname) 
    {
        if(@preg_match_all($selector, $html, $out) === false)
        {
            $this->log("field(\"{$fieldname}\")中selector的regex(\"{$selector}\")语法错误\n", 'fail');
            exit;
        }

        $array = array();
        if (!is_null($out[1])) 
        {
            foreach ($out[1] as $v) 
            {
                $array[] = trim($v);
            }
        }
        return $array;
    }

    /**
     * 采用CSS选择器提取字段
     * 
     * @param mixed $html
     * @param mixed $selector
     * @param mixed $fieldname
     * @return void
     * @author seatle <seatle@foxmail.com> 
     * @created time :2016-09-18 10:17
     */
    public function get_fields_css($html, $selector, $fieldname) 
    {
    }

    public function log($msg, $status = '')
    {
        echo util::colorize(date("H:i:s") . "  {$msg}\n", $status);
    }
}

