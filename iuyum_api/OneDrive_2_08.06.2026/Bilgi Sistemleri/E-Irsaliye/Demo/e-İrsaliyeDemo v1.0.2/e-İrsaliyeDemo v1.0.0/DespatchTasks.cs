using e_İrsaliyeDemo_v1._0._0.DespatchConnect;
using e_İrsaliyeDemo_v1._0._0.Properties;
using System;
using System.Collections.Generic;
using System.Linq;
using System.Text;
using System.Threading.Tasks;

namespace e_İrsaliyeDemo_v1._0._0
{
    class DespatchTasks
    {
  

        public static DespatchTasks Instance = new DespatchTasks();


        public DespatchIntegrationClient CreateClient()
        {
            var username = "";
            var password = "";
            var serviceuri = "";
            //if (Settings.Default.UseTestEnvironment)
            //{
                username = Settings.Default.WebServiceTestUsername;
                password = Settings.Default.WebServiceTestPassword;
                serviceuri = Settings.Default.WebServiceTestUri;
            //}
            //if (!Settings1.Default.UseTestEnvironment)
            //{
            //    username = Settings1.Default.WebServiceLiveUsername;
            //    password = Settings1.Default.WebServiceLivePassword;
            //    serviceuri = Settings1.Default.WebServiceLiveUri;
            //}

            DespatchIntegrationClient client = new DespatchIntegrationClient();
            client.Endpoint.Address = new System.ServiceModel.EndpointAddress(serviceuri);
            client.ClientCredentials.UserName.UserName = username;
            client.ClientCredentials.UserName.Password = password;
            return client;
        }
          }
}
