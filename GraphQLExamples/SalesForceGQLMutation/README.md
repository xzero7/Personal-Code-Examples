# Salesforce Marketing Cloud Integration with Fuel-SDK API and GraphQL

Learning Example of Working with public open source APIs to create a functioning
GraphQL Mutation that sends updates to Salesforce Marketing Cloud Data Extensions.

# Business Ask

Given a business requirements needed an update to the GraphQL mutation for customer recording. The old
email provider was being swapped out for Salesforce and the code needed to be updated to ensure that the current schema for email mutations need not change. In Salesforce given two Data Extensions (DE), Customer and CustomerPreference create payloads from the current schema to complete Email Integration. The Customer DE holds basic customer information while the preference DE was 1 to many extension with a customer email being the primaryKey.

# FuelClients File

I was working with FuelSDK provided by Salesforce to create an email signup integration
from a Node GraphQL application. At the time of development there was issues with handling authentication.
The documentation and API were limited to v1 usage but was building use v2 documentation. This file is
the culmination of trial and error to circumvent the problem, and an [Open Issue](https://github.com/salesforce-marketingcloud/FuelSDK-Node-Auth/issues/75) was created around **March 2019** when active development was happening. This helper calls allows a creation of a Rest and Soap Client Object to be used to make SOAP and Rest calls to Salesforce Marketing Cloud.

# Salesforce File

From the GraphQL resolver file, this file holds the functions that made direct API calls to Salesforce Instance to create records to business Data Extensions. Calls implemented where
  - Create Customer Record
  - Create Preference Record
  - Retrieve a Customer Preferences
