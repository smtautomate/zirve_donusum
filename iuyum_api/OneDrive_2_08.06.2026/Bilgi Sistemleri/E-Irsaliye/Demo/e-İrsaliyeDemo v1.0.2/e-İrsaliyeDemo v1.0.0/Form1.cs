using e_İrsaliyeDemo_v1._0._0.DespatchConnect;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.IO;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using System.Xml;
using System.Xml.Serialization;

namespace e_İrsaliyeDemo_v1._0._0
{
    public partial class Form1 : Form
    {
        public Form1()
        {
            InitializeComponent();
            GetConnectionInfo();

        }

        private void GetConnectionInfo()
        {
            txtConnectionTestUri.Text = Properties.Settings.Default.WebServiceTestUri == "" ? txtConnectionTestUri.Text : Properties.Settings.Default.WebServiceTestUri;
            txtConnectionTestUserName.Text = Properties.Settings.Default.WebServiceTestUsername == "" ? txtConnectionTestUserName.Text : Properties.Settings.Default.WebServiceTestUsername;
            txtConnectionTestPassword.Text = Properties.Settings.Default.WebServiceTestPassword == "" ? txtConnectionTestPassword.Text : Properties.Settings.Default.WebServiceTestPassword;

            //txtWebServiceTestPassword.Text = Settings1.Default.WebServiceTestPassword == "" ? txtWebServiceTestPassword.Text : Settings1.Default.WebServiceTestPassword;
            //txtWebServiceTestUrl.Text = Settings1.Default.WebServiceTestUri == "" ? txtWebServiceTestUrl.Text : Settings1.Default.WebServiceTestUri;
            //txtWebServiceLiveUsername.Text = Settings1.Default.WebServiceLiveUsername;
            //txtWebServiceLivePassword.Text = Settings1.Default.WebServiceLivePassword;
            //txtWebServiceLiveUrl.Text = Settings1.Default.WebServiceLiveUri;

        }



        private void btnSaveConnectionTestInfo_Click(object sender, EventArgs e)
        {
            //AppSettings.Default.TestPassword = txtConnectionTestPassword.Text;
            //AppSettings.Default.TestUserName = txtConnectionTestUserName.Text;
            //Properties.Settings.Default.WebServiceTestUri = txtConnectionTestUri.Text;

            //AppSettings.Default.Save();
        }

        //    public DespatchIntegrationClient CreateClient()
        //    {
        //        var username = "";
        //        var password = "";
        //        var serviceuri = "";
        //        //if (rbtnTestSystem.Checked)
        //        //{
        //            username = txtConnectionTestUserName.Text;
        //            password = txtConnectionTestPassword.Text;
        //            serviceuri = txtConnectionTestUri.Text;
        //        //}
        //        //if (rbtnLiveSystem.Checked)
        //        //{
        //        //    username = txtWebServiceLiveUsername.Text;
        //        //    password = txtWebServiceLivePassword.Text;
        //        //    serviceuri = txtWebServiceLiveUrl.Text;
        //        //}

        //        var client = new DespatchIntegrationClient();
        //        client.Endpoint.Address = new System.ServiceModel.EndpointAddress(serviceuri);
        //        client.ClientCredentials.UserName.UserName = username;
        //        client.ClientCredentials.UserName.Password = password;

        //        return client;
        //    }

        private void btnSendDespatch_Click(object sender, EventArgs e)
        {
            try
            {
                var client = DespatchTasks.Instance.CreateClient();

                var despInfo = CreateDespatchInfo();
                var despInfos = new DespatchInfo[] { despInfo };
                var response = client.SendDespatch(despInfos);

                if (response.IsSucceded)
                {
                    MessageBox.Show(string.Format("İrsaliye No: {0} İrsaliye UUID: {1} ", response.Value[0].Number, response.Value[0].Id));
                    txtDespatchUUID.Text = response.Value[0].Id;
                    txtDespatchID.Text = response.Value[0].Number;
                }
                else
                {
                    MessageBox.Show(response.Message);
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message);
            }
        }

        public DespatchInfo CreateDespatchInfo()
        {
            DespatchInfo despInfo = new DespatchInfo();
            DespatchAdviceType despatch = new DespatchAdviceType();

            #region İrsaliye Genel Bilgileri
            //İrsaliye Numarası
            if (txtDespatchID.Text != "")
            {
                despatch.ID = new IDType { Value = txtDespatchID.Text };
            }
            //İrsaliye UUID(Ettn)
            if (txtDespatchUUID.Text != "")
            {
                despatch.UUID = new UUIDType { Value = txtDespatchUUID.Text };
            }
            else
            {
                var uuid = Guid.NewGuid().ToString();
                despatch.UUID = new UUIDType { Value = uuid };
            }

            despatch.IssueDate = new IssueDateType { Value = DateTime.Now };
            despatch.IssueTime = new IssueTimeType { Value = DateTime.Now };
            despatch.CopyIndicator = new CopyIndicatorType { Value = false };
            despatch.ProfileID = new ProfileIDType { Value = "TEMELIRSALIYE" };
            despatch.DespatchAdviceTypeCode = new DespatchAdviceTypeCodeType { Value = "SEVK" };
            despatch.Note = new NoteType[] { new NoteType { Value = "İş bu sevk irsaliyesi muhteviyatına 7 gün içerisinde itiraz edilmediği taktirde aynen kabul edilmiş sayılır." } };
            despatch.OrderReference = new OrderReferenceType[] { new OrderReferenceType { ID = new IDType { Value = "SIP2018012984" }, IssueDate = new IssueDateType { Value = DateTime.Now } } };
            despatch.LineCountNumeric = new LineCountNumericType { Value = 2 };
            #endregion

            #region Shipment
            despatch.Shipment = new ShipmentType
            {
                GoodsItem = new GoodsItemType[]{ new GoodsItemType{
                Description= new DescriptionType[]{
                    new DescriptionType{
                        Value="Taşıma özenli olmalı"
                                    }
                                                    },
                Item = new ItemType[]{
                                new ItemType{
                                    Name=new NameType1{
                                        Value= "asdasd"
                                                        }
                                            }
                                       },

                   InvoiceLine = new InvoiceLineType[]{ new InvoiceLineType{
                             ID= new IDType{ Value= "1"},
                             LineExtensionAmount = new LineExtensionAmountType{ currencyID="TRY", Value= 0},
                             InvoicedQuantity = new InvoicedQuantityType{ Value= 0, unitCode="NIU"},
                             Item= new ItemType{ Name = new NameType1{ Value="test"}} ,
                             Price = new PriceType{ PriceAmount=new PriceAmountType{Value= 0, currencyID="TRY"}}
                   }}

            }},
                ShipmentStage = new ShipmentStageType[]{
                                     new ShipmentStageType
                                     {
                                         DriverPerson = new PersonType[]{
                                              new PersonType
                                              {
                                                  FirstName = new FirstNameType
                                                  {
                                                      Value ="Yunus"
                                                  },
                                                  FamilyName = new FamilyNameType
                                                  {
                                                      Value = "Şimşek"
                                                  },
                                                  NationalityID = new NationalityIDType
                                                  {
                                                       Value = "11111111111"
                                                  }

                                              }
                                         }

                                     }

                },

                TransportHandlingUnit = new TransportHandlingUnitType[] {
                                           new TransportHandlingUnitType
                                           {
                                               TransportEquipment = new TransportEquipmentType[]{
                                                                        new TransportEquipmentType
                                                                        {
                                                                          ID = new IDType {Value ="66 RM 7737", schemeID="DORSEPLAKA"}
                                                                        }

                                               }

                                           }
                },
                ID = new IDType { Value = "1" },
                Delivery = new DeliveryType
                {
                    ID = new IDType { Value = "1" },
                    Despatch = new DespatchType
                    {
                        ActualDespatchDate = new ActualDespatchDateType
                        {
                            Value = DateTime.Now
                        },
                        ActualDespatchTime = new ActualDespatchTimeType
                        {
                            Value = DateTime.Now
                        }
                    },
                    CarrierParty = new PartyType
                    {
                        PartyName = new PartyNameType { Name = new NameType1 { Value = "Deneme" } },
                        PartyIdentification = new PartyIdentificationType[] { new PartyIdentificationType { ID = new IDType { schemeID = ("VKN"), Value = "1234567890" } } },
                        PostalAddress = new AddressType
                        {
                            Country = new CountryType
                            {
                                Name = new NameType1 { Value = "TÜRKİYE" },
                                IdentificationCode = new IdentificationCodeType
                                {
                                    Value = "TR"

                                }
                            },
                            CityName = new CityNameType
                            {
                                Value = "İSTANBUL"
                            },
                            StreetName = new StreetNameType { Value = "YILDIZ TEKNİK ÜNİ. DAVUTPAŞA KAMP. TEKNOPARK" },
                            CitySubdivisionName = new CitySubdivisionNameType { Value = "ESENLER" },
                            BuildingName = new BuildingNameType { Value = "B1" },
                            Room = new RoomType { Value = "401" }
                        }
                    }
                }
            };
            #endregion

            #region DespatchSupplierParty
            despatch.DespatchSupplierParty = new SupplierPartyType
            {
                Party = new PartyType
                {
                    PartyName = new PartyNameType { Name = new NameType1 { Value = "Malların Sevkiyatını Sağlayan Firma" } },
                    PartyIdentification = new PartyIdentificationType[] { new PartyIdentificationType { ID = new IDType { schemeID = "VKN", Value = txtGondericiVKN.Text } } },
                    PostalAddress = new AddressType
                    {
                        Country = new CountryType
                        {
                            Name = new NameType1 { Value = "TÜRKİYE" },
                            IdentificationCode = new IdentificationCodeType
                            {
                                Value = "TR"

                            }
                        },
                        CityName = new CityNameType
                        {
                            Value = "İSTANBUL"
                        },
                        StreetName = new StreetNameType { Value = "YILDIZ TEKNİK ÜNİ. DAVUTPAŞA KAMP. TEKNOPARK" },
                        CitySubdivisionName = new CitySubdivisionNameType { Value = "ESENLER" },
                        BuildingName = new BuildingNameType { Value = "B1" },
                        Room = new RoomType { Value = "401" }
                    }
                }
            };
            #endregion

            #region DeliveryCustomerParty
            despatch.DeliveryCustomerParty = new CustomerPartyType
            {
                Party = new PartyType
                {
                    PartyName = new PartyNameType { Name = new NameType1 { Value = "Malları Teslim Alan Firma" } },
                    PartyIdentification = new PartyIdentificationType[] { new PartyIdentificationType { ID = new IDType { schemeID = "VKN", Value = txtVKNAlici.Text } } },
                    PostalAddress = new AddressType
                    {
                        Country = new CountryType
                        {
                            Name = new NameType1 { Value = "TÜRKİYE" },
                            IdentificationCode = new IdentificationCodeType
                            {
                                Value = "TR"

                            }
                        },
                        CityName = new CityNameType
                        {
                            Value = "İSTANBUL"
                        },
                        StreetName = new StreetNameType { Value = "YILDIZ TEKNİK ÜNİ. DAVUTPAŞA KAMP. TEKNOPARK" },
                        CitySubdivisionName = new CitySubdivisionNameType { Value = "ESENLER" },
                        BuildingName = new BuildingNameType { Value = "B1" },
                        Room = new RoomType { Value = "401" }
                    }
                }
            };
            #endregion

            #region BuyerCustomerParty
            despatch.BuyerCustomerParty = new CustomerPartyType
            {
                Party = new PartyType
                {
                    PartyName = new PartyNameType { Name = new NameType1 { Value = "Malların Satın Alımını Sağlayan firma" } },
                    PartyIdentification = new PartyIdentificationType[] { new PartyIdentificationType { ID = new IDType { schemeID = "VKN", Value = txtVKNAlici.Text } } },
                    PostalAddress = new AddressType
                    {
                        Country = new CountryType
                        {
                            Name = new NameType1 { Value = "TÜRKİYE" },
                            IdentificationCode = new IdentificationCodeType
                            {
                                Value = "TR"

                            }
                        },
                        CityName = new CityNameType
                        {
                            Value = "İSTANBUL"
                        },
                        StreetName = new StreetNameType { Value = "YILDIZ TEKNİK ÜNİ. DAVUTPAŞA KAMP. TEKNOPARK" },
                        CitySubdivisionName = new CitySubdivisionNameType { Value = "ESENLER" },
                        BuildingName = new BuildingNameType { Value = "B1" },
                        Room = new RoomType { Value = "401" }
                    }
                }
            };
            #endregion

            #region SellerSupplierParty
            despatch.SellerSupplierParty = new SupplierPartyType
            {
                Party = new PartyType
                {
                    PartyName = new PartyNameType { Name = new NameType1 { Value = "Malları Satan Firma" } },
                    PartyIdentification = new PartyIdentificationType[] { new PartyIdentificationType { ID = new IDType { schemeID = "VKN", Value = txtVKNAlici.Text } } },
                    PostalAddress = new AddressType
                    {
                        Country = new CountryType
                        {
                            Name = new NameType1 { Value = "TÜRKİYE" },
                            IdentificationCode = new IdentificationCodeType
                            {
                                Value = "TR"

                            }
                        },
                        CityName = new CityNameType
                        {
                            Value = "İSTANBUL"
                        },
                        StreetName = new StreetNameType { Value = "YILDIZ TEKNİK ÜNİ. DAVUTPAŞA KAMP. TEKNOPARK" },
                        CitySubdivisionName = new CitySubdivisionNameType { Value = "ESENLER" },
                        BuildingName = new BuildingNameType { Value = "B1" },
                        Room = new RoomType { Value = "401" }
                    }
                }
            };
            #endregion

            /*
            #region OriginatorCustomerParty
            despatch.OriginatorCustomerParty = new CustomerPartyType
            {
                Party = new PartyType
                {
                    PartyName = new PartyNameType { Name = new NameType1 { Value = "Tüm sürecin başlamasını Sağlayan Alıcı " } },
                    PartyIdentification = new PartyIdentificationType[] { new PartyIdentificationType { ID = new IDType { schemeID = "VKN", Value = txtVKNAlici.Text } } },
                    PostalAddress = new AddressType
                    {
                        Country = new CountryType
                        {
                            Name = new NameType1 { Value = "TÜRKİYE" },
                            IdentificationCode = new IdentificationCodeType
                            {
                                Value = "TR"

                            }
                        },
                        CityName = new CityNameType
                        {
                            Value = "İSTANBUL"
                        },
                        StreetName = new StreetNameType { Value = "YILDIZ TEKNİK ÜNİ. DAVUTPAŞA KAMP. TEKNOPARK" },
                        CitySubdivisionName = new CitySubdivisionNameType { Value = "ESENLER" },
                        BuildingName = new BuildingNameType { Value = "B1" },
                        Room = new RoomType { Value = "401" }
                    }
                }
            };
            #endregion
            */
            #region DespatchLine
            despatch.DespatchLine = new DespatchLineType[] {
                new DespatchLineType {
                    //DocumentReference = new DocumentReferenceType[] { new DocumentReferenceType {  ID = new IDType { Value = "SIP2018012984" }, IssueDate= new IssueDateType { Value=DateTime.Now } }  },
                     OrderLineReference =  new OrderLineReferenceType { LineID = new LineIDType { Value = "1" } }  ,
                     ID = new IDType { Value= "1"  },
                     Shipment = new ShipmentType[] {
                                                 new ShipmentType {
                                                                    ID = new IDType { Value = "1" },
                                                                    Delivery  = new DeliveryType { ID= new IDType { Value="1" } }
                                                                    }
                                                },
                    Item = new ItemType {
                                                        Name = new NameType1 { Value = "1 LT Kola" } },
                                                        DeliveredQuantity = new DeliveredQuantityType { Value=10, unitCode=  "NIU"  },
                                                        OversupplyQuantity = new OversupplyQuantityType { Value = 0 , unitCode="NIU"},
                                                        OutstandingQuantity = new OutstandingQuantityType { Value = 0 , unitCode="NIU"},
                                                        OutstandingReason = new OutstandingReasonType[] { new OutstandingReasonType { Value = "Stok Yok"  } },
                                                        Note = new NoteType[] { new NoteType {   Value= "1 koli 2 gün sonra gelecek" } },

                }
            };
            #endregion
            despInfo.DespatchAdvice = despatch;

            return despInfo;
        }


        public void SaveDespatchXml(DespatchAdviceType despatch)
        {
            string xml = CreateXmlFromDespatch(despatch);
            using (FolderBrowserDialog sf = new FolderBrowserDialog())
            {
                sf.SelectedPath = Environment.GetFolderPath(Environment.SpecialFolder.Desktop);
                if (sf.ShowDialog() != DialogResult.OK)
                    return;

                string path = sf.SelectedPath;

                string fileName = string.Format("{0}.xml", despatch.ID.Value.ToString());

                string fullName = Path.Combine(path, fileName);

                File.WriteAllText(fullName, xml);
            }
        }

        public String CreateXmlFromDespatch(DespatchAdviceType despatch)
        {
            var rootAttribute = new XmlRootAttribute("DespatchAdvice")
            {
                Namespace = "urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2",
                IsNullable = false
            };


            XmlSerializer serializer = new XmlSerializer(typeof(DespatchAdviceType), rootAttribute);
            using (MemoryStream mstr = new MemoryStream())
            {
                serializer.Serialize(mstr, despatch, DespatchNamespaces);
                serializer.Serialize(mstr, despatch);
                return Encoding.UTF8.GetString(mstr.ToArray());
            }
        }

        //XML serialization sırasında namespace'lerin doğru yapılabilmesi için namespace tanımlamaları
        private static XmlSerializerNamespaces _DespatchNamespaces;
        public static XmlSerializerNamespaces DespatchNamespaces
        {
            get
            {
                if (_DespatchNamespaces == null)
                {
                    _DespatchNamespaces = new XmlSerializerNamespaces();
                    _DespatchNamespaces.Add("", "urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2");
                    _DespatchNamespaces.Add("ext", "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
                    _DespatchNamespaces.Add("cac", "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2");
                    _DespatchNamespaces.Add("cbc", "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2");
                    _DespatchNamespaces.Add("cctc", "urn:un:unece:uncefact:documentation:2");
                    _DespatchNamespaces.Add("ds", "http://www.w3.org/2000/09/xmldsig#");
                    _DespatchNamespaces.Add("qdt", "urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2");
                    _DespatchNamespaces.Add("ubltr", "urn:oasis:names:specification:ubl:schema:xsd:TurkishCustomizationExtensionComponents");
                    _DespatchNamespaces.Add("udt", "urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2");
                    _DespatchNamespaces.Add("xades", "http://uri.etsi.org/01903/v1.3.2#");
                    _DespatchNamespaces.Add("xsi", "http://www.w3.org/2001/XMLSchema-instance");
                    _DespatchNamespaces.Add("ns10", "urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2");
                    _DespatchNamespaces.Add("ns11", "urn:oasis:names:specification:ubl:schema:xsd:ReceiptAdvice-2");
                    _DespatchNamespaces.Add("ns8", "urn:oasis:names:specification:ubl:schema:xsd:Invoice-2");
                    _DespatchNamespaces.Add("ns9", "urn:oasis:names:specification:ubl:schema:xsd:ApplicationResponse-2");
                }
                return _DespatchNamespaces;

            }

        }


        private void btnSaveDespatchXml_Click(object sender, EventArgs e)
        {
            DespatchInfo despInfo = CreateDespatchInfo();
            DespatchAdviceType despatch = despInfo.DespatchAdvice;  //Metottan dönen irsaliye diye düşünebilrisiniz bunu. Aynı tipte veri dönüyor size 
            SaveXml(despatch);

        }

        public void SaveXml(DespatchAdviceType despatch)
        {
            string xml = CreateXmlFromDespatch(despatch);
            using (FolderBrowserDialog sf = new FolderBrowserDialog())
            {
                sf.SelectedPath = Environment.GetFolderPath(Environment.SpecialFolder.Desktop);
                if (sf.ShowDialog() != DialogResult.OK)
                    return;

                string path = sf.SelectedPath;

                string fileName = string.Format("{0}.xml", despatch.UUID.Value.ToString());

                string fullName = Path.Combine(path, fileName);

                File.WriteAllText(fullName, xml);
            }
        }

        private void btnCreateDespatchFromDespatchXML_Click(object sender, EventArgs e)
        {
            CreateDespatchFromXml();
        }

        public void CreateDespatchFromXml()
        {
            byte[] array;
            string filename;
            var rootAttribute = new XmlRootAttribute("DespatchAdvice")
            {
                Namespace = "urn:oasis:names:specification:ubl:schema:xsd:DespatchAdvice-2",
                IsNullable = false
            };

            var client = DespatchTasks.Instance.CreateClient();
            using (OpenFileDialog openFileDialog1 = new OpenFileDialog())
            {
                if (openFileDialog1.ShowDialog() != DialogResult.OK)
                    return;
                filename = openFileDialog1.FileName;
                array = File.ReadAllBytes(filename);

                XmlSerializer serializer = new XmlSerializer(typeof(DespatchAdviceType), rootAttribute);
                MemoryStream ms = new MemoryStream(array);

                var read = new XmlTextReader(ms);
                var obj = serializer.Deserialize(read);
                var despatch = obj as DespatchAdviceType;
                DespatchInfo[] despatcInfos = new DespatchInfo[] { new DespatchInfo { DespatchAdvice = despatch } };

                var response = client.SendDespatch(despatcInfos);
                if (response.IsSucceded)
                {
                    MessageBox.Show(string.Format("İrsaliye Gönderildi\n UUID:{0} \n ID:{1} ", response.Value[0].Id.ToString(), response.Value[0].Number.ToString()));
                    //txtSampleOutboxGuid.Text = response.Value[0].Id.ToString();
                    //txtSampleGuid.Text = response.Value[0].Id.ToString();
                }
                else
                {
                    MessageBox.Show(response.Message);
                }
            }

        }

        private void btnPreviewDespatch_Click(object sender, EventArgs e)
        {
            //var invoiceInfo = CreateInvoice();
            //var invoice = new InvoiceType[1];
            //invoice[0] = invoiceInfo.Invoice;
            //frmInvoiceViewer frm = new frmInvoiceViewer(invoice);
            //frm.Show();
        }

        private void btnCreateDespatchFromDespatchInfoXml_Click(object sender, EventArgs e)
        {
            byte[] array;
            string filename;
            try
            {
                using (OpenFileDialog openFileDialog1 = new OpenFileDialog())
                {
                    if (openFileDialog1.ShowDialog() != DialogResult.OK)
                        return;
                    filename = openFileDialog1.FileName;
                    array = File.ReadAllBytes(filename);

                    XmlSerializer serializer = new XmlSerializer(typeof(DespatchInfo));
                    MemoryStream ms = new MemoryStream(array);

                    var read = new XmlTextReader(ms);
                    var obj = serializer.Deserialize(read);
                    var despatch = obj as DespatchAdviceType;
                    DespatchInfo[] despatcInfos = new DespatchInfo[] { new DespatchInfo { DespatchAdvice = despatch } };
                    var client = DespatchTasks.Instance.CreateClient();
                    var response = client.SendDespatch(despatcInfos);
                    if (response.IsSucceded)
                    {
                        MessageBox.Show(string.Format("İrsaliye Gönderildi\n UUID:{0} \n ID:{1} ", response.Value[0].Id.ToString(), response.Value[0].Number.ToString()));
                        //txtSampleOutboxGuid.Text = response.Value[0].Id.ToString();
                        //txtSampleGuid.Text = response.Value[0].Id.ToString();
                    }
                    else
                    {
                        MessageBox.Show(response.Message);
                    }
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message);
            }
        }

        private void btn_QueryDespatchStatus_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();
            try
            {

                if (txtDespatchUUID.Text.Length == 36)
                {
                    var response = client.QueryOutboxDespatchStatus(new string[] { txtDespatchUUID.Text });
                    if (response.IsSucceded)
                    {
                        MessageBox.Show(response.Value[0].StatusCode.ToString());

                    }

                }
                else
                {
                    MessageBox.Show("Id girmediniz ya da girdiğiniz ID GUID formatına uygun değil ");
                }
            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
        }

        private void btn_GetInboxDespacthList_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();
            var response = client.GetInboxDespatchList(new InboxDespatchListQueryModel { PageIndex = 0, PageSize = 10 });

            var a = response.Value.Items[0].DespatchId;
        }

        private void btn_GetOutboxDespatchStatusWithLogs_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();

            try
            {
                if (txtDespatchUUID.Text.Length == 36)
                {
                    var response = client.GetOutboxDespatchStatusWithLogs(new string[] { txtDespatchUUID.Text });
                    if (response.IsSucceded)
                    {

                        StringBuilder sb = new StringBuilder();
                        sb.AppendLine(string.Format("StatusCode : {0}", response.Value[0].StatusCode.ToString()));
                        sb.AppendLine(string.Format("Status : {0}", response.Value[0].StatusEnum.ToString()));
                        sb.AppendLine(string.Format("UUID : {0}", response.Value[0].DespatchId.ToString()));
                        if (response.Value != null && response.Value[0].Logs != null)
                        {
                            for (int i = 0; i < response.Value[0].Logs.Length; i++)
                            {
                                sb.AppendLine(string.Format("Log {0} : {1}", i, response.Value[0].Logs[i].Message));
                            }

                        }
                        else
                        {
                            sb.AppendLine("Henüz bir Log yok. Bir süre sonra tekrar kontrol edebilirsiniz.");
                        }
                        MessageBox.Show(sb.ToString());
                    }
                    else
                    {
                        MessageBox.Show(response.Message);
                    }


                }
                else
                {
                    MessageBox.Show("Id girmediniz ya da girdiğiniz ID GUID formatına uygun değil ");
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message);
            }
        }

        private void btn_SaveDraftDespatch_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();
            var despInfo = CreateDespatchInfo();
            var despInfos = new DespatchInfo[] { despInfo };
            try
            {
                var response = client.SaveAsDraft(despInfos);

                if (response.IsSucceded)
                {
                    MessageBox.Show(string.Format("İrsaliye No: {0} İrsaliye UUID: {1} ", response.Value[0].Number, response.Value[0].Id));
                    txtDespatchUUID.Text = response.Value[0].Id;
                    txtDespatchID.Text = response.Value[0].Number;
                }
                else
                {
                    MessageBox.Show(response.Message);
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message);
            }

        }

        private void btnSendDraft_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();
            try
            {
                var response = client.SendDraft(new string[] { txtDespatchUUID.Text });
                if (response.IsSucceded)
                {
                    MessageBox.Show("Taslak gönderilmek üzere kuyruğa alındı");
                }
                else
                {
                    MessageBox.Show(response.Message);
                }

            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message);
            }

        }

        private void btnCancelDraft_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();
            try
            {
                var response = client.CancelDraft(new string[] { txtDespatchUUID.Text });
                if (response.IsSucceded)
                {
                    MessageBox.Show("Taslak İptal edildi");
                }
                else
                {
                    MessageBox.Show(response.Message);
                }

            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message);
            }
        }

        private void btnQueryInboxDespatchStatus_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();
            try
            {

                if (txtDespatchUUID.Text.Length == 36)
                {
                    var response = client.QueryInboxDespatchStatus(new string[] { txtDespatchUUID.Text });
                    if (response.IsSucceded)
                    {
                        MessageBox.Show(response.Value[0].StatusCode.ToString());
                    }

                }
                else
                {
                    MessageBox.Show("Id girmediniz ya da girdiğiniz ID GUID formatına uygun değil ");
                }
            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
        }

        private void btnGetInboxDespatchStatusWithLogs_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();

            try
            {
                if (txtDespatchUUID.Text.Length == 36)
                {
                    var response = client.GetInboxDespatchStatusWithLogs(new string[] { txtDespatchUUID.Text });
                    if (response.IsSucceded)
                    {

                        StringBuilder sb = new StringBuilder();
                        sb.AppendLine(string.Format("StatusCode : {0}", response.Value[0].StatusCode.ToString()));
                        sb.AppendLine(string.Format("Status : {0}", response.Value[0].StatusEnum.ToString()));
                        sb.AppendLine(string.Format("UUID : {0}", response.Value[0].DespatchId.ToString()));
                        if (response.Value != null && response.Value[0].Logs != null)
                        {
                            for (int i = 0; i < response.Value[0].Logs.Length; i++)
                            {
                                sb.AppendLine(string.Format("Log {0} : {1}", i, response.Value[0].Logs[i].Message));
                            }

                        }
                        else
                        {
                            sb.AppendLine("Henüz bir Log yok. Bir süre sonra tekrar kontrol edebilirsiniz.");
                        }
                        MessageBox.Show(sb.ToString());
                    }
                    else
                    {
                        MessageBox.Show(response.Message);
                    }


                }
                else
                {
                    MessageBox.Show("Id girmediniz ya da girdiğiniz ID GUID formatına uygun değil ");
                }
            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message);
            }
        }

        private void btnCompressedSendDespatch_Click(object sender, EventArgs e)
        {
            byte[] file = null;
            var client = DespatchTasks.Instance.CreateClient();
            try
            {
                var response = client.CompressedSendDespatch(new BinaryRequestData { Data = file, Hash = "" });

            }
            catch (Exception ex)
            {
                MessageBox.Show(ex.Message); ;
            }
        }

        private void btnGetInboxDespatch_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();
            StringBuilder sb = new StringBuilder();
            try
            {
                var response = client.GetInboxDespatch(txtDespatchUUID.Text);
                if (response.IsSucceded)
                {
                    sb.AppendLine("UUID: " + response.Value.DespatchAdvice.UUID.Value);
                    sb.AppendLine("ID: " + response.Value.DespatchAdvice.ID.Value);
                    sb.AppendLine("Gönderici: " + response.Value.DespatchAdvice.DespatchSupplierParty.Party.PartyName.Name.Value);
                }
            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
            MessageBox.Show(sb.ToString());

            //FrmInfo  frm = new FrmInfo(9);
            //frm.Show();
        }

        private void btnGetOutboxDespatch_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();
            StringBuilder sb = new StringBuilder();
            try
            {
                var response = client.GetOutboxDespatch(txtDespatchUUID.Text);
                if (response.IsSucceded)
                {

                    sb.AppendLine("UUID: " + response.Value.DespatchAdvice.UUID.Value);
                    sb.AppendLine("ID: " + response.Value.DespatchAdvice.ID.Value);
                    sb.AppendLine("Alıcı: " + response.Value.DespatchAdvice.BuyerCustomerParty.Party.PartyName.Name.Value);
                }
                else
                {
                    MessageBox.Show(response.Message);
                }
            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
            MessageBox.Show(sb.ToString());
        }

        private void btnGetInboxDespatches_Click(object sender, EventArgs e)
        {

            var client = DespatchTasks.Instance.CreateClient();
            try
            {
                var response = client.GetInboxDespatches(new InboxDespatchQueryModel { OnlyNewestDespatches = true });
                if (response.IsSucceded)
                {
                    MessageBox.Show(string.Format("Metot başarıyla çağrıldı. {0} kayıt döndü.", response.Value.TotalCount));
                }
                else
                {
                    MessageBox.Show(response.Message);
                }
            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
        }

        private void btnGetInboxDespatchList_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();
            try
            {
                var response = client.GetInboxDespatchList(new InboxDespatchListQueryModel { OnlyNewestDespatches = true });
                if (response.IsSucceded)
                {
                    MessageBox.Show(string.Format("Metot başarıyla çağrıldı. {0} kayıt döndü.", response.Value.TotalCount));
                }
                else
                {
                    MessageBox.Show(response.Message);
                }
            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
        }

        private void btnGetOutboxDespatchList_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();
            try
            {
                var response = client.GetOutboxDespatchList(new OutboxDespatchListQueryModel { PageIndex = 0 });
                if (response.IsSucceded)
                {
                    MessageBox.Show(string.Format("Metot başarıyla çağrıldı. {0} kayıt döndü.", response.Value.TotalCount));
                }
                else
                {
                    MessageBox.Show(response.Message);
                }
            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
        }

        private void btnGetOutboxDespatches_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();
            try
            {
                var response = client.GetOutboxDespatches(new OutboxDespatchQueryModel { PageIndex = 0 });
                if (response.IsSucceded)
                {
                    MessageBox.Show(string.Format("Metot başarıyla çağrıldı. {0} kayıt döndü.", response.Value.TotalCount));
                }
                else
                {
                    MessageBox.Show(response.Message);
                }
            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
        }

        private void btnSendDespatchResponse_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();
            try
            {
                var response = client.SendReceiptAdvice(new ReceiptAdviceInfo[] {
                new ReceiptAdviceInfo {
                    ReceiptAdviceLineInfos = new ReceiptAdviceLineInfo[] {
                        new ReceiptAdviceLineInfo {
                            LineId = "1",
                            ReceivedQuantity =9,
                            OversupplyQuantity =0,
                            RejectReason ="Delik",
                            RejectedQuantity =1,
                            RejectReasonCode = "",
                            ShortQuantity =1 } },
                            ActualDeliveryDate = new DateTime(2018,1,1),
                            DeliveryContactName ="asdasdas",
                            DespatchContactName ="asdasdasd",
                            InboxDespatchId = txtDespatchUUID.Text,
                            Note ="1 Adet Eksik 1 Adet de kusurlu olduğu tespit edilmiştir"
                }
            }

                );
                if (response.IsSucceded)
                {
                    MessageBox.Show("response gönderimi başarılı");
                }
                else
                {
                    MessageBox.Show(response.Message);
                }

            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
        }

        private void btnGetInboxDespatchPdf_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();

            try
            {
                if (txtDespatchUUID.Text == "")
                {
                    MessageBox.Show("Örnek UUID girmediniz");
                }
                else
                {
                    var response = client.GetInboxDespatchPdf(txtDespatchUUID.Text);
                    if (response.IsSucceded)
                    {

                        using (var stream = File.Create(Environment.GetFolderPath(Environment.SpecialFolder.Desktop) + "\\" + txtDespatchUUID.Text + ".pdf"))
                        {
                            stream.Write(response.Value.Items[0].Data, 0, response.Value.Items[0].Data.Length);
                            stream.Flush();
                            MessageBox.Show(Environment.GetFolderPath(Environment.SpecialFolder.Desktop) + "\\" + txtDespatchUUID.Text + ".pdf adresine kaydedildi");
                        }
                    }
                    else
                    {
                        MessageBox.Show(response.Message);
                    }
                }

            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }

        }

        private void GetInboxDespatchView_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();

            try
            {
                if (txtDespatchUUID.Text == "")
                {
                    MessageBox.Show("Örnek UUID girmediniz");
                }
                else
                {
                    var response = client.GetInboxDespatchView(txtDespatchUUID.Text);
                    if (response.IsSucceded)
                    {

                        System.IO.File.WriteAllText(Environment.GetFolderPath(Environment.SpecialFolder.Desktop) + "\\" + txtDespatchUUID.Text + ".html", response.Value.Html);
                        MessageBox.Show(Environment.GetFolderPath(Environment.SpecialFolder.Desktop) + "\\" + txtDespatchUUID.Text + ".html adresine kaydedildi");
                    }
                    else
                    {
                        MessageBox.Show(response.Message);
                    }
                }

            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
        }

        private void btnGetInboxDespatchData_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();

            try
            {
                if (txtDespatchUUID.Text == "")
                {
                    MessageBox.Show("Örnek UUID girmediniz");
                }
                else
                {
                    var response = client.GetInboxDespatchesData(new InboxDespatchQueryModel { DespatchIds = new string[] { txtDespatchUUID.Text } });
                    if (response.IsSucceded)
                    {
                        using (var stream = File.Create(Environment.GetFolderPath(Environment.SpecialFolder.Desktop) + "\\" + txtDespatchUUID.Text + ".xml"))
                        {
                            stream.Write(response.Value.Items[0].Data, 0, response.Value.Items[0].Data.Length);
                            stream.Flush();
                            MessageBox.Show(Environment.GetFolderPath(Environment.SpecialFolder.Desktop) + "\\" + txtDespatchUUID.Text + ".xml adresine kaydedildi");
                        }
                    }
                    else
                    {
                        MessageBox.Show(response.Message);
                    }
                }

            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
        }

        private void btnGetOutboxDespatchPdf_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();

            try
            {
                if (txtDespatchUUID.Text == "")
                {
                    MessageBox.Show("Örnek UUID girmediniz");
                }
                else
                {
                    var response = client.GetOutboxDespatchPdf(txtDespatchUUID.Text);
                    if (response.IsSucceded)
                    {
                        using (var stream = File.Create(Environment.GetFolderPath(Environment.SpecialFolder.Desktop) + "\\" + txtDespatchUUID.Text + ".pdf"))
                        {
                            stream.Write(response.Value.Items[0].Data, 0, response.Value.Items[0].Data.Length);
                            stream.Flush();
                            MessageBox.Show(Environment.GetFolderPath(Environment.SpecialFolder.Desktop) + "\\" + txtDespatchUUID.Text + ".pdf adresine kaydedildi");
                        }
                    }
                    else
                    {
                        MessageBox.Show(response.Message);
                    }
                }

            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }

        }

        private void btnGetOutboxDespatchData_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();

            try
            {
                if (txtDespatchUUID.Text == "")
                {
                    MessageBox.Show("Örnek UUID girmediniz");
                }
                else
                {
                    var response = client.GetOutboxDespatchesData(new OutboxDespatchQueryModel { DespatchIds = new string[] { txtDespatchUUID.Text } });
                    if (response.IsSucceded)
                    {
                        using (var stream = File.Create(Environment.GetFolderPath(Environment.SpecialFolder.Desktop) + "\\" + txtDespatchUUID.Text + ".xml"))
                        {
                            stream.Write(response.Value.Items[0].Data, 0, response.Value.Items[0].Data.Length);
                            stream.Flush();
                            MessageBox.Show(Environment.GetFolderPath(Environment.SpecialFolder.Desktop) + "\\" + txtDespatchUUID.Text + ".xml adresine kaydedildi");
                        }
                    }
                    else
                    {
                        MessageBox.Show(response.Message);
                    }
                }

            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
        }

        private void btnGetOutboxDespatchView_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();

            try
            {
                if (txtDespatchUUID.Text == "")
                {
                    MessageBox.Show("Örnek UUID girmediniz");
                }
                else
                {
                    var response = client.GetOutboxDespatchView(txtDespatchUUID.Text);
                    if (response.IsSucceded)
                    {
                        System.IO.File.WriteAllText(Environment.GetFolderPath(Environment.SpecialFolder.Desktop) + "\\" + txtDespatchUUID.Text + ".html", response.Value.Html);
                        MessageBox.Show(Environment.GetFolderPath(Environment.SpecialFolder.Desktop) + "\\" + txtDespatchUUID.Text + ".html adresine kaydedildi");
                    }
                    else
                    {
                        MessageBox.Show(response.Message);
                    }
                }

            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
        }

        private void GetOutboxDespatchesList_Click(object sender, EventArgs e)
        {
            var client = DespatchTasks.Instance.CreateClient();
            var response = client.GetOutboxDespatchList(new OutboxDespatchListQueryModel { IsOnlyNewReceiptAdvice = true });


        }

        private void btnInboxReponseStatus_Click(object sender, EventArgs e)
        {
            try
            {
                var client = DespatchTasks.Instance.CreateClient();
                var response = client.QueryReceiptAdviceStatus(new string[] { txtDespatchUUID.Text });
                if (response.IsSucceded)
                {
                    MessageBox.Show(string.Format("Durum Kodu : {0} Durum : {1}", response.Value[0].StatusCode, response.Value[0].Status.ToString()));
                }
            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }


        }

        private void btnGetInboxReceiptAdvices_Click(object sender, EventArgs e)
        {

            try
            {
                var client = DespatchTasks.Instance.CreateClient();
                var response = client.GetOutboxDespatchList(new OutboxDespatchListQueryModel { IsOnlyNewReceiptAdvice = true, PageSize = 10, PageIndex = 0 });
                if (response.IsSucceded)
                {
                    if (response.Value.Items.Length > 0)
                    {
                        StringBuilder sb = new StringBuilder();

                        for (int i = 0; i < response.Value.Items.Length; i++)
                        {
                            for (int j = 0; j < response.Value.Items[i].Lines.Length; j++)
                            {
                                var line = response.Value.Items[i].Lines[j];
                                sb.Append("Satir" + j + ":");
                                sb.Append("DespatchLineId : ");
                                sb.Append(line.DespatchLineId);
                                sb.Append(" | DespatchId : ");
                                sb.Append(line.DespatchId);
                                sb.Append(" | DeliveredQuantity : ");
                                sb.Append(line.DeliveredQuantity);
                                sb.Append(" | ReceivedQuantity : ");
                                sb.Append(line.ReceivedQuantity);
                                sb.Append(" | OversupplyQuantity : ");
                                sb.Append(line.OversupplyQuantity);
                                sb.Append(" | RejectedQuantity : ");
                                sb.Append(line.RecejtedQuantity);
                                sb.Append(" | RejectReasonCode : ");
                                sb.Append(line.DeliveredQuantity);
                                sb.Append(" | DeliveredQuantity : ");
                                sb.Append(line.DeliveredQuantity);
                                sb.Append(" | ItemName : ");
                                sb.Append(line.ItemName);
                                sb.AppendLine();

                            }


                        }
                        MessageBox.Show(sb.ToString());
                    }

                }
                else
                {
                    MessageBox.Show(response.Message);
                }

            }

            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }

        }


        private void SetDespatchReceiptAdvicesTaken_Click(object sender, EventArgs e)
        {
            try
            {
                var client = DespatchTasks.Instance.CreateClient();
                var response = client.SetDespatchReceiptAdvicesTaken(new string[] { txtDespatchUUID.Text });

                if (response.IsSucceded)
                {
                    MessageBox.Show("İrsaliye ynaıtını aldığınıza dair sisteme bilgi gönderdiniz. Bu irsaliye yanıtı Yeni durumdaki irsaliye yanıtlarını çağırdığınızda artık gelmeyecek");
                }
                else
                {
                    MessageBox.Show(response.Message);
                }
            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
        }
    }
}