var config = require('config')

const FuelAuth = require('fuel-auth')
const FuelRest = require('fuel-rest')
const FuelSoap = require('fuel-soap')

const myClientId = config.get('salesforce.clientId')
const myClientSecret = config.get('salesforce.clientSecret')

const authUrl = config.get('rest.endpoints.salesforce.auth')
const restEndpoint = config.get('rest.endpoints.salesforce.rest')
const soapEndpoint = config.get('rest.endpoints.salesforce.soap')

const authOpts = {
  clientId: myClientId,
  clientSecret: myClientSecret,
  authUrl: authUrl,
  globalReqOptions: {
    json: {
      grant_type: 'client_credentials',
      client_id: myClientId,
      client_secret: myClientSecret
    }
  }
}
// Initialization to set up authorized requests.
const AuthClient = new FuelAuth(authOpts)

function _generateAuthToken (authOpts) {
  return AuthClient.getAccessToken(authOpts)
    .then(resp => {
    // Attach token to authorization client.
      authOpts.accessToken = resp.access_token ? resp.access_token : resp.accessToken
      return authOpts
    })
}

module.exports = {
  createFuelClients () {
    return _generateAuthToken(authOpts)
      .then(authorizedAuthOptions => {
        // Recreates client to have an access token in order to complete API calls.
        const restOpts = {
          auth: authorizedAuthOptions,
          restEndpoint: restEndpoint
        }

        const soapOpts = {
          auth: authorizedAuthOptions,
          soapEndpoint: soapEndpoint
        }
        return {
          RestClient: new FuelRest(restOpts),
          SoapClient: new FuelSoap(soapOpts)
        }
      })
  }
}
