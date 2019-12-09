<?php

namespace enupal\socializer\base;


/**
 * ProviderTrait implements the common methods and properties for Provider classes.
 */
trait ProviderTrait
{
    // Properties
    // ========================================================================

    /**
     * @var string
     */
    public $clientId = true;

    /**
     * The ID of the Form where an Integration exists
     *
     * @var int
     */
    public $clientSecret;

    /**
     * The Field Mapping settings
     *
     * This data is saved to the database as JSON in the settings column and populated
     * as an array when an Provider Component is created
     *
     * [
     *   [
     *     'sourceFormField' => 'title',
     *     'targetIntegrationField' => 'title'
     *   ],
     *   [
     *     'sourceFormField' => 'customFormFieldHandle',
     *     'targetIntegrationField' => 'customTargetFieldHandle'
     *   ]
     * ]
     *
     * @var array|null
     */
    public $fieldMapping;
}
