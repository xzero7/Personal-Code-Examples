# Custom Drupal Module to Create a Google Analytics Service Class

Learning example to create a Google Analytics Service in Drupal that allows operations to happen to a Google Analytics account.

# Business Ask

A Drupal 8 CMS handles a ton of exporting operations that is being sent to multiple sources. During an export module feature, there is a need for a Content Type to attach associated Google Analytics data along with an export. Create a Service that can be used to connect to a GA account and perform analytics queries to it.

# GoogleAnalyticsClientService

Currently a one function client class that generates 1 days worth of unique page views report for a specified page. This is a simple implementation that leaves room for more customization (changing dates, content type arguments) so at the moment it is best used as a way to get one page type report.
