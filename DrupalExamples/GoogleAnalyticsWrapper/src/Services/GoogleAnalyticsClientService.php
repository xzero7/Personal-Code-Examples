<?php

namespace Drupal\GoogleAnalyticsWrapper\Services;

use Drupal\Core\Site\Settings;

use \Google_Client;
use \Google_Service_AnalyticsReporting;
use \Google_Service_AnalyticsReporting_DateRange;
use \Google_Service_AnalyticsReporting_Metric;
use \Google_Service_AnalyticsReporting_Dimension;
use \Google_Service_AnalyticsReporting_ReportRequest;
use \Google_Service_AnalyticsReporting_GetReportsRequest;
use \Google_Service_AnalyticsReporting_DimensionFilterClause;
use \Google_Service_AnalyticsReporting_DimensionFilter;

/**
 * Class GoogleAnalyticsClientService.
 *
 * This service class is used to connect to Google Analytics
 */
class GoogleAnalyticsClientService {

  const PAGE_REQUEST_SIZE = 5000;
  /**
  * Variables for GoogleAnalyticsClientService
  *
  * These classes are used in the making of a request to Google Analytics
  *
  * @var \Google_Client $client
  * @var \Google_Service_AnalyticsReporting $analytics
  */
  protected $client;
  protected $analytics;
  protected $viewId;

  /**
  *
  *  GoogleAnalyticsClientService constructor
  */
  public function __construct() {
    // Configuration stores important GA information for setting up Client Connection
    $authConfig = Settings::get('google_anaytics_account_credentials');
    $this->viewId = Settings::get('google_analytics_view_id');

    $this->client = new Google_Client();
    $this->client->setApplicationName('Application Name');
    $this->client->setAuthConfig($authConfig);
    $this->client->setScopes(['https://www.googleapis.com/auth/analytics.readonly']);
    $this->analytics = new Google_Service_AnalyticsReporting($this->client);
  }

  /**
  * This function will generate a report for Page Unique View Counts for
  * one day's worth of data.
  *
  * @param $date
  *   Takes in a date string (YYYY-MM-DD)
  */
  public function generatePageUniqueViewsReport($date) {
    // Sets the range to grab data from the last day
    $dateRange = new Google_Service_AnalyticsReporting_DateRange();
    $dateRange->setStartDate($date);
    $dateRange->setEndDate($date);

    // The important metrics for this report
    // ga:uniquePageViews
    $uniquePageViews = new Google_Service_AnalyticsReporting_Metric();
    $uniquePageViews->setExpression('ga:uniquePageviews');
    $uniquePageViews->setAlias('uniquePageViews');

    // The important dimensions for this report
    // ga:contentGroup1
    // ga:pagePath
    // ga:date
    $contentGroup1 = new Google_Service_AnalyticsReporting_Dimension();
    $contentGroup1->setName('ga:contentGroup1');
    $pagePath = new Google_Service_AnalyticsReporting_Dimension();
    $pagePath->setName('ga:pagePath');
    $date = new Google_Service_AnalyticsReporting_Dimension();
    $date->setName('ga:date');

    // This will create the filter to only grab a certain type of page.
    $dimensionFilter = new Google_Service_AnalyticsReporting_DimensionFilter();
    $dimensionFilter->setDimensionName('ga:contentGroup1');
    $dimensionFilter->setOperator('EXACT');
    $dimensionFilter->setExpressions(array('Example Page'));

    // This will create the filter clause objects
    $dimensionFilterClause = new Google_Service_AnalyticsReporting_DimensionFilterClause();
    $dimensionFilterClause->setFilters(array($dimensionFilter));

    // Create the ReportRequest object.
    $request = new Google_Service_AnalyticsReporting_ReportRequest();
    $request->setViewId($this->viewId);
    $request->setDateRanges($dateRange);
    $request->setMetrics(array($uniquePageViews));
    $request->setDimensions(array($contentGroup1, $pagePath, $date));
    $request->setDimensionFilterClauses(array($dimensionFilterClause));
    $request->setPageSize(self::PAGE_REQUEST_SIZE);

    $body = new Google_Service_AnalyticsReporting_GetReportsRequest();
    $body->setReportRequests(array($request));

    return $this->analytics->reports->batchGet($body);

  }
}
