using System;
using System.Collections.Generic;
using System.ComponentModel;
using System.Data;
using System.Drawing;
using System.Linq;
using System.Text;
using System.Threading.Tasks;
using System.Windows.Forms;
using EDefterDemoProject.Connect;
using System.IO;



namespace EDefterDemoProject
{
    public partial class Form1 : Form
    {
        public Form1()
        {
            InitializeComponent();
        }


        private void button1_Click(object sender, EventArgs e)
        {
            if (openFileDialog1.ShowDialog() == DialogResult.OK)

                try
                {

                    txtFilePath.Text = openFileDialog1.FileName;
                    lblFileName.Text = "" + openFileDialog1.SafeFileName;
                }
                catch
                {
                    MessageBox.Show(openFileDialog1.FileName + " Bu dosya açılamadı");
                }
        }

        private void Form1_Load(object sender, EventArgs e)
        {
            openFileDialog1.Filter = "XML Dosyaları|*.xml|" +
                                     "CSV Dosyaları|*.csv";
            openFileDialog1.Title = "Açılacak dosya";
            saveFileDialog1.Filter = openFileDialog1.Filter;
            saveFileDialog1.DefaultExt = "rtf";
            saveFileDialog1.Title = "Kaydedilecek dosya";
        }


        private void button2_Click_1(object sender, EventArgs e)
        {
            LedgerIntegrationClient client = CreateClient();
            LedgerSourceRequest request = new LedgerSourceRequest();
            request.FileName = lblFileName.Text;
            request.Data = File.ReadAllBytes(txtFilePath.Text);
            StringResponse response = client.UploadSource(request);

            if (response.IsSucceded == true)
            {
                txtSourceID.Text = response.Value;
            }
            else
            {
                MessageBox.Show(string.Format("Hata: {0} ", response.Message));
            }


        }

        public LedgerIntegrationClient CreateClient()
        {
            var client = new LedgerIntegrationClient();
            client.ClientCredentials.UserName.UserName = "Uyumsoft";
            client.ClientCredentials.UserName.Password = "Uyumsoft";
            client.Endpoint.Address = new System.ServiceModel.EndpointAddress("https://efatura-test.uyumsoft.com.tr/services/ledgerIntegration");
            return client;
        }


        public class LogItem
        {
            public DateTime LogDate { get; set; }
            public string Message { get; set; }

        }

        public class LedgerItem
        {
            public string Status { get; set; }
            public int StatusCode { get; set; }
            public string Identifier { get; set; }
            public string DocumentID { get; set; }

        }


        private void btnGetSourceInformation_Click(object sender, EventArgs e)
        {
            var client = CreateClient();
          
            var response = client.GetSourceInformation(txtSourceID.Text);
            List<LogItem> logItemList = new List<LogItem>();
            List<LedgerItem> ledgerItemList = new List<LedgerItem>();
            if (response.Value != null && response.Value.Logs != null)
            {
                for (int i = 0; i < response.Value.Logs.Length; i++)
                {
                    var logItem = response.Value.Logs[i];
                    LogItem item = new LogItem
                    {
                        LogDate = logItem.CreateDateUtc,
                        Message = logItem.Message
                    };
                    logItemList.Add(item);

                }

                grdSourceLogs.DataSource = logItemList;
                if (response.Value.Ledgers != null)
                {
                    for (int i = 0; i < response.Value.Ledgers.Length; i++)
                    {
                        var ledgerItem = response.Value.Ledgers[i];
                        LedgerItem Item = new LedgerItem
                        {
                            StatusCode = ledgerItem.StatusCode,
                            Status = ledgerItem.Status.ToString(),
                            DocumentID = ledgerItem.DocumentId,
                            Identifier = ledgerItem.Identifier
                        };
                        ledgerItemList.Add(Item);
                    }

                    grdLedgers.DataSource = ledgerItemList;
                }
            }

            else
            {
                MessageBox.Show("Listelenecek öğe bulunamadı");
            }

        }

        private void btnDonwloadLedger_Click(object sender, EventArgs e)
        {

            LedgerIntegrationClient client = CreateClient();
            var selectedLedgerId = grdLedgers.Rows[grdLedgers.SelectedCells[0].RowIndex].Cells[2].Value.ToString();
            lblStatus.Text = selectedLedgerId;

            var response = client.GetLedgerData(selectedLedgerId, false);
            LedgerData ledgerdata = response.Value;
            byte[] ByteArray = ledgerdata.Data;
            string FileName = ledgerdata.FileName;
            string fileFullPath = string.Empty;


            folderBrowserDialog1.ShowNewFolderButton = true;
            DialogResult result = folderBrowserDialog1.ShowDialog();
            if (result == DialogResult.OK)
            {
                fileFullPath = Path.Combine(folderBrowserDialog1.SelectedPath, FileName);

            }
            SaveByteArrayToFile(ByteArray, fileFullPath);
        }

        private void SaveByteArrayToFile(byte[] ByteArray, string FileName)
        {
            try
            {

                using (FileStream File = new FileStream(FileName, FileMode.Create, FileAccess.Write))
                {

                    File.Write(ByteArray, 0, ByteArray.Count());
                    MessageBox.Show("Dosya Kaydedildi" + " " + FileName);
                }
            }
            catch (FileNotFoundException)
            {
                MessageBox.Show("Dosya Bulunamadı " + FileName);
            }
            catch (Exception ex)
            {

                MessageBox.Show(ex.Message);
            }
        }



    }
}
