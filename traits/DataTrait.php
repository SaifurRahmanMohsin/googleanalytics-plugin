<?php namespace Mohsin\GoogleAnalytics\Traits;

use Event;
use Cache;
use Exception;
use ApplicationException;
use Mohsin\GoogleAnalytics\Classes\Analytics;
use Google_Service_AnalyticsData_RunReportRequest;
use Google\Service\Exception as GoogleServiceException;

trait DataTrait
{
    /**
     * array Cache of available dimensions
     */
    protected $availableDimensionsCache = [];
    
    /**
     * array Cache of available metrics
     */
    protected $availableMetricsCache = [];

    public function getDimensionOptions()
    {
        return $this->availableDimensionsCache;
    }

    public function getOrderByDimensionOptions()
    {
        return $this->availableDimensionsCache;
    }

    public function getMetricOptions()
    {
        return $this->availableMetricsCache;
    }

    /**
     * Renders the widget
     */
    public function render()
    {
        try {
            $this->renderData();
        }
        catch (Exception $ex) {
            $this->vars['error'] = $ex->getMessage();
        }

        return $this->makePartial('widget');
    }

    /**
     * Loads the analytics metadata
     */
    protected function loadMeta()
    {
        if (!Cache::has('ga4_meta') || empty(Cache::get('ga4_meta'))) {
            $analyticsClient = Analytics::instance();
            $meta = $analyticsClient->service->properties->getMetadata([
                'properties/' . $analyticsClient->propertyId . '/metadata'
            ]);
            foreach ($meta->dimensions as $dimension) {
                $this->availableDimensionsCache[$dimension->apiName] = $dimension->uiName;
            }
            foreach ($meta->metrics as $metric) {
                $this->availableMetricsCache[$metric->apiName] = $metric->uiName;
            }

            Cache::put('ga4_meta', [
                'dimensions' => $this->availableDimensionsCache,
                'metrics'    => $this->availableMetricsCache
            ], now()->addDays(10));
        } else {
            $cacheMeta = Cache::get('ga4_meta');
            $this->availableDimensionsCache = $cacheMeta['dimensions'];
            $this->availableMetricsCache = $cacheMeta['metrics'];
        }
    }

    /**
     * Loads the analytics data
     */
    protected function loadData()
    {
        $dimensions = [];
        $metrics = [];
        $orderBys = [];

        if (!$days = $this->property('days')) {
            throw new ApplicationException(trans('mohsin.googleanalytics::lang.errors.invalid_days').$days);
        }

        if (!$dimension = $this->property('dimension')) {
            throw new ApplicationException(trans('mohsin.googleanalytics::lang.errors.invalid_dimension').$dimension);
        }
        array_push($dimensions, ['name' => $dimension]);

        if (!$metric = $this->property('metric')) {
            throw new ApplicationException(trans('mohsin.googleanalytics::lang.errors.invalid_metric').$metric);
        }
        array_push($metrics, ['name' => $metric]);

        if ($orderByDimension = $this->property('orderByDimension')) {
            array_push($orderBys, ['dimension' => [
                'dimensionName' => $orderByDimension
            ]]);

            if ($orderByDimension != $dimension) {
                array_push($dimensions, ['name' => $orderByDimension]);
            }
        } else {
            array_push($orderBys, ['dimension' => [
                'dimensionName' => $dimension
            ]]);
        }

        $analyticsClient = Analytics::instance();

        $requestData = [
            'entity' => [
                'propertyId' => $analyticsClient->propertyId
            ],
            'dateRanges' => [
                [
                    'startDate' => $days.'daysAgo',
                    'endDate'   => 'today'
                ]
            ],
            'orderBys' => $orderBys,
            'metrics' => $metrics,
            'dimensions' => $dimensions
        ];

        // Allow extension of API request
        $eventResults = Event::fire('mohsin.googleanalytics.extendRequest', [$this, $requestData]);
        foreach ($eventResults as $eventResult) {
            if (is_array($eventResult)) {
                $requestData = array_merge($requestData, $eventResult);
            }
        }

        if ($this->property('hideNotSet', false)) {
            $requestData['dimensionFilter'] = [
                'notExpression' => [
                    'filter' => [
                        'fieldName' => $dimension,
                        'stringFilter' => [
                            'value' => '(not set)'
                        ]
                    ]
                ]
            ];
        }

        $analyticsData = $analyticsClient->service->v1alpha->runReport(new Google_Service_AnalyticsData_RunReportRequest($requestData));

        // Conversions for month and day of week based on API schema at https://developers.google.com/analytics/devguides/reporting/data/v1/api-schema
        if ($dimension == 'dayOfWeek') {
            $dowMap = ['Sunday', 'Monday', 'Tuesday', 'Wednesday', 'Thursday', 'Friday', 'Saturday'];
            foreach ($analyticsData->getRows() as $row) {
                foreach ($row->getDimensionValues() as $dimensionRow) {
                    $dimensionRow->setValue($dowMap[$dimensionRow->getValue()]);
                }
            }
        }

        if ($dimension == 'month') {
            $monthMap = ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'];
            foreach ($analyticsData->getRows() as $row) {
                foreach ($row->getDimensionValues() as $dimensionRow) {
                    $dimensionRow->setValue($monthMap[(int)$dimensionRow->getValue()]);
                }
            }
        }

        return $analyticsData;
    }
}
