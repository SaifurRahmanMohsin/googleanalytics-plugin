<?php namespace Mohsin\GoogleAnalytics\Traits;

use ApplicationException;
use Exception;
use Google_Service_AnalyticsData_RunReportRequest;
use Mohsin\GoogleAnalytics\Classes\Analytics;
use Google\Service\Exception as GoogleServiceException;

trait DataTrait
{
    /**
     * array Available dimensions
     */
    protected $availableDimensions = [];
    
    /**
     * array Available metrics
     */
    protected $availableMetrics = [];

    public function getDimensionOptions()
    {
        return $this->availableDimensions;
    }

    public function getOrderByDimensionOptions()
    {
        return $this->availableDimensions;
    }

    public function getMetricOptions()
    {
        return $this->availableMetrics;
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
        $analyticsClient = Analytics::instance();
        $meta = $analyticsClient->service->properties->getMetadata([
            'properties/' . $analyticsClient->propertyId . '/metadata'
        ]);
        foreach ($meta->dimensions as $dimension) {
            $this->availableDimensions[$dimension->apiName] = $dimension->uiName;
        }
        foreach ($meta->metrics as $metric) {
            $this->availableMetrics[$metric->apiName] = $metric->uiName;
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
        return $analyticsClient->service->v1alpha->runReport(new Google_Service_AnalyticsData_RunReportRequest([
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
        ]));
    }
}
