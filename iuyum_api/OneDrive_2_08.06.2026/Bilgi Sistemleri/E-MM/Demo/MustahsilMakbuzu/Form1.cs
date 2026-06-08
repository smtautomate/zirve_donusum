using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Windows.Forms;
using MustahsilMakbuzu.MustahsilWS;

namespace MustahsilMakbuzu
{
    public partial class Form1 : Form
    {
        public Form1()
        {
            InitializeComponent();
        }

        private void Btn_SendVoucher_Click(object sender, EventArgs e)
        {
            var client = CreateClient();
            var makbuz = CreateCreditNote();
            var response = client.SendProducerReceipt(
                new ProducerReceiptInfo[] {
                new ProducerReceiptInfo {
                                        Receipt = makbuz
                                        }
                }
                ); ;

            if (response.IsSucceded)
            {
                MessageBox.Show(string.Format("Request Başarıyla işlendi. UUID:{0} ID:{1}",response.Value[0].DocumentId,response.Value[0].ReceiptNumber));
                txtUUID.Text = response.Value[0].DocumentId;
            }
            else
            {
                MessageBox.Show(string.Format("Hata: {0}", response.Message));
            }


        }

        public CreditNoteType CreateCreditNote()
        {
            var makbuz = new CreditNoteType
            {

                UBLVersionID = new UBLVersionIDType { Value = "2.1" }, //Sabit Değer
                CustomizationID = new CustomizationIDType { Value = "TR1.2" },  //Sabit Değer
                ProfileID = new ProfileIDType { Value = "e-Arşiv Belge" },   //Sabit Değer
                ID = new IDType { Value = txtID.Text }, //BelgeNo 16 hane. 3 hane prefix, 4 hane belge tarihinin yılı. 9 hane 0000000001'den başlayarak sıralı numara. Boş bırakıldığında sistem varsayılan seriden sıradaki nuamrayı verir. 
                CopyIndicator = new CopyIndicatorType { Value = false }, //Sabit değer
                UUID = new UUIDType { Value = txtUUID.Text }, //Makbuza ait unique Id, boş bırakıldığında sistem üretir ve response'ta geri döner. Dolu olduğunda yazılımcınınki kullanılır.
                IssueDate = new IssueDateType { Value = dateTimePicker1.Value.Date }, //Belge Tarihi
                IssueTime = new IssueTimeType { Value = dateTimePicker2.Value }, //Belge saati
                LineCountNumeric = new LineCountNumericType { Value = Convert.ToDecimal(txtLineCount.Text) }, //Belgedeki Mal hizmet kalemi sayısı (CreditNoteLine sayısı)
                Note = new NoteType[] { new NoteType { Value = txtMakbuzNot1.Text }, new NoteType { Value = txtMakbuzNot2.Text }, new NoteType { Value = txtMakbuzNot3.Text } },
                //Belgeyi oluşturan firma bilgileri 
                AccountingSupplierParty = new SupplierPartyType
                {
                    Party = new PartyType
                    {
                        PartyIdentification = new PartyIdentificationType[] {
                            new PartyIdentificationType {
                                ID = new IDType {
                                    schemeID = "VKN", //Gönderici 11 haneli bir TCKN'ye sahipse bu alan "TCKN" olmalıdır ve bu eleman altındaki Person elemanında firstname ve FamiliyName dolu olmalıdır.
                                    Value = txtSenderVKN.Text, //Gönderici VKN ya da TCKN numarası

                                }
                            },
                            new PartyIdentificationType {
                                ID = new IDType {
                                    schemeID = "MERSISNO", //Mersis no ve benzeri bazı değerler için bu alan kullanılabilir. Kod listelerinde hangi kodların kullanılabileceğine dair bir liste bulunmaktadır. 
                                    Value = "123456789", //İlgili numara

                                }
                            },
                        },
                        PartyName = new PartyNameType { Name = new NameType1 { Value = txtSenderName.Text } }, //Firma Ünvanı
                        PostalAddress = new AddressType
                        {
                            Country = new CountryType { Name = new NameType1 { Value = "Türkiye" } }, //Ülke. Yurtdışına belge düzenlenme ihtimali yoksa sabit değer olarka kalabilir 
                            CityName = new CityNameType { Value = txtSenderCity.Text },//Firma Şehir
                            CitySubdivisionName = new CitySubdivisionNameType { Value = txtSenderCitySubdivision.Text }, //Firma İlçe
                            StreetName = new StreetNameType { Value = txtSenderAddress.Text }, //Gönderici firma adresi.

                        },

                    },
                },


                //Belgenin alıcısı firmaya(müşteriye) ait bilgileri 
                AccountingCustomerParty = new CustomerPartyType
                {
                    Party = new PartyType
                    {
                        PartyIdentification = new PartyIdentificationType[] {
                            new PartyIdentificationType {
                                ID = new IDType {
                                    schemeID = "VKN", //Alıcı 11 haneli bir TCKN'ye sahipse bu alan "TCKN" olmalıdır ve bu eleman altındaki Person elemanında firstname ve FamiliyName dolu olmalıdır.
                                    Value = txtCustomerVKN.Text, //Gönderici VKN ya da TCKN numarası

                                }
                            },

                        },
                        PartyName = new PartyNameType { Name = new NameType1 { Value = txtCustomerName.Text } }, //Alıcı Ünvanı
                        PostalAddress = new AddressType
                        {
                            Country = new CountryType { Name = new NameType1 { Value = "Türkiye" } }, //Ülke. Yurtdışına belge düzenlenme ihtimali yoksa sabit değer olarka kalabilir 
                            CityName = new CityNameType { Value = txtCustomerCity.Text },//Firma Şehir
                            CitySubdivisionName = new CitySubdivisionNameType { Value = txtCustomerCitySubdivision.Text }, //Firma İlçe
                            StreetName = new StreetNameType { Value = txtCustomerAddress.Text }, //Gönderici firma adresi.

                        },

                    },
                },

                //Teslim Tarihi
                Delivery = new DeliveryType[] { new DeliveryType { ActualDeliveryDate = new ActualDeliveryDateType { Value = dateTimePicker3.Value.Date } } },


                //Belge alt toplamları
                LegalMonetaryTotal = new MonetaryTotalType
                {

                    LineExtensionAmount = new LineExtensionAmountType { currencyID = "TRY", Value = Convert.ToDecimal(txtLine1SatirToplam.Text) }, //Vergiler ve İskonto Hariç toplam tutar, Virgülden sonra 2 haneden fazla olmamalı
                    TaxInclusiveAmount = new TaxInclusiveAmountType { currencyID = "TRY", Value = Convert.ToDecimal(txtLine1SatirToplam.Text)- Convert.ToDecimal(txtLine1StopajTutar.Text)  - Convert.ToDecimal(txtLine1SGKTutar.Text)- Convert.ToDecimal(txtLine1BorsaTutar.Text) }, //Vergiler Dahil Toplam Tutar, Virgülden sonra 2 haneden fazla olmamalı
                    TaxExclusiveAmount = new TaxExclusiveAmountType { currencyID = "TRY", Value = 0 }, //Vergiler Hariç iskontolu Toplam Tutar, Virgülden sonra 2 haneden fazla olmamalı
                    AllowanceTotalAmount = new AllowanceTotalAmountType { currencyID = "TRY", Value = 0 }, //İskonto Toplamı , Virgülden sonra 2 haneden fazla olmamalı
                    PayableAmount = new PayableAmountType { currencyID = "TRY", Value = Convert.ToDecimal(txtLine1SatirToplam.Text) - Convert.ToDecimal(txtLine1StopajTutar.Text) - Convert.ToDecimal(txtLine1SGKTutar.Text) - Convert.ToDecimal(txtLine1BorsaTutar.Text) }, //Ödenecek Toplam Tutar , Virgülden sonra 2 haneden fazla olmamalı

                },


                //Belgedeki mal/hizmet kalemleri
                CreditNoteLine = new CreditNoteLineType[]{ // demoda 1 satır olarak varsayılmıştır. Bu arttırılabilir ya da azaltılabilir 
                                                         new CreditNoteLineType{
                                                              ID= new IDType{ Value=txtLine1ID.Text }, //Sıra no
                                                              CreditedQuantity = new CreditedQuantityType{ unitCode=cmbLine1UnitCode.Text, Value= Convert.ToDecimal(txtLine1Quantity.Text) }, //Mal Hizmet Miktarı, UnitCode C62=Adet, KGM= Kilogram, LTR = Litre, Diğerleri için bilgi alınız.  
                                                              Item = new ItemType{ Name= new NameType1{ Value= txtLine1Name.Text }, Description = new DescriptionType{ Value=txtLine1Description.Text }  }, //Mal Hizmet adı ve diğer açıklamalr 
                                                              Price = new PriceType{ PriceAmount= new PriceAmountType{ currencyID="TRY",Value= Convert.ToDecimal(txtLine1Price.Text)} },//Ürün birim fiyatı
                                                              LineExtensionAmount = new LineExtensionAmountType{ currencyID="TRY", Value=Convert.ToDecimal(txtLine1SatirToplam.Text) }, //Satır Toplam Tutarı
                                                               TaxTotal = new TaxTotalType[]{
                                                                   new TaxTotalType
                                                                   {

                                                                        TaxAmount = new TaxAmountType{ currencyID="TRY", Value= Convert.ToDecimal(txtLine1StopajTutar.Text) +Convert.ToDecimal(txtLine1SGKTutar.Text) +Convert.ToDecimal(txtLine1MeraTutar.Text) +Convert.ToDecimal(txtLine1BorsaTutar.Text) },
                                                                        TaxSubtotal = new TaxSubtotalType[]
                                                                        {
                                                                            new TaxSubtotalType
                                                                            {
                                                                                 TaxAmount =  new TaxAmountType{ currencyID="TRY", Value= Convert.ToDecimal(txtLine1StopajTutar.Text)  },
                                                                                  Percent = new PercentType1{ Value= Convert.ToDecimal(txtLine1StopajOran.Text) },
                                                                                   TaxableAmount = new TaxableAmountType{ currencyID="TRY", Value=Convert.ToDecimal(txtLine1SatirToplam.Text)  },
                                                                                    TaxCategory = new TaxCategoryType
                                                                                    {
                                                                                         TaxScheme = new TaxSchemeType
                                                                                         {
                                                                                              Name = new NameType1{ Value=  "G.V.Stopaj" },
                                                                                               TaxTypeCode = new TaxTypeCodeType{ Value= "0003" }
                                                                                         }
                                                                                    }

                                                                            },

                                                                            //new TaxSubtotalType
                                                                            //{
                                                                            //     TaxAmount =  new TaxAmountType{ currencyID="TRY", Value= Convert.ToDecimal(txtLine1MeraTutar.Text)  },
                                                                            //      Percent = new PercentType1{ Value= Convert.ToDecimal(txtLine1MeraOran.Text) },
                                                                            //       TaxableAmount = new TaxableAmountType{ currencyID="TRY", Value=Convert.ToDecimal(txtLine1SatirToplam.Text)  },
                                                                            //        TaxCategory = new TaxCategoryType
                                                                            //        {
                                                                            //             TaxScheme = new TaxSchemeType
                                                                            //             {
                                                                            //                  Name = new NameType1{ Value=  "Mera Fonu" },
                                                                            //                   TaxTypeCode = new TaxTypeCodeType{ Value= "9040" }
                                                                            //             }
                                                                            //        }

                                                                            //},

                                                                             new TaxSubtotalType
                                                                            {
                                                                                 TaxAmount =  new TaxAmountType{ currencyID="TRY", Value= Convert.ToDecimal(txtLine1BorsaTutar.Text)  },
                                                                                  Percent = new PercentType1{ Value= Convert.ToDecimal(txtLine1BorsaOran.Text) },
                                                                                   TaxableAmount = new TaxableAmountType{ currencyID="TRY", Value=Convert.ToDecimal(txtLine1SatirToplam.Text)  },
                                                                                    TaxCategory = new TaxCategoryType
                                                                                    {
                                                                                         TaxScheme = new TaxSchemeType
                                                                                         {
                                                                                              Name = new NameType1{ Value=  "Borsa Tescil Ücreti" },
                                                                                               TaxTypeCode = new TaxTypeCodeType{ Value= "8001" }
                                                                                         }
                                                                                    }

                                                                            },

                                                                               new TaxSubtotalType
                                                                            {
                                                                                 TaxAmount =  new TaxAmountType{ currencyID="TRY", Value= Convert.ToDecimal(txtLine1SGKTutar.Text)  },
                                                                                  Percent = new PercentType1{ Value= Convert.ToDecimal(txtLine1SGKOran.Text) },
                                                                                   TaxableAmount = new TaxableAmountType{ currencyID="TRY", Value=Convert.ToDecimal(txtLine1SatirToplam.Text)  },
                                                                                    TaxCategory = new TaxCategoryType
                                                                                    {
                                                                                         TaxScheme = new TaxSchemeType
                                                                                         {
                                                                                              Name = new NameType1{ Value=  "SGK Prim Kesintisi" },
                                                                                               TaxTypeCode = new TaxTypeCodeType{ Value= "SGK_PRIM" }
                                                                                         }
                                                                                    }

                                                                            },

                                                                        }



                                                                   }

                                                               }

                                                         },

                                                     },



                //Belgeye ait vergiler
                TaxTotal = new TaxTotalType[]{
                                                                   new TaxTotalType
                                                                   {

                                                                        TaxAmount = new TaxAmountType{ currencyID="TRY", Value= Convert.ToDecimal(txtLine1StopajTutar.Text) +Convert.ToDecimal(txtLine1SGKTutar.Text) +Convert.ToDecimal(txtLine1MeraTutar.Text) +Convert.ToDecimal(txtLine1BorsaTutar.Text) },

                                                                        //TaxSubtotal her farklı vergi kodu, oranı veya vergi muafiyet açıklaması için çoklanacaktır. Örneğin aynı belgede hem %18 hem %1 vergi olur ise 1 TaxTotal altında 2 TaxSubTotal olacaktır 
                                                                       TaxSubtotal = new TaxSubtotalType[]
                                                                        {
                                                                            new TaxSubtotalType
                                                                            {
                                                                                 TaxAmount =  new TaxAmountType{ currencyID="TRY", Value= Convert.ToDecimal(txtLine1StopajTutar.Text)  },
                                                                                  Percent = new PercentType1{ Value= Convert.ToDecimal(txtLine1StopajOran.Text) }, //Oranlar aynı olduğu için 1 tanesi baz alındı. Oranlar farklılaşırsa her oran için farklı bir subtotal oluşturulmalıdır. 
                                                                                   TaxableAmount = new TaxableAmountType{ currencyID="TRY", Value=Convert.ToDecimal(txtLine1SatirToplam.Text)   },
                                                                                    TaxCategory = new TaxCategoryType
                                                                                    {
                                                                                         TaxScheme = new TaxSchemeType
                                                                                         {
                                                                                              Name = new NameType1{ Value=  "G.V.Stopaj" },
                                                                                               TaxTypeCode = new TaxTypeCodeType{ Value= "0003" }
                                                                                         }
                                                                                    }

                                                                            },

                                                                            //new TaxSubtotalType
                                                                            //{
                                                                            //     TaxAmount =  new TaxAmountType{ currencyID="TRY", Value= Convert.ToDecimal(txtLine1MeraTutar.Text) },
                                                                            //      Percent = new PercentType1{ Value= Convert.ToDecimal(txtLine1MeraOran.Text) },
                                                                            //       TaxableAmount = new TaxableAmountType{ currencyID="TRY", Value=Convert.ToDecimal(txtLine1SatirToplam.Text)   },
                                                                            //        TaxCategory = new TaxCategoryType
                                                                            //        {
                                                                            //             TaxScheme = new TaxSchemeType
                                                                            //             {
                                                                            //                  Name = new NameType1{ Value=  "Mera Fonu" },
                                                                            //                   TaxTypeCode = new TaxTypeCodeType{ Value= "9040" }
                                                                            //             }
                                                                            //        }

                                                                            //},

                                                                             new TaxSubtotalType
                                                                            {
                                                                                 TaxAmount =  new TaxAmountType{ currencyID="TRY", Value=Convert.ToDecimal(txtLine1BorsaTutar.Text)  },
                                                                                  Percent = new PercentType1{ Value= Convert.ToDecimal(txtLine1BorsaOran.Text) },
                                                                                   TaxableAmount = new TaxableAmountType{ currencyID="TRY", Value=Convert.ToDecimal(txtLine1SatirToplam.Text) },
                                                                                    TaxCategory = new TaxCategoryType
                                                                                    {
                                                                                         TaxScheme = new TaxSchemeType
                                                                                         {
                                                                                              Name = new NameType1{ Value=  "Borsa Tescil Ücreti" },
                                                                                               TaxTypeCode = new TaxTypeCodeType{ Value= "8001" }
                                                                                         }
                                                                                    }

                                                                            },

                                                                               new TaxSubtotalType
                                                                            {
                                                                                 TaxAmount =  new TaxAmountType{ currencyID="TRY", Value= Convert.ToDecimal(txtLine1SGKTutar.Text)  },
                                                                                  Percent = new PercentType1{ Value= Convert.ToDecimal(txtLine1SGKOran.Text) },
                                                                                   TaxableAmount = new TaxableAmountType{ currencyID="TRY", Value=Convert.ToDecimal(txtLine1SatirToplam.Text)   },
                                                                                    TaxCategory = new TaxCategoryType
                                                                                    {
                                                                                         TaxScheme = new TaxSchemeType
                                                                                         {
                                                                                              Name = new NameType1{ Value=  "SGK Prim Kesintisi" },
                                                                                               TaxTypeCode = new TaxTypeCodeType{ Value= "SGK_PRIM" }
                                                                                         }
                                                                                    }

                                                                            },

                                                                        }



                                                                   }

                                                               }


            };
            return makbuz;

        }
        public ProducerReceiptIntegrationClient CreateClient()
        {
            var client = new ProducerReceiptIntegrationClient();
            client.ClientCredentials.UserName.UserName = "Uyumsoft";
            client.ClientCredentials.UserName.Password = "Uyumsoft";
            return client;


        }

        private void BtnCreateNewUUID_Click(object sender, EventArgs e)
        {
            txtUUID.Text=  Guid.NewGuid().ToString();
        }


        private void BtnAddNewLine_Click_1(object sender, EventArgs e)
        {
            //henüz yapılmadı
        }

        private void BtnQueryDocumentStatus_Click(object sender, EventArgs e)
        {
            var client = CreateClient();
            try
            {
                var response = client.QueryProducerReceiptStatus(new string[] { txtUUID.Text });
                if (response.IsSucceded)
                {
                    MessageBox.Show(string.Format("Belge No:{0} , Belge Durumu:{1}, Durum Kodu:{2}", response.Value[0].ReceiptNumber, response.Value[0].Status, response.Value[0].StatusCode));
                }
                else
                {
                    MessageBox.Show(response.Message);
                }
            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message) ;
            }
           
        }

        private void BtnViewVoucher_Click(object sender, EventArgs e)
        {
            var makbuz = CreateCreditNote();
            frmCreditNoteViewer frm = new frmCreditNoteViewer(makbuz);
            frm.Show();
        }
    }
}
