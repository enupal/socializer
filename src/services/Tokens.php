<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\services;

use Craft;
use craft\elements\User;
use enupal\socializer\elements\Provider;
use enupal\socializer\elements\Token;
use enupal\socializer\Socializer;
use yii\base\Component;

class Tokens extends Component
{
    /**
     * @param int $providerId
     * @param int $userId
     * @return array|\craft\base\ElementInterface|null
     */
    public function getToken(int $providerId, int $userId)
    {
        $query = Token::find();
        $query->providerId = $providerId;
        $query->userId = $userId;

        return $query->one();
    }

    /**
     * @param User $user
     * @param Provider $provider
     * @return bool
     * @throws \Throwable
     * @throws \craft\errors\ElementNotFoundException
     * @throws \yii\base\Exception
     */
    public function registerToken(User $user, Provider $provider)
    {
        $adapter = $provider->getAdapter();
        $token = Socializer::$app->tokens->getToken($provider->id, $user->id);

        if (is_null($token)){
            $token = new Token();
            $token->providerId = $provider->id;
            $token->userId = $user->id;
        }

        $token->accessToken = $adapter->getAccessToken();

        if (!Craft::$app->elements->saveElement($token)) {
            Craft::error("Unable to save token: ".json_encode($token->getErrors()), __METHOD__);
            return false;
        }

        return true;
    }
}
