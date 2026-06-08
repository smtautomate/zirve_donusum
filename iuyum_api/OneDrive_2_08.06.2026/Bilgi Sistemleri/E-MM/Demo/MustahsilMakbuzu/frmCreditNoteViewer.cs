using MustahsilMakbuzu.MustahsilWS;
using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.IO;
using System.Linq;
using System.Text;
using System.Windows.Forms;
using System.Xml;
using System.Xml.Serialization;
using System.Xml.Xsl;

namespace MustahsilMakbuzu
{
    public partial class frmCreditNoteViewer : Form
    {
        public frmCreditNoteViewer()
        {
            InitializeComponent();
            
        }
        public frmCreditNoteViewer(MustahsilWS.CreditNoteType makbuz)
        {
            InitializeComponent();
            ShowCreditNote(makbuz);
        }

      
        public void ShowCreditNote(CreditNoteType makbuz)
        {

            try
            {
                var xslt = string.Empty;

                if (makbuz.AdditionalDocumentReference != null)
                {
                    AttachmentType attachment = null;
                    DocumentReferenceType doc;
                    byte[] xsltObject = null;

                    for (int i = 0; i < makbuz.AdditionalDocumentReference.Length; i++)
                    {
                        doc = makbuz.AdditionalDocumentReference[i];
                        attachment = doc.Attachment;
                        if (attachment != null && attachment.EmbeddedDocumentBinaryObject.filename != null)
                        {
                            string fileName = attachment.EmbeddedDocumentBinaryObject.filename;
                            if (Path.GetExtension(fileName) == ".xslt" || Path.GetExtension(fileName) == ".XSLT")
                            {
                                xsltObject = attachment.EmbeddedDocumentBinaryObject.Value;
                            }
                        }

                    }


                    if (xsltObject != null)
                    {
                      
                        using (var stream = new MemoryStream(xsltObject))
                        {
                            stream.Seek(0, SeekOrigin.Begin);

                            using (var reader = new StreamReader(stream))
                            {

                                xslt = reader.ReadToEnd();

                                var rootAttribute = new XmlRootAttribute("CreditNote")
                                {
                                    Namespace = "urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2",
                                    IsNullable = false
                                };

                                XmlSerializer serializer = new XmlSerializer(typeof(CreditNoteType), rootAttribute);
                                using (MemoryStream mstr = new MemoryStream())
                                {
                                    serializer.Serialize(mstr, makbuz, CreditNoteNamespaces);

                                    string xml = Encoding.UTF8.GetString(mstr.ToArray());
                                    webBrowser1.DocumentText = TransformXMLToHTML(xml, xslt);
                                }
                            }
                        }
                    }
                    else
                    {
                        MessageBox.Show("Xslt Dosyası Bulunamadı");
                    }
                }
                else
                {

                    MessageBox.Show("Belge içerisinde Xslt(AdditionalDocumentReference alanında xslt tipinde bir dosya) yok. Varsayılan Xslt üzerinden görüntüleme yapılacaktır.");
                    xslt = Properties.Resources.Mustahsil;
                    var rootAttribute = new XmlRootAttribute("CreditNote")
                    {
                        Namespace = "urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2",
                        IsNullable = false
                    };

                    XmlSerializer serializer = new XmlSerializer(typeof(CreditNoteType), rootAttribute);
                    using (MemoryStream mstr = new MemoryStream())
                    {
                        serializer.Serialize(mstr, makbuz, CreditNoteNamespaces);

                        string xml = Encoding.UTF8.GetString(mstr.ToArray());
                        var html = TransformXMLToHTML(xml, xslt);
                        webBrowser1.DocumentText = html;
                    }

                }
            }
            catch (Exception ex)
            {

                MessageBox.Show("Hata?" + ex.Message);
            }
        }



        //XML serialization sırasında namespace'lerin doğru yapılabilmesi için namespace tanımlamaları
        private static XmlSerializerNamespaces _CreditNoteNamespaces;
        public static XmlSerializerNamespaces CreditNoteNamespaces
        {
            get
            {
                if (_CreditNoteNamespaces == null)
                {
                    _CreditNoteNamespaces = new XmlSerializerNamespaces();
                    _CreditNoteNamespaces.Add("", "urn:oasis:names:specification:ubl:schema:xsd:CreditNote-2");
                    _CreditNoteNamespaces.Add("ext", "urn:oasis:names:specification:ubl:schema:xsd:CommonExtensionComponents-2");
                    _CreditNoteNamespaces.Add("cac", "urn:oasis:names:specification:ubl:schema:xsd:CommonAggregateComponents-2");
                    _CreditNoteNamespaces.Add("cbc", "urn:oasis:names:specification:ubl:schema:xsd:CommonBasicComponents-2");
                    _CreditNoteNamespaces.Add("cctc", "urn:un:unece:uncefact:documentation:2");
                    _CreditNoteNamespaces.Add("ds", "http://www.w3.org/2000/09/xmldsig#");
                    _CreditNoteNamespaces.Add("qdt", "urn:oasis:names:specification:ubl:schema:xsd:QualifiedDatatypes-2");
                    _CreditNoteNamespaces.Add("ubltr", "urn:oasis:names:specification:ubl:schema:xsd:TurkishCustomizationExtensionComponents");
                    _CreditNoteNamespaces.Add("udt", "urn:un:unece:uncefact:data:specification:UnqualifiedDataTypesSchemaModule:2");
                    _CreditNoteNamespaces.Add("xades", "http://uri.etsi.org/01903/v1.3.2#");
                    _CreditNoteNamespaces.Add("xsi", "http://www.w3.org/2001/XMLSchema-instance");
                }
                return _CreditNoteNamespaces;
            }
        }


        public static string TransformXMLToHTML(string inputXml, string xsltString)
        {
            StringWriter results = null;
            XslCompiledTransform transform = new XslCompiledTransform();
            using (XmlReader reader = XmlReader.Create(new StringReader(xsltString)))
            {
                try
                {
                    transform.Load(reader);
                    results = new StringWriter();
                    using (XmlReader reader2 = XmlReader.Create(new StringReader(inputXml)))
                    {
                        transform.Transform(reader2, null, results);
                    }

                }
                catch (Exception ex)
                {
                    MessageBox.Show(string.Format(" HTML dönüşümü başarısız: {0}", ex.Message));

                }

            }
            if (results != null)
            {
                return results.ToString();
            }
            else
            {
                return null;
            }


        }


    }
}
