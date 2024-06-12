<?php

namespace Away\Oauth;

use Exception;
use Flarum\Forum\Auth\Registration;
use Flarum\Http\UrlGenerator;
use Flarum\Settings\SettingsRepositoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface;
use Laminas\Diactoros\Response\RedirectResponse;
use Illuminate\Support\Str;
use Illuminate\Support\Arr;

class OAuthLoginController implements RequestHandlerInterface
{
    /**
     * @var ResponseFactory
     */
    protected $response;

    /**
     * @var SettingsRepositoryInterface
     */
    protected $settings;

    /**
     * @var UrlGenerator
     */
    protected $url;

    /**
     * @param ResponseFactory $response []
     * @param SettingsRepositoryInterface $settings
     * @param UrlGenerator $url
     */
    public function __construct(ResponseFactory $response, SettingsRepositoryInterface $settings, UrlGenerator $url)
    {
        $this->response = $response;
        $this->settings = $settings;
        $this->url      = $url;
    }


    /**
     * 处理用户登录请求的方法
     *
     * @param Request $request 用户请求对象
     * @return ResponseInterface 返回响应接口实例
     * @throws Exception 当发生异常时抛出
     */
    public function handle(Request $request): ResponseInterface
    {
        // 构建回调URL和OAuth提供商实例
        $callback = $this->url->to('api')->route('oauth.login');
        $provider   = new PulsOauth([
            'apiurl' => $this->settings->get('away-puls-oauth.appurl'),
            'appid' => $this->settings->get('away-puls-oauth.appid'),
            'appkey' => $this->settings->get('away-puls-oauth.appkey'),
            'callback' => $callback,
        ]);

        // 获取会话和查询参数
        $session = $request->getAttribute('session');
        $queryParams = $request->getQueryParams();
        $type = Arr::get($queryParams, 'type');
        if(!$type){
            throw new Exception('Invalid type');
        }

        // 检查查询参数中的code，如果不存在则进行OAuth重定向
        $code = Arr::get($queryParams, 'code');
        if (!$code) {
            $state = md5(uniqid(rand(), TRUE));
            $authUrl = $provider->login($type, $state);
            $session->put('oauth2state', $state);
            return new RedirectResponse($authUrl);
        }

        $state = Arr::get($queryParams, 'state');

        // 验证state参数与会话中的oauth2state是否一致
        if (!$state || $state !== $session->get('oauth2state')) {
            $session->remove('oauth2state');
            throw new Exception('Invalid state');
        }

        // 通过code参数获取用户信息，并调用make方法创建登录或注册响应
        $userinfo = $provider->callback($code);// 第三方的token->info回调

        $loginResultRes = $this->response->make(
            $type,
            $userinfo["social_uid"],
            function (Registration $registration) use ($userinfo) {
                $registration
                    ->suggestUsername($this->UserNameMatch($userinfo["nickname"]))
                    ->setPayload($userinfo);
                if(!empty($userinfo['faceimg']))
                    $registration->provideAvatar($userinfo['faceimg']);
            }
        );

        return $loginResultRes;
    }


    public function UserNameMatch($str)
    {
        preg_match_all('/[\x{4e00}-\x{9fa5}a-zA-Z0-9]/u', $str, $result);
        return implode('', $result[0]);
    }
}