<?php

namespace enupal\socializer\elements\actions;

use Craft;
use craft\base\ElementAction;
use craft\elements\db\ElementQueryInterface;

use enupal\socializer\Socializer;
use Throwable;

/**
 *
 * @property string $triggerLabel
 */
class Delete extends ElementAction
{
    // Properties
    // =========================================================================

    /**
     * @var string|null The confirmation message that should be shown before the elements get deleted
     */
    public $confirmationMessage;

    /**
     * @var string|null The message that should be shown after the elements get deleted
     */
    public $successMessage;

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getTriggerLabel(): string
    {
        return Craft::t('enupal-socializer', 'Delete…');
    }

    /**
     * @inheritdoc
     */
    public static function isDestructive(): bool
    {
        return true;
    }

    // Public Methods
    // =========================================================================

    /**
     * @inheritdoc
     */
    public function getConfirmationMessage(): ?string
    {
        return Craft::t('enupal-socializer', "Are you sure you want to delete the selected providers, and all of it's tokens?");
    }

    /**
     * @inheritdoc
     * @throws Throwable
     */
    public function performAction(ElementQueryInterface $query): bool
    {
        $message = null;

        $response = Socializer::$app->providers->deleteProviders($query->all());

        if ($response) {
            $message = Craft::t('enupal-socializer', 'Providers Deleted.');
        } else {
            $message = Craft::t('enupal-socializer', 'Failed to delete providers.');
        }

        $this->setMessage($message);

        return $response;
    }
}
