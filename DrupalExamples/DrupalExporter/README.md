# Drupal 8 Module WorkCase to export Drupal Entities to External Sources.

There is a growing demand to create solutions that are decoupled from CMS monoliths to create robust fast frontend experiences. This module learning example I work with a contributed module to create an Event Listening System that can be configured to handle one event in multiple ways (if one so chooses). This is achieved by taken advantage of Drupal 8's Plugin System.

# Business Ask

There was an ETL process created using GraphQL Spec to allow a ReactJS application to have a simplified data model in which to retrieve content from. Using ElasticSearch as a data warehouse, the GraphQL API reads from that store to send information to a React Application. Allow a Drupal 8 CMS System to send data to a designated ElasticSearch Cluster.

# DrupalEventSubscriber File

Using the contributed module ***hook_event_dispatcher*** this service class was the basis to listen to all Drupal Events. For the needs of reacting to Node updates this was perfect. By using Plugins at this level the listener did not need to know or store information on how to react and can be handled by one or many Plugins (in theory).

# ElasticSearchModel File
The service class created to wrap the ES client and create a model to handle all calls to ElasticSearch. It can create an index if needed and expects a user of this class to configure where indexing should go. This allows flexibility to have content dictate how and where it go.

#ElasticSearchProcessor File

This is the heavy lifter, this takes the Drupal Entities and transform it to something we would want to send to ElasticSearch. You can process individual fields or field references as you wish. At the end this returns an array that can be sent to the ElasticSearchModel Service Class to be then sent to the designated ElasticSearch index destination.
