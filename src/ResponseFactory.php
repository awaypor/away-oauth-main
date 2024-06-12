<?php

namespace Away\Oauth;

use Flarum\Forum\Auth\Registration;
use Flarum\Http\RememberAccessToken;
use Flarum\Http\Rememberer;
use Flarum\User\LoginProvider;
use Flarum\User\RegistrationToken;
use Flarum\User\User;
use Illuminate\Support\Arr;
use Laminas\Diactoros\Response\HtmlResponse;
use Psr\Http\Message\ResponseInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ResponseFactory
{
    /**
     * @var Rememberer
     */
    protected $rememberer;

    protected $translator;

    /**
     * @param Rememberer $rememberer
     */
    public function __construct(Rememberer $rememberer, TranslatorInterface $translator)
    {
        $this->rememberer = $rememberer;
        $this->translator = $translator;
    }
    /**
     * 创建用户登录或注册的方法
     *
     * @param string $provider 登录提供商名称
     * @param string $identifier 用户标识
     * @param callable $configureRegistration 配置注册信息的回调函数
     * @return ResponseInterface 返回响应接口实例
     */
    public function make(string $provider, string $identifier, callable $configureRegistration): ResponseInterface
    {
        // 尝试使用登录提供商提供的信息进行用户登录
        if ($user = LoginProvider::logIn($provider, $identifier)) {
            return $this->makeLoggedInResponse($user);
        }

        // 配置注册信息并获取提供的信息
        $configureRegistration($registration = new Registration);
        $provided = $registration->getProvided();

        // 如果提供的信息中包含邮箱，并且已存在对应用户则创建登录提供商关联并返回登录响应
        if (!empty($provided['email']) && $user = User::where(Arr::only($provided, 'email'))->first()) {
            $user->loginProviders()->create(compact('provider', 'identifier'));
            return $this->makeLoggedInResponse($user);
        }

        // 生成注册令牌并保存到数据库，返回带有提供的信息、建议的信息和令牌的响应数据
        $token = RegistrationToken::generate($provider, $identifier, $provided, $registration->getPayload());
        $token->save();
        return $this->makeResponse(array_merge(
            $provided,
            $registration->getSuggested(),
            [
                'token' => $token->token,
                'provided' => array_keys($provided)
            ]
        ));
    }


    private function makeResponse(array $payload): HtmlResponse
    {
        if (preg_match('/Android|SymbianOS|iPhone|iPad|iPod|BlackBerry|IEMobile|Opera Mini|Windows Phone|Midp/', $_SERVER['HTTP_USER_AGENT'])) {
            if (isset($payload['loggedIn']) && $payload['loggedIn']) {
                $content = sprintf(
                    '<script>window.location.href = "/"; window.app.authenticationComplete(%s);</script>',
                    json_encode($payload)
                );
            } else {
                $message = $this->translator->trans('away-puls-oauth.forum.alerts.unlinked');
                $content = sprintf(
                    '<script>alert("%s"); window.location.href = "/"; window.app.authenticationComplete(%s);</script>',
                    $message,
                    json_encode($payload)
                );
            }
        } else {
            $content = sprintf(
                '<script>window.close(); window.opener.app.authenticationComplete(%s);</script>',
                json_encode($payload)
            );
        }

        return new HtmlResponse($content);
    }

    private function makeLoggedInResponse(User $user)
    {
        $response = $this->makeResponse(['loggedIn' => true]);

        $token = RememberAccessToken::generate($user->id);

        return $this->rememberer->remember($response, $token);
    }
}
