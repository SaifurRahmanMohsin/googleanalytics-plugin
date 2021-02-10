<?php namespace Mohsin\GoogleAnalytics\Models;

use October\Rain\Database\Model;

/**
 * Google Analytics settings model
 *
 * @package system
 * @author Saifur Rahman Mohsin
 *
 */
class Settings extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $implement = ['System.Behaviors.SettingsModel'];

    public $settingsCode = 'mohsin_googleanalytics_settings';

    public $settingsFields = 'fields.yaml';

    public $attachOne = [
        'gapi_key' => ['System\Models\File', 'public' => false]
    ];

    /**
     * Validation rules
     */
    public $rules = [
        'gapi_key'      => 'required_with:profile_id',
        'property_id'   => 'required_with:gapi_key'
    ];

    public function initSettingsData()
    {
        $this->domain_name = 'auto';
        $this->anonymize_ip = false;
        $this->force_ssl = false;
    }
}
