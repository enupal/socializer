<?php
/**
 * Socializer plugin for Craft CMS 3.x
 *
 * @link      https://enupal.com/
 * @copyright Copyright (c) 2019 Enupal LLC
 */

namespace enupal\socializer\base;

use Craft;
use craft\elements\User;
use yii\base\InvalidConfigException;

/**
 * Class Integration
 */
abstract class Integration implements IntegrationInterface
{
    // Traits
    // =========================================================================

    use ProviderTrait;

    /**
     * @return array|void|null
     * @throws InvalidConfigException
     */
    public function init()
    {
        parent::init();

        if ($this->formId) {
            $this->refreshFieldMapping();
        }
    }

    /**
     * @return Form
     */
    public function getForm(): Form
    {
        if (!$this->form) {
            $this->form = SproutForms::$app->forms->getFormById($this->formId);
        }

        return $this->form;
    }

    /**
     * @inheritdoc
     */
    public function settingsAttributes(): array
    {
        $attributes = parent::settingsAttributes();
        $attributes[] = 'fieldMapping';

        return $attributes;
    }

    /**
     * @inheritDoc
     */
    public function getSuccessMessage()
    {
        if ($this->successMessage !== null) {
            return $this->successMessage;
        }

        return Craft::t('sprout-forms', 'Success');
    }

    /**
     * @inheritDoc
     */
    public function submit(): bool
    {
        return false;
    }

    /**
     * Returns a list of Users Source Fields as Field Instances
     *
     * Field Instances will be used to help create the fieldMapping using field handles.
     *
     * @return array
     */
    public function getSourceFormFields(): array
    {
        $userModel = new User();
        $userFields = $userModel->getFieldLayout()->getFields();

        return $userFields;
    }

    /**
     * @inheritDoc
     */
    public function getTargetProviderFieldsAsMappingOptions(): array
    {
        return [];
    }

    /**
     * Represents a Field Mapping as a one-dimensional array where the
     * key is the sourceFormFieldHandle and the value is the targetIntegrationField handle
     *
     * [
     *   'title' => 'title',
     *   'customFormFieldHandle' => 'customTargetFieldHandle'
     * ]
     *
     * @return array
     * @var array
     */
    public function getIndexedFieldMapping(): array
    {
        if ($this->fieldMapping === null) {
            return [];
        }

        $indexedFieldMapping = [];

        // Update our stored settings to use the sourceFormField handle as the key of our array
        foreach ($this->fieldMapping as $fieldMap) {
            $indexedFieldMapping[$fieldMap['sourceFormField']] = $fieldMap['targetProviderField'];
        }

        return $indexedFieldMapping;
    }

    /**
     * Updates the Field Mapping with any fields that have been added
     * to the Field Layout for a given user
     *
     */
    public function refreshFieldMapping()
    {
        $newFieldMapping = [];
        $sourceFormFields = $this->getSourceFormFields();
        $indexedFieldMapping = $this->getIndexedFieldMapping();

        // Loop through the current list of form fields and match them to any existing fieldMapping settings
        foreach ($sourceFormFields as $sourceFormField) {
            // If the handle exists in our old field mapping (a field that was just
            // added to the form may not exist yet) use that value. Default to empty string.
            $targetIntegrationField = $indexedFieldMapping[$sourceFormField->handle] ?? '';

            $newFieldMapping[] = [
                'sourceFormField' => $sourceFormField->handle,
                'targetIntegrationField' => $targetIntegrationField
            ];
        }

        $this->fieldMapping = $newFieldMapping;
    }

    /**
     * @inheritDoc
     */
    public function getTargetIntegrationFieldValues(): array
    {
        if (!$this->fieldMapping) {
            return null;
        }

        $fields = [];
        $formEntry = $this->formEntry;

        foreach ($this->fieldMapping as $fieldMap) {
            if (isset($formEntry->{$fieldMap['sourceFormField']}) && $fieldMap['targetIntegrationField']) {
                $fields[$fieldMap['targetIntegrationField']] = $formEntry->{$fieldMap['sourceFormField']};
            }
        }

        return $fields;
    }

    /**
     * Returns the HTML where a user will prepare a field mapping
     *
     * @return string|null
     */
    public function getFieldMappingSettingsHtml()
    {
        return null;
    }

    /**
     * @return array
     * @throws InvalidConfigException
     */
    final public function getSendRuleOptions(): array
    {
        $fields = $this->getForm()->getFields();
        $optIns = [];
        $fieldHandles = [];

        foreach ($fields as $field) {
            if (get_class($field) == OptIn::class) {
                $optIns[] = [
                    'label' => $field->name.' ('.$field->handle.')',
                    'value' => $field->handle,
                ];
                $fieldHandles[] = $field->handle;
            }
        }

        $options = [
            [
                'label' => Craft::t('sprout-forms', 'Always'),
                'value' => '*'
            ]
        ];

        $options = array_merge($options, $optIns);

        $customSendRule = $this->sendRule;

        $options[] = [
            'optgroup' => Craft::t('sprout-forms', 'Custom Rule')
        ];

        if (!in_array($this->sendRule, $fieldHandles, false) && $customSendRule != '*') {
            $options[] = [
                'label' => $customSendRule,
                'value' => $customSendRule
            ];
        }

        $options[] = [
            'label' => Craft::t('sprout-forms', 'Add Custom'),
            'value' => 'custom'
        ];

        return $options;
    }
}
