var config = require('config')
var Promise = require('bluebird')

var FuelClients = require('./fuelClients')

const customerExtensionExternalKey = config.get('rest.keys.salesforce.extensionExternalKey.customer')
const customerPreferenceExtensionExternalKey = config.get('rest.keys.salesforce.extensionExternalKey.customerPreference')

const createFuelClients = FuelClients.createFuelClients

/**
 *  Creates a payload that represents values that need to be pushed into Salesforce
 *  Rest Route: POST /hub/v1/dataevents/key:{key}/rowset
 *
 *  @param type
 *    Represents the data extension this payload is being generated for
 *  @param data
 *    Reperesents the array of values for the secondary key for the data extension
 *  @param primaryKey
 *    Represents the primaryKey that can be used accross each row
 */
function _createRowSet (type, data, primaryKey) {
  const rowSet = []

  // Will loop through data, which in this case used to set up the secondary primary key for our row.
  for (var row of data) {
    // This creates the 1 to many relationship or data model requires.
    // Using email as the anchor
    const keys = {
      email: primaryKey
    }
    keys[type] = row
    rowSet.push(
      {
        keys: keys,
        values: keys
      }
    )
  }

  return rowSet
}

module.exports = {
  createCustomer (preferences) {
    return createFuelClients()
      .then(({ RestClient }) => {
        const putUrl = `/hub/v1/dataevents/key:${customerExtensionExternalKey}/rows/email:${preferences.userInformation.emailAddress}`

        const todaysDate = new Date().toISOString()
        const options = {
          uri: putUrl,
          json: {
            values: {
              first_name: preferences.userInformation.firstName,
              last_name: preferences.userInformation.lastName,
              locality: preferences.userInformation.city,
              region: preferences.userInformation.state,
              postal_code: preferences.userInformation.postalCode,
              country: preferences.userInformation.country,
              address_1: preferences.userInformation.address1,
              date_last_updated: todaysDate
            }
          }
        }

        return RestClient.put(options)
          .then(resp => {
            if (resp.res.statusCode === 200) {
              return resp.body
            } else {
              // Throw Handled Error
            }
          })
          .catch(err => {
            // Log Unexpected Error
            console.log(err)
          })
      })
  },
  createCustomerPreference (preferences) {
    return createFuelClients()
      .then(({ RestClient }) => {
        const postUrl = `/hub/v1/dataevents/key:${customerPreferenceExtensionExternalKey}/rowset`

        const options = {
          uri: postUrl,
          json: _createRowSet('preference', preferences.userSelectedPreferences, preferences.userInformation.emailAddress)
        }

        return RestClient.put(options)
          .then(resp => {
            if (resp.res.statusCode === 200) {
              return resp.body
            } else {
              // Throw Handled Error
            }
          })
          .catch(err => {
            // Log Unexpected Error
            console.log(err)
          })
      })
  },
  retrieveCustomerPreferences (emailAddress) {
    return createFuelClients()
      .then(({ SoapClient }) => {
        const customer = emailAddress
        const soapRetrieve = Promise.promisify(SoapClient.retrieve, { context: SoapClient })

        const options = {
          filter: {
            leftOperand: 'email',
            operator: 'equals',
            rightOperand: customer
          }
        }

        return soapRetrieve(
          `DataExtensionObject[${customerPreferenceExtensionExternalKey}]`,
          ['preference'],
          options
        )
          .then(resp => {
            const preferences = []
            resp.body.Results.forEach((result) => {
              preferences.push(result.Properties.Property.Value)
            })

            return {
              email: customer,
              preferences: preferences
            }
          })
      })
      .catch(err => {
        // Log Unexpected Error
        console.log(err)
      })
  }
}
