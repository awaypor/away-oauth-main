<?php

namespace Away\Oauth;

use Flarum\Extend;
use Flarum\Api\Serializer\UserSerializer;

return [
  (new Extend\Frontend('forum'))
    ->js(__DIR__ . '/js/dist/forum.js')
    ->css(__DIR__ . '/resources/less/forum.less'),
  (new Extend\Frontend('admin'))
    ->js(__DIR__ . '/js/dist/admin.js')
    ->css(__DIR__ . '/resources/less/admin.less'),

  (new Extend\Settings())
    ->serializeToForum('oauth_openqq', 'away-puls-oauth.openqq')
    ->serializeToForum('oauth_openwx', 'away-puls-oauth.openwx')
    ->serializeToForum('oauth_opensina', 'away-puls-oauth.opensina'),

  (new Extend\Routes('api'))
    ->get('/oauth/login', 'oauth.login', OAuthLoginController::class)
    ->get('/oauth/link', 'oauth.link', OAuthLinkController::class)
    ->post('/oauth/unlink', 'oauth.unlink', OAuthUnlinkController::class),

  new Extend\Locales(__DIR__ . '/resources/locale'),

  (new Extend\ApiSerializer(UserSerializer::class))
    ->attributes(function($serializer, $user, $attributes) {

        $attributes['is_qq_linked'] = $user->loginProviders()->where('provider', 'qq')->first() !== null;
        $attributes['is_wx_linked'] = $user->loginProviders()->where('provider', 'wx')->first() !== null;
        $attributes['is_sina_linked'] = $user->loginProviders()->where('provider', 'sina')->first() !== null;
        $attributes['providersCount'] = $user->loginProviders()->count();

        return $attributes;
    }),
];
